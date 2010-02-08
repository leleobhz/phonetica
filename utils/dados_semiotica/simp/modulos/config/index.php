<?php
//
// SIMP
// Descricao: Arquivo que lista as abas de configuracoes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.6
// Data: 10/07/2007
// Modificado: 11/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados Gerais
$modulo = util::get_modulo(__FILE__);
$classe = 'config';


/// Ddos da pagina
$titulo  = 'Configura&ccedil;&otilde;es';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css.php');
$scripts = array($CFG->wwwmods.$modulo.'/script.js.php');

/// Recuperar a entidade desejada
$entidade = new $classe('', 1);


/// Definicao das abas
$id_abas = $modulo.'_abas';

// Links e nomes das abas
$link       = $CFG->wwwmods.$modulo.'/index.php';
$dir_modulo = $CFG->dirmods.$modulo.'/';
$padrao     = 'pagina';


/// Criar as abas

// Aba: Dados da Pagina
$aba = new stdClass();
$aba->id = 'pagina';
$aba->link = link::adicionar_atributo($link, $id_abas, $aba->id);
$aba->arquivo = $dir_modulo.'config_pagina.php';
$aba->nome = 'Dados da P&aacute;gina';
$abas[$aba->id] = $aba;

// Aba: Dados do Sistema
$aba = new stdClass();
$aba->id = 'sistema';
$aba->link = link::adicionar_atributo($link, $id_abas, $aba->id);
$aba->arquivo = $dir_modulo.'config_sistema.php';
$aba->nome = 'Dados do Sistema';
$abas[$aba->id] = $aba;

// Aba: Autenticacao
$aba = new stdClass();
$aba->id = 'autenticacao';
$aba->link = link::adicionar_atributo($link, $id_abas, $aba->id);
$aba->arquivo = $dir_modulo.'config_autenticacao.php';
$aba->nome = 'Autentica&ccedil;&atilde;o';
$abas[$aba->id] = $aba;

// Aba: E-mail
$aba = new stdClass();
$aba->id = 'email';
$aba->link = link::adicionar_atributo($link, $id_abas, $aba->id);
$aba->arquivo = $dir_modulo.'config_email.php';
$aba->nome = 'E-mail';
$abas[$aba->id] = $aba;


/// Dados recebidos por GET
$padrao = isset($_SESSION[$modulo][$id_abas]) ? $_SESSION[$modulo][$id_abas] : $padrao;
$ativa  = util::get_dado($id_abas, 'string', false, $padrao);
$_SESSION[$modulo][$id_abas] = $ativa;


/// Incluir o arquivo correspondente
require_once($abas[$ativa]->arquivo);
