<?php
//
// SIMP
// Descricao: Classe para manipular o progresso de operacoes demoradas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 25/01/2010
// Modificado: 03/02/2010
// Copyright (C) 2010  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class progresso {


    //
    //     Abre um arquivo de controle de progresso
    //
    public static function abrir($id) {
    // String $id: Identificador unico do progresso
    //
        self::limpar();
        util::criar_diretorio_recursos('simp_progresso', 0700);
        $arquivo = self::get_arquivo($id);
        $time = time();
        return file_put_contents($arquivo, sprintf('%0.0f;%0.0f', 0, $time));
    }


    //
    //     Gera o nome do arquivo que guarda o progresso de um ID
    //
    private static function get_arquivo($id) {
    // String $id: Identificador unico do progresso
    //
        global $CFG;
        return $CFG->dirarquivos.'/simp_progresso/'.$id;
    }


    //
    //     Atualiza a porcentagem do progresso
    //
    public static function atualizar($id, $valor) {
    // String $id: identificador unico do progresso
    // Int $valor: valor da porcentagem do progresso (entre 0 e 100)
    //
        list($valor_antigo, $inicio) = self::consultar($id);

        $arquivo = self::get_arquivo($id);
        $valor = min(100, max(0, intval($valor)));
        return file_put_contents($arquivo, sprintf('%0.0f;%0.0f', $valor, $inicio));
    }


    //
    //     Obtem o progresso de um arquivo e a data de criacao
    //
    public static function consultar($id) {
    // String $id: identificador unico do progresso
    //
        $arquivo = self::get_arquivo($id);
        if (!file_exists($arquivo)) {
            return array(0, 0);
        }
        $conteudo = file_get_contents($arquivo);
        sscanf($conteudo, '%d;%d', $progresso, $inicio);
        return array($progresso, $inicio);
    }


    //
    //     Fecha um progresso
    //
    public static function fechar($id) {
    // String $id: identificador unico do progresso
    //
        $arquivo = self::get_arquivo($id);
        return unlink($arquivo);
    }


    //
    //     Limpa os progressos muito antigos
    //
    public static function limpar($periodo = null) {
    // Int $periodo: numero de segundos a serem coletados (padrao: 1 hora)
    //
        global $CFG;

        if (is_null($periodo)) {
            $periodo = 60 * 60 ;
        } else {
            $periodo = intval($periodo);
        }

        $time = time();
        $dir_base = $CFG->dirarquivos.'/simp_progresso';
        if (!is_dir($dir_base)) {
            return 0;
        }

        $dir = opendir($dir_base);
        $total = 0;
        while (($item = readdir($dir)) !== false) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $time_acesso = filemtime($dir_base.'/'.$item);
            if ($time > $time_acesso + $periodo) {
                self::fechar($item);
                $total += 1;
            }
        }
        closedir($dir_base);
        return $total;
    }

}//class
