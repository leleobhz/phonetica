<?

function Gera_Texto_Acento($amostra, $depuracao)
{
  global $db,$cmd_csp,$regras_Acentua;

  $query = "SELECT Texto FROM amostra WHERE Cod_Amostra=$amostra";

  if ($query=$db->query($query) and $row=$query->fetch_assoc()) 
  {
    $descriptorspec = array(0=>array("pipe","r"),1=>array("pipe","w"),2=>array("pipe","w"));
    $process = proc_open("$cmd_csp -d -r=$regras_Acentua", $descriptorspec, $pipes);
    if (is_resource($process)) {
      fwrite($pipes[0], $row["Texto"]);
      fclose($pipes[0]);

      $r=''; while (!feof($pipes[1])) $r.=fgets($pipes[1], 4096);
      $d=''; while (!feof($pipes[2])) $d.=fgets($pipes[2], 4096);
    
      fclose($pipes[1]);
      fclose($pipes[2]);
    
      $depuracao = $d;

      if (proc_close($process)==0)
      {
        if (!$db->query("UPDATE amostra SET Texto_Acento=\"".$db->real_escape_string($r)."\" WHERE Cod_Amostra=$amostra;")) echo $db->error;
      } else
      {
        echo "Erro $pr na execuчуo.\n";
      }
    }
    $query->close();
  }
}


?>