<?php
//
// SIMP
// Descricao: Exemplo de campos de formulario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

$dados    = formulario::get_dados();
$arquivos = formulario::get_arquivos();

// A checagem do captcha deve vir antes de imprimir o formulario novamente
$captcha_valido = $dados && captcha::validar($dados->captcha);

$pagina = new pagina();
$pagina->cabecalho('teste', array('' => 'Teste'), false);
$pagina->inicio_conteudo();
imprimir_form($dados);
if ($dados) {
    if ($captcha_valido) {
        tratar_dados($dados, $arquivos);
    } else {
        echo "<p>Texto do captcha inv&aacute;lido</p>";
    }
}
$pagina->fim_conteudo();
$pagina->rodape();


//
//     Imprime o formulario de teste
//
function imprimir_form($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    $ve = array(1 => 'Vermelho', 2 => 'Verde', 3 => 'Amarelo');

    $matriz = array('Colorido' => array(1 => 'Vermelho', 2 => 'Verde', 3 => 'Amarelo'),
                    'P&amp;B' => array(4 => 'Branco', 5 => 'Preto')
                   );
    $campos = array(
        'invisivel' => '',
        'nome' => '',
        'inteiro' => '100000000000000000',
        'real' => '-12,4',
        'senha' => '',
        'arq' => '',
        'texto' => '',
        'check' => array(),
        'radio' => 1,
        'select' => 1,
        'select_agrupado' => 1,
        'hora' => 0,
        'minuto' => 0,
        'inicio_dia' => (int)strftime('%d'),
        'inicio_mes' => (int)strftime('%m'),
        'inicio_ano' => (int)strftime('%Y'),
        'termino_dia' => 0,
        'termino_mes' => 0,
        'termino_ano' => 0,
        'rel' => '',
        'arquivo' => '',
        'cor_xml' => '1',
        'area_cnpq' => '',
        'busca' => '',
        'bool' => 0,
        'enviar' => ''
    );
    $dados = formulario::montar_dados($campos, $dados);

    $action = $CFG->wwwroot.'teste/formulario.php';
    $id = 'form_teste';

    $form = new formulario($action, $id, 'formulario');
    $form->titulo_formulario('Teste T&iacute;tulo');

    $form->campo_hidden('invisivel', 'oi');
    $form->campo_text('nome', 'nome', $dados->nome, 128, 30, 'Nome', false, false, false, false, 'Preencher com o nome');
    $form->campo_text('inteiro', 'inteiro', $dados->inteiro, 50, 30, 'Inteiro', false, false, false, 'int');
    $form->campo_text('real', 'real', $dados->real, 50, 30, 'Real', false, false, false, 'float');
    $form->campo_password('senha', 'senha', 128, 30, 'Senha', false, false, false, 'De prefer&ecirc;ncia, preencher com letras, n&uacute;meros e s&iacute;mbolos');
    $form->campo_file('arq1', 'arq1', 'Arquivo 1', false, false, false, false, 'Arquivo do tipo JPEG');
    $form->campo_file('arq2', 'arq2', 'Arquivo 2');
    $form->campo_textarea('texto', 'texto', $dados->texto, 30, 5, 'Texto', false, false, false, 'Texto com at&eacute; 200 caracteres');
    $form->campo_informacao('Texto explicativo (Informa&ccedil;&atilde;o)');
    $form->campo_aviso('Texto explicativo (Aviso)');
    $form->campo_checkbox('check', 'check', $ve, $dados->check, 'Checkbox', 2, array(2), 1, false, false, 'Se marcar vermelho, n&atilde;o pode verde');
    $form->campo_radio('radio', 'radio', $ve, $dados->radio, 'Radiobox', false, false, false, 'Cor favorita n&atilde;o vale ');
    $form->campo_select('select', 'select', $ve, $dados->select, 'Select', false, false, false, 'Cor favorita');
    $form->campo_select('select_agrupado', 'select_agrupado', $matriz, $dados->select_agrupado, 'Select Agrupado');
    $form->campo_hora('', $dados->hora, $dados->minuto, false, 'Hora', false, 'Hora em que chega no trabalho');
    $form->campo_data('inicio', $dados->inicio_dia, $dados->inicio_mes, $dados->inicio_ano, 'Data in&iacute;cio', 50, 0, false, false, 'Data de nascimento');
    $form->campo_data('termino', $dados->termino_dia, $dados->termino_mes, $dados->termino_ano, 'Data t&eacute;rmino');
    $form->campo_relacionamento('rel[]', 'rel', 'usuario', 'cod_usuario', 'nome', condicao_sql::montar('cancelado', '=', false), '', 128, 30, 'Usu&aacute;rio', 1, false, false, false, 'Usu&aacute;rio principal');
    $form->campo_clone('document.getElementById("area_rel")', "Usu&aacute;rio", 3, false, 'Clique em Adicionar para abrir um novo campo');
    $form->campo_relacionamento('arquivo', 'arquivo', 'arquivo', 'arquivo', 'descricao', condicao_sql::montar('modulo', '=', 'ajuda'), $dados->arquivo, 128, 30, 'Arquivo');
    $form->campo_relacionamento_xml('cor_xml', 'cor_xml', $CFG->wwwroot.'teste/cores.xml.php', $dados->cor_xml, 128, 30, 'Cor', 0, 0, 0, 'uint');
    $form->campo_hierarquico('area_cnpq', 'area_cnpq', $CFG->wwwroot.'teste/areas.xml.php', $dados->area_cnpq, 128, 30, '&Aacute;rea CNPq', false, false, false, false, 'Preencha ou busque um valor ');
    $form->campo_busca('busca', 'busca', 'usuario', 'login', $dados->busca, condicao_sql::montar('cancelado', '=', false), 50, 30, 'Login', false, false, array('link' => 'http://www.exemplo.com.br/', 'texto' => 'Login usado no sistema'));
    $form->campo_bool('bool', 'bool', 'Booleano', $dados->bool, false, false, false);
    $form->campo_image('imagem', 'imagem', $CFG->wwwimgs.'logos/logo_tecnolivre.jpg', 'Imagem');
    $form->campo_captcha();
    $form->campo_submit('enviar', 'enviar', 'Enviar', true, false, false, false, 'Ajuda do bot&atilde;o');
    $form->imprimir();
}


