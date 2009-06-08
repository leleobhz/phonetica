<?php
//
// SIMP
// Descricao: Classe Usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.12
// Data: 03/03/2007
// Modificado: 10/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Formas de geracao de senha
define('USUARIO_SENHA_ALEATORIA',  1);
define('USUARIO_SENHA_ESPECIFICA', 2);

// Constantes
define('USUARIO_TAM_SENHA',         8);
define('USUARIO_ACENTOS_LOGIN',     false);
define('USUARIO_MAIUSCULAS_LOGIN',  false);
define('USUARIO_NOME_SISTEMA',      $CFG->titulo);
define('USUARIO_DESCRICAO_SISTEMA', $CFG->descricao);
define('USUARIO_DOMINIO',           $CFG->dominio);
define('USUARIO_LINK_ACESSO',       $CFG->wwwroot);
define('USUARIO_TIPO_AUTENTICACAO', $CFG->autenticacao);

abstract class usuario_base extends objeto_formulario {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Usu&aacute;rio',
            /* Entidade Plural */ 'Usu&aacute;rios',
            /* Genero          */ 'M',
            /* Classe          */ 'usuario',
            /* Tabela          */ 'usuarios',
            /* Desc. Tabela    */ 'Tabela de dados dos usuarios');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_usuario
        $atributo = new atributo('cod_usuario', 'C&oacute;digo do Usu&aacute;rio', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 10000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: login
        $atributo = new atributo('login', 'Login', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(3, 20);
        $atributo->set_validacao('LOGIN', 'validar_login', true);
        $atributo->set_filtro('converter_login');
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: nome
        $atributo = new atributo('nome', 'Nome', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('NOME', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: email
        $atributo = new atributo('email', 'E-mail', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(3, 128);
        $atributo->set_validacao('EMAIL', false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: senha
        $atributo = new atributo('senha', 'Senha', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(5, 50);
        $atributo->set_validacao('SENHA', false, false);
        $atributo->set_filtro('codificar');
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cancelado
        $atributo = new atributo('cancelado', 'Cancelado', 0);
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: grupos
        $this->adicionar_rel_un(
            /* nome classe     */ 'usuarios_grupos',
            /* vetor gerado    */ 'grupos',
            /* index vetor     */ 'cod_grupo',
            /* campo impressao */ 'grupo:nome',
            /* campo ordem     */ 'grupo:nome');

        // CAMPO IMPLICITO: visivel
        $this->adicionar_atributo_implicito('visivel', 'Visivel', 'get_visivel', array('cancelado'));
    }


    //
    //     Indica se o usuario e' visivel ou nao
    //
    public function get_visivel() {
        return !$this->__get('cancelado');
    }


/// @ METODOS DE VALIDACAO


    //
    //     Valida o login do usuario
    //
    public function validar_login($login) {
    // String $login: login informado
    //
        $nao_permitidos = array('rad', 'ufla');
        $login_minusculo = texto::strtolower($login);
        if (in_array($login_minusculo, $nao_permitidos)) {
            $this->erros[] = "Este login n&atilde;o pode ser utilizado";
            return false;
        }
        return true;
    }


/// @ METODOS DE CONVERSAO


    //
    //     Codifica uma senha para ser armazenada no BD
    //
    abstract public function codificar($senha);
    // String $senha: senha a ser codificada
    //


    //
    //     Converte o login para padronizacao
    //
    public function converter_login($login) {
    // String $login: login de entrada
    //
        $converter = array('-' => '_',
                           '.' => '_',
                           ' ' => '_');
        $login = strtr($login, $converter);
        if (!USUARIO_ACENTOS_LOGIN) {
            $login = texto::strip_acentos($login);
        }
        if (!USUARIO_MAIUSCULAS_LOGIN) {
            $login = texto::strtolower($login);
        }
        return $login;
    }

}//class
