<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Exibe os dados de um Texto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array(
    'Dados do Texto' => array(
        'titulo',
        'arquivo',
        'frases'
    ),
    'Dados do Projeto' => array(
        'projeto:nome',
        'projeto:data_entrada_dados',
        'projeto:analista'
    ),
);

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'projeto';

modulo::exibir('texto_analise', $dados_quadro, $dados_pagina);
