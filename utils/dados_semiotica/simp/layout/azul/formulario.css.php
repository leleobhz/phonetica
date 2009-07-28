<?php
//
// SIMP
// Descricao: Folha de estilos de formularios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.27
// Data: 03/03/2007
// Modificado: 24/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Obter nome do tema
$tema = basename(dirname(__FILE__));

?>
/*
 * FORMULARIOS
 */
.formulario {
  background: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/formulario.jpg) top left no-repeat;
  display: block;
  min-width: 30em;
  padding: 3px;
  text-align: center;
  width: 30em;
<?php
if ($CFG->agent->mozfamily) {
    echo "  min-height: 100px;\n";
    echo "  -moz-border-radius-bottomleft: 20px;\n";
    echo "  -moz-border-radius-bottomright: 20px;\n";
}
?>
}

/* Titulo */
.formulario h3 {
  clear: both;
  cursor: default;
  float: left;
  line-height: 1.5em;
  text-shadow: #667799 2px 2px 4px;
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

.formulario label:hover {
  color: #000099;
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
  background-color: #D5DBE6;
  border: 1px outset #DDE0EE;
  clear: both;
  margin: 15px 1em;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 20px;\n";
}
?>
}

.formulario fieldset {
  float: left;
  width: 26em;
}

.formulario fieldset legend {
  background-color: #FFFFFF;
  border: 1px outset #DDE0EE;
  font-weight: bold;
  padding: 0px 15px 0px 15px;
  margin-left: 15px;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 15px;\n";
}
?>
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
  background-color: #DDDDDD;
  border: 1px outset #DDDDDD;
  color: #000066;
  cursor: pointer;
  font-weight: bold;
  margin: 3px 0 0 0;
  padding: 0.2em 1em 0.2em 1em;
<?php
if ($CFG->agent->mozfamily) {
    echo "  -moz-border-radius: 20px;\n";
}
?>
}

.formulario input[type="submit"] {
  background: #DDDDDD url(<?php echo icone::endereco('enviar') ?>) 10% 50% no-repeat;
  padding-left: 22px;
}

.formulario input[type="reset"] {
  background: #DDDDDD url(<?php echo icone::endereco('restaurar') ?>) 10% 50% no-repeat;
  padding-left: 22px;
}

.formulario input.botao:hover {
  background-color: #CCCCCC;
  color: #000099;
  text-decoration: blink;
}

.formulario input.botao:active {
  background-color: #CCCCCC;
  border: 1px inset #CCCCCC;
  color: #000033;
}

/* Fieldset de campos Radio ou Checkbox */
.formulario label.escolha,
.formulario fieldset.escolha label {
  display: block;
  text-align: left;
}

.formulario label.escolha:hover,
.formulario fieldset.escolha label:hover {
  background-color: #CCD5DD;
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

/*
 * CAMPOS DE TEXTO E SELECT
 */

.formulario input.file,
.formulario input.texto,
.formulario input.ddd,
.formulario input.numero,
.formulario input.ramal,
.formulario input.busca,
.formulario select,
.formulario textarea {
  background-color: #DDDDDD;
  background-repeat: no-repeat;
  background-position: top left;
  border: 1px #DDDDDD inset;
  color: #000000;
  margin: 0px;
  width: 100%;
}

.formulario input.busca {
  width: 50% !important;
}

.formulario input.file:focus,
.formulario input.texto:focus,
.formulario input.ddd:focus,
.formulario input.numero:focus,
.formulario input.ramal:focus,
.formulario input.busca:focus,
.formulario select:focus,
.formulario textarea:focus {
  background-color: #FFFFFF;
  border: 1px #FFFFFF outset;
  color: #000099;
}

.formulario input.texto,
.formulario input.ddd,
.formulario input.numero,
.formulario input.ramal,
.formulario input.busca,
.formulario textarea {
  padding: 1px 3px 1px 3px;
}

.formulario textarea {
  font-family: Arial;
  font-size: 1.01em;
}

.formulario input.texto,
.formulario input.ddd,
.formulario input.numero,
.formulario input.ramal,
.formulario input.busca {
  background-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/input.png);
  background-repeat: repeat-y;
}

.formulario input.texto:focus,
.formulario input.ddd:focus,
.formulario input.numero:focus,
.formulario input.ramal:focus,
.formulario input.busca:focus {
  background-image: url(<?php echo $CFG->wwwlayout.$tema ?>/imgs/input_focus.png);
  background-repeat: repeat-y;
}

/* Geral */
input[disabled="disabled"] {
  color: #FF2222;
}

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

table.tabela tbody th,
table.tabela tbody td {
  font-weight: normal;
}

.formulario h3,
table.tabela thead th {
  background-color: #FFFFFF;
  border: 1px inset #FFFFFF;
  color: #000000;
}

<?php
if ($CFG->agent->ie) {
    echo "table.tabela tbody td,\n";
    echo "table.tabela tbody th {\n";
} else {
    echo "table.tabela tbody tr + tr td,\n";
    echo "table.tabela tbody tr + tr th {\n";
}
?>
  border-top: 1px solid #6688BB;
}

table.tabela tr:hover {
  background-color: #CCD5DD;
  cursor: default;
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
  background-color: #DDE5EE;
  border: 1px outset #DDDDDD;
  margin-top: 5px;
  margin-bottom: 20px;
  margin-left: auto;
  margin-right: auto;
}

.formulario hr {
  border-bottom: 2px #909ADD dotted;
  margin: 1em 0;
}
