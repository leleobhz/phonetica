<?php
//
// SIMP
// Descricao: Classe Log do Sistema
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 05/09/2007
// Modificado: 01/06/2008
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
final class log_sistema extends log_sistema_base {

    //
    //     Insere um log
    //
    public function inserir($cod_usuario, $operacao, $erro = false, $cod_entidade = '0', $entidade = '', $detalhes = '') {
    // Int $cod_usuario: codigo do usuario
    // String $operacao: operacoes realizadas no sistema
    // Bool $erro: Determina se a operaao teve erro
    // Int $cod_entidade: codigo da entidade que gerou a operacao
    // String $entidade: nome da entidade que gerou a opeacao
    // String $detalhes: detalhes da operacao ealizada
    //
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'localhost';

        $detalhes = texto::substr($detalhes, 0, 255);
        $this->set_atributo('cod_usuario',  $cod_usuario);
        $this->set_atributo('ip',           $ip);
        $this->set_atributo('operacao',     $operacao);
        $this->set_atributo('cod_entidade', $cod_entidade);
        $this->set_atributo('entidade',     $entidade);
        $this->set_atributo('detalhes',     $detalhes);
        $this->set_atributo('erro',         $erro);
        $this->set_atributo('data',         time());

        return $this->salvar();
    }


    //
    //     Define a forma como um atributo simples e' exibido
    //
    public function exibir_atributo($nome_atributo) {
    // String $nome_atributo: nome do atributo a ser exibido
    //
        switch ($nome_atributo) {
        case 'data':
            return strftime(LOG_SISTEMA_FORMATO_DATA, $this->get_atributo($nome_atributo));
        }
        return parent::exibir_atributo($nome_atributo);
    }

}//class
