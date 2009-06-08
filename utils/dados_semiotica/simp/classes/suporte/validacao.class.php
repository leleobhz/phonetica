<?php
//
// SIMP
// Descricao: Classe singleton que realiza validacoes comuns
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.42
// Data: 09/08/2007
// Modificado: 05/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('VALIDACAO_CHARSET',              strtolower($CFG->charset));
define('VALIDACAO_UTF8',                 $CFG->utf8);
define('VALIDACAO_CHECAR_DOMINIO_EMAIL', true);  // Nao disponivel para Windows

final class validacao {
    private static $instancia = null;


    //
    //     Gera um objeto da classe validacao
    //
    public static function get_instancia() {
        if (!self::$instancia) {
            $classe = __CLASS__;
            self::$instancia = new $classe();
        }
        return self::$instancia;
    }


    //
    //     Construtor privado: use o metodo get_instancia
    //
    private function __construct() {}


    //
    //     Valida um atributo
    //
    public function validar_atributo(&$atributo, $valor, &$erros) {
    // atributo $atributo: descricao do atributo
    // Mixed $valor: valor a ser testado
    // Array[String] $erros: vetor de erros
    //
        $erros = array();

        // Retorno da funcao
        $valido = true;

        // Validar se e' vazio ou nao
        if (!$atributo->pode_vazio) {
            if ($valor === '' || is_null($valor)) {
                $vazio = ($atributo->tipo == 'string') ? 'vazio' : 'nulo';
                $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o pode ser '.$vazio;
                return false;
            }

        // Se pode vazio e esta' vazio, esta' OK
        } elseif ($valor === '' || is_null($valor)) {
            return true;
        }

        // Validar o campo
        $tipo_validacao = $atributo->validacao;
        if ($tipo_validacao) {
            if (!$this->validar_campo($tipo_validacao, $valor, $erro_campo)) {
                $valido = false;
                if (!DEVEL_BLOQUEADO) {
                    $erros[] = '[DEBUG-DEVEL] Erro no campo '.$atributo->classe.':'.$atributo->nome.' = "'.texto::codificar($valor).'" ('.gettype($valor).')';
                }
                $erros[] = 'Campo "'.$atributo->descricao.'" possui caracteres inv&aacute;lidos ou n&atilde;o est&aacute; no padr&atilde;o.'.
                           ($erro_campo ? ' Detalhes: '.$erro_campo : '');
            }
        }

        // Validar o tipo
        $tipos = array('int'    => 'inteiro',
                       'float'  => 'real',
                       'string' => 'texto',
                       'char'   => 'caractere',
                       'bool'   => 'booleano');
        $novo_valor = $valor;

        switch ($atributo->tipo) {
        case 'int':
            if ($valor != round($valor)) {
                $valido = false;
                $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o &eacute; do tipo '.$tipos[$atributo->tipo];
            }
            break;

        case 'char':
            settype($novo_valor, 'string');
            if ($novo_valor == $valor)  {
                $valor = $novo_valor;
            } else {
                $valido = false;
                $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o &eacute; do tipo '.$tipos[$atributo->tipo];
            }
            break;

        case 'float':
        case 'string':
            settype($novo_valor, $atributo->tipo);
            if ($novo_valor == $valor)  {
                $valor = $novo_valor;
            } else {
                $valido = false;
                $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o &eacute; do tipo '.$tipos[$atributo->tipo];
            }
            break;
        case 'data':
            $data = objeto::parse_data($valor, false);
            if ($atributo->pode_vazio && $data['dia'] == 0 && $data['mes'] == 0 && $data['ano'] == 0) {
                $valido = true;
            } elseif (!checkdate($data['mes'], $data['dia'], $data['ano'])) {
                $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o &eacute; uma data v&aacute;lida ('.$data['dia'].'/'.$data['mes'].'/'.$data['ano'].')';
                $valido = false;
            } else {
                if ($data['hora'] < 0 || $data['hora'] > 23) {
                    $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o possui a hora v&aacute;lida: '.$data['hora'];
                }
                if ($data['minuto'] < 0 || $data['minuto'] > 59) {
                    $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o possui o minuto v&aacute;lido: '.$data['hora'];
                }
                if ($data['segundo'] < 0 || $data['segundo'] > 59) {
                    $erros[] = 'Campo "'.$atributo->descricao.'" n&atilde;o possui o segundo v&aacute;lido: '.$data['hora'];
                }
            }
            break;
        }

        // Se nao validou ate aqui
        if (!$valido) {
            return false;
        }

        // Validar o tamanho
        switch ($atributo->tipo) {
        case 'int':
        case 'float':
            if (is_numeric($atributo->minimo) && ($valor < $atributo->minimo)) {
                if ($atributo->minimo >= 0 && $valor < 0) {
                    $erros[] = "Campo \"{$atributo->descricao}\" n&atilde;o pode ser negativo (valor m&iacute;nimo: {$atributo->minimo})";
                } else {
                    $erros[] = "Campo \"{$atributo->descricao}\" n&atilde;o tem o valor m&iacute;nimo ({$atributo->minimo})";
                }
                $valido = false;
            }
            if (is_numeric($atributo->maximo) && ($valor > $atributo->maximo)) {
                if ($atributo->maximo <= 0 && $valor > 0) {
                    $erros[] = "Campo \"{$atributo->descricao}\" n&atilde;o pode ser positivo (valor m&aacute;ximo: {$atributo->maximo})";
                } else {
                    $erros[] = "Campo \"{$atributo->descricao}\" ultrapassa o valor m&aacute;ximo ({$atributo->maximo})";
                }
                $valido = false;
            }
            break;
        case 'string';
            $valor_decode = utf8_decode($valor);
            if (is_numeric($atributo->minimo) && ($atributo->minimo > 0) && (!isset($valor_decode[$atributo->minimo - 1]))) {
                $erros[] = "Campo \"{$atributo->descricao}\" n&atilde;o tem o tamanho m&iacute;nimo de caracteres (m&iacute;nimo: {$atributo->minimo})";
                $valido = false;
            }
            if (is_numeric($atributo->maximo) && (isset($valor_decode[$atributo->maximo]))) {
                $erros[] = "Campo \"{$atributo->descricao}\" ultrapassa o tamanho m&aacute;ximo de caracteres (m&aacute;ximo: {$atributo->maximo})";
                $valido = false;
            }
            break;
        case 'data':
            if ($atributo->minimo && objeto::comparar_datas($valor, $atributo->minimo) < 0) {
                $minimo = objeto::parse_data($atributo->minimo);
                $erros[] = "Campo \"{$atributo->descricao}\" &eacute; anterior &agrave; data ".sprintf('%02d/%02d/%04d', $minimo['dia'], $minimo['mes'], $minimo['ano']);
                $valido = false;
            }
            if ($atributo->maximo && objeto::comparar_datas($valor, $atributo->maximo) > 0) {
                $maximo = objeto::parse_data($atributo->maximo);
                $erros[] = "Campo \"{$atributo->descricao}\" &eacute; posterior &agrave; data ".sprintf('%02d/%02d/%04d', $maximo['dia'], $maximo['mes'], $maximo['ano']);
                $valido = false;
            }
            break;
        }

        return $valido;
    }


