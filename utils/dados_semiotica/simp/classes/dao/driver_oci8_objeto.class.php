<?php
//
// SIMP
// Descricao: Consultas alto nivel ao banco de dados Oracle
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 09/09/2008
// Modificado: 29/07/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class driver_oci8_objeto extends driver_objeto {
    private $ultimo_insert;

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
        parent::__construct('oci8', $servidor, $porta, $usuario, $senha, $base);
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
//TODO
        trigger_error('Driver Oracle nao suporte a funcao "'.$funcao.'"', E_USER_WARNING);
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
    //     CREATE TABLE: Gera uma SQL de criacao de uma nova tabela no BD
    //
    public function sql_create_table($objeto, $charset = 'UTF-8') {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $charset: codificacao da tabela
    //
        static $num_tabela = 0;
        $extras = array();

        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        $sql_descricao = $objeto->get_tabela(true);
        $extras[] = "COMMENT ON TABLE {$sql_tabela} IS '{$sql_descricao}'";

        // Percorrer cada campo do objeto
        $vt_campos = array();
        $vt_constraint = array();
        foreach ($objeto->get_atributos() as $def_atributo) {
            $sql_campo = $this->delimitar_campo($def_atributo->nome);
            $sql_tipo  = $this->gerar_sql_tipo($def_atributo);

            // Checar se e' unico
            if ($def_atributo->unico && $def_atributo->chave != 'PK') {
                $sql_constraint = 'uk_'.$num_tabela.'_'.count($vt_constraint);
                $vt_constraint[$sql_constraint] = "  CONSTRAINT \"{$sql_constraint}\" UNIQUE ({$sql_campo}) ENABLE";
            }

            if ($def_atributo->chave != 'PK') {
                $sql_nulo = ' NOT NULL';
                if (!$objeto->possui_rel_uu($def_atributo->nome, false)) {
                    $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                } else {
                    $sql_default = '';
                }
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_default}{$sql_nulo} ENABLE";
            } else {
                $pk = $def_atributo->nome;
                $sql_constraint = 'pk_'.$num_tabela.'_'.count($vt_constraint);
                $sql_nulo    = ' NOT NULL';
                $sql_default = '';
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_nulo}{$sql_default} ENABLE";
                $vt_constraint[$sql_constraint] = "  CONSTRAINT \"{$sql_constraint}\" PRIMARY KEY ({$sql_campo}) ENABLE";
            }

            // Comentario do atributo
            $sql_descricao_atributo = texto::decodificar($def_atributo->descricao);
            $extras[] = "COMMENT ON COLUMN {$sql_tabela}.{$sql_campo} IS '{$sql_descricao_atributo}'";
        }

        // Restricoes de relacionamentos externos
        foreach ($objeto->get_definicoes_rel_uu() as $atributo_rel => $def_atributo_rel) {

            // Se e' um relacionamento fraco, nao e' constraint
            if (!$def_atributo_rel->forte) {
                continue;
            }

            $sql_constraint = 'fk_'.$num_tabela.'_'.count($vt_constraint);
            $sql_atributo_rel = $this->delimitar_campo($atributo_rel);
            $obj_ref = $objeto->__get($def_atributo_rel->nome);
            $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
            $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
            $vt_constraint[$sql_constraint] = "  CONSTRAINT \"{$sql_constraint}\" FOREIGN KEY ({$sql_atributo_rel})\n".
                                              "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                              "    ON DELETE CASCADE\n".
                                              "    ENABLE";
        }

        // Definir restricoes de chaves unicas compostas
        foreach ($objeto->get_chaves_unicas_compostas() as $chave_uk) {
            $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.implode(',', $chave_uk));
            $vt_campo_uk = array();
            foreach ($chave_uk as $campo_uk) {
                $vt_campo_uk[] = $this->delimitar_campo($campo_uk);
            }
            $sql_campo_uk = implode(', ', $vt_campo_uk);
            $sql_constraint = 'uk_'.$num_tabela.'_'.count($vt_constraint);
            $vt_constraint[$sql_constraint] = "  CONSTRAINT \"{$sql_constraint}\" UNIQUE ({$sql_campo_uk}) ENABLE";
        }

        $vt_campos = array_merge($vt_campos, $vt_constraint);

        $sql_campos = implode(",\n", $vt_campos);

        $vt_sql = array();

        if ($this->exibicao_usuario) {
            $cabecalho_comentario = "--\n".
                                    "-- Tabela ".$objeto->get_tabela()."\n".
                                    "--\n";
        } else {
            $cabecalho_comentario = '';
        }


        // Montar SQL
        $vt_sql[] = $cabecalho_comentario.
                    "CREATE TABLE {$sql_tabela} (\n".
                    "{$sql_campos}\n".
                    ")";

        if (isset($pk)) {

            // Sequence
            $nome_sequence = $objeto->get_tabela().'_pk_seq';
            $sql_existe_sequence = 'SELECT COUNT(*) AS "existe" FROM user_sequences '.
                                   "WHERE sequence_name = '{$nome_sequence}'";
            $resultado_sequence = $this->consultar($sql_existe_sequence);
            $sequence = $this->fetch_object($resultado_sequence);
            if ($sequence->existe) {
                $vt_sql[] = 'DROP SEQUENCE "'.$nome_sequence.'"';
            }
            $vt_sql[] = 'CREATE SEQUENCE "'.$nome_sequence.'" '.
                        'START WITH 1 '.
                        'INCREMENT BY 1 '.
                        'NOMAXVALUE';

            // Trigger
            $nome_trigger = $objeto->get_tabela().'_pk_trigger';
            $vt_sql[] = "CREATE OR REPLACE TRIGGER \"{$nome_trigger}\"\n".
                        "  BEFORE INSERT ON ".$this->delimitar_tabela($objeto->get_tabela())."\n".
                        "  FOR EACH ROW\n".
                        "BEGIN\n".
                        '  SELECT "'.$nome_sequence.'".nextval INTO :new.'.$this->delimitar_campo($pk)." FROM dual;\n".
                        'END;';
        }
        foreach ($extras as $extra) {
            $vt_sql[] = $extra;
        }

        $num_tabela++;
        return $vt_sql;
    }


    //
    //     Gera o tipo adequado ao atributo
    //
    protected function gerar_sql_tipo($atributo) {
    // atributo $atributo: objeto com as caracteristicas do atributo de uma entidade
    //
        switch ($atributo->tipo) {
        case 'int':
            return 'NUMBER';
        case 'float':
            $max = max(abs($atributo->maximo), abs($atributo->minimo));
            if (is_numeric($atributo->minimo) && ($atributo->minimo >= 0)) {
                $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 10;
                $casas_decimais = min(53, $atributo->casas_decimais);
                $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                return 'NUMBER'; //.'('.$casas_decimais.')';
            } else {
                $casas_decimais = ($atributo->casas_decimais !== false) ? $atributo->casas_decimais : 10;
                $casas_decimais = min(53, $atributo->casas_decimais);
                $casas_decimais = $atributo->casas_decimais ? '('.(strlen(round($max)) + $casas_decimais).','.$casas_decimais.')' : '';
                return 'NUMBER'; // .'('.$casas_decimais.')';
            }
            break;
        case 'string':
            if (is_numeric($atributo->maximo) && $atributo->maximo > 4000) {
                return 'BLOB';
            }

            $max = round(max(abs($atributo->maximo), abs($atributo->minimo)));
            $str_max = number_format($max, 0, '.', '');
            
            if (is_numeric($atributo->minimo) && 
                is_numeric($atributo->maximo) &&
                ($atributo->minimo == $atributo->maximo)) {
                return "NCHAR({$str_max})";
            }
            
            // VARCHAR(?)
            return "NVARCHAR2({$str_max})";
        case 'char':
            return 'NCHAR(1)';
        case 'bool':
            return 'CHAR(1)';
        case 'binario':
            $max = round(max(abs($atributo->maximo), abs($atributo->minimo)));
            return 'BLOB';
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
        return '';
    }


}//class
