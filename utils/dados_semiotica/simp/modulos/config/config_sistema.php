<?php
//
// SIMP
// Descricao: Altera os dados de Configuracao do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.8
// Data: 10/07/2007
// Modificado: 11/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'Layout' => array(
        'ajax',
        'gd',
        'transparencia',
        'opaco'
    ),
    'Data/Hora' => array(
        'formato_data',
        'formato_hora'
    )
);
$dados_form->prefixo = 'sistema';
$dados_form->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio lista as configura&ccedil;&otilde;es do sistema.</p>
  <p>Todos os campos devem ser preenchidos.</p>
  <p>Algumas op&ccedil;&otilde;es podem ter efeito apenas quando atualizada as folhas
  de estilo. Para isso, recomenda-se clicar neste <a href="">link</a> e em seguida
  clicar no bot&atilde;o atualizar de seu navegador.</p>
AJUDA;

/// Dados da pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo    = $titulo;
$dados_pagina->nav       = $nav;
$dados_pagina->estilos   = $estilos;
$dados_pagina->usar_abas = true;

modulo::alterar('config', $dados_form, $dados_pagina);
