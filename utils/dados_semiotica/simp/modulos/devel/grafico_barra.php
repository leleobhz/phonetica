<?php
//
// SIMP
// Descricao: Grafico de barra
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 08/10/2009
// Modificado: 18/01/2010
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Criar grafico
$g = new grafico($_GET['titulo']);
$g->formato      = $CFG->gd ? TIPO_PNG : TIPO_HTML;
$g->largura      = 200 + (20 * count($_GET['escala']));
$g->altura       = 360;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_BARRA;
$g->cache        = 24 * 60; // 1 dia
$g->cache        = false;
$g->escala       = $_GET['escala'];
$g->valores      = $_GET['valores'];

if (isset($_GET['valor_topo'])) {
    $g->altura     = 300 + ($_GET['valor_topo'] / 1048576 * 20);
    $g->valor_topo = $_GET['valor_topo'];
}
if (isset($_GET['formatar'])) {
    $g->conversao_valores = 'texto::formatar_bytes';
}

$g->imprimir();
