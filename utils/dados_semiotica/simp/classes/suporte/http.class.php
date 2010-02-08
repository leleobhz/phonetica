<?php
//
// SIMP
// Descricao: Classe de requisicoes HTTP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.10
// Data: 23/01/2008
// Modificado: 28/10/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class http {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Atalho para obter o conteudo de um link especifico
    //
    static public function get_conteudo_link($link) {
    // String $link: link a ser aberto
    //
        $dados_link = parse_url($link);
        $padrao = array('host' => 'localhost',
                        'port' => 80,
                        'path' => '/');
        foreach ($padrao as $chave => $valor) {
            if (!isset($dados_link[$chave])) {
                $dados_link[$chave] = $valor;
            }
        }

        if (isset($dados_link['query'])) {
            $dados_link['query'] = str_replace('&amp;', '&', $dados_link['query']);
            parse_str($dados_link['query'], $dados);
        } else {
            $dados = null;
        }

        $resultado = self::get($dados_link['host'], $dados_link['port'], $dados_link['path'], $dados);
        if ($resultado->cod_erro != 0) {
            return false;
        }
        return $resultado->conteudo_resposta;
    }


    //
    //     Envia dados via post para algum endereco e recebe o resultado
    //
    static public function post($host, $porta = 80, $path = '/', $dados = null) {
    // String $host: endereco do host para enviar os dados
    // Int $porta: porta usada na conexao por socket
    // String $path: caminho relativo do endereco para envio dos dados
    // Object || Array[String => Mixed] $dados: dados a serem submetidos
    //
        return self::enviar('POST', $host, $porta, $path, $dados);
    }


    //
    //     Envia dados via get para algum endereco e recebe o resultado
    //
    static public function get($host, $porta = 80, $path = '/', $dados = null) {
    // String $host: endereco do host para enviar os dados
    // Int $porta: porta usada na conexao por socket
    // String $path: caminho relativo do endereco para envio dos dados
    // Object || Array[String => Mixed] $dados: dados a serem submetidos
    //
        return self::enviar('GET', $host, $porta, $path, $dados);
    }


    //
    //     Envia dados por HTTP para algum endereco e recebe o resultado (cod_erro, erro, header_envio, header_resposta, vt_header_resposta, conteudo_resposta)
    //
    static public function enviar($metodo, $host, $porta = 80, $path = '/', $dados = null) {
    // String $metodo: metodo HTTP utilizado (GET, POST, HEAD, etc.)
    // String $host: endereco do host para enviar os dados
    // Int $porta: porta usada na conexao por socket
    // String $path: caminho relativo do endereco para envio dos dados
    // Object || Array[String => Mixed] $dados: dados a serem submetidos
    //
        $retorno = new stdClass();
        $retorno->cod_erro = false;
        $retorno->erro = false;
        $retorno->header_envio = self::montar_header($metodo, $host, $path, $dados);

        // Abrir conexao via socket
        $socket = fsockopen($host, $porta, $retorno->cod_erro, $retorno->erro, 1.0);
        stream_set_timeout($socket, 0, 100);
        if (!$socket) {
            return $retorno;
        }

        // Enviar pedido
        fwrite($socket, $retorno->header_envio);

        // Receber cabecalho da resposta
        $retorno->header_resposta = self::get_header_resposta($socket);
        $retorno->vt_header_resposta = self::parse_header($retorno->header_resposta);

        // Receber conteudo da resposta
        $retorno->conteudo_resposta = self::get_conteudo_resposta($socket, $retorno->vt_header_resposta);

        // Fechar a conexao
        fclose($socket);

        return $retorno;
    }


    //
    //     Monta o cabecalho HTTP de envio dos dados
    //
    static private function montar_header($metodo, $host, $path = '/', $dados = null) {
    // String $metodo: metodo HTTP utilizado (GET, POST, HEAD, etc.)
    // String $host: endereco do host para enviar os dados
    // String $path: caminho relativo do endereco para envio dos dados
    // Object || Array[String => Mixed] $dados: dados a serem submetidos
    //
        if ($path[0] != '/') {
            $path = '/'.$path;
        }
        $metodo = strtoupper($metodo);

        if ($dados) {
            $query = http_build_query((array)$dados, '', '&');
            $len = strlen($query);
        } else {
            $query = '';
            $len = 0;
        }

        switch ($metodo) {
        case 'GET':
            $h = "GET {$path}?{$query} HTTP/1.1\n".
                 "Host: {$host}\n".
                 "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\n".
                 "Connection: close\n".
                 "\n";
             break;

        case 'POST':
            $h = "POST {$path} HTTP/1.1\n".
                 "Host: {$host}\n".
                 "Content-Type: application/x-www-form-urlencoded\n".
                 "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\n".
                 "Content-Length: {$len}\n".
                 "Connection: close\n".
                 "\n".
                 $query;
            break;
        default:
            $h = "{$metodo} {$path} HTTP/1.1\n".
                 "Host: {$host}\n".
                 ($len ? "Content-Type: application/x-www-form-urlencoded\n" : '').
                 "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\n".
                 ($len ? "Content-Length: {$len}\n" : '').
                 "Connection: close\n".
                 "\n".
                 ($len ? $query : '');
            break;
        }
        return $h;
    }


    //
    //     Obtem o header da resposta
    //
    static private function get_header_resposta(&$socket) {
    // Resource $socket: conexao socket aberta
    //
        $h = '';
        do {
            $h .= fread($socket, 1);
        } while (!preg_match('/\\r\\n\\r\\n$/', $h) && !preg_match('/\\n\\n$/', $h));
        return $h;
    }


    //
    //     Interpreta um header HTTP de resposta e retorna na forma de um vetor associativo
    //
    static public function parse_header($header) {
    // String $header: headet HTTP a ser interpretado
    //
        $vt = explode((preg_match('/\\r\\n\\r\\n$/', $header) ? "\r\n" : "\n"), $header);

        $retorno = array();

        // Interpretar primeira linha
        $resultado = new stdClass();
        sscanf(array_shift($vt), '%s %d %s', $resultado->protocolo, $resultado->cod, $resultado->str);

        $retorno['resultado'] = $resultado;

        foreach ($vt as $item) {
            if ($item = trim($item)) {
                $p = strpos($item, ':');
                $cod = strtolower(substr($item, 0, $p));
                $valor = trim(substr($item, $p + 1));
                $retorno[$cod] = $valor;
            }
        }
        return $retorno;
    }


    //
    //     Obtem o conteudo da resposta HTTP
    //
    static private function get_conteudo_resposta(&$socket, $vt_header) {
    // Resource $socket: conexao socket aberta
    // Array[String => String] $vt_header: vetor com os dados do cabecalho da resposta
    //
        $c = '';

        // Caso tenha sido enviado truncado (em blocos)
        if (isset($vt_header['transfer-encoding']) && $vt_header['transfer-encoding'] == 'chunked') {
            while ($chunk_length = hexdec(fgets($socket))) {
                $chunk = '';
                $read_length = 0;
                while ($read_length < $chunk_length) {
                    $chunk .= fread($socket, $chunk_length - $read_length);
                    $read_length = strlen($chunk);
                }
                $c .= $chunk;
                fgets($socket);
            }

        // Caso tenha sido enviado em um bloco
        } else {
            if (isset($vt_header['content-length'])) {
                while (!feof($socket) && (strlen($c) < (int)$vt_header['content-length'])) {
                    $c .= fread($socket, 1024);
                }
            } else {
                $bytes = 128;
                do {
                    $c .= fread($socket, $bytes);
                    $meta = stream_get_meta_data($socket);
                    $bytes = min($meta['unread_bytes'], 128);
                } while ($bytes);
            }
        }
        if (isset($vt_header['content-length'])) {
            $c = substr($c, 0, (int)$vt_header['content-length']);
        }

        // Descomprimir o conteudo, caso tenha sido comprimido
        if (isset($vt_header['content-encoding'])) {
            $c = self::descomprimir($vt_header['content-encoding'], $c);
        }
        return $c;
    }


    //
    //     Descomprime um conteudo pelo metodo GZIP ou DEFLATE
    //
    static public function descomprimir($metodo, $conteudo) {
    // String $metodo: metodo usado na compressao (gzip ou deflate)
    // String $conteudo: valor comprimido
    //
        switch (strtolower($metodo)) {
        case 'gzip':
            if (function_exists('gzdecode')) {
                return gzdecode($conteudo);
            } else {
                echo 'Erro ao descomprimir conte&uacute;do com m&eacute;todo gzdecode';
                exit(1);
            }
        case 'deflate':
            if (functioin_exists('gzinflate')) {
                return gzinflate($conteudo);
            } else {
                echo 'Erro ao descomprimir conte&uacute;do com m&eacute;todo gzdecode';
                exit(1);
            }
        }
        return '';
    }


    //
    //     Obtem o tipo de codificacao do texto retornado
    //
    static public function get_charset(&$vt_header) {
    // Array[String => String] $vt_header: vetor com os dados do header
    //
        if (isset($vt_header['content-type']) && $pos = stripos($vt_header['content-type'], ';')) {
            $pos = stripos($vt_header['content-type'], '=', $pos) + 1;
            return trim(substr($vt_header['content-type'], $pos));
        }
        $charsets = explode(',', $_SERVER['HTTP_ACCEPT_CHARSET']);
        return strtolower(array_shift($charsets));
    }

}//class
