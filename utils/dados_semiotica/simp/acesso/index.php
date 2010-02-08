<?php
//
// SIMP
// Descricao: Arquivo de direcionamento de usuarios perdidos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 11/11/2009
// Modificado: 11/11/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

/// Dados da Pagina
$titulo  = 'Formas de Acesso';
$nav['']   = 'Acesso';
$estilos = false;


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
exibir_formas_acesso();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


///     Funcoes


//
//     Exibe as formas de acesso
//
function exibir_formas_acesso() {
    global $CFG;

    $vt_acessos = array();
    $dirname = dirname(__FILE__);
    $dir = opendir($dirname);
    while ($item = readdir($dir)) {
        if (substr($item, 0, 1) == '.' || !is_dir($dirname.'/'.$item)) {
            continue;
        }
        $arq_descricao = $dirname.'/'.$item.'/descricao.txt';
        if (is_file($arq_descricao)) {
            $vt_acessos[$item] = trim(file_get_contents($arq_descricao));
        }
    }
    closedir($dir);

    asort($vt_acessos);

    echo '<ul>';
    foreach ($vt_acessos as $acesso => $descricao) {
        echo '<li><a href="'.$CFG->wwwroot.'acesso/'.$acesso.'/">'.$descricao.'</a></li>';
    }
    echo '</ul>';
}
