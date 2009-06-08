<?php
//
// SIMP
// Descricao: Metodos uteis para montar condicoes SQL genericas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.5
// Data: 17/04/2008
// Modificado: 22/05/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

//TODO: Criar um mecanismo de criacao de consultas de forma textual (inclui a definicao de uma gramatica e um parser de conversao)

// Constantes
define('CONDICAO_SQL_VAZIA',       0); // Nenhuma condicao
define('CONDICAO_SQL_SIMPLES',     1); // Dois operandos
define('CONDICAO_SQL_COMPOSTA',    2); // N operandos
define('CONDICAO_SQL_UNITARIA',    3); // 1 operando
define('CONDICAO_SQL_AGRUPAMENTO', 4); // N consultas

final class condicao_sql {

    // Vetor de atributos da condicao (variam de acordo com o tipo de condicao, conforme definido abaixo)
    private $valores;

    // CONDICAO_SQL_VAZIA: consulta incondicional
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)

    // CONDICAO_SQL_SIMPLES: %operando1 %operador %operando2
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operando1           // Operando da esquerda (um atributo)
    // - String || Null $operando2   // Operando da direita (um atributo ou um valor ou null)
    // - String $operador            // Operador: < <= > >= = <> LIKE UNLIKE
    // - Bool $entre_atributos       // Flag indicando se o segundo operando e' um atributo (true) ou um valor (false)

    // CONDICAO_SQL_COMPOSTA: ( %condicao{1} [ %operador %condicao{2} ... ] )
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: AND OR
    // - Array[condicao_sql] $vetor  // Vetor de condicoes concatenadas sob o operador

    // CONDICAO_SQL_UNITARIA: %operador (%condicao)
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: NOT
    // - condicao_sql $condicao      // Condicao que serve de operando para o operador

    // CONDICAO_SQL_AGRUPAMENTO: (%consulta) [ %operador (%consulta) ... ]
    // - Int $tipo                   // Tipo de condicao
    // - String $id                  // Identificador unico da condicao (nao obrigatorio)
    // - String $operador            // Operador: UNION
    // - Array[condicao_sql] $vetor  // Vetor de condicoes das consultas a serem agrupadas sob o operador


    //
    //     Construtor padrao (prefira utilizar os metodos factory)
    //
    public function __construct($tipo = CONDICAO_SQL_VAZIA) {
    // Int $tipo: tipo de condicao (vazia, simples, composta, unitaria ou de agrupamento)
    //
        $this->valores = array();
        $tipo = abs($tipo);

        // A condicao sempre precisa de um tipo
        switch ($tipo) {
        case CONDICAO_SQL_VAZIA:
        case CONDICAO_SQL_SIMPLES:
        case CONDICAO_SQL_COMPOSTA:
        case CONDICAO_SQL_UNITARIA:
        case CONDICAO_SQL_AGRUPAMENTO:
            $this->valores['tipo'] = $tipo;
            break;
        default:
            $this->valores['tipo'] = CONDICAO_SQL_VAZIA;
            trigger_error('Valor invalido para o tipo de condicao ('.util::exibir_var($tipo).')', E_USER_NOTICE);
            break;
        }
    }


    //
    //     Define um atributo a condicao, caso ela permita o atributo
    //
    public function __set($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        $atributo = strtolower($atributo);
        switch ($this->valores['tipo']) {
        case CONDICAO_SQL_VAZIA:
            $this->set_atributo_sql_vazia($atributo, $valor);
            return;
        case CONDICAO_SQL_SIMPLES:
            $this->set_atributo_sql_simples($atributo, $valor);
            return;
        case CONDICAO_SQL_COMPOSTA:
            $this->set_atributo_sql_composta($atributo, $valor);
            return;
        case CONDICAO_SQL_UNITARIA:
            $this->set_atributo_sql_unitaria($atributo, $valor);
            return;
        case CONDICAO_SQL_AGRUPAMENTO:
            $this->set_atributo_sql_agrupamento($atributo, $valor);
            return;
        }
    }


    //
    //     Obtem um atributo da condicao
    //
    public function &__get($atributo) {
    // String $atributo: nome do atributo desejado
    //
        $atributo = strtolower($atributo);
        if (isset($this->valores[$atributo])) {
            return $this->valores[$atributo];
        }
        if ($atributo == 'operando2') {
            return $this->valores[$atributo];
        }
        throw new Exception("N&atilde;o existe o atributo {$atributo}");
    }


    //
    //     Retorna uma condicao na forma de string (comandos PHP de construcao da condicao)
    //
    public function __toString() {
        $id = $this->valores['id'];
        switch ($this->valores['tipo']) {
        case CONDICAO_SQL_VAZIA:
            $id = $id ? "'{$id}'" : '';
            return "condicao_sql::sql_vazia({$id})";
        case CONDICAO_SQL_SIMPLES:
            $id = $id ? ", '{$id}'" : '';
            $operando1 = texto::codificar($this->valores['operando1']);
            if (is_null($this->valores['operando2'])) {
                switch ($this->valores['operador']) {
                case '=':
                    return "condicao_sql::montar('{$operando1}', '=', null, false{$id})";
                case '<>':
                    return "condicao_sql::montar('{$operando1}', '&lt;&gt;', null, false{$id})";
                }
            } else {
                $operando2 = texto::codificar($this->valores['operando2']);
                $operador = texto::codificar($this->valores['operador']);
                $entre_atributos = $this->valores['entre_atributos'] ? 'true' : 'false';
                return "condicao_sql::montar('{$operando1}', '{$operador}', '{$operando2}', {$entre_atributos}{$id})";
            }
        case CONDICAO_SQL_COMPOSTA:
            $id = $id ? ", '{$id}'" : '';
            $operador = $this->valores['operador'];
            $vetor = "array(\n".implode(",\n", array_map('strval', $this->valores['vetor'])).")\n";
            if ($operador == 'AND') {
                return "condicao_sql::sql_and({$vetor}{$id})";
            } elseif ($operador == 'OR') {
                return "condicao_sql::sql_or({$vetor}{$id})";
            }
            break;
        case CONDICAO_SQL_UNITARIA:
            $operador = $this->valores['operador'];
            if ($operador == 'NOT') {
                $condicao = strval($this->valores['condicao']);
                return "condicao_sql::sql_not({$condicao})";
            }
            break;
        case CONDICAO_SQL_AGRUPAMENTO:
            $id = $id ? ", '{$id}'" : '';
            $operador = $this->valores['operador'];
            $vetor = "array(\n".implode(",\n", array_map('strval', $this->valores['vetor'])).")\n";
            if ($operador = 'UNION') {
                return "condicao_sql::sql_union({$vetor}{$id})";
            }
            break;
        }
        return false;
    }


