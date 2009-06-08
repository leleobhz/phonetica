<?php
//
// SIMP
// Descricao: Extensao da classe fpdf_table para impressao de etiquetas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 01/10/2008
// Modificado: 06/10/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('DEBUG_FPDF_ETIQUETA', 0);

abstract class fpdf_etiqueta extends fpdf_table {
    private $tipo;       // String: tipo de etiqueta escolhido
    private $medidas;    // Array[Mixed]: medidas do tipo de etiqueta
    private $conversao;  // Float: constante de conversao da unidade das medidas da etiqueta para a unidade de escolha do usuario
    private $celula;     // Int: numero da etiqueta corrente
    private $quantidade; // Int: quantidade de etiquetas a serem impressas
    private $linha;      // Int: numero da linha da etiqueta corrente
    private $coluna;     // Int: numero da coluna da etiqueta corrente
    private $vetor;      // Array[Object]: vetor de dados usados nas etiquetas
    private $id;         // String: identificador do grupo de etiquetas impresso



/// @ METODOS OBRIGATORIOS NAS CLASSES FILHAS


    //
    //     Metodo que imprime os dados de uma etiqueta orientando-se
    //     pelas coordenadas $x e $y que representam o ponto superior
    //     esquerdo da etiqueta corrente.
    //
    abstract protected function imprimir_etiqueta($x, $y);
    // Float $x: posicao X do topo esquerdo da etiqueta
    // Float $y: posicao Y do topo esquerdo da etiqueta
    //


/// @ METODOS QUE PODEM SER SOBRECARREGADOS


    //
    //     Retorna as medidas do tipo de etiqueta baseado no nome informado.
    //     Caso o tipo de etiqueta desejado nao se encontra na lista, sobrescreva
    //     este metodo na classe filha (invocando-o ao final).
    //     O metodo deve retornar um vetor com as seguintes posicoes:
    //     0 - Tipo de papel (A3, A4, A5, Letter, Legal ou um array com largura/altura).
    //     1 - Orientacao (P ou L).
    //     2 - Unidade das medidas expressas nas etiquetas (pt, mm, cm, in).
    //     3 - Numero de colunas da folha.
    //     4 - Numero de linhas da folha.
    //     5 - Distancia Horizontal (distancia entre as bordas equerdas de etiquetas adjacentes horizontalmente).
    //     6 - Distancia Vertical (distancia entre as bordas superiores de etiquetas adjacentes verticalmente).
    //     7 - Largura da etiqueta.
    //     8 - Altura da etiqueta.
    //     9 - Margem esquerda (margem entre a borda esquerda da folha e a borda esquerda da primeira etiqueta).
    //     10 - Margem superior (margem entre a borda superior da folha e a borda superior da primeira etiqueta).
    //
    public function get_medidas_tipo($tipo) {
    // String $tipo: nome do tipo de etiqueta
    //
        switch ($tipo) {

        // link: http://www.printlabel.com.br/downloads.php

        case '3080': // 66,7 x 25,4 mm
            return array('Letter', 'P', 'mm', 3, 10, 69.900, 25.400, 66.700, 25.400, 4.500, 12.700);
        case '3081': // 101,6 x 25,4 mm
            return array('Letter', 'P', 'mm', 2, 10, 106.800, 25.400, 101.600, 25.400, 3.500, 12.700);
        case '3082': // 101,6 x 33,9 mm
            return array('Letter', 'P', 'mm', 2, 7, 106.800, 33.900, 101.600, 33.900, 3.500, 21.200);
        case '3083': // 101,6 x 50,8 mm
            return array('Letter', 'P', 'mm', 2, 5, 106.800, 50.800, 101.600, 50.800, 3.500, 12.700);
        case '3084': // 101,6 x 84,7 mm
            return array('Letter', 'P', 'mm', 2, 3, 106.800, 84.700, 101.600, 84.700, 3.500, 12.700);
        case '3085': // 215,9 x 279,4 mm
            return array('Letter', 'P', 'mm', 1, 1, 215.900, 279.400, 215.900, 279.400, 0, 0);
        case '3086': // 212,73 x 138,11 mm
            return array('Letter', 'P', 'mm', 1, 2, 212.700, 138.100, 212.700, 138.100, 1.300, 1.600);
        case '3087': // 44,45 x 12,7 mm
            return array('Letter', 'P', 'mm', 4, 20, 47.000, 12.500, 44.000, 12.500, 13.700, 14.000);
        case '3088': // 106,36 x 138,11 mm
            return array('Letter', 'L', 'mm', 2, 2, 138.100, 106.400, 138.100, 106.400, 1.300, 1.600);
        case '3089': // 44,45 x 16,93 mm
            return array('Letter', 'P', 'mm', 4, 15, 48.000, 17.000, 44.000, 17.000, 13.700, 12.000);
        case '3095': // 85,73 x 59,27 mm
            return array('Letter', 'P', 'mm', 2, 4, 96.300, 59.300, 85.700, 59.300, 16.700, 21.200);
        case 'A4047': // 150 x 49 mm
            return array('A4', 'P', 'mm', 1, 5, 150.000, 50.000, 150.000, 50.000, 29.700, 24.000);
        case 'A4048': // 31 x 17 mm
            return array('A4', 'P', 'mm', 6, 16, 33.000, 17.000, 33.000, 17.000, 5.700, 12.500);

//        case '': //
//            return array('Letter', 'P', 'mm', col, lin, dw, dh, w, h, ml, mt);
        }
        $this->Error('Tipo de etiqueta invalido: '.$tipo);
    }


/// @ METODOS MAGICOS


