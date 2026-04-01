<?php
header("Content-type: application/vnd.ms-excel");
$nombre="Vale_Entrada_detalle_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");



require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
/* $proveedorID = $_GET["proveedor"];
 */
$sucursal = $_GET['sucursal'];

$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));


?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">Folio</th>
				<th align="center" style="font-size: 12px;">Fecha</th>
				<th align="center" style="font-size: 12px;">Codigo</th>
				<th align="center" style="font-size: 12px;">Nombre</th>
				<th align="center" style="font-size: 12px;">Descripcion</th>
				<th align="center" style="font-size: 12px;">Cantidad</th>
				<th align="center" style="font-size: 12px;">Precio</th>
				<th align="center" style="font-size: 12px;">Total</th>

				</tr>
			</thead>
			<tbody>



<?php

$resSQL = "SELECT 
			Ve.XFolio,
			Ve.Fecha,
			Prd.Codigo,
			Prd.Nombre,
			VeS.Descripcion,
			VeS.Cantidad,
			VeS.PrecioUnitario,
			VeS.Importe
		FROM {$prefijobd}valesentrada AS Ve
		LEFT JOIN {$prefijobd}valesentradasub AS VeS ON Ve.ID = VeS.FolioSub_RID
		LEFT JOIN {$prefijobd}productos AS Prd ON Prd.ID = VeS.ProductoEnt_RID
		WHERE Date(Ve.Fecha) BETWEEN ? AND ? AND Ve.OficinaEntrada_RID IN (SELECT ID FROM ".$prefijobd. "Oficinas  WHERE Sucursal_RID = ".$sucursal.")

		ORDER BY Ve.XFolio;
	";
	
	$stmt = $cnx_cfdi3->prepare($resSQL);
	if (!$stmt) {
		die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
	}

	/* if($proveedorID>0){
		$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $proveedorID);
	} else {
		} */
	$stmt->bind_param('ss', $fechaInicio, $fechaFin);
	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result(
		$folio,
		$fecha,
		$codigo,
		$nombre,
		$descripcion,
		$cantidad,
		$precioU,
		$importe );

	$sumCantidad = 0;
	$sumPrecioU = 0;
	$sumSubtotal = 0;
	$sumImpuesto = 0;
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
