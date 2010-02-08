<?php
//
// SIMP
// Descricao: Arquivo de Instalacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.1.0
// Data: 30/05/2007
// Modificado: 26/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('./config.php');

// Se o sistema ja' esta' instalado, redirecionar para tela de login
if ($CFG->versao && (!isset($_COOKIE['instalando']))) {
    header("Location: {$CFG->wwwlogin}");
}

// 5 minutos de execucao (minimo)
$tempo_necessario = 60 * 5;
if (ini_get('max_execution_time') < $tempo_necessario) {
    ini_set('max_execution_time', $tempo_necessario);
}

/// Dados da Pagina
$titulo  = 'Instala&ccedil;&atilde;o';
$nav[''] = 'Instala&ccedil;&atilde;o';
$estilos = array('./layout/instalacao.css');


/// Dados do Formulario
$dados = formulario::get_dados();


// Criar objeto de instalacao
$instalacao = new instalacao();


// Iniciar Sessao
if (!isset($_SESSION['instalacao'])) {
    $_SESSION['instalacao']['aceitou']  = 0;
    $_SESSION['instalacao']['gerou_bd'] = 0;
    $_SESSION['instalacao']['instalou'] = 0;
}

// Checar pre-requisitos para instalacao
if (!isset($_SESSION['instalacao']['pre_requisitos']) || !$_SESSION['instalacao']['pre_requisitos']) {
    $instalacao->checar_pre_requisitos();
    $_SESSION['instalacao']['pre_requisitos'] = true;
}

// Erros, avisos e mensagens
$erros     = array();
$avisos    = array();
$resultado = '';

// Dados da Sessao
if (isset($_GET['aceitou']))  {
    $_SESSION['instalacao']['aceitou']  = 0;
}
if (isset($_GET['gerou_bd'])) {
    $_SESSION['instalacao']['gerou_bd'] = 0;
}


/// Logica de negocios da pagina de instalacao
if ($dados):

/// Se aceitou os termos da licenca
if (isset($dados->aceitar)) {
    if ($dados->aceitar) {
        $_SESSION['instalacao']['aceitou'] = 1;
        $avisos[] = 'Os termos da licen&ccedil;a foram aceitos (fim da fase 1)';
    } else {
        $erros[] = '&Eacute; necess&aacute;rio aceitar os termos da licen&ccedil;a para instalar o sistema';
    }

// Se submeteu os dados do formulario 1
} elseif (isset($dados->instalacao1)) {

    // Filtrar dados
    $dados->servidor = util::host_local($dados->servidor) ? 'localhost' : $dados->servidor;

    // Se nao possui erros
    if (!$instalacao->possui_erros($dados, $erros)) {
        $r = true;

        $bd_config = new stdClass();
        $bd_config->sgbd     = $dados->sgbd;
        $bd_config->porta    = $dados->porta;
        $bd_config->base     = $dados->base;
        $bd_config->servidor = $dados->servidor;

        // Se precisa realizar operacoes no BD: usar usuario root do SGBD
        if ((!$dados->usar_bd) || (!$dados->usar_usuario)) {
            $bd_config->usuario = '[root]';
            $bd_config->senha   = $dados->senharoot;

            $instalacao->set_bd_config($bd_config);
            if (!$dados->usar_bd) {
                if ($r) {
                    $r = $instalacao->criar_bd($dados, $erros, $avisos);
                }
            }
            if (!$dados->usar_usuario) {
                if ($r) {
                    $r = $instalacao->criar_usuario($dados, $erros, $avisos);
                }
            }
        }
        $bd_config->usuario = $dados->usuario;
        $bd_config->senha   = $dados->senha;
        $instalacao->set_bd_config($bd_config);
        if ($r) {
            if ($instalacao->criar_tabelas(INSTALACAO_TODAS_TABELAS, $erros, $avisos, $resultado) &&
                $instalacao->criar_arquivo($dados, $erros, $avisos)
               ) {
                $avisos[] = 'Base de dados criada com sucesso (fim da fase 2)';
                $_SESSION['instalacao']['gerou_bd'] = 1;
            }
        }
    }

// Se submeteu os dados do formulario 2
} elseif (isset($dados->instalacao2)) {
    if ($instalacao->instalar_classes($erros, $avisos)) {
        $avisos[] = 'Instala&ccedil;&atilde;o das classes completa (fim da fase 3)';
        $_SESSION['instalacao']['instalou'] = 1;
    }
}
endif;

/// Imprimir a pagina de instalacao
$num_fases = 4;


