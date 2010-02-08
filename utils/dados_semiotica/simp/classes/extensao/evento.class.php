<?php
//
// SIMP
// Descricao: Classe Evento
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.com.br
// Versao: 1.2.0.4
// Data: 09/08/2007
// Modificado: 19/01/2010
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
final class evento extends evento_base {

    //
    //     Indica se o registro pode ser manipulado pelo usuario
    //
    public function pode_ser_manipulado(&$usuario) {
    // usuario $usuario: usuario a ser testado
    //
        // Se e' admin ou o criador do evento ou o evento e' publico a todos
        if ($usuario->possui_grupo(COD_ADMIN) ||
            $usuario->cod_usuario = $this->get_atributo('cod_usuario') ||
            $this->get_atributo('visibilidade') == EVENTO_TODOS_GRUPOS) {
            return true;
        }

        // Se esta' em um grupo que pode ver o evento
        $vt_grupos = array_merge(array(EVENTO_TODOS_GRUPOS), array_keys($usuario->grupos));
        return in_array($this->get_atributo('visibilidade'), $vt_grupos);
    }


    //
    //     Indica se o formulario de um registro pode ser acessado ou nao por um usuario
    //
    public function pode_acessar_formulario(&$usuario, &$motivo = '') {
    // usuario $usuario: usuario a ser testado
    // String $motivo: motivo pelo qual nao se pode acessar o registro
    //
        // Se e' admin ou o criador do evento ou o evento e' publico a todos
        if ($usuario->possui_grupo(COD_ADMIN) ||
            $usuario->cod_usuario == $this->get_atributo('cod_usuario')) {
            return true;
        }
        return false;
    }


    //
    //     Indica como um campo deve ser apresentado no formulario
    //
    public function campo_formulario(&$form, $campo, $valor) {
    // formulario $form: formulario que deve receber o campo
    // String $campo: nome do campo a ser inserido
    // Mixed $valor: valor padrao do campo
    //
        if ($this->possui_atributo($campo)) {
            $atributo = $this->get_definicao_atributo('data');
        }

        switch ($campo) {
        case 'data':
            if ($this->id_form == $this->id_formulario_inserir() &&
                !$this->get_auxiliar('campo_data')) {
                $prefixo = $atributo->nome;
                $data = objeto::parse_data($valor, false);
                $form->campo_hora('data', $data['hora'], $data['minuto'], $data['segundo'], 'Hora');
                return true;
            }
            break;
        }
        return parent::campo_formulario($form, $campo, $valor);
    }


    //
    //     Realiza a validacao final
    //
    public function validacao_final(&$dados) {
    // Object $dados: dados a serem validados
    //
        switch ($this->id_form) {
        case $this->id_formulario_alterar():
        case $this->id_formulario_inserir():
            if ($dados->visibilidade) {
                $g = new grupo('', $dados->visibilidade);
                if (!$g->existe()) {
                    $this->erros[] = 'Visibilidade informada n&atilde;o existe';
                    return false;
                }
            }
            break;
        }
        return true;
    }


    //
    //     Retorna um vetor com as possiveis visibilidades
    //
    public function get_vetor_visibilidade() {
        return array(EVENTO_TODOS_GRUPOS => 'Todos') + objeto::get_objeto('grupo')->vetor_associativo();
    }


    //
    //     Define a forma de exibir os atributos da classe
    //
    public function exibir_atributo($nome_atributo) {
    // String $nome_atributo: nome do atributo a ser impresso
    //
        $valor = $this->get_atributo($nome_atributo);

        switch ($nome_atributo) {
        case 'visibilidade':
            if ($valor) {
                $grupo = new grupo('', $valor);
                return $grupo->existe() ? $grupo->get_nome() : 'Nenhum';
            }
            return 'Todos';
        }
        return parent::exibir_atributo($nome_atributo);
    }


