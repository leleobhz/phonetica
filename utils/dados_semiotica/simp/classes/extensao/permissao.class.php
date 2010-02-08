<?php
//
// SIMP
// Descricao: Classe Permissoes dos Arquivos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.9
// Data: 10/09/2007
// Modificado: 10/12/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class permissao extends permissao_base {

    //
    //     Operacao pre-salvar
    //
    public function pre_salvar(&$salvar_campos) {
    // Array[String] $salvar_campos: campos a serem salvos
    //
        $posicao = $this->get_atributo('posicao');
        $grupo   = $this->get_atributo('cod_grupo');

        switch ($this->id_form) {
        case $this->id_formulario_excluir():

            // UPDATE {$tabela} SET posicao = posicao - 1 WHERE cod_grupo = '{$grupo}' AND posicao > '{$posicao}'
            $dados = new stdClass();
            $dados->posicao = 'sql:'.self::$dao->delimitar_campo('posicao').' - 1';

            $vt_condicoes = array();
            $vt_condicoes[] = condicao_sql::montar('cod_grupo', '=', $grupo);
            $vt_condicoes[] = condicao_sql::montar('posicao', '>', $posicao);
            $condicoes = condicao_sql::sql_and($vt_condicoes);

            $r = self::$dao->update($this, $dados, $condicoes);
            if (!$r) {
                $this->erros[] = 'Erro ao atualizar posi&ccedil;&otilde;es';
            }
            break;

        case $this->id_formulario_inserir():
            $vt_condicoes = array();
            $vt_condicoes[] = condicao_sql::montar('cod_arquivo', '=', $this->get_atributo('cod_arquivo'));
            $vt_condicoes[] = condicao_sql::montar('cod_grupo', '=', $this->get_atributo('cod_grupo'));
            $condicoes = condicao_sql::sql_and($vt_condicoes);

            if ($this->possui_registros($condicoes)) {
                $this->erros[] = 'Este grupo j&aacute; tem permiss&atilde;o para este arquivo';
                return false;
            }

            // UPDATE {$tabela} SET posicao = posicao + 1 WHERE cod_grupo = '{$grupo}' AND posicao >= '{$posicao}'
            $dados = new stdClass();
            $dados->posicao = 'sql:'.self::$dao->delimitar_campo('posicao').' + 1';

            $vt_condicoes = array();
            $vt_condicoes[] = condicao_sql::montar('cod_grupo', '=', $grupo);
            $vt_condicoes[] = condicao_sql::montar('posicao', '>=', $posicao);
            $condicoes = condicao_sql::sql_and($vt_condicoes);
            $r = self::$dao->update($this, $dados, $condicoes);
            if (!$r) {
                $this->erros[] = 'Erro ao atualizar posi&ccedil;&otilde;es';
            }
            break;
        }
        return $r;
    }


    //
    //     Operacoes pos-salvar
    //
    public function pos_salvar() {
        switch ($this->id_form) {
        case $this->id_formulario_inserir():
        case $this->id_formulario_excluir():
            objeto::limpar_cache('grupo');
            objeto::limpar_cache('permissao');
            break;
        }
        return true;
    }


    //
    //     Obtem a posicao da ultima permissao de um grupo
    //
    public function maior($cod_grupo) {
    // Int $cod_grupo: codigo do grupo
    //
        $cod_grupo = (int)$cod_grupo;
        $condicao = condicao_sql::montar('cod_grupo', '=', $cod_grupo);
        return self::$dao->select_maior($this, 'posicao', $condicao);
    }


    //
    //     Desce um item
    //
    public function descer() {
        if (!$this->existe()) {
            $this->erros[] = 'N&atilde;o pode descer um item que n&atilde;o existe';
            return false;
        }

        // Obter proxima posicao
        $proxima_permissao = new self();
        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('cod_grupo', '=', $this->get_atributo('cod_grupo'));
        $vt_condicoes[] = condicao_sql::montar('posicao', '=', $this->get_atributo('posicao') + 1);
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        $proxima_permissao->consultar_condicoes($condicoes, array('posicao'));

        // Se nao existe proxima posicao
        if (!$proxima_permissao->existe()) {
            $this->erros[] = 'N&atilde;o pode descer este item';
            return false;
        }

        // Alterar as posicoes
        $posicao = $this->get_atributo('posicao');
        $this->__set('posicao', $posicao + 1);
        $proxima_permissao->__set('posicao', $posicao);

        $retorno = objeto::inicio_transacao(DRIVER_BASE_SERIALIZABLE) &&
                   $this->salvar(array('posicao')) &&
                   $proxima_permissao->salvar(array('posicao')) &&
                   objeto::fim_transacao();
        if (!$retorno) {
            $this->erros[] = 'Erro ao descer o item no menu';
        }
        return true;
    }


    //
    //     Sobe um item
    //
    public function subir() {
        if (!$this->existe()) {
            $this->erros[] = 'N&atilde;o pode subir um item que n&atilde;o existe';
            return false;
        }

        // Obter proxima posicao
        $permissao_anterior = new self();
        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('cod_grupo', '=', $this->get_atributo('cod_grupo'));
        $vt_condicoes[] = condicao_sql::montar('posicao', '=', $this->get_atributo('posicao') - 1);
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        $permissao_anterior->consultar_condicoes($condicoes, array('posicao'));

        // Se nao existe posicao anterior
        if (!$permissao_anterior->existe()) {
            $this->erros[] = 'N&atilde;o pode subir este item';
            return false;
        }

        // Alterar as posicoes
        $posicao = $this->get_atributo('posicao');
        $this->__set('posicao', $posicao - 1);
        $permissao_anterior->__set('posicao', $posicao);

        $retorno = objeto::inicio_transacao(DRIVER_BASE_SERIALIZABLE) &&
                   $this->salvar(array('posicao')) &&
                   $permissao_anterior->salvar(array('posicao')) &&
                   objeto::fim_transacao();
        if (!$retorno) {
            $this->erros[] = 'Erro ao subir o item no menu';
        }
        return true;
    }


    //
    //     Imprime um campo do formulario
    //
    public function campo_formulario(&$form, $campo, $valor) {
    // formulario $form: objeto do tipo formulario
    // String $campo: campo a ser adicionado
    // Mixed $valor: valor a ser preenchido automaticamente
    //
        $atributo = $this->get_definicao_atributo($campo);

        switch ($campo) {
        case 'cod_arquivo':
            $modulos = listas::get_modulos();
            $obj = $this->get_objeto_rel_uu('arquivo');
            $vetor = array();
            foreach ($modulos as $modulo) {
                $condicoes = condicao_sql::montar('modulo', '=', $modulo);
                $vetor[$modulo] = $obj->vetor_associativo('cod_arquivo', 'arquivo', $condicoes);
            }
            $form->campo_select($atributo->nome, $atributo->nome, $vetor, $valor, $obj->get_entidade());
            return true;

        case 'posicao':
            if ($cod_grupo = $this->get_atributo('cod_grupo')) {
                if (!$valor) {
                    $condicao = condicao_sql::montar('cod_grupo', '=', $cod_grupo);
                    $valor = $this->quantidade_registros($condicao) + 1;
                } else {
                    $condicao = null;
                }
                $form->campo_relacionamento($atributo->nome, $atributo->nome, 'permissao', 'posicao', 'arquivo:descricao', $condicao, $valor, 3, 5, $atributo->get_label($this->id_form));
            } else {
                $form->campo_text($atributo->nome, $atributo->nome, $valor, $atributo->tamanho_maximo, 30, $atributo->get_label($this->id_form));
            }
            return true;
        }
        return parent::campo_formulario($form, $campo, $valor);
    }


    //
    //     Retorna o conteudo do arquivo INI de permissoes para um grupo
    //
    public function get_ini($grupo, &$nome_arquivo = '') {
    // grupo $grupo: grupo desejado
    // String $nome_arquivo: sugestao de nome de arquivo
    //
        $nome_grupo = texto::strip_acentos($grupo->nome);
        $nome_arquivo = texto::strtolower(str_replace(' ', '_', $nome_grupo)).'.ini';
        $data = strftime('%d/%m/%Y', $CFG->time);
        $ini = <<<INI
;
; SIMP
; Descricao: Permissoes do grupo {$nome_grupo}
; Autor: simp
; Versao: 1.0.0.0
; Data: {$data}
; Modificado: {$data}
; License: LICENSE.TXT
;

; Codigo do grupo em questao
cod_grupo = {$grupo->cod_grupo}


INI;

        $ordem = array('posicao' => true);
        $condicao = condicao_sql::montar('cod_grupo', '=', $grupo->cod_grupo);
        $modulos = $this->vetor_associativo('cod_permissao', 'arquivo:modulo', $condicao, $ordem);
        $modulos = array_unique(array_values($modulos));

        foreach ($modulos as $modulo) {
            if ($modulo) {
                $ini .= "[{$modulo}]\n";
            } else {
                $ini .= "[simp]\n";
            }
            $condicao2 = condicao_sql::montar('arquivo:modulo', '=', $modulo);
            $condicao3 = condicao_sql::sql_and(array($condicao, $condicao2));
            $permissoes = $this->vetor_associativo('arquivo:arquivo', 'visivel', $condicao3, $ordem);
            if (count($permissoes)) {
                $maior = max(array_map('strlen', array_keys($permissoes)));
                foreach ($permissoes as $arq => $visivel) {
                    $visivel = $visivel ? 1 : 0;
                    $ini .= sprintf("%-{$maior}s = %d\n", $arq, $visivel);
                }
            } else {
                $ini .= "; nenhuma permissao\n";
            }
            $ini .= "\n";
        }
        return $ini;
    }

}//class
