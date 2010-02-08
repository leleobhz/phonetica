<?php
//
// SIMP
// Descricao: Folha de estilos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.7
// Data: 07/03/2008
// Modificado: 12/11/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../../config.php');

// Obter nome do tema
$tema = basename(dirname(__FILE__));

// Armazenar em buffer
setlocale(LC_ALL, 'C');
ob_start();
echo "@charset \"{$CFG->charset}\";\n";

// Importar layout do usuario e layout basicas
include_once($CFG->dirlayout.'geral/index.css.php');
include_once($CFG->dirlayout.$tema.'/basico.css.php');
?>

#container {
  background: transparent url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/fundo.jpg) left top repeat-y;
  clear: both;
  display: block;
  margin: 0 auto;
  text-align: left;
  width: 780px !important;
}

/* TOPO */
#titulo_pagina {
  background: transparent url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/topo.jpg) left top no-repeat;
  height: 126px;
  width: 780px;
}

#titulo_pagina h1 {
  margin: 0;
  padding: 20px 30px 10px 40px;
}

#titulo_pagina h1 a {
  color: #707070;
  font-family: Georgia, Arial;
  font-style: italic;
  font-size: 40pt;
  text-decoration: none;
}

#titulo_pagina em {
  color: #808080;
  display: block;
  font-family: Georgia, Arial;
  font-style: italic;
  font-size: 10pt;
  position: relative;
  text-align: center;
  top: -15px;
}

#navegacao {
  border-top: 2px dotted #CCCCCC;
  border-bottom: 2px dotted #CCCCCC;
  display: block;
  margin: 0 30px;
  padding: .2em 0;
  position: relative;
  top: -20px;
}

#conteudo,
#rodape {
  clear: both;
  padding: 0 25px;
}

#conteudo_principal {
  float: left;
  padding: 0 10px;
  width: 530px !important;
}

#conteudo_secundario {
  float: left;
  width: 180px !important;
}

/* Menu */
#menu {
  background-color: #E6E6E6;
  border: 1px outset #E6E6E6;
}

#menu strong {
  background-color: #CCCCDD;
  border: 1px outset #CCCCDD;
  display: block;
  margin: 1px;
  text-align: center;
}

#menu ul {
  list-style-image: none;
  list-style-type: none;
  margin: 0;
  padding: 0;
}

#menu ul li {
  margin: 0;
}

#menu ul li span {
  display: none;
}

#menu ul li a {
  display: block;
  text-align: center;
  text-decoration: none;
}
#menu ul li a:hover {
  outline: 1px dotted #303040;
}

#menu div {
  margin-top: .1em;
  border-top: 2px dotted #CCCCCC;
}

#menu p {
  clear: both;
  margin: .3em 0;
  text-align: center;
}

/* Icones do Menu */
#menu #login_usuario,
#menu #opcoes,
#menu #ajuda,
#menu #saida {
  clear: none;
  display: block;
  float: left;
  height: 20px;
  width: 40px;
  text-indent: -1000px;
}

#menu #rodape_menu span {
  display: none;
}

#menu #login_usuario {
  background: url(<?php echo icone::endereco('menu_usuario') ?>) 50% 50% no-repeat;
}

#menu #opcoes {
  background: url(<?php echo icone::endereco('menu_opcoes') ?>) 50% 50% no-repeat;
}

#menu #ajuda {
  background: url(<?php echo icone::endereco('menu_ajuda') ?>) 50% 50% no-repeat;
}

#menu #saida {
  background: url(<?php echo icone::endereco('menu_sair') ?>) 50% 50% no-repeat;
}

/* Centro
#centro {
}
*/

/* RODAPE */
#rodape {
  background: transparent url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/rodape.jpg) left bottom no-repeat;
  color: #707070;
  height: 80px;
  padding-top: 5px;
  padding-bottom: 20px;
  text-align: center;
  width: auto;
}

#rodape p,
#rodape div,
#rodape acronym {
  font-size: 60%;
}

#rodape #voltar_topo {
  background: #808080;
  border-top: 1px outset #808080;
  border-left: 1px outset #808080;
  bottom: 0px;
  color: #FFFFFF;
  cursor: pointer;
  padding: .1em 1em;
  opacity: 0.5;
  position: fixed;
  right: 0px;
}

#rodape #voltar_topo:hover {
  opacity: 1;
}

#rodape address {
  display: inline;
}

/* Invisiveis */
#container > hr,
#conteudo > hr {
  display: none;
}
#menu h2,
#rodape h2 {
  display: none;
}

/* CURSORES */
#titulo_pagina em,
#menu h2,
#menu strong,
#menu ul + div,
#menu p + div,
#navegacao h2,
#navegacao strong,
#navegacao span,
#navegacao em,
#rodape {
  cursor: default;
}

<?php

// Estilos Especificos
if ($CFG->agent->mozfamily) {
    include_once($CFG->dirlayout.$tema.'/mozfamily.css.php');
} elseif ($CFG->agent->navegador == 'Opera') {
    include_once($CFG->dirlayout.$tema.'/opera.css.php');
} elseif ($CFG->agent->ie && $CFG->agent->ie < 7) {
    include_once($CFG->dirlayout.$tema.'/ie6.css.php');
}

// Estilos de formularios
include_once($CFG->dirlayout.$tema.'/formulario.css.php');

/// Obter buffer
$css = ob_get_contents();
ob_end_clean();

// Cabecalho
header('Content-Type: text/css; charset='.$CFG->charset);
header('Content-Disposition: inline; filename='.$tema.'.css');
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
