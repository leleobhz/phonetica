<?php
//
// SIMP
// Descricao: Script de busca o progresso de uma operacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 27/01/2010
// Modificado: 29/01/2010
// Copyright (C) 2010  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');


/// Obter progresso
$id = util::get_dado('id', 'string');
list($progresso, $inicio) = progresso::consultar($id);
$agora = time();

if ($inicio && $progresso) {
    $tempo_gasto    = intval($agora - $inicio);
    $tempo_estimado = intval($tempo_gasto * 100 / $progresso);
    $tempo_restante = intval($tempo_estimado - $tempo_gasto);
} else {
    $tempo_gasto    = 0;
    $tempo_estimado = '?';
    $tempo_restante = '?';
}


/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/html; charset='.$CFG->charset);
header("Content-Disposition: inline; filename=progresso_{$id}.html");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, $CFG->time));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
if ($progresso < 100) {
    header('Refresh: 3');
    $meta = '<meta http-equiv="refresh" content="3">';
} else {
    $meta = '';
}
setlocale(LC_ALL, $CFG->localidade);
echo <<<HTML
<html>
<head>
<title>Progresso</title>
{$meta}
</head>
<body>
HTML;

if ($inicio) {
    if ($progresso == 100) {
        echo <<<HTML
<p><strong>Progresso:</strong> <span>100%</span></p>
<div style="border 1px inset #CCCCCC; background-color: #CCCCCC; width: 100%px; height: 20px;"><div style="border: 1px outset #000033; background-color: #000033; width: 100%; height: 18px;"></div></div>
<p><strong>Tempo gasto:</strong> <span>{$tempo_gasto} segundos</span></p>
HTML;
    } else {
        echo <<<HTML
<p><strong>Progresso:</strong> <span>{$progresso}%</span></p>
<div style="border 1px inset #CCCCCC; background-color: #CCCCCC; width: 100%px; height: 20px;"><div style="border: 1px outset #000033; background-color: #000033; width: {$progresso}%; height: 18px;"></div></div>
<p><strong>Tempo gasto:</strong> <span>{$tempo_gasto} segundos</span></p>
<p><strong>Tempo estimado:</strong> <span>{$tempo_estimado} segundos</span></p>
<p><strong>Tempo restante estimado:</strong> <span>{$tempo_restante} segundos</span></p>
HTML;
    }
} else {
    echo <<<HTML
<p><strong>Progresso:</strong> <span>n&atilde;o iniciado</span></p>
HTML;
}

echo <<<HTML
</body>
</html>
HTML;
exit(0);
