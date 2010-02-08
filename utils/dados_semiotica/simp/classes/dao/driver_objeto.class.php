<?php
//
// SIMP
// Descricao: Classe base para os drivers de comunicacao com a classe objeto (extende um driver base especifico) seguindo os padroes da SQL92 (http://www.contrib.andrew.cmu.edu/~shadow/sql/sql1992.txt)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.2.0.4
// Data: 17/04/2008
// Modificado: 26/01/2010
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('DRIVER_OBJETO_UTF8',               $CFG->utf8);
define('DRIVER_OBJETO_ADICIONAR_ATRIBUTO', 1);
define('DRIVER_OBJETO_REMOVER_ATRIBUTO',   2);

abstract class driver_objeto {
    private   $driver_base      = null;  // Driver com os metodos basicos do SGBD especificado
    private   $nome_driver_base = '';    // Nome do driver base
    private   $exibicao_usuario = false; // Monta as SQLs de forma mais elegante para o usuario

/// @ METODOS ABSTRATOS


    //
    //     Monta a funcao usada em uma condicao SQL
    //
    abstract public function montar_funcao_condicao($funcao, $operando, $tipo_operando, $atributo);
    // String $funcao: nome da funcao (dia, mes, ano, hora, minuto, segundo)
    // String $operando: valor do operando
    // Int $tipo_operando: indica o que e' o operando (CONDICAO_SQL_TIPO_ATRIBUTO ou CONDICAO_SQL_TIPO_VALOR)
    // atributo $atributo: definicao do atributo
    //


/// @ METODOS ESPECIFICOS DA CLASSE


    //
    //     Construtor Padrao
    //
    public function __construct($sgbd, $servidor, $porta, $usuario, $senha, $base = null) {
    // String $sgbd: SGBD a ser extendido
    // String $servidor: endereco do servidor
    // Int $porta: porta de acesso ao servidor
    // String $usuario: login do usuario no BD ou "[root]" para o usuario root (administrador do SGBD)
    // String $senha: senha para acesso ao BD
    // String $base: nome do BD a ser usado ou 0 para nenhum
    //
        $classe = 'driver_'.$sgbd;
        $this->nome_driver_base = $classe;
        try {
            simp_autoload($classe);
            $this->driver_base = new $classe($servidor, $porta, $usuario, $senha, $base);
        } catch (Exceptcion $e) {
            throw new Exception("N&atilde;o existe driver para o SGBD especificado ({$sgbd})");
        }
    }


    //
    //     Chama algum metodo do driver base
    //
    public function __call($metodo, $args) {
    // String $metodo: nome do metodo
    // Array[Mixed] $args: vetor de paramtros
    //
        if (method_exists($this->driver_base, $metodo)) {
            return call_user_func_array(array($this->driver_base, $metodo), $args);
        } elseif (method_exists($this->nome_driver_base, $metodo)) {
            return call_user_func_array(array($this->nome_driver_base, $metodo), $args);
        }
        throw new Exception("N&atilde;o existe o m&eacute;todo {$metodo} no driver {$this->nome_driver_base} [driver_objeto]");
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
        return $this->driver_base->$atributo;
    }


    //
    //     SET
    //
    public function __set($atributo, $valor) {
    // String $atributo: nome do atributo desejado
    // Mixed $valor: valor a ser atribuido
    //
        if (isset($this->$atributo)) {
            $this->$atributo = $valor;
        } else {
            $this->driver_base->$atributo = $valor;
        }
    }


    //
    //     Define o modo de montagem das SQLs (padrao = false, usuario = true)
    //
    final public function set_exibicao_usuario($modo = false) {
    // Bool $modo: indica o modo de montagem das SQLs (padrao = false, usuario = true)
    //
        $this->exibicao_usuario = (bool)$modo;
    }


    //
    //     Indica se o resultado e' valido ou nao
    //
    public function resultado_valido($resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        return is_resource($resultado);
    }


    //
    //     Filtra os atributos do tipo condicao_sql
    //
    final public static function filtrar_condicao(&$condicao) {
    // condicao_sql $condicao: condicao a ser checada
    //
        $classe = 'condicao_sql';
        if ($condicao instanceof $classe) {
            return;
        } elseif (is_null($condicao) || is_bool($condicao)) {
            $condicao = condicao_sql::vazia();
        } else {
            trigger_error('Tipo invalido para a condicao "'.util::get_tipo($condicao).'"', E_USER_ERROR);
        }
    }


    //
    //     Checa se uma instancia casa com as condicoes
    //
    final public static function casa_condicoes($condicoes, $instancia) {
    // condicao_sql $condicoes: condicoes a serem checadas
    // Object $instancia: dados da instancia
    //
        self::filtrar_condicao($condicoes);
        switch ($condicoes->tipo) {
        case CONDICAO_SQL_SIMPLES:
            $valor1 = $instancia->{$condicoes->operando1};
            if ($condicoes->entre_atributos) {
                $valor2 = $instancia->{$condicoes->operando2};
            } else {
                $valor2 = $condicoes->operando2;
            }
            switch ($condicoes->operador) {
            case '<':
                return $valor1 < $valor2;
            case '<=':
                return $valor2 <= $valor2;
            case '>':
                return $valor1 > $valor2;
            case '>=':
                return $valor1 >= $valor2;
            case '<>':
                return strcmp($valor1, $valor2) != 0;
            case '=':
                return strcmp($valor1, $valor2);
            case 'LIKE':
                $tam = strlen($operando2);
                $exp = '/^';
                for ($i = 0; $i < $tam; $i++) {
                    switch ($condicoes->operando2[$i]) {
                    case '_':
                        if ($i > 0 && $condicoes->operando2[$i - 1] != '\\') {
                            $exp .= '(.)';
                        } else {
                            $exp .= $condicoes->operando2[$i];
                        }
                        break;
                    case '%':
                        if ($i > 0 && $condicoes->operando2[$i - 1] != '\\') {
                            $exp .= '(.*)';
                        } else {
                            $exp .= $condicoes->operando2[$i];
                        }
                        break;
                    default:
                        $exp .= $condicoes->operando2[$i];
                        break;
                    }
                }
                $exp .= '$/'.(DRIVER_OBJETO_UTF8 ? 'u' : '');
                return preg_match($exp, $valor2);
            }
            trigger_error('Operador desconhecido ('.util::exibir_var($condicoes->operador).')', E_USER_WARNING);
            return false;

        case CONDICAO_SQL_COMPOSTA:
            switch ($condicoes->operador) {
            case 'AND':
                foreach ($condicoes->vetor as $c) {
                    if (!self::casa_condicoes($c, $instancia)) {
                        return false;
                    }
                }
                return true;
            case 'OR':
                foreach ($condicoes->vetor as $c) {
                    if (self::casa_condicoes($c, $instancia)) {
                        return true;
                    }
                }
                return false;
            }
            trigger_error('Operador desconhecido para condicao composta ('.util::exibir_var($condicoes->operador).')', E_USER_WARNING);
            return false;

        case CONDICAO_SQL_UNITARIA:
            switch ($condicoes->operador) {
            case 'NOT':
                return !self::casa_condicoes($condicoes->condicao, $instancia);
            }
            trigger_error('Operador desconhecido para condicao unitaria ('.util::exibir_var($condicoes->operador).')', E_USER_WARNING);
            return false;

        case CONDICAO_SQL_AGRUPAMENTO:
            switch ($condicoes->operador) {
            case 'UNION':
                foreach ($condicoes->vetor as $c) {
                    if (self::casa_condicoes($c, $instancia)) {
                        return true;
                    }
                }
                return false;
            }
            trigger_error('Operador desconhecido para condicao de agrupamento ('.util::exibir_var($condicoes->operador).')', E_USER_WARNING);
            return false;
        }
        return false;
    }


    //
    //     Filtra um atributo do BD para o PHP
    //
    public function filtrar_atributo_bd($atributo, $valor) {
    // atributo $atributo: definicao do atributo
    // Mixed $valor: valor do atributo
    //
        switch ($atributo->tipo) {
        case 'int':
            $valor = round($valor);
            if ($valor < PHP_INT_MAX) {
                $valor = (int)$valor;
            }
            break;
        case 'float':
            $valor = (float)$valor;
            break;
        case 'string':
        case 'binario':
            $valor = (string)$valor;
            break;
        case 'char':
            $str = (string)$valor;
            $valor = isset($str[0]) ? $str[0] : '-';
            break;
        case 'bool':
            $valor = (bool)$valor;
            break;
        case 'data':
            $valor = $this->desformatar_data($valor, $atributo->campo_formulario);
            break;
        }
        return $valor;
    }


