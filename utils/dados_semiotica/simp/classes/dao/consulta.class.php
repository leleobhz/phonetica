<?php
//
// SIMP
// Descricao: Classe que estrutura uma consulta em SQL de forma generica e portavel (usar apenas o metodo estruturar)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 02/06/2008
// Modificado: 31/07/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Contextos de estruturacao da consulta (fases do algoritmo)
define('CONSULTA_CONTEXTO_ATRIBUTOS', 1);
define('CONSULTA_CONTEXTO_CONDICOES', 2);
define('CONSULTA_CONTEXTO_ORDEM',     3);

final class consulta {
    private $contexto;            // Int Contexto da estruturacao da consulta
    private $classe;              // String Nome da classe entidade raiz da consulta
    private $contador_campos;     // Int Contador de campos para geracao de apelidos
    private $contador_tabelas;    // Int Contador de tabelas para geracao de apelidos
    private $contador_condicoes;  // Int Contador de condicoes geradas ao longo da estruturacao

    // Array[String => campo]: vetor de campos envolvidos na consulta (indexado pelo apelido do campo)
    private $campos;
    // campo:
    // - String $nome      // Nome do campo no BD
    // - String $apelido   // Apelido do campo na consulta
    // - String $atributo  // Nome do atributo correspondente na classe
    // - Bool $consultar   // Indica se o campo deve ser consultado para ser retornado
    // - &tabela $tabela   // Referencia para o objeto que guarda dos dados da tabela que possui o campo

    // Array[String => tabela]: vetor de tabelas envolvidas na consulta (indexado pelo apelido da tabela)
    private $tabelas;
    // tabela:
    // - String $nome      // Nome da tabela no BD
    // - String $apelido   // Apelido da tabela na consulta
    // - String $classe    // Nome da classe entidade que utiliza a tabela (mapeamento classe/tabela)
    // - String $atributo  // Caminho do objeto raiz ate' o objeto que contem a tabela (contexto da tabela)

    // Array[juncao]: vetor de juncoes entre tabelas
    private $juncoes;
    // - &tabela tabela1    // Tabela da esquerda na juncao
    // - &tabela tabela2    // Tabela da direita na juncao
    // - String $tipo_join  // Tipo de juncao (INNER ou LEFT)
    // - condicao $condicao // Objeto que representa a condicao de juncao

    // Array[String => campo]: vetor de campos usados para ordenacao (indexado pelo apelido do campo)
    private $ordem;
    // - &campo $campo     // Referencia para o objeto com os dados do campo usado na ordenacao
    // - Bool $tipo_ordem  // Tipo de ordenacao (crescente = true ou decrescente = false)

    // Array[String => Array[String => Bool]]: vetor de campos identicos na consulta (usado para otimizar consultas)
    private $identicos;

    private $limite;  // Int || Bool: limite de resultados a serem retornados ou false para nenhum limite
    private $inicio;  // Int: posicao de inicio dos resultados a serem apresentados

    // condicao: condicoes da consulta (similar 'a classe condicao_sql, exceto que os
    // operandos sao referencias para algum objeto da lista de campos envolvidos)
    // que varia de acordo com o tipo:
    private $condicao;
    // CONDICAO_SQL_VAZIA: consulta incondicional
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    //
    // CONDICAO_SQL_SIMPLES: %operando1 %operador %operando2
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - &campo $operando1           // Operando da esquerda (um atributo)
    // - &campo || String $operando2 // Operando da direita (um atributo ou um valor)
    // - String $operador            // Operador: < <= > >= = <> LIKE
    // - Bool $entre_atributos       // Flag indicando se o segundo operando e' um atributo (true) ou um valor (false)
    //
    // CONDICAO_SQL_COMPOSTA: ( %condicao{1} [ %operador %condicao{2} ... ] )
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: AND OR
    // - Array[condicao] $vetor      // Vetor de condicoes concatenadas sob o operador
    //
    // CONDICAO_SQL_UNITARIA: %operador (%condicao)
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: NOT
    // - condicao $condicao          // Condicao que serve de operando para o operador
    //
    // CONDICAO_SQL_AGRUPAMENTO: (%consulta) [ %operador (%consulta) ... ]
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: UNION
    // - Array[condicao] $vetor      // Vetor de condicoes das consultas a serem agrupadas sob o operador


/// @ METODOS NECESSARIOS PARA MONTAR A ESTRUTURA DE UMA CONSULTA


