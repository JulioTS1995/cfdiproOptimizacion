<?php 
//error_reporting(0);

$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$proveedorID = $_POST["proveedor"];
$emisor = $_POST["emisor"];

$unidadID = $_POST["unidad"];
$productoID = $_POST["producto"];

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
<title>Compras Detalle</title>

<!-- Bootstrap -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

<!-- jQuery (solo una vez) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">

<!-- ESTILOS PARA SCROLL -->
<style>
    body {
        overflow-y: auto !important;
        padding: 20px;
    }

    /* Contenedor con scroll para la tabla */
    .table-wrapper {
        max-height: 80vh;
        overflow-y: auto;
        overflow-x: auto;
    }
</style>

</head>

<body>

<div id="container1">
	<div id="contenedor2">
		<h1 class="font-weight-bold" style="text-align:left; color:#0059b3;">
			<strong>Compras Detalle</strong>
		</h1>
	</div>

	<hr>

	<div class="row">
		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12" style="text-align:left">
					<a href="comprasDetalleExcel.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&proveedor=<?php echo $proveedorID; ?>&unidad=<?php echo $unidadID; ?>&producto=<?php echo $productoID; ?>">
						<button type="button" class="btn btn-success">Exporta a Excel</button>
					</a>
				</div>

				<br><br>

				<div class="col-md-12" style="text-align:left">
					<a href="comprasDetallePdf.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&proveedor=<?php echo $proveedorID; ?>&emisor=<?php echo $emisor; ?>&unidad=<?php echo $unidadID; ?>&producto=<?php echo $productoID; ?>">
						<button type="button" class="btn btn-danger">Exporta a PDF</button>
					</a>
				</div>
			</div>

			<label>Periodo Consultado: <?php echo $fechaInicio_f . " - " . $fechaFin_f; ?> </label>

			<!-- WRAPPER CON SCROLL -->
			<div class="table-wrapper">
				<table class="table table-hover table-condensed" id="table">
					<thead>
						<tr>
							<th>Proveedor</th>
							<th>Folio</th>
							<th>Fecha</th>
							<th>Código</th>
							<th>Nombre</th>
							<th>Descripción</th>
							<th>Unidad</th>
							<th>Factura</th>
							<th>Cantidad</th>
							<th>Precio</th>
							<th>Subtotal</th>
							<th>Importe IVA</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						<?php
	if($proveedorID>0){
		$filtroProv = " AND C.ProveedorNo_RID = ?";
	} else {
		$filtroProv = "";
	}

	if($unidadID>0){
		$filtroUnidad = " AND C.Unidad_RID = ?";
	} else {
		$filtroUnidad = "";
	}

	if($productoID>0){
		$filtroProducto= " AND Cs.ProductoA_RID = ?";
	} else {
		$filtroProducto = "";
	}
	
	$resSQL = "SELECT 
			Prv.RazonSocial,
			C.XFolio,
			C.Fecha,
			Prd.Codigo,
			Cs.Nombre,
			Prd.Descripcion,
			U.Unidad,
			C.Factura,
			Cs.Cantidad,
			Cs.PrecioUnitario,
			Cs.Importe,
			Cs.ImporteIVA,
			Cs.ImporteTotal
		FROM {$prefijobd}ComprasSub AS Cs
		LEFT JOIN {$prefijobd}Compras AS C ON C.ID = Cs.FolioSub_RID
		LEFT JOIN {$prefijobd}Proveedores AS Prv ON Prv.ID = C.ProveedorNo_RID
		LEFT JOIN {$prefijobd}Productos AS Prd ON Prd.ID = Cs.ProductoA_RID
		LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = C.Unidad_RID
		WHERE Date(C.Fecha) BETWEEN ? AND ? 
		{$filtroProv}
		{$filtroUnidad}
		{$filtroProducto}
		ORDER BY C.XFolio;
	";
	
	$stmt = $cnx_cfdi3->prepare($resSQL);
	if (!$stmt) {
		die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
	}

	/*if($proveedorID>0){
		$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $proveedorID);
	} else {
		$stmt->bind_param('ss', $fechaInicio, $fechaFin);
	}*/

	$tipos = "ss"; // por fechaInicio y fechaFin
	$params = [$fechaInicio, $fechaFin];


	// Agregar filtros dinámicos
	if (!empty($proveedorID)) {
		$resSQL .= " AND C.ProveedorNo_RID = ?";
		$tipos .= "i";
		$params[] = $proveedorID;
	}

	if (!empty($unidadID)) {
		$resSQL .= " AND C.Unidad_RID = ?";
		$tipos .= "i";
		$params[] = $unidadID;
	}

	if (!empty($productoID)) {
		$resSQL .= " AND Cs.ProductoA_RID = ?";
		$tipos .= "i";
		$params[] = $productoID;
	}


	// Preparar array con referencias
	$bindParams = [];
	$bindParams[] = $tipos;                  // primer elemento: string de tipos
	for ($i = 0; $i < count($params); $i++) {
		$bindParams[] = &$params[$i];        // IMPORTANT: pasar por referencia
	}

	// Llamar a bind_param con call_user_func_array
	if (!call_user_func_array([$stmt, 'bind_param'], $bindParams)) {
		die("Error en bind_param: " . $stmt->error);
	}

	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result(
		$proveedor,
		$folio,
		$fecha,
		$codigo,
		$nombre,
		$descripcion,
		$unidad,
		$factura,
		$cantidad,
		$precioU,
		$subtotal,
		$impuesto,
		$total
	);

	$sumCantidad = 0;
	$sumPrecioU = 0;
	$sumSubtotal = 0;
	$sumImpuesto = 0;
	$sumTotal = 0;
	while ($stmt->fetch()) {

	$fecha = date("d-m-Y", strtotime($fecha));
	$sumCantidad += $cantidad;
	$sumPrecioU += $precioU;
	$sumSubtotal += $subtotal;
	$sumImpuesto += $impuesto;
	$sumTotal += $total;

	?>

	<tr>
		<td align="center"><?php echo $proveedor ?> </td>
		<td align="left"><?php echo $folio ?> </td>
		<td align="left"><?php echo $fecha ?> </td>
		<td align="left"><?php echo $codigo ?> </td>
		<td align="left"><?php echo $nombre ?> </td>
		<td align="left"><?php echo $descripcion ?> </td>
		<td align="left"><?php echo $unidad ?> </td>
		<td align="left"><?php echo $factura ?> </td>
		<td align="left"><?php echo $cantidad ?> </td>
		<td align="left"><?php echo ("$".number_format($precioU,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($subtotal,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($impuesto,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($total,2)) ?> </td>
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
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="left"><?php echo $sumCantidad ?> </td>
		<td align="left"><?php echo ("$".number_format($sumPrecioU,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($sumSubtotal,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($sumImpuesto,2)) ?> </td>
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
	</div>
</div>

<script>
$(document).ready(function() {
	$('#table').DataTable({
		"order": [[1, "asc"]],
		pageLength: 20
	});
});
</script>

</body>
</html>
