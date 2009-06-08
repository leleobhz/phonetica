<?php
//@ignoredoc
/// Exemplos de como montar condicoes SQL
require_once('../config.php');


// A classe condicao_sql (/classes/dao/condicao_sql.class.php) possui metodos para criar condicoes SQL sob a logica Factory
// (O metodo retorna um objeto da classe condicao_sql com a condicao ja' montada)

// Os metodos factory sao:
// - vazia: monta uma condicao vazia (nenhuma condicao)
// - montar: monta uma condicao simples (>, =>, <, <=, =, <>, LIKE)
// - sql_and: monta uma condicao agrupando varias condicoes com o operando AND
// - sql_or: monta uma condicao agrupando varias condicoes com o operando OR
// - sql_not: monta uma condicao que nega outra
// - sql_union: monta uma uniao entre condicoes
// - data: monta uma condicao que obtem todos registros de determinado dia
// - sql_between: monta uma condicao onde um valor esta' em um intervalo
// - sql_in: monta uma condicao onde um valor precisa ser um dos valores de um vetor
// - sql_except: monta uma condicao onde um valor nao pode ser um dos valores de um vetor


// Exemplo 1: Consultar usuarios cujo codigo e' diferente de 1
$condicao = condicao_sql::montar('cod_usuario', '<>', '1', false);
// 'cod_usuario' e' o primeiro operando (um atributo da classe usuario)
// '<>' e' o operador que significa diferente
// '1' e' o segundo operando (um valor)
// false e' uma flag que indica que o segundo operando nao e' um atributo da classe usuario, mas um valor escalar
$usuarios = objeto::get_objeto('usuario')->consultar_varios(null, array('nome'));
imprimir_usuarios($usuarios);



// Exemplo 2: Consultar usuarios pela primeira condicao checando tambem se o login
//            comeca com 'r' e se esta' no grupo de administradores
$condicao2 = condicao_sql::montar('login', 'LIKE', 'r%', false);
$condicao3 = condicao_sql::montar('grupos:cod_grupo', '=', COD_ADMIN, false);

// Agrupar as condicoes com AND:
$vt_condicoes = array($condicao, $condicao2, $condicao3);
$condicao_and = condicao_sql::sql_and($vt_condicoes);

$usuarios = objeto::get_objeto('usuario')->consultar_varios($condicao_and);
imprimir_usuarios($usuarios);


// Exemplo 3: Obter o complemento da primeira condicao
$condicao_complemento = condicao_sql::sql_not($condicao);
$usuarios = objeto::get_objeto('usuario')->consultar_varios($condicao_complemento);
imprimir_usuarios($usuarios);



function imprimir_usuarios(&$usuarios) {
    static $i = 1;
    echo '<h1>Lista '.($i++).':</h1>';
    if (count($usuarios)) {
        echo '<ul>';
        foreach ($usuarios as $u) {
            echo '<li>'.$u->nome.'</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Nenhum</p>';
    }
}
