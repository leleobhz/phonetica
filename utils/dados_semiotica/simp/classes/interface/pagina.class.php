<?php
//
// SIMP
// Descricao: Classe que controla o layout da pagina
// Autor: Rubens Takiguti Ribeiro && Rodrigo Pereira Moreira
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.47
// Data: 21/05/2007
// Modificado: 24/08/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Dependencias
global $CFG;

final class pagina {
    private $id_pagina;

    private static $imprimiu_cabecalho       = false;
    private static $imprimiu_menu            = false;
    private static $imprimiu_rodape          = false;
    private static $imprimiu_inicio_conteudo = false;
    private static $imprimiu_fim_conteudo    = false;
    private static $contador_abas            = 0;
    public  static $salvou_cookies           = -1;

    // Lista de arquivos RSS
    private $rss;


    //
    //     Construtor padrao
    //
    public function __construct($id_pagina = false) {
    // String $id_pagina: ID da pagina (colocado na tag body)
    //
        $this->id_pagina = $id_pagina;
        $this->rss = array();
    }


    //
    //     Destrutor padrao
    //
    public function __destruct() {
        if (self::$imprimiu_cabecalho && !self::$imprimiu_rodape) {
            $e = "Erro ao imprimir rodap&eacute;: Rodap&eacute; omitido";
            mensagem::erro($e);
            $this->rodape();
        }
    }


    //
    //     Checa se o navegador pode aplicar folha de estilos
    //
    static public function pode_css() {
        global $CFG;
        static $pode = 0;
        if ($pode !== 0) { return $pode; }

        // Se o navegador nem da suporte: nao pode
        if (!$CFG->agent->css) {
            $pode = false;
            return false;
        }

        $versao = explode('.', $CFG->agent->versao_navegador);

        // Se o navegador da suporte, checar a versao
        switch (strtolower($CFG->agent->navegador)) {
        case 'mozilla':
            $necessario = array(1, 5);
            break;
        case 'firefox':
            $necessario = array(1, 5);
            break;
        case 'ie':
            $necessario = array(4, 0);
            break;
        case 'opera':
            $necessario = array(7, 0);
            break;
        default:
            $pode = true;
            return true;
        }

        // Se a versao e' muito antiga: nao pode
        if (intval($versao[0]) < $necessario[0]) {
            $pode = false;
            return false;
        } elseif (intval($versao[0]) == $necessario[0]) {
            if (intval($versao[1]) < $necessario[1]) {
                $pode = false;
                return false;
            }
        }

        // Se nao conhece ou a versao e' compativel: pode
        $pode = true;
        return true;
    }


    //
    //     Adiciona um RSS da pagina
    //
    public function adicionar_rss($link, $descricao = '') {
    // String $link: link do Feed
    // String $descricao: descricao do Feed
    //
        $this->rss[$link] = $descricao;
    }


