<?php
header("Content-type: application/vnd.ms-excel");
$nombre="compras_detalle_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");



require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
$proveedorID = $_GET["proveedor"];
$unidadID = $_GET["unidad"];
$productoID = $_GET["producto"];


$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));


?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">Proveedor</th>
				<th align="center" style="font-size: 12px;">Folio</th>
				<th align="center" style="font-size: 12px;">Fecha</th>
				<th align="center" style="font-size: 12px;">Codigo</th>
				<th align="center" style="font-size: 12px;">Nombre</th>
				<th align="center" style="font-size: 12px;">Descripcion</th>
				<th align="center" style="font-size: 12px;">Unidad</th>
				<th align="center" style="font-size: 12px;">Factura</th>
				<th align="center" style="font-size: 12px;">Cantidad</th>
				<th align="center" style="font-size: 12px;">Precio</th>
				<th align="center" style="font-size: 12px;">Subtotal</th>
				<th align="center" style="font-size: 12px;">Importe IVA</th>
				<th align="center" style="font-size: 12px;">Total</th>

				</tr>
			</thead>
			<tbody>



<?php

if($proveedorID>0){
	$filtroProv = "AND C.ProveedorNo_RID = ?";
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
	ORDER BY C.XFolio;";
	
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
<br>

<br><br>

</div>
