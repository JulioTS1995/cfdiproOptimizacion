<?php 
//error_reporting(0);

$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$emisor = $_POST["emisor"];

$fechaInicio = $_POST["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_POST["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));

require_once('cnx_cfdi3.php');

if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
//die($emisor);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte Combustible</title>

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
				<h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte Combustible</h1>
			</div>

	</div>

	<hr>
	
	<div class="row">
		<div class="col-lg-12">
		<div class="row">
	<div class="col-md-12" style="text-align:left">
		<a href="reporteCombustibleExcel.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&proveedor=<?php echo $operadorID; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
	</div>
	<br>
	<br>
	<div class="col-md-12" style="text-align:left">
		<a href="reporteCombustiblePdf.php?fechai=<?php echo $fechaInicio; ?>&fechaf=<?php echo $fechaFin; ?>&prefijodb=<?php echo $prefijobd; ?>&proveedor=<?php echo $operadorID; ?>&emisor=<?php echo $emisor; ?>"><button type="button" class="btn btn-danger">Exporta a PDF</button></a>
	</div>
</div>

			<label>Periodo Consultado: <?php echo $fechaInicio_f." - ".$fechaFin_f; ?> </label>
			<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">Folio</th>
				<th align="center" style="font-size: 12px;">Fecha</th>
				<th align="center" style="font-size: 12px;">Unidad</th>
				<th align="center" style="font-size: 12px;">Operador</th>
				<th align="center" style="font-size: 12px;">NIV</th>
				<th align="center" style="font-size: 12px;">Tanque</th>
				<th align="center" style="font-size: 12px;">LTS</th>
				<th align="center" style="font-size: 12px;">Importe</th>
				<th align="center" style="font-size: 12px;">KM</th>
				<th align="center" style="font-size: 12px;">KM Inicial</th>
				<th align="center" style="font-size: 12px;">KM Final</th>
				<th align="center" style="font-size: 12px;">Rendimiento</th>
				<th align="center" style="font-size: 12px;">LtsECM</th>
				<th align="center" style="font-size: 12px;">KmECM</th>
				<th align="center" style="font-size: 12px;">Rendimiento ECM</th>
				<th align="center" style="font-size: 12px;">Horas de Manejo ECM</th>
				<th align="center" style="font-size: 12px;">Recorrido</th>
				<th align="center" style="font-size: 12px;">Numero Carta Porte</th>
				<th align="center" style="font-size: 12px;">Bono o Descuento</th>
				<th align="center" style="font-size: 12px;">Observaciones</th>

				</tr>
			</thead>
			<tbody>
<?php
	/* if($operadorID>0){
		$filtroProv = "AND GV.OperadorNombre_RID = ?";
	} else {
		$filtroProv = "";
	} */
	
	$resSQL = "SELECT 
			Gv.XFolio,
			Gv.Fecha,
			U.Unidad,
			O.Operador,
			U.NumeroSerie,
			Prd.Nombre,
			Gv.LitrosCombustible,
			Gv.KmsHrsPrevio,
			Gv.KmsHrs,
			Gv.Rendimiento2,
			Gv.LtsEMC,
			Gv.KmEMC,
			Gv.RendEMC,
			Gv.HrsMnjEMC,
			Gv.Recorrido,
			Gv.CartaPorte,
			Gv.BonoDesc,
			Gv.Observaciones,
			Gv.Importe
		FROM {$prefijobd}gastosviajes AS Gv
		LEFT JOIN {$prefijobd}unidades AS U ON U.ID = Gv.Unidad_RID
		LEFT JOIN {$prefijobd}operadores AS O ON O.ID = Gv.OperadorNombre_RID
		LEFT JOIN {$prefijobd}productos AS Prd ON Prd.ID = Gv.FolioSubProductos_RID
	
		WHERE TipoVale = 'Combustible' AND Date(Gv.Fecha) BETWEEN ? AND ? 

		ORDER BY Gv.XFolio;
	";
	
	$stmt = $cnx_cfdi3->prepare($resSQL);
	if (!$stmt) {
		die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
	}

	/*  if($operadorID>0){
		$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $operadorID);
	} else {
		}  */
	$stmt->bind_param('ss', $fechaInicio, $fechaFin);
	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result(
		$folio,
		$fecha,
		$unidad,
		$operador,
		$numeroDeSerie,
		$nombre,
		$litros,
		$KmsHrsPrevio,
		$KmsHrs,
		$rendimiento,
		$ltsECM,
		$kmsECM,
		$rendECM,
		$hrsMnjECM,
		$recorrido,
		$cartaPorte,
		$bonoDesc,
		$observaciones,
		$importe
	);

	$restKmhrs = $KmsHrs;
	/* $sumImpuesto = 0; */
	$sumTotal = 0;
	while ($stmt->fetch()) {

	$fecha = date("d-m-Y", strtotime($fecha));

	$totalKm = $restKmhrs - $KmsHrsPrevio;
	/*$sumImpuesto += $impuesto; */
	//$sumTotal += $importe;

	?>

	<tr>
		<td align="left"><?php echo $folio ?> </td>
		<td align="left"><?php echo $fecha ?> </td>
		<td align="left"><?php echo $unidad ?> </td>
		<td align="left"><?php echo $operador?> </td>
		<td align="left"><?php echo $numeroDeSerie?> </td>
		<td align="left"><?php echo $nombre ?> </td>
		<td align="left"><?php echo $litros ?> </td>
		<td align="left"><?php echo ("$".number_format($importe, 2)) ?> </td>
		<td align="left"><?php echo (number_format($KmsHrs - $KmsHrsPrevio)) ?> </td>
		<td align="left"><?php echo $KmsHrsPrevio ?> </td>
		<td align="left"><?php echo $KmsHrs ?> </td>
		<td align="left"><?php echo $rendimiento ?> </td>
		<td align="left"><?php echo $ltsECM ?> </td>
		<td align="left"><?php echo $kmsECM ?> </td>
		<td align="left"><?php echo $rendECM ?> </td>
		<td align="left"><?php echo $hrsMnjECM ?> </td>
		<td align="left"><?php echo $recorrido ?> </td>
		<td align="left"><?php echo $cartaPorte ?> </td>
		<td align="left"><?php echo $bonoDesc ?> </td>
		<td align="left"><?php echo $observaciones ?> </td>
	</tr>

	<?php
	}
	?>

	<tr>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>

		
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
