<?php
//
// SIMP
// Descricao: Interface de operacoes sobre bancos de dados
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 11/09/2008
// Modificado: 02/09/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class driver_oci8_operacao extends driver_oci8 {


/// @ METODOS DE OPERACOES SOBRE O BANCO DE DADOS


    //
    //     CREATE DATABASE: Tenta criar uma base de dados
    //
    public function create_database($base, $charset = 'UTF-8') {
    // String $base: nome do banco de dados a ser criado
    // String $charset: codificacao usada no banco de dados a ser criado
    //
        // Obter SQL
        $sql = $this->sql_create_database($base, $charset);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     DROP DATABASE: Tenta remover uma base de dados
    //
    public function drop_database($base) {
    // String $base: nome da base de dados a ser removida
    //
        // Obter SQL
        $sql = $this->sql_drop_database($base);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     CREATE USER: Tenta criar um novo usuario do SGBD
    //
    public function create_user($usuario, $senha, $base) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $senha: senha descriptografada do usuario
    // String $base: nome da base de dados referenciada
    //
        // Se o usuario existe, entao precisa apaga-lo primeiro
        if ($this->user_exists($usuario, $base)) {
            if (!$this->drop_user($usuario, $base)) {
                trigger_error('Erro ao apagar usuario "'.util::exibir_var($usuario).'" no BD', E_USER_WARNING);
                return false;
            }
        }

        // Obter SQL
        $sql = $this->sql_create_user($usuario, $senha, $base);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     DROP USER: Tenta remover um usuario do SGBD
    //
    public function drop_user($usuario, $base) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $base: nome da base de dados referenciada
    //
        // Se o usuario nao existe, entao nao precisa apagar
        if (!$this->user_exists($usuario, $base)) {
            return true;
        }

        // Obter SQL
        $sql = $this->sql_drop_user($usuario, $base);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     GRANT: Da permissao a um usuario sobre uma base de dados
    //
    public function grant($usuario, $base, $host) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $base: nome da base de dados referenciada
    // String $host: host do servidor onde fica o sistema
    //
        // Obter SQL
        $sql = $this->sql_grant($usuario, $base, $host);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     SHOW TABLES: obtem a lista de tabelas do BD (com "nome", "comentario" e objeto "detalhes")
    //
    public function get_tabelas() {
        $tabelas = array();
        $sql = 'SELECT table_name AS "nome" '.
               'FROM user_tables';
        $resultado = $this->consultar($sql);
        if (!$resultado) {
            return false;
        }
        while ($tabela = $this->fetch_object($resultado)) {
            $nome_tabela = $tabela->nome;

            // Gerar objeto com os dados obtidos
            $t = new stdClass();
            $t->nome = $nome_tabela;
//TODO
            $t->comentario = null;
            $t->detalhes = null;
            $tabelas[$nome_tabela] = $t;
        }
        return $tabelas;
    }


    //
    //     DESCRIBE: descreve os campos de uma tabela
    //
    public function get_campos($tabela) {
    // String $tabela: nome da tabela que deseja-se obter os campos
    //
        $campos = array();
//TODO
        return $campos;
    }


    //
    //     DESCRIBE: retorna a descricao dos possiveis campos retornados em get_campos
    //
    public function get_descricao_campos() {
        return array('nome'        => 'Nome',
                     'posicao'     => 'Posi&ccedil;&atilde;o',
                     'tipo'        => 'Tipo',
                     'tamanho'     => 'Tamanho',
                     'precisao'    => 'Precis&atilde;o',
                     'escala'      => 'Escala',
                     'nulo'        => 'Nulo',
                     'default'     => 'Padr&atilde;o',
                     'chave'       => 'Chave',
                     'tabela_ref'  => 'Tabela Original',
                     'chave_ref'   => 'Chave Original',
                     'comentario'  => 'Coment&aacute;rio');
    }


    //
    //     Checa se um usuario existe no SGBD
    //
    public function user_exists($usuario, $base) {
    // String $usuario: login do usuario a ser testado
    // String $base: base de dados em questao
    //
        // Checar se o usuario existe
//TODO
    }


/// @ METODOS DE GERACAO DE SQL


    //
    //     CREATE DATABASE: Gera a SQL de criacao do BD
    //
    public function sql_create_database($base, $charset = 'UTF-8') {
    // String $base: nome do banco de dados a ser criado
    // String $charset: codificacao usada no banco de dados a ser criado
    //
        $base = trim($base);
        if (empty($base)) {
            $erro = 'O nome do BD n&atilde;o pode ser vazio';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_base = $this->delimitar_campo($base);
        $sql_charset = $this->get_charset($charset);
        $sql = "CREATE DATABASE {$sql_base} CHARACTER SET {$sql_charset}";
        return $sql;
    }


    //
    //     DROP DATABASE: Gera a SQL de remocao do BD
    //
    public function sql_drop_database($base) {
    // String $base: nome do banco de dados a ser removido
    //
        $sql = array('SHUTDOWN ABORT',
                     'STARTUP MOUNT EXCLUSIVE RESTRICT',
                     'DROP DATABASE');
        return $sql;
    }


    //
    //     CREATE USER: Gera a SQL de criacao do usuario
    //
    public function sql_create_user($usuario, $senha, $base) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $senha: senha descriptografada do usuario
    // String $base: nome da base de dados referenciada
    //
        $usuario = trim($usuario);
        if (empty($usuario)) {
            $erro = 'O nome de usu&aacute;rio n&atilde;o pode ser vazio';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        } elseif (!preg_match('/^[A-z0-9-_]+$/', $usuario)) {
            $erro = 'O nome de usu&aacute;rio possui caracteres inv&aacute;lidos';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_usuario = $usuario;
        $sql_servidor = str_replace("'", '', $this->servidor);

        $senha = trim($senha);
        if (empty($senha)) {
            $erro = 'A senha n&atilde;o pode ser vazia';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        } elseif (strpos($senha, "'") !== false || strpos($senha, ' ') !== false) {
            $erro = 'N&atilde;o &eacute; permitido usar aspas ou espa&ccedil;os na senha';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_senha = $senha;

        $sql = "CREATE USER {$sql_usuario} IDENTIFIED BY {$sql_senha}";
        return $sql;
    }


    //
    //     DROP USER: Gera a SQL de remocao do usuario
    //
    public function sql_drop_user($usuario, $base) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $base: nome da base de dados referenciada
    //
        $usuario = trim($usuario);
        if (empty($usuario)) {
            $erro = 'O nome de usu&aacute;rio n&atilde;o pode ser vazio';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        } elseif (!preg_match('/^[A-z0-9-_]+$/', $usuario)) {
            $erro = 'O nome de usu&aacute;rio possui caracteres inv&aacute;lidos';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_usuario  = $usuario;
        $sql_servidor = $this->servidor;

        $sql = "DROP USER {$sql_usuario}";
        return $sql;
    }


    //
    //     GRANT: Gera uma SQL para dar permissoes a um usuario sobre uma base de dados
    //
    public function sql_grant($usuario, $base, $host) {
    // String $usuario: nome de identificacao do usuario
    // String $base: nome da base de dados referenciada
    // String $host: host do servidor onde fica o sistema
    //
        $sql_permissoes = 'SELECT, UPDATE, INSERT, DELETE, ALTER, DEBUG, EXECUTE, INDEX, REFERENCES, '.
                          'CREATE TABLE, ALTER TABLE, DROP TABLE,';
        $sql_base    = $base;
        $sql_usuario = $usuario;

        $sql = "GRANT {$sql_permissoes} ON {$sql_base} TO {$sql_usuario}";
        return $sql;
    }

}//class
