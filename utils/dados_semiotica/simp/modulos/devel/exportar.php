<?php
//
// SIMP
// Descricao: exporta dados de uma tabela para CSV ou XML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.5
// Data: 04/10/2007
// Modificado: 15/07/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');

/// Constantes
define('DEVEL_CSV', 1);
define('DEVEL_XML', 2);


/// Dados do Formulario
$action = $CFG->site;
$dados  = formulario::get_dados();


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Exportar Dados';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Exportar Dados';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
if ((!$dados) || (!count($dados->entidades))) {
    $pagina = new pagina();
    $pagina->cabecalho($titulo, $nav, $estilos);
    $pagina->inicio_conteudo();
    if ($dados) {
        mensagem::erro('&Eacute; necess&aacute;rio selecionar pelo menos uma entidade');
    }
    if (!$CFG->instalacao) {
        echo '<p>O sistema ainda n&atilde;o foi instalado.</p>';
    } else {
        formulario_exportar($dados, $action);
    }
    $pagina->fim_conteudo();
    $pagina->rodape();
    exit(0);

/// Exportar
} else {
    $nome_arquivo = 'backup_'.strtolower($CFG->sistema).'-'.strftime('%Y-%m-%d').'.zip';

    $arquivos = array();
    switch ($dados->tipo) {

    // CSV
    case DEVEL_CSV:

        // Para cada entidade selecionada
        foreach ($dados->entidades as $classe) {
            $obj = new $classe();
            $vt = $obj->consultar_varios(condicao_sql::vazia(), true, array($obj->get_chave() => 1), false);
            $atributos = array_keys($obj->get_atributos());
            if ($vt) {

                // Gerar cabecalho CSV
                $buffer = implode(',', $atributos)."\n";

                // Gerar conteudo CSV
                foreach ($vt as $item) {
                    $buffer .= $item->exportar_csv($atributos);
                }
                $arquivos[$classe.'.csv'] = $buffer;
            }
        }
        break;

    // XML
    case DEVEL_XML:

        // Para cada entidade selecionada
        foreach ($dados->entidades as $classe) {
            $obj = new $classe();
            $vt = $obj->consultar_varios(null, true, $obj->get_chave(), false);
            $atributos = array_keys($obj->get_atributos());
            if ($vt) {
                $buffer = "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n".
                          '<xml>';
                foreach ($vt as $item) {
                    $buffer .= $item->exportar_xml($atributos)."\n";
                }
                $buffer .= '</xml>';
                $arquivos[$classe.'.xml'] = $buffer;
            }
        }
        break;
    }

    if (count($arquivos)) {
        compactacao::compactar($arquivos, $nome_arquivo);
    } else {
        pagina::erro(false, 'Nenhum dado a ser gerado');
    }

}
exit(0);


//
//     Imprime o formulario de exportacao
//
function formulario_exportar($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    $vt_entidades = listas::get_entidades();

    $vt_tipos = array(DEVEL_CSV => 'CSV',
                      DEVEL_XML => 'XML');

    $campos = array('entidades' => array(), 'tipo' => 1);
    $dados = formulario::montar_dados($campos, $dados);

    $form = new formulario($action, 'form_exportar', false, false, false);
    $form->campo_checkbox('entidades', 'entidades', $vt_entidades, $dados->entidades, 'Entidades', 2, false, true);
    $form->campo_select('tipo', 'tipo', $vt_tipos, $dados->tipo, 'Tipo');
    $form->campo_submit('enviar', 'enviar', 'Exportar', 1);
    $form->imprimir();
}


