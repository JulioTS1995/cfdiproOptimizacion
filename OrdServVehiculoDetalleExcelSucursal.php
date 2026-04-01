<?php
header("Content-type: application/vnd.ms-excel");
$nombre="orden_servicio_detalle_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");
date_default_timezone_set("America/Mexico_City");


require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
$unidadID = $_GET["unidad"];
$sucursal = $_GET["sucursal"];//trae sucursal


$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));


?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<div style="position: absolute; right: 0; text-align: right;">
		<div><strong>Fecha:</strong> <?php echo  date("d/m/Y") ?></div>
		<div><strong>Hora:</strong> <?php echo  date("H:i:s") ?></div>
</div>

<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
					<th align="center" style="font-size: 12px;">Orden Servicio</th>
					<th align="center" style="font-size: 12px;">Fecha</th>
					<th align="center" style="font-size: 12px;">Vehiculo</th>
					<th align="center" style="font-size: 12px;">Km</th>
					<th align="center" style="font-size: 12px;">Duracion (Hrs.)</th>
					<th align="center" style="font-size: 12px;">Servicio</th>
					<th align="center" style="font-size: 12px;">Taller</th>
					<th align="center" style="font-size: 12px;">Mecanico</th>
					<th align="center" style="font-size: 12px;">Subtotal</th>
					<th align="center" style="font-size: 12px;">IVA</th>
					<th align="center" style="font-size: 12px;">Total</th>
					<th align="center" style="font-size: 12px;">Articulo</th>
					<th align="center" style="font-size: 12px;">Cantidad</th>
					<th align="center" style="font-size: 12px;">Precio Unitario</th>
					<th align="center" style="font-size: 12px;">Importe</th>
					<th align="center" style="font-size: 12px;">IVA</th>
					<th align="center" style="font-size: 12px;">Total</th>
				</tr>
			</thead>
			<tbody>



