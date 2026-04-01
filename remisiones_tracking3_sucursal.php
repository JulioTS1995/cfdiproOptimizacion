<?php 
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);


//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$id_unidad = $_POST["unidad"];
$id_operador = $_POST["operador"];

if ($id_unidad == 0) {
	$sql_unidad = "";
}else{
	$sql_unidad = " AND R.Unidad_RID = ".$id_unidad;
}

if ($id_operador == 0) {
	$sql_operador = "";
}else{
	$sql_operador = " AND R.Operador_RID = ".$id_operador;
}

//Formato a Fechas

$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t = date("d-m-Y", strtotime($fecha_fin));

    
    

?>

<!DOCTYPE html>
<html>
<head>

<!-- datatable -->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap.min.css">

<!-- datatable -->

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

</head>


<body>  
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Tracking 3 por Viaje</b></h2>
				<h4>Periodo: <?php echo $fecha_inicio_t." - ".$fecha_fin_t; ?></h4>
			</div>
		</div>
		<br>
		<!--<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<form method="post" action="remisiones_tracking3_notificacion_mail.php" target="_blank" enctype="multipart/form-data">
						<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
						<input type="hidden" class="form-control inputdefault" name="fechai" id="fecha" hidden value="<?php echo $fecha_inicio; ?>">
						<input type="hidden" class="form-control inputdefault" name="fechaf" id="fecha" hidden value="<?php echo $fecha_fin; ?>">
					<p><input type="submit" value="Enviar al Email" name="send_notificacion" class="btn btn-info"></p>
					</form>
				</div>
			</div>
		</div>-->
		
		<div class="row">
			<form method="post" action="remisiones_tracking3_excel_pdf_sucursal.php" target="_blank" enctype="multipart/form-data">
				<div class="col-md-6 align-self-center text-center">
					<input type="hidden" name="prefijodb" id="prefijodb" value='<?php echo $prefijobd; ?>'>
							<input type="hidden" class="form-control inputdefault" name="fechai" id="fecha" hidden value="<?php echo $fecha_inicio; ?>">
							<input type="hidden" class="form-control inputdefault" name="fechaf" id="fecha" hidden value="<?php echo $fecha_fin; ?>">
							<input type="hidden" class="form-control inputdefault" name="unidad" id="unidad" hidden value="<?php echo $id_unidad; ?>">
							<input type="hidden" class="form-control inputdefault" name="operador" id="operador" hidden value="<?php echo $id_operador; ?>">
					<div class="form-group">
						<p><input type="submit" value="PDF" name="button" id="button" class="btn btn-danger btn-lg"></p>
					</div>
				</div>
				<div class="col-md-6 align-self-center text-center">
					<div class="form-group">
						<p><input type="submit" value="Excel" name="button" id="button" class="btn btn-success btn-lg"></p>
					</div>
				</div>
			</form>
		</div>
		
		<div class="row">
            <div class="col-lg-12" style="width:1200; height:500px; overflow:scroll;">
                <table class="table table-hover table-responsive table-condensed" id="table1">
				<!-- Inicio Tabla --------------------------------------------------------------------------------------------------------->
					<tr>
						<th>Viaje</th>
						<th>Unidad</th>
						<th>CR</th>
						<th>Operador</th>
						<th>Ruta</th>
						<th>Kms Ruta</th>
						<th>Cliente</th>
						<th>Fecha y Hora de Salida</th>
						<th>Fecha y Hora de Llegada</th>
						<th>Estatus</th>
						<th>Tiempo en Espera de Carga/Viaje</th>
						<th>Fecha de Tracking</th>
						<th>Documentador</th>
						<th>Cita</th>
						<th>Especificaciones de Viaje del Cliente</th>
						<th>Comentarios TR</th>
						<th>Temperatura CR</th>
						<th>Comentarios CR</th>
						<th>Ubicación de Unidad</th>
						<th>Kms Restantes</th>
						<th>Tiempo Estimado para llegar a Destino</th>
						<th>Estatus de Llegada</th>
						<th>Diesel TR</th>
						<th>Diesel CR</th>
					  </tr>
					  <?php
						//Buscar todos los registros de remisionesestatus2
						$resSQL="SELECT * FROM ".$prefijobd."remisiones R, ".$prefijobd."remisionesestatus2 RE WHERE Date(RE.Fecha) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND R.Oficina_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal.") AND FolioEstatus2_RID <> '' AND RE.FolioEstatus2_RID = R.ID".$sql_unidad.$sql_operador." ORDER BY R.XFolio";
						//echo $resSQL;
						$runSQL=mysql_query($resSQL);
						$total_registros_t = mysql_num_rows($runSQL);
						$total_registros = number_format($total_registros_t,0);
						while ($rowSQL1=mysql_fetch_array($runSQL)){
								//Obtener_variables
								$xfolio = $rowSQL1['XFolio'];
								$unidad = $rowSQL1['Unidad_RID'];
								$ruta_id = $rowSQL1['Ruta_RID'];
								$remolque_id = $rowSQL1['uRemolqueA_RID'];
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
								
								$estatus = $rowSQL1['Estatus'];
								$fecha_temp = $rowSQL1['Fecha'];
								$fecha00 = date("Y-m-d H:i:s", strtotime($fecha_temp));
								$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
								if($fecha < '01-01-1990 00:00:00') {
									$fecha = '';
								} 
								$documentador = $rowSQL1['Documentador'];
								$comentario = $rowSQL1['Comentarios'];
								//$fecha_hora_salida_temp = $rowSQL1['FechaHoraSalida'];
								//$fecha_hora_salida = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
								//$fecha_hora_llegada_temp = $rowSQL1['FechaHoraLlegada'];
								//$fecha_hora_llegada = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
								//$tiempo_espera_carga_viaje = $rowSQL1['TiempoEsperaCargaViaje'];
								$estatus_llegada = $rowSQL1['EstatusLlegada'];
								$temperatura_cr = $rowSQL1['TemperaturaCR'];
								$comentarios_cr = $rowSQL1['ComentariosCR'];
								$ubicacion_unidad = $rowSQL1['UbicacionUnidad'];
								$km_restantes_temp = $rowSQL1['KmRestantes'];
								$km_restantes = number_format($km_restantes_temp,2);
								$tiempo_estimado_llegada_destino = $rowSQL1['TiempoEstimadoLlegadaDestino'];
								$diesel_tr_temp = $rowSQL1['DieselTR'];
								$diesel_tr = number_format($diesel_tr_temp,2);
								$diesel_cr_temp = $rowSQL1['DieselCR'];
								$diesel_cr = number_format($diesel_cr_temp,2);
								
	
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
							//if(($fecha00 >= $fi2) AND ($fecha00 <= $nuevafecha_fin)){
							
					  ?>
					  <tr>

						<td><?php echo $xfolio; ?></td>
						<td><?php echo $nom_unidad; ?></td>
						<td><?php echo $nom_remolque; ?></td>
						<td><?php echo $nom_operador; ?></td>
						<td><?php echo $ruta; ?></td>
						<td><?php echo $kms_ruta; ?></td>
						<td><?php echo $nom_cliente; ?></td>
						<td><?php echo $fecha_hora_salida; ?></td>
						<td><?php echo $fecha_hora_llegada; ?></td>
						<td><?php echo $estatus; ?></td>
						<td><?php echo $tiempo_espera_carga_viaje; ?></td>
						<td><?php echo $fecha; ?></td>
						<td><?php echo $documentador; ?></td>
						<td><?php echo $cita_fecha; ?></td>
						<td><?php echo $instrucciones; ?></td>
						<td><?php echo $comentario; ?></td>
						<td><?php echo $temperatura_cr; ?></td>
						<td><?php echo $comentarios_cr; ?></td>
						<td><?php echo $ubicacion_unidad; ?></td>
						<td><?php echo $km_restantes; ?></td>
						<td><?php echo $tiempo_estimado_llegada_destino; ?></td>
						<td><?php echo $estatus_llegada; ?></td>
						<td><?php echo $diesel_tr; ?></td>
						<td><?php echo $diesel_cr; ?></td>

					  </tr>
					 <?php 
						 //} else {
						 //}
					  }  //Fin Buscar en RemisionesEstatus2?>
				<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
				</table>
			</div>
			 <div class="col-lg-12">
				<h3>Total de Registros: <?php echo $total_registros; ?></h3>
			 </div>
		</div>
		
	</div>
	
</body>



<script>
  $(document).ready(function() {
    $('#table1').DataTable();
} );
</script>

</body>
</html>
<?php
mysql_free_result($runSQL);
mysql_close($cnx_cfdi);
?>