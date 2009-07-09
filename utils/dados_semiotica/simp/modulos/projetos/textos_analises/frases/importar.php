<?php
//
// ANALISES - Cadastro de Analises sobre Frases
// Descricao: Arquivo para importar Frases
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 09/07/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$ANALISTA = analista::get_analista();

/// Obter Texto
$texto_analise = modulo::get_entidade_session('texto_analise');

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = 'Importar Frases para "'.$texto_analise->get_nome().'"';
$dados_pagina->submodulo = 'texto_analise';

/// Dados Gerais
$dados_gerais = new stdClass();
$dados_gerais->texto_analise = $texto_analise;
$dados_gerais->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio permite a importa&ccedil;&atilde;o de frases para determinado
     texto de um projeto.</p>
  <p>O arquivo submetido deve estar no formato TXT (texto puro) e codificado com
     {$CFG->charset}.</p>
  <p>Cada linha do arquivo deve representar uma frase. A disposi&ccedil;&atilde;o das frases
     no arquivo define a ordem de inser&ccedil;&atilde;o das mesmas no sistema.</p>
AJUDA;

modulo::pagina('logica_importar_frases', $dados_pagina, $dados_gerais);
exit(0);


/// Funcoes


//
//     Realiza a logica de importacao de frases
//
function logica_importar_frases($pagina, $dados, $arquivos, $dados_gerais) {
// pagina $pagina: elemento que gera a pagina
// Object $dados: dados submetidos
// Object $arquivos: arquivos submetidos
// Object $dados_gerais: dados gerais
//
    global $CFG;
    if (isset($dados_gerais->ajuda)) {
        mensagem::comentario($CFG->site, $dados_gerais->ajuda);
    }
    if (isset($dados->enviar)) {
        if (!importar_frases($dados, $arquivos, $dados_gerais, $erros, $avisos)) {
            mensagem::erro($erros);
        } else {
            mensagem::aviso($avisos);
        }
    }
    imprimir_formulario($dados);
}


//
//     Imprime o formulario de submissao de arquivos
//
function imprimir_formulario($dados) {
// Object $dados: dados submetidos
//     
    global $CFG;
    $vt_tipos_arquivo = array();
    $vt_tipos_arquivo['txt'] = 'TXT (texto puro)';

    simp_autoload('compactacao');
    $vt_compactacao = array('-' => 'Nenhum');
    if (extension_loaded('zip') || is_executable(COMPACTACAO_BIN_UNZIP)) {
        $vt_compactacao['zip'] = 'ZIP (.zip)';
    }
    if (function_exists('gzdecode')) {
        $vt_compactacao['gz'] = 'GZip (.gz)';
    }

    $padrao = array('tipo_arquivo' => 'txt',
                    'compactacao'  => '-');

    $dados = formulario::montar_dados($padrao, $dados);

    $form = new formulario($CFG->site, 'form_importacao');
    $form->campo_select('tipo_arquivo', 'tipo_arquivo', $vt_tipos_arquivo, $dados->tipo_arquivo, 'Formato');
    $form->campo_select('compactacao', 'compactacao', $vt_compactacao, $dados->compactacao, 'Compacta&ccedil;&atilde;o');
    $form->campo_file('arquivo', 'arquivo', 'Arquivo');
    $form->campo_submit('enviar', 'enviar', 'Enviar');
    $form->imprimir();
}


