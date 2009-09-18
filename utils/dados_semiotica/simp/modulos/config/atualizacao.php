<?php
//
// SIMP
// Descricao: Arquivo para atualizar entidades do BD
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.9
// Data: 27/09/2007
// Modificado: 26/02/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');
require_once($CFG->dirroot.'sessao.php');


/// Dados do Formulario
$modulo  = util::get_modulo(__FILE__);
$dados  = formulario::get_dados();
$action = $CFG->site;
$ajuda  = <<<AJUDA
  <p>Este formul&aacute;rio &eacute; destinado &agrave; 
  atualiza&ccedil;&atilde;o de entidades no BD. Deve ser
  usado com cautela, uma vez que modifica as tabelas do BD
  sem dar garantias de recupera&ccedil;&atilde;o dos dados.</p>
  <p><strong>Aten&ccedil;&atilde;o:</strong>
  N&atilde;o &eacute; poss&iacute;vel identificar campos que
  apenas trocaram de nome.</p>
AJUDA;


/// Dados da Pagina
$titulo  = 'Atualiza&ccedil;&atilde;o do Sistema';
$nav[]   = '#index.php';
$nav[]   = $modulo.'#'.basename(__FILE__);
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css.php');


/// Imprimir Pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->imprimir_menu($USUARIO);
$pagina->inicio_conteudo($titulo);
mensagem::comentario($CFG->site, $ajuda);
formulario_atualizacao($dados, $action);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


/// Funcoes


//
//     Imprime um formulario de atualizacao
//
function formulario_atualizacao($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    $resultado = logica_formulario($dados);
    if ($resultado === false) {
        imprimir_formulario($dados, $action);
    }
}


//
//     Realiza a logica de negocios e retorna false (nada feito), -1 (erro) e 1 (sucesso)
//
function logica_formulario($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    if (!$dados) {
        return false; // Nada foi feito
    }
    if (!$dados->confirmacao) {
        mensagem::aviso('&Eacute; necess&aacute;rio marcar a confirma&ccedil;&atilde;o para atualizar');
        return false;
    }

    $vt_criar_atributos   = array();
    $vt_remover_atributos = array();
    $vt_criar_entidades   = array();

    // Obter atributos a serem adicionados ou removidos de tabelas
    if (isset($dados->criar_atributos)) {
        foreach ($dados->criar_atributos as $dados_codificados) {
            $dados_decod = unserialize(base64_decode($dados_codificados));
            $vt_criar_atributos[] = $dados_decod;
        }
    }
    if (isset($dados->remover_atributos)) {
        foreach ($dados->remover_atributos as $dados_codificados) {
            $dados_decod = unserialize(base64_decode($dados_codificados));
            $vt_remover_atributos[] = $dados_decod;
        }
    }

    // Obter entidades a serem adicionadas
    if (isset($dados->criar_entidades)) {
        foreach ($dados->criar_entidades as $dados_codificados) {
            $dados_decod = unserialize(base64_decode($dados_codificados));
            $vt_criar_entidades[] = $dados_decod;
        }
    }

    // Instancia do objeto que realizara' as operacoes de atualizacao
    $instalacao = new instalacao();
    $instalacao->set_bd_config($CFG->bd_config);
    $retorno = false;

    echo "<h3>Adicionando os novos atributos:</h3>\n";
    if (count($vt_criar_atributos)) {
        echo "<ul>\n";
        foreach ($vt_criar_atributos as $a) {
            $resultado = $instalacao->adicionar_atributo($a->classe, $a->atributo, $erro);
            echo "<li>Classe {$a->classe} / Atributo {$a->atributo} / Resultado: ";
            echo ($resultado) ? 'OK' : "Erro (Detalhes: {$erro})";
            echo "</li>\n";
            $retorno = $resultado ? (($retorno == -1) ? -1 : 1) : -1;
        }
        echo "</ul>\n";
    } else {
        echo "<p>Nada a ser feito.</p>\n";
    }

    echo "<h3>Removendo os atributos depreciados:</h3>\n";
    if (count($vt_remover_atributos)) {
        echo "<ul>\n";
        foreach ($vt_remover_atributos as $a) {
            $resultado = $instalacao->remover_atributo($a->classe, $a->atributo, $erro);
            echo "<li>Classe {$a->classe} / Atributo {$a->atributo} / Resultado: ";
            echo ($resultado) ? 'OK' : "Erro (Detalhes: {$erro})";
            echo "</li>\n";
            $retorno = $resultado ? (($retorno == -1) ? -1 : 1) : -1;
        }
        echo "</ul>\n";
    } else {
        echo "<p>Nada a ser feito.</p>";
    }

    echo "<h3>Adicionando os novas entidades:</h3>\n";
    if (count($vt_criar_entidades)) {
        echo "<ul>\n";
        foreach ($vt_criar_entidades as $e) {
            $resultado = $instalacao->criar_tabelas(array($e->classe), $erros, $avisos, $resultado);
            echo "<li>Classe {$e->classe} / Resultado: ";
            echo ($resultado) ? 'OK' : "Erro (Detalhes: ".implode(' / ', $erros).")";
            echo "</li>\n";
            $retorno = $resultado ? (($retorno == -1) ? -1 : 1) : -1;
        }
        echo "</ul>\n";
    } else {
        echo "<p>Nada a ser feito.</p>\n";
    }

    switch ($retorno) {
    case 1:
        mensagem::aviso('Entidades atualizadas com sucesso');
        break;
    case -1:
        mensagem::erro('Erro ao atualizar as entidades. Confira os detalhes na p&aacute;gina.');
        break;
    case false:
        mensagem::aviso('Nada foi feito (nenhuma opera&ccedil;&atilde;o foi marcada)');
        break;
    }
    return $retorno;
}


