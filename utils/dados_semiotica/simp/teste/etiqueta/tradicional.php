<?php
//
// SIMP
// Descricao: Etiqueta gerada com um metodo simples imprimir_etiqueta
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

if (isset($_GET['debug'])) {
    define('DEBUG_FPDF_ETIQUETA', 1);
}


//
//     Exemplo de classe para geracao de etiquetas simples
//
class etiqueta extends fpdf_etiqueta {


    //
    //     Metodo que imprime os dados de uma etiqueta orientando-se
    //     pelas coordenadas $x e $y que representam o ponto superior
    //     esquerdo da etiqueta corrente.
    //
    protected function imprimir_etiqueta($x, $y) {
    // Float $x: posicao X do topo esquerdo da etiqueta
    // Float $y: posicao Y do topo esquerdo da etiqueta
    //
        $c = $this->get_conversao();
        $w = $this->largura * $c - 0.6;
        $h = 0.6;

        $texto = "Teste\n".
                 'Célula: '.$this->get_celula()."\n".
                 'Posição: '.$this->get_linha().'/'.$this->get_coluna()."\n";

        $this->SetFont('Arial', '', 12);
        $this->SetXY($x + 0.3, $y + 0.3);
        $this->MultiCell($w, $h, $texto, DEBUG_FPDF_ETIQUETA);

        $this->Line($x, $y, $x + $this->largura * $c, $y + $this->altura * $c);
    }


    //
    //     Obtem a unidade usada pelo usuario
    //
    public function get_unidade_usuario() {
        return 'cm';
    }

}

$e = new etiqueta('3080');              // Construtor de etiquetas do tipo 3080
$e->set_quantidade(65);                 // Definir a quantidade de etiquetas a serem impressas (65)
$e->imprimir_etiquetas(3, 2);           // Imprimir 65 etiquetas (tipo 3080) a partir da linha 3 coluna 2

$e->Output('etiquetas.pdf', 'i');       // Gerar PDF
