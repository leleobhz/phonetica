<?php
//
// SIMP
// Descricao: Classe grupos do usuario
// Autor: Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 13/08/2007
// Modificado: 06/11/2008
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
abstract class usuarios_grupos_base extends objeto {

    //
    //     Cria a definicao de entidade
    //
    protected function definir_entidade() {
        $this->criar_entidade(
            /* Nome Entidade   */ 'Grupo do Usu&aacute;rio',
            /* Entidade Plural */ 'Grupos do Usu&aacute;rio',
            /* Genero          */ 'M',
            /* Classe          */ 'usuarios_grupos',
            /* Tabela          */ 'rel_usuarios_grupos',
            /* Desc. Tabela    */ 'Relacao de usuarios em grupos');
    }


    //
    //     Cria os atributos da classe
    //
    protected function definir_atributos() {

        // CAMPO: cod_rel_usuarios_grupos
        $atributo = new atributo('cod_rel_usuarios_grupos', 'C&oacute;digo da Rela&ccedil;&atilde;o Usu&aacute;rios e Grupos', null);
        $atributo->set_tipo('int', false, 'PK');
        $atributo->set_intervalo(1, 100000000);
        $atributo->set_validacao(false, false, true);
        $this->adicionar_atributo($atributo);
        unset($atributo);

        // CAMPO: cod_usuario => gera um usuario
        $this->adicionar_rel_uu(
            /* nome classe     */ 'usuario');

        // CAMPO: cod_grupo => gera um grupo
        $this->adicionar_rel_uu(
            /* nome classe     */ 'grupo');

        // Restricao de unicidade
        $this->adicionar_chave_unica_composta(array('cod_usuario', 'cod_grupo'));
    }


    //
    //     Retorna o nome do campo a ser usado para descrever o relacionamento
    //
    public function get_campo_nome() {
        return 'grupo';
    }

}//class
