<?php
//
// ANALISES - Cadastro de Analises sobre Frases
// Descricao: Lista de Projetos de um analista
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 09/07/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

$ANALISTA = analista::get_analista();

/// Dados do Quadro
$dados_lista = new stdClass();
$dados_lista->opcoes    = array('exibir', 'alterar', 'textos_analista', 'excluir');
$dados_lista->campos    = array('nome');
$dados_lista->ordem     = 'nome';
$dados_lista->condicoes = condicao_sql::montar('cod_analista', '=', $ANALISTA->get_valor_chave());

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = 'Meus Projetos';

modulo::listar_entidades('projeto', $dados_lista, $dados_pagina);