    //
    //     Valida um campo (geralmente baseado em expressao regular)
    //     O tipo de campo pode assumir um dos valores:
    //     * ARQUIVO: o arquivo deve existir no servidor
    //     * CNPJ: o numero de CNPJ deve ser valido
    //     * CPF: o numero de CPF deve ser valido
    //     * EMAIL: o e-mail deve ser valido, e o dominio tambem, caso a constante VALIDACAO_CHECAR_DOMINIO_EMAIL seja true
    //     * IP: o IP deve ser valido (4 numeros de 0 a 255 separados por ponto)
    //     * BD: o nome do BD deve ser valido
    //     * CEP: o CEP deve estar no formato XXXXX-XXX
    //     * DN: o DN usado em bases LDAP deve ser valido
    //     * DOMINIO: o dominio deve ser valido (completo ou nao, ou seja, pode comecar em ponto)
    //     * FONE: o telefone deve estar no formato (XX) XXXX-XXXX ou +XX (XX) XXXX-XXXX
    //     * HOST: o host deve ser valido e completo (nao inicia em ponto)
    //     * LETRAS: o campo deve possuir apenas letras (independe da caixa)
    //     * LOGIN: o login deve ser valido (letras, numeros, -, _ e acentos)
    //     * MODULO: o nome do modulo deve ser valido
    //     * NOME: o nome completo da pessoa deve ser valido (caracteres latinos)
    //     * NOME_ARQUIVO: o nome de arquivo deve ser valido (letras, numeros, -, _, . e /)
    //     * NOME_INCOMPLETO: o nome incompleto da pessoa deve ser valido (aceita ponto final para abreviacoes)
    //     * NUMERICO: so aceita numeros
    //     * NUMERICO_PONTO: aceita numeros e ponto
    //     * RG: so aceita letras, numeros e -
    //     * SENHA: so letras, numeros, acentos, simbolos e espaco
    //     * SITE: o site deve ser valido (iniciando em http://)
    //     * TEXTO: o texto deve ser valido (permite-se quebra de linha)
    //     * TEXTO_LINHA: o texto deve ser valido (nao permite-se quebra de linha)
    //
    public function validar_campo($tipo, $valor, &$erro_campo) {
    // String $tipo: tipo de campo
    // String $valor: valor do campo
    // String $erro_campo: string que guarda o erro ocorrido caso consiga identificar
    //
        $erro_campo = false;

        // Tipos especiais
        switch ($tipo) {
        case 'ARQUIVO':
            if (!file_exists($valor)) {
                $erro_campo = 'Arquivo "'.$valor.'" n&atilde;o existe';
                return false;
            }
            return true;
        case 'CNPJ':
            return $this->validar_cnpj($valor, $erro_campo);
        case 'CPF':
            return $this->validar_cpf($valor, $erro_campo);
        case 'EMAIL':
            return $this->validar_email($valor, $erro_campo);
        case 'IP':
            return $this->validar_ip($valor, $erro_campo);
        }

        static $letras_maiusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        static $letras_minusculas = 'abcdefghijklmnopqrstuvwxyz';
        $letras_acentuadas        = self::acentos(true);
        $str_simbolos             = self::simbolos(true);
        $str_quebra_linha         = self::quebra_linha(true);
        $str_espaco               = self::espaco(true);

        static $numeros = '0123456789';
        $acentos        = self::acentos();
        $simbolos       = self::simbolos();
        $quebra_linha   = self::quebra_linha();
        $espaco         = self::espaco();

        $u = VALIDACAO_UTF8 ? 'u' : '';
        switch ($tipo) {
        case 'BD':
            $padrao = '/^[A-z0-9-_]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.'-_';
            break;
        case 'CEP':
            $padrao = '/^[0-9]{5}-[0-9]{3}$/'.$u;
            $permite = $numeros.'-';
            break;
        case 'DN':
            $padrao = '/^([A-z0-0-_'.$acentos.$espaco.',=])+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$letras_acentuadas.$str_espaco.'=-_,';
            break;
        case 'DOMINIO': // completo ou nao
            $padrao = '/^(\.?[A-z0-9-_]+)+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.'-_.';
            break;
        case 'FONE':
            $padrao = '/^(\+[0-9]{2,4}\040)?\([0-9]{2}\) [0-9]{4}-[0-9]{4}$/'.$u;
            $permite = $numeros.'()-+ ';
            break;
        case 'HOST': // completo
            $padrao = '/^([A-z0-9-_]+)|[A-z0-9-_]+(\.[A-z0-9-_]+)+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.'-_.';
            break;
        case 'LETRAS':
            $padrao = '/^[A-z]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas;
            break;
        case 'LOGIN':
            $padrao = '/^[A-z0-9-_\.'.$acentos.']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$letras_acentuadas.$numeros.'-_.';
            break;
        case 'MODULO':
            $padrao = '/^[A-z0-9-_\/]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.'-_/';
            break;
        case 'NOME':
            $padrao = '/^[A-z-'.$acentos.$espaco.'\']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.'-'.$letras_acentuadas.$str_espaco."'";
            break;
        case 'NOME_ARQUIVO':
            $padrao = '/^[A-z0-9-_\.\/]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.'-_./';
            break;
        case 'NOME_INCOMPLETO':
            $padrao = '/^[A-z-'.$acentos.$espaco.'\'\.]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.'-'.$letras_acentuadas.$str_espaco."'.";
            break;
        case 'NUMERICO':
            $padrao = '/^[0-9]+$/'.$u;
            $permite = $numeros;
            break;
        case 'NUMERICO_PONTO':
            $padrao = '/^[0-9\.]+$/'.$u;
            $permite = $numeros.'.';
            break;
        case 'RG':
            $padrao = '/^[A-z0-9-\.'.$espaco.']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.$str_espaco.'-.';
            break;
        case 'SENHA':
            $padrao = '/^[A-z0-9'.$simbolos.$acentos.$espaco.']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$numeros.$str_simbolos.$letras_acentuadas.$numeros.$str_espaco;
            break;
        case 'SITE':
            $padrao = '/^http:\/\/[A-z0-9-_\.\?&~%=\/]+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.':/-_.?&~%=';
            break;
        case 'TEXTO':
            $padrao = '/^[A-z0-9'.$simbolos.$acentos.$quebra_linha.$espaco.']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$letras_acentuadas.$numeros.$str_simbolos.$str_quebra_linha.$str_espaco;
            break;
        case 'TEXTO_LINHA':
            $padrao = '/^[A-z0-9'.$simbolos.$acentos.$espaco.']+$/'.$u;
            $permite = $letras_maiusculas.$letras_minusculas.$letras_acentuadas.$numeros.$str_simbolos.$str_espaco;
            break;
        }

        // Se validou
        if (preg_match($padrao, $valor)) {
            return true;
        }

        // Checando os caracteres que nao podiam ser usados
        if (isset($permite)) {
            $len = texto::strlen($valor);
            $caracteres_invalidos = array();
            $posicoes = array();
            for ($i = 0; $i < $len; $i++) {
                $char = texto::get_char($valor, $i);
                if (strpos($permite, $char) === false) {
                    $caracteres_invalidos[] = htmlspecialchars("\"{$char}\"");
                    $posicoes[] = $i + 1;
                }
            }

            if (count($caracteres_invalidos)) {
                $erro_campo = "os seguintes caracteres n&atilde;o s&atilde;o permitidos para este tipo de campo: ".
                              implode(', ', array_unique($caracteres_invalidos)).'. Est&atilde;o localizados nas seguintes '.
                              'posi&ccedil;&otilde;es: '.implode(', ', $posicoes).'.';
            }
        }
        return false;
    }


