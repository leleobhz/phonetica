<?php
//
// SIMP
// Descricao: Cria uma MP3
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 17/09/2009
// Modificado: 18/09/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

$arquivo = $_GET['arquivo'];
$caminho_arquivo = $CFG->dirarquivos.'fala/'.$arquivo;
if (!is_file($caminho_arquivo)) {
    trigger_error('Arquivo nao encontrado: '.$caminho_arquivo, E_USER_WARNING);
    exit(1);
}
$conteudo = file_get_contents($caminho_arquivo);

/// Exibir o XML
setlocale(LC_ALL, 'C');
header('Content-type: audio/mpeg');
header("Content-Disposition: inline; filename={$arquivo}.mp3");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, filemtime($CFG->dirarquivos.'fala/')));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($conteudo);
echo $conteudo;
exit(0);

