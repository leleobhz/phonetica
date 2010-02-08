<?php
//
// SIMP
// Descricao: Realiza insercoes genericas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 09/06/2009
// Modificado: 30/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Bloquear caso necessario
$modulo = util::get_modulo(__FILE__);
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Dados do formulario
$dados = formulario::get_dados();


/// Dados da pagina
$titulo  ='Inser&ccedil;&atilde;o Gen&eacute;rica';
$nav[$CFG->wwwmods.'devel/index.php'] = 'Desenvolvimento';
$nav[''] = 'Inser&ccedil;&atilde;o Gen&eacute;rica';
$estilos = $CFG->wwwmods.'devel/estilos.css';


/// Imprimir pagina
$pagina = new pagina('insercao');
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
logica_consulta($dados);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Logica da insercao generica
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

        case 'novo':
            $vetor = explode(':', $_GET['obj']);
            array_shift($vetor);
            $php = "\$sessao['abrir']['".implode("']['", $vetor)."'] = array();";
            eval($php);
            break;

        case 'existente':
            $vetor = explode(':', $_GET['obj']);
            array_shift($vetor);
            $php = "unset(\$sessao['abrir']['".implode("']['", $vetor)."']);";
            eval($php);
            break;

        case 'listar_entidades':
            $sessao['classe'] = false;
            $sessao['abrir'] = array();
            break;
        }
    }


    // Se nao escolheu a classe ainda
    if (!$sessao['classe']) {
        listar_entidades();

    // Se escolheu a classe, exibir formulario e resultado
    } else {
        if ($dados) {
            $classe = $sessao['classe'];
            $obj = new $classe();
            $campos = get_campos($sessao);
            $vt_campos_hierarquico = objeto::converter_notacao_vetor($campos);
            $dados->$classe = $obj->converter_componentes($dados->$classe, $vt_campos_hierarquico);

            if (!$obj->set_valores($dados->$classe, $vt_campos_hierarquico, true) ||
                !$obj->salvar_completo($vt_campos_hierarquico)
               ) {
                $obj->imprimir_erros();
                imprimir_formulario($dados, $sessao);
            } else {
                $obj->imprimir_avisos();
                switch ($obj->get_genero()) {
                case 'M':
                case 'I':
                    $o = 'o';
                    break;
                case 'F':
                    $o = 'a';
                    break;
                }

                $site = $CFG->site;
                link::normalizar($site);
                echo '<p><a href="'.$site.'">Cadastrar outr'.$o.'</a></p>';
            }
        } else {
            imprimir_formulario($dados, $sessao);
        }
        listar_opcoes($sessao);
    }

    salvar_sessao($sessao);
}


//
//     Obtem os campos a serem mostrados no formulario
//
function get_campos($sessao) {
// Array[String => Mixed] $sessao: dados da sessao
//
    get_campos_recursivo($campos, $sessao['classe'], $sessao['abrir'], '');
    return $campos;
}


//
//     Obtem os campos a serem mostrados no formulario recursivo
//
function get_campos_recursivo(&$campos, $classe, $vt_abrir, $prefixo) {
// Array[String] $campos: vetor de campos a ser preenchido
// String $classe: nome da classe a serem obtidos os campos
// Array[String || Type] $vt_abrir: vetor de objetos a serem abertos
// String $prefixo: prefixo a ser colocado nos campos
//
    $obj = objeto::get_objeto($classe);
    foreach ($obj->get_atributos() as $atributo => $def) {
        if ($def->chave == 'PK' || $def->chave == 'FK' || $def->chave == 'OFK') { continue; }
        $campos[] = $prefixo.$atributo;
    }

    foreach ($obj->get_definicoes_rel_uu() as $chave => $def) {
        $nome_obj_filho = $def->nome;
        $obj_filho = $obj->get_objeto_rel_uu($nome_obj_filho);
        if (isset($vt_abrir[$nome_obj_filho])) {
            get_campos_recursivo($campos, $obj_filho->get_classe(), $vt_abrir[$nome_obj_filho], $prefixo.$nome_obj_filho.':');
        } else {
            $campos[] = $prefixo.$chave;
        }
    }
}


//
//     Inicializa uma consulta obtendo os dados da sessao ou os dados padrao
//
function inicializar_consulta(&$sessao, $zerar = false) {
// Array[Mixed] $sessao: dados a serem armazenados em sessao
// Bool $zerar: apaga os dados da sessao
//
    if (isset($_SESSION['insercao']) && !$zerar) {
        $sessao = unserialize($_SESSION['insercao']);
    } else {
        $sessao['classe'] = false;
        $sessao['abrir'] = array();
        limpar_consulta($sessao);
    }
}


//
//     Salva os dados na sessao com serialize
//
function salvar_sessao(&$sessao) {
// Array[Mixed] $sessao: dados a serem salvos na sessao
//
    $_SESSION['insercao'] = serialize($sessao);
}


