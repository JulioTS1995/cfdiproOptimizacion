<?php  

//Recibir variables
$prefijobd = $_POST['prefijodb'];
$fechai = $_POST['txtDesde'];
$fechaf = $_POST['txtHasta'];
$cliente_id = $_POST['cliente'];
$moneda = $_POST['moneda'];
$boton = $_POST['btnEnviar'];

require_once('cnx_cfdi.php');

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$DTotSubTotal = 0;
$DTotIva = 0;
$DTotRetencion = 0;
$DTotNeto = 0;

$DTotSubTotalP = 0;
$DTotIvaP = 0;
$DTotRetencionP = 0;
$DTotNetoP = 0;

////////////////Agregar nombre del Mes

//Seleccionar Mes letra
  switch ("$mes_logs") {
    case '01':
        $mes2 = "Enero";
      break;
    case '02':
        $mes2 = "Febrero";
      break;
    case '03':
        $mes2 = "Marzo";
      break;
    case '04':
        $mes2 = "Abril";
      break;
    case '05':
        $mes2 = "Mayo";
      break;
    case '06':
        $mes2 = "Junio";
      break;
    case '07':
        $mes2 = "Julio";
      break;
    case '08':
        $mes2 = "Agosto";
      break;
    case '09':
        $mes2 = "Septiembre";
      break;
    case '10':
        $mes2 = "Octubre";
      break;
    case '11':
        $mes2 = "Noviembre";
      break;
    case '12':
        $mes2 = "Diciembre";
      break;
    
  } //Fin switch

$fecha = $dia_logs." de ".$mes2." de ". $anio_logs;

$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;
$ba = 0;

//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){
	$RazonSocial = $rowSQL0['RazonSocial'];
	$RFC = $rowSQL0['RFC'];
	$CodigoPostal = $rowSQL0['CodigoPostal'];
	$Calle = $rowSQL0['Calle'];
	$NumeroExterior = $rowSQL0['NumeroExterior'];
	$Colonia = $rowSQL0['Colonia'];
	$Ciudad = $rowSQL0['Ciudad'];
	$Pais = $rowSQL0['Pais'];
	$Estado = $rowSQL0['Estado'];
	$Municipio = $rowSQL0['Municipio'];
}

if($boton == 'PDF' and $moneda == 'PESOS'){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar PDF


require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");


if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente="AND f.CargoAFactura_RID = ".$cliente_id;
}

