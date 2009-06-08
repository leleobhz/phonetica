<?php
//
// SIMP
// Descricao: Arquivo RDF com a descricao do Sistema (Veja: http://dublincore.org/)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.3
// Data: 06/11/2007
// Modificado: 02/03/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../config.php');

$titulo    = htmlspecialchars($CFG->titulo, ENT_COMPAT, $CFG->charset);
$palavras  = htmlspecialchars($CFG->palavras, ENT_COMPAT, $CFG->charset);
$descricao = htmlspecialchars($CFG->descricao, ENT_COMPAT, $CFG->charset);
$autor     = htmlspecialchars($CFG->autor, ENT_COMPAT, $CFG->charset);
$cidade    = htmlspecialchars($CFG->cidade, ENT_COMPAT, $CFG->charset);
$estado    = htmlspecialchars($CFG->estado, ENT_COMPAT, $CFG->charset);

setlocale(LC_ALL, 'C');

$dados = stat($CFG->dirroot.'index.php');
$time  = $dados['mtime'];
$data  = strftime('%Y-%m-%d', $time);
$ano   = strftime('%Y', $time);

$data_criacao = strftime('%Y-%m-%d', $CFG->instalacao);

$vt_condicoes = array();
$vt_condicoes[] = condicao_sql::montar('entidade', '=', 'config', false);
$vt_condicoes[] = condicao_sql::montar('operacao', '=', LOG_UPDATE, false);
$vt_condicoes[] = condicao_sql::montar('erro', '=', '0', false);
$condicoes = condicao_sql::sql_and($vt_condicoes);
$campos = array('data');

$resultado = objeto::get_objeto('log_sistema')->consultar_varios($condicoes, $campos);
if ($resultado && count($resultado)) {
    $time_modificacao = $resultado[count($resultado) - 1]->data;
    $data_modificacao = strftime('%Y-%m-%d', $time_modificacao);
} else {
    $data_modificacao = $data_criacao;
}

/// Imprimir RDF
header("Content-type: application/rdf+xml; charset={$CFG->charset}");
echo <<<XML
<?xml version="1.0" encoding="{$CFG->charset}"?>
<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:con="http://www.w3.org/2000/10/swap/pim/contact#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:doap="http://usefulinc.com/ns/doap#"
  xml:lang="{$CFG->lingua}">
  <rdf:Description rdf:about="{$CFG->wwwroot}">
    <dc:title>{$titulo}</dc:title>
    <dc:subject>{$palavras}</dc:subject>
    <dc:description>{$descricao}</dc:description>
    <dc:date rdf:datatype="http://www.w3.org/2001/XMLSchema#date">{$data}</dc:date>
    <dc:format>{$CFG->content}</dc:format>
    <dc:language>{$CFG->lingua}</dc:language>
    <dc:creator>{$autor}</dc:creator>
    <dc:created rdf:datatype="http://www.w3.org/2001/XMLSchema#date">{$data_criacao}</dc:created>
    <dc:modified rdf:datatype="http://www.w3.org/2001/XMLSchema#date">{$data_modificacao}</dc:modified>
    <con:address rdf:parseType="Resource">
      <con:city>{$cidade}</con:city>
      <con:stateOrProvince>{$estado}</con:stateOrProvince>
      <con:country>Brazil</con:country>
    </con:address>
    <dc:tableOfContents rdf:datatype="http://www.w3.org/2001/XMLSchema#anyURI">{$CFG->wwwmods}ajuda/mapa.php</dc:tableOfContents>
    <dc:rights rdf:datatype="http://www.w3.org/2001/XMLSchema#anyURI">{$CFG->wwwmods}ajuda/licenca.php</dc:rights>
    <dc:license rdf:resource="{$CFG->wwwmods}ajuda/licenca.php">GNU GPL 2</dc:license>
    <rdfs:seeAlso rdf:datatype="http://www.w3.org/2001/XMLSchema#anyURI">{$CFG->wwwmods}ajuda/index.php</rdfs:seeAlso>
  </rdf:Description>
  <doap:Project>
    <doap:name>{$titulo}</doap:name>
    <doap:shortdesc>{$descricao}</doap:shortdesc>
    <doap:created rdf:datatype="http://www.w3.org/2001/XMLSchema#date">{$data_criacao}</doap:created>
    <doap:release>
      <doap:Version>
        <doap:name>{$CFG->sistema}-{$CFG->versao}</doap:name>
        <doap:revision>{$CFG->versao}</doap:revision>
      </doap:Version>
    </doap:release>
    <doap:license rdf:resource="http://usefulinc.com/doap/licenses/GPL" />
  </doap:Project>
</rdf:RDF>
XML;
