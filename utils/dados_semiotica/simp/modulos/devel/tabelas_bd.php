<?php
//
// SIMP
// Descricao: Arquivo que lista as tabelas do BD
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.2
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

    echo "<ol>\n";
    foreach ($tabelas as $tabela) {
        echo "<li><a href=\"{$CFG->site}#tab_{$tabela->nome}\">{$tabela->nome}</a> - {$tabela->comentario}</li>";
    }
    echo "</ol>\n";
    foreach ($tabelas as $tabela) {
        echo "<h2 id=\"tab_{$tabela->nome}\">$tabela->nome</h2>\n";
        $campos = $bd->get_campos($tabela->nome);

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
}
