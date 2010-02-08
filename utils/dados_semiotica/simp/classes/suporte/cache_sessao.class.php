<?php
//
// SIMP
// Descricao: Classe que controla dados armazenados em cache de sessao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 12/11/2009
// Modificado: 12/11/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class cache_sessao {
    const MAX_VALORES = 10;


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Verifica se um determinado valor esta em cache
    //
    static public function em_cache($id) {
    // String $id: identificador do valor em cache
    //
        // Se nao existe
        if (!isset($_SESSION['tmp'][$id])) {
            return false;
        }

        // Limpar caso expirou o tempo
        $agora = time();
        if ($_SESSION['tmp'][$id]['tempo'] && $_SESSION['tmp'][$id]['tempo'] < $agora) {
            unset($_SESSION['tmp'][$id]);
            return false;
        }
        return true;
    }


    //
    //     Obtem um valor da cache
    //
    static public function get_valor($id) {
    // String $id: identificador do valor em cache
    //
        return $_SESSION['tmp'][$id]['valor'];
    }


    //
    //     Armazena um valor na cache
    //
    static public function set_valor($id, $valor, $tempo = null) {
    // String $id: identificador do valor em cache
    // Mixed $valor: valor a ser armazenado em cache (nao utilize resource)
    // Int $tempo: tempo maximo em que o valor e' valido ou null para indefinido
    //
        // Criar, caso nao exista a sessao
        if (!isset($_SESSION['tmp'])) {
            $_SESSION['tmp'] = array();
        }

        // Armazenar o valor
        if ($tempo === null) {
            $_SESSION['tmp'][$id] = array('valor' => $valor,
                                          'tempo' => null);
        } else {
            $_SESSION['tmp'][$id] = array('valor' => $valor,
                                          'tempo' => time() + $tempo);
        }

        // Apagar o primeiro se passou o tamanho
        if (count($_SESSION['tmp']) > self::MAX_VALORES) {
            array_shift($_SESSION['tmp']);
        }
    }


    //
    //     Limpa os valores da cache
    //
    static public function limpar() {
        unset($_SESSION['tmp']);
    }


    //
    //     Obtem o identificador do dado armazenado em cache
    //
    static public function get_id() {
        $backtrace = debug_backtrace(false);
        $chamada = array_pop($backtrace);
        unset($backtrace);
        return $chamada['file'].':'.$chamada['line'];
    }

}//class
