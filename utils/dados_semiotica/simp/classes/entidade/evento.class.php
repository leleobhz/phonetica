<?php
//
// SIMP
// Descricao: Classe de controle de eventos
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.1.0.1
// Data: 09/08/2007
// Modificado: 17/03/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//

// Constantes
define('EVENTO_TODOS_GRUPOS',   0);
define('EVENTO_ANO_INSTALACAO', strftime('%Y', $CFG->instalacao));

abstract class evento_base extends objeto_formulario {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Evento',
            /* Entidade Plural */ 'Eventos',
            /* Genero          */ 'M',
            /* Classe          */ 'evento',
            /* Tabela          */ 'eventos',
            /* Desc. Tabela    */ 'Tabela de eventos do calendario');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_evento
        $atributo = new atributo('cod_evento', 'C&oacute;digo do Evento', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 1000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: nome
        $atributo = new atributo('nome', 'Nome','');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: descricao
        $atributo = new atributo('descricao', 'Descri&ccedil;&atilde;o','');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 1000);
        $atributo->set_validacao('TEXTO', false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: data
        $atributo = new atributo('data', 'Data', 'agora');
        $atributo->set_tipo('data', false);
        $atributo->set_campo_formulario('data_hora');
        $atributo->set_data_inicio(ATRIBUTO_DATA_ABSOLUTA, EVENTO_ANO_INSTALACAO);
        $atributo->set_data_fim(ATRIBUTO_DATA_RELATIVA, 5);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: visibilidade
        $atributo = new atributo('visibilidade', 'Visibilidade', 0);
        $atributo->set_tipo('int', false);
        $atributo->set_intervalo(0, 100);
        $atributo->set_validacao(false, false, false);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_usuario => gera um usuario
        $this->adicionar_rel_uu(
            /* nome classe        */ 'usuario',
            /* objeto gerado      */ 'usuario',
            /* atributo gerado    */ 'cod_usuario',
            /* descricao objeto   */ 'Criador',
            /* descricao atributo */ 'C&oacute;digo do Criador');
    }

}//class
