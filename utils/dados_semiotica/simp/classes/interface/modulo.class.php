<?php
//
// SIMP
// Descricao: Classe que gera paginas de modulos (exibir, alterar, inserir, listar e importar entidades)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.1.2.8
// Data: 01/02/2008
// Modificado: 22/12/2009
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
global $CFG, $USUARIO;

final class modulo {


/// # METODOS PUBLICOS PARA GERAR PAGINAS


    //
    //     Gera uma pagina de listar entidades (index.php)
    //     $dados_lista
    //     - String $id_lista: identificador da lista
    //     - Array[String] $opcoes: opcoes da lista ('inserir', 'alterar', 'excluir', ou algum codigo definido no metodo "dados_opcao", que e' um ponto de extensao da classe objeto)
    //     - Array[String] $campos: vetor de campos exibidos na lista e separados por "-"
    //     - Array[String] $campos_consultar: lista de campos a serem consultados para cada entidade (alem dos campos pedidos para exibicao)
    //     - Array[String => Bool] $ordem: campos usados para ordenar a lista apontando para o tipo de ordenacao (crescente = true / decrescente = false)
    //     - String $index: campo usado para indexacao dos registros da lista
    //     - Int $itens_pagina: Numero maximo de registros por pagina
    //     - Array[String => String] $nomes: vetor associativo com a descricao dos elementos listados. O vetor possui os seguintes indices:
    //       - String 'singular': indica o nome do elemento no singular
    //       - String 'plural': indica o nome do elemento no plural
    //       - String 'genero': indica o genero do elemento (M - masculino, F - feminino, I - indefinido)
    //     - String $ajuda: descricao da ajuda da pagina
    //     - String $texto_antes: bloco HTML que deve aparecer antes do quadro
    //     - String $texto_depois: bloco HTML que deve aparecer depois do quadro
    //     - Array[String => String || Array[String => Mixed]] $links: vetor de links a serem exibidos no rodape da lista (indexados pela descricao do link) ou com pacotes de dados dos links na forma de array (abaixo os possiveis indices)
    //       - String 'link': URL do link ou nome do arquivo no mesmo modulo
    //       - String 'modulo': Nome do modulo (usado em conjunto com $arquivo)
    //       - String 'arquivo': Nome do arquivo (usado em conjunto com o $modulo)
    //       - Bool 'ajax': Usar ajax no link
    //     - condicao_sql $condicoes: condicao base para filtrar os elementos da lista (e' unida 'as condicoes do formulario de filtragem, caso ele exista)
    //     - objeto $entidade: entidade responsavel por gerar a lista
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav e as $condicoes, caso nao sejam preenchidos)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //     $dados_form
    //     - String $funcao_form: nome da funcao que gera o formulario de filtro da lista
    //       A funcao deve receber como parametro apenas um objeto stdClass com os dados submetidos anteriormente.
    //       A funcao deve montar um formulario e imprimi-lo na tela.
    //     - String $funcao_condicoes: nome da funcao que recebe os dados do formulario e retorna o criterio de selecao de itens da lista
    //       A funcao deve receber os dados submetidos do formulario criado com a funcao "funcao_form", gerar um objeto da classe condicao_sql e retorna-lo
    //     - String $funcao_ordem: nome da funcao que recebe os dados do formulario e retorna o criterio de ordenacao do resultado
    //       A funcao deve receber os dados submetidos do formulario criado com a funcao "funcao_form", gerar um vetor com os campos usados para ordenacao apontando para o tipo de ordenacao (true = crescente / false = decrescente)
    //     - Bool $exibir_lista: indica se a lista de resultados deve ser exibida sempre ou apenas quando ativado o formulario montado pela funcao "funcao_form"
    //
    static public function listar_entidades($classe, $dados_lista = false, $dados_pagina = false, $dados_form = false) {
    // String $classe: nome da classe
    // Object $dados_lista: dados opcionais ($id_lista, $opcoes, $campos, $campos_consultar, $index, $itens_pagina, $nomes, $ordem, $ajuda, $texto_antes, $texto_depois, $links, $condicao, $entidade)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    // Object $dados_form: dados opcionais ($funcao_form, $funcao_condicoes, $funcao_ordem)
    //
        global $CFG, $USUARIO;

        if (!$dados_lista)  { $dados_lista  = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }
        if (!$dados_form)   { $dados_form   = new stdClass(); }

        if (isset($dados_lista->entidade)) {
            $entidade = &$dados_form->entidade;
        } else {
            $entidade = &self::criar_entidade($classe);
        }
        self::checar_classe($entidade, 'objeto');

        // Dados da lista
        $arquivo          = util::get_arquivo();
        $modulo           = util::get_modulo($arquivo);
        $modulo_pai       = util::get_modulo_pai($modulo);
        $modulo_rel       = util::get_modulo($arquivo, false);
        $opcoes           = isset($dados_lista->opcoes)           ? $dados_lista->opcoes           : array('exibir', 'alterar', 'excluir');
        $campos           = isset($dados_lista->campos)           ? $dados_lista->campos           : false;
        $campos_consultar = isset($dados_lista->campos_consultar) ? $dados_lista->campos_consultar : false;
        $index            = isset($dados_lista->index)            ? $dados_lista->index            : false;
        $itens_pagina     = isset($dados_lista->itens_pagina)     ? $dados_lista->itens_pagina     : false;
        $nomes            = isset($dados_lista->nomes)            ? $dados_lista->nomes            : false;
        $ordem            = isset($dados_lista->ordem)            ? $dados_lista->ordem            : false;
        $id_lista         = isset($dados_lista->id_lista)         ? $dados_lista->id_lista         : 'lista_'.$classe;

        // Obter link
        $link             = $CFG->site;
        $remover = array('op', $entidade->get_chave());
        link::normalizar($link, $remover);

        $texto_antes      = isset($dados_lista->texto_antes) ? $dados_lista->texto_antes : '';
        $texto_depois     = isset($dados_lista->texto_depois) ? $dados_lista->texto_depois : '';
        if (!isset($dados_lista->links)) {
            $dados_lista->links = array('inserir.php', 'importar_csv.php', 'importar_xml.php');
        }
        $links = array();
        if (is_array($dados_lista->links)) {
            $i = 1;
            foreach ($dados_lista->links as $descricao => $l) {

                // Tipo de link (absoluto 1 / relativo 0)
                $tipo = 0;

                // Se passou um pacote de dados
                if (is_array($l)) {
                    $usar_ajax = isset($l['ajax']) ? $l['ajax'] : true;

                    // Se informou 'link'
                    if (isset($l['link'])) {

                         // Absoluto
                         if (strpos($l['link'], 'http://') !== false) {
                             $tipo = 1;
                             $link_absoluto = $l['link'];

                         // Relativo
                         } else {
                             $tipo = 0;
                             $class = self::get_class_link($l['link']);
                             $arquivo_link = $l['link'];
                             $modulo_link = isset($l['modulo']) ? $l['modulo'] : $modulo;
                         }

                    // Se informou 'arquivo' e 'modulo'
                    } elseif (isset($l['arquivo']) && isset($l['modulo'])) {
                         $tipo = 0;
                         $class = self::get_class_link($l['arquivo']);
                         $arquivo_link = $l['arquivo'];
                         $modulo_link = $l['modulo'];
                    }

                // Se passou uma string
                } else {

                    // Absoluto
                    if (strpos($l, 'http://') !== false) {
                        $tipo = 1;
                        $link_absoluto = $l;
                        $class = 'op'.$i;
                        $usar_ajax = true;

                    // Relativo
                    } else {
                        $tipo = 0;
                        $class = self::get_class_link($l);
                        $arquivo_link = $l;
                        $modulo_link = $modulo;
                        $usar_ajax = true;
                    }
                }

                // Link absoluto
                if ($tipo == 1) {
                    $links[] = link::texto($link_absoluto, $descricao, false, false, $class, true, false, false, $usar_ajax);

                // Link relativo
                } else {
                    $class = self::get_class_link($l);
                    $l = link::arquivo_modulo($USUARIO, $arquivo_link, $modulo_link, false, '', $class, true, true, true, $usar_ajax);
                    if ($l) {
                        $links[] = $l;
                    }
                }
                $i++;
            }
        }
        if (isset($dados_lista->ajuda)) {
            $ajuda = &$dados_lista->ajuda;
        } else {
            $ajuda = "<p>A tabela a seguir apresenta a lista de ".texto::codificar($entidade->get_entidade(1))." registrados no sistema.</p>";
            if ($opcoes) {
                $vt_nomes_opcoes = array('exibir'  => 'exibir',
                                         'alterar' => 'alterar',
                                         'excluir' => 'excluir');
                foreach ($opcoes as $opcao) {
                    if (isset($vt_nomes_opcoes[$opcao])) {
                        $vt_opcoes[] = $vt_nomes_opcoes[$opcao];
                    }
                }
                if (count($vt_opcoes > 1)) {
                    $ultima = array_pop($vt_opcoes);
                    $st_opcoes = implode(', ', $vt_opcoes).' e '.$ultima.' os dados ';
                    switch ($entidade->get_genero()) {
                    case 'M':
                        $st_opcoes .= 'dos '.$entidade->get_entidade(1).'.';
                        break;
                    case 'F':
                        $st_opcoes .= 'das '.$entidade->get_entidade(1).'.';
                        break;
                    case 'I':
                        $st_opcoes .= 'dos(as) '.$entidade->get_entidade(1).'.';
                        break;
                    }
                    $ajuda .= "<p>As op&ccedil;&otilde;es possiveis s&atilde;o: {$st_opcoes}</p>";
                } else {
                    switch ($entidade->get_genero()) {
                    case 'M':
                        $ajuda .= "<p>A &uacute;nica op&ccedil;&atilde;o poss&iacute;vel &eacute; {$ultima} um ".$entidade->get_entidade().'</p>';
                        break;
                    case 'F':
                        $ajuda .= "<p>A &uacute;nica op&ccedil;&atilde;o poss&iacute;vel &eacute; {$ultima} uma ".$entidade->get_entidade().'</p>';
                        break;
                    case 'I':
                        $ajuda .= "<p>A &uacute;nica op&ccedil;&atilde;o poss&iacute;vel &eacute; {$ultima} um(a) ".$entidade->get_entidade().'</p>';
                        break;
                    }
                }
            }
        }

        // Listar apenas entidades filhas da entidade pai
        if (!isset($dados_lista->condicoes) && isset($dados_pagina->submodulo)) {
            $pos = strpos($dados_pagina->submodulo, ':');

            $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
            $classe_pai = $obj_pai->get_classe();
            if ($pos !== false) {
                $chave_pai = substr($dados_pagina->submodulo, 0, $pos).':'.$obj_pai->get_chave();
            } else {
                $chave_pai = $obj_pai->get_chave();
            }
            $valor_chave_pai = self::get_chave_session($classe_pai);
            if (!$valor_chave_pai) {
                header('Location: '.$CFG->wwwroot);
                exit(1);
            }
            $dados_lista->condicoes = condicao_sql::montar($chave_pai, '=', $valor_chave_pai);
        }

        // Dados da pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Dados do formulario de filtragem dos dados
        $funcao_form = isset($dados_form->funcao_form) ? $dados_form->funcao_form : false;
        $funcao_condicoes = isset($dados_form->funcao_condicoes) ? $dados_form->funcao_condicoes : false;
        $funcao_ordem = isset($dados_form->funcao_ordem) ? $dados_form->funcao_ordem : false;
        $exibir_lista = isset($dados_form->exibir_lista) ? $dados_form->exibir_lista : false;
        $dados = formulario::get_dados();
        if ($dados) {
            $paginacao = new paginacao($modulo, $id_lista);
            $paginacao->salvar_pagina(1);
        }

        // Exibir a pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        if ($funcao_form) {
            if (!$dados && isset($_SESSION[$modulo]['dados_lista'][$id_lista])) {
                $dados = unserialize($_SESSION[$modulo]['dados_lista'][$id_lista]);
            }
            $funcao_form($dados);
            if ($dados) {
                if (isset($dados_lista->condicoes)) {
                    $vt_condicoes = array();
                    $vt_condicoes[] = $funcao_condicoes($dados);
                    $vt_condicoes[] = $dados_lista->condicoes;
                    $condicoes = condicao_sql::sql_and($vt_condicoes);
                } else {
                    $condicoes = $funcao_condicoes($dados);
                }
                if ($funcao_ordem) {
                    $ordem = $funcao_ordem($dados);
                }
                $exibir_lista = true;
            } elseif (isset($dados_lista->condicoes)) {
                $condicoes = $dados_lista->condicoes;
            } else {
                $condicoes = condicao_sql::vazia();
            }
            if ($exibir_lista) {
                if ($texto_antes) {
                    echo $texto_antes;
                }
                $entidade->imprimir_lista($condicoes, $modulo, $id_lista, $link, $opcoes, $campos, $ordem, $index, $itens_pagina, $campos_consultar, $nomes);
                if ($texto_depois) {
                    echo $texto_depois;
                }
                $_SESSION[$modulo]['dados_lista'][$id_lista] = serialize($dados);
            }
        } else {
            $condicoes = isset($dados_lista->condicoes) ? $dados_lista->condicoes : condicao_sql::vazia();
            if ($texto_antes) {
                echo $texto_antes;
            }
            $entidade->imprimir_lista($condicoes, $modulo, $id_lista, $link, $opcoes, $campos, $ordem, $index, $itens_pagina, $campos_consultar);
            if ($texto_depois) {
                echo $texto_depois;
            }
        }
        if ($links) {
            $pagina->listar_opcoes($links);
        }
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de inserir entidades (inserir.php)
    //     $dados_form
    //     - Array[Array || String] $campos: vetor de campos do formulario de inserir
    //     - Object $dados: dados a serem preenchidos por padrao
    //     - String $prefixo: prefixo do ID do formulario
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de insercao.
    //     - Array[String => Mixed] $opcoes: atributos a serem setados no objeto automaticamente
    //     - String $ajuda: ajuda do formulario
    //     - String $class: classe CSS do formulario
    //     - Bool $ajax: usar ajax no formulario
    //     - objeto_formulario $entidade: entidade envolvida na insercao
    //     - Bool $outro: incluir link para cadastrar outro
    //     - String $nome_botao: nome do botao do formulario
    //     - Int $modo_transacao: tipo de transacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido, e o vetor $opcoes com a chave da sessao)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function inserir($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $dados, $prefixo, $funcao_operacoes, $opcoes, $ajuda, $class, $ajax, $entidade, $outro, $nome_botao, $modo_transacao)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $dados_pagina, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
        } else {
            $entidade = &self::criar_entidade($classe);
        }
        self::checar_classe($entidade);

        // Dados do formulario
        $arquivo    = util::get_arquivo();
        $modulo     = util::get_modulo($arquivo);
        $modulo_pai = util::get_modulo_pai($modulo);
        $modulo_rel = util::get_modulo($arquivo, false);
        $dados      = formulario::get_dados();
        if (!$dados && isset($dados_form->dados)) {
            $dados = $dados_form->dados;
        }
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : true;
        $opcoes         = isset($dados_form->opcoes)         ? $dados_form->opcoes         : array();
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $ajax           = isset($dados_form->ajax)           ? $dados_form->ajax           : true;
        $outro          = isset($dados_form->outro)          ? $dados_form->outro          : true;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        // Incluir chave FK em opcoes, caso nao tenha preenchido opcoes
        if (isset($dados_pagina->submodulo) && !isset($dados_form->opcoes)) {
            $todos_campos = array_merge($campos, array_keys($opcoes));
            $nome_obj_pai = $dados_pagina->submodulo;
            $pos = strpos($nome_obj_pai, ':');

            $obj_pai = $entidade->get_objeto_rel_uu($nome_obj_pai);
            if ($pos !== false) {
                $chave_fk = substr($nome_obj_pai, 0, $pos).':'.$entidade->get_nome_chave_rel_uu($nome_obj_pai);
            } else {
                $chave_fk = $entidade->get_nome_chave_rel_uu($nome_obj_pai);
            }
            $classe_pai = $obj_pai->get_classe();
            $chave_pai = $obj_pai->get_chave();
            $valor_chave_pai = self::get_chave_session($classe_pai);
            if (!$valor_chave_pai) {
                header('Location: '.$CFG->wwwroot);
                exit(1);
            }
            if ($campos !== true &&
                !in_array($nome_obj_pai, $todos_campos) &&
                !in_array($chave_fk, $todos_campos)
               ) {
                $opcoes[$chave_fk] = self::get_chave_session($classe_pai);
            }
        }
        $prefixo    = isset($dados_form->prefixo) ? $dados_form->prefixo : '';
        $action     = isset($dados_form->action)  ? $dados_form->action  : $CFG->site;
        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            switch ($entidade->get_genero()) {
            case 'M':
                $novos = 'novos';
                break;
            case 'F':
                $novos = 'novas';
                break;
            case 'I':
                $novos = 'novos(as)';
                break;
            }
            $ajuda = "<p>Este formul&aacute;rio &eacute; respons&aacute;vel pela cria&ccedil;&atilde;o de {$novos} ".$entidade->get_entidade(1)." no sistema.</p>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Inserir dados opcionais
        if ($opcoes) {
            foreach ($opcoes as $cod => $valor) {
                $entidade->__set($cod, $valor);
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_INSERIR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_inserir($dados, $campos, $action, $prefixo, $opcoes, $class, $ajax, $outro, $nome_botao, $modo_transacao);
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de alterar entidades (alterar.php)
    //     $dados_form
    //     - Array[Array || String] $campos: vetor de campos do formulario de alterar
    //     - String $prefixo: prefixo do ID do formulario de alterar
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de alteracao.
    //     - Array[String => Mixed] $opcoes: atributos a serem setados no objeto automaticamente
    //     - String $class: classe CSS do formulario
    //     - Bool $ajax: usar ajax no formulario
    //     - String $nome_botao: nome do botao do formulario
    //     - objeto_formulario $entidade: entidade envolvida na alteracao
    //     - Int $modo_transacao: tipo de transacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function alterar($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $prefixo, $funcao_operacoes, $opcoes, $class, $ajax, $nome_botao, $entidade, $modo_transacao)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : true;
        $opcoes         = isset($dados_form->opcoes)         ? $dados_form->opcoes         : array();
        $prefixo        = isset($dados_form->prefixo)        ? $dados_form->prefixo        : '';
        $action         = isset($dados_form->action)         ? $dados_form->action         : $CFG->site;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $ajax           = isset($dados_form->ajax)           ? $dados_form->ajax           : true;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
            $entidade->consultar_campos($campos);
        } else {
            $entidade = util::get_entidade($classe, $campos);
        }
        self::checar_classe($entidade);

        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $ajuda = "<p>Este formul&aacute;rio &eacute; respons&aacute;vel pela altera&ccedil;&atilde;o de ".$entidade->get_entidade(1)." no sistema.</p>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Inserir dados opcionais
        if ($opcoes) {
            foreach ($opcoes as $cod => $valor) {
                $entidade->__set($cod, $valor);
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_ALTERAR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_alterar($dados, $campos, $action, $prefixo, $opcoes, $class, $ajax, $nome_botao, $modo_transacao);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de excluir entidades (excluir.php)
    //     $dados_form
    //     - Array[Array || String] $campos: vetor de campos exibidos para confirmacao de exclusao
    //     - String $prefixo: prefixo do ID do formulario de excluir
    //     - String $class: nome da Classe CSS
    //     - Bool $ajax: usar ajax no formulario ou nao
    //     - String $nome_botao: nome do botao do formulario
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de exclusao.
    //     - objeto_formulario $entidade: entidade envolvida na exclusao
    //     - String $ajuda: ajuda do formulario
    //     - Int $modo_transacao: tipo de transacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function excluir($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $prefixo, $class, $ajax, $nome_botao, $funcao_operacoes, $ajuda, $modo_transacao, $entidade)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : true;
        $prefixo        = isset($dados_form->prefixo)        ? $dados_form->prefixo        : '';
        $action         = isset($dados_form->action)         ? $dados_form->action         : $CFG->site;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $ajax           = isset($dados_form->ajax)           ? $dados_form->ajax           : true;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        $flag = OBJETO_ADICIONAR_NOMES | OBJETO_ADICIONAR_CHAVES;
        $campos_reais = objeto::get_objeto($classe)->get_campos_reais($campos, $objetos, $vetores, $flag);
        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
            $entidade->consultar_campos($campos_reais);
        } else {
            $entidade = util::get_entidade($classe, $campos_reais);
        }
        self::checar_classe($entidade);

        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $ajuda = '<p>Este formul&aacute;rio destina-se a exclus&atilde;o de '.$entidade->get_entidade(1).' do sistema.</p>'.
                     '<p><strong>Aten&ccedil;&atilde;o:</strong> Os dados n&atilde;o poder&atilde;o ser recuperados ap&oacute;s a confirma&ccedil;&atilde;o.</p>';
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_EXCLUIR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_excluir($dados, $campos, $action, $prefixo, $class, $ajax, $nome_botao, $modo_transacao);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera um formulario de relacionamento
    //     $dados_form
    //     - String $nome_vetor: nome do vetor para listar os elementos
    //     - String $classe_relacionada: nome da classe do objeto a ser relacionado
    //     - String $prefixo: prefixo do ID do formulario
    //     - Array[String] $campos: campos a serem consultados da entidade
    //     - condicao_sql $condicoes: condicoes de filtro dos elementos do vetor
    //     - Array[String] $disable: vetor de itens desabilitados
    //     - String $class: nome da Classe CSS
    //     - Bool $ajax: usar ajax no formulario ou nao
    //     - String $nome_botao: nome do botao do formulario
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de relacionamento.
    //     - String $ajuda: ajuda do formulario
    //     - objeto_formulario $entidade: entidade envolvida na alteracao
    //     - Int $modo_transacao: tipo de transacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function relacionamento($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($nome_vetor, $classe_relacionada, $prefixo, $condicoes, $disable, $class, $ajax, $nome_botao, $funcao_operacoes, $ajuda, $entidade, $modo_transacao)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : true;
        $condicoes      = isset($dados_form->condicoes)      ? $dados_form->condicoes      : condicao_sql::vazia();
        $disable        = isset($dados_form->disable)        ? $dados_form->disable        : array();
        $prefixo        = isset($dados_form->prefixo)        ? $dados_form->prefixo        : '';
        $action         = isset($dados_form->action)         ? $dados_form->action         : $CFG->site;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $ajax           = isset($dados_form->ajax)           ? $dados_form->ajax           : true;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
            $entidade->consultar_campos($campos);
        } else {
            $entidade = util::get_entidade($classe, $campos);
        }
        self::checar_classe($entidade);

        // Nome do vetor relacionado
        if (isset($dados_form->nome_vetor)) {
            $nome_vetor = $dados_form->nome_vetor;
        } else {
            $vetores = $entidade->get_definicoes_rel_un();
            if (count($vetores) == 1) {
                $nome_vetor = array_pop(array_keys($vetores));
            } else {
                trigger_error('A classe '.$classe.' possui mais de um relacionamento 1:1', E_USER_ERROR);
            }
        }
        if (!$entidade->possui_rel_un($nome_vetor)) {
            trigger_error('A classe '.$classe.' nao possui o vetor "'.$nome_vetor.'"', E_USER_ERROR);
        }
        $def_un = $entidade->get_definicao_rel_un($nome_vetor);
        $classe_un = $def_un->classe;
        $entidade_rel = new $classe_un();

        // Classe relacionada
        if (isset($dados_form->classe_relacionada)) {
            $classe_relacionada = $dados_form->classe_relacionada;
        } else {
            $objetos = $entidade_rel->get_definicoes_rel_uu();
            if (count($objetos) == 2) {
                foreach ($objetos as $chave => $def_uu) {
                    if ($def_uu->classe == $classe) { continue; }
                    $classe_relacionada = $def_uu->classe;
                }
            } else {
                trigger_error('A classe '.$classe_un.' nao possui exatamente dois objetos relacionados', E_USER_ERROR);
            }
        }

        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $ajuda = "<p>Este formul&aacute;rio &eacute; respons&aacute;vel pela associa&ccedil;&atilde;o entre ".$entidade->get_entidade(true)." e ".$entidade_rel->get_entidade(true).".</p>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_ALTERAR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_relacionamento($dados, $action, $nome_vetor, $classe_relacionada, $prefixo, $condicoes, $disable, $class, $ajax, $nome_botao, $modo_transacao);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de alterar entidades mediante uma confirmacao
    //     $dados_form
    //     - Array[Array || String] $campos: vetor de campos do formulario
    //     - String $prefixo: prefixo do ID do formulario de alterar
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de alteracao.
    //     - String $mensagem: mensagem de confirmacao
    //     - Array[String => Mixed] $opcoes: atributos a serem setados no objeto automaticamente
    //     - Array[Array || String] $campos_exibir: lista de campos a serem exibidos para confirmacao
    //     - String $class: classe CSS do formulario
    //     - Bool $ajax: usar ajax no formulario
    //     - String $nome_botao: nome do botao do formulario
    //     - objeto_formulario_confirmacao $entidade: entidade envolvida na alteracao
    //     - Int $modo_transacao: tipo de transacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function confirmar($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $prefixo, $funcao_operacoes, $mensagem, $opcoes, $campos_exibir, $class, $ajax, $nome_botao, $entidade, $modo_transacao)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $mensagem       = isset($dados_form->mensagem)       ? $dados_form->mensagem       : 'Marque para confirmar';
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : array();
        $campos_exibir  = isset($dados_form->campos_exibir)  ? $dados_form->campos_exibir  : null;
        $opcoes         = isset($dados_form->opcoes)         ? $dados_form->opcoes         : array();
        $prefixo        = isset($dados_form->prefixo)        ? $dados_form->prefixo        : '';
        $action         = isset($dados_form->action)         ? $dados_form->action         : $CFG->site;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $ajax           = isset($dados_form->ajax)           ? $dados_form->ajax           : true;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
        } else {
            $entidade = util::get_entidade($classe, $campos);
        }
        self::checar_classe($entidade, 'objeto_formulario_confirmacao');

        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $ajuda = "<p>Este formul&aacute;rio &eacute; respons&aacute;vel pela altera&ccedil;&atilde;o de ".$entidade->get_entidade(1)." mediante uma confirma&ccedil;&atilde;o.</p>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_ALTERAR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_confirmar($dados, $mensagem, $campos, $opcoes, $action, $prefixo, $class, $ajax, $nome_botao, $modo_transacao);
        if ($campos_exibir) {
            $entidade->imprimir_dados($campos_exibir);
        }
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de importar entidades via CSV (importar_csv.php)
    //     $dados_form
    //     - Array[String] $campos: campos obrigatorios no arquivo
    //     - Array[String => Mixed] $opcoes: dados a serem inseridos automaticamente em cada registro
    //     - String $prefixo: prefixo do ID do formulario de importar
    //     - String $class: classe CSS do formulario
    //     - String $nome_botao: nome do botao do formulario
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de importacao.
    //     - String $ajuda: ajuda do formulario
    //     - Int $modo_transacao: tipo de transacao
    //     - objeto_formulario $entidade: entidade responsavel por realizar a importacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function importar_csv($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $prefixo, $class, $nome_botao, $funcao_operacoes, $ajuda, $modo_transacao, $entidade)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
        } else {
            $entidade = &self::criar_entidade($classe);
        }
        self::checar_classe($entidade);

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $arquivos       = formulario::get_arquivos();
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : false;
        $opcoes         = isset($dados_form->opcoes)         ? $dados_form->opcoes         : false;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        // Incluir chave FK em opcoes, caso nao tenha preenchido opcoes
        if (isset($dados_pagina->submodulo) && !isset($dados_form->opcoes)) {
            $todos_campos = array_merge($campos, array_keys($opcoes));
            $nome_obj_pai = $dados_pagina->submodulo;
            $pos = strpos($nome_obj_pai, ':');

            $classe_pai = $obj_pai->get_classe();
            $obj_pai = $entidade->get_objeto_rel_uu($nome_obj_pai);
            if ($pos !== false) {
                $prefixo_chave = substr($nome_obj_pai, 0, $pos).':';
                $chave_fk = $prefixo_chave.$entidade->get_nome_chave_rel_uu($nome_obj_pai);
                $chave_pai = $prefixo.$obj_pai->get_chave();
            } else {
                $chave_fk = $entidade->get_nome_chave_rel_uu($nome_obj_pai);
                $chave_pai = $obj_pai->get_chave();
            }
            $valor_chave_pai = self::get_chave_session($classe_pai);
            if (!$valor_chave_pai) {
                header('Location: '.$CFG->wwwroot);
                exit(1);
            }
            if ($campos !== true &&
                !in_array($nome_obj_pai, $todos_campos) &&
                !in_array($chave_fk, $todos_campos)
               ) {
                $opcoes[$chave_fk] = self::get_chave_session($classe_pai);
            }
        }

        $prefixo    = isset($dados_form->prefixo) ? $dados_form->prefixo : '';
        $action     = isset($dados_form->action)  ? $dados_form->action  : $CFG->site;
        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $st_campos = $campos ? implode(', ', $campos) : '(nenhum)';
            $ajuda = "<p>Este formul&aacute;rio destina-se a importa&ccedil;&atilde;o de ".$entidade->get_entidade(1)." no sistema.</p>".
                     "<p>O formato do arquivo deve ser <acronym title=\"Comma-separated Values\">CSV</acronym> contendo os campos: ".
                     "{$st_campos}.</p><p>O separador padr&atilde;o e as aspas ".
                     "podem ser alterados de acordo com as caracter&iacute;sticas do arquivo.</p>".
                     "<p>Importar diretamente para o BD significa que os dados n&atilde;o passar&atilde;o por ".
                     "uma valida&ccedil;&atilde;o, logo ser&atilde;o inseridos da forma como est&atilde;o no arquivo.</p>";
            $ajuda .= "<p>Lista de campos e tipos:</p>";
            $ajuda .= "<ul>";
            foreach ($entidade->get_atributos() as $nome => $def) {
                if ($def->chave == 'PK') {
                    continue;
                }
                $ajuda .= "<li>{$nome} ({$def->tipo})</li>";
            }
            $ajuda .= "</ul>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_importar_csv($dados, $arquivos, $action, $prefixo, $campos, $opcoes, $class, $nome_botao, $modo_transacao);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de importar entidades via XML (importar_xml.php)
    //     $dados_form
    //     - Array[String] $campos: campos obrigatorios no arquivo
    //     - Array[String => Mixed] $opcoes: dados a serem inseridos automaticamente em cada registro
    //     - String $prefixo: prefixo do ID do formulario de importar
    //     - String $class: classe CSS do formulario
    //     - String $nome_botao: nome do botao do formulario
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o formulario
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o formulario de importacao.
    //     - String $ajuda: ajuda do formulario
    //     - Int $modo_transacao: tipo de transacao
    //     - objeto_formulario $entidade: entidade responsavel por realizar a importacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function importar_xml($classe, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_form: dados opcionais ($campos, $prefixo, $class, $nome_botao, $funcao_operacoes, $ajuda, $modo_transacao, $entidade)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
        } else {
            $entidade = &self::criar_entidade($classe);
        }
        self::checar_classe($entidade);

        // Dados do formulario
        $arquivo        = util::get_arquivo();
        $modulo         = util::get_modulo($arquivo);
        $modulo_pai     = util::get_modulo_pai($modulo);
        $modulo_rel     = util::get_modulo($arquivo, false);
        $dados          = formulario::get_dados();
        $arquivos       = formulario::get_arquivos();
        $campos         = isset($dados_form->campos)         ? $dados_form->campos         : false;
        $opcoes         = isset($dados_form->opcoes)         ? $dados_form->opcoes         : false;
        $class          = isset($dados_form->class)          ? $dados_form->class          : false;
        $nome_botao     = isset($dados_form->nome_botao)     ? $dados_form->nome_botao     : false;
        $modo_transacao = isset($dados_form->modo_transacao) ? $dados_form->modo_transacao : DRIVER_BASE_MODO_PADRAO;

        // Incluir chave FK em opcoes, caso nao tenha preenchido opcoes
        if (isset($dados_pagina->submodulo) && !isset($dados_form->opcoes)) {
            $todos_campos = array_merge($campos, array_keys($opcoes));
            $nome_obj_pai = $dados_pagina->submodulo;
            $pos = strpos($nome_obj_pai, ':');

            $obj_pai = $entidade->get_objeto_rel_uu($nome_obj_pai);
            $classe_pai = $obj_pai->get_classe();
            if ($pos !== false) {
                $prefixo_chave = substr($nome_obj_pai, 0, $pos).':';
                $chave_fk  = $prefixo_chave.$entidade->get_nome_chave_rel_uu($nome_obj_pai);
                $chave_pai = $prefixo_chave.$obj_pai->get_chave();
            } else {
                $chave_fk = $entidade->get_nome_chave_rel_uu($nome_obj_pai);
                $chave_pai = $obj_pai->get_chave();
            }
            $valor_chave_pai = self::get_chave_session($classe_pai);
            if (!$valor_chave_pai) {
                header('Location: '.$CFG->wwwroot);
                exit(1);
            }
            if ($campos !== true &&
                !in_array($nome_obj_pai, $todos_campos) &&
                !in_array($chave_fk, $todos_campos)
               ) {
                $opcoes[$chave_fk] = self::get_chave_session($classe_pai);
            }
        }

        $prefixo    = isset($dados_form->prefixo) ? $dados_form->prefixo : '';
        $action     = isset($dados_form->action)  ? $dados_form->action  : $CFG->site;
        if (isset($dados_form->ajuda)) {
            $ajuda = &$dados_form->ajuda;
        } else {
            $st_campos = $campos ? implode(', ', $campos) : '(nenhum)';
            $formato = '<'.$entidade->get_tabela().">\n";
            foreach ($entidade->get_atributos() as $a) {
                $formato .= '  <'.$a->nome.'>'.$a->descricao.'</'.$a->nome.">\n";
            }
            $formato .= '</'.$entidade->get_tabela().">\n";

            $ajuda = "<p>Este formul&aacute;rio destina-se a importa&ccedil;&atilde;o de ".$entidade->get_entidade(1)." no sistema.</p>".
                     "<p>O formato do arquivo deve ser <acronym title=\"eXtensible Markup Language\">XML</acronym> contendo os campos: ".
                     "{$st_campos}.</p><p>A estrutura do XML deve seguir o modelo:</p><pre>".texto::codificar($formato).'</pre>'.
                     "<p>Importar diretamente para o BD significa que os dados n&atilde;o passar&atilde;o por ".
                     "uma valida&ccedil;&atilde;o, logo ser&atilde;o inseridos da forma como est&atilde;o no arquivo.</p>";
            $ajuda .= "<p>Lista de campos e tipos:</p>";
            $ajuda .= "<ul>";
            foreach ($entidade->get_atributos() as $nome => $def) {
                if ($def->chave == 'PK') {
                    continue;
                }
                $ajuda .= "<li>{$nome} ({$def->tipo})</li>";
            }
            $ajuda .= "</ul>";
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        $entidade->formulario_importar_xml($dados, $arquivos, $action, $prefixo, $campos, $opcoes, $class, $nome_botao, $modo_transacao);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina de exibir uma entidade (exibir.php)
    //     $dados_quadro
    //     - Array[String => Array || String] $campos: vetor de campos a serem exibidos no quadro
    //     - String $funcao_operacoes: nome da funcao que recebe a entidade e realiza operacoes antes de exibir o quadro
    //       A funcao deve receber um objeto da classe especificada pelo primeiro parametro deste metodo e realizar operacoes.
    //       Ela pode ser util para atribuir valores ao objeto antes de ser apresentado o quadro.
    //     - String $texto_antes: bloco HTML que deve aparecer antes do quadro
    //     - String $texto_depois: bloco HTML que deve aparecer depois do quadro
    //     - objeto $entidade: entidade envolvida na exibicao dos dados
    //     - String $ajuda: ajuda do quadro
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function exibir($classe, $dados_quadro = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // Object $dados_quadro: dados opcionais ($campos, $funcao_operacoes, $texto_antes, $texto_depois, $entidade, $ajuda)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_quadro) { $dados_quadro = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do quadro
        $arquivo      = util::get_arquivo();
        $modulo       = util::get_modulo($arquivo);
        $modulo_pai = util::get_modulo_pai($modulo);
        $modulo_rel   = util::get_modulo($arquivo, false);
        $campos       = isset($dados_quadro->campos) ? $dados_quadro->campos  : true;
        $texto_antes  = isset($dados_quadro->texto_antes) ? $dados_quadro->texto_antes : '';
        $texto_depois = isset($dados_quadro->texto_depois) ? $dados_quadro->texto_depois : '';

        $flag = OBJETO_ADICIONAR_NOMES | OBJETO_ADICIONAR_CHAVES;
        $campos_reais = objeto::get_objeto($classe)->get_campos_reais($campos, $objetos, $vetores, $flag);
        if (isset($dados_quadro->entidade)) {
            $entidade = $dados_quadro->entidade;
            $entidade->consultar_campos($campos_reais);
        } else {
            $entidade = util::get_entidade($classe, $campos_reais);
        }
        self::checar_classe($entidade, 'objeto');

        if (isset($dados_quadro->ajuda)) {
            $ajuda = &$dados_quadro->ajuda;
        } else {
            $ajuda = false;
        }

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_EXIBIR);
            exit(1);
        }
        if (isset($dados_quadro->funcao_operacoes) && function_exists($dados_quadro->funcao_operacoes)) {
            call_user_func($dados_quadro->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if ($ajuda) {
            mensagem::comentario($CFG->site, $ajuda);
        }
        echo $texto_antes;
        if (is_array($campos)) {
            $entidade->imprimir_dados($campos);
        } else {
            $entidade->imprimir_dados($campos, false, false);
        }
        echo $texto_depois;
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina com formulario generico
    //     $dados_form
    //     - Array[String => Array || String] $campos: campos apresentados no formulario
    //     - String $prefixo: prefixo do ID do formulario
    //     - String $funcao_operacoes: nome da funcao que realiza operacoes antes de exibir o formulario ou quadro de dados
    //       A funcao deve apenas validar os dados, executar a logica e preencher o vetor de erros ou avisos.
    //       Em geral, ela nao deve imprimir nada.
    //       Sao esperados os seguintes parametros na assinatura da funcao:
    //       - $dados: dados submetidos (obrigatorio)
    //       - $campos: campos solicitados no formulario (caso necessario)
    //       - $opcoes: campos de opcoes adicionais (caso necessario)
    //     - Array[String => Mixed] $opcoes: vetor de atributos a serem definidos automaticamente na entidade
    //     - String $class: classe CSS do formulario
    //     - Bool $ajax: indica se o formulario utiliza ajax ou nao
    //     - String $nome_botao: nome do botao de submeter os dados
    //     - String $destino_formulario: destino do formulario ('imprimir_dados' ou 'imprimir_formulario')
    //     - String $ajuda: ajuda do formulario
    //     - objeto_formulario $entidade: entidade envolvida na operacao
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function formulario($classe, $metodo, $dados_form = false, $dados_pagina = false) {
    // String $classe: nome da classe
    // String $metodo: nome do metodo que processara os dados enviados (recebe por parametro $dados, $vt_campos e $opcoes)
    // Object $dados_form: dados opcionais ($campos, $prefixo, $funcao_operacoes, $opcoes, $class, $ajax, $nome_botao, $destino_formulario, $ajuda, $entidade)
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    //
        global $CFG, $USUARIO;
        if (!$dados_form)   { $dados_form   = new stdClass(); }
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        // Dados do formulario
        $arquivo    = util::get_arquivo();
        $modulo     = util::get_modulo($arquivo);
        $modulo_pai = util::get_modulo_pai($modulo);
        $modulo_rel = util::get_modulo($arquivo, false);
        $dados      = formulario::get_dados();
        $campos     = isset($dados_form->campos)     ? $dados_form->campos     : true;
        $opcoes     = isset($dados_form->opcoes)     ? $dados_form->opcoes     : array();
        $class      = isset($dados_form->class)      ? $dados_form->class      : false;
        $ajax       = isset($dados_form->ajax)       ? $dados_form->ajax       : true;
        $prefixo    = isset($dados_form->prefixo)    ? $dados_form->prefixo    : '';
        $action     = isset($dados_form->action)     ? $dados_form->action     : $CFG->site;
        $nome_botao = isset($dados_form->nome_botao) ? $dados_form->nome_botao : 'Enviar';
        $destino_formulario = isset($dados_form->destino_formulario) ? $dados_form->destino_formulario : false;

        if (isset($dados_form->entidade)) {
            $entidade = &$dados_form->entidade;
            $entidade->consultar_campos($campos);
        } else {
            $entidade = util::get_entidade($classe, $campos);
        }
        self::checar_classe($entidade);

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo, $entidade);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }
        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Inserir dados opcionais
        if ($opcoes) {
            foreach ($opcoes as $cod => $valor) {
                $entidade->__set($cod, $valor);
            }
        }

        // Operacoes
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_INSERIR);
            exit(1);
        }
        if (isset($dados_form->funcao_operacoes) && function_exists($dados_form->funcao_operacoes)) {
            call_user_func($dados_form->funcao_operacoes, $entidade);
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        if (isset($dados_form->ajuda)) {
            mensagem::comentario($CFG->site, $dados_form->ajuda);
        }
        $entidade->formulario_generico($dados, $campos, $action, $metodo, $prefixo, $opcoes, $class, $ajax, $nome_botao, $destino_formulario);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera uma pagina generica (em geral para listar opcoes ou para apresentar dados)
    //     A funcao ou metodo callback deve receber os seguintes parametros:
    //     - $pagina: objeto da classe pagina que gera o hipertexto
    //     - $dados: possiveis dados submetidos pela pagina
    //     - $arquivos: possiveis arquivos submetidos pela pagina
    //     - $dados_gerais: dados enviados ao callback
    //     $dados_pagina
    //     - String $id: identificador da pagina
    //     - String $titulo: titulo da pagina
    //     - Array[String] $nav: barra de navegacao
    //     - Array[String] || String $estilos: folhas de estilos CSS
    //     - Array[String] || String $scripts: scripts em JavaScript
    //     - String $submodulo: indica que e' um submodulo, deve ser informado o nome do objeto filho da classe (usado para preencher o vetor $nav, caso nao seja preenchido)
    //     - Bool $usar_abas: indica que a pagina utiliza abas e espera que existam as variaveis globais:
    //       String $id_abas: identificador das abas, caso existam
    //       Array[String => stdClass] $abas: vetor com os dados das abas, caso existam (indexado pelo identificador da aba)
    //       String $ativa: identificador da aba ativa no momento
    //
    static public function pagina($callback, $dados_pagina = false, $dados_gerais = false) {
    // callback $callback: funcao/metodo que e' chamada pela pagina
    // Object $dados_pagina: dados opcionais ($id, $titulo, $nav, $estilos, $scripts, $submodulo, $usar_abas, $id_abas, $abas, $ativa)
    // Object $dados_gerais: dados informados ao callback
    //
        global $CFG, $USUARIO;
        if (!$dados_pagina) { $dados_pagina = new stdClass(); }

        if (!is_callable($callback)) {
            trigger_error('Callback invalido', E_USER_ERROR);
            exit(1);
        }

        // Dados do formulario
        $arquivo    = util::get_arquivo();
        $modulo     = util::get_modulo($arquivo);
        $modulo_pai = util::get_modulo_pai($modulo);
        $modulo_rel = util::get_modulo($arquivo, false);
        $dados      = formulario::get_dados();
        $arquivos   = formulario::get_arquivos();

        // Dados da Pagina
        $id = isset($dados_pagina->id) ? $dados_pagina->id : null;
        $titulo = self::get_titulo($dados_pagina, $arquivo);
        $nav = self::get_nav($dados_pagina, $modulo, $arquivo);
        if (isset($dados_pagina->estilos)) {
            $estilos = &$dados_pagina->estilos;
        } elseif (file_exists($CFG->dirmods.$modulo.'/estilos.css.php')) {
            $estilos = $CFG->wwwmods.$modulo.'/estilos.css.php';
        } else {
            $estilos = false;
        }
        if (isset($dados_pagina->scripts)) {
            $scripts = $dados_pagina->scripts;
        } else {
            $scripts = false;
        }

        $usar_abas = isset($dados_pagina->usar_abas) ? $dados_pagina->usar_abas : false;
        if ($usar_abas) {
            global $id_abas, $abas, $ativa;
            if (!isset($id_abas) || !isset($abas) || !isset($ativa)) {
                trigger_error('Nao foram definidos os parametros para as abas', E_USER_ERROR);
                return false;
            }
        }

        // Imprimir Pagina
        $pagina = new pagina($id);
        $pagina->cabecalho($titulo, $nav, $estilos, $scripts);
        $pagina->imprimir_menu($USUARIO);
        $pagina->inicio_conteudo($titulo);
        if ($usar_abas) {
            $pagina->imprimir_abas($abas, $id_abas, $ativa);
        }
        call_user_func($callback, $pagina, $dados, $arquivos, $dados_gerais);
        if ($usar_abas) {
            $pagina->fechar_abas();
        }
        $pagina->fim_conteudo();
        $pagina->rodape();
        exit(0);
    }


    //
    //     Gera os estilos do modulo
    //     $opcoes
    //     - String $icone: endereco do icone usado pelo modulo (ao lado do titulo das paginas do modulo)
    //     - Bool $com_linha: usa linhas para separar os itens das listas de entidades
    //     - Bool $vertical: coloca os nomes em uma linha e as opcoes abaixo (atalho para: com_linha = true; largura_label = 100%; largura_opcoes = 100%)
    //     A largura_label e largura_opcoes referem-se 'as listas, e a soma deles deve ser 90%
    //     - String $largura_label: largura da caixa label das listas de entidades do modulo
    //     - String $largura_opcoes: largura da caixa de opcoes das listas de entidades do modulo
    //     A largura_label_form e largura_campo_form referem-se aos formularios de inserir e alterar, e a soma deles deve ser 90%
    //     - String $largura_label_form: largura do label dos formularios (inserir ou alterar)
    //     - String $largura_campo_form: largura do campo dos formularios (inserir ou alterar)
    //
    static public function estilos($classe, $opcoes = false) {
    // String $classe: nome da classe
    // Object $opcoes: dados opcionais (icone, largura_label, largura_opcoes, largura_label_form, largura_campo_form, largura_form, com_linha)
    //
        global $CFG;
        if (!$opcoes) { $opcoes = new stdClass(); }
        $entidade = &self::criar_entidade($classe);

        // Dados do documento
        $arquivo       = util::get_arquivo();
        $modulo        = util::get_modulo($arquivo);
        $modulo_pai    = util::get_modulo_pai($modulo);
        $modulo_rel    = util::get_modulo($arquivo, false);
        $nome_entidade = texto::strip_acentos(texto::decodificar($entidade->get_entidade()));

        if (!ob_get_contents()) {
            $last = filemtime($arquivo);
            header('Content-Type: text/css; charset='.$CFG->charset);
            header('Content-Disposition: inline; filename=estilos.css');
            header('Content-Language: '.$CFG->lingua);
            header('Cache-Control: public');
            header('Pragma: ');
            header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
            header('Last-Modified: '.gmstrftime($CFG->gmt, $last));
            header('Expires: '.gmstrftime($CFG->gmt, $CFG->time + TEMPO_EXPIRA));
        }

        echo "/* Estilos do modulo {$nome_entidade} */\n";
        if (isset($opcoes->icone)) {
            if (function_exists('getimagesize')) {
                $tamanho = getimagesize($opcoes->icone);
                $largura = $tamanho[0] ? ($tamanho[0] + 5).'px' : '25px';
            } else {
                $largura = '25px';
            }
            echo "#conteudo_principal h2.titulo {\n".
                 "  background: transparent url({$opcoes->icone}) 0% 60% no-repeat;\n".
                 "  padding-left: {$largura};\n".
                 "}\n";

        }

        // Listas
        if (isset($opcoes->vertical) && $opcoes->vertical) {
            $opcoes->largura_label  = '100%';
            $opcoes->largura_opcoes = '100%';
            $opcoes->com_linha      = true;
            echo ".lista .label,\n.lista .inativo { text-align: left; }\n";
        }
        if (isset($opcoes->largura_label)) {
            echo ".lista .label,\n.lista .inativo { width: {$opcoes->largura_label}; }\n";
        }
        if (isset($opcoes->com_linha)) {
            echo ".lista .linha { border-top: 1px solid #AAAAAA; }\n";
            echo ".lista > strong + div.linha { border: none; }\n";
        }
        if (isset($opcoes->largura_opcoes)) {
            echo ".lista .opcoes { width: {$opcoes->largura_opcoes}; }\n";
        }

        // Formularios
        if (isset($opcoes->largura_label_form)) {
            echo ".formulario div.campo label { width: {$opcoes->largura_label_form}; }\n";
        }
        if (isset($opcoes->largura_campo_form)) {
            echo ".formulario div.campo div { width: {$opcoes->largura_campo_form}; }\n";
        }
        if (isset($opcoes->largura_form)) {
            echo ".formulario { width: {$opcoes->largura_form}; }\n";
            echo ".formulario fieldset { width: 90%; }\n";
        }
    }


/// # METODOS PUBLICOS GERAIS


    //
    //     Obtem uma entidade via get ou via session e salva o codigo em sessao
    //
    static public function get_entidade_session($classe, $modulo = false, $campos = false) {
    // String $classe: nome da classe pai
    // String || Bool $modulo: nome do modulo ou false para obter automaticamente
    // Array[String] || Bool $campos: campos a serem consultados automaticamente, ou true para todos ou false para PK
    //
        global $USUARIO;

        try {
            simp_autoload($classe);
        } catch (Exception $e) {
            pagina::erro($USUARIO, "Classe \"{$classe}\" n&atilde;o existe ou possui erros.");
            exit(1);
        }

        /// Obter codigo da entidade pai
        $campo = objeto::get_objeto($classe)->get_chave();
        $arquivo = util::get_arquivo();
        $modulo = $modulo === false ? util::get_modulo($arquivo) : $modulo;
        if (isset($_GET[$campo])) {
            $valor_campo = util::get_dado($campo, 'int');
        } elseif (isset($_SESSION[$modulo][$campo])) {
            $valor_campo = $_SESSION[$modulo][$campo];
        } else {
            pagina::erro($USUARIO, 'Faltou informar o campo '.$campo);
        }
        $entidade = new $classe('', $valor_campo, $campos);
        if (!$entidade->pode_ser_manipulado($USUARIO)) {
            $log = new log_sistema();
            $log->inserir($USUARIO->cod_usuario, LOG_ACESSO, true, $entidade->get_valor_chave(), $entidade->get_classe(), $modulo.'/'.$arquivo);
            pagina::erro($USUARIO, ERRO_PERMISSAO);
        }
        $_SESSION[$modulo][$campo] = $valor_campo;
        return $entidade;
    }


    //
    //     Obtem o codigo de uma entidade salva em sessao com o metodo get_entidade_session
    //
    static public function get_chave_session($classe, $modulo = false) {
    // String $classe: nome da classe pai
    // String || Bool $modulo: nome do modulo ou false para obter automaticamente
    //
        try {
            simp_autoload($classe);
        } catch (Exception $e) {
            pagina::erro($USUARIO, "Classe \"{$classe}\" n&atilde;o existe ou possui erros.");
            exit(1);
        }
        $campo = objeto::get_objeto($classe)->get_chave();
        $arquivo = util::get_arquivo();
        $modulo = $modulo === false ? util::get_modulo($arquivo) : $modulo;
        if (!isset($_SESSION[$modulo][$campo])) {
            return 0;
        }
        return $_SESSION[$modulo][$campo];
    }


/// # METODOS PRIVADOS


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Cria uma entidade
    //
    static private function &criar_entidade($classe) {
    // String $classe: nome da classe
    //
        global $USUARIO;
        try {
            simp_autoload($classe);
            $entidade = new $classe();
        } catch (Exception $e) {
            pagina::erro($USUARIO, $e->getMessage());
            exit(1);
        }
        return $entidade;
    }


    //
    //     Checa se o objeto e' subclasse de objeto_formulario e aborta a execucao caso necessario
    //
    static private function checar_classe($obj, $classe = 'objeto_formulario') {
    // Mixed $obj: objeto a ser testado
    // String $classe: classe a ser avaliada
    //
        global $CFG, $USUARIO;
        if (!($obj instanceof $classe)) {
            $classe_obj = get_class($obj);
            pagina::erro($USUARIO, "A classe {$classe_obj} n&atilde;o &eacute; subclasse de \"{$classe}\"");
            exit(1);
        }
    }


    //
    //     Gera o titulo da pagina automaticamente
    //
    static private function get_titulo($dados_pagina, $arquivo, $entidade = false) {
    // stdClass $dados_pagina: dados da pagina
    // String $arquivo: caminho completo ao arquivo
    // objeto $entidade: entidade em questao
    //
        $titulo = false;
        if (isset($dados_pagina->titulo)) {
            $titulo = $dados_pagina->titulo;
        } elseif ($entidade) {
            switch (basename($arquivo)) {
            case 'index.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = $entidade->get_entidade(1).' de "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = $entidade->get_entidade(1);
                }
                break;
            case 'inserir.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = 'Cadastrar '.$entidade->get_entidade().' de "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = 'Cadastrar '.$entidade->get_entidade();
                }
                break;
            case 'alterar.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = 'Alterar '.$entidade->get_entidade().' de "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = 'Alterar '.$entidade->get_entidade();
                }
                break;
            case 'excluir.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = 'Excluir '.$entidade->get_entidade().' de "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = 'Excluir '.$entidade->get_entidade();
                }
                break;
            case 'exibir.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = 'Exibir '.$entidade->get_entidade().' de "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = 'Exibir '.$entidade->get_entidade();
                }
                break;
            case 'importar_csv.php':
            case 'importar_xml.php':
                if (isset($dados_pagina->submodulo)) {
                    $obj_pai = $entidade->get_objeto_rel_uu($dados_pagina->submodulo);
                    $classe_pai = $obj_pai->get_classe();
                    $obj_pai->consultar('', self::get_chave_session($classe_pai), array($obj_pai->get_campo_nome()));
                    $titulo = 'Importar '.$entidade->get_entidade(1).' para "'.$obj_pai->get_nome().'"';
                } else {
                    $titulo = 'Importar '.$entidade->get_entidade(1);
                }
                break;
            }
        }
        if (!$titulo) {
            $modulo = util::get_modulo($arquivo);
            $a = new arquivo();
            $a = $a->consultar_arquivo_modulo(basename($arquivo), $modulo, array('descricao'));
            $titulo = $a->descricao;
        }
        return $titulo;
    }


    //
    //     Gera a barra de navegacao automaticamente
    //
    static private function get_nav($dados_pagina, $modulo, $arquivo) {
    // stdClass $dados_pagina: dados da pagina
    // String $modulo: nome do modulo
    // String $arquivo: caminho completo ao arquivo
    //
        $nav = array();
        if (isset($dados_pagina->nav)) {
            $nav = $dados_pagina->nav;
        } else {
            $arq = basename($arquivo);
            switch ($arq) {
            case 'index.php':
                if (isset($dados_pagina->submodulo)) {
                    $m = $modulo;
                    while ($modulo_pai = util::get_modulo_pai($m)) {
                        $subnav[] = $modulo_pai.'#index.php';
                        $m = $modulo_pai;
                    }
                    $nav[] = '#index.php';
                    foreach (array_reverse($subnav) as $item) {
                        $nav[] = $item;
                    }
                    $nav[] = $modulo.'#'.$arq;
                } else {
                    $nav[] = '#index.php';
                    $nav[] = $modulo.'#'.$arq;
                }
                break;
            default:
                if (isset($dados_pagina->submodulo)) {
                    $m = $modulo;
                    $subnav = array();
                    while ($modulo_pai = util::get_modulo_pai($m)) {
                        $subnav[] = $modulo_pai.'#index.php';
                        $m = $modulo_pai;
                    }

                    $nav[] = '#index.php';
                    foreach (array_reverse($subnav) as $item) {
                        $nav[] = $item;
                    }
                    $nav[] = $modulo.'#index.php';
                    $nav[] = $modulo.'#'.$arq;
                } else {
                    $nav[] = '#index.php';
                    $nav[] = $modulo.'#index.php';
                    $nav[] = $modulo.'#'.$arq;
                }
                break;
            }
        }
        return $nav;
    }


    //
    //     Obtem a classe CSS usada pelo link
    //
    private static function get_class_link($arquivo) {
    // String $arquivo: nome do arquivo
    //
        if (preg_match('/^inserir(_[a-z]+)*\.php$/', $arquivo)) {
            return 'inserir';
        }
        if (preg_match('/^importar(_[a-z]+)*\.php$/', $arquivo)) {
            return 'importar';
        }
        return '';
    }

}//class
