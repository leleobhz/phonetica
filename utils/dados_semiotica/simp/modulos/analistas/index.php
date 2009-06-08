<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Lista de Analistas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$dados_lista = new stdClass();
$dados_lista->opcoes = array('exibir', 'alterar', 'projetos', 'excluir');
$dados_lista->campos = array('usuario:nome');
$dados_lista->ordem  = 'usuario:nome';

/// Dados da Pagina
$dados_pagina = null;

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->funcao_form = 'imprimir_formulario';
$dados_form->funcao_condicoes = 'obter_condicoes';
$dados_form->exibir_lista = true;

modulo::listar_entidades('analista', $dados_lista, $dados_pagina, $dados_form);
exit(0);


/// Funcoes


//
//     Gera o formulario de filtro
//
function imprimir_formulario($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    $action = $CFG->site;
    link::normalizar($action);

    $padrao = array('nome' => '',
                    'email' => '');
    $dados = formulario::montar_dados($padrao, $dados);

    $form = new formulario($action, 'form_filtro');
    $form->campo_busca('nome', 'nome', 'analista', 'usuario:nome', $dados->nome, false, 50, 30, 'Nome');
    $form->campo_busca('email', 'email', 'analista', 'usuario:email', $dados->email, false, 50, 30, 'E-mail');
    $form->campo_submit('enviar', 'enviar', 'Filtrar');
    $form->imprimir();
}


//
//     Monta as condicoes de filtragem
//
function obter_condicoes($dados) {
// Object $dados: dados submetidos
//
    if (!$dados) {
        return condicao_sql::vazia();
    }

    $vt_condicoes = array();
    if (!empty($dados->nome)) {
        $vt_condicoes[] = condicao_sql::montar('usuario:nome', 'LIKE', '%'.$dados->nome.'%');
    }
    
    if (!empty($dados->email)) {
        $vt_condicoes_email = array();
        $vt_condicoes_email[] = condicao_sql::montar('usuario:email', 'LIKE', '%'.$dados->email.'%');
        $vt_condicoes_email[] = condicao_sql::montar('email_alternativo', 'LIKE', '%'.$dados->email.'%');

        $vt_condicoes[] = condicao_sql::sql_or($vt_condicoes_email);
    }
    if (count($vt_condicoes)) {
        return condicao_sql::sql_and($vt_condicoes);
    }
    return condicao_sql::vazia();
}
