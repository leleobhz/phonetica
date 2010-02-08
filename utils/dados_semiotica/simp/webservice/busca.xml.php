<?php
//
// SIMP
// Descricao: Script de busca de um campo semelhante
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.1.3
// Data: 30/04/2008
// Modificado: 13/01/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');


/// Obter dados
$dados = util::get_dado('dados', 'string');
$busca = util::get_dado('busca', 'string');
list($classe, $campo, $condicoes_extras) = explode(';', base64_decode($dados));
$condicoes_extras = unserialize(base64_decode($condicoes_extras));
$condicoes = condicao_sql::montar($campo, 'LIKE', '%'.$busca.'%');
if ($condicoes_extras) {
    $condicoes = condicao_sql::sql_and(array($condicoes, $condicoes_extras));
}


/// Obter entidade
try {
    simp_autoload($classe);
    $entidade = new $classe();
} catch (Exception $e) {
    pagina::erro(null, "Classe {$classe} possui erros.", $e->getMessage());
}


/// Consultar as entidades
$entidades = $entidade->consultar_varios($condicoes, array($campo), array($campo => 1), $campo);


/// Montar conteudo XML
$xml = "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n".
       "<busca>\n";
foreach ($entidades as $entidade) {
    $valor = $entidade->__get($campo);
    $xml .= "<resultado><![CDATA[{$valor}]]></resultado>\n";
}
//$xml .= '<memoria>'.texto::formatar_bytes(memory_get_usage())."</memoria>\n";
$xml .= "</busca>";

/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename=busca_{$classe}.xml");
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
