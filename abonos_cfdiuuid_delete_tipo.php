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
	
	//Buscar XFolio de Abono PPAL 
	
	$resSQL02 = "SELECT * FROM " . $prefijobd . "abonosuuidrelacionadosub WHERE ID = ".$idfacturasub;
	$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
	$rowSQL02 = mysql_fetch_assoc($runSQL02);
	do { 
		$idfoliosub = $rowSQL02['FolioSub_RID'];
		$tiporelacion2 = $rowSQL02['TipoRelacion2'];
	} while ($rowSQL02 = mysql_fetch_assoc($runSQL02)); 
	
	
	if($tiporelacion2=='066'){
		$resSQL01 = "SELECT * FROM " . $prefijobd . "abonos WHERE ID = ".$idfoliosub;
		$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
		$rowSQL01 = mysql_fetch_assoc($runSQL01);
		do { 
			$xfolio2 = $rowSQL01['XFolio'];
		} while ($rowSQL01 = mysql_fetch_assoc($runSQL01)); 
		
		//FIN Buscar XFolio de Abono PPAL 
			
		//Buscar ID en Remisiones para actualizar RelacionadoPor
		$resSQL00 = "SELECT * FROM " . $prefijobd . "abonos WHERE XFolio = '".$xfoliosub."'";
		$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
		$rowSQL00 = mysql_fetch_assoc($runSQL00);
		do { 
			$idremision = $rowSQL00['ID'];
		} while ($rowSQL00 = mysql_fetch_assoc($runSQL00)); 
		
		//Actualizar RelacionadoPor en Remision Seleccionada
		$sql_update = "UPDATE " . $prefijobd . "abonos SET 
			RelacionadoPor = ''
			WHERE ID = ".$idremision;
			
		mysql_query($sql_update,$cnx_cfdi);
		
		
		//Eliminar registro de FolioSubUUIDRelacionado
		$sql_delete = "DELETE FROM " . $prefijobd . "abonosuuidrelacionadosub 
			WHERE ID = ".$idfacturasub;
			
		mysql_query($sql_delete,$cnx_cfdi);
		
		
		echo "<h2>Abono Relacionado: <b>".$xfoliosub."</b> Se removio del Abono: <b>".$xfolio2."</b></h2>";
	}else {
				$resSQL01 = "SELECT * FROM " . $prefijobd . "abonos WHERE ID = ".$idfoliosub;
		$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
		$rowSQL01 = mysql_fetch_assoc($runSQL01);
		do { 
			$xfolio2 = $rowSQL01['XFolio'];
		} while ($rowSQL01 = mysql_fetch_assoc($runSQL01)); 
		
		//FIN Buscar XFolio de Abono PPAL 
		
		
			
		//Buscar ID en Abono con XFolio para actualizar cfdiSustituidaPor
		$resSQL00 = "SELECT * FROM " . $prefijobd . "abonos WHERE XFolio = '".$xfoliosub."'";
		$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
		$rowSQL00 = mysql_fetch_assoc($runSQL00);
		do { 
			$idfactura = $rowSQL00['ID'];
		} while ($rowSQL00 = mysql_fetch_assoc($runSQL00)); 
		
		//Actualizar SustituidaPor en Abono Seleccionada
		$sql_update = "UPDATE " . $prefijobd . "abonos SET 
			cfdiSustituidaPor = '',
			TipoRelacion = ''
			WHERE ID = ".$idfactura;
			
		mysql_query($sql_update,$cnx_cfdi);
		
		//Actualizar Tipo Relacion en Abono Original
		$sql_update2 = "UPDATE " . $prefijobd . "abono SET 
			TipoRelacion = ''
			WHERE ID = ".$idfoliosub;
				
			mysql_query($sql_update2,$cnx_cfdi);
		
		
		//Eliminar registro de FolioSubUUIDRelacionado
		$sql_delete = "DELETE FROM " . $prefijobd . "abonosuuidrelacionadosub 
			WHERE ID = ".$idfacturasub;
			
		mysql_query($sql_delete,$cnx_cfdi);
		
		
		echo "<h2>Abono Relacionado: <b>".$xfoliosub."</b> Se removio del Abono: <b>".$xfolio2."</b></h2>";

	}
	
	//Limpiar campo TipoRelacion de la Abono
	$res55 = "SELECT * FROM " . $prefijobd . "abonosuuidrelacionadosub WHERE FolioSub_RID = ".$idfoliosub;
	$run55 = mysql_query($res55, $cnx_cfdi);
	$total_reg = mysql_num_rows($run55);
	
	if($total_reg > 0){
		while($rowSQL55 = mysql_fetch_array($run55)){
			$id_uuidrelacionado= $rowSQL55['ID'];
			$var_tiporelacion= $rowSQL55['TipoRelacion'];
		}
		$sql_update55 = "UPDATE " . $prefijobd . "abonos SET 
			TipoRelacion = '".$var_tiporelacion."'
			WHERE ID = ".$idfoliosub;
			
		mysql_query($sql_update55,$cnx_cfdi);
	} else {
		$sql_update55 = "UPDATE " . $prefijobd . "abonos SET 
			TipoRelacion = ''
			WHERE ID = ".$idfoliosub;
			
		mysql_query($sql_update55,$cnx_cfdi);
	}
	
	
	
	
	
	

?>