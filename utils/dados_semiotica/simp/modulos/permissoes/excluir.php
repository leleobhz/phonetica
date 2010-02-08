<?php
//
// SIMP
// Descricao: Arquivo que remove permissoes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 25/09/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array('arquivo', 'grupo');
$dados_form->ajuda  = <<<AJUDA
  <p>O formul&aacute;rio abaixo permite a exclus&atilde;o de uma permiss&atilde;o 
  a um arquivo por algum grupo. Isso significa que o grupo indicado n&atilde;o ter&aacute;
  mais acesso ao arquivo em caso de confirma&ccedil;&atilde;o.</p>
  <p>Ap&oacute;s a confirma&ccedil;&atilde;o os dados n&atilde;o poder&atilde;o 
  ser recuperados, sendo necess&aacute;rio um novo cadastro.</p>
AJUDA;

modulo::excluir('permissao', $dados_form);
