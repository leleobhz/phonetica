<?php
//
// SIMP
// Descricao: Classe texto_analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class texto_analise extends texto_analise_base {

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
            if ($usuario->get_pai('analista')->cod_analista == $this->get_atributo('projeto:cod_analista')) {
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
        case 'frases':
            $dados->icone     = icone::endereco('lista');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/frases';
            $dados->descricao = 'Frases para An&aacute;lise';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        case 'frases_analista':
            $dados->icone     = icone::endereco('lista');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/frases';
            $dados->descricao = 'Frases para An&aacute;lise';
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
