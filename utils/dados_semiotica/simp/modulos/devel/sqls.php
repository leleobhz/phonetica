<?php
//
// SIMP
// Descricao: Arquivo que lista as SQL's de instalacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.3
// Data: 20/09/2007
// Modificado: 01/06/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'SQLs de Instala&ccedil;&atilde;o';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'SQLs de Instala&ccedil;&atilde;o';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Dados opcionais
$sgbd = util::get_dado('sgbd', 'string', false, false);


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_sgbds($CFG->site);
if ($sgbd) {
    imprimir_sql_instalacao($sgbd);
}
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Lista as os SGBDs suportados
//
function listar_sgbds($link) {
// String $link: link da pagina atual
//
    global $CFG;

    $sgbd = isset($_GET['sgbd']) ? $_GET['sgbd'] : false;

    $drivers = objeto_dao::get_drivers();
    echo '<p>Lista de Drivers:</p>';
    echo '<ul>';
    foreach ($drivers as $driver) {
        $l = link::adicionar_atributo($link, 'sgbd', $driver->codigo);
        $nome = $driver->nome;
        $descricao = 'SQLs de instala&ccedil;&atilde;o em '.$driver->nome;

        echo '<li>';
        if ($sgbd == $driver->codigo) {
            echo '<strong>';
            link::texto($l, $nome, $descricao);
            echo ' - <span>'.$driver->codigo.'</span>';
            echo '</strong>';
        } else {
            link::texto($l, $nome, $descricao);
            echo ' - <span>'.$driver->codigo.'</span>';
        }
        echo '</li>';
    }
    echo '</ul>';
}


//
//     Imprime as SQLs de instalacao no SGBD especificado
//
function imprimir_sql_instalacao($sgbd) {
// String $sgbd: codigo do SGBD a ser utilizado
//
    global $CFG;
    try {
        $bd = new objeto_dao($sgbd);
        $bd->carregar('operacao');
        $bd->set_exibicao_usuario(true);
    } catch (Exception $e) {
        mensagem::erro('Driver n&atilde;o suportado: '.$sgbd.' (Erro: '.$e->getMessage().')');
        return;
    }

    $vt_sql = array();

    if (isset($CFG->base)) {
        $vt_sql[] = $bd->sql_create_database(OBJETO_DAO_BASE, OBJETO_DAO_CHARSET);
        $sql_mudar_bd = $bd->sql_use_database();
        if ($sql_mudar_bd) {
            $vt_sql[] = $sql_mudar_bd;
        }
    }

    $i = new instalacao();
    foreach ($i->get_objetos() as $obj) {
        $vt_sql[] = $bd->sql_create_table($obj, OBJETO_DAO_CHARSET);
    }

    echo '<h2>SQLs de instala&ccedil;&atilde;o para '.$bd->get_nome().':</h2>';
    echo '<pre class="sqls">';
    echo $bd->formatar_sql($vt_sql, "\n\n");
    echo '</pre>';
}
