<?php
//
// SIMP
// Descricao: Arquivo para cadastrar dados dos arquivos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 25/09/2007
// Modificado: 09/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados para o formulario
$dados_form = new stdClass();
$dados_form->campos = array('descricao', 'modulo', 'arquivo');
$dados_form->ajuda  = <<<AJUDA
  <p>Formul&aacute;rio para a cria&ccedil;&atilde;o de novos arquivos no sistema.</p>
  <p>Certifique-se que o m&oacute;dulo e o arquivo existam.</p>
AJUDA;

$descricao_padrao = util::get_dado('descricao', 'string', false);
if ($descricao_padrao) {
    $dados_form->dados->arquivo->descricao = $descricao_padrao;
}
$modulo_padrao = util::get_dado('modulo', 'string', false);
if ($modulo_padrao) {
    $dados_form->dados->arquivo->modulo = $modulo_padrao;
}
$arquivo_padrao = util::get_dado('arquivo', 'string', false);
if ($arquivo_padrao) {
    $dados_form->dados->arquivo->arquivo = $arquivo_padrao;
}

modulo::inserir('arquivo', $dados_form);
