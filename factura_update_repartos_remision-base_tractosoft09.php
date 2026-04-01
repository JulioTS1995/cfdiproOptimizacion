<?php
//Proceso para actualizar Repartos en Facturas de Remision relacionada

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
	//Borrar facturasrepartos que tenga la Factura antes de actualizar (FolioSub_RID)
	
	mysql_query("DELETE FROM ".$prefijodb."facturasrepartos WHERE FolioSub_RID = ".$id_factura);
	
	
	//Contar Remisiones Relacionadas a la Factura
	$sql_01="SELECT COUNT(ID) AS total FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp01=mysql_fetch_array($res_01)){
		$total_remisiones = $fila_exp01['total'];
	}
	
	if($total_remisiones > 0){
	
		//Buscar Remisiones Relacionadas a la Factura
		$sql_00="SELECT * FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura;
		//echo $sql_00;
		$res_00=mysql_query($sql_00);
		while ($fila_exp00=mysql_fetch_array($res_00)){
			$id_remision = $fila_exp00['Remision_RID'];
			
			//Validar que tenga repartos la Remision seleccionada
			$sql_05="SELECT COUNT(ID) AS total2 FROM ".$prefijodb."remisionesrepartos WHERE FolioSub_RID = ".$id_remision;
			//echo $sql_05;
			$res_05=mysql_query($sql_05);
			while ($fila_exp05=mysql_fetch_array($res_05)){
				$total_remisiones_rep = $fila_exp05['total2'];
			}
			
			if($total_remisiones_rep > 0){
			
			
				//Buscar Repartos de Remisiones
				$sql_02="SELECT * FROM ".$prefijodb."remisionesrepartos WHERE FolioSub_RID = ".$id_remision;
				//echo $sql_02;
				$res_02=mysql_query($sql_02);
				while ($fila_exp2=mysql_fetch_array($res_02)){
					$r_BASVERSION = $fila_exp2['BASVERSION'];
					$r_BASTIMESTAMP = $fila_exp2['BASTIMESTAMP'];
					$r_RemitenteNumInt = $fila_exp2['RemitenteNumInt'];
					$r_Destinatario = $fila_exp2['Destinatario'];
					$r_DestinatarioNumInt = $fila_exp2['DestinatarioNumInt'];
					$r_RemitenteNumRegIdTrib = $fila_exp2['RemitenteNumRegIdTrib'];
					$r_DestinatarioMunicipio_REN = $fila_exp2['DestinatarioMunicipio_REN'];
					$r_DestinatarioMunicipio_RID = $fila_exp2['DestinatarioMunicipio_RID'];
					$r_DestinatarioMunicipio_RMA = $fila_exp2['DestinatarioMunicipio_RMA'];
					$r_RemitenteNumExt = $fila_exp2['RemitenteNumExt'];
					$r_RemitentePais = $fila_exp2['RemitentePais'];
					$r_RemitenteCodigoPostal = $fila_exp2['RemitenteCodigoPostal'];
					$r_DestinatarioNumExt = $fila_exp2['DestinatarioNumExt'];
					$r_DestinatarioDomicilio = $fila_exp2['DestinatarioDomicilio'];
					$r_DestinatarioPais = $fila_exp2['DestinatarioPais'];
					$r_DestinatarioTipoEstacion = $fila_exp2['DestinatarioTipoEstacion'];
					$r_DestinatarioRFC = $fila_exp2['DestinatarioRFC'];
					$r_RemitenteEstado_REN = $fila_exp2['RemitenteEstado_REN'];
					$r_RemitenteEstado_RID = $fila_exp2['RemitenteEstado_RID'];
					$r_RemitenteEstado_RMA = $fila_exp2['RemitenteEstado_RMA'];
					$r_Remitente = $fila_exp2['Remitente'];
					$r_DestinatarioCalle = $fila_exp2['DestinatarioCalle'];
					$r_DestinatarioEstado_REN = $fila_exp2['DestinatarioEstado_REN'];
					$r_DestinatarioEstado_RID = $fila_exp2['DestinatarioEstado_RID'];
					$r_DestinatarioEstado_RMA = $fila_exp2['DestinatarioEstado_RMA'];
					$r_RemitenteLocalidad = $fila_exp2['RemitenteLocalidad'];
					$r_RemitenteRFC = $fila_exp2['RemitenteRFC'];
					$r_RemitenteLocalidad2_REN = $fila_exp2['RemitenteLocalidad2_REN'];
					$r_RemitenteLocalidad2_RID = $fila_exp2['RemitenteLocalidad2_RID'];
					$r_RemitenteLocalidad2_RMA = $fila_exp2['RemitenteLocalidad2_RMA'];
					$r_DestinatarioLocalidad2_REN = $fila_exp2['DestinatarioLocalidad2_REN'];
					$r_DestinatarioLocalidad2_RID = $fila_exp2['DestinatarioLocalidad2_RID'];
					$r_DestinatarioLocalidad2_RMA = $fila_exp2['DestinatarioLocalidad2_RMA'];
					$r_DestinatarioColonia_REN = $fila_exp2['DestinatarioColonia_REN'];
					$r_DestinatarioColonia_RID = $fila_exp2['DestinatarioColonia_RID'];
					$r_DestinatarioColonia_RMA = $fila_exp2['DestinatarioColonia_RMA'];
					$r_FolioSub_REN = $fila_exp2['FolioSub_REN'];
					$r_FolioSub_RID = $fila_exp2['FolioSub_RID'];
					$r_FolioSub_RMA = $fila_exp2['FolioSub_RMA'];
					$r_DestinatarioSeEntregara = $fila_exp2['DestinatarioSeEntregara'];
					$r_RemitenteReferencia = $fila_exp2['RemitenteReferencia'];
					$r_DestinatarioReferencia = $fila_exp2['DestinatarioReferencia'];
					$r_DestinatarioNumRegIdTrib = $fila_exp2['DestinatarioNumRegIdTrib'];
					$r_RemitenteContacto = $fila_exp2['RemitenteContacto'];
					$r_RemitenteSeRecogera = $fila_exp2['RemitenteSeRecogera'];
					$r_RemitenteTelefono = $fila_exp2['RemitenteTelefono'];
					$r_DestinatarioLocalidad = $fila_exp2['DestinatarioLocalidad'];
					$r_RemitenteTipoEstacion = $fila_exp2['RemitenteTipoEstacion'];
					$r_CitaCarga_t = $fila_exp2['CitaCarga'];
					if (isset($r_CitaCarga_t)) {
						$r_CitaCarga = $r_CitaCarga_t;
					} else {
						$r_CitaCarga = '0000-00-00 00:00:00';
					}
					$r_DestinatarioContacto = $fila_exp2['DestinatarioContacto'];
					$r_DestinatarioTelefono = $fila_exp2['DestinatarioTelefono'];
					$r_RemitenteMunicipio_REN = $fila_exp2['RemitenteMunicipio_REN'];
					$r_RemitenteMunicipio_RID = $fila_exp2['RemitenteMunicipio_RID'];
					$r_RemitenteMunicipio_RMA = $fila_exp2['RemitenteMunicipio_RMA'];
					$r_DestinatarioCodigoPostal = $fila_exp2['DestinatarioCodigoPostal'];
					$r_DestinatarioCitaCarga_t = $fila_exp2['DestinatarioCitaCarga'];
					if (isset($r_DestinatarioCitaCarga_t)) {
						$r_DestinatarioCitaCarga = $r_DestinatarioCitaCarga_t;
					} else {
						$r_DestinatarioCitaCarga = '0000-00-00 00:00:00';
					}
					$r_RemitenteColonia_REN = $fila_exp2['RemitenteColonia_REN'];
					$r_RemitenteColonia_RID = $fila_exp2['RemitenteColonia_RID'];
					$r_RemitenteColonia_RMA = $fila_exp2['RemitenteColonia_RMA'];
					$r_RemitenteDomicilio = $fila_exp2['RemitenteDomicilio'];
					$r_RemitenteCalle = $fila_exp2['RemitenteCalle'];
					$r_CodigoDestino = $fila_exp2['CodigoDestino'];
					$r_CodigoOrigen = $fila_exp2['CodigoOrigen'];
					$r_DistanciaRecorrida = $fila_exp2['DistanciaRecorrida'];
					$r_Recoleccion = $fila_exp2['Recoleccion'];
					$r_RemitenteResidenciaFiscal = $fila_exp2['RemitenteResidenciaFiscal'];
					$r_DestinatarioResidenciaFiscal = $fila_exp2['DestinatarioResidenciaFiscal'];
					
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
					
					$v_ins = "INSERT INTO ".$prefijodb."facturasrepartos 
						(ID, 
						BASVERSION, 
						BASTIMESTAMP, 
						RemitenteNumInt, 
						Destinatario, 
						DestinatarioNumInt,
						RemitenteNumRegIdTrib,
						DestinatarioMunicipio_REN,
						DestinatarioMunicipio_RID,
						DestinatarioMunicipio_RMA,
						RemitenteNumExt,
						RemitentePais,
						RemitenteCodigoPostal,
						DestinatarioNumExt,
						DestinatarioDomicilio,
						DestinatarioPais,
						DestinatarioTipoEstacion,
						DestinatarioRFC,
						RemitenteEstado_REN,
						RemitenteEstado_RID,
						RemitenteEstado_RMA,
						Remitente,
						DestinatarioCalle,
						DestinatarioEstado_REN,
						DestinatarioEstado_RID,
						DestinatarioEstado_RMA,
						RemitenteLocalidad,
						RemitenteRFC,
						RemitenteLocalidad2_REN,
						RemitenteLocalidad2_RID,
						RemitenteLocalidad2_RMA,
						DestinatarioLocalidad2_REN,
						DestinatarioLocalidad2_RID,
						DestinatarioLocalidad2_RMA,
						DestinatarioColonia_REN,
						DestinatarioColonia_RID,
						DestinatarioColonia_RMA,
						DestinatarioSeEntregara,
						RemitenteReferencia,
						DestinatarioReferencia,
						DestinatarioNumRegIdTrib,
						RemitenteContacto,
						RemitenteSeRecogera,
						RemitenteTelefono,
						DestinatarioLocalidad,
						RemitenteTipoEstacion,
						CitaCarga,
						DestinatarioContacto,
						DestinatarioTelefono,
						RemitenteMunicipio_REN,
						RemitenteMunicipio_RID,
						RemitenteMunicipio_RMA,
						DestinatarioCodigoPostal,
						DestinatarioCitaCarga,
						RemitenteColonia_REN,
						RemitenteColonia_RID,
						RemitenteColonia_RMA,
						RemitenteDomicilio,
						RemitenteCalle,
						CodigoDestino,
						CodigoOrigen,
						DistanciaRecorrida,
						FolioSub_REN,
						FolioSub_RID,
						FolioSub_RMA,
						Recoleccion,
						RemitenteResidenciaFiscal,
						DestinatarioResidenciaFiscal) 
						VALUES 
						(".$newid.",
						'".$r_BASVERSION."', 
						'".$r_BASTIMESTAMP."', 
						'".$r_RemitenteNumInt."', 
						'".$r_Destinatario."', 
						'".$r_DestinatarioNumInt."', 
						'".$r_RemitenteNumRegIdTrib."', 
						'".$r_DestinatarioMunicipio_REN."', 
						'".$r_DestinatarioMunicipio_RID."',
						'".$r_DestinatarioMunicipio_RMA."',
						'".$r_RemitenteNumExt."',
						'".$r_RemitentePais."',
						'".$r_RemitenteCodigoPostal."',
						'".$r_DestinatarioNumExt."',
						'".$r_DestinatarioDomicilio."',
						'".$r_DestinatarioPais."',
						'".$r_DestinatarioTipoEstacion."',
						'".$r_DestinatarioRFC."',
						'".$r_RemitenteEstado_REN."',
						'".$r_RemitenteEstado_RID."',
						'".$r_RemitenteEstado_RMA."',
						'".$r_Remitente."',
						'".$r_DestinatarioCalle."',
						'".$r_DestinatarioEstado_REN."',
						'".$r_DestinatarioEstado_RID."',
						'".$r_DestinatarioEstado_RMA."',
						'".$r_RemitenteLocalidad."',
						'".$r_RemitenteRFC."',
						'".$r_RemitenteLocalidad2_REN."',
						'".$r_RemitenteLocalidad2_RID."',
						'".$r_RemitenteLocalidad2_RMA."',
						'".$r_DestinatarioLocalidad2_REN."',
						'".$r_DestinatarioLocalidad2_RID."',
						'".$r_DestinatarioLocalidad2_RMA."',
						'".$r_DestinatarioColonia_REN."',
						'".$r_DestinatarioColonia_RID."',
						'".$r_DestinatarioColonia_RMA."',
						'".$r_DestinatarioSeEntregara."',
						'".$r_RemitenteReferencia."',
						'".$r_DestinatarioReferencia."',
						'".$r_DestinatarioNumRegIdTrib."',
						'".$r_RemitenteContacto."',
						'".$r_RemitenteSeRecogera."',
						'".$r_RemitenteTelefono."',
						'".$r_DestinatarioLocalidad."',
						'".$r_RemitenteTipoEstacion."',
						'".$r_CitaCarga."',
						'".$r_DestinatarioContacto."',
						'".$r_DestinatarioTelefono."',
						'".$r_RemitenteMunicipio_REN."',
						'".$r_RemitenteMunicipio_RID."',
						'".$r_RemitenteMunicipio_RMA."',
						'".$r_DestinatarioCodigoPostal."',
						'".$r_DestinatarioCitaCarga."',
						'".$r_RemitenteColonia_REN."',
						'".$r_RemitenteColonia_RID."',
						'".$r_RemitenteColonia_RMA."',
						'".$r_RemitenteDomicilio."',
						'".$r_RemitenteCalle."',
						'".$r_CodigoDestino."',
						'".$r_CodigoOrigen."',
						'".$r_DistanciaRecorrida."',
						'Factura', 
						".$id_factura.",  
						'FolioSubRepartos',
						'".$r_Recoleccion."',
						'".$r_RemitenteResidenciaFiscal."',
						'".$r_DestinatarioResidenciaFiscal."')";
						
						
						
					$v_ins=str_replace("''","NULL",$v_ins);
					$v_ins=str_replace("' '","NULL",$v_ins);
					//echo $v_ins;
					mysql_query($v_ins,$cnx_cfdi);
					
			
					
					
					
					
					
					
					
					
					
				} // Fin Busca datos de Reparto de Remision
			
			} else {
			} //Fin Valida Repartos en Remision
			
			
			echo "<h2>Se realizo la actualizacion de los Repartos con Exito.</h2>";
			
		} //Fin Buscar Remisiones Relacionadas a la Factura
	
	} else {
		echo "<h2>Esta Factura no tiene Remisiones Anexadas.</h2>";
	}
	
	
	
	
	
	
	
	
	
	
	

	//http://localhost/cfdipro/factura_update_repartos_remision.php?prefijodb=prbcljif_&id=2611057

?>