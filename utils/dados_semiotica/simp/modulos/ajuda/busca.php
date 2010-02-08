<?php
//
// SIMP
// Descricao: Busca entre as ajudas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.4
// Data: 07/01/2008
// Modificado: 25/11/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados do formulario
$modulo = util::get_modulo(__FILE__);
$action = $CFG->site;
$dados  = formulario::get_dados();


/// Dados da Pagina
$titulo = 'Busca em Ajuda';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_formulario($dados, $action);
if ($dados) {
    imprimir_resultado_busca($dados);
}
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime o formulario de busca
//
function imprimir_formulario($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//

    $campos = array('busca' => '');
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'busca_ajuda');
    $form->campo_informacao('Preencha as palavras-chave da busca separadas por espa&ccedil;o');
    $form->campo_text('busca', 'busca', $dados->busca, 40, 30, 'Palavras-chave');
    $form->campo_submit('enviar', 'enviar', 'Consultar');
    $form->imprimir();
}


//
//     Imprime o resultado da busca por ajudas
//
function imprimir_resultado_busca($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    $palavras = explode(' ', $dados->busca);

    $condicoes = condicao_sql::montar('modulo', '<>', '');
    $arquivos = objeto::get_objeto('arquivo')->consultar_varios($condicoes, true);

    // Para cada arquivo do sistema
    foreach ($arquivos as $a) {

        // Obter o texto da ajuda
        $ajuda = get_ajuda($CFG->dirmods.$a->nome_completo);

        if (!$ajuda) { continue; }

        // Para cada palavra-chave, checar se existe em $buf
        $resultados = 0;
        foreach ($palavras as $p) {
            $p = texto::decodificar($p);
            $pos1 = stripos($ajuda, $p);
            if ($pos1 !== false) {
                $pos2 = $pos1 + strlen($p);

                $ajuda = substr($ajuda, 0, $pos1).
                         '<strong class="destaque">'.$p.'</strong>'.
                         substr($ajuda, $pos2);

                $resultados++;

            // Se nao achou, cancelar
            } else {
                break;
            }
        }
        if ($resultados) {
            $link = $a->nome_completo;
            if ($link[0] == '/') { $link = substr($link, 1); }
            $link = $CFG->wwwmods.$link;
            echo '<h2>'.$a->descricao.' ('.$a->nome_completo.")</h2>\n".
                 "<p>Link: <a href=\"{$link}\">{$link}</a></p>\n".
                 str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $ajuda);
        }
    }
}


//
//     Obtem o texto da ajuda de um arquivo
//
function get_ajuda($arquivo) {
// String $arquivo: caminho absoluto ao arquivo
//
    $ajuda = '';

    // Abrir o arquivo para leitura
    $arq = fopen($arquivo, 'r');
    if ($arq) {
        while (!feof($arq)) {
            $linha = fgets($arq, 10000);

            // Se encontrou um bloco de ajuda
            if (stripos($linha, '<'.'<'.'<'.'AJUDA') !== false) {

                // Armazenar a ajuda na variavel
                $fim = false;
                do {
                    $linha = fgets($arq, 10000);
                    if (stripos($linha, 'AJUDA;') !== false) {
                        $fim = true;
                    } else {
                        $ajuda .= $linha;
                    }
                } while (!$fim && !feof($arq));
            }
        }
        fclose($arq);
    }
    return texto::decodificar($ajuda);
}
