<?php
//
// SIMP
// Descricao: Altera os dados de Configuracao da Pagina
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.9
// Data: 10/07/2007
// Modificado: 27/02/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados do formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'Geral' => array(
        'titulo',
        'descricao',
        'palavras',
        'lingua',
        'localidade',
        'cidade',
        'estado'
    ),
    'Autor' => array(
        'autor',
        'link_autor'
    )
);
$dados_form->prefixo = 'pagina';
$dados_form->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio lista as configura&ccedil;&otilde;es de dados da p&aacute;gina.</p>
  <p>Todos os campos devem ser preenchidos.</p>
  <p>O campo Palavras-chave deve ser separado por v&iacute;rgula.</p>
AJUDA;


/// Dados da pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = $titulo;
$dados_pagina->nav = $nav;
$dados_pagina->usar_abas = true;

modulo::alterar('config', $dados_form, $dados_pagina);
