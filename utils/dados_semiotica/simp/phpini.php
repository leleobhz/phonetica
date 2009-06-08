<?php
//
// SIMP
// Descricao: Arquivo de sobreposicao das configuracoes do php.ini
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.9
// Data: 30/01/2008
// Modificado: 24/02/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Modificar estas configuracoes com cautela!                    // Valor padrao
ini_set('display_errors',                  0);                   // 0
ini_set('display_startup_errors',          0);                   // 0
ini_set('report_memleaks',                 1);                   // 1
ini_set('default_mimetype',                'text/html');         // text/html
ini_set('default_charset',                 'utf-8');             // utf-8
ini_set('zend.ze1_compatibility_mode',     0);                   // 0
ini_set('register_globals',                0);                   // 0
ini_set('auto_detect_line_endings',        0);                   // 0
ini_set('magic_quotes_runtime',            0);                   // 0 (nao mudar)
ini_set('memory_limit',                    '128M');              // 128M
ini_set('max_execution_time',              30);                  // 30
ini_set('arg_separator.output',            '&amp;');             // &amp; (nao mudar)
ini_set('session.use_cookies',             1);                   // 1 (nao mudar)
ini_set('session.use_trans_sid',           0);                   // 0
ini_set('session.use_only_cookies',        1);                   // 1 (nao mudar)
ini_set('session.hash_bits_per_character', 6);                   // 6
ini_set('session.gc_probability',          1);                   // 1
ini_set('session.gc_divisor',              100);                 // 100
ini_set('precision',                       14);                  // 14
ini_set('y2k_compliance',                  1);                   // 1 (nao mudar)
ini_set('date.timezone',                   'America/Sao_Paulo'); // America/Sao_Paulo

// Configuracoes para SGBD Oracle (descomentar caso necessario)
//putenv('ORACLE_HOME=/usr/lib/oracle/xe/app/oracle/product/10.2.0/server');
//putenv('NLS_LANG=BRAZILIAN PORTUGUESE_BRAZIL.UTF8');
