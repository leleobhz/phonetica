<?php
//
// SIMP
// Descricao: Folha de estilos especificos para navegador Opera
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 15/06/2007
// Modificado: 21/07/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
?>
/*****************
 * ESTILOS OPERA *
 *****************/
#navegacao + hr { clear: both; }

<?php
if ($CFG->transparencia) {
    echo "#menu {\n";
    echo "  opacity: {$CFG->transparencia};\n";
    echo "}\n";
}
?>

<?php
if ($CFG->transparencia) {
    echo "#menu:hover,\n";
    echo "#menu:focus,\n";
    echo "#menu:active {\n";
    echo "  opacity: {$CFG->opaco};\n";
    echo "}\n";
}
?>

div.erro,
div.aviso {
  border-top: 0px;
  margin-left: -20em;
  margin-top: 0px;
  padding-bottom: 5px;
  position: fixed !important;
  top: 0px;
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