    //
    //     Filtra um atributo do PHP para o BD
    //
    public function filtrar_atributo_php($atributo, $valor, $delimitar = false) {
    // atributo $atributo: definicao do atributo
    // Mixed $valor: valor do atributo
    // Bool $delimitar: delimitar o valor (caso seja necessario)
    //
        if (is_null($valor)) {
            return 'NULL';
        }

        $retorno = null;
        switch ($atributo->tipo) {
        case 'int':
            if (is_numeric($valor)) {
                $padrao = setlocale(LC_NUMERIC, 0);
                setlocale(LC_NUMERIC, 'C');
                $retorno = sprintf('%0.0f', $valor);
                setlocale(LC_NUMERIC, $padrao);
            } else {
                $retorno = '0';
            }
            break;
        case 'float':
            if (is_numeric($valor)) {
                $sub = abs($valor) - floor(abs($valor));
                $casas_decimais = max(0, strlen($sub) - 2);
                $padrao = setlocale(LC_NUMERIC, 0);
                setlocale(LC_NUMERIC, 'C');
                $retorno = sprintf("%0.{$casas_decimais}f", $valor);
                setlocale(LC_NUMERIC, $padrao);
            } else {
                $retorno = '0.0';
            }
            break;

        case 'string':
        case 'binario':
            $retorno = (string)$valor;
            if ($delimitar) {
                $retorno = $this->delimitar_valor($retorno);
            }
            break;

        case 'char':
            $str = (string)$valor;
            $retorno = isset($str[0]) ? $str[0] : '-';
            if ($delimitar) {
                $retorno = $this->delimitar_valor($retorno);
            }
            break;

        case 'bool':
            $retorno = $valor ? 'TRUE' : 'FALSE';
            break;

        case 'data':
            if ($valor == 'agora') {
                $data = '00-00-0000-00-00-00';
            } elseif (!$valor) {
                $data = '00-00-0000-00-00-00';
            } else {
                $data = $valor;
            }
            $retorno = $this->formatar_data($valor, $atributo->campo_formulario);
            if ($delimitar) {
                $retorno = $this->delimitar_valor($retorno);
            }
            break;

        default:
            trigger_error('Tipo invalido "'.$atributo->tipo.'"', E_USER_ERROR);
            return false;
        }
        return $retorno;
    }


    //
    //     Retorna o ultimo erro no SGBD
    //
    public function get_ultimo_erro(&$str_erro = '', &$cod_erro = '0') {
    // String $str_erro: erro ocorrido
    // Int $cod_erro: codigo do erro ocorrido
    //
        return $this->driver_base->get_ultimo_erro($str_erro, $cod_erro);
    }


    //
    //     Libera a memoria do resultado
    //
    public function liberar_resultado(&$resultado) {
    // Resource $resultado: resultado a ser apagado
    //
        return $this->driver_base->liberar_resultado($resultado);
    }


    //
    //     Converte um registro para objeto
    //
    public function fetch_object(&$resultado) {
    // Resource $resultado: resultado de uma consulta
    //
        return $this->driver_base->fetch_object($resultado);
    }


    //
    //     Valida um nome de usuario
    //
    public function validar_usuario($usuario, &$erros) {
    // String $usuario: nome do usuario a ser validado
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        return $this->driver_base->validar_usuario($usuario, $erros);
    }


    //
    //     Valida uma base
    //
    public function validar_base($base, &$erros) {
    // String $base: nome da base a ser validada
    // Array[String] $erros: vetor de possiveis erros encontrados
    //
        return $this->driver_base->validar_base($base, $erros);
    }


    //
    //     Retorna uma lista de funcoes desejaveis para utilizacao em condicoes de consultas (e suas respectivas descricoes)
    //
    public static function get_funcoes($agrupado = false) {
    // Bool $agrupado: agrupa as funcoes em grupos semelhantes
    //
        if ($agrupado) {
            return array(
                'Data' => array(
                    'dia'     => 'Dia do M&ecirc;s',
                    'mes'     => 'M&ecirc;s',
                    'ano'     => 'Ano',
                    'hora'    => 'Hora',
                    'minuto'  => 'Minuto',
                    'segundo' => 'Segundo',
                    'diaano'  => 'Dia do Ano'
                )
            );
        }
        return array(
            'dia'     => 'Dia do M&ecirc;s',
            'mes'     => 'M&ecirc;s',
            'ano'     => 'Ano',
            'hora'    => 'Hora',
            'minuto'  => 'Minuto',
            'segundo' => 'Segundo',
            'diaano'  => 'Dia do Ano'
        );
    }