    //
    //     Retorna se o CNPJ e' valido
    //
    public function validar_cnpj($cnpj, &$erro_campo = '') {
    // String $cnpj: CNPJ no formato xx.xxx.xxx/xxxx-xx ou apenas numeros
    // String $erro_campo: erro ocorrido na validacao, caso consiga identificar
    //
        // Se informou o CNPJ no formato completo (com pontos e tracos): converter para numeros
        if (preg_match('/^([0-9]{2})\.([0-9]{3})\.([0-9]{3})\/([0-9]{4})-([0-9]{2})$/', $cnpj, $match)) {
            $cnpj = implode(array_slice($match, 1));
        }

        // Se nao informou apenas numeros
        if (!is_numeric($cnpj)) {
            $erro_campo = 'Formato inv&aacute;lido';
            return false;
        }

        // Checar o tamanho
        $tamanho = strlen($cnpj);
        if ($tamanho != 14) {
            $erro_campo = 'Tamanho inv&aacute;lido (esperado tamanho 14)';
            return false;
        }

        // Somatorio das multiplicacoes dos 12 primeiros digitos por 6,7,8,9,2,3,4,5,6,7,8,9
        $soma = ($cnpj[0] * 6) + ($cnpj[1] * 7) + ($cnpj[2] * 8) + ($cnpj[3] * 9) +
                ($cnpj[4] * 2) + ($cnpj[5] * 3) + ($cnpj[6] * 4) + ($cnpj[7] * 5) +
                ($cnpj[8] * 6) + ($cnpj[9] * 7) + ($cnpj[10] * 8) + ($cnpj[11] * 9);

        $dv = $soma % 11;
        if ($dv == 10) { $dv = 0; }
        if ($dv != $cnpj[12]) {
            $erro_campo = 'Primeiro d&iacute;gito verificador n&atilde;o confere';
            return false;
        }

        // Somatorio das multiplicacoes dos 13 primeiros digitos por 5,6,7,8,9,2,3,4,5,6,7,8,9
        $soma = ($cnpj[0] * 5) + ($cnpj[1] * 6) + ($cnpj[2] * 7) + ($cnpj[3] * 8) + ($cnpj[4] * 9) +
                ($cnpj[5] * 2) + ($cnpj[6] * 3) + ($cnpj[7] * 4) + ($cnpj[8] * 5) +
                ($cnpj[9] * 6) + ($cnpj[10] * 7) + ($cnpj[11] * 8) + ($cnpj[12] * 9);

        $dv = $soma % 11;
        if ($dv == 10) { $dv = 0; }
        if ($dv != $cnpj[13]) {
            $erro_campo = 'Segundo d&iacute;gito verificador n&atilde;o confere';
            return false;
        }

        return true;
    }


