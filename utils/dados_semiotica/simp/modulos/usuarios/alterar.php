<?php
//
// SIMP
// Descricao: Arquivo que lista as abas de edicao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.5
// Data: 31/08/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados Gerais
$modulo = util::get_modulo(__FILE__);
$classe = 'usuario';


/// Dados da Pagina
$titulo  = 'Alterar Dados Pessoais';
$nav[] = '#index.php';
if ($USUARIO->possui_grupo(COD_ADMIN)) {
    $nav[] = $modulo.'#index.php';
}
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css.php');


/// Recuperar a entidade desejada
$entidade = util::get_entidade($classe, true, false, false, false, $USUARIO->cod_usuario);


/// Definicao das abas
$id_abas = $modulo.'_abas';

// Links e nomes das abas
$link       = $CFG->wwwmods.$modulo.'/alterar.php';                    // Link base
$dir_modulo = $CFG->dirmods.$modulo.'/';                               // Diretorio do modulo
$parametros = $entidade->get_chave().'='.$entidade->get_valor_chave(); // Parametros comuns
$padrao     = 'pessoal';                                               // ID da aba padrao


/// Criar as Abas

// Aba: Alterar Dados Pessoais
$aba = new stdClass();
$aba->id      = 'pessoal';
$aba->link    = $link.'?'.$id_abas.'='.$aba->id.'&amp;'.$parametros;
$aba->arquivo = $dir_modulo.'alterar_pessoal.php';
$aba->nome    = 'Pessoal';
$abas[$aba->id] = $aba;

// Aba: Alterar Senha
if ($CFG->autenticacao == 'simp') {
    $aba = new stdClass();
    $aba->id      = 'senha';
    $aba->link    = $link.'?'.$id_abas.'='.$aba->id.'&amp;'.$parametros;
    $aba->arquivo = $dir_modulo.'alterar_senha.php';
    $aba->nome    = 'Senha';
    $abas[$aba->id] = $aba;
}

// Observacoes:
// Em $_SESSION[$modulo][$entidade->get_chave()] guardamos a PK da entidade exibida
// Em $_SESSION[$modulo][$id_abas] guardamos o ID da aba aberta no momento

if (!isset($_SESSION[$modulo][$entidade->get_chave()])) {
    $_SESSION[$modulo][$entidade->get_chave()] = 0;
}

// Se a PK da entidade exibida mudou: voltar o id da aba para a aba padrao
if ($entidade->get_valor_chave() != $_SESSION[$modulo][$entidade->get_chave()]) {
    $_SESSION[$modulo][$id_abas] = $padrao;
}

// Guardar a PK da entidade exibida
$_SESSION[$modulo][$entidade->get_chave()] = $entidade->get_valor_chave();


/// Obter por GET o ID da aba a ser aberta

// Se nao foi passado nenhum dado por get, tentar pegar da sessao ou a propria aba padrao
$padrao = isset($_SESSION[$modulo][$id_abas]) ? $_SESSION[$modulo][$id_abas] : $padrao;

// Obter aba por GET e salva-la na sessao
$ativa  = util::get_dado($id_abas, 'string', false, $padrao);
$_SESSION[$modulo][$id_abas] = $ativa;


/// Incluir o arquivo correspondente
require_once($abas[$ativa]->arquivo);
