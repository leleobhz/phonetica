<?php
//
// SIMP
// Descricao: Classe base para as classes de autenticacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 12/08/2008
// Modificado: 02/06/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class autenticacao_base {
    protected $parametros;      // Array[atributo] Lista de atributos
    protected $credenciais;     // Array[String => Mixed] Dados para autenticacao
    protected $dados_usuario;   // Array[String => Mixed] Dados do usuario


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    abstract public function get_nome();


    //
    //     Metodo que define a lista de parametros para autenticacao
    //
    abstract public function definir_parametros();


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    abstract public function autenticar_usuario(&$erros);
    // Array[String] $erros: vetor de erros ocorridos
    //


    //
    //     Obtem a lista de parametros
    //
    final public function get_parametros() {
        return $this->parametros;
    }


    //
    //     Retorna dados do usuario autenticado, caso seja possivel
    //
    final public function get_dados_usuario() {
        return $this->dados_usuario;
    }


    //
    //     Metodo que define os parametros de autenticacao atraves de um vetor associativo
    //
    final public function set_credenciais($credenciais, &$erros) {
    // Array[String => Mixed] $credenciais: dados para autenticacao
    // Array[String] $erros: erros ocorridos
    //
        $v = validacao::get_instancia();
        $this->credenciais = array();
        foreach ($this->get_parametros() as $definicao) {
            $parametro = $definicao->nome;
            $valor = isset($credenciais[$parametro]) ? $credenciais[$parametro] : $definicao->padrao;
            if (!$v->validar_atributo($definicao, $valor, $erros)) {
                return false;
            }
            $this->credenciais[$parametro] = $valor;
        }
        return true;
    }


    //
    //      Metodo que indica se a forma de autenticacao permite que seja alterada a senha
    //
    public function pode_alterar_senha() {
        return false;
    }


    //
    //      Metodo que atualiza a senha na base utilizada de acordo com as novas credenciais
    //
    public function alterar_senha(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        return false;
    }


    //
    //     Retorna o nome da forma de autenticacao
    //
    final public function __toString() {
        return $this->get_nome();
    }

}
