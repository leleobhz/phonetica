<?php
//
// SIMP
// Descricao: Lista de programas recomendados
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.6
// Data: 26/11/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Lista de programas por categorias
$programas = array();


// CATEGORIA: VISUALIZADORES DE DOCUMENTOS PDF
$categoria = 'Visualizadores PDF';

// Adobe Reader
$programa = new stdClass();
$programa->programa = 'Adobe Reader';
$programa->descricao = <<<DESC
  A Adobe &eacute; a empresa que criou o formato Adobe&reg; PDF. Disponibiliza vers&otilde;es gratuitas
  do programa Adobe Reader para visualizar documentos neste formato. O programa possui vers&otilde;es
  para diferentes plataformas como Windows e Linux.
DESC;
$programa->site = 'http://www.adobe.com/';
$programa->download = 'http://www.adobe.com/br/products/acrobat/readstep2.html';
$programas[$categoria][] = $programa;

// Evince
$programa = new stdClass();
$programa->programa = 'Evince';
$programa->descricao = <<<DESC
  Evince &eacute; um visualizador de documentos. Atualmente reconhece os seguintes formatos:
  pdf, postscript, djvu, tiff, dvi entre outros. &Eacute; um software livre e gratuito oferecido para
  Linux.
DESC;
$programa->site = 'http://www.gnome.org/projects/evince/';
$programa->download = 'http://live.gnome.org/Evince/Downloads';
$programas[$categoria][] = $programa;

// KPDF
$programa = new stdClass();
$programa->programa = 'KPDF';
$programa->descricao = <<<DESC
  KPDF &eacute; um visualizador de documentos PDF para KDE (ambiente gr&aacute;fico para Linux)
  baseado no XPDF.
DESC;
$programa->site = 'http://kpdf.kde.org/';
$programa->download = 'http://kpdf.kde.org/download.php';
$programas[$categoria][] = $programa;


// CATEGORIA: NAVEGADORES WEB
$categoria = 'Navegadores Web';

// Firefox
$programa = new stdClass();
$programa->programa = 'Firefox';
$programa->descricao = <<<DESC
  Um navegador r&aacute;pido e seguro origin&aacute;rio do navegador Mozilla. Reconhece bem os padr&otilde;es
  definidos pela W3C, d&aacute; suporte a temas e extens&otilde;es, corretor ortogr&aacute;fico, navega&ccedil;&atilde;o
  em abas e muito mais. &Eacute; um software livre e gratuito dispon&iacute;vel para v&aacute;rias plataformas, 
  incluindo Windows, Linux e Mac OS X.
DESC;
$programa->site = 'http://br.mozdev.org/';
$programa->download = 'http://br.mozdev.org/firefox/download.html';
$programas[$categoria][] = $programa;

// Opera
$programa = new stdClass();
$programa->programa = 'Opera';
$programa->descricao = <<<DESC
  Navegador extremamente r&aacute;pido e reconhece muito bem os padr&otilde;es definidos pela W3C. N&atilde;o &eacute; 
  software livre, por&eacute;m &eacute; gratuito. Oferece navega&ccedil;&atilde;o em abas, seguran&ccedil;a,
  customiza&ccedil;&atilde;o do layout do programa e das p&aacute;ginas.
DESC;
$programa->site = 'http://www.opera.com/';
$programa->download = 'http://www.opera.com/download/';
$programas[$categoria][] = $programa;


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Programas Recomendados';
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
imprimir_programas($programas);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de programas recomendados
//
function imprimir_programas($programas) {
// Array[String => Array[Object]] $programas: vetor com as categorias apontando para um vetor com os dados dos programas
//
    global $CFG;
    echo "<p>A lista a seguir est&aacute; separada por categorias que apresentam diferentes possibilidades de programas. ";
    echo "Recomenda-se ter pelo menos um dos programas de cada categoria ";
    echo "<a class=\"link_observacao\" href=\"{$CFG->site}#observacao_local\">*</a>.</p>\n";
    echo "<ul>\n";
    foreach ($programas as $categoria => $possibilidades) {
        $c = texto::strip_acentos($categoria);
        $c = texto::strtolower($c);
        $c = str_replace(' ', '_', $c);

        echo "<li id=\"categoria_{$c}\"><strong>{$categoria}</strong></li>\n";
        echo "<ul>\n";
        foreach ($possibilidades as $possibilidade) {
            echo "<li>\n";
            echo "  <p>Programa: <em>{$possibilidade->programa}</em></p>\n";
            echo "  <p>Descri&ccedil;&atilde;o: {$possibilidade->descricao}</p>\n";
            echo "  <p>Site Oficial: <a rel=\"checar\" href=\"{$possibilidade->site}\" title=\"{$possibilidade->programa}\">{$possibilidade->site}</a></p>\n";
            echo "  <p>Download: <a rel=\"checar\" href=\"{$possibilidade->download}\" title=\"Download do {$possibilidade->programa}\">{$possibilidade->download}</a></p>\n";
            echo "</li>\n";
        }
        echo "</ul>\n";
    }
    echo "</ul>\n";
    echo "<p class=\"observacao\" id=\"observacao_local\">* A lista n&atilde;o apresenta todas as possibilidades de programas ".
         "para cada categoria, mas apresenta algumas alternativas que foram classificadas como satisfat&oacute;rias pelos ".
         "desenvolvedores do sistema.</p>\n";
}

