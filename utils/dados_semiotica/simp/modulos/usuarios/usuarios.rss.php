<?php
//
// SIMP
// Descricao: Feed de usuarios em formato RSS 2.0
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.2
// Data: 09/11/2007
// Modificado: 13/01/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Consultar os ultimos 10 usuarios cadastrados
simp_autoload('log_sistema');
$limite = 10;
$vt_condicoes = array();
$vt_condicoes[] = condicao_sql::montar('operacao', '=', LOG_INSERT);
$vt_condicoes[] = condicao_sql::montar('entidade', '=', 'usuario');
$vt_condicoes[] = condicao_sql::montar('erro', '=', '0');
$condicoes = condicao_sql::sql_and($vt_condicoes);
$log = new log_sistema();
$total = $log->quantidade_registros($condicoes);
$logs = $log->consultar_varios($condicoes, true, 'data', false, $limite, max($total - $limite, 0));
unset($log);


/// Montar o RSS
$titulo = 'Últimos Usuários Cadastrados'.' ('.$CFG->titulo.')';
$descricao = 'Lista com os últimos '.$limite.' usuários cadastrados no sistema ('.$CFG->titulo.')';

$rss = new rss($titulo, $CFG->wwwroot, $descricao);
$rss->set_atributo('language', $CFG->lingua);

if (file_exists($CFG->dirimgs.'logos/principal.jpg')) {
    $rss->definir_image($CFG->wwwimgs.'logos/principal.jpg', 'Logo', $CFG->wwwroot, 215, 100);
}

if ($logs) {
    foreach ($logs as $log) {
        $u = new usuario('', $log->cod_entidade);
        if ($u->existe()) {
            $data = strftime($CFG->formato_data.' - '.$CFG->formato_hora, $log->data);
            $opcoes = array('link'    => array('valor' => $CFG->wwwmods.'usuarios/exibir.php?cod_usuario='.$log->cod_entidade),
                            'pubDate' => array('valor' => $log->data)
                            );
            $rss->adicionar_item($u->nome, "{$u->nome} ({$u->login}) se cadastrou em {$data}", $opcoes);
        }
    }
}


/// Imprimir RSS
$rss->imprimir();
