<?php
header("Content-type: application/vnd.ms-excel");
$nombre="remisiones_circuito_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

$prefijobd = $_GET["prefijodb"];

$fecha_inicio = $_GET["finicio"];
$fecha_fin = $_GET["ffin"];
$circuitos = $_GET['circuito'];
$circuitos = mysqli_real_escape_string($cnx_cfdi2, $circuitos);

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

$sql_circuito = !empty($circuitos) ? " AND Circuito2_RID IN (" . $circuitos . ")" : "";

$v_operador = $_GET['operador'];


if ($v_operador == 0) {
    $sql_operador = "";
} else {
    $sql_operador = " AND Operador_RID = ".$v_operador."";
}


$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$fecha_actual = $dia_logs."-".$mes_logs."-".$anio_logs;

////////////////////////////////////////////////////////Reporte en Excel
?>


<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">


                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="22" style="font-size: 18px;">Remisiones Circuito</th>
                    </tr>
					<tr>
                      <th align="center" colspan="22" style="font-size: 18px;">Periodo: <?php echo $fecha_inicio_f." / ".$fecha_fin_f; ?></th>
                    </tr>
					<tr>
                      <th align="left" colspan="22" style="font-size: 12px;"><?php echo $fecha_actual; ?></th>
                    </tr>
                    <tr>
						<th align="center" style="font-size: 12px;">Transporte</th>
                        <th align="center" style="font-size: 12px;">Circuito</th>
						<th align="center" style="font-size: 12px;">Fecha Embarque</th>
						<th align="center" style="font-size: 12px;">Fecha Destino</th>
						<th align="center" style="font-size: 12px;">Folio Embarque</th>
						<th align="center" style="font-size: 12px;">Porte</th>
                        <th align="center" style="font-size: 12px;">Factura</th>
						<th align="center" style="font-size: 12px;">Transferencia</th>
						<th align="center" style="font-size: 12px;">Tractor</th>
						<th align="center" style="font-size: 12px;">Trailer</th>
						<th align="center" style="font-size: 12px;">Origen</th>
						<th align="center" style="font-size: 12px;">Destino</th>
						<th align="center" style="font-size: 12px;">Clave</th>
						<th align="center" style="font-size: 12px;">Viaje</th>
						<th align="center" style="font-size: 12px;">Renta</th>
						<th align="center" style="font-size: 12px;">Maniobras</th>
						<th align="center" style="font-size: 12px;">Casetas</th>
						<th align="center" style="font-size: 12px;">Tarimas</th>
						<th align="center" style="font-size: 12px;">Cajas</th>
						<th align="center" style="font-size: 12px;">Peso</th>
						<th align="center" style="font-size: 12px;">Km</th>
						<th align="center" style="font-size: 12px;">Operador</th>
                    </tr>
                  </thead>
                  <tbody>

