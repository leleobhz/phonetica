<?php
//
// SIMP
// Descricao: Classe para manipulacao de Imagens
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 28/02/2008
// Modificado: 10/06/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('IMAGEM_LOCALIDADE', $CFG->localidade);
define('IMAGEM_FONTE',      $CFG->dirclasses.'interface/fontes/AppleGaramond.ttf');

final class imagem {
    private $arquivo;
    private $img;
    private $mimetype;
    private $erros;


/// @ Metodos gerais


    //
    //     Construtor padrao: utilizar o primeiro ou o segundo parametro
    //
    public function __construct($arquivo = false, $binario = false) {
    // String $arquivo: nome do arquivo
    // String $binario: arquivo no formato binario
    //
        if ($arquivo) {
            $this->arquivo = $arquivo;
            $this->erros = array();

            if (!file_exists($this->arquivo)) {
                $this->erros[] = 'Arquivo '.$this->arquivo.' n&atilde;o existe';
                return false;
            }

            $this->mimetype = self::get_mimetype($arquivo);
            if (!$this->mimetype) {
                $this->erros[] = 'Extens&atilde;o de arquivo inv&aacute;lida';
                return false;
            }
            static $mimetypes = array('image/jpeg' => 'jpeg',
                                      'image/png'  => 'png',
                                      'image/gif'  => 'gif');

            $nome_funcao = 'imagecreatefrom'.$mimetypes[$this->mimetype];

            $this->img = $nome_funcao($arquivo);
        } else {
            $this->img      = imagecreatefromstring($binario);
            $this->arquivo  = 'imagem.jpg';
            $this->mimetype = 'image/jpeg';
        }
    }


    //
    //     Destrutor padrao
    //
    function __destruct() {
        if ($this->img) {
            imagedestroy($this->img);
        }
    }


    //
    //     Tenta obter o mimetype a partir da extensao do arquivo
    //
    public static function get_mimetype($arquivo) {
    // String $arquivo: nome do arquivo
    //
        // Se tem o modulo exif
        if (extension_loaded('exif') && function_exists('exif_imagetype')) {
            $tipo = exif_imagetype($arquivo);
            switch ($tipo) {
            case IMAGETYPE_GIF:
                return 'image/gif';
            case IMAGETYPE_JPEG:
                return 'image/jpeg';
            case IMAGETYPE_PNG:
                return 'image/png';
            case IMAGETYPE_WBMP:
                return 'image/x-wbmp';
            case false:
                break;
            }
        }

        static $extensoes = array('jpg'  => 'image/jpeg',
                                  'jpeg' => 'image/jpeg',
                                  'gif'  => 'image/gif',
                                  'png'  => 'image/png');
        $pos = strrpos($arquivo, '.');
        if ($pos ===  false) {
            return false;
        }
        $extensao = substr($arquivo, $pos + 1);
        if (isset($extensoes[$extensao])) {
            return $extensoes[$extensao];
        }
        return false;
    }


    //
    //     Retorna a altura e largura para usar em uma tag IMG
    //
    static public function tamanho_html($src) {
    // String $src: caminho da imagem
    //
        $def = getimagesize($src);
        if ($def) {
            return $def[3];
        }
        return '';
    }


    //
    //     Retorna a altura da imagem
    //
    public function get_altura() {
        if ($this->img) {
            return imagesy($this->img);
        }
        return 0;
    }


    //
    //     Retorna a largura da imagem
    //
    public function get_largura() {
        if ($this->img) {
            return imagesx($this->img);
        }
        return 0;
    }


    //
    //     Converte uma cor RGB HTML para um inteiro
    //
    public function get_cor($cor_html) {
    // String $cor_html: cor RGB no formato HTML
    //
        sscanf($cor_html, '#%2x%2x%2x', $r, $g, $b);
        return imagecolorallocate($this->img, $r, $g, $b);
    }


    //
    //     Retorna a imagem de recurso (resource)
    //
    public function get_resource() {
        return $this->img;
    }


