<?php
//
// SIMP
// Descricao: Exemplo de como trabalhar com campos de data
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

/*
Tabela de eventos

+------------+---------+-----------+---------------------+--------------+-------------+
| cod_evento | nome    | descricao | data                | visibilidade | cod_usuario |
+------------+---------+-----------+---------------------+--------------+-------------+
|          1 | Teste   | Teste     | 2009-03-25 10:20:30 |            0 |           1 |
|          2 | Outro   | outro     | 2009-03-27 01:01:01 |            0 |           1 |
|          3 | Mais Um | Mais um   | 2009-03-27 02:03:04 |            0 |           1 |
+------------+---------+-----------+---------------------+--------------+-------------+
*/

echo '<h1>Consultando Evento 1</h1>';
$e = new evento('', 1, true);

echo '<p>Nome: '.$e->nome.'</p>';
echo '<p>Data: '.$e->data.'</p>';
echo '<p>Data: '.$e->exibir('data').'</p>';

echo '<h1>Mudando Evento 1</h1>';
$e->set_dia('data', 1);
$e->set_mes('data', 2);
$e->set_ano('data', 2003);
$e->set_hora('data', 1);
$e->set_minuto('data', 2);
$e->set_segundo('data', 3);

echo '<p>Data Depois: '.$e->data.'</p>';
echo '<p>Data Depois: '.$e->exibir('data').'</p>';

echo '<h1>Componentes da data</h1>';
$data = $e->get_atributo_data('data');
// OU:
// $data = objeto::parse_data($e->data);
util::dump($data);


echo '<h1>Validando data</h1>';
$data = $e->get_atributo_data('data');
$def = $e->get_definicao_atributo('data');
$valor = $e->data;
$v = validacao::get_instancia();
$validou = $v->validar_atributo($def, $valor, $erros);
if ($validou) {
    echo '<p>Data v&aacute;lida</p>';
} else {
    echo '<p>Erros:</p>';
    util::dump($erros);
}

echo '<h1>Consultando varios</h1>';
objeto::set_modo_persistencia(OBJETO_MODO_SOBRESCREVER);

$condicoes = condicao_sql::data('data', '27-03-2009');
//$condicoes = condicao_sql::montar('data', '>', '26-03-2009-0-0-0');

$eventos = $e->consultar_varios($condicoes, true);
echo '<p>'.count($eventos).' resultados:</p>';
foreach ($eventos as $evento) {
    echo '<p>'.$evento->exibir('nome').': '.$evento->exibir('data').'</p>';
}

objeto::dump_instancias('evento');
