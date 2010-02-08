<?php
//
// SIMP
// Descricao: Arquivo que mescla todos arquivos js em um compactado
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.9
// Data: 22/01/2008
// Modificado: 07/10/2009
// License: LICENSE.TXT
// Copyright (C) 2008  Rubens Takiguti Ribeiro
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

$diretorio = $CFG->dirroot.'javascript/';

$ie = $CFG->agent->ie ? 'true' : 'false';
$localhost = $CFG->localhost ? 'true' : 'false';

$buffer = '/* Arquivo Compactado */'.
          '{'.
          "var script_local='{$CFG->wwwroot}javascript/javascript.js.php';".
          "var wwwroot='{$CFG->wwwroot}';".
          "var dominio='{$CFG->dominio}';".
          "var dominio_cookies=".($CFG->dominio_cookies ? "'".$CFG->dominio_cookies."'" : 'false').";".
          "var localhost={$localhost};".
          "var path='{$CFG->path}';".
          "var navegador='".strtolower($CFG->agent->navegador)."';".
          "var msie={$ie};".
          '}';

$dir = opendir($diretorio);
if ($dir) {
    while (($item = readdir($dir)) !== false) {
        if (preg_match('/^[A-z0-9-_]+\.js$/', $item)) {
            $buffer .= trim(file_get_contents($diretorio.$item));
            $vt_last[] = filemtime($diretorio.$item);
        }
    }
    closedir($dir);
}

// Data de ultima modificacao
$last = max($vt_last);

// Cabecalho
setlocale(LC_ALL, 'C');
header('Content-Type: text/javascript; charset='.$CFG->charset);
header('Content-Disposition: inline; filename=javascript.js');
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, $last));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($buffer);

echo $buffer;
exit(0);
