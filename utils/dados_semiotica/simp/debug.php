<?php
//
// SIMP
// Descricao: Biblioteca especial de tratamento de excecoes e erros
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 03/06/2008
// Modificado: 04/11/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//


//
//     Formata uma linha com erro para apresentacao
//
function formatar_linha($linha, &$vars) {
// String $linha: texto da linha onde ocorreu o erro
// Array[String => Mixed] $vars: variaveis alocadas no momento do erro
//
    global $CFG;
    $conversao = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
    $linha = strtr($linha, $conversao);
    if (preg_match_all('/\$([A-z_]{1}[A-z0-9-_]*)/', $linha, $match)) {
        foreach ($match[1] as $i => $nome_var) {
            $var = $match[0][$i];
            if (isset($vars['GLOBALS']) && array_key_exists($nome_var, $vars['GLOBALS'])) {
                $valor = $vars['GLOBALS'][$nome_var];
                $nova_var = '<var title="'.util::exibir_var($valor).'">'.htmlentities($var, ENT_COMPAT, $CFG->charset).'</var>';
                $linha = str_replace($var, $nova_var, $linha);
            }
        }
    }
    return $linha;
}


//
//     Trata o erro de acordo com o seu tipo
//
function tratar_erro($nivel, $erro, $arquivo, $linha, $vars) {
// Int $nivel: nivel (tipo) de erro disparado
// String $erro: mensagem do erro disparado
// String $arquivo: caminho absoluto do arquivo que disparou o erro
// Int $linha: linha do arquivo que ocasionou o erro
// Array[String => Mixed] $vars: variaveis disponiveis no contexto do erro
//
    $error_reporting = (int)ini_get('error_reporting');

    // Tipos de erros fatais: abortam a execucao da pagina
    $fatais = array(
        -1                  => 'Exce&ccedil;&atilde;o',
        E_ERROR             => 'Erro fatal',
        E_PARSE             => 'Erro de interpretador',
        E_CORE_ERROR        => 'Erro fatal PHP',
        E_COMPILE_ERROR     => 'Erro ao compilar PHP',
        E_USER_ERROR        => 'Erro do programador',
        E_RECOVERABLE_ERROR => 'Erro n&atilde;o inst&aacute;vel'
    );

    // Tipos de erros nao fatais: continuam a execucao, mas geram um log de erro
    $nao_fatais = array(
        E_WARNING         => 'Aviso importante',
        E_NOTICE          => 'Notifica&ccedil;&atilde;o de poss&iacute;vel problema',
        E_CORE_WARNING    => 'Aviso importante do PHP',
        E_COMPILE_WARNING => 'Aviso importante ao compilar',
        E_USER_WARNING    => 'Aviso importante do programador',
        E_USER_NOTICE     => 'Notifica&ccedil;&atilde;o de poss&iacute;vel problema pelo programador',
        E_STRICT          => 'Sugest&atilde;o de melhoria'
    );

    // Obter nome do erro e checar se e' um erro fatal
    if (isset($fatais[$nivel])) {
        $nome = $fatais[$nivel];
        $fatal = true;
    } elseif (isset($nao_fatais[$nivel])) {
        $nome = $nao_fatais[$nivel];
        $fatal = DEBUG_SIMP;
    } else {
        $nome = 'Desconhecido ('.$nivel.')';
        $fatal = DEBUG_SIMP;
    }

    // Formatar string a ser enviada para o log de erros
    $log = $nome.': '.$erro.' / Arquivo: '.$arquivo.' / Linha: '.$linha;
    $tabela = get_html_translation_table(HTML_ENTITIES);
    $tabela = array_flip($tabela);
    $log = strtr($log, $tabela);

    // Erros fatais abortam a execucao do programa
    if ($fatal) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Status: 500 Internal Server Error');
        header('Content-Type: text/html');

        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" value="text/html" />';
        echo '<title>'.$nome.'</title>';
        echo '<style type="text/css"><!--';
        echo 'table { border: 1px solid #000000; width: 40em }';
        echo 'table td { text-align: left; }';
        echo 'table td var { cursor: pointer; border-bottom: 1px dotted #000000; font-style: normal }';
        echo 'table td.l { background-color: #FFFAEE; width: 2em; text-align: right; padding-right: .5em }';
        echo 'table tr.erro td { background-color: #FFEEEE }';
        echo 'pre, code { margin: 0; padding 0; }';
        echo '--></style>';
        echo '</head>';
        echo '<body>';
        echo '<h1>'.$nome.'</h1>';
        echo '<p><strong>Descri&ccedil;&atilde;o:</strong> '.$erro.'</p>';
        echo '<p><strong>Arquivo:</strong> '.$arquivo.'</p>';
        echo '<p><strong>Linha:</strong> '.$linha.'</p>';
        if (is_file($arquivo)) {
            $conteudo = file($arquivo);
            echo '<table>';
            echo '<tr><th class="l">#</th><th>C&oacute;digo</th></tr>';
            for ($i = max($linha - 4, 0); $i < $linha; $i++) {
                echo '<tr><td class="l">'.$i.'</td><td><pre><code>'.htmlentities($conteudo[$i - 1]).'</code></pre></td></tr>';
            }
            echo '<tr class="erro"><td class="l">'.($linha).'</td><td><pre><code>'.formatar_linha($conteudo[$linha - 1], $vars).'</code></pre></td></tr>';
            for ($i = 0; $i < 4 && isset($conteudo[$linha + $i]); $i++) {
                echo '<tr><td class="l">'.($linha + 1 + $i).'</td><td><pre><code>'.htmlentities($conteudo[$linha + $i]).'</code></pre></td></tr>';
            }
            echo '</table>';
        }

        echo '<h2>Rastreamento da chamada que ocasionou o erro</h2>';
        util::debug(false);

        echo '<hr />';
        echo '<p>Ocorreu um erro ao gerar esta p&aacute;gina.</p>';
        echo '<p>Dados do Administrador: '.$_SERVER['SERVER_ADMIN'].'</p>';
        echo '</body>';
        echo '</html>';

        if ($nivel & $error_reporting) {
            error_log($log);
        }
        exit($nivel);
    } else {
        if ($nivel & $error_reporting) {
            error_log($log);
        }
    }
    return false;
}