    //
    //     Imprime o cabecalho dos arquivos
    //
    public function cabecalho($titulo = '', $nav = array(), $estilos = false, $scripts = false) {
    // String $titulo: titulo da pagina
    // Array[String => String] $nav: vetor associativo de modulos e scripts
    // Array[String] || String $estilos: vetor de arquivos CSS ou nome do arquivo para adicionar
    // Array[String] || String $scripts: vetor de arquivos JavaScript ou nome do arquivo para adicionar
    //
        global $CFG;

        // Determinar se obter o nome do BD ou do proprio vetor nav
        $bd = true;
        if (count($nav)) {
            $chaves = array_keys($nav);
            $bd = is_int(array_pop($chaves));
        }

        // Nao pode chamar a funcao mais de uma vez
        if (self::$imprimiu_cabecalho) {
            $e = "Erro ao imprimir cabe&ccedil;alho: Cabe&ccedil;alho duplicado";
            mensagem::erro($e);
            return;
        }
        self::$imprimiu_cabecalho = true;

        // Titulo
        if (!empty($titulo)) {
            $t = $CFG->titulo.' - '.$titulo;
        } else {
            $t = $CFG->titulo.' - '.$CFG->descricao;
        }
        $t = texto::codificar($t);

        // Descricao
        $descricao = texto::codificar($CFG->descricao);

        // Nome do arquivo
        $nome = str_replace('.php', '.xhtml', basename($_SERVER['SCRIPT_FILENAME']));

        // Salvar cookies
        self::$salvou_cookies = cookie::salvar($CFG->cookies);

        // Se o servidor esta' muito ocupado
        if ($CFG->load_avg > LOAD_AVG_MAX_ALERTA) {
            $this->sistema_indisponivel();
            exit(1);
        }

        // HEADER HTTP
        setlocale(LC_TIME, 'C');
        header("X-Framework: SIMP/".VERSAO_SIMP);
        header("Content-Type: {$CFG->content}; charset={$CFG->charset}");
        header("Content-Scrypt-Type: text/javascript; charset={$CFG->charset}");
        header("Content-Style-Type: text/css; charset={$CFG->charset}");
        header("Content-Base: {$CFG->wwwroot}");
        header("Content-Disposition: inline; filename={$nome}");
        header("Content-Language: {$CFG->lingua}");
        header('Date: '.gmstrftime($CFG->gmt, $CFG->time));
        header('Last-Modified: '.gmstrftime($CFG->gmt, filemtime($CFG->dirroot)));
        header('Expires: '.gmstrftime($CFG->gmt, $CFG->time));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        setlocale(LC_TIME, $CFG->localidade);

        // INICIO DO CODIGO
        if ($CFG->content != 'text/html') {
            echo "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" standalone=\"no\"?>\n";
            if (self::pode_css()) {
                if (!$CFG->agent->movel) {
                    if ($CFG->pessoal->tema) {
                        echo "<?xml-stylesheet href=\"{$CFG->wwwlayout}{$CFG->pessoal->tema}/index.css.php\" type=\"text/css\" title=\"Layout {$CFG->pessoal->tema}\" media=\"screen\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                    }
                    echo "<?xml-stylesheet href=\"{$CFG->wwwlayout}pessoal.css.php\" type=\"text/css\" media=\"screen\" charset=\"utf-8\" alternate=\"no\"?>\n";

                    // Estilos das pagina
                    if ($estilos) {
                        if (is_array($estilos)) {
                            foreach ($estilos as $e) {
                                echo "<?xml-stylesheet href=\"{$e}\" type=\"text/css\" media=\"screen\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                            }
                        } else {
                            echo "<?xml-stylesheet href=\"{$estilos}\" type=\"text/css\" media=\"screen\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                        }
                    }

                    echo "<?xml-stylesheet href=\"{$CFG->wwwlayout}print.css.php\" type=\"text/css\" media=\"print\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                    echo "<?xml-stylesheet href=\"{$CFG->wwwlayout}handheld.css.php\" type=\"text/css\" media=\"handheld\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                } else {
                    echo "<?xml-stylesheet href=\"{$CFG->wwwlayout}handheld.css.php\" type=\"text/css\" media=\"all\" charset=\"{$CFG->charset}\" alternate=\"no\"?>\n";
                }
            }
            echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
            echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$CFG->lingua}\" dir=\"ltr\">\n";
        } else {
            echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
            echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"{$CFG->lingua}\" xml:lang=\"{$CFG->lingua}\" dir=\"ltr\">\n";
        }
        echo "<head profile=\"{$CFG->wwwroot}rdf/descricao.rdf.php\">\n";
        echo "  <title>{$t}</title>\n";
        if (isset($CFG->wwwroot) && $CFG->wwwroot) {
            echo "  <base id=\"wwwroot\" href=\"{$CFG->wwwroot}\" />\n";
        }

        if (!$CFG->xml) {
            echo "\n  <!-- METADADOS -->\n";
            echo "  <meta http-equiv=\"content-type\" content=\"{$CFG->content}; charset={$CFG->charset}\" />\n";
            echo "  <meta name=\"title\" xml:lang=\"{$CFG->lingua}\" content=\"{$t}\" />\n";
            echo "  <meta name=\"description\" xml:lang=\"{$CFG->lingua}\" content=\"{$descricao}\" />\n";
            echo "  <meta name=\"keywords\" xml:lang=\"{$CFG->lingua}\" content=\"{$CFG->palavras}\" />\n";
            echo "  <meta name=\"author\" content=\"{$CFG->autor}\" />\n";
            echo "  <meta name=\"language\" content=\"{$CFG->lingua}\" />\n";
            echo "  <meta name=\"robots\" content=\"index,follow\" />\n";
            echo "  <meta name=\"generator\" content=\"SIMP ".VERSAO_SIMP."\" />\n";

            echo "\n  <!-- LINKS -->\n";
            echo "  <link rel=\"home\" type=\"{$CFG->content}\" charset=\"{$CFG->charset}\" href=\"{$CFG->wwwroot}\" title=\"Home\" />\n";
            echo "  <link rel=\"help\" type=\"{$CFG->content}\" charset=\"{$CFG->charset}\" href=\"{$CFG->wwwmods}ajuda/index.php\" title=\"Ajuda\" />\n";
            echo "  <link rel=\"glossary\" type=\"{$CFG->content}\" charset=\"{$CFG->charset}\" href=\"{$CFG->wwwmods}ajuda/glossario.php\"  title=\"Gloss&aacute;rio\" />\n";
            echo "  <link rel=\"author\" type=\"{$CFG->content}\" charset=\"{$CFG->charset}\" href=\"{$CFG->wwwmods}ajuda/creditos.php\" title=\"Cr&eacute;ditos\" />\n";
            echo "  <link rel=\"copyright\" type=\"{$CFG->content}\" charset=\"{$CFG->charset}\" href=\"{$CFG->wwwmods}ajuda/licenca.php\" title=\"Licen&ccedil;a\" />\n";
            echo "  <link rel=\"icon\" type=\"image/x-icon\" href=\"{$CFG->wwwroot}favicon.ico\" title=\"&Iacute;cone\" />\n";
            echo "  <link rel=\"meta\" type=\"application/rdf+xml\" href=\"{$CFG->wwwroot}rdf/descricao.rdf.php\" title=\"Descri&ccedil;&atilde;o\" />\n";
        }

        // Se o navegador suporta CSS
        if (self::pode_css()) {
            if ($CFG->content == 'text/html') {

                echo $CFG->xml ? '' : "\n  <!-- ESTILOS -->\n";
                if (!$CFG->agent->movel) {
                    if ($CFG->pessoal->tema) {
                        echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"screen\" href=\"{$CFG->wwwlayout}{$CFG->pessoal->tema}/index.css.php\" />\n";
                    }

                    // Estilos das pagina
                    if ($estilos) {
                        if (is_array($estilos)) {
                            foreach ($estilos as $e) {
                                echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"screen\" href=\"{$e}\" />\n";
                            }
                        } else {
                            echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"screen\" href=\"{$estilos}\" />\n";
                        }
                    }

                    echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"print\" href=\"{$CFG->wwwlayout}print.css.php\" />\n";
                    echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"handheld\" href=\"{$CFG->wwwlayout}handheld.css.php\" />\n";
                } else {
                    echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"all\" href=\"{$CFG->wwwlayout}handheld.css.php\" />\n";
                }

                // Configuracoes pessoais
                echo "  <link rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\" media=\"screen\" href=\"{$CFG->wwwlayout}pessoal.css.php\" />\n";
            }
        } else {
            echo $CFG->xml ? '' : "\n  <!-- NAVEGADOR SEM SUPORTE A CSS -->\n";
        }

        // Se o navegador suporta JavaScript
        if ($CFG->agent->javascript) {
            echo $CFG->xml ? '' : "\n  <!-- SCRIPTS -->\n";
            echo "  <script type=\"text/javascript\" charset=\"utf-8\" defer=\"defer\" src=\"{$CFG->wwwroot}javascript/javascript.js.php\" xml:space=\"preserve\"></script>\n";

            if ($scripts) {
                if (is_string($scripts)) {
                    $scripts = array($scripts);
                }
                foreach ($scripts as $s) {
                    echo "  <script type=\"text/javascript\" charset=\"utf-8\" defer=\"defer\" src=\"{$s}\" xml:space=\"preserve\"></script>\n";
                }
            }
        } else {
            echo $CFG->xml ? '' : "\n  <!-- NAVEGADOR SEM SUPORTE A JAVASCRIPT -->\n";
        }

        // Feeds RSS
        if ($this->rss) {
            echo $CFG->xml ? '' : "\n  <!-- FEEDS RSS -->\n";
            foreach ($this->rss as $link => $descricao_link) {
                $descricao_link = texto::decodificar($descricao_link);
                $title = $descricao_link ? "title=\"{$descricao_link}\"" : '';
                echo "  <link rel=\"alternate\" type=\"application/rss+xml\" href=\"{$link}\" {$title}/>\n";
            }
        }

        echo "</head>\n";

        $t = $CFG->titulo;
        $id_body = $this->id_pagina ? " id=\"{$this->id_pagina}\"" : '';
        echo "<body{$id_body}>\n";
        echo "<div id=\"container\">\n\n";

        if (!$CFG->xml) {
            echo "<!-- TITULO -->\n";
            echo "<div id=\"titulo_pagina\">\n";
            echo "  <h1><a accesskey=\"C\" href=\"{$CFG->wwwroot}\" title=\"{$descricao} v.{$CFG->versao}\">{$t}</a></h1>\n";
            echo "  <em>{$descricao}</em>\n";
            echo "</div>\n";
            echo "<!-- FIM TITULO -->\n\n";
        }

        $this->imprimir_navegacao($nav, $bd, $t);
        $hr = ($CFG->agent->ie && $CFG->agent->ie < 7) ? '' : "<hr />\n\n";
        echo $hr;

        echo $CFG->xml ? '' : "<!-- CONTEUDO -->\n";
        echo "<div id=\"conteudo\">\n";
    }


    //
    //     Imprime a barra de navegacao
    //
    private function imprimir_navegacao($nav, $bd = true, $titulo = '') {
    // Array[String] || Array[String => String] $nav: vetor de nome de modulos e arquivos ou vetor de links e nomes
    // Bool $bd: obter os dados no BD ou diretamente do vetor
    // String $titulo: titulo usado caso o vetor esteja vazio
    //
        global $CFG, $USUARIO;
        echo $CFG->xml ? '' : "<!-- BARRA DE NAVEGACAO -->\n";
        echo "<div id=\"navegacao\">\n";
        echo "  <strong class=\"hide\">Navega&ccedil;&atilde;o:</strong>\n";
        if (is_array($nav) && count($nav)) {

            // Obter os dados do bd
            if ($bd) {
                $arq = new arquivo();
                $ultimo = count($nav) - 1;
                foreach ($nav as $i => $modulo_arquivo) {
                    $parametros = '';

                    list($modulo, $arquivo) = explode('#', $modulo_arquivo);
                    $pos = strpos($arquivo, '?');
                    if ($pos !== false) {
                        $parametros = substr($arquivo, $pos + 1);
                        $arquivo = substr($arquivo, 0, $pos);
                    }
                    $dados_arq = false;
                    if (is_object($USUARIO) && $USUARIO->existe()) {
                        $dados_arq = $USUARIO->get_arquivo($modulo, $arquivo);
                    }
                    if (!$dados_arq) {
                        $dados_arq = $arq->consultar_arquivo_modulo($arquivo, $modulo, array('descricao'));
                    }

                    if ($modulo) {
                        $link = $CFG->wwwmods.$modulo.'/'.$arquivo;
                    } else {
                        $link = $CFG->wwwroot.$arquivo;
                    }
                    if ($parametros) {
                        $link .= '?'.$parametros;
                    }

                    $descricao = texto::codificar($dados_arq->descricao);
                    if ($i < $ultimo) {
                        link::texto($link, $descricao);
                        echo " <em>&raquo;</em>\n";
                    } else {
                        echo "  <span title=\"{$descricao}\">{$descricao}</span>\n";
                    }
                }

            // Usar os dados do proprio vetor
            } else {
                foreach ($nav as $link => $nome) {
                    if (!empty($link)) {
                        link::texto($link, $nome);
                        echo "  <em>&raquo;</em>\n";
                    } else {
                        echo "  <span title=\"{$nome}\">{$nome}</span>\n";
                    }
                }
            }
        } else {
            echo "  <span title=\"{$titulo}\">{$titulo}</span>\n";
        }
        echo "</div>\n";
        echo $CFG->xml ? '' : "<!-- FIM BARRA DE NAVEGACAO -->\n\n";
    }


    //
    //     Imprime o menu baseado nos grupos do usuario
    //
    public function imprimir_menu(&$usuario, $return = false) {
    // Object $usuario: usuario para o qual o menu e' apresentado
    // Bool $return: retornar ou imprimir o menu
    //
        global $CFG;

        if ($CFG->xml) {
            return;
        }

        // O cabecalho tem que esta inicializado
        if (!self::$imprimiu_cabecalho) {
            $this->cabecalho();
            $e = "Erro ao imprimir o Menu: Cabe&ccedil;alho omitido.\n";
            mensagem::erro($e);

        // Nao pode chamar a funcao mais de uma vez
        } elseif (self::$imprimiu_menu) {
            $e = "Erro ao imprimir o Menu: Menu duplicado.\n";
            mensagem::erro($e);
            return;
        }
        self::$imprimiu_menu = true;

        // Imprimir menu
        $opcoes = array();

        $m  = "<div id=\"conteudo_secundario\">\n\n";

        $m .= $CFG->xml ? '' : "<!-- MENU -->\n";
        $m .= "<div id=\"menu\">\n";
        $m .= "  <h2 class=\"hide\">Menu de Op&ccedil;&otilde;es</h2>\n";
        foreach ($usuario->grupos as $usuario_grupo) {
            $grupo = &$usuario_grupo->grupo;
            $buf = '';
            $entrou = false;

            $buf .= "  <strong>{$grupo->nome}</strong>\n";
            $buf .= "  <ul>\n";
            foreach ($grupo->permissoes as $permissao) {
                if (!$permissao->visivel) { continue; }
                $permissao->arquivo->consultar_campos(array('descricao', 'arquivo', 'modulo'));
                $descricao = $permissao->arquivo->exibir('descricao');
                $link      = $permissao->arquivo->link;

                if (!isset($opcoes[$link])) {
                    $buf .= "    <li><span class=\"hide\">[</span>".
                            link::texto($link, $descricao, $descricao, '', '', 1).
                            "<span class=\"hide\">]</span></li>\n";
                    $opcoes[$link] = 1;
                    $entrou = true;
                }
            }
            $buf .= "  </ul>\n";

            // Se tem alguma opcao
            if ($entrou) {
                $m .= $buf;
            } else {
                $m .= "  <strong>{$grupo->nome}</strong>\n".
                      "  <p>Nenhuma Op&ccedil;&atilde;o</p>\n";
            }
        }

        // Se nao faz parte de nenhum grupo
        $count_opcoes = count($opcoes);
        if (!$count_opcoes) {
            $admin = new usuario('login', 'admin');
            $email_admin = $admin->email;

            $m .= "  <strong>Aviso</strong>\n";
            $m .= "  <p>Voc&ecirc; n&atilde;o faz parte de nenhum grupo. Solicite ao administrador do ";
            $m .= "Sistema para acrescent&aacute;-lo(a) no(s) grupo(s) necess&aacute;rio(s).</p>\n";
            $m .= "  <p>E-mail: {$email_admin}</p>\n";
        }

        $id = ($CFG->agent->ie < 7) ? ' id="rodape_menu"' : '';
        $m .= "  <div{$id}>\n";
        $m .= "    <p>\n";

        // Editar
        if ($count_opcoes) {
            $l = $CFG->wwwmods.'usuarios/alterar.php';
            $m .= link::texto($l, $usuario->login, 'Alterar Dados Pessoais', 'login_usuario', '', 1).'<span> | </span>';
        } else {
            $m .= "<em id=\"login_usuario\">{$usuario->login}</em><span> | </span>";
        }

        // Opcoes
        $l = $CFG->wwwmods.'config_pessoal/index.php';
        $m .= link::texto($l, 'Op&ccedil;&otilde;es', 'Op&ccedil;&otilde;es Pessoais', 'opcoes', '', 1).'<span> | </span>';

        // Ajuda
        $l = $CFG->wwwmods.'ajuda/index.php';
        $m .= link::texto($l, 'Ajuda', 'Ajuda', 'ajuda', '', 1).'<span> | </span>';

        // Sair
        $l = $CFG->wwwlogin.'?encerrar=1';
        $onclick = $CFG->autenticacao_http ? ' onclick="window.alert(\'Clique em CANCELAR no quadro de log-in para SAIR\');"' : '';
        $m .= "      <a id=\"saida\" href=\"{$l}\" title=\"Sair do Sistema\"{$onclick}>Sair</a>\n";

        $m .= "    </p>\n";
        $m .= "    <p><em id=\"hora_local\">".strftime($CFG->formato_hora, $CFG->time).'</em> - <em id="data_local">'.strftime($CFG->formato_data, $CFG->time)."</em></p>\n";
        $m .= "  </div>\n";

        $m .= "</div>\n";

        $m .= $CFG->xml ? '' : "<!-- FIM MENU -->\n\n";

        $m  .= "</div>\n\n";//id conteudo_secundario

        $hr = ($CFG->agent->ie && $CFG->agent->ie < 7) ? '' : "<hr />\n\n";
        $m .= $hr;

        if ($return) {
            return $m;
        }
        echo $m;
    }


    //
    //     Imprime o rodape da pagina
    //
    public function rodape() {
        global $CFG;

        // O cabecalho tem que esta inicializado
        if (!self::$imprimiu_cabecalho) {
            $this->cabecalho();
            $e = "Erro ao imprimir o Rodap&eacute;: Cabe&ccedil;alho omitido.\n";
            mensagem::erro($e);

        // Se imprimiu incio de conteudo e nao o fechou
        } elseif (self::$imprimiu_inicio_conteudo && !self::$imprimiu_fim_conteudo) {
            $e = "Erro ao imprimir o Rodap&eacute;: Fim de conteudo omitido.\n";
            mensagem::erro($e);
            $this->fim_conteudo();
        }

        // Nao pode chamar a funcao mais de uma vez
        if (self::$imprimiu_rodape) {
            $e = "Erro ao imprimir o Rodap&eacute;: Rodap&eacute; duplicado.\n";
            mensagem::erro($e);
            return;
        }
        self::$imprimiu_rodape = true;

        // Obter SGBD e PHP
        $bd = new objeto_dao();
        try {
            $sgbd = $bd->get_nome();
            $sgbdv = $bd->get_versao();
            $sgbdv = $sgbdv ? ' v.'.$sgbdv : '';
            $sgbdv = DEVEL_BLOQUEADO ? $sgbd : $sgbd.$sgbdv;
        } catch (Exception $e) {
            $sgbd = '[SGBD indefinido]';
            $sgbdv = '?';
        }
        $php = DEVEL_BLOQUEADO ? 'PHP' : 'PHP v.'.phpversion();

        echo "</div>\n";
        echo $CFG->xml ? '' : "<!-- FIM CONTEUDO -->\n\n";

        $hr = ($CFG->agent->ie && $CFG->agent->ie < 7) ? '' : "<hr />\n\n";
        echo $hr;

        if (!$CFG->xml) {
            echo "<!-- RODAPE -->\n";
            echo "<div id=\"rodape\">\n";
            echo "  <a id=\"voltar_topo\" href=\"{$CFG->site}#titulo_pagina\">Voltar ao Topo</a>\n";
            echo "  <h2 class=\"hide\">Cr&eacute;ditos</h2>\n";
            if (!DEVEL_BLOQUEADO) {
                $modulo = util::get_modulo(util::get_arquivo());
                echo "<p id=\"devel\">\n";
                echo "<strong>Sistema em modo de Desenvolvimento</strong><br />\n";
                if ($modulo == 'devel') {
                    echo "<a href=\"{$CFG->wwwroot}\">SISTEMA</a> | <strong><a href=\"{$CFG->wwwmods}devel/\">DEVEL</a></strong>\n";
                } else {
                    echo "<strong><a href=\"{$CFG->wwwroot}\">SISTEMA</a></strong> | <a href=\"{$CFG->wwwmods}devel/\">DEVEL</a>\n";
                }
                echo "</p>\n";
            }
            echo "  <p>Este sistema est&aacute; protegido sob os termos da Licen&ccedil;a ";
            echo "<a href=\"http://www.gnu.org/\"><acronym title=\"GNU is Not Unix\">GNU</acronym></a><span>-</span>";
            echo "<a href=\"http://www.gnu.org/licenses/old-licenses/gpl-2.0.html\" title=\"GPL v.2\"><acronym title=\"General Public License\">GPL</acronym> 2</a></p>\n";
            echo "  <div>\n";
            echo "    Desenvolvido por ";
            if (isset($CFG->link_autor) && !empty($CFG->link_autor)) {
                echo "<a id=\"autor_sistema\" href=\"{$CFG->link_autor}\" title=\"{$CFG->autor}\">{$CFG->autor}</a>";
            } else {
                echo $CFG->autor;
            }
            echo " com o ";
            echo "    <acronym title=\"SIMP v.".VERSAO_SIMP."\">SIMP</acronym>\n";
            echo "  </div>\n";
            echo "  <p>\n";
            if (function_exists('apache_get_version')) {
                $apache = DEVEL_BLOQUEADO ? 'Apache' : apache_get_version();
                echo "    <acronym title=\"{$apache}\">Apache</acronym> + \n";
            }
            echo "    <acronym title=\"{$php}\">PHP</acronym> + \n";
            echo "    <acronym title=\"{$sgbdv}\">{$sgbd}</acronym> + \n";
            if ($CFG->ajax) {
                echo "    <acronym title=\"Asynchronous Javascript And XML\">Ajax</acronym> + \n";
            }
            if ($CFG->content == 'text/html') {
                echo "    <acronym title=\"HyperText Markup Language v.4.01\">HTML</acronym> + \n";
            } elseif (strpos($CFG->content, 'xhtml') !== false) {
                echo "    <acronym title=\"eXtensible HyperText Markup Language v.1.0\">XHTML</acronym> + \n";
            }
            echo "    <acronym title=\"Cascading Style Sheet v.3\">CSS</acronym>\n";
            echo "  </p>\n";
            if ($CFG->load_avg > LOAD_AVG_MIN_ALERTA) {
                echo '<p class="vermelho">';
                echo '<strong>Alerta de sobrecarga do servidor (';
                echo 'processamento: '.$CFG->load_avg.' / ';
                echo 'esperado: abaixo de '.LOAD_AVG_MIN_ALERTA.' / ';
                echo 'alerta: entre '.LOAD_AVG_MIN_ALERTA.' e '.LOAD_AVG_MAX_ALERTA;
                echo ")!</strong></p>\n";
            }
            echo "</div>\n";
            echo "<!-- FIM RODAPE -->\n\n";
        }

        echo "</div>\n";
        echo "</body>\n";
        echo "</html>\n";

        $t = round(microtime(1) - $CFG->microtime, 3);
        $m = texto::formatar_bytes(memory_get_usage());
        if (function_exists('memory_get_peak_usage')) {
            $m2 = '/ Pico: '.texto::formatar_bytes(memory_get_peak_usage()).' ';
        } else {
            $m2 = '';
        }
        $instrucoes = driver_base::get_quantidade_instrucoes();
        echo "<!-- Carregamento: {$t} segundos / Load AVG: {$CFG->load_avg} / Memoria: {$m} {$m2}/ SQL: {$instrucoes} / Classes: {$CFG->classes_carregadas} -->";
    }


    //
    //     Inicio do Conteudo da Pagina
    //
    public function inicio_conteudo($titulo = '') {
    // String $titulo: titulo da pagina
    //
        global $CFG;

        // O Cabecalho deve ter sido inicializado
        if (!self::$imprimiu_cabecalho) {
            $this->cabecalho();
            $e = "Erro ao imprimir In&iacute;cio de Conte&uacute;do: Cabe&ccedil;alho omitido.\n";
            mensagem::erro($e);

        // Nao pode chamar a funcao mais de uma vez
        } elseif (self::$imprimiu_inicio_conteudo) {
            $e = "Erro ao imprimir In&iacute;cio de Conte&uacute;do: Conte&uacute;do duplicado.\n";
            mensagem::erro($e);
            return;
        }
        self::$imprimiu_inicio_conteudo = true;

        echo "<div id=\"conteudo_principal\">\n\n";

        echo $CFG->xml ? '' : "<!-- CENTRO -->\n";
        echo "<div id=\"centro\">\n";
        if ($titulo) {
            $this->imprimir_titulo($titulo);
        }
    }


    //
    //     Fim do Conteudo da Pagina
    //
    public function fim_conteudo() {
        global $CFG;

        // O inicio de conteudo deve ter sido inicializado
        if (!self::$imprimiu_inicio_conteudo) {
            $this->inicio_conteudo();
            $e = "Erro ao imprimir Fim de Conte&uacute;do: In&iacute;cio de Conte&uacute;do omitido.\n";
            mensagem::erro($e);

        // Nao pode chamar a funcao mais de uma vez
        } elseif (self::$imprimiu_fim_conteudo) {
            $e = "Erro ao imprimir Fim de Conte&uacute;do: Fim de Conte&uacute;do duplicado.\n";
            mensagem::erro($e);
            return;
        }
        self::$imprimiu_fim_conteudo = true;

        echo "</div>\n";
        echo $CFG->xml ? '' : "<!-- FIM CENTRO -->\n\n";

        echo "</div>\n\n";//id conteudo_principal
    }


    //
    //     Imprime o titulo da pagina
    //
    public function imprimir_titulo($titulo, $return = false) {
    // String $titulo: titulo da pagina
    // Bool $return: retornar ou imprimir o titulo
    //
        // O inicio de Conteudo deve ter sido inicializado
        if (!self::$imprimiu_inicio_conteudo) {
            $this->inicio_conteudo();
            $e = "Erro ao imprimir T&iacute;tulo: In&iacute;cio de Conte&uacute;do omitido.\n";
            mensagem::erro($e);
        }
        $t = "<h2 class=\"titulo\">{$titulo}</h2>\n";
        if ($return) {
            return $t;
        }
        echo $t;
    }


    //
    //     Imprime um sub-titulo da pagina
    //
    public function imprimir_subtitulo($subtitulo, $return = false) {
    // String $subtitulo: subtitulo
    // Bool $return: retornar ou imprimir o subtitulo
    //
        // O inicio de Conteudo deve ter sido inicializado
        if (!self::$imprimiu_inicio_conteudo) {
            $this->inicio_conteudo();
            $e = "Erro ao imprimir Subt&iacute;tulo: In&iacute;cio de Conte&uacute;do omitido.\n";
            mensagem::erro($e);
        }
        $t = "<h3 class=\"subtitulo\">{$subtitulo}</h3>\n";
        if ($return) {
            return $t;
        }
        echo $t;
    }


    //
    //     Imprime abas
    //
    public function imprimir_abas($vt_abas, $id, $ativa = 0, $return = false) {
    // Array[String => String] $vt_abas: vetor associativo de abas
    // String $id: Id da caixa de abas
    // Int $ativa: numero da aba ativa
    // Bool $return: retornar ou imprimir o titulo
    //

        // O inicio de conteudo deve ter sido inicializado
        if (!self::$imprimiu_inicio_conteudo) {
            $this->inicio_conteudo();
            $e = "Erro ao imprimir Abas: Inico de conteudo omitido.\n";
            mensagem::erro($e);
        }
        self::$contador_abas++;

        $abas = '';
        if (is_array($vt_abas)) {
            $id_conteudo = $id.'_conteudo';

            $span = "<span>|</span>\n";

            $abas .= "<div id=\"{$id}\" class=\"abas\">\n";
            $abas .= "<div class=\"nomes_abas\">\n";
            $abas .= "<span>Abas:</span>\n";
            foreach ($vt_abas as $id_aba => $dados) {
                $class = ($ativa == $id_aba) ? 'ativa' : '';
                $aba = link::texto($dados->link, $dados->nome, '', 'aba_'.$id_aba, $class, 1, 'document.getElementById("'.$id_conteudo.'")');
                $abas .= (isset($imprimiu_primeiro) ? $span : '').$aba."\n";
                $imprimiu_primeiro = true;
            }
            $abas .= "</div>\n";
            $abas .= "<div class=\"conteudo_aba\" id=\"{$id_conteudo}\">\n";
        }

        if ($return) {
            return $abas;
        }
        echo $abas;

    }


    //
    //     Fecha um bloco de abas
    //
    public function fechar_abas($return = false) {
    // Bool $return: retorna ou imprime as abas
    //

        // Nao pode chamar a funcao mais de uma vez
        if (!self::$contador_abas) {
            $this->imprimir_abas(array('Erro'), 'aba_erro');
            $e = "Erro ao imprimir Fecha Abas: Abas omitido.\n";
            mensagem::erro($e);
        }
        self::$contador_abas--;

        $abas = "</div>\n".
                "</div>\n";
        if ($return) {
            return $abas;
        }
        echo $abas;
    }


    //
    //     Gera uma lista de opcoes
    //
    public function listar_opcoes($opcoes, $return = false) {
    // String || Array[String] $opcoes: string ou vetor com os links das opcoes
    // Bool $return: retornar ou imprimir a lista
    //
        global $CFG;

        if (!self::$imprimiu_inicio_conteudo) {
            $this->inicio_conteudo();
            $e = "Erro ao imprimir op&ccedil;&otilde;es: Inico de conteudo omitido.\n";
            mensagem::erro($e);
        }
        $opcoes_validas = array();

        // Imprimir opcoes
        $l = "<div class=\"opcoes\">\n";
        $l .= "  <strong>Op&ccedil;&otilde;es:</strong>\n";
        if (is_array($opcoes)) {
            foreach ($opcoes as $o) {
                if ($o) {
                    $opcoes_validas[] = $o;
                }
            }
            $l .= implode("  <span>|</span>\n", $opcoes_validas)."\n";
        } else {
            $l .= $opcoes."\n";
        }
        $l .= "</div>\n";

        $l = count($opcoes_validas) ? $l : '';

        if ($return) {
            return $l;
        }
        echo $l;
    }


    //
    //     Exibe uma nota de rodape
    //
    public function nota_rodape($texto) {
    // String || Array[String] $texto: texto da nota de rodape ou vetor de textos
    //
        echo "<div class=\"observacao\">\n";
        if (is_string($texto)) {
            echo $texto;
        } elseif (is_array($texto)) {
            $i = 1;
            foreach ($texto as $t) {
                echo "<div id=\"nota_rodape_{$i}\">{$t}</div>\n";
                $i++;
            }
        }
        echo "</div>\n";
    }


    //
    //     Exibe uma pagina de erro
    //
    static public function erro($usuario, $mensagem_erro, $conteudo_pagina = '') {
    // Object $usuario: usuario que causou o erro
    // String $mensagem_erro: mensagem de erro
    // String $conteudo_pagina: conteudo a ser exibido na pagina
    //
        global $CFG;
        $classe = __CLASS__;
        $titulo = 'Erro Inesperado';
        $nav = array($CFG->wwwroot => 'P&aacute;gina Principal',
                     ''            => 'Erro Inesperado');
        $estilos = false;

        $p = new $classe();
        $p->cabecalho($titulo, $nav, $estilos);
        if ($usuario) {
            $p->imprimir_menu($usuario);
        }
        $p->inicio_conteudo($titulo);
        mensagem::erro($mensagem_erro);
        echo $conteudo_pagina ? $conteudo_pagina : $mensagem_erro;
        $p->fim_conteudo();
        $p->rodape();
        exit(1);
    }


    //
    //     Exibe uma pagina de sistema indisponivel (para o caso do servidor estar sobrecarregado)
    //
    private function sistema_indisponivel() {
        global $CFG;

        self::$imprimiu_rodape = true;
        header('HTTP/1.1 503 Service Unavailable');
        header('Status: 503 Service Unavailable');
        header('Retry-After: 3600');
        header("Content-Type: {$CFG->content}; charset={$CFG->charset}");
        echo "<?xml version=\"1.0\" encoding=\"{$CFG->charset}\" standalone=\"no\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$CFG->lingua}\" dir=\"ltr\">";
        echo '<head>';
        echo '<title>Servidor Sobrecarregado</title>';
        echo '</head>';
        echo '<body>';
        echo '<h1>Servidor Sobrecarregado</h1>';
        echo '<p>O servidor est&aacute; operando acima do processamento esperado.</p>';
        echo '<p>Recomenda-se sair e voltar mais tarde.</p>';
        echo "<p>Tentar novamente: <a href=\"{$CFG->wwwroot}\">P&aacute;gina inicial</a>.</p>";
        echo '<h2>Detalhes</h2>';
        echo '<p>Processamento esperado: entre 0 e '.LOAD_AVG_MAX_ESPERADO.'</p>';
        echo '<p>Processamento alto: entre '.LOAD_AVG_MAX_ESPERADO.' e '.LOAD_AVG_MIN_ALERTA.'</p>';
        echo '<p>Processamento em alerta: entre '.LOAD_AVG_MIN_ALERTA.' e '.LOAD_AVG_MAX_ALERTA.'</p>';
        echo '<p>Processamento extremo: acima de '.LOAD_AVG_MAX_ALERTA.'</p>';
        echo '<p><strong>Processamento corrente:</strong> '.$CFG->load_avg.'</p>';
        echo '</body>';
        echo '</html>';
    }

}//class