//
//     Imprimir formulario
//
function imprimir_formulario($dados, $action) {
// Object $dados: dados submetidos
// String $action: endereco de destino dos dados
//
    // Vetores de alteracoes
    $vt_criar_atributos   = array(); // Vetor de atributos a serem criados nas tabelas existentes
    $vt_remover_atributos = array(); // Vetor de atributos a serem removidos de tabelas existentes
    $vt_criar_entidades   = array(); // Vetor de entidades a serem criadas

    // Obter as classes originarias da classe objeto
    $entidades = listas::get_entidades();

    // Obter as tabelas do BD
    $bd = new objeto_dao();
    $bd->carregar('operacao');
    $tabelas = array();
    foreach ($bd->get_tabelas() as $tabela) {
        $tabelas[] = $tabela->nome;
    }

    // Percorrer as classes em busca de mudancas
    $erros = array();
    $tabelas_entidades = array();
    foreach ($entidades as $nome_classe => $entidade) {
        try {
            simp_autoload($nome_classe);
            $obj = new $nome_classe();
        } catch (Exception $e) {
            $erros[] = $e->getMessage();
            continue;
        }

        // Obter tabela da entidade
        $tabelas_entidades[] = $obj->get_tabela();

        // Obter atributos da classe
        $atributos_classe = $obj->get_atributos();

        // Obter atributos da tabela
        $nome_tabela = $obj->get_tabela();

        // Se a tabela ja' existe
        if (in_array($nome_tabela, $tabelas)) {
            $atributos_tabela = $bd->get_campos($nome_tabela);

            // Percorrendo atributos da classe
            $nomes_atributos_classe = array();
            foreach ($atributos_classe as $atributo_classe) {
                $nome_atributo_classe = $atributo_classe->nome;
                $nomes_atributos_classe[] = $nome_atributo_classe;

                // Checar se existe o atributo na tabela
                $achou = false;
                foreach ($atributos_tabela as $atributo_tabela) {

                    // Se achou, parar
                    if (strcmp($nome_atributo_classe, $atributo_tabela->nome) == 0) {
                        $achou = true;
                        break;
                    }
                }

                // Se nao achou, incluir na lista de atributos a serem criados
                if (!$achou) {
                    $dados_criacao = new stdClass();
                    $dados_criacao->tabela   = $nome_tabela;
                    $dados_criacao->classe   = $nome_classe;
                    $dados_criacao->atributo = $nome_atributo_classe;
                    $vt_criar_atributos[] = $dados_criacao;
                }
            }

            // Percorrendo atributos da tabela
            foreach ($atributos_tabela as $atributo_tabela) {
                if (!in_array($atributo_tabela->nome, $nomes_atributos_classe)) {
                    $dados_remocao = new stdClass();
                    $dados_remocao->tabela   = $nome_tabela;
                    $dados_remocao->classe   = $nome_classe;
                    $dados_remocao->atributo = $atributo_tabela->nome;
                    $vt_remover_atributos[] = $dados_remocao;
                }
            }

        // Se a tabela nem existe ainda
        } else {
            $dados_criacao = new stdClass();
            $dados_criacao->tabela = $nome_tabela;
            $dados_criacao->classe = $nome_classe;
            $vt_criar_entidades[] = $dados_criacao;
        }
    }

    // Checar se ocorreram erros
    if (count($erros)) {
        mensagem::erro($erros);
    }

    // Checar se ha' operacoes a serem feitas
    $operacoes = false;

    $vt_criar_entidades_cod = array();
    if (count($vt_criar_entidades)) {
        foreach ($vt_criar_entidades as $dados) {
            $vt_criar_entidades_cod[base64_encode(serialize($dados))] = $dados->classe;
        }
        $operacoes = true;
    }

    $vt_criar_atributos_cod = array();
    if (count($vt_criar_atributos)) {
        foreach ($vt_criar_atributos as $dados) {
            $vt_criar_atributos_cod[base64_encode(serialize($dados))] = "{$dados->atributo} (Classe {$dados->classe})";
        }
        $operacoes = true;
    }

    $vt_remover_atributos_cod = array();
    if (count($vt_remover_atributos)) {
        foreach ($vt_remover_atributos as $dados) {
            $vt_remover_atributos_cod[base64_encode(serialize($dados))] = "{$dados->atributo} (Classe {$dados->classe})";
        }
        $operacoes = true;
    }

    // Imprimir formulario
    if ($operacoes) {
        $form = new formulario($action, 'form_atualizacao');
        if (count($vt_criar_entidades_cod)) {
            $form->campo_checkbox('criar_entidades', 'criar_entidades', $vt_criar_entidades_cod, array_keys($vt_criar_entidades_cod), 'Entidades a serem instaladas');
        }
        if (count($vt_criar_atributos_cod)) {
            $form->campo_checkbox('criar_atributos', 'criar_atributos', $vt_criar_atributos_cod, array_keys($vt_criar_atributos_cod), 'Atributos a serem adicionados');
        }
        if (count($vt_remover_atributos_cod)) {
            $form->campo_aviso("Os campos selecionados para remo&ccedil;&atilde;o ser&atilde;o apagados definitivamente do BD.");
            $form->campo_checkbox('remover_atributos', 'remover_atributos', $vt_remover_atributos_cod, array_keys($vt_remover_atributos_cod), 'Atributos a serem removidos');
        }
        $form->campo_bool('confirmacao', 'confirmacao', 'Marque para confirmar', 0);
        $form->campo_submit('enviar', 'enviar', 'Enviar', true);

        $form->imprimir();
    } else {
        echo '<p>N&atilde;o foi identificada nenhuma diferen&ccedil;a entre as entidades e o esquema do BD.</p>';
        echo '<p>Algumas mudan&ccedil;as que n&atilde;o s&atilde;o detectadas automaticamente:</p>';
        echo '<ul>';
        echo '  <li>Tamanhos dos campos</li>';
        echo '  <li>Nomes dos campos</li>';
        echo '  <li>Valida&ccedil;&otilde;es dos campos (inclusive de unicidade)</li>';
        echo '  <li>Descri&ccedil;&otilde;es dos campos ou das tabelas</li>';
        echo '  <li>Relacionamentos 1:1</li>';
        echo '</ul>';
        echo '<p>Para estes casos, recomenda-se estudar a mudan&ccedil;a para ';
        echo 'realiz&aacute;-la manualmente, com seguran&ccedil;a e sem perda de dados.</p>';
    }
}
