<?php
//
// SIMP
// Descricao: Script de Instalacao dos Usuarios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 05/09/2007
// Modificado: 06/07/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

//
//     Instala o usuario padrao
//
function instalar_usuario(&$erros) {
// Array[String] $erros: erros ocorridos
//
    $r = true;

    $u = new usuario('login', 'admin');
    if ($u->existe()) {
        return true;
    }
    $u->limpar_objeto();

    $u->nome  = 'Administrador';
    $u->login = 'admin';
    $u->senha = 'admin';
    $u->email = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : 'root@localhost';
    if (!$u->salvar()) {
        $r = false;
        $erros[] = $u->get_erros();
    } else {
        $grupo = new stdClass();
        $grupo->cod_grupo = COD_ADMIN;
        if (!$u->inserir_elemento_rel_un('grupos', $grupo)) {
            $r = false;
            $erros[] = $u->get_erros();
        }
    }

    $u->limpar_objeto();
    $u->nome  = 'Gerente';
    $u->login = 'gerente';
    $u->senha = 'gerente';
    $u->email = 'gerente@tecnolivre.com.br';
    if (!$u->salvar()) {
        $r = false;
        $erros[] = $u->get_erros();
    } else {
        $grupo = new stdClass();
        $grupo->cod_grupo = COD_GERENTES;
        if (!$u->inserir_elemento_rel_un('grupos', $grupo)) {
            $erros[] = $u->get_erros();
        }
    }

    return $r;
}


//
//     Retorna um vetor de classes dependentes
//
function dependencias_usuario() {
    return array('grupo');
}
