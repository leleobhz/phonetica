<?php
//
// SIMP
// Descricao: Exemplo de como montar condicoes SQL do Simp com string
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

$vt_sql = array(
"a = ''",
"a = 1",
"a = '1'",
"a = true",
"a = 'true'",
"a = b",
"a:b <> 'a'",
//"mes(d) = 1", //TODO suportar funcoes
"a:b != 1",
"a:b ~ 1 or a:b LIKE 2 or a:b !~ 3 or a:b UNLIKE 4",
"a:b is null and c:d is not null",
"a = 'a' or a = b",
"a = 'w\"x\'y\'z'",
"a = 1 and not (b = 2)",
"a = 1 and b = 2 and c = 3 or d = 4 or e = 5",
"a = 1 and b = 2 and (c = 3 or d = 4) or e = 5",
"a ~ 1 and b <> 2 or c is not null and not (d > 3 and e != 4)"
);

foreach ($vt_sql as $sql) {

    $t1 = microtime(true);
    $condicao = condicao_sql::parse($sql);
    $t2 = microtime(true);

    echo '<p><strong>SQL:</strong> '.$sql.'</p>';
    echo '<p>Condicao: <pre>'.$condicao.'</pre></p>';
    echo '<p>Tempo: '.($t2 - $t1).'</p>';
    util::dump($condicao);
    echo '<hr />';
}

// Exemplo semelhante a uma prepared statement
$sql = 'numero = :numero or nome = :nome and teste <> :teste';
$dados = array(
    ':numero' => 10000,
    ':nome' => 'bla bla bla',
    ':teste' => "oi 'tenta' oi"
);

$t1 = microtime(true);
$condicao = condicao_sql::parse($sql, $dados);
$t2 = microtime(true);

echo '<p><strong>SQL:</strong> '.$sql.'</p>';
echo '<p>Condicao: <pre>'.$condicao.'</pre></p>';
echo '<p>Tempo: '.($t2 - $t1).'</p>';
util::dump($condicao);
echo '<hr />';
