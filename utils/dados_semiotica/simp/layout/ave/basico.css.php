<?php
//
// SIMP
// Descricao: Folha de estilos basica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 07/03/2008
// Modificado: 04/03/2009
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
  font-size: small;
  margin: 0;
  padding: 0;
  z-index: 1;
}

/* Body */
body {
  background-color: #CCCCCC;
  direction: ltr;
  font-family: Arial, Verdana, Helvetica, sans-serif;
  height: 100%;
  margin: 0;
  padding: 0;
  position: static;
  text-align: center;
}

/* Links */
a {
  color: #202060;
}
a:hover {
  color: #302090;
}
a:visited {
  color: #302060;
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
}

dd:active {
  color: #000066;
}

/* Titulos */
h2 {
  border-top: 3px solid #332288;
  border-bottom: 1px solid #332288;
  padding: .2em;
}

p:active {
  color: #000099;
}

/* Listas */
ul {
  list-style-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/li.gif);
}

ul li,
ol li {
  margin-left: 3em;
}

/* Classes*/

/* Dados */
.dados {
  background-color: #F5F5F7;
  border: 1px outset #CCCCCC;
}

.dados .titulo {
  border-top: 3px solid #332288;
  border-bottom: 1px solid #332288;
}

.dados .rodape {
  border-top: 1px solid #332288;
  border-bottom: 3px solid #332288;
}

/* FIELDSET */
.dados fieldset {
  background-color: #E5E5EE;
  border: 1px outset #C5C5C5;
}

.dados fieldset legend {
  background-color: #FFFFFF;
  border: 1px outset #DDDDDD;
}

/* Observacoes */
.observacao {
  border-top: 1px solid #332288;
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
  background-color: #FFFFFF;
  border: 2px dotted #909ADD;
  border-bottom: none;
}

.bloco_ajuda_fechado a.ajuda {
  border: 2px solid transparent;
}

/* Informacoes */
.info {
  border: 1px dotted #909ADD;
  color: #000066;
}

/* Lista de Opcoes */
.opcoes {
  background-color: #EEEEEE;
  border-top: 3px solid #332288;
  border-bottom: 1px solid #332288;
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
