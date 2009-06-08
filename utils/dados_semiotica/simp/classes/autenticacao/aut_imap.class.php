<?php
//
// SIMP
// Descricao: Classe de autenticacao de usuarios de uma base IMAP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 10/02/2009
// Modificado: 10/02/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class aut_imap extends autenticacao_base {


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'IMAP';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais BD sao formadas por:
    //     String login: login do usuario na tabela do BD
    //     String senha: senha do usuario na tabela do BD
    //     String mailbox: local da caixa de entrada de acordo com a definicao da funcao imap_open
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

        $atributo = new atributo('host', 'Host', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 255);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('mailbox', 'Caixa de Correio', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 255);
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

        if (!extension_loaded('imap')) {
            $erros[] = 'O m&oacute;dulo IMAP do PHP n&atilde;o foi instalado';
            return false;
        }

        $mb = imap_open($this->credenciais['mailbox'],
                        $this->credenciais['login'],
                        $this->credenciais['senha'],
                        OP_READONLY);
        if (!$mb) {
            $erros[] = 'Usu&aacute;rio/Senha inv&aacute;lidos';
            if ($msg = imap_last_error()) {
                $erros[] = 'Detalhes: '.texto::codificar($msg);
            }
            return false;
        }

        $this->dados_usuario['check'] = imap_check($mb);
        $this->dados_usuario['mailboxes'] = imap_getmailboxes($mb, '{'.$this->credenciais['host'].'}', '*');
        $info = false;
        $max = $this->dados_usuario['check']->Nmsgs;
        for ($i = 1; $i <= $max && !$info; $i++) {
            $header = imap_headerinfo($mb, $i);
            if (!$header) {
                break;
            }
            foreach ($header->to as $obj) {
                if ($obj->mailbox == $this->credenciais['login']) {
                    if (isset($obj->personal)) {
                        $this->dados_usuario['personal'] = $obj->personal;
                        $this->dados_usuario['email'] = $obj->mailbox.'@'.$obj->host;
                        $info = true;
                    }
                }
            }
        }
        imap_close($mb);

        return true;
    }

}
