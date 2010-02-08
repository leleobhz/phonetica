<?php
//
// SIMP
// Descricao: Lista de Permissoes por grupos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.12
// Data: 25/09/2007
// Modificado: 26/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Quadro
$modulo   = util::get_modulo(__FILE__);
$id_lista = 'lista_'.$modulo;
$link     = $CFG->site;
$dados    = formulario::get_dados();
$action   = $CFG->site;
$ajuda    = <<<AJUDA
  <p>A tabela a seguir apresenta a lista de permiss&otilde;es de acesso
  aos arquivos do sistema para cada tipo de grupo.</p>
  <p>Os itens vis&iacute;veis significam que o arquivo ser&aacute; 
  exibido no pr&oacute;prio menu do usu&aacute;rio na posi&ccedil;&atilde;o
  indicada entre colchetes.</p>
  <p>A defini&ccedil;&atilde;o das permiss&otilde;es &eacute; baseada apenas
  no cadastro e na exclus&atilde;o de permiss&otilde;es, n&atilde;o sendo
  poss&iacute;vel alter&aacute;-las.</p>
AJUDA;


/// Operacoes
if (!$dados) {
    if (isset($_SESSION[$modulo]['dados'])) {
        $dados = unserialize($_SESSION[$modulo]['dados']);
    } else {
        $paginacao = new paginacao($modulo, $id_lista);
        $paginacao->salvar_pagina(1);
    }
} else {
    $_SESSION[$modulo]['dados'] = serialize($dados);
    $paginacao = new paginacao($modulo, $id_lista);
    $paginacao->salvar_pagina(1);
}


/// Possiveis acoes
if ($USUARIO->possui_grupo(COD_ADMIN)) {

    // Desativar item
    if (isset($_GET['desativar'])) {
        $permissao = new permissao('', (int)$_GET['desativar']);
        $permissao->visivel = 0;
        $permissao->salvar();

    // Ativar item
    } elseif (isset($_GET['ativar'])) {
        $permissao = new permissao('', (int)$_GET['ativar']);
        $permissao->visivel = 1;
        $permissao->salvar();

    // Descer item
    } elseif (isset($_GET['descer'])) {
        $permissao = new permissao('', (int)$_GET['descer']);
        $permissao->descer();

    // Subir item
    } elseif (isset($_GET['subir'])) {
        $permissao = new permissao('', (int)$_GET['subir']);
        $permissao->subir();
    }
}


/// Consultar Entidades
$condicoes = condicoes_consulta($dados);


/// Dados da Pagina
$titulo  = 'Permiss&otilde;es por Grupos';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css.php');


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
imprimir_formulario($dados, $action);
listar_permissoes($condicoes, $modulo, $id_lista, $link);
imprimir_links();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime um formulario para selecionar um grupo
//
function imprimir_formulario($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    $vt_grupos = objeto::get_objeto('grupo')->vetor_associativo();

    $campos = array('cod_grupo' => '');
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'form_permissoes');
    $form->titulo_formulario('Selecione um grupo para listar as op&ccedil;&otilde;es');
    $form->campo_select('cod_grupo', 'cod_grupo', $vt_grupos, $dados->cod_grupo, objeto::get_objeto('grupo')->get_entidade());
    $form->campo_submit('enviar', 'enviar', 'Exibir', true);
    $form->imprimir();
}


//
//     Monta as condicoes de filtragem dos dados da lista
//
function condicoes_consulta($dados) {
// Object $dados: dados submetidos
//
    if (!$dados) {
        return false;
    }

    $cod_grupo = (int)$dados->cod_grupo;
    return condicao_sql::montar('cod_grupo', '=', $cod_grupo);
}


//
//     Imprime a lista de permissoes de um grupo
//
function listar_permissoes($condicoes, $modulo, $id_lista, $link) {
// String $condicoes: Condicoes de filtragem das permissoes
// String $modulo: nome do modulo
// String $id_lista: identificador unico da lista
// String $link: link para a pagina atual
//
    global $CFG, $dados;

    if (!$condicoes) {
        return;
    }

    // Dados para paginacao
    $classe       = 'permissao';
    $campos       = array('posicao', 'visivel', 'arquivo:descricao', 'arquivo:modulo');
    $ordem        = 'posicao';
    $index        = false;
    $itens_pagina = 50;

    // Criar paginacao
    $paginacao = new paginacao($modulo, $id_lista, $link);

    // Consultar entidades
    $permissoes = $paginacao->inicio_lista($classe, $condicoes, $campos, $ordem, $index, $itens_pagina);

    // Consultar maior permissao
    $p = new permissao();
    $maior_posicao = $p->maior($dados->cod_grupo);
    if ($permissoes) {

        $classlinha = 'class="linha"';
        $classop    = 'class="opcoes"';

        // Imprime as permissoes
        foreach ($permissoes as $permissao) {
            $cod   = $permissao->get_valor_chave();
            $chave = $permissao->get_chave();

            $opcoes_posicao = array();
            if ($permissao->posicao < $maior_posicao) {
                $opcoes_posicao[] = link::icone("{$CFG->wwwmods}{$modulo}/index.php?descer={$cod}", icone::endereco('baixo'), 'Descer', '', 0, 0, 0);
            }
            if ($permissao->posicao > 1) {
                $opcoes_posicao[] = link::icone("{$CFG->wwwmods}{$modulo}/index.php?subir={$cod}", icone::endereco('cima'), 'Subir', '', 0, 0, 0);
            }

            // Gerar as opcoes
            $opcoes = "<div {$classop}>".
                      "<strong class=\"hide\">Op&ccedil;&otilde;es:</strong>".
                      link::icone("{$CFG->wwwmods}{$modulo}/excluir.php?{$chave}={$cod}", icone::endereco('excluir'), 'Excluir').
                      "<span class=\"hide\">|</span>";
                      if (!$permissao->visivel) {
                          $opcoes .= link::icone("{$CFG->wwwmods}{$modulo}/index.php?ativar={$cod}", icone::endereco('inativo'), 'Tornar Ativa', '', 0, 0, 0);
                      } else {
                          $opcoes .= link::icone("{$CFG->wwwmods}{$modulo}/index.php?desativar={$cod}", icone::endereco('ativo'), 'Tornar Inativa', '', 0, 0, 0);
                      }
                      $opcoes .= implode("<span class=\"hide\">|</span>", $opcoes_posicao).
                                 "</div>";

            $nome = $permissao->arquivo->exibir('descricao').' ['.$permissao->exibir('posicao').']';
            $class = $permissao->visivel ? 'label' : 'inativo';

            // Imprimir linha com uma permissao
            echo "  <div {$classlinha}>\n";
            echo "    <span class=\"{$class}\" title=\"M&oacute;dulo {$permissao->arquivo->modulo}\">{$nome}</span>";
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
    global $CFG, $USUARIO, $dados, $pagina;

    $modulo = 'permissoes';
    $links = array();
    if ($dados) {
        $links = array(link::arquivo_modulo($USUARIO, 'inserir.php?cod_grupo='.$dados->cod_grupo, $modulo, false, '', 'inserir', 1),
                       link::arquivo_modulo($USUARIO, 'gerar_ini.php?cod_grupo='.$dados->cod_grupo, $modulo, false, '', 'ini', 1, true, true, false));
    }

    $pagina->listar_opcoes($links);
}

