<?php
//
// SIMP
// Descricao: Folha de estilos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.18
// Data: 03/03/2007
// Modificado: 12/11/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
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

/*************************
 * ATRIBUTOS ESTRUTURAIS *
 *************************/
#titulo_pagina,
#navegacao,
#rodape,
#conteudo {
  clear: both;
  display: block;
  margin: 0px;
}

/*
 * TITULO
 */
#titulo_pagina {
  background: #CCDDFF url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/fundo.jpg) top left repeat-x;
  border-bottom: 1px solid #CCDDFF;
  color: #000066;
  max-height: 75px;
  overflow: hidden;
  padding: 0px;
  text-shadow: #667799 3px 3px 4px;
  white-space: nowrap;
  width: 100%;
}
#titulo_pagina h1 {
  background-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/titulo.jpg);
  background-position: top left;
  background-repeat: no-repeat;
  font-family: Garamond, Verdana, Arial, sans-serif;
  font-size: 50px;
  margin: 0px;
  padding: 0px;
  padding-left: 250px;
}

#titulo_pagina h1,
#titulo_pagina em {
  display: block;
  float: left;
  height: 75px;
  line-height: 75px;
  position: relative;
}
#titulo_pagina em {
  font-size: 16px;
  font-variant: small-caps;
  font-weight: bolder;
  padding-left: 20px;
  white-space: nowrap;
}

#titulo_pagina h1 a {
  color: #000066;
  text-decoration: none;
}

#titulo_pagina h1 a:hover {
  color: #224499;
}


/*
 * NAVEGACAO
 */
#navegacao {
  background: #343C8D url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/navegacao.jpg) bottom left repeat-x;
  color: #E5EAEF;
  display: block;
  font-size: 14px;
  font-weight: bolder;
  margin-bottom: 10px;
  padding: 2px 20px 12px 20px;
  text-shadow: #000033 2px 2px 2px;
}

#navegacao * {
  line-height: 22px;
  padding: 0 .4em;
  white-space: nowrap;
}

#navegacao a {
  color: #C5CAFF;
}

#navegacao a:hover {
  color: #E5EAEF;
  text-decoration: none;
}

/*
 * CONTEUDO
 */
#conteudo {
  float: left;
  min-height: 230px;
  padding: 0px;
  margin-bottom: 20px;
  width: 100%;
}

#conteudo_principal {
  float: left;
  min-width: 539px;
  width: 70%;
}
#conteudo_secundario {
  float: left;
  min-width: 231px;
  width: 30%;
}


/*
 * MENU E CENTRO
 */
#centro,
#menu {
  margin: 0 auto;
  padding: 2px;
}

#centro {
  margin-left: 0px;
  padding: 0px 10px 5px 10px;
  width: 95%;
}

#menu h2 {
  font-variant: small-caps;
  text-align: center;
}

#menu {
  background: #EEEEEE url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/menu.png) top left repeat-y;
  border: 1px outset #DDDDDD;
  width: 90%;
}

#menu strong {
  background: #FFFFFF url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/input_focus.png) top left repeat-y;
  border: 1px inset #FFFFFF;
  display: block;
  font-variant: small-caps;
  text-align: center;
}

#menu ul {
  list-style-type: none;
  list-style-image: none;
  margin: 0 0 5px 0;
  padding: 0;
}

#menu ul li {
  margin-top: 2px;
  padding: 0 4px 0 4px;
}

#menu a {
  color: #2266AA;
}
#menu a:hover {
  color: #004490;
}

#menu ul li a {
  background: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/item_menu.gif) 0% 60% no-repeat;
  display: inline;
  font-weight: 400;
  min-height: 18px;
  padding-left: 20px;
}
#menu ul li a:hover {
  background-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/item_menu_hover.gif);
}

#menu ul + div,
#menu p + div {
  border-top: 2px #909ADD dotted;
  color: #606677;
  font-size: 80%;
  text-align: center;
}
#menu ul + div p {
  display: block;
  margin: 5px;
}
#menu ul + div p a {
  display: inline;
  line-height: 20px;
  padding: 2px 0;
}

#menu #login_usuario {
  background: url(<?php echo icone::endereco('menu_usuario') ?>) 0% 60% no-repeat;
  padding-left: 17px;
}

#menu #opcoes {
  background: url(<?php echo icone::endereco('menu_opcoes') ?>) 0% 60% no-repeat;
  padding-left: 20px;
}

#menu #ajuda {
  background: url(<?php echo icone::endereco('menu_ajuda') ?>) 0% 60% no-repeat;
  padding-left: 20px;
}

#menu #saida {
  background: url(<?php echo icone::endereco('menu_sair') ?>) 0% 60% no-repeat;
  padding-left: 20px;
}

/*
 * RODAPE
 */
#rodape {
  border-top: 1px solid #909ADD;
  color: #606070;
  float: left;
  font-size: 70%;
  letter-spacing: 1px;
  margin-top: 27px;
  margin-bottom: 10px;
  text-align: center;
  width: 100%;
}

#rodape address {
  display: inline;
}

#rodape #voltar_topo {
  border: 0px;
  background: #FFFFFF url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/topo.png) bottom left no-repeat;
  display: block;
  font-size: 0px;
  height: 27px;
  max-height: 27px;
  left: 80%;
  line-height: 0px;
  margin: -27px 0px 0px 0px;
  padding: 0px;
  position: absolute;
  text-indent: -10000px;
  width: 36px;
}

#rodape div + p {
  font-size: 90%;
  font-variant: small-caps;
}

/*
 * ELEMENTOS INVISIVEIS
 */
#container > hr,
#conteudo > hr,
#navegacao strong,
#menu h2,
#menu ul li span,
#rodape h2 {
  display: none;
}


/*
 * CURSORES
 */
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
