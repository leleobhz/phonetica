<?php
//@ignoredoc
/// Exemplo de como checar se o link esta quebrado ou nao
require_once('../../config.php');

$pagina = new pagina();
$pagina->cabecalho('Teste de links', array('' => 'Teste'), false);
$pagina->inicio_conteudo();
echo <<<HTML
<ul>
  <li><a rel="checar" href="http://www.adobe.com/">Adobe</a></li>
  <li><a rel="checar" href="invalido.txt">invalido.txt</a></li>
  <li><a rel="checar" href="index.php">valido</a></li>
  <li><a rel="checar" href="http://www.teste.com/">http://www.teste.com/</a></li>
  <li><a rel="checar" href="http://www.google.com/">Google</a></li>
</ul>
HTML;
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);
