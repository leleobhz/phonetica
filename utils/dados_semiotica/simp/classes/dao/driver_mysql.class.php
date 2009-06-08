<?php
//
// SIMP
// Descricao: Driver de conexao com o MySQL usando funcoes da extensao mysql para PHP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.5
// Data: 17/04/2008
// Modificado: 03/06/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class driver_mysql extends driver_base {
    const DELIMITADOR_TABELA  = '`';
    const DELIMITADOR_CAMPO   = '`';
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
        return self::DELIMITADOR_TABELA.
               str_replace(self::DELIMITADOR_TABELA, '', $tabela).
               self::DELIMITADOR_TABELA;
    }


    //
    //     Delimita o nome de um campo
    //
    public function delimitar_campo($campo) {
    // String $campo: nome do campo
    //
        return self::DELIMITADOR_CAMPO.
               str_replace(self::DELIMITADOR_CAMPO, '', $campo).
               self::DELIMITADOR_CAMPO;
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
        if (self::DELIMITADOR_FUNCAO) {
            return self::DELIMITADOR_FUNCAO.
                   str_replace(self::DELIMITADOR_FUNCAO, '', $funcao).
                   self::DELIMITADOR_FUNCAO;
        }
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
        $this->nome = 'MySQL';
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
        $this->versao = mysql_get_server_info();
        if ($conectou !== false) {
            $this->desconectar();
        }
        return $this->versao;
    }


    //
    //     Retorna o valor da versao exigida
    //
    public function get_versao_exigida() {
        return '5.0.0';
    }


    //
    //     Conecta ao banco de dados e retorna uma conexao valida
    //
    public function conectar($base = null, $persistente = true) {
    // String || Bool $base: nome do BD a ser usado, false para nao usar nenhum, null para usar o padrao
    // Bool $persistente: abre uma conexao persistente
    //
        $base = is_null($base) ? $this->base : $base;

        // Se ja tem uma conexao aberta
        if ($this->conexao) {
            return $this->conexao;
        }

        $servidor = $this->servidor.($this->porta ? ':'.$this->porta : '');
        if ($persistente) {
            $this->conexao = mysql_pconnect($servidor, $this->usuario, $this->senha);
        } else {
            $this->conexao = mysql_connect($servidor, $this->usuario, $this->senha);
        }
        if ($this->conexao && $base) {
            $selecionou_base = mysql_select_db($base, $this->conexao);
        } else {
            $selecionou_base = true;
        }
        if (!($this->conexao && $selecionou_base)) {
            $this->get_ultimo_erro($str_erro, $cod_erro);
            $erro = "Erro ao conectar como \"{$this->usuario}\" (Erro {$cod_erro}: {$str_erro})";
            trigger_error($erro, E_USER_WARNING);
            $this->adicionar_erro($erro);
            $this->desconectar();
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
            $retorno = mysql_close($this->conexao);
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
        case 'utf-8':      return 'utf8';
        case 'iso-8859-1': return 'latin1';
        }
        trigger_error('Codificacao desconhecida ('.util::exibir_var($charset).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o collate correspondente no SGBD
    //
    public function get_collate($charset) {
    // String $charset: codificacao utilizada
    //
        switch (strtolower($charset)) {
        case 'utf-8':      return 'utf8_general_ci';
        case 'iso-8859-1': return 'latin1_general_ci';
        }
        trigger_error('Codificacao desconhecida ('.util::exibir_var($charset).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o usuario root
    //
    public function get_root() {
        return 'root';
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

        $this->set_modo_transacao($modo);
        $this->transacao = $this->consultar('START TRANSACTION');

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
            $str_erro = mysql_error($this->conexao);
            $cod_erro = mysql_errno($this->conexao);
        } else {
            $str_erro = mysql_error();
            $cod_erro = mysql_errno();
        }
        return "Erro {$cod_erro}: {$str_erro}";
    }


    //
    //     Converte um registro para objeto
    //
    public function fetch_object(&$resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        if (is_resource($resultado)) {
            return mysql_fetch_object($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o numero de resultados de um resource
    //
    public function quantidade_registros(&$resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        if (is_resource($resultado)) {
            return mysql_num_rows($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o numero de registros atingidos na ultima consulta
    //
    public function registros_atingidos(&$resultado) {
    // Resource $resultado: resultado a ser analisado
    //
        if ($this->conexao) {
            return mysql_affected_rows($this->conexao);
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
            return mysql_free_result($resultado);
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
            return mysql_query($sql, $this->conexao);
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

            $conectou = false;
            if (!$this->conexao) {
                $conectou = $this->conectar();
            }
            $valor = mysql_real_escape_string($valor, $this->conexao);
            if ($conectou) {
                $this->desconectar();
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
        if (strlen($usuario) > 16) {
            $erros[] = 'Usu&aacute;rio do BD deve ter tamanho m&aacute;ximo de 16 caracteres';
            return false;
        } elseif (!preg_match('/^[A-z_]+$/', $usuario)) {
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
        $sql_tabela  = $this->delimitar_tabela($tabela);
        $sql_posicao = abs(round($posicao));

        $sql = "ALTER TABLE {$sql_tabela} AUTO_INCREMENT = {$sql_posicao}";
        return $sql;
    }

}//class
