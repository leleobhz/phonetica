<?php
//
// SIMP
// Descricao: Parser de conversao de HTML para XHTML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 28/01/2008
// Modificado: 22/06/2009
// License: LICENSE.TXT
// Copyright (C) 2008  Rubens Takiguti Ribeiro
//

// Constantes
define('HTML2XHTML_INICIO',   0); // Nada foi lido
define('HTML2XHTML_HEADER',   1); // Cabecalho XML ja foi lido
define('HTML2XHTML_DOCTYPE',  2); // DocType ja foi lido
define('HTML2XHTML_CONTEUDO', 3); // Algum conteudo ja foi lido

final class html2xml {
    private $xml;
    private $pos;
    private $pilha;
    private $len;
    private $nl;
    private $ignorar_depreciados;
    private $status;
    public $url_base;


    // Vetor de elementos que requerem filhos
    public static $requer_filho = array('applet', 'button', 'fieldset',
        'frameset', 'map', 'noframes', 'object', 'optgroup', 'select', 'table',
        'tbody', 'thead', 'tfoot', 'tr', 'menu', 'ol', 'ul', 'form');

    // Vetor de elementos que nao podem ser fechados
    public static $nao_fechar = array('area', 'base', 'basefont', 'br', 'col',
        'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param');

    // Vetor de elementos com fechamento opcional
    public static $opcionais = array('body', 'colgroup', 'dd', 'dt', 'head',
        'html', 'li', 'option', 'p', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr');

    // Vetor de elementos depreciados
    public static $depreciados = array('applet', 'basefont', 'center', 'dir',
        'font', 'isindex', 'menu', 'strike', 's', 'u', 'i', 'b', 'layer',
        'marquee', 'embed', 'noembed', 'shadow');

    // Vetor de atributos depreciados
    public static $atributos_depreciados = array('width', 'height',
        'leftmargin', 'topmargin', 'rightmargin', 'bottommargin',
        'marginwidth', 'marginheight', 'margin', 'padding',
        'cellpadding', 'cellspacing',
        'target', 'background', 'border', 'bordercolor', 'bgcolor', 'color',
        'alink', 'vlink', 'align', 'valign', 'language', 'link',
        'noshade', 'nowrap', 'size', 'start', 'text', 'compact', 'scrolling');

    // Vetor de atributos booleanos
    public static $atributos_booleanos = array('compact', 'nowrap', 'ismap',
        'declare', 'noshade', 'checked', 'disabled', 'readonly', 'multiple',
        'selected', 'noresize', 'defer');


    //
    //     Retorna se um elemento pode ser filho de outro
    //
    public function pode_filho($tag, $filho) {
    // String $tag: elemento pai
    // String $filho: elemento filho a ser testado
    //
        $tag = strtolower($tag);
        $filho = strtolower($filho);

        // Elementos List
        $list = array('dir', 'dl', 'li', 'ol', 'ul');

        // Elementos BLOCK
        $block = array('address', 'blockquote', 'center', 'div', 'dir', 'frameset',
           'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'isindex', 'noframes',
           'noscript', 'p', 'pre', 'table', 'form');

        // Elementos INLINE
        $inline = array('a', 'abbr', 'acronym', 'area', 'b', 'basefont', 'bdo',
            'big', 'br', 'cite', 'code', 'dfn', 'em', 'font', 'i', 'img',
            'ins', 'kdb', 'param', 'q', 's', 'samp', 'small', 'span', 'strike',
            'strong', 'sub', 'sup', 'textarea', 'tt', 'u', 'var');

        // Elementos block inline
        $block_inline = array('applet', 'button', 'del', 'iframe', 'ins',
            'map', 'object', 'script');

        $block = array_merge($block, $block_inline, $list);
        $inline = array_merge($inline, $block_inline);

        // Se nao esta em vetor nenhum, supor que pode
        if (!in_array($filho, $inline) && !in_array($filho, $block) && !in_array($filho, $list)) {
            return true;
        }

        switch ($tag) {

        // Elementos especiais
        case 'applet':
            return $filho == 'param' || in_array($filho, $inline) || in_array($filho, $block);
        case 'button':
            return in_array($filho, $inline) && !in_array($filho, array('a', 'button', 'input', 'label', 'iframe', 'select', 'textarea'));
        case 'dl':
            return in_array($filho, array('dd', 'dt'));
        case 'fieldset':
            return in_array($filho, $inline) || in_array($filho, $block) || $filho == 'legend';
        case 'frameset':
            return in_array($filho, array('frameset', 'frame', 'noframes'));
        case 'head':
            return in_array($filho, array('base', 'isindex', 'link', 'meta', 'object', 'script', 'style', 'title'));
        case 'input':
            return $filho != 'button' && in_array($filho, $block);
        case 'html':
            return in_array($filho, array('head', 'body', 'frameset'));
        case 'map':
            return in_array($filho, $block) || $filho == 'area';
        case 'noframes':
            return in_array($filho, $inline) || in_array($filho, $block) || $filho == 'body';
        case 'object':
            return in_array($filho, $inline) || in_array($filho, $block) || $filho == 'param';
        case 'optgroup':
            return $filho == 'option';
        case 'pre':
            return in_array($filho, $inline) && !in_array($filho, array('applet', 'big', 'basefont', 'font', 'img', 'object', 'small', 'sub', 'sup'));
        case 'select':
            return in_array($filho, array('option', 'optgroup'));
        case 'table':
            return in_array($filho, array('tbody', 'tfoot', 'thead', 'caption', 'tr'));
        case 'tbody':
        case 'tfoot':
        case 'thead':
            return $filho == 'tr';

        case 'tr':
            return in_array($filho, array('th', 'td'));

        // Elementos Lista
        case 'menu':
        case 'ol':
        case 'ul':
            return $filho == 'li';

        // Elementos exclusivos inline
        case 'a':
        case 'label':
            return $filho != $tag && in_array($filho, $inline);

        // Elementos exclusivos block
        case 'form':
            return $filho != $tag && in_array($filho, $block);

        // Elementos com filhos inline
        case 'abbr':
        case 'acronym':
        case 'address':
        case 'b':
        case 'bdo':
        case 'big':
        case 'cite':
        case 'code':
        case 'dd':
        case 'dfn':
        case 'dir':
        case 'em':
        case 'font':
        case 'h1':
        case 'h2':
        case 'h3':
        case 'h4':
        case 'h5':
        case 'h6':
        case 'i':
        case 'kbd':
        case 'legend':
        case 'p':
        case 'q':
        case 's':
        case 'samp':
        case 'small':
        case 'span':
        case 'strike':
        case 'strong':
        case 'sub':
        case 'sup':
        case 'tt':
        case 'u':
        case 'var':
            return in_array($filho, $inline);

        // Elementos com filhos block
        case 'blockquote':
            return in_array($filho, $block);

        // Elementos com filhos inline ou block
        case 'body':
        case 'center':
        case 'del':
        case 'div':
        case 'dt':
        case 'iframe':
        case 'ins':
        case 'li':
        case 'noscript':
        case 'td':
        case 'th':
            return in_array($filho, $inline) || in_array($filho, $block);

        // Elementos sem filhos
        case 'area':
        case 'base':
        case 'basefont':
        case 'br':
        case 'frame':
        case 'hr':
        case 'img':
        case 'isindex':
        case 'link':
        case 'meta':
        case 'option':
        case 'param':
        case 'script':
        case 'style':
        case 'textarea':
        case 'title':
        default:
            return false;

        }
    }


    //
    //     Gera um cabecalho XML padrao
    //
    static public function get_cabecalho() {
        return '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'."\n";
    }


    //
    //     Gera um DocType padrao para XHTML transitional
    //
    static public function get_doctype() {
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
               '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
               "\n";
    }

    //
    //     Inicio do parser
    //
    public function parse($texto, $nl = '', $ignorar_depreciados = true) {
    // String $texto: texto HTML a ser verificado
    // String $nl: caracter(es) utilizados para quebra de linha
    // Bool $ignorar_depreciados: flag que permite ignorar tags e atributos depreciados
    //
        $this->pos = 0;
        $this->xml = '';
        $this->pilha = array();
        $this->texto = $texto;
        $this->len = strlen($this->texto);
        $this->nl = $nl;
        $this->ignorar_depreciados = $ignorar_depreciados;
        $this->status = HTML2XHTML_INICIO;

        while ($this->pos <= $this->len) {
            $texto = substr($this->texto, $this->pos);
            if (preg_match('/^<\?xml/i', $texto)) {
                $metodo = 'parse_xml';
            } elseif (preg_match('/^<!DOCTYPE/i', $texto)) {
                $metodo = 'parse_doctype';
            } elseif (preg_match('/^<!--/i', $texto)) {
                $metodo = 'parse_comentario';
            } elseif (preg_match('/^<!\[CDATA\[/i', $texto)) {
                $metodo = 'parse_cdata';
            } elseif (preg_match('/^<[\/]?[A-z][A-z0-9-_:]*/i', $texto)) {
                $metodo = 'parse_tag';
            } else {
                $metodo = 'parse_texto';
            }
            $this->$metodo($texto);
        }
        $this->restaurar();
        $this->operacoes_finais();
        return $this->xml;
    }


    //
    //     Interpreta um cabecalho XML
    //
    private function parse_xml($texto) {
    // String $texto: texto a ser interpretado
    //
        $inicio = strlen('<'.'?xml');
        $fim = strpos($texto, '?'.'>');
        $fim = ($fim !== false) ? $fim : strlen($fim);

        // Pode ser um xml-stylesheet
        if (preg_match('/^<\?xml(A-z0-9-_)+/i', $texto, $match)) {
            $tipo = $match[1];
        } else {
            $tipo = '';
        }

        // Obter o conteudo do cabecalho
        $conteudo = substr($texto, $inicio, $fim - $inicio);
        $conteudo = trim($conteudo);
        $atributos = $this->parse_atributos($conteudo);

        switch ($this->status) {
        case HTML2XHTML_INICIO:
        case HTML2XHTML_HEADER:
            $this->xml .= '<'."?xml{$tipo}{$atributos}?".">\n";
            $this->status = HTML2XHTML_HEADER;
        case HTML2XHTML_DOCTYPE:
        case HTML2XHTML_CONTEUDO:
            break;
        }
        $this->pos += $fim + strlen('?'.'>');
    }


    //
    //     Interpreta um cabecalho DOCTYPE
    //
    private function parse_doctype($texto) {
    // String $texto: texto a ser interpretado
    //
        $pos = strpos($texto, '>') + 1;

        // Nao incluir o mesmo DOCTYPE pois estamos alterando de HTML para XHTML
        if ($pos !== false) {
            //$this->xml .= substr($texto, 0, $pos);
            $this->pos += $pos;
        } else {
            //$this->xml .= $texto;
            $this->pos = strlen($texto) + 1;
        }

        switch ($this->status) {
        case HTML2XHTML_INICIO:
            $this->xml .= self::get_cabecalho();
        case HTML2XHTML_HEADER:
            $this->xml .= self::get_doctype();
            $this->status = HTML2XHTML_DOCTYPE;
        case HTML2XHTML_DOCTYPE:
        case HTML2XHTML_CONTEUDO:
            break;
        }
    }


    //
    //     Interpreta um comentario
    //
    private function parse_comentario($texto) {
    // String $texto: texto a ser interpretado
    //
        $inicio = strlen('<!--');
        $fim = strpos($texto, '-->');
        $fim = ($fim !== false) ? $fim : strlen($fim);

        // Obter o conteudo do comentario
        $comentario = substr($texto, $inicio, $fim - $inicio);
        $comentario = trim($comentario);

        switch ($this->status) {
        case HTML2XHTML_INICIO:
            $this->xml .= self::get_cabecalho();
        case HTML2XHTML_HEADER:
            $this->xml .= self::get_doctype();
            $this->status = HTML2XHTML_DOCTYPE;
        case HTML2XHTML_DOCTYPE:
        case HTML2XHTML_CONTEUDO:
            switch ($this->topo()) {

            // Blocos de script ou estilo
            case 'script':
            case 'style':
                // Em scripts, o "--" tem significado proprio, entao deve ser
                // delimitado com um bloco CDATA
                $comentario = texto::decodificar($comentario);
                $this->xml .= "<![CDATA[ {$comentario} ]]>";
                break;

            // Comentarios de texto
            default:

                // Remover qualquer ocorrencia de "--" e adicionar no documento
                $comentario = str_replace('--', '(menos)(menos)', $comentario);
                $comentario = texto::decodificar($comentario);
                $this->xml .= "<!-- {$comentario} -->{$this->nl}";
                break;
            }
            break;
        }

        $this->pos += $fim + strlen('-->');
    }


    //
    //     Interpreta um bloco CDATA
    //
    private function parse_cdata($texto) {
    // String $texto: texto a ser interpretado
    //
        $inicio = strlen('<![CDATA[');
        $fim = strpos($texto, ']]>');
        $fim = ($fim !== false) ? $fim : strlen($fim);

        // Obter o conteudo do bloco de dados
        $conteudo = substr($texto, $inicio, $fim - $inicio);
        $conteudo = trim($conteudo);

        switch ($this->status) {
        case HTML2XHTML_CONTEUDO:
            $conteudo = texto::decodificar($conteudo);
            $this->xml .= "<![CDATA[ {$conteudo} ]]>";
            break;
        case HTML2XHTML_INICIO:
        case HTML2XHTML_HEADER:
        case HTML2XHTML_DOCTYPE:
            break;
        }
        $this->pos += $fim + strlen('-->');
    }


    //
    //     Interpreta uma tag
    //
    private function parse_tag($texto) {
    // String $texto: texto a ser interpretado
    //
        // Obter nome da tag
        if (preg_match('/^<([\/]?[A-z][A-z0-9-_:]*)([^>]*)?>/i', $texto, $match)) {
            $nome = strtolower($match[1]);
            $atributos = $match[2];
            $len_atributos = strlen($atributos);
            if ($atributos[$len_atributos - 1] == '/') {
                $atributos = substr($atributos, 0, $len_atributos - 1);
            }

            $this->pos += strlen($match[0]);

            switch ($this->status) {
            case HTML2XHTML_INICIO:
                $this->xml .= self::get_cabecalho();
            case HTML2XHTML_HEADER:
                $this->xml .= self::get_doctype();
                $this->status = HTML2XHTML_DOCTYPE;
            case HTML2XHTML_DOCTYPE:
            case HTML2XHTML_CONTEUDO:

                // Fechar Tag
                if ($nome[0] == '/') {
                    $nome = substr($nome, 1);

                    if ($this->ignorar_depreciados && in_array($nome, self::$depreciados)) {
                        break;
                    }
                    $this->fechar_tag($nome);

                // Abrir Tag
                } else {
                    if ($this->ignorar_depreciados && in_array($nome, self::$depreciados)) {
                        break;
                    }
                    $atributos = $this->parse_atributos($atributos, $nome);
                    $this->abrir_tag($nome, $atributos);
                }
                $this->status = HTML2XHTML_CONTEUDO;
                break;
            }

        // Formato invalido de tag
        } else {
            echo "Erro: formato invalido de tag (".substr($texto, 0, 10)."...)\n";
            exit(1);
            $this->pos++;
        }
    }


    //
    //     Interpreta os atributos de uma tag
    //
    private function parse_atributos($atributos, $tag = false) {
    // String $atributos: texto a ser interpretado
    // String $tag: nome da tag que possui os atributos
    //
        // Vetor de atributos lidos
        $vt = array();

        // Obter nome da tag e o restante
        $resto = trim($atributos);
        while (preg_match('/^([A-z]+[A-z0-9-_:]*)([\040|=]?)/i', $resto, $match)) {
            $nome  = strtolower($match[1]);
            $igual = $match[2];
            $resto = str_replace($nome.$igual, '', $resto);

            // Se e' um atributo booleano
            if (in_array($nome, self::$atributos_booleanos)) {

                $vt[$nome] = "{$nome}=\"{$nome}\"";
                if ($igual == '=') {
                    $delim = $resto[0];
                    switch ($delim) {
                    case '"':
                    case "'":
                        $pos = strpos($resto, $delim, 1);
                        $resto = substr($resto, $pos + 1);
                        break;
                    default:
                        $pos = strpos($resto, ' ');
                        if ($pos === false) {
                            $pos = strlen($resto);
                        }
                        $resto = substr($resto, $pos + 1);
                        break;
                    }
                } else {
                    $pos = strlen($nome);
                    //$resto = $resto;
                }

            // Se e' um atributo simples
            } elseif ($igual == '=') {

                // Obter valor do atributo
                $delim = $resto[0];
                switch ($delim) {
                case '"':
                case "'":
                    $pos = strpos($resto, $delim, 1);
                    $valor = substr($resto, 1, $pos - 1);
                    break;
                default:
                    $delim = '"';
                    $pos = strpos($resto, ' ');
                    if ($pos === false) {
                        $pos = strlen($resto);
                    }
                    $valor = substr($resto, 0, $pos);
                    break;
                }

                $vt[$nome] = $nome.'='.$delim.htmlspecialchars(texto::decodificar($valor)).$delim;
                $resto = substr($resto, $pos + 1);

            // Se e' um atributo simples, mas sem valor
            } else {
                $vt[$nome] = $nome.'=""';
                $resto = substr($resto, strlen($nome));
            }
            $resto = trim($resto);
        }

        if (trim($resto) && defined('DEBUG') && DEBUG) {
            echo "Erro: atributos em formato invalido na tag {$tag} ({$resto})\n";
            exit(1);
        }

        // Definir atributos obrigatorios
        switch ($tag) {
        case 'area':
        case 'img':
            if (!isset($vt['alt'])) {
                $vt['alt'] = 'alt=""';
            }
            break;
        case 'html':
            if (!isset($vt['xmlns'])) {
                $vt['xmlns'] = 'xmlns="http://www.w3.org/1999/xhtml"';
            }
            break;
        case 'map':
            static $i = 0;
            if (!isset($vt['id'])) {
                $vt['id'] = 'id="map'.($i++).'"';
            }
            break;
        case 'meta':
            if (isset($vt['http-equiv']) &&
                strpos($vt['http-equiv'], 'http-equiv="content-type"') !== false &&
                isset($vt['content'])) {
                $vt['content'] = 'content="application/xhtml+xml; charset=UTF-8"';
            }
            break;
        case 'param':
            if (!isset($vt['name'])) {
                $vt['name'] = 'name=""';
            }
            break;
        case 'style':
            if (!isset($vt['type'])) {
                $vt['type'] = 'type="text/css"';
            }
            break;
        case 'script':
            if (!isset($vt['type'])) {
                $vt['type'] = 'type="text/javascript"';
            }
            break;
        }

        if (count($vt)) {
            if ($this->ignorar_depreciados) {
                foreach ($vt as $atributo => $item) {
                    if (in_array($atributo, self::$atributos_depreciados)) {
                        $vt[$atributo] = '';
                        unset($vt[$atributo]);
                    }
                }
            }
            if (count($vt)) {
                return ' '.implode(' ', $vt);
            }
        }
        return '';
    }


    //
    //     Abre uma Tag
    //
    private function abrir_tag($nome, $atributos) {
    // String $nome: nome da tag a ser aberta
    // String $atributos: atributos da tag a ser aberta
    //
        $topo = $this->topo();
        $obj = new stdClass();
        $obj->tag = $nome;
        $obj->opcional = in_array($nome, self::$opcionais);

        // A Tag nao pode ser fechada
        if (in_array($nome, self::$nao_fechar)) {

            // Se nao tem topo ou pode adicionar o filho
            if (!$topo || self::pode_filho($topo, $nome)) {
                //array_push($this->pilha, $obj);
                $this->xml .= "<{$nome}{$atributos}/>{$this->nl}";
                //array_pop($this->pilha);

            // Se nao pode adicionar o filho
            } else {
                if (defined('DEBUG') && DEBUG) {
                    echo "Erro: {$nome} nao pode ser filho de {$topo}\n";
                    exit(1);
                }

                do {
                    $topo = array_pop($this->pilha);
                    $this->xml .= "</{$topo->tag}>{$this->nl}";
                    if (self::pode_filho($this->topo(), $nome)) {
                        //array_push($this->pilha, $obj);
                        $this->xml .= "<{$nome}{$atributos}/>{$this->nl}";
                        //array_pop($this->pilha);
                        break;
                    }
                } while (count($this->pilha));
            }

        // A Tag pode ser fechada
        } else {

            // Se pode inserir imediatamente
            if (!$topo || self::pode_filho($topo, $nome)) {
                array_push($this->pilha, $obj);
                $this->xml .= "<{$nome}{$atributos}>";

            // Se nao pode inserir, tentar depurar
            } else {
                if (defined('DEBUG') && DEBUG) {
                    echo "Erro: {$nome} nao pode ser filho de {$topo}\n";
                    exit(1);
                }

                do {
                    $topo = array_pop($this->pilha);
                    $this->xml .= "</{$topo->tag}>{$this->nl}";
                    if (self::pode_filho($this->topo(), $nome)) {
                        array_push($this->pilha, $obj);
                        $this->xml .= "<{$nome}{$atributos}>";
                        break;
                    }
                } while (count($this->pilha));
            }
        }
    }


    //
    //     Fecha uma Tag
    //
    private function fechar_tag($nome) {
    // String $nome: nome da tag a ser fechada
    //
        while ($topo = array_pop($this->pilha)) {
            if ($topo->tag != $nome) {
                if ($topo->opcional) {
                    $this->xml .= "</{$topo->tag}>{$this->nl}";
                } else {
                    if (defined('DEBUG') && DEBUG) {
                        echo "Erro: esperado {$topo->tag}, encontrado /{$nome}\n";
                        exit(1);
                    }
                    $this->xml .= "</{$topo->tag}>{$this->nl}";
                }
            } else {
                $this->xml .= "</{$topo->tag}>{$this->nl}";
                return;
            }
        }
    }


    //
    //     Interpreta um conteudo de texto
    //
    private function parse_texto($texto) {
    // String $texto: texto a ser interpretado
    //
        $pos = strpos($texto, '<');
        if ($pos !== false) {
            $buf = substr($texto, 0, $pos);
        } else {
            $buf = $texto;
            $pos = strlen($texto) + 1;
        }

        switch ($this->status) {
        case HTML2XHTML_CONTEUDO:
            $this->xml .= $buf ? htmlspecialchars(texto::decodificar($buf)) : '';
//            if (in_array($this->topo(), $block)) {
//                $this->xml .= $this->nl;
//            }
            break;
        case HTML2XHTML_INICIO:
        case HTML2XHTML_HEADER:
        case HTML2XHTML_DOCTYPE:
            break;
        }

        $this->pos += $pos;
    }


    //
    //     Restaura o buffer com os elementos da pilha
    //
    private function restaurar() {
        while ($topo = array_pop($this->pilha)) {
            $this->xml .= "</{$topo->tag}>{$this->nl}";
        }
    }


    //
    //     Realiza algumas operacoes finais sobre o documento
    //
    private function operacoes_finais() {
        try {
            $xml = simplexml_load_string($this->xml);
        } catch (Exception $e) {
            if (defined('DEBUG') && DEBUG) {
                echo "Erro: XML invalido (".$e->getMessage().")\n";
            }
            return;
        }

        // 1 - Incluir um <base>
        if ($this->url_base && !$xml->head->base) {
            $base = &$xml->head->addChild('base');
            $base->addAttribute('href', $this->url_base);
        }
        $this->xml = $xml->asXML();
    }


    //
    //     Obtem o topo da pilha
    //
    private function topo() {
        $topo = array_pop($this->pilha);
        array_push($this->pilha, $topo);
        return $topo->tag;
    }

}//class
