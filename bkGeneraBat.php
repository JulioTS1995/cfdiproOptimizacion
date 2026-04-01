<?php
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");
$prefijobd = "prueba_";

$resSQL="SELECT TABLE_NAME from Information_Schema.Tables WHERE TABLE_NAME LIKE '".$prefijobd."%'";



	$runSQL=mysql_query($resSQL, $cnx_cfdi);

	if (!$runSQL) {
		$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL;
		die($mensaje);
	}
	
	$txt="echo on \n

pause \n

set pwd=Wec!EStE5osl@_GeprUc
set pf=prueba_ \n
set dir=C:\bk\%pf%\ \n
mkdir %dir% \n

cd C:\Program Files\MySQL\MySQL Server 5.6\bin 
\n";
	while ($rowSQL=mysql_fetch_array($runSQL)){
		//Obtener_variables
        $tbl = $rowSQL['TABLE_NAME'];
		
		$txt.="mysqldump.exe -u root -p%pwd% basdb ".$tbl." > %dir%".$tbl.".sql 
		\n";
		$txt.="\n";
		
		

	}
echo (".bat generado");
 $file = fopen("__prueba.bat", "w");

fwrite($file, $txt . PHP_EOL);


fclose($file);
?>