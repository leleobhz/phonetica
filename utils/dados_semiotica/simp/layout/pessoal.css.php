<?php
//
// SIMP
// Descricao: Configuracoes pessoais da Folha de estilos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 11/03/2008
// Modificado: 29/07/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

setlocale(LC_ALL, 'C');

$style = array();

if ($CFG->pessoal->tamanho != '100%') {
    $style['body, input, textarea, select']['font-size'] = $CFG->pessoal->tamanho;
}
if ($CFG->pessoal->fonte != 'padrao') {
    $style['body, input, textarea, select']['font-family'] = $CFG->pessoal->fonte;
}

if ($CFG->pessoal->sem_imagens) {
    $style['*']['background-image'] = 'none !important';
}
if ($CFG->pessoal->sem_transparencia) {
    $style['*']['opacity'] = '1 !important';
}

// Cabecalho
header("X-Framework: SIMP/".VERSAO_SIMP);
header('Content-Type: text/css; charset='.$CFG->charset);
header('Content-Disposition: inline; filename=pessoal.css');
header('Content-Language: '.$CFG->lingua);
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

echo "@charset \"{$CFG->charset}\";\n";
foreach ($style as $seletor => $propriedades) {
    echo $seletor.' { ';
    foreach ($propriedades as $propriedade => $valor) {
        echo $propriedade.': '.$valor.'; ';
    }
    echo "}\n";
}
