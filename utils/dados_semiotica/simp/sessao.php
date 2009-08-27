<?php
//
// SIMP
// Descricao: Arquivo para inicializar a sessao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.13
// Data: 03/03/2007
// Modificado: 14/08/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
global $CFG;

// Verificar se o sistema esta' instalado
if (!$CFG->versao) {
    header('Location: '.$CFG->wwwroot.'instalar.php');
    exit(0);
}

// Verificar se o usuario esta' autenticado
if (!isset($_SESSION[$CFG->codigo_session])) {
    $dados_destino = parse_url($CFG->site);
    $destino = base64_encode($CFG->site);

    // Se nao e' a pagina inicial
    if ($CFG->site != $CFG->wwwroot &&
        $CFG->site != $CFG->wwwroot.'index.php' &&
        $dados_destino['host'] == $CFG->dominio) {
        $link = link::adicionar_atributo($CFG->wwwlogin, 'destino', $destino);

    // Se e' a pagina inicial
    } else {
        $link = $CFG->wwwlogin;
    }

    header("Location: {$link}");
    exit(1);
}


// Consultar grupos no BD ou na Cache
try {
    $grupos = objeto::get_cache('grupo');
} catch (Exception $e) {
    $grupos = objeto::get_objeto('grupo')->consultar_varios(condicao_sql::vazia(), true);
}

// Consultar usuario logado
global $USUARIO;
define('COD_USUARIO', (int)$_SESSION[$CFG->codigo_session]);

try {
    $USUARIO = objeto::get_cache('usuario', COD_USUARIO);
} catch (Exception $e) {
    $USUARIO = new usuario('', COD_USUARIO, true);

    // Consultar grupos do usuario
    $USUARIO->consultar_vetor_rel_un('grupos', array('grupo:nome'));

    // Consultar permissoes de cada grupo do usuario
    $campos_permissoes = array('visivel', 'posicao', 'cod_grupo', 'cod_arquivo',
                               'arquivo:cod_arquivo', 'arquivo:arquivo',
                               'arquivo:modulo', 'arquivo:descricao');

    foreach ($USUARIO->grupos as $usuarios_grupos) {
        $usuarios_grupos->grupo->consultar_vetor_rel_un('permissoes', $campos_permissoes);
    }
    unset($campos_permissoes);

    objeto::set_cache('usuario', COD_USUARIO);
    objeto::set_cache('grupo');
}
unset($grupos);

// Checar permissoes de acesso a pagina
$arq = $CFG->site;
if (strpos($arq, $CFG->wwwmods) !== false) {
    $arq = str_replace($CFG->wwwmods, '', $arq);

    // Separar o que vem antes da ultima barra do restante (diretorio e arquivo)
    $pos = strrpos($arq, '/');
    $modulo = substr($arq, 0, $pos);
    $script = substr($arq, $pos + 1);

    // Se esta informando parametros ao link
    if ($pos2 = strpos($script, '?')) {
        $script = substr($script, 0, $pos2);
    }

    // Se o nome do arquivo esta vazio, assumir que e' o index.php
    if (empty($script)) {
        $script = 'index.php';
        $CFG->site .= $script;
    }

    // Checar se o usuario tem permissao para acessar a pagina
    if (!$USUARIO->checar_permissao($modulo, $script)) {
        $nav = array($CFG->wwwroot => 'P&aacute;gina Principal',
                     ''            => 'Erro');
        $estilos = false;

        $pagina = new pagina();
        $pagina->cabecalho('ERRO', $nav, $estilos);
        $pagina->inicio_conteudo();
        mensagem::erro(ERRO_PERMISSAO." (Arquivo: {$arq})");
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(1);
    }
}
unset($arq, $v, $c, $nav);
