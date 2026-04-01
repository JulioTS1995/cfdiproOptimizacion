<?php
/* NombreArchivo.php:
 * 
 * Recibe:
 * 	Id del registro
 * 	Instancia de la base de datos - prefijo
 */
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);


//======================================================================
//Verifico que vengan todos los parametros y que ninguno sea vacio

if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die("Falta id de la liquidacion");
}
if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    die("Falta el prefijo de la base de datos");
}

$id_liq = $_REQUEST["id"];
$prefijobd = $_REQUEST["prefijo"];

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


$resSQL1="SELECT * FROM ".$prefijobd."liquidaciones WHERE ID = ".$id_liq;			
$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
while($rowSQL1 = mysql_fetch_assoc($runSQL1)){
	$xfolio_liq = $rowSQL1['XFolio'];
}

$vpeaje = 0;
$vrefacciones = 0;
$vreparaciones = 0;
$vvarios_otros = 0;
$vadministracion = 0;
$vanticipo_viaje = 0;
$valimentos = 0;
$vauxiliar_comidas = 0;
$vauxiliar_maniobras = 0;
$vbascula = 0;
$vcasetas = 0;
$vcasetas_viapass = 0;
$vcombustible_diesel = 0;
$vcombustible_epol = 0;
$vcombustible_gasolina = 0;
$vcombustible_ticket = 0;
$vcomision_viaje = 0;
$vdeducible = 0;
$vespecificar = 0;
$vestacionamiento = 0;
$vferries = 0;
$vfitosanitarias = 0;
$vfondeo = 0;
$vgastos = 0;
$vgratificaciones = 0;
$vgruas_montecargas = 0;
$vhotel = 0;
$vmaniobras = 0;
$vpaqueteria = 0;
$vpasajes_taxis = 0;
$vpension = 0;
$vreparaciones_talachas = 0;
$vtaller = 0;
$vtransito = 0;
$vvarios = 0;
$vviaticos = 0;
$vdescuentocarga = 0;
$vdescuentodescarga = 0;
$vcargomercancia = 0;


