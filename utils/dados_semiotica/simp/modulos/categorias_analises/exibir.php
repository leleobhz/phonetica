<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Exibe os dados de um(a) Categoria de Analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$dados_quadro = new stdClass();
$dados_quadro->campos = array('cod_categoria_analise', 'nome');
modulo::exibir('categoria_analise', $dados_quadro);