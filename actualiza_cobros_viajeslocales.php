<?php 

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);



//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
	

//Buscar Viajes Locales-- PENDIENTE condicion de que viajes considerar para la actualización
	$resSQL00 = "SELECT * FROM " . $prefijobd . "viajeslocales WHERE FolioSubFactura_RID IS NULL OR FolioSubFactura_RID = ''";
	//echo $resSQL00."<br>";
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	while ($rowSQL00 = mysql_fetch_assoc($runSQL00)){ 
		$nv_id = $rowSQL00['ID'];
		$nv_mo = $rowSQL00['Mo'];
		$nv_clienteid = $rowSQL00['ClienteS_RID'];
		$nv_proveedorid = $rowSQL00['Proveedor_RID'];
		$nv_cobroproveedor = $rowSQL00['CobroProveedor'];
		$nv_cobrocliente = $rowSQL00['CobroCliente'];
		$nv_transportista = $rowSQL00['Transportista'];
		$nv_cliente = $rowSQL00['Cliente'];
		
		
				if($nv_clienteid > 0){
					if($nv_cobroproveedor > 0){
					} else {
						
						$resSQL01 = "SELECT * FROM " . $prefijobd . "clasificacionviajes WHERE Codigo = '".$nv_mo."'";
						//echo $resSQL01."<br>";
						$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
						while ($rowSQL01 = mysql_fetch_assoc($runSQL01)){ 
							$clasificacion_viaje_id = $rowSQL01['ID'];
						}
						
						//Busqueda de Tarifas en Cliente
						$resSQL02 = "SELECT * FROM " . $prefijobd . "clientestarifasclasificacion WHERE ClasificacionViaje_RID = ".$clasificacion_viaje_id." AND FolioTarifasClasificacion_RID = ".$nv_clienteid;
						//echo $resSQL02."<br>";
						$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
						while ($rowSQL02 = mysql_fetch_assoc($runSQL02)){ 
							$ct_demoras = $rowSQL02['Demoras'];
							$ct_maniobras = $rowSQL02['Maniobras'];
							$ct_repartos = $rowSQL02['Repartos'];
							$ct_flete = $rowSQL02['Flete'];
							$ct_importe = $rowSQL02['Importe'];
						}
						$total_tc = 0;
						//Contar registros encontrados
						$resSQL03 = "SELECT COUNT(*) AS total FROM " . $prefijobd . "clientestarifasclasificacion WHERE ClasificacionViaje_RID = ".$clasificacion_viaje_id." AND FolioTarifasClasificacion_RID = ".$nv_clienteid;
						//echo $resSQL03."<br>";
						$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
						while ($rowSQL03 = mysql_fetch_assoc($runSQL03)){ 
							$total_tc = $rowSQL03['total'];
						}
						
						if($total_tc > 0){
							//Si tiene registro
							$v_cobro_cliente = $ct_importe;
						} else {
							//No tiene registros en el catalogo de clientestarifasclasificacion
							//Buscar tarifa en catalogo clasificacion viajes
							$resSQL04 = "SELECT * FROM " . $prefijobd . "clasificacionviajes WHERE Codigo = '".$nv_mo."'";
							//echo $resSQL04."<br>";
							$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
							while ($rowSQL04 = mysql_fetch_assoc($runSQL04)){ 
								$v_cobro_cliente = $rowSQL04['TarifaCliente'];
							}
							
						}
						
						//Actualizar importes de Cobros en Viajes Locales
						mysql_query("UPDATE " . $prefijobd . "viajeslocales SET 
						CobroCliente = ".$v_cobro_cliente."
						WHERE ID = ".$nv_id."");
						
					} //Fin Valida Cobro Cliente
					
					
				}  else {
					$mensaje2 = "Viaje local sin Cliente registrado -- NOMBRE: ".$nv_cliente."<br>";
					echo $mensaje2;
				} //Fin valida Cliente en Viaje Local //Fin Valida Cliente
					
					//Busqueda de Tarifas en Proveedores
					$total_pc = 0;
					$v_cobro_proveeedor=0;
					$flag = 0;
					
				//Validar Proveeedores
				if($nv_proveedorid > 0){
						
					if($nv_cobroproveedor > 0){
					} else {
						
						//Contar registros en Proveedores tarifas
						$resSQL005 = "SELECT COUNT(*) as total2 FROM " . $prefijobd . "proveedores_ref WHERE ID = ".$nv_proveedorid;
						//echo $resSQL005."<br>";
						$runSQL005 = mysql_query($resSQL005, $cnx_cfdi);
						while ($rowSQL005 = mysql_fetch_assoc($runSQL005)){ 
							$total_pc = $rowSQL005['total2'];
							//echo "<br>";
							//echo "Total registros en Proveedores: ".$total_pc;
						}
						
						//Si tiene registros se busca la tarifa correspondiente
						if($total_pc > 0){
							//Si tiene registros, vericar que exista el correspondiente
							//Busca tarifas disponibles en catalogo
							//echo "<br>";
							//echo "Si hay registros en catalogo.";
							//echo "<br>";
							$resSQL05 = "SELECT * FROM " . $prefijobd . "proveedores_ref WHERE ID = ".$nv_proveedorid;
							//echo $resSQL05."<br>";
							$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
							while ($rowSQL05 = mysql_fetch_assoc($runSQL05)){ 
								$v_tarifaclasificacion_id = $rowSQL05['RID'];
								//echo "<br>";
								//echo "Registro en Proveedores: ".$v_tarifaclasificacion_id;
								
								//Identificar tarifas disponibles
								$resSQL06 = "SELECT * FROM " . $prefijobd . "proveedorestarifasclasificacion WHERE ID = ".$v_tarifaclasificacion_id;
								//echo $resSQL06."<br>";
								$runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
								while ($rowSQL06 = mysql_fetch_assoc($runSQL06)){ 
									$vp_clasificacion_id = $rowSQL06['ClasificacionViaje_RID'];
									$pt_demoras = $rowSQL06['Demoras'];
									$pt_maniobras = $rowSQL06['Maniobras'];
									$pt_repartos = $rowSQL06['Repartos'];
									$pt_flete = $rowSQL06['Flete'];
								}
								//echo "<br>";
								//echo "Datos en Proveedores: ".$v_tarifaclasificacion_id." - D: ".$pt_demoras." M: ".$pt_maniobras." R: ".$pt_repartos." F: ".$pt_flete;
								
								//Verificar si la clasificacion viaje corresponde al Viaje Local
								if ($vp_clasificacion_id == $clasificacion_viaje_id){
									//Si corresponde
									$v_cobro_proveeedor = $pt_demoras + $pt_maniobras + $pt_repartos + $pt_flete;
									//echo "<br>";
									//echo "Clasificacion de Viaje Local Corresponde ".$v_tarifaclasificacion_id." Cobro Proveedor: ".$v_cobro_proveeedor;
									$flag = 1;
								} else {
									//no corresponde
									//$v_cobro_proveeedor = 0;
									//echo "<br>";
									//echo "Clasificacion de Viaje Local NO Corresponde";
								}
								
								//echo "<br>";
								
								if ($flag == 0){
									//No tiene registros en el catalogo
									$resSQL007 = "SELECT * FROM " . $prefijobd . "clasificacionviajes WHERE Codigo = '".$nv_mo."'";
									//echo $resSQL007."<br>";
									$runSQL007 = mysql_query($resSQL007, $cnx_cfdi);
									while ($rowSQL007 = mysql_fetch_assoc($runSQL007)){ 
										$v_cobro_proveeedor = $rowSQL007['TarifaProveedor'];
									}
									//echo "<br>";
									//echo "Tarifa base aplicada a Cobro Proveedor ".$v_cobro_proveeedor;
								}
								
							}
							
							

							
						} else {
							//No tiene registros en el catalogo
							$resSQL004 = "SELECT * FROM " . $prefijobd . "clasificacionviajes WHERE Codigo = '".$nv_mo."'";
							//echo $resSQL004."<br>";
							$runSQL004 = mysql_query($resSQL004, $cnx_cfdi);
							while ($rowSQL004 = mysql_fetch_assoc($runSQL004)){ 
								$v_cobro_proveeedor = $rowSQL004['TarifaProveedor'];
							}
							//echo "<br>";
							//echo "Tarifa base aplicada a Cobro Proveedor ".$v_cobro_proveeedor;
							
						}
						
						//Actualizar importes de Cobros en Viajes Locales
						mysql_query("UPDATE " . $prefijobd . "viajeslocales SET 
						CobroProveedor = ".$v_cobro_proveeedor."
						WHERE ID = ".$nv_id."");
						
					}//Fin Valida Cobro Proveedor
						
					
						
				} else {
						
					$mensaje1 = "Viaje local sin Proveedor registrado -- NOMBRE: ".$nv_transportista."<br>";
					echo $mensaje1;
						
				} //Fin Valida Proveedores
					
				
					
					
					
					

		
	} 
	
	echo "<h2>Cobro a Proveedor y Cobro a Clientes de Viajes Locales Actualizados con Éxito</h2>";
	
	
//http://localhost/cfdipro/actualiza_cobros_viajeslocales_copy.php?prefijodb=prbsolosa_

//echo "<h2>Importes de Conceptos en Factura ".$xfolio." actualizados correctmente </h2>";
	
?>






