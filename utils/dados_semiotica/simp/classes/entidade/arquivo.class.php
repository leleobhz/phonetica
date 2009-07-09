<?php
//
// SIMP
// Descricao: Classe Arquivos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.11
// Data: 10/09/2007
// Modificado: 30/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('ARQUIVO_DIR_MODULOS', $CFG->dirmods);
define('ARQUIVO_DIR_ROOT',    $CFG->dirroot);
define('ARQUIVO_WWW_MODULOS', $CFG->wwwmods);
define('ARQUIVO_WWW_ROOT',    $CFG->wwwroot);

abstract class arquivo_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Arquivo',
            /* Entidade Plural */ 'Arquivos',
            /* Genero          */ 'M',
            /* Classe          */ 'arquivo',
            /* Tabela          */ 'arquivos',
            /* Desc. Tabela    */ 'Tabela de arquivos do sistema');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_arquivo
        $atributo = new atributo('cod_arquivo', 'C&oacute;digo do Arquivo', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: modulo
        $atributo = new atributo('modulo', 'M&oacute;dulo', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('MODULO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: arquivo
        $atributo = new atributo('arquivo', 'Arquivo', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 50);
        $atributo->set_validacao('NOME_ARQUIVO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: descricao
        $atributo = new atributo('descricao', 'Descri&ccedil;&atilde;o', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $atributo->set_ajuda(false, 'Cadastro de Usu&aacute;rios');
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO IMPLICITO: link
        $this->adicionar_atributo_implicito('link', 'Link', 'get_link', array('arquivo', 'modulo'));

        // CAMPO IMPLICITO: nome_completo
        $this->adicionar_atributo_implicito('nome_completo', 'Nome completo', 'get_nome_completo', array('arquivo', 'modulo'));

        // Restricao de unicidade
        $this->adicionar_chave_unica_composta(array('modulo', 'arquivo'));
    }


    //
    //     Retorna um link para o arquivo
    //
    public function get_link() {
        if (!$this->existe()) {
            return false;
        }
        if ($this->get_atributo('modulo')) {
            return ARQUIVO_WWW_MODULOS.$this->get_atributo('modulo').'/'.$this->get_atributo('arquivo');
        } else {
            return ARQUIVO_WWW_ROOT.$this->get_atributo('arquivo');
        }
    }


    //
    //     Retorna o nome completo do arquivo
    //
    public function get_nome_completo() {
        $a = $this->get_atributo('arquivo');
        $m = $this->get_atributo('modulo');
        return $m.'/'.$a;
    }

}//class
