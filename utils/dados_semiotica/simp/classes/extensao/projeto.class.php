<?php
//
// SIMP
// Descricao: Classe projeto
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 29/05/2009
// Modificado: 09/07/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class projeto extends projeto_base {

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
            if ($usuario->get_pai('analista')->cod_analista == $this->get_atributo('cod_analista')) {
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
        case 'textos':
            $dados->icone     = icone::endereco('livro');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/textos_analises';
            $dados->descricao = 'Textos para An&aacute;lise';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        case 'textos_analista':
            $dados->icone     = icone::endereco('livro');
            $dados->arquivo   = 'index.php';
            $dados->modulo    = $modulo.'/textos_analises';
            $dados->descricao = 'Textos para An&aacute;lise';
            $dados->id        = '';
            $dados->class     = '';
            return $dados;
        }

        return parent::dados_opcao($opcao, $modulo);
    }


    //
    //     Copia um texto para o projeto
    //
    public function copiar_texto($texto) {
    // texto_analise $texto: texto a ser copiado
    //
        $novo_texto = new texto_analise();
        $novo_texto->titulo = $texto->titulo;
        $novo_texto->arquivo = $texto->arquivo;
        $novo_texto->cod_projeto = $this->get_valor_chave();

        if (!$novo_texto->salvar()) {
            $this->erros[] = 'Erro ao salvar o texto "'.$texto_analise->get_nome().'"';
            $this->erros[] = $novo_texto->get_erros();
            return false;
        }

        // Copiar as frase
        foreach ($texto->frases as $frase) {
            $nova_frase = new frase();
            $nova_frase->conteudo = $frase->conteudo;
            $nova_frase->cod_texto_analise = $novo_texto->get_valor_chave();
            if (!$nova_frase->salvar()) {
                $this->erros[] = 'Erro ao salvar a frase "'.$frase->get_nome().'"';
                $this->erros[] = $nova_frase->get_erros();
                return false;
            }

            // Copiar as analises
            foreach ($frase->analises as $analise) {
                $nova_analise = new analise();
                $nova_analise->cod_categoria_analise = $analise->cod_categoria_analise;
                $nova_analise->cod_frase = $nova_frase->get_valor_chave();
                if (!$nova_analise->salvar()) {
                    $this->erros[] = 'Erro ao salvar a analise da frase "'.$frase->get_nome().'"';
                    $this->erros[] = $nova_analise->get_erros();
                    return false;
                }
            }
        }

        $this->avisos[] = 'Texto copiado com sucesso';
        return true;
    }

}//class
