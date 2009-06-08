<?php
//
// SIMP
// Descricao: Classe analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class analise_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'An&aacute;lise',
            /* Entidade Plural */ 'An&aacute;lises',
            /* Genero          */ 'F',
            /* Classe          */ 'analise',
            /* Tabela          */ 'analises',
            /* Desc. Tabela    */ 'Tabela de Analises');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_analise
        $atributo = new atributo('cod_analise', 'C&oacute;digo da An&aacute;lise', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 1000000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_categoria_analise / categoria_analise
        $this->adicionar_rel_uu(
            /* nome classe     */ 'categoria_analise');

        // CAMPO: cod_frase / frase
        $this->adicionar_rel_uu(
            /* nome classe     */ 'frase');

    }

    //
    //     Retorna o nome do atributo usado para identificar o objeto
    //
    final public function get_campo_nome() {
        return 'categoria_analise:nome';
    }

}//class
