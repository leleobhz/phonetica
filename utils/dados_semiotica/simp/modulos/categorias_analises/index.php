<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Lista de Categorias de Analise
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
$dados_lista->opcoes = array('exibir', 'alterar', 'excluir');
$dados_lista->campos = array('nome');
$dados_lista->ordem  = 'nome';

/// Dados da Pagina
$dados_pagina = null;

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->funcao_form = 'imprimir_formulario';
$dados_form->funcao_condicoes = 'obter_condicoes';
$dados_form->exibir_lista = true;

modulo::listar_entidades('categoria_analise', $dados_lista, $dados_pagina, $dados_form);


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

    $padrao = array('nome' => '');
    $dados = formulario::montar_dados($padrao, $dados);

    $form = new formulario($action, 'form_filtro');
    $form->campo_busca('nome', 'nome', 'categoria_analise', 'nome', $dados->nome, false, 50, 30, 'Nome');
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

    if (!empty($dados->nome)) {
        return condicao_sql::montar('nome', 'LIKE', '%'.$dados->nome.'%');
    }
    return condicao_sql::vazia();
}
