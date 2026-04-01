<?php 



//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["base"];
$sucursal = $_POST["sucursal"];//trae sucursal

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
    

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Listado Tracking por Remision</title>
<link href="sierraestilo2.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body class="twoColElsLtHdr">

<div id="container">
  <div id="header">
    <h1>Ultimo Tracking por Viaje
      <!-- end #header -->
    </h1>
  </div>
  <div id="sidebar1">
    <h3>Listado</h3>
    <p>&nbsp;</p>
	<form method="post" action="2_remisiones_tracking_notificacion_mail.php" target="_blank" enctype="multipart/form-data">
		<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
		<input type="date" class="form-control inputdefault" name="fechai" id="fecha" hidden value="<?php echo $fecha_inicio; ?>">
		<input type="date" class="form-control inputdefault" name="fechaf" id="fecha" hidden value="<?php echo $fecha_fin; ?>">
    <p><input type="submit" value="Enviar al Email" name="send_notificacion" class="btn btn-info"></p>
	</form>
    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">
	<div class="table-responsive">
    <table border="1">
  <tr>
    <th class="input">Viaje</th>
	<th class="input">Unidad</th>
	<th class="input">CR</th>
	<th class="input">Operador</th>
	<th class="input">Ruta</th>
	<th class="input">Kms Ruta</th>
	<th class="input">Cliente</th>
	<th class="input">Fecha y Hora de Salida</th>
	<th class="input">Fecha y Hora de Llegada</th>
	<th class="input">Estatus</th>
	<th class="input">Tiempo en Espera de Carga/Viaje</th>
	<th class="input">Fecha de Tracking</th>
	<th class="input">Documentador</th>
	<th class="input">Cita</th>
	<th class="input">Especificaciones de Viaje del Cliente</th>
	<th class="input">Comentarios TR</th>
    <th class="input">Temperatura CR</th>
	<th class="input">Comentarios CR</th>
	<th class="input">Ubicación de Unidad</th>
	<th class="input">Kms Restantes</th>
	<th class="input">Tiempo Estimado para llegar a Destino</th>
	<th class="input">Estatus de Llegada</th>
	<th class="input">Diesel TR</th>
	<th class="input">Diesel CR</th>
  </tr>
  <?php
	//Buscar ID's de Remisiones
	$resSQL="SELECT DISTINCT(FolioEstatus2_RID) FROM ".$prefijobd."remisionesestatus2 WHERE Date(Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' AND FolioEstatus2_RID <> '' ORDER BY ID";
	//echo $resSQL;
	$runSQL=mysql_query($resSQL);
	while ($rowSQL=mysql_fetch_array($runSQL)){
		//Obtener_variables
		$id_remision = $rowSQL['FolioEstatus2_RID'];
		
		//Buscar datos de la remision
		$resSQL1="SELECT R.ID, R.XFolio, R.Unidad_RID, R.Ruta_RID, R.URemolqueA_RID, R.Operador_RID, R.CargoACliente_RID, R.Instrucciones, R.CitaCarga, R.Creado, U.Unidad, R.FechaHoraSalida, R.FechaHoraLlegada, R.TiempoEsperaCargaViaje FROM ".$prefijobd."remisiones R, ".$prefijobd."unidades U WHERE R.Unidad_RID = U.ID AND R.ID = ".$id_remision." U.Sucursal_RID = ".$sucursal." ORDER BY U.Unidad";
		//echo $resSQL1."<br>";
		$runSQL1=mysql_query($resSQL1);
		while ($rowSQL1=mysql_fetch_array($runSQL1)){
			//Obtener_variables
			//$id_remision = $rowSQL1['ID'];
			$xfolio = $rowSQL1['XFolio'];
			$unidad = $rowSQL1['Unidad_RID'];
			$ruta_id = $rowSQL1['Ruta_RID'];
			$remolque_id = $rowSQL1['URemolqueA_RID'];
			$operador_id = $rowSQL1['Operador_RID'];
			$cliente_id = $rowSQL1['CargoACliente_RID'];
			$instrucciones = $rowSQL1['Instrucciones'];
			$cita_fecha_temp = $rowSQL1['CitaCarga'];
			$cita_fecha = date("d-m-Y H:i:s", strtotime($cita_fecha_temp));
			$fecha_temp2 = $rowSQL1['Creado'];
			$fecha2 = date("d-m-Y H:i:s", strtotime($fecha_temp2));
			$fecha_hora_salida_temp = $rowSQL1['FechaHoraSalida'];
			$fecha_hora_salida = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
			$fecha_hora_llegada_temp = $rowSQL1['FechaHoraLlegada'];
			$fecha_hora_llegada = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
			$tiempo_espera_carga_viaje = $rowSQL1['TiempoEsperaCargaViaje'];
			
			if($fecha_hora_salida_temp < '1990-01-01 00:00:00'){
				$fecha_hora_salida ='';
			}
			if($fecha_hora_llegada_temp < '1990-01-01 00:00:00'){
				$fecha_hora_llegada ='';
			}
			if($cita_fecha_temp < '1990-01-01 00:00:00'){
				$cita_fecha ='';
			}
			
			if (isset($unidad)){
				
			} else {
				$unidad = 0;
			}
			
			if (isset($remolque_id)){
				
			} else {
				$remolque_id = 0;
			}
			
			if (isset($operador_id)){
				
			} else {
				$operador_id = 0;
			}
			
			if (isset($cliente_id)){
				
			} else {
				$cliente_id = 0;
			}
			
			$resSQL6="SELECT Unidad FROM ".$prefijobd."unidades WHERE ID = ".$remolque_id." ";
			//echo $resSQL2;
			$runSQL6=mysql_query($resSQL6);
			$rowSQL6=mysql_fetch_array($runSQL6);
			$nom_remolque = $rowSQL6['Unidad'];
			
			$resSQL7="SELECT Operador FROM ".$prefijobd."operadores WHERE ID = ".$operador_id." ";
			//echo $resSQL2;
			$runSQL7=mysql_query($resSQL7);
			$rowSQL7=mysql_fetch_array($runSQL7);
			$nom_operador = $rowSQL7['Operador'];
			
			$resSQL8="SELECT RazonSocial FROM ".$prefijobd."clientes WHERE ID = ".$cliente_id." ";
			//echo $resSQL8;
			$runSQL8=mysql_query($resSQL8);
			$rowSQL8=mysql_fetch_array($runSQL8);
			$nom_cliente = $rowSQL8['RazonSocial'];
				
			
			$resSQL2="SELECT Unidad FROM ".$prefijobd."unidades WHERE ID = ".$unidad." ";
			//echo $resSQL2;
			$runSQL2=mysql_query($resSQL2);
			$rowSQL2=mysql_fetch_array($runSQL2);
			$nom_unidad = $rowSQL2['Unidad'];
			
			if($ruta_id > 0){
				$resSQL5="SELECT * FROM ".$prefijobd."rutas WHERE ID = ".$ruta_id." ";
				$runSQL5=mysql_query($resSQL5);
				$rowSQL5=mysql_fetch_array($runSQL5);
				$ruta = $rowSQL5['Ruta'];
				$kms_ruta_temp = $rowSQL5['Kms'];
				$kms_ruta = number_format($kms_ruta_temp,2); 
			}else{
				$ruta = '';
				$kms_ruta = 0;
			}
			
			
			$resSQL3="SELECT MAX(ID) as max_id FROM ".$prefijobd."remisionesestatus2 WHERE FolioEstatus2_RID = ".$id_remision." ";
			//echo $resSQL3;
			$runSQL3=mysql_query($resSQL3);
			$rowSQL3=mysql_fetch_array($runSQL3);
			$ultimo_id_tracking = $rowSQL3['max_id'];
			
			$resSQL4="SELECT * FROM ".$prefijobd."remisionesestatus2 WHERE ID = ".$ultimo_id_tracking." ";
			//echo $resSQL4;
			$runSQL4=mysql_query($resSQL4);
			$rowSQL4=mysql_fetch_array($runSQL4);
			$estatus = $rowSQL4['Estatus'];
			$fecha_temp = $rowSQL4['Fecha'];
			$fecha00 = date("Y-m-d H:i:s", strtotime($fecha_temp));
			$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
			if($fecha < '01-01-1990 00:00:00') {
				$fecha = '';
			} 
			$documentador = $rowSQL4['Documentador'];
			$comentario = $rowSQL4['Comentarios'];
			//$fecha_hora_salida_temp = $rowSQL4['FechaHoraSalida'];
			//$fecha_hora_salida = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
			//$fecha_hora_llegada_temp = $rowSQL4['FechaHoraLlegada'];
			//$fecha_hora_llegada = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
			//$tiempo_espera_carga_viaje = $rowSQL4['TiempoEsperaCargaViaje'];
			$estatus_llegada = $rowSQL4['EstatusLlegada'];
			$temperatura_cr = $rowSQL4['TemperaturaCR'];
			$comentarios_cr = $rowSQL4['ComentariosCR'];
			$ubicacion_unidad = $rowSQL4['UbicacionUnidad'];
			$km_restantes_temp = $rowSQL4['KmRestantes'];
			$km_restantes = number_format($km_restantes_temp,2);
			$tiempo_estimado_llegada_destino = $rowSQL4['TiempoEstimadoLlegadaDestino'];
			$diesel_tr_temp = $rowSQL4['DieselTR'];
			$diesel_tr = number_format($diesel_tr_temp,2);
			$diesel_cr_temp = $rowSQL4['DieselCR'];
			$diesel_cr = number_format($diesel_cr_temp,2);
			
		
		} 
		
		//echo $fecha00;
		//echo "<br>";
		$fi = $_POST["fechai"];
		$ff = $_POST["fechaf"];
		$fi2 = date("Y-m-d H:i:s", strtotime($fi));
		$ff2 = date("Y-m-d H:i:s", strtotime($ff));
		$nuevafecha_fin = strtotime ('+23 hour +59 minute + 59 second', strtotime($ff2));
		

		$nuevafecha_fin = date ('Y-m-d H:i:s' , $nuevafecha_fin);
				
		/*echo $fi2;
		echo "<br>";
		echo $nuevafecha_fin;
		echo "<br>";*/
		
		
		//Validar que la Fecha este en el rango especificado
		if(($fecha00 >= $fi2) AND ($fecha00 <= $nuevafecha_fin)){
		
  ?>
  <tr>

    <td width="60" class="table"><?php echo $xfolio; ?></td>
    <td width="80" class="table"><?php echo $nom_unidad; ?></td>
	<td width="80" class="table"><?php echo $nom_remolque; ?></td>
	<td width="80" class="table"><?php echo $nom_operador; ?></td>
    <td width="150" class="table"><?php echo $ruta; ?></td>
	<td width="60" class="table"><?php echo $kms_ruta; ?></td>
	<td width="80" class="table"><?php echo $nom_cliente; ?></td>
	<td width="60" class="table"><?php echo $fecha_hora_salida; ?></td>
	<td width="60" class="table"><?php echo $fecha_hora_llegada; ?></td>
	<td width="80" class="table"><?php echo $estatus; ?></td>
	<td width="80" class="table"><?php echo $tiempo_espera_carga_viaje; ?></td>
	<td width="60" class="table"><?php echo $fecha; ?></td>
	<td width="60" class="table"><?php echo $documentador; ?></td>
	<td width="60" class="table"><?php echo $cita_fecha; ?></td>
	<td width="150" class="table"><?php echo $instrucciones; ?></td>
	<td width="150" class="table"><?php echo $comentario; ?></td>
	<td width="60" class="table"><?php echo $temperatura_cr; ?></td>
	<td width="150" class="table"><?php echo $comentarios_cr; ?></td>
	<td width="80" class="table"><?php echo $ubicacion_unidad; ?></td>
	<td width="80" class="table"><?php echo $km_restantes; ?></td>
	<td width="80" class="table"><?php echo $tiempo_estimado_llegada_destino; ?></td>
	<td width="60" class="table"><?php echo $estatus_llegada; ?></td>
	<td width="60" class="table"><?php echo $diesel_tr; ?></td>
	<td width="60" class="table"><?php echo $diesel_cr; ?></td>

  </tr>
 <?php 
	 } else {
	 }
  }  //Fin Buscar ID's Remisiones?>
</table>
</div>
<?php



//	for($cont=1; $cont<=$paginas; $cont++)
//	{

//		echo "<a href='timbradoslist.php?numP=".$cont."' >$cont</a> ";	
//	}

?>
</form>
  <!-- end #mainContent --></div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
    <p>TractoSoft</p>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
<?php
mysql_free_result($runSQL);
mysql_close($cnx_cfdi);
?>