/// @ METODOS FACTORY (CRIACAO DINAMICA DE OBJETOS)


    //
    //     Cria uma nova condicao vazia (consulta incondicional)
    //
    static public function vazia($id = '') {
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;
        $condicao = new $classe(CONDICAO_SQL_VAZIA);
        $condicao->id = $id;
        return $condicao;
    }


    //
    //     Cria uma nova condicao simples
    //
    static public function montar($operando1, $operador, $operando2, $entre_atributos = false, $id = '') {
    // String $operando1: operando 1 (sempre um atributo)
    // String $operador: codigo da operacao
    // String || Null $operando2: operando 2 (um atributo ou um valor ou null)
    // Bool $entre_atributos: indica se o operando2 e' um atributo (true) ou um valor (false)
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;

        // Operadores especiais
        switch ($operador) {
        case 'UNLIKE':
            $operador = 'LIKE';
            $condicao = new $classe(CONDICAO_SQL_SIMPLES);
            $condicao->operando1       = $operando1;
            $condicao->operador        = $operador;
            $condicao->operando2       = $operando2;
            $condicao->entre_atributos = $entre_atributos;
            $condicao->id              = $id;
            if ($condicao->possui_erros()) {
                return false;
            }
            return self::sql_not($condicao);
        }

        $condicao = new $classe(CONDICAO_SQL_SIMPLES);
        $condicao->operando1       = $operando1;
        $condicao->operador        = $operador;
        $condicao->operando2       = $operando2;
        $condicao->entre_atributos = $entre_atributos;
        $condicao->id              = $id;
        if ($condicao->possui_erros()) {
            return false;
        }
        return $condicao;
    }


    //
    //     Une duas ou mais condicoes com o operandor AND
    //
    static public function sql_and($vt_condicoes, $id = '') {
    // Array[condicao_sql] $vt_condicoes: vetor de condicoes a serem agrupadas
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;
        if (count($vt_condicoes) == 1) {
            $condicao = array_pop($vt_condicoes);
            if (!($condicao instanceof $classe)) {
                trigger_error('Tipo invalido para a condicao ('.util::get_tipo($condicao).')', E_USER_WARNING);
                return false;
            }
        } else {
            $condicao = new $classe(CONDICAO_SQL_COMPOSTA);
            $condicao->operador = 'AND';
            $condicao->vetor    = $vt_condicoes;
            $condicao->id       = $id;
        }
        if ($condicao->possui_erros()) {
            return false;
        }
        return $condicao;
    }


    //
    //     Une duas ou mais condicoes com o operandor OR
    //
    static public function sql_or($vt_condicoes, $id = '') {
    // Array[condicao_sql] $vt_condicoes: vetor de condicoes a serem agrupadas
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;
        if (count($vt_condicoes) == 1) {
            $condicao = array_pop($vt_condicoes);
            if (!($condicao instanceof $classe)) {
                trigger_error('Tipo invalido para a condicao ('.util::get_tipo($condicao).')', E_USER_WARNING);
                return false;
            }
        } else {
            $condicao = new $classe(CONDICAO_SQL_COMPOSTA);
            $condicao->operador = 'OR';
            $condicao->vetor    = $vt_condicoes;
            $condicao->id       = $id;
        }
        if ($condicao->possui_erros()) {
            return false;
        }
        return $condicao;
    }


    //
    //     Cria uma nova condicao negando uma ja existente
    //
    static public function sql_not($condicao_original, $id = '') {
    // condicao_sql $condicao_original: condicao a ser negada
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;
        $condicao = new $classe(CONDICAO_SQL_UNITARIA);
        $condicao->operador = 'NOT';
        $condicao->condicao = $condicao_original;
        $condicao->id       = $id;
        return $condicao;
    }


    //
    //     Une duas ou mais consultas com o operandor UNION
    //
    static public function sql_union($vt_condicoes, $id = '') {
    // Array[condicao_sql] $vt_condicoes: vetor de condicoes a serem agrupadas
    // String $id: identificador unico da condicao
    //
        $classe = __CLASS__;
        if (count($vt_condicoes) == 1) {
            $condicao = array_pop($vt_condicoes);
            if (!($condicao instanceof $classe)) {
                trigger_error('Tipo invalido para a condicao ('.util::get_tipo($condicao).')', E_USER_WARNING);
                return false;
            }
        } else {
            $condicao = new $classe(CONDICAO_SQL_AGRUPAMENTO);
            $condicao->operador = 'UNION';
            $condicao->vetor    = $vt_condicoes;
            $condicao->id       = $id;
        }
        if ($condicao->possui_erros()) {
            return false;
        }
        return $condicao;
    }


    //
    //     Cria uma condicao sobre uma data (obtem os registros de determinado dia)
    //
    static public function data($operando1, $operando2, $id = '') {
    // String $operando1: operando 1 (sempre um atributo)
    // String $operando2: operando 2 um valor de data no formato dd-mm-aaaa
    // String $id: identificador unico da condicao
    //
        $data = explode('-', $operando2);
        if (count($data) != 3) {
            trigger_error('A condicao de data deve ter um valor no formato dd-mm-aaaa', E_USER_ERROR);
        }
        $data_inicio = sprintf('%02d-%02d-%04d-%02d-%02d-%02d', $data[0], $data[1], $data[2], 0, 0, 0);
        $data_fim    = sprintf('%02d-%02d-%04d-%02d-%02d-%02d', $data[0], $data[1], $data[2], 23, 59, 59);
        return self::sql_between($operando1, $data_inicio, $data_fim, $id);
    }


    //
    //     Cria uma condicao BETWEEN (entre) para especificar um intervalo de valores numericos desejados
    //
    static public function sql_between($operando, $inicio, $fim, $id = '') {
    // String $operando: nome do operando a ser comparado
    // Int | Float $inicio: inicio do intervalo
    // Int | Float $fim: fim do intervalo
    // String $id: identificador unico da condicao
    //
        $condicoes = array();
        $condicoes[] = self::montar($operando, '>=', $inicio, false);
        $condicoes[] = self::montar($operando, '<=', $fim, false);
        return self::sql_and($condicoes, $id);
    }


    //
    //     Cria uma condicao IN (em) para especificar um conjunto de valores desejados
    //
    static public function sql_in($operando, $valores, $id = '') {
    // String $operando: nome do operando a ser comparado
    // Array[String] $valores: conjunto de valores desejados
    // String $id: identificador unico da condicao
    //
        $condicoes = array();
        foreach ($valores as $valor) {
            $condicoes[] = self::montar($operando, '=', $valor, false);
        }
        return self::sql_or($condicoes, $id);
    }


    //
    //     Cria uma condicao EXCEPT (exceto) para especificar um conjunto de valores indesejados
    //
    static public function sql_except($operando, $valores, $id = '') {
    // String $operando: nome do operando a ser comparado
    // Array[String] $valores: conjunto de valores indesejados
    // String $id: identificador unico da condicao
    //
        $condicoes = array();
        foreach ($valores as $valor) {
            $condicoes[] = self::montar($operando, '<>', $valor, false);
        }
        return self::sql_and($condicoes, $id);
    }


