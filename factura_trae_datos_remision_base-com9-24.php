<?php

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
	

	//Buscar en remisionesfacturadetalle el ID de la remision anexada
	$sql_03="SELECT * FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura." ORDER BY ID ASC LIMIT 1";
	//echo $sql_03;
	$res_03=mysql_query($sql_03);
	while ($fila_exp3=mysql_fetch_array($res_03)){
		$id_remision_anexada = $fila_exp3['Remision_RID'];
		if($id_remision_anexada > 0){ //IF_1
			//Buscar Datos de la Remision
			$sql_04="SELECT * FROM ".$prefijodb."remisiones WHERE ID = ".$id_remision_anexada;
			//echo $sql_04;
			$res_04=mysql_query($sql_04);
			while ($fila_exp4=mysql_fetch_array($res_04)){
				$rem_Ruta_REN = $fila_exp4['Ruta_REN'];
				$rem_Ruta_RID = $fila_exp4['Ruta_RID'];
				$rem_Ruta_RMA = $fila_exp4['Ruta_RMA'];
				if($rem_Ruta_RID > 0){
					$q_rem_Ruta_RID = "Ruta_RID=".$rem_Ruta_RID.",";
					$q_rem_Ruta_REN = "Ruta_REN='".$rem_Ruta_REN."',";
					$q_rem_Ruta_RMA = "Ruta_RMA='".$rem_Ruta_RMA."',";
				} else {
					$q_rem_Ruta_RID = "";
					$q_rem_Ruta_REN = "";
					$q_rem_Ruta_RMA = "";
				}	
				$rem_CodigoOrigen = $fila_exp4['CodigoOrigen'];
				$rem_Remitente = $fila_exp4['Remitente'];
				$rem_RemitenteRFC = $fila_exp4['RemitenteRFC'];
				$rem_CitaCarga = $fila_exp4['CitaCarga'];
				if (isset($rem_CitaCarga)) {
					//echo "Variable definida!!!";
					$q_rem_CitaCarga = "CitaCarga='".$rem_CitaCarga."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_CitaCarga = "CitaCarga=null,";
				}
				$rem_RemitenteContacto = $fila_exp4['RemitenteContacto'];
				$rem_RemitenteCalle = $fila_exp4['RemitenteCalle'];
				$rem_RemitenteNumExt = $fila_exp4['RemitenteNumExt'];
				$rem_RemitenteNumInt = $fila_exp4['RemitenteNumInt'];
				$rem_RemitenteColonia_REN = $fila_exp4['RemitenteColonia_REN'];
				$rem_RemitenteColonia_RID = $fila_exp4['RemitenteColonia_RID'];
				$rem_RemitenteColonia_RMA = $fila_exp4['RemitenteColonia_RMA'];
				if($rem_RemitenteColonia_RID > 0){
					$q_rem_RemitenteColonia_RID = "RemitenteColonia_RID=".$rem_RemitenteColonia_RID.",";
					$q_rem_RemitenteColonia_REN = "RemitenteColonia_REN='".$rem_RemitenteColonia_REN."',";
					$q_rem_RemitenteColonia_RMA = "RemitenteColonia_RMA='".$rem_RemitenteColonia_RMA."',";
				} else {
					$q_rem_RemitenteColonia_RID = "RemitenteColonia_RID=null,";
					$q_rem_RemitenteColonia_REN = "RemitenteColonia_REN=null,";
					$q_rem_RemitenteColonia_RMA = "RemitenteColonia_RMA=null,";
				}
				$rem_RemitenteLocalidad2_REN = $fila_exp4['RemitenteLocalidad2_REN'];
				$rem_RemitenteLocalidad2_RID = $fila_exp4['RemitenteLocalidad2_RID'];
				$rem_RemitenteLocalidad2_RMA = $fila_exp4['RemitenteLocalidad2_RMA'];
				if($rem_RemitenteLocalidad2_RID > 0){
					$q_rem_RemitenteLocalidad2_RID = "RemitenteLocalidad2_RID=".$rem_RemitenteLocalidad2_RID.",";
					$q_rem_RemitenteLocalidad2_REN = "RemitenteLocalidad2_REN='".$rem_RemitenteLocalidad2_REN."',";
					$q_rem_RemitenteLocalidad2_RMA = "RemitenteLocalidad2_RMA='".$rem_RemitenteLocalidad2_RMA."',";
				} else {
					$q_rem_RemitenteLocalidad2_RID = "RemitenteLocalidad2_RID=null,";
					$q_rem_RemitenteLocalidad2_REN = "RemitenteLocalidad2_REN=null,";
					$q_rem_RemitenteLocalidad2_RMA = "RemitenteLocalidad2_RMA=null,";
				}
				
				$rem_RemitenteMunicipio_REN = $fila_exp4['RemitenteMunicipio_REN'];
				$rem_RemitenteMunicipio_RID = $fila_exp4['RemitenteMunicipio_RID'];
				$rem_RemitenteMunicipio_RMA = $fila_exp4['RemitenteMunicipio_RMA'];
				if($rem_RemitenteMunicipio_RID > 0){
					$q_rem_RemitenteMunicipio_RID = "RemitenteMunicipio_RID=".$rem_RemitenteMunicipio_RID.",";
					$q_rem_RemitenteMunicipio_REN = "RemitenteMunicipio_REN='".$rem_RemitenteMunicipio_REN."',";
					$q_rem_RemitenteMunicipio_RMA = "RemitenteMunicipio_RMA='".$rem_RemitenteMunicipio_RMA."',";
				} else {
					$q_rem_RemitenteMunicipio_RID = "RemitenteMunicipio_RID=null,";
					$q_rem_RemitenteMunicipio_REN = "RemitenteMunicipio_REN=null,";
					$q_rem_RemitenteMunicipio_RMA = "RemitenteMunicipio_RMA=null,";
				}
				
				$rem_RemitenteEstado_REN = $fila_exp4['RemitenteEstado_REN'];
				$rem_RemitenteEstado_RID = $fila_exp4['RemitenteEstado_RID'];
				$rem_RemitenteEstado_RMA = $fila_exp4['RemitenteEstado_RMA'];
				if($rem_RemitenteEstado_RID > 0){
					$q_rem_RemitenteEstado_RID = "RemitenteEstado_RID=".$rem_RemitenteEstado_RID.",";
					$q_rem_RemitenteEstado_REN = "RemitenteEstado_REN='".$rem_RemitenteEstado_REN."',";
					$q_rem_RemitenteEstado_RMA = "RemitenteEstado_RMA='".$rem_RemitenteEstado_RMA."',";
				} else {
					$q_rem_RemitenteEstado_RID = "RemitenteEstado_RID=null,";
					$q_rem_RemitenteEstado_REN = "RemitenteEstado_REN=null,";
					$q_rem_RemitenteEstado_RMA = "RemitenteEstado_RMA=null,";
				}
				
				$rem_RemitenteCodigoPostal = $fila_exp4['RemitenteCodigoPostal'];
				$rem_RemitenteReferencia = $fila_exp4['RemitenteReferencia'];
				$rem_RemitenteTelefono = $fila_exp4['RemitenteTelefono'];
				$rem_RemitenteSeRecogera = $fila_exp4['RemitenteSeRecogera'];
				$rem_Instrucciones = $fila_exp4['Instrucciones'];
				$rem_CodigoDestino = $fila_exp4['CodigoDestino'];
				$rem_Destinatario = $fila_exp4['Destinatario'];
				$rem_DestinatarioRFC = $fila_exp4['DestinatarioRFC'];
				$rem_DestinatarioCitaCarga = $fila_exp4['DestinatarioCitaCarga'];
				if (isset($rem_DestinatarioCitaCarga)) {
					//echo "Variable definida!!!";
					$q_rem_DestinatarioCitaCarga = "DestinatarioCitaCarga='".$rem_DestinatarioCitaCarga."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_DestinatarioCitaCarga = "DestinatarioCitaCarga=null,";
				}
				$rem_DestinatarioContacto = $fila_exp4['DestinatarioContacto'];
				$rem_DestinatarioCalle = $fila_exp4['DestinatarioCalle'];
				$rem_DestinatarioNumExt = $fila_exp4['DestinatarioNumExt'];
				$rem_DestinatarioNumInt = $fila_exp4['DestinatarioNumInt'];
				$rem_DestinatarioColonia_REN = $fila_exp4['DestinatarioColonia_REN'];
				$rem_DestinatarioColonia_RID = $fila_exp4['DestinatarioColonia_RID'];
				$rem_DestinatarioColonia_RMA = $fila_exp4['DestinatarioColonia_RMA'];
				if($rem_DestinatarioColonia_RID > 0){
					$q_rem_DestinatarioColonia_RID = "DestinatarioColonia_RID=".$rem_DestinatarioColonia_RID.",";
					$q_rem_DestinatarioColonia_REN = "DestinatarioColonia_REN='".$rem_DestinatarioColonia_REN."',";
					$q_rem_DestinatarioColonia_RMA = "DestinatarioColonia_RMA='".$rem_DestinatarioColonia_RMA."',";
				} else {
					$q_rem_DestinatarioColonia_RID = "DestinatarioColonia_RID=null,";
					$q_rem_DestinatarioColonia_REN = "DestinatarioColonia_REN=null,";
					$q_rem_DestinatarioColonia_RMA = "DestinatarioColonia_RMA=null,";
				}
				
				$rem_DestinatarioLocalidad2_REN = $fila_exp4['DestinatarioLocalidad2_REN'];
				$rem_DestinatarioLocalidad2_RID = $fila_exp4['DestinatarioLocalidad2_RID'];
				$rem_DestinatarioLocalidad2_RMA = $fila_exp4['DestinatarioLocalidad2_RMA'];
				if($rem_DestinatarioLocalidad2_RID > 0){
					$q_rem_DestinatarioLocalidad2_RID = "DestinatarioLocalidad2_RID=".$rem_DestinatarioLocalidad2_RID.",";
					$q_rem_DestinatarioLocalidad2_REN = "DestinatarioLocalidad2_REN='".$rem_DestinatarioLocalidad2_REN."',";
					$q_rem_DestinatarioLocalidad2_RMA = "DestinatarioLocalidad2_RMA='".$rem_DestinatarioLocalidad2_RMA."',";
				} else {
					$q_rem_DestinatarioLocalidad2_RID = "DestinatarioLocalidad2_RID=null,";
					$q_rem_DestinatarioLocalidad2_REN = "DestinatarioLocalidad2_REN=null,";
					$q_rem_DestinatarioLocalidad2_RMA = "DestinatarioLocalidad2_RMA=null,";
				}
				
				$rem_DestinatarioMunicipio_REN = $fila_exp4['DestinatarioMunicipio_REN'];
				$rem_DestinatarioMunicipio_RID = $fila_exp4['DestinatarioMunicipio_RID'];
				$rem_DestinatarioMunicipio_RMA = $fila_exp4['DestinatarioMunicipio_RMA'];
				if($rem_DestinatarioMunicipio_RID > 0){
					$q_rem_DestinatarioMunicipio_RID = "DestinatarioMunicipio_RID=".$rem_DestinatarioMunicipio_RID.",";
					$q_rem_DestinatarioMunicipio_REN = "DestinatarioMunicipio_REN='".$rem_DestinatarioMunicipio_REN."',";
					$q_rem_DestinatarioMunicipio_RMA = "DestinatarioMunicipio_RMA='".$rem_DestinatarioMunicipio_RMA."',";
				} else {
					$q_rem_DestinatarioMunicipio_RID = "DestinatarioMunicipio_RID=null,";
					$q_rem_DestinatarioMunicipio_REN = "DestinatarioMunicipio_REN=null,";
					$q_rem_DestinatarioMunicipio_RMA = "DestinatarioMunicipio_RMA=null,";
				}
				
				$rem_DestinatarioEstado_REN = $fila_exp4['DestinatarioEstado_REN'];
				$rem_DestinatarioEstado_RID = $fila_exp4['DestinatarioEstado_RID'];
				$rem_DestinatarioEstado_RMA = $fila_exp4['DestinatarioEstado_RMA'];
				if($rem_DestinatarioEstado_RID > 0){
					$q_rem_DestinatarioEstado_RID = "DestinatarioEstado_RID=".$rem_DestinatarioEstado_RID.",";
					$q_rem_DestinatarioEstado_REN = "DestinatarioEstado_REN='".$rem_DestinatarioEstado_REN."',";
					$q_rem_DestinatarioEstado_RMA = "DestinatarioEstado_RMA='".$rem_DestinatarioEstado_RMA."',";
				} else {
					$q_rem_DestinatarioEstado_RID = "DestinatarioEstado_RID=null,";
					$q_rem_DestinatarioEstado_REN = "DestinatarioEstado_REN=null,";
					$q_rem_DestinatarioEstado_RMA = "DestinatarioEstado_RMA=null,";
				}
				
				$rem_DestinatarioCodigoPostal = $fila_exp4['DestinatarioCodigoPostal'];
				$rem_DestinatarioReferencia = $fila_exp4['DestinatarioReferencia'];
				$rem_DestinatarioTelefono = $fila_exp4['DestinatarioTelefono'];
				$rem_DestinatarioSeEntregara = $fila_exp4['DestinatarioSeEntregara'];
				$rem_Poliza = $fila_exp4['Poliza'];
				$rem_FleteTipo = $fila_exp4['FleteTipo'];
				$rem_ValorDeclarado = $fila_exp4['ValorDeclarado'];
				$rem_ServicioTipo = $fila_exp4['ServicioTipo'];
				$rem_Aseguradora = $fila_exp4['Aseguradora'];
				$rem_TipoViaje = $fila_exp4['TipoViaje'];
				//$rem_Arancel = $fila_exp4['Arancel'];
				$rem_Unidad_REN = $fila_exp4['Unidad_REN'];
				$rem_Unidad_RID = $fila_exp4['Unidad_RID'];
				$rem_Unidad_RMA = $fila_exp4['Unidad_RMA'];
				if($rem_Unidad_RID > 0){
					$q_rem_Unidad_RID = "Unidad_RID=".$rem_Unidad_RID.",";
					$q_rem_Unidad_REN = "Unidad_REN='".$rem_Unidad_REN."',";
					$q_rem_Unidad_RMA = "Unidad_RMA='".$rem_Unidad_RMA."',";
				} else {
					$q_rem_Unidad_RID = "Unidad_RID=null,";
					$q_rem_Unidad_REN = "Unidad_REN=null,";
					$q_rem_Unidad_RMA = "Unidad_RMA=null,";
				}
				
				$rem_Operador_REN = $fila_exp4['Operador_REN'];
				$rem_Operador_RID = $fila_exp4['Operador_RID'];
				$rem_Operador_RMA = $fila_exp4['Operador_RMA'];
				if($rem_Operador_RID > 0){
					$q_rem_Operador_RID = "Operador_RID=".$rem_Operador_RID.",";
					$q_rem_Operador_REN = "Operador_REN='".$rem_Operador_REN."',";
					$q_rem_Operador_RMA = "Operador_RMA='".$rem_Operador_RMA."',";
				} else {
					$q_rem_Operador_RID = "Operador_RID=null,";
					$q_rem_Operador_REN = "Operador_REN=null,";
					$q_rem_Operador_RMA = "Operador_RMA=null,";
				}
				
				$rem_uRemolqueA_REN = $fila_exp4['uRemolqueA_REN'];
				$rem_uRemolqueA_RID = $fila_exp4['uRemolqueA_RID'];
				$rem_uRemolqueA_RMA = $fila_exp4['uRemolqueA_RMA'];
				if($rem_uRemolqueA_RID > 0){
					$q_rem_uRemolqueA_RID = "Remolque_RID=".$rem_uRemolqueA_RID.",";
					$q_rem_uRemolqueA_REN = "Remolque_REN='".$rem_uRemolqueA_REN."',";
					$q_rem_uRemolqueA_RMA = "Remolque_RMA='".$rem_uRemolqueA_RMA."',";
				} else {
					$q_rem_uRemolqueA_RID = "Remolque_RID=null,";
					$q_rem_uRemolqueA_REN = "Remolque_REN=null,";
					$q_rem_uRemolqueA_RMA = "Remolque_RMA=null,";
				}
				
				$rem_uRemolqueB_REN = $fila_exp4['uRemolqueB_REN'];
				$rem_uRemolqueB_RID = $fila_exp4['uRemolqueB_RID'];
				$rem_uRemolqueB_RMA = $fila_exp4['uRemolqueB_RMA'];
				if($rem_uRemolqueB_RID > 0){
					$q_rem_uRemolqueB_RID = "Remolque2_RID=".$rem_uRemolqueB_RID.",";
					$q_rem_uRemolqueB_REN = "Remolque2_REN='".$rem_uRemolqueB_REN."',";
					$q_rem_uRemolqueB_RMA = "Remolque2_RMA='".$rem_uRemolqueB_RMA."',";
				} else {
					$q_rem_uRemolqueB_RID = "Remolque2_RID=null,";
					$q_rem_uRemolqueB_REN = "Remolque2_REN=null,";
					$q_rem_uRemolqueB_RMA = "Remolque2_RMA=null,";
				}
				$rem_uRemolqueB_RMA = $fila_exp4['uRemolqueB_RMA'];
				$rem_uDolly_REN = $fila_exp4['uDolly_REN'];
				$rem_uDolly_RID = $fila_exp4['uDolly_RID'];
				$rem_uDolly_RMA = $fila_exp4['uDolly_RMA'];
				if($rem_uDolly_RID > 0){
					$q_rem_uDolly_RID = "Dolly_RID=".$rem_uDolly_RID.",";
					$q_rem_uDolly_REN = "Dolly_REN='".$rem_uDolly_REN."',";
					$q_rem_uDolly_RMA = "Dolly_RMA='".$rem_uDolly_RMA."',";
				} else {
					$q_rem_uDolly_RID = "Dolly_RID=null,";
					$q_rem_uDolly_REN = "Dolly_REN=null,";
					$q_rem_uDolly_RMA = "Dolly_RMA=null,";
				}
				
				$rem_PermisionarioFact_REN = $fila_exp4['PermisionarioFact_REN'];
				$rem_PermisionarioFact_RID = $fila_exp4['PermisionarioFact_RID'];
				$rem_PermisionarioFact_RMA = $fila_exp4['PermisionarioFact_RMA'];
				if($rem_PermisionarioFact_RID > 0){
					$q_rem_PermisionarioFact_RID = "PermisionarioFact_RID=".$rem_PermisionarioFact_RID.",";
					$q_rem_PermisionarioFact_REN = "PermisionarioFact_REN='".$rem_PermisionarioFact_REN."',";
					$q_rem_PermisionarioFact_RMA = "PermisionarioFact_RMA='".$rem_PermisionarioFact_RMA."',";
				} else {
					$q_rem_PermisionarioFact_RID = "PermisionarioFact_RID=null,";
					$q_rem_PermisionarioFact_REN = "PermisionarioFact_REN=null,";
					$q_rem_PermisionarioFact_RMA = "PermisionarioFact_RMA=null,";
				}
				
				$rem_ConfigAutotranporte_REN = $fila_exp4['ConfigAutotranporte_REN'];
				$rem_ConfigAutotranporte_RID = $fila_exp4['ConfigAutotranporte_RID'];
				$rem_ConfigAutotranporte_RMA = $fila_exp4['ConfigAutotranporte_RMA'];
				if($rem_ConfigAutotranporte_RID > 0){
					$q_rem_ConfigAutotranporte_RID = "ConfigAutotranporte_RID=".$rem_ConfigAutotranporte_RID.",";
					$q_rem_ConfigAutotranporte_REN = "ConfigAutotranporte_REN='".$rem_ConfigAutotranporte_REN."',";
					$q_rem_ConfigAutotranporte_RMA = "ConfigAutotranporte_RMA='".$rem_ConfigAutotranporte_RMA."',";
				} else {
					$q_rem_ConfigAutotranporte_RID = "ConfigAutotranporte_RID=null,";
					$q_rem_ConfigAutotranporte_REN = "ConfigAutotranporte_REN=null,";
					$q_rem_ConfigAutotranporte_RMA = "ConfigAutotranporte_RMA=null,";
				}
				
				
				
				
				
				////////////////////////////////////////////////////////////////Nuevos campos 16/11/2021
				
				//$rem_RemitenteNumRegIdTrib = '';
				$rem_RemitenteNumRegIdTrib = $fila_exp4['RemitenteNumRegIdTrib'];
				if (isset($rem_RemitenteNumRegIdTrib)) {
					//echo "Variable definida!!!";
					$q_rem_RemitenteNumRegIdTrib = "RemitenteNumRegIdTrib='".$rem_RemitenteNumRegIdTrib."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_RemitenteNumRegIdTrib = "";
				}
				
				//$rem_DestinatarioNumRegIdTrib = '';
				$rem_DestinatarioNumRegIdTrib = $fila_exp4['DestinatarioNumRegIdTrib'];
				if (isset($rem_DestinatarioNumRegIdTrib)) {
					//echo "Variable definida!!!";
					$q_rem_DestinatarioNumRegIdTrib = "DestinatarioNumRegIdTrib='".$rem_DestinatarioNumRegIdTrib."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_DestinatarioNumRegIdTrib = "";
				}
				
				//$rem_DestinatarioNumRegIdTrib = '';
				$rem_DestinatarioNumRegIdTrib = $fila_exp4['DestinatarioNumRegIdTrib'];
				if (isset($rem_DestinatarioNumRegIdTrib)) {
					//echo "Variable definida!!!";
					$q_rem_DestinatarioNumRegIdTrib = "DestinatarioNumRegIdTrib='".$rem_DestinatarioNumRegIdTrib."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_DestinatarioNumRegIdTrib = "";
				}
				
				
				$rem_ClaveUnidadPeso_REN = $fila_exp4['ClaveUnidadPeso_REN'];
				$rem_ClaveUnidadPeso_RID = $fila_exp4['ClaveUnidadPeso_RID'];
				$rem_ClaveUnidadPeso_RMA = $fila_exp4['ClaveUnidadPeso_RMA'];
				if($rem_ClaveUnidadPeso_RID > 0){
					$q_rem_ClaveUnidadPeso_RID = "ClaveUnidadPeso_RID=".$rem_ClaveUnidadPeso_RID.",";
					$q_rem_ClaveUnidadPeso_REN = "ClaveUnidadPeso_REN='".$rem_ClaveUnidadPeso_REN."',";
					$q_rem_ClaveUnidadPeso_RMA = "ClaveUnidadPeso_RMA='".$rem_ClaveUnidadPeso_RMA."',";
				} else {
					$q_rem_ClaveUnidadPeso_RID = "";
					$q_rem_ClaveUnidadPeso_REN = "";
					$q_rem_ClaveUnidadPeso_RMA = "";
				}
				
				////////////////////////////////////////////////////////////////Nuevos campos 18/11/2021
				
				//$rem_PesoNeto = '';
				$rem_PesoNeto = $fila_exp4['PesoNeto'];
				if (isset($rem_PesoNeto)) {
					//echo "Variable definida!!!";
					$q_rem_PesoNeto = "PesoNeto=".$rem_PesoNeto.",";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_PesoNeto = "";
				}
				
				//$rem_PesoTara = '';
				$rem_PesoTara = $fila_exp4['PesoTara'];
				if (isset($rem_PesoTara)) {
					//echo "Variable definida!!!";
					$q_rem_PesoTara = "PesoTara=".$rem_PesoTara.",";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_PesoTara = "";
				}
				
				//$rem_AseguraMedAmbiente = '';
				$rem_AseguraMedAmbiente = $fila_exp4['AseguraMedAmbiente'];
				if (isset($rem_AseguraMedAmbiente)) {
					//echo "Variable definida!!!";
					$q_rem_AseguraMedAmbiente = "AseguraMedAmbiente='".$rem_AseguraMedAmbiente."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_AseguraMedAmbiente = "";
				}
				
				//$rem_PolizaMedAmbiente = '';
				$rem_PolizaMedAmbiente = $fila_exp4['PolizaMedAmbiente'];
				if (isset($rem_PolizaMedAmbiente)) {
					//echo "Variable definida!!!";
					$q_rem_PolizaMedAmbiente = "PolizaMedAmbiente='".$rem_PolizaMedAmbiente."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_PolizaMedAmbiente = "";
				}
				
				////////////////////////////////////////////////////////////////Nuevos campos 20/11/2021
				
				//$rem_ParteTransporte = '';
				$rem_ParteTransporte = $fila_exp4['ParteTransporte'];
				if (isset($rem_ParteTransporte)) {
					//echo "Variable definida!!!";
					$q_rem_ParteTransporte = "ParteTransporte='".$rem_ParteTransporte."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_ParteTransporte = "";
				}
				
				////////////////////////////////////////////////////////////////Nuevos campos 29/11/2021
				
				//$rem_ParteTransporte = '';
				$rem_DistanciaRecorrida = $fila_exp4['DistanciaRecorrida'];
				if (isset($rem_DistanciaRecorrida)) {
					//echo "Variable definida!!!";
					$q_rem_DistanciaRecorrida = "DistanciaRecorrida='".$rem_DistanciaRecorrida."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_DistanciaRecorrida = "";
				}
				
				///////////////////////////////////////////////////////////////Nuevos Campos 16/01/2023
				
				//$rem_Ticket = '';
				$rem_Ticket = $fila_exp4['RemisionOperador'];
				if (isset($rem_Ticket)) {
					//echo "Variable definida!!!";
					$q_rem_Ticket = "Ticket='".$rem_Ticket."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_Ticket = "";
				}				
				//$rem_Ticket = '';
				$rem_Comentarios = $fila_exp4['Comentarios'];
				if (isset($rem_Comentarios)) {
					//echo "Variable definida!!!";
					$q_rem_Comentarios = "Comentarios='".$rem_Comentarios."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_rem_Comentarios = "";
				}
				$rem_RemitentePais = $fila_exp4['RemitentePais'];
				$rem_DestinatarioPais = $fila_exp4['DestinatarioPais'];
				
				/**CCPT 3.0 **/
				$Addenda = $fila_exp4['Addendas'];
				$AddCampoA = $fila_exp4['AddCampoA'];
				if (isset($AddCampoA)) {
					//echo "Variable definida!!!";
					$q_AddCampoA = "Addendas='".$Addenda."',
					AddCampoA='".$AddCampoA."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_AddCampoA = "";
				}
				$servicio = $fila_exp4['Servicio_RID'];
				if (isset($servicio)) {
					//echo "Variable definida!!!";
					$q_servicio = "Servicio_REN='Servicios',
					Servicio_RID='".$servicio."',";
				}else{
					//echo "Variable NO definida!!!";
					$q_servicio = "";
				}
				$registroISTMO = $fila_exp4['RegistroISTMO'];
				$destinatarioRegistroISTMO = $fila_exp4['DestinatarioRegistroISTMO_RID'];
				$remitenteRegistroISTMO = $fila_exp4['RemitenteRegistroISTMO_RID'];
				$logisticaInversa = $fila_exp4['LogisticaInversa'];
				$regimenAduanero = $fila_exp4['RegimenAduanero_RID'];
				$moneda = $fila_exp4['Moneda'];
				$parteTrans1 = $fila_exp4['ParteTransporte1_RID'];
				$parteTrans2 = $fila_exp4['ParteTransporte2_RID'];
				$parteTrans3 = $fila_exp4['ParteTransporte3_RID'];
				$figuraTrans1 = $fila_exp4['FiguraTransporte1_RID'];
				$figuraTrans2 = $fila_exp4['FiguraTransporte2_RID'];
				$figuraTransTipo = $fila_exp4['FiguraTransporteTipo1'];
				//FiguraTransporteTipo1


			}
			
			//Realizar UPDATE en Factura
			$mysql_update_factura="UPDATE ".$prefijodb."factura SET 
			CodigoOrigen='".$rem_CodigoOrigen."', 
			Remitente='".$rem_Remitente."', 
			RemitenteRFC='".$rem_RemitenteRFC."', 
			".$q_rem_CitaCarga."
			RemitenteContacto='".$rem_RemitenteContacto."', 
			RemitenteCalle='".$rem_RemitenteCalle."',	
			RemitenteNumExt='".$rem_RemitenteNumExt."',
			RemitenteNumInt='".$rem_RemitenteNumInt."',	
			".$q_rem_ClaveUnidadPeso_RID."
			".$q_rem_ClaveUnidadPeso_REN."
			".$q_rem_ClaveUnidadPeso_RMA."			
			".$q_rem_RemitenteNumRegIdTrib."
			".$q_rem_DestinatarioNumRegIdTrib."
			".$q_rem_RemitenteColonia_RID."
			".$q_rem_RemitenteColonia_REN."
			".$q_rem_RemitenteColonia_RMA."
			".$q_rem_RemitenteLocalidad2_RID."	
			".$q_rem_RemitenteLocalidad2_REN."
			".$q_rem_RemitenteLocalidad2_RMA."
			".$q_rem_RemitenteMunicipio_RID."
			".$q_rem_RemitenteMunicipio_REN."
			".$q_rem_RemitenteMunicipio_RMA."
			".$q_rem_RemitenteEstado_RID."
			".$q_rem_RemitenteEstado_REN."
			".$q_rem_RemitenteEstado_RMA."
			RemitenteCodigoPostal='".$rem_RemitenteCodigoPostal."',	
			RemitenteReferencia='".$rem_RemitenteReferencia."',	
			RemitenteTelefono='".$rem_RemitenteTelefono."',	
			RemitenteSeRecogera='".$rem_RemitenteSeRecogera."',	
			Instrucciones='".$rem_Instrucciones."',	
			CodigoDestino='".$rem_CodigoDestino."',
			Destinatario='".$rem_Destinatario."',	
			DestinatarioRFC='".$rem_DestinatarioRFC."',
			".$q_rem_DestinatarioCitaCarga."
			DestinatarioContacto='".$rem_DestinatarioContacto."',
			DestinatarioCalle='".$rem_DestinatarioCalle."',
			DestinatarioNumExt='".$rem_DestinatarioNumExt."',
			DestinatarioNumInt='".$rem_DestinatarioNumInt."',
			".$q_rem_DestinatarioColonia_RID."
			".$q_rem_DestinatarioColonia_REN."
			".$q_rem_DestinatarioColonia_RMA."
			".$q_rem_DestinatarioLocalidad2_RID."
			".$q_rem_DestinatarioLocalidad2_REN."
			".$q_rem_DestinatarioLocalidad2_RMA."
			".$q_rem_DestinatarioMunicipio_RID."
			".$q_rem_DestinatarioMunicipio_REN."
			".$q_rem_DestinatarioMunicipio_RMA."
			".$q_rem_DestinatarioEstado_RID."
			".$q_rem_DestinatarioEstado_REN."
			".$q_rem_DestinatarioEstado_RMA."
			DestinatarioCodigoPostal='".$rem_DestinatarioCodigoPostal."',
			DestinatarioReferencia='".$rem_DestinatarioReferencia."',
			DestinatarioTelefono='".$rem_DestinatarioTelefono."',
			DestinatarioSeEntregara='".$rem_DestinatarioSeEntregara."',
			Poliza='".$rem_Poliza."',
			FleteTipo='".$rem_FleteTipo."',
			ValorDeclarado='".$rem_ValorDeclarado."',
			ServicioTipo='".$rem_ServicioTipo."',
			Aseguradora='".$rem_Aseguradora."',
			TipoViaje='".$rem_TipoViaje."',
			".$q_rem_Unidad_RID."
			".$q_rem_Unidad_REN."
			".$q_rem_Unidad_RMA."
			".$q_rem_Operador_RID."
			".$q_rem_Operador_REN."
			".$q_rem_Operador_RMA."
			".$q_rem_uRemolqueA_RID."
			".$q_rem_uRemolqueA_REN."
			".$q_rem_uRemolqueA_RMA."
			".$q_rem_uRemolqueB_RID."
			".$q_rem_uRemolqueB_REN."
			".$q_rem_uRemolqueB_RMA."
			".$q_rem_uDolly_RID."
			".$q_rem_uDolly_REN."
			".$q_rem_uDolly_RMA."
			".$q_rem_ConfigAutotranporte_RID."
			".$q_rem_ConfigAutotranporte_REN."
			".$q_rem_ConfigAutotranporte_RMA."
			".$q_rem_Ruta_RID."
			".$q_rem_Ruta_REN."
			".$q_rem_Ruta_RMA."
			".$q_rem_PesoNeto."
			".$q_rem_PesoTara."
			".$q_rem_AseguraMedAmbiente."
			".$q_rem_PolizaMedAmbiente."
			".$q_rem_ParteTransporte."
			".$q_rem_DistanciaRecorrida."
			".$q_rem_PermisionarioFact_REN."
			".$q_rem_PermisionarioFact_RID."
			".$q_rem_PermisionarioFact_RMA."
			".$q_AddCampoA."
			".$q_servicio."
			RemitentePais='".$rem_RemitentePais."',	
			DestinatarioPais='".$rem_DestinatarioPais."',
			RegistroISTMO='".$registroISTMO."',
			DestinatarioRegistroISTMO_REN='c_RegistroISTMO',
			DestinatarioRegistroISTMO_RID='".$destinatarioRegistroISTMO."',
			RemitenteRegistroISTMO_REN='c_RegistroISTMO',
			RemitenteRegistroISTMO_RID='".$remitenteRegistroISTMO."',
			LogisticaInversa='".$logisticaInversa."',
			RegimenAduanero_REN='c_RegimenAduanero',
			RegimenAduanero_RID='".$regimenAduanero."',
			Moneda = '".$moneda."',
			ParteTransporte1_REN='c_Transporte',
			ParteTransporte2_REN='c_Transporte',
			ParteTransporte3_REN='c_Transporte',
			FiguraTransporte1_REN='Operadores',
			FiguraTransporte2_REN='Operadores',
			ParteTransporte1_RID='".$parteTrans1."',
			ParteTransporte2_RID='".$parteTrans2."',
			ParteTransporte3_RID='".$parteTrans3."',
			FiguraTransporte1_RID='".$figuraTrans1."',
			FiguraTransporte2_RID='".$figuraTrans2."',
			FiguraTransporteTipo1='".$figuraTransTipo."'

			WHERE Id=".$id_factura;

			/*
			ParteTransporte1_REN  =  figuraTransTipo
			FiguraTransporte1_REN =  Operadores
			*/
			
			//echo $mysql_update_factura;
			$mysql_update_factura=str_replace("''","NULL",$mysql_update_factura);
			mysql_query($mysql_update_factura);
			
			if ($rem_Ticket != NULL && $rem_Ticket != ''){  //Verifica si el campo Ticket esta en vacío o en NULL
				
				$mysql_update_factura2 = "UPDATE ".$prefijodb."factura SET 
				Ticket = '".$rem_Ticket."'
				WHERE Id=".$id_factura;
				
				if ($rem_Comentarios != NULL && $rem_Comentarios != ''){ //Verifica si el campo Comentarios esta en vacio o en NULL
					$mysql_update_factura2 = "UPDATE ".$prefijodb."factura SET 
					Ticket = '".$rem_Ticket."',
					Comentarios = '".$rem_Comentarios."'
					WHERE Id=".$id_factura;
				} //Sobreescribe la variable $mysql_update_factura2
				
				//echo $mysql_update_factura2;
				
				//die($mysql_update_factura2)
				
				mysql_query($mysql_update_factura2);
				
			}
			
			
			echo "<h2>La Factura se Actualiz� con Exito</h2>";
		} else {
			echo "<h2>La Factura no cuenta con alguna Remision Anexada. No es posible realizar el proceso. Favor de Anexar una Remision.</h2>";
		}
		
	}
		
		
		

	

	

	//http://localhost/cfdipro/factura_trae_datos_remision.php?prefijodb=prueba_&id=1845853

?>