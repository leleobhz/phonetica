<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Exibe os dados de uma Analise de uma Frase
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 05/06/2009
// Modificado: 05/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array(
    'An&aacute;lise' => array(
        'categoria_analise'
    ),
    'Dados da Frase' => array(
        'frase:texto_analise',
        'frase:conteudo'
    )
);

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'frase';

modulo::exibir('analise', $dados_quadro, $dados_pagina);
