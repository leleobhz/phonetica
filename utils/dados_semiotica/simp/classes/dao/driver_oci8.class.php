<?php
//
// SIMP
// Descricao: Driver de conexao com o Oracle usando funcoes da extensao oci8 para PHP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 09/09/2008
// Modificado: 04/11/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class driver_oci8 extends driver_base {
    public $ultima_consulta = null;

    const DELIMITADOR_TABELA  = '"';
    const DELIMITADOR_CAMPO   = '"';
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
        $this->nome = 'Oracle';
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
        $versao = oci_server_version($this->conexao);
        if (preg_match('/^.*[\040](([\d]+\.)+[\d]+)[\040].*$/', $versao, $match)) {
            $this->versao = $match[1];
        } else {
            $this->versao = '8+';
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
        return '10.0.0';
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

        if (defined('ORACLE_HOME')) {
            putenv('ORACLE_HOME='.ORACLE_HOME);
        }

        if ($persistente) {
            $this->conexao = oci_pconnect($this->usuario, $this->senha, $base);
        } else {
            $this->conexao = oci_connect($this->usuario, $this->senha, $base);
        }
        if (!$this->conexao) {
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
            $retorno = oci_close($this->conexao);
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
        case 'utf-8':      return 'AL32UTF8';
        case 'iso-8859-1': return '?';//TODO
        }
        trigger_erro('Codificacao desconhecida ('.util::exibir_var($charset).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Obtem o usuario root
    //
    public function get_root() {
        return 'sys';
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
        if (!$this->conectar(null, false)) {
            $this->adicionar_erro('Erro ao iniciar transa&ccedil;&atilde;o (conexao)');
            return false;
        }

        $this->set_modo_transacao($modo);

        // Nao precisa
        $this->transacao = true;
        
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
            $resultado = oci_rollback($this->conexao);
                
        // Se nao ocorreram erros: aceitar
        } else {
            $resultado = oci_commit($this->conexao);
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
            if ($this->ultima_consulta) {
                $vt_erro = oci_error($this->ultima_consulta);
                $str_erro = $vt_erro['message'].' / SQL: '.$vt_erro['sqltext'].' / Offset: '.$vt_erro['offset'];
                $cod_erro = $vt_erro['code'];
            } else {
                $vt_erro = oci_error($this->conexao);
                $str_erro = $vt_erro['message'].' / SQL: '.$vt_erro['sqltext'].' / Offset: '.$vt_erro['offset'];
                $cod_erro = $vt_erro['code'];
            }
        } else {
            $vt_erro = oci_error();
            $str_erro = $vt_erro['message'].' / SQL: '.$vt_erro['sqltext'].' / Offset: '.$vt_erro['offset'];
            $cod_erro = $vt_erro['code'];
        }
        return "Erro: ".$str_erro;
    }


    //
    //     Converte um registro para objeto
    //
    public function fetch_object($resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        if (is_resource($resultado)) {
            return oci_fetch_object($resultado);
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
            $i = 0;
            while (oci_fetch_object($resultado)) { $i++; }
            return $i;
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
        if (is_resource($resultado)) {
            return oci_num_rows($resultado);
        }
        trigger_error('Tipo invalido para o parametro resultado ('.util::get_tipo($resultado).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Libera a memoria do resultado
    //
    public function liberar_resultado(&$resultado) {
    // Resource $resultado: resultado a ser apagado
    //
        if (is_resource($resultado)) {
            return oci_free_statement($resultado);
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
            $consulta = oci_parse($this->conexao, $sql);

            // Se esta' em transacao: nao fazer auto-commit
            if ($this->transacao) {
                $flag = OCI_DEFAULT;

            // Se nao esta' em transacao: fazer auto-commit
            } else {
                $flag = OCI_COMMIT_ON_SUCCESS;
            }

            // Guardar a ultima consulta
            $this->ultima_consulta = $consulta;

            if (!oci_execute($consulta, $flag)) {
                return false;
            }
            return $consulta;
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
            $valor = str_replace("'", "''", $valor);
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
        if (strlen($usuario) > 30) {
            $erros[] = 'Usu&aacute;rio do BD deve ter tamanho m&aacute;ximo de 30 caracteres';
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

//TODO
        $sql = "ALTER TABLE {$sql_tabela} AUTO_INCREMENT = {$sql_posicao}";
        return $sql;
    }

}//class
