<?php
//
// SIMP
// Descricao: Classe de definicao do atributo latitude
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 22/10/2009
// Modificado: 22/10/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class atributo_latitude {


    //
    //     Retorna o atributo com as caracteristicas definidas
    //
    public static function get_instancia($nome = 'latitude') {
    // String $nome: nome do atributo
    //
        $atributo = new atributo($nome, 'Latitude', false);
        $atributo->set_tipo('float', true);
        $atributo->set_intervalo(-90, 90);
        $atributo->set_casas_decimais(6, true);
        $atributo->set_validacao(false, false, false);
        return $atributo;
    }

}//class
