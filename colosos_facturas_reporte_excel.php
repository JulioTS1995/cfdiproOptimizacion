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


////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="14" style="font-size: 18px;">Reporte de Ventas Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
                    <tr>
					  <th scope="col" style="text-align: center;">Factura</th>
					  <th scope="col" style="text-align: center;">Descripcion</th>
					<th scope="col" style="text-align: center;">Cliente</th>
					<th scope="col" style="text-align: center;">Fecha Creado</th>
					<th scope="col" style="text-align: center;">Flete</th>
					<th scope="col" style="text-align: center;">Subtotal</th>
					<th scope="col" style="text-align: center;">Impuesto</th>
					<th scope="col" style="text-align: center;">Retenido</th>
					<th scope="col" style="text-align: center;">Total</th>
					<th scope="col" style="text-align: center;">Cobranza Abonado</th>
					<th scope="col" style="text-align: center;">Cobranza Saldo</th>
					<th scope="col" style="text-align: center;">Fecha Vence</th>
					<th scope="col" style="text-align: center;">Estatus</th>
					<th scope="col" style="text-align: center;">CFDI Sustituida por</th>
					<th scope="col" style="text-align: center;">Abonos</th>
                    </tr>
                  </thead>
                  <tbody>



<?php

					$resSQL="SELECT *  FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$_GET["fechai"]."' And '".$_GET["fechaf"]."' ORDER BY Creado";
					$runSQL=mysql_query($resSQL);
					
					while ($rowSQL=mysql_fetch_array($runSQL)){
						//Obtener_variables
						$xfolio = $rowSQL['XFolio'];
						$creado = $rowSQL['Creado'];
						$flete_t = $rowSQL['yFlete'];
						$flete = "$".number_format($flete_t,2);
						$subtotal_t = $rowSQL['zSubtotal'];
						$subtotal = "$".number_format($subtotal_t,2);
						$impuesto_t = $rowSQL['zImpuesto'];
						$impuesto = "$".number_format($impuesto_t,2);
						$retenido_t = $rowSQL['zRetenido'];
						$retenido = "$".number_format($retenido_t,2);
						$cancelado = $rowSQL['cCanceladoT'];
						$total_t = $rowSQL['zTotal'];
						$total = "$".number_format($total_t,2);
						$cobranzaabonado_t = $rowSQL['CobranzaAbonado'];
						$cobranzaabonado = "$".number_format($cobranzaabonado_t,2);
						$cobranzasaldo_t = $rowSQL['CobranzaSaldo'];
						$cobranzasaldo = "$".number_format($cobranzasaldo_t,2);
						$vence = $rowSQL['Vence'];
						$sustituidapor = $rowSQL['cfdiSustituidaPor'];
						$cancelado_t = $rowSQL['cCanceladoT'];
						$cliente_id = $rowSQL['CargoAFactura_RID'];
						$factura_id = $rowSQL['ID'];
						if($cancelado_t == ''){
							$cancelado = "Vigente";
						} else {
							$cancelado = "Cancelado";
						}
						
						//Buscar Cliente
						
							$resSQL1="SELECT *  FROM ".$prefijobd."clientes WHERE ID=".$cliente_id;
							$runSQL1=mysql_query($resSQL1);
							while ($rowSQL1=mysql_fetch_array($runSQL1)){
								$cliente = $rowSQL1['RazonSocial'];
							}
							
							$resSQL3="SELECT a.Detalle  FROM ".$prefijobd."FacturaPartidas a  inner join ".$prefijobd."Factura b on a.FolioSub_RID=".$factura_id." and b.ID=a.FolioSub_RID AND a.Tipo='Flete'";
						$Detalles=[];
						$runSQL3=mysql_query($resSQL3);
						while ($rowSQL3=mysql_fetch_array($runSQL3)){
							//$folioabono = $rowSQL2['XFolio'];
							$Detalles[]=$rowSQL3['Detalle'];
						}
						
						//Buscar AbonosSub
						$foliosAbonos=[];
						$resSQL2="SELECT b.XFolio  FROM ".$prefijobd."abonossub a  inner join ".$prefijobd."abonos b on a.AbonoFactura_RID=".$factura_id." and b.ID=a.FolioSub_RID";
						$runSQL2=mysql_query($resSQL2);
						while ($rowSQL2=mysql_fetch_array($runSQL2)){
							$foliosAbonos[] = $rowSQL2['XFolio'];
						}
							

?>
                    <tr>
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo implode(", ",$Detalles); ?></td>
					<td style="text-align: center;"><?php echo $cliente; ?></td>
					<td style="text-align: center;"><?php echo $creado; ?></td>
					<td style="text-align: center;"><?php echo $flete; ?></td>
					<td style="text-align: center;"><?php echo $subtotal; ?></td>
					<td style="text-align: center;"><?php echo $impuesto; ?></td>
					<td style="text-align: center;"><?php echo $retenido; ?></td>
					<td style="text-align: center;"><?php echo $total; ?></td>
					<td style="text-align: center;"><?php echo $cobranzaabonado; ?></td>
					<td style="text-align: center;"><?php echo $cobranzasaldo; ?></td>
					<td style="text-align: center;"><?php echo $vence; ?></td>
					<td style="text-align: center;"><?php echo $cancelado; ?></td>
					<td style="text-align: center;"><?php echo $sustituidapor; ?></td>
					<td style="text-align: center;"><?php echo implode(", ",$foliosAbonos); ?></td>
                    </tr>


<?php
				$folioabono ="";

                  } // FIN del WHILE
?>					

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
