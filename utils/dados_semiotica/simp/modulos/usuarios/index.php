<?php
//
// SIMP
// Descricao: Lista de Usuarios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.1.7
// Data: 03/03/2007
// Modificado: 21/12/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

// Constantes
define('TODAS_LETRAS', 'todas');

/// Dados do Quadro
$modulo   = util::get_modulo(__FILE__);
$classe   = 'usuario';
$id_lista = 'lista_'.$modulo;
$link     = $CFG->site;
$action   = $CFG->site;
$dados    = formulario::get_dados();
$ajuda = <<<AJUDA
  <p>A tabela a seguir apresenta a lista de usu&aacute;rios do sistema.
  As op&ccedil;&otilde;es poss&iacute;veis s&atilde;o: editar dados pessoais, 
  definir grupos, ver logs e tornar ativo/inativo no sistema.</p>
  <p>&Eacute; poss&iacute;vel consultar usu&aacute;rios atrav&eacute;s do
  filtro, que separa os usu&aacute;rios pela inicial de seus nomes, ou 
  atrav&eacute;s da busca, que tenta localizar usu&aacute;rios por 
  semelhan&ccedil;a com o nome.</p>
  <p>Obs.: N&atilde;o &eacute; poss&iacute;vel tornar o Administrador do Sistema 
  inativo, pois &eacute; preciso pelo menos um usu&aacute;rio no grupo de
  Administradores.</p>
AJUDA;


/// Dados da Pagina
$titulo  = 'Lista de Usu&aacute;rios';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';


/// Checar possiveis acoes
if ($USUARIO->possui_grupo(COD_ADMIN)) {

    // Desativar usuario
    if (isset($_GET['desativar'])) {
        desativar_usuario((int)$_GET['desativar'], $erros);

    // Ativar usuario
    } elseif (isset($_GET['ativar'])) {
        ativar_usuario((int)$_GET['ativar'], $erros);
    }
}


/// Obter condicoes de consulta
$condicoes = condicoes_consulta($dados, $letra, $busca);


/// Imprimir pagina
$pagina = new pagina();
$pagina->adicionar_rss("{$CFG->wwwmods}{$modulo}/usuarios.rss.php", "&Uacute;ltimos Usu&aacute;rios ({$CFG->titulo})");
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
lista_letras($letra);
if (isset($erros) && $erros) {
    mensagem::erro($erros);
}
imprimir_form($busca, $action);
imprimir_usuarios($condicoes, $modulo, $id_lista, $link);
imprimir_links();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Obtem as condicoes de consulta de usuarios de acordo com os filtros
//
function condicoes_consulta($dados, &$letra, &$busca) {
// Object $dados: dados submetidos
// String $letra: letra usada no filtro
// Object $busca: dados da busca
//
    global $modulo, $id_lista;

    $paginacao = new paginacao($modulo, $id_lista);

    // Se utilizou a busca
    if ($dados) {
        $letra = null;
        $busca = $dados;
        $paginacao->salvar_pagina(1);

    // Se solicitou por uma letra
    } elseif (isset($_GET['letra'])) {
        $busca = null;
        $paginacao->salvar_pagina(1);
        if ($_GET['letra'] == TODAS_LETRAS) {
            $letra = TODAS_LETRAS;
        } else {
            $letra = strtoupper($_GET['letra'][0]);
            $ord = ord($letra);
            if ($ord < ord('A') || $ord > ord('Z')) {
                $letra = false;
            }
        }

    // Se foi gravada uma busca na sessao
    } elseif (isset($_SESSION[$modulo]['busca']) && $_SESSION[$modulo]['busca']) {
        $letra = null;
        $busca = $_SESSION[$modulo]['busca'];

    // Se foi gravada uma letra na sessao
    } elseif (isset($_SESSION[$modulo]['letra']) && $_SESSION[$modulo]['letra']) {
        $busca = null;
        $letra = $_SESSION[$modulo]['letra'];

    // Se nao foi gravado nada e nao foi solicitado nada
    } else {
        $busca = null;
        $letra = null;
    }

    // Salvar dados na sessao
    $_SESSION[$modulo]['letra'] = $letra;
    $_SESSION[$modulo]['busca'] = $busca;

    // Consultar pela busca, pela letra ou todos
    $condicao = condicao_sql::vazia();
    if ($busca) {
        $vt_condicoes = array();
        if ($busca->nome) {
            $vt_condicoes[] = condicao_sql::montar('nome', 'LIKE', '%'.$busca->nome.'%');
        }
        if ($busca->cod_grupo) {
            $vt_condicoes[] = condicao_sql::montar('grupos:cod_grupo', '=', $busca->cod_grupo);
        }
        $condicao = condicao_sql::sql_and($vt_condicoes);
    } elseif ($letra) {
        if ($letra == TODAS_LETRAS) {
            $condicao = condicao_sql::vazia();
        } else {
            switch ($letra) {
            case 'numeros':
                $vt_condicoes = array();
                for ($i = 0; $i <= 9; $i++) {
                    $vt_condicoes[] = condicao_sql::montar('nome', 'LIKE', $i.'%');
                }
                $condicao = condicao_sql::sql_or($vt_condicoes);
                break;
            default:
                $condicao = condicao_sql::montar('nome', 'LIKE', $letra.'%');
                break;
            }
        }
    }

    return $condicao;
}


