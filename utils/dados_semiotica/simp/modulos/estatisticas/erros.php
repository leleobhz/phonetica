<?php
//
// SIMP
// Descricao: Pagina de estatisticas do numero de erros
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 24/10/2007
// Modificado: 03/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Formulario
$dados = formulario::get_dados();
$campos = array('de', 'ate');
$action = $CFG->site;
$ajuda = <<<AJUDA
  <p>O formul&aacute;rio abaixo lista a quantidade de erros ocorridos em um determinado per&iacute;odo. 
  &Eacute; recomend&aacute;vel selecionar um per&iacute;odo de no m&aacute;ximo cinco meses
  para evitar a sobrecarga do sistema.</p> 
AJUDA;


/// Dados da pagina
$titulo  = 'N&uacute;mero de Erros';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = false;


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
imprimir_form($dados, $action, $campos);
if ($dados) {
    grafico::exibir_grafico('Gr&aacute;fico de Erros', $CFG->wwwmods.$modulo.'/grafico_erros.php',
                            $CFG->dirmods.$modulo.'/grafico_erros.php', $dados);
}
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime o formulario
//
function imprimir_form($dados, $action, &$campos) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
// Array[String] $campos: campos do formulario
//
    global $CFG;

    if (!isset($dados)) {
        $dados = new stdClass();
    }

    // Vetor de periodo
    list($dia, $mes, $ano) = util::get_data_completa($CFG->instalacao);
    do {
        $time_de  = mktime(0, 0, 0, $mes, 1, $ano);
        $time_ate = mktime(0, 0, -1, $mes + 1, 1, $ano);

        list($time_dia, $time_mes, $time_ano) = util::get_data_completa($time_de);
        $vet_de[$time_de] = strftime($CFG->formato_data, $time_de);

        list($time_dia, $time_mes, $time_ano) = util::get_data_completa($time_ate);
        $vet_ate[$time_ate] = strftime($CFG->formato_data, $time_ate);

        $mes++;
    } while ($time_ate <= $CFG->time);

    $dados->de = isset($dados->de) ? $dados->de : 0;
    if (!isset($dados->ate)) { $dados->ate = $time_ate; }

    $campos = array('de' => 0, 'ate' => 0);
    $dados = formulario::montar_dados($campos, $dados);

    // Imprimir o formulario
    $form = new formulario($action, 'form_acessos');
    $form->campo_select('de', 'de', $vet_de, $dados->de, 'In&iacute;cio');
    $form->campo_select('ate', 'ate', $vet_ate, $dados->ate, 'T&eacute;rmino');
    $form->campo_submit('enviar', 'enviar', 'Consultar', true);
    $form->imprimir();
}
