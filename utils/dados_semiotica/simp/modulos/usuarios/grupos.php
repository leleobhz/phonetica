<?php
//
// SIMP
// Descricao: Define os grupos do Usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.0
// Data: 20/08/2007
// Modificado: 04/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Formulario
$entidade = util::get_entidade('usuario');
$dados_form = new stdClass();
$dados_form->prefixo = 'grupos';
$dados_form->nome_vetor = 'grupos';        // Vetor da classe usuario (tipo usuarios_grupos)
$dados_form->classe_relacionada = 'grupo'; // Objeto da classe usuarios_grupos
$dados_form->ajuda   = <<<AJUDA
  <p>Este formul&aacute;rio destina-se a defini&ccedil;&atilde;o dos grupos de um usu&aacute;rio.</p>
  <p>Os grupos s&atilde;o usados para definir o tipo de acesso ao sistema, ou seja, por eles 
  s&atilde;o definidos os arquivos com visibilidade.</p>
AJUDA;
$dados_form->disable = ($entidade->get_valor_chave() == 1) ? array(COD_ADMIN) : array();

modulo::relacionamento('usuario', $dados_form);
