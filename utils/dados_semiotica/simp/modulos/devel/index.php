<?php
//
// SIMP
// Descricao: Arquivo que lista utilidades para Desenvolvimento
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.10
// Data: 30/07/2007
// Modificado: 16/03/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo  = util::get_modulo(__FILE__);
$titulo  = 'Desenvolvimento';
$nav[''] = 'Desenvolvimento';
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_opcoes_devel();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Lista as opcoes de desenvolvimento
//
function listar_opcoes_devel() {
    global $CFG, $modulo;

    // Informacoes
    $info = array('info.php'         => 'Informa&ccedil;&otilde;es do Servidor',
                  'arquivos.php'     => 'Arquivos do Sistema',
                  'funcoes.php'      => 'Fun&ccedil;&otilde;es do Sistema',
                  'classes.php'      => 'Classes do Sistema',
                  'constantes.php'   => 'Constantes',
                  'cookies.php'      => 'Cookies do Sistema',
                  'sessao.php'       => 'Dados da Sess&atilde;o',
                  'config.php'       => 'Dados do $CFG');

    // Informacoes do BD
    $info_bd = array('tabelas_bd.php'   => 'Tabelas do BD',
                     'sqls.php'         => 'SQLs de Instala&ccedil;&atilde;o',
                     'consulta.php'     => 'Consulta Gen&eacute;rica');

    // Utilitarios
    $util = array('exportar.php'         => 'Exportar Dados',
                  'codificar.php'        => 'Codificar/Decodificar Texto',
                  'unicode.php'          => 'Caracteres Unicode',
                  'funcoes_php.php'      => 'Buscar Fun&ccedil;&atilde;o PHP',
                  'testar_expressao.php' => 'Testar Express&atilde;o Regular',
                  'testar_memoria.php'   => 'Testar Mem&oacute;ria');


    echo "<div class=\"bloco_opcoes\" id=\"bloco_info\">\n";
    echo "<h3>Informa&ccedil;&otilde;es Gerais</h3>\n";
    echo "<ul>\n";
    foreach ($info as $arq => $nome) {
        echo "  <li>".link::texto($CFG->wwwmods.$modulo.'/'.$arq, $nome, $nome, '', '', 1)."</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div class=\"bloco_opcoes\" id=\"bloco_info_bd\">\n";
    echo "<h3>Banco de Dados</h3>\n";
    echo "<ul>\n";
    foreach ($info_bd as $arq => $nome) {
        echo "  <li>".link::texto($CFG->wwwmods.$modulo.'/'.$arq, $nome, $nome, '', '', 1)."</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div class=\"bloco_opcoes\" id=\"bloco_util\">\n";
    echo "<h3>Utilit&aacute;rios</h3>\n";
    echo "<ul>\n";
    foreach ($util as $arq => $nome) {
        echo "  <li>".link::texto($CFG->wwwmods.$modulo.'/'.$arq, $nome, $nome, '', '', 1)."</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";

    // Exibir o aviso apenas na primeira vez que acessou a pagina com uma sessao valida
    if (!isset($_SESSION[$modulo]['aviso_inicial'])) {
        mensagem::aviso('P&aacute;gina reservada aos desenvolvedores do sistema');
        $_SESSION[$modulo]['aviso_inicial'] = 1;
    }
}
