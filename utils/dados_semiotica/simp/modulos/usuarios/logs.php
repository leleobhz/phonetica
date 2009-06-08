<?php
//
// SIMP
// Descricao: Arquivo para vericar os logs do usuario
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.1.0.0
// Data: 20/06/2007
// Modificado: 30/04/2008
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Formulario
$modulo  = util::get_modulo(__FILE__);
$classe  = 'usuario';
$dados   = formulario::get_dados();
$campos  = array('nome');
$action  = $CFG->site;
$ajuda   = <<<AJUDA
  <p>Este formul&aacute;rio ajuda a listar as opera&ccedil;&otilde;es realizadas por um 
  usu&aacute;rio no sistema em um determinado per&iacute;odo.</p>
  <p>Recomenda-se que o per&iacute;odo entre o in&iacute;cio e t&eacute;rmino n&atilde;o passe
  de tr&ecirc;s meses para evitar a sobrecarga do sistema.</p>
AJUDA;


/// Consultar Entidade
$entidade = util::get_entidade($classe, $campos, false, false, false, $USUARIO->get_valor_chave());


/// Dados da Pagina
$titulo  = 'Logs'.(($entidade->get_valor_chave() != $USUARIO->get_valor_chave()) ? ' de '.$entidade->nome : '');
$nav[]   = '#index.php';
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#logs.php';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css.php');


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
formulario_logs($dados, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Faz a logica de negocios da consulta aos logs
//
function formulario_logs(&$dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG, $entidade;

    imprimir_form($dados, $action);
    if ($dados) {
        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar($entidade->get_chave(), '=', $entidade->get_valor_chave());
        $vt_condicoes[] = condicao_sql::montar('data', '>', $dados->de);
        $vt_condicoes[] = condicao_sql::montar('data', '<', $dados->ate);
        $condicoes = condicao_sql::sql_and($vt_condicoes);

        $logs = objeto::get_objeto('log_sistema')->consultar_varios($condicoes, true);
        imprimir_resultado($logs);
    }
}


//
//     Imprime o formulario de periodo
//
function imprimir_form($dados = null, $action) {
// Object $dados: dados enviados pelo formulario
// String $action: endereco de destino dos dados
//
    global $CFG;

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

    if (!isset($dados->de))  { $dados->de  = $time_de;  }
    if (!isset($dados->ate)) { $dados->ate = $time_ate; }

    $campos = array('de' => 0, 'ate' => 0);
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'form_logs');
    $form->campo_select('de', 'de', $vet_de, $dados->de, 'In&iacute;cio');
    $form->campo_select('ate', 'ate', $vet_ate, $dados->ate, 'T&eacute;rmino');
    $form->campo_submit('enviar', 'enviar', 'Consultar', true);
    $form->imprimir();
}


//
//     Imprime a tabela de logs
//
function imprimir_resultado($logs) {
// Array[Object] $logs: Vetor com resultado da consulta de logs
//
    global $CFG;
    $usuarios = array();

    $tam = count($logs);
    if (!$tam) {
        echo "<p class=\"aviso\">Nenhum Log com estas restri&ccedil;&otilde;es</p>\n";
        return;
    }

    // Criar matriz [data][hora] = log
    foreach ($logs as $log) {
         $data = strftime($CFG->formato_data, $log->data);
         $hora = strftime($CFG->formato_hora, $log->data);
         $vetor[$data][$hora] = $log;
    }

    // Imprimir tabela
    echo "<table class=\"tabela\" id=\"tabela_log\">\n";
    echo "<caption>Tabela de Log no Sistema</caption>\n";
    echo "<thead>\n";
    echo "  <tr>\n";
    echo "    <th>Data</th>\n";
    echo "    <th>Hora</th>\n";
    echo "    <th>Opera&ccedil;&atilde;o</th>\n";
    echo "    <th>Entidade</th>\n";
    echo "    <th>ID</th>\n";
    echo "    <th>Detalhes</th>\n";
    echo "  </tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";

    foreach ($vetor as $data => $vt_horas) {
        $tamanho = count($vt_horas);

        list($hora, $log) = each($vt_horas);
        array_shift($vt_horas);

        $class = $log->erro ? ' class="erro"' : '';

        echo "  <tr>\n";
        echo "    <td rowspan=\"{$tamanho}\" class=\"data_log\">{$data}</td>\n";
        echo "    <td{$class}>{$hora}</td>\n";
        echo "    <td{$class}>{$log->operacao}</td>\n";
        echo "    <td{$class}>{$log->entidade}</td>\n";
        echo "    <td{$class}>{$log->cod_entidade}</td>\n";
        echo "    <td{$class}>".texto::codificar($log->detalhes)."</td>\n";
        echo "  </tr>\n";

        foreach ($vt_horas as $hora => $log) {
            $class = $log->erro ? ' class="erro"' : '';

            echo "  <tr>\n";
            echo "    <td{$class}>{$hora}</td>\n";
            echo "    <td{$class}>{$log->operacao}</td>\n";
            echo "    <td{$class}>{$log->entidade}</td>\n";
            echo "    <td{$class}>{$log->cod_entidade}</td>\n";
            echo "    <td{$class}>".texto::codificar($log->detalhes)."</td>\n";
            echo "  </tr>\n";
        }
    }

    echo "</tbody>\n";
    echo "</table>\n";
}
