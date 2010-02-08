<?php
//
// SIMP
// Descricao: Folha de estilos para Impressao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.12
// Data: 06/06/2007
// Modificado: 23/10/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

/// Armazenar em buffer
setlocale(LC_ALL, 'C');
ob_start();
echo "@charset \"{$CFG->charset}\";\n";
?>
/***************
 * MEDIA PRINT *
 ***************/

* {
  color: #000000;
  background-color: #FFFFFF;
  font-family: Verdana, Arial, sans-serif;
  position: static !important;
}

h1 { font-size: 125%; }
h2 { font-size: 120%; }
h3 { font-size: 115%; }
h4 { font-size: 110%; }
h5 { font-size: 105%; }

p {
  text-align: justify;
  text-indent: 2em;
}

#titulo_pagina * {
  margin: 2px;
}

#conteudo * {
  border: 0px;
  text-decoration: none;
}

.tabela,
.tabela td,
.tabela th {
  border: 1px solid #000000 !important;
  font-size: 85%;
}

#menu,
#navegacao,
#rodape,
.comentario,
.comentario_fechado,
form {
  display: none;
}

<?php

/// Obter buffer
$css = ob_get_contents();
ob_end_clean();

// Cabecalho
header('Content-Type: text/css; charset='.$CFG->charset);
header('Content-Disposition: inline; filename=print.css');
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
compactacao::header($css);
echo $css;
exit(0);
