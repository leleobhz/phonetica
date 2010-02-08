<?php
//
// SIMP
// Descricao: Arquivo que lista os dados da sessao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 29/10/2007
// Modificado: 07/02/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Dados da Sess&atilde;o';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Dados da Sess&atilde;o';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_dados_sessao();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime os dados armazenados em sessao
//
function imprimir_dados_sessao() {
    $total = 0;

    echo "<table class=\"tabela\">\n";
    echo "<caption>Tabela de Dados Armazenados em Sess&atilde;o</caption>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>Campo</th>\n";
    echo "<th>Valor</th>\n";
    echo "<th>Tamanho</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    foreach ($_SESSION as $campo => $valor) {
        $subtotal = strlen(serialize($_SESSION[$campo]));
        $total += $subtotal;
        echo "<tr>";
        echo "<td>{$campo}</td>";
        if (is_scalar($valor)) {
            $tamanho = strlen($_SESSION[$campo]);
            echo "<td>{$valor}</td>";
        } elseif (is_array($valor)) {
            echo '<td>Vetor ['.count($valor).']</td>';
        }
        echo '<td>'.texto::formatar_bytes($subtotal).'</td>';
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";

    echo '<p><strong>Tamanho Total:</strong> '.texto::formatar_bytes($total, 1)."</p>\n";
}
