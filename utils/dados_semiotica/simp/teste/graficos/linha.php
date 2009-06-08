<?php
//@ignoredoc
/// Exemplo de grafico de linha simples

require_once('../../config.php');

$g = new grafico('Gráfico de Linha');
$g->nome_arquivo = 'teste';
$g->formato      = TIPO_PNG;
$g->largura      = 350;
$g->altura       = 250;
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_LINHA;
$g->ponto        = PONTO_QUADRADO;

$g->escala = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');
$g->valores = array(50, 23, 54, 13, 92);
$g->valor_topo = 100;

$media = array_sum($g->valores) / count($g->valores);
$g->legenda_linhas = array('Média Geral', 'Valor Mínimo');

$g->linhas         = array($media, $media,
                           50, 50);

$g->imprimir();
