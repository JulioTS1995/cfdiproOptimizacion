<?php

$xfolio = $_POST['xfolio'];
$prefijodb = 'ardica_';

$time = time();
$fecha = date("Y-m-d H:i:s", $time);

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
$begintrans = mysql_query("BEGIN", $cnx_cfdi);

//Buscar ID de Remisiones
$resSQL0 = "SELECT COUNT(*) as total3 FROM " . $prefijodb . "remisiones WHERE XFolio =  '".$xfolio."'";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){
	$v_total0 = $rowSQL0['total3'];
}

if($v_total0 == 0){
	echo "<script>
			alert('El XFOLIO NO EXISTE');
			window.location= 'ardica_update_remisiones.php'
		  </script>";
} else{


	$resSQL1 = "SELECT * FROM " . $prefijodb . "remisiones WHERE XFolio = '".$xfolio."'";
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	while($rowSQL1 = mysql_fetch_array($runSQL1)){
		$v_id_factura = $rowSQL1['ID'];
		$v_yFlete = $rowSQL1['yFlete'];
		$v_ySeguro = $rowSQL1['ySeguro'];
		$v_yCarga = $rowSQL1['yCarga'];
		$v_yDescarga = $rowSQL1['yDescarga'];
		$v_yRecoleccion = $rowSQL1['yRecoleccion'];
		$v_yRepartos = $rowSQL1['yRepartos'];
		$v_yAutopistas = $rowSQL1['yAutopistas'];
		$v_yDemoras = $rowSQL1['yDemoras'];
		$v_yOtros = $rowSQL1['yOtros'];
		
		
		
		
	}
	
	//if(($v_yCarga > 0) || ($v_yDescarga > 0) || ($v_yRecoleccion > 0) || ($v_yRepartos > 0) || ($v_yAutopistas > 0) || ($v_yDemoras > 0) || ($v_yEstadias > 0) || ($v_yOtros > 0)){
	//	echo "<script>
	//		alert('La Remision ".$xfolio." tiene conceptos registrados que aun no se validan. No puede ser procesada.');
	//		window.location= 'ardica_update_remisiones.php'
	//	  </script>";
	//} else {
	
	



		$resSQL2 = "SELECT COUNT(*) as total FROM " . $prefijodb . "remisionespartidas WHERE FolioSub_RID = ".$v_id_factura;
		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
		while($rowSQL2 = mysql_fetch_array($runSQL2)){
			$v_total = $rowSQL2['total'];
		}

		if($v_total == 0){
			////////////////////////////////////////////////Cargar Partida de Flete
			if($v_yFlete > 0){
				//Insert Partida
						
				
				
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
				//Calcular
				$subtotal = 1 * $v_yFlete;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				$retencion_importe = round($subtotal * 0.04,2);
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'FLETE', 'Conceptos', 1998743, 0, 16, '', ".$subtotal.", 'Flete', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '78101800', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yFlete.", 4, 0, 'Transporte de Carga por Carretera')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Flete Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Flete
			
			
			
			////////////////////////////////////////////////Cargar Partida de Seguro
			if($v_ySeguro > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_ySeguro;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'SEGURO', 'Conceptos', 1998744, 0, 16, '', ".$subtotal.", 'Seguro', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '1010101', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_ySeguro.", 0, 0, 'POR DEFINIR')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Seguro Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Seguro
			
			////////////////////////////////////////////////Cargar Partida de Carga
			if($v_yCarga > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_yCarga;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'CARGA', 'Conceptos', 1998745, 0, 16, '', ".$subtotal.", 'Carga', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '78121601', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yCarga.", 0, 0, 'Carga y Descarga de Mercancias')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Carga Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Carga
			
			////////////////////////////////////////////////Cargar Partida de Descarga
			if($v_yDescarga > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_yDescarga;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'DESCARGA', 'Conceptos', 1998746, 0, 16, '', ".$subtotal.", 'Descarga', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '78121601', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yDescarga.", 0, 0, 'Carga y descarga de Mercancias')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Descarga Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Descarga
			
			////////////////////////////////////////////////Cargar Partida de Recoleccion
			if($v_yRecoleccion > 0){
				//Insert Partida
				
						
				
				
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
				//Calcular
				$subtotal = 1 * $v_yRecoleccion;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				$retencion_importe = round($subtotal * 0.04,2);
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'RECOLECCION', 'Conceptos', 1998747, 0, 16, '".$v_descripcion."', ".$subtotal.", 'Recoleccion', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '78101801', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yRecoleccion.", 4, 0, 'Servicios de Transporte de Carga por Carretera (en camion) en area local')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Recoleccion Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Recoleccion
			
			////////////////////////////////////////////////Cargar Partida de Repartos
			if($v_yRepartos > 0){
				//Insert Partida
				
				
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
				//Calcular
				$subtotal = 1 * $v_yRepartos;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				$retencion_importe = round($subtotal * 0.04,2);
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'REPARTOS', 'Conceptos', 1998748, 0, 16, '".$v_descripcion."', ".$subtotal.", 'Repartos', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '78101801', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yRepartos.", 4, 0, 'Servicios de Transporte de carga por carretera (en camion) en area local')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Repartos Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Repartos
			
			////////////////////////////////////////////////Cargar Partida de Demoras
			if($v_yDemoras > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_yDemoras;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'DEMORAS', 'Conceptos', 1998749, 0, 16, '', ".$subtotal.", 'Demoras', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '1010101', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yDemoras.", 0, 0, 'POR DEFINIR')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Demoras Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Demoras
			
			////////////////////////////////////////////////Cargar Partida de Autopistas
			if($v_yAutopistas > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_yAutopistas;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'AUTOPISTAS', 'Conceptos', 1998750, 0, 16, '', ".$subtotal.", 'Autopistas', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '95111600', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yAutopistas.", 0, 0, 'Vias de Trafico Abierta')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Autopistas Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Autopistas
			
			////////////////////////////////////////////////Cargar Partida de Otros
			if($v_yOtros > 0){
				//Insert Partida
				
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
				//Calcular
				$subtotal = 1 * $v_yOtros;
				//$descuento_importe;
				$iva_importe= round($subtotal * 0.16,2);
				
				//$retencion_importe = round($subtotal * 0.04,2);
				$retencion_importe = 0;
				
				$importe = round($subtotal + $iva_importe - $retencion_importe,2);
				
				
				$newid = $basidgen;
				
				$sql = "INSERT INTO " . $prefijodb . "remisionespartidas (ID, BASTIMESTAMP, ConceptoPartida, FolioConceptos_REN, FolioConceptos_RID, DescuentoImporte, IVA, Detalle, Subtotal, Tipo, RetencionImporte, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Descuento, prodserv33, claveunidad33, Cantidad, Importe, IVAImporte, PrecioUnitario, Retencion, Excento, prodserv33dsc) VALUES (". $newid .", '".$fecha."', 'OTROS', 'Conceptos', 1998751, 0, 16, '', ".$subtotal.", 'Otros', ".$retencion_importe.", 'Remisiones', ".$v_id_factura.", 'FolioSubPartidas', 0, '1010101', 'E48', 1, ".$importe.", ".$iva_importe.", ".$v_yOtros.", 0, 0, 'POR DEFINIR')";
				
				
				mysql_query($sql,$cnx_cfdi);
				
				echo "<script>
					alert('Remision ".$xfolio." Concepto Otros Actualizado Correctamente');
				  </script>";
				  //echo "Remision Actualizada Correctamente";
				
				
			} 
			//////////////////////////////////////////////// FIN Cargar Partida de Otros
			
			echo "<script>
					alert('Se actualizaron las Partidas correctamente.');
					window.location= 'ardica_update_remisiones.php'
				  </script>";
				   //echo "La Remision no tiene valor en Flete";
			
		} else {
			echo "<script>
					alert('La Remision ya tiene Partida cargada. No puede ser procesada.');
					window.location= 'ardica_update_remisiones.php'
				  </script>";
				   //echo "La Remision ya tiene Partida cargada";
		}
		
	
	
	
	
	//} //Fin valida mas conceptos

} //Fin valida XFolio


?>