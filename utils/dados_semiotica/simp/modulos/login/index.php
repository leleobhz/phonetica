<?php
//
// SIMP
// Descricao: Arquivo com formulario de log-in
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.27
// Data: 03/03/2007
// Modificado: 25/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

/// Verificar se o sistema esta' instalado
if (!$CFG->versao) {
    header('location: '.$CFG->wwwroot.'instalar.php');
    exit(0);
}

/// Obter login automatico, caso desejado
$login_get = util::get_dado('login', 'string', false, '');


/// Dados do Formulario
$modulo = util::get_modulo(__FILE__);


/// Dados da Pagina
$id_pagina = 'pagina_login';
$titulo    = 'Log-in';
$nav[]     = $modulo.'#'.basename(__FILE__);
$estilos   = array($CFG->wwwmods.$modulo.'/estilos.css');

if ($CFG->autenticacao == 'simp') {
    $mensagem_senha = '<p>Caso voc&ecirc; tenha esquecido a sua senha, favor acessar a '.
                      'op&ccedil;&atilde;o "Esqueci Minha Senha", abaixo do formul&aacute;rio.</p>';
} else {
    $mensagem_senha = '<p>Caso voc&ecirc; tenha esquecido a sua senha, favor entrar em contato '.
                      'com os respons&aacute;veis pelo sistema ('.$CFG->email_padrao.')</p>';
}

$ajuda = <<<AJUDA
  <p>Este formul&aacute;rio &eacute; respons&aacute;vel pela autentica&ccedil;&atilde;o de 
     usu&aacute;rios para acessar o sistema.</p>
  <p>O login &eacute; um texto que identifica, de maneira &uacute;nica, determinado usu&aacute;rio do
     sistema. A senha, &eacute; um texto que garante que o informante do login realmente &eacute; quem
     diz ser.</p>
  {$mensagem_senha}
AJUDA;


// Avisos e Erros
$avisos = array();
$erros  = array();


// Se deslogou do sistema
if (isset($_GET['encerrar'])) {

    // Se existe mesmo, gerar log de saida
    if (isset($_SESSION[$CFG->codigo_session])) {
        $cod = $_SESSION[$CFG->codigo_session];

        // Gerar Log
        $log = new log_sistema();
        if ($log->inserir($cod, LOG_SAIDA, 0, $cod, 'usuario', $_SERVER['HTTP_USER_AGENT'])) {
            $avisos[] = 'At&eacute; logo.';
        } else {
            $erros[] = 'Erro ao gerar o log de sa&iacute;da';
            $erros = array_merge($erros, $log->get_erros());
        }
    }

    // Destruir sessao
    destruir_sessao(true);

// Se a sessao expirou
} elseif (isset($_COOKIE['sessao_expirada']) && $_COOKIE['sessao_expirada']) {
    $avisos[] = 'O tempo da sess&atilde;o expirou. Por favor, autentique-se novamente.';
    setcookie('sessao_expirada', null, $CFG->time - 1, $CFG->path, $CFG->dominio_cookies);
}


// Metodo de autenticacao HTTP
if ($CFG->autenticacao_http) {
    autenticar_http();
    exit(0);
}

$dados = formulario::get_dados();

