<?php
//
// SIMP
// Descricao: Gera graficos de barra em HTML baseado em CSS e com acessibilidade
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 18/06/2008
// Modificado: 06/10/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class grafico_barra {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Gerra um grafico de barra baseado em CSS
    //
    public static function gerar($label, $porcentagem) {
    // String $label: nome da estatistica apresentada
    // Float $porcentagem: valor percentual a ser apresentado
    //
        $porcentagem = texto::numero($porcentagem, 2, false);
        $p = max(0, min(100, $porcentagem)) * 0.25;

        return '<p class="grafico_barra">'.
               '<strong>'.$label.':</strong> '.
               '<span>'.
               '<span>'.
               '<span style="width: '.number_format($p, 2, '.', '').'em">'.
               '<span>'.$porcentagem.'%</span>'.
               '</span>'.
               '</span>'.
               '</span>'.
               '<br />'.
               '</p>';
    }

}//class
