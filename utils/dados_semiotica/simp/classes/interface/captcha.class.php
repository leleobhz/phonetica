<?php
//
// SIMP
// Descricao: Classe que gera e valida um campo captcha (texto em imagem)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.10
// Data: 30/01/2008
// Modificado: 19/06/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('CAPTCHA_TAMANHO',       5);    // Numero de caracteres gerados
define('CAPTCHA_LARGURA',       120);  // Largura da imagem em Pixels
define('CAPTCHA_ALTURA',        60);   // Altura da imagem em Pixels
define('CAPTCHA_CARACTERES',    'adhkmnprsxwz346'); // Caracteres utilizados (os omitidos podem confundir)

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

        // Gerar imagem
        $img = imagecreatetruecolor(CAPTCHA_LARGURA, CAPTCHA_ALTURA);

        // Alocar cores
        self::$cores['fundo1'] = imagecolorallocate($img, 255, 255, 150);
        self::$cores['fundo2'] = imagecolorallocate($img, 200, 255, 255);
        self::$cores['fundo3'] = imagecolorallocate($img, 255, 200, 255);
        self::$cores['fundo4'] = imagecolorallocate($img, 200, 200, 200);

        self::$cores['texto1'] = imagecolorallocate($img, 100, 170, 170);
        self::$cores['texto2'] = imagecolorallocate($img, 100, 110, 120);
        self::$cores['texto3'] = imagecolorallocate($img, 100, 160, 100);
        self::$cores['texto4'] = imagecolorallocate($img, 160, 100, 100);
        self::$cores['texto5'] = imagecolorallocate($img, 130, 130, 180);

        // Pintar o fundo e escrever a chave
        imagefill($img, 0, 0, self::$cores['fundo1']);
        self::rabiscar($img);
        self::escrever($img, $chave);
        self::riscar($img, 2);

        // Exibir a imagem
        self::header();
        imagejpeg($img, '', 70); // Qualidade ruim para estragar a imagem
        imagedestroy($img);
    }


    //
    //     Gera alguns rabiscos na imagem para ficar dificil uma maquina entender
    //
    static private function rabiscar(&$img) {
    // Resource $img: imagem a ser utilizada
    //
        // Desenhar retangulos aleatorios
        for ($i = 2; $i < 7; $i++) {
            $x1 = mt_rand(0, CAPTCHA_LARGURA);
            $x2 = mt_rand(0, CAPTCHA_LARGURA);
            $y1 = mt_rand(0, CAPTCHA_ALTURA);
            $y2 = mt_rand(0, CAPTCHA_ALTURA);
            $cor = ($i % 4) + 1;
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, self::$cores['fundo'.$cor]);

            $cx = mt_rand(0, CAPTCHA_LARGURA);
            $cy = mt_rand(0, CAPTCHA_ALTURA);
            $w = mt_rand(10, CAPTCHA_LARGURA / 2);
            $h = mt_rand(10, CAPTCHA_ALTURA / 2);
            $cor2 = (mt_rand(0, 99) % 4) + 1;
            imagefilledellipse($img, $cx, $cy, $w, $h, self::$cores['fundo'.$cor2]);
        }

        self::riscar($img, 10, 2);
    }


    //
    //     Risca a imagem aleatoriamente
    //
    static private function riscar(&$img, $vezes = 10, $largura = 1) {
    // Resource $img: imagem a ser utilizada
    // Int $vezes: numero de vezes que a imagem sera riscada
    // Int $largura: largura da linha
    //
        // Montar estilos aleatorios
        $num_estilos = 10;
        $estilos = array();
        for ($i = 0; $i < $num_estilos; $i++) {
            $pontos = mt_rand(3, 8);
            $estilo = array();
            for ($j = 0; $j < $pontos; $j++) {
                $cor = mt_rand(1, 4);
                $estilo[] = self::$cores['fundo'.$cor];
            }
            $estilos[] = $estilo;
        }

        // Rabiscar com linhas de estilos diferentes
        for ($i = 0; $i < $vezes; $i++) {
            $x1 = mt_rand(0, CAPTCHA_LARGURA / 2);
            $x2 = mt_rand(CAPTCHA_LARGURA / 2, CAPTCHA_LARGURA);
            $y1 = mt_rand(0, CAPTCHA_ALTURA);
            $y2 = mt_rand(0, CAPTCHA_ALTURA);
            imagesetstyle($img, $estilos[mt_rand(0, $num_estilos - 1)]);
            for ($j = $largura; $j > 0; $j--, $y1--, $y2--) {
                imageline($img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
            }
        }
    }


    //
    //     Escreve a chave na imagem
    //
    static private function escrever(&$img, $chave) {
    // Resource $img: imagem a ser utilizada
    // String $chave: chave a ser escrita
    //
        $len     = strlen($chave);
        $largura = CAPTCHA_LARGURA / $len;
        $altura  = 14;
        $meio    = (CAPTCHA_ALTURA / 2) - ($altura / 2);

        for ($i = 0; $i < $len; $i++) {

            // Calcular uma posicao X e Y
            $x = $i * $largura + mt_rand(1, 6);
            $y = $meio + mt_rand(-($altura / 2), ($altura / 2));
            $angulo = mt_rand(-10, 10);
            $cor = self::$cores['texto'.(mt_rand(1, 5))];
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

        // Validar o campo
        $valido = strcmp($_SESSION[__CLASS__]['chave'], self::codificar($chave)) == 0;

        // Apagar a sessao
        $_SESSION[__CLASS__]['chave'] = null;
        unset($_SESSION[__CLASS__]['chave']);

        return $valido;
    }


    //
    //     Gera um capctha "modo texto", com uma pergunta e resposta
    //
    static public function gerar_texto() {
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
        $conta = '<span><span>'.$n1.' </span><span>'.$operacao.' '.$n2.'</span></span>';
        $obj->pergunta = "Quanto &eacute; {$conta} com a letra \"{$letra}\" no {$posicao}?";

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

        // Salvar chave criptografada na sessao
        if (!isset($_SESSION)) {
            trigger_error("A sessao nao foi aberta", E_USER_ERROR);
        }
        $_SESSION[__CLASS__]['chave'] = self::codificar($obj->resposta);
        return $obj;
    }

}//class
