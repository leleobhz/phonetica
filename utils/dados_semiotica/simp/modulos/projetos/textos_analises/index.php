<?php
//
// ANALISES - Cadastro de Analises sobre Frases
// Descricao: Lista de Textos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 09/07/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$ANALISTA = analista::get_analista();
$projeto = modulo::get_entidade_session('projeto');

/// Dados do Quadro
$dados_lista = new stdClass();
$dados_lista->opcoes = array('exibir', 'alterar', 'frases_analista', 'excluir');
$dados_lista->campos = array('titulo');
$dados_lista->ordem  = 'titulo';

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'projeto';

modulo::listar_entidades('texto_analise', $dados_lista, $dados_pagina);
