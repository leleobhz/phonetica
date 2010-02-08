<?php
//
// SIMP
// Descricao: Exemplo de como se manda um e-mail com a classe email
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 03/03/2007
// Modificado: 14/01/2010
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

$mensagem = <<<MENSAGEM
<h1>Olá Rubens</h1>
<p>Como vai?</p>
<p>Até logo</p>
MENSAGEM;

$email = new email('Assunto de teste');

//$email->set_smtp('smtp.exemplo.br', 25);

$email->set_remetente('Rubens Takiguti Ribeiro', 'rubens@exemplo.com.br');
$email->set_destinatario('Destinatario da Silva', 'destinatario@exemplo.com.br');
//$email->adicionar_copia('Alguem da Silva', 'alguem@exemplo.com.br', 'oculta');
$email->set_mensagem($mensagem, 1);
$email->adicionar_anexo(dirname(__FILE__).'/pdf/imagem.jpg');

$enviou = $email->enviar();

if ($enviou) {
    echo "<h1>Enviou</h1>";
} else {
    echo "<h1>Erro ao enviar</h1>";
    echo "<hr />";
    $email->imprimir_erros();
}
echo '<hr />';
echo '<h3>Log:</h3>';
echo '<pre>';
echo texto::codificar($email->smtp_get_log());
echo '</pre>';
