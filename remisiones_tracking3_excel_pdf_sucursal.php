<?php  

//Recibir variables
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$id_unidad = $_POST["unidad"];
$id_operador = $_POST["operador"];
$boton = $_POST["button"];

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

if($boton == 'PDF'){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar PDF

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");




$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><h2><b>Tracking 3 por Remision</b></h2><h4>Periodo: '.$fecha_inicio_t.' - '.$fecha_fin_t.'</h4></p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      ';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">

              <div><br></div>


              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 20px;">Remision</th>
                      <th align="center" style="font-size: 20px;">Unidad</th>
                      <th align="center" style="font-size: 20px;">CR</th>
                      <th align="center" style="font-size: 20px;">Operador</th>
                      <th align="center" style="font-size: 20px;">Ruta</th>
                      <th align="center" style="font-size: 20x;">Kms Ruta</th>
                      <th align="center" style="font-size: 20px;">Cliente</th>
					  <th align="center" style="font-size: 20px;">Fecha y Hora de Salida</th>
                      <th align="center" style="font-size: 20px;">Fecha y Hora de Llegada</th>
					  <th align="center" style="font-size: 20px;">Estatus</th>
					  <th align="center" style="font-size: 20x;">Tiempo en Espera de Carga/Viaje</th>
					  <th align="center" style="font-size: 20x;">Fecha de Tracking</th>
					  <th align="center" style="font-size: 20px;">Documentador</th>
					  <th align="center" style="font-size: 20x;">Cita</th>
					  <th align="center" style="font-size: 20x;">Especificaciones de Viaje del Cliente</th>
					  <th align="center" style="font-size: 20x;">Comentarios TR</th>
					  <th align="center" style="font-size: 20x;">Temperatura CR</th>
					  <th align="center" style="font-size: 20px;">Comentarios CR</th>
					  <th align="center" style="font-size: 20px;">Ubicación de Unidad</th>
					  <th align="center" style="font-size: 20px;">Kms Restantes</th>
					  <th align="center" style="font-size: 20px;">Tiempo Estimado para llegar a Destino</th>
					  <th align="center" style="font-size: 20px;">Estatus de Llegada</th>
					  <th align="center" style="font-size: 20px;">Diesel TR</th>
					  <th align="center" style="font-size: 20px;">Diesel CR</th>
                    </tr>
                  </thead>
                  <tbody>';

				
                
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
						
						
				
                $html.='
                    <tr>
                      <td align="center">'.$xfolio.'</td>
                      <td align="left">'.$nom_unidad.'</td>
                      <td align="left">'.$nom_remolque.'</td>
                      <td align="left">'.$nom_operador.'</td>
                      <td align="left" >'.$ruta.'</td>
                      <td align="left" >'.$kms_ruta.'</td>
                      <td align="left" >'.$nom_cliente.'</td>
					  <td align="center" >'.$fecha_hora_salida.'</td>
					  <td align="center" >'.$fecha_hora_llegada.'</td>
					  <td align="left" >'.$estatus.'</td>
					  <td align="left" >'.$tiempo_espera_carga_viaje.'</td>
					  <td align="center" >'.$fecha.'</td>
					  <td align="left" >'.$documentador.'</td>
					  <td align="center" >'.$cita_fecha.'</td>
					  <td align="left" >'.$instrucciones.'</td>
					  <td align="left" >'.$comentario.'</td>
					  <td align="left" >'.$temperatura_cr.'</td>
					  <td align="left" >'.$comentarios_cr.'</td>
					  <td align="left" >'.$ubicacion_unidad.'</td>
					  <td align="left" >'.$km_restantes.'</td>
					  <td align="left" >'.$tiempo_estimado_llegada_destino.'</td>
					  <td align="left" >'.$estatus_llegada.'</td>
					  <td align="left" >'.$diesel_tr.'</td>
					  <td align="left" >'.$diesel_cr.'</td>

                    </tr>

                    ';
					
					} // FIN del WHILE 
					
					
                  

                  



              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>

              ';



           

          
$html.='</header>';


$mpdf = new mPDF('c', 'A4-L');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('tracking3_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar PDF
} elseif($boton == 'Excel'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="tracking3_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");




?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="24">
								<h2><b>Tracking 3 por Remision</b></h2>
							</th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="24">
								<h4>Periodo: <?php echo $fecha_inicio_t." - ".$fecha_fin_t; ?></h4>
							</th>
						</tr>
						<tr>
							<th class="input">Remision</th>
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
					</thead>
					<tbody>
					<?php
					
					//Buscar todos los registros de remisionesestatus2
						$resSQL="SELECT * FROM ".$prefijobd."remisiones R, ".$prefijobd."remisionesestatus2 RE WHERE Date(RE.Fecha) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND FolioEstatus2_RID <> '' AND RE.FolioEstatus2_RID = R.ID".$sql_unidad.$sql_operador." ORDER BY R.XFolio";
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
						<td align="center"><?php echo $xfolio; ?></td>
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
					
					
					
						}// FIN del WHILE $resSQL01
					?>
					</tbody>
				</table>

<?php


/////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar Excel
}




?>