<?php
//
// SIMP
// Descricao: Exemplo de um campo select hierarquico
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
header('Content-type: text/html; charset=UTF-8');

$vetor = array(
    'Grupo A' => array(
        'ABC' => array(
           0 => '123',
           1 => '456',
           2 =>'789'
        ),
        3 => 'DEF',
        4 => 'GHI'
    ),
    'Grupo B' => array(
        5 => 'JKL',
        6 =>'MNO',
        'PQR' => array(
            7 => 'abc',
            'def' => array(
                8 => 'xyz',
                9 => 'zyx',
                10 => 'xzy'
            )
        ),
        11 => 'teste'
    ),
    'Grupo C' => array(
        'STU' => array(
            12 => '01',
            13 => '02',
            14 => '03'
        ),
        15 => 'VWX',
        'YZÇ' => array(
            16 => '04'
        )
    )
);

montar_select($vetor);
exit(0);

//
//     Monta um select hierarquico
//
function montar_select($vetor) {
// Array[String => Type] $vetor: vetor hierarquico
//

//    echo '<select style="font-family: Courier-New;">';
    echo '<select style="font-family: Monospace;">';
    imprimir_options($vetor);
    echo '</select>';
}


//
//     Imprime as opcoes do select de forma recursiva
//
function imprimir_options($vetor, $niveis = array()) {
// Array[String => Type] $vetor: vetor hierarquico
// Array[String => String] $niveis: vetor auxiliar para montar os niveis hierarquicos
//
    $chaves = array_keys($vetor);
    $ultima_chave = array_pop($chaves);
    unset($chaves);

    $tipo = 'light';
//    $tipo = 'simple';
//    $tipo = 'heavy';

    switch ($tipo) {
    case 'simple':
        $espaco = '&nbsp;&nbsp;&nbsp;';
        $topo_baixo = '|&nbsp;&nbsp;';
        $topo_direita_baixo = '|-&nbsp;';
        $topo_direita = '\'-&nbsp;';
        break;
    case 'double':
        $espaco = '&nbsp;&nbsp;';
        $topo_baixo = '║&nbsp;';
        $topo_direita_baixo = '╠&nbsp;';
        $topo_direita = '╚&nbsp;';
        break;
    case 'light':
        $espaco = '&nbsp;&nbsp;';
        $topo_baixo = '│&nbsp;';
        $topo_direita_baixo = '├&nbsp;';
        $topo_direita = '└&nbsp;';
        break;
    }

    foreach ($vetor as $chave => $valor) {
        $ultimo = $ultima_chave === $chave;
        if (is_array($valor)) {
            echo '<option title="'.implode(' &gt; ', array_keys($niveis)).' &gt; '.$chave.'">';
            foreach ($niveis as $nivel) {
                echo $nivel ? $espaco : $topo_baixo;
            }
            echo $ultimo ? $topo_direita : $topo_direita_baixo;
            echo $chave;
            echo '</option>';

            $niveis_novo = $niveis;
            $niveis_novo[$chave] = $ultimo;
            imprimir_options($valor, $niveis_novo);
            
        } else {
            echo '<option value="'.$chave.'" title="'.implode(' &gt; ', array_keys($niveis)).' &gt; '.$valor.'">';
            foreach ($niveis as $nivel) {
                echo $nivel ? $espaco : $topo_baixo;
            }
            echo $ultimo ? $topo_direita : $topo_direita_baixo;
            echo $valor;
            echo '</option>';
        }
    }
}
