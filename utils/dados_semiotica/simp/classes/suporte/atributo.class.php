<?php
//
// SIMP
// Descricao: Classe Atributo (define os atributos das classes)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.2.12
// Data: 06/08/2007
// Modificado: 02/09/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('ATRIBUTO_DATA_RELATIVA', 1);
define('ATRIBUTO_DATA_ABSOLUTA', 2);

final class atributo {
    private $valores;

    //
    //     Retorna os valores padrao para cada caracteristica de um atributo
    //
    public function valor_padrao($caracteristica) {
    // String $caracteristica: nome da caracteristica do atributo
    //
        switch ($caracteristica) {

        // Caracteristicas gerais
        case 'nome': return '';                      // Nome do atributo no BD
        case 'descricao': return '';                 // Descricao do atributo (nome apresentado ao usuario)
        case 'label': return false;                  // Nome do label nos formularios (string ou array indexado pelo ID do formulario)
        case 'ajuda': return false;                  // Ajuda do atributo em formularios
        case 'exemplo': return false;                // Exemplo(s) de valor que pode(m) ser assumido(s) pelo campo
        case 'tipo': return 'string';                // Tipo (int, float, string, char, bool, binario ou data)
        case 'chave': return false;                  // Indica o tipo de Chave (PK, FK, OFK, CK ou false)
        case 'minimo': return false;                 // Valor minimo do campo ou false caso nao exista
        case 'maximo': return false;                 // Valor maximo do campo ou false caso nao exista
        case 'casas_decimais': return false;         // Numero de casas decimais em numeros reais ou false para indefinido
        case 'fixo': return false;                   // Numero fixo ou variavel de casas decimais
        case 'moeda': return false;                  // Campo do tipo moeda
        case 'padrao': return null;                  // Valor padrao
        case 'pode_vazio': return true;              // Pode ou nao deixar vazio no formulario
        case 'validacao': return false;              // Tipo de validacao utilizada (consultar classe validacao)
        case 'classe': return '';                    // Nome da classe original que possui o atributo
        case 'validacao_especifica': return false;   // Nome do metodo que faz uma validacao especifica (que recebe o valor a ser validado por parametro)
        case 'filtro': return false;                 // Tipo de filtro utilizado
        case 'unico': return false;                  // O campo e' unico no BD
        case 'campo_formulario': return false;       // Tipo de campo de formulario usado por padrao
        case 'usar_valor_padrao': return true;       // Usar o valor padrao para preencher o campo no formulario

        // Caracteristicas dos campos de data
        case 'tipo_data_inicio': return ATRIBUTO_DATA_RELATIVA; // Tipo de data de inicio (relativa ou absoluta) das caracteristicas data_inicio e data_fim
        case 'data_inicio': return 10;                          // Ano inicial nos campos de data
        case 'tipo_data_fim': return ATRIBUTO_DATA_RELATIVA;    // Tipo de data de fim (relativa ou absoluta) das caracteristicas data_inicio e data_fim
        case 'data_fim': return 10;                             // Ano final nos campos de data
        }
        return null;
    }


    //
    //     Retorna um XML com as caracteristicas do atributo
    //
    public function get_definicao_xml() {
        $caracteristicas = array('nome',
                                 'descricao',
                                 'label',
                                 'ajuda',
                                 'exemplo',
                                 'tipo',
                                 'chave',
                                 'minimo',
                                 'maximo',
                                 'casas_decimais',
                                 'fixo',
                                 'moeda',
                                 'padrao',
                                 'pode_vazio',
                                 'validacao',
                                 'classe',
                                 'validacao_especifica',
                                 'filtro',
                                 'unico',
                                 'campo_formulario',
                                 'usar_valor_padrao',
                                 'tipo_data_inicio',
                                 'data_inicio',
                                 'tipo_data_fim',
                                 'data_fim');
        $vt_caracteristicas = array();
        foreach ($caracteristicas as $c) {
            $valor = $this->__get($c);
            $tipo  = util::get_tipo($valor);
            $valor = util::exibir_var($valor, UTIL_EXIBIR_TEXTO);
            $vt_caracteristicas[] = '<'.$c.' tipo="'.$tipo.'"><![CDATA['.$valor.']]></'.$c.'>';
        }
        return '<atributo>'.implode('', $vt_caracteristicas).'</atributo>';
    }


