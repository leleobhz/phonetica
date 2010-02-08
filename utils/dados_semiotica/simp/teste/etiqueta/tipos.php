<?php
//
// SIMP
// Descricao: Etiquetas de varios tipos em um arquivo
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
//     Exemplo de classe para geracao de etiquetas de dois tipos
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

        switch ($this->get_id()) {
        case 'tipo1':
            $texto = "Tipo 1\n".
                     'Célula: '.$this->get_celula()."\n".
                     'Posição: '.$this->get_linha().'/'.$this->get_coluna()."\n";
            break;
        case 'tipo2':
            $texto = "Tipo 2\n".
                     'Célula: '.$this->get_celula()."\n".
                     'Posição: '.$this->get_linha().'/'.$this->get_coluna()."\n";
            break;
        }

        $this->SetFont('Arial', '', 12);
        $this->SetXY($x + 0.3, $y + 0.3);
        $this->MultiCell($w, $h, $texto, DEBUG_FPDF_ETIQUETA);
    }


    //
    //     Obtem a unidade usada pelo usuario
    //
    public function get_unidade_usuario() {
        return 'cm';
    }

}

$e = new etiqueta('3080');              // Construtor de etiquetas do tipo 3080
$e->set_id('tipo1');                    // Definir o identificador do grupo de etiquetas
$e->set_quantidade(40);                 // Definir a quantidade de etiquetas a serem impressas (40)
$e->imprimir_etiquetas();               // Imprimir 30 etiquetas (tipo 3080) a partir da linha 1 coluna 1

$e->set_tipo('3081');                   // Mudar para tipo de etiqueta 3081
$e->set_id('tipo2');                    // Definir o identificador do grupo de etiquetas
$e->set_quantidade(10);                 // Definir a quantidade de etiquetas a serem impressas (10)
$e->imprimir_etiquetas(3, 2);           // Imprimir 10 etiquetas (tipo 3081) a partir da linha 3 coluna 2

$e->Output('etiquetas.pdf', 'i');       // Gerar PDF
