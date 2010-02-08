<?php
//
// SIMP
// Descricao: Grafico de pizza
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 08/10/2009
// Modificado: 10/11/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Criar grafico
$g = new grafico($_GET['titulo']);
$g->formato      = $CFG->gd ? TIPO_PNG : TIPO_HTML;
$g->largura      = 250;
$g->altura       = 250;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_PIZZA;
$g->cache        = 24 * 60; // 1 dia
$g->cache        = false;
$g->legenda      = $_GET['legenda'];
$g->valores      = $_GET['valores'];

if (isset($_GET['formatar'])) {
    $g->conversao_valores = 'texto::formatar_bytes';
}

$g->imprimir();
