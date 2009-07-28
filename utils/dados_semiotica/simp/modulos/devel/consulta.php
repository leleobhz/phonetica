<?php
//
// SIMP
// Descricao: Realiza consultas genericas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.7
// Data: 16/05/2008
// Modificado: 09/07/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Bloquear caso necessario
$modulo = util::get_modulo(__FILE__);
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Dados do formulario
$dados = formulario::get_dados();


/// Dados da pagina
$titulo  ='Consulta Gen&eacute;rica';
$nav[$CFG->wwwmods.'devel/index.php'] = 'Desenvolvimento';
$nav[''] = 'Consulta Gen&eacute;rica';
$estilos = $CFG->wwwmods.'devel/estilos_consulta.css';


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
logica_consulta($dados);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Logica da consulta generica
//
function logica_consulta(&$dados) {
// Object $dados: dados submetidos por algum formulario
//
    global $CFG, $pagina;

    inicializar_consulta($sessao);

    // Se uma operacao de link foi acionada, checar qual foi
    if (isset($_GET['op'])) {
        switch ($_GET['op']) {
        case 'abrir_classe':
            $classe = $_GET['classe'];
            $sessao['classe'] = $classe;
            break;

        case 'abrir_aba':
            $aba = $_GET['aba'];
            $sessao['aba'] = max(1, min(4, (int)$aba)); // 1..4
            break;

        case 'abrir_atributo':
            $campo = $_GET['atributo'];

            // Se esta' aberto: fechar (remover indice do vetor 'abrir')
            if (isset($sessao['abrir'][$campo])) {
                unset($sessao['abrir'][$campo]);

            // Se esta' fechado: abrir (adicionar indice no vetor 'abrir')
            } else {
                $sessao['abrir'][$campo] = 1;
            }
            break;

        case 'adicionar_atributo':
            $campo = $_GET['atributo'];

            $pos = strpos($campo, ':') + 1;
            $campo = substr($campo, $pos);
            switch ($sessao['aba']) {
            case 1://campos
                $sessao['campos'][$campo] = $campo;
                break;
            case 2://condicoes
                $operando = &$sessao['condicao']['operando'];
                $sessao['condicao']['operandos'][$operando] = $campo;
                $operando = 1 - $operando;
                break;
            case 3://ordem
                $sessao['ordem'][$campo] = 1;
                break;
            }
            break;

        case 'avancar_pagina':
            $sessao['opcoes']['pagina_atual'] += 1;
            $dados->operacao->consultar = true;
            break;

        case 'desagrupar':
            desagrupar_condicoes($sessao);
            break;

        case 'limpar_condicao_simples':
            $sessao['condicao']['operando'] = 0;
            $sessao['condicao']['operandos'] = array('', '');
            break;

        case 'limpar_consulta':
            limpar_consulta($sessao);
            break;

        case 'listar_entidades':
            inicializar_consulta($sessao, true);
            break;

        case 'mudar_tipo_ordem':
            $campo = $_GET['atributo'];
            if (isset($sessao['ordem'][$campo])) {
                $sessao['ordem'][$campo] = 1 - $sessao['ordem'][$campo];
            }
            break;

        case 'remover_atributo':
            $campo = $_GET['atributo'];
            if (isset($sessao['campos'][$campo])) {
                unset($sessao['campos'][$campo]);
            }
            break;

        case 'remover_ordem':
            $campo = $_GET['atributo'];
            if (isset($sessao['ordem'][$campo])) {
                unset($sessao['ordem'][$campo]);
            }
            break;
        case 'voltar_pagina':
            $sessao['opcoes']['pagina_atual'] -= 1;
            $dados->operacao->consultar = true;
            break;
        }

    // Acoes de formulario:
    } elseif (isset($dados->operacao)) {

        // Incluir condicao simples
        if (isset($dados->operacao->incluir)) {
            $d = &$dados->operacao->incluir;

            $id = $sessao['condicao']['ultimo_id'] += 1;
            switch ($d->operador) {
            case 'ISNULL':
                $condicao = condicao_sql::montar($d->operando1, '=', null, false, $id);
                break;
            case 'ISNOTNULL':
                $condicao = condicao_sql::montar($d->operando1, '<>', null, false, $id);
                break;
            default:
                $condicao = condicao_sql::montar($d->operando1, $d->operador, $d->operando2, $d->entre_atributos, $id);
                break;
            }
            $sessao['condicao']['condicoes'][] = $condicao;
            $sessao['condicao']['operandos'][0] = '';
            $sessao['condicao']['operandos'][1] = '';
            $sessao['condicao']['operando'] = 0;

        // Agrupar condicoes simples
        } elseif (isset($dados->operacao->agrupar_condicoes)) {
            $d = &$dados->operacao->agrupar_condicoes;
            if (isset($d->or)) {
                agrupar_condicoes($sessao, $dados->condicoes, 'OR');
            } elseif (isset($d->and)) {
                agrupar_condicoes($sessao, $dados->condicoes, 'AND');
            } elseif (isset($d->not)) {
                agrupar_condicoes($sessao, $dados->condicoes, 'NOT');
            }

        // Remover condicoes simples
        } elseif (isset($dados->operacao->remover_condicoes)) {
            remover_condicoes($sessao, $dados->condicoes);

        // Consultar
        } elseif (isset($dados->operacao->consultar)) {
            $sessao['opcoes']['pagina_atual'] = 0;

        // Salvar opcoes
        } elseif (isset($dados->operacao->opcoes)) {
            $sessao['opcoes']['filtrar'] = (int)$dados->operacao->opcoes->filtrar;
            $sessao['opcoes']['limite']  = round(abs($dados->operacao->opcoes->limite));

        // Operacao desconhecida
        } else {
            mensagem::erro('Opera&ccedil;&atilde;o desconhecida');
        }
    }

    // Se nao escolheu a classe ainda
    if (!$sessao['classe']) {
        listar_entidades();

    // Se escolheu a classe, exibir formulario e resultado
    } else {
        imprimir_formulario($dados, $sessao);
        imprimir_resultado($dados, $sessao);
        listar_opcoes($sessao);
    }

    salvar_sessao($sessao);
}


