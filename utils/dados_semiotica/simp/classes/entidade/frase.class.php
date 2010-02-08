<?php
//
// SIMP
// Descricao: Classe Frase (para analise)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class frase_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Frase',
            /* Entidade Plural */ 'Frases',
            /* Genero          */ 'F',
            /* Classe          */ 'frase',
            /* Tabela          */ 'frases',
            /* Desc. Tabela    */ 'Tabela de Frases para Analise');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_frase
        $atributo = new atributo('cod_frase', 'C&oacute;digo da Frase', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: conteudo
        $atributo = new atributo('conteudo', 'Frase', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 1000);
        $atributo->set_validacao('TEXTO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_texto_analise / texto_analise
        $this->adicionar_rel_uu(
            /* nome classe     */ 'texto_analise');

        // CAMPO: analises
        $this->adicionar_rel_un(
            /* nome classe     */ 'analise',
            /* nome vetor      */ 'analises');
    }

    //
    //     Retorna o nome do atributo usado para identificar o objeto
    //
    final public function get_campo_nome() {
        return 'conteudo';
    }

}//class