// Se nao enviou os dados de log-in: imprimir formulario
if (!$dados) {

    // Destruir o cookie que guarda o ID da sessao
    setcookie($CFG->id_session, false, $CFG->time - 1, $CFG->path, $CFG->dominio_cookies);

    // Imprimir pagina
    $pagina = new pagina($id_pagina);
    $pagina->cabecalho($titulo, $nav, $estilos);
    $pagina->inicio_conteudo();
    if ($avisos) { mensagem::aviso($avisos); }
    if ($erros)  { mensagem::erro($erros);   }
    mensagem::comentario($CFG->site, $ajuda);
    imprimir_form();
    $pagina->fim_conteudo();
    $pagina->rodape();

    // Destruir a sessao para nao deixar lixo no servidor
    destruir_sessao(false);

    exit(0);

// Conferir os dados do usuario
} elseif (possui_erros($dados, $erros)) {

    // Destruir o cookie que guarda o ID da sessao
    setcookie($CFG->id_session, false, $CFG->time - 1, $CFG->path, $CFG->dominio_cookies);

    // Gerar log de erro
    $usuario = new usuario('login', $dados->login);
    $cod_usuario = $usuario->existe() ? $usuario->cod_usuario : 0;
    $log = new log_sistema();
    $log->inserir($cod_usuario, LOG_ENTRADA, 1, $cod_usuario, 'usuario', 'tentou logar como '.$dados->login.' ('.$_SERVER['HTTP_USER_AGENT'].')');

    // Imprimir pagina
    $pagina = new pagina($id_pagina);
    $pagina->cabecalho($titulo, $nav, $estilos);
    $pagina->inicio_conteudo();
    mensagem::erro($erros);
    mensagem::comentario($CFG->site, $ajuda);
    imprimir_form($dados);
    $pagina->fim_conteudo();
    $pagina->rodape();

    // Destruir a sessao para nao deixar lixo no servidor
    destruir_sessao(false);

    exit(0);

// Se nao houve erro no log-in: guardar sessao
} else {
    $usuario = new usuario('login', $dados->login, array('login'));

    // Se o usuario nao existe mais (sabe-se la como...)
    if (!$usuario->existe()) {

        // Destruir a sessao para nao deixar lixo no servidor
        destruir_sessao(true);

        // Gerar Log
        $log = new log_sistema();
        $log->inserir($usuario->cod_usuario, LOG_ENTRADA, 1, 0, 'usuario', 'usuario apagado '.$_SERVER['HTTP_USER_AGENT']);

        header("location: {$CFG->wwwlogin}?erro=usuario_inexistente");
        exit(1);
    }

    // Se deseja guardar login para posterior entrada
    if ($dados->lembrar_login) {
        $CFG->cookies['login'] = $usuario->login;

    // Se nao deseja guardar login, entao apaga-lo
    } elseif (isset($CFG->cookies['login'])) {
        unset($CFG->cookies['login']);
    }
    cookie::salvar($CFG->cookies);

    // Gravar Sessao
    $_SESSION[$CFG->codigo_session] = $usuario->cod_usuario;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

    // Gerar Log
    $log = new log_sistema();
    $log->inserir($usuario->cod_usuario, LOG_ENTRADA, 0, $usuario->cod_usuario, 'usuario', $_SERVER['HTTP_USER_AGENT']);

    // Ir para pagina principal ou de destino
    if (isset($_GET['destino'])) {
        $destino = texto::decodificar(base64_decode($_GET['destino']));
        header("Location: {$destino}");
    } else {
        header("Location: {$CFG->wwwroot}index.php");
    }
    exit(0);

}


//
//     Imprime o formulario de log-in
//
function imprimir_form($dados = null) {
// Object $dados: dados enviados pelo formulario na forma de objeto
//
    global $CFG, $pagina, $login_get;

    if (is_null($dados)) {
        $dados = new stdClass();
    }

    // Lembrar login, caso seja possivel
    if (!isset($dados->login)) {
        $dados->login = 'login';

        // Se possui um login nos cookies
        if (isset($CFG->cookies['login'])) {
            $dados->login = $CFG->cookies['login'];

        // Se informou via get
        } elseif (!empty($login_get)) {
            $dados->login = $login_get;
        }
    }

    // Lista de links com opcoes
    $links = array();

    // Link de Nova senha
    $l = $CFG->wwwmods.'login/nova_senha.php';
    $links[] = link::texto($l, 'Esqueci minha senha', 'Gerar nova senha', '', '', 1);

    // Link de Ajuda
    $l = $CFG->wwwmods.'ajuda/index.php?login=1';
    $links[] = link::texto($l, 'Ajuda', 'T&oacute;picos de Ajuda', '', '', 1);

    // Checar se esta' no modo de manutencao
    if ($CFG->fechado) {

        $mensagem = 'O sistema est&aacute; fechado temporariamente para manuten&ccedil;&atilde;o.';
        if ($CFG->motivo_fechado) {
            $mensagem .= ' O seguinte motivo foi deixado: '.$CFG->motivo_fechado;
        }

        mensagem::aviso($mensagem);
    }

    $action = $CFG->wwwlogin;
    if (isset($_GET['destino'])) {
        $destino = $_GET['destino'];
        $action = link::adicionar_atributo($action, 'destino', $destino);
    }

    $lembrar_login = !empty($dados->login) && ($dados->login != 'login');

    $usuario = new usuario();
    $form = new formulario($action, 'form_login', false, '', 0);
    $form->titulo_formulario('Autentica&ccedil;&atilde;o no sistema');
    if ($CFG->autenticacao !== 'simp') {
        $drivers = autenticacao::get_drivers(true);
        $form->campo_informacao('Autentica&ccedil;&atilde;o via: '.$drivers[$CFG->autenticacao]);
    }
    $usuario->campo_formulario($form, 'login', $dados->login);
    $usuario->campo_formulario($form, 'senha', '');
    $form->campo_bool('lembrar_login', 'lembrar_login', 'Lembrar login neste computador', $lembrar_login);
    $form->campo_submit('entrar', 'entrar', 'Entrar', true, true);
    $form->imprimir();
    $pagina->listar_opcoes($links);
}


