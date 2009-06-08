<?php
//@ignoredoc
require_once('../config.php');

$pagina = new pagina();
$pagina->cabecalho('Teste LDAP', array('' => 'Teste LDAP'), false);
$pagina->inicio_conteudo();
testar_ldap();
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


function testar_ldap() {
    global $CFG;
    $ldap = new ldap();
    if (!$ldap->conectar()) {
        echo 'nao conectou<br />';
        echo 'Erro: '.$ldap->imprimir_erros().'<br />';
        return;
    }

    echo '<p>conectou</p>';

    $busca = $ldap->consultar('', 'uid=rubens');
    foreach ($busca as $usuario) {
        foreach ($usuario as $chave => $valor) {
            echo "<strong>{$chave}:</strong> {$valor}<br />\n";
        }
        echo "<hr />\n";
    }

    $ldap->desconectar();
    echo '<p>desconectou</p>';
}
