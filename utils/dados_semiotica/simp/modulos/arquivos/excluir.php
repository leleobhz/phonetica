<?php
//
// SIMP
// Descricao: Exclui um arquivo no BD
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 14/11/2007
// Modificado: 01/02/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array('arquivo', 'descricao', 'modulo');
$dados_form->ajuda  = <<<AJUDA
  <p>O formul&aacute;rio abaixo permite a exclus&atilde;o de um arquivo no sistema. 
  Obs.: O script n&atilde;o apaga o arquivo, apenas sua refer&ecirc;ncia no sistema.</p>
AJUDA;
modulo::excluir('arquivo', $dados_form);
