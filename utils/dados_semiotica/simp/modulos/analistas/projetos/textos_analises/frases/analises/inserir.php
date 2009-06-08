<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Arquivo para inserir Analise da Frase
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 05/06/2009
// Modificado: 05/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos = array('cod_categoria_analise');

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'frase';

modulo::inserir('analise', $dados_form, $dados_pagina);