    //
    //    Retorna se o CPF e' valido
    //
    public function validar_cpf($cpf, &$erro_campo) {
    // String $cpf: CPF apenas numeros
    // String $erro_campo: string que guarda o erro ocorrido caso consiga identificar
    //
        if ((strlen($cpf) != 11) || ($cpf == '00000000000') || ($cpf == '99999999999')) {
            $erro_campo = 'CPF inv&aacute;lido.';
            return false;
        }

        $cpf_aux = substr($cpf, 0, 9);
        $dv = substr($cpf, 9, 2);
        $multiplicador = 10;
        $soma = 0;
        for ($i = 0; $i < 10 && isset($cpf_aux[$i]); $i++, $multiplicador--) {
            $soma += $cpf_aux[$i] * $multiplicador;
        }

        $soma %= 11;
        $dv1 = ($soma == 1 || $soma == 0) ? 0 : (11 - $soma);

        $multiplicador = 11;
        $soma = 0;
        for ($i = 0; $i < 10 && isset($cpf_aux[$i]); $i++, $multiplicador--) {
            $soma += $cpf_aux[$i] * $multiplicador;
        }

        $soma += ($dv1 * 2);
        $soma %= 11;
        $dv2 = ($soma == 1 || $soma == 0) ? 0 : (11 - $soma);

        if ($dv1 != $dv[0] || $dv2 != $dv[1]) {
            $erro_campo = 'Digito verificador incorreto.';
            return false;
        }
        return true;
    }


