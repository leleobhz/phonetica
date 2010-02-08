<?php
//
// SIMP
// Descricao: Classe de controle das permissoes de acesso aos arquivos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.5
// Data: 10/09/2007
// Modificado: 06/11/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class permissao_base extends objeto_formulario {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Permiss&atilde;o',
            /* Entidade Plural */ 'Permiss&otilde;es',
            /* Genero          */ 'F',
            /* Classe          */ 'permissao',
            /* Tabela          */ 'permissoes',
            /* Desc. Tabela    */ 'Tabela de permissoes de acesso dos grupos aos arquivos');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_permissao
        $atributo = new atributo('cod_permissao', 'C&oacute;digo da Permiss&atilde;o', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 10000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: visivel
        $atributo = new atributo('visivel', 'Vis&iacute;vel', 1);
        $atributo->set_tipo('bool', false);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: posicao
        $atributo = new atributo('posicao', 'Posi&ccedil;&atilde;o', 1);
        $atributo->set_tipo('int', false);
        $atributo->set_intervalo(1, 1000);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_grupo => gera um grupo
        $this->adicionar_rel_uu(
            /* nome classe        */ 'grupo');

        // CAMPO: cod_arquivo => gera um arquivo
        $this->adicionar_rel_uu(
            /* nome classe        */ 'arquivo');

        // Restricao de unicidade
        $this->adicionar_chave_unica_composta(array('cod_grupo', 'cod_arquivo'));
    }


    //
    //     Obtem o nome do campo usado para identificar a entidade
    //
    public function get_campo_nome() {
        return 'arquivo';
    }

}//class
