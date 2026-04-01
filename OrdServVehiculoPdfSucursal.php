<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('lib_mpdf/pdf/mpdf.php');

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
<div style="text-align: left;">
    <img src="'.$rutaLogo.'" alt="Logo" style="width: 60px; height: 60px;">
</div>
<div style="text-align: center;">
	<h3><strong>Ordenes de Servicio por Vehiculo</h3>
    <label>Periodo Consultado: '.$fechaInicio_f." - ".$fechaFin_f.' </label>
</div>


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
			<tbody>';

			if($unidadID>0){
				$filtroUnidad = "AND C.Unidad_RID = ?";
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
			WHERE DATE(M.Fecha) BETWEEN ? AND ?  AND M.OficinaMant_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." )
			{$filtroUnidad}
			GROUP BY U.Unidad;";
				
				$stmt = $cnx_cfdi3->prepare($resSQL);
				if (!$stmt) {
					die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
				}
			
				if($unidadID>0){
					$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $unidadID);
				} else {
					$stmt->bind_param('ss', $fechaInicio, $fechaFin);
				}
				if (!$stmt->execute()) {
					die("Error al ejecutar la consulta: " . $stmt->error);
				}
				$stmt->store_result();
			
				$stmt->bind_result(
					$xfolio,
					$fecha,
					$unidad,
					$kms,
					$reparacion,
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
			

			
				$html .= '<tr>
					<td align="center">'.$xfolio.'</td>
					<td align="left">'.$fecha.'</td>
					<td align="left">'.$unidad.'</td>
					<td align="left">'.$kms.'</td>
					<td align="left">'.$reparacion.'</td>
					<td align="left">'.$taller.'</td>
					<td align="left">'.("$".number_format($subtotal,2)).'</td>
					<td align="left">'.("$".number_format($impuesto,2)).'</td>
					<td align="left">'.("$".number_format($total,2)).'</td>
				</tr>';
			

				}

			
				$html .= '<tr>
					<td align="center">TOTAL:</td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="left">'.("$".number_format($sumSubtotal,2)).'</td>
					<td align="left">'.("$".number_format($sumImpuesto,2)).'</td>
					<td align="left">'.("$".number_format($sumTotal,2)).'</td>
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
$mpdf->Output('compras_detalle_'.date("d-m-Y")."_".date("h:i").'.pdf', 'D');

?>