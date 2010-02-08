<?php
//
// SIMP
// Descricao: Classe base de conexoes com Bancos de Dados
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.11
// Data: 17/04/2008
// Modificado: 19/10/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Modos de transacao
define('DRIVER_BASE_READ_UNCOMMITTED', 1);
define('DRIVER_BASE_READ_COMMITTED',   2);
define('DRIVER_BASE_REPEATABLE_READ',  3); // Padrao
define('DRIVER_BASE_SERIALIZABLE',     4);

// Indicacao do modo de transacao padrao
define('DRIVER_BASE_MODO_PADRAO',      DRIVER_BASE_REPEATABLE_READ);

abstract class driver_base {
    protected $conexao           = false;    // ID da conexao estabelecida
    protected $porta             = '';       // Porta de acesso ao servidor
    protected $base              = '';       // Nome do BD
    protected $servidor          = '';       // Endereco do servidor
    protected $usuario           = '';       // Usuario para conexao
    protected $senha             = '';       // Senha do usuario
    protected $transacao         = false;    // Controle de transacoes
    protected $erros_transacao   = 0;        // Controle de erros das transacoes
    protected $erros             = array();  // Vetor de erros internos
    protected $nome              = '';       // Nome do SGBD
    protected $versao            = '';       // Versao do SGBD
    protected $versao_exigida    = '';       // Versao exigida do SGBD
    protected static $instrucoes = 0;        // Quantidade de instrucoes executadas no total


/// @ METODOS EXIGIDOS PELAS CLASSES FILHAS


    //
    //     Monta o nome completo de um campo (Ex.: `tabela`.`campo`)
    //
    abstract public function montar_nome_campo($tabela, $campo);
    // String $tabela: nome da tabela
    // String $campo: nome do campo
    //


    //
    //     Delimita o nome de uma tabela
    //
    abstract public function delimitar_tabela($tabela);
    // String $tabela: nome da tabela
    //


    //
    //     Delimita o nome de um campo
    //
    abstract public function delimitar_campo($campo);
    // String $campo: nome do campo
    //


    //
    //     Delimita um valor
    //
    abstract public function delimitar_valor($valor);
    // String $valor: valor qualquer
    //


    //
    //     Delimita o nome de uma funcao
    //
    abstract public function delimitar_funcao($funcao);
    // String $funcao: nome da funcao
    //


    //
    //     Retorna o nome do SGBD
    //
    abstract public function get_nome();


    //
    //     Retorna a versao do SGBD
    //
    abstract public function get_versao();


    //
    //     Retorna o valor da versao exigida
    //
    abstract public function get_versao_exigida();


    //
    //     Conecta ao banco de dados
    //
    abstract public function conectar($base = null, $persistente = true);
    // String $base: nome do BD a ser usado
    // Bool $persistente: abre uma conexao persistente
    //


    //
    //     Desconecta do banco de dados
    //
    abstract public function desconectar();


    //
    //     Obtem o charset de acordo com o SGBD
    //
    abstract public function get_charset($charset);
    // String $charset: charset a ser convertido
    //


    //
    //     Obtem o usuario root
    //
    abstract public function get_root();


    //
    //     Define o modo de transacao
    //
    abstract protected function set_modo_transacao($modo);
    // Int $modo: codigo do modo de transacao
    //


    //
    //     Inicia uma transacao
    //
    abstract public function inicio_transacao($modo = DRIVER_BASE_MODO_PADRAO);
    // Int $modo: modo de transacao
    //


    //
    //     Aceia ou rejeita uma transacao
    //
    abstract public function fim_transacao($rollback = false);
    // Bool $rollback: forca a execucao de um ROLLBACK
    //


    //
    //     Retorna o ultimo erro no SGBD
    //
    abstract public function get_ultimo_erro(&$str_erro = '', &$cod_erro = '0');
    // String $str_erro: erro ocorrido
    // Int $cod_erro: codigo do erro ocorrido
    //


