<?php
//
// SIMP
// Descricao: Grafico de numero de atividades por mes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 03/03/2009
// Modificado: 18/11/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados Recebidos por GET
$de  = util::get_dado('de',  'int');
$ate = util::get_dado('ate', 'int');

/// Dados do Grafico
$titulo = 'Gráfico de Atividades';

simp_autoload('log_sistema');
$operacoes = array(
    LOG_INSERT  => 'Inserir',
    LOG_UPDATE  => 'Atualizar',
    LOG_DELETE  => 'Apagar'
    );


/// Consultar valores
$valores = array();
$escala  = array();
$legenda = array_values($operacoes);
$nomes_meses = listas::get_meses();

// Consultar Logs
$log = new log_sistema();
$preencheu_escala = false;
foreach ($operacoes as $cod_operacao => $operacao) {
    $time = $de;
    while ($time < $ate) {
        list($dia, $mes, $ano) = util::get_data_completa($time);
        $proximo_time = mktime(0, 0, 0, $mes + 1, 1, $ano);

        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('data', '>=', $time);
        $vt_condicoes[] = condicao_sql::montar('data', '<', $proximo_time);
        $vt_condicoes[] = condicao_sql::montar('erro', '=', false);
        $vt_condicoes[] = condicao_sql::montar('operacao', '=', $cod_operacao);
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        $valores[$cod_operacao][] = $log->quantidade_registros($condicoes);
        if (!$preencheu_escala) {
            $m = ($mes % 12) ? $mes % 12 : 12;
            $escala[]  = $nomes_meses[$m].'/'.$ano;
        }
        $time = $proximo_time;
    }
    $preencheu_escala = true;
}


/// Criar grafico
$g = new grafico($titulo);
$g->formato        = $CFG->gd ? TIPO_PNG : TIPO_HTML;
$g->largura        = 80 + (30 * count($escala));
$g->altura         = 250;
$g->tipo_cor       = COR_NORMAL;
$g->tipo_grafico   = GRAFICO_LINHA;
$g->ponto          = PONTO_BOLA;
$g->cache          = false;
$g->legenda        = $legenda;
$g->escala         = $escala;
$g->valores        = $valores;

$g->imprimir();
