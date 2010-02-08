<?php
//
// SIMP
// Descricao: Arquivo para alterar os dados dos arquivos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 25/09/2007
// Modificado: 01/02/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array('descricao', 'modulo', 'arquivo');
$dados_form->ajuda  = <<<AJUDA
  <p>Formul&aacute;rio para alterar os dados de um arquivo do sistema.</p>
  <p>Certifique-se de que o m&oacute;dulo e o arquivo existam de fato.</p>
AJUDA;

modulo::alterar('arquivo', $dados_form);
