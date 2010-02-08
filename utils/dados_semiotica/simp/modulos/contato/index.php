<?php
//
// SIMP
// Descricao: Envia mensagens ao e-mail padrao do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.7
// Data: 31/10/2007
// Modificado: 10/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Constantes
define('MENSAGEM_ERRO',    1);
define('MENSAGEM_DUVIDA',  2);
define('MENSAGEM_CRITICA', 3);
define('MENSAGEM_ELOGIO',  4);
define('MENSAGEM_OUTRO',   5);


/// Dados do Formulario
$modulo = util::get_modulo(__FILE__);
$dados  = formulario::get_dados();
$action = $CFG->site;
$ajuda  = <<<AJUDA
  <p>Formul&aacute;rio para reportar erros do sistema, dificuldades e sugest&otilde;es
  aos desenvolvedores.</p>
AJUDA;


/// Dados da Pagina
$titulo  = 'Enviar Mensagem';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
formulario_contato($dados, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Faz a logica de envio de mensagens
//
function formulario_contato($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG, $USUARIO;

    // Se nao submeteu os dados
    if (!$dados) {
        imprimir_formulario($dados, $action);

    // Se submeteu os dados
    } else {

        // Gerar o assunto do e-mail
        $assunto = '';
        switch ($dados->tipo) {
        case MENSAGEM_ERRO:    $assunto = 'Erro reportado';        break;
        case MENSAGEM_DUVIDA:  $assunto = 'Dificuldade reportada'; break;
        case MENSAGEM_CRITICA: $assunto = 'Crítica reportada';     break;
        case MENSAGEM_ELOGIO:  $assunto = 'Elogio do Usuário';     break;
        case MENSAGEM_OUTRO:   $assunto = 'Mensagem do Usuário';   break;
        }

        // Se possui tags
        if (strpos($dados->mensagem, '<') !== false) {
            $mensagem_texto = strip_tags($mensagem_texto);
        } else {
            $mensagem_texto = $dados->mensagem;
        }

        // Enviar e-mail
        $email = new email($assunto);
        $email->set_remetente($USUARIO->nome, $USUARIO->email);
        $email->set_destinatario('Webmaster', $CFG->email_padrao);
        $email->set_mensagem($mensagem_texto);

        if ($email->enviar()) {
            mensagem::aviso('Mensagem enviada com sucesso');
            link::texto($CFG->site, 'Voltar');
        } else {
            $email->imprimir_erros();
            imprimir_formulario($dados, $action);
        }
    }
}


//
//     Imprime o formulario de contato
//
function imprimir_formulario($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    global $CFG, $USUARIO;

    $vt_tipos = get_tipos_mensagem();
    $campos = array('tipo' => 0, 'mensagem' => '');
    $dados = formulario::montar_dados($campos, $dados);
    $aviso = 'Para melhor comunica&ccedil;&atilde;o, em caso de d&uacute;vidas ou erros reportados, '.
             'escreva com detalhes os passos que esteja realizando no sistema e os resultados apresentados '.
             'em cada passo.';

    // Imprimir o formulario
    $form = new formulario($action, 'form_contato');
    $form->set_onsubmit("return (lt(document.getElementById('mensagem').value.length, 200)) ? window.confirm('Você escreveu uma mensagem relativamente curta.\\nÉ recomendado explicar melhor o problema ou sugestão para melhor atendê-lo(a).\\nDeseja enviar a mensagem mesmo assim?') : true;");
    $form->campo_text('nome', 'nome', $USUARIO->nome, 128, 30, 'Nome', 1);
    $form->campo_text('email', 'email', $USUARIO->email, 128, 30, 'E-mail', 1);
    $form->campo_text('data', 'data', strftime($CFG->formato_data, $CFG->time), 128, 30, 'Data', 1);
    $form->campo_select('tipo', 'tipo', $vt_tipos, $dados->tipo, 'Tipo');
    $form->campo_aviso($aviso);
    $form->campo_textarea('mensagem', 'mensagem', $dados->mensagem, 30, 5, 'Mensagem');
    $form->campo_submit('enviar', 'enviar', 'Enviar', true);
    $form->imprimir();
}


//
//     Retorna os possiveis tipos de mensagem
//
function get_tipos_mensagem() {
    return array(MENSAGEM_ERRO    => 'Reportar um Erro do Sistema',
                 MENSAGEM_DUVIDA  => 'Reportar uma Dificuldade/D&uacute;vida',
                 MENSAGEM_CRITICA => 'Reportar uma Cr&iacute;tica/Sugest&atilde;o',
                 MENSAGEM_ELOGIO  => 'Elogiar o Sistema',
                 MENSAGEM_OUTRO   => 'Outro');
}
