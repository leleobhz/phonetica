<?php
//@ignoredoc
/// Exemplo de grafico de pizza

require_once('../../config.php');

$g = new grafico('Gráfico de Pizza');
$g->formato      = TIPO_PNG;
$g->largura      = 300;
$g->altura       = 300;
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_PIZZA;

$valores = array(10, 40, 56, 5, 15);
$legenda = array('a', 'b', 'c', 'd', 'e');

$g->valores = $valores;
$g->legenda = $legenda;

$g->imprimir();