//
//     Trata os dados recebidos pelo formulario
//
function tratar_dados($dados, $arquivos) {
// Object $dados: dados submetidos
// Array[Object] $arquivos: arquivos submetidos
//
    global $CFG;
    util::get_cdata($cdata, $fcdata, false);

    echo "<h2>Dados submetidos</h2>\n";
    echo "<pre>{$cdata}";
    var_dump($dados);
    echo "{$fcdata}</pre>\n";

    echo "<h2>Arquivos submetidos</h2>";
    echo "<pre>{$cdata}";
    var_dump($arquivos);
    echo "{$fcdata}</pre>\n";

    // Com os arquivos submetidos podemos salva-lo em algum lugar:
    //if (is_uploaded_file($arquivos->arq1->tmp_name)) {
    //    move_uploaded_file($arquivos->arq1->tmp_name, $CFG->dirarquivos);
    //}

    $nivel = 9; // Nivel de compressao

    // Ou armazenar no BD de forma compactada
    $tipo     = $arquivos->arq1->type;
    $conteudo = file_get_contents($arquivos->arq1->tmp_name);
    $zipado   = gzcompress($conteudo, $nivel);
    $base64   = base64_encode($zipado);

    // Obter tamanho
    $t_conteudo = strlen($conteudo);
    $t_zipado   = strlen($zipado);
    $t_base64   = strlen($base64);

    // Calcular porcentagem de compactacao
    $p_zipado = round($t_zipado * 100 / $t_conteudo, 2);
    $p_base64 = round($t_base64 * 100 / $t_conteudo, 2);

    echo '<p>Tamanho arquivo: '.$t_conteudo.' bytes ('.texto::formatar_bytes($t_conteudo).')</p>';
    echo '<p>Tamanho zipado: '.$t_zipado.' bytes ('.texto::formatar_bytes($t_zipado).') '.$p_zipado.'% do original</p>';
    echo '<p>Tamanho base64: '.$t_base64.' bytes ('.texto::formatar_bytes($t_base64).') '.$p_base64.'% do original</p>';

    // Aqui poderiamos armazenar $base64 no BD em um campo de texto

    // Apos consultar o valor do BD, basta desconverter o arquivo da seguinte forma:
    $zipado2 = base64_decode($base64);
    $conteudo2 = gzuncompress($zipado2);
    echo '<p>Tamanho original: '.strlen($conteudo2).' bytes</p>';

    // Para exibir o conteudo:
    // header('Content-type: '.$tipo);
    // print $conteudo2;
}

