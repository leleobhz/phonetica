<?php
//
// SIMP
// Descricao: Classe analista
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class analista extends analista_base {

    //
    //     Obtem o analista logado no sistema ou aborta
    //
    public static function get_analista() {
        global $USUARIO;
        if (!$USUARIO->possui_grupo(COD_ANALISTAS)) {
            pagina::erro('Voc&ecirc; n&atilde;o &eacute; um analista');
        }
        $analista = $USUARIO->get_pai('analista');
        if (!$analista->existe()) {
            pagina::erro('Voc&ecirc; n&atilde;o &eacute; um analista');
        }
        return $analista;
    }


    //
    //     Retorna um vetor com os dados da opcao (icone) que aparece na lista de entidades
    //
    public function dados_opcao($opcao, $modulo) {
    // String $opcao: identificador da opcao
    // String $modulo: nome do modulo
    //
        $dados = new stdClass();

        switch ($opcao) {
        case 'projetos':
            $dados->icone     = icone::endereco('produto');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/projetos';
            $dados->descricao = 'Projetos';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        }

        return parent::dados_opcao($opcao, $modulo);
    }

}//class
