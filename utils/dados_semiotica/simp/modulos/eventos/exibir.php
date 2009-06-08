<?php
//
// SIMP
// Descricao: Exibe os dados de um evento
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 09/08/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rodrigo Pereira Moreia
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados de Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array(
    'Dados do Evento' => array(
        'nome',
        'descricao',
        'data'
    ),
    'visibilidade',
    'usuario'
);

/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$dados_pagina = new stdClass();
$dados_pagina->nav[] = '#index.php';
$dados_pagina->nav[] = $modulo.'#index.php';
$dados_pagina->nav[] = $modulo.'#exibir_eventos.php';
$dados_pagina->nav[] = $modulo.'#'.basename(__FILE__);

/// Operacoes
if (!isset($_SESSION[$modulo]['time'])) {
    $entidade = util::get_entidade('evento', array('data'));
    $_SESSION[$modulo]['time'] = $entidade->data;
}

modulo::exibir('evento', $dados_quadro, $dados_pagina);
