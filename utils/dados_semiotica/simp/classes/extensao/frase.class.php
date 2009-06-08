<?php
//
// SIMP
// Descricao: Classe frase
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class frase extends frase_base {

    //
    //     Retorna um vetor com os dados da opcao (icone) que aparece na lista de entidades
    //
    public function dados_opcao($opcao, $modulo) {
    // String $opcao: identificador da opcao
    // String $modulo: nome do modulo
    //
        $dados = new stdClass();

        switch ($opcao) {
        case 'analises':
            $dados->icone     = icone::endereco('pesquisa');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/analises';
            $dados->descricao = 'An&aacute;lises';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        }

        return parent::dados_opcao($opcao, $modulo);
    }

}//class