    //
    //     Converte um registro para objeto
    //
    abstract public function fetch_object($resultado);
    // Resource $resultado: resultado de uma consulta
    //


    //
    //     Obtem o numero de resultados de um resource
    //
    abstract public function quantidade_registros($resultado);
    // Resource $resultado: resultado de uma consulta
    //


    //
    //     Obtem o numero de registros atingidos na ultima consulta
    //
    abstract public function registros_atingidos($resultado);
    // Resource $resultado: resultado a ser analisado
    //


    //
    //     Libera a memoria do resultado
    //
    abstract protected function liberar_resultado(&$resultado);
    // Resource $resultado: resultado a ser apagado
    //


    //
    //     Realiza uma consulta no banco de dados
    //
    abstract public function query($sql);
    // String $sql: Comando SQL a ser executado
    //


    //
    //     Limpa um valor
    //
    abstract protected function limpar_valor($valor);
    // Mixed $valor: valor a ser limpo
    //


    //
    //     Reinicia o auto_increment na posicao especificada
    //
    abstract public function reiniciar_contagem($tabela, $chave, $posicao = 1);
    // String $tabela: tabela que sera reiniciada
    // String $chave: nome da chave auto_increment
    // Int $posicao: posicao a ser definida
    //


/// @ METODOS ESPECIFICOS DA CLASSE


    //
    //     Construtor Padrao
    //
    public function __construct($servidor, $porta, $usuario, $senha, $base = null) {
    // String $servidor: endereco do servidor
    // Int $porta: porta de acesso ao servidor
    // String $usuario: login do usuario no BD ou "[root]" para o usuario root (administrador do SGBD)
    // String $senha: senha para acesso ao BD
    // String $base: nome do BD a ser usado ou 0 para nenhum
    //
        $this->servidor = $servidor;
        $this->porta    = $porta;
        $this->usuario  = $usuario;
        $this->senha    = $senha;
        $this->base     = $base;
        $this->conexao  = false;

        // Se deseja usar o usuario root
        if ($this->usuario == '[root]') {
            $this->usuario = $this->get_root();
        }
    }


    //
    //     Destrutor
    //
    final public function __destruct() {
        if ($this->transacao) {
            $this->fim_transacao();
        }
        $this->desconectar();
    }


    //
    //     GET
    //
    public function __get($atributo) {
    // String $atributo: nome do atributo desejado
    //
        if (isset($this->$atributo)) {
            return $this->$atributo;
        }
        trigger_error('A classe nao possui o atributo "'.util::exibir_var($atributo).'"', E_USER_NOTICE);
        return null;
    }


    //
    //     SET
    //
    public function __set($atributo, $valor) {
    // String $atributo: nome do atributo desejado
    // Mixed $valor: valor a ser atribuido
    //
        if (isset($this->$atributo)) {
            $this->$atributo = $valor;
            return;
        }
        trigger_error('A classe nao possui o atributo "'.util::exibir_var($atributo).'"', E_USER_NOTICE);
    }


    //
    //     CALL
    //
    public function __call($metodo, $atributos) {
    // String $metodo: nome do metodo chamado
    // Array[String => Mixed] $atributos: vetor de atributos informados
    //
        trigger_error("A classe driver_base nao possui o metodo \"{$metodo}\"", E_USER_WARNING);
    }


    //
    //     Adiciona um erro ao objeto
    //
    final public function adicionar_erro($erro) {
    // String $erro: erro a ser adicionado
    //
        $this->erros[] = $erro;
    }


    //
    //     Retorna se existem erros internos
    //
    final public function possui_erros() {
        return (bool)count($this->erros);
    }


    //
    //     Retorna o vetor de erros internos
    //
    final public function get_erros() {
        return $this->erros;
    }


    //
    //     Limpa o vetor de erros internos
    //
    final public function limpar_erros() {
        $this->erros = array();
    }


    //
    //     Checa se ocorreram erros durante a transacao
    //
    final public function erros_transacao() {
        return $this->erros_transacao;
    }


