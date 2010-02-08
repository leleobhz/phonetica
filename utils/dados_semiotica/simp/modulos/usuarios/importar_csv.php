<?php
//
// SIMP
// Descricao: Importa usuarios de um arquivo CSV
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 27/06/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array('nome', 'login', 'email'); // Campos obrigatorios

modulo::importar_csv('usuario', $dados_form);