    //
    //     Retorna a imagem em formato binario
    //
    public function get_imagem($mimetype, $qualidade = 100, $interlace = 0) {
    // String $mimetype: formato a ser exibido ou false para usar o mesmo da imagem
    // Int $qualidade: qualidade da imagem (apenas para JPG)
    // Bool $interlace: cria um JPEG progressivo ou nao
    //
        if (!$this->img) { return null; }
        switch (strtolower($mimetype)) {
        case 'image/gif':
            if (imagetypes() & IMG_GIF) {
                return imagegif($this->img);
            }
            break;
        case 'image/png':
            if (imagetypes() & IMG_PNG) {
                return imagepng($this->img);
            }
            break;
        case 'image/jpeg':
            if (imagetypes() & IMG_JPEG) {
                imageinterlace($this->img, ($interlace ? 1 : 0));
                return imagejpeg($this->img, '', $qualidade);
            }
            break;
        }
        return false;
    }


    //
    //     Exibe a imagem armazenada em buffer
    //
    public function exibir($mimetype = false, $qualidade = 100, $cache = 0) {
    // String $mimetype: formato a ser exibido ou false para usar o mesmo da imagem
    // Int $qualidade: qualidade da imagem (apenas para JPG) entre 0 e 100
    // Int $cache: tempo de duracao na cache
    //
        if (!$mimetype) {
            $mimetype = $this->mimetype;
        }
        $time = time();

        setlocale(LC_ALL, 'C');
        header('Content-type: '.$mimetype);
        header('Content-Disposition: inline; filename='.basename($this->arquivo));
        header('Date: '.gmstrftime($CFG->gmt, $time));
        header('Last-Modified: '.gmstrftime('%a, %d %b %Y %T %Z', $time));
        if ($cache) {
            header('Cache-Control: public');
            header('Pragma: ');
            header('Expires: '.gmstrftime('%a, %d %b %Y %T %Z', $time + $cache));
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: '.gmstrftime('%a, %d %b %Y %T %Z', $time));
        }
        setlocale(LC_ALL, IMAGEM_LOCALIDADE);

        $this->get_imagem($mimetype, $qualidade);
    }


    //
    //     Obtem os erros ocorridos
    //
    public function get_erros() {
        return $this->erros;
    }


    //
    //     Retorna uma cor transparente (inteiro)
    //
    public function criar_cor_transparente($cor1, $cor2, $transparencia = 0.5, $complemento = 0.5) {
    // Array[Int] $cor1: vetor com as componentes R, G e B da primeira cor
    // Array[Int] $cor2: vetor com as componentes R, G e B da segunda cor
    // Float $transparencia: nivel de transparencia de uma cor em relacao a outra
    // Float $complemento: nivel de transparencia da segunda cor em relacao a primeira
    //
        $r_novo = min(255, ($cor1[0] * $complemento) + ($cor2[0] * $transparencia));
        $g_novo = min(255, ($cor1[1] * $complemento) + ($cor2[1] * $transparencia));
        $b_novo = min(255, ($cor1[2] * $complemento) + ($cor2[2] * $transparencia));
        return imagecolorallocate($this->img, $r_novo, $g_novo, $b_novo);
    }


    //
    //     Gera um retangulo transparente
    //
    private function retangulo_transparente($x1, $y1, $x2, $y2, $cor_base = '#FFFFFF', $transparencia = 0.5) {
    // Int $x1: coordenada X de inicio
    // Int $y1: coordenada Y de inicio
    // Int $x2: coordenada X de fim
    // Int $y2: coordenada Y de fim
    // String $cor_base: cor RGB no formato HTML
    // Float $transparencia: nivel de transparencia da cor
    //
        $complemento = 1 - $transparencia;
        sscanf($cor_base, '#%2x%2x%2x', $r_base, $g_base, $b_base);
        $cor1 = array($r_base,
                      $g_base,
                      $b_base);
        for ($y = $y1; $y < $y2; $y++) {
            for ($x = $x1; $x < $x2; $x++) {
                $cor = imagecolorat($this->img, $x, $y);
                $cor2 = array(($cor >> 16) & 0xFF,
                              ($cor >> 8) & 0xFF,
                              $cor & 0xFF);
                $cor_novo = $this->criar_cor_transparente($cor1, $cor2, $transparencia, $complemento);
                imagesetpixel($this->img, $x, $y, $cor_novo);
            }
        }
    }


/// @ Metodos para modificacao da imagem


    //
    //     Gira uma imagem de acordo com um dado angulo
    //
    public function girar($angulo = 90, $cor_fundo = '#000000') {
    // Int $angulo: angulo a ser rotacionado em graus
    // String $cor_fundo: cor de fundo da imagem, caso o angulo nao seja multiplo de 90
    //
        if (!$this->img) { return null; }
        $cor_fundo = $this->get_cor($cor_fundo);
        $img = imagerotate($this->img, $angulo, $cor_fundo);
        imagedestroy($this->img);
        $this->img = $img;
    }


