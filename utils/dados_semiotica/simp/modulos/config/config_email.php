<?php
//
// SIMP
// Descricao: Altera os dados de Configuracao de E-mail
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.7
// Data: 27/09/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos  = array(
    'Geral' => array(
        'email_padrao',
        'preassunto',
        'tipo_email'
    ),
    'SMTP' => array(
        'smtp_host',
        'smtp_porta',
        'smtp_usuario',
        'smtp_senha'
    )
);
$dados_form->prefixo = 'email';
$dados_form->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio lista as configura&ccedil;&otilde;es de e-mail.</p>
  <p>O campo prefixo refere-se ao texto inclu&iacute;do antes do assunto de todos
  e-mails enviados pelo sistema.</p>
  <p>Caso selecione a op&ccedil;&atilde;o SMTP, &eacute; necess&aacute;rio 
  preencher os campos host e porta (e, opcionalmente, usu&aacute;rio e senha).</p>
AJUDA;

/// Dados da pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = $titulo;
$dados_pagina->nav = $nav;
$dados_pagina->usar_abas = true;

modulo::alterar('config', $dados_form, $dados_pagina);

