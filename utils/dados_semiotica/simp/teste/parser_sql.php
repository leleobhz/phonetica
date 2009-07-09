<?php
require_once('../config.php');

$vt_sql = array(
"a = ''",
"a = 1",
"a:b <> 'a'",
"a:b != 1",
"a:b ~ 1 or a:b LIKE 2 or a:b !~ 3 or a:b UNLIKE 4",
"a:b is null and c:d is not null",
"a = 'a' or a = b",
"a = 'x\'y\'z'",
"a = 1 and not (b = 2)",
"a = 1 and b = 2 and c = 3 or d = 4 or e = 5",
"a = 1 and b = 2 and (c = 3 or d = 4) or e = 5",
"a ~ 1 and b <> 2 or c is not null and not (d > 3 and e != 4)"
);

foreach ($vt_sql as $sql) {

    $t1 = microtime(true);
    $condicao = condicao_sql::parse($sql);
    $t2 = microtime(true);

    echo '<p>Tempo: '.($t2 - $t1).'</p>';
    echo '<p>SQL: '.$sql.'</p>';
    echo '<p>Condicao: <pre>'.$condicao.'</pre></p>';
    util::dump($condicao);
    echo '<hr />';
}
