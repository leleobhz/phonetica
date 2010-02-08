<?php
//
// SIMP
// Descricao: Arquivo que lista as tabelas do BD
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.1.0
// Data: 31/07/2007
// Modificado: 05/08/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Tabelas do BD';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Tabelas do BD';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_tabelas();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Lista as tabelas
//
function listar_tabelas() {
    global $CFG;

    if (!$CFG->instalacao) {
        echo '<p>O sistema ainda n&atilde;o foi instalado.</p>';
        return;
    }

    $bd = new objeto_dao();
    $bd->carregar('operacao');
    $tabelas = $bd->get_tabelas();

    if (!is_array($tabelas) || !count($tabelas)) {
        echo "<p>Nenhuma tabela instalada no sistema.</p>\n";
        return;
    }

    iniciar_sessao();
    tratar_operacao();

    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    echo '<div id="lista_tabelas">';
    echo '<table class="tabela">';
    echo '<caption>Tabelas do BD</caption>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Nome</th>';
    echo '<th><abbr title="Colunas">C</abbr></th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($tabelas as $tabela) {
        $link_tabela = link::adicionar_atributo($link_base, 'tabela', $tabela->nome);

        echo '<tr>';
        echo '<td>'.$tabela->nome.'</td>';
        echo '<td>';
        $link_coluna = link::adicionar_atributo($link_tabela, 'op', 'ver_colunas');
        echo link::icone($link_coluna, icone::endereco('rotulo'), 'Colunas');
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    echo '<div id="conteudo_tabela">';
    exibir_conteudo($bd, $tabelas);
    echo '</div>';
}


//
//     Inicializa a sessao
//
function iniciar_sessao() {
    if (isset($_SESSION['devel']['tabelas_bd'])) {
        return;
    }
    $_SESSION['devel']['tabelas_bd'] = array('acao'   => null,
                                             'tabela' => null);
}


//
//     Trata uma operacao
//
function tratar_operacao() {
    if (!isset($_GET['op'])) {
        return;
    }
    switch ($_GET['op']) {
    case 'ver_colunas':
        $_SESSION['devel']['tabelas_bd']['acao'] = 'ver_colunas';
        $_SESSION['devel']['tabelas_bd']['tabela'] = $_GET['tabela'];
        break;
    }
}


//
//     Exibem o conteudo de acordo com a acao
//
function exibir_conteudo($bd, $tabelas) {
// objeto_dao $bd: conexao com o BD
// Array[String => Object] $tabelas: dados das tabelas
//
    switch ($_SESSION['devel']['tabelas_bd']['acao']) {
    case 'ver_colunas':
        exibir_colunas($bd, $tabelas, $_SESSION['devel']['tabelas_bd']['tabela']);
        break;
    }
}


//
//     Exibe as colunas de uma tabela
//
function exibir_colunas($bd, $tabelas, $nome_tabela) {
// objeto_dao $bd: conexao com o BD
// Array[String => Object] $tabelas: dados das tabelas
// String $nome_tabela: nome da tabela
//
    $tabela = $tabelas[$nome_tabela];
    $campos = $bd->get_campos($nome_tabela);

    echo "<table class=\"tabela\" summary=\"Lista de campos da tabela {$tabela->nome}\">\n";
    if ($tabela->nome != $tabela->comentario) {
        echo "<caption>{$tabela->comentario}</caption>\n";
    } else {
        echo "<caption>{$tabela->nome}</caption>\n";
    }
    echo "<thead>\n";
    echo "<tr>\n";
    foreach ($bd->get_descricao_campos() as $caracteristica => $descricao) {
        echo "  <th id=\"campo_{$caracteristica}\">{$descricao}</th>\n";
    }
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    foreach ($campos as $campo) {
        echo "<tr>\n";
        foreach ($bd->get_descricao_campos() as $caracteristica => $descricao) {
            if ($caracteristica == 'default') {
                $valor = util::exibir_var($campo->$caracteristica, UTIL_EXIBIR_PHP);
            } else {
                $valor = util::exibir_var($campo->$caracteristica, UTIL_EXIBIR_NATURAL);
            }
            echo "  <td headers=\"campo_{$caracteristica}\">{$valor}</td>\n";
        }
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
}
