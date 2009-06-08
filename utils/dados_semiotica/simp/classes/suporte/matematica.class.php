<?php
//
// SIMP
// Descricao: Classe de operacoes matematicas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 06/02/2009
// Modificado: 06/02/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class matematica {


    //
    //     Calcula o desvio padrao de um vetor de numeros
    //
    public static function get_desvio_padrao($vetor, $precisao = 2) {
    // Array[Int || Float] $vetor: vetor de valores numericos
    // Int $precisao: numero de casas decimais do resultado
    //
        $media = self::get_media($vetor, $precisao);
        foreach ($vetor as $valor) {
            $variancia[] = pow($valor - $media, 2);
        }
        $desvio = sqrt(self::get_media($variancia, $precisao));
        return round($desvio, $precisao);
    }


    //
    //     Calcula a media de um vetor de numeros
    //
    public static function get_media($vetor, $precisao = 2) {
    // Array[Int || Float] $vetor: vetor de valores numericos
    // Int $precisao: numero de casas decimais do resultado
    //
        $quantidade = count($vetor);
        if (!$quantidade) {
            return 0;
        }
        $soma = array_sum($vetor);
        return round($soma / $quantidade, $precisao);
    }

}//class

