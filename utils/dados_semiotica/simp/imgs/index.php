<?php
//
// SIMP
// Descricao: Lista as imagens do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.5
// Data: 13/09/2007
// Modificado: 09/02/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../config.php');

$titulo = 'Imagens';
$nav[$CFG->wwwroot] = 'P&aacute;gina Principal';
$nav[''] = 'Imagens';
$estilos = false;

$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_imagens('');
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Lista as imagens da pasta
//
function listar_imagens($diretorio) {
// String $diretorio: caminho relativo do diretorio de imagens a ser exibido
//
    global $CFG;

    $dir = opendir($CFG->dirimgs.$diretorio);
    if (!$dir) {
        trigger_error('Erro ao abrir diretorio para leitura ('.$CFG->dirimgs.$diretorio.')', E_USER_WARNING);
        return false;
    }
    $diretorios = array();
    $imagens = array();

    $imagens = array();
    while (($item = readdir($dir)) !== false) {
        if ($item == '.' || $item == '..' || $item == '.svn') { continue; }
        if (is_dir($item)) {
            $diretorios[] = $item;
        } elseif (is_image($item)) {
            $imagens[] = $item;
        }
    }
    closedir($dir);

    if (count($diretorios)) {
        sort($diretorios);
    }
    if (count($imagens)) {
        sort($imagens);
    }

    echo "<h3 class=\"diretorio\">imgs/{$diretorio}</h3>";
    echo "<ol>\n";
    foreach ($imagens as $src) {
        $src_completo = $CFG->wwwimgs.$diretorio.$src;
        $def = getimagesize($src_completo);
        if ($def) {
            $size = $def[3];
            $img = "<img src=\"$src_completo\" alt=\"{$src}\" {$size}/>";
        } else {
            $img = '[imagem]';
        }
        $link = "<a href=\"{$CFG->wwwimgs}{$diretorio}{$src}\" title=\"{$src}\">{$src}</a>";
        echo "<li>{$img} - {$link}</li>\n";
    }
    echo "</ol>\n";
    foreach ($diretorios as $subdiretorio) {
        listar_imagens($subdiretorio.'/');
    }
}


//
//     Checa se um nome de arquivo corresponde a uma imagem
//
function is_image($arq) {
// String $arq: nome do arquivo
//
    $imgs = array('jpg', 'png', 'gif', 'jpeg', 'bmp', 'svg');

    $pos = strrpos($arq, '.');
    $ext = substr($arq, $pos + 1);

    return in_array($ext, $imgs);
}
