<?php
//@ignoredoc
$t = microtime(true);

$wwwroot = 'http://'.$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI'];

$charset = 'application/xhtml+xml';
//$charset = 'text/html';
//$charset = 'text/plain';

$imp = new DOMImplementation();
$dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Strict//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd');
$doc = $imp->createDocument('http://www.w3.org/1999/xhtml', 'html', $dtd);

$doc->xmlVersion = '1.0';
$doc->xmlStandalone = false;
$doc->resolveExternals = true;
$doc->formatOutput = true;
$doc->preserveWhiteSpace = true;
$doc->strictErrorChecking = true;
$doc->documentURI = $wwwroot;

$p = $doc->createProcessingInstruction('xml-stylesheet', 'oi');
$doc->insertBefore($p, $dtd);

$html = $doc->documentElement; {
    $html->setAttribute('dir', 'ltr');
    $html->setAttribute('xml:lang', 'pt-br');

    $head = $doc->createElement('head'); {
        $meta = $doc->createElement('meta'); {
            $meta->setAttribute('http-equiv', 'Content-Type');
            $meta->setAttribute('value', $charset.'; charset=UTF-8');
            $head->appendChild($meta);
        }

        $base = $doc->createElement('base'); {
            $base->setAttribute('id', 'wwwroot');
            $base->setAttribute('href', $wwwroot);
            $head->appendChild($base);
        }

        $title = $doc->createElement('title'); {
            $texto = $doc->createTextNode('Título'); {
                $title->appendChild($texto);
            }
            $head->appendChild($title);
        }
        $html->appendChild($head);
    }

    $comment = $doc->createComment(' Inicio do Body ');
    $html->appendChild($comment);

    $body = $doc->createElement('body'); {
        $h1 = $doc->createElement('h1'); {
            $texto = $doc->createTextNode('Título de Teste'); {
                $h1->appendChild($texto);
            }
            $body->appendChild($h1);
        }

        $f = $doc->createDocumentFragment(); {
            $f->appendXML('<p>oi &amp; olá – x</p>');
            $body->appendChild($f);
        }

        $html->appendChild($body);
    }
}

$doc->normalizeDocument();
$t = round(microtime(true) - $t, 3);
$doc->appendChild($doc->createComment('Carregamento: '.$t.' segundos'));

header('Content-Type: '.$charset.'; charset=UTF-8');
echo ($charset == 'application/xhtml+xml') ? $doc->saveXML() : $doc->saveHtml();