    //
    //     Devolve a definicao do valor retornado por uma funcao
    //
    public function get_definicao_retorno_funcao($funcao, $atributo) {
    // String $funcao: nome da funcao (dia, mes, ano, hora, minuto, segundo)
    // atributo $atributo: definicao do atributo usado como parametro para funcao
    //
        switch ($funcao) {
        case 'dia':
            $atributo = new atributo('dia', 'Dia', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(1, 31);
            break;
        case 'mes':
            $atributo = new atributo('mes', 'M&ecirc;s', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(1, 12);
            break;
        case 'ano':
            $atributo = new atributo('ano', 'Ano', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(0, 10000);
            break;
        case 'hora':
            $atributo = new atributo('hora', 'Hora', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(0, 23);
            break;
        case 'minuto':
            $atributo = new atributo('minuto', 'Minuto', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(0, 59);
            break;
        case 'segundo':
            $atributo = new atributo('segundo', 'Segundo', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(0, 59);
            break;
        case 'diaano':
            $atributo = new atributo('diaano', 'Dia do ano', 0);
            $atributo->set_tipo('int', false);
            $atributo->set_intervalo(1, 366);
            break;
        default:
            return false;
        }
        return $atributo;
    }


    //
    //     Devolve a definicao do valor passado por parametro para uma funcao
    //
    public function get_definicao_parametro_funcao($funcao, $numero) {
    // String $funcao: nome da funcao (dia, mes, ano, hora, minuto, segundo)
    // Int $numero: numero do parametro
    //
        switch ($funcao) {
        case 'dia':
        case 'mes':
        case 'ano':
        case 'hora':
        case 'minuto':
        case 'segundo':
        case 'diaano':
            $atributo = new atributo($funcao, 'Parametro', 0);
            $atributo->set_tipo('data', false);
            break;
        default:
            return false;
        }
        return $atributo;
    }


/// @ METODOS BASICOS DE CONSULTA BASEADOS NA SQL92


    //
    //     SELECT: Realiza uma consulta ao BD retornando um vetor de objetos
    //
    public function select($objeto, $atributos, $condicoes = null, $ordem = null, $index = null, $limite = null, $inicio = 0) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Array[String] $atributos: vetor de atributos desejados
    // condicao_sql $condicoes: condicoes da consulta
    // Array[String => Bool] $ordem: campos usados para ordenar e especificacao se a ordem e' crescente (true) ou decrescente (false)
    // String $index: campo usado para indexar o vetor ou null para nenhum
    // Int $limite: numero maximo de elementos retornados
    // Int $inicio: obtem elementos a partir do N'esimo elemento consultado
    //
        // Consultar campo de indexacao, caso seja especificado
        if ($index) {
            $atributos[] = $index;
        }

        $iterador = $this->select_iterador($objeto, $atributos, $condicoes, $ordem, $limite, $inicio);
        if (!$iterador) {
            return false;
        }

        // Converter resultado para uma notacao hierarquica de objetos
        $vt_retorno = array();

        // Indexar com um atributo
        if ($index && $objeto->possui_atributo($index)) {
            $php = '$vt_retorno[$obj->'.str_replace(':', '->', $index).'] = $obj;';

        // Indexar numericamente (vetor simples e sequencial)
        } else {
            $php = '$vt_retorno[] = $obj;';
        }
        foreach ($iterador as $obj) {
            eval($php);
        }

        return $vt_retorno;
    }


    //
    //     SELECT: Realiza uma consulta ao BD retornando um iterador de objetos
    //
    public function select_iterador($objeto, $atributos, $condicoes = null, $ordem = null, $limite = null, $inicio = 0) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Array[String] $atributos: vetor de atributos desejados
    // condicao_sql $condicoes: condicoes da consulta
    // Array[String => Bool] $ordem: campos usados para ordenar e especificacao se a ordem e' crescente (true) ou decrescente (false)
    // Int $limite: numero maximo de elementos retornados
    // Int $inicio: obtem elementos a partir do N'esimo elemento consultado
    //
        self::filtrar_condicao($condicoes);

        // Sempre consultar a chave primaria
        $atributos[] = $objeto->get_chave();

        // Obter SQL
        $classe = $objeto->get_classe();
        $sql = $this->sql_select($objeto, $atributos, $condicoes, $ordem, null, $limite, $inicio, $estrutura_consulta);
        if (!$sql) {
            return false;
        }

        // Consultar
        $resultado = $this->consultar($sql);
        if (!$this->resultado_valido($resultado)) {
            return false;
        }

        return new iterador_dao($this, $objeto, $resultado, $estrutura_consulta[0]);
    }


    //
    //     SELECT MAX: Realiza uma consulta ao maior campo de uma tabela
    //
    public function select_maior($objeto, $atributo, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser consultado
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $apelido = 'maior';
        $sql = $this->sql_select_maior($objeto, $atributo, $apelido, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        $resultado = $this->consultar($sql);
        if (!$this->resultado_valido($resultado)) {
            return false;
        }

        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);

        $def_atributo = $objeto->get_definicao_atributo($atributo);
        return $this->filtrar_atributo_bd($def_atributo, $obj->$apelido);
    }


    //
    //     SELECT MIN: Realiza uma consulta ao menor campo de uma tabela
    //
    public function select_menor($objeto, $atributo, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser consultado
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $apelido = 'menor';
        $sql = $this->sql_select_menor($objeto, $atributo, $apelido, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        $resultado = $this->consultar($sql);
        if (!$this->resultado_valido($resultado)) {
            return false;
        }

        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);

        $def_atributo = $objeto->get_definicao_atributo($atributo);
        return $this->filtrar_atributo_bd($def_atributo, $obj->$apelido);
    }


    //
    //     SELECT COUNT: Realiza uma consulta ao numero de registros de uma tabela
    //
    public function select_quantidade($objeto, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $apelido = 'quantidade';
        $sql = $this->sql_select_quantidade($objeto, $apelido, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        $resultado = $this->consultar($sql);
        if (!$this->resultado_valido($resultado)) {
            return false;
        }

        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);
        return (int)$obj->$apelido;
    }


    //
    //     SELECT SUM: Realiza uma consulta a soma de um atributo de registros de uma tabela
    //
    public function select_soma($objeto, $atributo, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser somado
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $apelido = 'soma';
        $sql = $this->sql_select_soma($objeto, $atributo, $apelido, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        $resultado = $this->consultar($sql);
        if (!$this->resultado_valido($resultado)) {
            return false;
        }

        $obj = $this->fetch_object($resultado);
        $this->liberar_resultado($resultado);
        $valor = $obj->$apelido;

        $def_atributo = $objeto->get_definicao_atributo($atributo);
        switch ($def_atributo->tipo) {
        case 'int':
            return (int)$valor;
        case 'float':
            return (float)$valor;
        }
        return false;
    }


    //
    //     INSERT: Insere um registro no BD e retorna o ultimo ID inserido
    //
    public function insert($objeto, $dados) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Object $dados: dados a serem inseridos
    //
        // Obter SQL
        $sql = $this->sql_insert($objeto, $dados);
        if (!$sql) {
            return false;
        }

        // Consultar
        $retorno = false;
        $conectou = false;
        if (!$this->conexao) {
            $conectou = $this->conectar();
        }
        $resultado = $this->consultar($sql);
        if ($this->registros_atingidos($resultado) > 0) {
            $retorno = $this->get_ultimo_insert($objeto);
        }
        if ($conectou) {
            $this->desconectar();
        }
        return $retorno;
    }


    //
    //     UPDATE: Atualiza um registro no BD
    //
    public function update($objeto, $dados, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Array[String => Mixed] $dados: dados a serem atualizados
    // condicao_sql $condicoes: condicoes da atualizacao
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $sql = $this->sql_update($objeto, $dados, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql);
    }


    //
    //     DELETE: Remove registros do BD
    //
    public function delete($objeto, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // condicao_sql $condicoes: vetor com as condicoes de remocao
    //
        self::filtrar_condicao($condicoes);

        // Obter SQL
        $sql = $this->sql_delete($objeto, $condicoes);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql);
    }


    //
    //     TRUNCATE: Remove todos registros de uma tabela do BD
    //
    public function truncate($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        // Obter SQL
        $sql = $this->sql_truncate($objeto);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql) && $this->reiniciar_contagem($objeto->get_tabela(), $objeto->get_chave(), 1);
    }


    //
    //     Prepara para a criacao de tabelas
    //
    public function preparar_criacao_tabelas($vt_objetos) {
    // Array[Objeto] $vt_objetos: vetor de objetos de entidades
    //
        return true;
    }


    //
    //     Encerra a criacao de tabelas
    //
    public function encerrar_criacao_tabelas($vt_objetos) {
    // Array[Objeto] $vt_objetos: vetor de objetos de entidades
    //
        return true;
    }


    //
    //     CREATE TABLE: Cria uma nova tabela no BD
    //
    public function create_table($objeto, $charset = 'UTF-8') {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $charset: codificacao da tabela
    //
        // Obter SQL
        $sql = $this->sql_create_table($objeto, $charset);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql);
    }


    //
    //     DROP TABLE: Remove uma tabela do BD
    //
    public function drop_table($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        // Obter SQL
        $sql = $this->sql_drop_table($objeto);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql);
    }


    //
    //     ALTER TABLE: Altera uma tabela do BD (adiciona ou remove atributos)
    //
    public function alter_table($objeto, $atributo, $operacao) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser adicionado ou removido
    // Int $operacao: operacao desejada (DRIVER_OBJETO_ADICIONAR_ATRIBUTO ou DRIVER_OBJETO_REMOVER_ATRIBUTO)
    //
        // Obter SQL
        $sql = $this->sql_alter_table($objeto, $atributo, $operacao);
        if (!$sql) {
            return false;
        }

        // Consultar
        return (bool)$this->consultar($sql);
    }


