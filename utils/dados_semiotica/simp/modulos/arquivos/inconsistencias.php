<?php
//
// SIMP
// Descricao: Arquivo para checar inconsistencias entre o BD e os arquivos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.6
// Data: 17/12/2007
// Modificado: 04/01/2010
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');
modulo::pagina('imprimir_inconsistencias');


/// Funcoes


//
//     Imprime uma lista de inconsistencias
//
function imprimir_inconsistencias() {
    global $CFG, $USUARIO;

$ajuda = <<<AJUDA
  <p>Esta lista apresenta as inconsist&ecirc;ncias entre os arquivos cadastrados no BD
  e os arquivos reais nos m&oacute;dulos.</p>
  <p>As inconsist&ecirc;ncias s&atilde;o classificadas em:</p>
  <ul>
    <li>Nenhuma: indica que n&atilde;o representa uma inconsist&ecirc;ncia (representado em verde)</li>
    <li>Media: indica que &eacute; um poss&iacute;vel arquivo inconsist&ecirc;nte,
        pois existe, mas n&atilde;o est&aacute; cadastrado no BD OU
        est&aacute; cadastrado, mas n&atilde;o existe (representado em amarelo)</li>
    <li>Alta: indica que tem grande chances de ser uma inconsist&ecirc;ncia, pois o arquivo existe,
        n&atilde;o est&aacute; cadastrado no BD e inclui o arquivo sessao.php, resons&aacute;vel por
        realizar a autentica&ccedil;&atilde;o do usu&aacute;rio (representado em vermelho)</li>
  </ul>
  <p>Apenas os m&oacute;dulos que apresentaram inconsist&ecirc;ncias s&atilde;o apresentados.</p>
  <p>A lista auxiliar&aacute; no cadastro ou exclus&atilde;o de arquivos no BD.</p>
AJUDA;

    mensagem::comentario($CFG->site, $ajuda);

    /// Para cada modulo, checar os arquivos reais e os arquivos cadastrados
    $modulos = listas::get_modulos($CFG->dirmods);
    foreach ($modulos as $modulo) {

        // Obter os arquivos reais e os arquivos cadastrados no BD
        $arquivos_reais = get_arquivos_reais($CFG->dirmods.$modulo);
        $arquivos_bd    = get_arquivos_bd($modulo);

        imprimir_tabela($modulo, $arquivos_reais, $arquivos_bd);
    }
}


//
//     Imprime uma tabela com as diferencas entre dois vetores
//
function imprimir_tabela($modulo, $arquivos_reais, $arquivos_bd) {
// String $modulo: nome do modulo
// Array[String] $arquivos_reais: vetor de arquivos reais
// Array[String] $arquivos_bd: vetor de arquivos do BD
//
    global $CFG, $USUARIO;
    $arquivos = $arquivos_reais + $arquivos_bd;

    $sim = '<span class="sim">SIM</span>';
    $nao = '<span class="nao">N&Atilde;O</span>';

    $inconsistencias = array('alta'    => icone::img('vermelho', 'Alta'),
                             'media'   => icone::img('amarelo', 'M&eacute;dia'),
                             'nenhuma' => icone::img('verde', 'Nenhuma')
                            );

    echo "<table class=\"tabela\">\n";
    echo "<caption>Inconsist&ecirc;ncias do m&oacute;dulo {$modulo}</caption>\n";
    echo "<thead>\n";
    echo "  <tr>\n";
    echo "    <th>Arquivo</th>\n";
    echo "    <th>Existe no Sistema</th>\n";
    echo "    <th>Cadastrado no BD</th>\n";
    echo "    <th>Inclui sessao.php</th>\n";
    echo "    <th>Inconsist&ecirc;ncia</th>\n";
    echo "  </tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    foreach ($arquivos as $arquivo) {
        $descricao = montar_descricao($arquivo);
        $link = "inserir.php?arquivo={$arquivo}&amp;modulo={$modulo}&amp;descricao={$descricao}";
        $link_cadastrar_arquivo = link::arquivo_modulo($USUARIO, $link, 'arquivos', $arquivo, '', '', true);

        $arquivo_completo = $CFG->dirmods.$modulo.'/'.$arquivo;

        $existe        = in_array($arquivo, $arquivos_reais);
        $cadastrado    = in_array($arquivo, $arquivos_bd);
        $inclui_sessao = inclui_sessao($arquivo_completo);

        $sn_existe        = $existe        ? $sim : $nao;
        $sn_cadastrado    = $cadastrado    ? $sim : $nao;
        $sn_inclui_sessao = $inclui_sessao ? $sim : $nao;

        // Obter grau de inconsistencia
        if ($existe) {
            if ($cadastrado) {
                $inconsistencia = $inconsistencias['nenhuma'];
            } else {
                $inconsistencia = $inclui_sessao ? $inconsistencias['alta'] : $inconsistencias['media'];
            }
        } else {
            $inconsistencia = $cadastrado ? $inconsistencias['media'] : $inconsistencias['nenhuma'];
        }

        echo "  <tr>\n";
        echo "    <td>".((!$cadastrado) ? $link_cadastrar_arquivo : $arquivo)."</td>\n";
        echo "    <td>{$sn_existe}</td>\n";
        echo "    <td>{$sn_cadastrado}</td>\n";
        echo "    <td>{$sn_inclui_sessao}</td>\n";
        echo "    <td>{$inconsistencia}</td>\n";
        echo "  </tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
}


