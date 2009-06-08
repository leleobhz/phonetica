<?php
//
// SIMP
// Descricao: Classe Abstrata Objeto Formulario Confirmacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 26/03/2009
// Modificado: 22/04/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
abstract class objeto_formulario_confirmacao extends objeto_formulario {


/// @ METODOS DE LOGICA


    //
    //     Faz a Logica de um formulario de alteracao de dados mediante confirmacao
    //
    protected function logica_formulario_confirmar(&$dados, $campos, $opcoes = false, $captcha = false) {
    // Object $dados: dados submetidos pelo formulario
    // Array[String] $campos: campos reais vindos do formulario
    // Array[String => Mixed] $opcoes: opcoes a serem salvas
    // Bool $captcha: indica se um campo captcha foi solicitado no formulario
    //
        // Se os dados nao foram submetidos
        if (!isset($dados->id_form) ||
            $dados->id_form != $this->id_form) {
            return null;
        }

        // Se o formulario possui um campo captcha
        if ($captcha && !captcha::validar($dados->captcha)) {
            $this->erros[] = 'O texto da imagem est&aacute; incorreto';
        }

        $classe = $this->get_classe();
        $chave_confirmacao = $this->chave_confirmacao();

        // Checar se o usuario confirmou
        if (!$dados->confirmacao) {
            $this->avisos[] = 'Nada foi feito (marque a confirma&ccedil;&atilde;o)';
            $this->imprimir_avisos();
            return null;
        }

        // Fazer as validacoes sobre a chave de confirmacao
        if (!isset($dados->chave_confirmacao)) {
            $this->erros[] = 'N&atilde;o foi informada a chave de confirma&ccedil;&atilde;o (Erro inesperado)';
        } elseif (strcmp($dados->chave_confirmacao, $chave_confirmacao) != 0) {
            $this->erros[] = 'Chave para confirma&ccedil;&atilde;o n&atilde;o confere (Erro inesperado)';
        }

        // Se houve erros
        if ($this->possui_erros()) {
            $this->imprimir_erros();
            return false;
        }

        // Montar vetor de opcoes adicionais e retorna o vetor de campos a serem salvos
        $salvar_campos = $this->montar_opcoes($dados, $campos, $opcoes);
        if (is_array($opcoes) && count($opcoes)) {
            $this->names = util::array_unique_recursivo(array_merge($this->names, array_keys($opcoes)));
        }

        // Se nao conseguir confirmar
        if (!$this->set_valores($dados->$classe, util::array_unique_recursivo($this->names), true) ||
            !$this->salvar_completo($salvar_campos, 'salvar')) {
            $this->imprimir_erros();
            return false;
        }

        // Se conseguiu salvar os valores
        $this->imprimir_avisos();
        return true;
    }


/// @ METODOS AUXILIARES


    //
    //     Retorna uma chave unica de confirmacao de um elemento da entidade
    //
    final protected function chave_confirmacao() {
         $c = $this->get_classe().'.'.                       // Nome da classe da entidade
              $this->get_chave().'.'.                        // Nome da chave primaria da entidade
              $_SERVER['REMOTE_ADDR'].'.'.                   // IP de quem esta' solicitando
              count($this->get_atributos()).'.'.             // Numero de atributos da entidade
              $this->get_valor_chave().'.'.                  // Valor da chave da entidade
              strftime('%d', time());                        // Dia em que ocorreu a solicitacao

         $c = md5($c);

         return $c;
    }


    //
    //     ID do formulario de confirmacao
    //
    final public function id_formulario_confirmar($prefixo = '') {
    // String $prefixo: prefixo do formulario
    //
        if (!empty($prefixo)) {
            $prefixo .= '_';
        }
        return $prefixo.'form_confirmar_'.$this->get_classe();
    }



/// @ METODOS DE APRESENTACAO DOS FORMULARIOS


