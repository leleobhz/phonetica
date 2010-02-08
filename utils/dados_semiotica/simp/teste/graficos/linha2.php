<?php
//
// SIMP
// Descricao: Exemplo de grafico de linhas multiplas
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
$media = matematica::get_media_geral($g->valores);

$g->legenda_linhas = array('Média Geral', 'Valor Mínimo');
$g->linhas         = array($media, $media,
                           50, 50);

$g->valor_topo = 100;
$g->imprimir();
