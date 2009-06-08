<?php
//
// SIMP
// Descricao: Arquivo para gerar o arquivo INI de instalacao dos arquivos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 22/01/2009
// Modificado: 22/01/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

$ini = objeto::get_objeto('arquivo')->get_ini();

// Cabecalho
header('Content-Type: text/plain; charset='.$CFG->charset);
header('Content-Disposition: attachment; filename=arquivo.ini');
header('Content-Language: '.$CFG->lingua);
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
compactacao::header($ini);
echo $ini;
exit(0);
