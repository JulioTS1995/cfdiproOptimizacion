<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('lib_mpdf/pdf/mpdf.php');

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
$proveedorID = $_GET["proveedor"];
$emisor = $_GET['emisor'];


$fechaInicio = $_GET["fechai"];
$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin = $_GET["fechaf"];
$fechaFin_f = date("d-m-Y", strtotime($fechaFin));

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

////////////////////////////////////////////////////////Reporte en Excel

$html='<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">


<div style="text-align: left;">
    <img src="'.$rutaLogo.'" alt="Logo" style="width: 60px; height: 60px;">
</div>
<div style="text-align: center;">
	<h3><strong>Reporte Combustible</h3>
    <label>Periodo Consultado: '.$fechaInicio_f." - ".$fechaFin_f.' </label>
</div>
<br>
<br>
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
			<tbody>';

			if($operadorID>0){
				$filtroProv = "AND GV.OperadorNombre_RID = ?";
			} else {
				$filtroProv = "";
			}
			
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
		
			 if($operadorID>0){
				$stmt->bind_param('ssi', $fechaInicio, $fechaFin, $operadorID);
			} else {
				} 
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
		

			/* $sumImpuesto = 0; */
			$sumTotal = 0;
			while ($stmt->fetch()) {
		
			$fecha = date("d-m-Y", strtotime($fecha));

			/*$sumImpuesto += $impuesto; */
			$sumTotal += $importe;
			

			
				$html .= '<tr>
							<td align="left">'  .$folio . ' </td>
							<td align="left">'  .$fecha . ' </td>
							<td align="left">'  .$unidad . ' </td>
							<td align="left">'  .$operador. ' </td>
							<td align="left">'  .$numeroDeSerie. ' </td>
							<td align="left">'  .$nombre . ' </td>
							<td align="left">'  .$litros . ' </td>
							<td align="left">$'  .(number_format($importe, 2)). '</td>
							<td align="left">' .(number_format($KmsHrs - $KmsHrsPrevio)) . ' </td>
							<td align="left">'  .$KmsHrsPrevio . ' </td>
							<td align="left">'  .$KmsHrs . ' </td>
							<td align="left">'  .$rendimiento . ' </td>
							<td align="left">'  .$ltsECM . ' </td>
							<td align="left">'  .$kmsECM . ' </td>
							<td align="left">'  .$rendECM . ' </td>
							<td align="left">'  .$hrsMnjECM . ' </td>
							<td align="left">'  .$recorrido . ' </td>
							<td align="left">'  .$cartaPorte . ' </td>
							<td align="left">'  .$bonoDesc . ' </td>
							<td align="left">'  .$observaciones . ' </td>  
			        	</tr>';
				}

			
				$html .= '	<tr>
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
$mpdf->Output('reporte_Combustible_'.date("d-m-Y")."_".date("h:i").'.pdf', 'D');

?>