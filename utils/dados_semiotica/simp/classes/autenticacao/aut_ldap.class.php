<?php
//
// SIMP
// Descricao: Classe de autenticacao via LDAP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 15/08/2008
// Modificado: 10/02/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class aut_ldap extends autenticacao_base {
    private $conexao = false;   // Resource: conexao com o servidor LDAP
    private $dn = '';           // String: DN usado para conectar


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'LDAP';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais LDAP sao formadas por:
    //     String login: login do usuario do servidor LDAP
    //     String senha: senha do usuario do servidor LDAP
    //     String host: host do servidor LDAP (padrao: "localhost")
    //     Bool url: indica se o host e' uma URL (padrao: false)
    //     Int porta: porta de conexao com o servidor LDAP, exceto para conexoes baseadas em URL (padrao: 389)
    //     String dn: formato(s) a serem usados na funcao sprintf que representa(m) o(s) DN utilizado(s) para autenticar um usuario e cujo parametro e' o login do usuario (separar os DN por ponto-e-virgula) (padrao: "uid=%s")
    //
    public function definir_parametros() {
        $atributo = new atributo('login', 'Login', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('senha', 'Senha', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('host', 'Host', 'localhost');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('url', 'URL', false);
        $atributo->set_tipo('bool', true);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('dn', 'DN', 'uid=%s');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(4, 128);
        $this->parametros[] = $atributo;
        unset($atributo);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        // Checa se pode usar o LDAP
        if (!extension_loaded('ldap')) {
            $erros[] = 'A extens&atilde;o LDAP do PHP n&atilde;o foi instalada e a autentica&ccedil;&atilde;o n&atilde;o pode ser feita';
            return false;
        }

        // Tenta conectar
        if ($this->credenciais['url']) {
            $this->conexao = ldap_connect($this->credenciais['host']);
        } else {
            $ip = gethostbyname($this->credenciais['host']);
            $this->conexao = ldap_connect($ip, $this->credenciais['porta']);
        }
        if (!$this->conexao) {
            $erros[] = 'Erro ao conectar no servidor LDAP';
            trigger_error('Erro ao conectar no servidor LDAP (Host: '.$this->credenciais['host'].')', E_USER_WARNING);
            return false;
        }

        $vt_dn = explode(';', $this->credenciais['dn']);
        foreach ($vt_dn as $dn_base) {
            $dn = sprintf($dn_base, $this->credenciais['login']);
            $bind = ldap_bind($this->conexao, $dn, $this->credenciais['senha']);
            if ($bind) {
                $this->dn = $dn;
                return true;
            }
        }
        $erros[] = 'Usu&aacute;rio ou senha inv&aacute;lido';
        return false;
    }

}