/// @ METODOS DE GERACAO DE SQL NO PADRAO SQL92 (DEVEM SER SOBRECARREGADOS CASO NECESSARIO)


    //
    //     SELECT: gera uma SQL de uma consulta SELECT
    //
    public function sql_select($objeto, $atributos, $condicoes, $ordem, $index = null, $limite = null, $inicio = null, &$estrutura_consulta = array()) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Array[String] $atributos: vetor de atributos desejados
    // condicao_sql $condicoes: condicoes da consulta
    // Array[String => Bool] $ordem: campos usados para ordenar e especificacao se a ordem e' crescente (true) ou decrescente (false)
    // String $index: campo usado para indexar o vetor ou null para nenhum
    // Int $limite: numero maximo de elementos retornados
    // Int $inicio: obtem elementos a partir do N'esimo elemento consultado
    // Array[consulta] $estrutura_consulta: estruturas com os dados necessarios para montar o SELECT de maneira portavel
    //
        self::filtrar_condicao($condicoes);
        $estrutura_consulta = array();

        // Se e' uma condicao de agrupamento
        if ($condicoes->tipo == CONDICAO_SQL_AGRUPAMENTO) {

            // Consultar os campos usados para ordenacao
            if (is_array($ordem)) {
                foreach ($ordem as $campo => $tipo_ordem) {
                    $atributos[] = $campo;
                }
            }

            // Obter a estrutura de cada consulta individual
            $operador_agrupamento = $condicoes->operador;
            foreach ($condicoes->vetor as $c) {
                $estrutura_consulta[] = consulta::estruturar($objeto, $atributos, $c, $ordem, $limite, $inicio);
            }

        // Se e' uma condicao simples
        } else {
            $operador_agrupamento = false;
            $estrutura_consulta[] = consulta::estruturar($objeto, $atributos, $condicoes, $ordem, $limite, $inicio);
        }

        $vt_sql = array();
        $tamanho = count($estrutura_consulta);
        foreach ($estrutura_consulta as $dado) {

            // Montar SQL de campos, tabelas, condicoes da consulta, ordem e limite
            $sql_campos       = $this->gerar_sql_campos($dado);
            $sql_tabelas      = $this->gerar_sql_tabelas($objeto, $dado);
            $sql_condicoes    = $this->gerar_sql_condicao($objeto, $dado, $dado->precisa_usar_apelidos_tabelas());
            $sql_ordem        = $this->gerar_sql_ordem($dado);
            $sql_limite       = $this->gerar_sql_limite($dado);
            $sql_ordem_global = $this->gerar_sql_ordem($dado, true);

            // Checar integridade da SQL
            if (empty($sql_campos) || empty($sql_tabelas)) {
                trigger_error('Consulta nao possui nenhum campo ou nenhuma tabela envolvida', E_USER_WARNING);
                $this->adicionar_erro("Nenhum campo ou nenhuma tabela na consulta");
                return false;
            }

            // Montar SQL final
            $sql = 'SELECT '.$sql_campos.                  // SELECT %s
                   ($this->exibicao_usuario ? "\n" : ' ').
                   'FROM '.$sql_tabelas.                   // FROM %s
                   $sql_condicoes.                         // [ WHERE %s ]
                   $sql_ordem;                             // [ ORDER BY %s [ ASC | DESC ] ]
            if ($tamanho == 1) {
                $sql .= $sql_limite;                       // [ LIMIT %d [ OFFSET %d ] ]
            }
            $vt_sql[] = $sql;
        }
        if ($tamanho == 1) {
            return array_pop($vt_sql);
        }

        // Montar a SQL com agrupamento
        return '('.implode(') '.$operador_agrupamento.' (', $vt_sql).')'. // (%s [ %s %s] )
               $sql_ordem_global.                                         // ORDER BY %d [ ASC | DESC ]
               $sql_limite;                                               // [ LIMIT %d [ OFFSET %d ] ]
    }


    //
    //     SELECT MAX: gera uma SQL de uma consulta SELECT MAX
    //
    public function sql_select_maior($objeto, $atributo, $apelido = 'maior', $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser consultado
    // String $apelido: apelido do campo a ser retornado
    // condicao_sql $condicoes: condicoes da consulta
    //
        return $this->sql_funcao($objeto, 'MAX', $atributo, $apelido, $condicoes);
    }


    //
    //     SELECT MIN: gera uma SQL de uma consulta SELECT MIN
    //
    public function sql_select_menor($objeto, $atributo, $apelido = 'menor', $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser consultado
    // String $apelido: apelido do campo a ser retornado
    // condicao_sql $condicoes: condicoes da consulta
    //
        return $this->sql_funcao($objeto, 'MIN', $atributo, $apelido, $condicoes);
    }


    //
    //     SELECT COUNT: gera uma SQL de uma consulta SELECT COUNT
    //
    public function sql_select_quantidade($objeto, $apelido = 'quantidade', $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $apelido: apelido do campo a ser retornado
    // condicao_sql $condicoes: condicoes da consulta
    //
        $atributo = $objeto->get_chave();
        return $this->sql_funcao($objeto, 'COUNT', $atributo, $apelido, $condicoes);
    }


    //
    //     SELECT SUM: gera uma SQL de uma consulta SELECT SUM
    //
    public function sql_select_soma($objeto, $atributo, $apelido = 'soma', $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser somado
    // String $apelido: apelido do campo a ser retornado
    // condicao_sql $condicoes: condicoes da consulta
    //
        return $this->sql_funcao($objeto, 'SUM', $atributo, $apelido, $condicoes);
    }


    //
    //     INSERT: Gera uma SQL de uma consulta INSERT
    //
    public function sql_insert($objeto, $dados) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Object $dados: dados a serem inseridos
    //
        if (!is_array($dados) && !is_object($dados)) {
            trigger_error('Tipo invalido para parametro dados ('.util::get_tipo($dados).')', E_USER_WARNING);
            $this->adicionar_erro('Par&acirc;metros inv&aacute;lidos na montagem da SQL de INSERT');
            return false;
        }

        // Montar dados da SQL
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());
        $vt_sql_campos = array();
        $vt_sql_valores = array();
        $possui_campo = false;
        foreach ($dados as $chave => $valor) {

            // Se o atributo nao existe
            if (!$objeto->possui_atributo($chave)) {
                continue;
            }

            // Se e' uma chave fraca
            if ($objeto->possui_rel_uu($chave, false)) {
                $def_rel = $objeto->get_definicao_rel_uu($chave, false);
                if (!$def_rel->forte) {
                    $vt_sql_campos[] = $this->delimitar_campo($chave);
                    $def = $objeto->get_definicao_atributo($chave);
                    $valor = $this->filtrar_atributo_php($def, $valor ? $valor : null, true);
                    $vt_sql_valores[] = $valor;
                    $possui_campo = true;
                    continue;
                }
            }

            // Se e' um campo ou chave convencional
            $vt_sql_campos[] = $this->delimitar_campo($chave);
            $def = $objeto->get_definicao_atributo($chave);
            $valor = $this->filtrar_atributo_php($def, $valor, true);
            $vt_sql_valores[] = $valor;
            $possui_campo = true;
        }
        if (!$possui_campo) {
            trigger_error('Nenhum atributo real foi definido para insercao', E_USER_WARNING);
            $this->adicionar_erro('Nenhum atributo real foi definido para inser&ccedil;&atilde;o');
            return false;
        }
        $sql_campos  = $this->implode_campos_insert($vt_sql_campos);
        $sql_valores = $this->implode_valores_insert($vt_sql_valores);

        // Montar SQL
        $sql = "INSERT INTO {$sql_tabela} ({$sql_campos})".  // INSERT INTO %s
               ($this->exibicao_usuario ? "\n" : ' ').
               "VALUES ({$sql_valores})";                    // VALUES (%s)

        return $sql;
    }


    //
    //     UPDATE: Gera uma SQL de uma consulta UPDATE
    //
    public function sql_update($objeto, $dados, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // Array[String => Mixed] $dados: dados a serem atualizados
    // condicao_sql $condicoes: condicoes da atualizacao
    //
        self::filtrar_condicao($condicoes);
        if (!is_array($dados) && !is_object($dados)) {
            trigger_error('Tipo invalido para parametro dados ('.util::get_tipo($dados).')', E_USER_WARNING);
            $this->adicionar_erro('Par&acirc;metros inv&aacute;lidos na montagem da SQL de UPDATE');
            return false;
        }

        // Montar dados da SQL
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());
        $vt_sql_atribuicoes = array();
        $possui_campo = false;
        foreach ($dados as $chave => $valor) {

            // Se o atributo nao existe
            if (!$objeto->possui_atributo($chave)) {
                continue;
            }

            // Se e' uma chave fraca
            if ($objeto->possui_rel_uu($chave, false)) {
                $def_rel = $objeto->get_definicao_rel_uu($chave, false);
                if (!$def_rel->forte) {
                    $sql_chave = $this->delimitar_campo($chave);
                    if (strpos($valor, 'sql:') === 0) {
                        $sql_valor = substr($valor, strlen('sql:'));
                    } else {
                        $def = $objeto->get_definicao_atributo($chave);
                        $sql_valor = $this->filtrar_atributo_php($def, $valor ? $valor : null, true);
                    }
                    $vt_sql_atribuicoes[] = $sql_chave.' = '.$sql_valor;
                    $possui_campo = true;
                    continue;
                }
            }

            // Se e' um campo ou chave convencional
            $sql_chave = $this->delimitar_campo($chave);
            if (strpos($valor, 'sql:') === 0) {
                $sql_valor = substr($valor, strlen('sql:'));
            } else {
                $def = $objeto->get_definicao_atributo($chave);
                $sql_valor = $this->filtrar_atributo_php($def, $valor, true);
            }
            $vt_sql_atribuicoes[] = $sql_chave.' = '.$sql_valor;
            $possui_campo = true;

        }
        if (!$possui_campo) {
            trigger_error('Nenhum atributo real foi definido para atualizacao', E_USER_WARNING);
            $this->adicionar_erro('Nenhum atributo real foi definido para atualizacao');
            return false;
        }
        $atributos = array();
        $dados = consulta::estruturar($objeto, $atributos, $condicoes);

        $sql_atribuicoes = $this->implode_campos_update($vt_sql_atribuicoes);
        $sql_condicoes = $this->gerar_sql_condicao($objeto, $dados, false);

        // Montar SQL
        $sql = "UPDATE {$sql_tabela}".                  // UPDATE %s
               ($this->exibicao_usuario ? "\n" : ' ').
               "SET {$sql_atribuicoes}".                // SET %
               $sql_condicoes;                          // [ WHERE %s ]

        return $sql;
    }


    //
    //     DELETE: Gera uma SQL de uma consulta DELETE condicional
    //
    public function sql_delete($objeto, $condicoes) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);
        $atributos = array();
        $dados = consulta::estruturar($objeto, $atributos, $condicoes);

        // Montar dados da SQL
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());
        $sql_condicoes = $this->gerar_sql_condicao($objeto, $dados, false);

        if (!$sql_condicoes) {
            trigger_error('Nenhuma condicao especificada para DELETE (utilize TRUNCATE, caso desejado)', E_USER_WARNING);
            $this->adicionar_erro('Nenhuma condi&ccedil;&atilde;o especificada para DELETE');
            return false;
        }

        // Montar SQL
        $sql = "DELETE FROM {$sql_tabela}". // DELETE FROM %s
               "{$sql_condicoes}";          // WHERE %s

        return $sql;
    }


    //
    //     TRUNCATE: Gera uma SQL de uma consulta DELETE incondicional
    //
    public function sql_truncate($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        // Montar dados da SQL
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Montar SQL
        $sql = "DELETE FROM {$sql_tabela}"; // DELETE FROM %s

        return $sql;
    }


    //
    //     CREATE TABLE: Gera uma SQL de criacao de uma nova tabela no BD
    //
    public function sql_create_table($objeto, $charset = 'UTF-8') {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $charset: codificacao da tabela
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Percorrer cada campo do objeto
        $vt_campos = array();
        $vt_constraint = array();
        foreach ($objeto->get_atributos() as $def_atributo) {
            $sql_campo = $this->delimitar_campo($def_atributo->nome);
            $sql_tipo  = $this->gerar_sql_tipo($def_atributo);

            // Checar se e' unico
            if ($def_atributo->unico) {
                $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE KEY ({$sql_campo})";

            // Checar se e' uma chave candidata
            //} elseif ($def_atributo->chave == 'CK') {
                //$vt_constraint[$def_atributo->nome] = "  INDEX ({$sql_campo})";
            }

            // Se nao e' a chave primaria
            if ($def_atributo->chave != 'PK') {

                // Se e' um relacionamento
                if ($objeto->possui_rel_uu($def_atributo->nome, false)) {
                    $def_rel = $objeto->get_definicao_rel_uu($def_atributo->nome, false);
                    if ($def_rel->forte) {
                        $sql_nulo = ' NOT NULL';
                        $sql_default = '';
                    } else {
                        $sql_nulo = ' NULL';
                        $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                    }

                // Se nao e' um relacionamento
                } else {
                    $sql_nulo = ' NOT NULL';
                    $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                }
                $vt_campos[] = "  {$sql_campo} {$sql_tipo}{$sql_nulo}{$sql_default}";

            // Se e' chave primaria: forcar que seja auto-incrementavel
            } else {
                $sql_identity = ' GENERATED ALWAYS AS IDENTITY';
                $vt_campos[] = "  {$sql_campo} {$sql_tipo} {$sql_identity}";

                $sql_constraint = 'pk_'.md5($objeto->get_tabela().':'.$def_atributo->nome);
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} PRIMARY KEY ({$sql_campo})";
            }
        }

        // Restricoes de relacionamentos externos
        foreach ($objeto->get_definicoes_rel_uu() as $atributo_rel => $def_atributo_rel) {
            $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo_rel);
            $sql_atributo_rel = $this->delimitar_campo($atributo_rel);
            $obj_ref = $objeto->__get($def_atributo_rel->nome);
            $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
            $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());

            // Se e' um relacionamento forte
            if ($def_atributo_rel->forte) {
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                                  "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                                  "      ON DELETE CASCADE\n".
                                                  "      ON UPDATE CASCADE";
            } else {
                $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                                  "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                                  "      ON DELETE SET NULL\n".
                                                  "      ON UPDATE SET NULL";
            }
        }

        // Definir restricoes de chaves unicas compostas
        foreach ($objeto->get_chaves_unicas_compostas() as $chave_uk) {
            $sql_constraint = 'uk_'.md5($objeto->get_tabela().':'.implode(',', $chave_uk));
            $vt_campo_uk = array();
            foreach ($chave_uk as $campo_uk) {
                $vt_campo_uk[] = $this->delimitar_campo($campo_uk);
            }
            $sql_campo_uk = implode(', ', $vt_campo_uk);
            $vt_constraint[$sql_constraint] = "  CONSTRAINT {$sql_constraint} UNIQUE KEY ({$sql_campo_uk})";
        }

        $vt_campos = array_merge($vt_campos, $vt_constraint);

        $sql_campos = $this->implode_campos_create_table($vt_campos);

        // Montar SQL
        $sql = "CREATE TABLE {$sql_tabela} (\n".
               "{$sql_campos}\n".
               ")";
        return $sql;
    }


    //
    //     DROP TABLE: Gera uma SQL de remocao de uma tabela do BD
    //
    public function sql_drop_table($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());

        // Montar SQL
        $sql = "DROP TABLE {$sql_tabela}";
        return $sql;
    }


    //
    //     ALTER TABLE: Gera uma SQL de alteracao de uma tabela do BD
    //
    public function sql_alter_table($objeto, $atributo, $operacao) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $atributo: nome do atributo a ser adicionado ou removido
    // Int $operacao: operacao desejada (DRIVER_OBJETO_ADICIONAR_ATRIBUTO ou DRIVER_OBJETO_REMOVER_ATRIBUTO)
    //
        $sql_tabela = $this->delimitar_tabela($objeto->get_tabela());
        $sql_atributo = $this->delimitar_campo($atributo);

        switch ($operacao) {
        case DRIVER_OBJETO_ADICIONAR_ATRIBUTO:
            $def_atributo = $objeto->get_definicao_atributo($atributo);
            $sql_tipo     = $this->gerar_sql_tipo($def_atributo);

            // Se e' um relacionamento
            if ($objeto->possui_rel_uu($def_atributo->nome, false)) {
                $def_atributo_rel = $objeto->get_definicao_rel_uu($def_atributo->nome, false);
                if ($def_atributo_rel->forte) {
                    $sql_nulo = ' NOT NULL';
                    $sql_default = '';
                } else {
                    $sql_nulo = ' NULL';
                    $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
                }

            // Se e' um campo texto
            } elseif (preg_match('/^(TINY|MEDIUM|LONG)?TEXT$/', $sql_tipo)) {
                $sql_nulo = ' NOT NULL';
                $sql_default = '';

            // Demais campos
            } else {
                $sql_nulo = ' NOT NULL';
                $sql_default = ' DEFAULT '.$this->gerar_sql_default($def_atributo);
            }

            $sql_alter = "ALTER TABLE {$sql_tabela} ADD COLUMN {$sql_atributo} {$sql_tipo}{$sql_nulo}{$sql_default}";

            // Se e' uma chave estrangeira
            if ($objeto->possui_rel_uu($atributo, false)) {
                $def_relacionamento = $objeto->get_definicao_rel_uu($atributo, false);
                $obj_ref = $objeto->get_objeto_rel_uu($def_relacionamento->nome);

                // Se o relacionamento e' forte e nao possui registros na tabela relacionada
                $sql_constraint = 'fk_'.md5($objeto->get_tabela().':'.$atributo);
                $sql_atributo_rel = $this->delimitar_campo($atributo);
                $sql_tabela_ref = $this->delimitar_tabela($obj_ref->get_tabela());
                $sql_atributo_ref = $this->delimitar_campo($obj_ref->get_chave());
                if ($def_relacionamento->forte && !$objeto->possui_registros()) {
                    $sql_constraint = "ALTER TABLE {$sql_tabela}\n".
                                      "  ADD CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                      "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                      "      ON DELETE CASCADE\n".
                                      "      ON UPDATE CASCADE";

                    $sql = array($sql_alter, $sql_constraint);
                } else {
                    $sql_constraint = "ALTER TABLE {$sql_tabela}\n".
                                      "  ADD CONSTRAINT {$sql_constraint} FOREIGN KEY ({$sql_atributo_rel})\n".
                                      "    REFERENCES {$sql_tabela_ref} ({$sql_atributo_ref})\n".
                                      "      ON DELETE SET NULL\n".
                                      "      ON UPDATE SET NULL";

                    $sql = array($sql_alter, $sql_constraint);
                }
            } else {
                $sql = $sql_alter;
            }
            break;
        case DRIVER_OBJETO_REMOVER_ATRIBUTO:
            $sql = "ALTER TABLE {$sql_tabela} DROP COLUMN {$sql_atributo}";
            break;
        default:
            $this->adicionar_erro('Opera&ccedil;&atilde;o inv&aacute;lida');
            trigger_error("Operacao invalida para o metodo: ".util::exibir_var($operacao), E_USER_WARNING);
            return false;
        }
        return $sql;
    }


    //
    //     USE DATABASE: Comando do SGBD para trocar de base de dados em tempo de execucao
    //
    public function sql_use_database($bd = false) {
    // String $bd: nome do bd a ser acessado
    //
        return false;
    }