    //
    //     Retorna o tipo de mascara adequado para o atributo
    //
    public function get_mascara() {
        if ($this->__get('moeda')) {
            return 'moeda';
        }
        switch ($this->__get('tipo')) {
        case 'string':
            switch ($this->__get('validacao')) {
            case 'CPF':
            case 'CNPJ':
            case 'NUMERICO':
                $mascara = 'digitos';
                break;
            case 'LETRAS':
                $mascara = 'letras';
                break;
            default:
                $mascara = '';
                break;
            }
            return $mascara;
        case 'int':
        case 'float':
            $mascara = $this->__get('tipo');
            if ($this->__get('minimo') >= 0 && $this->__get('maximo') >= 0) {
                $mascara = 'u'.$mascara;
            }
            return $mascara;
        }
        return '';
    }


    //
    //     Obtem o valor de uma caracteristica do atributo
    //
    public function __get($caracteristica) {
    // String $caracteristica: nome da caracteristica do atributo a ser obtido
    //
        if (isset($this->valores[$caracteristica])) {
            return $this->valores[$caracteristica];
        }
        return $this->valor_padrao($caracteristica);
    }


    //
    //     Define o valor de uma caracteristica do atributo
    //
    public function __set($caracteristica, $valor) {
    // String $caracteristica: nome da caracteristica do atributo
    // Mixed $valor: valor a ser definido
    //
        // Caracteristicas que precisam de validacao
        switch ($caracteristica) {

        // Campos enum
        case 'tipo':
            $tipos = array('int', 'float', 'string', 'char', 'bool', 'binario', 'data');
            if (!in_array($valor, $tipos)) {
                trigger_error('Tipo de atributo desconhecido: '.$valor, E_USER_WARNING);
                return false;
            }
            break;

        case 'chave':
            $tipos = array('PK', 'FK', 'OFK', 'CK', false);
            if (is_string($valor)) {
                $valor = strtoupper($valor);
            }
            if (!in_array($valor, $tipos)) { return false; }
            break;

        case 'campo_formulario':
            $tipos = array('text', 'textarea', 'select', 'bool', 'relacionamento',
                           'radio', 'hidden', 'password', 'file', 'submit',
                           'data', 'hora', 'data_hora', false);
            if (!in_array($valor, $tipos)) {
                trigger_error('Tipo de campo de formulario desconhecido: '.$valor, E_USER_WARNING);
                return false;
            }
            break;

        case 'validacao':
            if (is_string($valor)) {
                $tipos = validacao::get_tipos();
                if (!in_array($valor, $tipos)) {
                    trigger_error('Tipo de validacao desconhecido: '.$valor, E_USER_WARNING);
                    return false;
                }
            }
            break;

        case 'tipo_data_inicio':
        case 'tipo_data_fim':
            $tipos = array(ATRIBUTO_DATA_RELATIVA, ATRIBUTO_DATA_ABSOLUTA);
            if (!in_array($valor, $tipos)) {
                trigger_error('Tipo de data desconhecido: '.$valor, E_USER_WARNING);
                return false;
            }
            break;

        // Campos numericos ou false
        case 'minimo':
        case 'maximo':
            if ($this->__get('tipo') == 'data') {
                $valor = (string)$valor;
            } else {
                if (!is_numeric($valor)) {
                    $valor = false;
                }
            }
            break;

        // Campos inteiros
        case 'casas_decimais':
        case 'data_inicio':
        case 'data_fim':
            $valor = (int)$valor;
            break;

        // Campos booleanos
        case 'moeda':
        case 'fixo':
        case 'unico':
        case 'pode_vazio':
        case 'usar_valor_padrao':
            $valor = (bool)$valor;
            break;

        // Campos de texto
        case 'nome':
        case 'descricao':
        case 'classe':
        case 'exemplo':
            $valor = (string)$valor;
            break;

        // Campos especiais
        case 'filtro':
        case 'validacao_especifica':
            if ($valor !== false) {
                $valor = (string)$valor;
            }
            break;
        case 'ajuda':
            if (!is_string($valor) && !is_array($valor)) {
                trigger_error('A ajuda do atributo so pode ser string ou array', E_USER_WARNING);
                $valor = false;
            }
            break;
        case 'label':
            if (!is_string($valor) && !is_array($valor)) {
                trigger_error('O label do atributo so pode ser string ou array', E_USER_WARNING);
                $valor = false;
            }
            break;

        // Valores sem tipo definido
        case 'padrao':
            break;

        default:
            trigger_error('Caracteristica do atributo desconhecida: '.$caracteristica, E_USER_WARNING);
            return false;
        }

        // Guardar valor apenas se ele for diferente do padrao
        // (Leve economia de espaco em memoria)
        if ($valor === $this->valor_padrao($caracteristica)) {
            unset($this->valores[$caracteristica]);
        } else {
            $this->valores[$caracteristica] = $valor;
        }
    }


