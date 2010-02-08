<?php
//
// SIMP
// Descricao: Classe para realizar operacoes comuns sobre vetores
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 25/08/2009
// Modificado: 05/10/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class vetor {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Gera uma matriz indexada por um atributo dos elementos
    //
    public static function agrupar_por_atributo($vetor, $atributo, $indices = null) {
    // Array[Object] $vetor: vetor de objetos que possuem o atributo $atributo
    // String $atributo: nome do atributo a ser usado na indexacao
    // Array[Mixed] $indices: indices a serem definidos na matriz automaticamente
    //
        $matriz = array();
        if (is_array($indices)) {
            foreach ($indices as $indice) {
                $matriz[$indice] = array();
            }
        }
        foreach ($vetor as $chave => $objeto) {
            $indice = $objeto->$atributo;
            if (!is_null($indice)) {
                if (!isset($matriz[$indice])) {
                    $matriz[$indice] = array();
                }
                $matriz[$indice][$chave] = $objeto;
            } else {
                trigger_error("O objeto nao possui o atributo \"{$atributo}\"", E_USER_ERROR);
            }
        }
        return $matriz;
    }


    //
    //     Obtem um vetor com os valores de determinado atributo dos objetos do vetor original
    //
    public static function get_vetor_atributo($vetor, $atributo, $indexado = true) {
    // Array[Object] $vetor: vetor de objetos que possuem o atributo $atributo
    // String $atributo: atributo a ser coletado dos objetos
    // Bool $indexado: indica se o vetor resultante deve ser indexado pelo valor do atributo ou nao
    //
        $vetor_atributo = array();
        if ($indexado) {
            foreach ($vetor as $objeto) {
                $valor = $objeto->$atributo;
                $vetor_atributo[$valor] = $valor;
            }
        } else {
            foreach ($vetor as $objeto) {
                $vetor_atributo[] = $objeto->$atributo;
            }
        }
        return $vetor_atributo;
    }


    //
    //     Ordena um vetor utilizando um vetor de chaves e retorna o vetor ordenado
    //
    public static function ordenar_por_chaves($vetor, $chaves) {
    // Array[Mixed => Mixed] $vetor: vetor indexado de alguma forma
    // Array[Mixed] $chaves: vetor de chaves utilizadas para ordenacao
    //
        $novo = array();
        foreach ($chaves as $chave) {
            if (isset($vetor[$chave])) {
                $novo[$chave] = $vetor[$chave];
            }
        }
        return $novo;
    }

}//class
