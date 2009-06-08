<?php
//
// SIMP
// Descricao: Classe de obtencao dos icones
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 12/11/2008
// Modificado: 15/04/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('ICONE_DIRICONES', $CFG->dirimgs.'icones/');
define('ICONE_WWWICONES', $CFG->wwwimgs.'icones/');

final class icone {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Obtem o endereco de um icone
    //
    static public function endereco($nome_icone) {
    // String $nome_icone: nome do icone
    //
        if (is_file(ICONE_DIRICONES.$nome_icone.'.gif')) {
            return ICONE_WWWICONES.$nome_icone.'.gif';
        } elseif (is_file(ICONE_DIRICONES.$nome_icone.'.png')) {
            return ICONE_WWWICONES.$nome_icone.'.png';
        } elseif (is_file(ICONE_DIRICONES.$nome_icone.'.jpg')) {
            return ICONE_WWWICONES.$nome_icone.'.jpg';
        }
        trigger_error('Icone desconhecido "'.$nome_icone.'"', E_USER_WARNING);
        return false;
    }


    //
    //     Retona a imagem de um icone
    //
    static public function img($nome_icone, $descricao = false, $class = false, $id = false) {
    // String $nome_icone: nome do icone
    // String $descricao: descricao do icone
    // String $class: classe CSS do icone
    // String $id: ID do icone
    //
        $src = self::endereco($nome_icone);
        $tamanho = imagem::tamanho_html($src);
        $alt = $descricao ? ' alt="'.$descricao.'"' : '';
        $title = $descricao ? ' title="'.$descricao.'"' : '';
        $class = $class ? ' class="'.$class.'"' : '';
        $id = $id ? ' id="'.$id.'"' : '';
        return '<img src="'.$src.'" '.$tamanho.$alt.$title.$class.$id.' />';
    }

}//class
