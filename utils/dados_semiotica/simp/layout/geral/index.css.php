<?php
//
// SIMP
// Descricao: Folha de estilos geral
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.9
// Data: 11/03/2008
// Modificado: 19/02/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

// Armazenar em buffer
setlocale(LC_ALL, 'C');

?>

/* Links RSS */
a.rss {
  background-image: url(<?php echo icone::endereco('rss') ?>);
  background-position: 0% 50%;
  background-repeat: no-repeat;
  line-height: 1em;
  padding-left: 20px !important;
}

a.rss:hover {
  color: #FFAB1B;
}

/* Listas de Definicoes */
dt {
  font-weight: bolder;
}
dt:after {
  content: ':';
}

/* Titulos */
h2 {
  font-size: 100%;
  margin-top: 0px;
  margin-bottom: 4px;
}

h3 {
  font-size: 95%;
  margin-top: 0px;
  margin-bottom: 4px;
}

/* Acronimos e Abreviacoes */
acronym,
abbr {
  border-bottom: 1px dotted;
  cursor: help;
}
acronym:hover,
abbr:hover {
  border-bottom: 0px;
  border-top: 1px dotted;
}

/* Paragrafos */
p {
  margin: 5px;
}

/* Texto Pre-formatado*/
pre,
code {
  font-family: "Courier New", Monospace;
  letter-spacing: 1px;
  margin: 1px 0px 1px 0px;
}

ul, ol {
  margin-top: 2px;
  margin-bottom: 3px;
}

/***********
 * CLASSES *
 ***********/

/* Gerais */
.clear {
  clear: both;
}

p.fim,
br.clear {
  clear: both !important;
  display: block !important;
  font-size: 0;
  height: 0;
  line-height: 0;
  margin: 0;
  padding: 0;
}

br.clear {
  float: none !important;
}

.hide {
  display: none !important;
}

/* Cores de relevancia */
.verde {
  color: #006600;
}
.amarelo {
  color: #999900;
}
.vermelho {
  color: #CC0000;
}

/* Dados */
.dados {
  display: block;
  margin: 5px auto 20px auto;
  padding: 5px 10px;
  width: 80%;
}

.dados .titulo {
  clear: both;
  display: block;
  margin-bottom: 5px;
  padding-bottom: 3px;
  padding-top: 3px;
  text-align: center;
}

.dados .rodape {
  clear: both;
  display: block;
  padding: .1em 0;
}

.dados .rodape span {
  display: block;
  float: left;
  width: 50%;
}

.dados ul.relacionamento li strong {
  display: none;
}

/* Listas de dados */
.lista .linha {
  clear: both;
  float: left;
  width: 100%;
}

.lista .label,
.lista .inativo {
  display: block;
  float: left;
  font-weight: bolder;
  line-height: 1.5em;
  text-align: right;
  width: 70%;
}

.lista .label:after,
.lista .inativo:after {
  content: ':';
}

.lista .opcoes {
  border: 0;
  float: left;
  padding: 0;
  width: 29%;
}

.lista .opcoes img {
  margin: 0 0 0 10px;
}

.lista .opcoes a {
  padding: 0 2px 0 0;
}

.lista .opcoes a + span,
.lista .label + div strong,
.lista .inativo + div strong,
.lista + hr {
  display: none;
}

/*
 * FIELDSET
 */
fieldset legend {
  cursor: default;
}

.dados fieldset {
  clear: both;
  margin: 15px 1em;
}

.dados fieldset legend {
  font-weight: bold;
  padding: 0px 15px 0px 15px;
  margin-left: 15px;
}

/* Observacoes */
.observacao {
  margin: 1em 0;
  padding-top: .2em;
}

/* Erros, Avisos e Comentarios */
div.erro,
div.aviso,
.comentario {
  display: block;
  margin: 10px;
  padding: 5px;
}