    //
    //     Construtor padrao (singleton: use o metodo estruturar)
    //
    private function __construct($classe) {
    // String $classe: nome da classe entidade raiz da consulta
    //
        $this->classe    = $classe;
        $this->campos    = array();
        $this->tabelas   = array();
        $this->juncoes   = array();
        $this->condicao  = null;
        $this->ordem     = array();
        $this->identicos = array();
        $this->limite    = false;
        $this->inicio    = 0;

        // Zerar contadores
        $this->contador_campos    = 0;
        $this->contador_tabelas   = 0;
        $this->contador_condicoes = 0;
    }


    //
    //     GET
    //
    public function __get($atributo) {
    // String $atributo: nome do atributo desejado
    //
        if (isset($this->$atributo)) {
            return $this->$atributo;
        }
        trigger_error("O atributo {$atributo} nao existe na classe consulta", E_USER_WARNING);
        return null;
    }


    //
    //     Obtem a posicao de um campo na lista de campos a serem consultados
    //
    public function get_posicao($apelido) {
    // String $apelido: apelido do campo a ser buscado
    //
        $i = 0;
        foreach ($this->campos as $campo) {
            if ($campo->consultar) {
                $i++;
            }
            if ($campo->apelido == $apelido) {
                return $i;
            }
        }
        return false;
    }


    //
    //     Indica se e' necessario utilizar os apelidos das tabelas
    //
    public function precisa_usar_apelidos_tabelas() {
        return count($this->tabelas) > 1;
    }


    //
    //     Adiciona uma tabela no vetor de tabelas envolvidas na consulta
    //
    private function adicionar_tabela($nome, $classe, $atributo = '') {
    // String $nome: nome da tabela
    // String $classe: nome da classe que usa a tabela
    // String $atributo: caminho do objeto raiz ate o objeto que possui a tabela
    //
        $obj = new stdClass();
        $obj->nome     = $nome;
        $obj->apelido  = 't'.base_convert(++$this->contador_tabelas, 10, 36);
        $obj->classe   = $classe;
        $obj->atributo = $atributo;
        $this->tabelas[$obj->apelido] = $obj;
        return $obj->apelido;
    }


    //
    //     Checa se a tabela ja' foi adicionada
    //
    private function possui_tabela($nome, $atributo, &$apelido_tabela = '') {
    // String $nome: nome da tabela a ser procurada
    // String $atributo: caminho ate' o objeto que possui a tabela
    // String $apelido_tabela: preenche com o apelido da tabela caso seja encontrada
    //
        foreach ($this->tabelas as $obj) {
            if (strcmp($obj->nome, $nome) == 0 && strcmp($obj->atributo, $atributo) == 0) {
                $apelido_tabela = $obj->apelido;
                return true;
            }
        }
        $apelido_tabela = '';
        return false;
    }


    //
    //     Adiciona um campo no vetor de campos envolvidos na consulta
    //
    private function adicionar_campo($nome, $atributo, $apelido_tabela, $consultar = null) {
    // String $nome: nome do campo
    // String $atributo: nome do atributo na classe entidade raiz
    // String $apelido_tabela: apelido da tabela que possui o campo
    // Bool $consultar: indica se o campo deve ser consultado
    //
        if (!isset($this->tabelas[$apelido_tabela])) {
            trigger_error('A tabela com apelido "'.$apelido_tabela.'" nao foi adicionada');
            return false;
        }

        $obj = new stdClass();
        $obj->nome      = $nome;
        $obj->atributo  = $atributo;
        if (is_null($consultar)) {
            $obj->consultar = $this->contexto == CONSULTA_CONTEXTO_ATRIBUTOS;
        } else {
            $obj->consultar = (bool)$consultar;
        }
        $obj->apelido   = 'c'.base_convert(++$this->contador_campos, 10, 36);
        $obj->tabela    = &$this->tabelas[$apelido_tabela];
        $this->campos[$obj->apelido] = $obj;
        return $obj->apelido;
    }


