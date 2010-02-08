<?php
//
// SIMP
// Descricao: Script de Instalacao dos Grupos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.6
// Data: 05/09/2007
// Modificado: 06/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

//
//     Instala os grupos padrao
//
function instalar_grupo(&$erros) {
// Array[String] $erros: erros ocorridos
//
    $r = true;

    $g = new grupo();
    if ($g->possui_registros()) {
        return true;
    }

    $grupos = array('Administradores',
                    'Gerentes',
                    'Analistas');

    foreach ($grupos as $grupo) {
        $r = $r && inserir_grupo($grupo, $erros);
    }

    return $r;
}


//
//     Insere um novo grupo
//
function inserir_grupo($nome, &$erros) {
// String $nome: nome do grupo
// Array[String] $erros: vetor de erros
//
    $g = new grupo();
    $g->nome = $nome;
    if (!$g->salvar()) {
        $erros[] = 'Erro ao instalar grupo:';
        $erros[] = $g->get_erros();
        return false;
    }
    return true;
}
