<?php
//
// SIMP
// Descricao: Obtem os valores de um User-agent
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/02/2010
// Modificado: 03/02/2010
// License: LICENSE.TXT
// Copyright (C) 2010  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados do Formulario
$action = $CFG->site;
$dados  = formulario::get_dados();


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Dados do User-Agent';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = $titulo;
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo();
formulario_useragent($dados, $action);
if ($dados) {
    imprimir_resultado($dados);
}
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime o formulario de useragent
//
function formulario_useragent($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    $campos = array('useragent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'form_useragent');
    $form->campo_aviso('Preencha o campo abaixo com o texto de identifica&ccedil;&atilde;o de um User-Agent para consultar suas informa&ccedil;&otilde;es.');
    $form->campo_textarea('useragent', 'useragent', $dados->useragent, 50, 5, 'User-Agent');
    $form->campo_submit('enviar', 'enviar', 'Testar', 1);
    $form->imprimir();
}


//
//     Imprime o texto codificado
//
function imprimir_resultado($dados) {
// Object $dados: dados submetidos
//
    $ua = trim($dados->useragent);
    if ($ua === '') {
        mensagem::erro('Faltou informar o User-Agent');
        return false;
    }

    $info_ua = user_agent::get_dados($ua);
    echo '<table class="tabela">';
    echo '<caption>Tabela de informa&ccedil;&otilde;es do User-Agent</caption>';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Informa&ccedil;&atilde;o</th>';
    echo '<th scope="col">Valor</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>Navegador</td>';
    echo '<td>'.texto::codificar($info_ua->navegador).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Vers&atilde;o Navegador</td>';
    echo '<td>'.texto::codificar($info_ua->versao_navegador).'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><abbr title="Sistema Operacional">S.O.</abbr></td>';
    echo '<td>'.texto::codificar($info_ua->so).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Vers&atilde;o S.O.</td>';
    echo '<td>'.texto::codificar($info_ua->versao_so).'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Modo texto</td>';
    echo '<td>'.($info_ua->texto ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Dispositivo M&oacute;vel</td>';
    echo '<td>'.($info_ua->movel ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Fam&iacute;lia Mozilla</td>';
    echo '<td>'.($info_ua->mozfamily ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Fam&iacute;lia <abbr title="Internet Explorer">IE</abbr></td>';
    echo '<td>'.($info_ua->ie ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Suporte <abbr title="Cascading Style Sheet">CSS</abbr></td>';
    echo '<td>'.($info_ua->css ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Suporte JavaScript</td>';
    echo '<td>'.($info_ua->javascript ? 'Sim' : 'N&atilde;o').'</td>';
    echo '</tr>';

    echo '</tbody>';
    echo '</table>';
}
