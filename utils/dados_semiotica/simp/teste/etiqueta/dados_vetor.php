<?php
//
// SIMP
// Descricao: Etiquetas geradas a partir de dados de um vetor
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
//     Exemplo de classe para geracao de etiquetas a partir de um vetor
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

        $dados = $this->get_item_vetor();
        $texto = "Nome: {$dados->nome}";

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



// Criar vetor de dados
$vetor = array();
$obj = new stdClass();
$obj->nome = 'Teste';
$vetor[] = $obj;
$obj = new stdClass();
$obj->nome = 'Computador';
$vetor[] = $obj;
$obj = new stdClass();
$obj->nome = 'Teclado';
$vetor[] = $obj;
$obj = new stdClass();
$obj->nome = 'Mouse';
$vetor[] = $obj;

$e = new etiqueta('3080');              // Construtor de etiquetas do tipo 3080
$e->importar_vetor($vetor);             // Importar dados do vetor
$e->imprimir_etiquetas();               // Imprimir etiquetas com os dados do vetor importado

$e->Output('etiquetas.pdf', 'i');       // Gerar PDF
