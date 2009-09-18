<?php
//
// SIMP
// Descricao: Arquivo de ajuda geral
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.12
// Data: 15/06/2007
// Modificado: 18/09/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Dados recebidos por GET
$topico = util::get_dado('topico', 'int', false, false);


/// Dados da Pagina
$modulo = util::get_modulo(__FILE__);
$titulo = 'Solu&ccedil;&atilde;o de Problemas';
if (isset($_SESSION[$modulo]['login']) && $_SESSION[$modulo]['login']) {
    $nav[] = 'login#index.php';
} else {
    $nav[] = '#index.php';
}
$nav[]   = $modulo.'#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = $CFG->wwwmods.$modulo.'/estilos.css';


/// Topicos de ajuda
$obj = new stdClass();
$obj->titulo = 'Esqueci minha senha de acesso';
$obj->solucao = <<<SOLUCAO
 <p>Na p&aacute;gina de log-in no sistema existe uma op&ccedil;&atilde;o chamada "Esqueci minha senha".</p>
 <p>Nesta op&ccedil;&atilde;o s&atilde;o solicitados alguns dados que, ap&oacute;s preenchidos e submetidos,
 encaminham imediatamente uma nova senha ao e-mail do usu&aacute;rio correspondente. Caso voc&ecirc; tenha esquecido
 um dos dados solicitados ou a nova senha n&atilde;o tenha chegado (talvez por uma falha no servidor de e-mails), &eacute; 
 necess&aacute;rio entrar em contato com os administradores do sistema pelo e-mail {$CFG->email_padrao}.</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'N&atilde;o consigo entrar no Sistema';
$obj->solucao = <<<SOLUCAO
 <p>Para conseguir acesso ao sistema certifique-se antes que:</p>
 <ul>
 <li>Voc&ecirc; tenha sido cadastrado no sistema</li>
 <li>Os cookies do navegador estejam habilitados</li>
 <li>Voc&ecirc; n&atilde;o esteja digitando a senha com as teclas <em>Caps Lock</em> ativada ou <em>Num Lock</em> desativada no teclado</li>
 <li>O sistema n&atilde;o esteja fechado para manuten&ccedil;&atilde;o (nestes casos, um aviso &eacute; mostrado e apenas os Administradores do sistema podem acess&aacute;-lo)</li>
 <li>Voc&ecirc; n&atilde;o esteja suspenso do sistema (nestes casos, um aviso &eacute; mostrado)</li>
 <li>Voc&ecirc; n&atilde;o esteja digitando a senha de maneira incorreta</li>
 </ul>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Problemas com o layout';
$versao = ($CFG->agent->versao_navegador ? ' vers&atilde;o '.$CFG->agent->versao_navegador : '');
$obj->solucao = <<<SOLUCAO
 <p>Este sistema foi desenvolvido preocupando-se com a acessibilidade e a
 usabilidade. O <em>layout</em> foi testado nos seguintes navegadores:</p>
 <ul>
   <li><a rel="blank" href="http://www.microsoft.com/brasil/windows/ie/default.mspx">Internet Explorer 6</a>,</li>
   <li><a rel="blank" href="http://br.mozdev.org/firefox/">Mozilla Firefox vers&otilde;es 1.5, 2, 3 e 3.5</a>,</li>
   <li><a rel="blank" href="http://netscape.aol.com/">Netscape Navigator vers&atilde;o 9 e 10</a>,</li>
   <li><a rel="blank" href="http://www.opera.com/">Opera vers&atilde;o 9</a>,</li>
   <li><a rel="blank" href="http://elinks.or.cz/">ELinks vers&atilde;o 0.11.3</a>,</li>
   <li><a rel="blank" href="http://lynx.isc.org/">Lynx vers&atilde;o 2.8.6</a> e</li>
   <li><a rel="blank" href="http://w3m.sourceforge.net/">W3M vers&atilde;o 0.5.2</a>.</li>
 </ul>
 <p>A resolu&ccedil;&atilde;o do monitor 
 recomendada para correta visualiza&ccedil;&atilde;o das p&aacute;ginas &eacute; de pelo
 menos 800x600 pixels.</p><p>Infelizmente, um dos navegadores mais 
 utilizados no mundo (o <acronym title="Internet Explorer 6">IE6</acronym>) n&atilde;o 
 reconhece adequadamente todos os padr&otilde;es de estilos <acronym title="Cascading Style 
 Sheets">CSS</acronym>, logo, alguns problemas de visualiza&ccedil;&atilde;o podem 
 existir.</p><p>Recomenda-se, portanto, a utiliza&ccedil;&atilde;o dos outros navegadores 
 citados ou semelhantes (Iceweasel, SeaMonkey, Galeon e Mozilla), uma vez que s&atilde;o livres e/ou 
 gratuitos e reconhecem a maioria das especifica&ccedil;&otilde;es de estilos definidas 
 pelo <acronym title="World Wide Web Consortium">W3C</acronym> (&oacute;rg&atilde;o 
 respons&aacute;vel pela defini&ccedil;&atilde;o dos padr&otilde;es).</p>
 <p>Caso ainda tenha d&uacute;vidas, este sistema identificou que voc&ecirc; est&aacute; 
 utilizando o navegador <strong>{$CFG->agent->navegador}</strong>{$versao} (ou compat&iacute;vel).</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Vejo p&aacute;ginas amarelas exibindo erros';