//
//     Imprime a lista de usuarios
//
function imprimir_usuarios($condicoes, $modulo, $id_lista, $link) {
// String $condicoes: condicoes da consulta
// String $modulo: nome do modulo
// String $id_lista: identificador unico da lista
// String $link: link da pagina
//
    global $CFG, $USUARIO, $modulo;

    // Dados para paginacao
    $classe       = 'usuario';
    $campos       = array('nome', 'cancelado');
    $ordem        = array('nome' => true);
    $index        = false;
    $itens_pagina = false;

    // Criar paginacao
    $paginacao = new paginacao($modulo, $id_lista, $link);

    // Consultar entidades
    $usuarios = $paginacao->inicio_lista($classe, $condicoes, $campos, $ordem, $index, $itens_pagina);

    if ($usuarios) {
        $classlinha = 'class="linha"';
        $classop    = 'class="opcoes"';

        // Imprimir Usuarios
        foreach ($usuarios as $usuario) {
            $chave = $usuario->get_chave();
            $cod   = $usuario->get_valor_chave();

            // Gerar as opcoes
            $opcoes = "<div {$classop}>".
                      "<strong class=\"hide\">Op&ccedil;&otilde;es:</strong>".
                      link::icone_modulo($USUARIO, $modulo, "alterar.php?{$chave}={$cod}", icone::endereco('editar'), 'Editar').
                      "<span class=\"hide\">|</span>".
                      link::icone_modulo($USUARIO, $modulo, "grupos.php?{$chave}={$cod}", icone::endereco('grupos'), 'Definir Grupos').
                      "<span class=\"hide\">|</span>".
                      link::icone_modulo($USUARIO, $modulo, "logs.php?{$chave}={$cod}", icone::endereco('grafico'), 'Ver Log').
                      "<span class=\"hide\">|</span>";
            if ($usuario->cancelado) {
                $opcoes .= link::icone("{$CFG->wwwmods}{$modulo}/index.php?ativar={$cod}", icone::endereco('inativo'), 'Tornar Ativo', '', 0, 0, 0);
            } else {
                $opcoes .= link::icone("{$CFG->wwwmods}{$modulo}/index.php?desativar={$cod}", icone::endereco('ativo'), 'Tornar Inativo', '', 0, 0, 0);
            }
            $opcoes .= '</div>';

            $class = $usuario->cancelado ? 'inativo' : 'label';

            // Imprimir linha com um usuario
            echo "  <div {$classlinha}>\n";
            link::arquivo_modulo($USUARIO, "exibir.php?{$chave}={$cod}", $modulo, $usuario->nome, '', $class);
            echo "\n";
            echo "    {$opcoes}\n";
            echo "  </div>\n";
        }
    }
    $paginacao->fim_lista();
}


