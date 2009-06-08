<?php
//
// SIMP
// Descricao: Grafico de S.O. utilizados para acesso em um periodo
// Autor: Rubens Takiguti Ribeiro && Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.2
// Data: 09/11/2007
// Modificado: 19/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados Recebidos por GET
$de      = util::get_dado('de',  'int');
$ate     = util::get_dado('ate', 'int');
$versoes = util::get_dado('versoes', 'bool');
$ordem   = util::get_dado('ordem', 'int');

/// Dados do Grafico
$titulo = 'Gráfico de S.O. Utilizados';


/// Consultar valores
$valores = array();
$escala  = array();

// Consultar Logs
simp_autoload('log_sistema');
$vt_condicoes = array();
$vt_condicoes[] = condicao_sql::montar('data', '>', $de);
$vt_condicoes[] = condicao_sql::montar('data', '<', $ate);
$vt_condicoes[] = condicao_sql::montar('operacao', '=', LOG_ENTRADA);
$vt_condicoes[] = condicao_sql::montar('erro', '=', false);
$condicoes = condicao_sql::sql_and($vt_condicoes);
$logs = objeto::get_objeto('log_sistema')->consultar_varios($condicoes, true);

foreach ($logs as $log) {

    // Obter dados do S.O.
    $user_agent = $log->detalhes;
    $ua = new user_agent($user_agent);

    // Separar versoes diferentes
    if ($versoes) {
        $nome = $ua->so.($ua->versao_so ? ' '.$ua->versao_so : '');

    // Agrupar versoes diferentes
    } else {
        $nome = $ua->so;
    }

    $pos = array_search($nome, $escala);
    if ($pos !== false) {
        $valores[$pos] += 1;
    } else {
        $escala[] = $nome;
        $valores[] = 1;
    }
}

// Ordenar
switch ($ordem) {

// Nome
case 1:
    array_multisort($escala, SORT_ASC, SORT_STRING, $valores);
    break;

// Valor
case 2:
    array_multisort($valores, SORT_DESC, SORT_NUMERIC, $escala);
    break;
}


/// Criar grafico
$g = new grafico($titulo);
$g->formato      = $CFG->gd ? TIPO_PNG : TIPO_HTML;
$g->largura      = 100 + (35 * count($escala));
$g->altura       = 250;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_BARRA;
$g->cache        = false;
$g->escala       = $escala;
$g->valores      = $valores;

$g->imprimir();
