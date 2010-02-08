<?php
//
// SIMP
// Descricao: Arquivo de Configuracoes Adicionais
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.32
// Data: 03/03/2007
// Modificado: 15/01/2010
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('DEBUG_SIMP', 0); // Modo Debug

// Deixe descomentada a linha de interesse
//error_reporting((E_ALL | E_STRICT) ^ (E_NOTICE | E_USER_NOTICE)); // Ignorar noticias
error_reporting(E_ALL | E_STRICT | E_DEPRECATED); // Reportar todos os erros

$CFG = new stdClass();


//
//     Trata as excecoes ocorridas ao longo da execucao (nao use a funcao! ela e' chamada pelo proprio PHP)
//
function tratamento_excecao($e) {
// Exception $e: excecao ocorrida e nao tratada
//
    require_once(dirname(__FILE__).'/debug.php');
    $vet = array();
    tratar_erro(-1, $e->getMessage(), $e->getFile(), $e->getLine(), $vet);
}
set_exception_handler('tratamento_excecao');


//
//     Trata os erros ocorridos ao longo da execucao (nao use a funcao! ela e' chamada pelo proprio PHP)
//
function tratamento_erro($nivel, $erro, $arquivo, $linha, $vars) {
// Int $nivel: codigo numerico do nivel de erro
// String $erro: mensagem do erro
// String $arquivo: arquivo onde ocoreu o erro
// Int $linha: mumero da linha do erro
// Array[String] $vars: tabela de variaveis no contexto onde ocorreu o erro
//
    require_once(dirname(__FILE__).'/debug.php');
    tratar_erro($nivel, $erro, $arquivo, $linha, $vars);
    return false;
}
set_error_handler('tratamento_erro');


/// ARQUIVO DE CONFIGURACOES PHP.INI
require_once($dirroot.'phpini.php');


/// CONSTANTES
require_once($dirroot.'constantes.php');


/// VARIAVEIS UTEIS
$CFG->sistema    = $sistema;
$CFG->dominio    = $dominio;
$CFG->localhost  = $localhost;
$CFG->path       = $path;
$CFG->versao     = $versao;
$CFG->instalacao = $instalacao;
$CFG->charset    = $charset;
$CFG->utf8       = strcasecmp($CFG->charset, 'utf-8') == 0;
$CFG->ip         = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
$CFG->microtime  = microtime(1);
$CFG->time       = time();
$CFG->time_simp  = strftime('%d-%m-%Y-%H-%M-%S', $CFG->time);
$CFG->xml        = isset($_GET['xml']);
$CFG->gmt        = '%a, %d %b %Y %T %Z';
$CFG->id         = $CFG->ip.$CFG->sistema;
$CFG->vt_temas   = $vt_temas;
unset($sistema, $dominio, $path, $versao, $instalacao, $charset, $vt_temas);

if ($CFG->localhost) {
    $CFG->dominio_cookies = false;
} else {
    $CFG->dominio_cookies = $CFG->dominio;
}


/// COOKIES E SESSOES
$CFG->tempo_session       = 7200;                                    // Tempo de sessao: 2 horas = 2 * 60 * 60
$CFG->inatividade_session = true;                                    // Tempo de sessao por inatividade
$CFG->codigo_session      = md5($CFG->ip);                           // Nome do campo da sessao para guardar o cod_usuario
if ($CFG->versao && !isset($_COOKIE['instalando'])) {
    $CFG->nome_cookie  = 'cookie_'.$CFG->sistema;                    // Nome do cookie para guardar dados gerais
    $CFG->id_session   = md5($CFG->id);                              // Nome do cookie da sessao
    $CFG->path_session = 'simp'.DIRECTORY_SEPARATOR.$CFG->sistema;   // Caminho relativo para salvar a sessao
} else {
    $CFG->nome_cookie  = 'cookie_instalacao';
    $CFG->id_session   = md5('session_instalacao');
    $CFG->path_session = 'simp'.DIRECTORY_SEPARATOR.'instalacao';
}

