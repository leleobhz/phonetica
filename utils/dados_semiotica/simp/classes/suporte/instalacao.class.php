<?php
//
// SIMP
// Descricao: Classe que realiza as operacoes para instalacao do sistema
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.1.0.36
// Data: 05/09/2007
// Modificado: 14/07/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constantes
define('INSTALACAO_WWWROOT',             $CFG->wwwroot);
define('INSTALACAO_DIR_CLASSES',         $CFG->dirclasses);
define('INSTALACAO_TODAS_TABELAS',       1);
define('INSTALACAO_TABELAS_NECESSARIAS', 2);

final class instalacao {
    private $bd_config;                        // Dados para conexao com o BD para instalacao
    const versao_sistema     = VERSAO_SISTEMA; // Versao atual do sistema
    const versao_php_exigida = '5.2.0';        // Versao do PHP exigida para instalacao


    //
    //     Construtor padrao
    //
    public function __construct() {
        $this->bd_config = new stdClass();
        $this->bd_config->sgbd     = false;
        $this->bd_config->porta    = false;
        $this->bd_config->servidor = false;
        $this->bd_config->usuario  = false;
        $this->bd_config->senha    = false;
    }


    //
    //     Define as configuracoes para conexao com o BD
    //
    public function set_bd_config($bd_config) {
    // Object $bd_config: dados de configuracao do BD
    //
        $this->bd_config = $bd_config;
    }


    //
    //     Checa os pre-requisitos para instalacao
    //
    public function checar_pre_requisitos() {
        $erro = false;

        // Verificar a versao do PHP
        if (!$this->validar_php($erro)) {
            pagina::erro(null, $erro, $erro);
            exit(1);

        // Verificar os modulos do PHP instalados
        } elseif (!$this->checar_modulos($vt_modulos)) {
            $erro = 'Algum m&oacute;dulo necess&aacute;rio n&atilde;o foi instalado';
            $detalhes = '<table class="tabela" summary="Tabela de m&oacute;dulos necess&aacute;rios">'.
                        '<thead>'.
                        '<tr>'.
                        '<th>M&oacute;dulo</th>'.
                        '<th>Import&acirc;ncia</th>'.
                        '<th>Carregado</th>'.
                        '<th>Situa&ccedil;&atilde;o</th>'.
                        '</tr>'.
                        '</thead>'.
                        '<tbody>';
            foreach ($vt_modulos as $modulo => $dados) {
                $detalhes .= '<tr>'.
                             '<td><a href="'.MANUAL_PHP.'book.'.strtolower($modulo).'.php">'.$modulo.'</a></td>'.
                             '<td>'.$dados->importancia.'</td>'.
                             '<td>'.($dados->carregado ? 'Sim' : 'N&atilde;o').'</td>'.
                             '<td>'.($dados->situacao ? 'OK' : 'Erro').'</td>'.
                             '</tr>';
            }
            $detalhes .= '</tbody>'.
                         '</table>';
            pagina::erro(null, $erro, $detalhes);
            exit(1);

        // Testar se ha' erros de nas classes
        } elseif ($this->possui_erros_classes($classes_erradas)) {
            $erro = 'Alguma classe possui erro de sintaxe ou conceitual';
            $detalhes = '<ul>';
            foreach ($classes_erradas as $classe => $erro_classe) {
                $erro_classe = $erro_classe ? $erro_classe : 'Desconhecido';
                $detalhes .= "<li><strong>{$classe}</strong> <p class=\"erro\">{$erro_classe}</p></li>";
            }
            $detalhes .= '</ul>';
            pagina::erro(null, $erro, $detalhes);
            exit(1);

        // Checar se as configuracoes do php.ini estao OK
        } elseif ($this->possui_erros_ini($erros)) {
            $erro = 'Alguma configura&ccedil;&atilde;o est&aacute; errada';
            $detalhes = '<ul>';
            foreach ($erros as $e) {
                $detalhes .= '<li>'.$e.'</li>';
            }
            $detalhes .= '</ul>';
            pagina::erro(null, $erro, $detalhes);
            exit(1);
        }
        return true;
    }


    //
    //     Checa se a versao do PHP e' compativel com a versao exigida
    //
    public function validar_php(&$erro) {
    // String $erro: descricao do erro, caso exista
    //
        $versao_atual = phpversion();
        if (version_compare($versao_atual, self::versao_php_exigida) < 0) {
            $erro = "Para instalar este sistema &eacute; necess&aacute;ria a vers&atilde;o ".
                    self::versao_php_exigida." do PHP. ".
                    "A vers&atilde;o atual &eacute; {$versao_atual}.";
            return false;
        }
        return true;
    }


    //
    //     Checa os modulos carregados e preenche a lista de modulos necessarios e/ou carregados
    //
    public function checar_modulos(&$vt_modulos) {
    // Array[String => Object] $vt_modulos: vetor de modulos e flag indicando se foi carregado ou nao e o grau de importancia (0-6)
    //
        // Graus de importancia
        // 0 -> Nao precisa e possivelmente nao sera usado
        // 1 -> Nao precisa, mas pode ser util no futuro
        // 2 -> Nao precisa, mas pode ser muito util no futuro
        // 3 -> Nao precisa, mas melhora o desempenho quando utilizado
        // 4 -> Precisa em caso de escolha (do SGBD por exemplo)
        // 5 -> Precisa para situacoes especiais
        // 6 -> Indispensavel para instalacao

        $vt_importancia = array(
            'bz2' => 2,
            'ctype' => 1,
            'date' => 6,
            'dom' => 1,
            'exif' => 5,
            'filter' => 1,
            'gd' => 4,
            'geoip' => 2,
            'hash' => 4,
            'iconv' => 1,
            'imap' => 4,
            'json' => 0,
            'ldap' => 4,
            'libxml' => 6,
            'mailparse' => 2,
            'mcrypt' => 1,
            'mysql' => 4,
            'oci8' => 4,
            'pcre' => 6,
            'PDO' => 4,
            'pdo_mysql' => 4,
            'PDO_OCI' => 4,
            'pdo_pgsql' => 4,
            'pdo_sqlite' => 4,
            'pgsql' => 4,
            'posix' => 3,
            'Reflection' => 5,
            'session' => 6,
            'SimpleXML' => 6,
            'sockets' => 5,
            'SPL' => 5,
            'SQLite' => 4,
            'standard' => 5,
            'stats' => 2,
            'svn' => 2,
            'tokenizer' => 4,
            'unicode' => 2,
            'xdiff' => 2,
            'xml' => 6,
            'xmlreader' => 6,
            'xmlwriter' => 6,
            'xslt' => 1,
            'zip' => 3,
            'zlib' => 3
        );

        $vt_modulos = array();
        $situacao = true;
        foreach ($vt_importancia as $modulo => $importancia) {
            $obj = new stdClass();
            $obj->modulo = $modulo;
            $obj->importancia = $importancia;
            $obj->carregado = extension_loaded($modulo);
            $obj->situacao = ($obj->importancia == 6 && $obj->carregado) || true;
            $vt_modulos[$modulo] = $obj;
            $situacao = $situacao && $obj->situacao;
        }
        return $situacao;
    }


