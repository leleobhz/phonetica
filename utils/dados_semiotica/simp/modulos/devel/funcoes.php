<?php
//
// SIMP
// Descricao: Script que lista as funcoes de cada arquivo do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.13
// Data: 31/07/2007
// Modificado: 01/06/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');

/// Dados do Formulario
$action = $CFG->site;
link::normalizar($action, true);
$dados  = formulario::get_dados();


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Lista de Fun&ccedil;&otilde;es';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Fun&ccedil;&otilde;es';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_form($action, $dados);
imprimir_funcoes($dados);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime o formulario de busca por uma funcao recursivamente
//
function imprimir_form($action, $dados) {
// String $action: endereco de destino dos dados
// Object $dados: dados submetidos
//
    $padrao = array('nome'      => '',
                    'descricao' => '');
    $dados = formulario::montar_dados($padrao, $dados);

    $form = new formulario($action, 'busca_funcao');
    $form->titulo_formulario('Busca por Fun&ccedil;&otilde;es/M&eacute;todos');
    $form->campo_text('nome', 'nome', $dados->nome, 100, 30, 'Nome');
    $form->campo_text('descricao', 'descricao', $dados->descricao, 300, 30, 'Descri&ccedil;&atilde;o');
    $form->campo_submit('enviar', 'enviar', 'Enviar');
    $form->imprimir();
}


