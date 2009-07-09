<?php
//
// SIMP
// Descricao: Classe com funcoes uteis para manipulacao de texto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.19
// Data: 17/09/2007
// Modificado: 22/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('TEXTO_CHARSET', $CFG->charset);
define('TEXTO_UTF8',    $CFG->utf8);
define('TEXTO_WWWIMGS', $CFG->wwwimgs);

// Tipos de Enumeracao
define('TEXTO_ENUMERACAO_LETRA_MAIUSCULA', 1);
define('TEXTO_ENUMERACAO_LETRA_MINUSCULA', 2);

final class texto {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Codifica com base 64 sem caracteres de alinhamento
    //
    static public function base64_encode($valor) {
    // String $valor: valor a ser codificado
    //
        $tr = array('+' => '', '/' => '', '=' => '');
        return strtr(base64_encode($valor), $tr);
    }


    //
    //     Codifica um texto para HTML
    //
    static public function codificar($texto) {
    // String $texto: texto a ser convertido
    //
        $texto = self::decodificar($texto);
        return htmlentities($texto, ENT_COMPAT, TEXTO_CHARSET);
    }


    //
    //     Decodifica um texto HTML para caracteres originais
    //
    static public function decodificar($texto) {
    // String $texto: texto a ser desconvertido
    //
        $tabela = get_html_translation_table(HTML_ENTITIES);
        $tabela = array_flip($tabela);
        if (TEXTO_UTF8) {
            $tabela_aux = array();
            foreach ($tabela as $char => $entity) {
                $tabela_aux[$char] = utf8_encode($entity);
            }
            $tabela = $tabela_aux;
            unset($tabela_aux);
        }
        $texto = strtr($texto, $tabela);
        return $texto;
    }


    //
    //     Imprime um numero
    //
    static public function numero($numero, $casas_decimais = false, $fixo = false) {
    // Int || Float $numero: numero a ser impresso
    // Int || Bool $casas_decimais: numero de casas decimais ou false para um numero variavel
    // Bool $fixo: indica se deve ser usado um numero fixo de casas decimais
    //
        if (is_numeric($numero)) {
            $conv = localeconv();

            // Se especificou um numero de casas decimais
            if ($casas_decimais !== false) {
                $numero = number_format($numero, $casas_decimais, $conv['decimal_point'], $conv['thousands_sep']);

                // Se nao quer um numero fixo de casas decimais
                if (!$fixo) {
                    $vt = explode($conv['decimal_point'], $numero);
                    if (count($vt) == 2) {
                        $p = strlen($vt[1]) - 1;
                        while ($p && $vt[1][$p] == '0') { $p--; }
                        $vt[1] = substr($vt[1], 0, $p + 1);
                        $numero = implode($conv['decimal_point'], $vt);
                    }
                }

            // Se nao especificou um numero de casas decimais
            } else {
                $str = strval($numero);
                $pos_ponto = strpos($str, $conv['decimal_point']);
                if ($pos_ponto !== false) {
                    $casas_decimais = strlen($str) - ($pos_ponto + 1);
                } else {
                    $casas_decimais = 0;
                }
                $numero = number_format($numero, $casas_decimais, $conv['decimal_point'], $conv['thousands_sep']);
            }
        }
        return self::codificar($numero);
    }


