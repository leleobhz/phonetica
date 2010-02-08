<?php
//
// SIMP
// Descricao: Classe frase
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class frase extends frase_base {

    //
    //     Indica se o registro pode ser manipulado pelo usuario
    //
    public function pode_ser_manipulado(&$usuario) {
    // usuario $usuario: usuario a ser testado
    //
        $r = false;
        if ($usuario->possui_grupo(COD_GERENTES)) {
            $r = true;
        } elseif ($usuario->possui_grupo(COD_ANALISTAS)) {
            if ($usuario->get_pai('analista')->cod_analista == $this->get_atributo('texto_analise:projeto:cod_analista')) {
                $r = true;
            }
        }
        return $r;
    }
    

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
        case 'analises_analista':
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