$sql_moneda = "AND f.Moneda='".$moneda."'";


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Facturas Canceladas</h1>';
       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>

              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha Cancelacion</th>
					  <th align="center" style="font-size: 12px;">Factura</th>
                      <th align="center" style="font-size: 12px;">Cliente</th>
                      <th align="center" style="font-size: 12px;">Moneda</th>
                      <th align="center" style="font-size: 12px;">SubTotal</th>
					  <th align="center" style="font-size: 12px;">IVA</th>
                      <th align="center" style="font-size: 12px;">IVA Retenido</th>
					  <th align="center" style="font-size: 12px;">Neto</th>
                    </tr>
                  </thead>
                  <tbody>';
				  //Agrupar por cliente
					$resSQL00 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." ORDER BY CargoAFactura_RID";
				
				$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
				while($rowSQL00 = mysql_fetch_array($runSQL00)){
					$id_cliente = $rowSQL00['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL08 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
					while($rowSQL08 = mysql_fetch_array($runSQL08)){
						$nom_cliente = $rowSQL08['RazonSocial'];
					}
				

					//Buscar facturas del cliente	
					$resSQL01 = "select f.cCanceladoT,f.XFolio,f.Moneda,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.CargoAFactura_RID FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda."AND CargoAFactura_RID = ".$id_cliente." and f.cCanceladoT IS NOT NULL";
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['cCanceladoT'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Moneda = $rowSQL01['Moneda'];
						$Subtotal_t = $rowSQL01['zSubtotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$IVA_t = $rowSQL01['zImpuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVARetenido_t = $rowSQL01['zRetenido'];
						$IVARetenido = "$".number_format($IVARetenido_t,2);
						$Neto_t = $rowSQL01['zTotal'];
						$Neto = "$".number_format($Neto_t,2);
						$id_cliente = $rowSQL01['CargoAFactura_RID'];
						$ba =1;
                $html.='
                    <tr>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$XFolio.'</td>
                      <td align="left">'.$Cliente.'</td>
					  <td align="center" >'.$Moneda.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right" >'.$IVA.'</td>
                      <td align="right" >'.$IVARetenido.'</td>
					  <td align="right" >'.$Neto.'</td>
                    </tr>

                    ';
					}
					//////Agregar Totales por Clientes
					
					if ($ba == 1 ){
					$resSQL04 = "select sum(zSubtotal)as subtotal, sum(zImpuesto) as IVA, sum(zRetenido) as retenido, sum(zTotal) as importe from ".$prefijobd."factura where Date(Creado) Between '".$fechai."' And '".$fechaf."' AND cCanceladoT is not null AND CargoAFactura_RID=".$id_cliente;
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$TSubtotal_t = $rowSQL04['subtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						$TImpuesto_t = $rowSQL04['IVA'];
						$TImpuesto = "$".number_format($TImpuesto_t,2);
						$TRetenido_t = $rowSQL04['retenido'];
						$TRetenido = "$".number_format($TRetenido_t,2);
						$TImporte_t = $rowSQL04['importe'];
						$TImporte = "$".number_format($TImporte_t,2);
						$ba=0;
					}
					
					$html.='     
						<tr>
						  <td colspan="8"><hr></td>
						</tr>
						<tr>
						  <td colspan="4" align="right"><strong>SUMAS'.$rowSQL01.'</strong></td>
						  <td align="right"><strong>'.$TSubtotal.'</strong></td>
						  <td align="right"><strong>'.$TImpuesto.'</strong></td>
						  <td align="right"><strong>'.$TRetenido.'</strong></td>
						  <td align="right"><strong>'.$TImporte.'</strong></td>
						  
						</tr>
						<tr>
						  <td colspan="8"><hr></td>
						</tr>
					';
						}
					} // FIN del WHILE $resSQL01 
					
              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>

              ';

$html.='</header>';


$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
//$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('reporte_facturas_canceladas_pesos_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar PDF
} elseif($boton == 'Excel' and $moneda == 'PESOS'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_facturas_canceladas_pesos_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

$TotSubTotal = 0;
$TotIva = 0;
$TotRetencion = 0;
$TotNeto = 0;

if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente="AND f.CargoAFactura_RID = ".$cliente_id;
}

$sql_moneda = "AND f.Moneda='".$moneda."'";

?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo $RazonSocial; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "FACTURAS CANCELADAS PESOS DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha Cancelacion</th>
							<th align="center" style="font-size: 12px;">Factura</th>
							<th align="center" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Moneda</th>
							<th align="center" style="font-size: 12px;">SubTotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">IVA Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
						</tr>
					</thead>
					<tbody>
					<?php
					
					//Agrupar por cliente
					$resSQL00 = "SELECT DISTINCT(a.CargoAFactura_RID), b.RazonSocial FROM ".$prefijobd."factura a INNER JOIN ".$prefijobd."clientes b ON a.CargoAFactura_RID = b.ID WHERE Date(a.Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND a.MONEDA='PESOS' AND a.FECreado IS NOT NULL AND a.cCanceladoT IS NOT NULL AND a.CargoAFactura_RID IS NOT NULL ORDER BY b.RazonSocial";
	
					$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
					while($rowSQL00 = mysql_fetch_array($runSQL00)){
					$id_cliente = $rowSQL00['CargoAFactura_RID'];
					
					//Buscar nombre del cliente
					$resSQL08 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
					while($rowSQL08 = mysql_fetch_array($runSQL08)){
						$nom_cliente = $rowSQL08['RazonSocial'];
					}
				

					//Buscar facturas del cliente	
					$resSQL01 = "select f.cCanceladoT,f.XFolio,f.Moneda,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.CargoAFactura_RID FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda."AND CargoAFactura_RID = ".$id_cliente." and f.cCanceladoT IS NOT NULL AND FECreado IS NOT NULL ORDER BY f.XFolio";
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['cCanceladoT'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Moneda = $rowSQL01['Moneda'];
						$Subtotal_t = $rowSQL01['zSubtotal'];
						$Subtotal = number_format($Subtotal_t,2);
						$IVA_t = $rowSQL01['zImpuesto'];
						$IVA = number_format($IVA_t,2);
						$IVARetenido_t = $rowSQL01['zRetenido'];
						$IVARetenido = number_format($IVARetenido_t,2);
						$Neto_t = $rowSQL01['zTotal'];
						$Neto = number_format($Neto_t,2);
						$id_cliente = $rowSQL01['CargoAFactura_RID'];
						$ba =1;
					?>
					
				
                    <tr>
                      <td align="center"><?php echo $Creado; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                      <td align="left"><?php echo $Cliente; ?></td>
					  <td align="center" ><?php echo $Moneda; ?></td>
                      <td align="right"><?php echo $Subtotal; ?></td>
                      <td align="right" ><?php echo $IVA; ?></td>
                      <td align="right" ><?php echo $IVARetenido; ?></td>
					  <td align="right" ><?php echo $Neto; ?></td>
                    </tr>

					<?php
					}
					//////Agregar Totales por Clientes
					
					if ($ba == 1 ){
					$resSQL04 = "select sum(zSubtotal)as subtotal, sum(zImpuesto) as IVA, sum(zRetenido) as retenido, sum(zTotal) as importe from ".$prefijobd."factura where Date(Creado) Between '".$fechai."' And '".$fechaf."'AND cCanceladoT is not null AND MONEDA='PESOS' AND FECreado IS NOT NULL AND CargoAFactura_RID=".$id_cliente;
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$TSubtotal_t = $rowSQL04['subtotal'];
						$TotSubTotal = $TotSubTotal + $TSubtotal_t;
						$TSubtotal = number_format($TSubtotal_t,2);
						
						$TImpuesto_t = $rowSQL04['IVA'];
						$TotIva = $TotIva + $TImpuesto_t;
						$TImpuesto = number_format($TImpuesto_t,2);
						
						$TRetenido_t = $rowSQL04['retenido'];
						$TotRetencion = $TotRetencion + $TRetenido_t;
						$TRetenido = number_format($TRetenido_t,2);
						
						$TImporte_t = $rowSQL04['importe'];
						$TotNeto = $TotNeto + $TImporte_t;
						$TImporte = number_format($TImporte_t,2);
						$ba=0;
					}
				
					?>
						<tr>
						  <td colspan="4" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><?php echo $TSubtotal; ?></td>
						  <td align="right" ><?php echo $TImpuesto; ?></td>
						  <td align="right" ><?php echo $TRetenido; ?></td>
						  <td align="right" ><?php echo $TImporte; ?></td>
						</tr>

<?php
				}
				
				$TotSubTotal_t = number_format($TotSubTotal,2);
				$TotIva_t = number_format($TotIva, 2);
				$TotRetencion_t = number_format($TotRetencion, 2);
				$TotNeto_t = number_format($TotNeto, 2);
				
			}// FIN del WHILE $resSQL01
