<?php
//
// SIMP
// Descricao: Licenca do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 13/06/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Licen&ccedil;a';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_licenca();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a licenca
//
function imprimir_licenca() {
    global $CFG, $modulo;

    $lang = util::get_dado('lang', 'string', false, 'en');

    switch ($lang) {
    case 'en':
    default:
        echo '<p class="idiomas"><span>Language:</span><ul><li>';
        link::texto($CFG->wwwmods.$modulo.'/licenca.php?lang=en', 'English (Official version)', 'English Version of GNU GPL 2', 'licenca_en', 'ativo');
        echo '<span>;</span></li><li>';
        link::texto($CFG->wwwmods.$modulo.'/licenca.php?lang=ptbr', 'Portugese (Unofficial version)', 'Portugese Version of GNU GPL 2', 'licenca_ptbr');
        echo '<span>;</span></li><li>';
        echo "<a rel=\"checar\" href=\"http://www.gnu.org/licenses/old-licenses/gpl-2.0-translations.html\" title=\"Other Translations of GNU GPL 2\">Other Translations (Unofficial version)</a>";
        echo '<span>;</span></li></ul></p>';
        include_once($CFG->dirmods.$modulo.'/licenca.html');
        break;
    case 'ptbr':
        echo '<p class="idiomas"><span>L&iacute;ngua:</span><ul><li>';
        link::texto($CFG->wwwmods.$modulo.'/licenca.php?lang=en', 'Ingl&ecirc;s (Vers&atilde;o oficial)', 'Vers&atilde;o em Ingl&ecirc;s da GPL GNU 2', 'licenca_en');
        echo '<span>;</span></li><li>';
        link::texto($CFG->wwwmods.$modulo.'/licenca.php?lang=ptbr', 'Portugu&ecirc;s (Vers&atilde;o n&atilde;o-oficial)', 'Vers&atilde;o em Portugu&ecirc;s n&atilde;o-oficial da GPL GNU 2', 'licenca_ptbr', 'ativo');
        echo '<span>;</span></li><li>';
        echo "<a rel=\"checar\" href=\"http://www.gnu.org/licenses/old-licenses/gpl-2.0-translations.html\" title=\"Outras Tradu&ccedil;&otilde;es da GPL GNU 2\">Outras L&iacute;nguas (Vers&otilde;es n&atilde;o oficiais)</a>";
        echo '<span>;</span></li></ul></p>';
        include_once($CFG->dirmods.$modulo.'/licenca_ptbr.html');
        break;
    }
}
