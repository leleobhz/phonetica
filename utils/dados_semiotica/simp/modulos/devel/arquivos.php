<?php
//
// SIMP
// Descricao: Script que lista as funcoes de cada arquivo do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.20
// Data: 31/07/2007
// Modificado: 18/08/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Lista de Arquivos';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Arquivos';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_arquivos();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de arquivos
//
function imprimir_arquivos() {
    global $CFG;
    if (!isset($_SESSION['devel']['abrir'])) {
        $_SESSION['devel']['abrir'] = array();
        $_SESSION['devel']['estatisticas_arquivos'] = false;
    }
    $estatisticas = unserialize($_SESSION['devel']['estatisticas_arquivos']);
    if (isset($_GET['item'])) {
        $item = base64_decode($_GET['item']);
        if (aberto($item)) {
            fechar($item);
        } else {
            abrir($item);
        }
    } elseif (isset($_GET['calcular_estatisticas'])) {
        $estatisticas = calcular_estatisticas();
        $_SESSION['devel']['estatisticas_arquivos'] = serialize($estatisticas);
    } elseif (isset($_GET['limpar'])) {
        $estatisticas = false;
        $_SESSION['devel']['estatisticas_arquivos'] = false;
    }
    $arquivos = get_arquivos($CFG->dirroot, '', $_SESSION['devel']['abrir']);
    lista::hierarquica($arquivos);

    $link = $CFG->site;
    link::normalizar($link, true);
    $link = link::adicionar_atributo($link, 'calcular_estatisticas', 1);

    // Exibir estatisticas
    if ($estatisticas) {
        $num_paginas = round($estatisticas->tamanho_total / texto::desformatar_bytes('3K'));
        $porcentagem = 100 - round($estatisticas->tamanho_real * 100 / $estatisticas->tamanho_total, 2);

        $icone_amarelo  = icone::img('amarelo', 'Aten&ccedil;&atilde;o');
        $icone_verde    = icone::img('verde', 'OK');
        $icone_vermelho = icone::img('vermelho', 'Muita aten&ccedil;&atilde;o');

        if ($porcentagem < 25) {
            $situacao = "H&aacute; poucos coment&aacute;rios ou a identa&ccedil;&atilde;o pode estar errada (4). {$icone_amarelo}";
        } elseif ($porcentagem < 50) {
            $situacao = "Situa&ccedil;&atilde;o esperada. {$icone_verde}";
        } elseif ($porcentagem < 65) {
            $situacao = "H&aacute; um pouco mais de coment&aacute;rios e espa&ccedil;os do que o esperado, a identa&ccedil;&atilde;o pode estar errada (4). {$icone_amarelo}";
        } else {
            $situacao = "O valor est&aacute; muito alto, reduzindo a performance dos c&oacute;digos. Reduza os coment&aacute;rios e utilize identa&ccedil;&atilde;o de tamanho 4! {$icone_vermelho}";
        }

        if (count($estatisticas->sistemas) > 1) {
            echo '<h2>Sistemas</h2>';
            echo '<table class="tabela">';
            echo '<caption>Tabela de tamanhos dos sub-sistemas</caption>';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Sistema</th>';
            echo '<th scope="col">Arquivos</th>';
            echo '<th scope="col">Tamanho Total</th>';
            echo '<th scope="col">Tamanho Real</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tfoot>';
            echo '<tr>';
            echo '<td scope="row">Total</td>';
            echo '<td>'.$estatisticas->total.' (100%)</td>';
            echo '<td>'.texto::formatar_bytes($estatisticas->tamanho_total).' (100%)</td>';
            echo '<td>'.texto::formatar_bytes($estatisticas->tamanho_real).' (100%)</td>';
            echo '</tr>';
            echo '</tfoot>';
            echo '<tbody>';
            foreach ($estatisticas->sistemas as $sistema) {
                $sistema->porcentagem = new stdClass();
                $sistema->porcentagem->total = round($sistema->total * 100 / $estatisticas->total, 2);
                $sistema->porcentagem->tamanho_total = round($sistema->tamanho_total * 100 / $estatisticas->tamanho_total, 2);
                $sistema->porcentagem->tamanho_real = round($sistema->tamanho_real * 100 / $estatisticas->tamanho_real, 2);

                echo '<tr>';
                echo '<td scope="row">'.texto::codificar($sistema->nome).'</td>';
                echo '<td>'.$sistema->total.' ('.$sistema->porcentagem->total.'%)</td>';
                echo '<td>'.texto::formatar_bytes($sistema->tamanho_total).' ('.$sistema->porcentagem->tamanho_total.'%)</td>';
                echo '<td>'.texto::formatar_bytes($sistema->tamanho_real).' ('.$sistema->porcentagem->tamanho_real.'%)</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '<p><em>Tamanho dos Arquivos PHP:</em> '.texto::formatar_bytes($estatisticas->tamanho_total);
            echo ' (corresponde a um livro de '.$num_paginas.' p&aacute;ginas A4)</p>';
            echo '<p>'.$porcentagem.'% do c&oacute;digo s&atilde;o coment&aacute;rios e espa&ccedil;os: '.$situacao.'</p>';
        } else {
            echo '<h2>Estat&iacute;sticas</h2>';
            echo '<p><em>Total de Arquivos PHP:</em> '.$estatisticas->total.'</p>';
            echo '<p><em>Tamanho dos Arquivos PHP:</em> '.texto::formatar_bytes($estatisticas->tamanho_total);
            echo ' (corresponde a um livro de '.$num_paginas.' p&aacute;ginas A4)</p>';
            echo '<p><em>Tamanho dos Arquivos PHP sem coment&aacute;rios e espa&ccedil;os:</em> '.texto::formatar_bytes($estatisticas->tamanho_real).'</p>';
            echo '<p>'.$porcentagem.'% do c&oacute;digo s&atilde;o coment&aacute;rios e espa&ccedil;os: '.$situacao.'</p>';
        }

        echo '<h2>Autores</h2>';
        echo '<table class="tabela">';
        echo '<caption>Tabela de autores por c&oacute;digo gerado</caption>';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" rowspan="2">Autor</th>';
        echo '<th scope="col" colspan="2">Principal</th>';
        echo '<th scope="col" colspan="2">Secund&aacute;rio</th>';
        echo '<th scope="col" colspan="2">Total</th>';
        echo '<th scope="col" rowspan="2">Participa&ccedil;&atilde;o</th>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="col">Arquivos</th>';
        echo '<th scope="col">Tamanho</th>';
        echo '<th scope="col">Arquivos</th>';
        echo '<th scope="col">Tamanho</th>';
        echo '<th scope="col">Arquivos</th>';
        echo '<th scope="col">Tamanho</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($estatisticas->autores as $autor => $dados) {
            echo '<tr>';
            echo '<td scope="row">'.$autor.'</td>';
            echo '<td>'.$dados->quantidade.'</td>';
            echo '<td>'.texto::formatar_bytes($dados->tamanho).'</td>';
            if (isset($estatisticas->secundarios[$autor])) {
                $dados_secundario = $estatisticas->secundarios[$autor];
                echo '<td>'.$dados_secundario->quantidade.'</td>';
                echo '<td>'.texto::formatar_bytes($dados_secundario->tamanho).'</td>';
            } else {
                $dados_secundario = new stdClass();
                $dados_secundario->quantidade = 0;
                $dados_secundario->tamanho = 0;
                echo '<td>-</td>';
                echo '<td>-</td>';
            }

            $total_quantidade = $dados->quantidade + $dados_secundario->quantidade;
            $total_tamanho = $dados->tamanho + $dados_secundario->tamanho;

            echo '<td>'.($total_quantidade).'</td>';
            echo '<td>'.texto::formatar_bytes($total_tamanho).'</td>';
            echo '<td>'.round($total_tamanho * 100 / $estatisticas->tamanho_total, 2).'%</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        if (count($estatisticas->arq_invalidos)) {
            echo '<h2>Arquivos inv&aacute;lidos (Documenta&ccedil;&atilde;o incompleta)</h2>';
            echo '<ul>';
            foreach ($estatisticas->arq_invalidos as $arq) {
                echo '<li>'.$arq.'</li>';
            }
            echo '</ul>';
        }
        echo '<p>';
        link::texto($link, 'Recalcular Estatisticas');
        echo '</p>';

        $link = $CFG->site;
        link::normalizar($link, true);
        $link = link::adicionar_atributo($link, 'limpar', 1);
        echo '<p>';
        link::texto($link, 'Limpar Estat&iacute;sticas');
        echo '</p>';

    } else {
        echo '<p>';
        link::texto($link, 'Calcular Estatisticas');
        echo '</p>';
    }
}