?>

					<tr>
					  <td colspan="4" align="right"><strong>TOTALES</strong></td>
					  <td align="right"><?php echo $TotSubTotal_t; ?></td>
					  <td align="right" ><?php echo $TotIva_t; ?></td>
					  <td align="right" ><?php echo $TotRetencion_t; ?></td>
					  <td align="right" ><?php echo $TotNeto_t; ?></td>
					</tr>

				</tbody>
             </table>  
      
<?php 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar Excel
}elseif($boton == 'PDF' and $moneda == 'DOLARES'){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar PDF

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");


if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente="AND f.CargoAFactura_RID = ".$cliente_id;
}

$sql_moneda = "AND f.Moneda='".$moneda."'";

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Facturas Canceladas</h1>';
       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>

              <div>
                <table>
                  <thead>
					<tr>
						<th colspan="5"></th>
						<th colspan="4">DOLARES</th>
						<th colspan="4">PESOS</th>
					</tr>
                    <tr>
						
						<th align="center" style="font-size: 12px;">Fecha Cancelacion</th>
						<th align="center" style="font-size: 12px;">Factura</th>
						<th align="left" style="font-size: 12px;">Cliente</th>
						<th align="center" style="font-size: 12px;">Moneda</th>
						<th align="right" style="font-size: 12px;">Tipo de Cambio</th>
						<th align="right" style="font-size: 12px;">SubTotal</th>
						<th align="right" style="font-size: 12px;">IVA</th>
						<th align="right" style="font-size: 12px;">IVA Retenido</th>
						<th align="right" style="font-size: 12px;">Neto</th>
						<th align="right" style="font-size: 12px;">SubTotal</th>
						<th align="right" style="font-size: 12px;">IVA</th>
						<th align="right" style="font-size: 12px;">IVA Retenido</th>
						<th align="right" style="font-size: 12px;">Neto</th>
					</tr>
                  </thead>
                  <tbody>';

					//Buscar facturas del cliente	
					$resSQL01 = "select f.cCanceladoT,f.XFolio,f.Moneda,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.TipoCambio FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda.$sql_cliente." and f.cCanceladoT IS NOT NULL";
					
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['cCanceladoT'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Moneda = $rowSQL01['Moneda'];
						$TipoCambio_t = $rowSQL01['TipoCambio'];
						$TipoCambio = "$".number_format($TipoCambio_t,2);
						$Subtotal_t = $rowSQL01['zSubtotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$SubtotalPesos = "$".number_format($rowSQL01['zSubtotal']*$rowSQL01['TipoCambio'],2);
						$IVA_t = $rowSQL01['zImpuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVAPesos = "$".number_format($rowSQL01['zImpuesto']*$rowSQL01['TipoCambio'],2);
						$IVARetenido_t = $rowSQL01['zRetenido'];
						$IVARetenido = "$".number_format($IVARetenido_t,2);
						$IVARetenidoPesos = "$".number_format($rowSQL01['zRetenido']*$rowSQL01['TipoCambio'],2);
						$Neto_t = $rowSQL01['zTotal'];
						$Neto = "$".number_format($Neto_t,2);
						$NetoPesos = "$".number_format($rowSQL01['zTotal']*$rowSQL01['TipoCambio'],2);
						
                $html.='
                    <tr>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$XFolio.'</td>
                      <td align="left">'.$Cliente.'</td>
					  <td align="center">'.$Moneda.'</td>
					  <td align="right">'.$TipoCambio.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right">'.$IVA.'</td>
                      <td align="right">'.$IVARetenido.'</td>
					  <td align="right">'.$Neto.'</td>
					  <td align="right">'.$SubtotalPesos.'</td>
                      <td align="right">'.$IVAPesos.'</td>
                      <td align="right">'.$IVARetenidoPesos.'</td>
					  <td align="right">'.$NetoPesos.'</td>
                    </tr>

                    ';
					
					} // FIN del WHILE $resSQL01 
					
              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>

              ';

$html.='</header>';


$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
//$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('reporte_facturas_canceladas_dolares_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar PDF
}elseif($boton == 'Excel' and $moneda == 'DOLARES'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_facturas_canceladas_dolares_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");


if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente="AND f.CargoAFactura_RID = ".$cliente_id;
}

$sql_moneda = "AND f.Moneda='".$moneda."'";

?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="13"><?php echo $RazonSocial; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="13"><?php echo "FACTURAS CANCELADAS DOLARES DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th colspan="4"></th>
							<th colspan="4">DOLARES</th>
							<th colspan="5">PESOS</th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha Cancelacion</th>
							<th align="center" style="font-size: 12px;">Factura</th>
							<th align="center" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Moneda</th>
							<th align="center" style="font-size: 12px;">SubTotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">IVA Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
							<th align="center" style="font-size: 12px;">Tipo de Cambio</th>
							<th align="center" style="font-size: 12px;">SubTotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">IVA Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
						</tr>
					</thead>
					<tbody>
					<?php
					
					
					//Agrupar por cliente
					$resSQL000 = "SELECT DISTINCT(a.CargoAFactura_RID), b.RazonSocial FROM ".$prefijobd."factura a INNER JOIN ".$prefijobd."clientes b ON a.CargoAFactura_RID = b.ID WHERE Date(a.Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND a.CargoAFactura_RID IS NOT NULL AND a.MONEDA='DOLARES' AND a.cCanceladoT IS NOT NULL and a.FECreado IS NOT NULL ORDER BY b.RazonSocial";
				    								
					$runSQL000 = mysql_query($resSQL000, $cnx_cfdi);
					while($rowSQL000 = mysql_fetch_array($runSQL000)){
					$id_cliented = $rowSQL000['CargoAFactura_RID'];

					$resSQL01 = "select f.cCanceladoT,f.XFolio,f.Moneda,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.TipoCambio, f.CargoAFactura_RID FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda." AND CargoAFactura_RID = ".$id_cliented." and f.cCanceladoT IS NOT NULL AND f.FECreado IS NOT NULL ORDER BY f.XFolio";
												
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['cCanceladoT'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Moneda = $rowSQL01['Moneda'];
						$TipoCambio_t = $rowSQL01['TipoCambio'];
						$TipoCambio = number_format($TipoCambio_t,2);
						$Subtotal_t = $rowSQL01['zSubtotal'];
						$Subtotal = number_format($Subtotal_t,2);
						$SubtotalPesos = number_format($rowSQL01['zSubtotal']*$rowSQL01['TipoCambio'],2);
						$IVA_t = $rowSQL01['zImpuesto'];
						$IVA = number_format($IVA_t,2);
						$IVAPesos = number_format($rowSQL01['zImpuesto']*$rowSQL01['TipoCambio'],2);
						$IVARetenido_t = $rowSQL01['zRetenido'];
						$IVARetenido = number_format($IVARetenido_t,2);
						$IVARetenidoPesos = number_format($rowSQL01['zRetenido']*$rowSQL01['TipoCambio'],2);
						$Neto_t = $rowSQL01['zTotal'];
						$Neto = number_format($Neto_t,2);
						$NetoPesos = number_format($rowSQL01['zTotal']*$rowSQL01['TipoCambio'],2);
						$ba =1;
					?>
					
				
                    <tr>
                      <td align="center"><?php echo $Creado; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                      <td align="left"><?php echo $Cliente; ?></td>
					  <td align="center" ><?php echo $Moneda; ?></td>
                      <td align="right"><?php echo $Subtotal; ?></td>
                      <td align="right" ><?php echo $IVA; ?></td>
                      <td align="right" ><?php echo $IVARetenido; ?></td>
					  <td align="right" ><?php echo $Neto; ?></td>
					  <td align="right" ><?php echo $TipoCambio; ?></td>
					  <td align="right"><?php echo $SubtotalPesos; ?></td>
                      <td align="right" ><?php echo $IVAPesos; ?></td>
                      <td align="right" ><?php echo $IVARetenidoPesos; ?></td>
					  <td align="right" ><?php echo $NetoPesos; ?></td>
                    </tr>

					<?php
					}// FIN del WHILE $resSQL01
					
					//////Agregar Totales por Clientes
					
					if ($ba == 1 ){
					$resSQL004 = "select sum(zSubtotal)as subtotal, sum(zImpuesto) as IVA, sum(zRetenido) as retenido, sum(zTotal) as importe, 
					sum(zSubtotal*TipoCambio) as subtotalpesos, sum(zImpuesto*TipoCambio) as ivapesos, sum(zRetenido*TipoCambio) as retenidopesos, sum(zTotal*TipoCambio) as netopesos from ".$prefijobd."factura where Date(Creado) Between '".$fechai."' And '".$fechaf."' AND cCanceladoT is not null AND Moneda= '".$moneda."' AND FECreado IS NOT NULL AND CargoAFactura_RID=".$id_cliented;
					
					$runSQL004 = mysql_query($resSQL004, $cnx_cfdi);
					while($rowSQL004 = mysql_fetch_array($runSQL004)){
						$TSubtotal_t = $rowSQL004['subtotal'];
						$DTotSubTotal = $DTotSubTotal + $TSubtotal_t;
						$TSubtotal = number_format($TSubtotal_t,2);
						
						$TImpuesto_t = $rowSQL004['IVA'];
						$DTotIva = $DTotIva + $TImpuesto_t;
						$TImpuesto = number_format($TImpuesto_t,2);
						
						$TRetenido_t = $rowSQL004['retenido'];
						$DTotRetencion = $DTotRetencion + $TRetenido_t;
						$TRetenido = number_format($TRetenido_t,2);
						
						$TImporte_t = $rowSQL004['importe'];
						$DTotNeto = $DTotNeto + $TImporte_t;
						$TImporte = number_format($TImporte_t,2);
						
						$SubtotalPesos_P = $rowSQL004['subtotalpesos'];
						$DTotSubTotalP = $DTotSubTotalP + $SubtotalPesos_P;
						$SubtotalPesos_Pt = number_format($SubtotalPesos_P,2);
						
						$IvaPesos_P = $rowSQL004['ivapesos'];
						$DTotIvaP = $DTotIvaP + $IvaPesos_P;
						$IvaPesos_Pt = number_format($IvaPesos_P, 2);
						
						$RetenidoPesos_P = $rowSQL004['retenidopesos'];
						$DTotRetencionP = $DTotRetencionP + $RetenidoPesos_P;
						$RetenidoPesos_Pt = number_format($RetenidoPesos_P, 2);
						
						$NetoPesos_P = $rowSQL004['netopesos'];
						$DTotNetoP = $DTotNetoP + $NetoPesos_P;
						$NetoPesos_Pt = number_format($NetoPesos_P, 2);
						$ba=0;
					}
				
					?>
						<tr>
						  <td colspan="4" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><?php echo $TSubtotal; ?></td>
						  <td align="right" ><?php echo $TImpuesto; ?></td>
						  <td align="right" ><?php echo $TRetenido; ?></td>
						  <td align="right" ><?php echo $TImporte; ?></td>
						  <td align="right" ></td>
						  <td align="right" ><?php echo $SubtotalPesos_Pt; ?></td>
						  <td align="right" ><?php echo $IvaPesos_Pt; ?></td>
						  <td align="right" ><?php echo $RetenidoPesos_Pt; ?></td>
						  <td align="right" ><?php echo $NetoPesos_Pt; ?></td>
						</tr>
			
					<?php
					}
					}
					
					$DTotSubTotal_t = number_format($DTotSubTotal, 2);
					$DTotIva_t  = number_format($DTotIva, 2);
					$DTotRetencion_t  = number_format($DTotRetencion, 2);
					$DTotNeto_t = number_format($DTotNeto, 2);
					
					$DTotSubTotalP_tt = number_format($DTotSubTotalP, 2);
					$DTotIvaP_tt = number_format($DTotIvaP, 2);
					$DTotRetencionP_tt = number_format($DTotRetencionP, 2);
					$DTotNetoP_tt = number_format($DTotNetoP, 2);
					
					?>
					
					<td colspan="4" align="right"><strong>TOTALES</strong></td>
					  <td align="right"><?php echo $DTotSubTotal_t; ?></td>
					  <td align="right" ><?php echo $DTotIva_t; ?></td>
					  <td align="right" ><?php echo $DTotRetencion_t; ?></td>
					  <td align="right" ><?php echo $DTotNeto_t; ?></td>
					  <td align="right" ></td>
					  <td align="right" ><?php echo $DTotSubTotalP_tt; ?></td>
					  <td align="right" ><?php echo $DTotIvaP_tt; ?></td>
					  <td align="right" ><?php echo $DTotRetencionP_tt; ?></td>
					  <td align="right" ><?php echo $DTotNetoP_tt; ?></td>
					</tr>
					
					</tbody>
				</table>

<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar Excel
}

?>