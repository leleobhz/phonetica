<?php
//@ignoredoc
require_once('../config.php');

$mensagem = <<<MENSAGEM
<h1>Olá Rubens</h1>
<p>Como vai?</p>
<p>Até logo</p>
MENSAGEM;

$email = new email('Assunto de teste');

$email->set_smtp('smtp.exemplo.br', 25);

$email->set_remetente('Rubens Takiguti Ribeiro', 'rubens@exemplo.br');
$email->set_destinatario('rubs', 'rubs33@gmail.com');
//$email->adicionar_copia('Iuna Fricke DAscenzi', 'iunaf@exemplo.br', 'oculta');
$email->set_mensagem($mensagem, 1);
//$email->adicionar_anexo('/home/rubens/figura.jpg');

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
