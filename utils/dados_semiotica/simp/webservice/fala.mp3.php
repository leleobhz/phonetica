<?php
//
// SIMP
// Descricao: Cria uma MP3
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 17/09/2009
// Modificado: 22/09/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');
$arquivo = stripslashes(urldecode($_GET['arq']));
fala::exibir_som($arquivo);