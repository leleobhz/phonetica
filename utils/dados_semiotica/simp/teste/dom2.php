<?php
//
// SIMP
// Descricao: Segundo exemplo de como criar um documento com DOM
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Cria o documento XML versao 1.0 e codificacao UTF-8
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->xmlStandalone       = false;
$dom->resolveExternals    = true;
$dom->formatOutput        = true;
$dom->preserveWhiteSpace  = true;
$dom->strictErrorChecking = true;

// Cria o elemento raiz do documento
$raiz = $dom->createElement('raiz');
{
    $raiz->setAttribute('x', 1);
    $raiz->setAttribute('y', 2);
    $raiz->setAttribute('z', 'Rubens');
    $raiz->removeAttribute('y');
    $x = $raiz->getAttribute('x');

    $elem = $dom->createElement('elemento_vazio');
    {
        $elem->setAttribute('a', 'b');
        $raiz->appendChild($elem);
    }

    $elem2 = $dom->createElement('elemento', 'conteudo');
    {
        $raiz->appendChild($elem2);
    }

    $comentario = $dom->createComment('Texto do comentario');
    {
        $raiz->appendChild($comentario);
    }
}
$dom->appendChild($raiz);

echo $dom->saveXML();
