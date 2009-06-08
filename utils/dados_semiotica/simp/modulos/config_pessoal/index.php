<?php
//
// SIMP
// Descricao: Script de configuracoes pessoais
// Autor: Rubens Takiguti Ribeiro && Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 23/07/2007
// Modificado: 03/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados do formulario
$modulo = util::get_modulo(__FILE__);
$dados  = formulario::get_dados();
$campos = array('tema', 'ajax', 'fonte', 'tamanho', 'sem_imagens', 'sem_transparencia');
$action = $CFG->site;
$ajuda  = <<<AJUDA
  <p>Este formul&aacute;rio apresenta algumas op&ccedil;&otilde;es espec&iacute;ficas de 
  apresenta&ccedil;&atilde;o.</p><p><acronym title="Asynchronous Javascript And XML">Ajax</acronym> 
  &eacute; um conjunto de tecnologias que tornam aplica&ccedil;&otilde;es <em>Web</em> mais 
  din&acirc;micas. Deve ser desabilitado caso as p&aacute;ginas n&atilde;o sejam carregadas 
  corretamente.</p>
AJUDA;


/// Dados da pagina
$titulo  = 'Configura&ccedil;&otilde;es Pessoais';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Se submeteu os dados, grava-los no $CFG->cookies
if ($dados) {
    foreach ($campos as $campo) {
        $CFG->cookies[$campo] = $dados->$campo;
        $CFG->pessoal->$campo = $dados->$campo;
    }
    if (!$CFG->pessoal->ajax) {
        $CFG->ajax = false;
        $CFG->cookies['ajax'] = 0;
    }
}


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
logica_formulario($dados, $campos, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//    Logica do formulario de alterar configuracoes pessoais
//
function logica_formulario($dados, $campos, $action) {
// Object $dados: dados enviados pelo formulario
// Array[String] $campos: campos do formulario
// String $action: endereco de destino dos dados
//
    global $CFG;
    if ($dados) {
        if (pagina::$salvou_cookies === -1) {
            mensagem::aviso('Nenhum dado foi alterado');
        } elseif (pagina::$salvou_cookies) {
            mensagem::aviso('Dados salvos com sucesso');
        } else {
            mensagem::erro('Erro ao salvar os dados');
        }
    }
    imprimir_form($CFG->pessoal, $campos, $action);
}


//
//     Imprime o formulario de configuracoes pessoais
//
function imprimir_form($dados, $campos, $action) {
// Object $dados: dados enviados pelo formulario
// Array[String] $campos: campos do formulario
// String $action: endereco de destino dos dados
//
    global $CFG;
    $dados = util::objeto($campos, $dados);

    $vt_ajax = array(1 => 'Sim (Padr&atilde;o)',
                     0 => 'N&atilde;o');

    $vt_fontes = listas::get_fontes();

    $vt_tamanhos = array('50%' => '50% (Menor)',
                         '55%' => '55%',
                         '60%' => '60%',
                         '65%' => '65%',
                         '70%' => '70%',
                         '75%' => '75%',
                         '80%' => '80%',
                         '85%' => '85% (Ideal)',
                         '90%' => '90%',
                         '95%' => '95%',
                         '100%' => '100% (Padr&atilde;o)',
                         '110%' => '110%',
                         '120%' => '120%',
                         '130%' => '130%',
                         '140%' => '140%',
                         '150%' => '150% (Maior)'
                         );

    $form = new formulario($action, 'form_config_pessoal', false, 'post', false);
    $form->campo_aviso('As modifica&ccedil;&otilde;es deste formul&aacute;rio s&atilde;o aplicadas neste Navegador e n&atilde;o tem rela&ccedil;&atilde;o direta com o usu&aacute;rio que aplicou as modifica&ccedil;&otilde;es.');
    $form->campo_select('ajax', 'ajax', $vt_ajax, $dados->ajax, 'Usar <acronym title="Asynchronous Javascript And XML">Ajax</acronym>');
    $form->campo_select('fonte', 'fonte', $vt_fontes, $dados->fonte, 'Fonte Padr&atilde;o');
    $form->campo_select('tamanho', 'tamanho', $vt_tamanhos, $dados->tamanho, 'Tamanho da Fonte');
    $form->campo_select('tema', 'tema', $CFG->vt_temas, $dados->tema, 'Tema');
    $form->campo_bool('sem_imagens', 'sem_imagens', 'Omitir imagens do sistema', $dados->sem_imagens);
    $form->campo_bool('sem_transparencia', 'sem_transparencia', 'N&atilde;o usar transpar&ecirc;ncia', $dados->sem_transparencia);
    $form->campo_submit('enviar', 'enviar', 'Alterar', true, true);
    $form->imprimir();
}
