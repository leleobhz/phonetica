<?php
//
// SIMP
// Descricao: Arquivo para listar entidades e codigos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.18
// Data: 20/12/2007
// Modificado: 13/01/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');


/// Obter dados
$dados = util::get_dado('dados', 'string');
$input = util::get_dado('input', 'string', false);
list($classe, $campo_preencher, $campo_exibir, $condicoes) = explode(';', base64_decode($dados));
$condicoes = unserialize(base64_decode($condicoes));
if (is_null($condicoes) || (is_string($condicoes) && empty($condicoes))) {
    $condicoes = condicao_sql::vazia();
}

/// Obter entidade
try {
    simp_autoload($classe);
    $entidade = new $classe();
} catch (Exception $e) {
    pagina::erro(null, "Classe {$classe} possui erros.", $e->getMessage());
}


/// Consultar a quantidade de entidades
$total = $entidade->quantidade_registros($condicoes);
$passo = 500;

/// Montar conteudo XML
$xml = "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n".
       "<?xml-stylesheet type=\"text/xsl\" href=\"{$CFG->wwwroot}webservice/relacionamento.xsl.php?dados={$dados}&amp;input={$input}\" ?>\n".
       "<entidades>\n";
for ($i = 0; $i < $total; $i += $passo) {
    $vetor = $entidade->vetor_associativo($campo_preencher, $campo_exibir, $condicoes, array($campo_preencher => 1), $passo, $i);
    foreach ($vetor as $codigo => $valor) {
        $xml .= "<entidade>".
                "<codigo><![CDATA[{$codigo}]]></codigo>".
                "<valor><![CDATA[{$valor}]]></valor>\n".
                "</entidade>\n";
    }
}
//$xml .= '<memoria>'.texto::formatar_bytes(memory_get_usage())."</memoria>\n";
$xml .= "</entidades>";

/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename=lista_{$classe}.xml");
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, $CFG->time));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
setlocale(LC_ALL, $CFG->localidade);
compactacao::header($xml);
echo $xml;
exit(0);
