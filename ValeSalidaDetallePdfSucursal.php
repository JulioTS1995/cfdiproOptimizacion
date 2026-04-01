<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('lib_mpdf/pdf/mpdf.php');

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

$prefijobd = $_GET['prefijodb'];
/* $proveedorID = $_GET["proveedor"];
 */
$sucursal = $_GET ['sucursal'];
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
	<h3><strong>Vales de Salida Detalle</h3>
    <label>Periodo Consultado: '.$fechaInicio_f." - ".$fechaFin_f.' </label>
</div>

<table class="table table-hover table-responsive table-condensed" id="table">
			<thead>
				<tr>
				<th align="center" style="font-size: 12px;">Folio</th>
				<th align="center" style="font-size: 12px;">Fecha</th>
				<th align="center" style="font-size: 12px;">Unidad</th>
				<th align="center" style="font-size: 12px;">Codigo</th>
				<th align="center" style="font-size: 12px;">Nombre</th>
				<th align="center" style="font-size: 12px;">Descripcion</th>
				<th align="center" style="font-size: 12px;">Cantidad</th>
				<th align="center" style="font-size: 12px;">Precio</th>
				<th align="center" style="font-size: 12px;">Total</th>

				</tr>
			</thead>
			<tbody>';

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
			

			
				$html .= '<tr>
					<td align="left">'.$folio.'</td>
					<td align="left">'.$fecha.'</td>
					<td align="left">'.$unidad.'</td>
					<td align="left">'.$codigo.'</td>
					<td align="left">'.$nombre.'</td>
					<td align="left">'.$descripcion.'</td>
					<td align="left">'.$cantidad.'</td>
					<td align="left">'.("$".number_format($precioU,2)).'</td>
					<td align="left">'.("$".number_format($importe,2)).'</td>
				</tr>';



			}

			
				$html .= '<tr>
					<td align="center">TOTAL:</td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center"> </td>
					<td align="center">'.$sumCantidad.'</td>
					<td align="center">'.("$".number_format($sumPrecioU,2)).'</td>
					<td align="center">'.("$".number_format($sumTotal,2)).'</td>
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
$mpdf->Output('Vale_Salida_detalle_'.date("d-m-Y")."_".date("h:i").'.pdf', 'D');

?>