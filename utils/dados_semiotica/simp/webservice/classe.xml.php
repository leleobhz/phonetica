<?php
//
// SIMP
// Descricao: Gera a descricao das classes em formato XML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 02/03/2009
// Modificado: 02/03/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
define('IGNORAR_SESSAO', 1);
require_once('../config.php');


/// Obter classe
$classe = util::get_dado('classe', 'string');


/// Obter entidade
try {
    simp_autoload($classe);
    $entidade = new $classe();
} catch (Exception $e) {
    pagina::erro(null, "Classe {$classe} possui erros.", $e->getMessage());
}

/// Montar conteudo XML
$xml = "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n".
       $entidade->get_definicao_xml();

/// Exibir os possiveis itens
setlocale(LC_ALL, 'C');
header('Content-type: text/xml; charset='.$CFG->charset);
header("Content-Disposition: inline; filename={$classe}.xml");
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
