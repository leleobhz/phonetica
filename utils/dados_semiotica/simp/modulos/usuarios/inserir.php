<?php
//
// SIMP
// Descricao: Cadastra usuarios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.5
// Data: 29/08/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'nome',
    'login',
    'email',
    'vetor_grupos',
    'Senha' => array(
        'geracao_senha',
        'senha_sugerida',
        'confirmacao'
    )
);
$dados_form->prefixo = 'pessoal';
$dados_form->ajuda   = <<<AJUDA
  <p>Este formul&aacute;rio &eacute; destinado ao cadastro de novos
  usu&aacute;rios.</p><p>Definindo os dados de um novo usu&aacute;rio, a senha
  ser&aacute; gerada automaticamente e enviada para o e-mail
  informado.</p><p>Ap&oacute;s cadastrar o usu&aacute;rio, &eacute;
  necess&aacute;rio especificar os grupos que ele pertence acessando o
  <em>link</em> correspondente na p&aacute;gina de
  usu&aacute;rios.</p><p>Estando cadastrado em um grupo, o usu&aacute;rio
  poder&aacute; acessar o sistema e alterar seus dados pessoais, al&eacute;m de
  sua senha.</p>
AJUDA;

modulo::inserir('usuario', $dados_form);
