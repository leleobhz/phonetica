<?php
//
// SIMP
// Descricao: Feed de eventos em formato RSS 2.0
// Autor: Rubens Takiguti Ribeiro && Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.1
// Data: 01/11/2007
// Modificado: 18/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados recebidos por GET
$cod_grupo = util::get_dado('cod_grupo', 'int', false);


/// Consultar eventos do periodo (15 dias antes e 15 dias depois)
list($dia, $mes, $ano) = util::get_data_completa($CFG->time);
$inicio    = mktime(0, 0, 0, $mes, $dia - 15, $ano);
$fim       = mktime(0, 0, 0, $mes, $dia + 15, $ano);
$vt_condicoes = array();
$vt_condicoes[] = condicao_sql::montar('data', '>', $inicio);
$vt_condicoes[] = condicao_sql::montar('data', '<', $fim);
if ($cod_grupo) {
    $vt_aux = array();
    $vt_aux[] = condicao_sql::montar('visibilidade', '=', $cod_grupo);
    $vt_aux[] = condicao_sql::montar('visibilidade', '=', null);
    $vt_condicoes[] = condicao_sql::sql_or($vt_aux);
    unset($vt_aux);
    $grupo = new grupo('', $cod_grupo);
}
$condicoes = condicao_sql::sql_and($vt_condicoes);
$eventos = objeto::get_objeto('evento')->consultar_varios($condicoes, true, 'data');


/// Montar o RSS
$titulo = 'Eventos'.' ('.$CFG->titulo.')';
$descricao = 'Lista de Eventos'.($cod_grupo ? ' para '.$grupo->nome : '').' ('.$CFG->titulo.')';

$rss = new rss($titulo, $CFG->wwwroot, $descricao);
$rss->set_atributo('language', $CFG->lingua);

if (file_exists($CFG->dirimgs.'logo.jpg')) {
    $rss->definir_image($CFG->wwwimgs.'logo.jpg', 'Logo', $CFG->wwwroot, 215, 100);
}

if ($eventos) {
    foreach ($eventos as $evento) {
        $data = strftime($CFG->formato_data, $evento->data);
        $opcoes = array('link'   => array('valor' => $CFG->wwwmods.'eventos/exibir.php?cod_evento='.$evento->cod_evento),
                        'author' => array('valor' => $evento->usuario->email),
                        );
        $rss->adicionar_item($evento->nome. ' - '.$evento->exibir_atributo('data'), $evento->descricao, $opcoes);
    }
}


/// Imprimir RSS
$rss->imprimir();
