<?php
//
// SIMP
// Descricao: Classe que controla os cookies
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.10
// Data: 22/08/2007
// Modificado: 29/09/2008
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('COOKIE_NOME',         $CFG->nome_cookie);
define('COOKIE_TEMPO_EXPIRA', 31536000);
define('COOKIE_DOMINIO',      $CFG->dominio);
define('COOKIE_PATH',         $CFG->path);
define('COOKIE_LOCALHOST',    $CFG->localhost);
define('COOKIE_DELIMITADOR',  chr(255).chr(255));

final class cookie {
    static private $backup;


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Recupera os valores do cookie retornando um vetor associativo
    //
    static public function consultar() {
        $vet = array();
        if (!isset($_COOKIE[COOKIE_NOME])) {
            return $vet;
        }
        $campos = explode(COOKIE_DELIMITADOR, self::decodificar($_COOKIE[COOKIE_NOME]));

        foreach ($campos as $campo) {
            if (strpos($campo, '=') === false) { continue; }
            list($nome, $valor) = explode('=', $campo);
            $vet[$nome] = $valor;
        }
        self::$backup = $vet;
        return $vet;
    }


    //
    //     Salva o valor dos cookies
    //
    static public function salvar($vetor) {
    // Array[String => Mixed] $vetor: vetor com os valores a serem salvos
    //
        if (!is_array($vetor)) {
            return false;
        }

        // Armazenar os dados no cookie
        $mudou  = count($vetor) != count(self::$backup);
        $campos = array();
        foreach ($vetor as $nome => $valor) {
            if (!isset(self::$backup[$nome]) || (self::$backup[$nome] != $valor)) {
                $mudou = true;
            }
            $campos[] = $nome.'='.$valor;
        }

        // Se ocorreram mudancas, entao salvar
        if ($mudou) {
            $str_campos = self::codificar(implode(COOKIE_DELIMITADOR, $campos));

            // Cookie do sistema
            if (strcmp(COOKIE_NOME, 'cookie_instalacao') != 0) {
                if (!COOKIE_LOCALHOST) {
                    return setcookie(COOKIE_NOME, $str_campos,  time() + COOKIE_TEMPO_EXPIRA, COOKIE_PATH, COOKIE_DOMINIO);
                } else {
                    return setcookie(COOKIE_NOME, $str_campos,  time() + COOKIE_TEMPO_EXPIRA, COOKIE_PATH);
                }

            // Cookie da instalacao
            } else {
                $tempo_cookie_instalacao = 1800;  // 30 minutos
                return setcookie(COOKIE_NOME, $str_campos,  time() + $tempo_cookie_instalacao, '/');
            }
        }
        return -1;
    }


    //
    //     Metodo usado para codificar os dados no cookie
    //
    static public function codificar($valor) {
    // String $valor: valor a ser codificado
    //
        return strtr(base64_encode($valor), '+/=', '-_.');
    }


    //
    //     Metodo usado para decodificar os dados do cookie
    //
    static public function decodificar($valor) {
    // String $valor: valor a ser decodificado
    //
        return base64_decode(strtr($valor, '-_.', '+/='));
    }

}//class
