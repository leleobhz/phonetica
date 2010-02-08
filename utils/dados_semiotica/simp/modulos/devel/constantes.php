<?php
//
// SIMP
// Descricao: Script que lista as funcoes de cada arquivo do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.5
// Data: 19/05/2008
// Modificado: 15/07/2008
// License: LICENSE.TXT
// Copyright (C) 2008  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Lista de Constantes';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Constantes';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_constantes();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// @ Funcoes


//
//     Imprime a lista de constantes
//
function imprimir_constantes() {
    global $CFG;

    if (!isset($_SESSION['devel']['classes_constante'])) {
        $_SESSION['devel']['classes_constante'] = array();
    }
    if (isset($_GET['classe'])) {
        if (isset($_SESSION['devel']['classes_constante'][$_GET['classe']])) {
            unset($_SESSION['devel']['classes_constante'][$_GET['classe']]);
        } else {
            $_SESSION['devel']['classes_constante'][$_GET['classe']] = true;
        }
    }

    $classes = array_keys(listas::get_entidades());
    sort($classes);

    echo '<ul>';
    foreach ($classes as $classe) {
        $arquivo = realpath($CFG->dirclasses.'entidade/'.$classe.'.class.php');
        if (is_file($arquivo)) {
            $link = $CFG->site;
            link::normalizar($link, true);
            $link = link::adicionar_atributo($link, 'classe', $classe);

            echo "<li>\n";
            link::texto($link, $classe);
            if (isset($_SESSION['devel']['classes_constante'][$classe])) {
                $constantes = parser_simp::get_constantes_arquivo($arquivo);
                imprimir_vetor_constantes($constantes);
            }
            echo "</li>\n";
        }
    }
    echo '</ul>';
}


//
//     Imprime um vetor de constantes de forma hierarquica
//
function imprimir_vetor_constantes($constantes) {
// Array[String => String] $constantes: vetor de constantes de forma hierarquica
//
    if (!count($constantes)) {
        echo '<p>Nenhuma constante</p>';
        return;
    }

    echo "<ul>\n";
    foreach ($constantes as $constante => $valor) {
        if (is_array($valor)) {
            echo '<li>'.$constante.':';
            imprimir_vetor_constantes($valor);
            echo '</li>';
        } else {
            $php = '$nome = '.$constante.';';
            eval($php);

            $valor = trim($valor);
            $valor_real = defined($nome) ? constant($nome) : '';
            $valor_real = util::exibir_var($valor_real);
            echo "<li><strong>{$constante}:</strong> <span title=\"{$valor_real}\">{$valor}</span></li>\n";
        }
    }
    echo "</ul>\n";
}
