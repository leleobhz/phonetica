<?php
//
// SIMP
// Descricao: Exibe os dados de um arquivo do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 25/09/2007
// Modificado: 01/02/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array('descricao', 'modulo', 'arquivo', 'link');
modulo::exibir('arquivo', $dados_quadro);
