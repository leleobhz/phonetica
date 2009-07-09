<?php
//
// SIMP
// Descricao: Parser da estrutura do Simp (reflexao)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 19/05/2008
// Modificado: 24/06/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class parser_simp {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Obtem os dados das funcoes de um arquivo (nome, flags, descricao, parametros)
    //
    public static function get_funcoes_arquivo($arquivo) {
    // String $arquivo: arquivo a ser analisado
    //
        if (!is_file($arquivo)) {
            trigger_error("Arquivo invalido \"{$arquivo}\"", E_USER_WARNING);
            return false;
        }
        $tokens = token_get_all(file_get_contents($arquivo));
        $ignore_doc = (isset($tokens[1]) && substr($tokens[1][1], 0, 12) == '//@ignoredoc');
        $funcoes = array();
        foreach ($tokens as $i => $token) {
            if ($token[0] == T_FUNCTION) {
                $dados = self::get_dados_funcao($tokens, $i, $ignore_doc);
                $funcoes[] = $dados;
            }
        }
        return $funcoes;
    }


    //
    //     Obtem os dados de uma funcao de um arquivo ou false se nao encontrar
    //
    public static function get_funcao_arquivo($funcao, $arquivo) {
    // String $funcao: nome da funcao
    // String $arquivo: nome do arquivo
    //
        if (!is_file($arquivo)) {
            trigger_error("Arquivo invalido \"{$arquivo}\"", E_USER_WARNING);
            return false;
        }
        $tokens = token_get_all(file_get_contents($arquivo));
        $ignore_doc = (isset($tokens[1]) && substr($tokens[1][1], 0, 12) == '//@ignoredoc');
        $final = count($tokens);
        for ($i = 0; $i < $final; $i++) {
            if ($tokens[$i][0] == T_FUNCTION) {

                // Obter nome
                if ($tokens[$i + 2][0] == '&') {
                    $nome = $tokens[$i + 3][1];
                } else {
                    $nome = $tokens[$i + 2][1];
                }

                if ($nome == $funcao) {
                    return self::get_dados_funcao($tokens, $i, $ignore_doc);
                }
            }
        }
        return false;
    }


    //
    //     Obtem os dados de uma funcao a partir dos tokens do arquivo
    //
    private static function get_dados_funcao($tokens, $i, $ignore_doc = false) {
    // Array[Mixed] $tokens: vetor de tokens
    // Int $i: posicao onde foi encontrada a funcao
    // Bool $ignore_doc: indica se erros com a documentacao devem ser ignorados
    //
        $obj = new stdClass();
        $obj->linha = 0;
        $obj->nome = '';
        $obj->flags = '';
        $obj->descricao = '';
        $obj->parametros = array();

        $total = count($tokens);

        // Obter linha da funcao
        $obj->linha = $tokens[$i][2];

        // Obter nome
        if ($tokens[$i + 2][0] == '&') {
            $obj->nome = '&amp;'.$tokens[$i + 3][1];
        } else {
            $obj->nome = $tokens[$i + 2][1];
        }

        // Obter flags
        $linha = $obj->linha;
        for ($j = $i - 1; $tokens[$j][0] != T_COMMENT && $linha == $obj->linha && $j >= 0; $j--) {
            if (isset($tokens[$j][1])) {
                $linha = $tokens[$j][2];
                if ($linha == $obj->linha) {
                    $obj->flags = $tokens[$j][1].$obj->flags;
                }
            } else {
                $obj->flags = $tokens[$j][0].$obj->flags;
            }
        }
        $obj->flags = trim($obj->flags);
        $j--;

        // Obter descricao
        do {
            if (isset($tokens[$j][1])) {
                $obj->descricao = substr(trim($tokens[$j][1]), 2).$obj->descricao;
            } else {
                $obj->descricao = $tokens[$j][0].$obj->descricao;
            }
            $j--;
        } while ($j >= 0 && ($tokens[$j][0] == T_COMMENT || $tokens[$j][0] == T_WHITESPACE) && (trim($tokens[$j][1]) != '//'));
        if (!empty($obj->descricao)) {
            $obj->descricao = texto::codificar(trim($obj->descricao));
        } else {
            if (!$ignore_doc) {
                $obj->erro = 'Fun&ccedil;&atilde;o sem descri&ccedil;&atilde;o';
            }
            return $obj;
        }

        $j = $i + 1;
        while ($tokens[$j][0] != '(') { $j++; }
        $j++;

        // Obter parametros
        $aux = '';
        $nivel = 0;
        while (($tokens[$j][0] != ')' || $nivel != 0) && $j < $total) {
            if ($tokens[$j][0] == ',') {
                $obj_param = new stdClass();
                $obj_param->tipo = '';
                $obj_param->descricao = '';
                $obj_param->referencia = false;
                $pos = strpos($aux, '=');
                if ($pos !== false) {
                    $obj_param->nome = trim(substr($aux, 0, $pos));
                    $obj_param->padrao = trim(substr($aux, $pos + 1));
                } else {
                    $obj_param->nome = trim($aux);
                }
                if ($obj_param->nome[0] == '&') {
                    $obj_param->nome = substr($obj_param->nome, 1);
                    $obj_param->referencia = true;
                }
                $obj_param->nome = texto::codificar($obj_param->nome);
                $obj->parametros[$obj_param->nome] = $obj_param;
                $aux = '';
            } else {
                if ($tokens[$j][0] == '(') {
                    $nivel++;
                } elseif ($tokens[$j][0] == ')') {
                    $nivel--;
                }
                $aux .= isset($tokens[$j][1]) ? $tokens[$j][1] : $tokens[$j][0];
            }
            $j++;
        }
        if (!empty($aux)) {
            $obj_param = new stdClass();
            $obj_param->tipo = '';
            $obj_param->descricao = '';
            $pos = strpos($aux, '=');
            if ($pos !== false) {
                $obj_param->nome = trim(substr($aux, 0, $pos));
                $obj_param->padrao = trim(substr($aux, $pos + 1));
            } else {
                $obj_param->nome = trim($aux);
            }
            if ($obj_param->nome[0] == '&') {
                $obj_param->nome = substr($obj_param->nome, 1);
                $obj_param->referencia = true;
            }
            $obj_param->nome = texto::codificar($obj_param->nome);
            $obj->parametros[$obj_param->nome] = $obj_param;
        }
        if (count($obj->parametros)) {
            $j++;
            while (($tokens[$j][0] == '{' || $tokens[$j][0] == ';' || $tokens[$j][0] == T_WHITESPACE) && $j < $total) { $j++; }
            do {
                switch ($tokens[$j][0]) {
                case T_COMMENT:
                    $comentario = substr(trim($tokens[$j][1]), 3);
                    $pos = strpos($comentario, ':');
                    if ($pos === false) {
                        if (!$ignore_doc) {
                            $obj->erro = 'Faltou o dois-pontos no par&acirc;metro: "'.$comentario.'"';
                        }
                        return $obj;
                    }
                    $campo = substr($comentario, 0, $pos);
                    $descricao = trim(substr($comentario, $pos + 1));
                    $pos = strrpos($campo, ' ');
                    if ($pos === false) {
                        if (!$ignore_doc) {
                            $obj->erro = 'Faltou espa&ccedil;o separando o tipo do nome no par&acirc;metro: "'.$comentario.'"';
                        }
                        return $obj;
                    }
                    $tipo = trim(substr($campo, 0, $pos));
                    $parametro = trim(substr($campo, $pos + 1));
                    if (!isset($obj->parametros[$parametro])) {
                        if (!$ignore_doc) {
                            $obj->erro = 'N&atilde;o existe o par&acirc;metro "'.$parametro.'" na fun&ccedil;&atilde;o';
                        }
                        return $obj;
                    }
                    $obj->parametros[$parametro]->tipo = $tipo;
                    if (!$ignore_doc && !self::validar_tipo($tipo)) {
                        $obj->erro = "Tipo desconhecido \"{$tipo}\" para o par&acirc;metro {$parametro}";
                        return $obj;
                    }

                    $obj->parametros[$parametro]->descricao = $descricao;
                    if (!$ignore_doc && empty($descricao)) {
                        $obj->erro = "O par&acirc;metro {$p->nome} n&atilde;o tem descri&ccedil;&atilde;o";
                        return $obj;
                    }
                    break;
                case T_WHITESPACE:
                    break;
                default:
                    if (!$ignore_doc) {
                        $obj->erro = 'Token inv&aacute;lido na checagem dos par&acirc;metros: '.token_name($tokens[$j][0]).' ('.$tokens[$j][0].')';
                    }
                    return $obj;
                }
                $j++;
            } while (trim($tokens[$j][1]) != '//' && $j < $total);
        }
        return $obj;
    }


    //
    //     Obtem o cabecalho do arquivo (sistema, descricao, autor, email, orgao, versao, data, modificado)
    //
    public static function get_cabecalho_arquivo($arquivo) {
    // String $arquivo: nome completo do arquivo
    //
        $arq = fopen($arquivo, 'r');
        if (!$arq) {
            return false;
        }
        do {
            $linha = fgets($arq, 1024);
        } while (!feof($arq) && strpos($linha, '<'.'?php') === false);

        // Obter dados do arquivo
        $ignore_doc = false;
        $flag_comentario = 0;
        $obj = new stdClass();
        $linha = fgets($arq, 1024);
        if (trim($linha) == '//') {
            $linha = fgets($arq, 1024);
            $obj->sistema = substr(trim($linha), 3);
        }
        while (!feof($arq)) {
            $linha = fgets($arq, 1024);
            $linha = trim($linha);
            if ($linha == '//') {
                $flag_comentario++;
                if ($flag_comentario == 2) {
                    break;
                }
            }
            if (preg_match('/^[\/]+[\040]+(.+):[\040]+(.+)$/', $linha, $match)) {
                $campo = trim(strtolower($match[1]));
                $campo = str_replace('-', '', $campo);
                $campo = str_replace('_', '', $campo);
                $valor = trim($match[2]);
                switch (strtolower($campo)) {
                case 'autor':
                case 'descricao':
                case 'orgao':
                case 'email':
                case 'versao':
                case 'data':
                case 'modificado':
                case 'utilizacao': // opcional
                case 'observacao': // opcional
                    $obj->$campo = $valor;
                    break;
                }
            } elseif (strpos($linha, '@ignoredoc') !== false) {
                $ignore_doc = true;
            }
        }
        fclose($arq);

        $campos = array('sistema'    => 'SIMP',
                        'descricao'  => 'Sem Descri&ccedil;&atilde;o',
                        'autor'      => 'Desconhecido',
                        'email'      => 'sem e-mail',
                        'orgao'      => 'nenhum',
                        'versao'     => '?',
                        'data'       => '??/??/????',
                        'modificado' => '??/??/????',
                        'ignore_doc' => $ignore_doc);
        if (isset($obj->utilizacao)) {
            $campos['utilizacao'] = '';
        }
        if (isset($obj->observacao)) {
            $campos['observacao'] = '';
        }

        $obj = util::objeto($campos, $obj);
        return $obj;

    }


    //
    //     Retorna um vetor com as constantes de um arquivo
    //
    public static function get_constantes_arquivo($arquivo, $agrupar = true) {
    // String $arquivo: nome do arquivo a ser analisado
    // Bool $agrupar: agrupar em categorias de constantes (baseada no comentario precedente)
    //
        $vt_constantes = array();
        $arquivo = realpath($arquivo);
        if (!is_file($arquivo)) {
            return false;
        }
        $tokens = token_get_all(file_get_contents($arquivo));
        $total = count($tokens);
        $i = 0;
        while ($i < $total) {
            switch ($tokens[$i][0]) {
            case T_COMMENT:
                $comentario = $tokens[$i][1];
                $pos = 0;
                while ($comentario[$pos] == '/') {
                    $pos++;
                }
                $comentario = trim(substr($comentario, $pos));
                $i++;
                break;
            case T_STRING:
                if ($tokens[$i][1] == 'define') {
                    list($constante, $valor) = self::parse_constante($tokens, $i);
                    if ($agrupar) {
                        $vt_constantes[$comentario][$constante] = $valor;
                    } else {
                        $vt_constantes[$constante] = $valor;
                    }
                }
                $i++;
                break;
            DEFAULT:
                $i++;
                break;
            }
        }
        return $vt_constantes;
    }



    //
    //     Retorna nome de constante e seu respectivo valor
    //
    private static function parse_constante(&$tokens, &$i) {
    // Array[Mixed] $tokens: vetor de tokens do PHP
    // Int $i: posicao no vetor de tokens
    //
        $nome = $valor = '';

        $total = count($tokens);
        while ($i < $total && $tokens[$i][0] != '(') { $i++; }
        $i++;

        while ($i < $total && $tokens[$i][0] != ',') {
            $nome .= isset($tokens[$i][1]) ? $tokens[$i][1] : $tokens[$i][0];
            $i++;
        }
        $i++;

        while ($i < $total && $tokens[$i][0] != ')') {
            $valor .= isset($tokens[$i][1]) ? $tokens[$i][1] : $tokens[$i][0];
            $i++;
        }

        return array($nome, $valor);
    }


    //
    //     Checa se um tipo e' valido ou nao
    //
    public static function validar_tipo($tipo) {
    // String $tipo: nome do tipo
    //
        if (empty($tipo)) {
            return false;
        }
        $basicos = array('Int', 'Float', 'Bool', 'String', 'Char', 'Null', 'Array', 'Object', 'Resource', 'callback', 'Mixed');
        $vt_tipos = self::explode_tipo($tipo);
        foreach ($vt_tipos as $t) {

            // Se e' um tipo simples
            if (in_array($t, $basicos)) {
                continue;

            // Se e' um vetor
            } elseif (preg_match('/^Array\[(.+)\]$/', $t, $match)) {
                $pos = strpos($match[1], '=>');

                // Se foi especificado o indice e o valor
                if ($pos !== false) {
                    $tipo_indice = trim(substr($match[1], 0, $pos));
                    $tipo_valor = trim(substr($match[1], $pos + 2));
                    if (self::validar_indice($tipo_indice) && self::validar_valor($tipo_valor)) {
                        continue;
                    } else {
                        return false;
                    }

                // Se foi especificado apenas o valor
                } else {
                    $tipo_valor = $match[1];
                    if (self::validar_valor($tipo_valor)) {
                        continue;
                    } else {
                        return false;
                    }
                }

            // Se nao e' um tipo simples nem um vetor
            } else {

                // Se e' uma classe ja' definida
                if (class_exists($t, false)) {
                    continue;

                }

                // Tentar carregar a classe manualmente
                try {
                    simp_autoload($t);
                    continue;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return true;
    }


    //
    //     Checa se um tipo e' valido ou nao para ser indice de vetor
    //
    public static function validar_indice($tipo) {
    // String $tipo: nome do tipo
    //
        if (empty($tipo)) {
            return false;
        }
        $indices = array('Int', 'String', 'Char', 'Mixed');
        $vt_tipos = self::explode_tipo($tipo);
        foreach ($vt_tipos as $t) {

            // Se e' um tipo valido
            if (in_array($t, $indices)) {
                continue;
            } else {
                return false;
            }
        }
        return true;
    }


    //
    //     Checa se um tipo e' valido ou nao para o valor de um vetor
    //
    public static function validar_valor($tipo) {
    // String $tipo: nome do tipo
    //
        if (empty($tipo)) {
            return false;
        }
        $valores = array('Int', 'Float', 'Bool', 'String', 'Char', 'Array', 'Object', 'Resource', 'callback', 'Mixed', 'Type');
        $vt_tipos = self::explode_tipo($tipo);
        foreach ($vt_tipos as $t) {

            // Se e' um tipo simples
            if (in_array($t, $valores)) {
                continue;

            // Se e' um vetor
            } elseif (preg_match('/^Array\[(.+)\]$/', $t, $match)) {
                $pos = strpos($match[1], '=>');

                // Se foi especificado o indice e o valor
                if ($pos !== false) {
                    $tipo_indice = trim(substr($match[1], 0, $pos));
                    $tipo_valor = trim(substr($match[1], $pos + 2));
                    if (self::validar_indice($tipo_indice) && self::validar_valor($tipo_valor)) {
                        continue;
                    } else {
                        return false;
                    }

                // Se foi especificado apenas o valor
                } else {
                    $tipo_valor = $match[1];
                    if (self::validar_valor($tipo_valor)) {
                        continue;
                    } else {
                        return false;
                    }
                }

            // Se nao e' um tipo simples nem um vetor
            } else {

                // Se e' uma classe ja' definida
                if (class_exists($t, false)) {
                    continue;

                }

                // Tentar carregar a classe manualmente
                try {
                    simp_autoload($t);
                    continue;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return true;
    }


    //
    //     Explode o tipo na ocorrencia de ||
    //
    public static function explode_tipo($tipo) {
    // String $tipo: Tipo a ser quebrado
    //
        $tipo = str_replace('||', '|', $tipo);
        $final = strlen($tipo);
        $num_vetores = 0;
        $tipo_corrente = '';
        $vetor_saida = array();
        for ($i = 0; $i < $final; $i++) {
            switch ($tipo[$i]) {
            case '[':
                $num_vetores++;
                $tipo_corrente .= '[';
                break;
            case ']':
                $num_vetores--;
                $tipo_corrente .= ']';
                break;
            case '|':
                if ($num_vetores == 0) {
                    $vetor_saida[] = trim($tipo_corrente);
                    $tipo_corrente = '';
                } else {
                    $tipo_corrente .= '||';
                }
                break;
            case '(':
            case ')':
                break;
            default:
                $tipo_corrente .= $tipo[$i];
                break;
            }
        }
        $vetor_saida[] = trim($tipo_corrente);
        return $vetor_saida;
    }

}//class
