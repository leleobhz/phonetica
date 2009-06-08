<?php
//@ignoredoc
/// Exemplo de geracao de arquivo CSV com todos os usuarios
require_once('../../config.php');


/// Campos a serem armazenados no arquivo CSV
$campos = array('cod_usuario', 'login', 'nome');
// $campos = true; // true para todos os campos


/// Imprimir Header HTTP

// Tipo de Conteudo, codificacao e indicacao de cabecalho (present ou absent)
header('Content-type: text/csv; charset='.$CFG->charset.'; header=present');

// Disposicao do conteudo: inline (exibir) ou attachment (forcar download)
//header("Content-Disposition: inline; filename=usuarios.csv");
header("Content-Disposition: attachment; filename=usuarios.csv");

// Caso deseja armazenar o arquivo em cache
//header('Cache-Control: public');
//header('Pragma: ');
//header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
//header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + 604800)); // Ex.: uma semana (7 * 24 * 60 * 60)


/// Consultar os usuarios
$usuarios = objeto::get_objeto('usuario')->consultar_varios();


/// Gerar arquivo CSV

// Gerar Cabecalho CSV
if ($campos === true) {
    foreach ($u->get_atributos as $atributo) {
        $campos[] = $atributo->nome;
    }
}
echo implode(',', $campos)."\n";

// Imprimir conteudo CSV
foreach ($usuarios as $usuario) {
    echo $usuario->exportar('csv', $campos);
}