/* Erros, Avisos */
div.erro,
div.aviso {
  cursor: pointer;
  max-height: 400px;
  min-height: 3em;
  opacity: 0.92;
  overflow: auto;
  text-align: left;
  top: 0px !important;
  width: 40em;
  z-index: 1000;
}

div.erro:hover,
div.aviso:hover {
  opacity: 1;
}

div.erro:after,
div.aviso:after {
  content: 'Clique para Fechar';
  display: block;
  font-size: x-small;
  opacity: 0.8;
  text-align: center;
}

.erro .fechar,
.aviso .fechar {
  font-size: x-small;
  float: right;
}

/* Erros */
.erro {
  background-color: #FFF5F5;
  border: 1px outset #FF0000;
  color: #991111;
}
.erro ul {
  list-style-image: url(<?php echo icone::endereco('li_erro') ?>);
}
td.erro {
  margin: 0px;
  padding: 1px;
}

/* Avisos */
.aviso {
  background-color: #F5FFF5;
  border: 1px outset #009900;
  color: #009900;
}

.aviso ul {
  list-style-image: url(<?php echo icone::endereco('li_aviso') ?>);
}

/* Comentarios */
.comentario {
  clear: both;
  display: block;
  font-size: small;
  text-align: justify;
}

/* Comentario de Ajuda */
.bloco_ajuda_aberto,
.bloco_ajuda_fechado {
  margin-bottom: 1em;
  text-align: right;
}

/* Texto da ajuda */
.bloco_ajuda_aberto blockquote,
.bloco_ajuda_fechado blockquote {
  font-size: 90%;
  padding: 1px;
}
.bloco_ajuda_aberto blockquote {
  clear: both;
  display: block;
  text-align: justify;
}
.bloco_ajuda_fechado blockquote {
  border: 0;
  display: none;
}

/* Acessibilidade */
.acessivel {
  background: transparent  url(<?php echo icone::endereco('acessivel') ?>) 2% 50% no-repeat;
  padding: 2px 5px 2px 22px;
}

/* Botao de ajuda */
.bloco_ajuda_aberto a.ajuda,
.bloco_ajuda_fechado a.ajuda {
  background: transparent url(<?php echo icone::endereco('ajuda') ?>) 2% 50% no-repeat;
  cursor: help;
  font-size: 12px;
  padding: 2px 5px 2px 22px;
}

/* Carregando */
.carregando {
  background-color: #FFFFFF;
  border: 1px outset #CCCCCC;
  color: #707070;
  display: block;
  font-weight: bolder;
  height: 1.8em;
  line-height: 1.8em;
  opacity: 0.8;
  overflow: hidden;
  padding: .1em 1em;
  width: 11em;
  z-index: 100;
}
div.carregando {
  position: fixed;
  left: 0px;
  padding: 0 .1em .2em 0;
  text-shadow: #DDDDDD 1px 1px 1px;
  top: 0px;
}

div.carregando * {
  margin: .5em;
  vertical-align: middle;
}

div.carregando img {
  margin-right: 1em;
}

/* Informacoes */
.info {
  font-size: 80%;
  padding: 1px;
}

/* Lista de Opcoes */
.opcoes {
  padding: 2px 10px;
}

.opcoes a {
  font-size: 90%;
  padding: 0px 5px;
}

/* Campos de relacionamento */
a.relacionamento,
a.hierarquia {
  background-repeat: no-repeat;
  background-position: 1% 50%;
  padding-left: 25px;
}

a.relacionamento {
  background-image: url(<?php echo icone::endereco('buscar') ?>);
}

a.hierarquia {
  background-image: url(<?php echo icone::endereco('hierarquia') ?>);
}

ul.hierarquia,
ul.hierarquia li,
ul.hierarquia li .lb,
ul.hierarquia li .l,
ul.hierarquia li .valor {
  clear: both;
  display: block;
  list-style-type: none;
  list-style-image: none;
  margin: 0;
  padding: 0;
}

ul.hierarquia {
  margin-left: .2em;
}

