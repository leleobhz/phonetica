<?php
//
// SIMP
// Descricao: Classe que controla listas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 22/08/2007
// Modificado: 31/07/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

final class lista {
    static private $total = 0;


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Inicio de uma lista
    //
    static public function inicio($titulo = false, $id = false, $class = false, $return = false) {
    // String $titulo: titulo opcional da lista
    // String $id: identificador da lista
    // String $class: classe CSS da lista
    // Bool $return: retornar ou imprimir a lista
    //
        $id = $id ? " id=\"{$id}\"" : '';
        $class = $class ? " class=\"lista {$class}\"" : ' class="lista dados"';

        $l = "<div{$id}{$class}>\n";
        if ($titulo) {
            $l .= self::titulo($titulo, 1);
        }
        if ($return) {
            return $l;
        }
        echo $l;
    }


    //
    //     Imprime um fim de lista
    //
    static public function fim($rodape = '', $return = false) {
    // String $rodape: texto do rodape da lista
    // Bool $return: retornar ou imprimir o fim de lista
    //
        $l = '';
        if ($rodape) {
            $l .= self::rodape($rodape, true);
        }
        $l .= "  <br class=\"clear\" />\n".
             "</div>";
        if ($return) {
            return $l;
        }
        echo $l;
    }


    //
    //     Imprime o titulo da lista
    //
    static public function titulo($titulo, $return = false) {
    // String $titulo: titulo da lista
    // Bool $return: retornar ou imprimir o titulo
    //
        $t = "  <strong class=\"titulo\">{$titulo}</strong>\n";
        if ($return) {
            return $t;
        }
        echo $t;
    }


    //
    //     Imprime o rodape da lista
    //
    static public function rodape($texto, $return = false) {
    // String $texto: texto do rodape
    // Bool $return: retornar ou imprimir o titulo
    //
        $r = "  <strong class=\"rodape\">{$texto}</strong>\n";
        if ($return) {
            return $r;
        }
        echo $r;
    }


    //
    //     Imprime uma lista hierarquica
    //
    static public function hierarquica($lista) {
    // Array[String => Bool || Type] $lista: vetor associativo hierarquico onde as chaves sao os textos de impressao e os valores sao false (no nao expandido) ou um vetor associativo hierarquico
    //
        return self::hierarquica_recursiva($lista, true);
    }


    //
    //     Imprime uma lista hierarquica
    //
    static private function hierarquica_recursiva($lista, $primeira_chamada) {
    // Array[String => Bool || Type] $lista: vetor associativo hierarquico onde as chaves sao os textos de impressao e os valores sao false (no nao expandido) ou um vetor associativo hierarquico
    // Bool $primeira_chamada: indica se e' a primeira chamada 'a funcao
    //
        if (is_array($lista)) {
            $total = count($lista);
            $chaves = array_keys($lista);
            $valores = array_values($lista);
            unset($lista);

            echo "<ul class=\"hierarquia\">";
            for ($i = 0; $i < $total; $i++) {
                $chave = &$chaves[$i];
                $valor = &$valores[$i];
                $class = ($i < ($total - 1)) ? ' class="l"' : '';

                echo '<li>';
                echo '<span class="lb">&nbsp;</span>';
                echo "<span{$class}>";
                echo '<span class="valor">';
                echo $chave;
                if (is_array($valor)) {
                    self::hierarquica_recursiva($valor, false);
                }
                echo '</span>';
                echo '</span>';
                echo "</li>\n";
            }
            echo "</ul>\n";
        }
        if ($primeira_chamada) {
            echo "<br class=\"clear\" />\n";
        }
    }

}//class
