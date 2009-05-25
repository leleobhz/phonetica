<?
$db = new mysqli($db_server,$db_user,$db_pass,$db_database);
if (mysqli_connect_errno()) die('Não foi possível conectar: ' . mysqli_connect_errno());
$db->set_charset('utf8');
?>
