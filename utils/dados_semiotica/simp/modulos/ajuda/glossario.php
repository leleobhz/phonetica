<?php
//
// SIMP
// Descricao: Glossario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 04/06/2007
// Modificado: 16/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Gloss&aacute;rio';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Tipos de usuarios
$glossario['Administrador'] = 'Usu&aacute;rio respons&aacute;vel por administrar o sistema.';

/// Termos Tecnicos
$glossario['Apache'] = 'Servidor Web onde s&atilde;o hospedadas p&aacute;ginas ou sistemas Web.';
$glossario['Ajax'] = 'Asynchronous Javascript And XML &eacute; um conjunto de tecnologias que permite tornar as aplica&ccedil;&otilde;es Web mais din&acirc;micas.';
$glossario['HTML'] = 'Linguagem mais utilizada na Web para disponibilizar conte&uacute;dos de forma n&atilde;o linear atrav&eacute;s de links (liga&ccedil;&otilde;es).';
$glossario['MySQL'] = 'Sistema capaz de gerenciar bancos de dados, ou seja, um sistema que armazena dados para outros sistemas.';
$glossario['PHP'] = 'Linguagem de programa&ccedil;&atilde;o que permite a cria&ccedil;&atilde;o de sistemas Web entre outras coisas.';
$glossario['PostgreSQL'] = 'Assim como o MySQL, &eacute; um sistema gerenciador de bancos de dados.';
$glossario['RSS'] = 'Um mecanismo para disponibilizar informa&ccedil;&otilde;es e not&iacute;cias na forma de XML, podendo ser entendida pelos navegadores Web mais atuais. Com ele, &eacute; poss&iacute;vel listar as not&iacute;cias de um site sem precisar acess&aacute;-lo.';
$glossario['WAI'] = 'Web Accessibility Initiative &eacute; &eacute; um &oacute;rg&atilde;o que elabora guias, normas e recomenda&ccedil;&otilde;es de acessibilidade para Web.';
$glossario['WCAG'] = 'Web Content Accessibility Guidelines &eacute; um conjunto de recomenda&ccedil;&otilde;es para tornar as aplica&ccedil;&otilde;es Web acess&iacute;veis para pessoas com algum tipo de defici&ecirc;ncia f&iacute;sica.';
$glossario['XHTML'] = 'Linguagem semelhante &agrave; HTML, por&eacute;m com as caracter&iacute;sticas da XML. Isso garante que o Ajax funcione adequadamente. &Eacute; a vers&atilde;o recomenda pela W3C para a adequa&ccedil;&atilde;o com o futuro da Web.';
$glossario['XML'] = 'Linguagem de marca&ccedil;&atilde;o especialmente criada para armazenar dados. &Eacute; muito utilizada para transferir dados entre sistemas distindos.';


/// Ordenar glossario
$chaves = array_keys($glossario);
array_multisort($chaves, SORT_ASC, SORT_STRING, $glossario);


/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_glossario($glossario);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime o Glossario
//
function imprimir_glossario($glossario) {
// Array[String => String] $glossario: vetor de definicoes
//
    echo "<dl>\n";
    foreach ($glossario as $palavra => $definicao) {
        echo "<dt>{$palavra}</dt>\n";
        if (is_array($definicao)) {
            foreach ($definicao as $d) {
                echo "  <dd>{$d}</dd>\n";
            }
        } else {
            echo "  <dd>{$definicao}</dd>\n";
        }
    }
    echo "</dl>\n";
}


