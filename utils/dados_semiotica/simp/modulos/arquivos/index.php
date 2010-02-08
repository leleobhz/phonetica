<?php
//
// SIMP
// Descricao: Lista de Arquivos do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.6
// Data: 25/09/2007
// Modificado: 26/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');

$modulo = util::get_modulo(__FILE__);
$dados_pagina->nav[] = '#index.php';
$dados_pagina->nav[] = $modulo.'#'.basename(__FILE__);
modulo::pagina('listar_arquivos', $dados_pagina);


/// Funcoes


//
//     Imprime a lista de arquivos por modulos
//
function listar_arquivos($pagina) {
// pagina $pagina: objeto da classe pagina
//
    global $CFG, $modulo;

$ajuda  = <<<AJUDA
  <p>A tabela a seguir apresenta a lista de arquivos do sistema
  separados por m&oacute;dulos.</p>
  <p>Ela define quais os nomes dos arquivos que os usu&aacute;rios
  poder&atilde;o acessar. As permiss&otilde;es de acesso s&atilde;o
  definidas no m&oacute;dulo "Permiss&otilde;es".</p>
AJUDA;
    mensagem::comentario($CFG->site, $ajuda);

    $campos = array('arquivo', 'modulo', 'descricao');
    $arquivos = objeto::get_objeto('arquivo')->consultar_varios(condicao_sql::vazia(), $campos, array('modulo' => 1));
    if ($arquivos) {
        $modulos = vetor::agrupar_por_atributo($arquivos, 'modulo');

        $classlinha = 'class="linha"';
        $classop    = 'class="opcoes"';

        lista::inicio(false, 'lista_arquivos');

        // Imprime os modulos
        foreach ($modulos as $m => $arquivos_modulo) {
            $nome_modulo = $m ? 'M&oacute;dulo '.$m : 'Arquivos do Sistema';
            lista::titulo($nome_modulo);

            // Imprime os arquivos do modulo
            foreach ($arquivos_modulo as $arquivo) {
                $chave = $arquivo->get_chave();
                $cod   = $arquivo->get_valor_chave();

                // Gerar as opcoes
                $opcoes = "<div {$classop}>".
                          "<strong class=\"hide\">Op&ccedil;&otilde;es:</strong>".
                          link::icone("{$CFG->wwwmods}{$modulo}/alterar.php?{$chave}={$cod}", icone::endereco('editar'), 'Editar').
                          "<span class=\"hide\">|</span>".
                          link::icone("{$CFG->wwwmods}{$modulo}/excluir.php?{$chave}={$cod}", icone::endereco('excluir'), 'Excluir').
                          "</div>";

                $link = $CFG->wwwmods.$modulo.'/exibir.php?cod_arquivo='.$cod;

                // Imprimir linha com um arquivo
                echo "  <div {$classlinha}>\n";
                link::texto($link, texto::codificar($arquivo->descricao), '', '', 'label');
                echo "    {$opcoes}\n";
                echo "  </div>\n";
            }
        }
        lista::fim();
    } else {
        mensagem::aviso('Nenhum arquivo');
    }
    imprimir_links($pagina);
}


//
//     Imprime uma lista de links
//
function imprimir_links($pagina) {
// Object $pagina: pagina que esta exibindo os links
//
    global $CFG, $USUARIO, $modulo;

    $links = array(link::arquivo_modulo($USUARIO, 'inserir.php', $modulo, false, '', 'inserir', 1),
                   link::arquivo_modulo($USUARIO, 'inconsistencias.php', $modulo, false, '', 'inconsistencia', 1),
                   link::arquivo_modulo($USUARIO, 'gerar_ini.php', $modulo, false, '', 'ini', 1, true, true, false));

    $pagina->listar_opcoes($links);
}

