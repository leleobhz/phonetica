<?php
//
// SIMP
// Descricao: Folha de estilos para dispositivos moveis
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.13
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
/******************
 * MEDIA HANDHELD *
 ******************/

* {
  font-family: Arial, Verdana, sans-serif;
  margin: 0px;
  padding: 0px;
  position: static !important;
}

body {
  font-size: 80%;
  max-width: 240px;
}

h1 { font-size: 110%; }
h2 { font-size: 105%; }
h3 { font-size: 100%; }
h4 { font-size: 95%; }
h5 { font-size: 90%; }

#titulo_pagina,
h1, h2, h3, h4, h5 {
  padding: 1px 3px;
}

.aviso {
  color: #009900;
}

.erro {
  color: #DD0000;
}

.comentario,
.comentario_fechado {
  color: #000066;
}

/*
 * TITULO
 */
#titulo_pagina {
  background-color: #CCDDFF;
}

#titulo_pagina h1 a {
  color: #000066;
}

/*
 * MENU
 */
#menu strong {
  display: block;
}
#menu strong:after {
  content: ':';
}

#menu ul a {
  color: #000000;
  display: block;
  text-decoration: none;
}
#menu strong,
#menu ul li a {
  border-bottom: 1px solid #CCDDFF;
  padding-left: 4px;
}

#menu ul li a:hover,
#menu ul li a:active {
  background-color: #6688BB;
  color: #FFFFFF;
}

#menu ul + div {
  text-align: center;
}

/*
 * Centro
 */
#centro {
  background-color: #FFFFEE;
  margin-top: 5px;
  padding: 3px;
}

#centro h2 {
  border: 1px solid #5577B0;
  background-color: #6688BB;
  color: #FFFFAA;
}
#centro p {
  padding: 2px;
}


/*
 * Formularios
 */

fieldset,
fieldset legend {
  border: 1px solid #CCCCCC;
}

fieldset legend {
  margin: 0em 2em 0em 2em;
  padding: 0px 2px 0px 2px;
}

input,
textarea,
select {
  max-width: 234px;
}

label,
.label {
  display: block;
}

label:after {
  content: ':';
}

.calendario {
  background-color: #FFFFFF;
  border: 1px solid #000000;
  margin: 2px auto;
}

/* Abas */
.abas .nomes_abas {
  margin: 3px 0;
}
.abas .nomes_abas a {
  background-color: #CCCCCC;
  border: 1px outset #CCCCCC;
  padding: 1px .8em;
}

.abas .nomes_abas a.ativa {
  background-color: #AAAAAA;
  border: 1px inset #AAAAAA;
}

/*
 * Itens Invisiveis
 */
.hide,
.calendario .dados_eventos,
.abas .nomes_abas span,
#menu h2,
#menu ul span,
#menu ul + div p + p,
#titulo_pagina em,
#navegacao,
#rodape,
hr,
#formulario p strong {
  display: none;
}

<?php

/// Obter buffer
$css = ob_get_contents();
ob_end_clean();

// Cabecalho
header('Content-Type: text/css; charset='.$CFG->charset);
header('Content-Disposition: inline; filename=handheld.css');
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
compactacao::header($css);

/// Imprimir conteudo
echo $css;
exit(0);
