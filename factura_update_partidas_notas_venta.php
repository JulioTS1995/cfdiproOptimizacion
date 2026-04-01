<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
	//Borrar facturapartidas que vengan de Notas de Venta
	
	mysql_query("DELETE FROM ".$prefijodb."facturapartidas WHERE FolioSub_RID = ".$id_factura." AND NV=1");
	
	
	
	//Buscar Notas de Venta anexadas a la factura
	$sql_01="SELECT * FROM ".$prefijodb."notasventa WHERE FolioSubFactura_RID = ".$id_factura;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp1=mysql_fetch_array($res_01)){
		$id_notaventa = $fila_exp1['ID'];
		
		
		
		//Buscar Partidas de cada Nota de Venta
		$sql_02="SELECT * FROM ".$prefijodb."notasventa_ref WHERE ID = ".$id_notaventa;
		//echo $sql_02;
		$res_02=mysql_query($sql_02);
		while ($fila_exp2=mysql_fetch_array($res_02)){
			$id_notaventasub = $fila_exp2['RID'];
			
			$sql_03="SELECT * FROM ".$prefijodb."notasventasub WHERE ID = ".$id_notaventasub;
			$res_03=mysql_query($sql_03);
			$fila_exp3=mysql_fetch_array($res_03);
			//Obtener todos los datos de la nota venta partida
			$nv_basversion = $fila_exp3['BASVERSION'];
			$nv_bastimestamp = $fila_exp3['BASTIMESTAMP'];
			$nv_concepto = $fila_exp3['Concepto'];
			$nv_iva = $fila_exp3['IVA'];
			$nv_retencion_importe = $fila_exp3['RetencionImporte'];
			$nv_subtotal1 = $fila_exp3['Subtotal1'];
			$nv_descuento = $fila_exp3['Descuento'];
			$nv_importe = $fila_exp3['Importe'];
			$nv_retencion = $fila_exp3['Retencion'];
			$nv_descuento_importe = $fila_exp3['DescuentoImporte'];
			$nv_cantidad = $fila_exp3['Cantidad'];
			$nv_precio_unitario = $fila_exp3['PrecioUnitario'];
			$nv_descripcion = $fila_exp3['Descripcion'];
			$nv_subtotal = $fila_exp3['Subtotal'];
			$nv_concepto1_ren = $fila_exp3['Concepto1_REN'];
			$nv_concepto1_rid = $fila_exp3['Concepto1_RID'];
			$nv_concepto1_rma = $fila_exp3['Concepto1_RMA'];
			$nv_iva_importe = $fila_exp3['IVAImporte'];
			$FolioSub_REN = 'Factura';
			$FolioSub_RID = $id_factura;
			$FolioSubPartidas = 'FolioSubPartidas';
			
			//Buscar Tipo en Conceptos
			$sql_04="SELECT * FROM ".$prefijodb."conceptos WHERE ID = ".$nv_concepto1_rid;
			//echo $sql_04;
			$res_04=mysql_query($sql_04);
			$fila_exp4=mysql_fetch_array($res_04);
			$nv_tipo = $fila_exp4['Tipo'];
			$prodserv33 = $fila_exp4['prodserv33'];
			$claveunidad33 = $fila_exp4['claveunidad33'];
			$prodserv33dsc = $fila_exp4['prodserv33dsc'];
			//$excento = $fila_exp4['excento'];
				
			
		
			

				
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
					IVAImporte, Detalle, prodserv33, claveunidad33, prodserv33dsc, NV) 
					VALUES (".$newid.",".$nv_basversion.", '".$nv_bastimestamp."',
					'".$nv_concepto1_ren."',".$nv_concepto1_rid.",'".$nv_tipo."',".$nv_iva.",".$nv_retencion_importe.",
					".$nv_descuento.",".$nv_retencion.",".$nv_importe.",".$nv_descuento_importe.",".$nv_cantidad.",".$nv_precio_unitario.",
					".$nv_subtotal.",'".$nv_concepto."','".$FolioSub_REN."',".$FolioSub_RID.",'".$FolioSubPartidas."',
					".$nv_iva_importe.",'".$nv_descripcion."','".$prodserv33."','".$claveunidad33."','".$prodserv33dsc."',1)");
				
			
			
		
		} //FIN WHILE notas venta ref
		
	
	
		
	} //Fin Busqueda de Notas de Venta anexadas a la factura seleccionada
	
	
	
	
	
	
	echo "<h2>Se realizo la actualizacion de las Partidas de Notas de Venta con Exito.</h2>";

	//http://localhost/cfdipro/factura_update_partidas_notas_venta.php?prefijodb=prbsolosa_&id=2059827

?>