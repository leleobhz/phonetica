<?php
//
// SIMP
// Descricao: Exemplo de uma lista hierarquica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

$lista = array(
    'a' => array(
        'b' => false,
        'c' => false,
        'd' => false),
    'e' => array(
        'f' => false,
        'g' => false,
        'h' => array(
            'i' => false,
            'j' => false,
            'k' => false,
        ),
        'l' => false
    )
);

$pagina = new pagina();
$pagina->cabecalho('', null, null);
$pagina->inicio_conteudo();
lista::hierarquica($lista);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


