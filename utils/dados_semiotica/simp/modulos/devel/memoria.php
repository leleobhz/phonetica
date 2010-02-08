<?php
//
// SIMP
// Descricao: Arquivo que exibe um grafico de utilizacao da memoria
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.5
// Data: 20/09/2007
// Modificado: 21/10/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Bloquear caso necessario
$modulo = util::get_modulo(__FILE__);
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Criar grafico
$g = new grafico('Gráfico de Memória por Entidade');
$g->nome_arquivo = 'memoria';
$g->formato      = TIPO_PNG;
$g->largura      = 450;
$g->altura       = 350; // 2M
$g->pos_legenda  = DIREITA;
$g->tipo_cor     = COR_NORMAL;
$g->tipo_grafico = GRAFICO_LINHA;

$entidade = $_GET['entidade'];
try {
    simp_autoload($entidade);
} catch (Exception $e) {
    trigger_error('A entidade "'.$entidade.'" nao existe ou possui erros', E_USER_ERROR);
}

$inicio = memory_get_usage();

for ($i = 10; $i <= 100; $i += 10) {
    for ($j = 0; $j < 10; $j++) {
        $vt[] = new $entidade();
    }
    $escala[] = $i;
    $valores[] = memory_get_usage() - $inicio;
}

$g->escala = $escala;
$g->valores = $valores;
$g->conversao_valores = 'texto::formatar_bytes';

$maior = max($valores);
$margem = 118;

// Regra de 3
// altura -> $maior
// 350    -> 2097152
$g->altura = ($maior * 350 / 2097152) + $margem;
$g->imprimir();
