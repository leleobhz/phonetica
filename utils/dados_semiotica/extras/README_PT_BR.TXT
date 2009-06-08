Para instalar o Sistema, basta seguir os passos:

1 - Checar os requisitos minimos do servidor:
 a - Servidor Web Apache com suporte a PHP e SGBD MySQL ou PostgreSQL;
 b - PHP versão 5.1 ou superior;
 c - MySQL versão 5.0 ou superior ou PostgreSQL versão 8.0 ou superior.

2 - Mover o diretorio simp para algum local visivel na Internet/Intranet;

3 - Executar o script simp-prepare (para Linux), conforme o exemplo:
$ simp-prepare

OU copiar o arquivo config.bk.php com o nome config.php (em Windows)

4 - Acessar o endereco do sistema com visibilidade na Internet/Intranet;

5 - Preencher os formularios de configuracao atentando-se a ajuda;

6 - Pronto!

Algum problema durante a instalacao?
Copie o arquivo config.bk.php com o nome config.php, sobrescrevendo-o,
apague os cookies do navegador e tente instalar novamente.

########################################################################

Sugestoes para o administrador:
- Configurar o arquivo php.ini para apontar a diretiva browscap para o
arquivo php_browscap.ini, segundo o exemplo:
browscap = /var/www/html/simp/arquivos/php_browscap.ini

- Adicionar um comando no agendador de tarefas do Servidor para atualizar 
o arquivo php_browscap.ini fazendo o seguinte comando no Linux:
$ crontab -e

E adicionando a seguinte linha (exemplo) no arquivo:
0 4 1 * * wget -O /var/www/html/simp/arquivos/php_browscap.ini -nv http://browsers.garykeith.com/stream.asp?PHP_BrowsCapINI

Isso fara o servidor atualizar o arquivo uma vez por mes 'as 04:00am.

--

Rubens Takiguti Ribeiro
06/12/2007
