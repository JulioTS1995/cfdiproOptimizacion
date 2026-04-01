<?php 

/*if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}*/

//Internalizo los parametros previo escape de caracteres especiales
//$prefijobd = @mysql_escape_string($_GET["base"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
//$pos = strpos($prefijobd, "_");

//if ($pos === false) {
//    $prefijobd = $prefijobd . "_";
//} 

//Obtener Fechas

$fecha_inicio = $_POST["fechai"].' 00:00:00';
//$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"].' 23:59:59';
//$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));
$prefijobd = $_POST["base"];

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte Ventas ARDICA</title>

 <!-- Bootstrap links -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable -->

</head>

<body>
 
    <div id = "container1" style = "width: 80%; margin: 0 auto; text-align:center;" >
        <div id="contenedor2" style="overflow:hidden;">
                <!--<div id="1" style="float: left; width: 33%; text-align:left;">
                    <img src="img/logo_ts.png" height="120">
                </div>-->
                
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte de Ventas <small style="color:#4da6ff; ">ARDICA</small></strong></h1>
                </div>

        </div>

        <hr>
        
        <div class="row">
			<div class="col-lg-12">
			  <!--<div id="2" style="float: left; width: 33%; text-align:center;">
					<h1 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Resumen <?php echo $anio_01; ?></h1>
			  </div>-->
			  <label>Periodo Consultado: <?php echo $fecha_inicio_f." - ".$fecha_fin_f; ?> </label>
			  <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
				  <tr>
					<th scope="col" style="text-align: center;">Tipo Documento</th>
					<th scope="col" style="text-align: center;">No. Documento</th>
					<th scope="col" style="text-align: center;">Referencia</th>
					<th scope="col" style="text-align: center;">Dia</th>
					<th scope="col" style="text-align: center;">Mes</th>
					<th scope="col" style="text-align: center;">Año</th>
					<th scope="col" style="text-align: center;">Cliente</th>
					<th scope="col" style="text-align: center;">Unidad</th>
					<th scope="col" style="text-align: center;">Peso</th>
					<th scope="col" style="text-align: center;">SubTotal</th>
					<th scope="col" style="text-align: center;">IVA</th>
					<th scope="col" style="text-align: center;">Retencion</th>
					<th scope="col" style="text-align: center;">Total</th>
				  </tr>
				</thead>
				<tbody>
				<?php
					$resSQL="SELECT *  FROM ".$prefijobd."factura WHERE (Date(Creado) Between '".$fecha_inicio."' And '".$fecha_fin."') OR (Date(cCanceladoT) Between '".$fecha_inicio."' And '".$fecha_fin."') ORDER BY Creado";
					$runSQL=mysql_query($resSQL);
					//echo $resSQL;
					while ($rowSQL=mysql_fetch_array($runSQL)){
						//Obtener_variables
						$id_factura = $rowSQL['ID'];
						$xfolio = $rowSQL['XFolio'];
						$creado_t = $rowSQL['Creado'];
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
							
							//Verificar si la fecha de Cancelacion pertenece al Periodo de Consulta
							if (($cancelado_t2 >= $fecha_inicio) && ($cancelado_t2 <= $fecha_fin)) {
								//Proceso normal
								if ((($cancelado_t >= $fecha_inicio) && ($cancelado_t <= $fecha_fin)) && (($creado_t >= $fecha_inicio) && ($creado_t <= $fecha_fin))) {
													$subtotal_t = $rowSQL['zSubtotal'];
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto'];
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido'];
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal'];
													$total = "$".number_format($total_t,2);
										?>
										<tr>
											<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
											<td style="text-align: center;"><?php echo $xfolio; ?></td>
											<td style="text-align: center;"><?php echo $ticket; ?></td>
											<td style="text-align: center;"><?php echo $dia_creado; ?></td>
											<td style="text-align: center;"><?php echo $mes_creado; ?></td>
											<td style="text-align: center;"><?php echo $anio_creado; ?></td>
											<td style="text-align: left;"><?php echo $cliente; ?></td>
											<td style="text-align: left;"><?php echo $unidad; ?></td>
											<td style="text-align: left;"><?php echo $peso; ?></td>
											<td style="text-align: right;"><?php echo $subtotal; ?></td>
											<td style="text-align: right;"><?php echo $impuesto; ?></td>
											<td style="text-align: right;"><?php echo $retenido; ?></td>
											<td style="text-align: right;"><?php echo $total; ?></td>
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
										?>
										<tr>
											<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
											<td style="text-align: center;"><?php echo $xfolio; ?></td>
											<td style="text-align: center;"><?php echo $ticket; ?></td>
											<td style="text-align: center;"><?php echo $dia_creado; ?></td>
											<td style="text-align: center;"><?php echo $mes_creado; ?></td>
											<td style="text-align: center;"><?php echo $anio_creado; ?></td>
											<td style="text-align: left;"><?php echo $cliente; ?></td>
											<td style="text-align: left;"><?php echo $unidad; ?></td>
											<td style="text-align: left;"><?php echo $peso; ?></td>
											<td style="text-align: right;"><?php echo $subtotal; ?></td>
											<td style="text-align: right;"><?php echo $impuesto; ?></td>
											<td style="text-align: right;"><?php echo $retenido; ?></td>
											<td style="text-align: right;"><?php echo $total; ?></td>
										</tr>
										<?php
											
												} elseif ((($cancelado_t >= $fecha_inicio) && ($cancelado_t <= $fecha_fin)) && ($creado_t < $fecha_inicio)) {
													$subtotal_t = $rowSQL['zSubtotal']*-1;
													$subtotal = "$".number_format($subtotal_t,2);
													$impuesto_t = $rowSQL['zImpuesto']*-1;
													$impuesto = "$".number_format($impuesto_t,2);
													$retenido_t = $rowSQL['zRetenido']*-1;
													$retenido = "$".number_format($retenido_t,2);
													$total_t = $rowSQL['zTotal']*-1;
													$total = "$".number_format($total_t,2);
										?>
										<tr>
											<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
											<td style="text-align: center;"><?php echo $xfolio; ?></td>
											<td style="text-align: center;"><?php echo $ticket; ?></td>
											<td style="text-align: center;"><?php echo $dia_creado; ?></td>
											<td style="text-align: center;"><?php echo $mes_creado; ?></td>
											<td style="text-align: center;"><?php echo $anio_creado; ?></td>
											<td style="text-align: left;"><?php echo $cliente; ?></td>
											<td style="text-align: left;"><?php echo $unidad; ?></td>
											<td style="text-align: left;"><?php echo $peso; ?></td>
											<td style="text-align: right;"><?php echo $subtotal; ?></td>
											<td style="text-align: right;"><?php echo $impuesto; ?></td>
											<td style="text-align: right;"><?php echo $retenido; ?></td>
											<td style="text-align: right;"><?php echo $total; ?></td>
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
										?>
										<tr>
											<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
											<td style="text-align: center;"><?php echo $xfolio; ?></td>
											<td style="text-align: center;"><?php echo $ticket; ?></td>
											<td style="text-align: center;"><?php echo $dia_creado; ?></td>
											<td style="text-align: center;"><?php echo $mes_creado; ?></td>
											<td style="text-align: center;"><?php echo $anio_creado; ?></td>
											<td style="text-align: left;"><?php echo $cliente; ?></td>
											<td style="text-align: left;"><?php echo $unidad; ?></td>
											<td style="text-align: left;"><?php echo $peso; ?></td>
											<td style="text-align: right;"><?php echo $subtotal; ?></td>
											<td style="text-align: right;"><?php echo $impuesto; ?></td>
											<td style="text-align: right;"><?php echo $retenido; ?></td>
											<td style="text-align: right;"><?php echo $total; ?></td>
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
								
								?>
								<tr>
									<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
									<td style="text-align: center;"><?php echo $xfolio; ?></td>
									<td style="text-align: center;"><?php echo $ticket; ?></td>
									<td style="text-align: center;"><?php echo $dia_creado; ?></td>
									<td style="text-align: center;"><?php echo $mes_creado; ?></td>
									<td style="text-align: center;"><?php echo $anio_creado; ?></td>
									<td style="text-align: left;"><?php echo $cliente; ?></td>
									<td style="text-align: left;"><?php echo $unidad; ?></td>
									<td style="text-align: left;"><?php echo $peso; ?></td>
									<td style="text-align: right;"><?php echo $subtotal; ?></td>
									<td style="text-align: right;"><?php echo $impuesto; ?></td>
									<td style="text-align: right;"><?php echo $retenido; ?></td>
									<td style="text-align: right;"><?php echo $total; ?></td>
								</tr>
								<?php
								/*$subtotal_t = $rowSQL['zSubtotal']*-1;
								$subtotal = "$".number_format($subtotal_t,2);
								$impuesto_t = $rowSQL['zImpuesto']*-1;
								$impuesto = "$".number_format($impuesto_t,2);
								$retenido_t = $rowSQL['zRetenido']*-1;
								$retenido = "$".number_format($retenido_t,2);
								$total_t2 = $rowSQL['zTotal']*-1;
								$total2 = "$".number_format($total_t2,2);*/
								?>
								<tr>
									<th scope="row" style="text-align: center;"><?php echo $oficina2; ?></th>
									<td style="text-align: center;"><?php echo $xfolio2; ?></td>
									<td style="text-align: center;"><?php echo $ticket2; ?></td>
									<td style="text-align: center;"><?php echo $dia_creado2; ?></td>
									<td style="text-align: center;"><?php echo $mes_creado2; ?></td>
									<td style="text-align: center;"><?php echo $anio_creado2; ?></td>
									<td style="text-align: left;"><?php echo $cliente2; ?></td>
									<td style="text-align: left;"><?php echo $unidad2; ?></td>
									<td style="text-align: left;"><?php echo $peso2; ?></td>
									<td style="text-align: right;"><?php echo $subtotal2; ?></td>
									<td style="text-align: right;"><?php echo $impuesto2; ?></td>
									<td style="text-align: right;"><?php echo $retenido2; ?></td>
									<td style="text-align: right;"><?php echo $total2; ?></td>
								</tr>
								<?php
								
	
							}
							

						
						//Validar Canceladas
						} elseif ((($cancelado_t >= $fecha_inicio) && ($cancelado_t <= $fecha_fin)) && (($creado_t >= $fecha_inicio) && ($creado_t <= $fecha_fin))) {
							$subtotal_t = $rowSQL['zSubtotal'];
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto'];
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido'];
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal'];
							$total = "$".number_format($total_t,2);
				?>
				<tr>
					<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td style="text-align: center;"><?php echo $mes_creado; ?></td>
					<td style="text-align: center;"><?php echo $anio_creado; ?></td>
					<td style="text-align: left;"><?php echo $cliente; ?></td>
					<td style="text-align: left;"><?php echo $unidad; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td style="text-align: right;"><?php echo $total; ?></td>
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
				?>
				<tr>
					<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td style="text-align: center;"><?php echo $mes_creado; ?></td>
					<td style="text-align: center;"><?php echo $anio_creado; ?></td>
					<td style="text-align: left;"><?php echo $cliente; ?></td>
					<td style="text-align: left;"><?php echo $unidad; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td style="text-align: right;"><?php echo $total; ?></td>
				</tr>
				<?php
					
						} elseif ((($cancelado_t >= $fecha_inicio) && ($cancelado_t <= $fecha_fin)) && ($creado_t < $fecha_inicio)) {
							$subtotal_t = $rowSQL['zSubtotal']*-1;
							$subtotal = "$".number_format($subtotal_t,2);
							$impuesto_t = $rowSQL['zImpuesto']*-1;
							$impuesto = "$".number_format($impuesto_t,2);
							$retenido_t = $rowSQL['zRetenido']*-1;
							$retenido = "$".number_format($retenido_t,2);
							$total_t = $rowSQL['zTotal']*-1;
							$total = "$".number_format($total_t,2);
				?>
				<tr>
					<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td style="text-align: center;"><?php echo $mes_creado; ?></td>
					<td style="text-align: center;"><?php echo $anio_creado; ?></td>
					<td style="text-align: left;"><?php echo $cliente; ?></td>
					<td style="text-align: left;"><?php echo $unidad; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td style="text-align: right;"><?php echo $total; ?></td>
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
				?>
				<tr>
					<th scope="row" style="text-align: center;"><?php echo $oficina; ?></th>
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo $ticket; ?></td>
					<td style="text-align: center;"><?php echo $dia_creado; ?></td>
					<td style="text-align: center;"><?php echo $mes_creado; ?></td>
					<td style="text-align: center;"><?php echo $anio_creado; ?></td>
					<td style="text-align: left;"><?php echo $cliente; ?></td>
					<td style="text-align: left;"><?php echo $unidad; ?></td>
					<td style="text-align: left;"><?php echo $peso; ?></td>
					<td style="text-align: right;"><?php echo $subtotal; ?></td>
					<td style="text-align: right;"><?php echo $impuesto; ?></td>
					<td style="text-align: right;"><?php echo $retenido; ?></td>
					<td style="text-align: right;"><?php echo $total; ?></td>
				</tr>
				<?php
						} //Fin IF ELSE Canceladas
						
					} //Fin cosnulta principal
				?>  
				</tbody>
			  </table>
			</div>
        </div>
        <br>
		<div class="row">
			<div class="col-md-12" style="text-align:left">
				<a href="ardica_facturas_reporte_excel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
			</div>
		</div>
		<br>
		<br>
		
        
        
    </div>
	<script>
	  $(document).ready(function() {
		$('#table').DataTable();
	  } );
	</script>
	
  </body>
</html>
<?php
mysql_free_result($runSQL);
mysql_close($cnx_cfdi);
?>