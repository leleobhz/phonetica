<?php
//
// SIMP
// Descricao: Classe de geracao de feeds RSS 2.0 (Consulte http://validator.w3.org/feed/docs/rss2.html)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 01/11/2007
// Modificado: 22/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('RSS_LOCALIDADE', $CFG->localidade);

final class rss {
    private $rss;
    private $style;


    //
    //     Construtor padrao
    //
    public function __construct($title, $link, $description, $style = false) {
    // String $title: titulo do RSS
    // String $link: link da pagina principal do RSS
    // String $description: descricao do RSS
    // String $style: endereco do arquivo de estilos (CSS ou XSL)
    //
        // Estilo opcional
        $this->style = $style;

        // Montando o XML
        $rss_base = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        if ($this->style) {
            if (preg_match('/\.css$/', $this->style)) {
                $mime_style = 'text/css';
            } elseif (preg_match('/\.xsl$/', $this->style)) {
                $mime_style = 'text/xsl';
            } else {
                $mime_style = 'text/css';
            }
            $rss_base .= "<?xml-stylesheet type=\"{$mime_style}\" href=\"{$style}\" >\n";
        }
        $rss_base .= "<!DOCTYPE rss PUBLIC \"-//IDN Silmaril.IE//DTD RSS 2.0//EN\" ".
                     "  \"http://www.silmaril.ie/software/rss2.dtd\">\n".
                     "<rss version=\"2.0\">\n".
                     "  <channel>\n".
                     "    <title></title>\n".
                     "    <link></link>\n".
                     "    <description></description>\n".
                     "  </channel>\n".
                     "</rss>";

        $this->rss = new SimpleXMLElement($rss_base);

        // Atributos obrigatorios do Channel
        $this->rss->channel->title       = $title;
        $this->rss->channel->link        = $link;
        $this->rss->channel->description = $description;
    }


    //
    //     Define um atributo de channel opcional
    //
    public function set_atributo($atributo, $valor) {
    // String $atributo: nome do atributo
    // String $valor: valor do atributo
    //
        switch ($atributo) {

        // Atributos sem filtro
        case 'language':
        case 'copyright':
        case 'managingEditor':
        case 'webMaster':
        case 'category':
        case 'generator':
        case 'docs':
        case 'cloud':
        case 'ttl':
        case 'image':
        case 'textInput':
        case 'skipHours':
        case 'skipDays':
            break;

        // Filtro de data
        case 'pubDate':
        case 'lastBuildDate':
            if (is_numeric($valor)) {
                setlocale(LC_ALL, 'C');
                $valor = strftime('%a, %d %b %Y %H:%M:%S %z', $valor);
                setlocale(LC_ALL, RSS_LOCALIDADE);
            }
            break;
        default:
            $valor = false;
        }
        if ($valor !== false) {
            $this->rss->channel->$atributo = $valor;
        }
    }


    //
    //     Define a imagem do RSS
    //
    public function definir_image($url, $title, $link, $width = false, $height = false) {
    // String $url: URL da imagem
    // String $title: titulo da imagem
    // String $link: link para onde a imagem aponta
    // Int $width: largura da imagem em pixels
    // Int $height: altura da imagem em pixels
    //
        $img = &$this->rss->channel->image;
        $img->url   = $url;
        $img->title = $title;
        $img->link  = $link;
        if ($width) { $img->width = (int)$width; }
        if ($height) { $img->height = (int)$height; }
    }


    //
    //     Definie o elemento cloud do RSS
    //
    public function definir_cloud() {
        //TODO
    }


    //
    //     Define o elemento ttl do RSS
    //
    public function definir_ttl($minutos) {
    // Int $minutos: numero de minutos que o RSS ficara no cache
    //
        $this->rss->channel->ttl = (int)$minutos;
    }


    //
    //     Define um campo textInput ao RSS
    //
    public function definir_textInput($title, $description, $name, $link) {
    // String $title: label do campo de entrada
    // String $description: descricao do campo de entrada
    // String $name: nome do objeto do campo
    // String $link: endereco de destino do dado (action)
    //
        $input = &$this->rss->channel->textInput;
        $input->title       = $title;
        $input->description = $description;
        $input->name        = $name;
        $input->link        = $link;
    }


    //
    //     Adiciona um elemento item ao RSS (nova noticia)
    //
    public function adicionar_item($title, $description = '', $opcoes = false) {
    // String $title: titulo do item
    // String $description: descricao do item
    // Array[String => Array[String => String]] $opcoes: lista de opcoes adicionais apontando para os atributos
    //
        $item = $this->rss->channel->addChild('item');
        $item->title = $title;
        if ($description) { $item->description = $description; }
        if (!is_array($opcoes)) {
            return;
        }
        foreach ($opcoes as $opcao => $dados) {
            $cancelar = false;
            switch ($opcao) {

            // Atributos sem filtro
            case 'link':
            case 'author':
            case 'category':
            case 'comments':
            case 'enclosure':
            case 'guid':
            case 'source':
                break;

            // Atributos com filtro
            case 'pubDate':
                if (is_numeric($dados['valor'])) {
                    setlocale(LC_ALL, 'C');
                    $dados['valor'] = strftime('%a, %d %b %Y %H:%M:%S %z', $dados['valor']);
                    setlocale(LC_ALL, RSS_LOCALIDADE);
                }
                break;
            default:
                $cancelar = true;
            }
            if (!$cancelar) {
                $op = $item->addChild($opcao, $dados['valor']);
                foreach ($dados as $atributo => $valor) {
                    if ($atributo == 'valor' || is_numeric($atributo)) { continue; }
                    $op->addAttribute($atributo, $valor);
                }
            }
        }
    }


    //
    //     Retorna o conteudo do objeto como um arquivo RSS
    //
    public function imprimir() {
        header('Content-Type: application/rss+xml; charset=UTF-8');
        echo $this->rss->asXML();
    }

}//class