    //
    //     Retorna uma substring de uma string codificada em UTF-8 ou ISO-8859-1
    //
    static public function substr($str, $de, $tam) {
    // String $str: string original
    // Int $de: posicao de inicio
    // Int $tam: tamanho da substring
    //
        if (TEXTO_UTF8) {
            return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$de.'}'.
                                '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$tam.'}).*#s',
                                '$1', $str);
        }
        return substr($str, $de, $tam);
    }


    //
    //     Abrevia uma string para um tamanho limite (coloca tres pontos se abreviou)
    //
    static public function abreviar($str, $limite = 30) {
    // String $str: texto a ser abreviado
    // Int $limite: limite de caracteres
    //
        if (self::strlen($str) > $limite) {
            return self::substr($str, 0, $limite).'...';
        }
        return $str;
    }


    //
    //     Retorna o tamanho de uma string codificada em UTF-8 ou ISO-8859-1
    //
    static public function strlen($str) {
    // String $str: string a ser testada
    //
        return strlen(utf8_decode($str));
    }


    //
    //     Retorna um texto em letras maiusculas
    //
    static public function strtoupper($str) {
    // String $str: string original
    //
        $u = TEXTO_UTF8 ? 'u' : '';
        $vetor = array('/â/'.$u => 'Â',
                       '/á/'.$u => 'Á',
                       '/ã/'.$u => 'Ã',
                       '/à/'.$u => 'À',
                       '/é/'.$u => 'É',
                       '/ê/'.$u => 'Ê',
                       '/í/'.$u => 'Í',
                       '/ô/'.$u => 'Ô',
                       '/ó/'.$u => 'Ó',
                       '/õ/'.$u => 'Õ',
                       '/ú/'.$u => 'Ú',
                       '/ü/'.$u => 'Ü',
                       '/ç/'.$u => 'Ç'
                       );

        $str  = self::decodificar($str);
        $str2 = strtoupper($str);
        return preg_replace(array_keys($vetor), array_values($vetor), $str2);
    }


    //
    //     Retorna um texto em letras minusculas
    //
    static public function strtolower($str) {
    // String $str: string original
    //
        $u = TEXTO_UTF8 ? 'u' : '';
        $vetor = array('/Â/'.$u => 'â',
                       '/Á/'.$u => 'á',
                       '/Ã/'.$u => 'ã',
                       '/À/'.$u => 'à',
                       '/É/'.$u => 'é',
                       '/Ê/'.$u => 'ê',
                       '/Í/'.$u => 'í',
                       '/Ô/'.$u => 'ô',
                       '/Ó/'.$u => 'ó',
                       '/Õ/'.$u => 'õ',
                       '/Ú/'.$u => 'ú',
                       '/Ü/'.$u => 'ü',
                       '/Ç/'.$u => 'ç'
                       );

        $str  = self::decodificar($str);
        $str2 = strtolower($str);
        return preg_replace(array_keys($vetor), array_values($vetor), $str2);
    }


    //
    //     Remove a barra invertida de escape em dados escalares e vetores
    //
    static public function strip_slashes_recursivo($dado) {
    // String || Array[Type] $dado: dado a ser convertido
    //
        if (is_scalar($dado)) {
            $dado = stripslashes($dado);
        } elseif (is_array($dado)) {
            foreach ($dado as $chave => $valor) {
                $dado[$chave] = self::strip_slashes_recursivo($valor);
            }
        }
        return $dado;
    }


    //
    //     Retira os acentos do texto
    //
    static public function strip_acentos($texto) {
    // String $texto: texto a ser filtrado
    //
        $u = TEXTO_UTF8 ? 'u' : '';
        $a = array('/Â|Á|Ã|À|Ä/'.$u => 'A',
                   '/â|á|ã|à|ä/'.$u => 'a',
                   '/Ê|É|È|Ë/'.$u   => 'E',
                   '/ê|é|è|ë/'.$u   => 'e',
                   '/Î|Í|Ĩ|Ì|Ï/'.$u => 'I',
                   '/î|í|ĩ|ì|ï/'.$u => 'i',
                   '/Ô|Ó|Õ|Ò|Ö/'.$u => 'O',
                   '/ô|ó|õ|ò|ö/'.$u => 'o',
                   '/Û|Ú|Ũ|Ù|Ü/'.$u => 'U',
                   '/û|ú|ũ|ù|ü/'.$u => 'u',
                   '/Ç/'.$u         => 'C',
                   '/ç/'.$u         => 'c',
                   '/ĺ/'.$u         => 'l',
                   '/ń|ñ/'.$u       => 'n',
                   '/ŕ/'.$u         => 'r',
                   '/ś/'.$u         => 's',
                   '/ý|ÿ/'.$u       => 'y',
                   '/ź/'.$u         => 'z');
        return preg_replace(array_keys($a), array_values($a), $texto);
    }


    //
    //     Remove os espacos adjacentes do texto e retira espacos do inicio e fim
    //
    static public function strip_espacos($str) {
    // String $str: texto a ser filtrado
    //
        $str = trim($str);
        $str2 = '';
        $total = strlen($str);
        $i = 0;
        while ($i < $total) {
            if ($str[$i] == ' ') {
                $str2 .= ' ';
                $i++;
                while ($i < $total && $str[$i] == ' ') {
                    $i++;
                }
            } else {
                $str2 .= $str[$i];
                $i++;
            }
        }
        return $str2;
    }


    //
    //     Converte de numero arabico para romano
    //
    static public function numero_romano($numero, $maiusculo = true) {
    // Int $numero: numero arabico
    // Bool $maiusculo: numero em maiusculo ou minusculo
    //
        if ($numero <= 0 || $numero > 3999) {
            return $numero;
        }

        $n = (int)$numero;
        $y = '';

        // Nivel 1
        while (($n / 1000) >= 1) {
            $y .= 'M';
            $n -= 1000;
        }
        if (($n / 900) >= 1) {
            $y .= 'CM';
            $n -= 900;
        }
        if (($n / 500) >= 1) {
            $y .= 'D';
            $d -= 500;
        }
        if (($n / 400) >= 1) {
            $y .= 'CD';
            $n -= 400;
        }

        // Nivel 2
        while (($n / 100) >= 1) {
            $y .= 'C';
            $n -= 100;
        }
        if (($n / 90) >= 1) {
            $y .= 'XC';
            $n -= 90;
        }
        if (($n / 50) >= 1) {
            $y .= 'L';
            $n -= 50;
        }
        if (($n / 40) >= 1) {
            $y .= 'XL';
            $n -= 40;
        }

        // Nivel 3
        while (($n / 10) >= 1) {
            $y .= 'X';
            $n -= 10;
        }
        if (($n / 9) >= 1) {
            $y .= 'IX';
            $n -= 9;
        }
        if (($n / 5) >= 1) {
            $y .= 'V';
            $n -= 5;
        }
        if (($n / 4) >= 1) {
            $y .= 'IV';
            $n -= 4;
        }

        // Nivel 4
        while ($n >= 1) {
            $y .= 'I';
            $n -= 1;
        }

        if ($maiusculo) {
            return $y;
        }
        return strtolower($y);
    }


    //
    //     Formata um tamanho em Bytes em formato legivel
    //
    static public function formatar_bytes($bytes, $abbr = false) {
    // Int $bytes: valor em bytes
    // Bool $abbr: colocar abreviacao
    //
        $siglas = array('PB' => 'Petabytes',
                        'TB' => 'Terabytes',
                        'GB' => 'Gigabytes',
                        'MB' => 'Megabytes',
                        'KB' => 'Kilobytes',
                        'B' => 'bytes');

        if ($bytes > pow(2, 50)) {
            $u = $abbr ? ' <abbr title="'.$siglas['PB'].'">PB</abbr>' : ' PB';
            return round($bytes / pow(2, 50), 2).$u;
        } elseif ($bytes > pow(2, 40)) {
            $u = $abbr ? ' <abbr title="'.$siglas['TB'].'">TB</abbr>' : ' TB';
            return round($bytes / pow(2, 40), 2).$u;
        } elseif ($bytes > pow(2, 30)) {
            $u = $abbr ? ' <abbr title="'.$siglas['GB'].'">GB</abbr>' : ' GB';
            return round($bytes / pow(2, 30), 2).$u;
        } elseif ($bytes > pow(2, 20)) {
            $u = $abbr ? ' <abbr title="'.$siglas['MB'].'">MB</abbr>' : ' MB';
            return round($bytes / pow(2, 20), 2).$u;
        } elseif ($bytes > pow(2, 10)) {
            $u = $abbr ? ' <abbr title="'.$siglas['KB'].'">KB</abbr>' : ' KB';
            return round($bytes / pow(2, 10), 2).$u;
        }
        $u = $abbr ? ' <abbr title="'.$siglas['B'].'">B</abbr>' : ' B';
        return round($bytes, 2).$u;
    }


    //
    //     Converte de alguma unidade para bytes
    //
    public static function desformatar_bytes($valor) {
    // String $valor: valor com uma unidade
    //
        $i = intval(trim($valor));
        if (strpos($valor, 'T') !== false) {
            return $i * pow(2, 40);
        } elseif (strpos($valor, 'G') !== false) {
            return $i * pow(2, 30);
        } elseif (strpos($valor, 'M') !== false) {
            return $i * pow(2, 20);
        } elseif (strpos($valor, 'K') !== false) {
            return $i * pow(2, 10);
        }
        return $i;
    }


    //
    //     Gera uma numeracao
    //
    public static function enumeracao($numero, $tipo = TEXTO_ENUMERACAO_LETRA_MAIUSCULA) {
    // Int $numero: numero a ser codificado
    // Int $tipo: tipo de enumeracao
    //
        switch ($tipo) {
        case TEXTO_ENUMERACAO_LETRA_MAIUSCULA:
            $enum = 'A';
            while ($numero > 0) {
                $enum++;
                $numero--;
            }
            break;
        case TEXTO_ENUMERACAO_LETRA_MINUSCULA:
            $enum = 'a';
            while ($numero > 0) {
                $enum++;
                $numero--;
            }
            break;
        }
        return $enum;
    }


    //
    //     Converte um numero de uma base decimal para uma base especial (que utiliza os digitos especificados)
    //
    public static function base_encode($numero, $digitos) {
    // Int $numero: numero positivo na base decimal
    // String || Array[Char] $digitos: conjunto de digitos usados na base especial (ex. binario usa '01')
    //
        if ($numero < 0) {
            trigger_error('Nao pode valores negativos', E_USER_WARNING);
            return false;
        } elseif ($numero > PHP_INT_MAX) {
            trigger_error('Valor muito elevado', E_USER_WARNING);
            return false;
        }
        if (is_array($digitos)) {
            $len = count($digitos);
        } else {
            $len = strlen($digitos);
        }
        $retorno = '';
        do {
            $divisao = floor($numero / $len);
            $resto = $numero % $len;
            $retorno = $digitos[$resto].$retorno;
            $numero = $divisao;
        } while ($divisao);

        return $retorno;
    }


    //
    //     Desconverte um numero de uma base especial para uma base decimal
    //
    public static function base_decode($numero, $digitos) {
    // String $numero: numero em uma base especial
    // String || Array[Char] $digitos: digitos usados na base especial
    //
        if (is_array($digitos)) {
            $len = count($digitos);
        } else {
            $len = strlen($digitos);
        }
        $retorno = 0;
        $multiplicador = 1;
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            if (is_array($digitos)) {
                $digito = $digitos[array_search($numero[$i], $digitos)];
            } else {
                $digito = strpos($digitos, $numero[$i]);
            }
            $retorno += $digito * $multiplicador;
            $multiplicador *= $len;
        }
        return $retorno;
    }


    //
    //     Interpreta uma string como um texto em CSV, retorna uma matriz de dados
    //
    public static function get_csv($str, $separador = ',', $delimitador = '"') {
    // String $str: string de entrada no formato CSV
    // Char $separador: caracter usado como separador de dados
    // Char $delimitador: caracter usado como delimitador de dados
    //
        do {
            $md5_separador = md5($separador.microtime(), true);
        } while (strpos($md5_separador, $str) === true);

        do {
            $md5_separador_linha = md5(time(), true);
        } while (strpos($md5_separador_linha, $str) === true && $md5_separador_linha == $md5_separador);

        $buf = '';
        $len = strlen($str);
        $aberto = false;

        for ($i = 0; $i < $len; $i++) {
            $c = $str[$i];
            switch ($c) {
            case $separador:
                $buf .= $aberto ? $c : $md5_separador;
                break;
            case $delimitador:
                if ($str[$i + 1] == $delimitador) {
                    $buf .= $c;
                    $i++;
                } else {
                    $aberto = !$aberto;
                }
                break;
            case "\n":
                $buf .= $aberto ? $c : $md5_separador_linha;
                break;
            default:
                $buf .= $c;
                break;
            }
        }

        // Quebrando em linhas
        $linhas = explode($md5_separador_linha, $buf);

        // Para cada linha, quebrar em dados
        $retorno = array();
        foreach ($linhas as $i => $linha) {
            if (trim($linha)) {
                $retorno[] = explode($md5_separador, $linha);
            }
        }
        return $retorno;
    }


    //
    //     Retorna o codigo de um caracter em UTF-8
    //
    static public function ord_utf8($c) {
    // String $c: caractere UTF-8 (de 1 a 4 bytes)
    //
        // Caracteres UTF-8 tem entre 8 e 32 bits conforme tabela, sendo de 7 a 21 significativos

        // http://tools.ietf.org/html/rfc3629
        //      Intervalo      |        Sequencia de octetos
        //    (hexadecimal)    |              (binario)
        // --------------------+--------------------------------------
        // 00000000 - 0000007F | 0xxxxxxx
        // 00000080 - 000007FF | 110xxxxx 10xxxxxx
        // 00000800 - 0000FFFF | 1110xxxx 10xxxxxx 10xxxxxx
        // 00010000 - 0010FFFF | 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx

        $tam_real = strlen($c);

        /// 1 - Obter o representante decimal de cada byte do caractere recebido
        $vt_ord = array();
        for ($i = $tam_real - 1; $i >= 0; $i--) {
            $vt_ord[$i] = ord($c[$i]);
        }

        /// 2 - Checar se o caracter e' um ASCII padrao (1 byte): tem 7 bits significativos
        if ($vt_ord[0] <= 0x7F) { // byte1 <= 01111111
            return $vt_ord[0];
        }

        /// 3 - Validar caracter e obter os bits necessarios

        // Se espera 2 bytes: tem 8 a 11 bits significativos
        if ($vt_ord[0] <= 0xDF) {                  // byte1 <= 11011111
            if ($tam_real == 2 &&                  // tem 2 bytes
                (($vt_ord[1] & 0xC0) == 0x80)) {   // byte2 & 11000000 == 10000000 (byte2 == 10xxxxxx)

                return ($vt_ord[1] & 0x3F) |       // byte2 & 00111111 (6 bits)
                       (($vt_ord[0] & 0x1F) << 6); // byte1 & 00011111 (+ 5 bits)
            }

        // Se espera 3 bytes: tem 12 a 16 bits significativos
        } elseif ($vt_ord[0] <= 0xEF) {            // byte1 <= 11101111
            if ($tam_real == 3 &&                  // tem 3 bytes
                (($vt_ord[1] & 0xC0) == 0x80) &&   // byte2 & 11000000 == 10000000 (byte2 == 10xxxxxx)
                (($vt_ord[2] & 0xC0) == 0x80)) {   // byte3 & 11000000 == 10000000 (byte3 == 10xxxxxx)

                return ($vt_ord[2] & 0x3F) |        // byte3 & 00111111 (6 bits)
                       (($vt_ord[1] & 0x3F) << 6) | // byte2 & 00111111 (+ 6 bits)
                       (($vt_ord[0] & 0x1F) << 12); // byte1 & 00011111 (+ 5 bits)
            }

        // Se espera 4 bytes: tem 17 a 21 bits significativos
        } elseif ($vt_ord[0] <= 0xF4) { // byte1 <= 11110111
            if ($tam_real == 4 &&       // tem 4 bytes
                (($vt_ord[1] & 0xC0) == 0x80) &&   // byte2 & 11000000 == 10000000 (byte2 == 10xxxxxx)
                (($vt_ord[2] & 0xC0) == 0x80) &&   // byte3 & 11000000 == 10000000 (byte3 == 10xxxxxx)
                (($vt_ord[3] & 0xC0) == 0x80)) {   // byte4 & 11000000 == 10000000 (byte4 == 10xxxxxx)

                return ($vt_ord[3] & 0x3F) |         // byte4 & 00111111 (6 bits)
                       (($vt_ord[2] & 0x3F) << 6) |  // byte3 & 00111111 (+ 6 bits)
                       (($vt_ord[1] & 0x3F) << 12) | // byte2 & 00111111 (+ 6 bits)
                       (($vt_ord[0] & 0x1F) << 18);  // byte1 & 00011111 (+ 5 bits)
            }
        }

        // Se o UTF-8 informado e' invalido
        $vt_binario = array();
        for ($i = 0; $i < $tam_real; $i++) {
            $vt_binario[] = sprintf('%08d', decbin($vt_ord[$i]));
        }
        $binario = implode(' ', $vt_binario);

        trigger_error('Caracter UTF-8 invalido: '.$binario, E_USER_NOTICE);
        return false;
    }


    //
    //     Gera um caractere UTF-8 a partir do seu codigo (7 a 21 bits)
    //
    static public function chr_utf8($ord) {
    // Int $ord: codigo do caractere
    //
        // Tem 1 byte (7 bits significativos)
        if ($ord <= 0x7F) {
            return chr($ord);

        // Tem 2 bytes (11 bits significativos = 5 + 6)
        } elseif ($ord <= 0x7FF) {
            return chr((($ord >> 6) & 0x1F) | 0xC0).   // ((ord >> 6) & 00011111) | 11000000
                   chr((   $ord     & 0x3F) | 0x80);   // (   ord     & 00111111) | 10000000

        // Tem 3 bytes (16 bits significativos = 4 + 6 + 6)
        } elseif ($ord <= 0xFFFF) {
            return chr((($ord >> 12) & 0xF)  | 0xE0).  // ((ord >> 12) & 00001111) | 11100000
                   chr(( ($ord >> 6) & 0x3F) | 0x80).  // ( (ord >> 6) & 00111111) | 10000000
                   chr((    $ord     & 0x3F) | 0x80);  // (    ord     & 00111111) | 10000000

        // Tem 4 bytes (21 bits significativos = 3 + 6 + 6 + 6)
        } elseif ($ord <= 0x10FFFF) {
            return chr((($ord >> 18) & 0x7)  | 0xF0).  // ((ord >> 18) & 00000111) | 11110000
                   chr((($ord >> 12) & 0x3F) | 0x80).  // ((ord >> 12) & 00111111) | 10000000
                   chr((($ord >> 6)  & 0x3F) | 0x80).  // ( (ord >> 6) & 00111111) | 10000000
                   chr((    $ord     & 0x3F) | 0x80);  // (    ord     & 00111111) | 10000000
        }
        trigger_error('O codigo '.$ord.' nao pode ser representado em UTF-8', E_USER_NOTICE);
        return false;
    }


    //
    //     Retorna o tamanho esperado de um caracter UTF-8
    //
    static public function tamanho_utf8($c) {
    // String $c: caractere UTF-8 (de 1 a 4 bytes)
    //
        $ord = ord($c[0]);

        if ($ord <= 0x7F) {       // byte <= 01111111
            return 1;
        } elseif ($ord <= 0xDF) { // byte <= 11011111
            return 2;
        } elseif ($ord <= 0xEF) { // byte <= 11101111
            return 3;
        } elseif ($ord <= 0xF4) { // byte <= 11110111
            return 4;
        }

        trigger_error('O caractere informado nao representa um UTF-8', E_USER_NOTICE);
        return false;
    }


    //
    //     Retorna um caracter UTF-8 de uma posicao da string UTF-8
    //
    static public function get_char($str, $pos) {
    // String $str: string codificada em UTF-8
    // Int $pos: posicao do caracter desejado
    //
        $len = strlen($str);
        $tam_caractere = 0;

        $caractere = 0;
        for ($i = 0; $i < $len; $i += $tam_caractere, $caractere++) {

            // Checar o tamanho do caractere UTF-8
            $tam_caractere = self::tamanho_utf8($str[$i]);

            if ($caractere == $pos) {
                return substr($str, $i, $tam_caractere);
            }
        }
        trigger_error('Nao existe a posicao '.$pos.' na string "'.$str.'"', E_USER_NOTICE);
        return false;
    }


    //
    //     Checa se o texto e' UTF8
    //
    static public function is_utf8($texto) {
    // String $texto: texto a ser analisado
    //
        return (bool)preg_match ('/.+/u', $texto);
    }


    //
    //     Exibe um e-mail de forma protegida contra robos de spam
    //
    static public function proteger_email($email) {
    // String $email: e-mail a ser protegido
    //
        $pos = strpos($email, '@');
        if ($pos === false) {
            return $email;
        }
        $img = icone::img('arroba', '[em]');

        $login = substr($email, 0, $pos);
        $dominio = substr($email, $pos + 1);
        $protegido = self::codificar($login).$img.self::codificar($dominio);
        return $protegido;
    }


    //
    //     Mostra o valor real na forma de moeda (de acordo com a localidade definida em setlocale)
    //
    static public function money_format($valor, $formato = '%n') {
    // Float $valor: valor real
    // String $formato: formato especificado pela funcao money_format
    //
        if (function_exists('money_format')) {
            return money_format($formato, $valor);
        }

        $locale = localeconv();

        // Extraindo opcoes do formato
        $regex = '/^'.             // Inicio da Expressao
                 '%'.              // Caractere %
                 '(?:'.            // Inicio das Flags opcionais
                 '\=([\w\040])'.   // Flag =f
                 '|'.
                 '([\^])'.         // Flag ^
                 '|'.
                 '(\+|\()'.        // Flag + ou (
                 '|'.
                 '(!)'.            // Flag !
                 '|'.
                 '(-)'.            // Flag -
                 ')*'.             // Fim das flags opcionais
                 '(?:([\d]+)?)'.   // W  Largura de campos
                 '(?:#([\d]+))?'.  // #n Precisao esquerda
                 '(?:\.([\d]+))?'. // .p Precisao direita
                 '([in%])'.        // Caractere de conversao
                 '$/';             // Fim da Expressao

        if (!preg_match($regex, $formato, $match)) {
            trigger_error('Formato invalido', E_USER_WARNING);
            return $valor;
        }

        // Recolhendo opcoes do formato
        $opcoes = array(
                       'preenchimento'   => ($match[1] !== '') ? $match[1] : ' ',
                       'nao_agrupar'     => ($match[2] == '^'),
                       'usar_sinal'      => ($match[3] == '+'),
                       'usar_parenteses' => ($match[3] == '('),
                       'ignorar_simbolo' => ($match[4] == '!'),
                       'alinhamento_esq' => ($match[5] == '-'),
                       'largura_campo'   => ($match[6] !== '') ? (int)$match[6] : 0,
                       'precisao_esq'    => ($match[7] !== '') ? (int)$match[7] : false,
                       'precisao_dir'    => ($match[8] !== '') ? (int)$match[8] : $locale['int_frac_digits'],
                       'conversao'       => $match[9]
                       );

//util::dump($opcoes);

        // Sobrescrever $locale
        if ($opcoes['usar_sinal'] && $locale['n_sign_posn'] == 0) {
            $locale['n_sign_posn'] = 1; // TODO: por que nao 2, 3, ou 4?
        } elseif ($opcoes['usar_parenteses']) {
            $locale['n_sign_posn'] = 0;
        }
        if ($opcoes['precisao_dir']) {
            $locale['frac_digits'] = $opcoes['precisao_dir'];
        }
        if ($opcoes['nao_agrupar']) {
            $locale['mon_thousands_sep'] = '';
        }

        // Processar formatacao
        $tipo_sinal = $valor >= 0 ? 'p' : 'n';
        if ($opcoes['ignorar_simbolo']) {
            $simbolo = '';
        } else {
            $simbolo = $opcoes['conversao'] == 'n' ? $locale['currency_symbol']
                                                   : $locale['int_curr_symbol'];
        }
        $numero = number_format(abs($valor), $locale['frac_digits'], $locale['mon_decimal_point'], $locale['mon_thousands_sep']);
        list($inteiro, $fracao) = explode($locale['mon_decimal_point'], $numero);

/*
//TODO: dar suporte a todas as flags
        $tam_inteiro = strlen($inteiro);
        if ($opcoes['precisao_esq'] && $tam_inteiro < $opcoes['precisao_esq']) {
            $alinhamento = $opcoes['alinhamento_esq'] ? STR_PAD_RIGHT : STR_PAD_LEFT;
            $numero = str_pad($inteiro, $opcoes['precisao_esq'] - $tam_inteiro, $opcoes['preenchimento'], $alinhamento).
                      $locale['mon_decimal_point'].
                      $fracao;
        }
*/

        $sinal = $valor >= 0 ? $locale['positive_sign'] : $locale['negative_sign'];
        $simbolo_antes = $locale[$tipo_sinal.'_cs_precedes'];

        // Espaco entre o simbolo e o numero
        $espaco1 = $locale[$tipo_sinal.'_sep_by_space'] == 1 ? ' ' : '';

        // Espaco entre o simbolo e o sinal
        $espaco2 = $locale[$tipo_sinal.'_sep_by_space'] == 2 ? ' ' : '';

        $formatado = '';
        switch ($locale[$tipo_sinal.'_sign_posn']) {
        case 0:
            if ($simbolo_antes) {
                $formatado = '('.$simbolo.$espaco1.$numero.')';
            } else {
                $formatado = '('.$numero.$espaco1.$simbolo.')';
            }
            break;
        case 1:
            if ($simbolo_antes) {
                $formatado = $sinal.$espaco2.$simbolo.$espaco1.$numero;
            } else {
                $formatado = $sinal.$numero.$espaco1.$simbolo;
            }
            break;
        case 2:
            if ($simbolo_antes) {
                $formatado = $simbolo.$espaco1.$numero.$sinal;
            } else {
                $formatado = $numero.$espaco1.$simbolo.$espaco2.$sinal;
            }
            break;
        case 3:
            if ($simbolo_antes) {
                $formatado = $sinal.$espaco2.$simbolo.$espaco1.$numero;
            } else {
                $formatado = $numero.$espaco1.$sinal.$espaco2.$simbolo;
            }
            break;
        case 4:
            if ($simbolo_antes) {
                $formatado = $simbolo.$espaco2.$sinal.$espaco1.$numero;
            } else {
                $formatado = $numero.$espaco1.$simbolo.$espaco2.$sinal;
            }
            break;
        }

        // Se a string nao tem o tamanho minimo
        if ($opcoes['largura_campo'] > 0 && strlen($formatado) < $opcoes['largura_campo']) {
            $alinhamento = $opcoes['alinhamento_esq'] ? STR_PAD_RIGHT : STR_PAD_LEFT;
            $formatado = str_pad($formatado, $opcoes['largura_campo'], $opcoes['preenchimento'], $alinhamento);
        }

        return $formatado;
    }


    //
    //     Retorna um vetor associativo com as letras minusculas do alfabeto e os simbolos correspondentes em baille (UTF-8)
    //
    public static function get_letras_braille() {
        return array(
            'a' => '⠁',
            'b' => '⠃',
            'c' => '⠉',
            'd' => '⠙',
            'e' => '⠑',
            'f' => '⠋',
            'g' => '⠛',
            'h' => '⠓',
            'i' => '⠊',
            'j' => '⠚',
            'k' => '⠅',
            'l' => '⠇',
            'm' => '⠍',
            'n' => '⠝',
            'o' => '⠕',
            'p' => '⠏',
            'q' => '⠟',
            'r' => '⠗',
            's' => '⠎',
            't' => '⠞',
            'u' => '⠥',
            'v' => '⠧',
            'x' => '⠭',
            'y' => '⠽',
            'z' => '⠵',
            'ç' => '⠯',
            'é' => '⠿',
            'á' => '⠷',
            'è' => '⠮',
            'ú' => '⠾',
            'â' => '⠡',
            'ê' => '⠣',
            'ì' => '⠩',
            'ô' => '⠹',
            'ù' => '⠱',
            'à' => '⠫',
            'ï' => '⠻',
            'ü' => '⠳',
            'ö' => '⠪',
            'w' => '⠺',
            'í' => '⠌',
            'ã' => '⠜',
            'ó' => '⠬'
        );
    }


    //
    //     Retorna um vetor associativo com os numeros de 1 a 9 e o 0 e os simbolos correspondentes em baille (UTF-8)
    //
    public static function get_numeros_braille() {
        return array(
            '1' => '⠁',
            '2' => '⠃',
            '3' => '⠉',
            '4' => '⠙',
            '5' => '⠑',
            '6' => '⠋',
            '7' => '⠛',
            '8' => '⠓',
            '9' => '⠊',
            '0' => '⠚'
        );
    }


    //
    //     Retorna um vetor associativo com símbolos e os valores correspondentes em baille (UTF-8)
    //
    public static function get_simbolos_braille() {
        return array(
            ',' => '⠂',
            '.' => '⠄',
            ';' => '⠆',
            ':' => '⠒',
            '!' => '⠖',
            '?' => '⠢',
            '-' => '⠤',
            '"' => '⠦',
            "'" => '⠦',
            '(' => '⠣⠄',
            ')' => '⠠⠜',
            '*' => '⠔',
            '$' => '⠰'
        );
    }


    //
    //     Retorna o simbolo de maiuscula em braille (UTF-8)
    //
    public static function get_maiuscula_braille() {
        return '⠨';
    }


    //
    //     Codifica um texto para braille
    //
    public static function braille_encode($texto) {
    // String $texto: texto a ser codificado
    //
        $letras    = self::get_letras_braille();
        $numeros   = self::get_numeros_braille();
        $simbolos  = self::get_simbolos_braille();
        $maiuscula = self::get_maiuscula_braille();

        // Incluir espacos entre simbolos
        $tr = array();
        foreach (array_keys($simbolos) as $simbolo) {
            $tr[$simbolo] = ' '.$simbolo.' ';
        }
        $texto = strtr($texto, $tr);
        unset($tr);

        $vt_braille = array();
        $palavras = explode(' ', $texto);
        foreach ($palavras as $palavra) {
            if (!trim($palavra)) { continue; }
            $primeira = self::get_char($palavra, 0);

            // Se e' um simbolo
            if (isset($simbolos[$primeira])) {
                $braille = self::braille_encode_palavra($palavra, $letras, $numeros, $simbolos);
                $vt_braille[] = $braille;

            // Se a palavra e' inteira maiuscula
            } elseif (strcmp($palavra, self::strtoupper($palavra)) == 0) {
                $palavra = self::strtolower($palavra);
                $braille = $maiuscula.
                           $maiuscula.
                           self::braille_encode_palavra($palavra, $letras, $numeros, $simbolos);
                $vt_braille[] = $braille;

            // Se a primeira letra e' maiuscula
            } elseif (strcmp($primeira, self::strtoupper($primeira)) == 0) {
                $palavra = self::strtolower($palavra);
                $braille = $maiuscula.
                           self::braille_encode_palavra($palavra, $letras, $numeros, $simbolos);
                $vt_braille[] = $braille;

            // Demais casos
            } else {
                $palavra = self::strtolower($palavra);
                $braille = self::braille_encode_palavra($palavra, $letras, $numeros, $simbolos);
                $vt_braille[] = $braille;
            }
        }
        return implode(' ', $vt_braille);
    }


    //
    //     Codifica uma palavra em braille
    //
    private static function braille_encode_palavra($palavra, $letras, $numeros, $simbolos) {
    // String $palavra: palavra a ser codificada
    // Array[String => String] $letras: vetor de letras em braille
    // Array[String => String] $numeros: vetor de numeros em braille
    // Array[String => String] $simbolos: vetor de simbolos em braille
    //
        $braille = '';
        $len = self::strlen($palavra);
        for ($i = 0; $i < $len; $i++) {
            $char = self::get_char($palavra, $i);
            if (isset($letras[$char])) {
                $braille .= $letras[$char];
            } elseif (isset($numeros[$char])) {
                $braille .= $numeros[$char];
            } elseif (isset($simbolos[$char])) {
                $braille .= $simbolos[$char];
            } elseif ($i && self::get_char($palavra, $i - 1) != ' ') {
                $braille .= ' ';
            }
        }
        return $braille;
    }

}//class
