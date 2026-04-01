<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}

	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Factura");
	}


	//Internalizo los parametros previo escape de caracteres especiales
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

	$idfacturasub = $_GET["id"];
	
	
	$xfoliosub = $_GET["xfolio"];


	//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
	$pos = strpos($prefijobd, "_");

	if ($pos === false) {
		$prefijobd = $prefijobd . "_";
	} 	
	
	//Buscar XFolio de Factura PPAL 
	
	$resSQL02 = "SELECT * FROM " . $prefijobd . "facturauuidrelacionadosub WHERE ID = ".$idfacturasub;
	$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
	$rowSQL02 = mysql_fetch_assoc($runSQL02);
	do { 
		$idfoliosub = $rowSQL02['FolioSub_RID'];
	} while ($rowSQL02 = mysql_fetch_assoc($runSQL02)); 
	
	
	$resSQL01 = "SELECT * FROM " . $prefijobd . "factura WHERE ID = ".$idfoliosub;
	$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
	$rowSQL01 = mysql_fetch_assoc($runSQL01);
	do { 
		$xfolio2 = $rowSQL01['XFolio'];
	} while ($rowSQL01 = mysql_fetch_assoc($runSQL01)); 
	
	//FIN Buscar XFolio de Factura PPAL 
	
	
		
	//Buscar ID en Factura con XFolio para actualizar cfdiSustituidaPor
	$resSQL00 = "SELECT * FROM " . $prefijobd . "factura WHERE XFolio = '".$xfoliosub."'";
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	$rowSQL00 = mysql_fetch_assoc($runSQL00);
	do { 
		$idfactura = $rowSQL00['ID'];
	} while ($rowSQL00 = mysql_fetch_assoc($runSQL00)); 
	
	//Actualizar SustituidaPor en Factura Seleccionada
	$sql_update = "UPDATE " . $prefijobd . "factura SET 
		cfdiSustituidaPor = ''
		WHERE ID = ".$idfactura;
		
	mysql_query($sql_update,$cnx_cfdi);
	
	
	//Eliminar registro de FolioSubUUIDRelacionado
	$sql_delete = "DELETE FROM " . $prefijobd . "facturauuidrelacionadosub 
		WHERE ID = ".$idfacturasub;
		
	mysql_query($sql_delete,$cnx_cfdi);
	
	
	echo "<h2>Factura Relacionada: <b>".$xfoliosub."</b> Se removio de la Factura: <b>".$xfolio2."</b></h2>";
	
	

?>