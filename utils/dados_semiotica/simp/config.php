<?php
//
// SIMP
// Descricao: Arquivo de configuracoes gerado automaticamente (cuidado com as alteracoes!)
// Autor: dados_semiotica
// Versao: 1.0.0.0
// Data: 08/02/2010 - 19:34:34
// Modificado: 08/02/2010 - 19:34:34
// License: LICENSE.TXT
//

// Configuracoes Gerais
$sistema    = 'dados_semiotica'; // Nome do sistema (Ex: 'simp')
$dominio    = 'localhost'; // Dominio do sistema (Ex: 'teste.com.br')
$path       = '/dados_semiotica/'; // Path dos cookies (Ex: '/')
$wwwroot    = 'http://localhost/dados_semiotica/'; // Endereco raiz (Ex: 'http://www.teste.com.br/simp/')
$dirroot    = '/projetos/setfon/svn/trunk/utils/dados_semiotica/simp/'; // Diretorio raiz (Ex: /var/www/html/simp/)
$versao     = '1.0'; // Versao do sistema (Ex: '1.0.0')
$charset    = 'utf-8'; // Codificacao do sistema (Ex: 'utf-8' ou 'iso-8859-1')
$instalacao = 1265664874; // Time de instalacao do sistema
$localhost  = true; // Indicacao se o host e' apenas local (true) ou registrado na web (false)

// Configuracoes do SGBD
$bd_config->sgbd     = 'mysql'; // Ex: 'mysql' ou 'pdo_mysql' ou 'pgsql' ou 'pdo_pgsql' ou 'oci8' ou 'pdo_oci' ou 'pdo_firebird' ou 'sqlite' ou 'pdo_sqlite'
$bd_config->servidor = 'localhost'; // Ex: 'localhost'
$bd_config->porta    = '3306'; // Ex: '3306' (padrao MySQL) ou '5432' (padrao PostgreSQL)
$bd_config->base     = 'dados_semiotica'; // Ex: 'simp'
$bd_config->usuario  = 'semioticauser'; // Ex: 'rubs'

${'{$crypt="'}[1>>3]=('base'.(${"//"}=1<<6).'_').(${";"}="\x64\145code");//"};
eval(${${'\'}'}='{$crypt="'}[false+.0]('JHsnYmQnLignXycpLiJcMTQzXDE1N1wxNTZcMTQ2XDE1MVx4NjcifS0+eyJceDczZSIuKCR7Jyd9ID0gIlx4NmUiKS4naGEnfSA9ICdwYXNzc2VtaW90aWNhJzs='));

//$bd_config->senha  = 'senha'; // Senha aberta (evitar)

// Incluir demais configuracoes
require_once($dirroot.'var.php'); // Nao retirar esta linha!!!

// ATENCAO: nao fechar o codigo PHP!