<?php
//
// SIMP
// Descricao: Exemplo de utilizacao da classe cache_sessao
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

// Os dados sao armazenados em cache de sessao atraves de um ID unico para o sistema inteiro.
// Funcionamento: basicamente e' preciso checar se o valor ja' esta' em cache.
// Caso esteja, obtem o valor. Caso nao esteja, consulta o valor e guarda na cache indicando 
// o tempo de validade.

// Obter um ID unico
// (formado pelo nome do arquivo e numero da linha para garantir que e' unico no sistema)
$id = cache_sessao::get_id();

// Se o valor esta' em cache: obter da cache
if (cache_sessao::em_cache($id)) {
    $minha_lista = cache_sessao::get_valor($id);

    echo '<p>Obteve da cache</p>';

// Se o valor nao esta' em cache: obte-lo da forma tradicional e guarda-lo em cache
} else {
    $minha_lista = array('a', 'b', 'c', 'd');
    cache_sessao::set_valor($id, $minha_lista, 10);

    echo '<p>Obteve da forma tradicional</p>';
    echo '<p>Guardou em cache com validade de 10 segundos</p>';
}

util::dump($minha_lista);
util::dump($_SESSION['tmp']);
echo '<p>Time atual: '.time().'</p>';