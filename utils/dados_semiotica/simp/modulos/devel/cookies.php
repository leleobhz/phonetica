<?php
//
// SIMP
// Descricao: Arquivo que lista os cookies do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.6
// Data: 30/07/2007
// Modificado: 10/03/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Cookies do Sistema';
$nav = array();
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Cookies';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Obter cookies
$vet = $CFG->cookies;
$vet2 = array_keys($vet);
array_multisort($vet2, SORT_STRING, SORT_ASC, $vet);


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_dados_cookie($vet);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime os dados do cookie
//
function imprimir_dados_cookie($cookies) {
// String $cookies: vetor com os dados dos cookies
//
    global $CFG;

    /// Definir dados do cookie
    $nome_cookie = 'cookie_'.$CFG->sistema;
    $cookie_decodificado = cookie::decodificar($_COOKIE[$nome_cookie]);

    $tam_cookie_codificado = strlen($_COOKIE[$nome_cookie]);
    $tam_cookie_decodificado = strlen($cookie_decodificado);

    $max_cookie  = 4096;

    echo "<p><strong>Nome do cookie:</strong> {$nome_cookie}</p>\n";
    echo "<p><strong>Valor de \$_COOKIE['{$nome_cookie}'] (decodificado e com quebra de linha no caractere 80):</strong></p>\n";
    echo '<pre id="block_cookie">'.chunk_split($cookie_decodificado, 80)."</pre>\n";
    echo '<p><strong>Tamanho Codificado:</strong> '.texto::formatar_bytes($tam_cookie_codificado, 1).'</p>';
    echo '<p><strong>Tamanho Decodificado:</strong> '.texto::formatar_bytes($tam_cookie_decodificado, 1).'</p>';
    echo '<p>Limite recomendado: '.texto::formatar_bytes($max_cookie, 1)."</p>\n";
    imprimir_grafico_cookie($tam_cookie_codificado, $max_cookie);

    echo "<table class=\"tabela\">\n";
    echo "<caption>Tabela de Dados do Cookie</caption>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>Campo</th>\n";
    echo "<th>Valor</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    foreach ($cookies as $campo => $valor) {
        echo "<tr>";
        echo "<td>{$campo}</td>";
        echo '<td>'.util::exibir_var($valor, UTIL_EXIBIR_PHP).'</td>';
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo '<p>Cookies podem ser obtidos pelo vetor associativo <code>$CFG-&gt;cookies</code> que &eacute; indexado pelo nome do cookie.</p>';
}


//
//     Imprime um grafico de utilizacao do cookie
//
function imprimir_grafico_cookie($tam_cookie, $max_cookie) {
// Int $tam_cookie: tamanho do cookie em bytes
// Int $max_cookie: tamanho maximo do cookie em bytes
//
    $porcentagem = $tam_cookie * 100 / $max_cookie;
    echo grafico_barra::gerar('Utiliza&ccedil;&atilde;o', $porcentagem);
}