    //
    //     Checa se o campo ja' foi adicionado em alguma tabela
    //
    private function possui_campo($nome, $apelido_tabela = false, &$apelido_campo = '') {
    // String $nome: nome do campo a ser procurado
    // String $apelido_tabela: apelido da tabela a ser procurada (false para qualquer uma)
    // String $apelido_campo: preenche com o apelido do campo caso seja encontrado
    //
        foreach ($this->campos as $obj) {
            if (strcmp($obj->nome, $nome) == 0) {
                if (!$apelido_tabela || strcmp($obj->tabela->apelido, $apelido_tabela) == 0) {
                    $apelido_campo = $obj->apelido;
                    return true;
                }
            }
        }
        $apelido_campo = '';
        return false;
    }


    //
    //     Adiciona uma juncao entre duas tabelas
    //
    private function adicionar_juncao($apelido_tabela1, $apelido_tabela2, $condicao, $tipo_join) {
    // String $apelido_tabela1: apelido da tabela da esquerda
    // String $apelido_tabela2: apelido da tabela da direita
    // condicao_sql $condicao: condicao de juncao
    // String $tipo_join: tipo de juncao (INNER ou LEFT)
    //
        if (!isset($this->tabelas[$apelido_tabela1])) {
            trigger_error('A tabela com apelido "'.$apelido_tabela1.'" nao foi adicionada');
            return false;
        }
        if (!isset($this->tabelas[$apelido_tabela2])) {
            trigger_error('A tabela com apelido "'.$apelido_tabela2.'" nao foi adicionada');
            return false;
        }

        $obj = new stdClass();
        $obj->tabela1   = &$this->tabelas[$apelido_tabela1];
        $obj->tabela2   = &$this->tabelas[$apelido_tabela2];
        $obj->condicao  = $condicao;
        $obj->tipo_join = $tipo_join;
        $this->juncoes[] = $obj;
        return true;
    }


    //
    //     Marca um campo para ser usado para ordenar os resultados
    //
    private function adicionar_ordem($apelido_campo, $tipo_ordem) {
    // String $apelido_campo: apelido do campo
    // Bool $tipo_ordem: ordem crescente (true) ou decrescente (false)
    //
        if (!isset($this->campos[$apelido_campo])) {
            trigger_error('O campo com apelido "'.$apelido_campo.'" nao foi incluido');
            return false;
        }
        $obj = new stdClass();
        $obj->campo = &$this->campos[$apelido_campo];
        $obj->tipo_ordem = $tipo_ordem;
        $this->ordem[$apelido_campo] = $obj;
    }


    //
    //     Define um limite de registros retornados e o offset de inicio
    //
    private function definir_limite($limite = false, $inicio = false) {
    // Int $limite: limite de registros retornados
    // Int $inicio: posicao de inicio dos resultados
    //
        if (is_numeric($limite)) {
            $this->limite = round(abs($limite));
        }
        if (is_numeric($inicio)) {
            $this->inicio = round(abs($inicio));
        }
    }


/// @ METODOS AUXILIARES


