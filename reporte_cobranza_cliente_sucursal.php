<?php  
//Recibir variables
$prefijobd = $_POST['prefijodb'];
$fechai = $_POST['txtDesde'];
$fechaf = $_POST['txtHasta'];
$cliente_id = $_POST['cliente'];
$moneda = $_POST['moneda'];
$boton = $_POST['btnEnviar'];
$sucursal = $_POST["sucursal"];//trae sucursal

require_once('cnx_cfdi.php');
$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

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
	//$RFC = $rowSQL0['RFC'];
	//$CodigoPostal = $rowSQL0['CodigoPostal'];
	//$Calle = $rowSQL0['Calle'];
	//$NumeroExterior = $rowSQL0['NumeroExterior'];
	//$Colonia = $rowSQL0['Colonia'];
	//$Ciudad = $rowSQL0['Ciudad'];
	//$Pais = $rowSQL0['Pais'];
	//$Estado = $rowSQL0['Estado'];
	//$Municipio = $rowSQL0['Municipio'];
}
if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente=" AND f.CargoAFactura_RID = ".$cliente_id;
}


if($boton == 'PDF' and $moneda == 'PESOS'){


require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 16px;">Cobranza Por Cliente PESOS DEL: '.$fechai.' AL: '.$fechaf.'</h1>';

            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <!-- <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>-->
              
              <div><br></div>

              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha Pago</th>
					  <th align="center" style="font-size: 12px;">Factura</th>
					  <th align="center" style="font-size: 12px;">Rep</th>
                      <th align="left" style="font-size: 12px;">Cliente</th>
                      <th align="right" style="font-size: 12px;">Subtotal</th>
                      <th align="right" style="font-size: 12px;">IVA 16%</th>
					  <th align="right" style="font-size: 12px;">IVA Ret</th>
                      <th align="right" style="font-size: 12px;">Neto</th>
                    </tr>
                  </thead>
                  <tbody>';
                
               //Agrupar por cliente
					$resSQL01 = "select distinct(f.CargoAFactura_RID) from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' AND c.Sucursal_RID = ".$sucursal."".$sql_cliente;
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					
					//Buscar por cliente
					$resSQL03 = "select ab.FechaAplicacion,f.XFolio as Factura,a.XFolio as Abono,c.RazonSocial,ab.SubTotal,ab.Impuesto,ab.Retenido,ab.Importe from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' and f.CargoAFactura_RID=".$id_cliente." AND c.Sucursal_RID = ".$sucursal." order by c.RazonSocial";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Fecha = $rowSQL03['FechaAplicacion'];
						$XFolio = $rowSQL03['Factura'];
						$Abono = $rowSQL03['Abono'];
						$Subtotal_t = $rowSQL03['SubTotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$IVA_t = $rowSQL03['Impuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVARet_t = $rowSQL03['Retenido'];
						$IVARet = "$".number_format($IVARet_t,2);
						$Neto_t = $rowSQL03['Importe'];
						$Neto = "$".number_format($Neto_t,2);
									
				
                $html.='
                    <tr>
					  <td align="center">'.$Fecha.'</td>
                      <td align="center">'.$XFolio.'</td>
					  <td align="center">'.$Abono.'</td>
					  <td align="left" >'.$nom_cliente.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right" >'.$IVA.'</td>
                      <td align="right" >'.$IVARet.'</td>
                      <td align="right" >'.$Neto.'</td>
                    </tr>

                    ';
					
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "select sum(ab.Subtotal)as subtotal, sum(ab.Impuesto) as IVA, sum(ab.Retenido) as retenido, sum(ab.Importe) as importe from ".$prefijobd."abonossub as ab 
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS' and a.Cliente_RID=".$id_cliente."
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'";
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
					}
					
					$html.='     
						<tr>
						  <td colspan="8"><hr></td>
						</tr>
						<tr>
						  <td colspan="4" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong>'.$TSubtotal.'</strong></td>
						  <td align="right"><strong>'.$TImpuesto.'</strong></td>
						  <td align="right"><strong>'.$TRetenido.'</strong></td>
						  <td align="right"><strong>'.$TImporte.'</strong></td>
						  
						</tr>
						<tr>
						  <td colspan="8"><hr></td>
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
$mpdf->setFooter(' {DATE d-m-Y } / Tractosoft / Hoja {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Cobranza_Por_Cliente.pdf', 'I');

} elseif ($boton == 'Excel' and $moneda == 'PESOS') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Cobranza_Por_Cliente_".date("h:i:s")."_".date("d-m-Y").".xls";
	header("Content-Disposition: attachment; filename=$nombre");
	require_once('lib_mpdf/pdf/mpdf.php');

	require_once('cnx_cfdi.php');
	require_once('lib_mpdf/pdf/mpdf.php');
	mysql_select_db($database_cfdi, $cnx_cfdi);

	mysql_query("SET NAMES 'utf8'");
	
	?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo $RazonSocial.'</strong>' ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "Cobranza Por Cliente"; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha Pago</th>
							<th align="center" style="font-size: 12px;">Factura</th>
							<th align="center" style="font-size: 12px;">Rep</th>
							<th align="left" style="font-size: 12px;">Cliente</th>
							<th align="right" style="font-size: 12px;">Subtotal</th>
							<th align="right" style="font-size: 12px;">IVA 16%</th>
							<th align="right" style="font-size: 12px;">IVA Ret</th>
							<th align="right" style="font-size: 12px;">Neto</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	

				$resSQL01 = "select distinct(f.CargoAFactura_RID) from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'".$sql_cliente;
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					
					//Buscar facturas del cliente
					$resSQL03 = "select ab.FechaAplicacion,f.XFolio as Factura,a.XFolio as Abono,c.RazonSocial,ab.SubTotal,ab.Impuesto,ab.Retenido,ab.Importe from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' and f.CargoAFactura_RID=".$id_cliente." AND c.Sucursal_RID = ".$sucursal." order by c.RazonSocial";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Fecha = $rowSQL03['FechaAplicacion'];
						$XFolio = $rowSQL03['Factura'];
						$Abono = $rowSQL03['Abono'];
						$Subtotal_t = $rowSQL03['SubTotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$IVA_t = $rowSQL03['Impuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVARet_t = $rowSQL03['Retenido'];
						$IVARet = "$".number_format($IVARet_t,2);
						$Neto_t = $rowSQL03['Importe'];
						$Neto = "$".number_format($Neto_t,2);
						
	?>
					<tr>
					  <td align="center"><?php echo $Fecha; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                      <td align="center"><?php echo $Abono; ?></td>
                      <td align="left" ><?php echo $nom_cliente; ?></td>
                      <td align="right" ><?php echo $Subtotal; ?></td>
                      <td align="right" ><?php echo $IVA; ?></td>
					  <td align="right" ><?php echo $IVARet; ?></td>
					  <td align="right" ><?php echo $Neto; ?></td>
                    </tr>
	<?php	
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "select sum(ab.Subtotal)as subtotal, sum(ab.Impuesto) as IVA, sum(ab.Retenido) as retenido, sum(ab.Importe) as importe from ".$prefijobd."abonossub as ab 
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='PESOS' and a.Cliente_RID=".$id_cliente."
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'";
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
					}
	?>
						
						<tr>
						  <td colspan="4" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong><?php echo $TSubtotal; ?></strong></td>
						  <td align="right"><strong><?php echo $TImpuesto; ?></strong></td>
						  <td align="right"><strong><?php echo $TRetenido; ?></strong></td>
						  <td align="right"><strong><?php echo $TImporte; ?></strong></td>
						</tr>
						
	<?php
	}

