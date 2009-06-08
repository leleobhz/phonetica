<?php
//@ignoredoc
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


