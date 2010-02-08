<?php
//
// SIMP
// Descricao:  Exemplo de como checar se o link esta quebrado ou nao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

$pagina = new pagina();
$pagina->cabecalho('Teste de links', array('' => 'Teste'), false);
$pagina->inicio_conteudo();
echo <<<HTML
<ul>
  <li><a rel="checar" href="http://www.adobe.com/">Adobe</a></li>
  <li><a rel="checar" href="invalido.txt">invalido.txt</a></li>
  <li><a rel="checar" href="index.php">valido</a></li>
  <li><a rel="checar" href="outro_invalido.txt">outro_invalido.txt</a></li>
  <li><a rel="checar" href="http://www.teste.com/">http://www.teste.com/</a></li>
  <li><a rel="checar" href="http://www.google.com/">Google</a></li>
</ul>
HTML;
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);