/// [1] Aceitar os termos de Licenca do Sistema
if (!$_SESSION['instalacao']['aceitou']) {
    $fase = 1;
    setcookie('instalando', $fase, 0, '/');

    $pagina = new pagina('pagina_licenca');
    $pagina->cabecalho($titulo, $nav, $estilos, 0);
    $pagina->inicio_conteudo();
    if ($erros)  { mensagem::erro($erros);   }
    if ($avisos) { mensagem::aviso($avisos); }
    $instalacao->imprimir_licenca($dados);
    $pagina->nota_rodape("{$fase}/{$num_fases}: Fase em que os termos da licen&ccedil;a s&atilde;o aceitos.");
    $pagina->fim_conteudo();
    $pagina->rodape();
    exit(0);

/// [2] Gerar o BD e arquivo de configuracoes
} elseif (!$_SESSION['instalacao']['gerou_bd']) {
    $fase = 2;
    setcookie('instalando', $fase, 0, '/');

    // Sobrescrever o arquivo de configuracao
    $origem  = $CFG->dirroot.'config.bk.php';
    $destino = $CFG->dirroot.'config.php';
    copy($origem, $destino);

    $pagina = new pagina();
    $pagina->cabecalho($titulo, $nav, $estilos, 0);
    $pagina->inicio_conteudo('Configura&ccedil;&otilde;es Gerais');
    if ($erros)  { mensagem::erro($erros);   }
    if ($avisos) { mensagem::aviso($avisos); }
    if ($resultado) { echo $resultado; }
    $instalacao->formulario_instalacao($dados, $erros);
    link::texto('instalar.php?aceitou=0', 'Voltar', 'Voltar para a Licen&ccedil;a');
    $pagina->nota_rodape("{$fase}/{$num_fases}: Fase em que o BD &eacute; criado, as tabelas do sistema s&atilde;o criadas, o usu&aacute;rio de acesso ao BD &eacute; criado e o arquivo de configura&ccedil;&otilde;es (config.php) &eacute; criado (na ordem especificada). Em caso de d&uacute;vidas, leia a ajuda no in&iacute;cio da p&aacute;gina.");
    $pagina->fim_conteudo();
    $pagina->rodape();
    exit(0);

/// [3] Instalar classes
} elseif (!$_SESSION['instalacao']['instalou']) {
    $fase = 3;
    setcookie('instalando', $fase, 0, '/');

    $pagina = new pagina();
    $pagina->cabecalho($titulo, $nav, $estilos, 0);
    $pagina->inicio_conteudo('Instala&ccedil;&atilde;o das Classes');
    if ($erros)  { mensagem::erro($erros);   }
    if ($avisos) { mensagem::aviso($avisos); }
    $instalacao->formulario_instalacao_classes($dados, $erros);
    link::texto('instalar.php?gerou_bd=0', 'Voltar', 'Voltar para a Instala&ccedil;&atilde;o');
    $pagina->nota_rodape("{$fase}/{$num_fases}: Fase em que os dados iniciais de cada entidade s&atilde;o inseridos nas tabelas do BD.");
    if ($resultado) { echo "<h2>Detalhes da Instala&ccedil;&atilde;o</h2><blockquote>{$resultado}</blockquote>"; }
    $pagina->fim_conteudo();
    $pagina->rodape();
    exit(0);

/// [4] Pronto
} else {
    $fase = 4;

    // Apagar sessao e cookies da instalacao
    $_SESSION['instalacao'] = null;
    session_destroy();
    if ($CFG->abriu_session) {
        setcookie($CFG->id_session, '', $CFG->time - 1, $CFG->cookie_params['path'], $CFG->cookie_params['domain']);
    }
    setcookie('instalando', '', $CFG->time - 1, '/');
    setcookie('cookie_instalacao', '', $CFG->time - 1, '/');
    unset($_COOKIE['id_session']);
    unset($_COOKIE['instalando']);
    unset($_COOKIE['cookie_instalacao']);

    $link = $CFG->wwwlogin;
    $link = link::adicionar_atributo($link, 'login', 'admin');

    $pagina = new pagina();
    $pagina->cabecalho($titulo, $nav, $estilos, 0);
    $pagina->inicio_conteudo('Instala&ccedil;&atilde;o completa');
    if ($erros)  { mensagem::erro($erros);   }
    if ($avisos) { mensagem::aviso($avisos); }
    echo "<p>O sistema j&aacute; est&aacute; acess&iacute;vel no link: ".
         link::texto($link, 'P&aacute;gina Inicial', '', '', '', true, false, false, false).
         "</p>\n".
         "<p>\n".
         "  Dados para acesso:<br />\n".
         "  <em>Login:</em> <strong>admin</strong><br />\n".
         "  <em>Senha:</em> <strong>admin</strong><br />\n".
         "</p>\n".
         "<p>Assim que acessar o sistema, modifique a senha para maior seguran&ccedil;a.</p>\n";
    $pagina->nota_rodape("{$fase}/{$num_fases}: Instala&ccedil;&atilde;o completa.");
    $pagina->fim_conteudo();
    $pagina->rodape();
    exit(0);
}
