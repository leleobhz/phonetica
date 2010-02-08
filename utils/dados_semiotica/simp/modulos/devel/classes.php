<?php
//
// SIMP
// Descricao: Arquivo que lista as classes do Sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.1.1
// Data: 12/09/2007
// Modificado: 30/07/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
require_once('../../config.php');


/// Dados do quadro
$dados  = formulario::get_dados();
$modulo = util::get_modulo(__FILE__);
$titulo = 'Classes do Sistema';
$nav = array();
$nav[$CFG->wwwmods.$modulo.'/index.php'] = 'Desenvolvimento';
$nav[''] = 'Classes';
$estilos = array($CFG->wwwmods.$modulo.'/estilos.css');


/// Bloquear caso necessario
require_once($CFG->dirmods.$modulo.'/bloqueio.php');


/// Dados da pagina
$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
logica_listar_classes($dados);
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);


//
//     Gera a logica de listagem de classes
//
function logica_listar_classes($dados) {
// Object $dados: dados submetidos
//
    if ($dados) {
        $_SESSION['devel']['form_classes'] = serialize($dados);
        imprimir_formulario($dados);
        listar_classes($dados);
    } elseif (isset($_SESSION['devel']['form_classes'])) {
        $dados = unserialize($_SESSION['devel']['form_classes']);
        imprimir_formulario($dados);
        listar_classes($dados);
    } else {
        imprimir_formulario($dados);
    }
}


//
//     Imprime o formulario de selecao de classes
//
function imprimir_formulario($dados) {
// Object $dados: dados submetidos
//
    global $CFG;
    $action = $CFG->site;
    link::normalizar($action, true);

    $vt_tipos = array('Tradicionais' =>
                          array(
                              'suporte'      => 'Suporte',
                              'interface'    => 'Interface',
                              'dao'          => 'Acesso a Dados',
                              'autenticacao' => 'Autentica&ccedil;&atilde;o',
                              'tradicionais' => 'Todas'
                          ),
                      'Entidades da Aplica&ccedil;&atilde;o' =>
                          array(
                              'entidades' => 'Todas'
                          )
                      );

    $form = new formulario($action, 'form_classes');
    $form->titulo_formulario('Filtro de classes');
    $form->campo_select('tipo', 'tipo', $vt_tipos, $dados->tipo, 'Tipo');
    $form->campo_submit('enviar', 'enviar', 'Filtrar');
    $form->imprimir();
}


//
//     Lista as classes do sistema
//
function listar_classes($dados) {
// Object $dados: dados submetidos
//
    global $CFG;

    if (!extension_loaded('reflection')) {
        echo "<p>Este recurso exige a extens&atilde;o Reflection do PHP.</p>";
        return;
    }

    if (!isset($_SESSION['devel']['classe_aberta'])) {
        $_SESSION['devel']['classe_aberta'] = false;
        $_SESSION['devel']['metodos']       = array();
        $_SESSION['devel']['atributos']     = array();
    }
    if (isset($_GET['classe'])) {
        if ($_SESSION['devel']['classe_aberta'] == $_GET['classe']) {
            $_SESSION['devel']['classe_aberta'] = false;
            $_SESSION['devel']['metodos']       = array();
            $_SESSION['devel']['atributos']     = array();
        } else {
            try {
                simp_autoload($_GET['classe']);
                $_SESSION['devel']['classe_aberta'] = $_GET['classe'];
                $_SESSION['devel']['metodos']       = array();
                $_SESSION['devel']['atributos']     = array();
            } catch (Exception $e) {
                mensagem::erro('Classe inv&aacute;lida "'.$_GET['classe'].'"');
            }
        }
    } elseif (isset($_GET['metodo'])) {
        if (isset($_SESSION['devel']['metodos'][$_GET['metodo']])) {
            unset($_SESSION['devel']['metodos'][$_GET['metodo']]);
        } else {
            $_SESSION['devel']['metodos'][$_GET['metodo']] = true;
        }
    } elseif (isset($_GET['atributo'])) {
        if (isset($_SESSION['devel']['atributos'][$_GET['atributo']])) {
            unset($_SESSION['devel']['atributos'][$_GET['atributo']]);
        } else {
            $_SESSION['devel']['atributos'][$_GET['atributo']] = true;
        }
    }

    try {
        switch ($dados->tipo) {
        case 'suporte':
        case 'interface':
        case 'dao':
        case 'autenticacao':
            $classes = listas::get_classes($CFG->dirclasses.$dados->tipo.'/', false, false);
            break;
        case 'tradicionais':
            $classes = listas::get_classes($CFG->dirclasses.'suporte/', false, false) +
                       listas::get_classes($CFG->dirclasses.'interface/', false, false) +
                       listas::get_classes($CFG->dirclasses.'dao/', false, false) +
                       listas::get_classes($CFG->dirclasses.'autenticacao/', false, false);
            break;
        case 'entidades':
            $classes = listas::get_classes($CFG->dirclasses.'extensao/', 'objeto', true);
            break;
        default:
            mensagem::erro('Tipo de classe inv&aacute;lido: '.texto::codificar($dados->tipo));
            return false;
        }
    } catch (Exception $e) {
        mensagem::erro('Alguma classe possui um erro conceitual');
        return false;
    }
    sort($classes);

    foreach ($classes as $classe) {
        imprimir_classe($classe);
    }
    $total = count($classes);
    echo "<hr /><p><strong>Total:</strong> {$total} classes</p>\n";
    echo '<p><strong>Legenda:</strong></p>';
    echo '<p>'.icone::img('vermelho', 'private').' = private</p>';
    echo '<p>'.icone::img('amarelo', 'protected').' = protected</p>';
    echo '<p>'.icone::img('verde', 'public').' = public</p>';
    echo '<p>'.icone::img('adicionar', 'sobrescrever').' = pode ser sobrescrito(a)</p>';
}


