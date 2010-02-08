<?php
//
// SIMP
// Descricao: Altera os dados de Configuracao de Autenticacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.10
// Data: 21/08/2007
// Modificado: 11/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


// Dados Formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'Tipo' => array(
        'autenticacao',
        'autenticacao_http'
    ),
    'Manuten&ccedil;&atilde;o' => array(
        'fechado',
        'motivo_fechado'
    )
);
$dados_form->prefixo = 'autenticacao';
$dados_form->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio lista as formas de autentica&ccedil;&atilde;o no sistema.</p>
  <p>Definindo o sistema como fechado, apenas os administradores poder&atilde;o logar.</p>
  <p>Observe que os dados de configura&ccedil;&atilde;o espec&iacute;ficos de cada tipo de
  autentica&ccedil;&atilde;o ficam guardados no arquivo "autenticacao.ini", localizado
  no diretorio de driver de autentica&ccedil;&atilde;o.</p>
  <p>Observa&ccedil;&atilde;o: o usu&aacute;rio "admin" sempre se autentica pelo BD local.</p>
AJUDA;


/// Dados da pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo    = $titulo;
$dados_pagina->nav       = $nav;
$dados_pagina->estilos   = $estilos;
$dados_pagina->usar_abas = true;

modulo::alterar('config', $dados_form, $dados_pagina);

