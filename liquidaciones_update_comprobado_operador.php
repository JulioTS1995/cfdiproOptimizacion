<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_liquidacion = $_GET["id"];
	
	//Conceptos
	$zPeaje = 0;
	$zRefacciones = 0;
	$zReparaciones = 0;
	$zAdministracion = 0;
	$zAnticipoViaje = 0;
	$zAlimentos = 0;
	$zAuxiliarComidas = 0;
	$zAuxiliarManiobras = 0;
	$zBascula = 0;
	$zCasetas = 0;
	$zCasetasVIAPASS = 0;
	$zCombustibleDiesel = 0;
	$zCombustibleEPOLServifacil = 0;
	$zCombustibleGasolina = 0;
	$zCombustibleTicketCard = 0;
	$zComisionViaje = 0;
	$zDeducible = 0;
	$zEspecificar = 0;
	$zEstacionamiento = 0;
	$zFerries = 0;
	$zFitosanitarias = 0;
	$zFondeo = 0;
	$zGastos = 0;
	$zGratificaciones = 0;
	$zGruasMontecargas = 0;
	$zHotel = 0;
	$zManiobras = 0;
	$zPaqueteria = 0;
	$zPasajesTaxis = 0;
	$zPension = 0;
	$zReparacionesTalachas = 0;
	$zTaller = 0;
	$zTransito = 0;
	$zVarios2 = 0;
	$zViaticos = 0;
	$zVarios = 0;
	
	
	
	
	//Buscar Remisiones anexadas a la Liquidacion
	$sql_01="SELECT * FROM ".$prefijodb."liquidacionessub WHERE FolioSub_RID = ".$id_liquidacion;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp1=mysql_fetch_array($res_01)){
		$id_remision = $fila_exp1['RemisionLiq_RID'];
		
		//Buscar Comprobaion Operador de la remision
		
		$sql_02="SELECT * FROM ".$prefijodb."remisionescomprobacionoperador WHERE FolioSub_RID = ".$id_remision;
		//echo $sql_02;
		$res_02=mysql_query($sql_02);
		while ($fila_exp2=mysql_fetch_array($res_02)){
			//Obtener todos los datos de comprobacion operador
			$id_concepto_liquidacion = $fila_exp2['Concepto_RID'];
			$v_importe = $fila_exp2['Importe'];
			
			$sql_03="SELECT * FROM ".$prefijodb."conceptosliquidaciones WHERE ID = ".$id_concepto_liquidacion;
			$res_03=mysql_query($sql_03);
			while ($fila_exp3=mysql_fetch_array($res_03)){
				//Obtener todos los datos de conceptos liquidaciones
				$nom_concepto = $fila_exp3['Concepto'];
				$nom_basedatos = $fila_exp3['NombreBaseDatos'];
			}
			
			//Acumular importe en cada concepto
			switch ($nom_basedatos) {
				case 'zPeaje':
					$zPeaje = $zPeaje + $v_importe;
					break;
				case 'zRefacciones':
					$zRefacciones = $zRefacciones + $v_importe;
					break;
				case 'zReparaciones':
					$zReparaciones = $zReparaciones + $v_importe;
					break;
				case 'zVarios':
					$zVarios = $zVarios + $v_importe;
					break;
				case 'zAdministracion':
					$zAdministracion = $zAdministracion + $v_importe;
					break;
				case 'zAnticipoViaje':
					$zAnticipoViaje = $zAnticipoViaje + $v_importe;
					break;
				case 'zAlimentos':
					$zAlimentos = $zAlimentos + $v_importe;
					break;
				case 'zAuxiliarComidas':
					$zAuxiliarComidas = $zAuxiliarComidas + $v_importe;
					break;
				case 'zAuxiliarManiobras':
					$zAuxiliarManiobras = $zAuxiliarManiobras + $v_importe;
					break;
				case 'zBascula':
					$zBascula = $zBascula + $v_importe;
					break;
				case 'zCasetas':
					$zCasetas = $zCasetas + $v_importe;
					break;
				case 'zCasetasVIAPASS':
					$zCasetasVIAPASS = $zCasetasVIAPASS + $v_importe;
					break;
				case 'zCombustibleDiesel':
					$zCombustibleDiesel = $zCombustibleDiesel + $v_importe;
					break;
				case 'zCombustibleEPOLServifacil':
					$zCombustibleEPOLServifacil = $zCombustibleEPOLServifacil + $v_importe;
					break;
				case 'zCombustibleGasolina':
					$zCombustibleGasolina = $zCombustibleGasolina + $v_importe;
					break;
				case 'zCombustibleTicketCard':
					$zCombustibleTicketCard = $zCombustibleTicketCard + $v_importe;
					break;
				case 'zComisionViaje':
					$zComisionViaje = $zComisionViaje + $v_importe;
					break;
				case 'zDeducible':
					$zDeducible = $zDeducible + $v_importe;
					break;
				case 'zEspecificar':
					$zEspecificar = $zEspecificar + $v_importe;
					break;
				case 'zEstacionamiento':
					$zEstacionamiento = $zEstacionamiento + $v_importe;
					break;
				case 'zFerries':
					$zFerries = $zFerries + $v_importe;
					break;
				case 'zFitosanitarias':
					$zFitosanitarias = $zFitosanitarias + $v_importe;
					break;
				case 'zFondeo':
					$zFondeo = $zFondeo + $v_importe;
					break;
				case 'zGastos':
					$zGastos = $zGastos + $v_importe;
					break;
				case 'zGratificaciones':
					$zGratificaciones = $zGratificaciones + $v_importe;
					break;
				case 'zGruasMontecargas':
					$zGruasMontecargas = $zGruasMontecargas + $v_importe;
					break;
				case 'zHotel':
					$zHotel = $zHotel + $v_importe;
					break;
				case 'zManiobras':
					$zManiobras = $zManiobras + $v_importe;
					break;
				case 'zPaqueteria':
					$zPaqueteria = $zPaqueteria + $v_importe;
					break;
				case 'zPasajesTaxis':
					$zPasajesTaxis = $zPasajesTaxis + $v_importe;
					break;
				case 'zPension':
					$zPension = $zPension + $v_importe;
					break;
				case 'zReparacionesTalachas':
					$zReparacionesTalachas = $zReparacionesTalachas + $v_importe;
					break;
				case 'zTaller':
					$zTaller = $zTaller + $v_importe;
					break;
				case 'zTransito':
					$zTransito = $zTransito + $v_importe;
					break;
				case 'zVarios2':
					$zVarios2 = $zVarios2 + $v_importe;
					break;
				case 'zViaticos':
					$zViaticos = $zViaticos + $v_importe;
					break;
				
			
			  /*default:
				code to be executed if n is different from all labels;*/
			}

			$v_importe = 0;
		} //Fin busqueda de remisionescomprobacionoperador
		
		
	} //Fin Busqueda de Remisiones anexadas a la Liquidacion seleccionada
	
	
	//Actualizar el importe de todos los conceptos en Liquidaciones
	mysql_query("UPDATE ".$prefijodb."liquidaciones 
	SET 
		zPeaje = '$zPeaje',
		zRefacciones = '$zRefacciones',
		zReparaciones = '$zReparaciones', 
		zVarios = '$zVarios',
		zAdministracion = '$zAdministracion',
		zAnticipoViaje = '$zAnticipoViaje',
		zAlimentos = '$zAlimentos',
		zAuxiliarComidas = '$zAuxiliarComidas',
		zAuxiliarManiobras = '$zAuxiliarManiobras',
		zBascula = '$zBascula',
		zCasetas = '$zCasetas',
		zCasetasVIAPASS = '$zCasetasVIAPASS',
		zCombustibleDiesel = '$zCombustibleDiesel',
		zCombustibleEPOLServifacil = '$zCombustibleEPOLServifacil',
		zCombustibleGasolina = '$zCombustibleGasolina',
		zCombustibleTicketCard = '$zCombustibleTicketCard',
		zComisionViaje = '$zComisionViaje',
		zDeducible = '$zDeducible', 
		zEspecificar = '$zEspecificar',
		zEstacionamiento = '$zEstacionamiento',
		zFerries = '$zFerries',
		zFitosanitarias = '$zFitosanitarias',
		zFondeo = '$zFondeo',
		zGastos = '$zGastos',
		zGratificaciones = '$zGratificaciones',
		zGruasMontecargas = '$zGruasMontecargas',
		zHotel = '$zHotel',
		zManiobras = '$zManiobras',
		zPaqueteria = '$zPaqueteria',
		zPasajesTaxis = '$zPasajesTaxis',
		zPension = '$zPension',
		zReparacionesTalachas = '$zReparacionesTalachas',
		zTaller = '$zTaller',
		zTransito = '$zTransito',
		zVarios2 = '$zVarios2',
		zViaticos = '$zViaticos'
	WHERE 
		ID = $id_liquidacion");
	
	
	
	echo "<h2>Se realizo la actualizacion de los campos Comprobado Operador con Exito.</h2>";

	//http://localhost/cfdipro/liquidaciones_update_comprobado_operador.php?prefijodb=prueba_&id=1845853

?>