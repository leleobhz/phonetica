<?php
//
// SIMP
// Descricao: Classe Usuario
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.31
// Data: 22/08/2007
// Modificado: 22/05/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class usuario extends usuario_base {

    //
    //     Define a forma como um atributo simples e' exibido
    //
    public function exibir_atributo($nome_atributo) {
    // String $nome_atributo: nome do atributo a ser exibido
    //
        switch ($nome_atributo) {
        case 'email':
            return texto::proteger_email($this->__get('email'));
        }
        return parent::exibir_atributo($nome_atributo);
    }


    //
    //     Retorna se um usuario pode manipular os dados de outro usuario
    //
    public function pode_ser_manipulado(&$usuario) {
    // usuario $usuario: usuario a ser testado
    //
        return $usuario->possui_grupo(COD_ADMIN) ||
               $this->get_valor_chave() == $usuario->get_valor_chave();
    }


    //
    //     Indica se o formulario de um registro pode ser acessado ou nao por um usuario
    //
    public function pode_acessar_formulario(&$usuario, &$motivo = '') {
    // usuario $usuario: usuario a ser testado
    // String $motivo: motivo pelo qual nao se pode acessar o registro
    //
        if ($usuario->possui_grupo(COD_ADMIN)) {
            return true;
        }
        switch ($this->id_form) {
        case $this->id_formulario_alterar('pessoal'):
            return true;
        case $this->id_formulario_alterar('senha'):
            return true;
        }
        return false;
    }


    //
    //     Realiza a validacao final (de dados dependentes)
    //
    public function validacao_final(&$dados) {
    // Object $dados: objeto com os valores originais submetidos
    //
        switch ($this->id_form) {

        // Formulario para cadastro de analista
        case objeto::get_objeto('analista')->id_formulario_inserir():

        // Formulario para alterar senha
        case $this->id_formulario_alterar('senha'):

            // Checar se a confirmacao esta' correta
            if (!isset($dados->confirmacao) || empty($dados->confirmacao)) {
                $this->erros[] = 'N&atilde;o foi preenchida a confirma&ccedil;&atilde;o';
                return false;
            } elseif (strcmp($dados->senha, $dados->confirmacao) != 0) {
                $this->erros[] = 'Senha n&atilde;o confere com a confirma&ccedil;&atilde;o';
                return false;
            }
            break;

        // Formulario de inserir usuarios
        case $this->id_formulario_inserir('pessoal'):

            if ($dados->geracao_senha == USUARIO_SENHA_ESPECIFICA) {

                // Checar se a confirmacao esta' correta
                if (!isset($dados->confirmacao) || empty($dados->confirmacao)) {
                    $this->erros[] = 'N&atilde;o foi preenchida a confirma&ccedil;&atilde;o';
                    return false;
                } elseif (strcmp($dados->senha_sugerida, $dados->confirmacao) != 0) {
                    $this->erros[] = 'Senha n&atilde;o confere com a confirma&ccedil;&atilde;o';
                    return false;
                } elseif (!$this->validar_atributo('senha', $dados->senha_sugerida)) {
                    return false;
                }

            } elseif (!empty($dados->senha_sugerida) || !empty($dados->confirmacao)) {
                $this->avisos[] = 'Aten&ccedil;&atilde;o: a senha especificada n&atilde;o foi utilizada!';
            }

        // Formulario de definicao de grupos
        case $this->id_formulario_relacionamento('grupos'):
            if ($this->id_form == $this->id_formulario_relacionamento('grupos')) {
                $vt_grupos = isset($dados->cod_grupo) ? $dados->cod_grupo : array();
            } elseif ($this->id_form == $this->id_formulario_inserir('pessoal')) {
                $vt_grupos = isset($dados->vetor_grupos) ? $dados->vetor_grupos : array();
            }

            /**
            //TODO: inserir a logica de validacao de grupos AQUI (exemplo abaixo)

            // Se esta em X, precisa estar em Y
            if (in_array(COD_GRUPO_X, $vt_grupos)) {

                // Se nao marcou Y
                if ((!in_array(COD_GRUPO_Y, $vt_grupos))) {
                    $this->erros[] = 'Para ser X precisa ser Y';
                    return false;
                }
            }
            */
            break;
        }
        return true;
    }


    //
    //     Retorna se o usuario possui o grupo informado
    //
    public function possui_grupo($cod_grupo) {
    // Int $cod_grupo: codigo do grupo
    //
        return array_key_exists($cod_grupo, $this->__get('grupos'));
    }


    //
    //     Retorna a senha criptografada
    //
    public function codificar($senha) {
    // String $senha: senha nao codificada
    //
        $senha = utf8_decode($senha);
        return md5($senha);
        //return sha1($senha);
        //return crypt($senha, 'SP');
    }


    //
    //     Verifica se a senha esta correta
    //
    public function validar_senha($login, $senha, &$erros) {
    // String $login: login a ser verificado em autenticacao LDAP e usuario Linux
    // String $senha: senha a ser comparada com a do objeto
    // Array[String] $erros: vetor de erros ocorridos
    //
        // Autenticacao tradicional ou e' o admin
        if (USUARIO_TIPO_AUTENTICACAO == 'simp' || $this->get_valor_chave() == 1) {
            if (strcmp($this->codificar($senha), $this->__get('senha')) != 0) {
                $erros[] = 'Senha inv&aacute;lida';
                return false;
            }
            return true;
        }

        // Autenticacao via driver
        $credenciais = array('login' => $login,
                             'senha' => $senha);
        $autenticacao = new autenticacao(USUARIO_TIPO_AUTENTICACAO);
        if (!$autenticacao->set_credenciais($credenciais, $erros)) {
            return false;
        }
        if (!$autenticacao->autenticar_usuario($erros)) {
            return false;
        }

        // Checar se ja existe o usuario no BD local
        $classe = $this->get_classe();
        $u = new $classe('login', $login);
        if ($u->existe()) {
            return true;
        }
        $u->limpar_objeto();
        $u->login = $login;
        $u->senha = $senha;

        $dados = $autenticacao->get_dados_usuario();

        $dominio = USUARIO_DOMINIO;
        if ($dominio[0] == '.') {
            $dominio = substr($dominio, 1);
        }

        // Criar o usuario caso nao exista
        switch (USUARIO_TIPO_AUTENTICACAO) {
        case 'aut_imap':
            $u->nome  = isset($dados['personal']) ? $dados['personal'] : ucfirst($login);
            $u->email = isset($dados['email'])    ? $dados['email']    : $login.'@'.$dominio;
            break;

        case 'aut_ldap':
            $u->nome  = isset($dados['cn'])   ? $dados['cn']   : ucfirst($login);
            $u->email = isset($dados['mail']) ? $dados['mail'] : $login.'@'.$dominio;
            break;

        case 'aut_linux':
            $u->nome  = isset($dados['gecos']) ? $dados['gecos']             : ucfirst($login);
            $u->email = isset($dados['name'])  ? $dados['name'].'@'.$dominio : $login.'@'.$dominio;
            break;

        default:
            $u->nome  = ucfirst($login);
            $u->email = $login.'@'.$dominio;
            break;
        }

        $salvar_campos = array('nome', 'login', 'email', 'senha');
        if (!$u->salvar_completo($salvar_campos)) {
            $erros = array_merge($erros, $u->get_erros());
            $erros[] = 'Erro ao cadastrar usu&aacute;rio no sistema';
            return false;
        }
        return true;
    }


    //
    //     Operacoes pre-salvar
    //
    public function pre_salvar(&$salvar_campos) {
    // Array[String] $salvar_campos: vetor de campos a serem salvos
    //
        switch ($this->id_form) {

        // Envia e-mail para o usuario caso esteja sendo cadastrado
        case $this->id_formulario_inserir('pessoal'):
            $salvar_campos[] = 'senha';
            $geracao_senha = $this->get_auxiliar('geracao_senha');

            // Gerar senha aleatoria
            if ($geracao_senha == USUARIO_SENHA_ALEATORIA) {
                $senha = senha::gerar(USUARIO_TAM_SENHA, true);
                $this->__set('senha', $senha);

                if (!$this->enviar_senha($senha)) {
                    return false;
                }

            // Especifiar senha vinda do formulario
            } elseif ($geracao_senha == USUARIO_SENHA_ESPECIFICA) {
                $senha = $this->get_auxiliar('senha_sugerida');
                $this->__set('senha', $senha);
            }
            break;
        }
        return !$this->possui_erros();
    }


    //
    //     Opcoes pos-salvar
    //
    public function pos_salvar() {
        switch ($this->id_form) {

        // Define os grupos do usuario
        case $this->id_formulario_inserir('pessoal'):
            $r = true;
            foreach (array_unique($this->get_auxiliar('vetor_grupos')) as $cod_grupo) {
                if (!$cod_grupo) { continue; }
                $dados = new stdClass();
                $dados->cod_grupo = (int)$cod_grupo;
                $r = $r && $this->inserir_elemento_rel_un('grupos', $dados);
            }
            if ($r) {
                $this->avisos[] = 'Grupos definidos com sucesso';
            } else {
                $this->erros[] = 'Erro ao definir os grupos';
            }
            return $r;
            break;

        case objeto::get_objeto('analista')->id_formulario_inserir():
            $obj = new stdClass();
            $obj->cod_grupo = COD_ANALISTAS;
            return $this->inserir_elemento_rel_un('grupos', $obj);
            break;
        }
        return true;
    }


    //
    //     Envia uma senha por e-mail
    //
    public function enviar_senha($senha, $nova = false) {
    // String $senha: senha gerada aleatoriamente
    // Bool $nova: indica se e' uma nova senha ou o usuario esta' sendo cadastrado
    //
        // Gerar mensagem
        if (!$nova) {
            $assunto = 'Cadastro no Sistema';
            $msg = "Prezado(a) ".$this->__get('nome').",\n".
                   "   Informamos que voce acaba de ser cadastrado no Sistema ".
                   USUARIO_NOME_SISTEMA.' - '.USUARIO_DESCRICAO_SISTEMA."\n\n".
                   "   Link para acesso ao Sistema: ".USUARIO_LINK_ACESSO."\n\n".
                   "   Os dados para acesso estao abaixo:\n".
                   "login: ".$this->__get('login')."\n".
                   "senha: {$senha}\n\n".
                   "Obs: a senha foi gerada aleatoriamente. Por favor nao interprete-a ".
                   "como uma palavra de tom ofensivo.";

            $msg_html = "<p>Prezado(a) <em>".texto::codificar($this->__get('nome'))."</em>,<br />\n".
                        "Informamos que voc&ecirc; acaba de ser cadastrado(a) no Sistema ".
                        USUARIO_NOME_SISTEMA.' - '.USUARIO_DESCRICAO_SISTEMA."</p>\n".
                        "<p>Link para acesso ao Sistema: <a href=\"".USUARIO_LINK_ACESSO."\">".
                        USUARIO_LINK_ACESSO."</a>.</p>\n".
                        "<p>Os dados para acesso est&atilde;o abaixo:</p>\n".
                        "<p><strong>login:</strong> ".$this->__get('login')."</p>\n".
                        "<p><strong>senha:</strong> {$senha}</p>".
                        "<p><small>Obs: a senha foi gerada aleatoriamente. Por favor n&atilde;o ".
                        "interprete-a como uma palavra de tom ofensivo.</small></p>\n";
        } else {
            $assunto = 'Nova senha';
            $msg = "Prezado(a) ".$this->__get('nome').",\n".
                   "   Informamos a sua nova senha solicitada pelo Sistema ".
                   USUARIO_NOME_SISTEMA.' - '.USUARIO_DESCRICAO_SISTEMA."\n\n".
                   "   Link para acesso ao Sistema: ".USUARIO_LINK_ACESSO."\n\n".
                   "   Os novos dados para acesso estao abaixo:\n".
                   "login: ".$this->__get('login')."\n".
                   "senha: {$senha}\n".
                   "Obs: a senha foi gerada aleatoriamente. Por favor nao interprete-a ".
                   "como uma palavra de tom ofensivo.";

            $msg_html = "<p>Prezado(a) <em>".texto::codificar($this->__get('nome'))."</em>,<br />\n".
                        "Informamos a sua nova senha solicitada pelo Sistema ".
                        USUARIO_NOME_SISTEMA.' - '.USUARIO_DESCRICAO_SISTEMA."</p>\n".
                        "<p>Link para acesso ao Sistema: <a href=\"".USUARIO_LINK_ACESSO."\">".
                        USUARIO_LINK_ACESSO."</a>.</p>\n".
                        "<p>Os novos dados para acesso est&atilde;o abaixo:</p>\n".
                        "<p><strong>login:</strong> ".$this->__get('login')."</p>\n".
                        "<p><strong>senha:</strong> {$senha}</p>".
                        "<p><small>Obs: a senha foi gerada aleatoriamente. Por favor n&atilde;o ".
                        "interprete-a como uma palavra de tom ofensivo.</small></p>\n";
        }

        // Enviar e-mail
        $email = new email($assunto);
        $email->set_destinatario($this->__get('nome'), $this->__get('email'));

        // Pode-se escolher entre um metodo ou outro (obrigatorio apenas um)
        $email->set_mensagem($msg);
        $email->set_mensagem($msg_html, 1);

        if (!$email->enviar()) {
            $this->erros = array_merge($this->erros, $email->get_erros());
            $this->erros[] = 'Erro ao enviar e-mail com a senha para o usu&aacute;rio';
            return false;
        }
        return true;
    }


    //
    //     Imprime um campo do formulario
    //
    public function campo_formulario(&$form, $campo, $valor) {
    // formulario $form: objeto do tipo formulario
    // String $campo: campo a ser adicionado
    // Mixed $valor: valor a ser preenchido automaticamente
    //
        if ($this->possui_atributo($campo)) {
            $atributo = $this->get_definicao_atributo($campo);
        }

        switch ($campo) {

        // Campos de Senha
        case 'senha':
            $form->campo_password($atributo->nome, $atributo->nome, $atributo->maximo, 30, $atributo->get_label($this->id_form));
            return true;

        case 'confirmacao':
            $atributo = $this->get_definicao_atributo('senha');
            $form->campo_password('confirmacao', 'confirmacao', $atributo->maximo, 30, 'Confirma&ccedil;&atilde;o');
            return true;

        case 'senha_sugerida':
            $atributo = $this->get_definicao_atributo('senha');
            $form->campo_password('senha_sugerida', 'senha_sugerida', $atributo->maximo, 30, 'Senha Sugerida');
            return true;

        // Campo radio
        case 'geracao_senha':
            if (!$valor) {
                $valor = USUARIO_SENHA_ALEATORIA;
            }
            $vetor = array(
                           USUARIO_SENHA_ALEATORIA  => 'Gerar senha aleat&oacute;ria',
                           USUARIO_SENHA_ESPECIFICA => 'Definir senha'
                          );
            $form->campo_aviso('Utilize "gerar senha aleat&oacute;ria" para gerar uma senha e '.
                               'envi&aacute;-la por e-mail ou utilize "definir senha" e '.
                               'especifique uma senha abaixo (n&atilde;o ser&aacute; enviada '.
                               'por e-mail)');
            $form->campo_radio($campo, $campo, $vetor, $valor);
            return true;

        // Campo Grupo
        case 'vetor_grupos':
            $grupo = new grupo();
            $vt_grupos = array('0' => 'Nenhum') + $grupo->vetor_associativo();
            $total_grupos = count($vt_grupos) - 1;
            $id_dom = 'area_'.$form->montar_id($campo);

            // Se ja' submeteu o formulario uma vez
            if (is_array($valor) && count($valor)) {
                $i = 0;
                foreach (array_unique($valor) as $item) {
                    $form->campo_select($campo.'[]', $campo.$i, $vt_grupos, $item, $grupo->get_entidade());
                    $i++;
                }
                $total_clones = $total_grupos - $i + 1;
                $form->campo_clone('document.getElementById("'.$id_dom.'")', $grupo->get_entidade(), $total_clones);

            // Se esta' exibindo o formulario pela primeira vez
            } elseif (FORMULARIO_AJAX) {
                $form->campo_select($campo.'[]', $campo, $vt_grupos, 0, 'Grupo');
                $form->campo_clone('document.getElementById("'.$id_dom.'")', $grupo->get_entidade(), $total_grupos);
            }
            return true;

        // Campo bool
        case 'exibir_ajuda_senha':
            $form->campo_aviso('O campo abaixo pode exibir informa&ccedil;&otilde;es importantes sobre a sua senha, '.
                               'como a pontua&ccedil;&atilde;o obtida com vogais e consoantes.');
            $form->campo_bool($campo, $campo, 'Exibir detalhes sobre a qualidade da senha', $valor);
            return true;
        }

        return parent::campo_formulario($form, $campo, $valor);
    }


    //
    //     Faz a logica de negocios do formulario de geracao de nova senha
    //
    public function logica_formulario_nova_senha(&$dados, &$campos) {
    // Object $dados: dados submetidos
    // Array[String] $campos: campos necessarios para validacao
    //
        // Se os dados nao foram submetidos
        if (isset($dados->default)) {
            return;
        }

        if (!captcha::validar($dados->captcha)) {
            $this->erros[] = 'Texto da imagem n&atilde;o corresponde';
            $this->imprimir_erros();
            return;
        }

        $classe = $this->get_classe();

        // Tenta setar os valores
        $this->instancia->flag_unicidade = false;
        if (!$this->set_valores($dados->$classe, $campos)) {
            $this->imprimir_erros();
            return;
        }
        $this->instancia->flag_unicidade = true;

        // Checar se o usuario existe
        $primeiro_campo = $campos[0];
        $this->limpar_objeto();
        $this->consultar($primeiro_campo, $dados->$classe->$primeiro_campo, $campos);

        // Se nao existe
        if (!$this->existe()) {
            $this->limpar_erros();
            $this->erros[] = 'N&atilde;o existe usu&aacute;rio com o valor informado no campo "'.$primeiro_campo.'"';
            $this->imprimir_erros();
            return;
        }

        // Checar se todos os campos batem
        $ok = true;
        foreach ($campos as $c) {
            if ($c == 'captcha') { continue; }
            if ($this->__get($c) != $dados->$classe->$c) {
                $ok = false;
                break;
            }
        }
        if (!$ok) {
            $this->erros[] = "Os campos n&atilde;o conferem ({$c})";
            $this->imprimir_erros();
            return;
        }

        // Se esta' tudo OK
        $s = senha::gerar(USUARIO_TAM_SENHA, true);
        $this->__set('senha', $s);

        $config = new config();

        $r = objeto::inicio_transacao();
        $r = $r && $this->salvar();
        if ($config->autenticacao != 'simp') {
            $r = $r && $this->salvar_nova_senha($s);
        }
        if ($r) {
            $r = $r && $this->enviar_senha($s, 1);
        }
        $r = objeto::fim_transacao(!$r) && $r;

        // Se conseguir salvar os dados e enviar a senha por e-mail
        if ($r) {
            $this->avisos[] = 'Senha enviada para o e-mail';
        } else {
            $this->imprimir_erros();
            return;
        }

        // A senha e' sobrescrita ate' na memoria RAM do servidor
        $s = str_repeat(rand(0,9), USUARIO_TAM_SENHA + 1);

        // Se conseguiu setar os valores, mostrar aviso
        $this->imprimir_avisos();
    }


    //
    //     Salva a nova senha em outra base de autenticacao
    //
    protected function salvar_nova_senha($senha) {
    // String $senha: nova senha
    //
        $config = new config();
        $autenticacao = new autenticacao($config->autenticacao);
        $credenciais['login'] = $this->__get('login');
        $credenciais['senha'] = $senha;
        $autenticacao->set_credenciais($credenciais);
        return $autenticacao->alterar_senha($this->erros);
    }


    //
    //     Imprime um formulario para gerar nova senha
    //
    public function formulario_nova_senha(&$dados, &$campos, $action) {
    // Object $dados: dados submetidos
    // Array[String] $campos: campos exigidos para solicitar nova senha
    // String $action: endereco de destino dos dados
    //
        $config = new config();
        if ($config->autenticacao != 'simp') {
            $autenticacao = new autenticacao($config->autenticacao);
            $nome_autenticacao = $autenticacao->get_nome();
            if (!$autenticacao->pode_alterar_senha()) {
                echo '<div style="text-align: left;">';
                echo '<p>N&atilde;o &eacute; poss&iacute;vel gerar uma nova senha, pois a forma de autentica&ccedil;&atilde;o no sistema &eacute; via "'.$nome_autenticacao.'".</p>';
                echo '<p>Isso significa que o login/senha utilizado para acessar o sistema est&atilde;o cadastrados em outro local, e o sistema n&atilde;o pode atualiz&aacute;-los.</p>';
                echo '</div>';
                return;
            }
        }

        $this->set_id_form('form_nova_senha');
        $vt_campos = $this->get_campos_reais($campos);
        $form = $this->montar_formulario($action, $this->id_form, false, $campos, $dados, false, 'Solicitar Senha');
        $r = $this->logica_formulario_nova_senha($dados, $vt_campos);
        $form->imprimir();
        return $r;
    }


    //
    //     Checa se um usuario tem acesso ao arquivo do modulo
    //
    public function checar_permissao($modulo, $arquivo) {
    // String $modulo: nome do modulo
    // String $arquivo: nome do arquivo
    //
        return (bool)$this->get_arquivo($modulo, $arquivo);
    }


    //
    //     Obtem os dados do arquivo caso o usuario tenha permissao
    //
    public function get_arquivo($modulo, $arquivo) {
    // String $modulo: nome do modulo
    // String $arquivo: nome do arquivo
    //
        static $vt_cache = array();
        static $consultou = 0;
        if ($consultou) {
            return isset($vt_cache[$modulo.':'.$arquivo]) ? $vt_cache[$modulo.':'.$arquivo] : false;
        }
        foreach ($this->__get('grupos') as $usuarios_grupos) {
            foreach ($usuarios_grupos->grupo->permissoes as $p) {
                $vt_cache[$p->arquivo->modulo.':'.$p->arquivo->arquivo] = $p->arquivo;
            }
        }
        $consultou = true;
        return $this->get_arquivo($modulo, $arquivo);
    }

}//class