//
//     Obtem o conteudo de um diretorio
//
function get_arquivos($dir, $dir_rel, $abertos) {
// String $dir: diretorio a ser aberto
// String $dir_rel: nome relativo do diretorio a partir da raiz
// Array[Mixed] $abertos: vetor de itens abertos
//
    global $CFG;
    $site = $CFG->site;
    link::normalizar($site, true);

    $itens = scandir($dir);
    $arquivos = $diretorios = array();
    foreach ($itens as $i) {
        if ($i == '.' || $i == '..' || $i == '.svn') { continue; }
        if (is_file($dir.$i)) {
            $arquivos[] = $i;
        } elseif (is_dir($dir.$i)) {
            $diretorios[] = $i;
        }
    }
    $count_diretorios = count($diretorios);
    $count_arquivos = count($arquivos);
    $lista = array();
    if ($count_diretorios || $count_arquivos) {
        foreach ($diretorios as $diretorio) {
            $link = link::adicionar_atributo($site, 'item', texto::base64_encode($dir_rel.$diretorio));
            if (isset($abertos[$diretorio])) {
                $link = link::texto($link, $diretorio, false, false, 'diretorio_aberto', true);
                $valor = get_arquivos($dir.$diretorio.'/', $dir_rel.$diretorio.'/', $abertos[$diretorio]);
            } else {
                $link = link::texto($link, $diretorio, false, false, 'diretorio_fechado', true);
                $valor = false;
            }
            $lista[$link] = $valor;
        }
        foreach ($arquivos as $arquivo) {
            $link = link::adicionar_atributo($site, 'item', texto::base64_encode($dir_rel.$arquivo));
            $link = link::texto($link, $arquivo, false, false, 'arquivo', true);
            if (isset($abertos[$arquivo])) {
                $valor = get_conteudo_arquivo($arquivo, $dir);
            } else {
                $valor = false;
            }
            $lista[$link] = $valor;
        }
    } else {
        $lista['Nenhum conte&uacute;do'] = false;
    }
    return $lista;
}