    //
    //     Construtor
    //
    public function __construct($nome, $descricao, $padrao = null) {
    // String $nome: nome do campo
    // String $descricao: descricao do campo
    // Mixed $padrao: valor padrao
    //
        $this->valores = array();

        // Definir caracteristicas basicas
        $this->__set('nome', $nome);
        $this->__set('descricao', $descricao);
        if (!is_null($padrao)) {
            $this->__set('padrao', $padrao);
        }
    }


    //
    //    Define as caracteristicas basicas do atributo
    //
    public function set_tipo($tipo, $pode_vazio = null, $chave = null) {
    // String $tipo: string, int, bool, float, char, binario ou data
    // Bool $pode_vazio: indica se o campo deve ser preenchido ou nao
    // String $chave: PK (chave primaria), FK (chave estrangeira forte), OFK (chave estrangeira fraca), CK (chave candidata) ou false (nao e' chave)
    //
        $this->__set('tipo', $tipo);
        if (!is_null($pode_vazio)) {
            $this->__set('pode_vazio', $pode_vazio);
        }
        if (!is_null($chave)) {
            $this->__set('chave', $chave);
        }
    }


    //
    //    Define o nome do label do atributo em um formulario ou varios formularios
    //
    public function set_label($label) {
    // String || Array[String => String] $label: label usado em todos formularios ou array associativo indicando o ID do formulario apontando para o label a ser utilizado
    //
        $this->__set('label', $label);
    }


    //
    //    Obtem o label a ser utilizado no formulario
    //
    public function get_label($id_form = false) {
    // String $id_form: ID do formulario ou false para qualquer um
    //
        $label = $this->__get('label');
        if ($id_form === false) {
            if (is_array($label)) {
                return array_shift($label);
            } elseif (is_string($label)) {
                return $label;
            }
            return $this->__get('descricao');
        }
        if ($label) {
            if (is_array($label)) {
                return isset($label[$id_form]) ? $label[$id_form] : $this->__get('descricao');
            }
            return $label;
        }
        return $this->__get('descricao');
    }


    //
    //     Define um intervalo minimo e maximo ao atributo
    //
    public function set_intervalo($minimo = null, $maximo = null) {
    // Int || Float || String $minimo: valor minimo para campos numericos, strings ou datas
    // Int || Float || String $maximo: valor maximo para campos numericos, strings ou datas
    //
        if (!is_null($minimo)) {
            $this->__set('minimo', $minimo);
            if ($this->__get('tipo') == 'data') {
                $data = objeto::parse_data($this->__get('minimo'));
                $this->set_data_inicio(ATRIBUTO_DATA_ABSOLUTA, $data['ano']);
            }
        }
        if (!is_null($maximo)) {
            $this->__set('maximo', $maximo);
            if ($this->__get('tipo') == 'data') {
                $data = objeto::parse_data($this->__get('maximo'));
                $this->set_data_fim(ATRIBUTO_DATA_ABSOLUTA, $data['ano']);
            }
        }
    }


    //
    //     Define uma ajuda a ser exibida nos formularios
    //
    public function set_ajuda($ajuda = false, $exemplo = false) {
    // String || Array[String => String] $ajuda: mensagem de ajuda ou vetor com as possicoes "link" e (opcionalmente) "texto"
    // String $exemplo: exemplo de preenchimento do atributo
    //
        if ($ajuda !== false) {
            $this->__set('ajuda', $ajuda);
        }
        if ($exemplo !== false) {
            $this->__set('exemplo', $exemplo);
        }
    }


