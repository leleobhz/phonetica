<?php
//
// SIMP
// Descricao: Classe de autenticacao de usuarios de um servidor Linux
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 12/08/2008
// Modificado: 24/03/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class aut_linux extends autenticacao_base {


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'Servidor Linux';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais linux sao formadas por:
    //     String login: login do usuario do servidor linux
    //     String senha: senha do usuario do servidor linux
    //     String arq_shadow: arquivo onde sao armazenadas as senhas criptografadas dos usuarios, caso exista (padrao "/etc/shadow")
    //
    public function definir_parametros() {
        $atributo = new atributo('login', 'Login', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('senha', 'Senha', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('arq_shadow', 'Arquivo Shadow', '/etc/shadow');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        // Se nao informou as credenciais
        if (!$this->credenciais) {
            $erros[] = 'N&atilde;o foram especificadas as credenciais';
            return false;
        }

        // Se nao carregou o modulo POSIX
        if (!extension_loaded('posix')) {
            trigger_error('O modulo POSIX nao esta instalado no PHP', E_USER_WARNING);
            $erros[] = 'O sistema n&atilde;o est&aacute; configurado adequadamente para autenticar usu&aacute;rios do servidor Linux';
            return false;
        }

        // Se nao existe o usuario com o login informado
        $this->dados_usuario = posix_getpwnam($this->credenciais['login']);
        if (!$this->dados_usuario) {
            $erros[] = 'Usu&aacute;rio inv&aacute;lido';
            return false;
        }

        // Se a senha esta' em um arquivo shadow
        if ($this->dados_usuario['passwd'] == 'x') {

            // Se o arquivo de autenticacao nao existe
            if (!is_file($this->credenciais['arq_shadow'])) {
                trigger_error('O arquivo de autenticacao "'.$this->credenciais['arq_shadow'].'" nao existe', E_USER_WARNING);
                $erros[] = 'O sistema n&atilde;o est&aacute; configurado adequadamente para autenticar usu&aacute;rios do servidor Linux';
                return false;

            // Se o arquivo de autenticacao nao pode ser lido
            } elseif (!is_readable($this->credenciais['arq_shadow'])) {
                trigger_error('O arquivo de autenticacao "'.$this->credenciais['arq_shadow'].'" nao pode ser lido por "'.posix_getlogin().'"', E_USER_WARNING);
                $erros[] = 'O sistema n&atilde;o est&aacute; configurado adequadamente para autenticar usu&aacute;rios do servidor Linux';
                return false;
            }

            // Buscar a senha no arquivo shadow
            $arq = fopen($this->credenciais['arq_shadow'], 'r');
            $separador = ':';
            $p_namp   = 0; // char               *sp_namp;  user login name
            $p_pwdp   = 1; // char               *sp_pwdp;  encrypted password
            $p_lstchg = 2; // long int           sp_lstchg; last password change
            $p_min    = 3; // long int           sp_min;    days until change allowed
            $p_max    = 4; // long int           sp_max;    days before change required
            $p_warn   = 5; // long int           sp_warn;   days warning for expiration
            $p_inact  = 6; // long int           sp_inact;  days before account inactive
            $p_expire = 7; // long int           sp_expire; date when account expires
            $p_flag   = 8; // unsigned long int  sp_flag;   reserved for future use

            while (!feof($arq)) {
                $linha = fgets($arq, 1024);
                $dados_linha = explode($separador, $linha);
                if ($dados_linha[$p_namp] == $this->credenciais['login']) {
                    fclose($arq);
                    $this->dados_usuario['passwd'] = $dados_linha[$p_pwdp];
                    $this->dados_usuario['lstchg'] = $dados_linha[$p_lstchg];
                    $this->dados_usuario['min']    = $dados_linha[$p_min];
                    $this->dados_usuario['max']    = $dados_linha[$p_max];
                    $this->dados_usuario['warn']   = $dados_linha[$p_warn];
                    $this->dados_usuario['inact']  = $dados_linha[$p_inact];
                    $this->dados_usuario['expire'] = $dados_linha[$p_expire];
                    $this->dados_usuario['flag']   = $dados_linha[$p_flag];
                    $this->dados_usuario['group']  = posix_getgrgid($this->dados_usuario['gid']);
                    $this->dados_usuario['system'] = posix_uname();
                    break;
                }
            }
        }

        // Retornar se a senha confere com a informada
        if (crypt($this->credenciais['senha'], $this->dados_usuario['passwd']) != $this->dados_usuario['passwd']) {
            $erros[] = 'Senha inv&aacute;lida';
            return false;
        }
        return true;
    }

}