//
//     Obtem o conteudo do arquivo
//
function get_conteudo_arquivo($arquivo, $dir) {
// String $arquivo: nome do arquivo
// String $dir: nome do diretorio
//
    global $CFG;
    if (!preg_match('/^.*\.([A-z]+)$/', $arquivo, $match)) {
        return array('<p>Extens&atilde;o desconhecida</p>' => false);
    }

    switch (trim(strtolower($match[1]))) {
    case 'php':
        return get_cabecalho_arquivo($dir.$arquivo);

    // Textos
    case 'css':
    case 'csv':
    case 'ini':
    case 'js':
    case 'htaccess':
    case 'htm':
    case 'html':
    case 'txt':
    case 'sh':
        $conteudo = file_get_contents($dir.$arquivo);
        $conteudo = wordwrap($conteudo, 80, "\n");
        $conteudo = htmlentities($conteudo, ENT_COMPAT, $CFG->charset);
        return array('<pre class="comentario">'.$conteudo.'</pre>' => false);

    case 'rdf':
    case 'xml':
    case 'xsl':
        $conteudo = file_get_contents($dir.$arquivo);

        // Exibir identado
        if (extension_loaded('dom')) {
            $dom = new DOMDocument();
            if ($dom->loadXML($conteudo)) {
                $dom->formatOutput = true;
                $conteudo = $dom->saveXML();
            }
        } else {
            $conteudo = wordwrap($conteudo, 80, "\n");
        }
        $conteudo = texto::codificar($conteudo);
        return array('<pre class="comentario">'.$conteudo.'</pre>' => false);

    // Imagens
    case 'jpg':
    case 'png':
    case 'gif':
    case 'bmp':
        $caminho = $dir.$arquivo;
        $rel = str_replace($CFG->dirroot, '', $caminho);
        $src = $CFG->wwwroot.$rel;
        $img = '<img src="'.$src.'" alt="'.$arquivo.'" />';
        return array($img => false);

    case 'ignoredoc':
        return array('IGNOREDOC: Ignora a documenta&ccedil;&atilde;o inv&aacute;lida do diret&oacute;rio' => false);
    }
    return array('<p>Extens&atilde;o desconhecida: '.$match[1].'</p>' => false);
}