//
//     Imprime uma classe
//
function imprimir_classe($classe) {
// String $classe: nome da classe
//
    global $CFG;

    try {
        $rc = new ReflectionClass($classe);
    } catch (Exception $e) {
        mensagem::erro("Erro com a classe {$classe} (Detalhes: ".$e->getMessage().")");
        return false;
    }
    $tipo    = '';
    if ($rc->isAbstract()) {
        $tipo .= 'abstract ';
    } elseif ($rc->isFinal()) {
        $tipo .= 'final ';
    }
    if ($tipo) {
        $tipo = '<span class="tipo">'.trim($tipo).'</span> ';
    }

    $rc_objeto = new ReflectionClass('objeto');
    $eh_entidade = $rc->isSubclassOf($rc_objeto);

    $link = $CFG->site;
    link::normalizar($link, true);
    $link = link::adicionar_atributo($link, 'classe', $classe);

    echo "<div id=\"classe_{$classe}\">\n";
    echo "<p><strong>";
    link::texto($link, $tipo.$classe, false, false, false, false, true, false);
    echo "</strong></p>\n";
    if ($_SESSION['devel']['classe_aberta'] == $classe) {
        $arquivo = $rc->getFileName();
        $instanciavel = $rc->isInstantiable() ? 'Sim' : 'N&atilde;o';
        $iteravel = $rc->isIterateable() ? 'Sim' : 'N&atilde;o';

        echo "<div class=\"dados\">\n";
        echo "<p><strong>Arquivo:</strong> {$arquivo}</p>\n";
        echo "<p><strong>Instanci&aacute;vel:</strong> {$instanciavel}</p>\n";
        echo "<p><strong>Iter&aacute;vel:</strong> {$iteravel}</p>\n";
        imprimir_atributos($classe, $rc, $eh_entidade);
        imprimir_metodos($classe, $rc);
        echo "</div>\n";
    }
    echo "</div>\n";
}