/// ENDERECOS
$CFG->wwwroot   = $wwwroot;
$CFG->wwwmods   = $CFG->wwwroot.'modulos/';
$CFG->wwwlayout = $CFG->wwwroot.'layout/';
$CFG->wwwimgs   = $CFG->wwwroot.'imgs/';
$CFG->wwwlogin  = $CFG->wwwmods.'login/index.php';
if (isset($_SERVER['REQUEST_URI'])) {
    $CFG->site = $_SERVER['REQUEST_URI'];
} elseif (isset($_SERVER['argv'])) {
    $CFG->site = $_SERVER['PHP_SELF'].'?'.$_SERVER['argv'][0];
} elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['QUERY_STRING'])) {
    $CFG->site = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
} else {
    $CFG->site = '';
}
if ((strpos('http://', $CFG->site) === false) && isset($_SERVER['HTTP_HOST'])) {
    $CFG->site = 'http://'.$_SERVER['HTTP_HOST'].$CFG->site;
}
unset($wwwroot);


/// DIRETORIOS
$CFG->dirroot      = $dirroot;
$CFG->dirmods      = $CFG->dirroot.'modulos/';
$CFG->dirlayout    = $CFG->dirroot.'layout/';
$CFG->dirimgs      = $CFG->dirroot.'imgs/';
$CFG->dirclasses   = $CFG->dirroot.'classes/';
$CFG->dirarquivos  = $CFG->dirroot.'arquivos/';
unset($dirroot);


/// CONFIGURACOES DO SGBD
$CFG->bd_config = $bd_config;
unset($bd_config);


/// FUNCOES PARA CARREGAMENTO AUTOMATICO DE CLASSES
$CFG->classes_carregadas = 0;


