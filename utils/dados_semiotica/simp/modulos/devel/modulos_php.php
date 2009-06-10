<?php
//
// SIMP
// Descricao: Script que lista as funcoes de cada arquivo do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 09/06/2009
// Modificado: 09/06/2009
// License: LICENSE.TXT
// Copyright (C) 2009  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'M&oacute;dulos do PHP';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'M&oacute;dulos do PHP';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_tabela_recursos();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// @ Funcoes


//
//     Imprime a tabela de recursos do PHP
//
function imprimir_tabela_recursos() {
    $instalacao = new instalacao();
    $instalacao->checar_modulos($vt_modulos);

    $verde    = icone::img('verde');
    $vermelho = icone::img('vermelho');

    $vt_importancias = array(
        0 => 'N&atilde;o precisa e possivelmente n&atilde;o ser&aacute; usado',
        1 => 'N&atilde;o precisa, mas pode ser &uacute;til no futuro',
        2 => 'N&atilde;o precisa, mas pode ser muito &uacute;til no futuro',
        3 => 'N&atilde;o precisa, mas melhora o desempenho quando utilizado',
        4 => 'Precisa em caso de escolha (do SGBD por exemplo)',
        5 => 'Precisa para situa&ccedil;&otilde;es especiais',
        6 => 'Indispens&aacute;vel para instala&ccedil;&atilde;o'
    );

    echo '<p>Lista com os graus de import&acirc;ncias:</p>';
    echo '<ul>';
    foreach ($vt_importancias as $num => $importancia) {
        echo '<li>'.$num.' = '.$importancia.'</li>';
    }
    echo '</ul>';

    echo '<table class="tabela">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>M&oacute;dulo</th>';
    echo '<th><abbr title="Grau de Import&acirc;ncia">I</abbr></th>';
    echo '<th><abbr title="Carregado">C</abbr></th>';
    echo '<th><abbr title="Situa&ccedil;&atilde;o">S</abbr></th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($vt_modulos as $obj) {
        echo '<tr>';
        echo '<td>'.$obj->modulo.'</td>';
        echo '<td><abbr title="'.$vt_importancias[$obj->importancia].'">'.$obj->importancia.'</abbr></td>';
        echo '<td>'.($obj->carregado ? $verde : $vermelho).'</td>';
        echo '<td>'.($obj->situacao ? $verde : $vermelho).'</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