//
//     Inicializa uma consulta obtendo os dados da sessao ou os dados padrao
//
function inicializar_consulta(&$sessao, $zerar = false) {
// Array[Mixed] $sessao: dados a serem armazenados em sessao
// Bool $zerar: apaga os dados da sessao
//
    if (isset($_SESSION['consulta']) && !$zerar) {
        $sessao = unserialize($_SESSION['consulta']);
    } else {
        $sessao['classe'] = false;
        $sessao['aba'] = 1;
        $sessao['abrir'] = array();
        $sessao['opcoes']['filtrar'] = 1;
        $sessao['opcoes']['limite'] = 0;
        $sessao['opcoes']['pagina_atual'] = 0;
        limpar_consulta($sessao);
    }
}


//
//     Salva os dados na sessao com serialize
//
function salvar_sessao(&$sessao) {
// Array[Mixed] $sessao: dados a serem salvos na sessao
//
    $_SESSION['consulta'] = serialize($sessao);
}


//
//     Limpa os dados da consulta
//
function limpar_consulta(&$sessao) {
// Array[Mixed] $sessao: dados da sessao
//
    $sessao['campos'] = array();
    $sessao['ordem']  = array();
    $sessao['condicao'] = array(
        'condicoes' => null,
        'operando'  => 0,
        'ultimo_id' => 0
    );
    $sessao['opcoes'] = array(
        'filtrar'      => 1,
        'limite'       => 0,
        'pagina_atual' => 0
    );
}


//
//     Lista todas as entidades do sistema
//
function listar_entidades() {
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    $entidades = listas::get_entidades();
    echo '<p>Selecione a entidade principal que deseja consultar:</p>';
    echo '<ul>';
    foreach ($entidades as $classe => $entidade) {
        $link = link::adicionar_atributo($link_base, array('op', 'classe'), array('abrir_classe', $classe));
        echo '<li>';
        link::texto($link, $entidade, $entidade, false, false, false, false, false);
        echo '</li>';
    }
    echo '</ul>';
}


//
//     Exibe uma entidade e o formulario de busca generica
//
function exibir_entidade(&$sessao) {
// Array[Mixed] $sessao: dados a serem armazenados em sessao
//
    global $CFG;
    $classe = $sessao['classe'];
    try {
        simp_autoload($classe);
        $obj = new $classe();
    } catch (Exception $e) {
        echo '<p>Erro na classe '.$classe.'!</p>';
        return false;
    }
    echo '<fieldset><legend title="'.$obj->get_classe().'">'.$obj->get_entidade().'</legend>';
    exibir_todos_atributos($sessao, $obj, $obj->get_classe(), false);
    echo '</fieldset>';
}


//
//     Exibe uma lista com os atributos da entidade corrente
//
function exibir_todos_atributos(&$sessao, $obj, $nome, $vetor = false) {
// Array[Mixed] $sessao: dados da sessao
// Object $obj: objeto corrente
// String $nome: nome (caminho) da entidade escolhida ate o objeto corrente
// Bool $vetor: indica que a lista e' derivada de um atributo do tipo vetor
//
    echo '<ul>';
    exibir_atributos($sessao, $obj, $nome, $vetor);
    exibir_implicitos($sessao, $obj, $nome, $vetor);
    exibir_objetos($sessao, $obj, $nome, $vetor);
    exibir_vetores($sessao, $obj, $nome);
    echo '</ul>';
}


//
//     Exibe os atributos reais da entidade corrente
//
function exibir_atributos(&$sessao, &$obj, $nome, $vetor = false) {
// Array[Mixed] $sessao: dados da sessao
// Object $obj: objeto corrente
// String $nome: nome (caminho) da entidade escolhida ate o objeto corrente
// Bool $vetor: indica que a lista e' derivada de um atributo do tipo vetor
//
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    foreach ($obj->get_atributos() as $nome_atributo => $def_atributo) {
        $link = link::adicionar_atributo($link_base, array('op', 'atributo'), array('adicionar_atributo', $nome.':'.$nome_atributo));
        echo '<li>';
        if ($nome_atributo == $obj->get_chave()) {
            echo '[PK] <em title="'.$nome_atributo.'">'.$def_atributo->descricao.'</em> ';
        } else {
            echo '[Atr] <span title="'.$nome_atributo.'">'.$def_atributo->descricao.'</span> ';
        }
        switch ($sessao['aba']) {
        case 1://campos
        case 3://ordem
            if (!$vetor) {
                echo link::icone($link, icone::endereco('adicionar'), 'Adicionar '.$def_atributo->descricao, false, false, false);
            }
            break;
        case 2://condicoes
            echo link::icone($link, icone::endereco('adicionar'), 'Adicionar '.$def_atributo->descricao, false, false, false);
            break;
        }
        echo '</li>';
    }
}