//
//     Importa as frases para o BD em uma transacao unica
//
function importar_frases($dados, $arquivos, $dados_gerais, &$erros, &$avisos) {
// Object $dados: dados submetidos
// Object $arquivos: arquivos submetidos
// Object $dados_gerais: dados gerais
// Array[String] $erros: vetor de erros
// Array[String] $avisos: vetor de avisos
//
    global $CFG;
    $r = true;
    $avisos = $erros = array();

    if ($arquivos->arquivo->error) {
        $erros[] = util::erro_upload($arquivos->arquivo->error);
        return false;
    }
    $md5_file = md5_file($arquivos->arquivo->tmp_name);
    $tamanho  = filesize($arquivos->arquivo->tmp_name);

    $avisos[] = 'MD5 do arquivo enviado: '.$md5_file;
    $avisos[] = 'Tamanho do arquivo enviado: '.texto::formatar_bytes($tamanho).' ('.$tamanho.' bytes)';

    // Descompactar
    switch ($dados->compactacao) {
    case '-':
        $arquivo = $arquivos->arquivo->tmp_name;
        break;
    case 'gzip':
        $conteudo = gzdecode($arquivos->arquivo->tmp_name);
        $arquivo = tempnam($CFG->dirarquivos, 'importacao');
        file_put_contents($arquivo, $conteudo);
        unset($conteudo);
        break;
    case 'zip':
        $conteudo_zipado = file_get_contents($arquivos->arquivo->tmp_name);
        $vt_arquivos = compactacao::descompactar($conteudo_zipado);
        unset($conteudo_zipado);

        $num_arquivos = count($vt_arquivos);
        if ($num_arquivos == 0) {
            $erros[] = 'Nenhum arquivo dentro do pacote ZIP';
            $r = false;
        } elseif ($num_arquivos > 1) {
            $erros[] = 'S&oacute; &eacute; permitido um &uacute;nico arquivo no pacote ZIP';
            $r = false;
        } else {
            $conteudo = array_pop($vt_arquivos);
            $arquivo = tempnam($CFG->dirarquivos, 'importacao');
            file_put_contents($arquivo, $conteudo);
            unset($conteudo, $vt_arquivos);
        }
        break;
    default:
        $erros[] = 'Tipo de compacta&ccedil;&atilde;o desconhecido: "'.texto::codificar($dados->compactacao).$
        $r = false;
        break;
    }

    if ($r) {
        $dados_gerais->arquivo_submetido = basename($arquivos->arquivo->name);

        // Importar de acordo com o tipo de arquivo
        switch ($dados->tipo_arquivo) {
        case 'txt':
            $r = importar_frases_txt($dados, $arquivo, $dados_gerais, $erros, $avisos);
            break;
        default:
            $erros[] = 'Tipo inv&aacute;lido de arquivo: '.texto::codificar($dados->tipo_arquivo);
            $r = false;
        }
    }
    unlink($arquivo);
    return $r;
}


//
//     Realiza a importacao por arquivos TXT
//
function importar_frases_txt($dados, $arquivo, $dados_gerais, &$erros, &$avisos) {
// Object $dados: dados submetidos
// String $arquivo: caminho para o arquivo submetido
// Object $dados_gerais: dados gerais
// Array[String] $erros: vetor de erros
// Array[String] $avisos: vetor de avisos
//
    global $USUARIO;

    // Calcular tempo
    $tempo = time();

    $f = fopen($arquivo, 'r');
    if (!$f) {
        $erros[] = 'Erro ao abrir arquivo submetido';
        return false;
    }

    $r = objeto::inicio_transacao(DRIVER_BASE_SERIALIZABLE);
    $dados_gerais->texto_analise->arquivo = basename($dados_gerais->arquivo_submetido);
    if (!$dados_gerais->texto_analise->salvar()) {
        $erros[] = 'Erro ao salvar texto';
        $erros[] = $dados_gerais->texto_analise->get_erros();
        $r = false;
    } else {
        $linha = $importados = 0;
        while (!feof($f)) {
            $linha++;
            $linha = fgets($f, 1024);
            $linha = trim($linha);
            if ($linha) {
                $frase = new frase();
                $frase->conteudo = $linha;
                $frase->cod_texto_analise = $dados_gerais->texto_analise->get_valor_chave();
                if (!$frase->salvar()) {
                    $r = false;
                    $erros[] = 'Erro na linha '.$linha.' ("'.texto::codificar(texto::substr($linha, 0, 30)).'...")';
                    $erros[] = $frase->get_erros();
                } else {
                    $importados++;
                }
            }
        }
    }
    fclose($f);
    $r = objeto::fim_transacao(!$r) && $r;

    $tempo = time() - $tempo;

    if ($r) {
        $avisos[] = "Frases importadas com sucesso ({$importados} no total em {$tempo} segundos)";
    } else {
        $erros[] = 'Alguma opera&ccedil;&atilde;o falhou e todo processo foi cancelado';
    }

    return $r;
}
