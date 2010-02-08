<?php
//
// SIMP
// Descricao: Classe de definicao do atributo telefone
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 22/10/2009
// Modificado: 22/10/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
class atributo_telefone {


    //
    //     Retorna o atributo com as caracteristicas definidas
    //
    public static function get_instancia($nome = 'telefone', $opcoes = array()) {
    // String $nome: nome do atributo
    // Array[String => Mixed] $opcoes: opcoes adicionais
    //
        $atributo = new atributo($nome, 'Telefone', '(00) 0000-0000');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 30);
        $atributo->set_validacao('FONE', false, false);

        // Definir opcoes extras
        foreach ($opcoes as $opcao => $valor) {
            $atributo->$opcao = $valor;
        }

        return $atributo;
    }

}//class
