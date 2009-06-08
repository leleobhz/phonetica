<?php
//
// SIMP
// Descricao: Arquivo para gerar o arquivo INI de instalacao das permissoes de determinado grupo
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 22/01/2009
// Modificado: 27/02/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

$grupo = util::get_entidade('grupo');
$ini = objeto::get_objeto('permissao')->get_ini($grupo, $nome_arquivo);

// Cabecalho
header('Content-Type: text/plain; charset='.$CFG->charset);
header('Content-Disposition: attachment; filename='.$nome_arquivo);
header('Content-Language: '.$CFG->lingua);
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
compactacao::header($ini);
echo $ini;
exit(0);
