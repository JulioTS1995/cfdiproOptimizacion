<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('lib_mpdf/pdf/mpdf.php');
date_default_timezone_set("America/Mexico_City");
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
$unidadID = $_GET["unidad"];
$sucursal = $_GET["sucursal"];//trae sucursal
$emisor = $_GET['emisor'];
$resSQLEmisor = "SELECT RutaLogo FROM {$prefijobd}Emisores WHERE ID = ?;";
			
$stmtEmisor = $cnx_cfdi3->prepare($resSQLEmisor);
if (!$stmtEmisor) {
	die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
}


$stmtEmisor->bind_param('i', $emisor);
$stmtEmisor->execute();
$stmtEmisor->store_result();

$stmtEmisor->bind_result($rutaLogo);
$stmtEmisor->fetch();


$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));


////////////////////////////////////////////////////////Reporte en Excel

$html='<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<div style="position: relative; width: 100%;">
    
    <div style="text-align: left; display: inline-block;">
        <img src="'.$rutaLogo.'" alt="Logo" style="width: 60px; height: 60px;">
    </div>

    
    <div style="position: absolute; right: 0; text-align: right;">
		<div><strong>Fecha:</strong> ' . date("d/m/Y") . '</div>
		<div><strong>Hora:</strong> ' . date("H:i:s") . '</div>
    </div>
</div>
<div style="text-align: center;">
	<h3><strong>Ordenes de Servicio por Vehiculo Detalle</h3>
    <label>Periodo Consultado: '.$fechaInicio_f." - ".$fechaFin_f.' </label>
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
			<tbody>';

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
			
				$sumSubtotal += $subtotal;
				$sumImpuesto += $impuesto;
				$sumTotal += $total;
				$sumCantidad += $cantidad;
				$sumImporte += $importe;
				$sumImporteIVA += $importeIVA;
				$sumImporteTotal += $importeTotal;
			
				if ($folio != $ultimoFolio) {
					$html .= '<tr>
						<td align="center">'.$folio.'</td>
						<td align="center">'.$fecha.'</td>
						<td align="center">'.$unidad.'</td>
						<td align="center">'.$kms.'</td>
						<td align="center">'.$duracion.'</td>
						<td align="center">'.$servicio.'</td>
						<td align="center">'.$taller.'</td>
						<td align="center">'.$mecanico.'</td>
						<td align="center">'.("$".number_format($subtotal,2)).'</td>
						<td align="center">'.("$".number_format($impuesto,2)).'</td>
						<td align="center">'.("$".number_format($total,2)).'</td>
						<td align="center">'.$nombre.'</td>
						<td align="center">'.$cantidad.'</td>
						<td align="center">'.("$".number_format($precioUnitario,2)).'</td>
						<td align="center">'.("$".number_format($importe,2)).'</td>
						<td align="center">'.("$".number_format($importeIVA,2)).'</td>
						<td align="center">'.("$".number_format($importeTotal,2)).'</td>
					</tr>';
				} else {
					$html .= '<tr>
						<td colspan="11"></td> 
						<td align="center">'.$nombre.'</td>
						<td align="center">'.$cantidad.'</td>
						<td align="center">'.("$".number_format($precioUnitario,2)).'</td>
						<td align="center">'.("$".number_format($importe,2)).'</td>
						<td align="center">'.("$".number_format($importeIVA,2)).'</td>
						<td align="center">'.("$".number_format($importeTotal,2)).'</td>
					</tr>';
				}
			
				$ultimoFolio = $folio;
			}

			
				$html .= '<tr>
		<td align="center">TOTAL:</td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center"> </td>
		<td align="center">$'.number_format($sumSubtotal,2).'</td>
		<td align="center">$'.number_format($sumImpuesto,2).'</td>
		<td align="center">$'.number_format($sumTotal,2).'</td>
		<td align="center"> </td>
		<td align="center"> '.$sumCantidad.'</td>
		<td align="center"> </td>
		<td align="center">$'.number_format($sumImporte,2).'</td>
		<td align="center">$'.number_format($sumImporteIVA,2).'</td>
		<td align="center">$'.number_format($sumImporteTotal,2).'</td>
				</tr>';

			$stmt->free_result();
			$stmt->close();
			$cnx_cfdi3->close();
			
			
			$html .= '</tbody>
			</table>
			</div>
			</div>
			<br>
			
			<br><br>
			
			</div>';
			

$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('orden_servicio_detalle_'.date("d-m-Y")."_".date("h:i").'.pdf', 'D');

?>