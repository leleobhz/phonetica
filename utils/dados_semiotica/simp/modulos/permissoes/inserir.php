<?php
//
// SIMP
// Descricao: Arquivo para cadastrar permissao de um arquivo para um grupo
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 25/09/2007
// Modificado: 09/05/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Obter Grupo
$grupo = util::get_entidade('grupo', array('nome'));


/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos  = array('cod_arquivo', 'posicao', 'visivel');
$dados_form->opcoes  = array('cod_grupo' => $grupo->cod_grupo);
$dados_form->ajuda   = <<<AJUDA
  <p>Formul&aacute;rio de cadastro de permiss&otilde;es de arquivos para grupos.</p>
  <p>O arquivo deve estar cadastrado no m&oacute;dulo "Arquivos".</p>
  <p>O campo posi&ccedil;&atilde;o indica o local do menu onde o novo item deve aparecer,
  com visibilidade ou n&atilde;o.</p>
AJUDA;

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = 'Cadastrar Permiss&atilde;o para '.$grupo->exibir('nome');

modulo::inserir('permissao', $dados_form, $dados_pagina);
