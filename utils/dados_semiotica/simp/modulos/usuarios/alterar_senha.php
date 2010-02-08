<?php
//
// SIMP
// Descricao: Altera a senha de um usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.4
// Data: 13/08/2007
// Modificado: 15/05/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Checar se foi incluido do arquivo de abas
if (!isset($id_abas)) {
    exit(0);
}


/// Dados do Formulario
$modulo  = util::get_modulo(__FILE__);
$classe  = 'usuario';
$dados   = formulario::get_dados();
$campos  = array('senha', 'confirmacao', 'exibir_ajuda_senha');
$prefixo = 'senha';
$action  = $CFG->site;
$ajuda   = <<<AJUDA
   <p>Este formul&aacute;rio realiza a altera&ccedil;&atilde;o de senha.</p>
   <p>Para modific&aacute;-la, basta digitar a nova senha nos dois campos
   apresentados no formul&aacute;rio. A digita&ccedil;&atilde;o de ambas deve
   ser id&ecirc;ntica para que a nova senha seja salva.</p>
   <p>Caso a senha seja esquecida futuramente, basta acessar a p&aacute;gina
   "Esqueci minha senha" cujo <em>link</em> &eacute; apresentado na p&aacute;gina
   de log-in no sistema.</p>
   <p>Obs.: sua senha ser&aacute; salva na base de dados do sistema de forma 
   codificada.</p>
AJUDA;


/// Operacoes

// Checar se o usuario pode alterar o registro
if (!$entidade->pode_ser_manipulado($USUARIO)) {
    pagina::erro($USUARIO, ERRO_ALTERAR);
    exit(1);
}


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
$pagina->imprimir_abas($abas, $id_abas, $ativa);
mensagem::comentario($CFG->site, $ajuda);
$entidade->formulario_alterar($dados, $campos, $action, $prefixo);
checar_forca_senha($entidade, $dados);
$pagina->fechar_abas();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Checa a forca da senha, caso tenha sido enviada
//
function checar_forca_senha(&$usuario, &$dados) {
// usuario $usuario: dados do usuario
// Object $dados: dados submetidos
//
    if (isset($dados->default) || !$dados->usuario->exibir_ajuda_senha) { return; }

    $usuario->consultar_campos(array('nome', 'login'));
    $dados_pessoais = array('nome' => $usuario->nome,
                            'login' => $usuario->login);
    $nota = senha::get_qualidade($dados->usuario->senha, $dados_pessoais, $qualidade, $detalhes);

    echo '<div>';
    echo '<h3>Qualidade da Senha Enviada:</h3>';
    echo '<p>Dificuldade: '.$nota.' (0-100)</p>';
    echo '<p>Qualidade: '.$qualidade.'</p>';
    echo '<p>Detalhes:<br />'.nl2br($detalhes).'</p>';
    echo '</div>';
}
