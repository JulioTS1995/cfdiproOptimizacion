<?php
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_ventas_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');



require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
//mysqli_query($conexion,"SET NAMES 'utf8'");

$v_condicion = $_GET['vsql'];
$prefijobd = $_GET['prefijodb'];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

$resumen_total = 0;


////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="16" style="font-size: 18px;">Reporte de Ventas ARDICA Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
                    <tr>
					  <th align="center" style="font-size: 12px;">Oficina</th>
                      <th align="center" style="font-size: 12px;">Carta Porte</th>
                      <th align="center" style="font-size: 12px;">Fecha Emision</th>
					  <th align="center" style="font-size: 12px;">Referencia</th>
					  <th align="center" style="font-size: 12px;">Peso</th>
                      <th align="center" style="font-size: 12px;">Nombre</th>
					  <th align="center" style="font-size: 12px;">Subtotal</th>
					  <th align="center" style="font-size: 12px;">IVA</th>
					  <th align="center" style="font-size: 12px;">Retencion</th>
                      <th align="center" style="font-size: 12px;">Total Factura</th>
                      <th align="center" style="font-size: 12px;">Folio Remision</th>
                      <th align="center" style="font-size: 12px;">Oficina Remision</th>
                      <th align="center" style="font-size: 12px;">Unidad</th>
					  <th align="center" style="font-size: 12px;">Dia</th>
					  <th align="center" style="font-size: 12px;">Mes</th>
					  <th align="center" style="font-size: 12px;">Año</th>
                    </tr>
                  </thead>
                  <tbody>



