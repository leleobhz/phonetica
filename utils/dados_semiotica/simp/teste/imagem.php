<?php
//
// SIMP
// Descricao: Exemplo de como manipular uma imagem
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

// Cria imagem
$imagem = new imagem($CFG->dirimgs.'geral/teste_banda.jpg');

// Comente as linhas abaixo para ver o resultado
$imagem->redimensionar('200px');
$imagem->definir_transparencia();
$imagem->escala_cinza();
$imagem->adicionar_borda(1, '#880000', 1);
$imagem->adicionar_borda(2, '#FF0000', 1);
$imagem->adicionar_borda(1, '#880000', 1);
$imagem->tarja('Simp '.VERSAO_SIMP, '#FFFFFF', '#000060', 0.3, 1);
$imagem->incluir_quadriculado('10%', '10%', '#005500');
$imagem->girar(30, '#FFFFFF');

// Exibir imagem
$imagem->exibir();

// Debug
//var_dump($imagem->get_erros());