    //
    //     Define a forma de validacao do atributo
    //
    public function set_validacao($validacao = null, $validacao_especifica = null, $unico = null) {
    // String $validacao: tipo de validacao (consulte o metodo validar_campo da classe suporte/validacao.class.php)
    // String $validacao_especifica: nome do metodo que faz a validacao especifica (o metodo deve receber o valor a ser validado por parametro)
    // Bool $unico: indica se o campo pode se repetir ou nao no BD
    //
        if (!is_null($validacao)) {
            $this->__set('validacao', $validacao);
        }
        if (!is_null($validacao_especifica)) {
            $this->__set('validacao_especifica', $validacao_especifica);
        }
        if (!is_null($unico)) {
            $this->__set('unico', $unico);
        }
    }


    //
    //     Define um filtro para ser usado antes de se jogar os dados no BD
    //
    public function set_filtro($filtro) {
    // String $filtro: nome do metodo da classe usado como filtro para atribuir valor ao atributo
    //
        if ($filtro) {
            $this->__set('filtro', $filtro);
        }
    }


    //
    //     Define a classe do atributo
    //
    public function set_classe($classe) {
    // String $classe: nome da classe
    //
        $this->__set('classe', $classe);
    }


    //
    //     Define o numero de casas decimais de campos float
    //
    public function set_casas_decimais($casas_decimais, $fixo = null) {
    // Int $casas_decimais: define o numero de casas decimais de campos float
    // Bool $fixo: numero fixo ou variavel de casas decimais
    //
        $this->__set('casas_decimais', abs($casas_decimais));
        if (!is_null($fixo)) {
            $this->__set('fixo', $fixo);
        }
    }


    //
    //     Define se o campo e' do tipo moeda
    //
    public function set_moeda($moeda) {
    // Bool $moeda: indica se o campo e' do tipo moeda ou nao
    //
        $this->__set('moeda', $moeda);
    }


    //
    //    Define o tipo de campo a ser usado no formulario
    //
    public function set_campo_formulario($tipo, $usar_valor_padrao = null) {
    // String $tipo: tipo de campo 'text', 'textarea', 'select', 'bool', 'relacionamento', 'radio', 'hidden', 'password', 'file', 'submit', 'data', 'hora', 'data_hora' ou false;
    // Bool $usar_valor_padrao: usar o valor padrao para preencher o campo
    //
        $this->__set('campo_formulario', $tipo);
        if (!is_null($usar_valor_padrao)) {
            $this->__set('usar_valor_padrao', $usar_valor_padrao);
        }
    }


    //
    //    Define o ano de inicio dos campos de data
    //
    public function set_data_inicio($tipo_data, $inicio) {
    // Int $tipo_data: tipo de caracteristicas das datas (ATRIBUTO_DATA_RELATIVA ou ATRIBUTO_DATA_ABSOLUTA)
    // Int $inicio: valor de data_inicio (numero de anos no passado em data relativa / ano de inicio em data absoluta)
    //
        $this->__set('tipo_data_inicio', $tipo_data);
        $this->__set('data_inicio', $inicio);
    }


    //
    //    Define o ano de fim dos campos de data
    //
    public function set_data_fim($tipo_data, $fim) {
    // Int $tipo_data: tipo de caracteristicas da data (ATRIBUTO_DATA_RELATIVA ou ATRIBUTO_DATA_ABSOLUTA)
    // Int $fim: valor de data_fim (numero de anos no futuro em data relativa / ano de termino em data absoluta)
    //
        $this->__set('tipo_data_fim', $tipo_data);
        $this->__set('data_fim', $fim);
    }


    //
    //     Indica se um determinado valor e' considerado nulo para o tipo do atributo
    //
    public function is_null($valor) {
    // Mixed $valor: valor a ser testado
    //
        switch ($this->__get('tipo')) {
        case 'data':
            $data = objeto::parse_data($valor, false);
            return is_null($valor) || $data['ano'] == 0;
        default:
            return is_null($valor);
        }
    }

}//class
