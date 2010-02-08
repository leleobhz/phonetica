<?php
//
// SIMP
// Descricao: Classe arquivos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.9
// Data: 10/09/2007
// Modificado: 10/12/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class arquivo extends arquivo_base {


    //
    //     Indica como um campo deve ser apresentado no formulario
    //
    public function campo_formulario(&$form, $campo, $valor) {
    // formulario $form: formulario que deve receber o campo
    // String $campo: nome do campo a ser inserido
    // Mixed $valor: valor padrao do campo
    //
        if ($valor === '' && $this->possui_auxiliar($campo)) {
            $valor = $this->get_auxiliar($campo);
        }
        return parent::campo_formulario($form, $campo, $valor);
    }


    //
    //     Realiza a validacao final dos formularios
    //
    public function validacao_final(&$dados) {
    // Object $dados: dados a serem validados
    //
        switch ($this->id_form) {
        case $this->id_formulario_inserir():
        case $this->id_formulario_alterar():
            if ($this->get_atributo('modulo')) {
                $arq = ARQUIVO_DIR_MODULOS.$this->get_atributo('modulo').'/'.$this->get_atributo('arquivo');
            } else {
                $arq = ARQUIVO_DIR_ROOT.$this->get_atributo('arquivo');
            }
            if (!is_file($arq)) {
                $this->erros[] = 'Arquivo '.$arq.' n&atilde;o existe no sistema';
            }
            break;
        }
        return !$this->possui_erros();
    }


    //
    //     Retorna um vetor de modulos
    //
    public function get_vetor_modulo() {
        return array('' => 'Nenhum') + listas::get_modulos();
    }


    //
    //     Consulta um arquivo pelo nome e arquivo
    //
    public function consultar_arquivo_modulo($arquivo, $modulo, $campos = false) {
    // String $arquivo: nome do arquivo
    // String $modulo: nome do modulo
    // Array[String] $campos: campos desejados (true = todos | false = apenas PK)
    //
        $arquivo = addslashes($arquivo);
        $modulo  = addslashes($modulo);

        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('arquivo', '=', $arquivo);
        $vt_condicoes[] = condicao_sql::montar('modulo', '=', $modulo);
        $condicoes = condicao_sql::sql_and($vt_condicoes);

        $obj = new self();
        if (!$obj->consultar_condicoes($condicoes, $campos)) {
            $this->erros[] = 'Erro ao consultar arquivo ('.$arquivo.') pelo m&oacute;dulo ('.$modulo.')';
        }
        return $obj;
    }


    //
    //     Retorna o arquivo INI com os dados dos arquivos
    //
    public function get_ini() {
        $data = strftime('%d/%m/%Y');
        $ini = <<<INI
;
; SIMP
; Descricao: Lista de Arquivos por Modulo
; Autor: simp
; Versao: 1.0.0.0
; Data: {$data}
; Modificado: {$data}
; License: LICENSE.TXT
;

INI;

        $modulos = array('simp') + listas::get_modulos();
        $ordem = array('arquivo' => true, 'descricao' => true);

        foreach ($modulos as $modulo) {
            $ini .= "[{$modulo}]\n";
            if ($modulo == 'simp') { $modulo = ''; }
            $condicao = condicao_sql::montar('modulo', '=', $modulo);
            $arquivos = $this->vetor_associativo('arquivo', 'descricao', $condicao, $ordem);
            if (count($arquivos)) {
                $maior = max(array_map('strlen', array_keys($arquivos)));
                foreach ($arquivos as $arq => $desc) {
                    $desc = addslashes($desc);
                    $ini .= sprintf("%-{$maior}s = \"%s\"\n", $arq, $desc);
                }
            } else {
                $ini .= "; nenhum arquivo\n";
            }
            $ini .= "\n";
        }
        return $ini;
    }

}//class
