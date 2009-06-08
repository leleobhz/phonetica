<?php
//
// SIMP
// Descricao: Estilos do modulo de eventos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 27/02/2009
// Modificado: 27/02/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../../config.php');

$opcoes = new stdClass();
$opcoes->icone = icone::endereco('calendario');
modulo::estilos('config', $opcoes);
echo <<<CSS
#cal_eventos {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 15px;
}

#navegacao_dias {
  border: 1px solid #CCCCCC;
  margin: 1em auto;
  padding: .5em 1em;
  text-align: center;
  width: 25em;
}

#navegacao_dias span {
  color: #CCCCCC;
  margin: 0 1em;
}
CSS;
