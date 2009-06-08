<?php
//@ignoredoc
/// Exemplo de grafico de barras multiplas

require_once('../../config.php');

$g = new grafico('Gráfico de Barras Múltiplas');
$g->formato = TIPO_PNG;
$g->largura = 350;
$g->altura  = 250;
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_BARRA;

$g->legenda = array('Rubens', 'Iúna', 'Teste', 'Outro');
$g->escala = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');


$g->valores = array(array(100, 23, 54, 13, 90),
                    array(90, 30, 55, 50, 70),
                    array(70, 20, 50, 20, 45),
                    array(75, 56, 45, 60, 13)
                   );
$g->valor_topo = 100;

// Linha com o valor esperado
$g->legenda_linhas = array('Valor Esperado');
$g->linhas         = array(30, 80);

$g->imprimir();
