<?php
//
// SIMP
// Descricao: Classe que controla a paginacao de listas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.8
// Data: 22/10/2007
// Modificado: 21/12/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('PAGINACAO_ITENS_PAGINA', 15);

final class paginacao {
    private $link;               // Link do site que possui a lista
    private $modulo;             // Nome do modulo
    private $id_lista;           // Identificador unico da lista
    private $nome;               // Nome da sessao que guarda a pagina
    private $pagina;             // Numero da pagina atual
    private $total_paginas;      // Numero total de paginas
    private $itens_pagina;       // Numero de itens por pagina
    private $total_itens;        // Numero total de itens
    private $impressao_singular; // Nome para impressao no singular
    private $impressao_plural;   // Nome para impressao no plural
    private $impressao_genero;   // Genero da entidade (M = masculino, F = feminino ou I = indeterminado)

    private $objeto;        // Objeto auxiliar
    private $flag_inicio;   // Indica que o inicio da lista foi impresso
    private $mudou_pagina;  // Flag que indica se a pagina foi alterada


    //
    //     Construtor padrao
    //
    public function __construct($modulo, $id_lista, $link = false) {
    // String $modulo: nome do modulo
    // String $id_lista: identificador da lista
    // String $link: link do site que possui a lista
    //
        $nome = self::get_nome_sessao($modulo, $id_lista);
        $this->iniciar_sessao($nome);

        $this->link     = $link;
        $this->modulo   = $modulo;
        $this->id_lista = $id_lista;
        $this->nome     = $nome;
        $this->pagina   = $this->consultar_pagina();
        $this->objeto   = null;

        $this->set_itens_pagina(PAGINACAO_ITENS_PAGINA);
        $this->set_total_itens(0);

        if ($this->link) {
            link::normalizar($this->link, array($this->nome));
        }
    }


    //
    //     Inicializa a sessao
    //
    private function iniciar_sessao($nome) {
    // String $nome: nome da chave de sessao
    //
        if (isset($_SESSION[__CLASS__][$nome])) {
            return;
        }
        $_SESSION[__CLASS__][$nome] = array(
            'pagina'      => null,
            'total_itens' => null
        );
    }


    //
    //     Gera um nome para ser usado para armazenar dados da paginacao em sessao
    //
    public static function get_nome_sessao($modulo, $id_lista) {
    // String $modulo: nome do modulo
    // String $id_lista: ID da lista
    //
        return str_replace(' ', '_', $modulo.'_'.$id_lista);
    }


    //
    //     Retorna a pagina atual
    //
    public function get_pagina() {
        return $this->pagina;
    }


    //
    //     Obtem a pagina atual de uma determinada lista
    //
    public function consultar_pagina() {
        $this->mudou_pagina = false;

        // Se informou uma nova pagina
        if (isset($_GET[$this->nome])) {
            $this->mudou_pagina = true;
            $this->salvar_pagina((int)$_GET[$this->nome]);

        // Se nao informou uma nova pagina
        } elseif (!isset($_SESSION[__CLASS__][$this->nome]['pagina'])) {
            $this->salvar_pagina(1);
        }
        return (int)$_SESSION[__CLASS__][$this->nome]['pagina'];
    }


    //
    //     Salva a pagina de uma determinada lista
    //
    public function salvar_pagina($pagina = 1) {
    // Int $pagina: numero da pagina
    //
        if ($pagina > 0) {
            $p = (int)$pagina;
            $_SESSION[__CLASS__][$this->nome]['pagina'] = $p;
            $this->pagina = $p;
        }
    }


    //
    //     Retorna o titulo da lista com links de avancar e voltar
    //
    public function titulo($pagina = false, $total_paginas = false) {
    // Int $pagina: numero de paginas
    // Int $total_paginas: numero total de paginas
    //
        if ($this->pagina > 1) {
            $l = link::adicionar_atributo($this->link, $this->nome, $this->pagina - 1);
            $link_voltar  = link::texto($l, self::seta_esquerda(), 'Exibir p&aacute;gina anterior', '', 'seta', 1, 1, 0);
        } else {
            $link_voltar = '<strong class="seta">'.self::seta_esquerda().'</strong>';
        }

        if ($this->pagina < $this->total_paginas) {
            $l = link::adicionar_atributo($this->link, $this->nome, $this->pagina + 1);
            $link_avancar =  link::texto($l, self::seta_direita(), 'Exibir p&aacute;gina seguinte', '', 'seta', 1, 1, 0);
        } else {
            $link_avancar = '<strong class="seta">'.self::seta_direita().'</strong>';
        }

        $pagina = $pagina ? $pagina : $this->pagina;
        $total_paginas = $total_paginas ? $total_paginas : $this->total_paginas;
        $p = "{$link_voltar} P&aacute;gina {$pagina}/{$total_paginas} {$link_avancar}";
        return $p;
    }


