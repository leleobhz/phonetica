<?php
//
// SIMP
// Descricao: Arquivo que exclui um evento
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 13/08/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array('nome', 'descricao', 'data', 'visibilidade', 'usuario');

/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$dados_pagina = new stdClass();
$dados_pagina->nav[] = '#index.php';
$dados_pagina->nav[] = $modulo.'#index.php';
$dados_pagina->nav[] = $modulo.'#exibir_eventos.php';
$dados_pagina->nav[] = $modulo.'#'.basename(__FILE__);

modulo::excluir('evento', $dados_form, $dados_pagina);
