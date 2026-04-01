<?php
//Inicio la transaccion

	require_once('cnx_cfdi2.php');
	mysqli_select_db($cnx_cfdi2,$database_cfdi);    

	
	
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}

	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Liquidaciones");
	}


	//Internalizo los parametros previo escape de caracteres especiales
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

	$id_liq = $_GET["id"];
	


	//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
	$pos = strpos($prefijobd, "_");

	if ($pos === false) {
		$prefijobd = $prefijobd . "_";
	} 	

	//Buscar XFolio Liq
	//Buscar registros de LiquidacionesIAVE
	$sql001="SELECT * FROM " . $prefijobd . "liquidaciones WHERE ID = ".$id_liq;
	$res_sql001=mysqli_query($cnx_cfdi2,$sql001);								
	while ($fila_sql001 = mysqli_fetch_array($res_sql001)){
		$xfolio_liq = $fila_sql001['XFolio'];
	}
	

	//Buscar registros de LiquidacionesIAVE
	$sql00="SELECT * FROM " . $prefijobd . "liquidacionesIAVE WHERE FolioSubLiqIAVE_RID = ".$id_liq;
	$res_sql00=mysqli_query($cnx_cfdi2,$sql00);								
	while ($fila_sql00 = mysqli_fetch_array($res_sql00)){
		$id_liqiave = $fila_sql00['ID'];
		$id_iave = $fila_sql00['zID_RID'];


		//Poner en blanco campo Liquidaciones de IAVE
		$sql_update1 = "UPDATE " . $prefijobd . "iave SET 
		Liquidacion = ''
		WHERE Liquidacion ='".$xfolio_liq."'";
			
		mysqli_query($cnx_cfdi2,$sql_update1);

		//echo $sql_update1."<br>";


		


	}

	//Eliminar registros Liquidaciones IAVE 
	$sql_delete1 = "DELETE FROM " . $prefijobd . "liquidacionesIAVE 
	WHERE FolioSubLiqIAVE_RID = ".$id_liq;
		
	mysqli_query($cnx_cfdi2,$sql_delete1);
	
	
	
	echo "<h2>IAVES relacionadas a la Liquidacion eliminadas correctamente</h2>";



?>