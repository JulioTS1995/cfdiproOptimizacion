<?php

	require_once('cnx_usuarios.php');
    	mysql_select_db($database_usr, $cnx_usr);
	//echo $_GET["usuario"];
	$resSQL = "UPDATE Usuario SET activo=activo+1,Fecha=Now() WHERE IdCliente=1 AND usuario='".$_GET["usuario"]."';";
	//echo $resSQL." - ".$_GET["usuario"];
	$runSQL = mysql_query($resSQL, $cnx_usr);
	//echo "<script>window.close();</script>";

?>