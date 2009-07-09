<?php
//
// SIMP
// Descricao: Script de Instalacao da Configuracao Basica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.10
// Data: 10/09/2007
// Modificado: 06/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('INSTALACAO_CONFIG_TITULO',     $CFG->titulo);
define('INSTALACAO_CONFIG_DESCRICAO',  $CFG->descricao);
define('INSTALACAO_CONFIG_AUTOR',      $CFG->autor);
define('INSTALACAO_CONFIG_LINK_AUTOR', $CFG->link_autor);
define('INSTALACAO_CONFIG_SISTEMA',    $CFG->sistema);
define('INSTALACAO_CONFIG_EMAIL',      $CFG->email_padrao);
define('INSTALACAO_CONFIG_LINGUA',     $CFG->lingua);
define('INSTALACAO_CONFIG_LOCALIDADE', $CFG->localidade);
define('INSTALACAO_CONFIG_CIDADE',     $CFG->cidade);
define('INSTALACAO_CONFIG_ESTADO',     $CFG->estado);
define('INSTALACAO_CONFIG_PALAVRAS',   $CFG->palavras);


//
//     Instala a configuracao padrao
//
function instalar_config(&$erros) {
// Array[String] $erros: erros ocorridos
//
    $r = true;

    $c = new config();
    if ($c->existe()) {
        return true;
    }
    $c->limpar_objeto();

    $c->ajax = 1;
    $c->gd = extension_loaded('gd');
    $c->transparencia = 0.7;
    $c->opaco = 0.9;
    $c->autenticacao = 'simp';
    $c->fechado = false;
    $c->motivo_fechado = '';
    $c->formato_data = '%d/%m/%Y';
    $c->formato_hora = '%H:%M:%S';
    $c->tipo_email = CONFIG_EMAIL_PADRAO;
    $c->smtp_host = '';
    $c->smtp_porta = 25;
    $c->smtp_usuario = '';
    $c->smtp_senha = '';
    $c->titulo = INSTALACAO_CONFIG_TITULO;
    $c->descricao = INSTALACAO_CONFIG_DESCRICAO;
    $c->autor = INSTALACAO_CONFIG_AUTOR;
    $c->link_autor = INSTALACAO_CONFIG_LINK_AUTOR;
    $c->preassunto = '['.strtoupper(INSTALACAO_CONFIG_SISTEMA).']';
    $c->email_padrao = INSTALACAO_CONFIG_EMAIL;
    $c->lingua = INSTALACAO_CONFIG_LINGUA;
    $c->localidade = INSTALACAO_CONFIG_LOCALIDADE;
    $c->cidade = INSTALACAO_CONFIG_CIDADE;
    $c->estado = INSTALACAO_CONFIG_ESTADO;
    $c->palavras = INSTALACAO_CONFIG_PALAVRAS;

    if (!$c->salvar()) {
        $r = false;
        $erros[] = $c->get_erros();
    }

    return $r;
}
