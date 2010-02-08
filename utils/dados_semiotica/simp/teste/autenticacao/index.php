<?php
//
// SIMP
// Descricao: Arquivo que testa os tipos de autenticacao
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
$drivers = autenticacao::get_drivers(true);

echo '<html><head><title>Autentica&ccedil;&atilde;o</title></head><body>';

if (!isset($_POST['tipo'])) {
    echo '<form method="post" action="index.php">';
    echo '<p><label for="tipo">Tipo de Autentica&ccedil;&atilde;o:</label> ';
    echo '<select name="tipo" id="tipo">';
    foreach ($drivers as $driver => $descricao) {
        echo '<option value="'.$driver.'">'.$descricao.'</option>';
    }
    echo '</select> ';
    echo '<input type="submit" value="Selecionar" />';
    echo '</p>';
    echo '</form>';
} elseif (!isset($_POST['autenticar'])) {
    $tipo = $_POST['tipo'];
    $autenticacao = new autenticacao($_POST['tipo']);
    $i = 1;

    echo '<form method="post" action="index.php">';
    echo '<p>Tipo de Autentica&ccedil;&atilde;o: '.$tipo.'</p>';
    echo '<fieldset>';
    echo '<legend>Credenciais</legend>';
    echo '<p>Marque apenas os campos que deseja usar</p>';
    echo '<input type="hidden" name="tipo" value="'.$tipo.'" />';
    foreach ($autenticacao->get_parametros() as $definicao) {
        $parametro = $definicao->nome;
        switch ($definicao->tipo) {
        case 'bool':
            $checked = $definicao->padrao ? ' checked="checked"' : '';
            echo '<p>';
            echo '<label for="param_'.$parametro.'">'.$definicao->descricao.':</label> ';
            echo '<input type="hidden" name="credenciais['.$parametro.']" value="0" />';
            echo '<input tabindex="'.$i.'" type="checkbox" name="credenciais['.$parametro.']" value="1" id="param_'.$parametro.'"'.$checked.' />';
            echo '<input type="checkbox" name="usar['.$parametro.']" value="1" checked="checked" />';
            echo '</p>';
            break;
        case 'char':
            echo '<p>';
            echo '<label for="param_'.$parametro.'">'.$definicao->descricao.':</label> ';
            echo '<input tabindex="'.$i.'" type="text" name="credenciais['.$parametro.']" id="param_'.$parametro.'" value="'.texto::codificar($definicao->padrao).'" size="3" maxlength="1" />';
            echo '<input type="checkbox" name="usar['.$parametro.']" value="1" checked="checked" />';
            echo '</p>';
            break;
        case 'int':
            if ($definicao->maximo - $definicao->minimo <= 100) {
                echo '<p>';
                echo '<label for="param_'.$parametro.'">'.$definicao->descricao.':</label> ';
                echo '<select tabindex="'.$i.'" name="credenciais['.$parametro.']" id="param_'.$parametro.'">';
                for ($i = $definicao->minimo; $i < $definicao->maximo; $i++) {
                    $selected = ($i == $definicao->padrao) ? ' selected="selected"' : '';
                    echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                }
                echo '</select>';
                echo '<input type="checkbox" name="usar['.$parametro.']" value="1" checked="checked" />';
                echo '</p>';
                break;
            }
        default:
            $maxlength = '';
            if ($definicao->tipo == 'string') {
                if ($definicao->maximo) {
                    $maxlength = ' maxlength="'.$definicao->maximo.'"';
                }
                $type = strpos($definicao->nome, 'senha') !== false ? 'passoword' : 'text';
            }
            echo '<p>';
            echo '<label for="param_'.$parametro.'">'.$definicao->descricao.':</label> ';
            echo '<input tabindex="'.$i.'" type="'.$type.'" name="credenciais['.$parametro.']" id="param_'.$parametro.'" value="'.texto::codificar($definicao->padrao).'" size="30"'.$maxlength.' />';
            echo '<input type="checkbox" name="usar['.$parametro.']" value="1" checked="checked" />';
            echo '</p>';
            break;
        }
        $i++;
    }
    echo '<input tabindex="'.$i.'" type="submit" name="autenticar" value="Autenticar" />';
    echo '</fieldset>';
    echo '</form>';
} else {
    $tipo = $_POST['tipo'];
    $credenciais = array();
    $dados = false;

    foreach ($_POST['usar'] as $parametro => $usar) {
        $credenciais[$parametro] = $_POST['credenciais'][$parametro];
    }

    $autenticacao = new autenticacao($tipo);
    if ($autenticacao->set_credenciais($credenciais, $erros)) {
        $resultado = $autenticacao->autenticar_usuario($erros);
        $dados = $autenticacao->get_dados_usuario();
    }

    if ($resultado) {
        echo '<p style="color: #006600;">Autenticado!</p>';
    } else {
        echo '<p style="color: #660000;">Erros:</p>';
        echo '<ul>';
        foreach ($erros as $e) {
            echo '<li>'.$e.'</li>';
        }
        echo '</ul>';
    }
    echo '<hr />';
    echo '<p>Dados obtidos do usu&aacute;rio:</p>';
    if ($dados) {
        echo '<table border="1px">';
        echo '<tr><th>Campo</th><th>Valor</th><th>Tipo</th></tr>';
        foreach ($dados as $campo => $valor) {
            echo '<tr>';
            echo '<td>'.$campo.'</td>';
            echo '<td>';
            if (is_array($valor) || is_object($valor) || is_resource($valor)) {
                util::dump($valor);
            } elseif (is_bool($valor)) {
                echo $valor ? 'true' : 'false';
            } else {
                echo $valor;
            }
            echo '</td>';
            echo '<td>'.gettype($valor).'</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Nenhum</p>';
    }

    if ($tipo == 'aut_smtp') {
        echo '<p>Log de comunica&ccedil;&atilde;o:</p>';
        echo '<pre>'.$autenticacao->get_log().'</pre>';
    }
}

echo '<hr /><p><a href="index.php">Voltar</a></p>';
echo '</body></html>';
