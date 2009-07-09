<?php
//
// SIMP
// Descricao: Token das SQL especiais do Simp
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 06/07/2009
// Modificado: 06/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Tipos de tokens
define('TOKEN_SQL_STRING',           1);
define('TOKEN_SQL_STRING_ASPAS',     2);
define('TOKEN_SQL_ABRE_PARENTESES',  3);
define('TOKEN_SQL_FECHA_PARENTESES', 4);

final class token_sql {
    public $tipo;
    public $valor;


    //
    //     Construtor padrao
    //
    public function __construct($tipo, $valor) {
    // Int $tipo: tipo de token
    // String $valor: valor do token
    //
        $this->tipo = $tipo;
        $this->valor = $valor;
    }

}//class
