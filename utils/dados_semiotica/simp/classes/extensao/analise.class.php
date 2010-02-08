<?php
//
// SIMP
// Descricao: Classe analise
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 29/05/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class analise extends analise_base {

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
            if ($usuario->get_pai('analista')->cod_analista == $this->get_atributo('frase:texto_analise:projeto:cod_analista')) {
                $r = true;
            }
        }
        return $r;
    }


    //
    //     Realiza a validacao final dos dados antes de salva-los (util para validar atributos dependentes)
    //
    public function validacao_final(&$dados) {
    // Object $dados: dados a serem validados
    //
        $r = true;
        switch ($this->id_form) {
        case $this->id_formulario_inserir():
        case $this->id_formulario_alterar():
        case $this->id_formulario_inserir('analista'):
        case $this->id_formulario_alterar('analista'):
            foreach ($this->get_objeto_rel_uu('frase')->analises as $analise) {
                if ($analise->cod_categoria_analise == $this->__get('cod_categoria_analise') &&
                    $analise->get_valor_chave() != $this->get_valor_chave()) {
                    $this->erros[] = 'Esta frase j&aacute; possui uma an&aacute;lise com esta categoria';
                    $r = false;
                    break;
                }
            }
            break;
        }
        return $r;
    }


}//class
