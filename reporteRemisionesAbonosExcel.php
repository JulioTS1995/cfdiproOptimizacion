<?php
header("Content-type: application/vnd.ms-excel");
$nombre="Viajes_en_abonos_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");



require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];

$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));

$unidad = $_GET["unidad"];
$operador = $_GET["operador"];
$serie = $_GET["serie"];
$cliente = $_GET["cliente"];


?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">CLIENTE</th>
				<th align="center" style="font-size: 12px;">MONEDA</th>
				<th align="center" style="font-size: 12px;">XFOLIO</th>
				<th align="center" style="font-size: 12px;">TICKET</th>
				<th align="center" style="font-size: 12px;">UUID</th>
				<th align="center" style="font-size: 12px;">CREADO</th>
				<th align="center" style="font-size: 12px;">UNIDAD</th>
				<th align="center" style="font-size: 12px;">PLACAS</th>
				<th align="center" style="font-size: 12px;">REMOLQUE</th>
				<th align="center" style="font-size: 12px;">REM PLACAS</th>
				<th align="center" style="font-size: 12px;">REMOLQUE 2</th>
				<th align="center" style="font-size: 12px;">REM 2 PLACAS</th>
				<th align="center" style="font-size: 12px;">OPERADOR</th>
				<th align="center" style="font-size: 12px;">RUTA</th>
				<th align="center" style="font-size: 12px;">REMITENTE</th>
				<th align="center" style="font-size: 12px;">DESTINATARIO</th>
				<th align="center" style="font-size: 12px;">SE FACTURO EN</th>
				<th align="center" style="font-size: 12px;">SUBTOTAL</th>
				<th align="center" style="font-size: 12px;">IVA</th>
				<th align="center" style="font-size: 12px;">RETENCION</th>
				<th align="center" style="font-size: 12px;">TOTAL</th>
				<th align="center" style="font-size: 12px;">ABONO</th>
				<th align="center" style="font-size: 12px;">LIQUIDACION</th>
				<th align="center" style="font-size: 12px;">PESO TOTAL</th>
				<th align="center" style="font-size: 12px;">FLETE</th>
				<th align="center" style="font-size: 12px;">SUBTOTAL</th>
				<th align="center" style="font-size: 12px;">IMPUESTO</th>
				<th align="center" style="font-size: 12px;">RETENIDO</th>
				<th align="center" style="font-size: 12px;">TOTAL</th>
				<th align="center" style="font-size: 12px;">DOCUMENTADOR</th>
				<th align="center" style="font-size: 12px;">CANTIDAD TOTAL</th>

				</tr>
			</thead>
			<tbody>



<?php
if($unidad>0){
	$filtroUnidad = " AND R.Unidad_RID = {$unidad} ";
} else {
	$filtroUnidad = "";
}

if($operador>0){
	$filtroOperador = " AND R.Operador_RID = {$operador} ";
} else {
	$filtroOperador = "";
}

if($cliente>0){
	$filtroCliente = " AND R.CargoACliente_RID = {$Cliente} ";
} else {
	$filtroCliente = "";
}

if($serie>0){
	$filtroSerie = " AND R.Oficina_RID = {$Serie} ";
} else {
	$filtroSerie = "";
}

$resSQL = "SELECT 
    C.RazonSocial AS Cliente,
    R.Moneda,
    R.XFolio,
    R.RemisionOperador,
    R.cfdiuuid,
    R.Creado,
    U.Unidad,
    U.Placas,
    Rem.Unidad AS Remolque,
    Rem.Placas AS RemolquePlacas,
    Rem2.Unidad AS Remolque2,
    Rem2.Placas AS Remolque2Placas,
    O.Operador,
    Ru.Ruta,
    R.Remitente,
    R.Destinatario,
    R.SeFacturoEn,
    R.Liquidacion,
    R.xPesoTotal,
    R.yFlete,
    R.zSubtotal,
    R.zImpuesto,
    R.zRetenido,
    R.zTotal,
    R.Documentador,
    R.xCantidadTotal,
    A.XFolio AS Abono,
	F.zSubtotal AS SubtotalFact,
	F.zTotal AS TotalFact,
	F.zImpuesto AS ImpuestoFact,
	F.zRetenido AS RetenidoFact
