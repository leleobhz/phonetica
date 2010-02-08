<?php
//
// SIMP
// Descricao: Classe texto para analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 10/06/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class texto_analise_base extends objeto_formulario {

    //
    //     Cria a definicao da entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Texto para An&aacute;lise',
            /* Entidade Plural */ 'Textos para An&aacute;lise',
            /* Genero          */ 'M',
            /* Classe          */ 'texto_analise',
            /* Tabela          */ 'textos_analises',
            /* Desc. Tabela    */ 'Tabela de Textos para Analise');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_texto_analise
        $atributo = new atributo('cod_texto_analise', 'C&oacute;digo do Texto para An&aacute;lise', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 10000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: titulo
        $atributo = new atributo('titulo', 'T&iacute;tulo', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: arquivo
        $atributo = new atributo('arquivo', 'Arquivo', '');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(0, 128);
        $atributo->set_validacao('NOME_ARQUIVO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_projeto / projeto
        $this->adicionar_rel_uu(
            /* nome classe     */ 'projeto');

        // CAMPO: frases
        $this->adicionar_rel_un(
            /* nome classe     */ 'frase',
            /* nome vetor      */ 'frases');
    }

}//class