/// @ METODOS AUXILIARES PARA GERACAO DE SQL


    //
    //     Monta as condicoes na sintaxe do SGBD
    //
    protected function gerar_sql_condicao($objeto, $dados, $usar_apelido = true) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // consulta $dados: dados estrutrados da consulta
    // Bool $usar_apelido: usar apelidos das tabelas
    //
        $sql_condicao = $this->gerar_sql_condicao_recursivo($objeto, $dados->condicao, $usar_apelido);
        if ($sql_condicao === false) {
            return false;
        }
        if ($sql_condicao) {
            return ($this->exibicao_usuario ? "\n" : ' ').
                   'WHERE '.$sql_condicao;
        }
        return '';
    }


    //
    //     Monta as condicoes na sintaxe do SGBD recursivamente
    //
    public function gerar_sql_condicao_recursivo($objeto, $condicao, $usar_apelido = true) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // stdClass $condicao: condicoes a serem transformadas em SQL
    // Bool $usar_apelido: usar apelidos das tabelas
    //
        if (is_null($condicao)) {
            trigger_error('Condicao invalida', E_USER_NOTICE);
            return '';
        }
        simp_autoload('condicao_sql');

        switch ($condicao->tipo) {
        case CONDICAO_SQL_VAZIA:
            return '';
            break;

        case CONDICAO_SQL_SIMPLES:

            // Comparacao com NULL
            if (is_null($condicao->operando2)) {
                switch ($condicao->operador) {
                case '=':
                    return $this->gerar_sql_isnull($objeto, $condicao, $usar_apelido);
                case '<>':
                    return $this->gerar_sql_isnotnull($objeto, $condicao, $usar_apelido);
                default:
                    trigger_error('O operador "'.$condicao->operador.'" nao aceita operando com valor "null"', E_USER_ERROR);
                    return false;
                }
            }

            // Operando 1
            $def1 = false;
            $def_retorno1 = false;
            $tipo1 = condicao_sql::get_flag($condicao, 'tipo1');
            $funcao1 = condicao_sql::get_flag($condicao, 'funcao1');
            if ($tipo1 == CONDICAO_SQL_TIPO_ATRIBUTO) {
                $def1 = $objeto->get_definicao_atributo($condicao->operando1->atributo);
                if ($usar_apelido) {
                    $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->apelido, $condicao->operando1->nome);
                } else {
                    $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->nome, $condicao->operando1->nome);
                }
            } elseif ($tipo1 == CONDICAO_SQL_TIPO_VALOR) {
                trigger_error('O primeiro operando nao pode ser um valor', E_USER_ERROR);
            }
            if ($funcao1) {
                $sql_operando1 = $this->montar_funcao_condicao($funcao1, $sql_operando1, $tipo1, $def1);
                $def_retorno1 = $this->get_definicao_retorno_funcao($funcao1, $def1);
            } else {
                $def_retorno1 = $def1;
            }

            // Operador
            $sql_operador = $this->gerar_sql_operador($condicao->operador);

            // Operando 2
            $tipo2 = condicao_sql::get_flag($condicao, 'tipo2');
            $funcao2 = condicao_sql::get_flag($condicao, 'funcao2');
            if ($tipo2 == CONDICAO_SQL_TIPO_ATRIBUTO) {
                $def2 = $objeto->get_definicao_atributo($condicao->operando1->atributo);
                if ($usar_apelido) {
                    $sql_operando2 = $this->montar_nome_campo($condicao->operando2->tabela->apelido, $condicao->operando2->nome);
                } else {
                    $sql_operando2 = $this->montar_nome_campo($condicao->operando2->tabela->nome, $condicao->operando2->nome);
                }
                if ($funcao2) {
                    $sql_operando2 = $this->montar_funcao_condicao($funcao2, $sql_operando2, $tipo2, $def2);
                }
            } elseif ($tipo2 == CONDICAO_SQL_TIPO_VALOR) {
                if ($funcao2) {
                    $def_parametro2 = $this->get_definicao_parametro_funcao($funcao2, 0);

                    $sql_operando2 = $this->filtrar_atributo_php($def_parametro2, $condicao->operando2, true);
                    $sql_operando2 = $this->montar_funcao_condicao($funcao2, $sql_operando2, $tipo2, $def_parametro2);
                } else {
                    $sql_operando2 = $this->filtrar_atributo_php($def_retorno1, $condicao->operando2, true);
                }
            }

            // Com chave fraca e valor vazio: comparacao com null
            if ($def1 && $def1->chave == 'OFK' && $tipo2 == CONDICAO_SQL_TIPO_VALOR && !$condicao->operando2) {
                switch ($condicao->operador) {
                case '=':
                    return $this->gerar_sql_isnull($objeto, $condicao, $usar_apelido);
                case '<>':
                    return $this->gerar_sql_isnotnull($objeto, $condicao, $usar_apelido);
                }
            }

            // Com uma atributo e um valor
            return $sql_operando1.' '.$sql_operador.' '.$sql_operando2;

        case CONDICAO_SQL_COMPOSTA:
            $sql_operador = $this->gerar_sql_operador($condicao->operador);
            $vt_condicoes = array();
            foreach ($condicao->vetor as $sub_condicao) {
                $condicao = $this->gerar_sql_condicao_recursivo($objeto, $sub_condicao, $usar_apelido);
                if ($condicao === false) {
                    return false;
                } elseif ($condicao) {
                    $vt_condicoes[] = $condicao;
                }
            }
            if (count($vt_condicoes) > 1) {
                $sql = '('.implode(' '.$sql_operador.' ', $vt_condicoes).')';
            } else {
                $condicao = array_pop($vt_condicoes);
                $sql = $condicao;
            }
            return $sql;

        case CONDICAO_SQL_UNITARIA:
            $sql_operador = $this->gerar_sql_operador($condicao->operador);
            $sql_condicao = $this->gerar_sql_condicao_recursivo($objeto, $condicao->condicao, $usar_apelido);
            if ($sql_condicao === false) {
                return false;
            }
            $sql = $sql_operador.' ('.$sql_condicao.')';
            return $sql;
        }
        trigger_error('Tipo de condicao invalido: '.$condicao->tipo, E_USER_ERROR);
        return false;
    }


    //
    //     Monta as condicoes de comparacao com valor nulo
    //
    public function gerar_sql_isnull($objeto, $condicao, $usar_apelido = true) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // stdClass $condicao: condicoes a serem transformadas em SQL
    // Bool $usar_apelido: usar apelidos das tabelas
    //
        if ($usar_apelido) {
            $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->apelido, $condicao->operando1->nome);
        } else {
            $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->nome, $condicao->operando1->nome);
        }
        return $sql_operando1.' IS NULL';
    }


    //
    //     Monta as condicoes de comparacao com valor nao nulo
    //
    public function gerar_sql_isnotnull($objeto, $condicao, $usar_apelido = true) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // stdClass $condicao: condicoes a serem transformadas em SQL
    // Bool $usar_apelido: usar apelidos das tabelas
    //
        if ($usar_apelido) {
            $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->apelido, $condicao->operando1->nome);
        } else {
            $sql_operando1 = $this->montar_nome_campo($condicao->operando1->tabela->nome, $condicao->operando1->nome);
        }
        return $sql_operando1.' IS NOT NULL';
    }


    //
    //     Converte um simbolo de operador no correspondente do SGBD
    //
    public function gerar_sql_operador($operador) {
    // String $operador: codigo do operador a ser convertido
    //
        $operador = strtoupper($operador);
        switch ($operador) {

        // Operadores permitidos
        case '=':      // Igual
        case '<>':     // Diferente
        case '>':      // Maior
        case '>=':     // Maior ou Igual
        case '<':      // Menor
        case '<=':     // Menor ou Igual
        case 'LIKE':   // Parecido
        case 'AND':    // E
        case 'OR':     // OU
        case 'NOT':    // NAO
            return $operador;

        // Apelidos de Operadores
        case '!=':
            return '<>';
        case '==':
            return '=';
        }
        trigger_error('Operador desconhecido ('.util::exibir_var($operador).')', E_USER_WARNING);
        return false;
    }


    //
    //     Gera uma lista de campos em SQL
    //
    protected function gerar_sql_campos($dados) {
    // consulta $dados: dados estrutrados da consulta
    //
        $vt_sql_campos = array();
        if ($dados->precisa_usar_apelidos_tabelas()) {
            foreach ($dados->campos as $campo) {
                if (!$campo->consultar) { continue; }
                $vt_sql_campos[] = $this->montar_nome_campo($campo->tabela->apelido, $campo->nome).
                                   ' AS '.
                                   $this->delimitar_campo($campo->apelido);
            }
        } else {
            foreach ($dados->campos as $campo) {
                if (!$campo->consultar) { continue; }
                $vt_sql_campos[] = $this->delimitar_campo($campo->nome).
                                   ' AS '.
                                   $this->delimitar_campo($campo->apelido);
            }
        }
        return $this->implode_campos_select($vt_sql_campos);
    }


    //
    //      Gera uma lista de tabelas em SQL
    //
    protected function gerar_sql_tabelas($objeto, $dados) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // consulta $dados: dados estrutrados da consulta
    //
        $vt_sql_tabelas = array();
        $incluidos = array();
        foreach ($dados->tabelas as $tabela) {
            $t = $this->gerar_sql_tabela($objeto, $tabela, $dados, $incluidos, false);
            if ($t) {
                $vt_sql_tabelas[] = $t;
            }
        }
        return $this->implode_tabelas($vt_sql_tabelas);
    }


    //
    //     Gera uma tabela em SQL com suas juncoes
    //
    protected function gerar_sql_tabela($objeto, &$tabela, &$dados, &$incluidos, $separar_join = true) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // stdClass $tabela: dados da tabela a ser retornada
    // consulta $dados: dados estruturados da consulta
    // Array[String] $incluidos: vetor de apelidos de tabelas ja' incluidas
    // Bool $separar_join: flag interna de controle de chamadas recursivas
    //
        if (isset($incluidos[$tabela->apelido])) { return ''; }
        $incluidos[$tabela->apelido] = 1;

        if ($dados->precisa_usar_apelidos_tabelas()) {
            $t = $this->delimitar_tabela($tabela->nome).' '.$this->delimitar_tabela($tabela->apelido);
        } else {
            $t = $this->delimitar_tabela($tabela->nome);
        }
        $join = '';

        foreach ($dados->juncoes as $juncao) {
            if (isset($incluidos[$juncao->tabela2->apelido])) { continue; }
            if ($juncao->tabela1->nome == $tabela->nome && $juncao->tabela1->atributo == $tabela->atributo) {
                if ($juncao->tipo_join == 'INNER') {
                    $join .= ' INNER JOIN ';
                } elseif ($juncao->tipo_join == 'LEFT') {
                    $join .= ' LEFT JOIN ';
                }
                list($t2, $join2) = $this->gerar_sql_tabela($objeto, $juncao->tabela2, $dados, $incluidos, true);
                $join .= $t2.
                         ' ON ('.$this->gerar_sql_condicao_recursivo($objeto, $juncao->condicao, 1).')'.
                         $join2;
            }
        }
        if ($separar_join) {
            return array($t, $join);
        }
        return $t.$join;
    }


    //
    //      Gera uma declaracao de agrupamento em SQL
    //
    protected function gerar_sql_agrupamento($objeto) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    //
        $tabela_campo = self::get_tabela_atributo($objeto, $objeto->get_chave());
        if (!$tabela_campo) {
            return false;
        }
        list($sql_tabela, $sql_campo) = $tabela_campo;
        $sql_campo = $this->montar_nome_campo($sql_tabela, $sql_campo);

        return ($this->exibicao_usuario ? "\n" : ' ').
               'GROUP BY '.$sql_campo;
    }


    //
    //      Gera uma lista de campos de ordenacao em SQL
    //
    protected function gerar_sql_ordem($dados, $global = false) {
    // consulta $dados: dados estrutrados da consulta
    // Bool $global: gerar a ordem global (numerico)
    //
        $vt_sql_ordem = array();
        foreach ($dados->ordem as $ordem) {
            if ($global) {
                if ($ordem->campo->consultar) {
                    $o = $dados->get_posicao($ordem->campo->apelido);
                } else {
                    return '';
                }
            } else {
                if ($ordem->campo->consultar) {
                    $o = $this->delimitar_campo($ordem->campo->apelido);
                } elseif ($dados->precisa_usar_apelidos_tabelas()) {
                    $o = $this->montar_nome_campo($ordem->campo->tabela->apelido, $ordem->campo->nome);
                } else {
                    $o = $this->delimitar_campo($ordem->campo->nome);
                }
            }
            $o .= $ordem->tipo_ordem ? '' : ' DESC';
            $vt_sql_ordem[] = $o;
        }
        if (count($vt_sql_ordem)) {
            return ($this->exibicao_usuario ? "\n" : ' ').
                   'ORDER BY '.$this->implode_ordem($vt_sql_ordem);
        }
        return '';
    }


    //
    //      Especifica um limite e um offset de consulta SQL
    //
    protected function gerar_sql_limite($dados) {
    // consulta $dados: dados estrutrados da consulta
    //
        if (!$dados->limite) {
            return '';
        }
        $sql = ($this->exibicao_usuario ? "\n" : ' ').
               'LIMIT '.$dados->limite;
        if ($dados->inicio) {
            $sql .= ($this->exibicao_usuario ? "\n" : ' ').
                    'OFFSET '.$dados->inicio;
        }
        return $sql;
    }


    //
    //     Gera um SELECT que aplica uma funcao ao atributo informado
    //
    protected function sql_funcao($objeto, $funcao, $atributo, $apelido = null, $condicoes = null) {
    // Object $objeto: instancia de uma entidade derivada da classe objeto
    // String $funcao: nome da funcao a ser aplicada
    // String $atributo: nome do atributo a ser utilizado na funcao
    // String $apelido: apelido da saida do resultado da funcao
    // condicao_sql $condicoes: condicoes da consulta
    //
        self::filtrar_condicao($condicoes);

        $atributos = array($atributo);
        $dados = consulta::estruturar($objeto, $atributos, $condicoes);
        if (!$apelido) {
            $apelido = 'valor';
        }

        foreach ($dados->campos as $c) {
            if ($c->atributo == $atributo) {
                $tabela = $c->tabela->apelido;
                $campo = $c->nome;
                $achou = true;
                break;
            }
        }
        if (!$achou) {
            $this->adicionar_erro('O atributo "'.$atributo.'" n&atilde;o foi encontrado na consulta');
            return false;
        }

        // Montar SQL de campos, tabelas e condicoes da consulta
        $dados_campo = new stdClass();
        $dados_campo->tabela  = $tabela;
        $dados_campo->campo   = $campo;
        $dados_campo->apelido = $apelido;

        $sql_funcao_campo = $this->gerar_sql_funcao_campo($funcao, $dados_campo, $dados->precisa_usar_apelidos_tabelas());
        $sql_tabelas      = $this->gerar_sql_tabelas($objeto, $dados);
        $sql_condicoes    = $this->gerar_sql_condicao($objeto, $dados, $dados->precisa_usar_apelidos_tabelas());

        // Montar SQL final
        $sql = 'SELECT '.$sql_funcao_campo.' '. // SELECT %s(%s) %s
               'FROM '.$sql_tabelas.            // FROM %s
               $sql_condicoes;                  // [ WHERE %s ]

        return $sql;
    }


    //
    //     Gera uma consulta ao maior campo na notacao SQL
    //
    protected function gerar_sql_funcao_campo($funcao, $dados, $usar_apelido_tabela = true) {
    // String $funcao: nome da funcao
    // String $dados: dados do atributo (tabela,campo,apelido)
    // Bool $usar_apelido_tabela: indica se deve ou nao usar o apelido da tabela
    //
        $funcao = $this->delimitar_funcao($funcao);
        if ($usar_apelido_tabela) {
            $funcao .= '('.$this->montar_nome_campo($dados->tabela, $dados->campo).')';
        } else {
            $funcao .= '('.$this->delimitar_campo($dados->campo).')';
        }
        $funcao .= ' AS '.$this->delimitar_campo($dados->apelido);
        return $funcao;
    }


    //
    //     Gera o tipo adequado ao atributo
    //
    protected function gerar_sql_tipo($atributo) {
    // atributo $atributo: objeto com as caracteristicas do atributo de uma entidade
    //
        // Tipos: CHARACTER, CHARACTER VARYING, BIT VARYING, INTEGER, SMALLINT, FLOAT, DOUBLE PRECISION, DATE, TIME, TIMESTAMP
        switch ($atributo->tipo) {
        case 'int':
            if (!$atributo->maximo || $atributo->maximo > 255) {
                return 'INTEGER';
            } else {
                return 'SMALLINT';
            }
        case 'float':
            $max = max(abs($atributo->maximo), abs($atributo->minimo));
            if (!$atributo->maximo || $max > (1.175494351 * pow(10, 38))) {
                return 'DOUBLE PRECISION';
            } else {
                return 'FLOAT';
            }
        case 'string':
            if (is_numeric($atributo->maximo) && is_numeric($atributo->minimo) && $atributo->maximo == $atributo->minimo) {
                return "CHARACTER ({$atributo->maximo})";
            } else {
                return "CHARACTER VARYING ({$atributo->maximo})";
            }
        case 'char':
        case 'bool':
            return 'CHARACTER (1)';
        case 'binario':
            if (is_numeric($atributo->maximo) && is_numeric($atributo->minimo) && $atributo->maximo == $atributo->minimo) {
                return "CHARACTER ({$atributo->maximo})";
            } else {
                return "CHARACTER VARYING ({$atributo->maximo})";
            }
        case 'data':
            switch ($atributo->campo_formulario) {
            case 'data':
                return 'DATE';
            case 'hora':
                return 'TIME';
            case 'data_hora':
            default:
                return 'TIMESTAMP';
            }
        }
        trigger_error('Tipo desconhecido ('.util::exibir_var($atributo->tipo).')', E_USER_WARNING);
        return false;
    }


    //
    //     Gera o valor default adequado ao atributo
    //
    protected function gerar_sql_default($atributo) {
    // atributo $atributo: objeto com as caracteristicas do atributo de uma entidade
    //
        if (is_null($atributo->padrao)) {
            switch ($atributo->tipo) {
            case 'bool':
                $atributo->padrao = false;
                break;
            case 'int':
                if ($atributo->chave == 'OFK') {
                    return 'NULL';
                }
                //nobreak
            case 'float':
                $atributo->padrao = 0;
                break;
            case 'char':
                $atributo->padrao = '-';
                break;
            case 'string':
            case 'binario':
                $atributo->padrao = '';
                break;
            case 'data':
                switch ($atributo->campo_formulario) {
                case 'data':
                    $atributo->padrao = '00-00-0000';
                    break;
                case 'hora':
                    $atributo->padrao = '00-00-00';
                    break;
                case 'data_hora':
                default:
                    $atributo->padrao = '00-00-0000-00-00-00';
                    break;
                }
                break;
            }
        }
        $valor = $this->filtrar_atributo_php($atributo, $atributo->padrao, true);
        switch ($atributo->tipo) {
        case 'bool':
        case 'int':
        case 'float':
        case 'char':
        case 'data':
            return $valor;
        case 'string':
        case 'binario':
            if (is_numeric($atributo->maximo) &&
                is_numeric($atributo->minimo) &&
                $atributo->maximo == $atributo->minimo) {

                $padrao = (string)$atributo->padrao;
                if (strlen($padrao) == $atributo->maximo) {
                    $retorno = $padrao;
                } else {
                    $retorno = str_repeat('-', $atributo->maximo);
                }
            } else {
                $retorno = '';
            }
            return $this->delimitar_valor($retorno);
        }
        return '';
    }


    //
    //     Formata uma data para ser inserida no BD
    //
    public function formatar_data($data, $tipo = 'data_hora') {
    // String $data: data no formato dd-mm-aaaa-HH-MM-SS
    // String $tipo: 'data_hora', 'data' ou 'hora'
    //
        $vt_data = explode('-', $data);
        switch ($tipo) {
        case 'data':
            return sprintf('%04d-%02d-%02d',
                           isset($vt_data[2]) ? $vt_data[2] : 0,
                           isset($vt_data[1]) ? $vt_data[1] : 0,
                           isset($vt_data[0]) ? $vt_data[0] : 0
                          );

        case 'hora':
            return sprintf('%02d:%02d:%02d',
                           isset($vt_data[3]) ? $vt_data[3] : 0,
                           isset($vt_data[4]) ? $vt_data[4] : 0,
                           isset($vt_data[5]) ? $vt_data[5] : 0
                          );

        case 'data_hora':
        default:
            return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
                           isset($vt_data[2]) ? $vt_data[2] : 0,
                           isset($vt_data[1]) ? $vt_data[1] : 0,
                           isset($vt_data[0]) ? $vt_data[0] : 0,
                           isset($vt_data[3]) ? $vt_data[3] : 0,
                           isset($vt_data[4]) ? $vt_data[4] : 0,
                           isset($vt_data[5]) ? $vt_data[5] : 0
                          );
        }
    }


    //
    //     Desformata uma data obtida do BD
    //
    public function desformatar_data($data_bd, $tipo = 'data_hora') {
    // String $data_bd: data no formato do BD
    // String $tipo: 'data_hora', 'data' ou 'hora'
    //
        switch ($tipo) {
        case 'data':
            sscanf($data_bd, '%d-%d-%d',
                             $ano, $mes, $dia);
            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           $dia, $mes, $ano,
                           0, 0, 0);

        case 'hora':
            sscanf($data_bd, '%d:%d:%d',
                             $hora, $minuto, $segundo);
            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           0, 0, 0,
                           $hora, $minuto, $segundo);

        case 'data_hora':
        default:
            sscanf($data_bd, '%d-%d-%d %d:%d:%d',
                             $ano, $mes, $dia,
                             $hora, $minuto, $segundo);
            return sprintf('%02d-%02d-%04d-%02d-%02d-%02d',
                           $dia, $mes, $ano,
                           $hora, $minuto, $segundo);
        }
    }


    //
    //     Converte um objeto consultado para um objeto real (stdClass)
    //
    public function gerar_objeto($objeto, $obj, $dados) {
    // Object $objeto: objeto derivado da classe objeto
    // Object $obj: objeto advindo de uma consulta
    // consulta $dados: dados estrutrados da consulta
    //
        $novo = new stdClass();
        foreach ($dados->identicos as $campo => $vetor) {
            if (property_exists($obj, $campo)) {
                $dados_campo = $dados->campos[$campo];
                foreach (array_keys($vetor) as $campo2) {
                    $dados_campo2 = $dados->campos[$campo2];
                    if ($objeto->possui_rel_uu($dados_campo2->atributo, false)) {
                        $def = $objeto->get_definicao_rel_uu($dados_campo2->atributo, false);
                        if ($def->forte || $obj->$campo) {
                            $obj->$campo2 = $obj->$campo;
                        }
                    } elseif ($objeto->possui_rel_uu($dados_campo->atributo, false)) {
                        $def = $objeto->get_definicao_rel_uu($dados_campo->atributo, false);
                        if ($def->forte || $obj->$campo) {
                            $obj->$campo2 = $obj->$campo;
                        }
                    } else {
                        $obj->$campo2 = $obj->$campo;
                    }
                }
            }
        }
        foreach ($dados->campos as $campo) {
            if (property_exists($obj, $campo->apelido)) {
                $def = $objeto->get_definicao_atributo($campo->atributo);
                $valor = $obj->{$campo->apelido};
                $valor = $this->filtrar_atributo_bd($def, $valor);
                $this->set_atributo_objeto($novo, $campo->atributo, $valor);
            }
        }
        return $novo;
    }


    //
    //     Define o valor de um atributo do objeto simples
    //
    private function set_atributo_objeto(&$obj, $atributo, $valor) {
    // Object $obj: objeto que recebera' o atributo
    // String $atributo: atributo na notacao subobjeto:atributo
    // Mixed $valor: valor a ser atribuido
    //
        $pos = strpos($atributo, ':');
        if ($pos === false) {
            $obj->$atributo = $valor;
        } else {
            $sub   = substr($atributo, 0, $pos);
            $resto = substr($atributo, $pos + 1);
            if (!property_exists($obj, $sub)) {
                $obj->$sub = new stdClass();
            }
            $this->set_atributo_objeto($obj->$sub, $resto, $valor);
        }
    }


    //
    //     Une um conjunto de campos para insert
    //
    public function implode_campos_insert($campos) {
    // Array[String] $campos: campos a serem unidos
    //
        return implode(', ', $campos);
    }


    //
    //     Une um conjunto de valores para insert
    //
    public function implode_valores_insert($valores) {
    // Array[String] $valores: valores a serem unidos
    //
        return implode(', ', $valores);
    }


    //
    //     Une um conjunto de campos para update
    //
    public function implode_campos_update($campos) {
    // Array[String] $campos: campos a serem unidos
    //
        return implode(', ', $campos);
    }


    //
    //     Une um conjunto de campos para criacao da tabela
    //
    public function implode_campos_create_table($campos) {
    // Array[String] $campos: campos a serem unidos
    //
        return implode(",\n", $campos);
    }


    //
    //     Une um conjunto de campos para select
    //
    public function implode_campos_select($campos) {
    // Array[String] $campos: campos a serem unidos
    //
        return implode(', ', $campos);
    }


    //
    //     Une um conjunto de tabelas
    //
    public function implode_tabelas($tabelas) {
    // Array[String] $tabelas: tabelas a serem unidas
    //
        return implode(', ', $tabelas);
    }


    //
    //     Une um conjunto de campos de ordenacao
    //
    public function implode_ordem($campos) {
    // Array[String] $campos: campos a serem unidos
    //
        return implode(', ', $campos);
    }

}//class