//
//     Exibe a lista de atributos implicitos da entidade corrente
//
function exibir_implicitos(&$sessao, &$obj, $nome, $vetor = false) {
// Array[Mixed] $sessao: dados da sessao
// Object $obj: objeto corrente
// String $nome: nome (caminho) da entidade escolhida ate o objeto corrente
// Bool $vetor: indica que a lista e' derivada de um atributo do tipo vetor
//
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);
    foreach ($obj->get_implicitos() as $nome_atributo => $def_atributo) {
        $link = link::adicionar_atributo($link_base, array('op', 'atributo'), array('adicionar_atributo', $nome.':'.$nome_atributo));
        echo '<li>[Imp] <span title="'.$nome_atributo.'">'.$def_atributo->descricao.'</span>';
        switch ($sessao['aba']) {
        case 1://campos
            if (!$vetor) {
                echo ' '.link::icone($link, icone::endereco('adicionar'), 'Adicionar '.$def_atributo->descricao, false, false, false);
            }
            break;
        case 2://condicoes
        case 3://ordem
            break;
        }
        echo '</li>';
    }
}


//
//     Exibe a lista de objetos (relacionamentos 1:1) da entidade corrente
//
function exibir_objetos(&$sessao, &$obj, $nome, $vetor = false) {
// Array[Mixed] $sessao: dados da sessao
// Object $obj: objeto corrente
// String $nome: nome (caminho) da entidade escolhida ate o objeto corrente
// Bool $vetor: indica que a lista e' derivada de um atributo do tipo vetor
//
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);
    foreach ($obj->get_definicoes_rel_uu() as $chave => $def) {
        $novo_nome = $nome.':'.$def->nome;
        echo '<li>[Obj] ';
        $link = link::adicionar_atributo($link_base, array('op', 'atributo'), array('abrir_atributo', $novo_nome));
        link::texto($link, $def->descricao, $def->nome, false, false, false, false, false);
        if (is_array($sessao['abrir']) && array_key_exists($novo_nome, $sessao['abrir'])) {
            exibir_todos_atributos($sessao, $obj->{$def->nome}, $novo_nome, $vetor);
        }
        echo '</li>';
    }
}


//
//     Exibe a lista de vetores (relacionamentos 1:N) da entidade corrente
//
function exibir_vetores(&$sessao, &$obj, $nome) {
// Array[Mixed] $sessao: dados da sessao
// Object $obj: objeto corrente
// String $nome: nome (caminho) da entidade escolhida ate o objeto corrente
//
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);
    foreach ($obj->get_definicoes_rel_un() as $nome_vetor => $def_vetor) {
        $classe = $def_vetor->classe;
        try {
            simp_autoload($classe);
            $obj_vetor = new $classe();
        } catch (Exception $e) {
            continue;
        }

        $novo_nome = $nome.':'.$nome_vetor;
        echo '<li>[Vet] ';
        $link = link::adicionar_atributo($link_base, array('op', 'atributo'), array('abrir_atributo', $novo_nome));
        link::texto($link, $obj_vetor->get_entidade(1), $novo_nome, false, false, false, false, false);
        if (array_key_exists($novo_nome, $sessao['abrir'])) {
            exibir_todos_atributos($sessao, $obj_vetor, $novo_nome, true);
        }
        echo '</li>';
    }
}


