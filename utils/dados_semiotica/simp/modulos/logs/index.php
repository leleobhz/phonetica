<?php
//
// SIMP
// Descricao: Lista de logs do sistema
// Autor: Rubens Takiguti Ribeiro && Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.1.4
// Data: 23/05/2007
// Modificado: 09/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do Formulario
$modulo = util::get_modulo(__FILE__);
$classe = 'log_sistema';
$dados  = formulario::get_dados();
$action = $CFG->site;
$ajuda  = <<<AJUDA
  <p>Este formul&aacute;rio lista as opera&ccedil;&otilde;es realizadas no sistema em um 
  determinado per&iacute;odo (iniciado na instala&ccedil;&atilde;o do sistema {$CFG->titulo}).
  Para refinar a busca, deve-se preencher o(s) campo(s) 
  Usu&aacute;rio e/ou IP e/ou Entidade e/ou ID.</p><p>A busca por nome de usu&aacute;rio 
  &eacute; feita atrav&eacute;s da semelhan&ccedil;a com o nome, j&aacute; por IP a busca 
  &eacute; espec&iacute;fica.</p><p>Para n&atilde;o refinar a busca, basta deixar os 
  campos em branco.</p>
  <p>Os poss&iacute;veis campos exibidos pela pesquisa s&atilde;o:</p>
  <ul>
    <li>Usu&aacute;rio: nome do usu&aacute;rio que realizou a a&ccedil;&atilde;o</li>
    <li>Nome da Entidade: nome da entidade que sofreu a a&ccedil;&atilde;o</li>
    <li>Data: data em que ocorreu a a&ccedil;&atilde;o</li>
    <li>A&ccedil;&atilde;o: a&ccedil;&atilde;o realizada pelo usu&aacute;rio</li>
    <li>IP: Endere&ccedil;o de IP do usu&aacute;rio quando realizou a a&ccedil;&atilde;o</li>
    <li>ID: Chave &uacute;nica que identifica a entidade que sofreu a a&ccedil;&atilde;o</li>
    <li>Entidade: Tipo de entidade que sofreu a a&ccedil;&atilde;o</li>
    <li>Detalhes: Outras informa&ccedil;&otilde;es referentes &agrave; a&ccedil;&atilde;o 
    como os dados alterados</li>
  </ul>
  <p>Obs.: recomenda-se n&atilde;o especificar um per&iacute;odo muito longo para evitar a
  sobrecarga do sistema.</p>
AJUDA;


