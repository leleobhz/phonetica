<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Exibe os dados de um(a) Projeto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array(
    'Dados do Projeto' => array(
        'cod_projeto',
        'nome',
        'data_entrada_dados'
    ),
    'Dados do Analista' => array(
        'analista:usuario:nome',
        'analista:usuario:email',
        'analista:email_alternativo'
    ),
    'textos'
);

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'analista';

modulo::exibir('projeto', $dados_quadro, $dados_pagina);
