<?php 
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);


//Obtener Fechas


//Formato a Fechas

//$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
//$fecha_fin_t = date("d-m-Y", strtotime($fecha_fin));

$prefijobd = $_POST['prefijobd'];

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
    
$fecha2_t = $anio_logs."-".$mes_logs."-".$dia_logs;  
$fecha2 = date("d-m-Y", strtotime($fecha2_t));


$vDesde = $_POST['txtDesde'];
$vHasta = $_POST['txtHasta'];

$fechai = date("d-m-Y", strtotime($vDesde));
$fechaf = date("d-m-Y", strtotime($vHasta));

?>

<!DOCTYPE html>
<html>
<head>


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<!-- datatable -->
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
<!-- datatable -->

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

</head>


<body>  
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Resumen Documentos por Periodo</b></h2>
				<h4>Periodo: <?php echo $fechai." - ".$fechaf; ?></h4>
			</div>
			<!--<div class="col-md-6">
				<a href="Reporte_edo_cuenta_clientes_mail.php?prefijobd=<?php //echo $prefijobd; ?>"><button type="button" class="btn btn-info btn-lg btn-block">Enviar Mail</button></a>
			</div>
			<div class="col-md-6">
				<a href="Reporte_edo_cuenta_clientes_excel.php?prefijobd=<?php //echo $prefijobd; ?>"><button type="button" class="btn btn-success btn-lg btn-block">Exportar a Excel</button></a>
			</div>-->
		</div>
		<br>
		

		<div class="row">
            <div class="col-lg-12" style="heigth:1200; overflow:scroll;">
                <table class="table table-hover table-responsive table-condensed" id="table1">
					<thead>
						<tr>
							<!--<th>Cliente</th>-->
							<th>Módulos</th>
							<th>Total</th>
						  </tr>
					</thead>
                    <tbody>
							<tr>
							<?php
							  
								//Buscar Total de Doc Creados SOLICITUDES				
								$resSQL1="SELECT COUNT(ID) as total_solicitudes FROM ".$prefijobd."solicitudes WHERE Date(Creado) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL1;
								$runSQL1=mysql_query($resSQL1);
								$solicitudes_t = mysql_num_rows($runSQL1);
								$solicitudes = number_format($solicitudes_t,0);
								while ($rowSQL1=mysql_fetch_array($runSQL1)){
									//Obtener_variables
									$total_solicitudes_t = $rowSQL1['total_solicitudes'];
									$total_solicitudes = number_format($total_solicitudes_t,0);
								}
								?>	
									<td>Solicitudes</td>
									<td><?php echo $total_solicitudes; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados REMISIONES
								$resSQL2="SELECT COUNT(ID) as total_remisiones FROM ".$prefijobd."remisiones WHERE Date(Creado) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL2;
								$runSQL2=mysql_query($resSQL2);
								$remisiones_t = mysql_num_rows($runSQL2);
								$remisiones = number_format($remisiones_t,0);
								while ($rowSQL2=mysql_fetch_array($runSQL2)){
									//Obtener_variables
									$total_remisiones_t = $rowSQL2['total_remisiones'];
									$total_remisiones = number_format($total_remisiones_t,0);
								}
							
							?>
									<td>Remisiones</td>
									<td><?php echo $total_remisiones; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados TRANSFER
								$resSQL3="SELECT COUNT(ID) as total_transfer FROM ".$prefijobd."transfer WHERE Date(Creado) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL3;
								$runSQL3=mysql_query($resSQL3);
								$transfer_t = mysql_num_rows($runSQL3);
								$transfer = number_format($transfer_t,0);
								while ($rowSQL3=mysql_fetch_array($runSQL3)){
									//Obtener_variables
									$total_transfer_t = $rowSQL3['total_transfer'];
									$total_transfer = number_format($total_transfer_t,0);
								}
							
							?>
									<td>Transfer</td>
									<td><?php echo $total_transfer; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados GASTOS
								$resSQL4="SELECT COUNT(ID) as total_gastos FROM ".$prefijobd."gastosviajes WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL4;
								$runSQL4=mysql_query($resSQL4);
								$gastos_t = mysql_num_rows($runSQL4);
								$gastos = number_format($gastos_t,0);
								while ($rowSQL4=mysql_fetch_array($runSQL4)){
									//Obtener_variables
									$total_gastos_t = $rowSQL4['total_gastos'];
									$total_gastos = number_format($total_gastos_t,0);
								}
							
							?>
									<td>Gastos</td>
									<td><?php echo $total_gastos; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados CASETAS
								$resSQL5="SELECT COUNT(ID) as total_iave FROM ".$prefijobd."iave WHERE Date(cFecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL5;
								$runSQL5=mysql_query($resSQL5);
								$castas_t = mysql_num_rows($runSQL5);
								$castas = number_format($castas_t,0);
								while ($rowSQL5=mysql_fetch_array($runSQL5)){
									//Obtener_variables
									$total_casetas_t = $rowSQL5['total_iave'];
									$total_casetas = number_format($total_casetas_t,0);
								}
							
							?>
									<td>Casetas</td>
									<td><?php echo $total_casetas; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados FACTURA
								$resSQL6="SELECT COUNT(ID) as total_factura FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL6;
								$runSQL6=mysql_query($resSQL6);
								$factura_t = mysql_num_rows($runSQL6);
								$factura = number_format($factura_t,0);
								while ($rowSQL6=mysql_fetch_array($runSQL6)){
									//Obtener_variables
									$total_factura_t = $rowSQL6['total_factura'];
									$total_factura = number_format($total_factura_t,0);
								}
							
							?>
									<td>Facturación</td>
									<td><?php echo $total_factura; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados ABONOS
								$resSQL7="SELECT COUNT(ID) as total_abonos FROM ".$prefijobd."abonos WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL7;
								$runSQL7=mysql_query($resSQL7);
								$abonos_t = mysql_num_rows($runSQL7);
								$abonos = number_format($abonos_t,0);
								while ($rowSQL7=mysql_fetch_array($runSQL7)){
									//Obtener_variables
									$total_abonos_t = $rowSQL7['total_abonos'];
									$total_abonos = number_format($total_abonos_t,0);
								}
							
							?>
									<td>Abonos</td>
									<td><?php echo $total_abonos; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados LIQUIDACIONES
								$resSQL8="SELECT COUNT(ID) as total_liq FROM ".$prefijobd."liquidaciones WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL8;
								$runSQL8=mysql_query($resSQL8);
								$liq_t = mysql_num_rows($runSQL8);
								$liq = number_format($liq_t,0);
								while ($rowSQL8=mysql_fetch_array($runSQL8)){
									//Obtener_variables
									$total_liq_t = $rowSQL8['total_liq'];
									$total_liq = number_format($total_liq_t,0);
								}
							
							?>
									<td>Liquidaciones</td>
									<td><?php echo $total_liq; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados PLANEACION DE SERVICIOS
								$resSQL9="SELECT COUNT(ID) as total_planeacion FROM ".$prefijobd."planeacionmant WHERE Date(Creacion) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL9;
								$runSQL9=mysql_query($resSQL9);
								$planeacion_t = mysql_num_rows($runSQL9);
								$planeacion = number_format($planeacion_t,0);
								while ($rowSQL9=mysql_fetch_array($runSQL9)){
									//Obtener_variables
									$total_planeacion_t = $rowSQL9['total_planeacion'];
									$total_planeacion = number_format($total_planeacion_t,0);
								}
							
							?>
									<td>Planeación de Servicios</td>
									<td><?php echo $total_planeacion; ?></td>
							</tr> 
							<tr>	
							<?php
								//Buscar Total de Doc Creados MANTENIMIENTOS
								$resSQL10="SELECT COUNT(ID) as total_mantenimientos FROM ".$prefijobd."mantenimientos WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL10;
								$runSQL10=mysql_query($resSQL10);
								$mantenimientos_t = mysql_num_rows($runSQL10);
								$mantenimientos = number_format($mantenimientos_t,0);
								while ($rowSQL10=mysql_fetch_array($runSQL10)){
									//Obtener_variables
									$total_mantenimientos_t = $rowSQL10['total_mantenimientos'];
									$total_mantenimientos = number_format($total_mantenimientos_t,0);
								}
							
							?>
									<td>Mantenimientos</td>
									<td><?php echo $total_mantenimientos; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados VALE DE ENTRADA
								$resSQL11="SELECT COUNT(ID) as total_vale_entrada FROM ".$prefijobd."valesentrada WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL11;
								$runSQL11=mysql_query($resSQL11);
								$vale_entrada_t = mysql_num_rows($runSQL11);
								$vale_entrada = number_format($vale_entrada_t,0);
								while ($rowSQL11=mysql_fetch_array($runSQL11)){
									//Obtener_variables
									$total_vale_entrada_t = $rowSQL11['total_vale_entrada'];
									$total_vale_entrada = number_format($total_vale_entrada_t,0);
								}
							
							?>
									<td>Vale de Entrada</td>
									<td><?php echo $total_vale_entrada; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados VALE DE SALIDA
								$resSQL12="SELECT COUNT(ID) as total_vale_salida FROM ".$prefijobd."valessalida WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL12;
								$runSQL12=mysql_query($resSQL12);
								$vale_salida_t = mysql_num_rows($runSQL12);
								$vale_salida = number_format($vale_salida_t,0);
								while ($rowSQL12=mysql_fetch_array($runSQL12)){
									//Obtener_variables
									$total_vale_salida_t = $rowSQL12['total_vale_salida'];
									$total_vale_salida = number_format($total_vale_salida_t,0);
								}
							
							?>
									<td>Vale de Salida</td>
									<td><?php echo $total_vale_salida; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados ORDEN COMPRA
								$resSQL13="SELECT COUNT(ID) as total_orden_compra FROM ".$prefijobd."ordencompra WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL13;
								$runSQL13=mysql_query($resSQL13);
								$orden_compra_t = mysql_num_rows($runSQL13);
								$orden_compra = number_format($orden_compra_t,0);
								while ($rowSQL13=mysql_fetch_array($runSQL13)){
									//Obtener_variables
									$total_orden_compra_t = $rowSQL13['total_orden_compra'];
									$total_orden_compra = number_format($total_orden_compra_t,0);
								}
							
							?>
									<td>Orden de Compra</td>
									<td><?php echo $total_orden_compra; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados COMPRAS
								$resSQL14="SELECT COUNT(ID) as total_compras FROM ".$prefijobd."compras WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL14;
								$runSQL14=mysql_query($resSQL14);
								$compras_t = mysql_num_rows($runSQL14);
								$compras = number_format($compras_t,0);
								while ($rowSQL14=mysql_fetch_array($runSQL14)){
									//Obtener_variables
									$total_compras_t = $rowSQL14['total_compras'];
									$total_compras = number_format($total_compras_t,0);
								}
							
							?>
									<td>Compras</td>
									<td><?php echo $total_compras; ?></td>
							</tr>
							<tr>	
							<?php
								//Buscar Total de Doc Creados PAGOS A PROVEEDORES
								$resSQL15="SELECT COUNT(ID) as total_pagos FROM ".$prefijobd."pagos WHERE Date(Fecha) Between '".$vDesde." 00:00:00' AND '".$vHasta." 23:59:59'";
								//echo "<br>".$resSQL15;
								$runSQL15=mysql_query($resSQL15);
								$pagos_t = mysql_num_rows($runSQL15);
								$pagos = number_format($pagos_t,0);
								while ($rowSQL15=mysql_fetch_array($runSQL15)){
									//Obtener_variables
									$total_pagos_t = $rowSQL15['total_pagos'];
									$total_pagos = number_format($total_pagos_t,0);
								}
							
							?>
									<td>Pagos a Proveedores</td>
									<td><?php echo $total_pagos; ?></td>
							</tr>
							
							
					</tbody>
						
						<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
				</table>		
			</div>
		</div>			
	</div>
</body>



<script>
  $(document).ready(function() {
    $('#table1').DataTable({
    	 "order": [[ 0, "asc" ]],
         pageLength : 25
    });
  } );
</script>

</body>
</html>
<?php
//mysql_free_result($runSQL4);
mysql_close($cnx_cfdi);

//http://ts-c13.ddns.net/cfdipro/resumen_documentos_periodo_fechas.php?prefijodb=tpsrali


?>