<?php
//
// ANALISES - Cadastro de Analises sobre Frases
// Descricao: Arquivo para alterar dados de Analises das Frases
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 09/07/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$ANALISTA = analista::get_analista();

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->prefixo = 'analista';
$dados_form->campos = array('cod_categoria_analise');

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'frase';

modulo::alterar('analise', $dados_form, $dados_pagina);
