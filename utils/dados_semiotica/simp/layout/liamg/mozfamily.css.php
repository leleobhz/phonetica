<?php
//
// SIMP
// Descricao: Folha de estilos especificos de navegadores da familia do Mozilla
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 06/05/2008
// Modificado: 06/05/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
?>
/*********************
 * ESTILOS MOZFAMILY *
 *********************/

#navegacao + hr { clear: both; }

/* Botao de ajuda */
.bloco_ajuda_aberto a.ajuda,
.bloco_ajuda_fechado a.ajuda {
  -moz-border-radius: 10px;
}

/* Caixa de opcoes */
.caixa {
  -moz-border-radius-topleft: 18px;
  -moz-border-radius-topright: 5px;
  -moz-border-radius-bottomleft: 5px;
  -moz-border-radius-bottomright: 5px;
}

.caixa h2 {
  -moz-border-radius-topleft: 13px;
  -moz-border-radius-topright: 5px;
}

.caixa h2 .bt_fechar {
  -moz-border-radius: 5px;
}

div.carregando {
  -moz-border-radius-bottomleft: 10px;
  -moz-border-radius-bottomright: 10px;
}

.opcoes {
  -moz-border-radius: 2em;
}

.abas .nomes_abas a {
  -moz-border-radius-topleft: 0.7em;
  -moz-border-radius-topright: 0.7em;
}

div.erro,
div.aviso {
  border-top: 0px;
  margin-left: -20em;
  margin-top: 0px;
  padding-bottom: 5px;
  position: fixed !important;
  top: 0px;
  -moz-border-radius-bottomright: 25px;
  -moz-border-radius-bottomleft: 25px;
}

div.erro {
  left: 49%;
}

div.aviso {
  left: 51%;
}

div.erro:hover,
div.aviso:hover {
  z-index: 1001;
}

#centro {
  -moz-border-radius: 1em;
  -moz-border-radius-topleft: 0;
}

#menu strong,
#menu ul li:hover {
  -moz-border-radius-topleft: .5em;
  -moz-border-radius-bottomleft: .5em;
  position: relative;
  left: 1px;
}

#menu ul + div {
  -moz-border-radius-bottomleft: 1em;
  background-color: #909090;
  border-left: 1px solid #CCCCCC;
  border-bottom: 1px solid #CCCCCC;
  float: none;
  height: .5em;
  right: 0px;
  overflow: hidden;
  opacity: 0.9;
  position: fixed;
  text-align: right;
  top: 0px;
}

#menu ul + div > p + p {
  border: 0;
}

#menu ul + div:hover {
  background-color: #FFFFFF;
  height: auto;
}

#menu:after {
  color: #707070;
  content: '+ opções no canto superior direito';
  cursor: default;
  font-size: .6em;
}
