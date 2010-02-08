<?php
//
// SIMP
// Descricao: Script de configuracoes de e-mail
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 11/11/2009
// Modificado: 11/11/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

// Ao marcar a opcao de tipo de e-mail SMTP,
// deve ser aberto o fieldset com os campos especificos
// deste tipo de e-mail
$cod_smtp = CONFIG_EMAIL_SMTP;

$script = <<<JAVASCRIPT
atualizar_tipo();

//
//     Atualiza o select de tipo de e-mail
//
function atualizar_tipo() {
    var tipo = document.getElementById("config-tipo_email");
    if (!tipo) {
        return false;
    }
    tipo.onchange = function() {
        var fieldset = document.getElementById("config-fieldset_c2239a92bde29f0a9f9173193cc2fe00");

        switch (this.options[this.selectedIndex].value) {
        case '{$cod_smtp}':
            fieldset.style.display = "block";
            break;
        default:
            fieldset.style.display = "none";
            break;
        }
        return true;
    };
    tipo.onchange();
    return true;
}

JAVASCRIPT;

// Cabecalho
setlocale(LC_ALL, 'C');
header('Content-Type: text/javascript; charset='.$CFG->charset);
header('Content-Disposition: inline; filename=script.js');
header('Content-Language: '.$CFG->lingua);
header('Cache-Control: public');
header('Pragma: ');
header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
header('Last-Modified: '.gmstrftime($CFG->gmt, getlastmod()));
header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
compactacao::header($script);
setlocale(LC_ALL, $CFG->localidade);

// Exibir script
echo $script;
exit(0);
