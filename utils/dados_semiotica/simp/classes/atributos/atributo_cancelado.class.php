<?php
//
// SIMP
// Descricao: Classe de definicao do atributo cancelado
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 22/10/2009
// Modificado: 22/10/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class atributo_cancelado {


    //
    //     Retorna o atributo com as caracteristicas definidas
    //
    public static function get_instancia($nome = 'cancelado') {
    // String $nome: nome do atributo
    //
        $atributo = new atributo($nome, 'Cancelado', false);
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        return $atributo;
    }

}//class
