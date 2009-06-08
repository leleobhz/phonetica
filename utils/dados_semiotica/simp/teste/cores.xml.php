<?php
//@ignoredoc
require_once('../config.php');
/*
Este arquivo apresenta uma lista de entidades para ser usado em um
campo_relacionamento_xml (definido pela classe formulario).

Deve conter um elemento raiz chamado "entidades" que guardara
uma lista de elementos do tipo "entidade". Cada elemento
"entidade" deve conter dois elementos: um chamado "codigo", com
o campo que sera preenchido no formulario, e outro chamado "valor",
com o campo que sera utilizado apenas para exibir ao usuario para
facilitar a busca entre as entidades.
*/

$input = util::get_dado('input', 'string', false);

header('Content-type: text/xml; charset=UTF-8');
echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{$CFG->wwwroot}webservice/template.xsl.php?input={$input}"?>
<entidades>
  <entidade>
    <codigo><![CDATA[1]]></codigo>
    <valor><![CDATA[Vermelho]]></valor>
  </entidade>

  <entidade>
    <codigo><![CDATA[2]]></codigo>
    <valor><![CDATA[Amarelo]]></valor>
  </entidade>

  <entidade>
    <codigo><![CDATA[3]]></codigo>
    <valor><![CDATA[Verde]]></valor>
  </entidade>
</entidades>
XML;