<?php

					$resSQL="SELECT *  FROM ".$prefijobd."factura WHERE (Date(Creado) Between '".$_GET["fechai"]."' And '".$_GET["fechaf"]."') OR (Date(cCanceladoT) Between '".$_GET["fechai"]."' And '".$_GET["fechaf"]."') ORDER BY Creado";
					//echo $resSQL;
					$runSQL=mysql_query($resSQL);
					
					while ($rowSQL=mysql_fetch_array($runSQL)){
						//Obtener_variables
						$id_factura = $rowSQL['ID'];
						$xfolio = $rowSQL['XFolio'];
						$creado_t = $rowSQL['Creado'];
						$fecha_emision = date("d-m-Y", strtotime($creado_t));
						$creado = strtotime($creado_t);
						$dia_creado = date("d", $creado);
						$mes_creado = date("m", $creado);
						$anio_creado = date("Y", $creado);
						$cancelado_t = $rowSQL['cCanceladoT'];
						
						$unidad_id = $rowSQL['Unidad_RID'];
						$oficina_id = $rowSQL['Oficina_RID'];
						$ticket = $rowSQL['Ticket'];
						$cliente_id = $rowSQL['CargoAFactura_RID'];
						$peso_t = $rowSQL['xPesoTotal'];
						$peso = number_format($peso_t,2);
						/*$subtotal_t = $rowSQL['zSubtotal'];
						$subtotal = "$".number_format($subtotal_t,2);
						$impuesto_t = $rowSQL['zImpuesto'];
						$impuesto = "$".number_format($impuesto_t,2);
						$retenido_t = $rowSQL['zRetenido'];
						$retenido = "$".number_format($retenido_t,2);*/
						
						/*if($cancelado_t > '1969-12-31 00:00:00'){
							$total_t = $rowSQL['zTotal']*-1;
							$total = "$".number_format($total_t,2);
						} else {
							$total_t = $rowSQL['zTotal'];
							$total = "$".number_format($total_t,2);
						}*/
						
						//Buscar Oficina
						$resSQL1="SELECT *  FROM ".$prefijobd."oficinas WHERE ID=".$oficina_id;
						//echo $resSQL1;
						$runSQL1=mysql_query($resSQL1);
						while ($rowSQL1=mysql_fetch_array($runSQL1)){
							$oficina = $rowSQL1['Serie'];
						}
						//Buscar Unidad
						if($unidad_id > 0){
							$resSQL2="SELECT *  FROM ".$prefijobd."unidades WHERE ID=".$unidad_id;
							$runSQL2=mysql_query($resSQL2);
							while ($rowSQL2=mysql_fetch_array($runSQL2)){
								$unidad = $rowSQL2['Unidad'];
							}
						} else {
							$unidad = "";
						}
							
						
						//Buscar Cliente
						if($cliente_id > 0){
							$resSQL3="SELECT *  FROM ".$prefijobd."clientes WHERE ID=".$cliente_id;
							$runSQL3=mysql_query($resSQL3);
							while ($rowSQL3=mysql_fetch_array($runSQL3)){
								$cliente = $rowSQL3['RazonSocial'];
							}
						} else {
							$cliente = "";
						}
						
						
						//Buscar FacturaDetalle
						if($id_factura > 0){
							$resSQL4="SELECT *  FROM ".$prefijobd."facturasdetalle WHERE FolioSubDetalle_RID=".$id_factura;
							$runSQL4=mysql_query($resSQL4);
							while ($rowSQL4=mysql_fetch_array($runSQL4)){
								$id_remision = $rowSQL4['Remision_RID'];
							}
						} else {
							$id_remision = 0;
						}
						
						
						//Buscar Remision
						if($id_remision > 0){
							$resSQL5="SELECT *  FROM ".$prefijobd."remisiones WHERE ID=".$id_remision;
							$runSQL5=mysql_query($resSQL5);
							while ($rowSQL5=mysql_fetch_array($runSQL5)){
								$folio_remision = $rowSQL5['XFolio'];
								$remisiones_oficina_id = $rowSQL5['Oficina_RID'];
							}
						} else {
							$folio_remision = "";
							$remisiones_oficina_id = 0;
						}
						
						
						//Buscar Oficina Remision
						if($remisiones_oficina_id > 0){
							$resSQL6="SELECT *  FROM ".$prefijobd."oficinas WHERE ID=".$remisiones_oficina_id;
							$runSQL6=mysql_query($resSQL6);
							while ($rowSQL6=mysql_fetch_array($runSQL6)){
								$oficina_remision = $rowSQL6['Serie'];
							}
						} else {
							$oficina_remision = "";
						}
						
						/*Facturas con Refacturación
						1. Buscar que la factura tenga refacturación
						2. Si no tiene, sigue el curso normal
						3. Si si tiene, buscar fecha de Cancelación de la Factura Cancelada-Refacturada
						4. Verificar si la fecha de la Factura Cancelada-Refacturada corresponde al periodo de busqueda, si corresponde, sigue el curso normal
						5. Si no corresponde, generar registro de esta factura en negativo
						*/
						$id_refacturacion = 0;
						//Buscar en ardica_facturauuidrelacionadosub
						$resSQL50="SELECT *  FROM ".$prefijobd."facturauuidrelacionadosub WHERE FolioSub_RID=".$id_factura;
						$runSQL50=mysql_query($resSQL50);
						while ($rowSQL50=mysql_fetch_array($runSQL50)){
							$id_refacturacion = $rowSQL50['ID'];
							$xfolio_cancelado_refacturacion = $rowSQL50['XFolio'];
						}
						if($id_refacturacion > 0){
							//Buscar Datos de la Factura Refacturada
							$resSQL51="SELECT *  FROM ".$prefijobd."factura WHERE XFolio='".$xfolio_cancelado_refacturacion."'";
							$runSQL51=mysql_query($resSQL51);
							while ($rowSQL51=mysql_fetch_array($runSQL51)){
								$id_factura2 = $rowSQL51['ID'];
								$xfolio2 = $rowSQL51['XFolio'];
								$creado_t2 = $rowSQL51['Creado'];
								$fecha_emision2 = date("d-m-Y", strtotime($creado_t2));
								$creado2 = strtotime($creado_t2);
								$dia_creado2 = date("d", $creado2);
								$mes_creado2 = date("m", $creado2);
								$anio_creado2 = date("Y", $creado2);
								$cancelado_t2 = $rowSQL51['cCanceladoT'];
								
								$unidad_id2 = $rowSQL51['Unidad_RID'];
								$oficina_id2 = $rowSQL51['Oficina_RID'];
								$ticket2 = $rowSQL51['Ticket'];
								$cliente_id2 = $rowSQL51['CargoAFactura_RID'];
								$peso_t2 = $rowSQL51['xPesoTotal'];
								$peso2 = number_format($peso_t2,2);
								$subtotal_t2 = $rowSQL51['zSubtotal']*-1;
								$subtotal2 = "$".number_format($subtotal_t2,2);
								$impuesto_t2 = $rowSQL51['zImpuesto']*-1;
								$impuesto2 = "$".number_format($impuesto_t2,2);
								$retenido_t2 = $rowSQL51['zRetenido']*-1;
								$retenido2 = "$".number_format($retenido_t2,2);
								$total_t2 = $rowSQL51['zTotal']*-1;
								$total2 = "$".number_format($total_t2,2);
							}
							
							//Buscar Oficina
							$resSQL52="SELECT *  FROM ".$prefijobd."oficinas WHERE ID=".$oficina_id2;
							$runSQL52=mysql_query($resSQL52);
							while ($rowSQL52=mysql_fetch_array($runSQL52)){
								$oficina2 = $rowSQL52['Serie'];
							}
							//Buscar Unidad
							if($unidad_id2 > 0){
								$resSQL53="SELECT *  FROM ".$prefijobd."unidades WHERE ID=".$unidad_id2;
								$runSQL53=mysql_query($resSQL53);
								while ($rowSQL53=mysql_fetch_array($runSQL53)){
									$unidad2 = $rowSQL53['Unidad'];
								}
							} else {
								$unidad2 = "";
							}
							//Buscar Cliente
							if($cliente_id2 > 0){
								$resSQL54="SELECT *  FROM ".$prefijobd."clientes WHERE ID=".$cliente_id2;
								$runSQL54=mysql_query($resSQL54);
								while ($rowSQL54=mysql_fetch_array($runSQL54)){
									$cliente2 = $rowSQL54['RazonSocial'];
								}
							} else {
								$cliente2 = "";
							}
							
							//Buscar FacturaDetalle2
							if($id_factura2 > 0){
								$resSQL55="SELECT *  FROM ".$prefijobd."facturasdetalle WHERE FolioSubDetalle_RID=".$id_factura2;
								$runSQL55=mysql_query($resSQL55);
								while ($rowSQL55=mysql_fetch_array($runSQL55)){
									$id_remision2 = $rowSQL55['Remision_RID'];
								}
							} else {
								$id_remision2 = 0;
							}
							
							
							//Buscar Remision2
							if($id_remision2 > 0){
								$resSQL56="SELECT *  FROM ".$prefijobd."remisiones WHERE ID=".$id_remision2;
								$runSQL56=mysql_query($resSQL56);
								while ($rowSQL56=mysql_fetch_array($runSQL56)){
									$folio_remision2 = $rowSQL56['XFolio'];
									$remisiones_oficina_id2 = $rowSQL56['Oficina_RID'];
								}
							} else {
								$folio_remision2 = "";
								$remisiones_oficina_id2 = 0;
							}
							
							
							//Buscar Oficina Remision2
							if($remisiones_oficina_id2 > 0){
								$resSQL57="SELECT *  FROM ".$prefijobd."oficinas WHERE ID=".$remisiones_oficina_id2;
								$runSQL57=mysql_query($resSQL57);
								while ($rowSQL57=mysql_fetch_array($runSQL57)){
									$oficina_remision2 = $rowSQL57['Serie'];
								}
							} else {
								$oficina_remision2 = "";
							}
							
							//Verificar si la fecha de Cancelacion pertenece al Periodo de Consulta
							if (($cancelado_t2 >= $_GET["fechai"]) && ($cancelado_t2 <= $_GET["fechaf"])) {
								//Proceso normal
								if ((($cancelado_t >= $_GET["fechai"]) && ($cancelado_t <= $_GET["fechaf"])) && (($creado_t >= $_GET["fechai"]) && ($creado_t <= $_GET["fechaf"]))) {
													$subtotal_t = $rowSQL['zSubtotal'];
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto'];
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido'];
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal'];
													$total = "$".number_format($total_t,2);
													$resumen_total= $resumen_total + $total_t;
										?>
										<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
										  <td align="center"><?php echo $xfolio; ?></td>
										  <td align="center"><?php echo $fecha_emision; ?></td>
										  <td style="text-align: center;"><?php echo $ticket; ?></td>
										  <td style="text-align: left;"><?php echo $peso; ?></td>
										  <td align="left"><?php echo $cliente; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto; ?></td>
										  <td style="text-align: right;"><?php echo $retenido; ?></td>
										  <td align="center"><?php echo $total; ?></td>
										  <td align="center"><?php echo $folio_remision; ?></td>
										  <td align="left"><?php echo $oficina_remision; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado; ?></td>
										  <td align="left"><?php echo $mes_creado; ?></td>
										  <td align="center"><?php echo $anio_creado; ?></td>
										</tr>
										<?php
													$subtotal_t = $rowSQL['zSubtotal']*-1;
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto']*-1;
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido']*-1;
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal']*-1;
													$total = "$".number_format($total_t,2);
													$resumen_total= $resumen_total + $total_t;
										?>
										<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
										  <td align="center"><?php echo $xfolio; ?></td>
										  <td align="center"><?php echo $fecha_emision; ?></td>
										  <td style="text-align: center;"><?php echo $ticket; ?></td>
										  <td style="text-align: left;"><?php echo $peso; ?></td>
										  <td align="left"><?php echo $cliente; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto; ?></td>
										  <td style="text-align: right;"><?php echo $retenido; ?></td>
										  <td align="center"><?php echo $total; ?></td>
										  <td align="center"><?php echo $folio_remision; ?></td>
										  <td align="left"><?php echo $oficina_remision; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado; ?></td>
										  <td align="left"><?php echo $mes_creado; ?></td>
										  <td align="center"><?php echo $anio_creado; ?></td>
										</tr>
										<?php
											
												} elseif ((($cancelado_t >= $_GET["fechai"]) && ($cancelado_t <= $_GET["fechaf"])) && ($creado_t < $_GET["fechai"])) {
													$subtotal_t = $rowSQL['zSubtotal']*-1;
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto']*-1;
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido']*-1;
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal']*-1;
													$total = "$".number_format($total_t,2);
													$resumen_total= $resumen_total + $total_t;
										?>
										<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
										  <td align="center"><?php echo $xfolio; ?></td>
										  <td align="center"><?php echo $fecha_emision; ?></td>
										  <td style="text-align: center;"><?php echo $ticket; ?></td>
										  <td style="text-align: left;"><?php echo $peso; ?></td>
										  <td align="left"><?php echo $cliente; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto; ?></td>
										  <td style="text-align: right;"><?php echo $retenido; ?></td>
										  <td align="center"><?php echo $total; ?></td>
										  <td align="center"><?php echo $folio_remision; ?></td>
										  <td align="left"><?php echo $oficina_remision; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado; ?></td>
										  <td align="left"><?php echo $mes_creado; ?></td>
										  <td align="center"><?php echo $anio_creado; ?></td>
										</tr>
										<?php
												} else {
													$subtotal_t = $rowSQL['zSubtotal'];
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto'];
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido'];
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal'];
													$total = "$".number_format($total_t,2);
													$resumen_total= $resumen_total + $total_t;
										?>
										<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
										  <td align="center"><?php echo $xfolio; ?></td>
										  <td align="center"><?php echo $fecha_emision; ?></td>
										  <td style="text-align: center;"><?php echo $ticket; ?></td>
										  <td style="text-align: left;"><?php echo $peso; ?></td>
										  <td align="left"><?php echo $cliente; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto; ?></td>
										  <td style="text-align: right;"><?php echo $retenido; ?></td>
										  <td align="center"><?php echo $total; ?></td>
										  <td align="center"><?php echo $folio_remision; ?></td>
										  <td align="left"><?php echo $oficina_remision; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado; ?></td>
										  <td align="left"><?php echo $mes_creado; ?></td>
										  <td align="center"><?php echo $anio_creado; ?></td>
										</tr>
										<?php
												} //Fin IF ELSE Canceladas PRIOCESO NORMAL
								
								
								
								
								
							} else {
								//Agregar Factura Refacturada y Cancelada Sustituida en negativo
								$subtotal_t = $rowSQL['zSubtotal'];
								$subtotal = "$".number_format($subtotal_t,2);
								$impuesto_t = $rowSQL['zImpuesto'];
								$impuesto = "$".number_format($impuesto_t,2);
								$retenido_t = $rowSQL['zRetenido'];
								$retenido = "$".number_format($retenido_t,2);
								$total_t = $rowSQL['zTotal'];
								$total = "$".number_format($total_t,2);
								$resumen_total= $resumen_total + $total_t;
								
								?>
								<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
										  <td align="center"><?php echo $xfolio; ?></td>
										  <td align="center"><?php echo $fecha_emision; ?></td>
										  <td style="text-align: center;"><?php echo $ticket; ?></td>
										  <td style="text-align: left;"><?php echo $peso; ?></td>
										  <td align="left"><?php echo $cliente; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto; ?></td>
										  <td style="text-align: right;"><?php echo $retenido; ?></td>
										  <td align="center"><?php echo $total; ?></td>
										  <td align="center"><?php echo $folio_remision; ?></td>
										  <td align="left"><?php echo $oficina_remision; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado; ?></td>
										  <td align="left"><?php echo $mes_creado; ?></td>
										  <td align="center"><?php echo $anio_creado; ?></td>
								</tr>
								<?php
								/*$total_t2 = $rowSQL['zTotal']*-1;
								$total2 = "$".number_format($total_t2,2);
								$resumen_total= $resumen_total + $total_t2;*/
								?>
								<tr>
										  <td scope="row" style="text-align: center;"><?php echo $oficina2; ?></td>
										  <td align="center"><?php echo $xfolio2; ?></td>
										  <td align="center"><?php echo $fecha_emision2; ?></td>
										  <td style="text-align: center;"><?php echo $ticket2; ?></td>
										  <td style="text-align: left;"><?php echo $peso2; ?></td>
										  <td align="left"><?php echo $cliente2; ?></td>
										  <td style="text-align: right;"><?php echo $subtotal2; ?></td>
										  <td style="text-align: right;"><?php echo $impuesto2; ?></td>
										  <td style="text-align: right;"><?php echo $retenido2; ?></td>
										  <td align="center"><?php echo $total2; ?></td>
										  <td align="center"><?php echo $folio_remision2; ?></td>
										  <td align="left"><?php echo $oficina_remision2; ?></td>
										  <td align="left"><?php echo $unidad; ?></td>
										  <td style="text-align: center;"><?php echo $dia_creado2; ?></td>
										  <td align="left"><?php echo $mes_creado2; ?></td>
										  <td align="center"><?php echo $anio_creado2; ?></td>
								</tr>
								<?php
								
	
							}
							

						
						//Validar Canceladas
						} elseif ((($cancelado_t >= $_GET["fechai"]) && ($cancelado_t <= $_GET["fechaf"])) && (($creado_t >= $_GET["fechai"]) && ($creado_t <= $_GET["fechaf"]))) {
							$subtotal_t = $rowSQL['zSubtotal'];
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto'];
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido'];
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal'];
							$total = "$".number_format($total_t,2);
							$resumen_total= $resumen_total + $total_t;
				?>
				<tr>
				    <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
					<td align="center"><?php echo $xfolio; ?></td>
					<td align="center"><?php echo $fecha_emision; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td align="left"><?php echo $cliente; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td align="center"><?php echo $total; ?></td>
					<td align="center"><?php echo $folio_remision; ?></td>
					<td align="left"><?php echo $oficina_remision; ?></td>
					<td align="left"><?php echo $unidad; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td align="left"><?php echo $mes_creado; ?></td>
					<td align="center"><?php echo $anio_creado; ?></td>
				</tr>
				<?php
							$subtotal_t = $rowSQL['zSubtotal']*-1;
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto']*-1;
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido']*-1;
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal']*-1;
							$total = "$".number_format($total_t,2);
							$resumen_total= $resumen_total + $total_t;
				?>
				<tr>
				    <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
					<td align="center"><?php echo $xfolio; ?></td>
					<td align="center"><?php echo $fecha_emision; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td align="left"><?php echo $cliente; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td align="center"><?php echo $total; ?></td>
					<td align="center"><?php echo $folio_remision; ?></td>
					<td align="left"><?php echo $oficina_remision; ?></td>
					<td align="left"><?php echo $unidad; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td align="left"><?php echo $mes_creado; ?></td>
					<td align="center"><?php echo $anio_creado; ?></td>
				</tr>
				<?php
					
						} elseif ((($cancelado_t >= $_GET["fechai"]) && ($cancelado_t <= $_GET["fechaf"])) && ($creado_t < $_GET["fechai"])) {
							$subtotal_t = $rowSQL['zSubtotal']*-1;
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto']*-1;
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido']*-1;
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal']*-1;
							$total = "$".number_format($total_t,2);
							$resumen_total= $resumen_total + $total_t;
				?>
				<tr>
				    <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
					<td align="center"><?php echo $xfolio; ?></td>
					<td align="center"><?php echo $fecha_emision; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td align="left"><?php echo $cliente; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td align="center"><?php echo $total; ?></td>
					<td align="center"><?php echo $folio_remision; ?></td>
					<td align="left"><?php echo $oficina_remision; ?></td>
					<td align="left"><?php echo $unidad; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td align="left"><?php echo $mes_creado; ?></td>
					<td align="center"><?php echo $anio_creado; ?></td>
				</tr>
				<?php
						} else {
							$subtotal_t = $rowSQL['zSubtotal'];
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto'];
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido'];
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal'];
							$total = "$".number_format($total_t,2);
							$resumen_total= $resumen_total + $total_t;
				?>
				<tr>
				    <td scope="row" style="text-align: center;"><?php echo $oficina; ?></td>
					<td align="center"><?php echo $xfolio; ?></td>
					<td align="center"><?php echo $fecha_emision; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td align="left"><?php echo $cliente; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td align="center"><?php echo $total; ?></td>
					<td align="center"><?php echo $folio_remision; ?></td>
					<td align="left"><?php echo $oficina_remision; ?></td>
					<td align="left"><?php echo $unidad; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td align="left"><?php echo $mes_creado; ?></td>
					<td align="center"><?php echo $anio_creado; ?></td>
				</tr>
				<?php
						} //Fin IF ELSE Canceladas
						
						

					
                  } // FIN del WHILE
				  
				  //Calcular Total de Forma Distinta

				   
				   
				   $resumen_total_t = "$".number_format($resumen_total,2);	

?>

						<tr>
						  <td colspan="9" style="text-align: right;"><strong>TOTAL: </strong></td>
						  <td align="left"><strong><?php echo $resumen_total_t; ?></strong></td>
						  <td colspan="6" align="left"></td>
						</tr>

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
