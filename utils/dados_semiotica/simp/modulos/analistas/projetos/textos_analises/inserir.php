<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Arquivo para inserir Texto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'titulo',
    'arquivo'
);

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'projeto';

modulo::inserir('texto_analise', $dados_form, $dados_pagina);
