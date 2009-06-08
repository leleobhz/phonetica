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
final class driver_pdo_pgsql_operacao extends driver_pgsql {


/// @ METODOS DE OPERACOES SOBRE O BANCO DE DADOS


    //
    //     Checa se o Banco existe
    //
    public function database_exists($base) {
    // String $base: nome do banco de dados a ser verificado
    //
        $sql = "SELECT COUNT(*) AS existe FROM pg_database WHERE datname = '{$base}'";
        $resultado = $this->consultar($sql);
        if (!$resultado) {
            return false;
        }
        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);

        return (bool)$obj->existe;
    }


    //
    //     CREATE DATABASE: Tenta criar uma base de dados
    //
    public function create_database($base, $charset = 'UTF-8') {
    // String $base: nome do banco de dados a ser criado
    // String $charset: codificacao usada no banco de dados a ser criado
    //
        if ($this->database_exists($base)) {
            $this->drop_database($base);
        }

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

        $sql = 'SELECT c.relname AS tabela, obj_description(c.oid, \'pg_class\') AS comentario '.
               'FROM pg_class c LEFT JOIN pg_namespace n ON (n.oid = c.relnamespace) '.
               'WHERE c.relkind = \'r\' AND nspname = \'public\' '.
               'ORDER BY c.relname';

        if ($resultado = $this->consultar($sql)) {
            while ($t = $this->fetch_object($resultado)) {

                // Armazenar dados da tabela
                $tabela = new stdClass();
                $tabela->nome       = $t->tabela;
                $tabela->comentario = $t->comentario;
                $tabelas[] = $tabela;
            }
            $this->liberar_resultado($resultado);
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

        $conectou = false;
        if (!$this->conexao) {
            $conectou = $this->conectar();
        }

        // Obter dados basicos dos campos
        $sql = 'SELECT a.attname AS nome, a.attnum AS posicao, t.typname AS tipo, '.
               '       a.attlen AS tamanho, NOT(a.attnotNULL) AS nulo '.
               'FROM pg_class as c, pg_attribute a, pg_type t '.
               'WHERE '.
               '  a.attnum > 0 AND '.
               '  a.attrelid = c.oid AND '.
               "  c.relname = '{$tabela}' AND ".
               '  a.atttypid = t.oid '.
               'ORDER BY a.attnum';
        $rbase = $this->consultar($sql);
        $resultado = array();
        while ($lbase = $this->fetch_object($rbase)) {
            $resultado[$lbase->nome] = $lbase;
        }

        // Obter dados extras dos campos
        if ($resultado) {
            $extras = array();

            // Obter chave primaria
            $sql = 'SELECT a.attname AS atributo '.
                   'FROM pg_class c '.
                   '  INNER JOIN pg_attribute a ON (c.oid = a.attrelid) '.
                   '  INNER JOIN pg_index i ON (c.oid = i.indrelid) '.
                   'WHERE '.
                   '  i.indkey[0] = a.attnum AND '.
                   "  i.indisprimary = 't' AND ".
                   "  c.relname = '{$tabela}'";

            $r = $this->consultar($sql);
            if ($r && $this->quantidade_registros($r)) {
                $obj = $this->fetch_object($r);
                $extras[$obj->atributo] = new stdClass();
                $extras[$obj->atributo]->chave = 'PK';
                $this->liberar_resultado($r);
            }

            // Consultar nome, posicao, tamanho e comentario
            $sql2 = 'SELECT '.
                    '  a.attname AS atributo, '.
                    '  (a.atttypmod - 4) AS tamanho, '.
                    '  a.attnum AS posicao, '.
                    '  col_description(c.oid, a.attnum) AS comentario '.
                    'FROM pg_class c '.
                    '  INNER JOIN pg_attribute a ON (c.oid = a.attrelid) '.
                    'WHERE '.
                    "  c.relname = '{$tabela}' AND ".
                    '  a.attnum > 0 '.
                    'ORDER BY a.attnum ASC';
            $result2 = $this->consultar($sql2);

            while ($obj = $this->fetch_object($result2)) {
                if (!isset($extras[$obj->atributo])) {
                    $extras[$obj->atributo] = new stdClass();
                }

                // Comentario
                $extras[$obj->atributo]->comentario = $obj->comentario;

                // Posicao
                $extras[$obj->atributo]->posicao = $obj->posicao;

                // Tamanho
                if ($obj->tamanho > 0) {
                    $extras[$obj->atributo]->tamanho = $obj->tamanho;
                }
            }
            $this->liberar_resultado($resut2);

            // Consultar valor padrao
            $sql3 = 'SELECT '.
                    '  a.attname AS atributo, '.
                    '  pg_get_expr(d.adbin, d.adrelid) AS padrao '.
                    'FROM pg_class c '.
                    '  INNER JOIN pg_attribute a ON (c.oid = a.attrelid) '.
                    '  INNER JOIN pg_attrdef d ON (c.oid = d.adrelid AND d.adnum = a.attnum) '.
                    'WHERE '.
                    "  c.relname = '{$tabela}' ".
                    'ORDER BY a.attnum ASC';
            $result3 = $this->consultar($sql3);

            while ($obj = $this->fetch_object($result3)) {
                if (strpos('nextval', $obj->padrao) !== false) {
                    $extras[$obj->atributo]->padrao = null;
                } elseif (($pos = strpos($obj->padrao, '::')) !== false) {
                    $p = substr($obj->padrao, 0, $pos);
                    @ eval("\$valor_padrao = {$p};");
                    $extras[$obj->atributo]->padrao = $valor_padrao;
                } else {
                    if ($resultado[$obj->atributo]->tipo == 'bool') {
                        $extras[$obj->atributo]->padrao = ($obj->padrao == 'true') ? 1 : 0;
                    } else {
                        @ eval("\$valor_padrao = {$obj->padrao};");
                        $extras[$obj->atributo]->padrao = $valor_padrao;
                    }
                }
            }
            $this->liberar_resultado($resut3);

            // Obter restricoes de chaves estrangeiras
            $sql4 = 'SELECT '.
                    '  a.attname AS atributo, '.
                    '  clf.relname AS tabela_ref, '.
                    '  af.attname AS atributo_ref '.
                    'FROM pg_catalog.pg_attribute a '.
                    '  JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = \'r\') '.
                    '  JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace) '.
                    '  JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND '.
                    '       ct.confrelid != 0 AND ct.conkey[1] = a.attnum) '.
                    '  JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = \'r\') '.
                    '  JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace) '.
                    '  JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND '.
                    '       af.attnum = ct.confkey[1]) '.
                    'WHERE '.
                    "  cl.relname = '{$tabela}'";

            $result4 = $this->consultar($sql4);
            while ($obj = $this->fetch_object($result4)) {
                $extras[$obj->atributo]->tabela_ref = $obj->tabela_ref;
                $extras[$obj->atributo]->atributo_ref = $obj->atributo_ref;
                $extras[$obj->atributo]->chave = 'FK';
            }
            $this->liberar_resultado($resut4);

            // Preencher o vetor de campos
            foreach ($resultado as $nome => $atributos) {
                $tamanho      = false;
                $padrao       = null;
                $tipo         = $atributos->tipo;
                $nulo         = $atributos->nulo == 't';
                $chave        = isset($extras[$nome]->chave) ? $extras[$nome]->chave : '';
                $posicao      = $extras[$nome]->posicao;
                $comentario   = $extras[$nome]->comentario;
                $tabela_ref   = isset($extras[$nome]->tabela_ref) ? $extras[$nome]->tabela_ref : false;
                $atributo_ref = isset($extras[$nome]->atributo_ref) ? $extras[$nome]->atributo_ref : false;

                if ($atributos->tamanho > 0) {
                    $tamanho = $atributos->tamanho;
                } elseif (isset($extras[$nome]->tamanho)) {
                    $tamanho = $extras[$nome]->tamanho;
                }

                if (isset($extras[$nome]->padrao)) {
                    $padrao = $extras[$nome]->padrao;
                }

                $c = new stdClass();
                $c->nome         = $nome;
                $c->posicao      = $posicao;
                $c->tipo         = $tipo;
                $c->nulo         = $nulo;
                $c->chave        = $chave;
                $c->tamanho      = $tamanho;
                $c->default      = $padrao;
                $c->comentario   = $comentario;
                $c->tabela_ref   = $tabela_ref;
                $c->atributo_ref = $atributo_ref;
                $campos[$nome] = $c;
            }
        }

        if ($conectou) {
            $this->desconectar();
        }

        return $campos;
    }


    //
    //     DESCRIBE: retorna a descricao dos possiveis campos retornados em get_campos
    //
    public function get_descricao_campos() {
        return array('nome'         => 'Nome',
                     'posicao'      => 'Posi&ccedil;&atilde;o',
                     'tipo'         => 'Tipo',
                     'nulo'         => 'Nulo',
                     'chave'        => 'Chave',
                     'tamanho'      => 'Tamanho',
                     'default'      => 'Padr&atilde;o',
                     'tabela_ref'   => 'Tabela Original',
                     'atributo_ref' => 'Atributo Original',
                     'comentario'   => 'Coment&aacute;rio');
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
        $sql = "SELECT COUNT(*) AS existe FROM pg_roles WHERE rolname = {$sql_usuario}";
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
        $sql_base = $base;
        $sql_charset = "'".$this->get_charset($charset)."'";
        $sql = "CREATE DATABASE {$sql_base} ENCODING {$sql_charset}";
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
        $sql_base = $base;
        $sql = "DROP DATABASE {$sql_base}";
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

        $sql = "CREATE USER {$sql_usuario} PASSWORD '{$sql_senha}' NOCREATEDB NOCREATEUSER";
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

        $sql = "DROP USER {$sql_usuario}";
        return $sql;
    }


    //
    //     GRANT: Gera uma SQL para dar permissoes a um usuario sobre uma base de dados
    //
    public function sql_grant($usuario, $base) {
    // String $usuario: nome de identificacao do usuario
    // String $base: nome da base de dados referenciada
    //
        $sql_usuario = $usuario;

        $sqls = array();
        foreach ($this->get_tabelas() as $tabela) {
            $sql_tabela = $tabela->nome;
            $sqls[] = "ALTER TABLE {$sql_tabela} OWNER TO {$sql_usuario}";
        }
        return $sqls;
    }

}//class
