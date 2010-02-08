<?php
//
// SIMP
// Descricao: Exemplo de utilizacao de Late Static Bindings do PHP 5.3
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 22/12/2009
// Modificado: 22/12/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

//
//     Classe base de exemplo
//
abstract class base {

    //
    //     Metodo de exemplo
    //
    abstract public static function get_nome();

    //
    //     Chama o metodo estatico get_nome da classe filha
    //
    public function testar() {
        return static::get_nome();

        // Isso nao funciona:
        //return self::get_nome();
    }

}


//
//     Classe que extende a classe base
//
final class extensao extends base {

    //
    //     Metodo de exemplo
    //
    public static function get_nome() {
        return 'oi';
    }

}

$x = new extensao();
echo $x->testar();