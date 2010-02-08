<?php
//
// SIMP
// Descricao: Classe para obter atributos de uma classe atraves de uma linguagem simplificada
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 10/10/2009
// Modificado: 10/12/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class filtro_atributo {

    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Obtem os atributos de uma classe de acordo com a linguagem de obtencao de atributos
    //
    public static function get_atributos_classe($classe, $solicitacao) {
    // String $classe: classe derivada da classe objeto
    // String $solicitacao: codigo de solicitacao de atributos
    //
        $obj = objeto::get_objeto($classe);
        return self::get_atributos_objeto($obj, $solicitacao);
    }


    //
    //     Obtem os atributos de um objeto de acordo com a linguagem de obtencao de atributos
    //
    public static function get_atributos_objeto($objeto, $solicitacao) {
    // objeto $objeto: objeto derivado da classe objeto
    // String $solicitacao: codigo de solicitacao de atributos
    //
        $atributos = array();

        $tr = array(' ' => '', "\n" => '', "\r" => '', "\t" => '');
        $solicitacao = strtr($solicitacao, $tr);

        // Percorrer as partes da solicitacao
        $partes = explode(';', $solicitacao);
        foreach ($partes as $parte) {
            if ($parte === '') { continue; }
            $atributos_parte = array();

            // Percorrer as instrucoes da parte
            $instrucoes = explode(',', $parte);
            foreach ($instrucoes as $instrucao) {

                // Adicionar ou remover atributos da parte
                switch ($instrucao[0]) {
                case '-':
                    $instrucao = substr($instrucao, 1);
                    self::operacao($objeto, $instrucao, 'remover', $atributos_parte);
                    break;
                case '+':
                    $instrucao = substr($instrucao, 1);
                    self::operacao($objeto, $instrucao, 'adicionar', $atributos_parte);
                    break;
                default:
                    self::operacao($objeto, $instrucao, 'adicionar', $atributos_parte);
                    break;
                }
            }

            // Incluir atributos da parte no vetor geral
            $atributos = array_merge($atributos, $atributos_parte);
        }
        $atributos = array_unique($atributos);
        return $atributos;
    }


    //
    //     Realiza uma operacao sobre o vetor de atributos
    //
    private static function operacao($objeto, $instrucao, $metodo, &$atributos) {
    // objeto $objeto: objeto derivado da classe objeto
    // String $instrucao: instrucao simples
    // String $metodo: nome do metodo que realiza a operacao
    // Array[String] $atributos: vetor de atributos a ser utilizado para adicionar
    //
        $especial = preg_match('/^([a-z_:]*)\[([^\]]*)\]$/', $instrucao, $match);
        if ($especial) {
            $nome_objeto_filho = substr($match[1], 0, -1);
            $tipo = strtoupper($match[2]);
            if (empty($nome_objeto_filho)) {
                self::operacao_especial($objeto, $tipo, $metodo, $atributos);
            } elseif ($objeto->possui_rel_uu($nome_objeto_filho)) {
                $prefixo = $nome_objeto_filho.':';
                self::operacao_especial($objeto, $tipo, $metodo, $atributos, $objeto->get_objeto_rel_uu($nome_objeto_filho), $prefixo);
            } else {
                trigger_error('A classe "'.$objeto->get_classe().'" nao possui o objeto "'.$nome_objeto_filho.'"', E_USER_ERROR);
            }

        } else {
            self::$metodo($objeto, $instrucao, $atributos);
        }
    }


    //
    //     Realiza uma operacao especial
    //
    private static function operacao_especial($objeto, $tipo, $metodo, &$atributos, $obj_consulta = false, $prefixo = '') {
    // objeto $objeto: objeto derivado da classe objeto
    // String $tipo: tipo de operacao especial
    // String $metodo: nome do metodo que realiza a operacao
    // Array[String] $atributos: vetor de atributos a ser utilizado para adicionar
    // String $prefixo: prefixo a ser adicionado sobre os elementos
    //
        if ($obj_consulta === false) {
            $obj_consulta = $objeto;
        }
        switch ($tipo) {
        case '*':
            foreach ($obj_consulta->get_atributos() as $nome_atributo => $def_atributo) {
                self::$metodo($objeto, $prefixo.$nome_atributo, $atributos);
            }
            foreach ($obj_consulta->get_implicitos() as $nome_implicito => $def_implicito) {
                self::$metodo($objeto, $prefixo.$nome_implicito, $atributos);
            }
            foreach ($obj_consulta->get_definicoes_rel_uu() as $chave_fk => $rel_uu) {
                self::$metodo($objeto, $prefixo.$chave_fk, $atributos);
            }
            foreach ($obj_consulta->get_objetos_rel_uu() as $nome_objeto => $obj) {
                self::$metodo($objeto, $prefixo.$nome_objeto, $atributos);
            }
            foreach ($obj_consulta->get_definicoes_rel_un() as $nome_vetor => $rel_un) {
                self::$metodo($objeto, $prefixo.$nome_vetor, $atributos);
            }
            break;

        case 'B':
            foreach ($obj_consulta->get_atributos() as $nome_atributo => $def_atributo) {
                if (!$def_atributo->chave) {
                    self::$metodo($objeto, $prefixo.$nome_atributo, $atributos);
                }
            }
            break;
        case 'S':
            foreach ($obj_consulta->get_atributos() as $nome_atributo => $def_atributo) {
                self::$metodo($objeto, $prefixo.$nome_atributo, $atributos);
            }
            break;
        case 'I':
            foreach ($obj_consulta->get_implicitos() as $nome_implicito => $def_implicito) {
                self::$metodo($objeto, $prefixo.$nome_implicito, $atributos);
            }
            break;
        case 'PK':
            $chave_pk = $obj_consulta->get_chave();
            self::$metodo($objeto, $prefixo.$chave_pk, $atributos);
            break;
        case 'FK':
            foreach ($obj_consulta->get_definicoes_rel_uu() as $chave_fk => $rel_uu) {
                self::$metodo($objeto, $prefixo.$chave_fk, $atributos);
            }
            break;
        case 'OBJ':
            foreach ($obj_consulta->get_objetos_rel_uu() as $nome_objeto => $obj) {
                self::$metodo($objeto, $prefixo.$nome_objeto, $atributos);
            }
            break;
        case 'VET':
            foreach ($obj_consulta->get_definicoes_rel_un() as $nome_vetor => $rel_un) {
                self::$metodo($objeto, $prefixo.$nome_vetor, $atributos);
            }
            break;
        }
    }


    //
    //     Adiciona um atributo no vetor
    //
    private static function adicionar($objeto, $atributo, &$atributos) {
    // objeto $objeto: objeto derivado da classe objeto
    // String $atributo: nome do atributo simples, implicito, objeto ou vetor
    // Array[String] $atributos: vetor de atributos a ser utilizado para adicionar
    //
        if ($objeto->__isset($atributo)) {
            $atributos[] = $atributo;
        } else {
            trigger_error('A classe "'.$objeto->get_classe().'" nao possui o campo "'.$atributo.'"', E_USER_ERROR);
        }
    }


    //
    //     Remove atributos do vetor
    //
    private static function remover($objeto, $atributo, &$atributos) {
    // objeto $objeto: objeto derivado da classe objeto
    // String $atributo: nome do atributo simples, implicito, objeto ou vetor
    // Array[String] $atributos: vetor de atributos a ser utilizado para remover
    //
        if ($objeto->__isset($atributo)) {
            $pos = array_search($atributo, $atributos);
            if ($pos !== false) {
                unset($atributos[$pos]);
            }
        } else {
            trigger_error('A classe "'.$objeto->get_classe().'" nao possui o campo "'.$atributo.'"', E_USER_ERROR);
        }
    }

}//class
