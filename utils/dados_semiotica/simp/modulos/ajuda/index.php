<?php
//
// SIMP
// Descricao: Lista de paginas extras
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 04/06/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$login = util::get_dado('login', 'bool', false, false);

// Indica se veio do login ou da pagina principal
if (!isset($_SESSION[$modulo]['login'])) {
    $_SESSION[$modulo]['login'] = $login;
}

$titulo = 'Ajuda';
if ($_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Links a serem exibidos
$links_ajuda = array(
    'Solu&ccedil;&atilde;o de Problemas'        => 'ajuda.php',
    'Gloss&aacute;rio'                          => 'glossario.php',
    'Descri&ccedil;&atilde;o dos &Iacute;cones' => 'icones.php'
    );

$links_informacoes = array(
    'Busca nas Ajudas'                          => 'busca.php',
    'Mapa do Site'                              => 'mapa.php',
    'Programas Recomendados'                    => 'programas.php'
    );

$links_extras = array(
    'Tecnologias Empregadas'                    => 'tecnologias.php',
    'Licen&ccedil;a'                            => 'licenca.php',
    'Cr&eacute;ditos'                           => 'creditos.php'
    );



/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_links('Ajuda', $links_ajuda);
imprimir_links('Informa&ccedil;&otilde;es &Uacute;teis', $links_informacoes);
imprimir_links('Dados Extras', $links_extras);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de opcoes
//
function imprimir_links($titulo, $links) {
// String $titulo: titulo da lista de links
// Array[String => String] $links: vetor com os links
//
    global $CFG, $modulo;
    echo "<ul class=\"lista_topicos\">\n";
    foreach ($links as $nome => $arquivo) {
        $l = $CFG->wwwmods.$modulo.'/'.$arquivo;
        echo "  <li>".link::texto($l, $nome, $nome, '', '', 1)."</li>\n";
    }
    echo "</ul>\n";
}
