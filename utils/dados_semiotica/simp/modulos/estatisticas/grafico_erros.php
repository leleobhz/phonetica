<?php
//
// SIMP
// Descricao: Grafico do numero de erros por mes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.2
// Data: 24/10/2007
// Modificado: 20/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados Recebidos por GET
$de  = util::get_dado('de',  'int');
$ate = util::get_dado('ate', 'int');


/// Dados do Grafico
$titulo = 'Gráfico de Erros';


/// Consultar valores
$valores = array();
$escala  = array();
$nomes_meses = listas::get_meses();

// Consultar Logs
$log = new log_sistema();
$time = $de;
while ($time < $ate) {
    list($dia, $mes, $ano) = util::get_data_completa($time);
    $proximo_time = mktime(0, 0, 0, $mes + 1, 1, $ano);

    $vt_condicoes = array();
    $vt_condicoes[] = condicao_sql::montar('data', '>=', $time);
    $vt_condicoes[] = condicao_sql::montar('data', '<', $proximo_time);
    $vt_condicoes[] = condicao_sql::montar('erro', '=', 1);
    $condicoes = condicao_sql::sql_and($vt_condicoes);
    $valores[] = $log->quantidade_registros($condicoes);
    $m = ($mes % 12) ? $mes % 12 : 12;
    $escala[]  = $nomes_meses[$m].'/'.$ano;

    $time = $proximo_time;
}

// Calcular media
$media = array_sum($valores) / count($valores);
$legenda_linhas = array('Média');
$linhas = array($media, $media);

/// Criar grafico
$g = new grafico($titulo);
$g->formato        = $CFG->gd ? TIPO_PNG : TIPO_HTML;
$g->largura        = 80 + (30 * count($escala));
$g->altura         = 250;
$g->tipo_cor       = COR_NORMAL;
$g->tipo_grafico   = GRAFICO_LINHA;
$g->ponto          = PONTO_BOLA;
$g->cache          = false;
$g->escala         = $escala;
$g->valores        = $valores;
$g->linhas         = $linhas;
$g->legenda_linhas = $legenda_linhas;

$g->imprimir();
