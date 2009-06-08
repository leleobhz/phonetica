<?php
//
// SIMP
// Descricao: Classe de autenticacao de exemplo
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 13/08/2008
// Modificado: 04/02/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class exemplo extends autenticacao_base {


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'Exemplo';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais TODO sao formadas por:
    //     String login: login do usuario
    //     String senha: senha do usuario
    //
    public function definir_parametros() {
        $atributo = new atributo('login', 'Login', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('senha', 'Senha', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        //TODO: autenticar o usuario, preencher os erros (se necessario) e retornar um bool
    }
}
