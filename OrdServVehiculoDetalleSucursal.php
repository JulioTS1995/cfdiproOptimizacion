<?php 
//error_reporting(0);

$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$unidadID = $_POST["unidad"];
$sucursal = $_POST["sucursal"];
$emisor = $_POST["emisor"];

$fechaInicio = $_POST["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_POST["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));

require_once('cnx_cfdi3.php');

if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>ORDENES DE SERVICIO POR VEHICULO A DETALLE</title>

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

			
			<div id="2" style="float: left; width: 100%; text-align:left;">
				<h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>ORDENES DE SERVICIO POR VEHICULO A DETALLE</h1>
			</div>

	</div>

	<hr>
	
	<div class="row">
		<div class="col-lg-12">
		<div class="row">
	<div class="col-md-12" style="text-align:left">
		<a href="OrdServVehiculoExcelDetalleSucursal.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&unidad=<?php echo $unidadID; ?>&sucursal=<?php echo $sucursal; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
	</div>
	<br>
	<br>
	<div class="col-md-12" style="text-align:left">
		<a href="OrdServVehiculoPdfDetalleSucursal.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&unidad=<?php echo $unidadID; ?>&sucursal=<?php echo $sucursal; ?>&emisor=<?php echo $emisor; ?>"><button type="button" class="btn btn-danger">Exporta a PDF</button></a>
	</div>
</div>

			<label>Periodo Consultado: <?php echo $fechaInicio_f." - ".$fechaFin_f; ?> </label>
			<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
					<th align="center" style="font-size: 12px;">Orden Servicio</th>
					<th align="center" style="font-size: 12px;">Fecha</th>
					<th align="center" style="font-size: 12px;">Vehiculo</th>
					<th align="center" style="font-size: 12px;">Km</th>
					<th align="center" style="font-size: 12px;">Servicio</th>
					<th align="center" style="font-size: 12px;">Taller</th>
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
	//MantenimientosSub AS Ms
	$resSQL = "SELECT 
    M.XFolio,
    M.Fecha,
    U.Unidad,
    Ms.Kilometros,
    R.Reparacion,
    T.Taller,
    SUM(VS.Subtotal) AS Subtotal,
    SUM(VS.Impuesto) AS Impuesto,
    SUM(VS.Total) AS Total,
	P.Nombre,
	VSS.Cantidad,
	VSS.PrecioUnitario,
	VSS.Importe,
	VSS.ImporteIVA,
	VSS.ImporteTotal
	FROM {$prefijobd}ValesSalidaSub AS VSS
	LEFT JOIN {$prefijobd}ValesSalida AS VS ON VS.ID = VSS.FolioSub_RID
	LEFT JOIN {$prefijobd}Mantenimientos AS M ON M.ID = VS.MantVSalida_RID
	LEFT JOIN {$prefijobd}MantenimientosSub AS Ms ON Ms.FolioSub_RID = M.ID
	LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = M.UnidadMantenimiento_RID
	LEFT JOIN {$prefijobd}Reparaciones AS R ON R.ID = Ms.Reparacion_RID
	LEFT JOIN {$prefijobd}Talleres AS T ON T.ID = Ms.Taller_RID
	LEFT JOIN {$prefijobd}Productos AS P ON P.ID = VSS.ProductoV_RID
	WHERE DATE(M.Fecha) BETWEEN ? AND ? AND M.OficinaMant_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." )
	{$filtroUnidad}
	GROUP BY M.XFolio, M.Fecha, U.Unidad, Ms.Kilometros, R.Reparacion, T.Taller, P.Nombre, VSS.Cantidad, VSS.PrecioUnitario, VSS.Importe, VSS.ImporteIVA, VSS.ImporteTotal
	ORDER BY  U.Unidad;";
	
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
		$importeTotal
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
				<td align="center"><?php echo $servicio ?> </td>
				<td align="center"><?php echo $taller ?> </td>
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
				<td colspan="9"></td>
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
<br>

<br><br>

</div>

<script>
	$(document).ready(function() {
	$('#table').DataTable();
	});
</script>

</body>
</html>
