<?php
//
// SIMP
// Descricao: Arquivo para transformar o XML de uma lista de entidades em HTML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 30/01/2008
// Modificado: 23/10/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

$input = util::get_dado('input', 'string', false);
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
  <li><strong><xsl:value-of select="codigo" /></strong> - <span><xsl:value-of select="valor" /></span></li>
</xsl:template>
XML;
endif;

// Montar XML
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.1"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xlink="http://www.w3.org/1999/xlink"
  xml:lang="pt-br">

<xsl:output method="xml" version="1.0" encoding="utf-8" omit-xml-declaration="no" standalone="no" />
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>

<!-- TEMPLATE GERAL (Estrutura do documento) -->
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml" xml:space="default" dir="ltr">

<head>
  <title>Lista</title>
  <link rel="stylesheet" type="text/css" charset="utf-8" media="screen" href="{$CFG->wwwlayout}{$CFG->pessoal->tema}/index.css.php" />
</head>

<body>

<h1>Lista</h1>
<p>Selecione um valor:</p>
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
    <xsl:sort select="codigo" data-type="text" />
  </xsl:apply-templates>
</xsl:template>


<!-- TEMPLATE PARA UM ITEM DA LISTA -->
{$template_entidade}

</xsl:stylesheet>
XML;

/// Definir header
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset=UTF-8');
header("Content-Disposition: inline; filename=template.xsl");
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