//
//     Imprime uma lista de links
//
function imprimir_links() {
    global $CFG, $USUARIO, $pagina;
    $modulo = 'usuarios';

    $link_rss = "{$CFG->wwwmods}{$modulo}/usuarios.rss.php";
    $links = array(link::arquivo_modulo($USUARIO, 'inserir.php', $modulo, false, '', 'inserir', 1),
                   link::arquivo_modulo($USUARIO, 'importar_csv.php', $modulo, false, '', 'importar', 1),
                   link::arquivo_modulo($USUARIO, 'importar_xml.php', $modulo, false, '', 'importar', 1),
                   link::texto($link_rss, 'Feed', '&Uacute;ltimos Usu&aacute;rios cadastrados', '', 'rss', 1, 0, 0, 0)
                  );

    $pagina->listar_opcoes($links);
}


//
//     Imprime um formulario de busca
//
function imprimir_form($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG, $modulo, $id_lista;

    $padrao = array('nome' => '',
                    'cod_grupo' => 0);
    $dados = formulario::montar_dados($padrao, $dados);

    $vt_grupos = array(0 => 'Todos') + objeto::get_objeto('grupo')->vetor_associativo();

    link::normalizar($action, array(paginacao::get_nome_sessao($modulo, $id_lista)));

    $form = new formulario($action, 'form_busca');
    $form->titulo_formulario('Op&ccedil;&otilde;es de Busca');
    $form->campo_busca('nome', 'nome', 'usuario', 'nome', $dados->nome, null, 128, 30, 'Nome');
    $form->campo_select('cod_grupo', 'cod_grupo', $vt_grupos, $dados->cod_grupo, 'Grupo');
    $form->campo_submit('enviar', 'enviar', 'Consultar');
    $form->imprimir();
}


//
//     Retorna uma lista de links para cada letra
//
function lista_letras($letra) {
// String $letra: letra ativa
//
    global $CFG, $modulo;

    $links_letras = "<div id=\"lista_letras\">\n".
                    "  <strong>Filtro:</strong>\n";

    // Listar Todos ou nao
    if ($letra != TODAS_LETRAS) {
        $l = $CFG->wwwmods.$modulo.'/index.php?letra='.TODAS_LETRAS;
        $links_letras .= link::texto($l, 'Todos', 'Exibir Todos Usu&aacute;rios', '', '', 1, 0)." <span class=\"hide\">|</span>\n";

    } else {
        $links_letras .= "  <strong>Todos</strong>\n";
    }

    // Exibir link para cada letra
    for ($i = 'A'; ord($i) <= ord('Z'); $i = chr(ord($i) + 1)) {

        // Link para Letra
        if ($i != $letra) {
            $l = $CFG->wwwmods.$modulo.'/index.php?letra='.$i;
            $links_letras .= link::texto($l, $i, 'Nomes com a letra '.$i, '', '', 1, 0);

        // Letra ativa
        } else {
            $links_letras .= "  <strong>{$i}</strong>\n";
        }
        $links_letras .= ($i != 'Z') ? "  <span class=\"hide\">|</span>\n" : '';
    }
    $links_letras .= "</div>\n";

    echo $links_letras;
}


//
//     Desativa um usuario
//
function desativar_usuario($cod_usuario, &$erros = array()) {
// Int $cod_usuario: codigo do usuario
// Array[String] $erros: erros ocorridos
//
    global $classe;

    // Nao desativar usuario admin
    if ($cod_usuario == 1) {
        $erros[] = 'N&atilde;o &eacute; permitido desativar o usu&aacute;rio Administrador';
        return false;
    }
    $usuario = new $classe('', $cod_usuario);
    if (!$usuario->existe()) {
        $erros[] = 'O usu&aacute;rio n&atilde;o pode ser desativado pois n&atilde;o existe';
        return false;
    }
    $usuario->cancelado = true;
    if (!$usuario->salvar()) {
        $erros = $usuario->get_erros();
        return false;
    }
    return true;
}


//
//     Ativa um usuario visivel
//
function ativar_usuario($cod_usuario, &$erros = array()) {
// Int $cod_usuario: codigo do usuario
// Array[String] $erros: erros ocorridos
//
    global $classe;

    $usuario = new $classe('', $cod_usuario);
    if (!$usuario->existe()) {
        $erros[] = 'O usu&aacute;rio n&atilde;o pode ser ativado pois n&atilde;o existe';
        return false;
    }
    $usuario->cancelado = false;
    if (!$usuario->salvar()) {
        $erros = $usuario->get_erros();
        return false;
    }
    return true;
}
