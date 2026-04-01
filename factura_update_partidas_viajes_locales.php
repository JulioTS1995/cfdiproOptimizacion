<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
	//Borrar facturapartidas que vengan de Viajes Locales
	
	mysql_query("DELETE FROM ".$prefijodb."facturapartidas WHERE FolioSub_RID = ".$id_factura." AND VL=1");
	
	
	
	//Buscar Viajes Locales anexadas a la factura
	$sql_01="SELECT * FROM ".$prefijodb."viajeslocales WHERE FolioSubFactura_RID = ".$id_factura;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp1=mysql_fetch_array($res_01)){
		$id_viajeslocales = $fila_exp1['ID'];
		$vl_mo= $fila_exp1['Mo'];
		$vl_basversion= $fila_exp1['BASVERSION'];
		$vl_bastimestamp= $fila_exp1['BASTIMESTAMP'];
		$vl_cobrocliente= $fila_exp1['CobroCliente'];
		
		
		
		//Buscar Concepto en Viajes Clasificación
		$sql_02="SELECT * FROM ".$prefijodb."clasificacionviajes WHERE Codigo = '".$vl_mo."'";
		//echo $sql_02;
		$res_02=mysql_query($sql_02);
		$fila_exp2=mysql_fetch_array($res_02);
		$id_concepto = $fila_exp2['FolioConcepto_RID'];
		$concepto_ren = $fila_exp2['FolioConcepto_REN'];
		$concepto_rma = $fila_exp2['FolioConcepto_RMA'];
	
		
		
		//Buscar Tipo en Conceptos
		$sql_04="SELECT * FROM ".$prefijodb."conceptos WHERE ID = ".$id_concepto;
		//echo $sql_04;
		$res_04=mysql_query($sql_04);
		$fila_exp4=mysql_fetch_array($res_04);
		$concepto = $fila_exp4['Concepto'];
		$vl_tipo = $fila_exp4['Tipo'];
		$prodserv33 = $fila_exp4['prodserv33'];
		$claveunidad33 = $fila_exp4['claveunidad33'];
		$prodserv33dsc = $fila_exp4['prodserv33dsc'];
		$IVA = $fila_exp4['IVA'];
		$Retencion = $fila_exp4['Retencion'];
		
		$descuento = 0;
		$descuento_importe=0;
		//Calcular Importe IVA, Retencion 
		
		$iva_importe = $vl_cobrocliente * ($IVA/100);
		$retencion_importe = $vl_cobrocliente * ($Retencion/100);
		
		$FolioSub_REN = 'Factura';
		$FolioSub_RID = $id_factura;
		$FolioSubPartidas = 'FolioSubPartidas';
		
		$importe = $vl_cobrocliente + $iva_importe - $retencion_importe;
		
		

				
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
					IVAImporte, Detalle, prodserv33, claveunidad33, prodserv33dsc, VL) 
					VALUES (".$newid.",".$vl_basversion.", '".$vl_bastimestamp."',
					'".$concepto_ren."',".$id_concepto.",'".$vl_tipo."',".$IVA.",".$retencion_importe.",
					".$descuento.",".$Retencion.",".$importe.",".$descuento_importe.",1,".$vl_cobrocliente.",
					".$vl_cobrocliente.",'".$concepto."','".$FolioSub_REN."',".$FolioSub_RID.",'".$FolioSubPartidas."',
					".$iva_importe.",'','".$prodserv33."','".$claveunidad33."','".$prodserv33dsc."',1)");
				
			
			
		
	} //Fin Busqueda de Viajes Locales anexados a la factura seleccionada
	
	
	
	
	
	
	echo "<h2>Se realizo la actualizacion de las Partidas de Viajes Locales con Exito.</h2>";

	//http://localhost/cfdipro/factura_update_partidas_viajes_locales.php?prefijodb=prbsolosa_&id=2101540

?>