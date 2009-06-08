<?php
//
// SIMP
// Descricao: Arquivo para listar entidades e codigos de maneira hierarquica
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 14/02/2008
// Modificado: 23/10/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

// Obter link do XML a ser aberto
$link  = util::get_dado('link', 'string');
$arq   = basename($link);
$input = util::get_dado('input', 'string', false);

// Obter itens a serem abertos
$itens_abertos = util::get_dado('a', 'array', false, array());
array_multisort(array_keys($itens_abertos), SORT_ASC, SORT_NUMERIC, $itens_abertos);

// Consultar o XML
$xml_str = file_get_contents($link);
$pos = strrpos($xml_str, '?'.'>');
if ($pos !== false) {
    $xml_str = substr($xml_str, $pos + 2);
}

$xml_str = <<<XML
<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="{$CFG->wwwroot}webservice/template_hierarquia.xsl.php?input={$input}"?>
{$xml_str}
XML;

$xml = simplexml_load_string($xml_str);

// Se informou os itens a serem abertos
if (count($itens_abertos)) {

    $nivel = 0;
    foreach ($itens_abertos as $posicao) {
        $posicao = (int)$posicao;

        // Se existe a posicao no nivel
        if ($xml->item[$posicao]) {
            $xml = &$xml->item[$posicao];

        // Se nao existe a posicao no nivel
        } else {
            $xml_str = <<<XML
<?xml version="1.0"?>
<!-- Nenhum Item -->
<item/>
XML;
            $xml = simplexml_load_string($xml_str);
        }
    }
}

// Remover os filhos
if (!$input) {
    foreach ($xml->item as $item) {
        $item->addAttribute('eh_grupo', isset($item->item) ? '1' : '0');
        unset($item->item);
    }
}

// Obter conteudo xml a ser exibido
$str_xml = $xml->asXML();

/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename={$arq}");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, $CFG->time));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($str_xml);
echo $str_xml;
exit(0);