//
//     Imprime os atributos da classe informada
//
function imprimir_atributos($classe, $rc, $entidade) {
// String $classe: nome da classe
// ReflectionClass $rc: dados da classe
// Bool $entidade: indica se eh uma entidade ou nao
//
    global $CFG;
    if ($rc->isInstantiable()) {
        if ($entidade) {
            $obj = new $classe();
            $atributos  = $obj->get_atributos();
            $implicitos = $obj->get_implicitos();
            $rel_uu     = $obj->get_definicoes_rel_uu();
            $rel_un     = $obj->get_definicoes_rel_un();

            $count_atributos  = count($atributos);
            $count_implicitos = count($implicitos);
            $count_rel_uu     = count($rel_uu);
            $count_rel_un     = count($rel_un);
            $total            = $count_atributos + $count_implicitos + $count_rel_uu + $count_rel_un;

            echo "<p><strong>Atributos Virtuais (".$total."):</strong></p>\n";
            echo "<ul>\n";
            echo "<li>Simples:\n";
            if ($count_atributos) {
                echo "<ol>\n";
                foreach ($atributos as $nome => $atributo) {
                    $link = $CFG->site;
                    link::normalizar($link, true);
                    $link = link::adicionar_atributo($link, 'atributo', $nome);

                    $tipo = '<span class="tipo">'.$atributo->tipo.'</span> ';

                    $enum = $rc->hasMethod('get_vetor_'.$nome) ? ' <span>{enum}</span>' : '';

                    $descricao = $tipo.$nome.$enum;
                    echo "<li>\n";
                    link::texto($link, $descricao, false, false, false, false, true, false);
                    if (isset($_SESSION['devel']['atributos'][$nome])) {
                        imprimir_atributo_virtual_simples($obj, $rc, $atributo);
                    }
                    echo "</li>\n";
                }
                echo "</ol>\n";
            } else {
                echo 'Nenhum';
            }
            echo "</li>\n";

            echo "<li>Implicitos:\n";
            if ($count_implicitos) {
                echo "<ol>\n";
                foreach ($implicitos as $nome => $implicito) {
                    $implicito->nome = $nome;
                    $link = $CFG->site;
                    link::normalizar($link, true);
                    $link = link::adicionar_atributo($link, 'atributo', $nome);

                    echo "<li>\n";
                    link::texto($link, $nome, false, false, false, false, true, false);
                    if (isset($_SESSION['devel']['atributos'][$nome])) {
                        imprimir_atributo_virtual_implicito($implicito);
                    }
                    echo "</li>\n";
                }
                echo "</ol>\n";
            } else {
                echo 'Nenhum';
            }
            echo "</li>\n";
            echo "<li>Relacionamentos 1:1:\n";
            if ($count_rel_uu) {
                echo "<ol>\n";
                foreach ($rel_uu as $chave => $def) {
                    $def->chave = $chave;
                    $link = $CFG->site;
                    link::normalizar($link, true);
                    $link = link::adicionar_atributo($link, 'atributo', $def->nome);

                    $tipo = '<span class="tipo">'.$def->classe.'</span> ';
                    $descricao = $tipo.$def->nome;
                    echo "<li>\n";
                    link::texto($link, $descricao, false, false, false, false, true, false);
                    if (isset($_SESSION['devel']['atributos'][$def->nome])) {
                         imprimir_atributo_virtual_uu($def);
                    }
                    echo "</li>\n";
                }
                echo "</ol>\n";
            } else {
                echo 'Nenhum';
            }
            echo "</li>\n";
            echo "<li>Relacionamentos 1:N:\n";
            if ($count_rel_un) {
                echo "<ol>\n";
                foreach ($rel_un as $nome => $def) {
                    $def->nome = $nome;
                    $link = $CFG->site;
                    link::normalizar($link, true);
                    $link = link::adicionar_atributo($link, 'atributo', $nome);

                    $tipo = '<span class="tipo">'.$def->classe.'[]</span> ';
                    $descricao = $tipo.$nome;
                    echo "<li>\n";
                    link::texto($link, $descricao, false, false, false, false, true, false);
                    if (isset($_SESSION['devel']['atributos'][$nome])) {
                         imprimir_atributo_virtual_un($def);
                    }
                    echo "</li>\n";
                }
                echo "</ol>\n";
            } else {
                echo 'Nenhum';
            }
            echo "</li>\n";
            echo "</ul>\n";
        } else {
            $atributos = $rc->getProperties();
            $count_atributos = count($atributos);
            echo "<p><strong>Atributos Reais (".$count_atributos."):</strong></p>\n";
            if ($count_atributos) {
                echo "<ol>\n";
                foreach ($atributos as $atributo) {
                    imprimir_atributo($atributo);
                }
                echo "</ol>\n";
            } else {
                echo "<p>Nenhum</p>\n";
            }
        }

    } else {
        $atributos = $rc->getProperties();
        $count_atributos = count($atributos);
        echo "<p><strong>Atributos Reais (".count($atributos)."):</strong></p>\n";
        if ($count_atributos) {
            echo "<ol>\n";
            foreach ($atributos as $atributo) {
                imprimir_atributo($atributo);
            }
            echo "</ol>\n";
        } else {
            echo "<p>Nenhum</p>\n";
        }
    }
}


//
//     Imprime um atributo
//
function imprimir_atributo($atributo) {
// ReflectionProperty $atributo: dados do atributo
//
    $modificadores = '';
    if ($atributo->isStatic()) {
        $modificadores .= 'static';
    }
    if ($atributo->isPublic()) {
        $modificadores .= ' public';
    } elseif ($atributo->isProtected()) {
        $modificadores .= ' protected';
    } elseif ($atributo->isPrivate()) {
        $modificadores .= ' private';
    }
    $modificadores = '<span class="tipo">'.trim($modificadores).'</span>';
    echo '<li>'.$modificadores.' $'.$atributo->getName()."</li>\n";
}


