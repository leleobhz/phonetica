<?php
//
// SIMP
// Descricao: Exemplo de geracao de arquivo XML com todos os usuarios
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');


/// Campos a serem armazenados no arquivo XML
$campos = array('cod_usuario', 'login', 'nome');
// $campos = true; // true para todos os campos


/// Imprimir Header HTTP

// Tipo de Conteudo
header('Content-type: text/xml; charset='.$CFG->charset);

// Disposicao do conteudo: inline (exibir) ou attachment (forcar download)
header("Content-Disposition: inline; filename=usuarios.xml");
//header("Content-Disposition: attachment; filename=usuarios.xml");

// Caso deseja armazenar o arquivo em cache
//header('Cache-Control: public');
//header('Pragma: ');
//header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
//header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + 604800)); // uma semana (7 * 24 * 60 * 60)


/// Consultar os usuarios
$usuarios = objeto::get_objeto('usuario')->consultar_varios();


/// Gerar arquivo XML
echo "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" ?>\n";
echo "<usuarios>\n";
foreach ($usuarios as $usuario) {
    echo $usuario->exportar('xml', $campos);
}
echo '</usuarios>';