    //
    //     Checa se ha' configuracoes incompativeis com o simp no php.ini
    //
    public function possui_erros_ini(&$erros) {
    // Array[String] $erros: vetor de erros encontrados
    //
        // @ MEMORY_LIMIT
        $memory_limit = ini_get('memory_limit');
        if (intval($memory_limit) == -1) {
            // Otimo: sem limite de memoria
        } elseif (preg_match('/^([\d]+)([KMG])$/i', $memory_limit, $match)) {
            switch ($match[2]) {
            case 'K':
            case 'k':
                $memory_limit = $match[1] * pow(2, 10);
                break;
            case 'M':
            case 'm':
                $memory_limit = $match[1] * pow(2, 20);
                break;
            case 'G':
            case 'g':
                $memory_limit = $match[1] * pow(2, 30);
                break;
            }
        } elseif (is_numeric($memory_limit)) {
            $memory_limit = round($memory_limit);
        }
        if ($memory_limit < (8 * pow(2, 10))) {
            $erros[] = 'Arquivo php.ini: a diretiva "memory_limit" precisa ser superior a 8M para o correto funcionamento do sistema (recomenda-se 16M / utilizado '.$memory_limit.')';
        }

        // @ MAX_EXECUTION_TIME
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time > 0 && $max_execution_time < 10) {
            $erros[] = 'Arquivo php.ini: a diretiva "max_execution_time" precisa ser superior a 10 para o correto funcionamento do sistema (recomenda-se 30)';
        }

        // @ ARG_SEPARATOR.OUTPUT
        if (ini_get('arg_separator.output') != '&amp;') {
            $erros[] = 'Arquivo php.ini: a diretiva "arg_separator.output" deve valer "&amp;amp;"';
        }

        // @ SESSION.USE_COOKIES
        if (ini_get('session.use_cookies') != 1) {
            $erros[] = 'Arquivo php.ini: a diretiva "session.use_cookies" deve estar On para o correto funcionamento do sistema';
        }

        // @ SESSION.USE_ONLY_COOKIES
        if (ini_get('session.use_only_cookies') != 1) {
            $erros[] = 'Arquivo php.ini: a diretiva "session.use_only_cookies" deve estar On para o correto funcionamento do sistema';
        }

        // @ FILE_UPLOADS
        if (ini_get('file_uploads') != 1) {
            $erros[] = 'Arquivo php.ini: a diretiva "file_uploads" deve estar On para o correto funcionamento do sistema';
        }

