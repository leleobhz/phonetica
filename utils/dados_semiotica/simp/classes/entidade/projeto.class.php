<?php
//
// SIMP
// Descricao: Classe projeto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class projeto_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Projeto',
            /* Entidade Plural */ 'Projetos',
            /* Genero          */ 'M',
            /* Classe          */ 'projeto',
            /* Tabela          */ 'projetos',
            /* Desc. Tabela    */ 'Tabela de Projetos');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_projeto
        $atributo = new atributo('cod_projeto', 'C&oacute;digo do Projeto', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: nome
        $atributo = new atributo('nome', 'Nome', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: data_entrada_dados
        $atributo = new atributo('data_entrada_dados', 'Data de Entrada dos Dados', '00-00-0000');
        $atributo->set_tipo('data', true, false);
        $atributo->set_campo_formulario('data_hora');
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_analista / analista
        $this->adicionar_rel_uu(
            /* nome classe     */ 'analista');

        // CAMPO: cod_texto_analise / textos
        $this->adicionar_rel_un(
            /* nome classe     */ 'texto_analise',
            /* nome vetor      */ 'textos');
    }

}//class