//
//     Imprime a lista de funcoes
//
function imprimir_funcoes($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    if (!isset($_SESSION['devel']['abrir'])) {
        $_SESSION['devel']['abrir'] = array();
    }
    if (!isset($_SESSION['devel']['estatisticas_funcoes'])) {
        $_SESSION['devel']['estatisticas_funcoes'] = false;
    }
    $estatisticas = unserialize($_SESSION['devel']['estatisticas_funcoes']);
    if (isset($_GET['item'])) {
        $item = base64_decode($_GET['item']);
        if (aberto($item)) {
            fechar($item);
        } else {
            abrir($item);
        }
    } elseif (isset($_GET['calcular_estatisticas'])) {
        $estatisticas = calcular_estatisticas();
        $_SESSION['devel']['estatisticas_funcoes'] = serialize($estatisticas);
    } elseif (isset($_GET['limpar'])) {
        $_SESSION['devel']['estatisticas_funcoes'] = false;
        $estatisticas = false;
    }

    // Se submeteu os dados: abrir os arquivos que casam com o pedido
    if ($dados) {
        buscar_funcao($dados);
    }

    $arquivos = get_arquivos($CFG->dirroot, '', $_SESSION['devel']['abrir']);
    lista::hierarquica($arquivos);

    $link = $CFG->site;
    link::normalizar($link, true);
    $link = link::adicionar_atributo($link, 'calcular_estatisticas', 1);
    if ($estatisticas) {
        echo '<h2>Estat&iacute;sticas</h2>';
        echo '<p><strong>Total de Fun&ccedil;&otilde;es:</strong> '.$estatisticas->total_funcoes.' fun&ccedil;&otilde;es</p>';
        if (count($estatisticas->funcoes_invalidas)) {
            echo '<h2>Fun&ccedil;&otilde;es com problemas de documenta&ccedil;&atilde;o</h2>';
            echo '<ul>';
            foreach ($estatisticas->funcoes_invalidas as $arq => $funcoes) {
                echo '<li>';
                echo '<strong>'.$arq.'</strong>';
                echo '<ul>';
                foreach ($funcoes as $funcao => $erro) {
                    echo '<li><em>'.$funcao.':</em> '.texto::codificar($erro).'</li>';
                }
                echo '</ul>';
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '<p>';
        link::texto($link, 'Recalcular Estat&iacute;sticas', '', '', '', false, true, false);
        echo '</p>';

        $link = $CFG->site;
        link::normalizar($link, true);
        $link = link::adicionar_atributo($link, 'limpar', 1);
        echo '<p>';
        link::texto($link, 'Limpar Estat&iacute;sticas', '', '', '', false, true, false);
        echo ' (para deixar a p&aacute;gina mais r&aacute;pida)</p>';

    } else {
        echo '<p>';
        link::texto($link, 'Calcular Estat&iacute;sticas', '', '', '', false, true, false);
        echo '</p>';
    }

    //util::dump($_SESSION['devel']['abrir']);
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
        if ($i[0] == '.') { continue; }
        if (is_file($dir.$i) && preg_match('/^.+\.php$/', $i)) {
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
                $link = link::texto($link, $diretorio, false, false, 'diretorio_aberto', true, false, false);
                $valor = get_arquivos($dir.$diretorio.'/', $dir_rel.$diretorio.'/', $abertos[$diretorio]);
            } else {
                $link = link::texto($link, $diretorio, false, false, 'diretorio_fechado', true, false, false);
                $valor = false;
            }
            $lista[$link] = $valor;
        }
        foreach ($arquivos as $arquivo) {
            $link = link::adicionar_atributo($site, 'item', texto::base64_encode($dir_rel.$arquivo));
            $link = link::texto($link, $arquivo, false, false, 'arquivo', true, false, false);
            if (isset($abertos[$arquivo])) {
                $valor = get_funcoes_arquivo($dir.$arquivo, $dir_rel.$arquivo);
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
//     Obtem as funcoes do arquivo
//
function get_funcoes_arquivo($arquivo, $arq_rel) {
// String $arquivo: nome completo do arquivo
// String $arq_rel: caminho relativo ao arquivo
//
    global $CFG;
    $site = $CFG->site;
    link::normalizar($site, true);
    $tokens = token_get_all(file_get_contents($arquivo));
    $funcoes = array();
    foreach ($tokens as $i => $token) {
        if ($token[0] == T_FUNCTION) {
            $dados = get_dados_funcao($tokens, $i, $arq_rel);
            $funcoes[$dados->nome] = $dados;
        }
    }
    $lista = array();
    if (count($funcoes)) {
        array_multisort(array_keys($funcoes), SORT_ASC, SORT_STRING, $funcoes);
        foreach ($funcoes as $funcao => $dados) {
            $link = link::adicionar_atributo($site, 'item', texto::base64_encode($arq_rel.'/'.$funcao));
            $link = link::texto($link, $funcao, '', '', '', true, false, false);
            if (aberto($arq_rel.'/'.$funcao)) {
                $valor = '<div>';
                if (!isset($dados->erro)) {
                    $valor .= '<p><strong>Descri&ccedil;&atilde;o:</strong> '.nl2br($dados->descricao).'</p>';
                    if (!empty($dados->flags)) {
                        $valor .= '<p><strong>Flags:</strong> '.$dados->flags.'</p>';
                    }
                    if (count($dados->parametros)) {
                        $valor .= '<p><strong>Par&acirc;metros:</strong></p>';
                        $valor .= '<ol>';
                        foreach ($dados->parametros as $p) {
                            $valor .= '<li><span>'.$p->tipo.'</span> '.($p->referencia ? '&amp;' : '').'<strong>'.$p->nome.'</strong>: '.$p->descricao;
                            $valor .= isset($p->padrao) ? ' (Padr&atilde;o: '.$p->padrao.')' : '';
                            $valor .= '</li>';
                        }
                        $valor .= '</ol>';
                    } else {
                        $valor .= '<p>[Nenhum Par&acirc;metro]</p>';
                    }
                } else {
                    $valor .= '<p>Erro: '.$dados->erro.'</p>';
                }
                $valor .= '</div>';
                $valor = array($valor => false);
            } else {
                $valor = false;
            }
            $lista[$link] = $valor;
        }
    } else {
        $lista['Nenhuma fun&ccedil;&atilde;o'] = false;
    }
    return $lista;
}


//
//     Obtem os dados de uma funcao
//
function get_dados_funcao($tokens, $i, $arq_rel) {
// Array[Mixed] $tokens: vetor de tokens
// Int $i: posicao onde foi encontrada a funcao
// String $arq_rel: caminho relativo ao arquivo
//
    $obj = new stdClass();
    $obj->nome = '';
    $obj->flags = '';
    $obj->descricao = '';
    $obj->parametros = array();

    $total = count($tokens);

    // Obter nome
    if ($tokens[$i + 2][0] == '&') {
        $obj->nome = '&amp;'.$tokens[$i + 3][1];
    } else {
        $obj->nome = $tokens[$i + 2][1];
    }

    // So' obter os demais dados se a funcao esta' aberta
    if (!aberto($arq_rel.'/'.$obj->nome)) {
        return $obj;
    }

    // Obter flags
    for ($j = $i - 1; $tokens[$j][0] != T_COMMENT && $j >= 0; $j--) {
        if (isset($tokens[$j][1])) {
            $obj->flags = $tokens[$j][1].$obj->flags;
        } else {
            $obj->flags = $tokens[$j][0].$obj->flags;
        }
    }
    $obj->flags = trim($obj->flags);
    $j--;

    // Obter descricao
    do {
        if (isset($tokens[$j][1])) {
            $obj->descricao = substr($tokens[$j][1], 2).$obj->descricao;
        } else {
            $obj->descricao = $tokens[$j][0].$obj->descricao;
        }
        $j--;
    } while ($j >= 0 && (trim($tokens[$j][1]) != '//') && ($tokens[$j][0] == T_COMMENT || $tokens[$j][0] == T_WHITESPACE));
    if (!empty($obj->descricao)) {
        $obj->descricao = texto::codificar(trim($obj->descricao));
    } else {
        $obj->erro = 'Fun&ccedil;&atilde;o sem descri&ccedil;&atilde;o';
        return $obj;
    }

    $j = $i + 1;
    while ($tokens[$j][0] != '(') { $j++; }
    $j++;

    // Obter parametros
    $aux = '';
    $nivel = 0;
    while (($tokens[$j][0] != ')' || $nivel != 0) && $j < $total) {
        if ($tokens[$j][0] == ',') {
            $obj_param = new stdClass();
            $obj_param->tipo = '';
            $obj_param->descricao = '';
            $obj_param->referencia = false;
            $pos = strpos($aux, '=');
            if ($pos !== false) {
                $obj_param->nome   = trim(substr($aux, 0, $pos));
                $obj_param->padrao = trim(substr($aux, $pos + 1));
            } else {
                $obj_param->nome = trim($aux);
            }
            if ($obj_param->nome[0] == '&') {
                $obj_param->nome = substr($obj_param->nome, 1);
                $obj_param->referencia = true;
            }
            $obj_param->nome = texto::codificar($obj_param->nome);
            $obj->parametros[$obj_param->nome] = $obj_param;
            $aux = '';
        } else {
            if ($tokens[$j][0] == '(') {
                $nivel++;
            } elseif ($tokens[$j][0] == ')') {
                $nivel--;
            }
            $aux .= isset($tokens[$j][1]) ? $tokens[$j][1] : $tokens[$j][0];
        }
        $j++;
    }
    if (!empty($aux)) {
        $obj_param = new stdClass();
        $obj_param->tipo = '';
        $obj_param->descricao = '';
        $pos = strpos($aux, '=');
        if ($pos !== false) {
            $obj_param->nome = trim(substr($aux, 0, $pos));
            $obj_param->padrao = trim(substr($aux, $pos + 1));
        } else {
            $obj_param->nome = trim($aux);
        }
        if ($obj_param->nome[0] == '&') {
            $obj_param->nome = substr($obj_param->nome, 1);
            $obj_param->referencia = true;
        }
        $obj_param->nome = texto::codificar($obj_param->nome);
        $obj->parametros[$obj_param->nome] = $obj_param;
    }

    if (count($obj->parametros)) {
        $j++;
        while (($tokens[$j][0] == '{' || $tokens[$j][0] == ';' || $tokens[$j][0] == T_WHITESPACE) && $j < $total) { $j++; }
        do {
            switch ($tokens[$j][0]) {
            case T_COMMENT:
                $comentario = substr(trim($tokens[$j][1]), 3);
                $pos = strpos($comentario, ':');
                if ($pos === false) {
                    $obj->erro = 'Faltou o dois-pontos no par&acirc;metro: "'.$comentario.'"';
                    return $obj;
                }
                $campo = substr($comentario, 0, $pos);
                $descricao = trim(substr($comentario, $pos + 1));
                $pos = strrpos($campo, ' ');
                if ($pos === false) {
                    $obj->erro = 'Faltou espa&ccedil;o separando o tipo do nome no par&acirc;metro: "'.$comentario.'"';
                    return $obj;
                }
                $tipo = trim(substr($campo, 0, $pos));
                $parametro = trim(substr($campo, $pos + 1));
                if (!isset($obj->parametros[$parametro])) {
                    $obj->erro = 'N&atilde;o existe o par&acirc;metro "'.$parametro.'" na fun&ccedil;&atilde;o';
                    return $obj;
                }
                $obj->parametros[$parametro]->tipo = $tipo;
                if (!parser_simp::validar_tipo($tipo)) {
                    $obj->erro = "Tipo desconhecido \"{$p->tipo}\" para o par&acirc;metro {$p->nome}";
                    return $obj;
                }
                $obj->parametros[$parametro]->descricao = $descricao;
                if (empty($descricao)) {
                    $obj->erro = "O par&acirc;metro {$p->nome} n&atilde;o tem descri&ccedil;&atilde;o";
                    return $obj;
                }
                break;
            case T_WHITESPACE:
                break;
            default:
                $obj->erro = 'Token inv&aacute;lido na checagem dos par&acirc;metros: '.token_name($tokens[$j][0]).' ('.$tokens[$j][0].')';
                return $obj;
            }
            $j++;
        } while (trim($tokens[$j][1]) != '//' && $j < $total);
    }
    return $obj;
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
//     Calcula as estatisticas de funcoes
//
function calcular_estatisticas() {
    global $CFG;
    $obj = new stdClass();
    $obj->total_funcoes = 0;
    $obj->funcoes_invalidas = array();
    calcular_estatisticas_dir($CFG->dirroot, $obj);
    return $obj;
}


//
//     Calcula estatisticas de funcoes no sistema
//
function calcular_estatisticas_dir($dir, &$obj) {
// String $dir: diretorio a ser analisado
// stdClass $obj: objeto que guarda as estatisticas
//
    $itens = scandir($dir);
    foreach ($itens as $i) {
        if ($i[0] == '.') { continue; }
        if (is_file($dir.$i) && preg_match('/^.+\.php$/', $i)) {
            contar_funcoes_arquivo($dir.$i, $obj);
        } elseif (is_dir($dir.$i)) {
            calcular_estatisticas_dir($dir.$i.'/', $obj);
        }
    }
}


//
//     Conta o numero de funcoes no arquivo
//
function contar_funcoes_arquivo($arq, &$obj) {
// String $arq: nome completo do arquivo
// stdClass $obj: objeto que guarda as estatisticas
//
    $funcoes = parser_simp::get_funcoes_arquivo($arq);
    foreach ($funcoes as $f) {
        $obj->total_funcoes++;
        if (isset($f->erro)) {
            $obj->funcoes_invalidas[$arq][$f->nome] = $f->erro;
        }
    }
}


//
//     Busca uma funcao e abre os arquivos que casam com o padrao
//
function buscar_funcao($dados) {
// Object $dados: dados submetidos
//
    global $CFG;

    // Checar se informou o nome ou a descricao da funcao
    if (empty($dados->nome) && empty($dados->descricao)) {
        mensagem::erro('Preencha pelo menos um dos campos do formul&aacute;rio');
        return;
    }

    // Montar expressao regular de busca pela descricao
    if (!empty($dados->descricao)) {
        $dados->exp = '/'.implode('(.*)', array_map('preg_quote', explode(' ', $dados->descricao))).'/i';
    }

    // Fechar todos os diretorios e arquivos abertos
    $_SESSION['devel']['abrir'] = array();

    // Percorrer todo o sistema em busca da funcao/metodo
    $total = 0;
    buscar_funcao_recursivo($CFG->dirroot, $dados, $total);
    echo '<p>'.$total.' resultado(s) encontrado(s).</p>';
}


//
//     Busca uma funcao em um diretorio recursivamente e abre os arquivos que casam com o padrao
//
function buscar_funcao_recursivo($diretorio_busca, &$dados, &$total) {
// String $diretorio_busca: diretorio a ser percorrido recursivamente
// Object $dados: dados submetidos
// Int $total: numero de resultados obtidos
//
    global $CFG;
    $itens = scandir($diretorio_busca);
    foreach ($itens as $item) {
        if ($item[0] == '.') { continue; }
        if (is_file($diretorio_busca.$item)) {
            if (preg_match('/\.php$/', $item)) {
                $arquivos[] = $item;
            }
        } elseif (is_dir($diretorio_busca.$item)) {
            $diretorios[] = $item;
        }
    }

    foreach ($arquivos as $arquivo) {
        $funcoes = parser_simp::get_funcoes_arquivo($diretorio_busca.$arquivo);
        foreach ($funcoes as $funcao) {
            $achou = false;

            // Buscar pelo nome
            if (!empty($dados->nome)) {
                if (strcmp($dados->nome, $funcao->nome) == 0) {
                    $achou = true;
                    $total++;
                    $abrir = str_replace($CFG->dirroot, '', $diretorio_busca.$arquivo.'/'.$funcao->nome);
                    abrir($abrir);
                }
            }

            // Buscar pela descricao
            if (!$achou && isset($dados->exp)) {
                if (preg_match($dados->exp, $funcao->descricao)) {
                    $achou = true;
                    $total++;
                    $abrir = str_replace($CFG->dirroot, '', $diretorio_busca.$arquivo.'/'.$funcao->nome);
                    abrir($abrir);
                }
            }
        }
    }
    unset($arquivos, $funcoes);

    foreach ($diretorios as $diretorio) {
        buscar_funcao_recursivo($diretorio_busca.$diretorio.'/', $dados, $total);
    }
}
