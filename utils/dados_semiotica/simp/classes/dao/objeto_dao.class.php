<?php
//
// SIMP
// Descricao: Classe de comunicacao com Bancos de Dados (Data Access Object) atraves de um driver
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 22/04/2008
// Modificado: 03/07/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('OBJETO_DAO_SGBD',     $CFG->bd_config->sgbd);
define('OBJETO_DAO_PORTA',    $CFG->bd_config->porta);
define('OBJETO_DAO_BASE',     $CFG->bd_config->base);
define('OBJETO_DAO_SERVIDOR', $CFG->bd_config->servidor);
define('OBJETO_DAO_USUARIO',  $CFG->bd_config->usuario);
define('OBJETO_DAO_SENHA',    $CFG->bd_config->senha);
define('OBJETO_DAO_CHARSET',  strtolower($CFG->charset));

final class objeto_dao {
    private $drivers = array();   // Vetor de nomes apontando para os drivers carregados (bibliotecas de um SGBD para um proposito)
    private $bd_config = array(); // Vetor com as configuracoes do sgbd


    //
    //     Construtor padrao
    //
    public function __construct($sgbd = null, $servidor = null, $porta = null, $usuario = null, $senha = null, $base = null) {
    // String $sgbd: codigo do SGBD a ser utilizado
    // String $servidor: endereco do servidor
    // Int $porta: porta de acesso ao servidor
    // String $usuario: login do usuario no BD ou "[root]" para o usuario root (administrador do SGBD)
    // String $senha: senha para acesso ao BD
    // String $base: nome do BD a ser usado ou 0 para nenhum
    //
        $this->bd_config['sgbd']     = is_null($sgbd)     ? OBJETO_DAO_SGBD     : $sgbd;
        $this->bd_config['servidor'] = is_null($servidor) ? OBJETO_DAO_SERVIDOR : $servidor;
        $this->bd_config['porta']    = is_null($porta)    ? OBJETO_DAO_PORTA    : $porta;
        $this->bd_config['usuario']  = is_null($usuario)  ? OBJETO_DAO_USUARIO  : $usuario;
        $this->bd_config['senha']    = is_null($senha)    ? OBJETO_DAO_SENHA    : $senha;
        $this->bd_config['base']     = is_null($base)     ? OBJETO_DAO_BASE     : $base;

        if ($this->bd_config['sgbd']) {
            $this->carregar('objeto');
        }
    }