$resSQL2="SELECT * FROM ".$prefijobd."gastosviajes WHERE Liquidacion = '".$xfolio_liq."' AND TipoVale = 'Deposito'";			
$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
	while($rowSQL2 = mysql_fetch_assoc($runSQL2)){
		$id_gasto_viaje = $rowSQL2['ID'];
		
		$resSQL3="SELECT * FROM ".$prefijobd."gastosviajes_ref WHERE ID = ".$id_gasto_viaje;			
		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
		while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
			$id_gasto_viaje_sub = $rowSQL3['RID'];
			
			$resSQL4="SELECT * FROM ".$prefijobd."gastosviajessub WHERE ID = ".$id_gasto_viaje_sub;			
			$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
			while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
				$concepto = $rowSQL4['Concepto'];
				$importe = $rowSQL4['Cantidad'];
				
				//Actualizar cada concepto en Liquidacion
				
				if($concepto == 'Peaje'){
					$vpeaje = $vpeaje + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zPeaje = ".$vpeaje."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				}elseif($concepto == 'Refacciones'){
					$vrefacciones = $vrefacciones + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zRefacciones = ".$vrefacciones."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				}elseif($concepto == 'Reparaciones'){
					$vreparaciones = $vreparaciones + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zReparaciones = ".$vreparaciones."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				}elseif($concepto == 'Varios/Otros'){
					$vvarios_otros = $vvarios_otros + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zVarios = ".$vvarios_otros."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Administracion'){
					$vadministracion = $vadministracion + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zAdministracion = ".$vadministracion."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Anticipo Viaje'){
					$vanticipo_viaje = $vanticipo_viaje + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zAnticipoViaje = ".$vanticipo_viaje."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Alimentos'){
					$valimentos = $valimentos + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zAlimentos = ".$valimentos."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Auxiliar Comidas'){
					$vauxiliar_comidas = $vauxiliar_comidas + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zAuxiliarComidas = ".$vauxiliar_comidas."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Auxiliar Maniobras'){
					$vauxiliar_maniobras = $vauxiliar_maniobras + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zAuxiliarManiobras = ".$vauxiliar_maniobras."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Bascula'){
					$vbascula = $vbascula + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zBascula = ".$vbascula."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Casetas'){
					$vcasetas = $vcasetas + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCasetas = ".$vcasetas."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Casetas VIAPASS'){
					$vcasetas_viapass = $vcasetas_viapass + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCasetasVIAPASS = ".$vcasetas_viapass."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Combustible Diesel'){
					$vcombustible_diesel = $vcombustible_diesel + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCombustibleDiesel = ".$vcombustible_diesel."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Combustible EPOL/Servifacil'){
					$vcombustible_epol = $vcombustible_epol + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCombustibleEPOLServifacil = ".$vcombustible_epol."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Combustible Gasolina'){
					$vcombustible_gasolina = $vcombustible_gasolina + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCombustibleGasolina = ".$vcombustible_gasolina."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Combustible Ticket Card'){
					$vcombustible_ticket = $vcombustible_ticket + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCombustibleTicketCard = ".$vcombustible_ticket."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Comision Viaje'){
					$vcomision_viaje = $vcomision_viaje + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zComisionViaje = ".$vcomision_viaje."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Deducible'){
					$vdeducible = $vdeducible + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zDeducible = ".$vdeducible."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Especificar'){
					$vespecificar = $vespecificar + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zEspecificar = ".$vespecificar."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Estacionamiento'){
					$vestacionamiento = $vestacionamiento + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zEstacionamiento = ".$vestacionamiento."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Ferries'){
					$vferries = $vferries + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zFerries = ".$vferries."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Fitosanitarias'){
					$vfitosanitarias = $vfitosanitarias + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zFitosanitarias = ".$vfitosanitarias."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Fondeo'){
					$vfondeo = $vfondeo + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zFondeo = ".$vfondeo."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Gastos'){
					$vgastos = $vgastos + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zGastos = ".$vgastos."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Gratificaciones'){
					$vgratificaciones = $vgratificaciones + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zGratificaciones = ".$vgratificaciones."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Gruas Montecargas'){
					$vgruas_montecargas = $vgruas_montecargas + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zGruasMontecargas = ".$vgruas_montecargas."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Hotel'){
					$vhotel = $vhotel + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zHotel = ".$vhotel."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Maniobras'){
					$vmaniobras = $vmaniobras + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zManiobras = ".$vmaniobras."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Paqueteria'){
					$vpaqueteria = $vpaqueteria + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zPaqueteria = ".$vpaqueteria."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Pasajes Taxis'){
					$vpasajes_taxis = $vpasajes_taxis + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zPasajesTaxis = ".$vpasajes_taxis."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Pension'){
					$vpension = $vpension + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zPension = ".$vpension."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Reparaciones Talachas'){
					$vreparaciones_talachas = $vreparaciones_talachas + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zReparacionesTalachas = ".$vreparaciones_talachas."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Taller'){
					$vtaller = $vtaller + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zTaller = ".$vtaller."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Transito'){
					$vtransito = $vtransito + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zTransito = ".$vtransito."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Varios'){
					$vvarios = $vvarios + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zVarios2 = ".$vvarios."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Viaticos'){
					$vviaticos = $vviaticos + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zViaticos = ".$vviaticos."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Descuento Carga'){
					$vdescuentocarga = $vdescuentocarga + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zDescuentoCarga = ".$vdescuentocarga."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Descuento Descarga'){
					$vdescuentodescarga = $vdescuentodescarga + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zDescuentoDescarga = ".$vdescuentodescarga."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} elseif($concepto == 'Cargo Por Mercancia'){
					$vcargomercancia = $vcargomercancia + $importe;
					$resSQL5="UPDATE ".$prefijobd."liquidaciones SET 
						zCargoPorMercancia = ".$vcargomercancia."
						WHERE ID = ".$id_liq;	
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				} else {
				}
				
				
			}
			
			
		}
		
		echo "<br>";
		echo "<h3>Gastos de la Liquidacion ".$xfolio_liq." se actualizaron con Exito</h3>";
		
		
	}








?>
