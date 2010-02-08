<?php
//
// SIMP
// Descricao: Classe que controla os logs do sistema
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.0.0.14
// Data: 05/09/2007
// Modificado: 25/11/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//

// Constantes
define('LOG_SISTEMA_FORMATO_DATA', $CFG->formato_data.' - '.$CFG->formato_hora);

// Tipos de operacoes padrao
define('LOG_ENTRADA', 'entrada');
define('LOG_SAIDA',   'saida');
define('LOG_ACESSO',  'acesso');
define('LOG_INSERT',  'insert');
define('LOG_UPDATE',  'update');
define('LOG_DELETE',  'delete');

abstract class log_sistema_base extends objeto {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Log do Sistema',
            /* Entidade Plural */ 'Logs do Sistema',
            /* Genero          */ 'M',
            /* Classe          */ 'log_sistema',
            /* Tabela          */ 'logs',
            /* Desc. Tabela    */ 'Tabela de logs do sistema');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_log
        $atributo = new atributo('cod_log_sistema', 'C&oacute;digo do Log do Sistema', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 10000000000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: ip
        $atributo = new atributo('ip', 'IP', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 15);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: operacao
        $atributo = new atributo('operacao', 'Opera&ccedil;o', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 30);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_entidade
        $atributo = new atributo('cod_entidade', 'C&oacute;digo da Entidade', 0);
        $atributo->set_tipo('int', false);
        $atributo->set_intervalo(0, 10000000000000);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: entidade
        $atributo = new atributo('entidade', 'Entidade', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 64);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: detalhes
        $atributo = new atributo('detalhes', 'Detalhes', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 255);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: erro
        $atributo = new atributo('erro', 'Erro', false);
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: data
        $atributo = new atributo('data', 'Data', 0);
        $atributo->set_tipo('int', false);
        $atributo->set_intervalo(1, 9000000000);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_usuario => gera usuario
        $this->adicionar_rel_uu(
        /* nome classe     */ 'usuario',
        /* objeto gerado   */ 'usuario',
        /* atributo gerado */ 'cod_usuario',
        /* desc. objeto    */ false,
        /* desc. atributo  */ false,
        /* unico           */ false,
        /* forte           */ false);
    }

}//class
