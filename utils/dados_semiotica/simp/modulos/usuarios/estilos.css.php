<?php
//
// SIMP
// Descricao: Estilos do modulo de usuarios
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
$opcoes->icone = icone::endereco('grupos');
modulo::estilos('usuario', $opcoes);
echo <<<CSS

#lista_usuarios_grupos .label,
#lista_usuarios .label,
#lista_usuarios .inativo {
  clear: left;
  display: block;
  float: left;
  font-weight: bolder;
  line-height: 1.5em;
  text-align: right;
  width: 65%;
}

#lista_usuarios_grupos .opcoes,
#lista_usuarios .opcoes {
  clear: right;
  float: left;
  width: 34%;
}

#tabela_log td {
  border-right: 1px dotted #6688BB;
  padding-left: 1em;
  padding-right: 1em;
  vertical-align: top;
}

#lista_letras {
  text-align: center;
}

#lista_letras a,
#lista_letras strong {
  font-size: 80%;
}

#form_busca {
  margin-bottom: 15px;
}

#aba_pessoal {
  background-image: url(../../imgs/icones/perfil.gif);
  background-position: 5px 60%;
  background-repeat: no-repeat;
  padding-left: 25px;
}

#aba_senha {
  background-image: url(../../imgs/icones/bloqueado.gif);
  background-position: 5px 60%;
  background-repeat: no-repeat;
  padding-left: 25px;
}

/* Itens Invisiveis */
#lista_letras span,
#area_vetor_grupos {
  display: none;
}

p.clear {
  height: 20px;
}
p a.rss {
  float: right;
  font-size: 80%;
  height: 14px;
}
CSS;
