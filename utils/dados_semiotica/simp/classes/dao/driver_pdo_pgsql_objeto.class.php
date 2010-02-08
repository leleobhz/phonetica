<?php
//
// SIMP
// Descricao: Consultas alto nivel ao banco de dados PostgreSQL
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.3
// Data: 14/10/2008
// Modificado: 26/01/2010
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class driver_pdo_pgsql_objeto extends driver_objeto {
    private $ultimo_insert;
    private $constraint;


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
        $this->ultimo_insert = array();
        parent::__construct('pdo_pgsql', $servidor, $porta, $usuario, $senha, $base);
    }


    //
    //     Monta a funcao usada em uma condicao SQL
    //
    public function montar_funcao_condicao($funcao, $operando, $tipo_operando, $atributo) {
    // String $funcao: nome da funcao (dia, mes, ano, hora, minuto, segundo)
    // String $operando: valor do operando
    // Int $tipo_operando: indica o que e' o operando (CONDICAO_SQL_TIPO_ATRIBUTO ou CONDICAO_SQL_TIPO_VALOR)
    // atributo $atributo: definicao do atributo
    //
        $funcoes = array('dia'     => 'day',
                         'mes'     => 'month',
                         'ano'     => 'year',
                         'hora'    => 'hour',
                         'minuto'  => 'minute',
                         'segundo' => 'second',
                         'diaano'  => 'doy');
        if (isset($funcoes[$funcao])) {
            switch ($tipo_operando) {
            case CONDICAO_SQL_TIPO_ATRIBUTO:
                return $this->delimitar_funcao('extract').'('.$funcoes[$funcao].' from '.$operando.')';
            case CONDICAO_SQL_TIPO_VALOR:
                return $this->delimitar_funcao('extract').'('.$funcoes[$funcao].' from timestamp '.$operando.')';
            }
        }
        trigger_error('Driver PostgreSQL nao suporte a funcao "'.$funcao.'"', E_USER_WARNING);
    }


    // 
    //     Prepara para a criacao de tabelas
    //
    public function preparar_criacao_tabelas($vt_objetos) {
    // Array[Objeto] $vt_objetos: vetor de objetos de entidades
    //
        $this->constraint = array();
        
        foreach ($vt_objetos as $objeto) {
            $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

            // Restricoes de relacionamentos externos
            foreach ($objeto->get_definicoes_rel_uu() as $atributo_rel => $def_atributo_rel) {
                $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo_rel);
                $sql_atributo_rel = $this->delimitar_campo($atributo_rel);
                $obj_ref = $objeto->__get($def_atributo_rel->nome);
                $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
                $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
                $sql_id = 'id_'.md5($sql_tabela.'.'.$sql_atributo_rel);

                // Forte
                if ($def_atributo_rel->forte) {
                    $this->constraint[$sql_constraint] = "ALTER TABLE {$sql_tabela} ADD\n".
                                                         "  CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                                         "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                                         "      ON DELETE CASCADE\n".
                                                         "      ON UPDATE CASCADE";

                // Fraco
                } else {

                    // Se o relacionamento e' com a mesma tabela: ignorar constraint
                    if ($objeto->get_tabela() == $obj_ref->get_tabela()) {
                        continue;
                    }

                    $this->constraint[$sql_constraint] = "ALTER TABLE {$sql_tabela} ADD\n".
                                                         "  CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                                         "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                                         "      ON DELETE SET NULL\n".
                                                         "      ON UPDATE SET NULL";
                }
            }
        }
        return true;
    }


    //
    //     Encerra a criacao de tabelas
    // 
    public function encerrar_criacao_tabelas($vt_objetos) {
    // Array[Objeto] $vt_objetos: vetor de objetos de entidades
    //
        $r = true;
        foreach ($this->constraint as $sql) {
            $r = $r && $this->consultar($sql);
        }
        return $r;
    }


    //
    //     INSERT: Insere um registro no BD e retorna o ultimo ID inserido
    //
    public function insert($objeto, $dados) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Object $dados: dados a serem inseridos
    //
        // Obter SQL
        $sql = $this->sql_insert($objeto, $dados);
        if (!$sql) {
            return false;
        }

        // Consultar
        $retorno = false;
        $conectou = false;
        if (!$this->conexao) {
            $conectou = $this->conectar();
        }
        $resultado = $this->consultar($sql);
        if ($this->registros_atingidos($resultado) > 0) {
            $chave = $objeto->get_chave();
            $obj = $this->fetch_object($resultado);
            $this->ultimo_insert[$objeto->get_tabela()] = $obj->$chave;
            $retorno = $this->get_ultimo_insert($objeto);
        }
        if ($conectou) {
            $this->desconectar();
        }
        return $retorno;
    }


    //
    //     Indica se o resultado e' valido ou nao
    //
    final public function resultado_valido($resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        $classe = 'PDOStatement';
        return ($resultado instanceof $classe);
    }


    //
    //     Retorna a ultima chave primaria retornada por um insert
    //
    public function get_ultimo_insert($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        if (isset($this->ultimo_insert[$objeto->get_tabela()])) {
            return $this->ultimo_insert[$objeto->get_tabela()];
        }
        return 0;
    }


    //
    //     INSERT: Gera uma SQL de uma consulta INSERT
    //
    public function sql_insert($objeto, $dados) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Object $dados: dados a serem inseridos
    //
        $sql_chave = $this->delimitar_campo($objeto->get_chave());
        return parent::sql_insert($objeto, $dados).' RETURNING '.$sql_chave;
    }


    //
    //     DROP TABLE: Gera uma SQL de remocao de uma tabela do BD
    //
    public function sql_drop_table($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Montar SQL
        $sql = "DROP TABLE {$sql_tabela} CASCADE";
        return $sql;
    }


    //
    //     CREATE TABLE: Gera uma SQL de criacao de uma nova tabela no BD
    //
    public function sql_create_table($objeto, $charset = 'UTF-8') {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $charset: codificacao da tabela
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // SQLs extras
        $extras = array();

        $sql_descricao = texto::decodificar($objeto->get_tabela(true));
        $extras[] = "COMMENT ON TABLE {$sql_tabela} IS '{$sql_descricao}'";

        // Percorrer cada campo do objeto
        $vt_campos = array();
        $vt_constraint = array();
        foreach ($objeto->get_atributos() as $def_atributo) {
            $sql_campo = $this->delimitar_campo($def_atributo->nome);
            $sql_tipo  = $this->gerar_sql_tipo($def_atributo);

            // Checar se e' unico
            if ($def_atributo->unico) {
                $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE ({$sql_campo})";

            // Checar se e' uma chave candidata
            } elseif ($def_atributo->chave == 'CK') {
                $sql_id = 'id_'.md5($sql_tabela.'.'.$sql_campo);
                $extras[] = "CREATE INDEX {$sql_id} ON {$sql_tabela} ({$sql_campo})";
            }

            // Se nao e' a chave primaria
            if ($def_atributo->chave != 'PK') {

                // Se e' um relacionamento
                if ($objeto->possui_rel_uu($def_atributo->nome, false)) {
                    $def_atributo_rel = $objeto->get_definicao_rel_uu($def_atributo->nome, false);
                    if ($def_atributo_rel->forte) {
                        $sql_nulo = ' NOT NULL';
                        $sql_default = '';
                    } else {
                        $sql_nulo = ' NULL';
                        $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                    }
                } else {
                    $sql_nulo = ' NOT NULL';
                    $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                }
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_nulo}{$sql_default}";

            // Se e' a chave primaria
            } else {
                $sql_constraint = 'pk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}";
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} PRIMARY KEY ({$sql_campo})";
            }

            // Comentario do atributo
            $sql_descricao_atributo = texto::strip_acentos(texto::decodificar($def_atributo->descricao));
            if (strpos($sql_descricao_atributo, '&') !== false) {
                trigger_error('Erro de entities no atributo "'.$def_atributo->nome.'" da classe "'.$objeto->get_classe().'"', E_USER_ERROR);
            }
            $extras[] = "COMMENT ON COLUMN {$sql_tabela}.{$sql_campo} IS '{$sql_descricao_atributo}'";
        }

        // Restricoes de relacionamentos externos
        foreach ($objeto->get_definicoes_rel_uu() as $atributo_rel => $def_atributo_rel) {
            $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo_rel);
            $sql_atributo_rel = $this->delimitar_campo($atributo_rel);
            $obj_ref = $objeto->__get($def_atributo_rel->nome);
            $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
            $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
            $sql_id = 'id_'.md5($sql_tabela.'.'.$sql_atributo_rel);

            $extras[] = "CREATE INDEX {$sql_id} ON {$sql_tabela} ({$sql_atributo_rel})";

            // Definir constraints de relacionamentos depois
        }

        // Definir restricoes de chaves unicas compostas
        foreach ($objeto->get_chaves_unicas_compostas() as $chave_uk) {
            $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.implode(',', $chave_uk));
            $vt_campo_uk = array();
            foreach ($chave_uk as $campo_uk) {
                $vt_campo_uk[] = $this->delimitar_campo($campo_uk);
            }
            $sql_campo_uk = implode(', ', $vt_campo_uk);
            $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE ({$sql_campo_uk})";
        }

        $vt_campos = array_merge($vt_campos, $vt_constraint);
        $sql_campos = $this->implode_campos_create_table($vt_campos);

        if ($this->exibicao_usuario) {
            $cabecalho_comentario = "#\n".
                                    "# Tabela ".$objeto->get_tabela()."\n".
                                    "#\n";
        } else {
            $cabecalho_comentario = '';
        }

        $sql = $cabecalho_comentario.
               "CREATE TABLE {$sql_tabela} (\n".
               "{$sql_campos}\n".
               ")";

        $sqls = array_merge(array($sql), $extras);

        return $sqls;
    }


    //
    //     Gera o tipo adequado ao atributo
    //
    protected function gerar_sql_tipo($atributo) {
    // atributo $atributo: objeto com as caracteristicas do atributo de uma entidade
    //
        switch ($atributo->tipo) {
        case 'int':

            $max = max(abs($atributo->maximo), abs($atributo->minimo));
            if ((!is_numeric($atributo->maximo)) && (!is_numeric($atributo->minimo))) {
                return ($atributo->chave == 'PK') ? 'SERIAL' : 'INTEGER';

            } elseif ($max >= pow(2, 4 * 8 - 1)) {
                return ($atributo->chave == 'PK') ? 'BIGSERIAL' : 'BIGINT';
            } elseif ($max >= pow(2, 2 * 8 - 1)) {
                return ($atributo->chave == 'PK') ? 'SERIAL' : 'INTEGER';
            } else {
                return ($atributo->chave == 'PK') ? 'SERIAL' : 'SMALLINT';
            }
            break;
        case 'float':
            return 'DOUBLE PRECISION';
        case 'string':
            $max = max(abs($atributo->maximo), abs($atributo->minimo));
            if ($atributo->maximo > 255) {
                return 'TEXT';
            } elseif (is_numeric($atributo->minimo) && is_numeric($atributo->maximo)) {
                if ($atributo->minimo == $atributo->maximo) {
                    return "CHARACTER({$max})";
                }
            }
            return "CHARACTER VARYING({$max})";
        case 'char':
            return 'CHAR(1)';
        case 'bool':
            return 'BOOLEAN';
        case 'binario':
            return 'BYTEA';
        case 'data':
            switch ($atributo->campo_formulario) {
            case 'data':
                return 'DATE';
            case 'hora':
                return 'TIME';
            case 'data_hora':
            default:
                return 'TIMESTAMP';
            }
        }
        trigger_error('Tipo desconhecido ('.util::exibir_var($atributo->tipo).')', E_USER_WARNING);
        return false;
    }


    //
    //     Filtra um atributo do BD para o PHP
    //
    public function filtrar_atributo_bd($atributo, $valor) {
    // atributo $atributo: definicao do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo->tipo) {
        case 'bool':
            return $valor == 't';
        }
        return parent::filtrar_atributo_bd($atributo, $valor);
    }


    //
    //     Formata uma data para ser inserida no BD
    //
    public function formatar_data($data, $tipo = 'data_hora') {
    // String $data: data no formato dd-mm-aaaa-HH-MM-SS
    // String $tipo: 'data_hora', 'data', 'hora'
    //
        $vt_data = explode('-', $data);
        switch ($tipo) {
        case 'data':
            return sprintf('%04d-%02d-%02d',
                           isset($vt_data[2]) ? $vt_data[2] : 0,
                           isset($vt_data[1]) ? max((int)$vt_data[1], 1) : 1,
                           isset($vt_data[0]) ? max((int)$vt_data[0], 1) : 1
                          );

        case 'hora':
            return sprintf('%02d:%02d:%02d',
                           isset($vt_data[3]) ? $vt_data[3] : 0,
                           isset($vt_data[4]) ? $vt_data[4] : 0,
                           isset($vt_data[5]) ? $vt_data[5] : 0
                          );

        case 'data_hora':
        default:
            return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
                           isset($vt_data[2]) ? $vt_data[2] : 0,
                           isset($vt_data[1]) ? max((int)$vt_data[1], 1) : 1,
                           isset($vt_data[0]) ? max((int)$vt_data[0], 1) : 1,
                           isset($vt_data[3]) ? $vt_data[3] : 0,
                           isset($vt_data[4]) ? $vt_data[4] : 0,
                           isset($vt_data[5]) ? $vt_data[5] : 0
                          );
        }
    }


    //
    //     Desformata uma data obtida do BD
    //
    public function desformatar_data($data_bd, $tipo = 'data_hora') {
    // String $data_bd: data no formato do BD
    // String $tipo: 'data_hora', 'data', 'hora'
    //
        switch ($tipo) {
        case 'data':
            sscanf($data_bd, '%d-%d-%d',
                             $ano, $mes, $dia);

            if ((int)$ano == 0) {
                return '00-00-0000-00-00-00';
            }
            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           $dia, $mes, $ano,
                           0, 0, 0);

        case 'hora':
            sscanf($data_bd, '%d:%d:%d',
                             $hora, $minuto, $segundo);

            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           0, 0, 0,
                           $hora, $minuto, $segundo);

        case 'data_hora':
        default:
            sscanf($data_bd, '%d-%d-%d %d:%d:%d',
                             $ano, $mes, $dia,
                             $hora, $minuto, $segundo);
            if ((int)$ano == 0) {
                return sprintf('00-00-0000-%02d-%02d-%02d',
                               $hora, $minuto, $segundo);
            }
            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           $dia, $mes, $ano,
                           $hora, $minuto, $segundo);
        }
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
        $sql = "TRUNCATE TABLE {$sql_tabela} CASCADE"; // TRUNCATE TABLE %s CASCADE

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
        return '\connect '.$bd;
    }

}//class