ul.hierarquia li .lb {
  border-left: 1px solid #000000;
  border-bottom: 1px solid #000000;
  height: .7em;
  width: .7em;
}

ul.hierarquia li .l {
  border-left: 1px solid #000000;
}

ul.hierarquia li .valor {
  clear: right;
  margin-top: -.7em;
  padding-left: 1.2em;
}

ul.hierarquia li .valor img.bt_expandir {
   background-color: #FFFFFF;
   border: 0;
   cursor: pointer;
   margin-right: .5em;
}

/* Caixa de opcoes (Janela) */
.caixa {
  background-color: #CCCCD5;
  border: 1px outset #CCCCD5;
  display: block;
  opacity: <?php echo $CFG->opaco; ?>;
  position: absolute;
  width: 25em;
}

.caixa h2.titulo {
  background-color: #FFFFFF;
  background-image: none;
  border: 1px inset #FFFFFF;
  color: #000000;
  font-size: 14px;
  height: 20px;
  line-height: 20px;
  margin: 2px;
  overflow: hidden;
  padding: 2px;
}

.caixa h2.titulo div.texto,
.caixa h2.titulo div.botoes {
  border: 0;
  margin: 0;
  padding: 0;
}

.caixa h2.titulo div.texto {
  float: left;
  padding-left: 1%;
  width: 80%;
}

.caixa h2.titulo div.botoes {
  float: right;
  width: 15%;
}

.caixa h2.titulo div.botoes .bt_fechar {
  background: #CCCCCC url(<?php echo icone::endereco('cancelar') ?>) 50% 50% no-repeat;
  border: 1px outset #CCCCCC;
  cursor: default;
  float: right;
  height: 16px;
  margin: 0;
  text-indent: -10000px;
  width: 16px;
}
.caixa h2.titulo div.botoes .bt_fechar:hover {
  background-color: #DDDDDD;
}

.caixa div.status {
  border: 1px inset #CCCCCC;
  height: 1.3em;
  margin: 0 4px 5px 4px;
  padding: 1px .6em;
}

.caixa div.busca {
  padding: .1em .3em;
}
.caixa div.busca label {
  background-image: url(<?php echo icone::endereco('buscar') ?>);
  background-repeat: no-repeat;
  background-position: 1% 50%;
  cursor: pointer;
  font-weight: bolder;
  padding-left: 25px;
  width: 5em;
}
.caixa div.busca label:after {
  content: ': ';
}
.caixa div.busca input {
  border: 1px solid #404040;
  width: 15em;
}
.caixa div.busca img.bt_atualizar {
  border: 0;
  cursor: pointer;
  margin-left: 1em;
  vertical-align: middle;
}

.caixa div.itens,
.caixa div.conteudo {
  border: 1px inset #FFFFFF;
  margin: 5px;
  overflow: auto;
}

.caixa div.itens {
  height: 12em;
}

.caixa div.itens p.selecionado {
   background-color: #000066;
   color: #FFFFFF;
}

.caixa div.conteudo {
  height: 260px;
}

.caixa div.itens p {
  border-bottom: 1px dotted #666666;
  cursor: pointer;
  font-size: x-small;
  margin: 0;
  padding: .2em .8em;
}

/* Abas */
.abas {
  clear: both;
  display: block;
  margin: 10px 2px;
}

.abas .nomes_abas span {
  display: none;
}

.abas .nomes_abas a {
  background-color: #EEEEEE;
  border-top: 1px outset #909090;
  border-left: 1px outset #909090;
  border-right: 1px outset #909090;
  margin-left: 1em;
  padding: 0 1em;
  position: relative;
  white-space: nowrap;
}

.abas .nomes_abas a:hover,
.abas .nomes_abas a:active,
.abas .nomes_abas a.ativa {
  background-color: #FFFFFF;
}
.abas .nomes_abas a.ativa {
  top: 1px;
}

.abas .conteudo_aba {
  background-color: #FFFFFF;
  border: 1px outset #909090;
  padding: 5px 10px 15px 10px;
}

