<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<TITLE>SetFon</TITLE>
</HEAD>
<BODY>
<?
include("conf.php");
include("db.php");
include("funcoes.php");

$amostra=2;
$d='';
Gera_Texto_Acento($amostra,&$d);
echo "<P>Depuracao:<P>".nl2br($d)."<P>";



$db->close();
?>
</BODY>
</HTML>