//
//     Imprime os metodos da classe informada
//
function imprimir_metodos($classe, $rc) {
// String $classe: nome da classe
// ReflectionClass $rc: dados da classe
//
    global $CFG;
    $arquivo = $rc->getFileName();
    $metodos = $rc->getMethods();
    $count_metodos = count($metodos);

    echo "<p><strong>M&eacute;todos ({$count_metodos})</strong></p>\n";
    if ($count_metodos) {
        echo "<ol>\n";
        foreach ($metodos as $m) {
            $tipo = '';
            if ($m->isAbstract()) {
                $tipo .= 'abstract ';
                $sobrescrever = true;
            } elseif ($m->isFinal()) {
                $tipo .= 'final ';
                $sobrescrever = false;
            } else {
                $sobrescrever = (!$rc->isFinal()) && ($m->isPublic() || $m->isProtected());
            }
            if ($m->isStatic()) { $tipo .= 'static '; }

            $img = false;
            $alt = false;
            if ($m->isPublic()) {
                $tipo .= $alt = 'public ';
                $img = icone::img('verde', $alt);
            } elseif ($m->isProtected()) {
                $tipo .= $alt = 'protected ';
                $img = icone::img('amarelo', $alt);
            } elseif ($m->isPrivate()) {
                $tipo .= $alt = 'private ';
                $img = icone::img('vermelho', $alt);

                // Se nao pertence a classe e e' privado, nao pode ser acessado
                if ($arquivo != $m->getFileName()) {
                    continue;
                }
            }

            // Obter icone para sobrescrever ou nao o metodo
            if ($sobrescrever) {
                $img_escrever = ' '.icone::img('adicionar');
            } else {
                $img_escrever = '';
            }

            if ($tipo) {
                $tipo = '<span class="tipo">'.trim($tipo).'</span> ';
            }

            $link = $CFG->site;
            link::normalizar($link, true);
            $link = link::adicionar_atributo($link, 'metodo', $m->getName());

            echo "<li>\n";
            link::texto($link, $img.$img_escrever.' '.$tipo.$m->getName(), false, false, false, false, true, false);
            if (isset($_SESSION['devel']['metodos'][$m->getName()])) {
                imprimir_metodo($m);
            }
            echo "</li>\n";
        }
        echo "</ol>\n";
    } else {
        echo "<p>Nenhum</p>\n";
    }
}


//
//     Imprime os dados de um determinado metodo
//
function imprimir_metodo($m) {
// ReflectionMethod $m: dados do metodo
//
    echo "<div>\n";
    echo "<p><strong>Par&acirc;metros:</strong> ".$m->getNumberOfParameters()." (Obrigat&oacute;rios: ".$m->getNumberOfRequiredParameters().")</p>\n";
    if ($m->getNumberOfParameters()) {
        echo "<ol>\n";
        foreach ($m->getParameters() as $parametro) {
            $referencia = $parametro->isPassedByReference() ? '&amp;' : '';
            if ($parametro->isDefaultValueAvailable()) {
                $valor = ' = '.util::exibir_var($parametro->getDefaultValue(), UTIL_EXIBIR_PHP);
            } else {
                $valor = '';
            }
            echo '<li>'.$referencia.'$'.$parametro->getName().$valor."</li>\n";
        }
        echo "</ol>\n";
    }
    echo "</div>\n";
}


