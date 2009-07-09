<?php
//
// SIMP
// Descricao: Classe de Congiguracoes do Sistema
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.16
// Data: 20/08/2007
// Modificado: 19/06/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//

// Tipo de Email
define('CONFIG_EMAIL_PADRAO',  1);
define('CONFIG_EMAIL_SMTP',    2);

abstract class config_base extends objeto_formulario {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Configura&ccedil;&atilde;o',
            /* Entidade Plural */ 'Configura&ccedil;&otilde;es',
            /* Genero          */ 'F',
            /* Classe          */ 'config',
            /* Tabela          */ 'config',
            /* Desc. Tabela    */ 'Tabela de configuracoes',
            /* Singleton       */ true);
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_config
        $atributo = new atributo('cod_config', 'C&oacute;digo do Config', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: ajax
        $atributo = new atributo('ajax', 'Ajax', '1');
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: gd
        $atributo = new atributo('gd', 'GD', '1');
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, 'existe_gd', false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: transparencia
        $atributo = new atributo('transparencia', 'Transpar&ecirc;ncia', '0.7');
        $atributo->set_tipo('float', false);
        $atributo->set_intervalo(0.3, 1);
        $atributo->set_casas_decimais(2);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: opaco
        $atributo = new atributo('opaco', 'Opaco', '0.9');
        $atributo->set_tipo('float', false);
        $atributo->set_intervalo(0.3, 1);
        $atributo->set_casas_decimais(2);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: autenticacao
        $atributo = new atributo('autenticacao', 'Autentica&ccedil;&atilde;o', 'simp');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 15);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: autenticacao_http
        $atributo = new atributo('autenticacao_http', 'Autentica&ccedil;&atilde;o usando HTTP', '0');
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: fechado
        $atributo = new atributo('fechado', 'Fechado para Manuten&ccedil;&atilde;o', '0');
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: motivo_fechado
        $atributo = new atributo('motivo_fechado', 'Motivo', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 255);
        $atributo->set_validacao('TEXTO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: formato_data
        $atributo = new atributo('formato_data', 'Formato de Data', '%d/%m/%Y');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 50);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: formato_hora
        $atributo = new atributo('formato_hora', 'Formato de Hora', '%H:%M:%S');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 50);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: tipo_email
        $atributo = new atributo('tipo_email', 'Tipo de E-mail', CONFIG_EMAIL_PADRAO);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(1, 10);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: smtp_host
        $atributo = new atributo('smtp_host', 'Host SMTP', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 128);
        $atributo->set_validacao('HOST', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: smtp_porta
        $atributo = new atributo('smtp_porta', 'Porta SMTP', '0');
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(0, 10000);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: smtp_usuario
        $atributo = new atributo('smtp_usuario', 'Usu&aacute;rio SMTP', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 50);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: smtp_senha
        $atributo = new atributo('smtp_senha', 'Senha SMTP', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 50);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: titulo
        $atributo = new atributo('titulo', 'Titulo', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: descricao
        $atributo = new atributo('descricao', 'Descri&ccedil;&atilde;o', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 255);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: autor
        $atributo = new atributo('autor', 'Autor', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: link_autor
        $atributo = new atributo('link_autor', 'Link do Autor', null);
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 255);
        $atributo->set_validacao('SITE', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: preassunto
        $atributo = new atributo('preassunto', 'Prefixo nos Assuntos', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 20);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

         // CAMPO: email_padrao
        $atributo = new atributo('email_padrao', 'Email Padr&atilde;o', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('EMAIL', false, false);
        $atributo->set_ajuda('E-mail utilizado quando n&atilde;o &eacute; especificado o remetente');
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: lingua
        $atributo = new atributo('lingua', 'L&iacute;ngua', 'pt_br');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 10);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: localidade
        $atributo = new atributo('localidade', 'Localidade', 'C');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 64);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cidade
        $atributo = new atributo('cidade', 'Cidade', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: estado
        $atributo = new atributo('estado', 'Estado', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(2, 2);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: palavras
        $atributo = new atributo('palavras', 'Palavras', null);
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 500);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);
    }


    //
    //     Retorna se existe a biblioteca GD instalada
    //
    public function existe_gd($valor) {
    // Bool $valor: valor a ser validado
    //
        if ($valor && !extension_loaded('gd')) {
            $this->erros[] = 'A biblioteca GD n&atilde;o est&aacute; instalada e n&atilde;o pode ser usada';
            return false;
        }
        return true;
    }

}//class
