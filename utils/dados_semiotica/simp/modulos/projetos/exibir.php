<?php
//
// ANALISES - Cadastro de Analises sobre Frases
// Descricao: Exibe os dados de um Projeto
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
$dados_quadro = new stdClass();
$dados_quadro->campos = array(
    'Dados do Projeto' => array(
        'cod_projeto',
        'nome',
        'data_entrada_dados'
    ),
    'textos'
);

modulo::exibir('projeto', $dados_quadro);
