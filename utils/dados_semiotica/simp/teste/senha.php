<?php
//@ignoredoc
require_once('../config.php');

$dados_pessoais = array('Nome' => 'Rubens Takiguti Ribeiro',
                        'login' => 'rubens',
                        'Dia de Nascimento' => 24,
                        'Ano de Nascimento' => 1984);

$dados = formulario::get_dados();
$campos = array('senha' => '');
$dados = formulario::montar_dados($campos, $dados);

echo '<form action="" method="post">';
echo '<label for="senha">Senha:</label> ';
echo '<input type="text" name="senha" id="senha" value="'.$dados->senha.'" />';
echo '<input type="submit" value="Testar" />';
echo '</form>';

$nota = senha::get_qualidade($dados->senha, $dados_pessoais, $qualidade, $detalhes);
echo '<p>Dificuldade: '.$nota.' (0-100)</p>';
echo '<p>Qualidade: '.$qualidade.'</p>';
echo '<p>Detalhes:<br />'.nl2br($detalhes).'</p>';


$aleatoria = senha::gerar(12);
$nota = senha::get_qualidade($aleatoria, $dados, $qualidade, $detalhes);
echo '<hr />';
echo '<p>Senha aleat&oacute;ria: '.$aleatoria.'</p>';
echo '<p>Dificuldade: '.$nota.' (0-100)</p>';
echo '<p>Qualidade: '.$qualidade.'</p>';
echo '<p>Detalhes:<br />'.nl2br($detalhes).'</p>';

$aleatoria = senha::gerar(12, true);
$nota = senha::get_qualidade($aleatoria, $dados, $qualidade, $detalhes);
echo '<hr />';
echo '<p>Senha pronunci&aacute;vel: '.$aleatoria.'</p>';
echo '<p>Dificuldade: '.$nota.' (0-100)</p>';
echo '<p>Qualidade: '.$qualidade.'</p>';
echo '<p>Detalhes:<br />'.nl2br($detalhes).'</p>';
