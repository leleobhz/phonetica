<?php
//
// SIMP - Framework de Desenvolvimento
// Descricao: Arquivo para alterar dados de Analistas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.0
// Data: 29/05/2009
// Modificado: 29/05/2009
// Copyright (C) 2009  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Formulario
$dados_form = new stdClass();
$dados_form->campos = array(
    'Dados Gerais' => array(
        'usuario:nome',
        'usuario:email',
        'email_alternativo'
    )
);
modulo::alterar('analista', $dados_form);
