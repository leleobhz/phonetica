<?php
//
// SIMP
// Descricao: Classe que exibe mensagens de erros, avisos e ajuda
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.0
// Data: 09/08/2007
// Modificado: 05/05/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('MENSAGEM_AJAX', $CFG->ajax);

// Tipos de mensagens
define('MENSAGEM_ERRO',  1);
define('MENSAGEM_AVISO', 2);

final class mensagem {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Exibe uma mensagem de erro
    //
    static public function erro($mensagem, $return = false) {
    // String || Array[String] $mensagem: mensagem a ser impressa
    // Bool $return: retorna ou imprime o(s) erro(s)
    //
        $e = self::imprimir_mensagem($mensagem, MENSAGEM_ERRO);
        if ($return) {
            return $e;
        }
        echo $e;
    }


    //
    //     Exibe um aviso
    //
    static public function aviso($mensagem, $return = false) {
    // String || Array[String] $mensagem: mensagem a ser impressa (se falso, imprime os avisos interos do objeto)
    // Bool $return: retorna ou imprime o(s) aviso(s)
    //
        $a = self::imprimir_mensagem($mensagem, MENSAGEM_AVISO);
        if ($return) {
            return $a;
        }
        echo $a;
    }


    //
    //     Imprime uma mensagem de erro
    //
    static private function imprimir_mensagem($mensagem, $tipo) {
    // String || Array[String] $mensagem: string ou vetor com as mensagens
    // Int $tipo: MENSAGEM_ERRO ou MENSAGEM_AVISO
    //
        $m = '';

        // Se for um vetor, converter para string
        if (is_array($mensagem) && (count($mensagem) == 1)) {
            $mensagem = $mensagem[0];
        }
        switch ($tipo) {
        case MENSAGEM_ERRO:
            $nome = 'Erro';
            $nome_plural = 'Erros';
            $class = 'erro';
            break;
        case MENSAGEM_AVISO:
            $nome = 'Aviso';
            $nome_plural = 'Avisos';
            $class = 'aviso';
            break;
        default:
            $nome = "Tipo desconhecido ({$tipo})";
            $nome_plural = "Tipo desconhecido ({$tipo})";
            break;
        }

        if (is_array($mensagem)) {
            if (!count($mensagem)) { return false; }
            $m .= "<div class=\"{$class}\" onclick=\"return fechar(this);\">\n".
                  "  <strong>{$nome_plural}</strong>:\n".
                  self::listar($mensagem).
                  "</div>\n";
        } else {
            $m .= "<div class=\"{$class}\" onclick=\"return fechar(this);\">\n".
                  "  <strong>{$nome}</strong>: {$mensagem}\n".
                  "</div>\n";
        }
        return $m;
    }


    //
    //     Exibe uma lista hierarquica de mensagens
    //
    static private function listar($lista) {
    // Array[String || Type] $lista: lista hierarquica de strings
    //
        $m = "<ul>\n";
        foreach ($lista as $item) {
            if (is_array($item)) {
                $m .= self::listar($item);
            } else {
                $m .= "<li>{$item}</li>\n";
            }
        }
        $m .= "</ul>\n";

        return $m;
    }


    //
    //     Imprime um comentario de ajuda
    //
    static public function comentario($link, $comentarios, $return = false) {
    // String $link: link da pagina que possui o comentario
    // String $comentarios: comentarios sobre uma pagina
    // Bool $return: retornar ou imprimir o comentario
    //
        static $i = 0;
        $i++;

        if (isset($_GET['expandir'])) {
            $_SESSION[__CLASS__]['expandir'] = (int)$_GET['expandir'];
        } elseif (isset($_COOKIE['expandir'])) {
            $_SESSION[__CLASS__]['expandir'] = $_COOKIE['expandir'];
        } elseif (!isset($_SESSION[__CLASS__]['expandir'])) {
            $_SESSION[__CLASS__]['expandir'] = 0;
        }

        if ($_SESSION[__CLASS__]['expandir']) {
            $link = link::adicionar_atributo($link, 'expandir', 0);
            $onclick = MENSAGEM_AJAX ? "onclick=\"return mostrar_ajuda(this);\"" : '';

            $c = "<div class=\"bloco_ajuda_aberto\">\n";
            $c .= "  <a class=\"ajuda\" href=\"{$link}\" title=\"Esconder Coment&aacute;rio\" {$onclick}>Ajuda</a>\n";
            $c .= "  <blockquote>{$comentarios}</blockquote>\n";
            $c .= "</div>\n";
        } else {
            $link = link::adicionar_atributo($link, 'expandir', 1);
            $onclick = MENSAGEM_AJAX ? "onclick=\"return mostrar_ajuda(this);\"" : '';

            $c = "<div class=\"bloco_ajuda_fechado\">\n";
            $c .= "  <a class=\"ajuda\" href=\"{$link}\" title=\"Expandir Coment&aacute;rio\" {$onclick}>Ajuda</a>\n";
            $c .= "  <blockquote class=\"hide\">{$comentarios}</blockquote>\n";
            $c .= "</div>\n";
        }

        if ($return) { return $c; }
        echo $c;
    }

}//class
