<?php
//
// SIMP
// Descricao: Arquivo para inserir os dados do evento
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 09/08/2007
// Modificado: 09/06/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'Dados do Evento' => array(
        'nome',
        'descricao',
        'visibilidade'
    ),
    'data'
);
$dados_form->opcoes = array();
$dados_form->opcoes['cod_usuario'] = $USUARIO->cod_usuario;


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$dados_pagina = new stdClass();
$dados_pagina->nav[]   = '#index.php';
$dados_pagina->nav[]   = $modulo.'#index.php';
$dados_pagina->nav[]   = $modulo.'#exibir_eventos.php';
$dados_pagina->nav[]   = $modulo.'#'.basename(__FILE__);

/// Outros dados
if (isset($_GET['time'])) {
    $time = util::get_dado('time', 'int');
    $data = getdate($time);
    $dados_form->opcoes['data_dia'] = (int)$data['mday'];
    $dados_form->opcoes['data_mes'] = (int)$data['mon'];
    $dados_form->opcoes['data_ano'] = (int)$data['year'];

    $dados_pagina->titulo = 'Cadastrar evento em '.strftime('%d/%m/%Y', $time);
} else {
    $entidade = new evento();
    $entidade->set_auxiliar('campo_data', true);
    $dados_form->entidade = &$entidade;
}

modulo::inserir('evento', $dados_form, $dados_pagina);
