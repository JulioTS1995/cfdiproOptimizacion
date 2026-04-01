<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	$begintrans = mysql_query("BEGIN", $cnx_cfdi);
	
		
	//Obtengo el siguiente BASIDGEN
	$qry_basidgen = "SELECT MAX_ID from bas_idgen";
	$result_qry_basidgen = mysql_query($qry_basidgen, $cnx_cfdi);
	
	if (!$result_qry_basidgen){
		//No pude obtener el siguiente basidgen
		$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
		echo "Error4";
	}
	else {
				
		//Le sumo uno y hago el update
		$rowbasidgen = mysql_fetch_row($result_qry_basidgen);
				
		$basidgen = $rowbasidgen[0]+1;
				
		//echo "<br>Basidgen" . $basidgen . "<br>";
				
		$upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
		$result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
				
		if ($result_upd_basidgen) {
		//Se hizo el update sin problemas
		$endtrans = mysql_query("COMMIT", $cnx_cfdi);
		}
		
	}
	
	$newid = $basidgen;
	$cfdiuuid = $_GET["cfdiuuid"];
	$xfolio = $_GET["xfolio"];
	$idfactura = $_GET["foliofactura"];
	$prefijodb = $_GET["prefijodb"];
	$time = time();
	$fecha = date("Y-m-d H:i:s", $time);
	$facturaorigen = $_GET["facturaorigen"];
	$id_factura_sel = $_GET["id_factura_sel"];
	
	//Datos de Prueba
	/*$newid = $basidgen;
	$cfdiuuid = 'folio008';
	$xfolio = 'PR4';
	$idfactura = $_GET["foliofactura"];
	$prefijodb = $_GET["prefijodb"];*/
	//Fin datos de Prueba
	
	
	//Validar si el Abono ya tiene anexado otro Abono
	$resSQL1 = "SELECT COUNT(*) as total FROM " . $prefijodb . "abonosuuidrelacionadosub WHERE FolioSub_RID = ".$idfactura;
	//$resSQL1 = "SELECT * FROM " . $prefijobd . "factura WHERE ID = 706102";
	//echo "Numero: ".$numero;
	//echo $resSQL1;
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	while($rowSQL1 = mysql_fetch_array($runSQL1)){
		$v_total = $rowSQL1['total'];
	}
	
	if($v_total == 0){
	
	
		$sql = "INSERT INTO " . $prefijodb . "abonosuuidrelacionadosub (ID, cfdiuuidRelacionado, XFolio, FolioSub_RID, FolioSub_REN, FolioSub_RMA, BASTIMESTAMP) VALUES (". $newid .", '". $cfdiuuid ."', '". $xfolio ."', ". $idfactura .", 'Abonos', 'FolioSubUUIDRelacionado', '".$fecha."')";
		
		mysql_query($sql,$cnx_cfdi);
		
		//Actualizar Sustituida Por en Abono Seleccionado
		$sql_update = "UPDATE " . $prefijodb . "abonos SET 
			cfdiSustituidaPor = '".$facturaorigen."'
			WHERE ID = ".$id_factura_sel;
			
		mysql_query($sql_update,$cnx_cfdi);
				
		
		echo "<h1>CDP ".$xfolio."  Anexado con Exito al CDP: ".$facturaorigen."</h1>";
	
	} else {
		echo "<h1>NOTA: El CDP: ".$facturaorigen." ya tiene cfdiuuid Relacionado, no es posible relacionar mas CDP</h1>";
	}
	



?>