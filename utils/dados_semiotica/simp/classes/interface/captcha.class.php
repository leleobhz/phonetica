<?php
//
// SIMP
// Descricao: Classe que gera e valida um campo captcha (texto em imagem)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.13
// Data: 30/01/2008
// Modificado: 21/12/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('CAPTCHA_TAMANHO',       5);    // Numero de caracteres gerados
define('CAPTCHA_ANGULO',        20);   // Angulo maximo que os caracteres podem girar para direita ou esquerda de 90 graus
define('CAPTCHA_LARGURA',       120);  // Largura da imagem em Pixels
define('CAPTCHA_ALTURA',        60);   // Altura da imagem em Pixels
define('CAPTCHA_CARACTERES',    'adhkmnprsxwz346'); // Caracteres utilizados (os omitidos podem confundir humanos)

define('CAPTCHA_FONTE',         $CFG->dirclasses.'interface/fontes/AppleGaramond.ttf');
define('CAPTCHA_LOCALIDADE',    $CFG->localidade);
define('CAPTCHA_GD',            $CFG->gd);
define('CAPTCHA_GMT',           $CFG->gmt);

final class captcha {
    static private $cores = array();


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Gera uma chave para ser checada
    //
    static private function gerar_chave() {
        $s = CAPTCHA_CARACTERES;
        $len = strlen($s);
        $chave = '';
        for ($i = 0; $i < CAPTCHA_TAMANHO; $i++) {
            $chave .= $s[mt_rand(0, $len - 1)];
        }
        return $chave;
    }


    //
    //     Codifica uma chave
    //
    static private function codificar($d) {
    // String $d: valor a ser codificado
    //
        return md5(strtolower($d));
    }


    //
    //     Gera uma imagem de uma chave
    //
    static public function gerar_imagem() {

        // Gerar uma chave
        $chave = self::gerar_chave();

        // Salvar chave criptografada na sessao
        if (!isset($_SESSION)) {
            trigger_error("A sessao nao foi aberta", E_USER_ERROR);
        }
        $_SESSION[__CLASS__]['chave'] = self::codificar($chave);
        $_SESSION[__CLASS__]['time'] = time();

        // Gerar imagem
        $img = imagecreatetruecolor(CAPTCHA_LARGURA, CAPTCHA_ALTURA);

        // Alocar cores
        self::$cores['fundo1'] = imagecolorallocate($img, 255, 255, 150);
        self::$cores['fundo2'] = imagecolorallocate($img, 200, 255, 255);
        self::$cores['fundo3'] = imagecolorallocate($img, 255, 200, 255);
        self::$cores['fundo4'] = imagecolorallocate($img, 200, 200, 200);

        self::$cores['texto1'] = imagecolorallocate($img, 100 + mt_rand(-5, 5), 170 + mt_rand(-5, 5), 170 + mt_rand(-5, 5));
        self::$cores['texto2'] = imagecolorallocate($img, 100 + mt_rand(-5, 5), 110 + mt_rand(-5, 5), 120 + mt_rand(-5, 5));
        self::$cores['texto3'] = imagecolorallocate($img, 100 + mt_rand(-5, 5), 160 + mt_rand(-5, 5), 100 + mt_rand(-5, 5));
        self::$cores['texto4'] = imagecolorallocate($img, 160 + mt_rand(-5, 5), 100 + mt_rand(-5, 5), 100 + mt_rand(-5, 5));
        self::$cores['texto5'] = imagecolorallocate($img, 130 + mt_rand(-5, 5), 130 + mt_rand(-5, 5), 180 + mt_rand(-5, 5));

        // Pintar o fundo e escrever a chave
        imagefill($img, 0, 0, self::$cores['fundo1']);

        for ($x = 0; $x < CAPTCHA_LARGURA; $x += 2) {
            $cor = mt_rand(2, 4);
            for ($y = ($x % 2); $y < CAPTCHA_ALTURA; $y += 2) {
                imagesetpixel($img, $x, $y, self::$cores['fundo'.$cor]);
            }
        }

        self::riscar_linha($img);
        if (function_exists('imagefilter')) {
            imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
        }
        self::escrever($img, $chave);

        // Testar qualidade do captcha
//        imagefilter($img, IMG_FILTER_CONTRAST, -80);

        // Exibir a imagem
        self::header();
        imagejpeg($img, '', 70); // Qualidade ruim para estragar a imagem
        imagedestroy($img);
    }


