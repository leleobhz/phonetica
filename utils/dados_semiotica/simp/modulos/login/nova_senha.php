<?php
//
// SIMP
// Descricao: Gera nova senha e envia ao usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 03/03/2007
// Modificado: 30/04/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Verificar se o sistema esta' instalado
if (!$CFG->versao) {
    header('location: '.$CFG->wwwroot.'instalar.php');
    exit(0);
}


/// Dados do Formulario
$modulo  = util::get_modulo(__FILE__);
$classe  = 'usuario';
$dados   = formulario::get_dados();
$campos  = array('login', 'email', 'captcha');
$action  = $CFG->site;
$email   = '&lt;'.texto::proteger_email($CFG->email_padrao).'&gt;';
$ajuda   = <<<AJUDA
  <p>Este formul&aacute;rio permite aos usu&aacute;rios, que esqueceram suas senhas, gerar uma nova senha e envi&aacute;-la por e-mail 
  mediante a confirma&ccedil;&atilde;o de alguns campos. Caso os campos pedidos foram esquecidos ou seu e-mail tenha sido alterado,
  favor entrar em contato com os respons&aacute;veis pelo sistema. E-mail de contato principal: {$email}.
</p>
AJUDA;


/// Dados da Pagina
$id_pagina = 'esqueci_senha';
$titulo    = 'Nova Senha';
$nav[]     = $modulo.'#index.php';
$nav[]     = $modulo.'#'.basename(__FILE__);
$estilos   = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Consultar entidade
$entidade = new $classe();


/// Imprimir Pagina
$pagina = new pagina($id_pagina);
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo();
mensagem::comentario($CFG->site, $ajuda);
$entidade->formulario_nova_senha($dados, $campos, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);
