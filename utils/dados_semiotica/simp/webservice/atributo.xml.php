<?php
//
// SIMP
// Descricao: Gera a descricao de um atributo de uma classe em formato XML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.0
// Data: 18/06/2009
// Modificado: 25/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');


/// Obter classe
$id_campo = util::get_dado('id', 'string');
$id_campo = base64_decode($id_campo);

list($classe, $atributo, $id_form) = explode(':', $id_campo);
$classe   = trim($classe);
$atributo = trim($atributo);
$id_form  = trim($id_form);


/// Obter entidade
$erro = false;
try {
    simp_autoload($classe);
    $entidade = new $classe();
} catch (Exception $e) {
    $erro = true;
}

$entidade->set_id_form($id_form);
$definicao = $entidade->get_info_campo($atributo);
if (!$definicao) {
    $erro = true;
}

/// Montar conteudo XML
$xml = "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n";
if (!$erro) {
    $xml_definicao = $entidade->gerar_definicao_atributo_xml($definicao);
    $xml .= $xml_definicao;
} else {
    $xml .= '<erro>1</erro>';
}

/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename={$classe}-{$atributo}-{$id_form}.xml");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, filemtime($CFG->dirclasses)));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
setlocale(LC_ALL, 
$CFG->localidade);
compactacao::header($xml);
echo $xml;
exit(0);
