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
	$sql_cliente=" AND CargoAFactura_RID = ".$cliente_id;
}


if($boton == 'PDF'){


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
      <h1 style="font-size: 20px;">Estado de Cuenta</h1>';


       
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
					  <th align="center" style="font-size: 12px;">Factura</th>
					  <th align="center" style="font-size: 12px;">Moneda</th>
                      <th align="right" style="font-size: 12px;">Importe</th>
                      <th align="center" style="font-size: 12px;">Condiciones Pago</th>
                    </tr>
                  </thead>
                  <tbody>';
				  
                //Agrupar por cliente
				$resSQL01 = "SELECT a.CargoAFactura_RID, b.RazonSocial, b.DiasCredito FROM ".$prefijobd."factura a Inner Join ".$prefijobd."clientes b On a.CargoAFactura_RID = b.Id WHERE Date (a.Creado) Between '".$fechai." 00:00:00' And  '".
				$fechaf." 11:59:59' and a.Moneda='".$moneda."' ".$sql_cliente." And a.cCanceladoT IS NULL AND CobranzaSaldo > 0 Group BY a.CargoAFactura_RID, b.RazonSocial, b.DiasCredito 
				Order By b.RazonSocial";
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
						echo $nom_cliente;
					}
				$html.='
                    <tr>
                      <td colspan="5" align="left"><strong>'.$nom_cliente.'</strong></td>
					</tr>
				';

					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." AND Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL ORDER BY XFolio ";
				
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$XFolio = $rowSQL03['XFolio'];
						$moneda_t = $rowSQL03['Moneda'];
						$Creado = $rowSQL03['Creado'];
						$Importe_t = $rowSQL03['zTotal'];
						$Importe = "$".number_format($Importe_t,2);
						$CondicionesPago = $rowSQL03['DiasCredito'];
				
                $html.='
					<div>
                    <tr>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$XFolio.'</td>
					  <td align="center">'.$moneda_t.'</td>
                      <td align="right">'.$Importe.'</td>
					  <td align="center" >'.$CondicionesPago.' Dias</td>
                    </tr>
					</div>
                    ';
					
					} // FIN del WHILE $resSQL03 
					
					
										//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT SUM(zTotal) AS Tsaldo FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." AND Moneda='".$moneda."' AND Date(Creado) Between '".$fechai."' And '".$fechaf."' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}

					
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT round(SUM(zTotal),2) AS Tsaldo FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." AND Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
					$html.='     
						<tr>
						  <td colspan="3" align="right"><strong>TOTALES</strong></td>
						  <td align="right"><strong>'.$Tsaldo.'</strong></td>
						 
						</tr>
					';	
                  
                  } // FIN del WHILE $resSQL01

              $html.='     
                   
                  </tbody>
                </table>  
              </div>
			</div>
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
$mpdf->Output('Estado_de_cuenta_de_clientes.pdf', 'I');

} elseif ($boton == 'Excel') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Estado_de_cuenta_de_clientes_".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="3"><?php echo $RazonSocial.'</strong>' ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="3"><?php echo "Estado de Cuenta ".$moneda." DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha</th>
							<th align="center" style="font-size: 12px;">Factura</th>
							<th align="right" style="font-size: 12px;">Importe</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	
				$resSQL01 = "SELECT a.CargoAFactura_RID, b.RazonSocial, b.DiasCredito FROM ".$prefijobd."factura a Inner Join ".$prefijobd."clientes b On a.CargoAFactura_RID = b.Id WHERE Date (a.Creado) Between '".$fechai." 00:00:00' And  '".$fechaf." 11:59:59' and a.Moneda='".$moneda."' " .$sql_cliente." AND CobranzaSaldo > 0 AND cCanceladoT IS NULL Group BY a.CargoAFactura_RID, b.RazonSocial, b.DiasCredito Order By b.RazonSocial";
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
						$DiasC = $rowSQL02['DiasCredito'];
					}
	?>
			<tr>
                <td colspan="3" align="left"><strong><?php echo $nom_cliente; ?></strong></td>
			</tr>
			<tr>
                <td colspan="3" align="left"><strong><?php echo 'Dias Credito '.$DiasC; ?></strong></td>
			</tr>

	<?php
					
					
					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." AND Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL ORDER BY XFolio ";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$XFolio = $rowSQL03['XFolio'];
						$moneda_t = $rowSQL03['Moneda'];
						$Creado = $rowSQL03['Creado'];
						$Importe_t = $rowSQL03['zTotal'];
						$Importe = "$".number_format($Importe_t,2);
						$CondicionesPago = $rowSQL03['DiasCredito'];
						
	?>
					<tr>
					  <td align="center"><?php echo $Creado; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                     <td align="right"><?php echo $Importe; ?></td>
                    </tr>
	<?php	
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT SUM(zTotal) AS Tsaldo FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." AND Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL ";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
						
	?>
					
						<tr>
						  <td colspan="2" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						</tr>
						
	<?php
	}

					//////Agregar Totales por Reportes
					
					if($cliente_id == 0){
						$resSQL05 = "SELECT SUM(zTotal) AS TsaldoT FROM ".$prefijobd."factura WHERE Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL";
					}else{
						$resSQL05 = "SELECT SUM(zTotal) AS TsaldoT FROM ".$prefijobd."factura WHERE CargoAFactura_RID = ".$id_cliente." And Moneda='".$moneda."' AND Date(Creado) Between '".$fechai." 00:00:00' And '".$fechaf." 11:59:59' AND CobranzaSaldo > 0 AND cCanceladoT IS NULL";
					}	
					$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
					while($rowSQL05 = mysql_fetch_array($runSQL05)){
						$Tsaldo_tt = $rowSQL05['TsaldoT'];
						$Tsaldot = "$".number_format($Tsaldo_tt,2);
					}
?>

						<tr>
						  <td colspan="2" align="right"><strong>TOTALES</strong></td>
						  <td align="right"><strong><?php echo $Tsaldot; ?></strong></td>
						</tr>
				</tbody>
             </table>  
      
<?php 
	}
?>