<?php
//
// SIMP
// Descricao: Script de Instalacao dos Arquivos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.19
// Data: 10/09/2007
// Modificado: 06/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

//
//     Instala os arquivos padrao
//
function instalar_arquivo(&$erros) {
// Array[String] $erros: erros ocorridos
//
    $r = true;

    $a = new arquivo();
    if ($a->possui_registros()) {
        return true;
    }

    $arq_ini = dirname(__FILE__).'/arquivo.ini';
    if (!is_file($arq_ini)) {
        $erros[] = 'Arquivo "arquivo.ini" n&atilde;o foi encontrado';
        $r = false;
    } else {
        $ini = parse_ini_file($arq_ini, true);
        if (!$ini) {
            $erros[] = 'Erro no arquivo "arquivo.ini"';
            $r = false;
        } else {
            foreach ($ini as $modulo => $arquivos) {
                $r = $r && inserir_arquivos($modulo, $arquivos, $erros);
            }
        }
    }
    return $r;
}


//
//     Insere os arquivos de um modulo no BD
//
function inserir_arquivos($modulo, $arquivos, &$erros) {
// String $modulo: nome do modulo
// Array[String => String] $arquivos: vetor com nomes e descricoes dos arquivos
// Array[String] $erros: vetor de erros
//
    $r = true;
    if ($modulo == 'simp') {
        $modulo = '';
    }
    foreach ($arquivos as $arquivo => $descricao) {
        $r = $r && inserir_arquivo($modulo, $arquivo, $descricao, $erros);
    }
    return $r;
}


//
//     Insere um arquivo no BD
//
function inserir_arquivo($modulo, $arquivo, $descricao, &$erros) {
// String $modulo: nome do modulo
// String $arquivo: nome do arquivo
// String $descricao: descricao do arquivo
// Array[String] $erros: erros ocorridos
//
    $a = new arquivo();
    $a->set_id_form($a->id_formulario_inserir());
    $a->modulo    = $modulo;
    $a->arquivo   = $arquivo;
    $a->descricao = $descricao;

    $dados = $a->get_dados(true);
    $r = $a->validacao_final($dados) && $a->salvar();

    if (!$r) {
        $erros[] = "Erro no arquivo {$arquivo} / m&oacute;dulo {$modulo}:";
        $erros[] = $a->get_erros();
    }
    return $r;
}
