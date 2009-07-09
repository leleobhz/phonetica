<?php
//
// SIMP
// Descricao: Classe de autenticacao de usuarios de um servidor SMTP
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 13/08/2008
// Modificado: 03/07/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Tipos de servidor
define('AUT_SMTP_SERVIDOR_UNIX', 1);  // Utiliza \n
define('AUT_SMTP_SERVIDOR_WIN',  2);  // Utiliza \r\n
define('AUT_SMTP_SERVIDOR_MAC',  3);  // Utiliza \r

// Tipos de autenticacao
define('AUT_SMTP_AUTENTICACAO_AUTO',        1); // Utiliza o primeiro tipo disponivel pelo servidor
define('AUT_SMTP_AUTENTICACAO_DIGEST_MD5',  2); //TODO: saber por que nao funciona
define('AUT_SMTP_AUTENTICACAO_CRAM_MD5',    3); //TODO: saber por que nao funciona
define('AUT_SMTP_AUTENTICACAO_GSSAPI',      4); //TODO: implementar
define('AUT_SMTP_AUTENTICACAO_KERBEROS_V4', 5); //TODO: implementar
define('AUT_SMTP_AUTENTICACAO_LOGIN',       6); // Pouco segura
define('AUT_SMTP_AUTENTICACAO_PLAIN',       7); // Pouco segura

final class aut_smtp extends autenticacao_base {
    private $conexao = false;    // Resource: conexao com o servidor SMTP
    private $log     = '';       // String: logs de comunicacao com o servidor
    private $eol     = "\n";     // String: sequencia de caracteres usado como quebra de linha pelo servidor SMTP


    //
    //     Metodo que retorna o nome da forma de autenticacao
    //
    public function get_nome() {
        return 'Servidor SMTP';
    }


