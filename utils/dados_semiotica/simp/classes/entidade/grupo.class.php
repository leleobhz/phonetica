<?php
//
// SIMP
// Descricao: Classe Grupo
// Autor: Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.0.0.7
// Data: 13/08/2007
// Modificado: 06/11/2008
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
abstract class grupo_base extends objeto_formulario {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Grupo',
            /* Entidade Plural */ 'Grupos',
            /* Genero          */ 'M',
            /* Classe          */ 'grupo',
            /* Tabela          */ 'grupos',
            /* Desc. Tabela    */ 'Tabela de grupos de usuarios');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_grupo
        $atributo = new atributo('cod_grupo', 'C&oacute;digo do Grupo', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: nome
        $atributo = new atributo('nome', 'Nome', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $atributo->set_validacao('TEXTO_LINHA', false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: usuarios
        $this->adicionar_rel_un(
            /* nome classe     */ 'usuarios_grupos',
            /* vetor gerado    */ 'usuarios',
            /* index vetor     */ 'cod_usuario',
            /* campo impressao */ 'usuario');

        // CAMPO: permissoes
        $this->adicionar_rel_un(
            /* nome classe     */ 'permissao',
            /* vetor gerado    */ 'permissoes',
            /* index vetor     */ 'cod_arquivo',
            /* campo impressao */ 'posicao');
    }

}//class