/// @ METODOS AUXILIARES


    //
    //     Filtra o operador simples para padronizacao
    //
    static private function filtrar_operador($operador) {
    // String $operador: codigo do operador simples
    //
        $operador = trim(strtoupper($operador));
        switch ($operador) {

        // Operadores Simples Oficiais
        case '<':
        case '<=':
        case '>':
        case '>=':
        case '=':
        case '<>':
        case 'LIKE':
            return $operador;

        // Apelidos de Operadores
        case '!=':
            return '<>';
        case '==':
            return '=';
        case '~':
            return 'LIKE';
        }
        trigger_error('Operador "'.util::exibir_var($operador).'" desconhecido', E_USER_NOTICE);
        return false;
    }


    //
    //     Define um atributo de uma condicao vazia
    //
    private function set_atributo_sql_vazia($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo) {
        case 'id':
            $this->valores[$atributo] = (string)$valor;
            return;
        }
        trigger_error('Atributo "'.util::exibir_var($atributo).'" nao pode ser atribuido a uma condicao vazia', E_USER_NOTICE);
    }


    //
    //     Define um atributo de uma condicao simples
    //
    private function set_atributo_sql_simples($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo) {
        case 'id':
        case 'operando1':
            $this->valores[$atributo] = (string)$valor;
            return;
        case 'operando2':
            if (is_null($valor)) {
                $this->valores[$atributo] = null;
            } else {
                $this->valores[$atributo] = (string)$valor;
            }
            return;
        case 'operador':
            $valor = self::filtrar_operador($valor);
            if ($valor) {
                $this->valores[$atributo] = $valor;
            }
            return;
        case 'entre_atributos':
            $this->valores[$atributo] = (bool)$valor;
            return;
        }
        trigger_error('Atributo "'.util::exibir_var($atributo).'" nao pode ser atribuido a uma condicao simples', E_USER_NOTICE);
    }


    //
    //     Define um atributo de uma condicao composta
    //
    private function set_atributo_sql_composta($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo) {
        case 'id':
            $this->valores[$atributo] = (string)$valor;
            return;
        case 'operador':
            $valor = strtoupper($valor);
            $operadores = array('AND', 'OR');
            if (in_array($valor, $operadores)) {
                $this->valores[$atributo] = $valor;
            }
            return;
        case 'vetor':
            if (is_array($valor)) {
                $this->valores[$atributo] = $valor;
            }
            return;
        }
        trigger_error('Atributo "'.util::exibir_var($atributo).'" nao pode ser atribuido a uma condicao composta', E_USER_NOTICE);
    }


    //
    //     Define um atributo de uma condicao unitaria
    //
    private function set_atributo_sql_unitaria($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo) {
        case 'id':
            $this->valores[$atributo] = (string)$valor;
            return;
        case 'operador':
            $valor = strtoupper($valor);
            $operadores = array('NOT');
            if (in_array($valor, $operadores)) {
                $this->valores[$atributo] = $valor;
            }
            return;
        case 'condicao':
            $classe = __CLASS__;
            if ($valor instanceof $classe) {
                $this->valores[$atributo] = $valor;
            }
            return;
        }
        trigger_error('Atributo "'.util::exibir_var($atributo).'" nao pode ser atribuido a uma condicao unitaria', E_USER_NOTICE);
    }


    //
    //     Define um atributo de uma condicao de agrupamento
    //
    private function set_atributo_sql_agrupamento($atributo, $valor) {
    // String $atributo: nome do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo) {
        case 'id':
            $this->valores[$atributo] = (string)$valor;
            return;
        case 'operador':
            $valor = strtoupper($valor);
            $operadores = array('UNION');
            if (in_array($valor, $operadores)) {
                $this->valores[$atributo] = $valor;
            }
            return;
        case 'vetor':
            if (is_array($valor)) {
                $this->valores[$atributo] = $valor;
            }
            return;
        }
        trigger_error('Atributo "'.util::exibir_var($atributo).'" nao pode ser atribuido a uma condicao de agrupamento', E_USER_NOTICE);
    }


    //
    //     Checa a integridade da condicao
    //
    public function possui_erros() {
        switch ($this->valores['tipo']) {
        case CONDICAO_SQL_VAZIA:
            $r = false;
            break;
        case CONDICAO_SQL_SIMPLES:
            $r = !isset($this->valores['operando1']) ||
                 !isset($this->valores['operador']);
            if ($r) {
                trigger_error('A condicao simples possui algum erro ou esta incompleta', E_USER_NOTICE);
            }

            // Checar IS NULL e IS NOT NULL
            $vt_operadores = array('=', '<>');
            if (is_null($this->valores['operando2']) && !in_array($this->valores['operador'], $vt_operadores)) {
                trigger_error('A condicao de comparacao com valor nulo so aceita os operadores "igual" e "diferente"');
            }
            break;
        case CONDICAO_SQL_COMPOSTA:
            $r = !isset($this->valores['operador']) ||
                 !isset($this->valores['vetor']);
            if (!$r) {
                foreach ($this->valores['vetor'] as $condicao) {
                    if ($condicao->possui_erros()) {
                        $r = true;
                        break;
                    }
                }
            }
            if ($r) {
                trigger_error('A condicao composta possui algum erro ou esta incompleta', E_USER_NOTICE);
            }
            break;
        case CONDICAO_SQL_UNITARIA:
            $r = !isset($this->valores['operador']) ||
                 !isset($this->valores['condicao']) ||
                 $this->valores['condicao']->possui_erros();
            if ($r) {
                trigger_error('A condicao unitaria possui algum erro ou esta incompleta', E_USER_NOTICE);
            }
            break;
        case CONDICAO_SQL_AGRUPAMENTO:
            $r = !isset($this->valores['operador']) ||
                 !isset($this->valores['vetor']) ||
                 self::possui_tipo_condicao(CONDICAO_SQL_AGRUPAMENTO, $this->valores['vetor']);
            if (!$r) {
                foreach ($this->valores['vetor'] as $condicao) {
                    if ($condicao->possui_erros()) {
                        $r = true;
                        break;
                    }
                }
            }
            if ($r) {
                trigger_error('A condicao de agrupamento possui algum erro ou esta incompleta', E_USER_NOTICE);
            }
            break;
        default:
            $r = true;
            break;
        }
        return $r;
    }


    //
    //     Checa recursivamente se uma condicao possui um determinado tipo
    //
    static public function possui_tipo_condicao($tipo, $condicao) {
    // Int $tipo: tipo de condicao
    // condicao_sql || Array[condicao_sql] $condicao: local da busca
    //
        if (is_array($condicao)) {
            foreach ($condicao as $c) {
                if ($c->tipo == $tipo) {
                    return true;
                }
                switch ($c->tipo) {
                case CONDICAO_SQL_COMPOSTA:
                    foreach ($condicao->vetor as $c) {
                        if (self::possui_tipo_condicao($tipo, $c)) {
                            return true;
                        }
                    }
                    return false;
                case CONDICAO_SQL_UNITARIA:
                    return self::possui_tipo_condicao($tipo, $condicao->condicao);
                case CONDICAO_SQL_AGRUPAMENTO:
                    foreach ($condicao->vetor as $c) {
                        if (self::possui_tipo_condicao($tipo, $c)) {
                            return true;
                        }
                    }
                    return false;
                }
            }
            return false;
        } elseif (is_object($condicao)) {
            return $condicao->tipo == $tipo;
        }
        trigger_error('Tipo invalido para o parametro condicao ('.util::get_tipo($condicao).')', E_USER_NOTICE);
        return false;
    }


    //
    //     Exporta os dados do objeto para um stdClass
    //
    public function exportar() {
        switch ($this->valores['tipo']) {
        case CONDICAO_SQL_VAZIA:
        case CONDICAO_SQL_SIMPLES:
            return (object)$this->valores;
        case CONDICAO_SQL_COMPOSTA:
        case CONDICAO_SQL_AGRUPAMENTO:
            $valores = $this->valores;
            unset($valores['vetor']);
            $obj = (object)$valores;
            foreach ($this->valores['vetor'] as $c) {
                $obj->vetor[] = $c->exportar();
            }
            return $obj;
        case CONDICAO_SQL_UNITARIA:
            $valores = $this->valores;
            unset($valores['condicao']);
            $obj = (object)$valores;
            $obj->condicao = $this->valores['condicao']->exportar();
            return $obj;
        }
    }

}//class