//
//     Limpa os dados da consulta
//
function limpar_consulta(&$sessao) {
// Array[Mixed] $sessao: dados da sessao
//
    $sessao['abrir'] = array();
}


//
//     Lista todas as entidades do sistema
//
function listar_entidades() {
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    $entidades = listas::get_entidades();
    echo '<p>Selecione a entidade principal que deseja inserir:</p>';
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
//     Imprime o formulario de insercao
//
function imprimir_formulario($dados, $sessao) {
// Object $dados: dados submetidos
// Array[String => Mixed] $sessao: dados em sessao
//
    global $CFG;
    $classe = $sessao['classe'];
    try {
        simp_autoload($classe);
        $obj = new $classe();
    } catch (Excecption $e) {
        mensagem::erro('Classe inv&aacute;lida "'.texto::codificar($classe).'"');
        return;
    }
    $vt_nome = array($obj->get_classe());

    $action = $CFG->site;
    link::normalizar($action, true);

    $classe_objeto_formulario = 'objeto_formulario';
    if (!($obj instanceof $classe_objeto_formulario)) {
        echo '<p>A entidade "'.$obj->get_entidade().'" n&atilde;o permite inser&ccedil;&otilde;es manuais</p>';
        return false;
    }

    $form = new formulario($action, 'form_'.$classe);
    imprimir_campos($form, $obj, $obj->get_entidade(), $dados->$classe, '', $sessao['abrir'], $vt_nome);
    $form->set_nome(array());
    $form->campo_submit('enviar', 'enviar', 'Inserir');
    $form->imprimir();
}


//
//     Imprime os campos da entidade
//
function imprimir_campos(&$form, &$obj, $descricao, $dados, $prefixo, $abertos, $vt_nome) {
// formulario $form: formulario principal
// objeto $obj: entidade usada para listar os campos
// String $descricao: descricao da entidade
// Object $dados: dados submetidos
// String $prefixo: prefixo do nome do campo
// Array[String] $abertos: objetos abertos
// Array[String] $vt_nome: nome da classe e caminho ate o objeto corrente
//
    global $CFG;
    $link_base = $CFG->site;
    link::normalizar($link_base, true);

    $form->inicio_bloco($descricao);

    // Link novo / existente
    if ($prefixo) {
        $link_existente = link::adicionar_atributo($link_base, 'op', 'existente');
        $link_existente = link::adicionar_atributo($link_existente, 'obj', $prefixo);
        $existente = link::texto($link_existente, 'Existente', 'Existente', '', '', 1, 0, 0);
        $form->campo_informacao('<span>'.icone::img('adicionar').' Novo</span> | '.$existente);
    }

    // Imprimir campos simples da classe
    $form->set_nome($vt_nome);
    foreach ($obj->get_atributos() as $atributo => $def) {
        if ($def->chave == 'PK' || $def->chave == 'FK' || $def->chave == 'OFK') { continue; }
        if (isset($dados->$atributo)) {
            $valor = $dados->$atributo;
        } else {
            $valor = null;
        }
        $obj->campo_formulario($form, $atributo, $valor);
    }

    foreach ($obj->get_definicoes_rel_uu() as $chave => $def) {
        $nome_obj_filho = $def->nome;
        $obj_filho = $obj->get_objeto_rel_uu($nome_obj_filho);

        // Imprimir campos do objeto filho
        if (isset($abertos[$nome_obj_filho])) {
            if (isset($dados->$nome_obj_filho)) {
                $dados_obj = &$dados->$nome_obj_filho;
            } else {
                $dados_obj = null;
            }

            $vt_nome_obj = $vt_nome;
            $vt_nome_obj[] = $nome_obj_filho;
            imprimir_campos($form, $obj_filho, $def->descricao, $dados_obj, $prefixo.':'.$nome_obj_filho, $abertos[$nome_obj_filho], $vt_nome_obj);

        // Imprimir campo de selecao
        } else {
            if (isset($dados->$atributo)) {
                $valor = $dados->$chave;
            } else {
                $valor = null;
            }

            $form->inicio_bloco($def->descricao);

            // Link novo / existente
            $link_novo = link::adicionar_atributo($link_base, 'op', 'novo');
            $link_novo = link::adicionar_atributo($link_novo, 'obj', $prefixo.':'.$nome_obj_filho);
            $novo = link::texto($link_novo, icone::img('adicionar').' Novo', 'Novo', '', '', 1, 0, 0);
            $form->campo_informacao($novo.' | <span>Existente</span>');

            $obj->campo_formulario($form, $chave, $valor);
            $form->fim_bloco();
        }
    }
    $form->fim_bloco();
}


//
//     Exibe as opcoes de rodape do formulario
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

    $pagina->listar_opcoes($links);
}
