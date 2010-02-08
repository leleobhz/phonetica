<?php
//
// SIMP
// Descricao: Classe de autenticacao factory
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 12/08/2008
// Modificado: 04/03/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('AUTENTICACAO_DIR_BASE', $CFG->dirclasses.'autenticacao/');

final class autenticacao {
    private $nome_driver;  // String: nome do driver escolhido para autenticacao
    private $driver;       // Mixed: Objeto que extende a autenticacao_base
    private $config;       // Array[String => Mixed]: vetor de configuracoes


    //
    //     Construtor padrao
    //
    public function __construct($nome_driver) {
    // String $nome_driver: nome da classe de autenticacao utilizada
    //

        // Criar o driver interno
        $this->nome_driver = $nome_driver;
        try {
            simp_autoload($nome_driver);
        } catch (Exception $e) {
            trigger_error('O driver de autenticacao "'.$nome_driver.'" nao existe', E_USER_WARNING);
            return false;
        }
        $this->driver = new $nome_driver();
        $autenticacao_base = 'autenticacao_base';
        if (!($this->driver instanceof $autenticacao_base)) {
            $this->driver = null;
            $this->nome_driver = '';
            trigger_error('O driver de autenticacao "'.$nome_driver.'" nao pode ser usado pois nao extende a classe autenticacao_base', E_USER_WARNING);
            return false;
        }
        $this->driver->definir_parametros();

        // Carregar as configuracoes
        $this->config = parse_ini_file(dirname(__FILE__).'/autenticacao.ini', true);
    }


    //
    //     Invoca um metodo do driver caso o metodo nao exista na classe corrente
    //
    public function __call($metodo, $args) {
    // String $metodo: nome do metodo invocado
    // Array[String => Mixed] $args: parametros informados
    //
        if ($this->driver) {
            if (method_exists($this->driver, $metodo)) {
                $callback = array($this->driver, $metodo);
                return call_user_func_array($callback, $args);
            } else {
                trigger_error('O metodo "'.$metodo.'" nao existe no driver "'.$this->nome_driver.'"', E_USER_WARNING);
            }
        } else {
            trigger_error('O metodo "'.$metodo.'" nao existe na classe "'.__CLASS__.'" e nenhum driver de autenticacao foi carregado', E_USER_WARNING);
        }
        return null;
    }


    //
    //     Metodo que define os parametros de autenticacao atraves de um vetor associativo
    //
    public function set_credenciais($credenciais, &$erros) {
    // Array[String => Mixed] $credenciais: dados para autenticacao
    // Array[String] $erros: erros ocorridos
    //
        $credenciais_completa = array();
        foreach ($this->driver->get_parametros() as $parametro) {
            if (isset($credenciais[$parametro->nome])) {
                $credenciais_completa[$parametro->nome] = $credenciais[$parametro->nome];
            } elseif (isset($this->config[$this->nome_driver][$parametro->nome])) {
                $credenciais_completa[$parametro->nome] = $this->config[$this->nome_driver][$parametro->nome];
            } else {
                $credenciais_completa[$parametro->nome] = $parametro->padrao;
            }
        }
        return $this->driver->set_credenciais($credenciais_completa, $erros);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        if ($this->driver) {
            return $this->driver->autenticar_usuario($erros);
        }
        return false;
    }


    //
    //     Retorna os tipos de autenticacao disponiveis
    //
    public static function get_drivers($nome = false) {
    // Bool $nome: indica se deve ser retornado um vetor associativo com o nome da classe apontando para a descricao da forma de autenticacao
    //
        $classes = listas::get_classes(AUTENTICACAO_DIR_BASE, 'autenticacao_base', true);
        sort($classes);
        if ($nome) {
            $associativo = array();
            foreach ($classes as $classe) {
                try {
                    simp_autoload($classe);
                } catch (Exception $e) {
                    // Ignorar classe
                }
                $obj = new $classe();
                $associativo[$classe] = $obj->get_nome();
            }
            return $associativo;
        }
        return $classes;
    }


    //
    //      Metodo que atualiza a senha na base utilizada de acordo com as novas credenciais
    //
    public function alterar_senha(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        return $this->driver->alterar_senha($erros);
    }
}
