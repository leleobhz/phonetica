<?php
//
// SIMP
// Descricao: Exibe os dados de um usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 09/08/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array('nome', 'email', 'login', 'grupos', 'cancelado');

modulo::exibir('usuario', $dados_quadro);
