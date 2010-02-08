<?php
//
// SIMP
// Descricao: Arquivo para transformar o XML em HTML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.11
// Data: 20/12/2007
// Modificado: 23/10/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

/// Obter dados enviados por parametro
$dados = util::get_dado('dados', 'string');
$input = util::get_dado('input', 'string', false);
list($classe, $campo_preencher, $campo_exibir, $condicoes) = explode(';', base64_decode($dados));

if ($input):
$template_entidade = <<<XML
<xsl:template match="entidade">
  <li>
    <xsl:element name="strong">
      <xsl:attribute name="style">cursor: pointer; text-decoration: underline;</xsl:attribute>
      <xsl:attribute name="onclick">
        <xsl:text>window.opener.document.getElementById('{$input}').value = '</xsl:text>
        <xsl:value-of select="codigo" />
        <xsl:text>'; window.close(); return false;</xsl:text>
      </xsl:attribute>
      <xsl:value-of select="codigo" />
    </xsl:element>
    <xsl:text> - </xsl:text>
    <span><xsl:value-of select="valor" /></span>
  </li>
</xsl:template>
XML;
else:
$template_entidade = <<<XML
<xsl:template match="entidade">
  <li>
    <strong><xsl:value-of select="codigo" /></strong>
    <xsl:text> - </xsl:text>
    <span><xsl:value-of select="valor" /></span>
  </li>
</xsl:template>
XML;
endif;


try {
    $obj = new $classe();
    $entidade = texto::decodificar($obj->get_entidade(true));
    $atributo = $obj->get_definicao_atributo($campo_preencher);
    $descricao_atributo = texto::decodificar($atributo->descricao);
    $tipo_atributo = $atributo->tipo;
} catch (Exception $e) {
    $entidade = 'Entidades';
    $descricao_atributo = 'código';
    $tipo_atributo = false;
}
$omit_xml = (stripos($_SERVER['HTTP_ACCEPT'], 'text/xml') !== false) ? 'no' : 'yes';

if (!$CFG->agent->ie) {
    $xml_header = "<xsl:output method=\"xml\" version=\"1.0\" encoding=\"{$CFG->charset}\" omit-xml-declaration=\"{$omit_xml}\" standalone=\"no\" />";
} else {
    $xml_header = '';
}


// Campo usado para ordenar a lista
$ordem = 'codigo';
switch ($tipo_atributo) {
case 'int':
case 'float':
    $tipo_ordem = 'number';
    break;
case 'string':
case 'char':
case 'bool':
default:
    $tipo_ordem = 'text';
    break;
}


/// Montar XML
$xml = <<<XSL
<?xml version="1.0" encoding="{$CFG->charset}" ?>
<xsl:stylesheet version="1.1"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xlink="http://www.w3.org/1999/xlink"
  xml:lang="pt-br">

{$xml_header}
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>

<!-- TEMPLATE GERAL (Estrutura do documento) -->
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml" xml:space="default" dir="ltr">

<head>
  <title>Lista de {$entidade}</title>
  <link rel="stylesheet" type="text/css" charset="{$CFG->charset}" media="screen" href="{$CFG->wwwlayout}{$CFG->pessoal->tema}/index.css.php" />
</head>

<body>

<h1>Lista de {$entidade}</h1>
<p>Selecione um(a) {$descricao_atributo}:</p>
<ul class="lista_busca">
<xsl:apply-templates select="entidades" />
</ul>

<hr />
<p>Total: <xsl:value-of select="count(//codigo)" /></p>

</body>
</html>
</xsl:template>


<!-- TEMPLATE DA LISTA -->
<xsl:template match="entidades">
  <xsl:apply-templates>
    <xsl:sort select="{$ordem}" data-type="{$tipo_ordem}" />
  </xsl:apply-templates>
</xsl:template>


<!-- TEMPLATE PARA UM ITEM DA LISTA -->
{$template_entidade}

</xsl:stylesheet>
XSL;


/// Definir header
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename=lista_{$classe}.xsl");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, $CFG->time));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($xml);
echo $xml;
exit(0);
