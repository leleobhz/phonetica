<?php
//
// SIMP
// Descricao: Altera os dados de um usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 21/01/2007
// Modificado: 09/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados do Formulario
$dados_form = new stdClass();
if ($entidade->possui_grupo(COD_ANALISTAS)) {
    $dados_form->entidade = $entidade->get_pai('analista');
    $dados_form->campos   = array('usuario:nome', 'usuario:email', 'email_alternativo', 'usuario:login');
    $classe = 'analista';
} else {
    $dados_form->entidade = $entidade;
    $dados_form->campos   = array('nome', 'email', 'login');
    $classe = 'usuario';
}
$dados_form->prefixo  = 'pessoal';
$dados_form->ajuda    = <<<AJUDA
  <p>Formul&aacute;rio para alterar os dados de usu&aacute;rios. N&atilde;o
  &eacute; permitido duplicar e-mails ou logins para usu&aacute;rios diferentes.</p>
AJUDA;

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo    = $titulo;
$dados_pagina->nav       = $nav;
$dados_pagina->usar_abas = true;

modulo::alterar($classe, $dados_form, $dados_pagina);