/* Diretorios e Arquivos */
.arquivo,
.diretorio,
.diretorio_aberto,
.diretorio_fechado {
  background-position: 0% 60%;
  background-repeat: no-repeat;
  line-height: 20px;
  min-height: 20px;
  padding-left: 20px;
}
.arquivo {
  background-image: url(<?php echo icone::endereco('arq_web') ?>);
}
.diretorio {
  background-image: url(<?php echo icone::endereco('diretorio_fechado') ?>);
}
.diretorio_aberto {
  background-image: url(<?php echo icone::endereco('diretorio_aberto') ?>);
}
.diretorio_fechado {
  background-image: url(<?php echo icone::endereco('diretorio_fechado') ?>);
}

/* Textos */
.texto h1,
.texto h2,
.texto h3,
.texto h4,
.texto h5 {
  border: 0px;
}

.texto p {
  line-height: 1.4em;
  margin-top: 1.3em;
  text-align: justify;
  text-indent: 2em;
}

.texto h1 + p,
.texto h2 + p,
.texto h3 + p,
.texto h4 + p,
.texto h5 + p {
  margin-top: 5px;
  text-indent: 0em;
}

.texto pre {
  font-size: 80%;
  margin: 20px;
}

/* Setas */
.seta {
  font-weight: bolder;
}

/* Formularios */
form a.ajuda span {
  background-color: #FFFFFF;
  border: 1px outset #CCCCCC;
  color: #000000;
  cursor: default;
  display: none;
  font-size: .8em;
  font-weight: normal;
  padding: .5em;
  position: absolute;
  text-align: left;
  text-decoration: none;
  width: 200px;
  <?php if ($CFG->agent->mozfamily) { echo "opacity: 0.9;\n"; } ?>
}

form a.ajuda:hover span {
  display: block;
}

form a.ajuda a img {
  border: 1px solid #000000;
  margin: 0 .3em;
}

/* Formulario de paginacao */
.form_paginacao {
  display: block;
  float: right;
  text-align: right;
  width: 12em;
}
.form_paginacao label,
.form_paginacao div {
  font-weight: normal;
  display: inline;
}

.form_paginacao label:after {
  content: ':';
}
.form_paginacao input[type='submit'] {
  border: 1px solid #CCCCCC;
  cursor: pointer;
}

/* Graficos */
.area_grafico {
  text-align: center;
  margin: .1em 0 1em 0;
}
.area_grafico img {
  margin: 0 auto;
}
.area_grafico p {
  margin: 0;
}
.area_grafico a {
  font-size: .8em;
}
.area_grafico + map area {
  cursor: help;
}

/* Graficos de barra */
.grafico_barra {
  clear: both;
}
.grafico_barra strong,
.grafico_barra span {
  display: block;
  float: left;
}
.grafico_barra strong {
  clear: left;
  cursor: pointer;
  text-align: right;
  padding-right: .3em;
  width: 10em;
}
.grafico_barra span {
  clear: right;
  margin: .1em 0;
  width: 25em;
}

.grafico_barra span span {
  background-color: #DDDDDD;
  border: .1em inset #DDDDDD;
  display: block;
  height: .8em;
  line-height: .8em;
  margin: 0;
  width: inherit;
}
.grafico_barra span span span {
  background-color: #000033;
  border: .1em outset #000033;
  display: block;
  height: .6em;
  line-height: .6em;
}
.grafico_barra span span span span {
  background-color: #FFFF00;
  border: 0;
  display: none;
  font-weight: bolder;
  height: 1em;
  line-height: 1em;
  opacity: 0.8;
  position: absolute;
  text-align: center;
  width: 3em;
}
.grafico_barra strong:hover + span span span {
  background-color: #000088;
  border-color: #000088;
}
.grafico_barra strong:hover + span span span span {
  background-color: #FFFF00;
  border: 0;
  display: block;
}

<?php
// Formularios no IE
if ($CFG->agent->ie) {
    echo "form { margin: 0; }\n";
}
?>
