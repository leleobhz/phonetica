<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Arquivo para copiar o texto para outro projeto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 08/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../../../config.php');
require_once($CFG->dirroot.'sessao.php');

$texto_analise = util::get_entidade('texto_analise');

/// Dados da Pagina
$dados_pagina = new stdClass();
$dados_pagina->titulo = 'Copiar texto "'.$texto_analise->get_nome().'"';
$dados_pagina->submodulo = 'projeto';

/// Dados Gerais
$dados_gerais = new stdClass();
$dados_gerais->texto_analise = $texto_analise;
$dados_gerais->ajuda = <<<AJUDA
  <p>Este formul&aacute;rio &eacute; respons&aacute;vel por copiar um
  texto de um projeto para outro.</p>
AJUDA;

modulo::pagina('logica_copiar_texto', $dados_pagina, $dados_gerais);
exit(0);


/// Funcoes


//
//     Importa os candidatos a alunos
//
function logica_copiar_texto($pagina, $dados, $arquivos, $dados_gerais) {
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
        if (!copiar_texto($dados, $dados_gerais, $erros, $avisos)) {
            mensagem::erro($erros);
        } else {
            mensagem::aviso($avisos);
        }
    }
    imprimir_formulario($dados, $dados_gerais);
}


//
//    Imprime o formulario para copiar texto
//
function imprimir_formulario($dados, $dados_gerais) {
// Object $dados: dados submetidos
// Object $dados_gerais: dados gerais
//
    global $CFG;

    $vt_projetos = objeto::get_objeto('projeto')->vetor_associativo_hierarquico('analista', 'usuario:nome');

    $padrao = array('cod_projeto' => 0);
    $dados = formulario::montar_dados($padrao, $dados);

    $form = new formulario($CFG->site, 'form_copia');
    $form->campo_informacao('<strong>Texto a ser copiado:</strong> '.$dados_gerais->texto_analise->get_nome().' (Projeto '.$dados_gerais->texto_analise->projeto->get_nome().')');
    $form->campo_aviso('Selecione o projeto para onde deseja copiar o texto indicado.');
    $form->campo_select('cod_projeto', 'cod_projeto', $vt_projetos, $dados->cod_projeto, 'Projeto');
    $form->campo_submit('enviar', 'enviar', 'Copiar');
    $form->imprimir();
}


//
//     Realiza a copia do texto de um projeto para outro
//
function copiar_texto($dados, $dados_gerais, &$erros, &$avisos) {
// Object $dados: dados submetidos
// Object $dados_gerais: dados gerais
// Array[String] $erros: vetor de erros ocorridos
// Array[String] $avisos: vetor de avisos ocorridos
//
    $erros = $avisos = array();
    if (!isset($dados->cod_projeto) || !$dados->cod_projeto) {
        $erros[] = 'Faltou informar o Projeto';
        return false;
    } elseif ($dados->cod_projeto == $dados_gerais->texto_analise->projeto->get_valor_chave()) {
        $erros[] = 'O projeto de destino &eacute; o mesmo de origem';
        return false;
    }
    $projeto_destino = new projeto('', $dados->cod_projeto);
    if (!$projeto_destino->existe()) {
        $erros[] = 'O projeto de destino n&atilde;o existe';
        return false;
    }

    if ($projeto_destino->copiar_texto($dados_gerais->texto_analise)) {
        $avisos = $projeto_destino->get_avisos();
        return true;
    } else {
        $erros = $projeto_destino->get_erros();
        return false;
    }
}
