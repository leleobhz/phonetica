<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Lista de Frases
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 10/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$texto_analise = modulo::get_entidade_session('texto_analise');

/// Dados do Quadro
$dados_lista = new stdClass();
$dados_lista->opcoes = array('exibir', 'alterar', 'analises', 'excluir');
$dados_lista->campos = array('conteudo');
$dados_lista->ordem  = 'cod_frase';

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'texto_analise';

modulo::listar_entidades('frase', $dados_lista, $dados_pagina);