    //
    //     Risca a imagem aleatoreamente com linhas consecutivas
    //
    static private function riscar_linha(&$img, $curvas = 50) {
    // Resource $img: imagem a ser utilizada
    // Int $curvas: numero de vezes que a linha ganhara curva
    //
        // Rabiscar com linhas da cor do texto
        $x1 = mt_rand(0, CAPTCHA_LARGURA / 5);
        $y1 = mt_rand(0, CAPTCHA_ALTURA);
        $cor = self::$cores['texto'.(mt_rand(1, 5))];
        for ($i = 0; $i < $curvas; $i++) {
            $x2 = $x1 + (CAPTCHA_LARGURA / $curvas);

            $dif = $y2 - $y1;

            // Esta decendo
            if ($dif > 0) {
                $min = max(10, $y1 - 3);
                $max = min(CAPTCHA_ALTURA - 10, $y1 + 5);

            // Esta subindo
            } else {
                $min = max(10, $y1 - 5);
                $max = min(CAPTCHA_ALTURA - 10, $y1 + 3);
            }
            $y2 = mt_rand($min, $max);
            imageline($img, $x1, $y1, $x2, $y2, $cor);
            $x1 = $x2;
            $y1 = $y2;
        }
    }


    //
    //     Escreve a chave na imagem
    //
    static private function escrever(&$img, $chave) {
    // Resource $img: imagem a ser utilizada
    // String $chave: chave a ser escrita
    //
        $altura_min = 15;
        $altura_max = 25;
        $len        = strlen($chave);
        $largura    = CAPTCHA_LARGURA / $len;
        $base       = mt_rand(0, CAPTCHA_ALTURA - (2 * ($altura_max + 2)));

        for ($i = 0; $i < $len; $i++) {
            $altura = mt_rand($altura_min, $altura_max);

            // Calcular uma posicao X e Y
            $x = $i * $largura + mt_rand(2, 6);
            $y = $base + mt_rand(0, $altura);
            $angulo = mt_rand(-1 * CAPTCHA_ANGULO, CAPTCHA_ANGULO);
            $cor = self::$cores['texto'.(mt_rand(1, 5))];
            
            $x_bola = $x + 10 + mt_rand(-5, 5);
            $y_bola = $y + ($altura / 2) + mt_rand(-15, 15);

            imageellipse($img, $x_bola, $y_bola, (400 + mt_rand(-100, 100)) / $altura, (400 + mt_rand(-100, 100)) / $altura, $cor);

            imagettftext($img, $altura, $angulo, $x, $y + $altura, $cor, CAPTCHA_FONTE, $chave[$i]);
        }
    }


    //
    //     Gera o header HTTP da imagem
    //
    static private function header() {
        $t = time();
        setlocale(LC_TIME, 'C');
        header("Content-type: image/jpeg");
        header("Content-Disposition: inline; filename=captcha.jpg");
        header('Date: '.gmstrftime(CAPTCHA_GMT, $t));
        header('Last-Modified: '.gmstrftime(CAPTCHA_GMT, $t));
        header('Expires: '.gmstrftime(CAPTCHA_GMT, $t));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        setlocale(LC_TIME, CAPTCHA_LOCALIDADE);
    }


    //
    //     Valida um valor informado pelo usuario (so' pode ser chamado uma vez)
    //
    static public function validar($chave) {
    // String $chave: chave informada pelo usuario
    //
        if (!isset($_SESSION)) {
            trigger_error("A sessao nao foi aberta", E_USER_ERROR);
        }

        // Checar se foi salvo na sessao
        if (!isset($_SESSION[__CLASS__]['chave'])) {
            return false;
        }

        // Validar o tempo: precisa de pelo menos 3 segundos para responder
        $agora = time();
        if ($agora < ($_SESSION[__CLASS__]['time'] + 3)) {
            return false;
        }

        // Validar o valor da chave
        $valido = strcmp($_SESSION[__CLASS__]['chave'], self::codificar($chave)) == 0;

        // Apagar a sessao
        $_SESSION[__CLASS__]['chave'] = null;
        $_SESSION[__CLASS__]['time'] = null;
        unset($_SESSION[__CLASS__]);

        return $valido;
    }


