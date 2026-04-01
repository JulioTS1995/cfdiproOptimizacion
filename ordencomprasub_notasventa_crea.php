<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}

	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Orden de Compra");
	}
	
	if (!isset($_GET['xfolio']) || empty($_GET['xfolio'])) {
    die("Falta XFolio");
}
	
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

	$idordencompra = $_GET["id"];
	
	$xfolio_ordencompra = $_GET["xfolio"];
	
	$x=0;



	//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
	$pos = strpos($prefijobd, "_");

	if ($pos === false) {
		$prefijobd = $prefijobd . "_";
	}
	
	//Buscar ID de Orden de compra en Notas de venta
	$resSQL00 = "SELECT * FROM " . $prefijobd . "notasventa WHERE FolioSubOrdenCompra_RID = ".$idordencompra;
	//echo $resSQL00;
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	//$rowSQL00 = mysql_fetch_assoc($runSQL00);
	while($rowSQL00 = mysql_fetch_assoc($runSQL00)) {
        $idnotaventa = $rowSQL00['ID'];
		//echo "<br>";
		//echo $idnotaventa;
		$xfolio_notaventa = $rowSQL00['XFolio'];
	}
	
	//Buscar Notas de Venta Sub relacionadas
	$resSQL01 = "SELECT * FROM " . $prefijobd . "notasventa_ref WHERE ID = ".$idnotaventa;
	$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
	//$rowSQL01 = mysql_fetch_assoc($runSQL00);
	while($rowSQL01 = mysql_fetch_assoc($runSQL01)) {
        $idnotaventasub = $rowSQL01['RID'];
		
		//Buscar datos de cada una de las Notas de Venta Sub 
		$resSQL02 = "SELECT * FROM " . $prefijobd . "notasventasub WHERE ID = ".$idnotaventasub;
		$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
		//$rowSQL02 = mysql_fetch_assoc($runSQL00);
		while($rowSQL02 = mysql_fetch_assoc($runSQL02)) {
			$concepto = $rowSQL02['Concepto'];
			$descripcion = $rowSQL02['Descripcion'];
			$subtotal = $rowSQL02['Subtotal'];
			$cantidad = $rowSQL02['Cantidad'];
			$precio_unitario = $rowSQL02['PrecioUnitario'];
			$descuento_importe = $rowSQL02['DescuentoImporte'];
			
		}
		
		//Hacer insert en Orden de Compra Sub
		//////////////////////////////////////////////////////////////////////////////////////////////////////Obtengo el siguiente BASIDGEN
		$begintrans = mysql_query("BEGIN", $cnx_cfdi);
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
		/////////////////////////////////////////////////////////////////////////////////////////////////////FIN Obtengo el siguiente BASIDGEN
		$newid = $basidgen;
		$time = time();
		$fecha = date("Y-m-d H:i:s", $time);
		
		$sql = "INSERT INTO " . $prefijobd . "ordencomprassub (ID, Nombre, Importe, Cantidad, PrecioUnitario, Descuento, FolioSub_RID, FolioSub_REN, FolioSub_RMA, BASTIMESTAMP) VALUES (". $newid .", '". $concepto ." - ".$descripcion."', ". $subtotal .", ". $cantidad .", ".$precio_unitario.", ".$descuento_importe.", ".$idordencompra.", 'OrdenCompra', 'FolioSub', '".$fecha."')";
	
		mysql_query($sql,$cnx_cfdi);
		
		$x++;
		
			
			
		
	} //Fin Busca notas de venta sub relacionadas
	
	
	echo "<h2>Conceptos de Nota de Venta: ".$xfolio_notaventa." se importaron correctamente ".$x." conceptos en la Orden de Compra: ".$xfolio_ordencompra."</h2>";




?>