$obj->solucao = <<<SOLUCAO
 <p>Por ser desenvolvido com a linguagem XHTML, o sistema exige que todo
 seu conte&uacute;do seja um documento XML v&aacute;lido. Caso alguma p&aacute;gina seja 
 exibida em amarelo com erros, significa que o documento gerado cont&eacute;m erros. Informe 
 o administrador ou os desenvolvedores do sistema para depurar o problema.</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Clico nos links, mas eles s&oacute; geram um Carregando na p&aacute;gina';
$obj->solucao = <<<SOLUCAO
 <p>Provavelmente a p&aacute;gina do link que voc&eacute; clicou est&aacute; 
 com algum problema ou seu navegador n&atilde;o d&aacute; suporte &agrave; JavaScript da forma 
 esperada.</p><p>Felizmente (&#x263A;), o sistema foi desenvolvido para (tamb&eacute;m) trabalhar sem 
 JavaScript. Portanto, tente desabilitar o JavaScript do seu navegador para checar se o erro 
 persiste.</p><p>Caso o problema seja resolvido, acesse as op&ccedil;&otilde;es pessoais na 
 parte inferior do menu principal e desabilite a op&ccedil;&atilde;o Ajax. Em seguida habilite 
 o JavaScript de seu navegador para checar se o erro persiste.</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Os bot&otilde;es de avan&ccedil;ar, voltar e atualizar n&atilde;o funcionam';
$obj->solucao = <<<SOLUCAO
 <p>Por ser desenvolvido com as tecnologias propostas pelo AJAX, o sistema 
 sempre se mant&eacute;m na mesma p&aacute;gina. Infelizmente, a utiliza&ccedil;&atilde;o 
 das tecnologias implica em abandonar os bot&otilde;es do navegador em troca da praticidade 
 de apenas recarregar o que precisa ser recarregado na p&aacute;gina.</p><p>Mas caso este 
 problema o(a) incomoda muito, basta desabilitar o JavaScript de seu navegador. Neste caso,
 o sistema dever&aacute; funcionar sem Ajax. Outra op&ccedil;&atilde;o &eacute; desabilitar 
 o &iacute;tem Ajax nas op&ccedil;&otilde;es pessoais, localizada na parte inferior do menu 
 principal.</p><p>Lembre-se, no entanto, que a utiliza&ccedil;&atilde;o destas tecnologias 
 torna a navega&ccedil;&atilde;o mais r&aacute;pida, uma vez que trafegam menos dados pela 
 rede.</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Consigo entrar no sistema sem ter feito log-in';
$obj->solucao = <<<SOLUCAO
 <p>Sempre que desejar sair do sistema utilize o <em>link</em> "Sair" no menu 
 principal do sistema. Ele evita que seja poss&iacute;vel retornar ao sistema sem realizar o log-in.</p>
SOLUCAO;
$topicos[] = $obj;

$obj = new stdClass();
$obj->titulo = 'Meu menu principal foi para o lado direito';
$obj->solucao = <<<SOLUCAO
 <p>Para mover o menu principal para um lado ou para o outro, basta clicar sobre 
 algum titulo do menu e arrast&aacute;-lo para o lado desejado.</p>
SOLUCAO;
$topicos[] = $obj;

$drivers = objeto::get_objeto('config')->get_vetor_autenticacao();
$descricao = $drivers[$CFG->autenticacao];
$obj = new stdClass();
$obj->titulo = 'N&atilde;o consigo alterar a senha';
$obj->solucao = <<<SOLUCAO
 <p>Provavelmente o modo de autentica&ccedil;&atilde;o n&atilde;o est&aacute; sendo 
 pelo Banco de Dados (BD). Isso significa que a senha est&aacute; armazenada em outro local que n&atilde;o 
 pode ser alterado atrav&eacute;s do sistema.</p><p>Quando o modo de autentica&ccedil;&atilde;o n&atilde;o 
 &eacute; feito por BD, ent&atilde;o a op&ccedil;&atilde;o de alterar senha fica inacess&iacute;vel para todos 
 os usu&aacute;rios.</p><p>Neste momento o modo de autentica&ccedil;&atilde;o  &eacute; 
 "{$descricao}".</p>
SOLUCAO;
$topicos[] = $obj;


// Imprimir pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
imprimir_topicos($topicos, $topico);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Imprime a lista de topicos e um topico caso seja especificado
//
function imprimir_topicos($topicos, $topico) {
// Array[Object] $topicos: vetor de topicos
// Int $topico: numero do topico escolhido para exibicao
//
    global $CFG, $modulo;
    $prefixo_link = $CFG->wwwmods.$modulo.'/'.basename(__FILE__);

    echo "<p>T&oacute;picos de ajuda:</p>\n";
    echo "<ul class=\"lista_topicos\">\n";
    foreach ($topicos as $i => $obj) {
        $l = $prefixo_link.'?topico='.($i + 1);
        echo "<li>".link::texto($l, $obj->titulo, $obj->titulo, '', 'popup', 1, 0)."</li>\n";
    }
    echo "</ul>\n";

    if ($topico) {
        if ($topico <= count($topicos) && $topico > 0) {
            $topico_obj = $topicos[$topico - 1];

            echo "<div id=\"conteudo_popup\">\n";
            echo "<blockquote id=\"topico_ajuda\">\n";
            echo "  <h3>{$topico_obj->titulo}</h3>\n";
            echo $topico_obj->solucao;
            echo "</blockquote>\n";
            echo "</div>\n";
        } else {
            mensagem::erro('Este t&oacute;pico n&atilde;o existe');
        }
    }
}
