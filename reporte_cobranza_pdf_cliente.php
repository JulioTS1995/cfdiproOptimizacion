<?php  

//Recibir variables
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijobd"];
$id_cliente = $_POST["cliente"];
$v_moneda = $_POST["moneda"];
$imagen="imagenes/".$prefijobd.".png";

$boton = $_POST["button"];

if ($id_cliente == 0) {
	$sql_cliente = "";
}else{
	$sql_cliente = "AND F.CargoAFactura_RID = ".$id_cliente." ";
}

if ($v_moneda == 'NA') {
	$sql_moneda = "";
}else{
	$sql_moneda = "AND F.Moneda = '".$v_moneda."' ";
}

//Formato a Fechas

$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t = date("d-m-Y", strtotime($fecha_fin));
$fechaini = date("Y-m-d", strtotime($fecha_inicio));
$fechafin = date("Y-m-d", strtotime($fecha_fin));

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
    
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;  

if($boton == 'PDF'){

	require_once('cnx_cfdi.php');
	require_once('lib_mpdf/pdf/mpdf.php');
	mysql_select_db($database_cfdi, $cnx_cfdi);

	mysql_query("SET NAMES 'utf8'");

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
	
	//Buscar datos para el cliente
	$resSQL1 = "SELECT * FROM ".$prefijobd."clientes WHERE ID=".$id_cliente."";
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	While($rowSQL1 = mysql_fetch_array($runSQL1)){
		$RZCliente = $rowSQL1['RazonSocial'];
	}
	
	
	$html = '
		<header class="clearfix">
			<meta charset="utf-8">
				<div id="logo">
					<img style="float:left; margin:10px;" alt="" src="'.$imagen.'" width="150px">  
					<p align=center><font size=6>'.$RazonSocial.'</font> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.', '.$RFC.' </p>
				</div>';
	
	if ($id_cliente==0) {
		$html .= ' 	
			<h1 style="font-size: 12px;">Cuentas por Cobrar Detalle, Desde: '.$fecha_inicio_t.' Al: '.$fecha_fin_t.', Estatus:Facturas,  Moneda: '.$v_moneda.'</h1>';
	}
	else {
		$html .= ' 		
			<h1 style="font-size: 12px;">Cuentas por Cobrar Detalle, Cliente:'.$RZCliente.', Desde: '.$fecha_inicio_t.' Al: '.$fecha_fin_t.', Estatus:Facturas,  Moneda: '.$v_moneda.'</h1>';
	}

	$html .='
		<br>
		<hr>
			<div>
			<table style="width:100%">
            <thead>
                <tr>
				  <th style="width:10px" align=left>C.Porte</th>
                  <th style="width:10px" align=left>Fecha</th>
				  <th style="width:150px" align=left>Cliente</th>
                  <th style="width:15px" align=center>Total</th>
                  <th style="width:15px" align=center>Abonos</th>
				  <th style="width:15px" align=center>Saldo</th>
				  <th style="width:15px" align=center>Acumulados</th>
                </tr>
			</thead>
		<tbody>';

		
	if ($id_cliente==0) {
		$acumulado = 0;
		$TotalCliente = 0;
		$TotalAbono = 0;
		$TotalSaldo = 0;
		$Cuenta = 0;
		
		// Buscar Datos Todos los clientes
		$resSQL00 = "Select distinct(a.CargoAFactura_RID) as IdCliente, b.RazonSocial FROM ".$prefijobd."factura a Inner Join ".$prefijobd."Clientes b 
		On a.CargoAFactura_RID = b.ID WHERE Date(a.Creado) Between '".$fechaini." 00:00:00' And '".$fechafin." 23:59:59' And 
		a.Moneda= '".$v_moneda."' And CobranzaSaldo > 0 AND cCanceladoT IS NULL Order By b.RazonSocial";
		
		$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
		While ($rowSQL00 = mysql_fetch_array($runSQL00)){
			$NoCliente = $rowSQL00['IdCliente'];
			$Cliente = $rowSQL00['RazonSocial'];
			
			$resSQL2 = "SELECT * FROM ".$prefijobd."factura  WHERE Date(Creado) Between '".$fechaini." 00:00:00' And '".$fechafin." 23:59:59' 
			And Moneda= '".$v_moneda."' And CargoAFactura_RID=".$NoCliente." And CobranzaSaldo > 0 AND cCanceladoT IS NULL Order By Creado, XFolio";
			$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
			
			$TCFacturado = 0;
			$TCAbono = 0;
			$TCSaldo = 0;
			$TCFacturado_t = 0;
			$TCAbono_t = 0;
			$TCSaldo_t = 0;

			While ($rowSQL2 = mysql_fetch_array($runSQL2)){
				$FolioFac = $rowSQL2['XFolio'];
				$FechaFac = $rowSQL2['Creado'];
				$FecFac = date("d-m-Y", strtotime($FechaFac));
				
				$TotalFac = $rowSQL2['zTotal'];
				$TotalFac_t = number_format($TotalFac, 2);
				$TCFacturado = $TCFacturado + $TotalFac;
				$TCFacturado_t = number_format($TCFacturado, 2);
				
				$AbonoFac = $rowSQL2['CobranzaAbonado'];
				$AbonoFac_t = number_format($AbonoFac, 2);
				$TCAbono = $TCAbono + $AbonoFac;
				$TCAbono_t = number_format($TCAbono, 2);
				
				$SaldoFac = $rowSQL2['CobranzaSaldo'];
				$SaldoFac_t = number_format($SaldoFac, 2);
				$TCSaldo = $TCSaldo + $SaldoFac;
				$TCSaldo_t = number_format($TCSaldo, 2);
				
				$Acumulado = $Acumulado + $SaldoFac;
				$Acumulado_t = number_format($Acumulado, 2);

				$html.='
				<tr>
					<td style="width:10px" align=center>'.$FolioFac.'</td>
					<td style="width:10px" align=center>'.$FecFac.'</td>
					<td style="width:150px" align=left>'.$Cliente.'</td>
					<td style="width:15px" align=center>$'.$TotalFac_t.'</td>
					<td style="width:15px" align=center>$'.$AbonoFac_t.'</td>
					<td style="width:15px" align=center>$'.$SaldoFac_t.'</td>
					<td style="width:15px" align=center>$'.$Acumulado_t.'</td>
				</tr>
				';
			}
			
			$html.='     
			<tr>
				<td colspan="7"><hr></td>
			</tr>
			<tr>
				<td colspan="3" align="right"><strong>TOTAL CLIENTE</strong></td>
				<td align="center"><strong>$'.$TCFacturado_t.'</strong></td>
				<td align="center"><strong>$'.$TCAbono_t.'</strong></td>
				<td align="center"><strong>$'.$TCSaldo_t.'</strong></td>
			</tr>
			<tr>
				<td colspan="7"><hr></td>
			</tr>
			';
			
		}
			
	}
	else {
		$acumulado = 0;
		$TotalCliente = 0;
		$TotalAbono = 0;
		$TotalSaldo = 0;
		$Cuenta = 0;

 		// Buscar Datos Cliente Especifico
		$resSQL3 = "SELECT * FROM ".$prefijobd."factura WHERE CargoAFactura_RID=".$id_cliente." And Creado Between '".$fechaini." 00:00:00' And '".$fechafin." 23:59:59' And Moneda= '".$v_moneda."' And CobranzaSaldo > 0 And cCanceladoT IS NULL Order By Creado, XFolio";
		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
		While ($rowSQL3 = mysql_fetch_array($runSQL3)){
			$FolioFac = $rowSQL3['XFolio'];
			$FechaFac = $rowSQL3['Creado'];
			$FecFac = date("d-m-Y", strtotime($FechaFac));
			$TotalFac = $rowSQL3['zTotal'];
			$TotalFac_t = number_format($TotalFac, 2);
			$AbonoFac = $rowSQL3['CobranzaAbonado'];
			$AbonoFac_t = number_format($AbonoFac, 2);
			$SaldoFac = $rowSQL3['CobranzaSaldo'];
			$SaldoFac_t = number_format($SaldoFac, 2);
			$Acumulado = $Acumulado + $SaldoFac;
			$Acumulado_t = number_format($Acumulado, 2);
			
			$TotalCliente = $TotalCliente + $TotalFac;
			$TotalCliente_t = number_format($TotalCliente, 2);
			$TotalAbono = $TotalAbono + $AbonoFac;
			$TotalAbono_t = number_format($TotalAbono,2);
			$TotalSaldo = $TotalSaldo + $SaldoFac;
			$TotalSaldo_t = number_format($TotalSaldo, 2);
			$Cuenta = $Cuenta + 1;
			$html.='
				<tr>
					<td style="width:10px" align=center>'.$FolioFac.'</td>
					<td style="width:10px" align=center>'.$FecFac.'</td>
					<td style="width:150px" align=left>'.$RZCliente.'</td>
					<td style="width:15px" align=center>$'.$TotalFac_t.'</td>
					<td style="width:15px" align=center>$'.$AbonoFac_t.'</td>
					<td style="width:15px" align=center>$'.$SaldoFac_t.'</td>
					<td style="width:15px" align=center>$'.$Acumulado_t.'</td>
				</tr>';
		}
		
		$html.='</tbody>';
	}
	
	//$resSQL3 = "SELECT * FROM ".$prefijobd."factura WHERE CargoAFactura_RID=".$id_cliente."";
	//$runSQL3 = mysql_query($resSQL2, $cnx_cfdi);
	//While ($rowSQL3 = mysql_fetch_array($runSQL3)){
	//	$FolioFac = $rowSQL3['XFolio'];
	//	$FechaFac = $rowSQL3['Creado'];
	
	//	$html.='
    //    <tr>
	//	  <td style="width:15px" align=center>'.$FolioFac.'</td>
    //      <td style="width:15px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fechaini.'</td>
	//	  <td style="width:150px" align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$RZCliente.'</td>
	//	  <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$RZZCliente.'</td>
	//	  <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$TotalAcumulado_t.'</td>
    //   </tr>';
	//}

$html.='</table>
		</header>';

}

//echo $html;

$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('ReporteCXC_Clientes.pdf', 'I');

?>