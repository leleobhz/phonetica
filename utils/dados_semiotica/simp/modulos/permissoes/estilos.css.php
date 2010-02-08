<?php
//
// SIMP
// Descricao: Estilos do modulo de permissoes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 27/02/2009
// Modificado: 27/02/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../../config.php');

$opcoes = new stdClass();
$opcoes->icone = icone::endereco('checado');
$opcoes->largura_label = '60%';
$opcoes->largura_opcoes = '35%';

modulo::estilos('config', $opcoes);
