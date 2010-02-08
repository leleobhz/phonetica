<?php
//
// SIMP
// Descricao: Exemplo de grafico de pizza
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