    //
    //     Obtem um valor da medida da etiqueta
    //
    final public function __get($atributo) {
    // String $atributo: nome do atributo
    //
        switch ($atributo) {
        case 'papel':
            return $this->medidas[0];
        case 'orientacao':
            return $this->medidas[1];
        case 'unidade':
            return $this->medidas[2];
        case 'numero_colunas':
            return $this->medidas[3];
        case 'numero_linhas':
            return $this->medidas[4];
        case 'distancia_horizontal':
            return $this->medidas[5];
        case 'distancia_vertical':
            return $this->medidas[6];
        case 'largura':
            return $this->medidas[7];
        case 'altura':
            return $this->medidas[8];
        case 'margem_esquerda':
            return $this->medidas[9];
        case 'margem_superior':
            return $this->medidas[10];
        }
        trigger_error('Atributo invalido: '.$atributo, E_USER_ERROR);
    }


    //
    //     Construtor padrao
    //
    final public function __construct($tipo, $unidade = 'mm') {
    // String $tipo: nome do tipo de etiqueta a ser buscado no metodo get_medidas
    // String $unidade: unidade de medida usado para exibir uma etiqueta
    //
        $this->tipo    = $tipo;
        $this->medidas = $this->get_medidas_tipo($tipo);
        parent::__construct($this->orientacao, $unidade, $this->papel);
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);
        $this->conversao = $this->calcular_conversao();
    }


/// @ METODOS PUBLICOS


    //
    //     Imprime uma quantidade de etiquetas na pagina corrente a partir
    //     de determinada linha e coluna.
    //
    final public function imprimir_etiquetas($linha = 1, $coluna = 1, $borda = false) {
    // Int $linha: numero da linha inicial
    // Int $coluna: numero da coluna inicial
    // Bool $borda: indica se deve imprimir a borda da etiqueta
    //
        if (is_null($this->quantidade)) {
            $this->Error('Quantidade de etiquetas nao foi especificado');
        }
        if (!is_int($linha) || !is_int($coluna)) {
            $this->Error('Linha/Coluna devem ser valores inteiros');
        }
        if ($linha < 0 || $coluna < 0) {
            $this->Error('Linha/Coluna devem ser valores positivos');
        }
        if ($coluna > $this->numero_colunas) {
            $this->Error('A folha do tipo de etiqueta "'.$this->tipo.'" nao possui coluna '.$coluna);
        }
        if ($linha > $this->numero_linhas) {
            $this->Error('A folha do tipo de etiqueta "'.$this->tipo.'" nao possui linha '.$linha);
        }

        // Guardar as margens atuais e o auto page breack
        $def = array(
                     'l'   => $this->lMargin,
                     't'   => $this->tMargin,
                     'r'   => $this->rMargin,
                     'b'   => $this->bMargin,
                     'apb' => $this->AutoPageBreak
                    );
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);


        $this->linha = $linha;
        $this->coluna = $coluna;

        $this->AddPage($this->orientacao, $this->papel);
        for ($this->celula = 1; $this->celula <= $this->quantidade; $this->celula++) {
            $x = $this->calcular_x();
            $y = $this->calcular_y();
            $this->SetXY($x, $y);
//echo 'Etiqueta '.$this->celula.':<br />';
//echo 'Celula: '.$this->linha.'/'.$this->coluna.'<br />';
//echo 'Posicao: '.$x.'/'.$y.'<hr />';
            $this->imprimir_etiqueta($x, $y);
            if (DEBUG_FPDF_ETIQUETA || $borda) {
                $this->imprimir_borda_debug($x, $y);
            }
            if ($this->celula < $this->quantidade) {
                $this->proxima_etiqueta();
            }
        }

        // Voltar configuracoes anteriores
        $this->SetMargins($def['l'], $def['t'], $def['r']);
        $this->SetAutoPageBreak($def['apb'], $def['b']);
    }


    //
    //     Importar dados de um arquivo CSV
    //
    final public function importar_csv($arquivo, $separador = ',', $delimitador = '"') {
    // String $arquivo: nome do arquivo CSV
    // Char $separador: caractere separador de dados
    // Char $delimitador: caractere delimitador de dado
    //
        if (!is_file($arquivo)) {
            $this->Error('Arquivo invalido: '.$arquivo);
        }
        $f = fopen($arquivo, 'r');
        $this->vetor = array();
        $cabecalho = fgetcsv($f, 0, $separador, $delimitador);
        while ($linha = fgetcsv($f, 0, $separador, $delimitador)) {
            $dados = array_combine($cabecalho, $linha);
            $this->vetor[] = (object)$dados;
        }
        fclose($f);
        $this->set_quantidade(count($this->vetor));
    }


    //
    //     Importar dados de um vetor
    //
    final public function importar_vetor($vetor) {
    // Array[Object] $vetor: vetor de dados
    //
        if (!is_array($vetor)) {
            $this->Error('Tipo invalido: '.gettype($vetor).' (esperado um vetor)');
        }
        $this->vetor = array_values($vetor);
        $this->set_quantidade(count($this->vetor));
    }