//
//     Obtem um vetor com os nomes dos arquivos que precisam ser incluidos pela classe
//
function get_arquivos_classe($classe) {
// String $classe: nome da classe desejada
//
    global $CFG;
    $vt = array();

    // Se pediu por uma classe de extensao
    if (file_exists($arq_extensao = $CFG->dirclasses.'extensao/'.$classe.'.class.php')) {
        if (file_exists($arq_entidade = $CFG->dirclasses.'entidade/'.$classe.'.class.php')) {
            $vt[] = realpath($arq_entidade);
        }
        $vt[] = realpath($arq_extensao);

    // Se pediu por uma classe de suporte
    } elseif (file_exists($arq_suporte = $CFG->dirclasses.'suporte/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_suporte);

    // Se pediu por uma classe de interface
    } elseif (file_exists($arq_interface = $CFG->dirclasses.'interface/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_interface);

    // Se pediu por uma classe entidade
    } elseif (file_exists($arq_entidade = $CFG->dirclasses.'entidade/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_entidade);

    // Se pediu por uma classe dao
    } elseif (file_exists($arq_dao = $CFG->dirclasses.'dao/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_dao);

    // Se pediu por uma classe de autenticacao
    } elseif (file_exists($arq_autenticacao = $CFG->dirclasses.'autenticacao/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_autenticacao);

    // Se pediu por uma classe de definicao de atributo
    } elseif (file_exists($arq_atributo = $CFG->dirclasses.'atributos/'.$classe.'.class.php')) {
        $vt[] = realpath($arq_atributo);
    }
    return $vt;
}


//
//     Funcao que carrega as classes sob demanda automaticamente (semelhante 'a __autoload)
//
function simp_autoload($classe) {
// String $classe: nome da classe
//
    global $CFG;

    if (class_exists($classe, false) || interface_exists($classe, false)) {
        return;
    }

    $arquivos = get_arquivos_classe($classe);

    // Se existem arquivos a serem incluidos
    if (count($arquivos)) {
        $CFG->classes_carregadas += 1;
        foreach ($arquivos as $a) {
            require_once($a);
        }

        if (!class_exists($classe, false) && !interface_exists($classe, false)) {
            $erro = "A classe/interface \"{$classe}\" n&atilde;o foi implementada no(s) arquivo(s): ".implode(', ', $arquivos).'.';
            throw new Exception($erro);
        }

    // Se pediu por uma classe que nao existe
    } else {
        $erro = "Classe inexistente \"{$classe}\"";
        throw new Exception($erro);
    }
}


// Registrar a funcao de auto-load
$funcoes_autoload = spl_autoload_functions();
if ($funcoes_autoload === false) {
    spl_autoload_register('simp_autoload');
} else {
    $funcoes_autoload = array_merge(array('simp_autoload'), $funcoes_autoload);
    foreach ($funcoes_autoload as $funcao) {
        spl_autoload_register($funcao);
    }
}
unset($funcoes_autoload);


/// INICIAR SESSAO
session_name($CFG->id_session);
session_set_cookie_params($CFG->time + $CFG->tempo_session, $CFG->path, $CFG->dominio_cookies);

// Path para guardar as sessoes
$save_path = session_save_path();
if (empty($save_path)) {
    $save_paths = array('/var/lib/php/session/', '/tmp/');
    foreach ($save_paths as $sp) {
        if (is_dir($sp) && is_writeable($sp)) {
            $save_path = $sp;
        }
    }
    unset($save_paths);
}
if (!empty($save_path)) {
    $novo_save_path = realpath($save_path).DIRECTORY_SEPARATOR.$CFG->path_session;

    // Se o diretorio nao existe ainda
    if (!is_dir($novo_save_path) && is_writeable($save_path)) {

        // Se conseguir criar o diretorio
        if (util::criar_diretorio_recursivo($novo_save_path, 0300)) {
            @chmod($novo_save_path, 0700);
            @chown($novo_save_path, 'root');

        // Se nao conseguir criar o diretorio: manter o antigo
        } else {
            $novo_save_path = $save_path;
        }
    }
    session_save_path($novo_save_path);
}
unset($novo_save_path, $save_path);

ini_set('session.gc_maxlifetime', $CFG->tempo_session);

if (!defined('IGNORAR_SESSAO') && php_sapi_name() != 'cli') {
    $CFG->abriu_session = session_start();
    $CFG->cookie_params = session_get_cookie_params();

    // Gravar o time de criacao da sessao
    if (!isset($_SESSION['inicio_sessao'])) {
        $_SESSION['inicio_sessao'] = $CFG->time;
    }

    // Se o tempo expirou: apagar a sessao e o cookie de sessao
    if ($_SESSION['inicio_sessao'] + $CFG->tempo_session < $CFG->time) {
        session_destroy();
        setcookie($CFG->id_session, false, $CFG->time - 1, $CFG->path, $CFG->dominio_cookies);
        setcookie('sessao_expirada', 1, $CFG->time + 10, $CFG->path, $CFG->dominio_cookies);
        unset($_SESSION, $_COOKIE[$CFG->id_session]);
    }

    if ($CFG->inatividade_session) {
        $_SESSION['inicio_sessao'] = $CFG->time;
    }

} else {
    $CFG->abriu_session = false;
}


/// Inicializar a classe objeto
objeto::iniciar();


/// VARIAVEIS DE CONFIGURACAO

try {
    $dados_config = objeto::get_cache('config', 1);
} catch (Exception $e) {
    $dados_config = new config();
    if ($dados_config->existe()) {
        objeto::set_cache('config', 1);
    }
}

// Valores salvos
if (isset($dados_config) && $dados_config->existe()) {
    foreach ($dados_config->get_dados() as $campo => $valor) {
        $CFG->$campo = $valor;
    }

// Valores default
} else {
    $CFG->ajax              = true;          // Usar AJAX (objeto XMLHttpRequest)
    $CFG->gd                = true;          // Usar GD (geracao de imagens)
    $CFG->transparencia     = 0.7;           // Nivel de transparencia
    $CFG->opaco             = 0.9;           // Nivel para opaco
    $CFG->autenticacao      = 'simp';        // Forma de autenticacao de usuarios ('simp' ou o nome de um driver de autenticacao)
    $CFG->autenticacao_http = 0;             // Forma de apresentacao do formulario de login por HTTP
    $CFG->fechado           = 0;             // Fechado para manutencao
    $CFG->motivo_fechado    = '';            // Motivo para estar fechado
    $CFG->formato_data      = '%d/%m/%Y';
    $CFG->formato_hora      = '%H:%M:%S';
    $CFG->tipo_email        = 1;
    $CFG->titulo            = 'Análises';
    $CFG->descricao         = 'Cadastro de Análises sobre Frases';
    $CFG->autor             = 'Rubens Takiguti Ribeiro';
    $CFG->link_autor        = 'http://www.tecnolivre.com.br/';
    $CFG->email_padrao      = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : 'root@localhost';
    $CFG->preassunto        = '['.strtoupper($CFG->sistema).']';
    $CFG->lingua            = 'pt-br';
    $CFG->localidade        = (strtolower(substr(PHP_OS, 0, 3)) == 'win') ? 'Portuguese_Brazil.1252' : 'pt_BR'.($CFG->utf8 ? '.UTF-8' : '');
    $CFG->cidade            = 'Lavras';
    $CFG->estado            = 'MG';
    $CFG->palavras          = 'simp';
}


// Checar se a GD existe
if ($CFG->gd && !extension_loaded('gd')) {
    $CFG->gd = false;
}


/// NAVEGADOR E SISTEMA OPERACIONAL
$CFG->agent = new stdClass();
$CFG->cookies = cookie::consultar();
if (!isset($CFG->cookies['navegador'])) {
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        simp_autoload('user_agent');
        $CFG->agent = user_agent::get_dados($_SERVER['HTTP_USER_AGENT']);
        unset($CFG->agent->user_agent);

        // Gravar cookies
        foreach ($CFG->agent as $nome => $valor) {
            $CFG->cookies[$nome] = $valor;
        }
    }

} else {
    $dados = array('navegador', 'versao_navegador', 'so', 'versao_so', 'mozfamily', 'ie', 'css', 'javascript', 'movel', 'texto');
    foreach ($dados as $campo) {
        $CFG->agent->$campo = $CFG->cookies[$campo];
    }
    unset($dados, $campo);
}


/// Tipo de documento gerado
if (isset($_SERVER['HTTP_ACCEPT'])) {
    if (stripos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') !== false) {
        $CFG->content   = 'application/xhtml+xml';
        $CFG->usa_cdata = true;
    } elseif (stripos($_SERVER['HTTP_ACCEPT'], 'application/xml') !== false) {
        $CFG->content   = 'application/xml';
        $CFG->usa_cdata = true;
    } elseif (stripos($_SERVER['HTTP_ACCEPT'], 'text/xml') !== false) {
        $CFG->content   = 'text/xml';
        $CFG->usa_cdata = true;
    } else {
        $CFG->content   = 'text/html';
        $CFG->usa_cdata = false;
    }
} else {
    $CFG->content   = 'text/html';
    $CFG->usa_cdata = false;
}
if (isset($_GET['xml'])) {
    $CFG->content = 'text/xml';
    $CFG->usa_cdata = true;
}

// Modo Debug
if (DEBUG_SIMP) {
    $CFG->content    = 'text/html';
    $CFG->usa_cdata  = false;
    $CFG->ajax       = false;
    $CFG->gd         = false;
}

if (!setlocale(LC_ALL, $CFG->localidade)) {
    $CFG->localidade = 'C';
}
$CFG->load_avg = util::get_load_avg();
link::normalizar($CFG->site, array('xml'));


/// CONFIGURACOES PESSOAIS
$CFG->pessoal = new stdClass();
$campos = array('tema'              => TEMA_PADRAO,
                'ajax'              => '1',
                'fonte'             => 'padrao',
                'tamanho'           => '100%',
                'sem_imagens'       => '0',
                'sem_transparencia' => '0',
                'som'               => '1');
if (isset($CFG->cookies['tema'])) {
    foreach ($campos as $nome => $valor) {
        $CFG->pessoal->$nome = $CFG->cookies[$nome];
    }
    unset($nome, $valor);
} else {
    foreach ($campos as $nome => $valor) {
        $CFG->cookies[$nome] = $valor;
        $CFG->pessoal->$nome = $valor;
    }
}
unset($campos);

// Opcoes que sobrecarregam as opcoes gerais
if (!$CFG->pessoal->ajax) {
    $CFG->ajax = false;
}
