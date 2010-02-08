<?php
//
// SIMP
// Descricao: Exemplo de geracao de PDF com classe pdf.class.php e XML
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

$arq = $CFG->dirroot.'teste/pdf/layout.xml';
$tabela = simplexml_load_file($arq);

if (!$tabela) {
    echo "<h1>Erro no layout XML</h1>\n";
    echo "<pre>";
    var_dump($tabela);
    echo "</pre>";
    exit(1);
}

/// Imprimir o PDF
$pdf = new fpdf_table('L', 'mm', 'A4');
$pdf->SetMargins(30, 30, 25);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 6, 'Título de Exemplo', 0, 'C');
$pdf->PrintTable($tabela);
$pdf->Ln(50);
$pdf->PrintTable($tabela);
$pdf->Output();
exit(0);
