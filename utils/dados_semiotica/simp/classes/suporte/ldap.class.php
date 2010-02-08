<?php
//
// SIMP
// Descricao: Classe de comunicacao com servidor LDAP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.5
// Data: 16/07/2007
// Modificado: 15/08/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
global $CFG;

// Constantes
if (isset($CFG->ldap_config)) {
    define('LDAP_HOST',  $CFG->ldap_config->host);
    define('LDAP_PORTA', $CFG->ldap_config->porta);
    define('LDAP_DN',    $CFG->ldap_config->dn);
} else {
    define('LDAP_HOST', 'localhost');
    define('LDAP_PORTA', '389');
    define('LDAP_DN', '');
}

final class ldap {
    private $conexao = false;
    private $host    = 'localhost';
    private $porta   = '389';
    private $dn      = '';
    private $usuario = '';
    private $senha   = '';


    //
    //     Construtor
    //
    public function __construct($host = false, $porta = false, $dn = false) {
    // String $host: endereco do servidor LDAP
    // Int $porta: porta de conexao
    // String $dn: DN base para conexao
    //
        // Checa se pode usar o LDAP
        if (!extension_loaded('ldap')) {
            return;
        }

        // Recuperando parametros para conexao
        $this->conexao = false;
        $this->host    = ($host !== false)  ? $host  : LDAP_HOST;
        $this->porta   = ($porta !== false) ? $porta : LDAP_PORTA;
        $this->dn      = ($dn !== false)    ? $dn    : LDAP_DN;
    }


    //
    //     Destrutor
    //
    function __destruct() {
        $this->desconectar();
    }


    //
    //     Conectar com um usuario ou como visitante
    //
    public function conectar($usuario = false, $senha = false) {
    // String $usuario: usuario para conexao
    // String $senha: senha do usuario
    //
        $this->usuario = $usuario;
        $this->senha   = $senha;

        $ip = gethostbyname($this->host);

        // Tenta conectar
        $this->conexao = ldap_connect($ip, $this->porta);
        if (!$this->conexao) {
            return false;
        }

        if ($usuario && $senha) {

            // Tenta autenticar com um usuario
            $dn = 'uid='.$this->usuario.','.$this->dn;
            $bind = ldap_bind($this->conexao, $dn, $this->senha);
            if (!$bind) {
                return false;
            }
        }
        return true;
    }


    //
    //     Desconectar
    //
    public function desconectar() {
        if ($this->conexao) {
            ldap_unbind($this->conexao);
        }
    }


    //
    //     Realiza uma busca (consulta)
    //
    public function consultar($dn, $busca, $campos = false, $ordem = false) {
    // String $dn: DN relativo para busca
    // String $busca: parametro de busca
    // Array[String] $campos: campos desejados
    // String $ordem: ordem dos resultados
    //
        $vet_resultado = array();

        $dn = $dn ? $dn.',' : '';

        // Consultando
        $resultado = ldap_search($this->conexao, $dn.$this->dn, $busca);

        // Ordenar resultado
        if ($ordem) {
            ldap_sort($this->conexao, $resultado, $ordem);
        }

        // Recuperando resultado
        $lista = ldap_get_entries($this->conexao, $resultado);
        $consulta = array_shift($lista);

        // Liberando a memoria
        ldap_free_result($resultado);

        if (!$lista) {
            return $vet_resultado;
        }

        // Percorrendo os resultados
        foreach ($lista as $item) {
            $obj = null;

            //echo "dn: ".$item['dn']."<br />";
            //echo "count: ".$item['count']."<br />";
            unset($item['dn']);
            unset($item['count']);

            // Consultar todos os campos
            if (!$campos) {
                foreach ($item as $chave => $vetor) {
                    if (is_numeric($chave)) { continue; }
                    unset($vetor['count']);
                    //echo "{$chave} = ".implode(' / ', $vetor)."<br />";
                    $obj->$chave = $vetor[0];
                }

            // Consultar campos desejados
            } else {
                foreach ($campos as $chave) {
                    //echo "{$chave} = ".implode(' / ', $item[$chave])."<br />";
                    $obj->$chave = $item[$chave][0];
                }
            }

            $vet_resultado[] = $obj;
        }
        return $vet_resultado;
    }


    //
    //     Altera o valor de uma entrada
    //
    public function update($dn, $dados) {
    // String $dn: DN da entrada
    // Array[Mixed] $dados: novos dados da entrada
    //
        $alterado = ldap_modify($this->conexao, $this->dn.','.$dn, $dados);
        return $alterado;
    }


    //
    //     Insere uma nova entrada
    //
    public function insert($dn, $dados) {
    // String $dn: DN da nova entrada
    // Array[Mixed] $dados: dados da entrada
    //
        $inserido = ldap_add($this->conexao, $dn.','.$this->dn, $dados);
        return $inserido;
    }


    //
    //     Retorna o ultimo erro
    //
    public function erro() {
        if ($this->conexao) {
            return ldap_error($this->conexao);
        }
        return 'N&atilde;o conectou';
    }

}//class