    //
    //     Realiza uma consulta no banco de dados
    //
    public function consultar($sql) {
    // String || Array[String] $sql: Comando(s) SQL a ser(em) executado(s)
    //
        $conectou = false;
        if (!$this->conexao) {
            $conectou = $this->conectar();
            if (!$conectou) {
                $this->erros_transacao++;
                return false;
            }
        }
        if (is_array($sql)) {
            $resultado = array();
            foreach ($sql as $i) {
                $resultado = $this->consultar($i);
                if (!$resultado) {
                    return false;
                }
                $resultados[] = $resultado;
            }
            return $resultados;
        } else {
//if (headers_sent()) {
//echo '<p style="overflow: auto; border: 1px dotted #000000; font-family: monospace; background-color: #EEEEEE; color: #660000; padding: 1em">['.self::$instrucoes.'] '.texto::codificar($sql).'</p>';
//file_put_contents('/tmp/sql', $sql.";\n", FILE_APPEND);
//}
            $resultado = $this->query($sql);
            self::$instrucoes += 1;
            if (!$resultado) {
                $erro = $this->get_ultimo_erro($str_erro, $cod_erro);
                trigger_error('Erro na SQL ['.$sql.'] ('.$erro.')', E_USER_WARNING);
                $this->erros[] = "Erro na SQL [{$sql}] ({$erro})";
                $this->erros_transacao++;
            }
        }
        if ($conectou) {
            $this->desconectar();
        }
        return $resultado;
    }


    //
    //     Formata o retorno de um metodo sql_[...] para um formato textual
    //
    final public function formatar_sql($sql, $implode = "\n") {
    // String || Array[String] $sql: retorno de um metodo sql_[...]
    // String $implode: caractere utilizado para quebra de linha
    //
        if (is_string($sql)) {
            return $sql.(($sql[strlen($sql) - 1] == ';') ? '' : ';');
        } elseif (is_array($sql)) {
            $vt_retorno = array();
            foreach ($sql as $s) {
                $vt_retorno[] = $this->formatar_sql($s);
            }
            return implode($implode, $vt_retorno);
        }
        trigger_error('Tipo invalido para o parametro sql ('.util::get_tipo($sql).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Valida uma base
    //
    public function validar_base($base, &$erros) {
    // String $base: nome da base a ser validada
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        $validacao = validacao::get_instancia();
        if (!$validacao->validar_campo('BD', $base, $erro_campo)) {
            $erros[] = 'Nome do <acronym title="Banco de Dados">BD</acronym> possui caracteres inv&aacute;lidos.'.
                       ($erro_campo ? ' Detalhes: '.$erro_campo : '');
            return false;
        }
        return true;
    }


    //
    //     Valida um nome de usuario
    //
    public function validar_usuario($usuario, &$erros) {
    // String $usuario: nome do usuario a ser validado
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        return true;
    }


    //
    //     Obtem a quantidade de instrucoes executadas ate o momento
    //
    public static function get_quantidade_instrucoes() {
        return self::$instrucoes;
    }


    //
    //     Retorna se a versao atual do SGBD e' valida
    //
    public function versao_valida() {
        static $valida  = null;

        // Se ja consultou uma vez: retornar o resultado
        if (!is_null($valida)) {
            return $valida;
        }

        $versao_exigida = $this->get_versao_exigida();
        $versao_instalada = $this->get_versao();

        $vt_instalada = explode('.', $versao_instalada);
        $vt_exigida   = explode('.', $versao_exigida);

        $valida = true;
        if ((int)$vt_instalada[0] < (int)$vt_exigida[0]) {
            $valida = false;
        } elseif ((int)$vt_instalada[0] == (int)$vt_exigida[0]) {
            if ((int)$vt_instalada[1] < (int)$vt_exigida[1]) {
                $valida = false;
            } elseif ((int)$vt_instalada[1] == (int)$vt_exigida[1]) {
                if ((int)$vt_instalada[2] < (int)$vt_exigida[2]) {
                    $valida = false;
                }
            }
        }
        return $valida;
    }

}//class