//
//     Obtem um vetor com os arquivos reais de um modulo
//
function get_arquivos_reais($diretorio) {
// String $diretorio: caminho completo ao diretorio do modulo
//
    $arquivos = array();
    $dir = opendir($diretorio);
    if (!$dir) {
        return $arquivos;
    }
    while (($item = readdir($dir)) !== false) {

        // Checar se e' um arquivo PHP
        if (preg_match('/^[A-z0-9-_\.]+\.php$/', $item)) {
            $arquivos[] = $item;
        }
    }
    return $arquivos;
}


//
//     Indica se um arquivo inclui o arquivo sessao.php ou nao
//
function inclui_sessao($arquivo) {
// String $arquivo: caminho completo ao arquivo
//
    if (!is_file($arquivo)) {
        return false;
    }
    $f = fopen($arquivo, 'r');
    if (!$f) {
        return false;
    }
    $l = 0;
    $achou = false;
    while (!$achou) {
        $linha = fgets($f, 1024);
        $linha = trim($linha);
        if (substr($linha, 0, 2) == '//') { continue; }
        if ((stripos($linha, 'sessao.php') !== false) && (stripos($linha, 'require') !== false)) {
            $achou = true;
        }
        $l++;
        if ($l >= 10) { break; }
    }
    fclose($f);
    return $achou;
}


//
//     Obtem um vetor com os arquivos de um modulo cadastrados no BD
//
function get_arquivos_bd($modulo) {
// String $modulo: nome do modulo
//
    $arquivos = array();
    $condicao = condicao_sql::montar('modulo', '=', $modulo);
    $arquivos_modulo = objeto::get_objeto('arquivo')->consultar_varios($condicao, array('arquivo'));
    foreach ($arquivos_modulo as $arquivo) {
        $arquivos[] = $arquivo->arquivo;
    }
    return $arquivos;
}


//
//     Monta a descricao baseada no nome do arquivo
//
function montar_descricao($arquivo) {
// String $arquivo: nome do arquivo
//
    $vt_nome = array();

    // Retirar a extensao
    $pos = strpos($arquivo, '.');
    $arquivo = substr($arquivo, 0, $pos);

    $vt_arquivo = explode('_', $arquivo);
    foreach ($vt_arquivo as $i) {
        $nome = ucfirst($i);
        if (substr($nome, -3) == 'cao') {
            $len  = strlen($nome);
            $nome = substr($nome, 0, $len - 3).texto::decodificar('&ccedil;&atilde;o');
        }
        $vt_nome[] = $nome;
    }
    return urlencode(implode(' ', $vt_nome));
}
