<?php
//
// SIMP
// Descricao: imprime as informacoes guardadas em $CFG
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 31/07/2007
// Modificado: 07/02/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Dados do $CFG';
$nav = array();
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Dados do $CFG';
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_config('CFG', $CFG);
echo "<noscript>\n";
echo "  <p>Este navegador n&atilde;o d&aacute; suporte &agrave; JavaScript ou o mesmo se encontra desabilitado.</p>\n";
echo "  <p>Para exibir corretamente o conte&uacute;do desta p&aacute;gina &eacute; preciso que o JavaScript esteja habilitado</p>\n";
echo "</noscript>\n";
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime os dados do $CFG (recursivamente)
//
function imprimir_config($nome, &$e) {
// String $nome: nome do elemento
// Mixed $e: elemento a ser impresso
//
    static $i = 1;

    $tipo = util::get_tipo($e);
    switch ($tipo) {

    // Vetor ou objeto
    case 'array':
    case 'object':
        echo "<p class=\"item\"><strong onclick=\"return mudar('elemento{$i}');\">{$nome} ({$tipo})</strong></p>\n";
        echo "<div id=\"elemento{$i}\" class=\"hide\">\n";
        $i++;
        foreach ($e as $chave => $valor) {
            imprimir_config($chave, $valor);
            $i++;
        }
        echo "</div>\n";
        break;
    default:
        $valor = util::exibir_var($e);
        echo "<p>{$nome} ({$tipo}): {$valor}</p>\n";
        break;
    }
}
