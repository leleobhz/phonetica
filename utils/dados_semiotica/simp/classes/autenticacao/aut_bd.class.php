<?php
//
// SIMP
// Descricao: Classe de autenticacao de usuarios de uma tabela de um banco de dados relacional
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 10/02/2009
// Modificado: 04/03/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class aut_bd extends autenticacao_base {


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'Banco de Dados Externo';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais BD sao formadas por:
    //     String login: login do usuario na tabela do BD
    //     String senha: senha do usuario na tabela do BD
    //     String cript: metodo de criptografia utilizado ('none', 'md5', 'sha1' ou 'crypt')
    //     String sgbd: codigo do SGBD a ser utilizado (como na classe DAO)
    //     String servidor: endereco do servidor
    //     Int porta: porta de acesso ao servidor
    //     String usuario_acesso: login do usuario de acesso ao BD
    //     String senha_acesso: senha para acesso ao BD
    //     String base: nome do BD a ser usado
    //     String tabela: nome da tabela onde estao os usuarios
    //     String campo_login: nome do campo da tabela onde estao os logins
    //     String campo_senha: nome do campo da tabela onde estao as senhas
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

        $atributo = new atributo('sgbd', 'SGBD', 'mysql');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 20);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('servidor', 'Servidor', 'localhost');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('porta', 'Porta', 0);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(0, 1000000);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('usuario_acesso', 'Usu&aacute;rio de acesso', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('senha_acesso', 'Senha de acesso', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('base', 'Nome do BD', 'simp');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tabela', 'Tabela', 'usuarios');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('campo_login', 'Nome do campo de login', 'login');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('campo_senha', 'Nome do campo de senha', 'senha');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('cript', 'Forma de Criptografia', 'md5');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 20);
        $this->parametros[] = $atributo;
        unset($atributo);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        // Se nao informou as credenciais
        if (!$this->credenciais) {
            $erros[] = 'N&atilde;o foram especificadas as credenciais';
            return false;
        }

        $sgbd     = $this->credenciais['sgbd'];
        $servidor = $this->credenciais['servidor'];
        $porta    = $this->credenciais['porta'];
        $usuario  = $this->credenciais['usuario_acesso'];
        $senha    = $this->credenciais['senha_acesso'];
        $base     = $this->credenciais['base'];
        $dao = new objeto_dao($sgbd, $servidor, $porta, $usuario, $senha, $base);

        if (!$dao->conectar()) {
            $erros[] = 'Erro ao conectar na base de dados';
            return false;
        }

        // Dados para a consulta
        $tabela      = $this->credenciais['tabela'];
        $campo_login = $this->credenciais['campo_login'];
        $campo_senha = $this->credenciais['campo_senha'];
        $login       = $this->credenciais['login'];
        $senha       = $this->credenciais['senha'];

        // Campos
        $vt_campos = array();
        $vt_campos[] = $dao->montar_nome_campo($tabela, $campo_login);
        $vt_campos[] = $dao->montar_nome_campo($tabela, $campo_senha);
        $campos = $dao->implode_campos_select($vt_campos);

        // Tabela
        $tabela = $dao->delimitar_tabela($tabela);

        // Condicoes
        $condicoes = $dao->montar_nome_campo($tabela, $campo_login).
                     ' '.$dao->gerar_sql_operador('=').' '.
                     $dao->delimitar_valor($login);

        // SQL
        $sql = 'SELECT '.$campos.' FROM '.$tabela.' WHERE '.$condicoes;

        $resultado = $dao->query($sql);
        if (!$resultado) {
            $erros[] = 'Erro ao consultar usu&aacute;rio na base de dados';
            return false;
        } else {
            $quantidade = $dao->quantidade_registros($resultado);
            if ($quantidade == 0) {
                $erros[] = 'Usu&aacute;rio inv&aacute;lido';
                return false;
            } elseif ($quantidade !== 1) {
                $erros[] = 'Foram encontrados '.$quantidade.' usu&aacute;rios com o login informado';
                return false;
            }
        }
        $obj = $dao->fetch_object($resultado);
        $senha_bd = $obj->{$campo_senha};

        // Validar a senha
        switch ($this->credenciais['cript']) {
        case 'md5':
            if (strcmp($senha_bd, md5($senha)) != 0) {
                $erros[] = 'Senha inv&aacute;lida';
                return false;
            }
            break;
        case 'sha1':
            if (strcmp($senha_bd, sha1($senha)) != 0) {
                $erros[] = 'Senha inv&aacute;lida';
                return false;
            }
            break;
        case 'crypt':
            if (strcmp($senha_bd, crypt($senha, $senha_bd)) != 0) {
                $erros[] = 'Senha inv&aacute;lida';
                return false;
            }
            break;
        case '':
        case 'none':
            if (strcmp($senha_bd, $senha) != 0) {
                $erros[] = 'Senha inv&aacute;lida';
                return false;
            }
            break;
        }

        return true;
    }


    //
    //      Metodo que indica se a forma de autenticacao permite que seja alterada a senha
    //
    public function pode_alterar_senha() {
        return true;
    }


    //
    //      Metodo que atualiza a senha na base utilizada
    //
    public function alterar_senha(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        // Se nao informou as credenciais
        if (!$this->credenciais) {
            $erros[] = 'N&atilde;o foram especificadas as credenciais';
            return false;
        }

        $sgbd     = $this->credenciais['sgbd'];
        $servidor = $this->credenciais['servidor'];
        $porta    = $this->credenciais['porta'];
        $usuario  = $this->credenciais['usuario_acesso'];
        $senha    = $this->credenciais['senha_acesso'];
        $base     = $this->credenciais['base'];
        $dao = new objeto_dao($sgbd, $servidor, $porta, $usuario, $senha, $base);

        if (!$dao->conectar()) {
            $erros[] = 'Erro ao conectar na base de dados';
            return false;
        }

        // Dados para a atualizacao
        $tabela      = $this->credenciais['tabela'];
        $campo_login = $this->credenciais['campo_login'];
        $campo_senha = $this->credenciais['campo_senha'];
        $login       = $this->credenciais['login'];
        $senha       = $this->credenciais['senha'];

        // Campos
        $vt_campos = array();
        $vt_campos[] = $dao->montar_nome_campo($tabela, $campo_login);
        $vt_campos[] = $dao->montar_nome_campo($tabela, $campo_senha);
        $campos = $dao->implode_campos_select($vt_campos);

        switch ($this->credenciais['cript']) {
        case 'md5':
            $senha_codificada = md5($senha);
            break;
        case 'sha1':
            $senha_codificada = sha1($senha);
            break;
        case 'crypt':
            $senha_codificada = crypt($senha);
            break;
        default:
        case 'none':
            $senha_codificada = $senha;
            break;
        }

        $tabela = $dao->delimitar_tabela($tabela);
        $campo_senha_sql = $dao->montar_nome_campo($tabela, $campo_senha);
        $valor_senha_sql = $dao->delimitar_valor($senha_codificada);
        $condicoes = $dao->montar_nome_campo($tabela, $campo_login).
                     ' '.$dao->gerar_sql_operador('=').' '.
                     $dao->delimitar_valor($login);

        // SQL
        $sql = 'UPDATE '.$tabela.' SET '.$campo_senha_sql.' = '.$valor_senha_sql.' WHERE '.$condicoes;
        $resultado = $dao->query($sql);
        if (!$resultado) {
            $erros[] = 'Erro ao atualizar a senha na base de dados externa';
            return false;
        }
        return true;
    }
}
