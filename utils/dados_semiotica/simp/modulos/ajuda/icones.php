<?php
//
// SIMP
// Descricao: Arquivo de descricao dos icones utilizados no sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.9
// Data: 27/09/2007
// Modificado: 13/05/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Descri&ccedil;&otilde;es dos &Iacute;cones';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Lista de icones
$icones = array(
    icone::endereco('ajuda')        => 'Ajuda ou link para uma ajuda',
    icone::endereco('adicionar')    => 'Ferramenta de cadastro de novos registros',
    icone::endereco('editar')       => 'Ferramenta de edi&ccedil;&atilde;o de um registro',
    icone::endereco('excluir')      => 'Ferramenta de exclus&atilde;o de um registro (normalmente &eacute; pedida uma confirma&ccedil;&atilde;o)',
    icone::endereco('importar')     => 'Ferramenta de importa&ccedil;&atilde;o de dados',
    icone::endereco('duplicar')     => 'Ferramenta de duplica&ccedil;&atilde;o de dados',
    icone::endereco('checado')      => 'Ferramenta de confirma&ccedil;&atilde;o/aprova&ccedil;&atilde;o',
    icone::endereco('pdf')          => 'Ferramenta de gera&ccedil;&atilde;o de documentos no formato PDF',
    icone::endereco('ativo')        => 'Indica que um registro est&aacute; ativo ou vis&iacute;vel no sistema (pode ser um link para torn&aacute;-lo inativo)',
    icone::endereco('inativo')      => 'Indica que um registro est&aacute; inativo ou invis&iacute;vel no sistema (pode ser um link para torn&aacute;-lo ativo)',
    icone::endereco('bloqueado')    => 'Indica que um registro est&aacute; bloqueado no sistema (pode ser um link para desbloque&aacute;-lo)',
    icone::endereco('desbloqueado') => 'Indica que um registro est&aacute; desbloqueado no sistema (pode ser um link para bloque&aacute;-lo)',
    icone::endereco('baixo')        => 'Move um registro para baixo em uma lista',
    icone::endereco('cima')         => 'Move um registro para cima em uma lista',
    icone::endereco('aviso')        => 'Indica um aviso importante e que deve ser lido',
    icone::endereco('buscar')       => 'Indica um campo de busca',
    icone::endereco('hierarquia')   => 'Indica um campo de busca em uma estrutura hier&aacute;rquica',
    icone::endereco('calendario')   => 'Indica um campo de data',
    icone::endereco('lista')        => 'Indica uma lista',
    icone::endereco('curso')        => 'Indica uma lista de cursos',
    icone::endereco('sede')         => 'Indica uma lista de sedes, departamentos ou locais',
    icone::endereco('grafico')      => 'Indica uma estat&iacute;stica ou gr&aacute;fico',
    icone::endereco('logs')         => 'Indica uma &aacute;rea com relat&oacute;rios ou logs',
    icone::endereco('grupos')       => 'Indica uma equipe ou grupo',
    icone::endereco('rss')          => 'Indica um Feed RSS, que &eacute; uma tecnologia utilizada principalmente para indexar not&iacute;cias',
    icone::endereco('email')        => 'Indica uma &aacute;rea de envio de mensagens',
    icone::endereco('acessivel')    => 'Indica uma &aacute;rea com dados acess&iacute;veis',
    icone::endereco('info')         => 'Indica uma informa&ccedil;&atilde;o adicional sobre um campo de formul&aacute;rio ou entidade'
   );

/// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_icones($icones);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de icones
//
function imprimir_icones($icones) {
// Array[String => String] $icones: vetor de icones com o nome do arquivo apontanto para descricao
//
    global $CFG;
    $site = $CFG->site;
    link::normalizar($site, array('icone'));

    echo "<ul id=\"lista_icones\">\n";
    foreach ($icones as $arquivo => $descricao) {
        $icone = basename($arquivo);
        $site_icone = link::adicionar_atributo($site, 'icone', $icone);
        echo "<li><a href=\"{$site_icone}\">";
        echo "<img src=\"{$arquivo}\" alt=\"&Iacute;cone\" ".imagem::tamanho_html($arquivo)." /><span>{$descricao}</span>";
        echo "</a></li>\n";
    }
    echo "</ul>\n";
    echo "<p class=\"clear\"></p>\n";

    if ($icone = util::get_dado('icone', 'string', false)) {
        $link = "{$CFG->wwwimgs}icones/{$icone}";
        echo "<p><strong>Descri&ccedil;&atilde;o do &iacute;cone ";
        echo "<img src=\"{$link}\" alt=\"{$icone}\" ".imagem::tamanho_html($link)." />:</strong><br />\n";
        echo $icones[$link];
        echo "</p>\n";
    }
}
