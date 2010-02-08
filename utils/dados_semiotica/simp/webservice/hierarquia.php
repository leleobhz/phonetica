<?php
//
// SIMP
// Descricao: Arquivo para listar entidades e codigos no formato HTML (acessivel para navegadores modo texto)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 11/04/2008
// Modificado: 29/07/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');

// Obter link do XML a ser aberto
$link  = util::get_dado('link', 'string');

// Consultar o XML
$xml_str = file_get_contents($link);
$xml = simplexml_load_string($xml_str);
if (!$xml) {
    pagina::erro(null, 'Erro ao listar entidades');
    exit(1);
}
$titulo  = $xml['nome'];
$nav[''] = $titulo;
$estilos = false;


/// Exibir os itens
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
listar_entidades($xml);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Lista os codigos e entidades recursivamente
//
function listar_entidades(&$xml) {
// SimpleXMLElement $xml: xml a ser impresso
//
    $valor = isset($xml['valor']) ? '<strong>'.$xml['valor'].'</strong> - ' : '';
    $nome = $xml['nome'];
    $filhos = $xml->children();

    echo '<p>'.$valor.$nome.'</p>';
    if (count($filhos)) {
        echo '<ul>';
        foreach ($filhos as $filho) {
            echo '<li>';
            listar_entidades($filho);
            echo '</li>';
        }
        echo '</ul>';
    }
}
