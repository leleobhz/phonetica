<?php
//
// SIMP
// Descricao: Lista as tecnologias, linguagens e normas empregadas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.8
// Data: 07/11/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Tecnologias, Linguagens e Normas Empregadas';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_tecnologias();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime as tecnologias empregadas no sistema
//
function imprimir_tecnologias() {
    global $CFG;

    // XHTML
    $nome  = 'XHTML 1.0 Strict';
    $check = 'http://validator.w3.org/check?verbose=1&amp;uri='.urlencode($CFG->wwwroot);
    $link  = 'http://www.w3.org/TR/xhtml1/';
    $src   = $CFG->wwwimgs.'tecnologias/xhtml10.png';
    $img   = "<img src=\"{$src}\" alt=\"XHTML 1.0\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc  = "Linguagem de Marca&ccedil;&atilde;o: <abbr title=\"eXtensible HyperText Markup Language\">XHTML</abbr> 1.0 Strict";
    $validar = "<a rel=\"blank\" href=\"{$check}\" title=\"Validar {$nome}\">Validar</a>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc} / {$validar}</p>\n";

    // CSS
    $nome  = 'CSS 3';
    $check = 'http://jigsaw.w3.org/css-validator/validator?uri='.urlencode($CFG->wwwlayout.TEMA_PADRAO.'/index.css.php').'&amp;warning=1&amp;profile=css3&amp;usermedium=all';
    $link  = 'http://www.w3.org/TR/css3-roadmap/';
    $src   = $CFG->wwwimgs.'tecnologias/css3.png';
    $img   = "<img src=\"{$src}\" alt=\"CSS 3\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc  = "Linguagem de Estilos: <abbr title=\"Cascading Style Sheet\">CSS</abbr> 3";
    $validar = "<a rel=\"blank\" href=\"{$check}\" title=\"Validar {$nome}\">Validar</a>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc} / {$validar}</p>\n";

    // XML
    $nome = 'XML 1.0';
    $link = 'http://www.w3.org/TR/xml/';
    $src  = $CFG->wwwimgs.'tecnologias/xml10.png';
    $img  = "<img src=\"{$src}\" alt=\"XML 1.0\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc = "Linguagem de transfer&ecirc;ncia de dados: <abbr title=\"eXtensible Markup Language\">XML</abbr> 1.0";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc}</p>\n";

    // WCAG 1.0 AAA
    $nome  = 'WCAG 1.0 AAA';
    $check = 'http://www.cynthiasays.com/mynewtester/cynthia.exe?rptmode=2&amp;url1='.urlencode($CFG->wwwroot);
    $link  = 'http://www.w3.org/TR/WAI-WEBCONTENT/';
    $src   = $CFG->wwwimgs.'tecnologias/wcag1AAA.gif';
    $img   = "<img src=\"{$src}\" alt=\"WCAG 1.0 AAA\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc  = "De acordo com as diretrizes <abbr title=\"Web Content Accessibility Guidelines\">WCAG</abbr> 1.0 n&iacute;vel AAA (Triplo A)";
    $validar = "<a rel=\"blank\" href=\"{$check}\" title=\"Validar {$nome}\">Validar</a>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc} / {$validar}</p>\n";

    // Section 508
    $nome  = 'Sec&ccedil;&atilde;o 508';
    $check = 'http://www.cynthiasays.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1='.urlencode($CFG->wwwroot);
    $link  = 'http://www.section508.gov/';
    $src   = $CFG->wwwimgs.'tecnologias/section_508.jpg';
    $img   = "<img src=\"{$src}\" alt=\"Sec&ccedil;&atilde;o 508\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc  = "De acordo com a Sec&ccedil;&atilde;o 508 de Acessibilidade";
    $validar = "<a rel=\"blank\" href=\"{$check}\" title=\"Validar {$nome}\">Validar</a>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc} / {$validar}</p>\n";

    // RSS
    $nome = 'RSS 2.0';
    $link = 'http://cyber.law.harvard.edu/rss/rss.html';
    $src  = $CFG->wwwimgs.'tecnologias/rss.gif';
    $img  = "<img src=\"{$src}\" alt=\"RSS 2.0\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc = "Linguagem de apresenta&ccedil;&atilde;o de not&iacute;cias: <abbr title=\"Really Simple Syndication\">RSS</abbr> 2.0";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc}</p>\n";

    // RDF
    $nome = 'RDF';
    $link = 'http://www.w3.org/RDF/';
    $src  = $CFG->wwwimgs.'tecnologias/rdf.gif';
    $img  = "<img src=\"{$src}\" alt=\"RDF\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc = "Linguagem de compartilhamento de informa&ccedil;&otilde;es: <abbr title=\"Resource Description Framework\">RDF</abbr>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc}</p>\n";

    // Ajax
    $nome = 'Ajax';
    $link = 'http://en.wikipedia.org/wiki/Ajax_(programming)';
    $src  = $CFG->wwwimgs.'tecnologias/ajax.gif';
    $img  = "<img src=\"{$src}\" alt=\"Ajax\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
    $desc = "Tecnologias para maior interatividade web e redu&ccedil;&atilde;o de tr&aacute;fego na rede: <abbr title=\"Asynchronous Javascript And XML\">Ajax</abbr>";
    echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc}</p>\n";

    // Unicode
    if ($CFG->utf8) {
        $nome = 'Unicode';
        $link = 'http://www.unicode.org/';
        $src  = $CFG->wwwimgs.'tecnologias/unicode.gif';
        $img  = "<img src=\"{$src}\" alt=\"Codificado em UTF-8\" longdesc=\"{$link}\" ".imagem::tamanho_html($src).' />';
        $desc = "Codificado em Unicode (UTF-8) para internacionaliza&ccedil;&atilde;o";
        echo "<p><a href=\"{$link}\" title=\"{$nome}\">{$img}</a> - {$desc}</p>\n";
    }
}