//
//     Imprime um atributo virtual simples (derivado da classe objeto)
//
function imprimir_atributo_virtual_simples($obj, $rc, $atributo) {
// objeto $obj: objeto da classe
// ReflectionClass $rc: dados da classe
// atributo $atributo: definicao do atributo
//
    global $CFG;
    echo "<div>\n";
    echo "<p><strong>Descri&ccedil;&atilde;o:</strong> {$atributo->descricao}</p>\n";
    echo "<p><strong>Valor Padr&atilde;o:</strong> ".util::exibir_var($atributo->padrao, UTIL_EXIBIR_PHP)."</p>\n";
    if ($atributo->chave) {
        echo "<p><strong>Chave:</strong> {$atributo->chave}</p>\n";
    }
    echo "<p><strong>&Uacute;nico:</strong> ".($atributo->unico ? 'sim' : 'n&atilde;o')."</p>\n";
    echo "<p><strong>Pode ser vazio:</strong> ".($atributo->pode_vazio ? 'sim' : 'n&atilde;o')."</p>\n";
    if ($atributo->validacao) {
        echo "<p><strong>Valida&ccedil;&atilde;o:</strong> {$atributo->validacao}</p>\n";
    }
    if ($atributo->validacao_especifica) {
        echo "<p><strong>Valida&ccedil;&atilde;o Espec&iacute;fica:</strong> m&eacute;todo {$atributo->validacao_especifica}</p>\n";
    }
    if ($atributo->filtro) {
        echo "<p><strong>Filtro:</strong> m&eacute;todo {$atributo->filtro}</p>\n";
    }

    switch ($atributo->tipo) {
    case 'int':
    case 'float':
        $intervalo = (($atributo->minimo !== false) ? $atributo->minimo : 'inf').
                     ' .. '.
                     (($atributo->maximo !== false) ? $atributo->maximo : 'inf');
        echo "<p><strong>Intervalo:</strong> {$intervalo}</p>\n";
        if ($atributo->tipo == 'float' && $atributo->casas_decimais !== false) {
            echo "<p><strong>Casas Decimais:</strong> {$atributo->casas_decimais}".($atributo->fixo ? ' Fixo' : '')."</p>\n";
        }
        if ($atributo->moeda) {
            echo "<p><strong>Moeda:</strong> sim</p>\n";
        }
        break;
    case 'string':
    case 'binario':
        $tamanho = (($atributo->minimo !== false) ? $atributo->minimo : 'inf').
                   ' .. '.
                   (($atributo->maximo !== false) ? $atributo->maximo : 'inf');
        echo "<p><strong>Tamanho:</strong> {$tamanho}</p>\n";
        break;
    }

    // Se eh um tipo enum
    $metodo = 'get_vetor_'.$atributo->nome;
    if ($rc->hasMethod($metodo)) {
        $valores = $obj->{$metodo}();

        echo "<p><strong>Enum:</strong></p>\n";
        echo "<ul>\n";
        foreach ($valores as $chave => $valor) {
            echo "<li>{$chave} = {$valor}</li>\n";
        }
        echo "</ul>\n";
    }
    
    echo "</div>\n";
}


//
//     Imprime um atributo virtual implicito (derivado da classe objeto)
//
function imprimir_atributo_virtual_implicito($atributo) {
// stdClass $atributo: definicao do atributo
//
    global $CFG;
    echo "<div>\n";
    echo "<p><strong>Descri&ccedil;&atilde;o:</strong> {$atributo->descricao}</p>\n";
    echo "<p><strong>M&eacute;todo:</strong> {$atributo->metodo}</p>\n";
    if (count($atributo->atributos)) {
        echo "<p><strong>Atributos Necess&aacute;rios:</strong></p>\n";
        echo "<ul>\n";
        foreach ($atributo->atributos as $a) {
            echo "<li>{$a}</li>\n";
        }
        echo "</ul>\n";
    }
    echo "</div>\n";
}


//
//     Imprime um atributo virtual 1:1 (derivado da classe objeto)
//
function imprimir_atributo_virtual_uu($atributo) {
// stdClass $atributo: definicao do atributo
//
    global $CFG;
    echo "<div>\n";
    echo "<p><strong>Descri&ccedil;&atilde;o:</strong> {$atributo->descricao}</p>\n";
    echo "<p><strong>Chave Relacionada:</strong> {$atributo->chave}</p>\n";
    echo "<p><strong>Forte:</strong> ".($atributo->forte ? 'Sim' : 'N&atilde;o')."</p>\n";
    echo "</div>\n";
}


//
//     Imprime um atributo virtual 1:N (derivado da classe objeto)
//
function imprimir_atributo_virtual_un($atributo) {
// stdClass $atributo: definicao do atributo
//
    global $CFG;
    echo "<div>\n";
    echo "<p><strong>Chave Relacionada:</strong> {$atributo->chave_fk}</p>\n";
    echo "<p><strong>Campo de Indexa&ccedil;&atilde;o:</strong> {$atributo->index}</p>\n";
    echo "<p><strong>Campo de Impress&atilde;o:</strong> {$atributo->impressao}</p>\n";
    if (is_string($atributo->ordem)) {
        echo "<p><strong>Campo de Ordena&ccedil;&atilde;o:</strong> {$atributo->ordem}</p>\n";
    } elseif (is_array($atributo->ordem)) {
        echo "<p><strong>Campos de Ordena&ccedil;&atilde;o:</strong></p>\n";
        echo "<ul>\n";
        foreach ($atributo->ordem as $campo => $tipo) {
            echo "<li>{$campo}: ".($tipo ? 'Crescente' : 'Decrescente')."</li>\n";
        }
        echo "</ul>\n";
    }
    echo "</div>\n";
}
