<?php  

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

//Recibir variables
$foliopago = ($_GET['folio']);
$id_proveedor = ($_GET['proveedor']);
$prefijobd = $_GET["prefijobd"];
$imagen="imagenes/".$prefijobd.".png";

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$fecha = $anio_logs."-".$mes_logs."-".$dia_logs;  

if (!isset($_GET['prefijobd']) || empty($_GET['prefijobd'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijodb = @mysql_escape_string($_GET["prefijobd"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijodb = $prefijobd . "_";
} 

//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
While($rowSQL0 = mysql_fetch_array($runSQL0)){
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

//Buscar datos de Pago
$resSQL2 = "SELECT * FROM ".$prefijobd."pagos WHERE xfolio='".$foliopago."'";
$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
while ($rowSQL2 = mysql_fetch_array($runSQL2)){
	$FechaPago= $rowSQL2['Fecha'];
	$TotalPago= $rowSQL2['Total'];
	$IdProveedor= $rowSQL2['Proveedor_RID'];
	$XFolio=$rowSQL2['XFolio'];
	$ID=$rowSQL2['ID'];
	$ComenPagos1=$rowSQL2['Comentarios'];
	$Documentador=$rowSQL2['Documentador'];
	$IdBancoDestino=$rowSQL2['Banco_RID'];
}

//Buscar datos Banco Proveedor (Origen)
$resSQL1 = "SELECT * FROM ".$prefijobd."proveedores WHERE ID=".$IdProveedor."";
$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
While ($rowSQL1 = mysql_fetch_array($runSQL1)){
	$Rzproveedor = $rowSQL1['RazonSocial'];
	$Bancoproveedor = $rowSQL1['Banco'];
	$Cuentaproveedor = $rowSQL1['CuentaBancaria'];
}	

//Buscar datos Banco Empresa (Cliente)
$resSQL6 = "SELECT * FROM ".$prefijobd."bancos WHERE ID=".$IdBancoDestino."";
$runSQL6 = mysql_query($resSQL6, $cnx_cfdi);
While ($rowSQL6 = mysql_fetch_array($runSQL6)){
	$BancoDes = $rowSQL6['Banco'];
	$CuentaDes = $rowSQL6['Cuenta'];
	$ClabeDes = $rowSQL6['CLABE'];
}	

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
	    <img style="float:left; margin:10px;" alt="" src="'.$imagen.'" width="150px">  
		<p align=center><font size=6>'.$RazonSocial.'</font> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.', '.$RFC.' </p>
        
				

	  </div>

      <h1 style="font-size: 16px;" align=right>POLIZA DE EGRESOS</h1>';
	  
	  $html .= '<table class="default">
				<tr>
					<td align="center"><b>Pago Num.: </b></td>
					<td align="center"><b>Fecha: </b></td>
					<td align="center"><b>Proveedor: </b></td>
				</tr>
				<tr>
					<td align="center">'.$XFolio.'</td>
					<td align="center">'.$FechaPago.'</td>
					<td align="center">'.$Rzproveedor.'</td>
				</tr>
				</table>
				';
	  
	  
//	  $html .= '<H4 align=center>Pago No. '.$XFolio.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha: '.$FechaPago.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Proveedor: '.$Rzproveedor.'</H3>';
			
			$html .= '<table class="default">
					   <tr>
					    <td align="left"><b>Cuenta de Origen: </b>'.$CuentaDes.'</td>
                        <td align="left"><b>Cuenta Destino: </b>'.$Cuentaproveedor.'</td>
					   </tr>';
			$html .= '		   
					   <tr>
					    <td align="left"><b>Banco de Origen: </b>'.$BancoDes.'</td>
                        <td align="left"><b>Banco Destino: </b>'.$Bancoproveedor.'</td>
					   </tr>';
					   
			$html .= '		   
					  <tr>
                        <td align="left"><b>CLABE: </b>'.$ClabeDes.'</td>
					    <td><b></b></td>
					  </tr>
					 </table>';
          
 			$html .='
				<br>
                <table>
                  <thead>
                    <tr>
					  <th align="center" style="font-size: 12px;">Factura</th>
					  <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Compra No.</th>
					  <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
					  <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forma Pago</th>
					  <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Comentario</th>
					  <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <th align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Importe</th>
                    </tr>
				  </thead>
				 <tbody>';
        
				$TotalPagosFac = 0;
				//Buscar facturas del cliente
				$resSQL3 = "SELECT * FROM ".$prefijobd."pagossub WHERE foliosubpago_rid=".$ID."";
				$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
				while ($rowSQL3 = mysql_fetch_array($runSQL3)){
					$Factura = $rowSQL3['FacturaP'];
					$FormaPagoSub = $rowSQL3['FomaPago'];
					$ImporteFac_t = $rowSQL3['Importe'];
					$ImporteFac = number_format($ImporteFac_t,2);
					$NoCompra = $rowSQL3['Compra_RID'];
					$Banco = $rowSQL3['CuentaBancaria_RID'];
					$Comentario = $rowSQL3['Comentario'];
					$FormaPagoC = $rowSQL3['FormaPago'];
					
					$TotalPagosFac = $TotalPagosFac + $ImporteFac_t;
			
					//Buscar Folio Compra 
					$resSQL4 = "SELECT * FROM ".$prefijobd."compras WHERE ID=".$NoCompra."";
					$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
					while ($rowSQL4 = mysql_fetch_array($runSQL4)){
						$FolioCompra = $rowSQL4['XFolio'];
									
					}
					
					//Buscar Datos de Banco
					$resSQL5 = "SELECT * FROM ".$prefijobd."bancos WHERE ID=".$Banco."";
					$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
					while ($rowSQL5 = mysql_fetch_array($runSQL5)){
						$CuentaBancoD = $rowSQL5['Cuenta'];
						$BancoD = $rowSQL5['Banco'];
					}
	
				
				                $html.='
                    <tr>
					  <td align="center">'.$Factura.'</td>
					  <td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$FolioCompra.'</td>
					  <td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$FormaPagoC.'</td>
					  <td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$Comentario.'</td>
					  <td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$ImporteFac.'</td>

                    </tr>';
			
				}
				
							$TotalPagosFac =number_format($TotalPagosFac,2);
									$html.='     
						<tr>
						</tr>
						<tr>
							<td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td align="center" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td colspan="4" align="right"><strong>TOTAL</strong></td>
							<td align="center"><strong>$'.$TotalPagosFac.'</strong></td>
						</tr>
						<tr>
						</tr>
						</tbody>
						</table>
					';
					
				$html.= '<br/><br/><p>Observaciones:&nbsp;'.$ComenPagos1.'</p>';	
				
				$html.= '<br/><br/><br/>';
				
				$linea='__________________________';
				
				
				if ($prefijobd == 'MARTINVC_') {
					$nombreautoriza='MARTIN VASQUEZ CASTILLO';
				}
				
				if ($prefijobd == 'MARYLIN_') {
					$nombreautoriza='ROSA MARYLIN VASQUEZ BARRADA';
				}

				if ($prefijobd == 'ALEJANDROVC_') {
					$nombreautoriza='ALEJANDRO VASQUEZ CASTILLO';
				}
				
				if ($prefijobd == 'GTV_') {
					$nombreautoriza='MARTIN VASQUEZ CASTILLO';
				}
				
				$html .= '<table class="default">
					   <tr>
					    <td align="left">Elaboro:</th>
                        <td align="left">Reviso:</th>
						<td align="left">Autorizo:</th>
					   <br/><br/><br/>';
					
				$html .= '
					   <tr><br/><br/>
					    <td align="left">'.$linea.'&nbsp;&nbsp;&nbsp;</td>
                        <td align="left">'.$linea.'&nbsp;&nbsp;&nbsp;</td>
						<td align="left">'.$linea.'</td>
					   </tr>';
				$html .= '		   
					   <tr>
					    <td align="left"><b>'.$Documentador.'</b></td>
                        <td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
						<td align="left"><b>'.$nombreautoriza.'</b></td>
					   </tr>
					  </table>';

											
$html.=' 
</body>        
</header>';

$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('ImprimePago.pdf', 'I');


?>