    //
    //     Valida um IP
    //
    public function validar_ip($ip, &$erro_campo) {
    // String $ip: IP a ser validado
    // String $erro_campo: string que guarda o erro ocorrido caso consiga identificar
    //
        $vetor = explode('.', $ip);
        if (count($vetor) != 4) {
            $erro_campo = 'O IP precisa estar no formato X.X.X.X';
            return false;
        }
        foreach ($vetor as $i) {
            if (!is_numeric($i) || $i < 0 || $i > 255) {
                $erro_campo = "O valor \"{$i}\" deveria estar entre 0 e 255.";
                return false;
            }
        }
        return true;
    }


    //
    //     Valida um e-mail tanto por expressao regular quanto pela validade do dominio
    //
    public function validar_email($email, &$erro_campo) {
    // String $email: e-mail a ser validado
    // String $erro_campo: string que guarda o erro ocorrido caso consiga identificar
    //
        if (isset($_SERVER['SERVER_ADMIN'])) {
            if ($email == $_SERVER['SERVER_ADMIN']) {
                return true;
            }
        } elseif ($email == 'root@localhost') {
            return true;
        }
        $u = VALIDACAO_UTF8 ? 'u' : '';
        $exp = '/^[A-z0-9]+[\.A-z0-9-_]*@(([A-z0-9-_]+)(\.[A-z0-9-_]+)+)$/'.$u;
        if (!preg_match($exp, $email, $match)) {
            $email = texto::codificar($email);
            $erro_campo = "O e-mail n&atilde;o est&aacute; no padr&atilde;o ou possui caracteres inv&aacute;lidos ({$email}).";
            return false;
        }
        if (VALIDACAO_CHECAR_DOMINIO_EMAIL && function_exists('checkdnsrr')) {
            $dominio = substr($email, strpos($email, '@') + 1);
            if (!checkdnsrr($dominio)) {
                $dominio = texto::codificar($match[1]);
                $erro_campo = "O dom&iacute;nio \"{$dominio}\" parece n&atilde;o ser v&aacute;lido.";
                return false;
            }
        }
        return true;
    }


