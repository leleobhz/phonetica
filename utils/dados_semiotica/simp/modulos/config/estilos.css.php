<?php
//
// SIMP
// Descricao: Estilos do modulo de configuracoes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 26/01/2009
// Modificado: 26/01/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../../config.php');

$opcoes = new stdClass();
$opcoes->icone = icone::endereco('config');
$opcoes->largura_label_form = '40%';
$opcoes->largura_campo_form = '50%';

modulo::estilos('config', $opcoes);
