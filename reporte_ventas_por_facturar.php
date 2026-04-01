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
      <h1 style="font-size: 20px;">Ventas Por Facturar</h1>';
       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>

              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha</th>
					  <th align="center" style="font-size: 12px;">Folio</th>
                      <th align="left" style="font-size: 12px;">Cliente</th>
                      <th align="center" style="font-size: 12px;">Tracking</th>
                      <th align="center" style="font-size: 12px;">Carta Porte</th>
                      <th align="right" style="font-size: 12px;">SubTotal</th>
					  <th align="right" style="font-size: 12px;">IVA</th>
                      <th align="right" style="font-size: 12px;">IVA Retenido</th>
					  <th align="right" style="font-size: 12px;">Neto</th>
					  <th align="right" style="font-size: 12px;">Estatus Cobranza</th>
                    </tr>
                  </thead>
                  <tbody>';

					//Buscar facturas del cliente	
					$resSQL01 = "select f.Creado,f.XFolio,f.Ticket,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,r.XFolio as CartaPorte, f.EstatusCobranza as EstatusCobranza FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					left join ".$prefijobd."facturasdetalle d on d.FolioSubDetalle_RID=f.ID
					left join ".$prefijobd."remisiones r on d.Remision_RID= r.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda.$sql_cliente." and f.FECreado IS NOT NULL";
					
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['Creado'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Tracking = $rowSQL01['Ticket'];
						$CartaPorte = $rowSQL01['CartaPorte'];
						$Subtotal_t = $rowSQL01['zSubtotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$IVA_t = $rowSQL01['zImpuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVARetenido_t = $rowSQL01['zRetenido'];
						$IVARetenido = "$".number_format($IVARetenido_t,2);
						$Neto_t = $rowSQL01['zTotal'];
						$Neto = "$".number_format($Neto_t,2);
						$EstatusCobranza = $rowSQL01['EstatusCobranza'];
						
                $html.='
                    <tr>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$XFolio.'</td>
                      <td align="left">'.$Cliente.'</td>
					  <td align="center" >'.$Tracking.'</td>
                      <td align="center">'.$CartaPorte.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right" >'.$IVA.'</td>
                      <td align="right" >'.$IVARetenido.'</td>
					  <td align="right" >'.$Neto.'</td>
					  <td align="left" >'.$EstatusCobranza.'</td>
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
$mpdf->Output('reporte_ventas_por_folio_pesos_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar PDF
} elseif($boton == 'Excel' and $moneda == 'PESOS'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
	
	$totsubtotal = 0;
	$totiva = 0;
	$totretenido = 0;
	$totales = 0;
	
	header("Content-type: application/vnd.ms-excel");
	$nombre="reporte_ventas_por_folio_pesos_".date("h:i:s")."_".date("d-m-Y").".xls";
	header("Content-Disposition: attachment; filename=$nombre");
	require_once('lib_mpdf/pdf/mpdf.php');

	require_once('cnx_cfdi.php');
	require_once('lib_mpdf/pdf/mpdf.php');
	mysql_select_db($database_cfdi, $cnx_cfdi);

	mysql_query("SET NAMES 'utf8'");

	if($cliente_id == 0){
		$sql_cliente="";
	}else {
		$sql_cliente="AND f.CargoAFactura_RID = ".$cliente_id;
	}

	$sql_moneda = "AND f.Moneda='".$moneda."'";
	
?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

	<table class="table table-hover table-responsive table-condensed" border="1" id="table">
	<thead>
		<tr>
			<th align="center" style="font-size: 12px;" colspan="11"><?php echo $RazonSocial; ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;" colspan="11"><?php echo "VENTAS POR FOLIO PESOS DEL: ".$fechai." AL: ".$fechaf; ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;">Fecha</th>
			<th align="center" style="font-size: 12px;">Folio</th>
			<th align="center" style="font-size: 12px;">Cliente</th>
			<th align="center" style="font-size: 12px;">Tracking</th>
			<th align="center" style="font-size: 12px;">Carta Porte</th>
			<th align="center" style="font-size: 12px;">SubTotal</th>
			<th align="center" style="font-size: 12px;">IVA</th>
            <th align="center" style="font-size: 12px;">IVA Retenido</th>
			<th align="center" style="font-size: 12px;">Neto</th>
			<th align="center" style="font-size: 12px;">Cancelado</th>
			<th align="center" style="font-size: 12px;">Estatus Cobranza</th>
		</tr>
	</thead>
	<tbody>

<?php
					
	$resSQL01 = "select f.Creado,f.XFolio,f.Ticket,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,r.XFolio as CartaPorte, f.cCanceladoT as cCanceladoT, f.Estatuscobranza as EstatusCobranza FROM ".$prefijobd."factura as f inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
	left join ".$prefijobd."facturasdetalle d on d.FolioSubDetalle_RID=f.ID
	left join ".$prefijobd."remisiones r on d.Remision_RID= r.ID
	where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda.$sql_cliente." AND f.FECreado IS NOT NULL ORDER BY f.XFolio";

	$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
	while($rowSQL01 = mysql_fetch_array($runSQL01)){
		$Creado_t = $rowSQL01['Creado'];
		$Creado = date("d-m-Y", strtotime($Creado_t));
		$XFolio = $rowSQL01['XFolio'];
		$Cliente = $rowSQL01['RazonSocial'];
		$Tracking = $rowSQL01['Ticket'];
		$CartaPorte = '';
		$Subtotal_t = $rowSQL01['zSubtotal'];
		$totsubtotal = $totsubtotal + $Subtotal_t;
		$Subtotal = number_format($Subtotal_t, 2, '.', ',');
		$IVA_t = $rowSQL01['zImpuesto'];
		$totiva = $totiva + $IVA_t;
		$IVA = number_format($IVA_t,2);
		$IVARetenido_t = $rowSQL01['zRetenido'];
		$totretenido = $totretenido + $IVARetenido_t;
		$IVARetenido = number_format($IVARetenido_t,2);
		$Neto_t = $rowSQL01['zTotal'];
		$totales = $totales + $Neto_t;
		$Neto = number_format($Neto_t, 2, '.', ',');
		$FechaCancelado = $rowSQL01['cCanceladoT'];
		$EstatusCobranza = $rowSQL01['EstatusCobranza'];
						
?>
        <tr>
			<td align="center"><?php echo $Creado; ?></td>
            <td align="center"><?php echo $XFolio; ?></td>
            <td align="left"><?php echo $Cliente; ?></td>
			<td align="center" ><?php echo $Tracking; ?></td>
            <td align="center"><?php echo $CartaPorte; ?></td>
            <td align="right"><?php echo $Subtotal; ?></td>
            <td align="right" ><?php echo $IVA; ?></td>
            <td align="right" ><?php echo $IVARetenido; ?></td>
			<td align="right" ><?php echo $Neto; ?></td>
			<td align="right" ><?php echo $FechaCancelado; ?></td>
			<td align="left" ><?php echo $EstatusCobranza; ?></td>
        </tr>
<?php
					
	}// FIN del WHILE $resSQL01
	
	$totsubtotal_t = number_format($totsubtotal, 2);
	$totiva_t = number_format($totiva, 2);
	$totretenido_t = number_format($totretenido, 2);
	$totales_t = number_format($totales, 2);
				
?>
	
	<tr>
		<td colspan="5" align="right"><strong>TOTALES</strong></td>
		<td align="right"><strong><?php echo $totsubtotal_t; ?></strong></td>
		<td align="right"><strong><?php echo $totiva_t; ?></strong></td>
		<td align="right"><strong><?php echo $totretenido_t; ?></strong></td>
		<td align="right"><strong><?php echo $totales_t; ?></strong></td>
		<td colspan="2"></td>
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
      <h1 style="font-size: 20px;">Ventas Por Facturar</h1>';
       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>

              <div>
                <table>
                  <thead>
					<tr>
						<th colspan="6"></th>
						<th colspan="4">DOLARES</th>
						<th colspan="5">PESOS</th>
					</tr>
                    <tr>
						
						<th align="center" style="font-size: 12px;">Fecha</th>
						<th align="center" style="font-size: 12px;">Folio</th>
						<th align="left" style="font-size: 12px;">Cliente</th>
						<th align="center" style="font-size: 12px;">Tracking</th>
						<th align="center" style="font-size: 12px;">Carta Porte</th>
						<th align="right" style="font-size: 12px;">Tipo de Cambio</th>
						<th align="right" style="font-size: 12px;">SubTotal</th>
						<th align="right" style="font-size: 12px;">IVA</th>
						<th align="right" style="font-size: 12px;">IVA Retenido</th>
						<th align="right" style="font-size: 12px;">Neto</th>
						<th align="right" style="font-size: 12px;">SubTotal</th>
						<th align="right" style="font-size: 12px;">IVA</th>
						<th align="right" style="font-size: 12px;">IVA Retenido</th>
						<th align="right" style="font-size: 12px;">Neto</th>
						<th align="left" style="font-size: 12px;">Estatus Cobranza</th>
					</tr>
                  </thead>
                  <tbody>';

					//Buscar facturas del cliente	
					$resSQL01 = "select f.Creado,f.XFolio,f.Ticket,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.TipoCambio,r.XFolio as CartaPorte, f.EstatusCobranza as EstatusCobranza FROM ".$prefijobd."factura as f 
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					left join ".$prefijobd."facturasdetalle d on d.FolioSubDetalle_RID=f.ID
					left join ".$prefijobd."remisiones r on d.Remision_RID= r.ID
					where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda.$sql_cliente." and f.FECreado IS NOT NULL";
					
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
						$Creado_t = $rowSQL01['Creado'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$XFolio = $rowSQL01['XFolio'];
						$Cliente = $rowSQL01['RazonSocial'];
						$Tracking = $rowSQL01['Ticket'];
						$CartaPorte = $rowSQL01['CartaPorte'];
						$TipoCambio = $rowSQL01['TipoCambio'];
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
						$EstatusCobranza = $rowSQL01['EstatusCobranza'];
						
                $html.='
                    <tr>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$XFolio.'</td>
                      <td align="left">'.$Cliente.'</td>
					  <td align="center">'.$Tracking.'</td>
                      <td align="center">'.$CartaPorte.'</td>
					  <td align="right">'.$TipoCambio.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right">'.$IVA.'</td>
                      <td align="right">'.$IVARetenido.'</td>
					  <td align="right">'.$Neto.'</td>
					  <td align="right">'.$SubtotalPesos.'</td>
                      <td align="right">'.$IVAPesos.'</td>
                      <td align="right">'.$IVARetenidoPesos.'</td>
					  <td align="right">'.$NetoPesos.'</td>
					  <td align="left">'.$EstatusCobranza.'</td>
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
$mpdf->Output('reporte_ventas_por_folio_dolares_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar PDF
}elseif($boton == 'Excel' and $moneda == 'DOLARES'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_ventas_por_folio_dolares_".date("h:i:s")."_".date("d-m-Y").".xls";
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
			<th align="center" style="font-size: 12px;" colspan="11"><?php echo $RazonSocial; ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;" colspan="11"><?php echo "VENTAS POR FOLIO DOLARES DEL: ".$fechai." AL: ".$fechaf; ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;">Fecha</th>
			<th align="center" style="font-size: 12px;">Folio</th>
			<th align="center" style="font-size: 12px;">Cliente</th>
			<th align="center" style="font-size: 12px;">Tracking</th>
			<th align="center" style="font-size: 12px;">Carta Porte</th>
			<th align="center" style="font-size: 12px;">SubTotal</th>
			<th align="center" style="font-size: 12px;">IVA</th>
			<th align="center" style="font-size: 12px;">IVA Retenido</th>
			<th align="center" style="font-size: 12px;">Neto</th>
			<th align="center" style="font-size: 12px;">Cancelado</th>
			<th align="center" style="font-size: 12px;">Estatus Cobranza</th>
		</tr>
	</thead>
	<tbody>
<?php
					
	//Agrupar por cliente
	
	$totsubtotal = 0;
	$totiva = 0;
	$totretenido = 0;
	$totales = 0;

	$resSQL01 = "select f.Creado,f.XFolio,f.Ticket,f.zSubtotal,f.zImpuesto,f.zRetenido,f.zTotal,c.RazonSocial,f.TipoCambio,r.XFolio as CartaPorte, f.cCanceladoT as cCanceladoT, f.EstatusCobranza as EstatusCobranza FROM ".$prefijobd."factura as f 
	inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
	left join ".$prefijobd."facturasdetalle d on d.FolioSubDetalle_RID=f.ID
	left join ".$prefijobd."remisiones r on d.Remision_RID= r.ID
	where Date(f.Creado) Between '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ".$sql_moneda.$sql_cliente." AND f.FECreado IS NOT NULL ORDER BY f.XFolio";
				
	$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
	while($rowSQL01 = mysql_fetch_array($runSQL01)){
		$Creado_t = $rowSQL01['Creado'];
		$Creado = date("d-m-Y", strtotime($Creado_t));
		$XFolio = $rowSQL01['XFolio'];
		$Cliente = $rowSQL01['RazonSocial'];
		$Tracking = $rowSQL01['Ticket'];
		$CartaPorte = $rowSQL01['CartaPorte'];
		$TipoCambio_t = $rowSQL01['TipoCambio'];
		$TipoCambio = $rowSQL01['TipoCambio'];
		$Subtotal_t = $rowSQL01['zSubtotal'];
		$totsubtotal = $totsubtotal + $Subtotal_t; 
		$Subtotal = number_format($Subtotal_t,2);
		$SubtotalPesos = number_format($rowSQL01['zSubtotal']*$rowSQL01['TipoCambio'],2);
		$IVA_t = $rowSQL01['zImpuesto'];
		$totiva = $totiva + $IVA_t;
		$IVA = number_format($IVA_t,2);
		$IVAPesos = number_format($rowSQL01['zImpuesto']*$rowSQL01['TipoCambio'],2);
		$IVARetenido_t = $rowSQL01['zRetenido'];
		$totretenido = $totretenido + $IVARetenido_t;
		$IVARetenido = number_format($IVARetenido_t,2);
		$IVARetenidoPesos = number_format($rowSQL01['zRetenido']*$rowSQL01['TipoCambio'],2);
		$Neto_t = $rowSQL01['zTotal'];
		$totales = $totales + $Neto_t;
		$Neto = number_format($Neto_t,2);
		$NetoPesos = number_format($rowSQL01['zTotal']*$rowSQL01['TipoCambio'],2);
		$FechaCancelado = $rowSQL01['cCanceladoT'];
		$EstatusCobranza = $rowSQL01['EstatusCobranza'];
?>
					
		<tr>
			<td align="center"><?php echo $Creado; ?></td>
            <td align="center"><?php echo $XFolio; ?></td>
            <td align="left"><?php echo $Cliente; ?></td>
			<td align="center" ><?php echo $Tracking; ?></td>
            <td align="center"><?php echo $CartaPorte; ?></td>
            <td align="right"><?php echo $Subtotal; ?></td>
            <td align="right" ><?php echo $IVA; ?></td>
            <td align="right" ><?php echo $IVARetenido; ?></td>
			<td align="right" ><?php echo $Neto; ?></td>
			<td align="right" ><?php echo $FechaCancelado; ?></td>
			<td align="left"><?php echo $EstatusCobranza; ?></td>
		</tr>

<?php
					
	}// FIN del WHILE $resSQL01
	
	$totsubtotal_t = number_format($totsubtotal, 2);
	$totiva_t = number_format($totiva, 2);
	$totretenido_t = number_format($totretenido, 2);
	$totales_t = number_format($totales, 2);
				
?>

	<tr>
		<td colspan="5" align="right"><strong>TOTALES</strong></td>
		<td align="right"><strong><?php echo $totsubtotal_t; ?></strong></td>
		<td align="right"><strong><?php echo $totiva_t; ?></strong></td>
		<td align="right"><strong><?php echo $totretenido_t; ?></strong></td>
		<td align="right"><strong><?php echo $totales_t; ?></strong></td>
		<td colspan="2"></td>
	</tr>
		
</tbody>
</table>

<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar Excel
}

?>