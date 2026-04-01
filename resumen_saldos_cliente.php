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
      <h1 style="font-size: 20px;">Resumen Saldos de Clientes</h1>';

            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <!-- <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>-->
              
              <div><br></div>

              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Cliente</th>
					  <th align="center" style="font-size: 12px;">Saldo Inicial</th>
					  <th align="center" style="font-size: 12px;">Ventas</th>
                      <th align="center" style="font-size: 12px;">Facturas Canceladas</th>
                      <th align="center" style="font-size: 12px;">Notas de Credito</th>
                      <th align="center" style="font-size: 12px;">Saldo Final</th>
                    </tr>
					<tr>
					  <th colspan="6" align="left" style="font-size: 12px;"><Strong>'.$moneda.'</Strong></th>
					</tr>
                  </thead>
                  <tbody>';
                
               //Agrupar por cliente
					$resSQL01 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND Moneda='".$moneda."' ORDER BY CargoAFactura_RID";
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					//Buscar id de Oficina
					$resSQL07 = "select ID from ".$prefijobd."oficinas where Serie='NC'";
					$runSQL07 = mysql_query($resSQL07, $cnx_cfdi);
					while($rowSQL07 = mysql_fetch_array($runSQL07)){
						$Oficina = $rowSQL07['ID'];
					} // FIN del WHILE $resSQL07
					
					//Buscar sumas del cliente
					$resSQL03 = "SELECT sum(zTotal)as zTotal FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND CargoAFactura_RID = ".$id_cliente;
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Neto_t = $rowSQL03['zTotal'];
						$Neto = "$".number_format($Neto_t,2);
						$TNeto_t = $TNeto_t + $Neto_t;
						$TNeto = "$".number_format($TNeto_t,2);
					} // FIN del WHILE $resSQL03 	
					$resSQL05 = "SELECT sum(zTotal) as zTotal FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND cCanceladoT IS NOT NULL AND CargoAFactura_RID = ".$id_cliente;
					$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
					while($rowSQL05 = mysql_fetch_array($runSQL05)){
						$Cancelado_t = $rowSQL05['zTotal'];
						$Cancelado = "$".number_format($Cancelado_t,2);
						$TCancelado_t = $TCancelado_t + $Cancelado_t;
						$TCancelado = "$".number_format($TCancelado_t,2);
					} // FIN del WHILE $resSQL05
					$resSQL06 = "select sum(TotalImporte) as TotalImporte from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND Oficina_RID=".$Oficina." AND Cliente_RID = ".$id_cliente;
					$runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
					while($rowSQL06 = mysql_fetch_array($runSQL06)){
						$NotaCredito_t = $rowSQL06['TotalImporte'];
						$NotaCredito = "$".number_format($NotaCredito_t,2);
						$TNotaCredito_t = $TNotaCredito_t + $NotaCredito_t;
						$TNotaCredito = "$".number_format($TNotaCredito_t,2);
					} // FIN del WHILE $resSQL06
					
					$SaldoFinal_t = $Neto_t-$Cancelado_t-$NotaCredito_t;
					$SaldoFinal = "$".number_format($SaldoFinal_t,2);
					$TSaldoFinal_t = $TSaldoFinal_t + $SaldoFinal_t;
					$TSaldoFinal = "$".number_format($TSaldoFinal_t,2);
                $html.='
                    <tr>
					  <td align="left">'.$nom_cliente.'</td>
                      <td align="right">'.$Neto.'</td>
					  <td align="right"></td>
					  <td align="right" >'.$Cancelado.'</td>
                      <td align="right">'.$NotaCredito.'</td>
                      <td align="right" >'.$SaldoFinal.'</td>
                    </tr>

                    ';
					
					
				} // FIN del WHILE $resSQL01
					//////Agregar Totales 	
					$html.='     
						<tr>
						  <td colspan="6"><hr></td>
						</tr>
						<tr>
						  <td align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong>'.$TNeto.'</strong></td>
						  <td align="right"><strong></strong></td>
						  <td align="right"><strong>'.$TCancelado.'</strong></td>
						  <td align="right"><strong>'.$TNotaCredito.'</strong></td>
						  <td align="right"><strong>'.$TSaldoFinal.'</strong></td>
						</tr>
						<tr>
						  <td colspan="6"><hr></td>
						</tr>
					';
					     
                  

              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>

              ';
      
$html.='</header>';



