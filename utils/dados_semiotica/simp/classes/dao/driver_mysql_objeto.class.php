<?php
//
// SIMP
// Descricao: Consultas alto nivel ao banco de dados MySQL
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.12
// Data: 17/04/2008
// Modificado: 20/08/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class driver_mysql_objeto extends driver_objeto {

    //
    //     Construtor padrao
    //
    public function __construct($servidor, $porta, $usuario, $senha, $base) {
    // String $servidor: endereco do servidor
    // Int $porta: porta de acesso ao servidor
    // String $usuario: login do usuario no BD
    // String $senha: senha para acesso ao BD
    // String $base: nome do BD a ser usado ou 0 para nenhum
    //
        parent::__construct('mysql', $servidor, $porta, $usuario, $senha, $base);
    }


    //
    //     Retorna a ultima chave primaria retornada por um insert
    //
    public function get_ultimo_insert($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        if ($this->conexao) {
            $id = mysql_insert_id($this->conexao);
            if ($id) {
                return $id;
            }
            $resultado = $this->consultar('SELECT LAST_INSERT_ID() AS id');
            if (is_resource($resultado)) {
                $obj = $this->fetch_object($resultado);
                $this->liberar_resultado($resultado);
                return $obj->id;
            }
        }
        trigger_error('Erro ao obter ultima chave inserida', E_USER_WARNING);
        return false;
    }


    //
    //     CREATE TABLE: Gera uma SQL de criacao de uma nova tabela no BD
    //
    public function sql_create_table($objeto, $charset = 'UTF-8') {
    // Object $objeto: instencia de uma entidade derivada da classe objeto
    // String $charset: codificacao da tabela
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Percorrer cada campo do objeto
        $vt_campos = array();
        $vt_constraint = array();
        foreach ($objeto->get_atributos() as $def_atributo) {
            $sql_campo = $this->delimitar_campo($def_atributo->nome);
            $sql_tipo  = $this->gerar_sql_tipo($def_atributo);
            $descricao = texto::strip_acentos(texto::decodificar($def_atributo->descricao));
            if (strpos($descricao, '&') !== false) {
                trigger_error('Erro de entities no atributo "'.$def_atributo->nome.'" da classe "'.$objeto->get_classe().'"', E_USER_ERROR);
            }
            $sql_comment = " COMMENT '{$descricao}'";

            // Checar se e' unico
            if ($def_atributo->unico) {
                $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE KEY ({$sql_campo})";

            // Checar se e' uma chave candidata
            } elseif ($def_atributo->chave == 'CK') {
                $vt_constraint['index_'.$def_atributo->nome] = "  INDEX ({$sql_campo})";
            }

            if ($def_atributo->chave != 'PK') {
                $sql_nulo = ' NOT NULL';
                if ($objeto->possui_rel_uu($def_atributo->nome, false)) {
                    $def_atributo_rel = $objeto->get_definicao_rel_uu($def_atributo->nome, false);
                    if ($def_atributo_rel->forte) {
                        $sql_default = '';
                    } else {
                        $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                    }
                } elseif (!preg_match('/^(TINY|MEDIUM|LONG)?TEXT$/', $sql_tipo)) {
                    $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                } else {
                    $sql_default = '';
                }
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_nulo}{$sql_default}{$sql_comment}";
            } else {
                $sql_constraint = 'pk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $sql_nulo    = ' NOT NULL';
                $sql_default = '';
                $sql_autoincrement = ' AUTO_INCREMENT';
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_nulo}{$sql_default}{$sql_autoincrement}{$sql_comment}";
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} PRIMARY KEY ({$sql_campo})";
            }
        }
        foreach ($objeto->get_definicoes_rel_uu() as $atributo_rel => $def_atributo_rel) {
            $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo_rel);
            $sql_atributo_rel = $this->delimitar_campo($atributo_rel);
            $obj_ref = $objeto->__get($def_atributo_rel->nome);
            $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
            $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
            $vt_constraint['index_'.$def_atributo_rel->nome] = "  INDEX ({$sql_atributo_rel})";

            // Se e' um relacionamento forte, adicionar constraint
            if ($def_atributo_rel->forte) {
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                                  "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                                  "      ON DELETE CASCADE\n".
                                                  "      ON UPDATE CASCADE";
            }
        }

        // Definir restricoes de chaves unicas compostas
        foreach ($objeto->get_chaves_unicas_compostas() as $chave_uk) {
            $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.implode(',', $chave_uk));
            $vt_campo_uk = array();
            foreach ($chave_uk as $campo_uk) {
                $vt_campo_uk[] = $this->delimitar_campo($campo_uk);
            }
            $sql_campo_uk = implode(', ', $vt_campo_uk);
            $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE KEY ({$sql_campo_uk})";
        }

        $vt_campos = array_merge($vt_campos, $vt_constraint);
        $sql_campos = $this->implode_campos_create_table($vt_campos);

        $sql_charset = $this->get_charset($charset);
        $sql_collate = $this->get_collate($charset);
        $sql_descricao = substr($objeto->get_tabela(true), 0, 60);
        $sql_opcoes = "ENGINE = InnoDB\n".
                      "AUTO_INCREMENT = 1\n".
                      "DEFAULT CHARACTER SET = {$sql_charset}\n".
                      "DEFAULT COLLATE = {$sql_collate}\n".
                      "COMMENT = '{$sql_descricao}'";

        if ($this->exibicao_usuario) {
            $cabecalho_comentario = "--\n".
                                    "-- TABELA ".$objeto->get_tabela()."\n".
                                    "--\n";
        } else {
            $cabecalho_comentario = '';
        }

        $sql = $cabecalho_comentario.
               "CREATE TABLE {$sql_tabela} (\n".
               "{$sql_campos}\n".
               ")\n".
               $sql_opcoes;
        return $sql;
    }


    //
    //     ALTER TABLE: Gera uma SQL de alteracao de uma tabela do BD
    //
    public function sql_alter_table($objeto, $atributo, $operacao) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser adicionado ou removido
    // Int $operacao: operacao desejada (DRIVER_OBJETO_ADICIONAR_ATRIBUTO ou DRIVER_OBJETO_REMOVER_ATRIBUTO)
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());
        $sql_atributo = $this->delimitar_campo($atributo);

        switch ($operacao) {
        case DRIVER_OBJETO_ADICIONAR_ATRIBUTO:
            $def_atributo = $objeto->get_definicao_atributo($atributo);
            $sql_tipo     = $this->gerar_sql_tipo($def_atributo);
            $sql_nulo     = ' NOT NULL';
            $sql_default  = ' DEFAULT '.$this->gerar_sql_default($def_atributo);

            $descricao   = texto::strip_acentos(texto::decodificar($def_atributo->descricao));
            if (strpos($descricao, '&') !== false) {
                trigger_error('Erro de entities no atributo "'.$def_atributo->nome.'" da classe "'.$objeto->get_classe().'"', E_USER_ERROR);
            }
            $sql_comment = " COMMENT '{$descricao}'";
            
            $sql_alter = "ALTER TABLE {$sql_tabela} ADD COLUMN {$sql_atributo} {$sql_tipo}{$sql_nulo}{$sql_default}{$sql_comment}";

            // Se e' uma chave estrangeira
            if ($objeto->possui_rel_uu($atributo, false)) {
                $def_relacionamento = $objeto->get_definicao_rel_uu($atributo, false);
                $obj_ref = $objeto->get_objeto_rel_uu($def_relacionamento->nome);

                // Se o relacionamento e' fraco e nao possui registros na tabela
                if ($def_relacionamento->forte && !$objeto->possui_registros()) {
                    $sql_index = "ALTER TABLE {$sql_tabela} ADD INDEX ($sql_atributo)";

                    $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo);
                    $sql_atributo_rel = $this->delimitar_campo($atributo);
                    $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
                    $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
                    $sql_constraint = "ALTER TABLE {$sql_tabela}\n".
                                      "  ADD CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                      "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                      "      ON DELETE CASCADE\n".
                                      "      ON UPDATE CASCADE";

                    $sql = array($sql_alter, $sql_index, $sql_constraint);
                } else {
                    $sql = $sql_alter;
                }
            } else {
                $sql = $sql_alter;
            }
            break;
        case DRIVER_OBJETO_REMOVER_ATRIBUTO:
            $sql_alter = "ALTER TABLE {$sql_tabela} DROP COLUMN {$sql_atributo}";

            $dao = new objeto_dao();
            $dao->carregar('operacao');
            $campos_tabela = $dao->get_campos($objeto->get_tabela());
            $campo_tabela = $campos_tabela[$atributo];
        
            // Se e' uma chave estrangeira
            if ($campo_tabela->tabela_ref && $campo_tabela->chave_ref) {
                $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo);
                $sql_index = "ALTER TABLE {$sql_tabela} DROP FOREIGN KEY {$sql_constraint}";
                $sql = array($sql_index, $sql_alter);
            } else {
                $sql = $sql_alter;
            }
            break;
        default:
            $this->adicionar_erro('Opera&ccedil;&atilde;o inv&aacute;lida');
            trigger_error("Operacao invalida para o metodo: ".util::exibir_var($operacao), E_USER_WARNING);
            return false;
        }
        return $sql;
    }


    //
    //     Gera o tipo adequado ao atributo
    //
    protected function gerar_sql_tipo($atributo) {
    // atributo $atributo: objeto com as caracteristicas do atributo de uma entidade
    //
        switch ($atributo->tipo) {
        case 'int':

            // Nao sinalizado
            if (is_numeric($atributo->minimo) && ($atributo->minimo >= 0)) {
                if (!$atributo->maximo) {
                    return 'INT UNSIGNED';
                }
                if ($atributo->maximo >= pow(2, 4 * 8)) {
                    return 'BIGINT UNSIGNED';
                } elseif ($atributo->maximo >= pow(2, 3 * 8)) {
                    return 'INT UNSIGNED';
                } elseif ($atributo->maximo >= pow(2, 2 * 8)) {
                    return 'MEDIUMINT UNSIGNED';
                } elseif ($atributo->maximo >= pow(2, 1 * 8)) {
                    return 'SMALLINT UNSIGNED';
                } else {
                    return 'TINYINT UNSIGNED';
                }

            // Sinalizado
            } else {
                $max = max($atributo->maximo, abs($atributo->minimo));
                if (!$max) {
                    return 'INT';
                }
                
                if ($max >= pow(2, 4 * 8 - 1)) {
                    return 'BIGINT';
                } elseif ($max >= pow(2, 3 * 8 - 1)) {
                    return 'INT';
                } elseif ($max >= pow(2, 2 * 8 - 1)) {
                    return 'MEDIUMINT';
                } elseif ($max >= pow(2, 1 * 8 - 1)) {
                    return 'SMALLINT';
                } else {
                    return 'TINYINT';
                }
            }
            break;
        case 'float':
            $max = max(abs($atributo->maximo), abs($atributo->minimo));
            if (is_numeric($atributo->minimo) && ($atributo->minimo >= 0)) {
                if ($max > (1.175494351 * pow(10, 38))) {
                    $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 10;
                    $casas_decimais = min(53, $atributo->casas_decimais);
                    $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                    return 'DOUBLE'.$casas_decimais.' UNSIGNED';
                } else {
                    $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 5;
                    $casas_decimais = min(23, $atributo->casas_decimais);
                    $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                    return 'FLOAT'.$casas_decimais.' UNSIGNED';
                }
            } else {
                if ($max > (1.175494351 * pow(10, 38))) {
                    $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 10;
                    $casas_decimais = min(53, $atributo->casas_decimais);
                    $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                    return 'DOUBLE'.$casas_decimais;
                } else {
                    $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 5;
                    $casas_decimais = min(23, $atributo->casas_decimais);
                    $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                    return 'FLOAT'.$casas_decimais;
                }
            }
            break;
        case 'string':
            $max = round(max(abs($atributo->maximo), abs($atributo->minimo)));
            $str_max = number_format($max, 0, '.', '');

            // TEXT
            if ($max > 255) {
                if ($max < pow(2, 8)) {
                    return 'TINYTEXT';
                } elseif ($max < pow(2, 16)) {
                    return 'TEXT';
                } elseif ($max < pow(2, 24)) {
                    return 'MEDIUMTEXT';
                }
                return 'LONGTEXT';

            // CHAR(?)
            } elseif (is_numeric($atributo->minimo) &&
                      is_numeric($atributo->maximo) &&
                      ($atributo->minimo == $atributo->maximo)) {
                return "CHAR({$str_max})";
            }

            // VARCHAR(?)
            return "VARCHAR({$str_max})";
        case 'char':
            return 'CHAR(1)';
        case 'bool':
            return 'BOOL';
        case 'binario':
            $max = round(max(abs($atributo->maximo), abs($atributo->minimo)));
            if ($max < pow(2, 8)) {
                return 'TINYBLOB';
            } elseif ($max < pow(2, 16)) {
                return 'BLOB';
            } elseif ($max < pow(2, 24)) {
                return 'MEDIUMBLOB';
            }
            return 'LONGBLOB';
        case 'data':
            return 'DATETIME';
        }
        trigger_error('Tipo desconhecido ('.util::exibir_var($atributo->tipo).')', E_USER_WARNING);
        return false;
    }


    //
    //     TRUNCATE: Gera uma SQL de uma consulta TRUNCATE (DELETE incondicional)
    //
    public function sql_truncate($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        // Montar dados da SQL
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Montar SQL
        $sql = "TRUNCATE TABLE {$sql_tabela}"; // TRUNCATE TABLE %s

        return $sql;
    }


    //
    //     USE DATABASE: Comando do SGBD para trocar de base de dados em tempo de execucao
    //
    public function sql_use_database($bd = false) {
    // String $bd: nome do bd a ser acessado
    //
        if (!$bd) {
            $bd = $this->base;
        }
        return 'use '.$bd;
    }


}//class
