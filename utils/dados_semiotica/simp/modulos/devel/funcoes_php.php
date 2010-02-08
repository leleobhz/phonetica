<?php
//
// SIMP
// Descricao: Script que busca funcoes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 18/01/2008
// Modificado: 31/07/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');
require_once($CFG->dirmods.'devel/bloqueio.php');


/// Dados do Formulario
$action = $CFG->site;
$dados = formulario::get_dados();


/// Dados da Pagina
$titulo = 'Fun&ccedil;&otilde;es PHP';
$nav[$CFG->wwwmods.'devel/index.php'] = 'Desenvolvimento';
$nav[''] = 'Fun&ccedil;&otilde;es PHP';
$estilos = array($CFG->wwwmods.'devel/estilos.css');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_form($dados, $action);
if ($dados) {
    imprimir_funcoes($dados);
}
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de funcoes
//
function imprimir_funcoes($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    $funcao_busca = str_replace(' ', '', $dados->funcao);

    $funcoes_internas = get_defined_functions();
    $funcoes_internas = $funcoes_internas['internal'];
    $semelhanca = array();
    $vt_nivel = array();
    foreach ($funcoes_internas as $funcao) {
        similar_text($funcao_busca, $funcao, $nivel);

        if ($nivel < 100 && (strpos($funcao, $funcao_busca) !== false)) {
            $nivel += (100 - $nivel) * 0.3;
        }

        $obj = new stdClass();
        $obj->funcao = $funcao;
        $obj->nivel  = $nivel;

        $semelhanca[] = $obj;
        $vt_nivel[] = $nivel;
    }
    array_multisort($vt_nivel, SORT_DESC, SORT_NUMERIC, $semelhanca);

    if ($dados->num_resultados > 1) {
        echo "<h2>{$dados->num_resultados} Resultados mais Relevantes</h2>\n";
        echo "<ul>\n";
        for ($i = 0, $n = 0; $i < $dados->num_resultados; $i++) {
            $obj = $semelhanca[$i];
            $funcao = str_replace('_', '-', $obj->funcao);
            echo "<li><a rel=\"blank\" href=\"".MANUAL_PHP."function.{$funcao}.php\" title=\"Documenta&ccedil;&atilde;o de {$obj->funcao}\">".destacar_funcao($obj->funcao, $funcao_busca).'</a>: '.formatar_nivel($obj->nivel).'</li>';
        }
        echo "</ul>\n";
    } else {
        $obj = $semelhanca[0];
        echo "<h2>Resultado mais relevante</h2>\n";
        echo "<p><a rel=\"blank\" href=\"".MANUAL_PHP."function.{$obj->funcao}.php\" title=\"Documenta&ccedil;&atilde;o de {$obj->funcao}\">{$obj->funcao}</a>: ".formatar_nivel($obj->nivel)."</p>";
    }
}


//
//     Retorna o nivel de similaridade entre as strings de forma formatada
//
function formatar_nivel($nivel) {
// Float $nivel: nivel em porcentagem
//
    $n = round($nivel, 2);
    if ($n >= 80) {
        return "<span class=\"verde\">{$n}%</span>";
    } elseif ($n >= 50) {
        return "<span class=\"amarelo\">{$n}%</span>";
    } else {
        return "<span class=\"vermelho\">{$n}%</span>";
    }
}


//
//     Imprime o formulario de busca
//
function imprimir_form($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG;

    $campos = array('funcao' => '', 'num_resultados' => 10);
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'form_busca');
    $form->campo_text('funcao', 'funcao', $dados->funcao, 128, 30, 'Fun&ccedil;&atilde;o PHP');
    $form->campo_text('num_resultados', 'num_resultados', $dados->num_resultados, 4, 30, 'N&uacute;mero de Resultados', 0, 0, 0, 'uint');
    $form->campo_submit('enviar', 'enviar', 'Buscar');
    $form->imprimir();
}


//
//     Destaca uma funcao para impressao
//
function destacar_funcao($funcao_encontrada, $funcao_buscada) {
// String $funcao_encontrada: nome da funcao encontrada
// String $funcao_buscada: nome da funcao buscada
//
    $pos = strpos($funcao_encontrada, $funcao_buscada);
    if ($pos === false) {
        return $funcao_encontrada;
    }
    return substr($funcao_encontrada, 0, $pos).
           '<strong>'.
           $funcao_buscada.
           '</strong>'.
           substr($funcao_encontrada, $pos + strlen($funcao_buscada));
}
