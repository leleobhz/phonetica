<?php
//@ignoredoc
/// Exemplo de grafico de linhas multiplas

require_once('../../config.php');

$g = new grafico('Gráfico de Linhas Múltiplas');
$g->formato      = TIPO_PNG;
$g->largura      = 350;
$g->altura       = 250;
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_LINHA;
$g->ponto        = PONTO_BOLA;

// Valores
$g->legenda = array('Rubens', 'Iúna');
$g->escala  = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');
$g->valores = array( array(100, 23, 54, 13, 90),
                     array(90, 30, 55, 50, 70)
                   );

// Linhas com a media geral e valor esperado
$media = (array_sum($g->valores[0]) + array_sum($g->valores[1])) /
         (count($g->valores[0]) + count($g->valores[1]));

$g->legenda_linhas = array('Média Geral', 'Valor Mínimo');
$g->linhas         = array($media, $media,
                           50, 50);

$g->valor_topo = 100;
$g->imprimir();