//
//    Valida os campos enviados no formulario
//
function possui_erros(&$dados, &$erros) {
// Object $dados: dados enviados pelo formulario
// Array[String] $erros: vetor de erros
//
    global $CFG;
    $erros = array();

    $validacao = validacao::get_instancia();

    // Checar se os cookies estao habilitados
    if (!isset($_COOKIE[$CFG->nome_cookie])) {
        $erros[] = 'Seu navegador n&atilde;o est&aacute; salvando os cookies. Procure saber se seu navegador d&aacute; suporte a este recurso ou se ele apenas est&aacute; desabilitado. Este sistema requer cookies para funcionar.';
        if (!DEVEL_BLOQUEADO) {
            if ($CFG->localhost) {
                $erros[] = '[DEBUG-DEVEL] As configura&ccedil;&otilde;es (arquivo config.php) indicam que o host &eacute; local.';
            } else {
                $erros[] = '[DEBUG-DEVEL] As configura&ccedil;&otilde;es (arquivo config.php) indicam que o host &eacute; registrado. Se isso n&atilde;o &eacute; verdade, altere as configura&ccedil;&otilde;es definindo "$localhost = true;" no local adequado.';
            }
        }
        return true;
    }

    // Login
    if (empty($dados->login)) {
        $erros[] = 'Faltou preencher o login';
    } elseif (!$validacao->validar_campo('LOGIN', $dados->login, $erro_campo)) {
        $erros[] = 'Campo "login" possui caracteres inv&aacute;lidos ou n&atilde;o est&aacute; no padr&atilde;o.'.
                   ($erro_campo ? ' Detalhes: '.$erro_campo : '');
    } else {
        $dados->login = objeto::get_objeto('usuario')->converter_login($dados->login);
    }
    $usuario = new usuario('login', $dados->login, true);

    // Senha
    if (empty($dados->senha)) {
        $erros[] = 'Faltou preencher a senha';
    }

    if ($e = count($erros)) {
        return $e;
    }

    // Se esta no modo de manutencao
    if ($CFG->fechado && !$usuario->possui_grupo(COD_ADMIN)) {
        $erros[] = 'O sistema est&aacute; fechado temporariamente para manuten&ccedil;&atilde;o';

    // Autenticar usuario
    } else {
        if (($CFG->autenticacao == 'simp') && (!$usuario->existe())) {
            $erros[] = 'Usu&aacute;rio inv&aacute;lido (talvez o login esteja digitado errado)';
        } elseif ($usuario->cancelado) {
            $erros[] = 'Usu&aacute;rio cancelado';
        } elseif (!$usuario->validar_senha($dados->login, $dados->senha, $erros)) {
            if (!count($erros)) {
                $erros[] = 'Usu&aacute;rio/Senha inv&aacute;lidos';
            }
        }
    }

    return count($erros);
}


//
//     Autentica um usuario pela forma HTTP
//
function autenticar_http() {
    global $CFG;

    // Se nao enviou o usuario
    if (!isset($_SERVER['PHP_AUTH_USER']) || isset($_GET['encerrar'])) {
        header("WWW-Authenticate: Basic realm=\"{$CFG->titulo}\"");
        header('HTTP/1.0 401 Unauthorized');
        $conteudo = "<p><a href=\"{$CFG->wwwlogin}\">Tentar Novamente</a></p>\n".
                    "<p>Recomenda-se fechar o navegador ao sair por quest&otilde;es de seguran&ccedil;a.</p>";
        pagina::erro(false, '&Eacute; preciso se autenticar para acessar a p&aacute;gina', $conteudo);
        exit(0);

    // Se enviou o usuario, checar se a senha confere
    } else {
        $dados = new stdClass();
        $dados->login = $_SERVER['PHP_AUTH_USER'];
        $dados->senha = $_SERVER['PHP_AUTH_PW'];

        if (possui_erros($dados, $erros)) {
            header("WWW-Authenticate: Basic realm=\"{$CFG->titulo}\"");
            header('HTTP/1.0 401 Unauthorized');
            $conteudo = "<p><a href=\"{$CFG->wwwlogin}\">Tentar Novamente</a></p>\n".
                        "<p>Recomenda-se fechar o navegador ao sair por quest&otilde;es de seguran&ccedil;a.</p>";
            pagina::erro(false, '&Eacute; preciso se autenticar para acessar a p&aacute;gina', $conteudo);
            exit(0);
        } else {
            $usuario = new usuario('login', $dados->login);

            // Gravar Sessao
            $_SESSION[$CFG->codigo_session] = $usuario->cod_usuario;
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            // Gerar Log
            $log = new log_sistema();
            $log->inserir($usuario->cod_usuario, LOG_ENTRADA, 0, $usuario->cod_usuario, 'usuario', $_SERVER['HTTP_USER_AGENT']);

            header("location: {$CFG->wwwroot}index.php");
            exit(0);
        }
    }
}


//
//     Destroi a sessao atual
//
function destruir_sessao($cookie = true) {
// Bool $cookie: indica se deve destruir o cookie tambem
//
    global $CFG;
    if (isset($_COOKIE[$CFG->id_session])) {
        if (isset($_SESSION)) {
            $_SESSION[$CFG->codigo_session] = 0;
            $_SESSION['user_agent'] = '';
            @session_destroy();
            unset($_SESSION);
        }

        // Destruir o cookie que guarda o ID da sessao
        if ($cookie) {
            setcookie($CFG->id_session, false, $CFG->time - 1, $CFG->path, $CFG->dominio_cookies);
        }
    }
}
