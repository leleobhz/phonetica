<?php
//
// SIMP
// Descricao: Exemplo de formatacao de texto e numeros
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

echo '<p>Texto codificado: '.texto::codificar('äçêñtò').'</p>';
echo '<hr />';

echo '<p>Valor monet&aacute;rio: '.texto::money_format(0).'</p>';
echo '<p>Valor monet&aacute;rio: '.texto::money_format(0.1).'</p>';
echo '<p>Valor monet&aacute;rio: '.texto::money_format(0.10).'</p>';
echo '<p>Valor monet&aacute;rio: '.texto::money_format(1.99).'</p>';
echo '<p>Valor monet&aacute;rio: '.texto::money_format(1.991).'</p>';
echo '<p>Valor monet&aacute;rio: '.texto::money_format(1.999).'</p>';
echo '<hr />';

echo '<p>Inteiro "um":</p>';
echo '<p>Com casas decimais (caso exista): '.texto::numero(1).'</p>';
echo '<p>Sem casas decimais: '.texto::numero(1, 0).'</p>';
echo '<p>Sempre duas casas decimais: '.texto::numero(1, 2, true).'</p>';
echo '<p>De uma a duas casas decimais: '.texto::numero(1, 2, false).'</p>';

echo '<hr />';
echo '<p>Real "um v&iacute;rgula duzentos e trinta e quatro":</p>';
echo '<p>Com casas decimais (caso exista): '.texto::numero(1.234).'</p>';
echo '<p>Sem casas decimais: '.texto::numero(1.234, 0).'</p>';
echo '<p>Sempre duas casas decimais: '.texto::numero(1.234, 2, true).'</p>';
echo '<p>De uma a duas casas decimais: '.texto::numero(1.234, 2, false).'</p>';

echo '<hr />';
echo '<p>Real "um v&iacute;rgula duzentos e trinta e quatro":</p>';
echo '<p>Sempre quatro casas decimais: '.texto::numero(1.234, 4, true).'</p>';
echo '<p>De uma a quatro casas decimais: '.texto::numero(1.234, 4, false).'</p>';


