<?php
//
// SIMP
// Descricao: Interface de operacoes sobre bancos de dados
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 14/10/2008
// Modificado: 14/10/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class driver_pdo_mysql_operacao extends driver_pdo_mysql {


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
    public function grant($usuario, $base) {
    // String $usuario: nome de identificacao do usuario a ser criado
    // String $base: nome da base de dados referenciada
    //
        // Obter SQL
        $sql = $this->sql_grant($usuario, $base);
        if (!$sql) {
            return false;
        }

        // Executar
        return (bool)$this->consultar($sql);
    }


    //
    //     SHOW TABLES: obtem a lista de tabelas do BD (com nome e comentario)
    //
    public function get_tabelas() {
        $tabelas = array();

        $sql = 'SHOW TABLES';
        $resultado = $this->consultar($sql);
        if (!$resultado) {
            return false;
        }
        while ($tabela = $this->fetch_object($resultado)) {
            $nome_tabela = $tabela->{'Tables_in_'.$this->base};
            $sql_status = "SHOW TABLE STATUS FROM {$this->base} LIKE '{$nome_tabela}'";
            $resultado_status = $this->consultar($sql_status);

            if ($resultado_status) {
                $status = $this->fetch_object($resultado_status);
                $this->liberar_resultado($resultado_status);
                $comentario = $status->Comment;
                $pos = strpos($comentario, ';');
                if ($pos !== false) {
                    $comentario = substr($comentario, 0, $pos);
                }
            } else {
                $comentario = '';
            }

            // Gerar objeto com os dados obtidos
            $t = new stdClass();
            $t->nome = $nome_tabela;
            $t->comentario = $comentario;
            $tabelas[] = $t;
        }
        $this->liberar_resultado($resultado);
        return $tabelas;
    }


    //
    //     DESCRIBE: descreve os campos de uma tabela
    //
    public function get_campos($tabela) {
    // String $tabela: nome da tabela que deseja-se obter os campos
    //
        $campos = array();

        $sql_tabela = $this->delimitar_tabela($tabela);
        $sql = 'SELECT `c`.`COLUMN_NAME` `nome`, `c`.`ORDINAL_POSITION` `posicao`, `c`.`COLUMN_DEFAULT` `default`, '.
               "(`c`.`IS_NULLABLE` = 'YES') `nulo`, `c`.`DATA_TYPE` `tipo`, `c`.`COLUMN_TYPE` `tipo_campo`, ".
               '`c`.`CHARACTER_MAXIMUM_LENGTH` `tamanho`, '.
               '`c`.`CHARACTER_OCTET_LENGTH` `tamanho_bin`, `c`.`NUMERIC_SCALE` `escala`, `c`.`NUMERIC_PRECISION` `precisao`, '.
               '`c`.`CHARACTER_SET_NAME` `codificacao`, `c`.`COLLATION_NAME` `collation`, `c`.`COLUMN_KEY` `chave`, '.
               '`EXTRA` `extra`, `c`.`COLUMN_COMMENT` `comentario` '.
               'FROM `information_schema`.`COLUMNS` `c` '.
               'WHERE '.
               "`c`.`TABLE_SCHEMA` = '{$this->base}' AND `c`.`TABLE_NAME` = '{$tabela}'";
        $resultado = $this->consultar($sql);
        if (!$resultado) {
            return false;
        }
        $sql_fk = 'SELECT `k`.`COLUMN_NAME` `chave_fk`, `k`.`REFERENCED_TABLE_NAME` `tabela_ref`, `k`.`REFERENCED_COLUMN_NAME` `chave_ref` '.
                  'FROM `information_schema`.`KEY_COLUMN_USAGE` `k` '.
                  'WHERE '.
                  "`k`.`TABLE_SCHEMA` = '{$this->base}' AND `k`.`TABLE_NAME` = '{$tabela}' AND `k`.`REFERENCED_TABLE_NAME` IS NOT NULL";

        $relacoes = array();
        $resultado_fk = $this->consultar($sql_fk);
        if ($resultado) {
            while ($relacao = $this->fetch_object($resultado_fk)) {
                $relacoes[$relacao->chave_fk] = $relacao;
            }
        }
        while ($campo = $this->fetch_object($resultado)) {
            $campo->nulo = (bool)$campo->nulo;
            if (isset($relacoes[$campo->nome])) {
                $campo->tabela_ref = $relacoes[$campo->nome]->tabela_ref;
                $campo->chave_ref  = $relacoes[$campo->nome]->chave_ref;
            }
            $campos[] = $campo;
        }
        $this->liberar_resultado($resultado);
        return $campos;
    }


    //
    //     DESCRIBE: retorna a descricao dos possiveis campos retornados em get_campos
    //
    public function get_descricao_campos() {
        return array('nome'        => 'Nome',
                     'posicao'     => 'Posi&ccedil;&atilde;o',
                     'tipo'        => 'Tipo',
                     'tipo_campo'  => 'Tipo de Campo',
                     'tamanho'     => 'Tamanho',
                     'tamanho_bin' => 'Tamanho Bin&aacute;rio',
                     'precisao'    => 'Precis&atilde;o',
                     'escala'      => 'Escala',
                     'nulo'        => 'Nulo',
                     'default'     => 'Padr&atilde;o',
                     'codificacao' => 'Codifica&ccedil;&atilde;o',
                     'collation'   => 'Collation',
                     'chave'       => 'Chave',
                     'extra'       => 'Extra',
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
        $sql_usuario = $this->delimitar_valor($usuario);
        $sql_servidor = $this->delimitar_valor($this->servidor);
        $sql = "SELECT COUNT(*) AS `existe` FROM `mysql`.`user` AS `u` ".
               "WHERE `u`.`User` = {$sql_usuario} AND `u`.`Host` = {$sql_servidor};";
        $resultado = $this->consultar($sql);
        if (!$resultado) {
            return false;
        }
        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);

        return (bool)$obj->existe;
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
        $base = trim($base);
        if (empty($base)) {
            $erro = 'O nome do BD n&atilde;oo pode ser vazio';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_base = $this->delimitar_campo($base);
        $sql = "DROP DATABASE IF EXISTS {$sql_base}";
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
        } elseif (strpos($senha, "'") !== false) {
            $erro = 'N&atilde;o &eacute; permitido usar aspas na senha';
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            return false;
        }
        $sql_senha = $senha;

        $sql = "CREATE USER '{$sql_usuario}'@'{$sql_servidor}' IDENTIFIED BY '{$sql_senha}'";
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

        $sql = "DROP USER '{$sql_usuario}'@'{$sql_servidor}'";
        return $sql;
    }


    //
    //     GRANT: Gera uma SQL para dar permissoes a um usuario sobre uma base de dados
    //
    public function sql_grant($usuario, $base) {
    // String $usuario: nome de identificacao do usuario
    // String $base: nome da base de dados referenciada
    //
        $sql_permissoes = 'ALTER, CREATE, DELETE, DROP, INDEX, INSERT, LOCK TABLES, SELECT, UPDATE';
        $sql_base = str_replace('`', '', $base);
        $sql_usuario = str_replace("'", '', $usuario);
        $sql_servidor = $this->servidor;

        $sql = "GRANT {$sql_permissoes} ON `{$sql_base}`.* TO '{$sql_usuario}'@'{$sql_servidor}'";
        return $sql;
    }


/// @ METODOS AUXILIARES


    //
    //     Retorna o tipo de chave do campo
    //
    private function get_tipo_chave($tipo) {
    // String $tipo: tipo de chave obtida com DESCRIBE `tabela`
    //
        switch ($tipo) {
        case 'PRI':
            return 'PK';
        case 'MUL':
            return 'FK';
        }
        return '';
    }

}//class