//
//     Obtem o cabecalho do arquivo
//
function get_cabecalho_arquivo($arquivo) {
// String $arquivo: nome completo do arquivo
//
    global $CFG, $modulo;

    $obj = parser_simp::get_cabecalho_arquivo($arquivo);

    if ($obj) {
        $a = base64_encode($arquivo);

        // Imprimir dados obtidos
        $dados = "<div class=\"comentario\">".
                 "<p><strong>Descri&ccedil;&atilde;o:</strong> ".texto::codificar($obj->descricao)."</p>\n".
                 "<p><strong>Vers&atilde;o:</strong> ".texto::codificar($obj->versao)."</p>\n".
                 "<p><strong>Cria&ccedil;&atilde;o:</strong> ".texto::codificar($obj->data)." &nbsp; - &nbsp;".
                 "   <strong>Modifica&ccedil;&atilde;o:</strong> ".texto::codificar($obj->modificado)."</p>\n".
                 (isset($obj->utilizacao) ? '<p><strong>Utiliza&ccedil;&atilde;o:</strong> '.texto::codificar($obj->utilizacao).'</p>' : '').
                 (isset($obj->observacao) ? '<p><strong>Observa&ccedil;&atilde;o:</strong> '.texto::codificar($obj->observacao).'</p>' : '').
                 "<p><a href=\"{$CFG->wwwmods}{$modulo}/ver_arquivo.php?a={$a}\" rel=\"blank\">Visualizar</a></p>\n".
                 "<hr />\n".
                 "<p><strong>Autor:</strong> ".texto::codificar($obj->autor)." &lt;".texto::codificar($obj->email)."&gt;</p>\n".
                 "<p><strong>&Oacute;rg&atilde;o:</strong> ".texto::codificar($obj->orgao)."</p>\n".
                 "</div>\n";
    } else {
        $dados = "<div class=\"comentario\">Erro ao ler cabe&ccedil;alho do arquivo.</div>\n";
    }
    return array($dados => false);
}


//
//     Checa se o diretorio/arquivo esta' aberto
//
function aberto($item) {
// String $item: nome do diretorio/arquivo
//
    $vetor = explode('/', $item);
    return existe($_SESSION['devel']['abrir'], $vetor);
}


//
//     Checa se um item existe na sessao recursivamente
//
function existe($sessao, $vetor) {
// Array[Mixed] $sessao: dados da sessao
// Array[String] $vetor: vetor com o nome do item a ser checado
//
    $item = array_shift($vetor);
    if (isset($sessao[$item])) {
        if (is_array($vetor) && count($vetor)) {
            return existe($sessao[$item], $vetor);
        } else {
            return true;
        }
    }
    return false;
}


//
//     Abre um diretorio/arquivo
//
function abrir($item) {
// String $item: item a ser aberto
//
    $vetor = explode('/', $item);
    $s = &$_SESSION['devel']['abrir'];
    foreach ($vetor as $nome) {
        if (!isset($s[$nome])) {
            $s[$nome] = array();
        }
        $s = &$s[$nome];
    }
}


//
//     Fecha um diretorio/arquivo
//
function fechar($item) {
// String $item: item a ser fechado
//
    $vetor = explode('/', $item);
    $php = 'unset($_SESSION["devel"]["abrir"]["'.implode('"]["', $vetor).'"]);';
    eval($php);
}


//
//     Calcula estatisticas do sistema
//
function calcular_estatisticas() {
    global $CFG;
    // Obter autores
    // Obter arquivos com autores desconhecidos
    // Obter total de arquivos
    // Obter tamanho total dos arquivos
    // Obter tamanho real (sem espacos e comentarios)
    // Obter tamanho total dos arquivos e tamanhos por sistema
    // Obter situacao esperada
    $obj = new stdClass();
    $obj->autores       = array();
    $obj->sistemas      = array();
    $obj->secundarios   = array();
    $obj->arq_invalidos = array();
    $obj->total         = 0;
    $obj->tamanho_total = 0;
    $obj->tamanho_real  = 0;

    calcular_estatisticas_dir($CFG->dirroot, $obj);

    if (count($obj->autores)) {
        array_multisort(array_keys($obj->autores), SORT_STRING, SORT_ASC, $obj->autores);
    }

    return $obj;
}


