<?php
//@ignoredoc
require_once('../config.php');

// Alterne entre:
$modo = OBJETO_MODO_CONGELAR;
$modo = OBJETO_MODO_SOBRESCREVER;

objeto::set_modo_persistencia($modo);
echo '<p>Modo de Persist&ecirc;ncia: '.objeto::get_modo_persistencia().'</p>';
echo '<hr />';

echo '<p>Consultar usu&aacute;rio com cod_usuario 1</p>';
$u = new usuario('', 1, true);

echo '<p>Definindo o nome como "Oi"</p>';
$u->nome = 'Oi';

echo '<p>Consultar usuario com login "admin"</p>';
$u2 = new usuario('login', 'admin', true);

switch ($modo) {
case OBJETO_MODO_CONGELAR:
    echo '<p>Deve exibir: Oi</p>';
    break;
case OBJETO_MODO_SOBRESCREVER:
    echo '<p>Deve exibir: Administrador</p>';
    break;
}
echo '<p><strong>Nome: '.$u->exibir('nome').'</strong></p>';

echo '<p>Definindo o nome como "Oi"</p>';
$u->nome = 'Oi';

echo '<p>Consultar usuario com login "admin" e n&atilde;o cancelado</p>';
$vt_condicoes = array();
$vt_condicoes[] = condicao_sql::montar('login', '=', 'admin');
$vt_condicoes[] = condicao_sql::montar('cancelado', '=', false);
$condicoes = condicao_sql::sql_and($vt_condicoes);
$u3 = new usuario();
$u3->consultar_condicoes($condicoes, true);

switch ($modo) {
case OBJETO_MODO_CONGELAR:
    echo '<p>Deve exibir: Oi</p>';
    break;
case OBJETO_MODO_SOBRESCREVER:
    echo '<p>Deve exibir: Administrador</p>';
    break;
}
echo '<p><strong>Nome: '.$u->exibir('nome').'</strong></p>';

echo '<p>Consultar todos usuarios</p>';
$condicoes = condicao_sql::vazia();
$campos = true;
$u->consultar_varios($condicoes, $campos);

switch ($modo) {
case OBJETO_MODO_CONGELAR:
    echo '<p>Deve exibir: Oi</p>';
    break;
case OBJETO_MODO_SOBRESCREVER:
    echo '<p>Deve exibir: Administrador</p>';
    break;
}
echo '<p><strong>Nome: '.$u->exibir('nome').'</strong></p>';

objeto::dump_instancias();
