<?php
//
// SIMP
// Descricao: Classe analista
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class analista_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Analista',
            /* Entidade Plural */ 'Analistas',
            /* Genero          */ 'M',
            /* Classe          */ 'analista',
            /* Tabela          */ 'analistas',
            /* Desc. Tabela    */ 'Tabela de Analistas');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_analista
        $atributo = new atributo('cod_analista', 'C&oacute;digo do Analista', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: email_alternativo
        $atributo = new atributo('email_alternativo', 'E-mail Alternativo', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 128);
        $atributo->set_validacao('EMAIL', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_usuario / usuario
        $this->adicionar_rel_uu(
            /* nome classe     */ 'usuario');

        // CAMPO: projetos
        $this->adicionar_rel_un(
            /* nome classe     */ 'projeto',
            /* nome vetor      */ 'projetos');
    }


    //
    //     Retorna o nome do atributo usado para identificar o objeto
    //
    final public function get_campo_nome() {
        return 'usuario:nome';
    }

}//class