/// Dados da Pagina
$titulo  = 'Logs';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
formulario_log($dados, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Logica de negocio
//
function logica_formulario_log($dados) {
// Array[String => Mixed] $dados: Dados a serem avaliados pela logica de negocio
//
    // Se os dados foram submetidos
    if ($dados) {

        // Montar o time de inicio e termino
        $de  = mktime(0, 0, 0, $dados->de_mes, $dados->de_dia, $dados->de_ano);
        $ate = mktime(0, 0, 0, $dados->ate_mes, $dados->ate_dia + 1, $dados->ate_ano);

        $vt_condicoes = array();

        // Montar condicoes com o intervalo de tempo
        $vt_condicoes[] = condicao_sql::montar('data', '>', $de);
        $vt_condicoes[] = condicao_sql::montar('data', '<', $ate);

        // Tipo de erro
        switch ($dados->tipo_log) {
        case 0:
        case 1:
            $vt_condicoes[] = condicao_sql::montar('erro', '=', $dados->tipo_log);
            break;
        }

        // Se informou o IP
        if (isset($dados->ip) && (!empty($dados->ip))) {
            $vt_condicoes[] = condicao_sql::montar('ip', '=', $dados->ip);
        }

        // Se Informou a Entidade
        if (isset($dados->entidade) && (!empty($dados->entidade))) {
            $vt_condicoes[] = condicao_sql::montar('entidade', '=', $dados->entidade);
        }

        // Se informou o Usuario
        if (!empty($dados->usuario)) {
            $vt_condicoes[] = condicao_sql::montar('usuario:nome', 'like', "%{$dados->usuario}%");
        }
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        return objeto::get_objeto('log_sistema')->consultar_varios($condicoes, true);
    }
    return false;
}


//
//      Imprime o formulario de Log
//
function formulario_log($dados, $action) {
// Array[String => Mixed] $dados: Dados
// String $action: url para o action do formulario 
//
    $resultado = logica_formulario_log($dados);
    imprimir_form($dados, $action);
    imprimir_resultado($resultado, $dados->campos_exibidos);
}


//
//     Metodo que retorna quais sao os campos da busca
//
function get_campos_exibidos() {
    return array(0 => 'Usu&aacute;rio',
                 1 => 'Nome da Entidade',
                 2 => 'Data',
                 3 => 'A&ccedil;&atilde;o',
                 4 => 'IP',
                 5 => 'ID',
                 6 => 'Entidade',
                 7 => 'Detalhes');
}


//
//     Imprime um formulario de filtro do Log
//
function imprimir_form($dados, $action) {
// Object $dados: dados enviados pelo formulario
// String $action: endereco de destino dos dados;
//
    global $CFG;

    // Obter data de instalacao do sistema
    $ano_inicio = (int)strftime('%Y', $CFG->instalacao);

    // Obter data atual
    $ano_atual = (int)strftime('%Y', $CFG->time);

    $vt_campos_exibidos = get_campos_exibidos();

    $vt_entidades = listas::get_entidades();
    $vt_entidades[0] = 'Todos';
    $vet_tipo_log = array(0 => 'Opera&ccedil;&otilde;es com Sucesso',
                          1 => 'Apenas Opera&ccedil;&otilde;es com Erro',
                          2 => 'Todos');
    list($dia, $mes, $ano) = util::get_data_completa($CFG->time);

    // Campos do formulario
    $campos = array('de_dia'   => $dia,
                    'de_mes'   => $mes,
                    'de_ano'   => $ano,
                    'ate_dia'  => $dia,
                    'ate_mes'  => $mes,
                    'ate_ano'  => $ano,
                    'usuario'  => '',
                    'ip'       => '',
                    'entidade' => 0,
                    'tipo_log' => '0',
                    'campos_exibidos' => array(1, 2, 3)
                    );
    $dados = formulario::montar_dados($campos, $dados);
    $dados->campos_exibidos[] = 0;

    $form = new formulario($action, 'form_logs');
    $form->campo_busca('usuario', 'usuario', 'usuario', 'nome', $dados->usuario, null, 128, 30, 'Usu&aacute;rio');
    $form->campo_busca('ip', 'ip', 'log_sistema', 'ip', $dados->ip, null, 15, 30, 'IP');
    $form->campo_select('entidade', 'entidade', $vt_entidades, $dados->entidade, 'Entidade');
    $form->campo_data('de', $dados->de_dia, $dados->de_mes, $dados->de_ano, 'In&iacute;cio', $ano_atual - $ano_inicio, 0);
    $form->campo_data('ate', $dados->ate_dia, $dados->ate_mes, $dados->ate_ano, 'T&eacute;rmino');
    $form->campo_select('tipo_log', 'tipo_log', $vet_tipo_log, $dados->tipo_log, 'Tipo de Log');
    $form->campo_checkbox('campos_exibidos', 'campo_exibidos', $vt_campos_exibidos, $dados->campos_exibidos, 'Campos Exibidos na Pesquisa', 2, array(0));
    $form->campo_submit('enviar', 'enviar', 'Consultar', true);
    $form->imprimir();
}


//
//     Imprime a tabela de Logs
//
function imprimir_resultado($logs, &$vt_campos) {
// Array[Object] $logs: Um vetor de objetos com os dados dos logs
// Array[Int] $vt_campos: Um vetor como os campos a serem exibido
//
    global $CFG;
    if (!is_array($logs)) {
        return;
    }

    $usuarios = array();

    if (!count($logs)) {
        mensagem::aviso('Nenhum Log com estas restri&ccedil;&otilde;es');
        return;
    }
    $vt_campos_exibidos = get_campos_exibidos();

    util::get_cdata($cdata, $fcdata, false);

    echo "<table class=\"tabela\" id=\"lista_logs\">\n";
    echo "<caption>Tabela de Log no Sistema</caption>\n";
    echo "<thead>\n";
    echo "  <tr>\n";
    foreach ($vt_campos_exibidos as $i => $nome) {
        if (in_array($i, $vt_campos)) {
            echo "    <th>{$nome}</th>\n";
        }
    }
    echo "  </tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    foreach ($logs as $log) {
        if (isset($usuarios[$log->cod_usuario])) {
            $nome = $usuarios[$log->cod_usuario];
        } else {
            $u = new usuario('', $log->cod_usuario);
            $nome = $u->nome;
            unset($u);
            $usuarios[$log->cod_usuario] = $nome;
        }
        $ip = $log->ip;
        $data = strftime("{$CFG->formato_data} ({$CFG->formato_hora})", $log->data);
        $operacao = $log->operacao;
        $class = '';
        if ($log->erro) {
            $class = 'class="erro"';
        }
        $cod_entidade = $log->cod_entidade;
        $entidade = $log->entidade;
        $detalhes = $log->detalhes;

        if ($cod_entidade && $entidade) {
            try {
                simp_autoload($entidade);
                $obj = new $entidade('', $cod_entidade);
                if ($obj->existe()) {
                    $nome_entidade = $obj->get_nome();
                }
            } catch (Exception $e) {
                $nome_entidade = '-';
            }
        }

        if (!isset($nome_entidade)) { $nome_entidade = '-'; }

        $dados = array($nome, $nome_entidade, $data, $operacao, $ip, $cod_entidade, $entidade, $detalhes);
        echo "  <tr>\n";
        foreach ($vt_campos_exibidos as $i => $nome) {
            if (in_array($i, $vt_campos)) {
                echo "    <td {$class}>".texto::codificar(wordwrap($dados[$i], 25, "\n"))."</td>\n";
            }
        }
        echo "  </tr>\n";
        unset($nome, $nome_entidade, $data, $operacao, $ip, $cod_entidade, $entidade, $detalhes);
    }
    echo "</tbody>\n";
    echo "</table>\n";
}
