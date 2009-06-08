Follow the steps to intall the system

1 - Review the minimum requirements of server:
 a - Apache Web Server with PHP support and MySQL or PostgreSQL DBMS;
 b - PHP version 5.1 or higher;
 c - MySQL version 5.0 or higher OR PostgreSQL version 8.0 or higher.

2 - Move simp directory to a visible local in Internet/Intranet;

3 - Run the simp-prepare script (on Linux), as the example:
$ simp-prepare

OR copy the config.bk.php file to config.php (on Windows)

4 - Access the system URL in Internet/Intranet;

5 - Fill the configuration forms (attention to help link)

6 - End!

Does any problem ocours during the installation?
Copy config.bk.php file to config.php,
so clear your browser cookies and try again.

########################################################################

Other sugests:
- Configure the php.ini file to link the browscap directive to
php_browscap.ini file, as follow:
browscap = /var/www/html/simp/arquivos/php_browscap.ini

- Add a command on task manager on the Server to update de php_browscap.ini
once a month. Run the exemple command (on Linux):
$ crontab -e

And add the follow line (example) in the cron file:
0 4 1 * * wget -O /var/www/html/simp/arquivos/php_browscap.ini -nv http://browsers.garykeith.com/stream.asp?PHP_BrowsCapINI

This will update the file on server once a month at 04:00am.

--

Rubens Takiguti Ribeiro
06/12/2007
