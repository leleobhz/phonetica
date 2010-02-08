<?php
//
// SIMP
// Descricao: Exemplo de grafico de barras simples
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
$g->formato = TIPO_PNG;
$g->largura = 350;
$g->altura  = 250;
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_BARRA;

$g->escala = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio');
$g->valores = array(50, 23, 54, 13, 90);
$g->valor_topo = 100;

$media = matematica::get_media($g->valores);
$g->legenda_linhas = array('Média Geral', 'Valor Mínimo');

$g->linhas         = array($media, $media,
                           50, 50);

$g->imprimir();
