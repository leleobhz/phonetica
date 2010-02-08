<?php
//
// SIMP
// Descricao: Arquivo para listar entidades e codigos de maneira acessivel
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.2
// Data: 11/04/2008
// Modificado: 28/10/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

// Obter link do XML a ser aberto
$link  = util::get_dado('link', 'string');

// Consultar o XML
$xml_str = http::get_conteudo_link($link);
$xml = simplexml_load_string($xml_str);
if (!$xml) {
    pagina::erro(null, 'Erro ao listar entidades');
    exit(1);
}


/// Dados da pagina
$titulo  = 'Busca';
$nav[]   = 'Busca';
$estilos = false;

/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_entidades($xml);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de entidades
//
function listar_entidades($xml) {
// SimpleXMLElement $xml: estrutura XML com os codigos e valores possiveis
//
    echo '<table class="tabela" summary="Tabela de c&oacute;digos e valores possiveis">';
    echo '<caption>Tabela de c&oacute;digos e valores possiveis</caption>';
    echo '<thead>';
    echo '<tr>';
    echo '<th id="codigos" scope="col">C&oacute;digo</th>';
    echo '<th id="valores" scope="col">Valor</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($xml->children() as $item) {
        echo '<tr><td headers="codigos">'.$item->codigo.'</td><td headers="valores">'.$item->valor.'</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
