<?php
//
// SIMP
// Descricao: testa a memoria para uma entidade
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 17/10/2007
// Modificado: 15/07/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados do formulario
$dados = formulario::get_dados();
$action = $CFG->site;


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Testar Mem&oacute;ria';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = $titulo;
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos, 0);
$pagina->inicio_conteudo();
logica_teste_memoria($dados, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Realiza a logica de teste de memoria
//
function logica_teste_memoria($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG, $modulo;
    if (!$CFG->instalacao) {
        echo '<p>O sistema ainda n&atilde;o foi instalado.</p>';
        return;
    }

    imprimir_formulario($dados, $action);
    if ($dados) {
        $link = $CFG->wwwmods.$modulo.'/memoria.php';
        $arq  = $CFG->dirmods.$modulo.'/memoria.php';
        grafico::exibir_grafico('Teste de Mem&oacute;ria', $link, $arq, $dados);
    }
}


//
//     Imprime o formulario para escolher a entidade
//
function imprimir_formulario($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    $entidade = isset($dados->entidade) ? $dados->entidade : 0;


    $vt = listas::get_entidades();

    $form = new formulario($action, 'teste_memoria');
    $form->campo_select('entidade', 'entidade', $vt, $entidade, 'Entidade');
    $form->campo_submit('enviar', 'enviar', 'Enviar');
    $form->imprimir();
}
