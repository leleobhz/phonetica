<?php
//
// SIMP
// Descricao: Cria a lista de MP3 no formato XSPF (XML Shareable Playlist Format)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 17/09/2009
// Modificado: 17/09/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

$lista = $_GET['item'];

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$xml .= "<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\">\n";
$xml .= "<trackList>";
foreach ($lista as $chave => $item) {
    $descricao = is_int($chave) ? 'Ajuda' : $chave;
    $xml .= "<track>";
    $xml .= "<location>{$CFG->wwwroot}webservice/fala.mp3.php?arquivo={$item}</location>";
    $xml .= "<annotation>{$descricao}</annotation>";
    $xml .= "</track>\n";
}
$xml .= "</trackList>\n";
$xml .= "</playlist>";

$md5 = md5(implode(':', $lista));

/// Exibir o XML
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename=playlist.{$md5}.xml");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, filemtime($CFG->dirarquivos.'fala/')));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($xml);
echo $xml;
exit(0);

