<?php
//
// SIMP
// Descricao: Arquivo mostra e altera os eventos cadastrados
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 27/07/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados da Pagina
$modulo  = util::get_modulo(__FILE__);
$dados_pagina = new stdClass();
$dados_pagina->nav[] = '#index.php';
$dados_pagina->nav[] = $modulo.'#'.basename(__FILE__);
$dados_pagina->estilos = array($CFG->wwwlayout.'calendario.css',
                               $CFG->wwwmods.$modulo.'/estilos.css.php');

modulo::pagina('imprimir_calendario', $dados_pagina);


//
//     Imprime o calendario de eventos
//
function imprimir_calendario($pagina) {
// pagina $pagina: objeto que imprime a pagina
//
    global $CFG, $USUARIO;
    $modulo = util::get_modulo(__FILE__);
    $vt_grupos = array_merge(array(0), array_keys($USUARIO->grupos));
    $eh_admin = $USUARIO->possui_grupo(COD_ADMIN);
    $link_pagina = $CFG->wwwmods.$modulo.'/exibir_eventos.php';
    $link_pagina_exibir = $CFG->wwwmods.$modulo.'/exibir.php';
    $ajuda = <<<AJUDA
  <p>O calend&aacute;rio abaixo permite visualizar os eventos dos dias e,
  caso se tenha permiss&atilde;o, cadastrar novos eventos.</p>
  <p>Para cadastrar um novo evento, primeiro &eacute; necess&aacute;rio 
  selecionar um dia.</p>
  <p>O objetivo deste m&oacute;dulo &eacute; servir de lembrete para as atividades
  a serem realizadas no sistema ou fora dele.</p>
AJUDA;

    $link_rss = "{$CFG->wwwmods}{$modulo}/eventos.rss.php";
    $links = array(link::arquivo_modulo($USUARIO, 'exibir_eventos.php?time=0', $modulo, 'Todos Eventos', '', '', 1),
                   link::texto($link_rss, 'Feed', 'Eventos', '', 'rss', 1, 0, 0, 0)
                  );

    mensagem::comentario($CFG->site, $ajuda);
    evento::imprimir_calendario($link_pagina, $link_pagina_exibir, 'cal_eventos', $USUARIO->cod_usuario, false, $vt_grupos, $eh_admin);
    $pagina->listar_opcoes($links);
}

