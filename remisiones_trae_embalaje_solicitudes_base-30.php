<?php
//Inicio la transaccion


	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_remision = $_GET["id"];
	
	//Borrar remisionessub de la Remision *************************************
	
	//Buscar IDs de RemisionesSub 
	$sql_01="SELECT * FROM ".$prefijodb."remisionessub WHERE FolioSub_RID = ".$id_remision;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp1=mysql_fetch_array($res_01)){
		$id_remisionessub = $fila_exp1['ID'];
		
		//echo "Eliminar RemisionesSub ID: ".$id_remisionessub." <br>";
		
		mysql_query("DELETE FROM ".$prefijodb."remisionessub WHERE ID = ".$id_remisionessub);

	}
	
	//Buscar ID  de Solicitud que cre� la Remision
	$sql_03="SELECT * FROM ".$prefijodb."remisiones WHERE ID = ".$id_remision." ORDER BY ID ASC";
	//echo $sql_03;
	$res_03=mysql_query($sql_03);
	
	while ($fila_exp3=mysql_fetch_array($res_03)){
		$id_solicitud = $fila_exp3['SolicitudID'];
	}
	
	if($id_solicitud > 0){
			
			//Buscar registros de SolicitudesSub de la Solicitud
			$sql_04="SELECT * FROM ".$prefijodb."solicitudessub WHERE FolioSub_RID = ".$id_solicitud;
				//echo $sql_04;
				$res_04=mysql_query($sql_04);
				$num_rows2 = mysql_num_rows($res_04);
				if($num_rows2 > 0){
					while ($fila_exp4=mysql_fetch_array($res_04)){
						$remisionsub_id = $fila_exp4['ID'];
						$remisionsub_BASVERSION = $fila_exp4['BASVERSION'];
						$remisionsub_BASTIMESTAMP = $fila_exp4['BASTIMESTAMP'];
						$remisionsub_Documentador = $fila_exp4['Documentador'];
						$remisionsub_Pedimento = $fila_exp4['Pedimento'];
						$remisionsub_Orden = $fila_exp4['Orden'];
						$remisionsub_Comentario = $fila_exp4['Comentario'];
						$remisionsub_Referencia = $fila_exp4['Referencia'];
						$remisionsub_Proveedor = $fila_exp4['Proveedor'];
						$remisionsub_PesoCobrar = $fila_exp4['PesoCobrar']; 
						if($remisionsub_PesoCobrar > 0){
							$s_remisionsub_PesoCobrar = 'PesoCobrar,';
							$s1_remisionsub_PesoCobrar = $remisionsub_PesoCobrar.",";
						} else {
							$s_remisionsub_PesoCobrar = '';
							$s1_remisionsub_PesoCobrar = '';
						}
						$remisionsub_Tipo = $fila_exp4['Tipo'];
						$remisionsub_Sello = $fila_exp4['Sello'];
						$remisionsub_Descripcion = $fila_exp4['Descripcion'];
						$remisionsub_PesoEstimado = $fila_exp4['PesoEstimado'];
						if($remisionsub_PesoEstimado > 0){
							$s_remisionsub_PesoEstimado = 'PesoEstimado,';
							$s1_remisionsub_PesoEstimado = $remisionsub_PesoEstimado.",";
						} else {
							$s_remisionsub_PesoEstimado = '';
							$s1_remisionsub_PesoEstimado = '';
						}
						$remisionsub_FolioSub_REN = $fila_exp4['FolioSub_REN'];
						//$remisionsub_FolioSub_RMA = $fila_exp4['FolioSub_RMA'];
						$remisionsub_FolioSub_RID = $fila_exp4['FolioSub_RID'];
						if($remisionsub_FolioSub_RID > 0){
							$s_remisionsub_FolioSub_RID = 'FolioSub_RID,';
							$s1_remisionsub_FolioSub_RID = $remisionsub_FolioSub_RID.",";
						} else {
							$s_remisionsub_FolioSub_RID = '';
							$s1_remisionsub_FolioSub_RID = '';
						}
						$remisionsub_BKG = $fila_exp4['BKG'];
						$remisionsub_Peso = $fila_exp4['Peso'];
						if($remisionsub_Peso > 0){
							$s_remisionsub_Peso = 'Peso,';
							$s1_remisionsub_Peso = $remisionsub_Peso.",";
						} else {
							$s_remisionsub_Peso = '';
							$s1_remisionsub_Peso = '';
						}
						$remisionsub_Cantidad = $fila_exp4['Cantidad'];
						if($remisionsub_Peso > 0){
							$s_remisionsub_Cantidad = 'Cantidad,';
							$s1_remisionsub_Cantidad = $remisionsub_Cantidad.",";
						} else {
							$s_remisionsub_Cantidad = '';
							$s1_remisionsub_Cantidad = '';
						}
						$remisionsub_PesoVolumen = $fila_exp4['PesoVolumen'];
						if($remisionsub_PesoVolumen > 0){
							$s_remisionsub_PesoVolumen = 'PesoVolumen,';
							$s1_remisionsub_PesoVolumen = $remisionsub_PesoVolumen.",";
						} else {
							$s_remisionsub_PesoVolumen = '';
							$s1_remisionsub_PesoVolumen = '';
						}	
						$remisionsub_Movimientos = $fila_exp4['Movimientos'];
						$remisionsub_BL = $fila_exp4['BL'];
						$remisionsub_Modificado = $fila_exp4['Modificado'];
						$remisionsub_Embalaje = $fila_exp4['Embalaje'];
						$remisionsub_OrdenCompra = $fila_exp4['OrdenCompra'];
						$remisionsub_Mt3 = $fila_exp4['Mt3'];
						if($remisionsub_Mt3 > 0){
							$s_remisionsub_Mt3 = 'Mt3,';
							$s1_remisionsub_Mt3 = $remisionsub_Mt3.",";
						} else {
							$s_remisionsub_Mt3 = '';
							$s1_remisionsub_Mt3 = '';
						}
						
						//$remisionsub_ClaveUnidadPeso_RID = "";
						$remisionsub_ClaveUnidadPeso_RID = $fila_exp4['ClaveUnidadPeso_RID'];
						if (isset($remisionsub_ClaveUnidadPeso_RID)) {
							//$remisionsub_ClaveUnidadPeso_REN = $fila_exp4['ClaveUnidadPeso_REN'];
							$s_remisionsub_ClaveUnidadPeso_RID = 'ClaveUnidadPeso_RID,ClaveUnidadPeso_REN,';
							$s1_remisionsub_ClaveUnidadPeso_RID = $remisionsub_ClaveUnidadPeso_RID.",'c_ClaveUnidadPeso',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_ClaveUnidadPeso_RID = '';
							$s1_remisionsub_ClaveUnidadPeso_RID = '';
						}
						
						//$remisionsub_ClaveProdServCP_RID = "";
						$remisionsub_ClaveProdServCP_RID = $fila_exp4['ClaveProdServCP_RID'];
						if (isset($remisionsub_ClaveProdServCP_RID)) {
							//$remisionsub_ClaveProdServCP_REN = $fila_exp4['ClaveProdServCP_REN'];
							$s_remisionsub_ClaveProdServCP_RID = 'ClaveProdServCP_RID,ClaveProdServCP_REN,';
							$s1_remisionsub_ClaveProdServCP_RID = $remisionsub_ClaveProdServCP_RID.",'c_ClaveProdServCP',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_ClaveProdServCP_RID = '';
							$s1_remisionsub_ClaveProdServCP_RID = '';
						}
						
						//$remisionsub_TipoEmbalaje_RID = "";
						$remisionsub_TipoEmbalaje_RID = $fila_exp4['TipoEmbalaje_RID'];
						if (isset($remisionsub_TipoEmbalaje_RID)) {
							//$remisionsub_TipoEmbalaje_REN = $fila_exp4['TipoEmbalaje_REN'];
							$s_remisionsub_TipoEmbalaje_RID = 'TipoEmbalaje_RID,TipoEmbalaje_REN,';
							$s1_remisionsub_TipoEmbalaje_RID = $remisionsub_TipoEmbalaje_RID.",'c_TipoEmbalaje',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_TipoEmbalaje_RID = '';
							$s1_remisionsub_TipoEmbalaje_RID = '';
						}
						
						$remisionsub_MaterialPeligrosoC = $fila_exp4['MaterialPeligrosoC'];
						
						//$remisionsub_MaterialPeligroso_RID = "";
						$remisionsub_MaterialPeligroso_RID = $fila_exp4['MaterialPeligroso_RID'];
						if (isset($remisionsub_MaterialPeligroso_RID)) {
							//$remisionsub_MaterialPeligroso_REN = $fila_exp4['MaterialPeligrosoC_REN'];
							$s_remisionsub_MaterialPeligroso_RID = 'MaterialPeligroso_RID,MaterialPeligroso_REN,';
							$s1_remisionsub_MaterialPeligroso_RID = $remisionsub_MaterialPeligroso_RID.",'c_MaterialPeligroso',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_MaterialPeligroso_RID = '';
							$s1_remisionsub_MaterialPeligroso_RID = '';
						}
						
						
						$remisionsub_NumeroPedimento = $fila_exp4['NumeroPedimento'];
						
						
						//$remisionsub_FraccionArancelaria_RID = "";
						$remisionsub_FraccionArancelaria_RID = $fila_exp4['FraccionArancelaria_RID'];
						if (isset($remisionsub_FraccionArancelaria_RID)) {
							//$remisionsub_FraccionArancelaria_REN = $fila_exp4['FraccionArancelaria_REN'];
							$s_remisionsub_FraccionArancelaria_RID = 'FraccionArancelaria_RID,FraccionArancelaria_REN,';
							$s1_remisionsub_FraccionArancelaria_RID = $remisionsub_FraccionArancelaria_RID.",'c_FraccionArancelaria',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_FraccionArancelaria_RID = '';
							$s1_remisionsub_FraccionArancelaria_RID = '';
						}
						
						$remisionsub_UUIDComercioExt = $fila_exp4['UUIDComercioExt'];
						
						////////////////////////////////////////////////////////////////Nuevos campos 16/11/2021
						//$remisionsub_Dimensiones = "";
						$remisionsub_Dimensiones = $fila_exp4['Dimensiones'];
						if (isset($remisionsub_Dimensiones)) {
							$s_remisionsub_Dimensiones = 'Dimensiones,';
							$s1_remisionsub_Dimensiones = "'".$remisionsub_Dimensiones."',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_Dimensiones = '';
							$s1_remisionsub_Dimensiones = '';
						}
						
						//$remisionsub_ValorMercancia = "";
						$remisionsub_ValorMercancia = $fila_exp4['ValorMercancia'];
						if (isset($remisionsub_ValorMercancia)) {
							$s_remisionsub_ValorMercancia = 'ValorMercancia,';
							$s1_remisionsub_ValorMercancia = $remisionsub_ValorMercancia.",";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_ValorMercancia = '';
							$s1_remisionsub_ValorMercancia = '';
						}
						
						//$remisionsub_Moneda = "";
						$remisionsub_Moneda = $fila_exp4['Moneda'];
						if (isset($remisionsub_Moneda)) {
							$s_remisionsub_Moneda = 'Moneda,';
							$s1_remisionsub_Moneda = "'".$remisionsub_Moneda."',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_Moneda = '';
							$s1_remisionsub_Moneda = '';
						}
						
						
						////////////////////////////////////////////////////////////////Nuevos campos 18/11/2021
						//$remisionsub_PesoNeto = "";
						$remisionsub_PesoNeto = $fila_exp4['PesoNeto'];
						if (isset($remisionsub_PesoNeto)) {
							$s_remisionsub_PesoNeto = 'PesoNeto,';
							$s1_remisionsub_PesoNeto = "'".$remisionsub_PesoNeto."',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_PesoNeto = '';
							$s1_remisionsub_PesoNeto = '';
						}
						
						//$remisionsub_PesoTara = "";
						$remisionsub_PesoTara = $fila_exp4['PesoTara'];
						if (isset($remisionsub_PesoTara)) {
							$s_remisionsub_PesoTara = 'PesoTara,';
							$s1_remisionsub_PesoTara = "'".$remisionsub_PesoTara."',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_PesoTara = '';
							$s1_remisionsub_PesoTara = '';
						}
						
						//$remisionsub_IDOrigen_REN = "";
						$remisionsub_IDOrigen_RID = $fila_exp4['IDOrigen_RID'];
						if (isset($remisionsub_IDOrigen_RID)) {
							$s_remisionsub_IDOrigen_RID = 'IDOrigen_RID,IDOrigen_REN,';
							$s1_remisionsub_IDOrigen_RID = $remisionsub_IDOrigen_RID.",'ClientesDestinos',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_IDOrigen_RID = '';
							$s1_remisionsub_IDOrigen_RID = '';
						}
						
						//$remisionsub_IDDestino_REN = "";
						$remisionsub_IDDestino_RID = $fila_exp4['IDDestino_RID'];
						if (isset($remisionsub_IDDestino_RID)) {
							$s_remisionsub_IDDestino_RID = 'IDDestino_RID,IDDestino_REN,';
							$s1_remisionsub_IDDestino_RID = $remisionsub_IDDestino_RID.",'ClientesDestinos',";
						}else{
							//echo "Variable NO definida!!!";
							$s_remisionsub_IDDestino_RID = '';
							$s1_remisionsub_IDDestino_RID = '';
						}

						/* CCPT 3.0 */

						$remisionsub_SectorCOFEPRIS = $fila_exp4['SectorCOFEPRIS_RID'];
						$remisionsub_NombreIngredienteActivo = $fila_exp4['NombreIngredienteActivo'];
						$remisionsub_NomQuimico = $fila_exp4['NomQuimico'];
						$remisionsub_DenominacionGenericaProd = $fila_exp4['DenominacionGenericaProd'];
						$remisionsub_DenominacionDistintivaProd = $fila_exp4['DenominacionDistintivaProd'];
						$remisionsub_Fabricante = $fila_exp4['Fabricante'];
						$remisionsub_FechaCaducidad = $fila_exp4['FechaCaducidad'];
						$remisionsub_LoteMedicamento = $fila_exp4['LoteMedicamento'];
						$remisionsub_FormaFarmaceutica = $fila_exp4['FormaFarmaceutica_RID'];
						$remisionsub_CondicionesEspTransp = $fila_exp4['CondicionesEspTransp_RID'];
						$remisionsub_RegistroSanitarioFolioAutorizacion = $fila_exp4['RegistroSanitarioFolioAutorizacion'];
						$remisionsub_PermisoImportacion = $fila_exp4['PermisoImportacion'];
						$remisionsub_FolioImpoVUCEM = $fila_exp4['FolioImpoVUCEM'];
						$remisionsub_NumCAS = $fila_exp4['NumCAS'];
						$remisionsub_RazonSocialEmpImp = $fila_exp4['RazonSocialEmpImp'];
						$remisionsub_NumRegSanPlagCOFEPRIS = $fila_exp4['NumRegSanPlagCOFEPRIS'];
						$remisionsub_DatosFabricante = $fila_exp4['DatosFabricante'];
						$remisionsub_DatosFormulador = $fila_exp4['DatosFormulador'];
						$remisionsub_DatosMaquilador = $fila_exp4['DatosMaquilador'];
						$remisionsub_UsoAutorizado = $fila_exp4['UsoAutorizado'];
						$remisionsub_TipoMateria = $fila_exp4['TipoMateria_RID'];
						$remisionsub_DescripcionMateria = $fila_exp4['DescripcionMateria'];
						$remisionsub_TipoDocumento = $fila_exp4['TipoDocumento_RID'];
						$remisionsub_IdentDocAduanero = $fila_exp4['IdentDocAduanero'];
						$remisionsub_RFCImpo = $fila_exp4['RFCImpo'];
						
						//Crear Nuevo ID
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
								
						$sql_insert1="INSERT INTO ".$prefijodb."remisionessub 
						(ID, 
						BASVERSION, 
						BASTIMESTAMP, 
						Documentador, 
						Pedimento,
						Orden,
						Comentario,
						Referencia,
						Proveedor,
						".$s_remisionsub_PesoCobrar."
						Tipo,
						Sello,
						Descripcion,
						".$s_remisionsub_PesoEstimado."
						FolioSub_REN,
						FolioSub_RMA,
						FolioSub_RID,
						BKG,
						".$s_remisionsub_Peso."
						".$s_remisionsub_Cantidad."
						".$s_remisionsub_PesoVolumen."
						Movimientos,
						BL,
						Modificado,
						Embalaje,
						OrdenCompra,
						".$s_remisionsub_Mt3."
						".$s_remisionsub_ClaveUnidadPeso_RID."
						".$s_remisionsub_ClaveProdServCP_RID."
						".$s_remisionsub_TipoEmbalaje_RID."
						MaterialPeligrosoC,
						".$s_remisionsub_MaterialPeligroso_RID."
						NumeroPedimento,
						".$s_remisionsub_FraccionArancelaria_RID."
						".$s_remisionsub_Dimensiones."
						".$s_remisionsub_ValorMercancia."
						".$s_remisionsub_Moneda."
						".$s_remisionsub_PesoNeto."
						".$s_remisionsub_PesoTara."
						".$s_remisionsub_IDOrigen_RID."
						".$s_remisionsub_IDDestino_RID."
						UUIDComercioExt,
						SectorCOFEPRIS_REN,
						SectorCOFEPRIS_RID,
						NombreIngredienteActivo,
						NomQuimico,
						DenominacionGenericaProd,
						DenominacionDistintivaProd,
						Fabricante,
						FechaCaducidad,
						LoteMedicamento,
						FormaFarmaceutica_REN,
						FormaFarmaceutica_RID,
						CondicionesEspTransp_REN,
						CondicionesEspTransp_RID,
						RegistroSanitarioFolioAutorizacion,
						PermisoImportacion,
						FolioImpoVUCEM,
						NumCAS,
						RazonSocialEmpImp,
						NumRegSanPlagCOFEPRIS,
						DatosFabricante,
						DatosFormulador,
						DatosMaquilador,
						UsoAutorizado,
						TipoMateria_REN,
						TipoMateria_RID,
						DescripcionMateria,
						TipoDocumento_REN,
						TipoDocumento_RID,
						IdentDocAduanero,
						RFCImpo
						)
						VALUES (".$newid.",
						".$remisionsub_BASVERSION.", 
						'".$remisionsub_BASTIMESTAMP."',
						'".$remisionsub_Documentador."',
						'".$remisionsub_Pedimento."',
						'".$remisionsub_Orden."',
						'".$remisionsub_Comentario."',
						'".$remisionsub_Referencia."',
						'".$remisionsub_Proveedor."',
						".$s1_remisionsub_PesoCobrar."
						'".$remisionsub_Tipo."',
						'".$remisionsub_Sello."',
						'".$remisionsub_Descripcion."',
						".$s1_remisionsub_PesoEstimado."
						'Remisiones',
						'FolioSub',
						".$id_remision.",
						'".$remisionsub_BKG."',
						".$s1_remisionsub_Peso."
						".$s1_remisionsub_Cantidad."
						".$s1_remisionsub_PesoVolumen."
						'".$remisionsub_Movimientos."',
						'".$remisionsub_BL."',
						'".$remisionsub_Modificado."',
						'".$remisionsub_Embalaje."',
						'".$remisionsub_OrdenCompra."',
						".$s1_remisionsub_Mt3."
						".$s1_remisionsub_ClaveUnidadPeso_RID."
						".$s1_remisionsub_ClaveProdServCP_RID."
						".$s1_remisionsub_TipoEmbalaje_RID."
						'".$remisionsub_MaterialPeligrosoC."',
						".$s1_remisionsub_MaterialPeligroso_RID."
						'".$remisionsub_NumeroPedimento."',
						".$s1_remisionsub_FraccionArancelaria_RID."
						".$s1_remisionsub_Dimensiones."
						".$s1_remisionsub_ValorMercancia."
						".$s1_remisionsub_Moneda."
						".$s1_remisionsub_PesoNeto."
						".$s1_remisionsub_PesoTara."
						".$s1_remisionsub_IDOrigen_RID."
						".$s1_remisionsub_IDDestino_RID."
						'".$remisionsub_UUIDComercioExt."',
						'c_SectorCofepris',
						'".$remisionsub_SectorCOFEPRIS."',
						'".$remisionsub_NombreIngredienteActivo."',
						'".$remisionsub_NomQuimico."',
						'".$remisionsub_DenominacionGenericaProd."',
						'".$remisionsub_DenominacionDistintivaProd."',
						'".$remisionsub_Fabricante."',
						'".$remisionsub_FechaCaducidad."',
						'".$remisionsub_LoteMedicamento."',
						'c_FormaFarmaceutica',
						'".$remisionsub_FormaFarmaceutica."',
						'c_CondicionesEspeciales',
						'".$remisionsub_CondicionesEspTransp."',
						'".$remisionsub_RegistroSanitarioFolioAutorizacion."',
						'".$remisionsub_PermisoImportacion."',
						'".$remisionsub_FolioImpoVUCEM."',
						'".$remisionsub_NumCAS."',
						'".$remisionsub_RazonSocialEmpImp."',
						'".$remisionsub_NumRegSanPlagCOFEPRIS."',
						'".$remisionsub_DatosFabricante."',
						'".$remisionsub_DatosFormulador."',
						'".$remisionsub_DatosMaquilador."',
						'".$remisionsub_UsoAutorizado."',
						'c_TipoMateria',
						'".$remisionsub_TipoMateria."',
						'".$remisionsub_DescripcionMateria."',
						'c_DocumentoAduanero',
						'".$remisionsub_TipoDocumento."',
						'".$remisionsub_IdentDocAduanero."',
						'".$remisionsub_RFCImpo."'
						)";
						
						//die ($sql_insert1);
						
						//echo $sql_insert1;
						$sql_insert1=str_replace("''","NULL",$sql_insert1);
						mysql_query($sql_insert1);
						

					}
					
				} else {
					//echo "<h2>La Remision no cuenta con registros de Embalaje</h2>";
				}
		
		echo "<h2>Se realizo la Actualizaci�n de Embalaje con Exito</h2>";
		
	} else {
		echo "<h2>La Remision no se cre� desde una Solicitud </h2>";
	}
	
	
	

	

	//http://localhost/cfdipro/factura_trae_embalaje_rem.php?prefijodb=prueba_&id=3089447

?>