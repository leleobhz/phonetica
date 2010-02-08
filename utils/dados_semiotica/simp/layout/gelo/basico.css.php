<?php
//
// SIMP
// Descricao: Folha de estilos basica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.8
// Data: 19/02/2008
// Modificado: 22/05/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
if (!isset($CFG)) { exit(0); }

// Obter nome do tema
$tema = basename(dirname(__FILE__));

setlocale(LC_ALL, 'C');
?>
/********************
 * ATRIBUTOS GERAIS *
 ********************/

* {
  z-index: 1;
}

/* Body */
body {
  background-color: #FFFFFF;
  direction: ltr;
  font-family: Arial, Verdana, Helvetica, sans-serif;
  margin: 0px 0px 5px 0px;
  min-width: 780px;
}

/* Links */
a {
  color: #337733;
  text-decoration: none;
}
a:hover {
  color: #44AA44;
  cursor: pointer;
  text-decoration: underline;
}

a.inserir {
  background-image: url(<?php echo icone::endereco('adicionar') ?>);
  background-position: 0% 50%;
  background-repeat: no-repeat;
  padding-left: 20px !important;
}

a.importar {
  background-image: url(<?php echo icone::endereco('importar') ?>);
  background-position: 0% 50%;
  background-repeat: no-repeat;
  padding-left: 20px !important;
}

a.inconsistencia {
  background-image: url(<?php echo icone::endereco('bug') ?>);
  background-position: 0% 50%;
  background-repeat: no-repeat;
  padding-left: 20px !important;
}

a.ini {
  background-image: url(<?php echo icone::endereco('arq_ini') ?>);
  background-position: 0% 50%;
  background-repeat: no-repeat;
  padding-left: 20px !important;
}

a img {
  border: 0px;
  opacity: 0.5;
}

a:hover img {
  opacity: 1;
}

dd:active {
  color: #009900;
}


/* Linhas */
hr {
  border-top: 0px;
  border-left: 0px;
  border-right: 0px;
  margin-top: 4px;
  margin-bottom: 2px;
}

h2, hr {
  border-bottom: 1px #90DD9A solid;
  text-shadow: #AAAAAA 2px 1px 2px;
}

p:active {
  color: #00AA00;
}

/* Listas */
ul {
  list-style-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/li.gif);
}

/***********
 * CLASSES *
 ***********/

/* Dados */
.dados {
  background: #EEEEEE url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/formulario.jpg) top left no-repeat;
  border: 1px outset #CCCCCC;
}

.dados .titulo {
  background-color: #F5FFF5;
  border: 1px outset #F5FFF5;
}

.dados .rodape {
  border-top: 1px solid #CCCCCC;
}

/*
 * FIELDSET
 */
.dados fieldset {
  background-color: #E5E5E5;
  border: 1px outset #C5C5C5;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 10px;\n";
}
?>
}

.dados fieldset legend {
  background-color: #FFFFFF;
  border: 1px outset #DDDDDD;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 10px;\n";
}
?>
}

/* Observacoes */
.observacao {
  background: transparent url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/observacao.jpg) left top no-repeat;
}

/* Comentarios */
.comentario {
  border: 1px dotted #909ADD;
  background-color: #FFFFFF;
  color: #000055;
}

/* Comentario de Ajuda */

/* Texto da ajuda */
.bloco_ajuda_aberto blockquote,
.bloco_ajuda_fechado blockquote {
  color: #000066;
}
.bloco_ajuda_aberto blockquote {
  border: 2px dotted #909ADD;
  background-color: #FFFFFF;
}

/* Botao de ajuda */
.bloco_ajuda_aberto a.ajuda {
  background-color: #DDDDDD;
  border: 1px inset #DDDDDD;
}

.bloco_ajuda_fechado a.ajuda {
  background-color: #EEEEEE;
  border: 1px outset #EEEEEE;
}

/* Informacoes */
.info {
  border: 1px dotted #90DD9A;
  color: #006600;
}

/* Lista de Opcoes */
.opcoes {
  background-color: #EEEEEE;
  border: 1px outset #CCCCCC;
}

.opcoes span {
  color: #AAB0BB;
}

/* Lista de busca */
ul.lista_busca span {
  color: #808080;
}

/* Inativos */
.inativo {
  color: #AAAAAA;
}
