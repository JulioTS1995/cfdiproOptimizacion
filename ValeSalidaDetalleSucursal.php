<?php 
//error_reporting(0);

$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$sucursal = $_POST ["sucursal"];
$emisor = $_POST["emisor"];

/* $proVsedorID = $_POST["proVsedor"]; */

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
<title>Vale Salida Detalle</title>

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
				<h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Vale Salida Detalle</h1>
			</div>

	</div>

	<hr>
	
	<div class="row">
		<div class="col-lg-12">
		<div class="row">
	<div class="col-md-12" style="text-align:left">
		<a href="ValeSalidaDetalleExcelSucursal.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
	</div>
	<br>
	<br>
	<div class="col-md-12" style="text-align:left">
		<a href="ValeSalidaDetallePdfSucursal.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&emisor=<?php echo $emisor; ?>"><button type="button" class="btn btn-danger">Exporta a PDF</button></a>
	</div>
</div>

			<label>Periodo Consultado: <?php echo $fechaInicio_f." - ".$fechaFin_f; ?> </label>
			<table class="table table-hoVsr table-responsiVs table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">Folio</th>
				<th align="center" style="font-size: 12px;">Fecha</th>
				<th align="center" style="font-size: 12px;">Unidad</th>
				<th align="center" style="font-size: 12px;">Codigo</th>
				<th align="center" style="font-size: 12px;">Nombre</th>
				<th align="center" style="font-size: 12px;">Descripcion</th>
				<th align="center" style="font-size: 12px;">Cantidad</th>
				<th align="center" style="font-size: 12px;">Precio Unitario</th>
				<th align="center" style="font-size: 12px;">Total</th>

				</tr>
			</thead>
			<tbody>
<?php
	/* if($proVsedorID>0){
		$filtroProv = "AND C.ProVsedorNo_RID = ?";
	} else {
		$filtroProv = "";
	} */
	
	$resSQL = "SELECT 
			Vs.XFolio,
			Vs.Fecha,
            U.Unidad,
			Prd.Codigo,
			Prd.Nombre,
			VsS.Descripcion,
			VsS.Cantidad,
			VsS.PrecioUnitario,
			VsS.Importe
		FROM {$prefijobd}valessalida AS Vs
        LEFT JOIN {$prefijobd}unidades AS U on Vs.Unidad_RID = U.ID
		LEFT JOIN {$prefijobd}valessalidasub AS VsS ON Vs.ID = VsS.FolioSub_RID
		LEFT JOIN {$prefijobd}productos AS Prd ON Prd.ID = VsS.ProductoV_RID
		WHERE Date(Vs.Fecha) BETWEEN ? AND ? AND Vs.OficinaVSalida_RID IN (SELECT ID FROM ".$prefijobd. "Oficinas  WHERE Sucursal_RID = ".$sucursal.")


		ORDER BY Vs.XFolio;
	";
	
	$stmt = $cnx_cfdi3->prepare($resSQL);
	if (!$stmt) {
		die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
	}

	/* if($proVsedorID>0){
		$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $proVsedorID);
	} else {
		} */
	$stmt->bind_param('ss', $fechaInicio, $fechaFin);
	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result(
		$folio,
		$fecha,
        $unidad,
		$codigo,
		$nombre,
		$descripcion,
		$cantidad,
		$precioU,
		$importe
	);

	$sumCantidad = 0;
	$sumPrecioU = 0;
/* 	$sumSubtotal = 0;
	$sumImpuesto = 0; */
	$sumTotal = 0;
	while ($stmt->fetch()) {

	$fecha = date("d-m-Y", strtotime($fecha));
	$sumCantidad += $cantidad;
	$sumPrecioU += $precioU;
	/* $sumSubtotal += $subtotal;
	$sumImpuesto += $impuesto; */
	$sumTotal += $importe;

	?>

	<tr>
		<td align="left"><?php echo $folio ?> </td>
		<td align="left"><?php echo $fecha ?> </td>
		<td align="left"><?php echo $unidad ?> </td>
		<td align="left"><?php echo $codigo ?> </td>
		<td align="left"><?php echo $nombre ?> </td>
		<td align="left"><?php echo $descripcion ?> </td>
		<td align="left"><?php echo $cantidad ?> </td>
		<td align="left"><?php echo ("$".number_format($precioU,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($importe,2)) ?> </td>
	</tr>

	<?php
	}
	?>

	<tr>
		<td align="center">TOTAL:</td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="left"><?php echo $sumCantidad ?> </td>
		<td align="left"><?php echo ("$".number_format($sumPrecioU,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($sumTotal,2)) ?> </td>
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
