<?php
//
// SIMP
// Descricao: Arquivo que retorna se o link e' valido ou nao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 27/12/2007
// Modificado: 29/07/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

// Retornos:
define('TIPO_LINK_VALIDO',        0);
define('TIPO_LINK_INDETERMINADO', 1);
define('TIPO_LINK_INVALIDO',      2);

ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
header("Content-type: text/xml; charset=UTF-8");

// Obter host e link
$resultado = isset($_GET['link']) ? get_resultado($_GET['link']) : TIPO_LINK_INDETERMINADO;

$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<resultado>{$resultado}</resultado>
XML;

echo $xml;
exit(0);


//
//     Determina se um link esta quebrado ou nao
//
function get_resultado($url) {
// String $url: endereco a ser testado
//
    $vt = parse_url($url);
    $host  = isset($vt['host']) ? $vt['host'] : $_SERVER['HTTP_HOST'];
    $path  = isset($vt['path']) ? $vt['path'] : '/';
    $porta = isset($vt['port']) ? $vt['port'] : 80;

    $resultado = TIPO_LINK_INDETERMINADO;

    // Pedir retorno de cabecalho HTTP via metodo HEAD
    $resultado = http::enviar('HEAD', $host, $porta, $path);
    $retorno = $resultado->vt_header_resposta['resultado']->cod;

    if (is_numeric($retorno)) {
        if ($retorno == 302 || ($retorno >= 200 && $retorno < 300)) {
            $resultado = TIPO_LINK_VALIDO;
        } elseif ($retorno == 404) {
            $resultado = TIPO_LINK_INVALIDO;
            salvar_link_quebrado($url);
        }
    }
    return $resultado;
}


//
//     Salva o link quebrado em arquivo texto
//
function salvar_link_quebrado($link) {
// String $link: url do link quebrado
//
    global $CFG;

    $link = trim($link);

    // Checar se o link ja foi cadastrado no arquivo
    $f = fopen($CFG->dirarquivos.'links_quebrados.txt', 'r');
    if ($f) {
        while ($l = trim(fgets($f, 256))) {

            // Se ja existe: ignorar
            if ($l == $link) { return; }
        }
        fclose($f);
    }

    // Salvar novo link no arquivo
    $f = fopen($CFG->dirarquivos.'links_quebrados.txt', 'a');
    if ($f) {
        fwrite($f, $link."\n");
        fclose($f);
    }
}