    //
    //     Redimensiona uma imagem para uma largura ou altura especifica (mantendo-se a proporcao) retornando os bytes da imagem
    //
    public function redimensionar($altura = false, $largura = false, $qualidade = 100) {
    // Int || String $altura: altura desejada com unidade (px ou %) ou false para usar a largura
    // Int || String $largura: largura desejada com unidade (px ou %) ou false para usar a altura
    // Int $qualidade: qualidade da imagem de saida (0-100) caso seja JPG
    //
        if (!$this->img) { return null; }
        $imagem_x = $this->get_largura();
        $imagem_y = $this->get_altura();

        // Usar a altura como base
        if ($altura) {
            sscanf($altura, '%d%s', $altura, $unidade);
            switch ($unidade) {
            case '%':
                $altura = $imagem_y * $altura / 100;
                $tam_y = $altura;
                $tam_x = $imagem_x / ($imagem_y / $tam_y);
                break;
            case 'px':
            default:
                $tam_y = $altura;
                $tam_x = $imagem_x / ($imagem_y / $tam_y);
                break;
            }

        // Usar a largura como base
        } else {
            sscanf($largura, '%d%s', $largura, $unidade);
            switch ($unidade) {
            case '%':
                $largura = $imagem_x * $largura / 100;
                $tam_x = $largura;
                $tam_y = $imagem_y / ($imagem_x / $tam_x);
                break;
            case 'px':
            default:
                $tam_x = $largura;
                $tam_y = $imagem_y / ($imagem_x / $tam_x);
                break;
            }
        }

        $img = imagecreatetruecolor($tam_x, $tam_y);
        imagecopyresized($img, $this->img, 0, 0, 0, 0, $tam_x, $tam_y, $imagem_x, $imagem_y);
        if ($img) {
            imagedestroy($this->img);
            $this->img = &$img;
        } else {
            $this->erros[] = 'Erro ao redimensionar imagem';
        }
    }


    //
    //     Insere uma borda na imagem
    //
    public function adicionar_borda($largura = 1, $cor_html = '#000000', $expandir = false) {
    // Int $largura: largura da borda em px
    // String $cor_html: RGB da cor no formato HTML
    // Bool $expandir: insere a borda diretamente na imagem (false) ou aumenta suas dimensoes para inseri-la (true)
    //
        if (!$this->img) { return null; }
        $largura = round(abs($largura));
        $cor = $this->get_cor($cor_html);

        // Expandir a imagem, caso desejado
        if ($expandir) {
            $img = imagecreatetruecolor($this->get_largura() + 2 * $largura, $this->get_altura() + 2 * $largura);
            imagecopy($img, $this->img, $largura, $largura, 0, 0, $this->get_largura(), $this->get_altura());
            imagedestroy($this->img);
            $this->img = &$img;
        }

        // Desenhar as bordas

        // Topo
        imagefilledrectangle($this->img, 0, 0, $this->get_largura(), $largura, $cor);

        // Esquerda
        imagefilledrectangle($this->img, 0, 0, $largura, $this->get_altura(), $cor);

        // Base
        imagefilledrectangle($this->img, 0, $this->get_altura() - $largura, $this->get_largura(), $this->get_altura(), $cor);

        // Direita
        imagefilledrectangle($this->img, $this->get_largura() - $largura, 0, $this->get_largura(), $this->get_altura(), $cor);
    }


