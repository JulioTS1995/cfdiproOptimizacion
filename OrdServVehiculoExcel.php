<?php
header("Content-type: application/vnd.ms-excel");
$nombre="orden_servicio_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");



require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
$unidadID = $_GET["unidad"];


$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));


?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



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
				</tr>
			</thead>
			<tbody>



<?php
if($unidadID>0){
	$filtroUnidad = "AND C.unidadNo_RID = ?";
} else {
	$filtroUnidad = "";
}

$resSQL = "SELECT 
M.XFolio,
M.Fecha,
U.Unidad,
Ms.Kilometros,
R.Reparacion,
T.Taller,
SUM(VS.Subtotal) AS Subtotal,
SUM(VS.Impuesto) AS Impuesto,
SUM(VS.Total) AS Total
FROM {$prefijobd}MantenimientosSub AS Ms
LEFT JOIN {$prefijobd}Mantenimientos AS M ON M.ID = Ms.FolioSub_RID
LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = M.UnidadMantenimiento_RID
LEFT JOIN {$prefijobd}Reparaciones AS R ON R.ID = Ms.Reparacion_RID
LEFT JOIN {$prefijobd}Talleres AS T ON T.ID = Ms.Taller_RID
LEFT JOIN {$prefijobd}ValesSalida AS VS ON VS.MantVSalida_RID = M.ID
WHERE DATE(M.Fecha) BETWEEN ? AND ? 
{$filtroUnidad}
GROUP BY  U.Unidad;
;";

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
	$total
);

$sumSubtotal = 0;
$sumImpuesto = 0;
$sumTotal = 0;
while ($stmt->fetch()) {

$fecha = date("d-m-Y", strtotime($fecha));
$sumSubtotal += $subtotal;
$sumImpuesto += $impuesto;
$sumTotal += $total;

?>

<tr>
	<td align="center"><?php echo $folio ?> </td>
	<td align="center"><?php echo $fecha ?> </td>
	<td align="center"><?php echo $unidad ?> </td>
	<td align="center"><?php echo $kms ?> </td>
	<td align="center"><?php echo $servicio ?> </td>
	<td align="center"><?php echo $taller ?> </td>
	<td align="center"><?php echo ("$".number_format($subtotal,2)) ?> </td>
	<td align="center"><?php echo ("$".number_format($impuesto,2)) ?> </td>
	<td align="center"><?php echo ("$".number_format($total,2)) ?> </td>
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
	<td align="center"><?php echo ("$".number_format($sumSubtotal,2)) ?> </td>
	<td align="center"><?php echo ("$".number_format($sumImpuesto,2)) ?> </td>
	<td align="center"><?php echo ("$".number_format($sumTotal,2)) ?> </td>
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