//
//     Calcula as estatisticas de um diretorio
//
function calcular_estatisticas_dir($dir, &$obj, $ignore_doc = false) {
// String $dir: nome do diretorio
// stdClass $obj: objeto com as estatisticas levantadas
// Bool $ignore_doc: ignorar documentacao errada no diretorio e sub-diretorios
//
    $arquivos = array();
    $diretorios = array();

    // Obter arquivos PHP e diretorios
    $itens = scandir($dir);
    foreach ($itens as $i) {
        if ($i == '.' || $i == '..' || $i == '.svn') { continue; }
        if (is_file($dir.$i)) {
            if (preg_match('/^.+\.php$/', $i)) {
                $arquivos[] = $i;
            } elseif ($i == '.ignoredoc') {
                $ignore_doc = true;
            }

        } elseif (is_dir($dir.$i)) {
            $diretorios[] = $i;
        }
    }
    unset($itens);

    // Percorrer arquivos PHP primeiro para economizar memoria (a funcao e' recursiva em diretorios)
    foreach ($arquivos as $arq) {
        $dados = parser_simp::get_cabecalho_arquivo($dir.$arq);
        $tam_arquivo = filesize($dir.$arq);
        $tam_codigos = strlen(php_strip_whitespace($dir.$arq));

        list($principal, $secundarios) = get_autores($dados);

        // Calcular trabalho do principal
        if (!isset($obj->autores[$principal])) {
            $obj->autores[$principal] = new stdClass();
            $obj->autores[$principal]->quantidade = 0;
            $obj->autores[$principal]->tamanho    = 0;
        }
        $obj->autores[$principal]->quantidade += 1;
        $obj->autores[$principal]->tamanho    += $tam_arquivo;

        // Calcular trabalho dos secundarios
        foreach ($secundarios as $s) {
            if (!isset($obj->secundarios[$s])) {
                $obj->secundarios[$s] = new stdClass();
                $obj->secundarios[$s]->quantidade = 0;
                $obj->secundarios[$s]->tamanho    = 0;
            }
            $obj->secundarios[$s]->quantidade += 1;
            $obj->secundarios[$s]->tamanho    += $tam_arquivo;
        }

        $obj->total += 1;
        $obj->tamanho_total += $tam_arquivo;
        $obj->tamanho_real  += $tam_codigos;

        // Obter dados do sistema
        $nome_sistema = strtoupper($dados->sistema);
        if (!isset($obj->sistemas[$nome_sistema])) {
            $obj->sistemas[$nome_sistema] = new stdClass();
            $obj->sistemas[$nome_sistema]->nome = '';
            $obj->sistemas[$nome_sistema]->total = 0;
            $obj->sistemas[$nome_sistema]->tamanho_total = 0;
            $obj->sistemas[$nome_sistema]->tamanho_real  = 0;
        }
        $obj->sistemas[$nome_sistema]->nome = $dados->sistema;
        $obj->sistemas[$nome_sistema]->total += 1;
        $obj->sistemas[$nome_sistema]->tamanho_total += $tam_arquivo;
        $obj->sistemas[$nome_sistema]->tamanho_real  += $tam_codigos;

        if ($principal == 'Desconhecido' && !$dados->ignore_doc && !$ignore_doc) {
            $obj->arq_invalidos[] = realpath($dir.$arq);
        }
    }
    unset($arquivos);

    // Percorrer os diretorios
    foreach ($diretorios as $diretorio) {
        calcular_estatisticas_dir($dir.$diretorio.'/', $obj, $ignore_doc);
    }
}


//
//     Separa o autor principal dos secundarios
//
function get_autores($dados) {
// Object $dados: dados do cabecalho de um arquivo
//
    if (strpos($dados->autor, '&&')) {
        $vt = array_map('trim', explode('&&', $dados->autor));
        $principal = array_shift($vt);
        return array($principal, $vt);
    } else {
        return array($dados->autor, array());
    }
}