    //
    //     Preenche a consulta com base nos atributos envolvidos
    //
    private function estruturar_atributos(&$obj, $atributos) {
    // Object $obj: objeto raiz da consulta e derivado da classe objeto
    // Array[String] $atributos: vetor de atributos desejados pelo objeto
    //
        foreach ($atributos as $atributo) {
            $this->adicionar_atributo($obj, $atributo);
        }
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para obter o atributo para o objeto
    //
    private function adicionar_atributo(&$obj, $atributo) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // String $atributo: nome do atributo do objeto informado
    //
        $pos = strpos($atributo, ':');
        if ($pos === false) {
            return $this->adicionar_atributo_simples($obj, $atributo);
        } else {
            return $this->adicionar_atributo_complexo($obj, $atributo);
        }
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para obter o atributo simples para o objeto
    //
    private function adicionar_atributo_simples(&$obj, $atributo) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // String $atributo: nome do atributo simples do objeto informado
    //
        if (!$obj->possui_atributo($atributo)) {
            trigger_error('A classe "'.$obj->get_classe().'" nao possui o atributo "'.$atributo.'"');
            return false;
        }

        $tabela = $obj->get_tabela();
        if (!$this->possui_tabela($tabela, '', $apelido_tabela)) {
            $classe = $obj->get_classe();
            $apelido_tabela = $this->adicionar_tabela($tabela, $classe, '');
        }
        if (!$this->possui_campo($atributo, $apelido_tabela, $apelido_campo)) {
            $apelido_campo =  $this->adicionar_campo($atributo, $atributo, $apelido_tabela);
        }
        return $apelido_campo;
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para obter o atributo complexo para o objeto
    //
    private function adicionar_atributo_complexo(&$obj, $atributo) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // String $atributo: nome do atributo complexo do objeto informado
    //
        // Quebrar o caminho ate' chegar no atributo simples
        $caminho = explode(':', $atributo);
        $atributo = array_pop($caminho);

        // Para cada parte do caminho, incluir a juncao entre as entidades
        $caminho_relativo = '';
        $obj1 = &$obj;
        foreach ($caminho as $filho) {
            unset($obj2);

            // Juncao entre objetos
            if ($obj1->possui_rel_uu($filho)) {
                $obj2 = $obj1->__get($filho);
                $this->adicionar_join_objeto($obj1, $obj2, $filho, $caminho_relativo);
                unset($obj1);
                $obj1 = &$obj2;

            // Juncao entre um objeto e um vetor (so' permitido em contexto de condicoes)
            } elseif ($obj1->possui_rel_un($filho)) {
                if ($this->contexto == CONSULTA_CONTEXTO_CONDICOES) {
                    $dados = $obj1->get_definicao_rel_un($filho);
                    $classe = $dados->classe;
                    $obj2 = new $classe();
                    $this->adicionar_join_vetor($obj1, $obj2, $filho, $caminho_relativo);
                    unset($obj1);
                    $obj1 = &$obj2;
                } else {
                    trigger_error('O vetor "'.$filho.'" nao pode ser utilizado no contexto de consulta, apenas em condicoes');
                    return false;
                }

            // Juncao invalida
            } else {
                trigger_error('A classe "'.$obj->get_classe().'" nao possui o atributo "'.$atributo.'"');
                return false;
            }
            $caminho_relativo .= ($caminho_relativo ? ':' : '').$filho;
        }

        // Incluir o atributo simples (sem o caminho)
        $tabela1 = $obj1->get_tabela();
        $classe1 = $obj1->get_classe();
        $atributo_obj = implode(':', $caminho);
        if (!$this->possui_tabela($tabela1, $atributo_obj, $apelido_tabela1) && $this->contexto == CONSULTA_CONTEXTO_CONDICOES) {
            $apelido_tabela1 = $this->adicionar_tabela($tabela1, $classe1, $atributo_obj);
        }
        if (!$apelido_tabela1) {
            trigger_error('A tabela "'.$tabela1.'" nao foi inserida (caminho: '.$atributo_obj.')');
            return false;
        }
        $caminho_relativo .= ':'.$atributo;
        if (!$this->possui_campo($atributo, $apelido_tabela1, $apelido_campo)) {
            $apelido_campo = $this->adicionar_campo($atributo, $caminho_relativo, $apelido_tabela1);
        }
        return $apelido_campo;
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para montar uma juncao entre os objetos
    //
    private function adicionar_join_objeto(&$obj1, &$obj2, $nome_obj2, $caminho_relativo) {
    // Object $obj1: primeiro objeto derivado da classe objeto
    // Object $obj2: segundo objeto derivado da classe objeto (objeto do primeiro)
    // String $nome_obj2: nome do segundo objeto no primeiro objeto
    // String $caminho_relativo: caminho do objeto raiz ate o primeiro objeto
    //
        // Obter dados dos objetos
        $tabela1      = $obj1->get_tabela();
        $tabela2      = $obj2->get_tabela();
        $classe1      = $obj1->get_classe();
        $classe2      = $obj2->get_classe();
        $chave1       = $obj1->get_nome_chave_rel_uu($nome_obj2);
        $chave2       = $obj2->get_chave();
        $caminho_obj1 = $caminho_relativo;
        $caminho_obj2 = ($caminho_relativo ? $caminho_relativo.':' : '').$nome_obj2;
        $caminho1     = ($caminho_relativo ? $caminho_relativo.':' : '').$chave1;
        $caminho2     = ($caminho_relativo ? $caminho_relativo.':' : '').$nome_obj2.':'.$chave2;

        // Incluir as tabelas envolvidas na juncao
        if (!$this->possui_tabela($tabela1, $caminho_obj1, $apelido_tabela1)) {
            $apelido_tabela1 = $this->adicionar_tabela($tabela1, $classe1, $caminho_obj1);
        }
        if (!$this->possui_tabela($tabela2, $caminho_obj2, $apelido_tabela2)) {
            $apelido_tabela2 = $this->adicionar_tabela($tabela2, $classe2, $caminho_obj2);
        }

        // Incluir a chave estrangeira do operando 1 e a chave primaria do operando 2
        if (!$this->possui_campo($chave1, $apelido_tabela1, $apelido_chave1)) {
            $apelido_chave1 = $this->adicionar_campo($chave1, $caminho1, $apelido_tabela1);
        }
        if (!$this->possui_campo($chave2, $apelido_tabela2, $apelido_chave2)) {
            $apelido_chave2 = $this->adicionar_campo($chave2, $caminho2, $apelido_tabela2, false);
        }

        // So' precisa consultar uma das chaves, pois elas sao iguais
        if ($this->contexto == CONSULTA_CONTEXTO_ATRIBUTOS) {
            $this->identicos[$apelido_chave1][$apelido_chave2] = true;
        }

        // Montar condicao de juncao
        $condicao = new stdClass();
        $condicao->id              = 'join'.(++$this->contador_condicoes);
        $condicao->tipo            = CONDICAO_SQL_SIMPLES;
        $condicao->operando1       = &$this->campos[$apelido_chave1];
        $condicao->operador        = '=';
        $condicao->operando2       = &$this->campos[$apelido_chave2];
        $condicao->entre_atributos = true;

        // Incluir juncao
        $def = $obj1->get_definicao_rel_uu($nome_obj2);
        $tipo_join = $def->forte ? 'INNER' : 'LEFT';
        $this->adicionar_juncao($apelido_tabela1, $apelido_tabela2, $condicao, $tipo_join);
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para montar uma juncao entre um objeto e um vetor
    //
    private function adicionar_join_vetor(&$obj1, &$obj2, $nome_obj2, $caminho_relativo) {
    // Object $obj1: primeiro objeto derivado da classe objeto
    // Object $obj2: segundo objeto derivado da classe objeto (vetor do primeiro)
    // String $nome_obj2: nome do segundo objeto no primeiro objeto
    // String $caminho_relativo: caminho do objeto raiz ate o primeiro objeto
    //
        $def = $obj1->get_definicao_rel_un($nome_obj2);

        // Obter dados dos objetos
        $tabela1      = $obj1->get_tabela();
        $tabela2      = $obj2->get_tabela();
        $classe1      = $obj1->get_classe();
        $classe2      = $obj2->get_classe();
        $chave1       = $obj1->get_chave();
        $chave2       = $def->chave_fk;
        $caminho_obj1 = $caminho_relativo;
        $caminho_obj2 = ($caminho_relativo ? $caminho_relativo.':' : '').$nome_obj2;
        $caminho1     = ($caminho_relativo ? $caminho_relativo.':' : '').$chave1;
        $caminho2     = ($caminho_relativo ? $caminho_relativo.':' : '').$nome_obj2.':'.$chave2;

        // Incluir as tabelas envolvidas na juncao
        if (!$this->possui_tabela($tabela1, $caminho_obj1, $apelido_tabela1)) {
            $apelido_tabela1 = $this->adicionar_tabela($tabela1, $classe1, $caminho_obj1);
        }
        if (!$this->possui_tabela($tabela2, $caminho_obj2, $apelido_tabela2)) {
            $apelido_tabela2 = $this->adicionar_tabela($tabela2, $classe2, $caminho_obj2);
        }

        // Incluir a chave estrangeira do operando 1 e a chave primaria do operando 2
        if (!$this->possui_campo($chave1, $apelido_tabela1, $apelido_chave1)) {
            $apelido_chave1 = $this->adicionar_campo($chave1, $caminho1, $apelido_tabela1);
        }
        if (!$this->possui_campo($chave2, $apelido_tabela2, $apelido_chave2)) {
            $apelido_chave2 = $this->adicionar_campo($chave2, $caminho2, $apelido_tabela2, false);
        }

        // So' precisa consultar uma das chaves, pois elas sao iguais
        if ($this->contexto == CONSULTA_CONTEXTO_ATRIBUTOS) {
            $this->identicos[$apelido_chave1][$apelido_chave2] = true;
        }

        // Montar condicao de juncao
        $condicao = new stdClass();
        $condicao->id              = 'join'.(++$this->contador_condicoes);
        $condicao->tipo            = CONDICAO_SQL_SIMPLES;
        $condicao->operando1       = &$this->campos[$apelido_chave1];
        $condicao->operador        = '=';
        $condicao->operando2       = &$this->campos[$apelido_chave2];
        $condicao->entre_atributos = true;

        // Incluir juncao
        $tipo_join = 'LEFT';
        $this->adicionar_juncao($apelido_tabela1, $apelido_tabela2, $condicao, $tipo_join);
    }


    //
    //     Preenche os campos, tabelas e juncoes necessarias para montar as condicoes da consulta
    //
    private function estruturar_condicao(&$obj, $condicoes) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // condicao_sql $condicoes: condicoes da consulta
    //
        $atributos = self::get_atributos_condicao($obj, $condicoes);
        foreach ($atributos as $atributo) {
            $this->adicionar_atributo($obj, $atributo, false);
        }
        $this->condicao = $this->montar_condicao($condicoes);
    }


    //
    //     Obtem a lista de atributos envolvidos nas condicoes
    //
    static private function get_atributos_condicao(&$obj, $condicao) {
    // Object $obj: objeto derivado da classe objeto
    // condicao_sql $condicao: condicao da consulta a ser analisada
    //
        $vt_atributos = array();

        switch ($condicao->tipo) {
        case CONDICAO_SQL_SIMPLES:

            // Atributo do operando 1
            $vt_atributos[] = $condicao->operando1;

            // Atributo do operando 2, caso seja uma condicao entre atributos
            if ($condicao->entre_atributos) {
                $vt_atributos[] = $condicao->operando2;
            }
            break;

        case CONDICAO_SQL_AGRUPAMENTO:
        case CONDICAO_SQL_COMPOSTA:
            foreach ($condicao->vetor as $c) {
                $vt_atributos = array_merge($vt_atributos, self::get_atributos_condicao($obj, $c));
            }
            break;

        case CONDICAO_SQL_UNITARIA:
            $vt_atributos = self::get_atributos_condicao($obj, $condicao->condicao);
            break;
        }
        return array_unique($vt_atributos);
    }


    //
    //     Monta as condicoes apontando os campos envolvidos nas condicoes para os itens da lista de campos
    //
    private function montar_condicao(&$condicoes) {
    // condicao_sql $condicoes: condicao da consulta a ser analisada
    //
        $nova = $condicoes->exportar();
        return $this->montar_condicao_recursivo($nova);
    }


    //
    //     Monta as condicoes recursivamente
    //
    private function montar_condicao_recursivo($condicao) {
    // stdClass $condicao: condicao a ser reestruturada
    //
        switch ($condicao->tipo) {
        case CONDICAO_SQL_VAZIA:
            break;
        case CONDICAO_SQL_SIMPLES:
            $apelido1 = $this->get_apelido_campo_por_atributo($condicao->operando1);
            if ($apelido1) {
                $condicao->operando1 = &$this->campos[$apelido1];
            } else {
                trigger_error('Nao foi encontrado o campo relacionado ao atributo "'.$condicao->operando1.'"');
            }
            if ($condicao->entre_atributos) {
                $apelido2 = $this->get_apelido_campo_por_atributo($condicao->operando2);
                if ($apelido2) {
                    $condicao->operando2 = &$this->campos[$apelido2];
                } else {
                    trigger_error('Nao foi encontrado o campo relacionado ao atributo2 "'.$condicao->operando2.'"');
                }
            }
            break;
        case CONDICAO_SQL_COMPOSTA:
        case CONDICAO_SQL_AGRUPAMENTO:
            foreach ($condicao->vetor as $i => $c) {
                $condicao->vetor[$i] = $this->montar_condicao_recursivo($c);
            }
            break;
        case CONDICAO_SQL_UNITARIA:
            $condicao->condicao = $this->montar_condicao_recursivo($condicao->condicao);
            break;
        }
        return $condicao;
    }


    //
    //     Retorna o apelido de um campo buscado pelo valor do atributo relacionado
    //
    private function get_apelido_campo_por_atributo($atributo) {
    // String $atributo: nome do atributo
    //
        foreach ($this->campos as $campo) {
            if (strcmp($campo->atributo, $atributo) == 0) {
                return $campo->apelido;
            }
        }
        return false;
    }


    //
    //     Marca os campos para ordenacao (apontam para itens da lista de campos)
    //
    private function marcar_campos_ordenacao(&$obj, $ordem) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // Array[String => Bool] $ordem: atributos usados para ordenar e tipo de ordem (crescente = true / decrescente = false)
    //
        foreach ($ordem as $campo => $tipo_ordem) {

            // Obter o objeto que possui o atributo
            $pos = strrpos($campo, ':');
            if ($pos !== false) {
                $atributo_obj = substr($campo, 0, $pos);
                $campo = substr($campo, $pos + 1);
                $obj_filho = $obj->__get($atributo_obj);
            } else {
                $atributo_obj = '';
                $obj_filho = $obj;
            }

            $tabela = $obj_filho->get_tabela();
            if ($this->possui_tabela($tabela, $atributo_obj, $apelido_tabela)) {
                if ($this->possui_campo($campo, $apelido_tabela, $apelido_campo)) {
                    $this->adicionar_ordem($apelido_campo, $tipo_ordem);
                }
            }
        }
    }


/// @ METODO PRINCIPAL (GERA A ESTRUTURA DE UMA CONSULTA)