$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y } / Tractosoft / Hoja {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Resumen_Saldos_Por_Cliente.pdf', 'I');

} elseif ($boton == 'Excel') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Resumen_Saldos_Por_Cliente_".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="6"><strong><?php echo $RazonSocial; ?></strong></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="6"><?php echo "Ventas Por Cliente"; ?></th>
						</tr>
						<tr>
                      <th align="center" style="font-size: 12px;">Cliente</th>
					  <th align="center" style="font-size: 12px;">Saldo Inicial</th>
					  <th align="center" style="font-size: 12px;">Ventas</th>
                      <th align="center" style="font-size: 12px;">Facturas Canceladas</th>
                      <th align="center" style="font-size: 12px;">Notas de Credito</th>
                      <th align="center" style="font-size: 12px;">Saldo Final</th>
                    </tr>
					<tr>
					  <th colspan="6" align="left" style="font-size: 12px;"><Strong><?php echo $moneda; ?></Strong></th>
					</tr>
					</thead>
					<tbody>	
	<?php
	

				//Agrupar por cliente
					$resSQL01 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND Moneda='".$moneda."' ORDER BY CargoAFactura_RID";
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
					//Buscar id de Oficina
					$resSQL07 = "select ID from ".$prefijobd."oficinas where Serie='NC'";
					$runSQL07 = mysql_query($resSQL07, $cnx_cfdi);
					while($rowSQL07 = mysql_fetch_array($runSQL07)){
						$Oficina = $rowSQL07['ID'];
					} // FIN del WHILE $resSQL07
					
					//Buscar sumas del cliente
					$resSQL03 = "SELECT sum(zTotal)as zTotal FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND CargoAFactura_RID = ".$id_cliente;
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Neto_t = $rowSQL03['zTotal'];
						$Neto = "$".number_format($Neto_t,2);
						$TNeto_t = $TNeto_t + $Neto_t;
						$TNeto = "$".number_format($TNeto_t,2);
					} // FIN del WHILE $resSQL03 	
					$resSQL05 = "SELECT sum(zTotal) as zTotal FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND cCanceladoT IS NOT NULL AND CargoAFactura_RID = ".$id_cliente;
					$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
					while($rowSQL05 = mysql_fetch_array($runSQL05)){
						$Cancelado_t = $rowSQL05['zTotal'];
						$Cancelado = "$".number_format($Cancelado_t,2);
						$TCancelado_t = $TCancelado_t + $Cancelado_t;
						$TCancelado = "$".number_format($TCancelado_t,2);
					} // FIN del WHILE $resSQL05
					$resSQL06 = "select sum(TotalImporte) as TotalImporte from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' AND Oficina_RID=".$Oficina." AND Cliente_RID = ".$id_cliente;
					$runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
					while($rowSQL06 = mysql_fetch_array($runSQL06)){
						$NotaCredito_t = $rowSQL06['TotalImporte'];
						$NotaCredito = "$".number_format($NotaCredito_t,2);
						$TNotaCredito_t = $TNotaCredito_t + $NotaCredito_t;
						$TNotaCredito = "$".number_format($TNotaCredito_t,2);
					} // FIN del WHILE $resSQL06
					
					$SaldoFinal_t = $Neto_t-$Cancelado_t-$NotaCredito_t;
					$SaldoFinal = "$".number_format($SaldoFinal_t,2);
					$TSaldoFinal_t = $TSaldoFinal_t + $SaldoFinal_t;
					$TSaldoFinal = "$".number_format($TSaldoFinal_t,2);
						
	?>
					<tr>
					  <td align="left"><?php echo $nom_cliente; ?></td>
                      <td align="right"><?php echo $Neto; ?></td>
                      <td align="right"></td>
                      <td align="right" ><?php echo $Cancelado; ?></td>
                      <td align="right" ><?php echo $NotaCredito; ?></td>
                      <td align="right" ><?php echo $SaldoFinal; ?></td>
                    </tr>
	<?php	
					} // FIN del WHILE $resSQL01
					
					
	?>
						<tr>
						  <td align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong><?php echo $TNeto; ?></strong></td>
						  <td align="right"><strong></strong></td>
						  <td align="right"><strong><?php echo $TCancelado; ?></strong></td>
						  <td align="right"><strong><?php echo $TNotaCredito; ?></strong></td>
						  <td align="right"><strong><?php echo $TSaldoFinal; ?></strong></td>
						</tr>
				</tbody>
             </table>  
      
<?php 
	}
?>