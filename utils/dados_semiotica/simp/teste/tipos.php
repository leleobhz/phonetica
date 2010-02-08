<?php
//
// SIMP
// Descricao: Descricao de como montar a documentacao dos tipos de dados usados no Simp
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

echo '<h1>Documenta&ccedil;&atilde;o de Tipos</h1></h1>';
echo '<p>Tipos v&aacute;lidos para documenta&ccedil;&atilde;o seguem o seguinte padr&atilde;o:</p>';
echo '<p><strong>{tipo}</strong> = Int, Float, Bool, String, Char, Null, Array, Object, Resource, callback, Mixed, <strong>{vetor}</strong>, <strong>{classe}</strong>, <strong>{tipo}</strong> || <strong>{tipo}</strong>, (<strong>{tipo}</strong>)</p>';
echo '<p><strong>{vetor}</strong> = Array[<strong>{valor}</strong>], Array[<strong>{indice}</strong> => <strong>{valor}</strong>]</p>';
echo '<p><strong>{indice}</strong> = Int, String, Char, Mixed, <strong>{indice}</strong> || <strong>{indice}</strong></p>';
echo '<p><strong>{valor}</strong> = Int, Float, Bool, String, Char, Array, Object, Resource, callback, Mixed, Type, <strong>{vetor}</strong>, <strong>{classe}</strong>, <strong>{valor}</strong> || <strong>{valor}</strong>, (<strong>{valor}</strong>)</p>';
echo '<p><strong>{classe}</strong> = Qualquer classe definida no sistema</p>';
echo '<hr />';
echo '<h1>Observa&ccedil;&otilde;es:</h1>';
echo '<p>A checagem de tipos &eacute; case-sensitive.</p>';
echo '<p>A palavra reservada "Type" &eacute; usada para indicar um tipo recursivo envolvendo vetores.</p>';
echo '<p>Por exemplo: <code>Array[String || Type]</code> &eacute; o mesmo que <code>Array[String || Array[String || Array[...]]]</code>, que corresponde a substituir a palavra "Type" pela pr&oacute;pria defini&ccedil;&atilde;o do tipo.</p>';
echo '<p>Observe que o tipo "Type" &eacute; especial para recurs&atilde;o e n&atilde;o deve ser usado isoladamente.</p>';
echo '<p>Deve-se evitar utilizar "Mixed", dando-se prefer&ecirc;ncia ao s&iacute;mbolo || (ou).</p>';
echo '<p>Por exemplo, para especificar um tipo Inteiro ou Booleano, usar <code>Int || Bool</code> ao inv&eacute;s de <code>Mixed</code>.</p>';
echo '<p>A palavra "Mixed" deve ser usada quando n&atilde;o se sabe com exatid&atilde;o qual o tipo recebido.</p>';
echo '<p>A palavra "callback" &eacute; um pseudo-tipo que indica uma forma de invocar uma fun&ccedil;&atilde;o ou m&eacute;todo.</p>';
echo '<hr />';
echo '<h1>Exemplos</h1>';

$vt = array(
'int',
'Int',
'Integer',
'Char',
'Float',
'Double',
'Object',
'stdClass',
'Mixed',
'callback',
'usuario',
'Bool || Int || Float',
'Array[Float]',
'Array[String => Int]',
'Array[oi]',
'Array[Int || String => Object || Resource] || Int',
'Array[(Int || String) => (Object || Resource)] || Int',
'Array[(String => Object) || Int || Resource] || Int',
'Array[(Float => String) || Int]',
'Array[usuario]',
'Array[usuario => String]',
'Array[Float => Int]',
'Array[String || Type]',
'Array[callback]',
'Type'
);

foreach ($vt as $tipo) {
    if (parser_simp::validar_tipo($tipo)) {
        echo '<p style="color: #006600; margin: 1px;"><code>'.$tipo.'</code> = v&aacute;lido</p>';
    } else {
        echo '<p style="color: #660000; margin: 1px;"><code>'.$tipo.'</code> = inv&aacute;lido</p>';
    }
}
