<?php
//
// SIMP
// Descricao: Classe responsavel pela geracao de falas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.1
// Data: 17/09/2009
// Modificado: 18/09/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Tipos de player
define('FALA_PLAYER_BOTAO',    1);
define('FALA_PLAYER_FINO',     2);
define('FALA_PLAYER_COMPLETO', 3);

// Constantes
define('FALA_PROGRAMA',       '/usr/bin/espeak -v pt -s 150 --stdout > ${arquivo}');
define('FALA_PROGRAMA_WAVE',  true);
define('FALA_PROGRAMA_UTF8',  true);
define('FALA_CONVERSOR_MP3',  '/usr/local/bin/lame ${entrada} ${saida}');
define('FALA_SISTEMA_UTF8',   $CFG->utf8);

final class fala {

    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Gera o som correspondente ao texto
    //
    public static function gerar_som($texto) {
    // String $texto: texto a ser gerado o som
    //
        // Gerar nome do arquivo
        $arquivo = self::get_nome_arquivo($texto);

        // Criar diretorio onde ficam as falas
        util::criar_diretorio_recursos('fala', 0700);

        // Se o sistema e' utf-8, mas o programa nao
        if (FALA_SISTEMA_UTF8 && !FALA_PROGRAMA_UTF8) {
            $texto = utf8_decode($texto);
        }
        $texto = trim($texto);
        $tr = array("\n" => ' ',
                    "\r" => ' ',
                    "\t" => ' ');
        $texto = strtr($texto, $tr);

        // Se o arquivo ja' existe, abortar
        if (is_file($arquivo)) {
            return true;
        }

        // Escapar argumentos do programa
        $arquivo_escapado = escapeshellarg($arquivo);

        // Preparar comando
        $tr = array('${arquivo}' => $arquivo_escapado);
        $comando = strtr(FALA_PROGRAMA, $tr);

        // Executar comando
        $p = popen($comando, 'w');
        if (!$p) {
            trigger_error('Erro ao executar comando: '.$comando, E_USER_WARNING);
            return false;
        }
        fwrite($p, $texto);
        fclose($p);

        // Converter para Mp3
        if (FALA_PROGRAMA_WAVE) {
            $arquivo_mp3 = $arquivo.'.mp3';
            $arquivo_mp3_escapado = escapeshellarg($arquivo_mp3);

            $tr = array('${entrada}' => $arquivo_escapado,
                        '${saida}'   => $arquivo_mp3_escapado);
            $comando = strtr(FALA_CONVERSOR_MP3, $tr);
            exec($comando);

            // Se gerou o mp3: apagar o wave e renomear o mp3
            if (file_exists($arquivo_mp3)) {
                unlink($arquivo);
                rename($arquivo_mp3, $arquivo);
            } else {
                return false;
            }
        }

        return file_exists($arquivo);
    }


    //
    //     Gera o codigo XHTML para inclusao do som
    //
    public static function gerar_html_som($texto, $tipo = FALA_PLAYER_BOTAO) {
    // String $texto: texto do som correspondente
    //
        global $CFG;
        self::gerar_som($texto);

        $arquivo = basename(self::get_nome_arquivo($texto));

        switch ($tipo) {
        case FALA_PLAYER_BOTAO:
            $link_playlist = urlencode($CFG->wwwroot.'webservice/playlist.xml.php?item[]='.$arquivo);
            $link = $CFG->wwwroot.'webservice/player_button/musicplayer.swf?playlist_url='.$link_playlist.'&amp;autoplay=0&amp;autoload=0';
            $obj = '<object type="application/x-shockwave-flash" width="18" height="18" data="'.$link.'">'.
                   '<param name="movie" value="'.$link.'" />'.
                   '</object>';
            break;
        case FALA_PLAYER_FINO:
            $link_playlist = urlencode($CFG->wwwroot.'webservice/playlist.xml.php?item[]='.$arquivo);
            $link = $CFG->wwwroot.'webservice/player_slim/xspf_player_slim.swf?playlist_url='.$link_playlist.'&amp;autoplay=0&amp;autoload=0&amp;player_title=Ajuda';
            $obj = '<object type="application/x-shockwave-flash" width="400" height="18" data="'.$link.'">'.
                   '<param name="movie" value="'.$link.'" />'.
                   '</object>';
            break;
        case FALA_PLAYER_COMPLETO:
            $link_playlist = urlencode($CFG->wwwroot.'webservice/playlist.xml.php?item[]='.$arquivo);
            $link = $CFG->wwwroot.'webservice/player/xspf_player.swf?playlist_url='.$link_playlist.'&amp;autoplay=0&amp;autoload=0&amp;player_title=Ajuda';
            $obj = '<object type="application/x-shockwave-flash" width="400" height="170" data="'.$link.'">'.
                   '<param name="movie" value="'.$link.'" />'.
                   '</object>';
            break;
        }
        return $obj;
    }


    //
    //     Gera o documento do som correspondente ao arquivo
    //
    public static function exibir_som($arquivo) {
    // String $arquivo: arquivo a ser buscado
    //
        global $CFG;
        $arquivo = basename($arquivo);
        $conteudo = file_get_contents($CFG->dirarquivos.'fala/'.$arquivo);

        setlocale(LC_TIME, 'C');
        header("X-Framework: SIMP/".VERSAO_SIMP);
        header('Content-Type: audio/mpeg');
        header("Content-Disposition: inline; filename={$arquivo}.mp3");
        header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
        header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + 31536000));
        header('Cache-Control: public');
        header('Pragma: ');
        compactacao::header($conteudo);
        echo $conteudo;
    }


    //
    //     Obtem o nome do arquivo correspondente ao texto
    //
    public static function get_nome_arquivo($texto) {
    // String $texto: texto a ser considerado
    //
        global $CFG;
        return $CFG->dirarquivos.'fala/'.md5($texto);
    }


}//class
