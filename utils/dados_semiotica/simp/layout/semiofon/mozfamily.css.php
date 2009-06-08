<?php
//
// SIMP
// Descricao: Folha de estilos especificos de navegadores da familia do Mozilla
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 19/02/2008
// Modificado: 19/02/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
?>
/*********************
 * ESTILOS MOZFAMILY *
 *********************/

#navegacao + hr { clear: both; }

#menu {
<?php
if ($CFG->transparencia) {
    echo "  -moz-opacity: {$CFG->transparencia};\n";
    echo "  opacity: {$CFG->transparencia};\n";
}
?>
}

<?php
if ($CFG->transparencia) {
    echo "#menu:hover,\n";
    echo "#menu:focus,\n";
    echo "#menu:active {\n";
    echo "  -moz-opacity: {$CFG->opaco};\n";
    echo "  opacity: {$CFG->opaco};\n";
    echo "}\n";
}
?>

/* Botao de ajuda */
.bloco_ajuda_aberto a.ajuda,
.bloco_ajuda_fechado a.ajuda {
  -moz-border-radius: .5em;
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
  -moz-border-radius-bottomright: 1em;
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
