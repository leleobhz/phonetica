<?php
//
// SIMP
// Descricao: Arquivo para listar o conteudo da sessao e as entidades gravadas em cache
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

echo '<h1>Dados da sess&atilde;o</h1>';
util::dump($_SESSION);

echo '<h1>Entidades em Cache</h1>';
foreach ($_SESSION['cache'] as $classe => $instancias) {
    echo '<div style="border: 1px solid red;">';
    echo '<h2 style="background-color: #FFEEEE; padding: .2em; margin: 0;">'.$classe.'</h2>';
    foreach ($instancias as $indice => $instancia_serial) {
        $instancia = unserialize($instancia_serial);
        echo '<hr />';
        echo '<h3>'.$classe.'['.$indice.']</h3>';
        util::dump($instancia);
    }
    echo '</div>';
}
