<?php
//
// SIMP
// Descricao: Arquivo de configuracoes padrao (pre-instalacao do sistema)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.7
// Data: 03/03/2007
// Modificado: 09/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Nome do Sistema
$sistema = 'simp';

// Diretorio raiz
if (php_sapi_name() == 'cli') {
   $dirroot = `simp-config --dirweb`;
} else {
   $dirroot = dirname($_SERVER['SCRIPT_FILENAME']);
}

$rel = $dirroot;
while (! file_exists($rel.'/config.bk.php')) {
    $rel = dirname($rel);
}
$a = str_replace($rel, '', $dirroot);
$dirroot = $rel;

// Dominio e endereco
$dominio = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
$endereco = $dominio.dirname($_SERVER['SCRIPT_NAME']);
$endereco = str_replace($a, '', $endereco);
$wwwroot = 'http://'.str_replace('//', '/', $endereco);
$path = '/';

$p = strlen($dirroot) - 1;
if ($dirroot[$p] != '/') { $dirroot .= '/'; }

$p = strlen($wwwroot) - 1;
if ($wwwroot[$p] != '/') { $wwwroot .= '/'; }

// Versao antes da instalacao
$versao = 0;
$instalacao = 0;

// Flag indicando se o host e' apenas local ou registrado na Web
$localhost = true;

// Codificacao
$charset = 'utf-8';

/// Configuracoes do BD
$bd_config = new stdClass();
$bd_config->sgbd     = '';
$bd_config->servidor = '';
$bd_config->porta    = '';
$bd_config->base     = '';
$bd_config->usuario  = '';
$bd_config->senha    = '';


unset($p, $a, $rel, $endereco);
require_once($dirroot.'var.php');