    //
    //     Retorna o rodape da lista com o numero total de itens
    //
    public function rodape($objeto = null, $total_itens = false) {
    // Object $objeto: objeto da classe que foi listada
    // Int $total_itens: informa manualmente o numero total de itens apresentados
    //
        $objeto = $objeto ? $objeto : $this->objeto;
        if (!$this->objeto) {
            $this->objeto = $objeto;
        }
        $total_itens = (is_int($total_itens) && ($total_itens >= 0)) ? (int)$total_itens : $this->total_itens;
        if (!is_null($objeto)) {
            if ($total_itens == 0) {
                switch ($this->get_genero()) {
                case 'M':
                    $quantidade = 'Nenhum '.$this->get_singular();
                    break;
                case 'F':
                    $quantidade = 'Nenhuma '.$this->get_singular();
                    break;
                case 'M':
                    $quantidade = 'Nenhum(a) '.$this->get_singular();
                    break;
                }
            } else {
                $quantidade = $total_itens.' '.(($total_itens != 1) ? $this->get_plural() : $this->get_singular());
            }

            $vt_paginas = listas::numeros(1, $this->total_paginas);

            $form = new formulario($this->link, 'form_paginacao_'.$this->modulo, 'form_paginacao', 'get');
            $form->campo_select($this->nome, $this->nome, $vt_paginas, $this->pagina, 'P&aacute;gina');
            $form->campo_submit('enviar', 'enviar', 'Exibir');

            $form_select = $form->imprimir(1);

            return "<span>Total: {$quantidade}</span>{$form_select}<p class=\"fim\"></p>";
        }
        return false;
    }


    //
    //     Define o numero de itens por pagina
    //
    private function set_itens_pagina($itens) {
    // Int $itens: numero de itens por pagina
    //
        $this->itens_pagina = ($itens > 0) ? (int)$itens : PAGINACAO_ITENS_PAGINA;
    }


    //
    //     Define o numero total de itens e de paginas consequentemente
    //
    private function set_total_itens($total) {
    // Int $total: numero total de itens
    //
        $this->total_itens   = (int)$total;
        $this->total_paginas = $this->numero_paginas($this->total_itens);
    }


    //
    //     Retorna o numero de paginas necessarias para o numero de itens e a quantidade de itens por pagina
    //
    public function numero_paginas($total_itens, $itens_pagina = false) {
    // Int $total_itens: numero total de itens
    // Int $itens_pagina: numero de itens por pagina
    //
        if (!$itens_pagina) {
            $itens_pagina = $this->itens_pagina ? $this->itens_pagina : 1;
        }
        $total_paginas = ceil($total_itens / $itens_pagina);
        return ($total_paginas > 0) ? $total_paginas : 1;
    }