    //
    //     Metodo que define a lista de parametros para autenticacao
    //     As credenciais SMTP sao formadas por:
    //     String login: login do usuario do servidor SMTP
    //     String senha: senha do usuario do servidor SMTP
    //     String $host: host do servidor SMTP (padrao: "localhost")
    //     Int $porta: porta de acesso ao servidor SMTP (padrao: 25)
    //     String $servico: nome do servico de acesso (padrao: "smtp")
    //     Int $tipo_servidor: tipo de servidor para saber a sequencia de caracteres para quebra de linha (padrao: AUT_SMTP_SERVIDOR_UNIX)
    //     Int $tipo_autenticacao: tipo de autenticacao no servidor SMTP (padrao: AUT_SMTP_AUTENTICACAO_AUTO)
    //     Float $tempo_espera_conexao: tempo limite de espera para conexao com o servidor em segundos (padrao: 1.0)
    //     Int $tempo_espera_resposta_segundos: tempo limite de espera para resposta do servidor em segundos (padrao: 3)
    //     Int $tempo_espera_resposta_microsegundos: tempo limite de espera para resposta do servidor em microsegundos (padrao: 0)
    //
    public function definir_parametros() {
        $atributo = new atributo('login', 'Login', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('senha', 'Senha', '');
        $atributo->set_tipo('string', false);
        $atributo->set_intervalo(0, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('host', 'Host', 'localhost');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('porta', 'Porta', 25);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(1, 1000000);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('servico', 'Servi&ccedil;o', 'smtp');
        $atributo->set_tipo('string', true);
        $atributo->set_intervalo(1, 128);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tipo_servidor', 'Tipo de Servidor', AUT_SMTP_SERVIDOR_UNIX);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(1, 20);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tipo_autenticacao', 'Tipo de Autentica&ccedil;&atilde;o', AUT_SMTP_AUTENTICACAO_AUTO);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(1, 20);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tempo_espera_conexao', 'Tempo de Espera da Conex&atilde;o', 1.0);
        $atributo->set_tipo('float', true);
        $atributo->set_intervalo(0, 1000);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tempo_espera_resposta_segundos', 'Tempo de Espera da Resposta (segundos)', 3);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(0, 100);
        $this->parametros[] = $atributo;
        unset($atributo);

        $atributo = new atributo('tempo_espera_resposta_microsegundos', 'Tempo de Espera da Resposta (microsegundos)', 0);
        $atributo->set_tipo('int', true);
        $atributo->set_intervalo(0, 1000);
        $this->parametros[] = $atributo;
        unset($atributo);
    }


    //
    //     Metodo que retorna se o usuario foi autenticado ou nao
    //
    public function autenticar_usuario(&$erros) {
    // Array[String] $erros: vetor de erros ocorridos
    //
        switch ($this->credenciais['tipo_servidor']) {
        case AUT_SMTP_SERVIDOR_UNIX: $this->eol = "\n";   break;
        case AUT_SMTP_SERVIDOR_WIN:  $this->eol = "\r\n"; break;
        case AUT_SMTP_SERVIDOR_MAC:  $this->eol = "\r";   break;
        }

        // Abrindo conexao
        $this->conexao = fsockopen($this->credenciais['host'], $this->credenciais['porta'], $cod_erro, $str_erro, $this->credenciais['tempo_espera_conexao']);

        // Se nao conectou
        if (!$this->conexao) {
            trigger_error('Erro ao conectar no servidor SMTP (Erro '.$cod_erro.': '.$str_erro.')');
            return $this->fechar_conexao($erros, "Erro ao conectar no servidor SMTP (Erro {$cod_erro})");
        }

        // Definir tempo de espera das resposta
        stream_set_timeout($this->conexao, $this->credenciais['tempo_espera_resposta_segundos'], $this->credenciais['tempo_espera_resposta_microsegundos']);

        $this->log = '';

        // Obter resposta
        $resposta = $this->smtp_get();
        $this->parse_resposta($resposta, $codigo, $mensagem);
        switch ($codigo) {
        case 220:
        case 200:
            // OK
            break;
        case 421:
            return $this->fechar_conexao($erros, 'Servidor SMTP indispon&iacute;vel no momento (Erro '.$codigo.': '.$mensagem.')');
        default:
            if ($codigo) {
                return $this->fechar_conexao($erros, 'Erro no servidor SMTP (Erro '.$codigo.': '.$mensagem.')');
            }
            return $this->fechar_conexao($erros, 'Sem resposta do servidor SMTP (talvez a conex&atilde;o esteja lenta demais)');
        }

        // Apresentando-se ao servidor SMTP para obter tipos de autenticacao suportados
        $this->smtp_put('EHLO '.$this->credenciais['host']);

        // Vetor com os tipos de autenticacao suportados pelo servidor SMTP e conhecidos pelo metodo
        $vt_autenticacao = array();

        // Vetor com os tipos de autenticacao suportados pelo servidor SMTP, mas desconhecidos pelo metodo
        $vt_desconhecidos = array();

        // Obter resposta
        $respostas = explode($this->eol, $this->smtp_get());
        foreach ($respostas as $resposta) {
            $this->parse_resposta($resposta, $codigo, $mensagem);
            switch ($codigo) {
            case 250:
            case 200:
                if (strpos($mensagem, 'AUTH') !== false) {

                    // Pegar o que vem depois de "AUTH " separado por espacos
                    $vt = explode(' ', substr($mensagem, 5));
                    foreach ($vt as $item) {
                        $item = strtoupper(trim($item));
                        switch ($item) {
                        case 'DIGEST-MD5':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_DIGEST_MD5;
                            break;
                        case 'CRAM-MD5':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_CRAM_MD5;
                            break;
                        case 'GSSAPI':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_GSSAPI;
                            break;
                        case 'KERBEROS_V4':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_KERBEROS_V4;
                            break;
                        case 'LOGIN':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_LOGIN;
                            break;
                        case 'PLAIN':
                            $vt_autenticacao[] = AUT_SMTP_AUTENTICACAO_PLAIN;
                            break;
                        default:
                            $vt_desconhecidos[] = $item;
                            break;
                        }
                    }
                }
                break;
            case '421':
                return $this->fechar_conexao($erros, 'Servidor SMTP indispon&iacute;vel no momento (Erro '.$codigo.': '.$mensagem.')');
            default:
                if ($codigo) {
                    return $this->fechar_conexao($erros, 'Erro no servidor SMTP (Erro '.$codigo.': '.$mensagem.')');
                }
                return $this->fechar_conexao($erros, 'Sem resposta do servidor SMTP (talvez a conex&atilde;o esteja lenta demais)');
            }
        }

        if (!count($vt_autenticacao)) {
            $erro = array();
            $erro[] = 'Nenhum tipo de autentica&ccedil;&atilde;o conhecido foi encontrado no servidor SMTP';
            if (count($vt_desconhecidos)) {
                $erro[] = 'O(s) seguinte(s) tipo(s) de autentica&ccedil;&atilde;o foi(ram) encontrado(s): '.implode(', ', $vt_desconhecidos);
            }
            return $this->fechar_conexao($erros, $erro);
        }

        // Obter o tipo de autenticacao a ser usado
        $tipo_autenticacao = false;
        if ($this->credenciais['tipo_autenticacao'] == AUT_SMTP_AUTENTICACAO_AUTO) {
            $tipo_autenticacao = current($vt_autenticacao);
        } else {
            $tipo_autenticacao = $this->credenciais['tipo_autenticacao'];
        }

        switch ($tipo_autenticacao) {

        // DIGEST-MD5 (RFC 2831): http://www.ietf.org/rfc/rfc2831.txt
        case AUT_SMTP_AUTENTICACAO_DIGEST_MD5:
            $this->smtp_put('AUTH DIGEST-MD5');
            $resposta = $this->smtp_get();
            $this->parse_resposta($resposta, $codigo, $mensagem);
            switch ($codigo) {
            case 334:
                $credenciais = $this->montar_credenciais_digest($mensagem);
                break;
            default:
                return $this->fechar_conexao($erros, 'Erro ao autenticar pelo m&eacute;todo DIGEST-MD5');
            }
            $this->smtp_put($credenciais);
            break;

        // CRAM-MD5 (RFC 2195): http://tools.ietf.org/html/rfc2195
        case AUT_SMTP_AUTENTICACAO_CRAM_MD5:
            $this->smtp_put('AUTH CRAM-MD5');
            $resposta = $this->smtp_get();
            $this->parse_resposta($resposta, $codigo, $mensagem);
            switch ($codigo) {
            case 334:
                $challenge = base64_decode($mensagem);
                $response = bin2hex($this->hmac_md5($challenge, $this->credenciais['senha']));
                $credenciais = base64_encode($this->credenciais['login'].' '.$response);
//echo '<p>Challenge: '.$challenge.'</p>';
//echo '<p>MD5 Response: '.$response.'</p>';
//echo '<p>Response: '.$credenciais.'</p>';
                break;
            default:
                return $this->fechar_conexao($erros, 'Erro ao autenticar pelo m&eacute;todo CRAM-MD5');
            }
            $this->smtp_put($credenciais);
            break;

        // KERBEROS_V4
        case AUT_SMTP_AUTENTICACAO_KERBEROS_V4:
            //TODO: implementar o metodo KERBEROS_V4
            return $this->fechar_conexao($erros, 'M&eacute;todo KERBEROS_V4 n&atilde;o implementado');
            break;

        // GSSAPI
        case AUT_SMTP_AUTENTICACAO_GSSAPI:
            //TODO: implementar o metodo GSSAPI
            return $this->fechar_conexao($erros, 'M&eacute;todo GSSAPI n&atilde;o implementado');
            break;

        // LOGIN
        case AUT_SMTP_AUTENTICACAO_LOGIN:
            $this->smtp_put('AUTH LOGIN');
            $resposta = $this->smtp_get();
            $this->parse_resposta($resposta, $codigo, $mensagem);
            switch ($codigo) {
            case 334:
                // OK
                break;
            case 504:
                return $this->fechar_conexao($erros, 'M&eacute;todo LOGIN n&atilde;o dispon&iacute;vel pelo servidor SMTP');
            default:
                return $this->fechar_conexao($erros, 'Erro ao autenticar pelo m&eacute;todo LOGIN (fase 1)');
            }
            $this->smtp_put(base64_encode($this->credenciais['login']));
            $resposta = $this->smtp_get();
            $this->parse_resposta($resposta, $codigo, $mensagem);
            if ($codigo != 334) {
                return $this->fechar_conexao($erros, 'Erro ao autenticar pelo m&eacute;todo LOGIN (fase 2)');
            }
            $this->smtp_put(base64_encode($this->credenciais['senha']));
            break;

        // PLAIN
        case AUT_SMTP_AUTENTICACAO_PLAIN:
            $credenciais = base64_encode("\000".$this->credenciais['login']."\000".$this->credenciais['senha']);
            $this->smtp_put('AUTH PLAIN '.$credenciais);
            break;

        // Tipo desconhecido
        default:
            return $this->fechar_conexao($erros, "Tipo de autentica&ccedil;&atilde;o desconhecido: ".$this->credenciais['tipo_autenticacao']);
        }

        // Verificar se autenticou corretamente
        $resposta = $this->smtp_get();
        $this->parse_resposta($resposta, $codigo, $mensagem);
        switch ($codigo) {
        case 235:
            // OK!
            break;
        case 535:
            return $this->fechar_conexao($erros, 'Usu&aacute;rio inv&aacute;lido ou senha incorreta');
        default:
            if ($codigo) {
                return $this->fechar_conexao($erros, 'Erro durante a autentica&ccedil;&atilde;o (Erro '.$codigo.': '.$mensagem.')');
            }
            return $this->fechar_conexao($erros, 'Erro durante a autentica&ccedil;&atilde;o (Sem resposta do servidor)');
        }

        // Encerrar conexao
        return $this->fechar_conexao($erros);
    }


    //
    //     Monta as credenciais para autenticacao DIGEST-MD5 [RFC-2831]
    //     (codigo baseado no arquivo auth.php do projeto SquirrelMail)
    //
    private function montar_credenciais_digest($challenge) {
    // String $challenge: desafio devolvido pelo servidor SMTP
    //
        $this->parse_challenge($challenge, $params, $repeated);

//util::dump($params);
//echo '<p><strong>Challenge:</strong></p><pre>'.chunk_split(base64_decode($challenge), 80, "\n").'</pre><hr />';

        $params_response = array();

        $utf8 = isset($params['charset']) && strtolower($params['charset']) == 'utf-8';

        if ($utf8) {
            $params_response['username'] = utf8_encode($this->credenciais['login']);
        } else {
            $params_response['username'] = utf8_decode($this->credenciais['login']);
        }
        $params_response['realm'] = $params['realm'];
        $params_response['nonce'] = $params['nonce'];
        $params_response['nc'] = sprintf('%08x', 1);
        $params_response['cnonce'] = base64_encode(md5(microtime()));
        $params_response['qop'] = 'auth';
        $params_response['serv-type'] = $this->credenciais['servico'];
        if ($this->credenciais['host'] != $params['realm']) {
            $params_response['host'] = $this->credenciais['host'];
        }
        $params_response['digest-uri'] = $this->credenciais['servico'].'/'.$this->credenciais['host'];

        // Computar a entrada RESPONSE
        // H($s) = md5($s, true)
        // KD($k, $s) = H($k.':'.$s) = md5($k.':'.$s);
        // HEX($n) = bin2hex($n)
        // HMAC($k, $s) = hmac_md5($s, $k);

        // if authzid {
        //     A1 = { H( { username-value, ":", realm-value, ":", passwd } ), ":", nonce-value, ":", cnonce-value }
        // } else {
        //     A1 = { H( { username-value, ":", realm-value, ":", passwd } ), ":", nonce-value, ":", cnonce-value, ":", authzid-value }
        // }
        $cred_a1 = $this->credenciais['login'].':'.$params['realm'].':'.$this->credenciais['senha'];
        if (isset($params['charset']) && strtolower($params['charset']) == 'utf-8') {
            $cred_a1 = utf8_encode($cred_a1);
        } else {
            $cred_a1 = utf8_decode($cred_a1);
        }
        $h_cred_a1 = md5($cred_a1, true);
        $A1 = $h_cred_a1.':'.$params_response['nonce'].':'.$params_response['cnonce'];
        if (isset($params_response['authzid'])) {
            $A1 .= ':'.$params_response['authzid'];
        }

        // if qop == auth-int or qop == auth-conf {
        //     A2 = { "AUTHENTICATE:", digest-uri-value, ":00000000000000000000000000000000" }
        // } else {
        //     A2 = { "AUTHENTICATE:", digest-uri-value }
        // }
        $A2 = 'AUTHENTICATE:'.$params_response['digest-uri'];
        switch ($params_response['qop']) {
        case 'auth-int':
        case 'auth-conf':
            $A2 .= ':'.sprintf('%032d', 0);
        }


        // RESPONSE = HEX( KD ( HEX( H(A1) ),
        //                      { nonce-value, ":" nc-value, ":", cnonce-value, ":", qop-value, ":", HEX( H(A2) ) }
        //                    )
        //               )
        // H($s) = md5($s, true)
        // KD($k, $s) = H($k.':'.$s) = md5($k.':'.$s);
        // HEX($n) = bin2hex($n)
        // HMAC($k, $s) = hmac_md5($s, $k);

        $hex_h_a1 = md5($A1);
        $prefix = $params['nonce'].':'.$params_response['nc'].':'.$params_response['cnonce'].':'.$params_response['qop'];
        $hex_h_a2 = md5($A2);

        $hex_kd = md5($hex_h_a1.':'.$prefix.':'.$hex_h_a2);

/*
echo '<p>Cred A1:<pre>'.$cred_a1.'</pre></p>';
echo '<p>H(Cred A1):<pre>'.$h_cred_a1.'</pre></p>';
echo '<p>A1:<pre>'.$A1.'</pre></p>';
echo '<p>HEX( H(A1) ) = <pre>'.$hex_h_a1.'</pre></p>';
echo '<p>PREFIX = <pre>'.$prefix.'</pre></p>';
echo '<p>A2:<pre>'.$A2.'</pre></p>';
echo '<p>HEX( H(A2) ) = <pre>'.$hex_h_a2.'</pre></p>';
echo '<p>HEX(KD) = <pre>'.$hex_kd.'</pre></p>';
*/

        $params_response['response'] = $hex_kd;
        $params_response['maxbuf'] = 65536;
        if ($utf8) {
            $params_response['charset'] = 'utf-8';
        }

        if (isset($params['cipher'])) {
            $vt_cipher = explode(',', $params['cipher']);
            if (in_array('3des', $vt_cipher) && defined('MCRYPT_3DES')) {
                $params_response['cipher'] = '3des';
            } elseif (in_array('des', $vt_cipher) && defined('MCRYPT_DES')) {
                $params_response['cipher'] = 'des';
            } elseif (in_array('rc4', $vt_cipher) && defined('MCRYPT_RC4')) {
                $params_response['cipher'] = 'rc4';
            } elseif (in_array('rc4-40', $vt_cipher) && defined('MCRYPT_RC4_40')) {
                $params_response['cipher'] = 'rc4-40';
            } elseif (in_array('rc4-56', $vt_cipher) && defined('MCRYPT_RC4_56')) {
                $params_response['cipher'] = 'rc4-56';
            } else {
                return false;
            }
        }

//util::dump($params_response);

        foreach ($params_response as $param => $value) {
            switch ($param) {
            case 'username':
            case 'cnonce':
            case 'digest-uri':
            case 'authzid':
            case 'realm':
            case 'nonce':
                $params_response[$param] = $param.'="'.$value.'"';
                break;
            default:
                $params_response[$param] = $param.'='.$value;
                break;
            }
        }

        $response = implode(',', $params_response);

        if (strlen($response) > 4096) {
            return false;
        }

//echo '<p><strong>Response:</strong></p><pre>'.chunk_split($response, 80, "\n").'</pre>';

        return base64_encode($response);
    }


    //
    //     Obtem os dados do challenge enviado pelo DIGEST-MD5
    //
    private function parse_challenge($challenge, &$params, &$repeated) {
    // String $challenge: Challenge do metodo DIGEST-MD5 enviado pelo servidor em base64
    // Array[String => Mixed] $params: parametros obtidos do challenge
    // Array[String => Array[String => Mixed]] $repeated: parametros com mais de uma entrada sao armazenados nesta matriz
    //
        $challenge = base64_decode($challenge);

        // Parse challenge
        $challenge_part = $challenge;
        $params = array();
        $repeat = array();
        while (preg_match('/^([A-z-]+)=([\"][^"]*[\"]|[^,]+)[,](.*)/', $challenge_part, $match)) {
            $value = ($match[2][0] == '"') ? substr($match[2], 1, -1) : $match[2];
            if (!isset($params[$match[1]])) {
                $params[$match[1]] = $value;
            } else {
                $repeated[$match[1]][] = $value;
            }
            $challenge_part = $match[3];
        }
        preg_match('/^([A-z-]+)=([\"][^"]*[\"]|.+)$/', $challenge_part, $match);
        $value = ($match[2][0] == '"') ? substr($match[2], 1, -1) : $match[2];
        if (!isset($params[$match[1]])) {
            $params[$match[1]] = $value;
        } else {
            $repeated[$match[1]][] = $value;
        }

        // Completar/Formatar challenge
        if (!isset($params['realm'])) {
            $params['realm'] = $this->credenciais['host'];
        }
        if (!isset($params['maxbuf'])) {
            $params['maxbuf'] = 65536;
        } else {
            $params['maxbuf'] = (int)$params['maxbuf'];
        }
        if (!isset($params['qop'])) {
            $params['qop'] = 'auth';
        }
    }


    //
    //     Gera o hash MD5 com ou sem chave
    //
    private function hmac_md5($str, $chave = '') {
    // String $str: dado a ser criptografado
    // String $chave: chave de criptografia
    //
        // Usar extensao MHASH
        if (extension_loaded('mhash')) {
            if (!$chave) {
                $hmac = mhash(MHASH_MD5, $str);
            } else {
                $hmac = mhash(MHASH_MD5, $str, $chave);
            }
            return $hmac;

        // Usar extensao HASH
        } elseif (extension_loaded('hash') && in_array('md5', hash_algos())) {
            if (!$chave) {
                $hmac = hash('md5', $str, true);
            } else {
                $hmac = hash_hmac('md5', $str, $chave, true);
            }
            return $hmac;
        }

        // Nao usar extensao
        if (!$chave) {
            return md5($str, true);
        }
        $chave = str_pad($chave, 64, chr(0x00));
        if (strlen($chave) > 64) {
            $chave = md5($chave, true);
        }
        $ipad = $chave ^ str_repeat(chr(0x36), 64);
        $opad = $chave ^ str_repeat(chr(0x5c), 64);

        $hmac = $this->hmac_md5($opad.md5($ipad.$str, true));
        return $hmac;
    }


    //
    //     Fecha a conexao informando um possivel erro ocorrido
    //
    private function fechar_conexao(&$erros, $erro = false) {
    // Array[String] $erros: vetor de erros ocorridos
    // String || Array[String] $erro: erro ocorrido, caso exista
    //
        if (is_string($erro)) {
            $erros[] = $erro;
        } elseif (is_array($erro)) {
            $erros = array_merge($erros, $erro);
        }
        if ($this->conexao) {
            $this->smtp_put('QUIT');
            $resposta = $this->smtp_get();
            fclose($this->conexao);
        }
        if (count($erros)) {
            return false;
        }
        return true;
    }


    //
    //     Envia um comando ao servidor SMTP aberto
    //
    private function smtp_put($mensagem) {
    // String $mensagem: comando ou mensagem a ser enviada
    //
        fwrite($this->conexao, $mensagem.$this->eol);

        // Guardar log de comunicacao com SMTP
        $this->log .= '[Envio '.microtime(true).'] '.$mensagem.$this->eol;
    }


    //
    //     Obtem a resposta do servidor SMTP
    //
    private function smtp_get() {
        $bytes = 128;
        $resposta = '';

        do {
            $resposta .= fread($this->conexao, $bytes);
            $meta = stream_get_meta_data($this->conexao);
            $bytes = min($meta['unread_bytes'], 128);
        } while ($bytes);
        $resposta = trim($resposta);

        // Guardar log de comunicacao com SMTP
        $this->log .= '[Resposta '.microtime(true).'] '.$resposta.$this->eol;

        return $resposta;
    }


    //
    //     Obtem os dados de uma resposta do servidor
    //
    private function parse_resposta($resposta, &$codigo, &$mensagem = '') {
    // String $resposta: resposta do servidor a ser analisada
    // Int $codigo: codigo de retorno da resposta
    // String $mensagem: mensagem legivel sobre a resposta
    //
        $expressao = '/^([0-9]{3})[ |-](.*)[\r|\n]*$/';
        if (preg_match($expressao, $resposta, $match)) {
            $codigo = (int)$match[1];
            $mensagem = $match[2];
            return $codigo;
        }
        $codigo = 0;
        $mensagem = '';
        return false;
    }


    //
    //     Retorna o log entre o cliente e servidor SMTP
    //
    public function get_log() {
        return $this->log;
    }


    //
    //     Codifica uma string com o metodo guradado nos atributos cipher_...
    //
    private function codificar($str) {
    // String $str: string a ser codificada
    //
        return mcrypt_cbc($this->cipher_method, $this->cipher_key, $str, MCRYPT_ENCRYPT, $this->cipher_iv);
    }


    //
    //     Decodifica uma string com o metodo guradado nos atributos cipher_...
    //
    private function decodificar($str) {
    // String $str: string a ser decodificada
    //
        return mcrypt_cbc($this->cipher_method, $this->cipher_key, $str, MCRYPT_DECRYPT, $this->cipher_iv);
    }

}
