<?php
//
// SIMP
// Descricao: info do servidor Apache
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 31/07/2007
// Modificado: 14/02/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo  = util::get_modulo(__FILE__);
$titulo  = 'Informa&ccedil;&otilde;es do Servidor';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Informa&ccedil;&otilde;es do Servidor';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Obtendo dados do servidor
$info = get_phpinfo();


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo();
echo $info;
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Obtem dados do servidor
//
function get_phpinfo() {
    $opcoes = INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_ENVIRONMENT;

    ob_start();
    phpinfo($opcoes);
    $info = ob_get_contents();
    ob_end_clean();
    $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
    $info = str_replace('<table ', '<table class="tabela tabela_info" ', $info);
    return $info;
}
