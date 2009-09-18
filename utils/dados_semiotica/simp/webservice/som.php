<?php
//
// SIMP
// Descricao: Classe de geracao de um som
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 17/09/2009
// Modificado: 17/09/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');
$arquivo = $_GET['arq'];
fala::exibir_som($arquivo);