/// @@ METODOS SET


    //
    //     Define um novo tipo de etiqueta na pagina seguinte
    //
    final public function set_tipo($tipo) {
    // String $tipo: nome do tipo de etiqueta a ser buscado no metodo get_medidas
    //
        $this->tipo    = $tipo;
        $this->medidas = $this->get_medidas_tipo($tipo);
    }


    //
    //     Define a quantidade de etiquetas a serem impressas
    //
    final public function set_quantidade($quantidade) {
    // Int $quantidade: quantidade de etiquetas a serem impressas
    //
        if (!is_int($quantidade)) {
            $this->Error('Quantidade deve ser um valor inteiro');
        }
        $this->quantidade = $quantidade;
    }


    //
    //     Define um identificador do grupo de etiquetas impresso
    //
    final public function set_id($id) {
    // String $id: identificador a ser atribuido
    //
        $backtrace = debug_backtrace();
        if ($backtrace[1]['function'] == 'imprimir_etiqueta') {
            $this->Error('O metodo set_id deve ser chamado fora do metodo imprimir_etiqueta');
        }
        $this->id = (string)$id;
    }


/// @@ METODOS GET


    //
    //     Retorna o tipo de etiqueta corrente
    //
    final public function get_tipo() {
        return $this->tipo;
    }


    //
    //     Retorna as medidas do tipo de etiqueta atual
    //
    final public function get_medidas() {
        return $this->medidas;
    }


    //
    //     Retorna o valor da constante de conversao
    //
    final public function get_conversao() {
        return $this->conversao;
    }


    //
    //     Retorna o numero da celula corrente (inicio em 1)
    //
    final public function get_celula() {
        return $this->celula;
    }


    //
    //     Retorna o identificador do grupo de etiquetas corrente
    //
    final public function get_id() {
        return $this->id;
    }


    //
    //     Obtem a linha corrente (inicio em 1)
    //
    final public function get_linha() {
        return $this->linha;
    }


    //
    //     Obtem a coluna corrente (inicio em 1)
    //
    final public function get_coluna() {
        return $this->coluna;
    }


    //
    //     Retorna a quantidade de etiquetas a serem impressas
    //
    final public function get_quantidade() {
        return $this->quantidade;
    }


    //
    //     Obtem os dados da celula corrente do vetor de dados
    //
    final public function get_item_vetor() {
        $pos = $this->celula - 1;
        if (!isset($this->vetor[$pos])) {
            return false;
        }
        return $this->vetor[$pos];
    }


/// @ METODOS PRIVADOS


    //
    //     Calcula a constante de conversao da unidade de medidas da etiqueta para
    //     a unidade de escolha do usuario.
    //
    private function calcular_conversao() {
        $vt = array('pt' => 1,
                    'mm' => 72 / 25.4,
                    'cm' => 72 / 2.54,
                    'in' => 72);

        $unidade_etiquetas = $this->unidade;
        $unidade_usuario   = $this->unit;
        return (float)$vt[$unidade_etiquetas] / (float)$vt[$unidade_usuario];
    }


    //
    //     Calcula a posicao X onde a etiqueta deve ser posicionada
    //
    private function calcular_x() {
        // margem esquerda + ((coluna - 1) * distancia horizontal)
        return ($this->margem_esquerda + (($this->coluna - 1) * $this->distancia_horizontal)) * $this->conversao;
    }


    //
    //     Calcula a posicao Y onde a etiqueta deve ser posicionada
    //
    private function calcular_y() {
        // margem superior + ((linha - 1) * distancia vertical)
        return ($this->margem_superior + (($this->linha - 1) * $this->distancia_vertical)) * $this->conversao;
    }


    //
    //     Passa para a proxima etiqueta
    //
    private function proxima_etiqueta() {

        // Passar para proxima coluna
        $this->coluna += 1;

        // Se chegou ao limite de colunas: passar para proxima linha
        if ($this->coluna > $this->numero_colunas) {
            $this->coluna = 1;
            $this->linha += 1;

            // Se chegou ao limite de linhas: passar para proxima pagina
            if ($this->linha > $this->numero_linhas) {
                $this->coluna = 1;
                $this->linha  = 1;
                $this->AddPage($this->orientacao, $this->papel);
            }
        }
    }


    //
    //     Imprime uma borda representando os limites da etiqueta para debug
    //
    private function imprimir_borda_debug($x, $y) {
    // Float $x: posicao X do topo esquerdo da etiqueta
    // Float $y: posicao Y do topo esquerdo da etiqueta
    //
        $largura = $this->largura * $this->conversao;
        $altura  = $this->altura * $this->conversao;
        $this->Rect($x, $y, $largura, $altura);
    }

}//class