    //
    //     Retorna uma string com letras com acentos para ser usada no preg_match
    //
    public static function acentos($forcar_chr = false) {
    // Bool $forcar_chr: forca o retorno da funcao na forma de chr
    //
        $letras = array(192, 193, 194, 195, 196, 199, 201, 202, 205, 209, 211, 212, 213, 218, 220,
                        224, 225, 226, 227, 228, 231, 233, 234, 237, 241, 243, 244, 245, 250, 252);
        $str = '';
        if (VALIDACAO_UTF8) {
            foreach ($letras as $l) {
                $str .= $forcar_chr ? texto::chr_utf8($l) : '\x{'.dechex($l).'}';
            }
        } else {

            // Ignorar caracteres nao ASCII
            foreach ($letras as $l) {
                if ($l <= 0xFF) {
                    $str .= chr($l);
                }
            }
        }
        return $str;
    }


    //
    //     Retorna uma string com simbolos para ser usados no preg_match
    //
    public static function simbolos($forcar_chr = false) {
    // Bool $forcar_chr: forca o retorno da funcao na forma de chr
    //
        $simbolos = array(
                          /* Caracteres ASCII */
                          33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 58,
                          59, 60, 61, 62, 63, 64, 91, 92, 93, 95, 124, 161, 167, 168, 169,
                          170, 174, 176, 178, 179, 180, 185, 186, 191, 194, 200, 203, 204,
                          206, 207, 210, 214, 215, 216, 217, 219, 221, 229, 232, 235, 236,
                          238, 239, 246, 247, 248, 249,

                          /* Caracteres UTF-8 */
                          8211, 8216, 8217, 8218, 8219, 8220, 8221, 8222, 8223, 8242, 8243,
                          8244, 8245, 8246, 8247, 8364, 8373, 8482);

        $str = '';
        if (VALIDACAO_UTF8) {
            foreach ($simbolos as $s) {
                $str .= $forcar_chr ? texto::chr_utf8($s) : '\x{'.dechex($s).'}';
            }
        } else {

            // Ignorar caracteres nao ASCII
            foreach ($simbolos as $s) {
                if ($s <= 0xFF) {
                    $str .= chr($s);
                }
            }
        }
        return $str;
    }


    //
    //     Retorna uma quebra de linha para ser usada no preg_match
    //
    public static function quebra_linha($forcar_chr = false) {
    // Bool $forcar_chr: forca o retorno da funcao na forma de chr
    //
        if ($forcar_chr || !VALIDACAO_UTF8) {
            return chr(10).chr(13);
        }
        return '\x{a}\x{d}';
    }


    //
    //     Retorna um espaco para ser usado no preg_match
    //
    public static function espaco($forcar_chr = false) {
    // Bool $forcar_chr: forca o retorno da funcao na forma de chr
    //
        if ($forcar_chr || !VALIDACAO_UTF8) {
            return ' ';
        }
        return '\040';
    }

}//class
