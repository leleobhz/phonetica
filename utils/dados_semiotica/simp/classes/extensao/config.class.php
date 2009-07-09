<?php
//
// SIMP
// Descricao: Classe Configuracao do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.15
// Data: 20/08/2007
// Modificado: 30/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class config extends config_base {

    //
    //     Realiza a validacao final do formulario
    //
    public function validacao_final(&$dados) {
    // Object $dados: dados a serem validados
    //
        switch ($this->id_form) {
        case $this->id_formulario_alterar('email');

            // Se selecionar SMTP, validar o host e porta
            if ($this->get_atributo('tipo_email') == CONFIG_EMAIL_SMTP) {
                $validacao = validacao::get_instancia();

                $erro = '';
                if (empty($dados->smtp_host)) {
                    $this->erros[] = 'Faltou preencher o campo Host';
                } elseif (!$validacao->validar_campo('HOST', $dados->smtp_host, $erro)) {
                    if ($erro) {
                        $this->erros[] = "Erro no campo Host (Detalhes: {$erro})";
                    } else {
                        $this->erros[] = 'Erro no campo Host';
                    }
                }

                if ($dados->smtp_porta <= 0) {
                    $this->erros[] = 'Porta inv&aacute;lida';
                }
            }
            break;
        }
        return !$this->possui_erros();
    }


    //
    //    Imprime um campo do formulario
    //
    public function campo_formulario(&$form, $campo, $valor) {
    // formulario $form: objeto do tipo formulario
    // String $campo: campo a ser adicionado
    // Mixed $valor: valor a ser preenchido automaticamente
    //
        if ($this->possui_atributo($campo)) {
            $atributo = $this->get_definicao_atributo($campo);
        }

        switch ($campo) {
        case 'formato_data':
            $form->campo_aviso('Consulte a especifica&ccedil;&atilde;o do <a rel="checar" href="http://www.opengroup.org/onlinepubs/007908799/xsh/strftime.html">Open Group</a> de strftime para cria&ccedil;&atilde;o de formatos de data e hora');
            return parent::campo_formulario($form, $campo, $valor);
        }
        return parent::campo_formulario($form, $campo, $valor);
    }


    //
    //     Retorna um vetor com os tipos de autenticacao
    //
    public function get_vetor_autenticacao() {
        return array_merge(array('simp' => 'Banco de Dados Local'),
                           autenticacao::get_drivers(true));
    }


    //
    //     Retorna um vetor de niveis de opacidade
    //
    public function get_vetor_opaco() {
        return $this->get_vetor_percentagens();
    }


    //
    //     Retorna um vetor de niveis de transparencia
    //
    public function get_vetor_transparencia() {
        return $this->get_vetor_percentagens();
    }


    //
    //     Retorna um vetor com possiveis percentagens
    //
    private function get_vetor_percentagens() {
        $vt_transparencia = array();
        $vt_transparencia['0.3'] = '30% (Transparente)';
        for ($i = 0.35; $i <= 1; $i += 0.05) {
            $vt_transparencia[strval($i)] = ($i * 100).'%';
        }
        $vt_transparencia['1'] = '100% (Opaco)';
        return $vt_transparencia;
    }


    //
    //     Retorna um vetor com os possiveis tipos de envio de e-mail
    //
    public function get_vetor_tipo_email() {
        return array(CONFIG_EMAIL_PADRAO => 'Padr&atilde;o (fun&ccedil;&atilde;o mail do PHP)',
                     CONFIG_EMAIL_SMTP   => 'SMTP');
    }


    //
    //     Retorna um vetor de linguas
    //
    public function get_vetor_lingua() {
        return listas::get_linguas();
    }


    //
    //     Retorna um vetor de localidades
    //
    public function get_vetor_localidade() {
        return array_flip(listas::get_locales());
    }


    //
    //     Retorna um vetor de estados
    //
    public function get_vetor_estado() {
        return listas::get_estados();
    }

}//class
