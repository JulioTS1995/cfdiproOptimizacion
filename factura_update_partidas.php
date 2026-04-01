<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
	//Borrar facturapartidas que tengala Factura antes de actualizar (FolioSub_RID)
	
	mysql_query("DELETE FROM ".$prefijodb."facturapartidas WHERE FolioSub_RID = ".$id_factura." AND (VL <> 1 OR NV <> 1)");
	
	
	
	//Buscar Remisiones anexadas a la factura
	$sql_01="SELECT * FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp1=mysql_fetch_array($res_01)){
		$id_remision = $fila_exp1['Remision_RID'];
		
		//Buscar partidas de la remision
		
		$sql_02="SELECT * FROM ".$prefijodb."remisionespartidas WHERE FolioSub_RID = ".$id_remision;
		//echo $sql_02;
		$res_02=mysql_query($sql_02);
		while ($fila_exp2=mysql_fetch_array($res_02)){
			//Obtener todos los datos de la remision partida
			$id_rem_partida = $fila_exp2['ID'];
			$BASVERSION = $fila_exp2['BASVERSION'];
			$BASTIMESTAMP = $fila_exp2['BASTIMESTAMP'];
			$FolioConceptos_REN = $fila_exp2['FolioConceptos_REN'];
			$FolioConceptos_RID = $fila_exp2['FolioConceptos_RID'];
			$Tipo = $fila_exp2['Tipo'];
			$IVA = $fila_exp2['IVA'];
			$RetencionImporte = $fila_exp2['RetencionImporte'];
			$Descuento = $fila_exp2['Descuento'];
			$Retencion = $fila_exp2['Retencion'];
			$Importe = $fila_exp2['Importe'];
			$DescuentoImporte = $fila_exp2['DescuentoImporte'];
			$Cantidad = $fila_exp2['Cantidad'];
			$PrecioUnitario = $fila_exp2['PrecioUnitario'];
			$Subtotal = $fila_exp2['Subtotal'];
			$ConceptoPartida = $fila_exp2['ConceptoPartida'];
			$FolioSub_REN = 'Factura';
			$FolioSub_RID = $id_factura;
			$FolioSubPartidas = 'FolioSubPartidas';
			$IVAImporte = $fila_exp2['IVAImporte'];
			$Detalle = $fila_exp2['Detalle'];
			$prodserv33 = $fila_exp2['prodserv33'];
			$claveunidad33 = $fila_exp2['claveunidad33'];
			$prodserv33dsc = $fila_exp2['prodserv33dsc'];
			$excento = $fila_exp2['Excento'];
			


			
			//Insert copia de la partida de remisiones a partida de facturas
			
			//Generar nuevo ID
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
			
			//Insert alterno
			mysql_query("INSERT INTO ".$prefijodb."facturapartidas (ID, BASVERSION, BASTIMESTAMP, 
				FolioConceptos_REN, FolioConceptos_RID, Tipo, IVA, RetencionImporte,
				Descuento, Retencion, Importe, DescuentoImporte, Cantidad, PrecioUnitario,
				Subtotal, ConceptoPartida, FolioSub_REN, FolioSub_RID, FolioSub_RMA, 
				IVAImporte, Detalle, prodserv33, claveunidad33, prodserv33dsc, Excento) 
				VALUES (".$newid.",".$BASVERSION.", '".$BASTIMESTAMP."',
				'".$FolioConceptos_REN."',".$FolioConceptos_RID.",'".$Tipo."',".$IVA.",".$RetencionImporte.",
				".$Descuento.",".$Retencion.",".$Importe.",".$DescuentoImporte.",".$Cantidad.",".$PrecioUnitario.",
				".$Subtotal.",'".$ConceptoPartida."','".$FolioSub_REN."',".$FolioSub_RID.",'".$FolioSubPartidas."',
				".$IVAImporte.",'".$Detalle."','".$prodserv33."','".$claveunidad33."','".$prodserv33dsc."',".$excento.")");
			
			
			
		} //Fin busqueda de remisiones partidas
		
	
		
	} //Fin Busqueda de Remisiones anexadas a la factura seleccionada
	
	
	
	
	
	
	echo "<h2>Se realizo la actualizacion de las Partidas con Exito.</h2>";

	//http://localhost/cfdipro/factura_update_partidas.php?prefijodb=prueba_&id=1845853

?>