    //
    //     Gera um capctha "modo texto", com uma pergunta e resposta
    //
    static public function gerar_texto() {
        $metodos = array('gerar_conta', 'gerar_pergunta');

        $metodo = $metodos[mt_rand(0, count($metodos) - 1)];
        $obj = self::$metodo();

        // Salvar chave criptografada na sessao
        if (!isset($_SESSION)) {
            trigger_error("A sessao nao foi aberta", E_USER_ERROR);
        }
        $_SESSION[__CLASS__]['chave'] = self::codificar($obj->resposta);
        $_SESSION[__CLASS__]['time'] = time();

        return $obj;
    }


    //
    //     Gera uma conta
    //
    static public function gerar_conta() {
        $obj = new stdClass();
        $obj->pergunta = '';
        $obj->resposta = '';

        $numeros = array(1 => 'um', 2 => 'dois', 3 => 'tr&ecirc;s',
                         4 => 'quatro', 5 => 'cinco',
                         6 => 'seis', 7 => 'sete', 8 => 'oito', 9 => 'nove');
        $operacoes = array('mais', 'menos');
        $letras = 'abcdefghijklmnopqrstuvwxyz';
        $posicoes = array('in&iacute;cio', 'final');

        $r1 = mt_rand(1, 9);
        $r2 = mt_rand(1, 9);
        $r3 = mt_rand(0, 1);
        $r4 = mt_rand(0, strlen($letras) - 1);
        $r5 = mt_rand(0, 1);

        $n1 = $numeros[$r1];
        $n2 = $numeros[$r2];

        $operacao = $operacoes[$r3];
        $letra = $letras[$r4];
        $posicao = $posicoes[$r5];

        // Gerar uma conta aleatoria e a pergunta
        $vt_quanto = array('Quanto &eacute;', 'Quanto vale', 'Em quanto resulta', 'Qual o valor de', 'Qual o resultado de');
        $quanto = $vt_quanto[mt_rand(0, count($vt_quanto) - 1)];
        $conta = '<span><span>'.$n1.' </span><span>'.$operacao.' '.$n2.'</span></span>';
        $obj->pergunta = "{$quanto} {$conta} com a letra \"{$letra}\" no {$posicao}? (utilize n&uacute;meros)";

        // Gerar a resposta
        switch ($r3) {
        case 0:
            $resultado = (string)($r1 + $r2);
            break;
        case 1:
            $resultado = (string)($r1 - $r2);
            break;
        }
        switch ($r5) {
        case 0:
            $obj->resposta = $letra.$resultado;
            break;
        case 1:
            $obj->resposta = $resultado.$letra;
            break;
        }
        return $obj;
    }


    //
    //     Gera uma pergunta simples
    //
    public static function gerar_pergunta() {
        $obj = new stdClass();
        $obj->pergunta = '';
        $obj->resposta = '';

        $base = array('Qual a cor do c&eacute;u?' => 'azul',
                      'Que dia &eacute; hoje? (Escreva o n&uacute;mero)' => strftime('%d'),
                      'Em que m&ecirc;s estamos? (Escreva o n&uacute;mero)' => strftime('%m'),
                      'Em que ano estamos? (Escreva o n&uacute;mero)' => strftime('%Y'),
                      'Quantos dedos tem em uma m&atilde;o?' => '5',
                      'Qual o nome do nosso planeta?' => 'terra');

        $perguntas = array_keys($base);
        $respostas = array_values($base);

        $numero = mt_rand(0, count($base) - 1);
        $obj->pergunta = $perguntas[$numero];
        $obj->resposta = $respostas[$numero];
        return $obj;
    }

}//class