    //
    //     Imprime o inicio de uma lista com paginacao e retorna os elementos consultados
    //
    public function inicio_lista($classe, $condicoes, $campos, $ordem = false, $index = false, $itens_pagina = false, $campos_consultar = false) {
    // String $classe: nome da classe que se deseja consultar
    // condicao_sql $condicoes: condicoes da consulta
    // Array[String] $campos: campos a serem exibidos
    // String || Array[String => Bool] $ordem: campo usado para ordenacao ou vetor de campos usados para ordenacao apontando para o tipo de ordem (true = crescente / false = decrescente)
    // String $index: campo usado para indexacao
    // Int $itens_pagina: numero de itens por pagina
    // Array[String] $campos_consultar: campos a serem consultados alem dos campos de exibicao
    //
        $resultado = false;

        $this->set_itens_pagina($itens_pagina);

        // Tenta consultar os registros
        $this->objeto = objeto::get_objeto($classe);

        // Se nao calculou o total de itens
        if (!isset($_SESSION[__CLASS__][$this->nome]['total_itens'])) {
            $total = $this->objeto->quantidade_registros($condicoes);
            $_SESSION[__CLASS__][$this->nome]['total_itens'] = $total;

        // Se ja calculou o total de itens
        } else {

            // Se apenas mudou de pagina
            if ($this->mudou_pagina) {
                $total = (int)$_SESSION[__CLASS__][$this->nome]['total_itens'];

            // Se voltou para a lista de outro lugar (pode ter inserido ou removido um item
            } else {
                $total = $this->objeto->quantidade_registros($condicoes);
                $_SESSION[__CLASS__][$this->nome]['total_itens'] = $total;
            }
        }
        $this->set_total_itens($total);

        $campos_consulta = array();
        if (is_array($campos)) {
            $campos_consulta = $campos;
        } elseif (is_string($campos)) {
            $campos_consulta[] = $campos;
        }
        if (is_array($campos_consultar)) {
            $campos_consulta = array_merge($campos_consulta, $campos_consultar);
        } elseif (is_string($campos_consultar)) {
            $campos_consulta[] = $campos_consultar;
        }
        $campos_consulta = array_unique($campos_consulta);

        $resultado = $this->objeto->consultar_varios($condicoes, $campos_consulta, $ordem, $index,
                                                     $this->itens_pagina,
                                                     ($this->pagina - 1) * $this->itens_pagina);

        // Imprimir o inicio da lista
        if ($resultado !== false) {
            $this->flag_inicio = true;
            lista::inicio($this->titulo(), $this->id_lista);

            // Caso nao tenha obtido nenhum resultado
            if (!count($resultado)) {
                switch ($this->get_genero()) {
                case 'M':
                    echo '<p>Nenhum '.$this->get_singular()."</p>\n";
                    break;
                case 'F':
                    echo '<p>Nenhuma '.$this->get_singular()."</p>\n";
                    break;
                case 'I':
                    echo '<p>Nenhum(a) '.$this->get_singular()."</p>\n";
                    break;
                }
            }

        // Caso tenha ocorrido um erro
        } else {
            mensagem::erro('Erro ao consultar '.$this->get_plural());
            return false;
        }
        return $resultado;
    }


    //
    //     Imprime o fim da lista
    //
    public function fim_lista() {
        if ($this->flag_inicio) {
            lista::fim($this->rodape());
        }
    }


    //
    //     Retorna a entidade no singular
    //
    public function get_singular() {
        if ($this->impressao_singular) {
            return $this->impressao_singular;
        } elseif ($this->objeto) {
            return $this->objeto->get_entidade();
        }
        return 'Registro';
    }


    //
    //     Retorna a entidade no plural
    //
    public function get_plural() {
        if ($this->impressao_plural) {
            return $this->impressao_plural;
        } elseif ($this->objeto) {
            return $this->objeto->get_entidade(1);
        }
        return 'Registros';
    }


    //
    //     Retorna o genero da entidade
    //
    public function get_genero() {
        if ($this->impressao_genero) {
            return $this->impressao_genero;
        } elseif ($this->objeto) {
            return $this->objeto->get_genero();
        }
        return 'I';
    }


    //
    //     Define o nome no singular e plural
    //
    public function set_nomes($singular, $plural, $genero = 'I') {
    // String $singular: nome no singular
    // String $plural: nome no plural
    // Char $genero: genero do nome
    //
        $this->impressao_singular = $singular;
        $this->impressao_plural = $plural;
        $this->impressao_genero = $genero;
    }


    //
    //     Gera uma seta para esquerda
    //
    public static function seta_esquerda() {
        global $CFG;
        if ($CFG->agent->mozfamily && $CFG->utf8) {
            return '&#x21E6;';
        } elseif ($CFG->agent->ie) {
            return '&#x25C4;';
        }
        return '&larr;';
    }


    //
    //     Gera uma seta para direita
    //
    public static function seta_direita() {
        global $CFG;
        if ($CFG->agent->mozfamily && $CFG->utf8) {
            return '&#x21E8;';
        } elseif ($CFG->agent->ie) {
            return '&#x25BA;';
        }
        return '&rarr;';
    }
}
