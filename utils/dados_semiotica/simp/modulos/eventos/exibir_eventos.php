<?php
//
// SIMP
// Descricao: Arquivo mostra e altera os eventos cadastrados para o dia
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.1.0.6
// Data: 09/08/2007
// Modificado: 23/03/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados recebidos por GET
$modulo = util::get_modulo(__FILE__);
$padrao = isset($_SESSION[$modulo]['time']) ? $_SESSION[$modulo]['time'] : 0;
$time = util::get_dado('time', 'int', false, $padrao);

/// Dados da pagina
$dados_pagina = new stdClass();
if ($time) {
    $dados_pagina->titulo = 'Eventos do dia '.strftime($CFG->formato_data, $time);
} else {
    $dados_pagina->titulo = 'Todos Eventos';
}

modulo::pagina('imprimir_eventos', $dados_pagina);


//
//     Imprime a lista de eventos
//
function imprimir_eventos($pagina) {
// pagina $pagina: pagina que exibe os dados
//
    global $USUARIO, $CFG;

    $modulo   = util::get_modulo(__FILE__);
    $id_lista = 'lista_eventos';
    $link     = $CFG->site;
    $ajuda    = <<<AJUDA
<p>A tabela a seguir apresenta a lista de eventos registrados no sistema. 
  As op&ccedil;&otilde;es poss&iacute;veis s&atilde;o editar dados do evento 
  e excluir evento(somente para administradores ou o criador do evento).</p>
AJUDA;


    /// Dados recebidos por GET
    $padrao = isset($_SESSION[$modulo]['time']) ? $_SESSION[$modulo]['time'] : 0;
    $time = util::get_dado('time', 'int', false, $padrao);

    // Se informou um time especifico, usa-lo
    $paginacao = new paginacao($modulo, $id_lista, $link);
    if (util::usou_get('time')) {
        $paginacao->salvar_pagina(1);
    }
    $_SESSION[$modulo]['time'] = $time;

    // Se especificou a data
    if ($time) {
        $dia = getdate($time);
        $time_dia_seguinte = mktime(0, 0, 0, $dia['mon'], $dia['mday'] + 1, $dia['year']);

        $dia = strftime('%d-%m-%Y-%H-%M-%S', $time);
        $dia_seguinte = strftime('%d-%m-%Y-%H-%M-%S', $time_dia_seguinte);

        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('data', '>=', $dia);
        $vt_condicoes[] = condicao_sql::montar('data', '<', $dia_seguinte);
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        $titulo = 'Eventos do dia '.strftime($CFG->formato_data, $time);

    // Se nao, consultar todos eventos
    } else {
        $condicoes = null;
        $titulo = 'Todos Eventos';
    }

    $classlinha = 'class="linha"';
    $classop    = 'class="opcoes"';

    $pagina_atual = $paginacao->get_pagina();
    $evento = new evento();
    $total = $evento->quantidade_registros($condicoes);

    $campos = array('nome, cod_usuario, visibilidade');
    $ordem = array('data' => true);
    $index = false;
    $itens_pagina = false;

    $eventos = $evento->consultar_varios($condicoes, $campos, $ordem, $index, $itens_pagina, $pagina_atual);
    $total_eventos = 0;

    if ($USUARIO->possui_grupo(COD_ADMIN)) {
        $eventos_visiveis = $eventos;
        $total_eventos = count($eventos_visiveis);
    } else {
        $eventos_visiveis = array();
        foreach ($eventos as $e) {
            if (($e->visibilidade == EVENTO_TODOS_GRUPOS) ||
                $USUARIO->possui_grupo($e->visibilidade) ||
                ($USUARIO->cod_usuario == $e->cod_usuario)) {
                $eventos_visiveis[] = $e;
                $total_eventos++;
            }
        }
    }
    unset($eventos);

    $total_paginas = $paginacao->numero_paginas($total_eventos, $itens_pagina);
    $titulo = $paginacao->titulo($pagina_atual, $total_paginas);
    $rodape = $paginacao->rodape($evento, $total_eventos);

    mensagem::comentario($CFG->site, $ajuda);
    lista::inicio($titulo, $id_lista);
    foreach ($eventos_visiveis as $e) {
        $cod   = $e->get_valor_chave();
        $chave = $e->get_chave();
        $nome = $e->exibir('nome').' - '.$e->exibir('data');

        // Gerar as opcoes
        $tem_permissao = $USUARIO->possui_grupo(COD_ADMIN);
        if ($tem_permissao || ($USUARIO->cod_usuario == $e->cod_usuario)) {
            $opcoes = "<div {$classop}>".
                      "<strong class=\"hide\">Op&ccedil;&otilde;es:</strong>".
                      link::icone("{$CFG->wwwmods}{$modulo}/alterar.php?{$chave}={$cod}", icone::endereco('editar'), 'Editar').
                      "<span class=\"hide\">|</span>".
                      link::icone("{$CFG->wwwmods}{$modulo}/excluir.php?{$chave}={$cod}", icone::endereco('excluir'), 'Excluir').
                      "</div>\n";
        } else {
            $opcoes = '';
        }

        $link = $CFG->wwwmods.$modulo.'/exibir.php?cod_evento='.$cod;

        // Imprime a linha da lista
        echo "  <div {$classlinha}>\n";
        link::texto($link, $nome, '', '', 'label');
        echo "    {$opcoes}\n";
        echo "  </div>\n";
    }

    if (!count($eventos_visiveis)) {
        echo "  <div {$classlinha}>\n";
        echo "    <p>Nenhum Evento.</p>\n";
        echo "  </div>\n";
    }

    lista::fim($rodape);

    // Imprimir quadro para ir para o dia seguinte ou dia anterior
    if ($time) {
        list($dia, $mes, $ano) = util::get_data_completa($time);
        $time_anterior = mktime(0, 0, 0, $mes, $dia - 1, $ano);
        $time_seguinte = mktime(0, 0, 0, $mes, $dia + 1, $ano);

        $link_anterior = $CFG->wwwmods.$modulo.'/exibir_eventos.php?time='.$time_anterior;
        $link_seguinte = $CFG->wwwmods.$modulo.'/exibir_eventos.php?time='.$time_seguinte;

        echo "<div id=\"navegacao_dias\">\n";
        link::texto($link_anterior, '&lt; Dia Anterior');
        echo ' <span>|</span> '.strftime($CFG->formato_data, $time).' <span>|</span> ';
        link::texto($link_seguinte, 'Dia Seguinte &gt;');
        echo "</div>\n";
    }

    // Imprime uma lista de links
    if ($time) {
        $modulo = 'eventos';
        $links = array(link::arquivo_modulo($USUARIO, 'inserir.php?time='.$time, $modulo, 'Cadastrar Evento em '.strftime('%d/%m/%Y', $time), '', 'inserir', 1));
    } else {
        $modulo = 'eventos';
        $links = array(link::arquivo_modulo($USUARIO, 'inserir.php', $modulo, false, '', 'inserir', 1));
    }
    $pagina->listar_opcoes($links);
}
