<?php
//
// SIMP
// Descricao: Classe texto_analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class texto_analise extends texto_analise_base {

    //
    //     Retorna um vetor com os dados da opcao (icone) que aparece na lista de entidades
    //
    public function dados_opcao($opcao, $modulo) {
    // String $opcao: identificador da opcao
    // String $modulo: nome do modulo
    //
        $dados = new stdClass();

        switch ($opcao) {
        case 'frases':
            $dados->icone     = icone::endereco('lista');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/frases';
            $dados->descricao = 'Frases para Analise';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        case 'copiar':
            $dados->icone     = icone::endereco('duplicar');
            $dados->arquivo   = 'copiar.php';
            $dados->modulo    = $modulo;
            $dados->descricao = 'Copiar Texto de outro Projeto';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        }

        return parent::dados_opcao($opcao, $modulo);
    }

}//class
