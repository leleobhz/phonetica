<?php
//@ignoredoc
/// Exemplos de graficos

require_once('../../config.php');

$arquivos = array('barra2.php',
                  'barra.php',
                  'linha2.php',
                  'linha.php',
                  'pizza.php',
                  'simples2.php',
                  'simples.php');


$titulo = 'Gr&aacute;ficos';
$nav[''] = $titulo;

$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, false);
$pagina->inicio_conteudo($titulo);

foreach ($arquivos as $arquivo) {
    $html = strpos($arquivo, 'simples') !== false;
    grafico::exibir_grafico('Teste', $CFG->wwwroot.'teste/graficos/'.$arquivo, $CFG->dirroot.'teste/graficos/'.$arquivo, false, $html);
}

$pagina->fim_conteudo();
$pagina->rodape();
exit(0);

