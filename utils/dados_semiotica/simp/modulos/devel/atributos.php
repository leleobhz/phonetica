<?php
//
// SIMP
// Descricao: Arquivo que lista atributos semelhantes no BD
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 21/10/2009
// Modificado: 21/10/2009
// License: LICENSE.TXT
// Copyright (C) 2009  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Constantes
define('DEVEL_ATRIBUTOS_NOME', 1);
define('DEVEL_ATRIBUTOS_TIPO', 2);


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Atributos Comuns';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Atributos Comuns';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_atributos($pagina);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Lista os atributos comuns
//
function listar_atributos($pagina) {
// pagina $pagina: pagina que exibe os dados
//
    global $CFG;

    $icone_verde = icone::img('verde', 'igual');
    $icone_vermelho = icone::img('vermelho', 'diferente');

    iniciar_sessao();

    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    // Tratar dados
    if (isset($_GET['op'])) {
        switch ($_GET['op']) {
        case 'abrir_atributo':
            abrir_atributo($_GET['atributo']);
            break;
        case 'fechar_atributo':
            fechar_atributo($_GET['atributo']);
            break;
        }
    }

    $vt_atributos = get_atributos_nome();
    echo '<ol>';
    foreach ($vt_atributos as $atributo => $definicoes) {
        $aberto = atributo_aberto($atributo);
        if ($aberto) {
            $link = link::adicionar_atributo($link_base, 'op', 'fechar_atributo');
        } else {
            $link = link::adicionar_atributo($link_base, 'op', 'abrir_atributo');
        }
        $link = link::adicionar_atributo($link, 'atributo', $atributo);

        echo '<li>';
        link::texto($link, $atributo, $atributo, false, false, false, true, false);
        echo ' ('.count($definicoes).')';
        if ($aberto) {
            echo '<table class="tabela">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="row">Classe</th>';
            foreach ($definicoes as $definicao) {
                echo '<th rowspan="2" scope="col">'.$definicao->classe.'</th>';
            }
            echo '<th rowspan="2">Igual</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th scope="col">Caracter&iacute;stica</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach (atributo::get_caracteristicas() as $caracteristica) {
                if ($caracteristica == 'classe') { continue; }
                echo '<tr>';
                echo '<th scope="row">'.$caracteristica.'</th>';

                $igual = true;
                unset($valor_antigo);
                foreach ($definicoes as $definicao) {
                    $valor = $definicao->$caracteristica;
                    if ($igual) {
                        if (isset($valor_antigo)) {
                            if ($valor !== $valor_antigo) {
                                $igual = false;
                            }
                        }
                        $valor_antigo = $valor;
                    }
                    echo '<td>'.util::exibir_var($valor, UTIL_EXIBIR_PHP).'</td>';
                }
                echo '<td>'.($igual ? $icone_verde : $icone_vermelho).'</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        echo '</li>';
    }
    echo '</ol>';
}


//
//     Verifica se um atributo esta aberto ou fechado
//
function atributo_aberto($atributo) {
// String $atributo: nome do atributo
//
    return isset($_SESSION['devel']['atributos']['abertos'][$atributo]);
}


//
//     Abre um atributo
//
function abrir_atributo($atributo) {
// String $atributo: nome do atributo
//
    $_SESSION['devel']['atributos']['abertos'][$atributo] = true;
}


//
//     Fecha um atributo
//
function fechar_atributo($atributo) {
// String $atributo: nome do atributo
//
    unset($_SESSION['devel']['atributos']['abertos'][$atributo]);
}


//
//     Inicializa a sessao
//
function iniciar_sessao() {
    if (isset($_SESSION['devel']['atributos'])) {
        return;
    }
    $_SESSION['devel']['atributos']['abertos'] = array();
}


//
//     Retorna a lista de atributos agrupados por nomes identicos
//
function get_atributos_nome() {
    $vt_atributos = array();
    $vt_nomes = array();
    foreach (listas::get_entidades() as $classe => $descricao_classe) {
        $obj = new $classe();
        foreach ($obj->get_atributos() as $atributo => $def_atributo) {
            if (!$def_atributo->chave) {
                if (!isset($vt_atributos[$atributo])) {
                    $vt_atributos[$atributo] = array();
                }
                $vt_atributos[$atributo][] = $def_atributo;
                $vt_nomes[$atributo] = $atributo;
            }
        }
    }

    array_multisort($vt_nomes, SORT_ASC, SORT_STRING, $vt_atributos);

    return $vt_atributos;
}
