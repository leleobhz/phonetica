<?php
//
// SIMP
// Descricao: Classe de Categorias de Analise de Frases
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class categoria_analise_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Categoria de An&aacute;lise',
            /* Entidade Plural */ 'Categorias de An&aacute;lise',
            /* Genero          */ 'F',
            /* Classe          */ 'categoria_analise',
            /* Tabela          */ 'categorias_analises',
            /* Desc. Tabela    */ 'Tabela de Categorias Analises');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_categoria_analise
        $atributo = new atributo('cod_categoria_analise', 'C&oacute;digo da Categoria de An&aacute;lise', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 10000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: nome
        $atributo = new atributo('nome', 'Nome', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 50);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);
    }

}//class
