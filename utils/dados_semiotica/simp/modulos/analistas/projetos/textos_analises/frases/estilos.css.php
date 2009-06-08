<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Estilos do modulo frases
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../../../../../config.php');

$opcoes = new stdClass();
$opcoes->icone = icone::endereco('lista');

modulo::estilos('projeto', $opcoes);

