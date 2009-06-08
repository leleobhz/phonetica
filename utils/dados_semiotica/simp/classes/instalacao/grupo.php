<?php
//
// SIMP
// Descricao: Script de Instalacao dos Grupos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 05/09/2007
// Modificado: 04/06/2009
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
    if ($g->quantidade_registros()) {
        return true;
    }

    $r = $r && inserir_grupo('Administradores', $erros);
    $r = $r && inserir_grupo('Gerentes', $erros);
    $r = $r && inserir_grupo('Analistas', $erros);

    return $r;
}


//
//     Insere um novo grupo
//
function inserir_grupo($nome) {
// String $nome: nome do grupo
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
