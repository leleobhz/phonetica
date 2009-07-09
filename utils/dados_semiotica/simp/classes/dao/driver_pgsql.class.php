<?php
//
// SIMP
// Descricao: Driver de conexao com o PostgreSQL usando funcoes da extensao pgsql para PHP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.7
// Data: 01/08/2008
// Modificado: 06/07/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class driver_pgsql extends driver_base {
    const DELIMITADOR_TABELA  = '';
    const DELIMITADOR_CAMPO   = '';
    const DELIMITADOR_VALOR   = "'";
    const DELIMITADOR_FUNCAO  = '';
    const IDENTIFICADOR_CAMPO = '.';


    //
    //     Monta o nome completo de um campo (tabela.campo)
    //
    public function montar_nome_campo($tabela, $campo) {
    // String $tabela: nome da tabela
    // String $campo: nome do campo
    //
        return $this->delimitar_tabela($tabela).
               self::IDENTIFICADOR_CAMPO.
               $this->delimitar_campo($campo);
    }


    //
    //     Delimita o nome de uma tabela
    //
    public function delimitar_tabela($tabela) {
    // String $tabela: nome da tabela
    //
        return $tabela;
    }


    //
    //     Delimita o nome de um campo
    //
    public function delimitar_campo($campo) {
    // String $campo: nome do campo
    //
        return $campo;
    }


    //
    //     Delimita um valor
    //
    public function delimitar_valor($valor) {
    // String $valor: valor qualquer
    //
        return self::DELIMITADOR_VALOR.
               $this->limpar_valor($valor).
               self::DELIMITADOR_VALOR;
    }


    //
    //     Delimita o nome de uma funcao
    //
    public function delimitar_funcao($funcao) {
    // String $funcao: nome da funcao
    //
        return $funcao;
    }


    //
    //     Retorna o nome do SGBD
    //
    public function get_nome() {

        // Se ja foi consultado: retorna-lo
        if ($this->nome) {
            return $this->nome;
        }
        $this->nome = 'PostgreSQL';
        return $this->nome;
    }


    //
    //     Retorna a versao do SGBD
    //
    public function get_versao() {

        // Se ja foi consultada: retorna-la
        if ($this->versao) {
            return $this->versao;
        }
        $this->versao = 0;

        $conectou = false;
        if (!$this->conexao) {
            $conectou = $this->conectar();
            if (!$conectou) {
                $this->adicionar_erro('Erro ao obter vers&atilde;o do SGBD');
                return false;
            }
        }
        $v = pg_version($this->conexao);
        if (isset($v['server'])) {
            $this->versao = $v['server'];
        } elseif (isset($v['client'])) {
            $this->versao = $v['client'];
        } else {
            trigger_error("Erro ao identificar a versao do SGBD", E_USER_WARNING);
        }
        if ($conectou !== false) {
            $this->desconectar();
        }
        return $this->versao;
    }


    //
    //     Retorna o valor da versao exigida
    //
    public function get_versao_exigida() {
        return '8.2.0';
    }


    //
    //     Conecta ao banco de dados e retorna uma conexao valida
    //
    public function conectar($base = null, $persistente = true) {
    // String || Bool $base: nome do BD a ser usado, false para nao usar nenhum, null para usar o padrao
    // Bool $persistente: abre uma conexao persistente
    //
        // Se ja tem uma conexao aberta
        if ($this->conexao) {
            return $this->conexao;
        }

        $dbname = '';
        if ($base) {
            $dbname = " dbname={$base}";
        } elseif (OBJETO_DAO_BASE) {
            $dbname = ' dbname='.OBJETO_DAO_BASE;
        }

        if ($this->servidor == 'localhost') {
            $str_conexao = "host=localhost port={$this->porta}{$dbname} user={$this->usuario} password={$this->senha}";
        } else {
            $str_conexao = "host={$this->servidor} port={$this->porta}{$dbname} user={$this->usuario} password={$this->senha}";
        }

        if ($persistente) {
            $this->conexao = pg_connect($str_conexao);
        } else {
            $this->conexao = pg_connect($str_conexao);
        }
        if (!$this->conexao) {
            $erro = "Erro ao conectar no PostgreSQL (String de conexao: \"{$str_conexao}\")";
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro("Erro ao conectar como \"{$this->usuario}\"");
            return false;
        }
        return $this->conexao;
    }


    //
    //     Desconecta do banco de dados
    //
    public function desconectar() {
        $retorno = true;
        if ($this->conexao && !$this->transacao) {
            $retorno = pg_close($this->conexao);
            $this->conexao = false;
        }
        return $retorno;
    }


    //
    //     Obtem o charset correspondente no SGBD
    //
    public function get_charset($charset) {
    // String $charset: charset a ser convertido
    //
        switch (strtolower($charset)) {
        case 'utf-8':      return 'UNICODE';
        case 'iso-8859-1': return 'LATIN1';
        }
        trigger_erro('Codificacao desconhecida ('.util::exibir_var($charset).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o usuario root
    //
    public function get_root() {
        return 'postgres';
    }


    //
    //     Define o modo de transacao
    //
    protected function set_modo_transacao($modo) {
    // Int $modo: codigo do modo de transacao
    //
        switch ($modo) {
        case DRIVER_BASE_READ_UNCOMMITTED:
            return $this->consultar('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
        case DRIVER_BASE_READ_COMMITTED:
            return $this->consultar('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        case DRIVER_BASE_REPEATABLE_READ:
            return $this->consultar('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        case DRIVER_BASE_SERIALIZABLE:
            return $this->consultar('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        }
        trigger_error('Modo de transacao invalido "'.$modo.'"', E_USER_WARNING);
        return false;
    }


    //
    //     Inicia uma transacao
    //
    public function inicio_transacao($modo = DRIVER_BASE_REPEATABLE_READ) {
    // Int $modo: codigo do modo de transacao
    //

        // Se a transacao ja foi aberta
        if ($this->transacao) {
            return true;
        }

        // Conectar
        if (!$this->conectar()) {
            $this->adicionar_erro('Erro ao iniciar transa&ccedil;&atilde;o (conexao)');
            return false;
        }

        $this->transacao = $this->consultar('START TRANSACTION');
        $this->set_modo_transacao($modo);

        if (!$this->transacao) {
            $this->adicionar_erro('Erro ao iniciar transa&ccedil;&atilde;o (start transaction)');
            return false;
        }

        // Se conseguiu iniciar a transacao, zerar o numero de erros
        $this->erros_transacao = 0;
        return true;
    }


    //
    //     Aceia ou rejeita uma transacao
    //
    public function fim_transacao($rollback = false) {
    // Bool $rollback: forca a execucao de um ROLLBACK
    //
        // Se a transacao nao foi iniciada
        if (!$this->transacao) {
            $erro = 'Nenhuma transacao foi iniciada';
            trigger_error($erro, E_USER_NOTICE);
            $this->adicionar_erro($erro);
            return false;
        }

        $resultado = false;

        // Se ocorreram erros: rejeitar
        if ($this->erros_transacao || $rollback) {
            $resultado = $this->consultar('ROLLBACK');

        // Se nao ocorreram erros: aceitar
        } else {
            $resultado = $this->consultar('COMMIT');
        }

        // Voltar o modo de transacao para o padrao
        $this->set_modo_transacao(DRIVER_BASE_REPEATABLE_READ);

        $this->transacao = false; // Fechar a transacao
        $this->desconectar();     // Desconectar
        return $resultado;
    }


    //
    //     Retorna o ultimo erro no SGBD
    //
    public function get_ultimo_erro(&$str_erro = '', &$cod_erro = '0') {
    // String $str_erro: erro a ser obtido na forma de texto
    // Int $cod_erro: erro a ser obtido na forma de codigo numerico
    //
        if ($this->conexao) {
            $str_erro = pg_last_error($this->conexao);
            $cod_erro = 0;
        }
        return "Erro {$cod_erro}: {$str_erro}";
    }


    //
    //     Converte um registro para objeto
    //
    public function fetch_object($resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        if (is_resource($resultado)) {
            return pg_fetch_object($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o numero de resultados de um resource
    //
    public function quantidade_registros($resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        if (is_resource($resultado)) {
            return pg_num_rows($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o numero de registros atingidos na ultima consulta
    //
    public function registros_atingidos($resultado) {
    // Resource $resultado: resultado a ser analisado
    //
        if ($this->conexao) {
            return pg_affected_rows($resultado);
        }
        trigger_error('Nenhuma conexao foi aberta para obter o numero de registros atingidos', E_USER_NOTICE);
        return false;
    }


    //
    //     Libera a memoria do resultado
    //
    public function liberar_resultado(&$resultado) {
    // Resource $resultado: resultado a ser apagado
    //
        if (is_resource($resultado)) {
            return pg_free_result($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return;
    }


    //
    //     Realiza uma consulta no banco de dados
    //
    public function query($sql) {
    // String $sql: Comando SQL a ser executado
    //
        if ($this->conexao) {
            return pg_query($this->conexao, $sql);
        }
        trigger_error('Nenhuma conexao foi aberta para executar a query ('.$sql.')', E_USER_NOTICE);
        return false;
    }


    //
    //     Limpa um valor
    //
    public function limpar_valor($valor) {
    // Mixed $valor: valor a ser limpo
    //
        if (is_bool($valor)) {
            $valor = $valor ? '1' : '0';
        } else {
            $padrao = setlocale(LC_NUMERIC, 0);
            $mudou = false;
            if ($padrao != 'C' && setlocale(LC_NUMERIC, 'C')) {
                $mudou = true;
            }
            if (function_exists('pg_escape_string')) {
                $valor = pg_escape_string($valor);
            } else {
                $valor = addslashes($valor);
            }
            if ($mudou) {
                setlocale(LC_NUMERIC, $padrao);
            }
        }
        return $valor;
    }


    //
    //     Valida um nome de usuario
    //
    public function validar_usuario($usuario, &$erros) {
    // String $usuario: nome do usuario a ser validado
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        if (!preg_match('/^[A-z_]+$/', $usuario)) {
            $erros[] = 'Usu&aacute;rio do BD possui caracteres inv&aacute;lidos';
            return false;
        }
        return true;
    }


    //
    //     Reinicia o auto_increment na posicao especificada
    //
    public function reiniciar_contagem($tabela, $chave, $posicao = 1) {
    // String $tabela: tabela que sera reiniciada
    // String $chave: nome da chave auto_increment
    // Int $posicao: posicao a ser definida
    //
        // Obter SQL
        $sql = $this->sql_reiniciar_contagem($tabela, $chave, $posicao);

        // Consultar
        return (bool)$this->consultar($sql);
    }


    //
    //     Gera a SQL de reiniciar a contagem de um campo auto_increment de uma tabela
    //
    public function sql_reiniciar_contagem($tabela, $chave, $posicao) {
    // String $tabela: tabela que sera reiniciada
    // String $chave: nome da chave auto_increment
    // Int $posicao: posicao a ser definida
    //
        $sql_tabela  = "'".$tabela."'";
        $sql_chave   = "'".$chave."'";
        $sql_posicao = abs(round($posicao));

        $sql = "SELECT setval(pg_get_serial_sequence({$sql_tabela}, {$sql_chave}), {$sql_posicao}, false)";
        return $sql;
    }

}//class
