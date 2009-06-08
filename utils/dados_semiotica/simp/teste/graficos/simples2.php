<?php
//@ignoredoc
/// Exemplo de grafico de barras multiplas em HTML

require_once('../../config.php');

$g = new grafico('Gráfico de Barras Múltiplas');
$g->formato = TIPO_HTML;
$g->largura = 350;
$g->altura  = 250;
$g->tipo_cor    = COR_NORMAL;
$g->tipo        = GRAFICO_BARRA;

$g->escala = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');

$g->valores = array(100, 23, 54, 13, 90);

$g->imprimir();