?>
				</tbody>
             </table>  
      
<?php 
}elseif($boton == 'PDF' and $moneda == 'DOLARES'){


require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> 
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Cobranza por Cliente</h1>';

            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <!-- <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>-->
              
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
                      <th align="center" style="font-size: 12px;">Fecha Pago</th>
					  <th align="center" style="font-size: 12px;">Factura</th>
					  <th align="center" style="font-size: 12px;">Rep</th>
                      <th align="left" style="font-size: 12px;">Cliente</th>
					  <th align="center" style="font-size: 12px;">Tipo Cambio</th>
                      <th align="right" style="font-size: 12px;">Subtotal</th>
                      <th align="right" style="font-size: 12px;">IVA 16%</th>
					  <th align="right" style="font-size: 12px;">IVA Ret</th>
                      <th align="right" style="font-size: 12px;">Neto</th>
					  <th align="right" style="font-size: 12px;">Subtotal</th>
                      <th align="right" style="font-size: 12px;">IVA 16%</th>
					  <th align="right" style="font-size: 12px;">IVA Ret</th>
                      <th align="right" style="font-size: 12px;">Neto</th>
                    </tr>
                  </thead>
                  <tbody>';
                
               //Agrupar por cliente
					$resSQL01 = "select distinct(f.CargoAFactura_RID) from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' AND c.Sucursal_RID = ".$sucursal."".$sql_cliente;
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					
					//Buscar por cliente
					$resSQL03 = "select ab.FechaAplicacion,f.XFolio as Factura,a.XFolio as Abono,c.RazonSocial,ab.SubTotal,ab.Impuesto,ab.Retenido,ab.Importe,a.TipoCambio from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' and f.CargoAFactura_RID=".$id_cliente." order by c.RazonSocial";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Fecha = $rowSQL03['FechaAplicacion'];
						$XFolio = $rowSQL03['Factura'];
						$Abono = $rowSQL03['Abono'];
						$TipoCambio= $rowSQL03['TipoCambio'];
						$Subtotal_t = $rowSQL03['SubTotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$SubtotalPesos = "$".number_format($TipoCambio*$Subtotal_t,2);
						$IVA_t = $rowSQL03['Impuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVAPesos = "$".number_format($TipoCambio*$IVA_t,2);
						$IVARet_t = $rowSQL03['Retenido'];
						$IVARet = "$".number_format($IVARet_t,2);
						$IVARetPesos = "$".number_format($TipoCambio*$IVARet_t,2);
						$Neto_t = $rowSQL03['Importe'];
						$Neto = "$".number_format($Neto_t,2);
						$NetoPesos = "$".number_format($TipoCambio*$Neto_t,2);
									
				
                $html.='
                    <tr>
					  <td align="center">'.$Fecha.'</td>
                      <td align="center">'.$XFolio.'</td>
					  <td align="center">'.$Abono.'</td>
					  <td align="left" >'.$nom_cliente.'</td>
					  <td align="center">'.$TipoCambio.'</td>
                      <td align="right">'.$Subtotal.'</td>
                      <td align="right" >'.$IVA.'</td>
                      <td align="right" >'.$IVARet.'</td>
                      <td align="right" >'.$Neto.'</td>
					  <td align="right">'.$SubtotalPesos.'</td>
                      <td align="right" >'.$IVAPesos.'</td>
                      <td align="right" >'.$IVARetPesos.'</td>
                      <td align="right" >'.$NetoPesos.'</td>
                    </tr>

                    ';
					
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "select sum(ab.Subtotal)as subtotal, sum(ab.Impuesto) as IVA, sum(ab.Retenido) as retenido, sum(ab.Importe) as importe from ".$prefijobd."abonossub as ab 
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES' and a.Cliente_RID=".$id_cliente."
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$TSubtotal_t = $rowSQL04['subtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						$TSubtotalPesos = "$".number_format($TipoCambio*$TSubtotal_t,2);
						$TImpuesto_t = $rowSQL04['IVA'];
						$TImpuesto = "$".number_format($TImpuesto_t,2);
						$TImpuestoPesos = "$".number_format($TipoCambio*$TImpuesto_t,2);
						$TRetenido_t = $rowSQL04['retenido'];
						$TRetenido = "$".number_format($TRetenido_t,2);
						$TRetenidoPesos = "$".number_format($TipoCambio*$TRetenido_t,2);
						$TImporte_t = $rowSQL04['importe'];
						$TImporte = "$".number_format($TImporte_t,2);
						$TImportePesos = "$".number_format($TipoCambio*$TImporte_t,2);
					}
					
					$html.='     
						<tr>
						  <td colspan="13"><hr></td>
						</tr>
						<tr>
						  <td colspan="5" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong>'.$TSubtotal.'</strong></td>
						  <td align="right"><strong>'.$TImpuesto.'</strong></td>
						  <td align="right"><strong>'.$TRetenido.'</strong></td>
						  <td align="right"><strong>'.$TImporte.'</strong></td>
						  <td align="right"><strong>'.$TSubtotalPesos.'</strong></td>
						  <td align="right"><strong>'.$TImpuestoPesos.'</strong></td>
						  <td align="right"><strong>'.$TRetenidoPesos.'</strong></td>
						  <td align="right"><strong>'.$TImportePesos.'</strong></td>
						</tr>
						<tr>
						  <td colspan="13"><hr></td>
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
$mpdf->setFooter(' {DATE d-m-Y } / Tractosoft / Hoja {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Cobranza_Por_Cliente.pdf', 'I');

}elseif ($boton == 'Excel' and $moneda == 'DOLARES') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Cobranza_Por_Cliente_".date("h:i:s")."_".date("d-m-Y").".xls";
	header("Content-Disposition: attachment; filename=$nombre");
	require_once('lib_mpdf/pdf/mpdf.php');

	require_once('cnx_cfdi.php');
	require_once('lib_mpdf/pdf/mpdf.php');
	mysql_select_db($database_cfdi, $cnx_cfdi);

	mysql_query("SET NAMES 'utf8'");
	
	?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="13"><?php echo $RazonSocial.'</strong>' ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="13"><?php echo "Cobranza Por Cliente"; ?></th>
						</tr>
						<tr>
							<th colspan="5"></th>
							<th colspan="4">DOLARES</th>
							<th colspan="4">PESOS</th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha Pago</th>
							<th align="center" style="font-size: 12px;">Factura</th>
							<th align="center" style="font-size: 12px;">Rep</th>
							<th align="left" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Tipo Cambio</th>
							<th align="right" style="font-size: 12px;">Subtotal</th>
							<th align="right" style="font-size: 12px;">IVA 16%</th>
							<th align="right" style="font-size: 12px;">IVA Ret</th>
							<th align="right" style="font-size: 12px;">Neto</th>
							<th align="right" style="font-size: 12px;">Subtotal</th>
							<th align="right" style="font-size: 12px;">IVA 16%</th>
							<th align="right" style="font-size: 12px;">IVA Ret</th>
							<th align="right" style="font-size: 12px;">Neto</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	

				$resSQL01 = "select distinct(f.CargoAFactura_RID) from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'".$sql_cliente;
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					
					//Buscar facturas del cliente
					$resSQL03 = "select ab.FechaAplicacion,f.XFolio as Factura,a.XFolio as Abono,c.RazonSocial,ab.SubTotal,ab.Impuesto,ab.Retenido,ab.Importe,a.TipoCambio from ".$prefijobd."abonossub as ab
					inner join ".$prefijobd."factura as f on ab.AbonoFactura_RID=f.ID
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES'
					inner join ".$prefijobd."clientes as c on f.CargoAFactura_RID=c.ID
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."' and f.CargoAFactura_RID=".$id_cliente." order by c.RazonSocial";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Fecha = $rowSQL03['FechaAplicacion'];
						$XFolio = $rowSQL03['Factura'];
						$Abono = $rowSQL03['Abono'];
						$TipoCambio= $rowSQL03['TipoCambio'];
						$Subtotal_t = $rowSQL03['SubTotal'];
						$Subtotal = "$".number_format($Subtotal_t,2);
						$SubtotalPesos = "$".number_format($TipoCambio*$Subtotal_t,2);
						$IVA_t = $rowSQL03['Impuesto'];
						$IVA = "$".number_format($IVA_t,2);
						$IVAPesos = "$".number_format($TipoCambio*$IVA_t,2);
						$IVARet_t = $rowSQL03['Retenido'];
						$IVARet = "$".number_format($IVARet_t,2);
						$IVARetPesos = "$".number_format($TipoCambio*$IVARet_t,2);
						$Neto_t = $rowSQL03['Importe'];
						$Neto = "$".number_format($Neto_t,2);
						$NetoPesos = "$".number_format($TipoCambio*$Neto_t,2);
						
	?>
					<tr>
					  <td align="center"><?php echo $Fecha; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                      <td align="center"><?php echo $Abono; ?></td>
                      <td align="left" ><?php echo $nom_cliente; ?></td>
					  <td align="center"><?php echo $TipoCambio; ?></td>
                      <td align="right" ><?php echo $Subtotal; ?></td>
                      <td align="right" ><?php echo $IVA; ?></td>
					  <td align="right" ><?php echo $IVARet; ?></td>
					  <td align="right" ><?php echo $Neto; ?></td>
					  <td align="right" ><?php echo $SubtotalPesos; ?></td>
                      <td align="right" ><?php echo $IVAPesos; ?></td>
					  <td align="right" ><?php echo $IVARetPesos; ?></td>
					  <td align="right" ><?php echo $NetoPesos; ?></td>
                    </tr>
	<?php	
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "select sum(ab.Subtotal)as subtotal, sum(ab.Impuesto) as IVA, sum(ab.Retenido) as retenido, sum(ab.Importe) as importe from ".$prefijobd."abonossub as ab 
					inner join ".$prefijobd."abonos as a on ab.FolioSub_RID=a.ID and a.Moneda='DOLARES' and a.Cliente_RID=".$id_cliente."
					where Date(ab.FechaAplicacion) Between '".$fechai."' And '".$fechaf."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$TSubtotal_t = $rowSQL04['subtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						$TSubtotalPesos = "$".number_format($TipoCambio*$TSubtotal_t,2);
						$TImpuesto_t = $rowSQL04['IVA'];
						$TImpuesto = "$".number_format($TImpuesto_t,2);
						$TImpuestoPesos = "$".number_format($TipoCambio*$TImpuesto_t,2);
						$TRetenido_t = $rowSQL04['retenido'];
						$TRetenido = "$".number_format($TRetenido_t,2);
						$TRetenidoPesos = "$".number_format($TipoCambio*$TRetenido_t,2);
						$TImporte_t = $rowSQL04['importe'];
						$TImporte = "$".number_format($TImporte_t,2);
						$TImportePesos = "$".number_format($TipoCambio*$TImporte_t,2);
					}
	?>
						
						<tr>
						  <td colspan="5" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong><?php echo $TSubtotal; ?></strong></td>
						  <td align="right"><strong><?php echo $TImpuesto; ?></strong></td>
						  <td align="right"><strong><?php echo $TRetenido; ?></strong></td>
						  <td align="right"><strong><?php echo $TImporte; ?></strong></td>
						  <td align="right"><strong><?php echo $TSubtotalPesos; ?></strong></td>
						  <td align="right"><strong><?php echo $TImpuestoPesos; ?></strong></td>
						  <td align="right"><strong><?php echo $TRetenidoPesos; ?></strong></td>
						  <td align="right"><strong><?php echo $TImportePesos; ?></strong></td>
						</tr>
						
	<?php
	}

?>
				</tbody>
             </table>  
      
<?php 
}
?>