    //
    //     Chama algum metodo do driver
    //
    public function __call($metodo, $args) {
    // String $metodo: nome do metodo
    // Array[Mixed] $args: vetor de paramtros
    //
        foreach ($this->drivers as $classe => &$driver) {
            if (method_exists($driver, $metodo)) {
                return call_user_func_array(array($driver, $metodo), $args);
            } elseif (method_exists($classe, $metodo)) {
                return call_user_func_array(array($classe, $metodo), $args);
            } else {
                try {
                    return call_user_func_array(array($driver, '__call'), array($metodo, $args));
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        throw new Exception("N&atilde;o existe o m&eacute;todo {$metodo} no driver {$this->bd_config['sgbd']}");
    }


    //
    //     Metodo Factory
    //
    public static function dao() {
        $classe = __CLASS__;
        $obj = new $classe();
        return $obj;
    }


    //
    //     Carrega uma biblioteca do driver para um determinado proposito (agrega metodos ao objeto)
    //
    public function carregar($biblioteca) {
    // String $biblioteca: tipo de biblioteca ('objeto', 'operacao')
    //
        $bibliotecas = array('objeto', 'operacao');
        if (!in_array($biblioteca, $bibliotecas)) {
            throw new Exception("N&atilde;o existe o tipo de biblioteca do driver ({$biblioteca})");
        }
        if (empty($this->bd_config['sgbd'])) {
            throw new Exception("N&atilde;o foi especificado o sgbd");
        }

        try {
            $classe = 'driver_'.$this->bd_config['sgbd'].'_'.$biblioteca;
            simp_autoload($classe);
            $this->drivers[$classe] = new $classe($this->bd_config['servidor'], $this->bd_config['porta'],
                                                  $this->bd_config['usuario'], $this->bd_config['senha'],
                                                  $this->bd_config['base']);
        } catch (Exceptcion $e) {
            throw new Exception("N&atilde;o existe driver para o SGBD especificado ({$sgbd}/{$biblioteca})");
        }
    }


    //
    //     Checa se uma biblioteca foi carregada
    //
    public function carregou($biblioteca) {
    // String $biblioteca: tipo de biblioteca ('objeto', 'operacao')
    //
        $classe = 'driver_'.$this->bd_config['sgbd'].'_'.$biblioteca;
        return isset($this->drivers[$classe]);
    }


    //
    //     Lista os SGBDs suportados e alguns dados (codigo, nome e porta padrao)
    //
    public static function get_drivers() {
        $vt_driver = array();

        // MySQL
        if (extension_loaded('mysql')) {
            $dados = new stdClass();
            $dados->codigo = 'mysql';
            $dados->sgbd   = 'MySQL';
            $dados->nome   = 'MySQL Padr&atilde;o';
            $dados->porta  = '3306';
            $vt_driver[$dados->codigo] = $dados;
        }
        if (extension_loaded('pdo_mysql')) {
            $dados = new stdClass();
            $dados->codigo = 'pdo_mysql';
            $dados->sgbd   = 'MySQL';
            $dados->nome   = 'MySQL PDO';
            $dados->porta  = '3306';
            $vt_driver[$dados->codigo] = $dados;
        }

        // PostgreSQL
        if (extension_loaded('pgsql')) {
            $dados = new stdClass();
            $dados->codigo = 'pgsql';
            $dados->sgbd   = 'PostgreSQL';
            $dados->nome   = 'PostgreSQL Padr&atilde;o';
            $dados->porta  = '5432';
            $vt_driver[$dados->codigo] = $dados;
        }
        if (extension_loaded('pdo_pgsql')) {
            $dados = new stdClass();
            $dados->codigo = 'pdo_pgsql';
            $dados->sgbd   = 'PostgreSQL';
            $dados->nome   = 'PostgreSQL PDO';
            $dados->porta  = '5432';
            $vt_driver[$dados->codigo] = $dados;
        }

        // Oracle 8
        if (extension_loaded('oci8')) {
            $dados = new stdClass();
            $dados->codigo = 'oci8';
            $dados->sgbd   = 'Oracle';
            $dados->nome   = 'Oracle Padr&atilde;o';
            $dados->porta  = '1521';
            $vt_driver[$dados->codigo] = $dados;
        }
        if (extension_loaded('PDO_OCI')) {
            $dados = new stdClass();
            $dados->codigo = 'pdo_oci';
            $dados->sgbd   = 'Oracle';
            $dados->nome   = 'Oracle PDO';
            $dados->porta  = '1521';
            $vt_driver[$dados->codigo] = $dados;
        }

        // Firebird
        if (extension_loaded('pdo_firebird')) {
            $dados = new stdClass();
            $dados->codigo = 'pdo_firebird';
            $dados->sgbd   = 'Firebird';
            $dados->nome   = 'Firebird PDO';
            $dados->porta  = '3050';
            $vt_driver[$dados->codigo] = $dados;
        }

        // SQLite
        if (extension_loaded('SQLite')) {
            $dados = new stdClass();
            $dados->codigo = 'sqlite';
            $dados->sgbd   = 'SQLite';
            $dados->nome   = 'SQLite Padr&atilde;o';
            $dados->porta  = '0';
            $vt_driver[$dados->codigo] = $dados;
        }
        if (extension_loaded('pdo_sqlite')) {
            $dados = new stdClass();
            $dados->codigo = 'pdo_sqlite';
            $dados->sgbd   = 'SQLite';
            $dados->nome   = 'SQLite PDO';
            $dados->porta  = '0';
            $vt_driver[$dados->codigo] = $dados;
        }

        return $vt_driver;
    }


    //
    //     Retorna o ultimo erro no SGBD
    //
    public function get_ultimo_erro(&$str_erro = '', &$cod_erro = '0') {
    // String $str_erro: erro ocorrido
    // Int $cod_erro: codigo do erro ocorrido
    //
        $classe = 'driver_'.$this->bd_config['sgbd'].'_objeto';
        return $this->drivers[$classe]->get_ultimo_erro($str_erro, $cod_erro);
    }


    //
    //     Valida um nome de usuario
    //
    public function validar_usuario($usuario, &$erros) {
    // String $usuario: nome do usuario a ser validado
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        $classe = 'driver_'.$this->bd_config['sgbd'].'_objeto';
        return $this->drivers[$classe]->validar_usuario($usuario, $erros);
    }


    //
    //     Valida uma base
    //
    public function validar_base($base, &$erros) {
    // String $base: nome da base a ser validada
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        $classe = 'driver_'.$this->bd_config['sgbd'].'_objeto';
        return $this->drivers[$classe]->validar_base($base, $erros);
    }

}//class
