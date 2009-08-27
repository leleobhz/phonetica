<?php
//
// SIMP
// Descricao: Arquivo de Constantes
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.12
// Data: 28/05/2007
// Modificado: 04/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

/// ERROS
define('ERRO_ALTERAR',   'Voc&ecirc; n&atilde;o tem permiss&atilde;o para alterar este registro');
define('ERRO_EXCLUIR',   'Voc&ecirc; n&atilde;o tem permiss&atilde;o para excluir este registro');
define('ERRO_EXIBIR',    'Voc&ecirc; n&atilde;o tem permiss&atilde;o para ver os dados deste registro');
define('ERRO_INSERIR',   'Voc&ecirc; n&atilde;o tem permiss&atilde;o para inserir um novo registro');
define('ERRO_PERMISSAO', 'Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina');


/// TEMAS
define('TEMA_PADRAO', 'semiofon');
global $vt_temas;
$vt_temas = array('semiofon'       => 'Semiofon',
                  'azul'           => 'Azul',
                  'gelo'           => 'Gelo',
                  'ave'            => 'Ave',
                  'liamg'          => 'Liamg',
                  'acessibilidade' => 'Acessibilidade',
                  '0'              => 'Nenhum'
                 );


/// GRUPOS

// GERAIS
define('COD_ADMIN',     1);
define('COD_GERENTES',  2);
define('COD_ANALISTAS', 3);


/// CONSTANTES
define('VERSAO_SISTEMA', '1.0');
define('TEMPO_EXPIRA', 31536000);   // Tempo de duracao da cache (1 ano)
define('DESCRICAO_SIMP', 'Framework para o Desenvolvimento de Sistemas de Informação Modulares em PHP');
define('VERSAO_SIMP', '1.4.1b');
define('MANUAL_PHP', 'http://br.php.net/manual/pt_BR/');
define('DEVEL_BLOQUEADO', true);

// Ajustar estes valores de acordo com as capacidades do servidor
define('LOAD_AVG_MAX_ESPERADO', 1); // padrao 1
define('LOAD_AVG_MIN_ALERTA',   2); // padrao 2
define('LOAD_AVG_MAX_ALERTA',   3); // padrao 3