<?php
if($unidadID>0){
		$filtroUnidad = "AND M.UnidadMantenimiento_RID = ?";
	} else {
		$filtroUnidad = "";
	}
	
	$resSQL = "SELECT 
    M.XFolio,
    M.Fecha,
    U.Unidad,
    (SELECT Kilometros FROM {$prefijobd}MantenimientosSub WHERE FolioSub_RID=M.ID LIMIT 1) AS Kilometros,
    (SELECT Reparacion FROM {$prefijobd}Reparaciones WHERE ID=(SELECT Reparacion_RID FROM {$prefijobd}MantenimientosSub WHERE FolioSub_RID=M.ID LIMIT 1) LIMIT 1) AS Reparacion,
    (SELECT Taller FROM {$prefijobd}Talleres WHERE ID=(SELECT Taller_RID FROM {$prefijobd}MantenimientosSub WHERE FolioSub_RID=M.ID LIMIT 1) LIMIT 1) AS Taller,
    SUM(VS.Subtotal) AS Subtotal,
    SUM(VS.Impuesto) AS Impuesto,
    SUM(VS.Total) AS Total,
	P.Nombre,
	VSS.Cantidad,
	VSS.PrecioUnitario,
	VSS.Importe,
	VSS.ImporteIVA,
	VSS.ImporteTotal,
	(SELECT Duracion FROM {$prefijobd}MantenimientosSub WHERE FolioSub_RID=M.ID LIMIT 1) AS Duracion,
			(SELECT Mecanico FROM {$prefijobd}Mecanicos WHERE ID = (SELECT Atencion_RID FROM {$prefijobd}MantenimientosSub WHERE FolioSub_RID=M.ID LIMIT 1) LIMIT 1) AS Mecanico
	FROM {$prefijobd}ValesSalidaSub AS VSS
	LEFT JOIN {$prefijobd}ValesSalida AS VS ON VS.ID = VSS.FolioSub_RID
	LEFT JOIN {$prefijobd}Mantenimientos AS M ON M.ID = VS.MantVSalida_RID
	LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = M.UnidadMantenimiento_RID

	LEFT JOIN {$prefijobd}Productos AS P ON P.ID = VSS.ProductoV_RID
	WHERE DATE(M.Fecha) BETWEEN ? AND ? 
	{$filtroUnidad}
	GROUP BY M.XFolio, M.Fecha, U.Unidad, Kilometros, Reparacion, Taller, P.Nombre, VSS.Cantidad, VSS.PrecioUnitario, VSS.Importe, VSS.ImporteIVA, VSS.ImporteTotal
	ORDER BY  M.XFolio;";
	
	$stmt = $cnx_cfdi3->prepare($resSQL);
	if (!$stmt) {
		die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
	}

	if($unidadID>0){
		$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $unidadID);
	} else {
		$stmt->bind_param('ss', $fechaInicio, $fechaFin);
	}
	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result(
		$folio,
		$fecha,
		$unidad,
		$kms,
		$servicio,
		$taller,
		$subtotal,
		$impuesto,
		$total,
		$nombre,
		$cantidad,
		$precioUnitario,
		$importe,
		$importeIVA,
		$importeTotal,
		$duracion,
		$mecanico
	);

	$sumSubtotal = 0;
	$sumImpuesto = 0;
	$sumTotal = 0;
	$sumCantidad = 0;
	$sumImporte = 0;
	$sumImporteIVA = 0;
	$sumImporteTotal = 0;
	$ultimoFolio = null;

	while ($stmt->fetch()) {
		$fecha = date("d-m-Y", strtotime($fecha));
	
		if ($folio != $ultimoFolio) {
			?>
			<tr>
				<td align="center"><?php echo $folio ?> </td>
				<td align="center"><?php echo $fecha ?> </td>
				<td align="center"><?php echo $unidad ?> </td>
				<td align="center"><?php echo $kms ?> </td>
				<td align="center"><?php echo $duracion ?> </td>
				<td align="center"><?php echo $servicio ?> </td>
				<td align="center"><?php echo $taller ?> </td>
				<td align="center"><?php echo $mecanico ?> </td>
				<td align="center"><?php echo ("$" . number_format($subtotal, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($impuesto, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($total, 2)) ?> </td>
				<td align="center"><?php echo $nombre ?> </td>
				<td align="center"><?php echo $cantidad ?> </td>
				<td align="center"><?php echo ("$" . number_format($precioUnitario, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importe, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importeIVA, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importeTotal, 2)) ?> </td>
			</tr>
			<?php
		} else {
			
			?>
			<tr>
				<td colspan="11"></td> 
				<td align="center"><?php echo $nombre ?> </td>
				<td align="center"><?php echo $cantidad ?> </td>
				<td align="center"><?php echo ("$" . number_format($precioUnitario, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importe, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importeIVA, 2)) ?> </td>
				<td align="center"><?php echo ("$" . number_format($importeTotal, 2)) ?> </td>
			</tr>
			<?php
		}
	
		$ultimoFolio = $folio; 
	}
	?>

	<tr>
		<td align="center">TOTAL:</td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"><?php echo ("$".number_format($sumSubtotal,2)) ?> </td>
		<td align="center"><?php echo ("$".number_format($sumImpuesto,2)) ?> </td>
		<td align="center"><?php echo ("$".number_format($sumTotal,2)) ?> </td>
		<td align="center"> </td>
		<td align="center"> <?php echo ($sumCantidad) ?> </td>
		<td align="center"> </td>
		<td align="center"><?php echo ("$".number_format($sumImporte,2)) ?> </td>
		<td align="center"><?php echo ("$".number_format($sumImporteIVA,2)) ?> </td>
		<td align="center"><?php echo ("$".number_format($sumImporteTotal,2)) ?> </td>
	</tr>

	<?php
$stmt->free_result();
$stmt->close();
$cnx_cfdi3->close();
?>

</tbody>
</table>
</div>
</div>

</div>

</body>
</html>