FROM {$prefijobd}Remisiones AS R
LEFT JOIN {$prefijobd}Clientes AS C ON C.ID = R.CargoACliente_RID
LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = R.Unidad_RID
LEFT JOIN {$prefijobd}Unidades AS Rem ON Rem.ID = R.uRemolqueA_RID
LEFT JOIN {$prefijobd}Unidades AS Rem2 ON Rem2.ID = R.uRemolqueB_RID
LEFT JOIN {$prefijobd}Operadores AS O ON O.ID = R.Operador_RID
LEFT JOIN {$prefijobd}Rutas AS Ru ON Ru.ID = R.Ruta_RID
LEFT JOIN {$prefijobd}FacturasDetalle AS FD ON FD.Remision_RID = R.ID
LEFT JOIN {$prefijobd}Factura AS F ON F.ID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}AbonosSub AS ABS ON ABS.AbonoFactura_RID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}Abonos AS A ON A.ID = ABS.FolioSub_RID
WHERE Date(R.Creado) BETWEEN ? AND ? 
{$filtroUnidad}
{$filtroOperador}
{$filtroCliente}
{$filtroSerie}
ORDER BY R.XFolio;
";

$stmt = $cnx_cfdi3->prepare($resSQL);
if (!$stmt) {
	die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
}


$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result(
	$cliente,
	$moneda,
	$xfolio,
	$ticket,
	$uuid,
	$creado,
	$unidad,
	$placas,
	$remolque,
	$remolquePlacas,	
	$remolque2,
	$remolque2Placas,
	$operador,
	$ruta,
    $remitente,
	$destinatario,
	$seFacturoEn,
	$liquidacion,
	$pesoTotal,
	$flete,
	$subtotal,
	$impuesto,
	$retencion,
	$total,
	$documentador,
	$cantidadTotal,
	$abono,
	$subtotalFact,
	$totalFact,
	$impuestoFact,
	$retenidoFact
);


while ($stmt->fetch()) {

	$creado = (!empty($creado) && $creado != '0000-00-00') ? date("d-m-Y", strtotime($creado)) : '';
	$fechaDoc = (!empty($fechaDoc) && $fechaDoc != '0000-00-00') ? date("d-m-Y", strtotime($fechaDoc)) : '';


		?>

	<tr>
		<td align="left"><?php echo $cliente ?> </td>
		<td align="left"><?php echo $moneda ?> </td>
		<td align="left"><?php echo $xfolio ?> </td>
		<td align="left"><?php echo $ticket ?> </td>
		<td align="left"><?php echo $uuid ?> </td>
		<td align="left"><?php echo $creado ?> </td>
		<td align="left"><?php echo $unidad ?> </td>
		<td align="left"><?php echo $placas ?> </td>
		<td align="left"><?php echo $remolque ?> </td>
		<td align="left"><?php echo $remolquePlacas ?> </td>
		<td align="left"><?php echo $remolque2 ?> </td>
		<td align="left"><?php echo $remolque2Placas ?> </td>
		<td align="left"><?php echo $operador ?> </td>
		<td align="left"><?php echo $ruta ?> </td>
		<td align="left"><?php echo $remitente ?> </td>
		<td align="left"><?php echo $destinatario ?> </td>
		<td align="left"><?php echo $seFacturoEn ?> </td>
		<td align="left"><?php echo ("$".number_format($subtotalFact,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($impuestoFact,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($retenidoFact,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($totalFact,2)) ?> </td>
		<td align="left"><?php echo $abono ?> </td>
		<td align="left"><?php echo $liquidacion ?> </td>
		<td align="left"><?php echo $pesoTotal ?> </td>
		<td align="left"><?php echo ("$".number_format($flete,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($subtotal,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($impuesto,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($retencion,2)) ?> </td>
		<td align="left"><?php echo ("$".number_format($total,2)) ?> </td>
		<td align="left"><?php echo $documentador ?> </td>
		<td align="left"><?php echo $cantidadTotal ?> </td>

	</tr>

<?php
}
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