        return count($erros);
    }


    //
    //     Imprime os termos de licenca do Sistema
    //
    public function imprimir_licenca() {
        $ano = 2007; // Ano de criacao do SIMP

        if ((int)strftime('%Y') > $ano) {
            $ano .= ' - '.strftime('%Y');
        }
        $link_en    = INSTALACAO_WWWROOT.'LICENSE.TXT';
        $link_pt_br = INSTALACAO_WWWROOT.'LICENSE_PT_BR.TXT';

echo <<<LICENCA
<div id="copyright_en" class="licenca" xml:lang="en">
  <h2>Copyright notice</h2>
  <div>
    <p><strong>Copyright &copy; {$ano}&nbsp;&nbsp;Rubens Takiguti Ribeiro</strong></p>
    <p>This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2 as published by
    the Free Software Foundation.</p>
    <p>This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.</p>
    <p>See the <acronym title="GNU is Not Unix">GNU</acronym>
    <acronym title="General Public License">GPL</acronym> version 2 for more details.</p>
    <p>Attached File: <a href="{$link_en}" title="GPL 2">{$link_en}</a></p>
    <p>Official Site: <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" title="GPL 2">http://www.gnu.org/licenses/old-licenses/gpl-2.0.html</a></p>
  </div>
</div>
LICENCA;

echo <<<LICENCA
<div id="copyright_br" class="licenca" xml:lang="pt_br">
  <h2>Notas de Licen&ccedil;a</h2>
  <div>
    <p><strong>Tradu&ccedil;&atilde;o n&atilde;o-oficial em Portugu&ecirc;s</strong></p>
    <p>Este programa &eacute; software livre; voc&ecirc; pode redistribu&iacute;-lo e/ou
    modific&aacute;-lo sob os termos da Licen&ccedil;a P&uacute;blica Geral GNU vers&atilde;o 2,
    conforme publicada pela <em>Free Software Foundation</em>.</p>
    <p>Este programa &eacute; distribuido na expectativa de ser &uacute;til, mas SEM
    QUALQUER GARANTIA; sem mesmo a garantia impl&iacute;cita de
    COMERCIALIZA&Ccedil;&Atilde;O ou de ADEQUA&Ccedil;&Atilde;O A QUALQUER PROPOSITO EM
    PARTICULAR.</p>
    <p>Consulte a Licen&ccedil;a P&uacute;blica Geral GNU vers&atilde;o 2 para obter mais detalhes.</p>
    <p>Arquivo Anexado: <a href="{$link_pt_br}" title="GPL 2">{$link_pt_br}</a></p>
    <p>Site Oficial: <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" title="GPL 2">http://www.gnu.org/licenses/old-licenses/gpl-2.0.html</a></p>
  </div>
</div>
LICENCA;

        $form = new formulario('instalar.php', 'formlicenca', false, 'post', false);
        $form->campo_informacao('<em xml:lang="en">Did you read, understand and accept the terms of the license?</em>');
        $form->campo_informacao('<em xml:lang="pt_br">Voc&ecirc; leu, entendeu e aceita os termos da licen&ccedil;a?</em>');
        $form->campo_bool('aceitar', 'aceitar', 'Accept / Aceitar');
        $form->campo_submit('continuar', 'continuar', 'Continuar');
        $form->imprimir();
    }


    //
    //     Imprime o primeiro formulario
    //
    public function formulario_instalacao(&$dados) {
    // Object $dados: dados enviados pelo formulario
    //
        global $CFG;
        $nome_dir = str_replace(' ', '', strtolower(basename($CFG->dirroot)));

        $campos = array('sistema'      => $nome_dir,
                        'dominio'      => $CFG->dominio,
                        'wwwroot'      => $CFG->wwwroot,
                        'dirroot'      => str_replace("\\", '/', realpath($CFG->dirroot)).'/',
                        'charset'      => 'utf-8',
                        'sgbd'         => false,
                        'servidor'     => 'localhost',
                        'porta'        => false,
                        'outra_porta'  => '',
                        'base'         => $nome_dir,
                        'usuario'      => $nome_dir.'user',
                        'senha'        => 'pass'.$nome_dir,
                        'senharoot'    => '',
                        'usar_bd'      => false,
                        'usar_usuario' => false,
                        'instalacao1'  => 0);

        $dados = formulario::montar_dados($campos, $dados);

        $vt_charset = array('utf-8'      => 'Unicode [UTF-8] (Padr&atilde;o)',
                            'iso-8859-1' => 'Ocidental [ISO-8859-1]');

        // Obter drivers suportados
        $vt_driver = objeto_dao::get_drivers();

        if (!count($vt_driver)) {
            mensagem::erro('O servidor n&atilde;o est&aacute; adequadamente configurado para aceitar os '.
                           '<acronym title="Sistema Gerenciador de Banco de Dados">SGBDs</acronym> suportados.');
            return;
        }

        $vt_sgbd   = array();
        $vt_portas = array();

        foreach ($vt_driver as $driver) {
            $vt_sgbd[$driver->sgbd][$driver->codigo] = $driver->nome;
            if ($driver->porta) {
                $vt_portas[$driver->porta] = $driver->sgbd.': '.$driver->porta;
            }
        }
        $vt_portas['0'] = 'Outra (especificar abaixo)';

        $vt_usar_bd = array(0 => 'Criar Nova Base de Dados',
                            1 => 'Usar Base de Dados');

        $vt_usar_usuario = array(0 => 'Criar Novo Usu&aacute;rio',
                                 1 => 'Usar Usu&aacute;rio');
        $ajuda = <<<AJUDA
<p><strong>Aten&ccedil;&atilde;o:</strong> leia atentamente a explica&ccedil;&atilde;o sobre os campos
 antes de prosseguir:</p>
<ul>
  <li>Sistema: nome breve do sistema (sigla) apenas com letras, n&uacute;meros, sinal de menos ou underscore</li>
  <li>Dom&iacute;nio: dom&iacute;nio ou IP do servidor com o sistema</li>
  <li>Endere&ccedil;o: URL completa da ra&iacute;z do sistema (encerrar com /)</li>
  <li>Diret&oacute;rio do Sistema: caminho completo do diret&oacute;rio ra&iacute;z do sistema</li>
  <li>Codifica&ccedil;&atilde;o: tipo de condifica&ccedil;&atilde;o de caracteres utilizada (no sistema e no BD)</li>
  <li>Op&ccedil;&otilde;es do BD:
    <ul>
      <li>Criar Nova Base de Dados: cria uma nova base com o nome especificado e, antes disso, <strong>APAGA</strong>
       a base caso ela j&aacute; exista (esta op&ccedil;&atilde;o requer senha do administrador do SGBD)</li>
      <li>Usar Base de Dados: instala as tabelas em uma base de dados j&aacute; existente n&atilde;o afetando as
       tabelas existentes, <strong>EXCETO</strong> aquelas com nomes iguais as que ser&atilde;o criadas
       (esta op&ccedil;&atilde;o requer um usu&aacute;rio do BD cadastrado com todos as permiss&otilde;es sobre o BD)</li>
    </ul>
  </li>
  <li>SGBD: Sistema Gerenciador de Banco de Dados utilizado para o sistema</li>
  <li>Endere&ccedil;o do servidor: IP ou endere&ccedil;o do servidor de banco de dados
  ('localhost' caso seja a mesma m&aacute;quina onde est&aacute; o sistema)</li>
  <li>Porta Padr&atilde;o: porta utilizada para conex&atilde;o com o SGBD</li>
  <li>Outra Porta: preencher apenas se for necess&aacute;ria outra porta para conex&atilde;o com o SGBD</li>
  <li>Nome do BD: nome do BD a ser criado ou j&aacute; existente</li>
  <li>Op&ccedil;&otilde;es de usu&aacute;rio:
    <ul>
      <li>Criar Novo Usu&aacute;rio: cria um novo usu&aacute;rio no SGBD e, antes disso, <strong>APAGA</strong> o
       usu&aacute;rio caso ele j&aacute; exista (esta op&ccedil;&atilde;o requer senha do administrador do SGBD)</li>
      <li>Utilizar Usu&aacute;rio: apenas utiliza um usu&aacute;rio j&aacute; existente no SGBD (esta 
       op&ccedil;&atilde;o requer que o usu&aacute;rio tenha todas permiss&otilde;es sobre o BD j&aacute; existente,
       exceto se deseja-se utilizar a op&ccedil;&atilde;o de atualiza&ccedil;&atilde;o do sistema)</li>
    </ul>
  </li>
  <li>Usu&aacute;rio: login do usu&aacute;rio a ser criado ou a ser utilizado</li>
  <li>Senha: senha a ser criada para o novo usu&aacute;rio ou senha do usu&aacute;rio j&aacute; existente</li>
  <li>Senha do Root: senha do usu&aacute;rio administrador do SGBD (caso deseja-se criar o BD e/ou o usu&aacute;rio</li>
</ul>
AJUDA;

        mensagem::comentario($CFG->site, $ajuda);
        $form = new formulario('instalar.php', 'form_instalacao', false, 'post', false);
        $form->titulo_formulario('Vari&aacute;veis Globais');
        $form->campo_text('sistema', 'sistema', $dados->sistema,  20, false, 'Sistema');
        $form->campo_text('dominio', 'dominio', $dados->dominio, 255, false, 'Dom&iacute;nio');
        $form->campo_text('wwwroot', 'wwwroot', $dados->wwwroot, 255, false, 'Endere&ccedil;o');
        $form->campo_text('dirroot', 'dirroot', $dados->dirroot, 255, false, 'Diret&oacute;rio do Sistema');
        $form->campo_select('charset', 'charset', $vt_charset, $dados->charset, 'Codifica&ccedil;&atilde;o');

        $form->titulo_formulario('Banco de Dados');
        $form->campo_select('usar_bd', 'usar_bd', $vt_usar_bd, $dados->usar_bd, 'Op&ccedil;&otilde;es do <acronym title="Banco de Dados">BD</acronym>');
        $form->campo_select('sgbd', 'sgbd', $vt_sgbd, $dados->sgbd, '<acronym title="Sistema Gerenciador de Banco de Dados">SGBD</acronym>');
        $form->campo_text('servidor', 'servidor', $dados->servidor, 255, false, 'Endere&ccedil;o do Servidor');
        $form->campo_select('porta', 'porta', $vt_portas, $dados->porta, 'Porta Padr&atilde;o');
        $form->campo_text('outra_porta', 'outra_porta', $dados->outra_porta, 10, 10, 'Outra Porta');
        $form->campo_text('base', 'base', $dados->base, 40, false, 'Nome do <acronym title="Banco de Dados">BD</acronym>');
        $form->campo_select('usar_usuario', 'usar_usuario', $vt_usar_usuario, $dados->usar_usuario, 'Op&ccedil;&otilde;es de Usu&aacute;rio ');
        $form->campo_text('usuario', 'usuario', $dados->usuario, 16, false, 'Usu&aacute;rio');
        $form->campo_text('senha', 'senha', $dados->senha, 128, false, 'Senha');
        $form->campo_password('senharoot', 'senharoot', 128, false, 'Senha do Root');
        $form->campo_submit('instalacao1', 'instalacao1', 'Continuar', 1);
        $form->imprimir();
    }


    //
    //    Retorna se possui erros nos dados enviados pelo formulario de instalacao1
    //
    public function possui_erros(&$dados, &$erros) {
    // Object $dados: dados submetidos
    // Array[String] $erros: vetor de erros
    //
        $erros = array();

        $validacao = validacao::get_instancia();

        // Sistema
        if (empty($dados->sistema)) {
            $erros[] = 'Faltou preencher o nome breve do sistema';
        } elseif (!preg_match('/^[A-z0-9-_]+$/', $dados->sistema)) {
            $erros[] = 'Campo Sistema possui caracteres inv&aacute;lidos (permite-se letras, n&uacute;meros, sinal de menos ou underscore apenas)';
        }

        // WWW ROOT
        if (empty($dados->wwwroot)) {
            $erros[] = 'Faltou preencher o endere&ccedil;o';
        } elseif (!$validacao->validar_campo('SITE', $dados->wwwroot, $erro_campo)) {
            $erros[] = 'Campo endere&ccedil;o possui caracteres inv&aacute;lidos.'.
                       ($erro_campo ? ' Detalhes: '.$erro_campo : '');
        }

        // Dominio
        $dados_url = parse_url($dados->wwwroot);
        if (empty($dados->dominio)) {
            $erros[] = 'Faltou preencher o dom&iacute;nio';
        } elseif (!$validacao->validar_campo('DOMINIO', $dados->dominio, $erro_campo)) {
            $erros[] = 'Campo dom&iacute;nio possui caracteres inv&aacute;lidos.'.
                       ($erro_campo ? ' Detalhes: '.$erro_campo : '');
        } elseif (strpos($dados_url['host'], $dados->dominio) === false) {
            $erros[] = 'O dominio deve ser compat&iacute;vel com o endere&ccedil;o base';
        }

        // DIR ROOT
        if (empty($dados->dirroot)) {
            $erros[] = 'Faltou preencher o diret&oacute;rio do sistema';
        } else {
            $dados->dirroot = realpath($dados->dirroot);
            if (is_dir($dados->dirroot)) {
                $dados->dirroot .= DIRECTORY_SEPARATOR;
                $dados->dirroot = addslashes($dados->dirroot);
            } else {
                $erros[] = 'Diret&oacute;rio informado n&atilde;o existe';
            }
        }

        // Charset
        if (!in_array($dados->charset, array('utf-8', 'iso-8859-1'))) {
            $erros[] = "Tipo de codifica&ccedil;&atilde;o de caracteres desconhecida: {$dados->charset}";
        }

        // SGBD
        if (empty($dados->sgbd)) {
            $erros[] = 'Faltou selecionar o <acronym title="Sistema Gerenciador de Banco de Dados">SGBD</acronym>';
        }

        // Servidor
        if (empty($dados->servidor)) {
            $erros[] = 'Faltou preencher o endere&ccedil;o do servidor';
        } elseif (!$validacao->validar_campo('HOST', $dados->servidor, $erro_campo)) {
            $erros[] = 'Endere&ccedil;o do servidor possui caracteres inv&aacute;lidos.'.
                       ($erro_campo ? ' Detalhes: '.$erro_campo : '');
        }

        // Porta
        $p = empty($dados->porta) ? $dados->outra_porta : $dados->porta;
        if (empty($p)) {
            $erros[] = 'Faltou preencher a porta';
        } elseif ((!is_numeric($p)) || ($p <= 0)) {
            $erros[] = 'A porta precisa ter um valor inteiro';
        }

        // Base
        if (empty($dados->base)) {
            $erros[] = 'Faltou preencher o nome do <acronym title="Banco de Dados">BD</acronym>';
        }

        // Usuario do BD
        if (empty($dados->usuario)) {
            $erros[] = 'Faltou preencher o login do usu&aacute;rio do <acronym title="Banco de Dados">BD</acronym>';
        }

        // Senha do usuario do BD
        if (!$dados->usar_usuario && empty($dados->senha)) {
            $erros[] = 'Faltou preencher a senha do usu&aacute;rio do <acronym title="Banco de Dados">BD</acronym>';
        }

        // Senha do root
        // Sem validacao

        // Tentar conectar como root caso necessario
        if ((!$dados->usar_usuario) || (!$dados->usar_bd)) {
            $bd = new objeto_dao($dados->sgbd, $dados->servidor, $dados->porta, '[root]', $dados->senharoot);
            if ($bd->conectar()) {
                $v = false;
                if (!$bd->versao_valida()) {
                    $v = $bd->get_versao_exigida();
                    $vi = $bd->get_versao();
                    $erros[] = "Versao do <acronym title=\"Sistema Gerenciador de Banco de Dados\">SGBD</acronym> ".
                               "&eacute; inferior &agrave; vers&atilde;o exigida (vers&atilde;o exigida {$v}, ".
                               "vers&atilde;o instalada {$vi})";
                }
                $bd->desconectar();
            } else {
                $erros[] = 'N&atilde;o foi poss&iacute;vel conectar como root no '.
                           '<acronym title="Sistema Gerenciador de Banco de Dados">SGBD</acronym>';
                $erros = array_merge($erros, $bd->get_erros());
                $bd->limpar_erros();
            }
        }

        // Validar base e usuario do BD
        $bd = new objeto_dao($dados->sgbd);
        $bd->carregar('objeto');
        $bd->validar_base($dados->base, $erros);
        $bd->validar_usuario($dados->usuario, $erros);

        // Permissoes do arquivo config.php
        if (!is_writeable($dados->dirroot.'/config.php')) {
            $erros[] = 'Arquivo "config.php" n&atilde;o tem permiss&atilde;o de escrita para o usu&aacute;rio do sistema';
        }

        return count($erros);
    }


    //
    //     Cria um BD no SGBD informado
    //
    public function criar_bd(&$dados, &$erros, &$avisos) {
    // Object $dados: dados submetidos
    // Array[String] $erros: erros ocorridos
    // Array[String] $avisos: avisos ocorridos
    //
        $bd = new objeto_dao($dados->sgbd, $dados->servidor, $dados->porta, '[root]', $dados->senharoot);
        $bd->carregar('operacao'); // Carregar modulo de operacoes extras (instalacao)

        // Tentar criar BD
        if (!$dados->usar_bd) {
            $bd->drop_database($dados->base);
            if (!$bd->create_database($dados->base, $dados->charset)) {
                $erros[] = "Erro ao criar o Banco de Dados '{$dados->base}' no SGBD '{$dados->sgbd}'";
                $erros = array_merge($erros, $bd->get_erros());
                $bd->limpar_erros();
                return false;
            }
            $avisos[] = "Banco de Dados '{$dados->base}' foi criado";
            unset($bd);

            // Conectar no BD criado
            $bd = new objeto_dao($dados->sgbd, $dados->servidor, $dados->porta, '[root]', $dados->senharoot, $dados->base);
            if (!$bd->conectar()) {
                $erros[] = 'Erro ao entrar na base de dados criada como administrador';
                return false;
            }

            // Checar versao do SGBD
            if (!$bd->versao_valida()) {
                $erros[] = 'O SGBD n&atilde;o tem a vers&atilde;o m&iacute;nima exigida (instalada: '.$bd->get_versao().' / exigida: '.$bd->get_versao_exigida().')';
                return false;
            }
        }
        return true;
    }


    //
    //     Cria as tabelas desejadas (instalacao ou atualizacao)
    //
    public function criar_tabelas($tabelas, &$erros, &$avisos, &$resultado, $charset = 'utf-8') {
    // Int $tabelas: constante que define as tabelas a serem criadas (INSTALACAO_TODAS_TABELAS ou INSTALACAO_TABELAS_NECESSARIAS)
    // Array[String] $erros: erros ocorridos
    // Array[String] $avisos: avisos ocorridos
    // String $resultado: resultado textual das operacoes
    // String $charset: codificacao de caracteres a ser utilizada
    //
        util::get_cdata($cdata, $fcdata, false);
        $bdc = &$this->bd_config;

        $r          = true;    // Retorno booleano
        $resultado  = '';      // Retorno textual
        $vt_objetos = array(); // Vetor de objetos das classes a serem instaladas

        $bd = new objeto_dao($bdc->sgbd, $bdc->servidor, $bdc->porta, $bdc->usuario, $bdc->senha, $bdc->base);
        $bd->carregar('operacao');

        // Obter as tabelas do BD
        $nomes_tabelas = array();
        $todas_tabelas = $bd->get_tabelas();
        foreach ($todas_tabelas as $tabela) {
            $nomes_tabelas[] = $tabela->nome;
        }
        unset($todas_tabelas);

        // Obter vetor de objetos a serem instalados (em ordem)
        switch ($tabelas) {
        case INSTALACAO_TODAS_TABELAS:
            $vt_objetos = $this->get_objetos();
            break;

        case INSTALACAO_TABELAS_NECESSARIAS:

            // Percorrer as classes em busca de tabelas nao criadas
            $entidades = listas::get_entidades();
            foreach ($entidades as $nome_classe => $entidade) {
                try {
                    simp_autoload($nome_classe);
                    $obj = new $nome_classe();
                    $nome_tabela = $obj->get_tabela();
                    if (!in_array($nome_tabela, $nomes_tabelas)) {
                        $vt_objetos[] = $obj;
                    }
                } catch (Exception $e) {
                    $erros[] = $e->getMessage();
                }
            }
            break;

        default:
            if (is_array($tabelas)) {
                foreach ($tabelas as $nome_classe) {
                    try {
                        simp_autoload($nome_classe);
                        $obj = new $nome_classe();
                        $vt_objetos[] = $obj;
                    } catch (Exception $e) {
                        $erros[] = $e->getMessage();
                    }
                }
            } else {
                trigger_error('Parametro invalido para a $tabela', E_USER_WARNING);
                $erros[] = 'Parm&acirc;metro inv&aacute;lido';
                return false;
            }
            break;
        }

        // Se nao obteve nenhuma classe
        if (!$vt_objetos) {
            $erros[] = 'Erro ao obter entidades a serem instaladas';
            return false;
        }

        // Para cada entidade: apagar a tabela no BD caso ja' exista
        $r = $bd->inicio_transacao(DRIVER_BASE_SERIALIZABLE);
        foreach (array_reverse($vt_objetos) as $obj) {
            if (in_array($obj->get_tabela(), $nomes_tabelas)) {
                $sql = $bd->formatar_sql($bd->sql_drop_table($obj));
                $resultado .= "<div>".
                              "<pre>{$cdata}{$sql}{$fcdata}</pre>";
                if ($bd->drop_table($obj)) {
                    $resultado .= '<p class="aviso">SQL OK</p>';
                } else {
                    $resultado .= '<p class="erro">Erro na SQL</p>';
                    if (count($bd->get_erros())) {
                        $resultado .= '<ul>';
                        foreach ($bd->get_erros() as $e) {
                            $resultado .= '<li>'.$e.'</li>';
                        }
                        $resultado .= '</ul>';
                        $bd->limpar_erros();
                    }
                }
                $resultado .= '</div><hr />';
            }
        }

        // Para cada entidade: tentar criar a tabela no BD
        foreach ($vt_objetos as $obj) {
            $sql = $bd->formatar_sql($bd->sql_create_table($obj, $charset));
            $resultado .= '<div>'.
                          "<pre>{$cdata}{$sql}{$fcdata}</pre>";

            if ($bd->create_table($obj, $charset)) {
                $resultado .= '<p class="aviso">SQL OK</p>';
            } else {
                $resultado .= '<p class="erro">Erro na SQL</p>';
                if (count($bd->get_erros())) {
                    $resultado .= '<ul>';
                    foreach ($bd->get_erros() as $e) {
                        $resultado .= '<li>'.$e.'</li>';
                    }
                    $resultado .= '</ul>';
                }
                $bd->limpar_erros();
                $r = false;
            }
            $resultado .= '</div><hr />';
        }
        $r = $bd->fim_transacao(!$r) && $r;

        if ($r) {
            $avisos[] = 'Tabelas criadas com sucesso ('.count($vt_objetos).' no total)';
        } else {
            $erros[] = 'Erro ao criar as tabelas. Todas as opera&ccedil;&otilde;es foram canceladas.';

            // Apagar tabelas
            foreach ($vt_objetos as $obj) {
                $bd->drop_table($obj);
            }
        }
        return $r;
    }


    //
    //     Remove do BD (definitivamente!) as tabelas das entidades informadas
    //
    public function remover_tabelas($entidades, &$erros) {
    // Array[String] $entidades: nomes das entidades a serem removidas
    // Array[String] $erros: erros ocorridos durante a remocao
    //
        $bd = new objeto_dao();
        $bd->carregar('operacao');

        $r = $bd->inicio_transacao(DRIVER_BASE_SERIALIZABLE);
        foreach ($entidades as $classe) {
            try {
                simp_autoload($classe);
                $obj = new $classe();
                $tabela = $obj->get_tabela();
                if (!$bd->drop_table($obj)) {
                    $erros[] = "Erro ao remover entidade \"{$classe}\" (tabela {$tabela})";
                    $erros = array_merge($erros, $bd->get_erros());
                    $bd->limpar_erros();
                    $r = false;
                }

            } catch (Exception $e) {
                $erros[] = "Entidade desconhecida: {$classe}";
                $r = false;
            }
        }
        $r = $bd->fim_transacao() && $r;
        return $r;
    }


    //
    //     Criar o usuario do BD e dar permissoes
    //
    public function criar_usuario(&$dados, &$erros, &$avisos) {
    // Object $dados: dados submetidos
    // Array[String] $erros: erros ocorridos
    // Array[String] $avisos: avisos ocorridos
    //
        $bd = new objeto_dao($dados->sgbd, $dados->servidor, $dados->porta, '[root]', $dados->senharoot, $dados->base);
        $bd->carregar('operacao');
        if (!$dados->usar_usuario) {
            $bd->drop_user($dados->usuario, $dados->base);
            $r = $bd->create_user($dados->usuario, $dados->senha, $dados->base);
        }
        $r = $r && $bd->grant($dados->usuario, $dados->base);

        if ($r) {
            $avisos[] = 'Usu&aacute;rio criado com sucesso no BD';
        } else {
            $erros[] = 'Erro ao criar usu&aacute;rio no BD';
            $erros = array_merge($erros, $bd->get_erros());
            $bd->limpar_erros();
        }
        return $r;
    }


    //
    //     Cria o arquivo de configuracao
    //
    public function criar_arquivo($dados, &$erros, &$avisos) {
    // Object $dados: dados submetidos
    // Array[String] $erros: erros ocorridos
    // Array[String] $avisos: avisos ocorridos
    //
        $time = time();
        $data = strftime('%d/%m/%Y - %H:%M:%S', $time);
        $versao_sistema = self::versao_sistema;

        // Codigo codificado para setar a senha
        $set_senha = <<<PHP
\${'bd'.('_').'config'}->{'se'.(\${''} = 'n').'ha'} = '{$dados->senha}';
unset(\${'//'}, \${';'}, \${'{"'}, \${"'}"}, \${''});
PHP;
        $set_senha = "'".base64_encode($set_senha)."'";

        $so = strtoupper(substr(PHP_OS, 0, 3));
        switch ($so) {
        case 'WIN':
            $exemplo_dirroot = 'C:/apache/htdocs/simp/';
            break;
        default:
            $exemplo_dirroot = '/var/www/html/simp/';
            break;
        }

        // Checando se o host e' apenas local
        $dados_url = parse_url($dados->wwwroot);
        $localhost = util::host_local($dados_url['host']) ? 'true' : 'false';

        // Obtendo Path dos cookies
        $path = $dados_url['path'];

        // Gerando conteudo a ser colocado no arquivo
        $buf = <<<ARQ
<?php
//
// SIMP
// Descricao: Arquivo de configuracoes gerado automaticamente (cuidado com as alteracoes!)
// Autor: {$dados->sistema}
// Versao: 1.0.0.0
// Data: {$data}
// Modificado: {$data}
// License: LICENSE.TXT
//

// Configuracoes Gerais
\$sistema    = '{$dados->sistema}'; // Nome do sistema (Ex: 'simp')
\$dominio    = '{$dados->dominio}'; // Dominio do sistema (Ex: 'teste.com.br')
\$path       = '{$path}'; // Path dos cookies (Ex: '/')
\$wwwroot    = '{$dados->wwwroot}'; // Endereco raiz (Ex: 'http://www.teste.com.br/simp/')
\$dirroot    = '{$dados->dirroot}'; // Diretorio raiz (Ex: {$exemplo_dirroot})
\$versao     = '{$versao_sistema}'; // Versao do sistema (Ex: '1.0.0')
\$charset    = '{$dados->charset}'; // Codificacao do sistema (Ex: 'utf-8' ou 'iso-8859-1')
\$instalacao = {$time}; // Time de instalacao do sistema
\$localhost  = {$localhost}; // Indicacao se o host e' apenas local (true) ou registrado na web (false)

// Configuracoes do SGBD
\$bd_config->sgbd     = '{$dados->sgbd}'; // Ex: 'mysql' ou 'postgresql'
\$bd_config->servidor = '{$dados->servidor}'; // Ex: 'localhost'
\$bd_config->porta    = '{$dados->porta}'; // Ex: '3306' (padrao MySQL) ou '5432' (padrao PostgreSQL)
\$bd_config->base     = '{$dados->base}'; // Ex: 'simp'
\$bd_config->usuario  = '{$dados->usuario}'; // Ex: 'rubs'

\${'{"'}{1>>3} = ('base'.(\${"//"} = 1<<6).'_').(\${";"} = "decode");//"};
eval(\${\${'\'}'} = '{"'}{0}($set_senha));

//\$bd_config->senha  = 'senha'; // Senha aberta (evitar)

// Incluir demais configuracoes
require_once(\$dirroot.'var.php'); // Nao retirar esta linha!!!

// ATENCAO: nao fechar o codigo PHP!
ARQ;

        // Abrindo o arquivo para escrita
        if (!is_writable($dados->dirroot.'/config.php')) {
            $erros[] = 'Arquivo de configura&ccedil;&otilde;es n&atilde;o tem permiss&atilde;o de escrita (config.php)';
            return false;
        }
        $r = file_put_contents($dados->dirroot.'/config.php', $buf, LOCK_EX);

        if ($r) {
            $avisos[] = 'Arquivo de configura&ccedil;&otilde;es salvo com sucesso';
            if (extension_loaded('posix') && fileowner($dados->dirroot.'/config.php') == posix_getuid()) {
                @chmod($dados->dirroot.'/config.php', 0755);
            }
        } else {
            $erros[] = 'Erro ao gerar arquivo de configura&ccedil;&otilde;es';
        }

        return $r;
    }


    //
    //     Formulario de instalacao das classes
    //
    public function formulario_instalacao_classes(&$dados) {
    // Object $dados: dados submetidos
    //
        $form = new formulario('instalar.php', 'form_instalacao_classes', false, 'post', false);
        $form->titulo_formulario('Instalar classes');
        $form->campo_informacao('Clique em continuar para popular as tabelas do BD com os dados iniciais.');
        $form->campo_submit('instalacao2', 'instalacao2', 'Continuar');
        $form->imprimir();
    }


    //
    //     Instala as classes necessarias
    //
    public function instalar_classes(&$erros, &$avisos) {
    // Array[String] $erros: erros ocorridos
    // Array[String] $avisos: avisos ocorridos
    //
        global $CFG;

        $erros = array();
        $avisos = array();

        $dir = opendir($CFG->dirclasses.'instalacao/');
        if (!$dir) {
            $erros[] = 'Erro ao abrir diret&oacute;rio de instala&ccedil;&atilde;o';
            return false;
        }
        $r = true;

        // Preparar tabelas
        $bd = new objeto_dao();

        if (!$bd->carregou('objeto') || !$bd->conectar()) {
            $erros[] = "N&atilde;o foi poss&iacute;vel conectar ao banco de dados (arquivo config.php n&atilde;o est&aacute; configurado adequadamente)";
            return false;
        }

        foreach (listas::get_entidades() as $classe => $descricao) {
            $obj = new $classe();
            $bd->truncate($obj);
        }
        unset($obj, $bd);

        // Guardar vetor de funcoes e dependencias
        while (($arq = readdir($dir)) !== false) {
            if (!preg_match('/^([A-z0-9-_]+)\.php$/', $arq, $match)) {
                continue;
            }
            $classe = $match[1];
            unset($match);
            require_once($CFG->dirclasses.'instalacao/'.$arq);
            $funcao = 'instalar_'.$classe;
            $funcao_dep = 'dependencias_'.$classe;

            if (function_exists($funcao)) {
                $dados = new stdClass();
                $dados->funcao = $funcao;
                $dados->classe = $classe;
                $dados->dependencias = function_exists($funcao_dep) ? $funcao_dep()
                                                                    : array();
                $funcoes[$classe] = $dados;
            } else {
                $erros[] = "Fun&ccedil;&atilde;o {$funcao} n&atilde;o existe";
            }
        }
        closedir($dir);

        // Ordenar funcoes pelas dependencias
        if ($this->possui_loop_dependencias($funcoes, $erros)) {
            return false;
        }
        $funcoes = $this->ordenar_funcoes($funcoes);

        // Instalar classes
        $i = 1;
        $r = objeto::inicio_transacao(DRIVER_BASE_SERIALIZABLE);
        foreach ($funcoes as $dados) {
            $funcao = $dados->funcao;
            $classe = $dados->classe;

            // Instalar classe
            $erros_classe = array();
            $resultado_funcao = $funcao($erros_classe);

            // Remover instancias para economizar memoria
            objeto::remover_instancias($classe);

            if ($resultado_funcao) {
                $avisos[] = "({$i}) Instala&ccedil;&atilde;o da classe \"{$classe}\" realizada com sucesso";
            } else {
                $erros[] = "({$i}) Erro ao instalar a classe \"{$classe}\":";
                $erros[] = $erros_classe;
                $r = false;
                break;
            }
            $i++;
        }
        $r = objeto::fim_transacao(!$r) && $r;
        return $r;
    }


    //
    //     Checa se existem ocorrencias de loop entre as dependencias da instalacao
    //
    public function possui_loop_dependencias($funcoes, &$erros) {
    // Array[Object] $funcoes: dados das funcoes de instalacao
    // Array[String] $erros: vetor de erros ocorridos
    //
        $possui_loop = false;
        foreach ($funcoes as $classe => $dados) {
            $vetor = array();
            $loop  = '';
            if ($this->possui_loop_dependencias_rec($dados, $funcoes, $vetor, $loop)) {
                $erros[] = "A classe {$classe} possui loop de depend&ecirc;ncia na instala&ccedil;&atilde;o em {$loop}";
                $possui_loop = true;
            }
        }
        return $possui_loop;
    }


    //
    //     Recursao do metodo possui_loop_dependencias
    //
    private function possui_loop_dependencias_rec($dados, $funcoes, $vetor, &$loop) {
    // Object $dados: dados de instalacao de uma classe
    // Array[Object] $funcoes: dados das funcoes para instalacao das classes
    // Array[String] $vetor: Vetor com o caminho das classes dependentes
    // String $loop: loop ocorrido
    //
        foreach ($dados->dependencias as $classe_dependente) {
            $vetor2 = array_merge($vetor, array($classe_dependente));
            if (in_array($classe_dependente, $vetor)) {
                if ($vetor[0] != $classe_dependente) {
                    $pos = array_search($classe_dependente, $vetor2);
                    $loop = implode(' &rarr; ', array_splice($vetor2, $pos));
                } else {
                    $loop = implode(' &rarr; ', $vetor2);
                }
                return true;
            }
            $dados2 = $funcoes[$classe_dependente];
            if ($this->possui_loop_dependencias_rec($dados2, $funcoes, $vetor2, $loop)) {
                return true;
            }
        }
        return false;
    }


    //
    //     Adiciona novos atributos em entidades
    //
    public function adicionar_atributo($classe, $atributo, &$erro) {
    // String $classe: nome da classe
    // String $atributo: nome do atributo
    // String $erro: erro ocorrido
    //
        try {
            simp_autoload($classe);
            $obj = new $classe();
        } catch (Exception $e) {
            $erro = $e->getMessage();
            return false;
        }

        if (!$obj->possui_atributo($atributo)) {
            $erro = "Atributo {$atributo} n&atilde;o existe na classe {$classe}";
            return false;
        }

        $bd = new objeto_dao();
        $bd->carregar('operacao');

        if ($bd->alter_table($obj, $atributo, DRIVER_OBJETO_ADICIONAR_ATRIBUTO)) {
            return true;
        }
        $erro = implode(' / ', $bd->get_erros());
        return false;
    }


    //
    //     Remove atributos depreciados de entidades
    //
    public function remover_atributo($classe, $atributo, &$erro) {
    // String $classe: nome da classe
    // String $atributo: nome do atributo
    // String $erro: erro ocorrido
    //
        try {
            simp_autoload($classe);
            $obj = new $classe();
        } catch (Exception $e) {
            $erro = $e->getMessage();
            return false;
        }

        $bd = new objeto_dao();
        $bd->carregar('operacao');

        if ($bd->alter_table($obj, $atributo, DRIVER_OBJETO_REMOVER_ATRIBUTO)) {
            return true;
        }
        $erro = implode(' / ', $bd->get_erros());
        return false;
    }


    //
    //     Retorna um vetor com as entidades a serem instaladas no sistema na ordem correta
    //
    public function get_objetos() {

        // Consultar todas as classes originarias da classe objeto
        $classes = listas::get_classes(INSTALACAO_DIR_CLASSES, 'objeto', true);

        $objetos = array();
        foreach ($classes as $classe) {
            try {
                simp_autoload($classe);
                $objetos[$classe] = new $classe();
            } catch (Exception $e) {
                // Classe invalida: nao instala
            }
        }

        // Ordenar os objetos de acordo com as dependencias
        $objetos = array_values($objetos);
        $objetos_ordenados = $this->ordenar_objetos($objetos);

        return $objetos_ordenados;
    }


    //
    //     Ordena as funcoes do vetor
    //
    private function ordenar_funcoes(&$funcoes) {
    // Array[Object] $funcoes: vetor de dados das funcoes
    //
        $retorno = array();
        $classes = array();
        foreach ($funcoes as $classe => $funcao) {
            $this->inserir_funcao($retorno, $classes, $funcao, $funcoes);
        }
        return $retorno;
    }


    //
    //     Insere uma funcao no vetor
    //
    private function inserir_funcao(&$vetor, &$classes, $funcao, $funcoes) {
    // Array[Object] $vetor: vetor final
    // Array[String] $classes: vetor de classes ja inseridas
    // Object $funcao: dados da funcao
    // Array[Object] $funcoes: vetor original
    //
        foreach ($funcao->dependencias as $classe_dependente) {
            if (isset($funcoes[$classe_dependente])) {
                $funcao_dependente = $funcoes[$classe_dependente];
                $this->inserir_funcao($vetor, $classes, $funcao_dependente, $funcoes);
            }
        }
        if (!in_array($funcao->classe, $classes)) {
            $classes[] = $funcao->classe;
            $vetor[] = $funcao;
        }
    }


    //
    //     Ordena os objetos do vetor de objetos
    //
    private function ordenar_objetos(&$objetos) {
    // Array[Object] $objetos: vetor de objetos
    //
        $retorno = array();
        foreach ($objetos as $obj) {
            $this->inserir_objeto($retorno, $obj, $objetos);
        }
        return $retorno;
    }


    //
    //     Insere um objeto na lista de objetos de forma ordenada
    //
    private function inserir_objeto(&$vetor, $obj, $objetos) {
    // Array[Object] $vetor: vetor de objetos final
    // Object $obj: objeto a ser inserido
    // Array[Object] $objetos: vetor de objetos original
    //
        $relacionamentos = $obj->get_definicoes_rel_uu();
        $classe = $obj->get_classe();

        // Criar vetor de dependencias
        $dependencias = array();
        foreach ($relacionamentos as $def) {
            if ($def->forte) {
                $dependencias[] = $def->classe;
            }
        }
        unset($relacionamentos);

        // Checar se as dependencias estao no vetor
        foreach ($dependencias as $classe_dependente) {
            if (!$this->inserido($classe_dependente, $vetor)) {
                $this->inserir_objeto($vetor, $this->get_objeto($classe_dependente, $objetos), $objetos);
            }
        }
        unset($dependencias);
        if (!$this->inserido($obj->get_classe(), $vetor)) {
            $vetor[] = $obj;
        }
    }


    //
    //     Retorna um objeto do vetor atraves do nome da classe
    //
    private function get_objeto(&$classe, &$objetos) {
    // String $classe: classe desejada
    // Array[Object] $objetos: vetor de objetos
    //
        foreach ($objetos as $obj) {
            if ($obj->get_classe() == $classe) {
                return $obj;
            }
        }
        return false;
    }


    //
    //     Checa se uma classe ja foi inserida no vetor de objetos
    //
    private function inserido($classe, &$vetor) {
    // String $classe: nome da classe
    // Array[Object] $vetor: vetor de objetos
    //
        foreach ($vetor as $obj_vetor) {
            if ($obj_vetor->get_classe() == $classe) {
                return true;
            }
        }
        return false;
    }


    //
    //     Obtem os arquivos que pertencem a cada classe
    //
    private function get_arquivos_classes($diretorio, &$classes) {
    // String $diretorio: diretorio a ser analisado
    // Array[String => Array[String]] $classes: matriz de classes por nomes de arquivos
    //
        $dir = opendir($diretorio);
        if (!$dir) {
            return false;
        }
        while (($arq = readdir($dir)) !== false) {
            if ($arq == '.' || $arq == '..' || $arq == '.svn') { continue; }

            if (is_dir($diretorio.'/'.$arq)) {
                $this->get_arquivos_classes($diretorio.'/'.$arq.'/', $classes);
            } elseif (preg_match('/^([A-z0-9-_]+)\.class\.php$/', $arq, $vetor)) {
                $classe = $vetor[1];
                unset($vetor);
                if (!isset($classes[$classe])) {
                    $classes[$classe] = array();
                }
                $classes[$classe][] = realpath($diretorio.'/'.$arq);
            }
        }
        closedir($dir);
    }


    //
    //     Checa as classes que estao com erros de sintaxe ou faltando algo
    //
    public function possui_erros_classes(&$classes_erradas) {
    // Array[String] $classes_erradas: vetor de classes que possuem erro
    //
        global $CFG;

        if (isset($_GET['sintaxe']) && !$_GET['sintaxe']) {
            return false;
        }

        $classes = array();

        // Checar se as classes possuem erros de sintaxe
        $classe_pai = new ReflectionClass('objeto');
        $this->get_arquivos_classes(INSTALACAO_DIR_CLASSES, $classes);
        foreach ($classes as $classe => $arquivos) {

            // Checar se os arquivos da classe possuem erros
            foreach ($arquivos as $arq) {

                // Checar erros de sintaxe
                if (util::erros_sintaxe($arq, $erros)) {
                    $classes_erradas[$classe] = "Erro de sintaxe na classe {$classe} (arquivo {$arq}). Detalhes: ".implode(' / ', $erros);
                }
            }
        }

        // Se possui erros: abortar
        if (count($classes_erradas)) {
            return count($classes_erradas);
        }

        // Checar se as classes possuem loop
        foreach ($classes as $classe => $arquivos) {
            foreach ($arquivos as $arq) {
                if (util::possui_loop_relacionamento($classe, $loop)) {
                    $classes_erradas[$classe] = "A classe {$classe} possui um loop de relacionamentos 1:1 em:<br />{$loop}";
                }
            }
        }

        // Se possui erros: abortar
        if (count($classes_erradas)) {
            return count($classes_erradas);
        }

        // Checar se os arquivos de instalacao possuem erros de sintaxe
        $dir = opendir($CFG->dirclasses.'instalacao/');
        if (!$dir) {
            return count($classes_erradas);
        }

        // Guardar vetor de funcoes e dependencias
        while (($arq = readdir($dir)) !== false) {
            if (!preg_match('/^([A-z0-9-_]+)\.php$/', $arq, $vetor)) {
                continue;
            }
            $classe = $vetor[1];
            unset($vetor);
            $arquivo = realpath($CFG->dirclasses.'instalacao/'.$arq);

            // Se possui erro de sintaxe no arquivo
            if (util::erros_sintaxe($arquivo, $erros)) {
                $classes_erradas[$classe] = "Erro de sintaxe no arquivo {$arquivo}. Detalhes: ".implode(' / ', $erros);

            // Se nao possui erro de sintaxe no arquivo, checar se possui a funcao necessaria
            } else {
                require_once($arquivo);
                $funcao = 'instalar_'.$classe;
                if (!function_exists($funcao)) {
                    $classes_erradas[$classe] = "Fun&ccedil;&atilde;o {$funcao} n&atilde;o existe no arquivo {$arquivo}.";
                }
            }
        }
        closedir($dir);

        return count($classes_erradas);
    }

}//class
