<?php
//
// SIMP
// Descricao: Arquivo Principal
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 03/03/2007
// Modificado: 14/08/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('./config.php');
require_once($CFG->dirroot.'sessao.php');

/// Dados da Pagina
$titulo  = 'Apresenta&ccedil;&atilde;o';
$nav[]   = '#index.php';
$estilos = array($CFG->wwwlayout.'calendario.css',
                 $CFG->wwwlayout.'principal.css');


/// Imprimir pagina
$pagina = new pagina();
$pagina->adicionar_rss("{$CFG->wwwmods}eventos/eventos.rss.php", "Eventos ({$CFG->titulo})");
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
apresentacao();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


///     Funcoes


//
//     Exibe a apresentacao do sistema
//
function apresentacao() {
    global $CFG, $USUARIO;

    $evento = new evento();

    // Consultar grupos do usuario
    $vt_grupos = array(0);
    if (is_array($USUARIO->grupos)) {
        foreach ($USUARIO->grupos as $g) {
            $vt_grupos[] = $g->cod_grupo;
        }
    }
    $eh_admin = $USUARIO->possui_grupo(COD_ADMIN);

    // Montar links para o modulo de eventos
    $link_pagina = $CFG->wwwmods.'eventos/exibir_eventos.php';
    $link_pagina_exibir = $CFG->wwwmods.'eventos/exibir.php';


    // CENTRO SECUNDARIO
    echo "<div id=\"centro_secundario\">\n";
    evento::imprimir_calendario($link_pagina, $link_pagina_exibir, 'calendario', $USUARIO->cod_usuario, false, $vt_grupos, $eh_admin);
    echo "<p class=\"clear\"><a class=\"rss\" rel=\"rss\" href=\"{$CFG->wwwmods}eventos/eventos.rss.php\" title=\"Eventos (RSS 2.0)\">Feed</a></p>";
    echo "</div>\n";


    // CENTRO PRINCIPAL
    echo "<div id=\"centro_principal\" class=\"texto\">\n";
    echo "<p>{$CFG->titulo} &eacute; um sistema para cadastrar projetos de an&aacute;lises ".
         "sobre frases em textos. Este cadastro &eacute; sempre associado a um analista. ".
         "A hierarquia de cadastros permitidos no sistema &eacute;:<br />".
         "Analista &rarr; Projeto &rarr; Texto &rarr; Frase &rarr; An&aacute;lise".
         "</p>\n";

    echo '<noscript>'.
         '<p><strong>Aten&ccedil;&atilde;o:</strong> seu navegador n&atilde;o d&aacute; suporte a JavaScript ou a '.
         'op&ccedil;&atilde;o est&aacute; desabilitada no momento. Para usufruir de todas as funcionalidades do '.
         'sistema de forma pr&aacute;tica, recomenda-se habilitar este suporte. Caso contr&aacute;rio, o sistema '.
         'continuar&aacute; acess&iacute;vel, mas com algumas limita&ccedil;&otilde;es.</p>'.
         "</noscript>\n";

    echo "</div>\n";
    echo "<p class=\"clear\"></p>\n";
}
