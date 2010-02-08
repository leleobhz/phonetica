<?php
//
// SIMP
// Descricao: Exemplo de conexao via LDAP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
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

    $usuarios = $ldap->consultar('', 'uid=rubens');
    if (count($usuarios)) {
        foreach ($usuarios as $usuario) {
            foreach ($usuario as $chave => $valor) {
                echo "<strong>{$chave}:</strong> {$valor}<br />\n";
            }
            echo "<hr />\n";
        }
    } else {
        echo '<p>n&atilde;o achou</p>';
    }

    $ldap->desconectar();
    echo '<p>desconectou</p>';
}