    //
    //     Imprime um calendario
    //
    static public function imprimir_calendario($link_pagina, $link_pagina_exibir, $id, $cod_usuario, $time = false, $vt_grupos, $exibir_todos = false) {
    // String $link_pagina: link da pagina dos eventos do dia
    // String $link_pagina_exibir: link da pagina do evento
    // String $id: id da tabela
    // Int $cod_usuario: codigo do usuario
    // Int $time: timestamp desejado
    // Array[Int] $vt_grupos: vetor de grupos
    // Bool $exibir_todos: exibe todos os eventos, independente do grupo
    //
        global $CFG;

        // Dias da semana livres
        $livres = array(0, 6); // (Domingo e Sabado)

        // Recuperar dia, mes e ano
        if (isset($_GET['cal'])) {
            $time = (int)$_GET['cal'];
        } elseif (isset($_SESSION[__CLASS__][$id])) {
            $time = (int)$_SESSION[__CLASS__][$id];
        } else {
            $time = ($time !== false) ? $time : $CFG->time;
        }
        $mes  = (int)strftime('%m', $time);
        $ano  = strftime('%Y', $time);
        $anom = strftime('%y', $time);
        $hoje = (($mes == (int)strftime('%m')) && ($ano == strftime('%Y'))) ? strftime('%d') : 0;

        // Guardar na sessao (caso exista)
        if (isset($_SESSION)) {
            $_SESSION[__CLASS__][$id] = $time;
        }

        // Gerar link para mes anterior
        $mes_anterior = mktime(0, 0, 0, $mes - 1, 1, $ano);
        $link_voltar = $CFG->site;
        $link_voltar = link::adicionar_atributo($link_voltar, 'cal', $mes_anterior);
        $voltar = link::texto($link_voltar, paginacao::seta_esquerda(), 'M&ecirc;s anterior', '', 'seta', 1, 0, 0);

        // Gerar link para mes seguinte
        $mes_seguinte = mktime(0, 0, 0, $mes + 1, 1, $ano);
        $link_avancar = $CFG->site;
        $link_avancar = link::adicionar_atributo($link_avancar, 'cal', $mes_seguinte);
        $avancar = link::texto($link_avancar, paginacao::seta_direita(), 'M&ecirc;s seguinte', '', 'seta', 1, 0, 0);


        // Recuperar nomes dos dias da semana e dos meses
        $semanas           = listas::get_semanas();
        $semanas_abreviado = listas::get_semanas(1);
        $meses             = listas::get_meses();

        // Recuperar primeiro e ultimo dia do mes
        $mes_seguinte = $mes + 1;
        $ano_seguinte = $ano;
        if ($mes_seguinte == 13) {
            $mes_seguinte = 1;
            $ano_seguinte = $ano + 1;
        }
        $primeiro_dia_bd = '01-'.$mes.'-'.$ano;
        $ultimo_dia_bd   = '01-'.$mes_seguinte.'-'.$ano_seguinte;

        $primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
        $ultimo_dia   = mktime(0, 0, -1, $mes + 1, 1, $ano);

        // Consultar eventos do mes
        $eventos = array();
        $vt_condicoes = array();
        $vt_condicoes[] = condicao_sql::montar('data', '>=', $primeiro_dia_bd);
        $vt_condicoes[] = condicao_sql::montar('data', '<', $ultimo_dia_bd);
        $condicoes = condicao_sql::sql_and($vt_condicoes);
        $eventos_mes = objeto::get_objeto('evento')->consultar_varios($condicoes, true, 'data');
        foreach ($eventos_mes as $evento) {
            $data_evento = $evento->get_atributo_data('data');
            $eventos[(int)$data_evento['dia']][] = $evento;
        }

        // Gerar titulo da tabela
        $titulo = $meses[$mes].'/'.$anom;
        $caption = 'Calend&aacute;rio de '.$meses[$mes].' de '.$ano;

        // Imprimindo o Calendario
        echo "<table id=\"{$id}\" class=\"calendario\" summary=\"Listar eventos de {$titulo}\">\n";
        echo "<caption>{$caption}</caption>\n";

        // Gerando grupo de colunas
        echo "<colgroup>\n";
        for ($i = 0; $i < 7; $i++) {
            $s = $semanas[$i];
            $class = in_array($i, $livres) ? 'class="livre"' : '';
            echo "  <col {$class}></col>\n";
        }
        echo "</colgroup>\n";
        echo "<thead>\n";

        // Cabecalho
        echo "  <tr>\n";
        echo "    <th>{$voltar}</th>\n";
        echo "    <th class=\"titulo_calendario\" colspan=\"5\"><span title=\"M&ecirc;s atual\">{$titulo}</span></th>\n";
        echo "    <th>{$avancar}</th>\n";
        echo "  </tr>\n";

        // Dias da semana
        echo "  <tr>\n";
        for ($i = 0; $i < 7; $i++) {
            $s = ucfirst($semanas[$i]);
            $a = $semanas_abreviado[$i];
            echo "    <th id=\"semana{$i}\" abbr=\"{$s}\" scope=\"col\"><acronym title=\"{$s}\">{$a}</acronym></th>\n";
        }
        echo "  </tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // Primeira linha
        $s = strftime('%w', $primeiro_dia);
        echo "  <tr>\n";
        for ($i = 0; $i < $s; $i++) {
            echo "    <td headers=\"semana{$i}\">&nbsp;</td>\n";
        }
        for ($d = 1; $s < 7; $d++, $s++) {
            $eh_visivel = false;
            $data = mktime(0, 0, 0, $mes, $d, $ano);

            // Checar se existem eventos no dia
            $data_seguinte = mktime(0, 0, 0, $mes, $d + 1, $ano);
            if (isset($eventos[$d])) {
                $eventos_dia = $eventos[$d];
                $tem_evento  = true;
            } else {
                $eventos_dia = array();
                $tem_evento  = false;
            }

            $bloco_eventos = array();
            if ($tem_evento) {
                foreach ($eventos_dia as $e) {
                    $eh_dono = $e->cod_usuario == $cod_usuario;

                    // Se o usuario tem visibilidade sobre o evento
                    $eh_visivel = (in_array($e->visibilidade, $vt_grupos) || $exibir_todos || $eh_dono);
                    if ($eh_visivel) {
                        $link_evento = link::texto($link_pagina_exibir.'?cod_evento='.$e->cod_evento, texto::codificar($e->nome), texto::codificar($e->descricao), '', '', 1);
                        $bloco_eventos[] = "<p>{$link_evento}</p>\n";
                    }
                }
            }

            if ($d == $hoje) {
                if ($tem_evento && $eh_visivel) {
                    $class = ' class="evento_hoje"';
                } else {
                    $class = ' class="hoje"';
                }
            } elseif ($tem_evento && $eh_visivel) {
                $class = ' class="evento"';
            } else {
                $class = '';
            }

            $bloco = '';
            if ($eh_visivel && count($bloco_eventos)) {
                $bloco .= "<div class=\"dados_eventos\">\n";
                $bloco .= "<strong>Eventos:</strong>\n";
                $bloco .= implode("\n", $bloco_eventos);
                $bloco .= "</div>\n";
            }
            $link_dia = link::texto($link_pagina.'?time='.$data, $d, false, '', '', 1);
            echo "    <td{$class} headers=\"semana{$s}\">{$link_dia}{$bloco}</td>\n";
        }
        echo "  </tr>\n";

        // Linhas intermediarias
        $ultimo_d = (int)strftime('%d', $ultimo_dia);
        while ($d <= $ultimo_d) {
            echo "  <tr>\n";
            for ($s = 0; $s < 7; $d++, $s++) {

                // Reinicia a varival
                $eh_visivel = false;

                if ($d > $ultimo_d) {
                    echo "    <td headers=\"semana{$s}\">&nbsp;</td>\n";
                    continue;
                }
                $dia = $d;

                $data = mktime(0, 0, 0, $mes, $dia, $ano);

                // Checar se existem eventos no dia
                $data_seguinte = mktime(0, 0, 0, $mes, $d + 1, $ano);
                if (isset($eventos[$d])) {
                    $eventos_dia = $eventos[$d];
                    $tem_evento  = true;
                } else {
                    $eventos_dia = array();
                    $tem_evento  = false;
                }

                // Gerar bloco de eventos
                $bloco_eventos = array();
                if ($tem_evento) {
                    foreach ($eventos_dia as $e) {

                        // Se o usuario tem visibilidade sobre o evento
                        $eh_dono = $e->cod_usuario == $cod_usuario;
                        $eh_visivel = (in_array($e->visibilidade, $vt_grupos) || $exibir_todos || $eh_dono);
                        if ($eh_visivel) {
                            $link_evento = link::texto($link_pagina_exibir.'?cod_evento='.$e->cod_evento, texto::codificar($e->nome), texto::codificar($e->descricao), '', '', 1);
                            $bloco_eventos[] = "<p>{$link_evento}</p>\n";
                        }
                    }
                }

                $link_dia = link::texto($link_pagina.'?time='.$data, $dia, false, '', '', 1);

                if ($d == $hoje) {
                    if ($tem_evento && $eh_visivel) {
                        $class = ' class="evento_hoje"';
                    } else {
                        $class = ' class="hoje"';
                    }
                } elseif ($tem_evento && $eh_visivel) {
                    $class = ' class="evento"';
                } else {
                    $class = '';
                }
                $bloco = '';
                if ($eh_visivel && count($bloco_eventos)) {
                    $bloco .= "<div class=\"dados_eventos\">\n";
                    $bloco .= "<strong>Eventos:</strong>\n";
                    $bloco .= implode("\n", $bloco_eventos);
                    $bloco .= "</div>\n";
                }
                echo "    <td{$class} headers=\"semana{$s}\">{$link_dia}{$bloco}</td>\n";
            }
            echo "  </tr>\n";
        }
        echo "</tbody>\n";
        echo "</table>\n";
    }

}//class
