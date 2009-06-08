<?php
//@ignoredoc

/// Listar conteudo da sessao e as entidades gravadas em cache

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
