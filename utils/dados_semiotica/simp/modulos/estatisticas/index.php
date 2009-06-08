<?php
//
// SIMP
// Descricao: Pagina de estatisticas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 25/06/2007
// Modificado: 03/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados da pagina
$titulo  = 'Estat&iacute;sticas';
$nav[]   = '#index.php';
$nav[]   = 'estatisticas#index.php';
$estilos = array($CFG->wwwmods.'estatisticas/estilos.css');


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
lista_estatisticas();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Retorna uma lista de estatisticas
//
function lista_estatisticas() {
    global $CFG;
    $lista = array('acessos.php'     => 'N&uacute;mero de Acessos',
                   'erros.php'       => 'N&uacute;mero de Erros',
                   'atividades.php'  => 'N&uacute;mero de Atividades no Sistema',
                   'navegadores.php' => 'Navegadores Utilizados',
                   'so.php'          => 'Sistemas Operacionais Utilizados');

    echo "<ul>\n";
    foreach ($lista as $arq => $nome) {
        $l = $CFG->wwwmods.'estatisticas/'.$arq;
        echo "  <li>".link::texto($l, $nome, $nome, '', '', 1)."</li>\n";
    }
    echo "</ul>\n";
}
