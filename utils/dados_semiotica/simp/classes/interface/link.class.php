<?php
//
// SIMP
// Descricao: Classe link
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.0.2
// Data: 22/08/2007
// Modificado: 26/11/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Dependencias
global $CFG; // $CFG->ajax

final class link {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Retorna um link para um script de algum modulo (recomendado quando ja' se tem o nome do arquivo e modulo)
    //
    static public function arquivo_modulo(&$usuario, $arquivo, $modulo = '', $descricao = false, $id = '', $class = '', $return = false, $carregar = true, $foco = true, $ajax = true) {
    // usuario $usuario: usuario para quem e' apresentado o link
    // String $arquivo: nome do arquivo
    // String $modulo: nome do modulo
    // String $descricao: nome do link (false para obter nome padrao)
    // String $id: id do link
    // String $class: classe do link
    // Bool $return: retornar ou imprimir o link
    // Bool $carregar: exibe o carregando ou nao
    // Bool $foco: define o foco no primeiro campo da proxima pagina
    // Bool $ajax: utiliza AJAX para abrir o link
    //
        $link = false;

        // Retirar parametros do arquivo
        $pos = strpos($arquivo, '?');
        if ($pos !== false) {
            $parametros = substr($arquivo, $pos + 1);
            $arquivo = substr($arquivo, 0, $pos);
        } else {
            $parametros = '';
        }

        // Consultar dados do arquivo
        $a = new arquivo();
        $campos = array('descricao');

        // Se nao existe o arquivo indicado ou o usuario nao tem acesso
        $dados_script = $usuario->get_arquivo($modulo, $arquivo);

        // Checar se o usuario tem permissao para acessa-lo
        if ($dados_script) {
            $l = $dados_script->link;
            if ($parametros) {
                $l .= '?'.$parametros;
            }
            $d = ($descricao === false) ? $dados_script->descricao : $descricao;
            $link = self::texto($l, $d, $d, $id, $class, 1, $carregar, $foco, $ajax);
        } else {
            $dados_script = objeto::get_objeto('arquivo')->consultar_arquivo_modulo($arquivo, $modulo, array('descricao'));
            if ($dados_script->existe()) {
                $d = ($descricao === false) ? $dados_script->descricao : $descricao;
                $link = "<span class=\"inativo\" title=\"Sem permiss&otilde;es para acessar esta op&ccedil;&atilde;o\">".texto::codificar($d)."</span>\n";
            }
        }

        // Exibir o link
        if ($return) {
            return $link;
        }
        echo $link;
    }


    //
    //     Retorna um link texto simples
    //
    static public function texto($link, $nome, $descricao = '', $id = '', $class = '', $return = false, $carregar = true, $foco = true, $ajax = true) {
    // String $link: link de destino
    // String $nome: texto do link
    // String $descricao: descricao do link
    // String $id: id do link
    // String $class: classe do link
    // Bool $return: retornar ou imprimir o link
    // Bool || String $carregar: exibe o carregando ou nao OU elemento onde o carregando deve ser exibido
    // Bool $foco: define o foco no primeiro campo disponivel na proxima pagina
    // Bool $ajax: utiliza AJAX para abrir o link
    //
        global $CFG;

        $carregar = $carregar ? str_replace('"', "'", $carregar) : 0;
        $foco = $foco ? 1 : 0;
        $onclick  = ($CFG->ajax && $ajax) ? " onclick=\"return carregar(this, {$carregar}, {$foco});\"" : '';
        if ($descricao === false) {
            $descricao = '';
        } else {
            if ($descricao) {
                $descricao = texto::codificar(strip_tags($descricao));
            } else {
                $descricao = texto::codificar(strip_tags($nome));
            }
            $descricao = " title=\"{$descricao}\"";
        }
        $id    = $id ? " id=\"{$id}\"" : '';
        $class = $class ? " class=\"{$class}\"" : '';
        $a = "<a href=\"{$link}\"{$id}{$class}{$descricao}{$onclick}>{$nome}</a>";
        if ($return) {
            return $a;
        }
        echo $a;
    }


    //
    //     Imprime um icone com um link caso o usuario passado como parametro tenha permissao de acesso ao arquivo do modulo
    //     Caso o usuario nao tenha permissao, nada e' apresentado
    //
    static public function icone_modulo(&$usuario, $modulo, $arquivo, $icone, $descricao = '', $texto = '', $exibir_texto = false, $carregar = true, $foco = true) {
    // usuario $usuario: usuario para quem e' apresentado o link
    // String $modulo: nome do modulo
    // String $arquivo: nome do arquivo
    // String $icone: endereco do icone
    // String $descricao: descricao do icone
    // String $texto: texto explicativo opcional
    // Bool $exibir_texto: exibir o texto apos a imagem ou nao
    // Bool $carregar: exibe o "carregando" com Ajax ou nao
    // Bool $foco: define o foco no primeiro campo disponivel na proxima pagina
    //
        global $CFG;

        // Retirar parametros do arquivo
        $pos = strpos($arquivo, '?');
        if ($pos !== false) {
            $parametros = substr($arquivo, $pos + 1);
            $arquivo = substr($arquivo, 0, $pos);
        } else {
            $parametros = '';
        }

        // Consultar dados do arquivo
        $dados_script = $usuario->get_arquivo($modulo, $arquivo);

        // Checar se o usuario tem permissao para acessa-lo
        if ($dados_script) {
            $l = $dados_script->link;
            if ($parametros) {
                $l .= '?'.$parametros;
            }
            return self::icone($l, $icone, $descricao, $texto, $exibir_texto, $carregar, $foco);
        }
        return '';
    }


    //
    //     Imprime um link com um icone
    //
    static public function icone($link, $icone, $descricao = '', $texto = '', $exibir_texto = false, $carregar = true, $foco = true) {
    // String $link: link de destino
    // String $icone: endereco do icone
    // String $descricao: descricao do icone
    // String $texto: texto explicativo opcional
    // Bool $exibir_texto: exibir o texto apos a imagem ou nao
    // Bool $carregar: exibe o "carregando" com Ajax ou nao
    // Bool $foco: define o foco no primeiro campo disponivel na proxima pagina
    //
        global $CFG;
        static $tamanhos = array();
        if (isset($tamanhos[$icone])) {
            $tamanho = $tamanhos[$icone];
        } else {
            $tamanho = $tamanhos[$icone] = imagem::tamanho_html($icone);
        }
        if ($link) {
            $carregar = $carregar ? 1 : 0;
            $foco = $foco ? 1 : 0;
            $onclick = $CFG->ajax ? "onclick=\"return carregar(this, {$carregar}, {$foco});\"" : '';
            return "<a href=\"{$link}\" title=\"{$descricao}\" {$onclick}>".
                   "<img src=\"{$icone}\" alt=\"{$descricao}\" {$tamanho} />".
                   (($exibir_texto) ? ' '.$texto : '').
                   "</a>";
        }
        return "<img src=\"$icone\" alt=\"{$texto}\" {$tamanho} />".
               (($exibir_texto) ? ' '.$texto : '');
    }


    //
    //     Monta uma URL baseado nos dados informados (mesmo padrao que a funcao parse_url)
    //
    static public function montar_url($dados, $remover = array(), $delimitador = '&amp;') {
    // Array[String => Mixed] $dados: vetor associativo com os atributos da URL (scheme, host, port, user, pass, path, query, fragment)
    // Array[String] || Bool $remover: vetor de elementos que se deseja remover ou true para todos
    // String $delimitador: delimitador de atributos
    //
        $link = '';
        if (isset($dados['scheme'])) {
            $link .= $dados['scheme'].'://';
        }
        if (isset($dados['user'])) {
            $link .= $dados['user'];
            if (isset($dados['pass'])) {
                $link .= ':'.$dados['pass'].'@';
            }
        }
        if (isset($dados['host'])) {
            $link .= $dados['host'];
            if (isset($dados['port'])) {
                $link .= ':'.$dados['port'];
            }
        }
        if (isset($dados['path'])) {
            $link .= $dados['path'];
        } else {
            $link .= '/';
        }
        if (isset($dados['query']) && ($remover !== true)) {
            $parametros = array();
            $dados['query'] = html_entity_decode($dados['query']);
            parse_str($dados['query'], $parametros);
            if (is_array($remover) && count($remover)) {
                $novos_parametros = array();
                foreach ($parametros as $parametro => $valor) {
                    if (!in_array($parametro, $remover)) {
                        $novos_parametros[$parametro] = $valor;
                    }
                }
                if (count($novos_parametros)) {
                    $link .= '?'.http_build_query($novos_parametros, '', $delimitador);
                }
            } else {
                $link .= '?'.http_build_query($parametros, '', $delimitador);
            }
        }
        if (isset($dados['fragment'])) {
            $link .= '#'.$dados['fragment'];
        }
        return $link;
    }


    //
    //     Normaliza o link (remove atributos iguais e os especificados)
    //
    static public function normalizar(&$l, $remover = array()) {
    // String $l: link
    // Array[String] || Bool $remover: vetor de elementos que se deseja remover ou true para todos
    //
        $dados = parse_url($l);
        $l = self::montar_url($dados, $remover);
    }


    //
    //     Adiciona um atributo no link e retorna o novo link
    //
    static public function adicionar_atributo($link, $atributo, $valor, $delimitador = '&amp;') {
    // String $link: link
    // String || Array[String] $atributo: nome do atributo ou vetor de atributos
    // Mixed || Array[Mixed] $valor: valor do atributo ou vetor de valores
    // String $delimitador: delimitador de atributos
    //
        $dados = parse_url($link);
        $parametros = array();
        if (isset($dados['query'])) {
            $dados['query'] = html_entity_decode($dados['query']);
            parse_str($dados['query'], $parametros);
        }
        if (is_string($atributo) && (is_scalar($valor) || is_null($valor))) {
            $parametros[$atributo] = self::converter_valor($valor);
        } elseif (is_array($atributo) && is_array($valor) && (count($atributo) == count($valor))) {
            $parametros = $parametros + array_combine($atributo, $valor);
        }
        $dados['query'] = http_build_query($parametros, '', $delimitador);
        return self::montar_url($dados, array(), $delimitador);
    }


    //
    //     Converte um valor para um formato que possa ser enviado em uma query string
    //
    static public function converter_valor($valor) {
    // Mixed $valor: valor a ser convertido
    //
        switch (util::get_tipo($valor)) {
        case 'string':
            return $valor;
        case 'int':
            return sprintf('%0.0f', $valor);
        case 'float':
            $sub = abs($valor) - floor(abs($valor));
            $casas_decimais = max(0, strlen($sub) - 2);
            return sprintf("%0.{$casas_decimais}f", $valor);
        case 'bool':
            return $valor ? '1' : '0';
        case 'null':
            return '0';
        }

        // Se nao e' um valor escalar, serializar
        return htmlentities(serialize($valor));
    }

}//class