    //
    //     Gera uma estrutura preparada com os nomes a serem usados em um comando SELECT em SQL
    //
    static public function estruturar($obj, $atributos, $condicoes = null, $ordem = null, $limite = false, $inicio = false) {
    // Object $obj: objeto raiz da consulta e derivado da calsse objeto
    // Array[String] $atributos: vetor de atributos desejados pelo objeto
    // condicao_sql $condicoes: condicoes da consulta
    // Array[String => Bool] $ordem: atributos usados para ordenar e tipo de ordem (crescente = true / decrescente = false)
    // Int $limite: numero maximo de elementos retornados
    // Int $inicio: obtem elementos a partir do N'esimo elemento consultado
    //
        $entidade = $obj->get_classe();
        $classe = __CLASS__;
        $consulta = new $classe($entidade);
        $atributos = array_unique($atributos);
        if (is_null($condicoes)) {
            $condicoes = condicao_sql::vazia();
        }

        // 1 - Preenche a consulta com base nos atributos pedidos para consulta
        $consulta->contexto = CONSULTA_CONTEXTO_ATRIBUTOS;
        $consulta->estruturar_atributos($obj, $atributos, true);

        // 2 - Preenche a consulta com base nos atributos presentes nas condicoes
        $consulta->contexto = CONSULTA_CONTEXTO_CONDICOES;
        $consulta->estruturar_condicao($obj, $condicoes);

        // 3 - Preenche a consulta com base nos atributos usados para ordenacao
        if (is_array($ordem)) {
            $atributos_ordenacao = array_keys($ordem);
            $consulta->contexto = CONSULTA_CONTEXTO_ORDEM;
            $consulta->estruturar_atributos($obj, $atributos_ordenacao, false);

            // 4 - Marcar os campos usados para ordenacao
            $consulta->marcar_campos_ordenacao($obj, $ordem);
        }

        // 5 - Definir limite e offset dos resultados
        $consulta->definir_limite($limite, $inicio);

        return $consulta;
    }

}//class
