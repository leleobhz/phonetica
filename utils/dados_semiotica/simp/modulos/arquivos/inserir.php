<?php
//
// SIMP
// Descricao: Arquivo para cadastrar dados dos arquivos do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.3
// Data: 25/09/2007
// Modificado: 01/02/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados para o formulario
$dados_form = new stdClass();
$dados_form->campos = array('descricao', 'modulo', 'arquivo');
$dados_form->ajuda  = <<<AJUDA
  <p>Formul&aacute;rio para a cria&ccedil;&atilde;o de novos arquivos no sistema.</p>
  <p>Certifique-se que o m&oacute;dulo e o arquivo existam.</p>
AJUDA;
$dados_form->funcao_operacoes = 'operacoes';
modulo::inserir('arquivo', $dados_form);


/// Funcoes


//
//     Operacoes antes de exibir a pagina
//
function operacoes(&$entidade) {
// Object $entidade: objeto da classe arquivo
//
    $descricao_padrao = util::get_dado('descricao', 'string', false);
    if ($descricao_padrao) {
        $entidade->set_auxiliar('descricao', $descricao_padrao);
    }
    $modulo_padrao = util::get_dado('modulo', 'string', false);
    if ($modulo_padrao) {
        $entidade->set_auxiliar('modulo', $modulo_padrao);
    }
    $arquivo_padrao = util::get_dado('arquivo', 'string', false);
    if ($arquivo_padrao) {
        $entidade->set_auxiliar('arquivo', $arquivo_padrao);
    }
}
