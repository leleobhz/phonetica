<?php
//
// SIMP
// Descricao: Script que exibe o conteudo de um arquivo PHP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 17/01/2008
// Modificado: 07/02/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Bloquear caso necessario
$modulo = util::get_modulo(__FILE__);
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Exibir pagina
$a = base64_decode($_GET['a']);

header('Content-type: text/html; charset=UTF-8');
echo '<html>';
echo '<head>';
echo '<title>'.basename($a).'</title>';
echo '<style type="text/css">';
echo 'body { background-color: #F5F5F5; margin: 1em; }';
echo '* { font-size: 1.05em; }';
echo '</style>';
echo '<body>';
if (eregi('.php', $a)) {
    echo '<code style="font-size: 7.5pt;">';
    highlight_file($a);
    echo '</code>';
} else {
    echo '<pre>';
    readfile($a);
    echo '</pre>';
}
echo '</body></html>';

