<?php
//
// SIMP
// Descricao: Exemplo de requisicao XML para o Web Service
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 28/11/2007
// Modificado: 28/11/2007
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');
require_once($CFG->dirroot.'webservice/lib/nusoap.php');

// Criar o cliente
$cliente = new nusoap_client($CFG->wwwroot.'webservice/index.php?wsdl', true);

// Checar erros
$erro = $cliente->getError();
if ($erro) {
    echo "<p>Erro no construtor: {$erro}</p>";
    exit(1);
}

// Chamar o metodo SOAP
$parametros = array('entidade' => 'usuario',
                    'codigo'   => 1);

$resultado = $cliente->call('consultar', $parametros, 'uri:consulta', 'uri:consulta/consultar');

// Se houve falhas
if ($cliente->fault) {
    echo '<p>Falha:</p>';
    echo '<pre>';
    print_r($resultado);
    echo '</pre>';

// Se nao houve falhas
} else {

    $erro = $cliente->getError();

    // Se houve erro
    if ($erro) {
        echo "<p>Erro: {$erro}</p>";

    // Se nao houve erro
    } else {
        $pagina = new pagina();
        $pagina->cabecalho('Teste do Web Service', array('' => 'Teste do Web Service'), false);
        $pagina->inicio_conteudo('Teste do Web Service');
        echo '<pre>';
        print_r($resultado);
        echo '</pre>';

        // Debug
/*
        echo '<hr />';
        echo '<pre><![CDATA[';
        echo $cliente->getDebug();
        echo ']]></pre>';
*/
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }
}
