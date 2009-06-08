<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Lista de Analises da Frase
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 05/05/2009
// Modificado: 05/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$frase = modulo::get_entidade_session('frase');

/// Dados do Quadro
$dados_lista = new stdClass();
$dados_lista->opcoes = array('exibir', 'alterar', 'excluir');
$dados_lista->campos = array('categoria_analise:nome');
$dados_lista->ordem  = 'categoria_analise:nome';

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->submodulo = 'frase';

modulo::listar_entidades('analise', $dados_lista, $dados_pagina);