    //
    //     Adiciona uma tarja na imagem
    //
    public function tarja($texto, $cor_texto = '#FFFFFF', $cor_fundo = '#000000', $transparencia = 0.5, $repetir = true) {
    // String $texto: texto a ser exibido na tarja
    // String $cor_texto: cor do texto da tarja
    // String $cor_fundo: cor da tarja
    // Float $transparencia: nivel de transparencia da tarja
    // Bool $repetir: repetir o texto
    //
        if (!$this->img) { return null; }
        $cor_texto = $this->get_cor($cor_texto);
        $altura_fonte = 14;
        $meia_fonte = round($altura_fonte / 2);
        $padding = 5;

        $x_inicio = 0;
        $y_inicio = ($this->get_altura() / 2) - ($meia_fonte + $padding);
        $x_fim    = $this->get_largura();
        $y_fim    = ($this->get_altura() / 2) + ($meia_fonte + $padding);

        // Primeiro desenhar o fundo da tarja
        if (is_double($transparencia)) {
            $this->retangulo_transparente($x_inicio, $y_inicio, $x_fim, $y_fim, $cor_fundo, $transparencia);

        // Tarja solida
        } else {
            $cor_fundo = $this->get_cor($cor_fundo);
            imagefilledrectangle($this->img, 0, $y_inicio, $this->get_largura(), $y_fim, $cor_fundo);
        }

        // Depois desenhar o texto
        if ($repetir) {
            $texto_base = $texto;
            for ($i = 5; $i <= 50; $i += 5) {
                $texto = str_repeat($texto_base.'  ', $i);
                $vt = imagettfbbox($altura_fonte, 0, IMAGEM_FONTE, $texto);
                if ($vt[2] - $vt[0] > $this->get_largura()) {
                    break;
                }
            }
            $x_texto = $padding;
            $y_texto = $y_fim - $padding;
        } else {
            $vt = imagettfbbox($altura_fonte, 0, IMAGEM_FONTE, $texto);
            $x_texto = ($this->get_largura() - ($vt[2] - $vt[0])) / 2;
            $y_texto = $y_fim - $padding;
        }
        imagettftext($this->img, $altura_fonte, 0, $x_texto, $y_texto, $cor_texto, IMAGEM_FONTE, $texto);
    }


    //
    //     Define uma transparencia para toda a imagem
    //
    public function definir_transparencia($transparencia = 0.5, $cor_html = '#FFFFFF') {
    // Float $transparencia: nivel de transparencia
    // String $cor_html: cor RGB no formato HTML para servir de base para a transparencia
    //
        if (!$this->img) { return null; }
        $x1 = 0;
        $y1 = 0;
        $x2 = $this->get_largura();
        $y2 = $this->get_altura();
        $this->retangulo_transparente($x1, $y1, $x2, $y2, $cor_html, $transparencia);
    }


    //
    //     Muda a imagem para escalas de cinza
    //
    public function escala_cinza($peso_r = 0.45, $peso_g = 0.30, $peso_b = 0.25) {
    // Float $peso_r: peso da componente vermelha da cor para conversao para cinza
    // Float $peso_g: peso da componente verde da cor para conversao para cinza
    // Float $peso_b: peso da componente azul da cor para conversao para cinza
    //
        if (!$this->img) { return null; }
        $x_fim = $this->get_largura();
        $y_fim = $this->get_altura();
        for ($y = 0; $y < $y_fim; $y++) {
            for ($x = 0; $x < $x_fim; $x++) {
                $cor = imagecolorat($this->img, $x, $y);
                $r = ($cor >> 16) & 0xFF;
                $g = ($cor >> 8) & 0xFF;
                $b = $cor & 0xFF;
                $tom_cinza = ($r * $peso_r) + ($g * $peso_g) + ($b * $peso_b);
                $tom_cinza = min(255, max(0, $tom_cinza));
                $cor_cinza = imagecolorallocate($this->img, $tom_cinza, $tom_cinza, $tom_cinza);
                imagesetpixel($this->img, $x, $y, $cor_cinza);
            }
        }
    }


    //
    //     Inclui quadriclado na imagem
    //
    public function incluir_quadriculado($largura = '20px', $altura = '20px', $cor_linha = '#000000') {
    // String $largura: largura das celulas (px ou %)
    // String $altura: altura das celulas (px ou %)
    // String $cor_linha: cor em HTML da linha usada no quadriculado
    //
        $altura_img = $this->get_altura();
        $largura_img = $this->get_largura();

        // Converter altura
        sscanf($altura, '%d%s', $altura, $unidade);
        switch ($unidade) {
        case '%':
            $altura = $altura * $altura_img / 100;
            break;
        }

        // Converter largura
        sscanf($largura, '%d%s', $largura, $unidade);
        switch ($unidade) {
        case '%':
            $largura = $largura * $largura_img / 100;
            break;
        }

        $cor_linha = $this->get_cor($cor_linha);

        for ($x = $largura; $x < $largura_img; $x += $largura) {

            // Criar linha vertical
            imageline($this->img, $x, 0, $x, $altura_img, $cor_linha);

            for ($y = $altura; $y < $altura_img; $y += $altura) {

                // Criar linha horizontal
                imageline($this->img, 0, $y, $largura_img, $y, $cor_linha);
            }
        }
    }

}//class