//
//     Imprime um formulario generico
//
function imprimir_formulario(&$dados, &$sessao) {
// Object $dados: dados submetidos
// Array[Mixed] $sessao: dados da sessao
//
    global $CFG;

    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    echo '<p>Selecione os campos desejados na busca, as condi&ccedil;&otilde;es de busca ';
    echo 'e os campos usados para ordena&ccedil;&atilde;o dos resultados.</p>';
    echo '<div class="dados">';
    echo '<strong class="titulo">Consulta</strong>';

    echo '<div class="abas">';

    echo '<div class="nomes_abas">';
    $class = ($sessao['aba'] == 1) ? 'ativa' : false;
    link::texto(link::adicionar_atributo($link_base, array('op', 'aba'), array('abrir_aba', '1')), 'Campos', false, false, $class, false, false, false);
    echo '<span> | </span>';
    $class = ($sessao['aba'] == 2) ? 'ativa' : false;
    link::texto(link::adicionar_atributo($link_base, array('op', 'aba'), array('abrir_aba', '2')), 'Condi&ccedil;&otilde;es', false, false, $class, false, false, false);
    echo '<span> | </span>';
    $class = ($sessao['aba'] == 3) ? 'ativa' : false;
    link::texto(link::adicionar_atributo($link_base, array('op', 'aba'), array('abrir_aba', '3')), 'Ordem', false, false, $class, false, false, false);
    echo '<span> | </span>';
    $class = ($sessao['aba'] == 4) ? 'ativa' : false;
    link::texto(link::adicionar_atributo($link_base, array('op', 'aba'), array('abrir_aba', '4')), 'Op&ccedil;&otilde;es', false, false, $class, false, false, false);
    echo '</div>';

    echo '<div class="conteudo_aba">';
    switch ($sessao['aba']) {
    case 1://campos
        $ajuda = '<p>Selecione os campos desejados no quadro abaixo clicando no &iacute;cone de adicionar.</p>'.
                 '<p>&Eacute; poss&iacute;vel expandir/esconder campos derivados clicando sobre eles.</p>';
        mensagem::comentario($link_base, $ajuda);
        if (count($sessao['campos'])) {
            echo '<ul>';
            foreach ($sessao['campos'] as $campo) {
                echo '<li>';
                echo str_replace(':', ' &rarr;', $campo).' ';
                echo link::icone(link::adicionar_atributo($link_base, array('op', 'atributo'), array('remover_atributo', $campo)), icone::endereco('excluir'), 'Remover Campo', false, false, false, false, false);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhum campo selecionado.</p>';
        }
        break;

    case 2://condicoes
        $ajuda = '<ol>'.
                 '  <li>Inclua todas as condi&ccedil;&otilde;es envolvidas:'.
                 '    <ol>'.
                 '      <li>Selecione o primeiro operando no quadro abaixo</li>'.
                 '      <li>Selecione o segundo operando ou digite um valor de compara&ccedil;&atilde;o</li>'.
                 '      <li>Selecione o operador desejado</li>'.
                 '      <li>Selecione o tipo de compara&ccedil;&atilde;o: entre dois atributos ou entre um atributo e um valor</li>'.
                 '      <li>Clique em "Incluir"</li>'.
                 '    </ol>'.
                 '  </li>'.
                 '  <li>Agrupe as condi&ccedil;&otilde;es de forma hier&aacute;rquica:'.
                 '    <ol>'.
                 '      <li>Selecione as condi&ccedil;&otilde;es desejadas</li>'.
                 '      <li>Clique em um agrupador: "E", "OU" ou "N&Atilde;O" (obs.: "E" e "OU" devem agrupar '.
                 '      condi&ccedil;&otilde;es de um mesmo n&iacute;vel, ou seja, n&atilde;o agrupe '.
                 '      condi&ccedil;&otilde;es j&aacute; agrupadas)</li>'.
                 '    </ol>'.
                 '  </li>'.
                 '  <li>Utilize o link "Desagrupar tudo" para voltar as condi&ccedil;&otilde;es ao padr&atilde;o</li>'.
                 '</ol>';
        mensagem::comentario($link_base, $ajuda);

        $condicoes = &$sessao['condicao']['condicoes'];
        $disabled = count($condicoes) ? '' : ' disabled="disabled"';

        echo '<form id="form_condicoes" action="'.$link_base.'" method="post" onsubmit="return submeter(this, 1);">';
        echo '<fieldset>';
        echo '<legend>Condi&ccedil;&otilde;es</legend>';
        echo '<div class="condicoes">';
        imprimir_condicoes($condicoes);
        echo '</div>';
        echo '<hr />';
        echo '<p>';
        echo '  <span>Agrupar com:</span>';
        echo '  <input name="operacao[agrupar_condicoes][or]" type="submit" value="OU" class="botao" '.$disabled.'/>';
        echo '  <input name="operacao[agrupar_condicoes][and]" type="submit" value="E" class="botao" '.$disabled.'/>';
        echo '  <input name="operacao[agrupar_condicoes][not]" type="submit" value="N&Atilde;O" class="botao" '.$disabled.'/>';
        echo '</p>';
        echo '<p>';
        echo '  <span>Op&ccedil;&otilde;es:</span>';
        echo '  <input name="operacao[remover_condicoes][submit]" type="submit" value="Remover" class="botao" '.$disabled.'/>';
        echo '</p>';
        echo '<p>';
        link::texto(link::adicionar_atributo($link_base, 'op', 'desagrupar'), 'Desagrupar tudo', false, false, false, false, false, false);
        echo '</p>';
        echo '</fieldset>';
        echo '</form>';

        $operando1 = isset($sessao['condicao']['operandos'][0]) ? $sessao['condicao']['operandos'][0] : '';
        $operando2 = isset($sessao['condicao']['operandos'][1]) ? $sessao['condicao']['operandos'][1] : '';

        echo '<form id="form_nova_condicao" action="'.$link_base.'" method="post" onsubmit="return submeter(this, 1);">';
        echo '<fieldset>';
        echo '<legend>Nova condi&ccedil;&atilde;o simples</legend>';
        echo '<p>';
        $class = ($sessao['condicao']['operando'] == 0) ? 'class="ativo"' : '';
        echo '<input type="text" name="operacao[incluir][operando1]" value="'.$operando1.'" size="10" '.$class.'/>';
        echo '<select name="operacao[incluir][operador]">';
        echo '  <option value="=">igual a (=)</option>';
        echo '  <option value="&lt;&gt;">diferente de (&ne;)</option>';
        echo '  <option value="&gt;">maior que (&gt;)</option>';
        echo '  <option value="&lt;">menor que (&lt;)</option>';
        echo '  <option value="&gt;=">maior ou igual a (&ge;)</option>';
        echo '  <option value="&lt;=">menor ou igual a (&le;)</option>';
        echo '  <option value="LIKE">similar a (&sim;)</option>';
        echo '  <option value="ISNULL">&eacute; nulo</option>';
        echo '  <option value="ISNOTNULL">n&atilde;o &eacute; nulo</option>';
        echo '</select>';
        $class = ($sessao['condicao']['operando'] == 1) ? 'class="ativo"' : '';
        echo '<input type="text" name="operacao[incluir][operando2]" value="'.$operando2.'" size="10" '.$class.'/>';
        echo '<input name="operacao[incluir][submit]" type="submit" value="Incluir" class="botao" />';
        echo '</p>';
        echo '<p><label><input type="radio" value="0" name="operacao[incluir][entre_atributos]" checked="checked" /> Compara&ccedil;&atilde;o com um valor</label></p>';
        echo '<p><label><input type="radio" value="1" name="operacao[incluir][entre_atributos]" /> Compara&ccedil;&atilde;o entre atributos</label></p>';
        echo '<p>';
        link::texto(link::adicionar_atributo($link_base, 'op', 'limpar_condicao_simples'), 'Limpar', false, false, false, false, false, false);
        echo '</p>';
        echo '</fieldset>';
        echo '</form>';
        break;

    case 3://ordem
        $ajuda = '<p>Selecione os campos usados para ordena&ccedil;&atilde;o no quadro abaixo.</p>'.
                 '<p>Em seguida, escolha o tipo de ordena&ccedil;&atilde;o (crescente ou decrescente).</p>';
        mensagem::comentario($link_base, $ajuda);
        if (count($sessao['ordem'])) {
            echo '<ul>';
            foreach ($sessao['ordem'] as $campo => $tipo) {
                echo '<li>';
                echo str_replace(':', ' &rarr;', $campo).' ';
                $link_ordem = link::adicionar_atributo($link_base, array('op', 'atributo'), array('mudar_tipo_ordem', $campo));
                if ($tipo) {
                    echo link::icone($link_ordem, icone::endereco('crescente'), 'Tornar Decrescente', false, false, false, false, false);
                } else {
                    echo link::icone($link_ordem, icone::endereco('decrescente'), 'Tornar Crescente', false, false, false, false, false);
                }
                echo ' <span class="hide">|</span> ';
                echo link::icone(link::adicionar_atributo($link_base, array('op', 'atributo'), array('remover_ordem', $campo)), icone::endereco('excluir'), 'Remover Campo', false, false, false, false, false);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhum campo selecionado para ordena&ccedil;&atilde;o.</p>';
        }
        break;
    case 4://opcoes
        $ajuda = '<p>Selecione o tipo de filtro e o n&uacute;mero m&aacute;ximo de resultados exibidos por vez. '.
                 'Em seguida, clique em Salvar.</p>';
        mensagem::comentario($link_base, $ajuda);
        echo '<form id="opcoes_consulta" action="'.$link_base.'" method="post" onsubmit="return submeter(this, 1);">';
        echo '<div>';
        echo '<p>Forma de Exibi&ccedil;&atilde;o: ';
        switch ($sessao['opcoes']['filtrar']) {
        case 1:
            echo 'dados filtrados';
            break;
        case 2:
            echo 'dados brutos do <abbr title="Banco de Dados">BD</abbr>';
            break;
        }
        echo '</p>';
        echo '<p>Limite de Resultados: '.($sessao['opcoes']['limite'] ? $sessao['opcoes']['limite'] : 'sem limite').'</p>';
        echo '</div>';
        echo '<fieldset><legend>Op&ccedil;&otilde;es</legend>';
        echo '<div>';
        $checked = ($sessao['opcoes']['filtrar'] == 1) ? ' checked="checked"' : '';
        echo '<p><label><input'.$checked.' type="radio" name="operacao[opcoes][filtrar]" value="1" /> Dados Filtrados</label> (Padr&atilde;o)</p>';
        $checked = ($sessao['opcoes']['filtrar'] == 2) ? ' checked="checked"' : '';
        echo '<p><label><input'.$checked.' type="radio" name="operacao[opcoes][filtrar]" value="2" /> Dados Brutos do BD</label></p>';
        echo '</div>';
        echo '<p>';
        echo '<label for="limite">Limite de Resultados:</label> ';
        echo '<select name="operacao[opcoes][limite]">';
        echo '<option value="0">Sem limite</option>';
        for ($i = 10; $i <= 300; $i += 10) {
            $selected = $sessao['opcoes']['limite'] == $i ? ' selected="selected"' : '';
            echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
        }
        echo '</select>';
        echo '</p>';
        echo '<div><input type="submit" name="operacao[opcoes][submit]" value="Salvar" /></div>';
        echo '</fieldset>';
        echo '</form>';
        break;
    }
    echo '</div>';
    exibir_entidade($sessao);
    echo '</div>';

    echo '<form id="consulta_generica" action="'.$link_base.'" method="post" onsubmit="return submeter(this, 1);">';

    $disabled = is_array($sessao['campos']) && count($sessao['campos']) ? '' : ' disabled="disabled"';
    echo '<div><input type="submit" name="operacao[consultar][submit]" value="Consultar" class="botao"'.$disabled.' /></div>';
    if ($disabled) {
        echo '<p>&Eacute; necess&aacute;rio escolher pelo menos um campo para sele&ccedil;&atilde;o.</p>';
    }
    if (is_array($sessao['condicao']['condicoes']) && count($sessao['condicao']['condicoes']) > 1) {
        echo '<p><abbr title="Observa&ccedil;&atilde;o">Obs.</abbr>: O resultado ser&aacute; gerado a partir da UNI&Atilde;O '.
             'das consultas que n&atilde;o foram agrupadas.</p>';
    }
    echo '</form>';
    echo '</div>';
}


//
//     Imprime parte do formulario de condicoes hierarquicas
//
function imprimir_condicoes(&$condicoes) {
// Array[condicao_sql] || condicao_sql $condicoes: condicoes a serem impressas na forma de formulario
//
    switch (util::get_tipo($condicoes)) {
    case 'array':
        foreach ($condicoes as $c) {
            imprimir_condicoes($c);
        }
        break;
    case 'object':
        if ($condicoes->tipo == CONDICAO_SQL_SIMPLES) {
            $c = &$condicoes;
            $operando1 = $c->operando1;
            $operador = converter_operador($c->operador);
            $operando2 = $c->operando2;
            $id = $c->id;

            echo '<div class="linha">';
            echo '<span class="celula"><input type="checkbox" id="condicao_'.$id.'" name="condicoes['.$id.']" value="'.$id.'" /></span>';
            echo '<span class="abre"></span>';
            if (is_null($operando2)) {
                switch ($c->operador) {
                case '=':
                    echo '<span class="condicao"><label for="condicao_'.$id.'">'.$operando1.' IS NULL</label></span>';
                    break;
                case '<>':
                    echo '<span class="condicao"><label for="condicao_'.$id.'">'.$operando1.' IS NOT NULL</label></span>';
                    break;
                }
            } else {
                echo '<span class="condicao"><label for="condicao_'.$id.'">'.$operando1.' '.$operador.' '.$operando2.'</label></span>';
            }
            echo '<span class="fecha"></span>';
            echo '</div>';
        } elseif ($condicoes->tipo == CONDICAO_SQL_COMPOSTA) {
            $vetor = array_values($condicoes->vetor);
            $ultimo = count($vetor) - 1;
            $id = $condicoes->id;

            echo '<div class="linha">';
            echo '<span class="celula"><input type="checkbox" id="condicao_'.$id.'" name="condicoes['.$id.']" value="'.$id.'" /></span>';
            echo '<span class="abre"></span>';
            echo '<div class="condicao">';
            echo '  <div class="condicoes">';
            foreach ($vetor as $i => $c) {
                imprimir_condicoes($c);
                if ($i != $ultimo) {
                    echo '<div class="linha">';
                    echo '  <span class="celula"></span>';
                    echo '  <span class="celula"></span>';
                    echo '  <span class="operando"><label for="condicao_'.$id.'">'.converter_operador($condicoes->operador).'</label></span>';
                    echo '  <span class="celula"></span>';
                    echo '</div>';
                }
            }
            echo '  </div>';
            echo '</div>';
            echo '<span class="fecha"></span>';
            echo '</div>';
        } elseif ($condicoes->tipo == CONDICAO_SQL_UNITARIA) {
            $id = $condicoes->id;

            echo '<div class="linha">';
            echo '  <span class="celula">';
            echo '    <input type="checkbox" id="condicao_'.$id.'" name="condicoes['.$id.']" value="'.$id.'" />';
            echo '    <span class="operando"><label for="condicao_'.$id.'">'.converter_operador($condicoes->operador).'</label></span>';
            echo '  </span>';
            echo '  <span class="abre"></span>';
            echo '  <div class="condicao">';
            echo '    <div class="condicoes">';
            imprimir_condicoes($condicoes->condicao);
            echo '    </div>';
            echo '  </div>';
            echo '  <span class="fecha"></span>';
            echo '</div>';
        }
        break;

    default:
    case 'null':
        echo '<p>Nenhuma condi&ccedil;&atilde;o (isso consulta todos os registros).</p>';
        echo '<p>Utilize o formul&aacute;rio abaixo para incluir condi&ccedil;&otilde;es.</p>';
        break;
    }
}


//
//     Imprime o resultado da busca generica
//
function imprimir_resultado(&$dados, &$sessao) {
// Object $dados: dados submetidos
// Array[Mixed] $sessao: dados da sessao
//
    global $CFG;
    if (!isset($dados->operacao->consultar)) {
        return;
    }
    $d = &$dados->operacao->consulta;

    $classe = $sessao['classe'];
    $campos = isset($sessao['campos']) ? $sessao['campos'] : array();
    $ordem  = isset($sessao['ordem'])  ? $sessao['ordem']  : false;
    $condicoes_sessao = &$sessao['condicao']['condicoes'];
    if (is_array($condicoes_sessao)) {
        switch (count($condicoes_sessao)) {
        case 0:
            $condicoes = condicao_sql::vazia();
            break;
        case 1:
            $condicoes = $sessao['condicao']['condicoes'][0];
            break;
        default:
            $condicoes = condicao_sql::sql_union($condicoes_sessao);
            break;
        }
    } else {
        $condicoes = condicao_sql::vazia();
    }
    try {
        simp_autoload($classe);
        $obj = new $classe();
    } catch (Exception $e) {
        echo '<p>Erro na classe '.$classe.'</p>';
        return false;
    }

    $atributos = $obj->get_campos_reais($campos);
    $index  = false;
    $limite = $sessao['opcoes']['limite'] ? $sessao['opcoes']['limite'] : false;
    if ($limite) {
        $inicio = $sessao['opcoes']['pagina_atual'] ? $sessao['opcoes']['pagina_atual'] * $limite : false;
    } else {
        $inicio = false;
    }

    $ordem = array();
    foreach ($sessao['ordem'] as $campo => $tipo_ordem) {
        $ordem[] = "'{$campo}' => ".($tipo_ordem ? 'true' : 'false');
    }

    // Exibir a SQL usada
    $dao = new objeto_dao();
    $dao->set_exibicao_usuario(true);
    $sql = $dao->sql_select($obj, $atributos, $condicoes, $ordem, $index, $limite, $inicio);
    echo '<div class="dados">';
    echo '<p><strong>SQL:</strong></p>';
    echo '<code>'.nl2br(texto::codificar($sql)).'</code>';
    echo '</div>';

    // Exibir o comando SQL do SIMP
    echo '<div class="dados">';
    echo '<p><strong>Simp</strong></p>';
    echo '<p><strong>Campos:</strong></p>';
    echo '<code>'."array('".implode("', '", $sessao['campos'])."')".'</code>';
    echo '<p><strong>Condi&ccedil;&otilde;es:</strong></p>';
    echo '<code>'.$condicoes.'</code>';
    echo '<p><strong>Campos de Ordena&ccedil;&atilde;o:</strong></p>';
    echo '<code>';
    echo 'array(';
    echo count($ordem) ? '<br />'.implode(',<br />', $ordem).'<br />' : '';
    echo ')';
    echo '</code>';
    echo '</div>';

    // Consultar efetivamente
    $tempo = microtime(true);
    $resultados = $obj->consultar_varios($condicoes, $campos, $ordem, $obj->get_chave(), $limite, $inicio);
    $tempo = microtime(true) - $tempo;
    $tempo = round($tempo, 3);

    if (!is_array($resultados) || !count($resultados)) {
        echo '<p>Nenhum resultado</p>';
        return;
    }

    echo '<table class="tabela" summary="Tabela com os resultados da consulta">';
    echo '<caption>Resultados da consulta</caption>';
    echo '<thead>';
    if ($sessao['opcoes']['limite']) {
        $pagina_atual = $sessao['opcoes']['pagina_atual'] + 1;
        $limite = $sessao['opcoes']['limite'];
        $quantidade = $obj->quantidade_registros($condicoes);
        $total_paginas = max(1, ceil($quantidade / $limite));

        $link_base = $CFG->site;
        link::normalizar($link_base, true);

        if ($pagina_atual > 1) {
            $link_voltar = link::adicionar_atributo($link_base, 'op', 'voltar_pagina');
            $voltar = link::texto($link_voltar, '&larr;', 'Voltar', '', '', 1, 0, 0);
        } else {
            $voltar = '&larr;';
        }

        if ($pagina_atual < $total_paginas) {
            $link_avancar = link::adicionar_atributo($link_base, 'op', 'avancar_pagina');
            $avancar = link::texto($link_avancar, '&rarr;', 'Avan&ccedil;ar', '', '', 1, 0, 0);
        } else {
            $avancar = '&rarr;';
        }

        echo '<tr>';
        echo '  <th colspan="'.(count($campos) + 1).'">';
        echo $voltar.' <strong>P&aacute;gina '.$pagina_atual.'/'.$total_paginas.'</strong> '.$avancar;
        echo '  </th>';
        echo '</tr>';
    }
    echo '<tr>';
    echo '<th>#</th>';
    foreach ($campos as $c) {
        if ($obj->possui_atributo($c)) {
            $def = $obj->get_definicao_atributo($c);
            $descricao = $def->descricao;
        } elseif ($obj->possui_atributo_implicito($c)) {
            $def = $obj->get_definicao_implicito($c);
            $descricao = $def->descricao;
        }
        echo '<th>'.$descricao.'<br />('.str_replace(':', ' &rarr; ', $c).')</th>';
    }
    echo '</tr>';
    echo '</thead>';

    $colspan = count($campos) + 1;
    $quantidade_exibidos = count($resultados);
    $rodape = $quantidade_exibidos.' resultado'.($quantidade_exibidos != 1 ? 's' : '');
    if ($sessao['opcoes']['limite']) {
        $rodape .= ' (de '.$quantidade.')';
    }
    $rodape .= ' em '.$tempo.' segundo'.($tempo != 1 ? 's' : '');

    echo '<tfoot>';
    echo '<tr>';
    echo '<td colspan="'.$colspan.'">'.$rodape.'</td>';
    echo '</tr>';
    echo '</tfoot>';
    echo '<tbody>';
    $i = 1;
    foreach ($resultados as $resultado) {
        echo '<tr>';
        echo '<td>'.($i++).'</td>';
        foreach ($campos as $c) {
            $valor = ($sessao['opcoes']['filtrar'] == 1) ? $resultado->imprimir_atributo($c, 1, 0) : texto::codificar($resultado->$c);
            echo '<td>'.$valor.'</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}


//
//     Exibe as opcoes de rodape do formulario de consulta
//
function listar_opcoes(&$sessao) {
// Array[Mixed] $sessao: dados da sessao
//
    global $CFG, $pagina;

    // Opcoes de rodape
    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    $links = array();

    $link = link::adicionar_atributo($link_base, 'op', 'listar_entidades');
    $links[] = link::texto($link, 'Voltar', 'Listar Entidades', false, false, true, false, false);

    $link = link::adicionar_atributo($link_base, 'op', 'limpar_consulta');
    $links[] = link::texto($link, 'Limpar Consulta', 'Limpar todos os dados da consulta', false, false, true, false, false);

    $pagina->listar_opcoes($links);
}


//
//     Converte um operador para a notacao HTML
//
function converter_operador($operador) {
// String $operador: codigo do operador aceito pela classe condicao_sql
//
    $conversao = array(
        '<'    => '&lt;',
        '>'    => '&gt;',
        '<='   => '&le;',
        '>='   => '&ge;',
        '='    => '=',
        '<>'   => '&ne;',
        'LIKE' => '&sim;',
        'AND'  => 'E',
        'OR'   => 'OU',
        'NOT'  => 'N&Atilde;O'
    );
    if (isset($conversao[$operador])) {
        return $conversao[$operador];
    }
    return '?';
}


//
//     Agrupa as condicoes dos IDs informados com algum operador
//
function agrupar_condicoes(&$sessao, $ids, $operador) {
// Array[Mixed] $sessao: dados da sessao
// Array[String] $ids: identificadores das condicoes a serem agrupadas
// String $operador: operador de agrupamento (AND, OR, NOT)
//
    $erros = array();
    $compostas = array('AND' => 1, 'OR' => 1);
    $unitarias = array('NOT' => 1);

    if (!count($ids)) {
        $erros[] = 'Nenhuma condi&ccedil;&atilde;o foi selecionada para ser agrupada';
        mensagem::erro($erros);
        return false;
    }

    // Agrupamento de operador Composto (AND e OR)
    if (isset($compostas[$operador])) {
        $primeiro_id = array_shift($ids);
        try {
            $referencia = &obter_condicao($sessao, $primeiro_id);
        } catch (Exception $e) {
            return false;
        }
        $condicao = clone($referencia);
        $vt_condicoes = array($condicao);
        foreach ($ids as $id) {
            try {
                $condicao = &obter_condicao($sessao, $id, true);
                $vt_condicoes[] = $condicao;
            } catch (Exception $e) {
                return false;
            }
        }
        if (count($vt_condicoes) > 1) {
            $nova = new condicao_sql(CONDICAO_SQL_COMPOSTA);
            $nova->id       = $sessao['condicao']['ultimo_id'] += 1;
            $nova->operador = $operador;
            $nova->vetor    = $vt_condicoes;
            $referencia     = $nova;
        } else {
            $erros[] = '&Eacute; necess&aacute;rio selecionar pelo menos duas condi&ccedil;&otilde;es para este tipo de agrupamento';
        }

    // Agrupamento de operador Unitario (NOT)
    } elseif (isset($unitarias[$operador])) {
        foreach ($ids as $id) {
            try {
                $referencia = &obter_condicao($sessao, $id);
            } catch (Exception $e) {
                $erros[] = 'Erro ao obter condi&ccedil;&atilde;o (ID: '.$id.')';
                continue;
            }
            $nova = new condicao_sql(CONDICAO_SQL_UNITARIA);
            $nova->operador = $operador;
            $nova->id       = $sessao['condicao']['ultimo_id'] += 1;
            $nova->condicao = clone($referencia);
            $referencia     = $nova;
        }
    }
    if (count($erros)) {
        mensagem::erro($erros);
    }
}


//
//     Desagrupa todas as condicoes
//
function desagrupar_condicoes(&$sessao) {
// Array[Mixed] $sessao: dados da sessao
//
    $condicoes = $sessao['condicao']['condicoes'];
    $vt_condicoes = array();
    foreach ($condicoes as $condicao) {
        $vt_condicoes = array_merge($vt_condicoes, obter_condicoes($condicao));
    }

    // Refazer IDs das condicoes
    $id = 1;
    foreach ($vt_condicoes as $i => $c) {
        $vt_condicoes[$i]->id = $id++;
    }
    --$id;
    $sessao['condicao']['ultimo_id'] = $id;
    $sessao['condicao']['condicoes'] = $id ? $vt_condicoes : null;
}


//
//     Obtem o vetor de condicoes recursivamente
//
function obter_condicoes($condicao) {
// condicao_sql $condicao: condicao a ser percorrida
//
    if (is_object($condicao) && get_class($condicao) == 'condicao_sql') {
        switch ($condicao->tipo) {
        case CONDICAO_SQL_SIMPLES:
            return array($condicao);

        case CONDICAO_SQL_COMPOSTA:
            $vt_condicoes = array();
            foreach ($condicao->vetor as $c) {
                $vt_condicoes = array_merge($vt_condicoes, obter_condicoes($c));
            }
            return $vt_condicoes;

        case CONDICAO_SQL_UNITARIA:
            return obter_condicoes($condicao->condicao);
        }
    }
    return array();
}


//
//     Obtem uma condicao da sessao pelo ID
//
function &obter_condicao(&$sessao, $id, $apagar = false) {
// Array[Mixed] $sessao: dados da sessao
// String $id: identificador da condicao a ser obtida
// Bool $apagar: flag indicando se a condicao deve ser apagada
//
    try {
        return buscar_condicao($id, $sessao['condicao']['condicoes'], $apagar);
    } catch (Exception $e) {
        // Ignorar
    }
    throw new Exception('Condi&ccedil;&atilde;o n&atilde;o encontrada');
}


//
//     Busca uma condicao pelo ID
//
function &buscar_condicao($id, &$condicoes, $apagar = false) {
// String $id: identificador da condicao a ser obtida
// Array[condicao_sql] $condicoes: local da busca
// Bool $apagar: flag indicando se a condicao deve ser apagada
//
    foreach ($condicoes as $i => $c) {
        if ($c->id == $id) {
            if ($apagar) {
                $retorno = clone($condicoes[$i]);
                unset($condicoes[$i]);
                return $retorno;
            } else {
                return $condicoes[$i];
            }
        }
        if ($condicoes[$i]->tipo == CONDICAO_SQL_COMPOSTA) {
            try {
                return buscar_condicao($id, $condicoes[$i]->vetor, $apagar);
            } catch (Exception $e) {
                // Ignorar
            }
        } elseif ($condicoes[$i]->tipo == CONDICAO_SQL_UNITARIA) {
            $ref = &$condicoes[$i];
            do {
                if ($ref->condicao->id == $id) {
                    if ($apagar) {
                        $retorno = clone($ref->condicao);
                        unset($ref->condicao);
                        return $retorno;
                    } else {
                        $retorno = &$ref->condicao;
                        return $retorno;
                    }
                }
                $ref = &$ref->condicao;
            } while ($ref->tipo == CONDICAO_SQL_UNITARIA);
            if ($ref->tipo == CONDICAO_SQL_COMPOSTA) {
                try {
                    return buscar_condicao($id, $ref->vetor, $apagar);
                } catch (Exception $e) {
                    // Ignorar
                }
            }
        }
    }
    throw new Exception('Condi&ccedil;&atilde;o n&atilde;o encontrada');
}


//
//     Remove as condicoes dos IDs informados
//
function remover_condicoes(&$sessao, $ids) {
// Array[Mixed] $sessao: dados da sessao
// Array[String] $ids: identificadores das condicoes a serem apagadas
//
    $erros = array();
    if (!count($ids)) {
        mensagem::erro('Nenhuma condi&ccedil;&atilde;o foi selecionada para exclus&atilde;o');
        return false;
    }

    foreach ($ids as $id) {
        try {
            obter_condicao($sessao, $id, true);
        } catch (Exception $e) {
            $erros[] = $e->getMessage();
        }
    }

    // Se apagou todas as condicoes
    if (!count($sessao['condicao']['condicoes'])) {
        $sessao['condicao']['condicoes'] = null;
    }

    if (count($erros)) {
        mensagem::erro($erros);
    }
}