<?php
								$resSQL2 = "SELECT ID, RazonSocial as empresa FROM " . $prefijobd . "systemsettings LIMIT 1";
								$runSQL2 = mysqli_query($cnx_cfdi2, $resSQL2);
								while($rowSQL2 = mysqli_fetch_array($runSQL2)){
									$r_transporte = $rowSQL2['empresa'];
								}
							
                                $resSQL1 = "SELECT XFolio,Creado,Remitente,Destinatario,DestinatarioCitaCarga,DescripcionProducto,Factura,Transferencia, 
								(SELECT Nombre FROM " . $prefijobd . "circuito WHERE ID = Rem.Circuito2_RID) AS NomCircuito,Unidad_RID,uRemolqueA_RID,
								yFlete, yDescarga, yAutopistas, Tarimas, Cajas, xPesoTotal, KmsRecorridos, Operador_RID
								FROM " . $prefijobd . "remisiones Rem WHERE Date(Creado) Between '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59' ".$sql_circuito.$sql_operador." ORDER BY XFolio ASC";
								
								//echo $resSQL1;
								
								
								$runSQL1 = mysqli_query($cnx_cfdi2, $resSQL1);
								
								$total_viaje = 0;
								$total_renta = 0;
								$total_maniobras= 0;
								$total_casetas = 0;
								$total_tarimas = 0;
								$total_cajas = 0;
								$total_peso = 0;
								$total_km = 0;
								$total_rem = 0;
								
								while($rowSQL1 = mysqli_fetch_array($runSQL1))
									 {
										$r_creado_t = $rowSQL1['Creado'];
										$r_creado = date("d-m-Y", strtotime($r_creado_t));
										$r_xfolio = $rowSQL1['XFolio'];
										$r_origen = $rowSQL1['Remitente'];
										$r_destino = $rowSQL1['Destinatario'];
										$r_destinatario_cita_carga_t = $rowSQL1['DestinatarioCitaCarga'];
										$r_destinatario_cita_carga = date("d-m-Y", strtotime($r_destinatario_cita_carga_t));
										$r_ticket = $rowSQL1['DescripcionProducto'];
										$r_factura = $rowSQL1['Factura'];
										$r_transferencia = $rowSQL1['Transferencia'];
										$nombre_circuito = $rowSQL1['NomCircuito'];
										
										$r_unidad_id = $rowSQL1['Unidad_RID'];
										
										if($r_unidad_id > 0){
											$resSQL3 = "SELECT * FROM " . $prefijobd . "unidades WHERE ID=".$r_unidad_id;
											$runSQL3 = mysqli_query($cnx_cfdi2, $resSQL3);
											while($rowSQL3 = mysqli_fetch_array($runSQL3)){
												$r_unidad_placas = $rowSQL3['Placas'];
												$r_unidad_unidad = $rowSQL3['Unidad'];
											}
										} else {
											$r_unidad_placas = "";
											$r_unidad_unidad = "";
										}
										
										$r_remolquea_id = $rowSQL1['uRemolqueA_RID'];
										
										if($r_remolquea_id > 0){
											$resSQL4 = "SELECT * FROM " . $prefijobd . "unidades WHERE ID=".$r_remolquea_id;
											$runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
											while($rowSQL4 = mysqli_fetch_array($runSQL4)){
												$r_remolque_placas = $rowSQL4['Placas'];
												$r_remolque_unidad = $rowSQL4['Unidad'];
											}
										} else {
											$r_remolque_placas = "";
											$r_remolque_unidad = "";
										}
										
										
										$r_clave = "";
										$r_viaje = 1;
										
										$r_flete_t = $rowSQL1['yFlete']; 
										$r_flete = "$".number_format($r_flete_t,2);
										
										$r_maniobras_t = $rowSQL1['yDescarga']; 
										$r_maniobras = "$".number_format($r_maniobras_t,2);
										
										$r_casetas_t = $rowSQL1['yAutopistas']; 
										$r_casetas = "$".number_format($r_casetas_t,2);
										
										$r_tarimas_t = $rowSQL1['Tarimas']; 
										$r_tarimas = number_format($r_tarimas_t,0);
										
										$r_cajas_t = $rowSQL1['Cajas']; 
										$r_cajas = number_format($r_cajas_t,0);
										
										$r_peso_t = $rowSQL1['xPesoTotal']; 
										$r_peso = number_format($r_peso_t,2);
										
										$r_km_t = $rowSQL1['KmsRecorridos']; 
										$r_km = number_format($r_km_t,2);
										
										$r_operador_id = $rowSQL1['Operador_RID'];
										
										$resSQL5 = "SELECT * FROM " . $prefijobd . "operadores WHERE ID=".$r_operador_id;
										$runSQL5 = mysqli_query($cnx_cfdi2, $resSQL5);
										while($rowSQL5 = mysqli_fetch_array($runSQL5)){
											$r_operador_nombre = $rowSQL5['Operador'];
										}
										
										$total_viaje_t = $total_viaje_t + $r_viaje;
										
										$total_renta_t = $total_renta_t + $r_flete_t;
										
										$total_maniobras_t= $total_maniobras_t + $r_maniobras_t;
										
										$total_casetas_t = $total_casetas_t + $r_casetas_t;
										
										$total_tarimas_t = $total_tarimas_t + $r_tarimas_t;
										
										$total_cajas_t = $total_cajas_t + $r_cajas_t;
										
										$total_peso_t = $total_peso_t + $r_peso_t;
										
										$total_km_t = $total_km_t + $r_km_t;
										
										
										
										
?>
							<tr>
                                <td style="text-align:center;"><?php echo $r_transporte; ?></td>
								<td style="text-align:left;"><?php echo $nombre_circuito; ?></td>
								<td style="text-align:center;"><?php echo $r_creado; ?></td>
								<td style="text-align:center;"><?php echo $r_destinatario_cita_carga; ?></td>
								<td style="text-align:left;"><?php echo $r_ticket; ?></td>
								<td style="text-align:center;"><?php echo $r_xfolio; ?></td>
								<td style="text-align:left;"><?php echo $r_factura; ?></td>
								<td style="text-align:left;"><?php echo $r_transferencia; ?></td>
								<td style="text-align:center;"><?php echo $r_unidad_unidad; ?></td>
                                <td style="text-align:center;"><?php echo $r_remolque_unidad; ?></td>
								<td style="text-align:left;"><?php echo $r_origen; ?></td>	
								<td style="text-align:left;"><?php echo $r_destino; ?></td>
								<td style="text-align:left;"><?php echo $r_clave; ?></td>
								<td style="text-align:center;"><?php echo $r_viaje; ?></td>
								<td style="text-align:right;"><?php echo $r_flete; ?></td>
								<td style="text-align:right;"><?php echo $r_maniobras; ?></td>
								<td style="text-align:right;"><?php echo $r_casetas; ?></td>
								<td style="text-align:right;"><?php echo $r_tarimas; ?></td>
								<td style="text-align:right;"><?php echo $r_cajas; ?></td>
								<td style="text-align:right;"><?php echo $r_peso; ?></td>
								<td style="text-align:right;"><?php echo $r_km; ?></td>
								<td style="text-align:left;"><?php echo $r_operador_nombre; ?></td>

                               
                            </tr>
                            <?php
                                }
								$total_viaje = number_format($total_viaje_t,2);
								$total_renta = "$".number_format($total_renta_t,2);
								$total_maniobras = "$".number_format($total_maniobras_t,2);
								$total_casetas = "$".number_format($total_casetas_t,2);
								$total_tarimas = number_format($total_tarimas_t,2);
								$total_cajas = number_format($total_cajas_t,2);
								$total_peso = number_format($total_peso_t,2);
								$total_km = number_format($total_km_t,2);
								
								
								
								$total_rem_t = $total_renta_t + $total_maniobras_t + $total_casetas_t;
								$total_rem = "$".number_format($total_rem_t,2);
                            ?>
							
							<tr>
								<td style="text-align:center;" colspan="12"></td>
								<td style="text-align:right;">RENTA:</td>
								<td style="text-align:center;"><?php echo $total_viaje; ?></td>
								<td style="text-align:right;"><?php echo $total_renta; ?></td>
								<td style="text-align:right;"><?php echo $total_maniobras; ?></td>
								<td style="text-align:right;"><?php echo $total_casetas; ?></td>
								<td style="text-align:right;"><?php echo $total_tarimas; ?></td>
								<td style="text-align:right;"><?php echo $total_cajas; ?></td>
								<td style="text-align:right;"><?php echo $total_peso; ?></td>
								<td style="text-align:right;"><?php echo $total_km; ?></td>
								<td style="text-align:center;"></td>
							</tr>
							<tr>
								<td style="text-align:center;" colspan="12"></td>
								<td style="text-align:right;">TOTAL:</td>
								<td style="text-align:center;"></td>
								<td style="text-align:right;"><?php echo $total_rem; ?></td>
								<td style="text-align:center;" colspan="7"></td>
							</tr>

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
