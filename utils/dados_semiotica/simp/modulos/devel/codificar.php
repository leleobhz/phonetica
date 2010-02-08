<?php
//
// SIMP
// Descricao: codifica/decodifica um texto usando algum mecanismo
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.3
// Data: 03/09/2008
// Modificado: 23/12/2008
// License: LICENSE.TXT
// Copyright (C) 2009  Rubens Takiguti Ribeiro
//
require_once('../../config.php');

/// Dados do Formulario
$dados = formulario::get_dados();


/// Dados da Pagina
$modulo  = util::get_modulo(__FILE__);
$titulo  = 'Codificar/Decodificar Texto';
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Codificar/Decodificar Texto';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
formulario_codificacao($dados);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Exibe o formulario de codificacao/decodificacao de textos
//
function formulario_codificacao($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    if (isset($dados->enviar)) {
        $funcao = empty($dados->funcao) ? $dados->outra : $dados->funcao;
        switch ($funcao) {
        case 'braille_encode':
            $resultado = texto::braille_encode($dados->texto);
            break;
        case 'htmlentities':
        case 'html_entity_decode':
            $resultado = call_user_func_array($funcao, array($dados->texto, ENT_COMPAT, $CFG->charset));
            break;
        case 'htmlentities2':
            $resultado = htmlentities($dados->texto, ENT_NOQUOTES, $CFG->charset);
            $resultado = str_replace('&lt;', '<', $resultado);
            $resultado = str_replace('&gt;', '>', $resultado);
            break;
        default:
            if (function_exists($funcao)) {
                $resultado = call_user_func($funcao, $dados->texto);
            } else {
                mensagem::erro('A fun&ccedil;&atilde;o "'.$funcao.'" n&atilde;o existe');
            }
            break;
        }
        switch ($funcao) {
        case 'unserialize':
            ob_start();
            var_dump($resultado);
            $resultado = ob_get_contents();
            ob_end_clean();
            break;
        }
    }

    $funcoes = array(
                   'Braille' => array(
                       'braille_encode' => 'Codificar em Braille'
                   ),
                   'Base64' => array(
                       'base64_encode' => 'Codificar (base64_encode)',
                       'base64_decode' => 'Decodificar (base64_decode)'
                   ),
                   'Entity' => array(
                       'htmlentities'        => 'Codificar (htmlentities)',
                       'htmlentities2'       => 'Codificar sem Tags (htmlentities)',
                       'html_entity_decode'  => 'Decodificar (html_entity_decode)'
                   ),
                   'Serialize' => array(
                       'serialize'     => 'Serializar (serialize)',
                       'unserialize'   => 'Desserializar (unserialize)'
                   ),
                   'UTF-8' => array(
                       'utf8_encode'   => 'Codificar (utf8_encode)',
                       'utf8_decode'   => 'Decodificar (utf8_decode)'
                   ),
                   'Criptografia' => array(
                       'md5'           => 'MD5 (md5)',
                       'sha1'          => 'SHA-1 (sha1)',
                       'crypt'         => 'Crypt (crypt)'
                   ),
                   'Geral' => array(
                       'chr'           => 'Caractere (chr)',
                       'ord'           => 'C&oacute;digo ASCII (ord)',
                       'crc32'         => 'CRC-32 (crc32)',
                       'soundex'       => 'Soundex (soundex)',
                       'strlen'        => 'Tamanho (strlen)',
                       ''              => 'Outra (especifique abaixo)'
                   )
               );

    $padrao = array('texto'  => '',
                    'funcao' => '',
                    'outra'  => '');

    $dados = formulario::montar_dados($padrao, $dados);
    $form = new formulario($CFG->site, 'form_codificar');
    $form->campo_textarea('texto', 'texto', $dados->texto, 30, 5, 'Texto');
    $form->campo_select('funcao', 'funcao', $funcoes, $dados->funcao, 'Fun&ccedil;&atilde;o');
    $form->campo_text('outra', 'outra', $dados->outra, 128, 30, 'Outra');
    if (isset($resultado)) {
        $form->campo_textarea('resultado', 'resultado', $resultado, 30, 5, 'Resultado');
    }
    $form->campo_submit('enviar', 'enviar', 'Enviar');
    $form->imprimir();

}
