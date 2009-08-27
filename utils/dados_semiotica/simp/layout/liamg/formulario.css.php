<?php
//
// SIMP
// Descricao: Folha de estilos de formularios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 06/05/2008
// Modificado: 25/08/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Obter nome do tema
$tema = basename(dirname(__FILE__));
?>
/*
 * FORMULARIOS
 */
.formulario {
  display: block;
  min-width: 30em;
  padding: 3px;
  text-align: center;
  width: 30em;
}

/* Titulo */
.formulario h3 {
  clear: both;
  cursor: default;
  float: left;
  line-height: 1.5em;
  width: 99%;
}

.formulario * + h3 {
  margin-top: .7em;
}

.formulario p,
.formulario blockquote {
  text-align: left !important;
}

/* Espaco para label e campo */
.formulario .campo {
  clear: both;
  float: left;
  margin: 1.5px 0;
  padding: 0;
  width: 100%;
}

/* Label */
.formulario label {
  cursor: pointer;
  font-weight: bold;
  margin: 0;
  padding-left: 2%;
}

.formulario div.campo label {
  clear: left;
  display: block;
  float: left;
  padding: 0 2% 0 0;
  text-align: right;
  width: 25%;
}

.formulario div.campo label:after {
  content: ':';
}

/* Fieldset */
.formulario fieldset {
  background-color: #FFFFFF;
  border: 1px solid #CCCCCC;
  clear: both;
  margin: 15px 1em;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 10px;\n";
}
?>
}

.formulario fieldset {
  float: left;
  width: 26em;
}

.formulario fieldset legend {
  background-color: #C3D9FF;
  border: 1px solid #CCCCCC;
  font-weight: bold;
  padding: 0px 15px 0px 15px;
}

/* Espaco para o campo */
.formulario div.campo div {
  clear: none;
  float: left;
  height: auto;
  margin: 0;
  padding: 0;
  text-align: left;
  vertical-align: top;
  width: 70%;
}


/*
 * BOTOES
 */
.formulario input.botao {
  cursor: pointer;
  font-weight: bold;
  margin: 3px 0 0 0;
}

/* Fieldset de campos Radio ou Checkbox */
.formulario label.escolha,
.formulario fieldset.escolha label {
  display: block;
  text-align: left;
}

/* CAMPO BOOL */
.formulario .campo_bool {
  display: block;
  padding-left: 40px;
  text-align: left;
}
.formulario .campo_bool * {
  clear: none !important;
  display: inline !important;
  float: none !important;
  margin-right: 1em;
  width: auto;
}
.formulario .campo_bool label:after {
  content: '' !important;
}

.formulario input.busca {
  width: 50% !important;
}

/* Geral */
.formulario .block {
  clear: both;
  display: block;
  padding: 3px 5px;
  text-align: center;
  width: 98% !important;
}

.formulario .block .comentario {
  background: transparent url(<?php echo icone::endereco('aviso') ?>) 0% 50% no-repeat;
  margin: 0px 10px !important;
  padding-left: 25px;
  text-align: justify !important;
}


/*
 * TABELAS
 */
table.tabela {
  border-spacing: 0px;
}

table.tabela caption {
  font-weight: bold;
  margin: 2px auto;
}

table.tabela th,
table.tabela td {
  text-align: center;
  padding: .1em .5em;
  vertical-align: middle;
}

table.indice tbody td {
  border: 1px solid #DDDDDD;
  font-size: .8em;
  padding: .1em .6em;
  width: 33%;
}

table.tabela tbody th,
table.tabela tbody td {
  font-weight: normal;
}

.formulario h3,
table.tabela thead th {
  background-color: #C3D9FF;
  border: 1px solid #CCCCCC;
  color: #000000;
}

/* CAMPOS CAPTCHA */
#area_captcha .captcha_imagem * {
  float: left;
}
#area_captcha .captcha_imagem input {
  margin-left: 1em;
  margin-top: .3em;
}
#area_captcha .captcha_imagem input.texto {
  width: 7em;
}
#area_captcha .captcha_imagem input.botao {
  padding: 2px 3px;
}
#img_captcha {
  border: 1px inset #CCCCCC;
}

/*
 * FORMULARIOS E TABELAS
 */
.formulario,
table.tabela {
  background-color: #FFFFFF;
  border: 1px outset #CCCCCC;
  margin-top: 5px;
  margin-bottom: 20px;
  margin-left: auto;
  margin-right: auto;
}

.formulario hr {
  border-bottom: 1px solid #CCCCCC;
  margin: 1em 0;
}
