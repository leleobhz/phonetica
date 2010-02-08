<?php
//
// SIMP
// Descricao: Classe de definicao do atributo identificador
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 10/12/2009
// Modificado: 10/12/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class atributo_identificador {


    //
    //     Retorna o atributo com as caracteristicas definidas
    //
    public static function get_instancia($nome = 'identificador', $descricao = 'Identificador') {
    // String $nome: nome do atributo
    //
        $atributo = new atributo($nome, $descricao, '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 50);
        $atributo->set_validacao('IDENTIFICADOR', false, false);
        return $atributo;
    }

}//class
