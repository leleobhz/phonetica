<?php
//
// SIMP
// Descricao: Exemplo de grafico de barras multiplas em HTML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

$g = new grafico('Gráfico de Barra');
$g->formato = TIPO_HTML;
$g->largura = 400;
$g->altura  = 250;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_BARRA;

$g->escala = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');

$g->valores = array(100, 23, 54, 13, 90);

$g->imprimir();