    //
    //     Logica de geracao de um formulario de confirmacao
    //
    public function formulario_confirmar(&$dados, $mensagem, &$campos, $opcoes, $action, $prefixo_id = '', $class = false, $ajax = true, $nome_botao = false) {
    // Object $dados: dados submetidos
    // String $mensagem: mensagem de confirmacao
    // Array[String || String => Array[String]] $campos: campos do formulario (true = todos)
    // Array[String => Mixed] $opcoes: valores a serm definidos automaticamente apos confirmacao
    // String $action: endereco para envio dos dados
    // String $prefixo_id: prefixo do ID do formulario
    // String $class: nome da classe CSS utilizada
    // Bool $ajax: usar ajax ou nao
    // String $nome_botao: nome do botao de confirmar os dados
    //
        global $USUARIO;

        if ($nome_botao === false) {
            $nome_botao = 'Enviar';
        }

        if (!$this->existe()) {
            trigger_error('Para confirmar precisa ter consultado o objeto', E_USER_WARNING);
            return false;
        }

        $this->set_id_form($this->id_formulario_confirmar(), $prefixo_id);

        // Checar se pode acessar o formulario
        if (!$this->pode_acessar_formulario($USUARIO, $motivo)) {
            $aviso = 'Este registro n&atilde;o pode ser alterado';
            if ($motivo) {
                $aviso .= " (Motivo: {$motivo})";
            }
            mensagem::aviso($aviso);
            if (count($campos)) {
                $this->imprimir_dados($campos, false, false);
            } else {
                echo '<div class="dados">';
                echo '<p>Nenhum dado a ser mostrado</p>';
                echo '</div>';
            }
            return null;
        }

        // Consultar os campos necessarios
        if ($campos === true) {
            $campos = array();
            foreach ($this->get_atributos() as $atributo) {
                if ($atributo->chave != 'PK') {
                    $campos[] = $atributo->nome;
                }
            }
        }
        $vt_campos = $this->get_campos_reais($campos, $objetos, $vetores, OBJETO_ADICIONAR_CHAVES);
        $captcha = in_array('captcha', $campos);
        $this->consultar_campos($vt_campos);

        // Preencher os dados necessarios
        $classe = $this->get_classe();
        if (!isset($dados->id_form)) {
            if ($this->existe()) {
                $dados = new stdClass();
                $dados->$classe = $this->get_dados($vt_campos);
            } else {
                $dados_classe = util::objeto($vt_campos, $dados);
                $dados = new stdClass();
                $dados->$classe = $dados_classe;
            }
            $dados->default = true;
        }

        // Executar a logica de confirmacao
        $r = $this->logica_formulario_confirmar($dados, $campos, $opcoes, $captcha);

        // Se nao submeteu ou ocorreu um erro
        if (!$r) {

            // Cria o formulario
            $form = new formulario($action, $this->id_form, $class, 'post', $ajax);
            $form->titulo_formulario('Formul&aacute;rio de Confirma&ccedil;&atilde;o');

            // Inserir os campos
            foreach ($campos as $chave => $campo) {
                if (is_array($campo)) {
                    $form->inicio_bloco($chave);
                    foreach ($campo as $c) {
                        if (!$this->inserir_campo_formulario($form, $c, $dados, $this->id_form)) {
                            return false;
                        }
                    }
                    $form->fim_bloco();
                } elseif (!$this->inserir_campo_formulario($form, $campo, $dados, $this->id_form)) {
                    return false;
                }
            }
            $form->set_nome(array());

            // Gera uma chave para garantir a confirmacao
            $chave_confirmacao = $this->chave_confirmacao();
            $form->campo_hidden('chave_confirmacao', $chave_confirmacao);
            $form->campo_bool('confirmacao', 'confirmacao', $mensagem, 0);

            // Gera o botao de enviar
            $form->campo_hidden('id_form', $this->id_form);
            $form->campo_submit($this->id_salvar(), $this->id_salvar(), $nome_botao, 1);

            // Imprimir o formulario
            $form->imprimir();

        // Se confirmou os dados
        } else {
            if (count($campos)) {
                $this->imprimir_dados($campos, false, false);
            } else {
                echo '<div class="dados">';
                echo '<p>Confirma&ccedil;&atilde;o efetuada</p>';
                echo '</div>';
            }
        }

        return $r;
    }

}//class
