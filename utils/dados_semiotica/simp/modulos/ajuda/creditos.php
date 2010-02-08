<?php
//
// SIMP
// Descricao: Creditos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.7
// Data: 04/06/2007
// Modificado: 20/08/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Cr&eacute;ditos';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');
if (file_exists($CFG->dirmods.$modulo.'/estilos_equipe.css')) {
    $estilos[] = $CFG->wwwmods.$modulo.'/estilos_equipe.css';
}


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo();
$pagina->imprimir_titulo($titulo);
imprimir_creditos();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime os creditos do Sistema
//
function imprimir_creditos() {
    global $CFG, $modulo;

    $nome_sistema = $CFG->titulo;
    $descricao_sistema = $CFG->descricao;
    $versao_sistema = $CFG->versao;
    $data_instalacao = strftime("{$CFG->formato_data} ({$CFG->formato_hora})", $CFG->instalacao);
    $tempo_ativo = util::get_uptime();
    if ($tempo_ativo === false) {
        $tempo_ativo = '(tempo indeterminado)';
    }
    $descricao_simp = DESCRICAO_SIMP;
    $versao_simp = VERSAO_SIMP;

    $equipe = file_get_contents($CFG->dirmods.$modulo.'/equipe.html');
    if (file_exists($CFG->dirmods.$modulo.'/creditos.html')) {
        $creditos = '<div id="creditos">'.
                    file_get_contents($CFG->dirmods.$modulo.'/creditos.html').
                    '</div>';
    } else {
        $creditos = '';
    }

    echo <<<CREDITOS
<h4><strong>{$nome_sistema}</strong> - {$descricao_sistema} - <em>Vers&atilde;o {$versao_sistema}</em></h4>
<p>Este sistema foi desenvolvido por:</p>
<div id="equipe">
{$equipe}
</div>
<hr />
{$creditos}

<div id="simp">
<h2>SIMP</h2>
<p>Descri&ccedil;&atilde;o: {$descricao_simp}.</p>
<p>Vers&atilde;o: {$versao_simp}</p>
<p>Autores:</p>
<ul>
  <li><em>Rubens <span xml:lang="jp" title="&#28381;&#21475;">Takiguti</span> Ribeiro</em> - <span>Projeto inicial e design</span></li>
  <li><em>Rodrigo Pereira Moreira</em> - <span>Colabora&ccedil;&atilde;o</span></li>
</ul>
</div>
<p>Instalado em: {$data_instalacao}</p>
<p>Servidor ativo a {$tempo_ativo}</p>
CREDITOS;
}
