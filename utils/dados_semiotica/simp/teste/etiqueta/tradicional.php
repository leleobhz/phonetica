<?php
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
        $w = $this->largura * $this->get_conversao() - 0.6;
        $h = 0.6;

        $texto = "Teste\n".
                 'Célula: '.$this->get_celula()."\n".
                 'Posição: '.$this->get_linha().'/'.$this->get_coluna()."\n";

        $this->SetFont('Arial', '', 12);
        $this->SetXY($x + 0.3, $y + 0.3);
        $this->MultiCell($w, $h, $texto, DEBUG_FPDF_ETIQUETA);

        $this->Line($x, $y, $x + $this->largura * $this->get_conversao(), $y + $this->altura * $this->get_conversao());
    }

}

$e = new etiqueta('3080', 'cm');        // Construtor de etiquetas do tipo 3080
$e->set_quantidade(65);                 // Definir a quantidade de etiquetas a serem impressas (65)
$e->imprimir_etiquetas(3, 2);           // Imprimir 65 etiquetas (tipo 3080) a partir da linha 3 coluna 2

$e->Output('etiquetas.pdf', 'i');       // Gerar PDF
