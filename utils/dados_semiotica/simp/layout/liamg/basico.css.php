<?php
//
// SIMP
// Descricao: Folha de estilos basica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 06/05/2008
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
  z-index: 1;
}

/* Body */
body {
  background-color: #FFFFFF;
  direction: ltr;
  font-family: Arial, Verdana, Helvetica, sans-serif;
  margin: 0;
  min-width: 780px;
}

/* Links */
a,
a:active,
a:hover,
a:visited {
  color: #0000FF;
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

/* Titulos */
h2 {
  border-bottom: 1px solid #909090;
  color: #303030;
}

/* Linhas */
hr {
  border: 0;
  border-top: 1px solid #C3D9FF;
  margin-top: 4px;
  margin-bottom: 2px;
}

/***********
 * CLASSES *
 ***********/

/* Dados */
.dados {
  background-color: #FFFFFF;
  border: 1px solid #CCCCCC;
}

.dados .titulo {
  background-color: #C3D9FF;
  border-bottom: 1px solid #CCCCCC;
  margin: 0;
}

.dados .rodape {
  border-top: 1px solid #CCCCCC;
  margin: 0;
}

/*
 * FIELDSET
 */
.dados fieldset {
  border: 1px solid #CCCCCC;
}

.dados fieldset legend {
  background-color: #C3D9FF;
  border: 1px solid #CCCCCC;
}

/* Observacoes */
.observacao {
  border-top: 1px solid #C3D9FF;
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
  border: 2px dotted #C3D9FF;
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
  border: 1px dotted #C3D9FF;
  color: #303030;
}

/* Lista de Opcoes */
.opcoes {
  background-color: #C3D9FF;
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
