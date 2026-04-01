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
	$sql_cliente=" AND CargoAFactura_RID = ".$cliente_id;
}


if($boton == 'PDF' and $moneda == 'PESOS'){


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
      <h1 style="font-size: 20px;">Antigüedades saldos de clientes</h1>';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <!-- <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>-->
              
              <div><br></div>
              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha</th>
					  <th align="center" style="font-size: 12px;">Folio</th>
					  <th align="center" style="font-size: 12px;">Moneda</th>
                      <th align="right" style="font-size: 12px;">Importe</th>
                      <!-- <th align="center" style="font-size: 12px;">Abonado</th>-->
                      <th align="right" style="font-size: 12px;">Por Vencer</th>
                      <th align="right" style="font-size: 12px;">De 1 a 30</th>
                      <th align="right" style="font-size: 12px;">De 31 a 60</th>
					  <th align="right" style="font-size: 12px;">De 61 a 90</th>
                      <th align="right" style="font-size: 12px;">Más de 90</th>
                    </tr>
                  </thead>
                  <tbody>';


                
                //Agrupar por cliente
				$resSQL01 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND CobranzaSaldo > 0 AND cCanceladoT  IS NULL AND Oficina_RID IN (SELECT ID FROM ".$prefijodb."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ORDER BY CargoAFactura_RID";
				
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
				
				
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente." AND Sucursal_RID = ".$sucursal." ";
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
				$html.='
                    <tr>
                      <td colspan="9" align="left"><strong>'.$nom_cliente.'</strong></td>
					</tr>
				';
				
					$v_1_15_t = 0.00;
					$v_16_30_t = 0.00;
					$v_31_60_t = 0.00;
					$v_61_90_t = 0.00;
					$v_90_t = 0.00;
					
					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente." AND Date(Creado) Between '".$fechai."' And '".$fechaf."' ORDER BY Vence ";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$XFolio = $rowSQL03['XFolio'];
						$moneda_t = $rowSQL03['Moneda'];
						$Vence = $rowSQL03['Vence'];
						$CobranzaSaldo_t = $rowSQL03['CobranzaSaldo'];
						$CobranzaSaldo = "$".number_format($CobranzaSaldo_t,2);
						$CobranzaAbonado_t = $rowSQL03['CobranzaAbonado'];
						$CobranzaAbonado = "$".number_format($CobranzaAbonado_t,2);
						//Poner saldo en columna correspondiente
						$diff = abs(strtotime($fecha2) - strtotime($Vence));
						$dias_vencimiento = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						
						if($dias_vencimiento >= 1 AND $dias_vencimiento <=15){
							$v_1_15 = $CobranzaSaldo;
							$aux_15 = $CobranzaSaldo_t;
							$v_1_15_t = $v_1_15_t + $aux_15;
							$aux_15_t ="$".number_format($v_1_15_t,2);
						} else {
							$v_1_15 = 0.00;
							$aux_15 = 0.00;
							$v_1_15_t = $v_1_15_t + $aux_15;
							$aux_15_t ="$".number_format($v_1_15_t,2);
						}
						
						if($dias_vencimiento >= 16 AND $dias_vencimiento <=30){
							$v_16_30 = $CobranzaSaldo;
							$aux_30 = $CobranzaSaldo_t;
							$v_16_30_t = $v_16_30_t + $aux_30;
							$aux_30_t ="$".number_format($v_16_30_t,2);
						} else {
							$v_16_30 = 0.00;
							$aux_30 = 0.00;
							$v_16_30_t = $v_16_30_t + $aux_30;
							$aux_30_t ="$".number_format($v_16_30_t,2);
						}
						
						if($dias_vencimiento >= 31 AND $dias_vencimiento <=60){
							$v_31_60 = $CobranzaSaldo;
							$aux_60 = $CobranzaSaldo_t;
							$v_31_60_t = $v_31_60_t + $aux_60;
							$aux_60_t ="$".number_format($v_31_60_t,2);
						} else {
							$v_31_60 = 0.00;
							$aux_60 = 0.00;
							$v_31_60_t = $v_31_60_t + $aux_60;
							$aux_60_t ="$".number_format($v_31_60_t,2);
						}
						
						if($dias_vencimiento >= 61 AND $dias_vencimiento <=90){
							$v_61_90 = $CobranzaSaldo;
							$aux_90 = $CobranzaSaldo_t;
							$v_61_90_t = $v_61_90_t + $aux_90;
							$aux_90_t ="$".number_format($v_61_90_t,2);
						} else {
							$v_61_90 = 0.00;
							$aux_90 = 0.00;
							$v_61_90_t = $v_61_90_t + $aux_90;
							$aux_90_t ="$".number_format($v_61_90_t,2);
						}
						
						if($dias_vencimiento > 90){
							$v_90 = $CobranzaSaldo;
							$aux_90m = $CobranzaSaldo_t;
							$v_90_t = $v_90_t + $aux_90m;
							$aux_90m_t ="$".number_format($v_90_t,2);
						} else {
							$v_90 = 0.00;
							$aux_90m= 0.00;
							$v_90_t = $v_90_t + $aux_90m;
							$aux_90m_t ="$".number_format($v_90_t,2);
						}
						
				
                $html.='
                    <tr>
					  <td align="center">'.$Vence.'</td>
                      <td align="center">'.$XFolio.'</td>
					  <td align="center">'.$moneda_t.'</td>
                      <td align="right">'.$CobranzaSaldo.'</td>
                     <!--  <td align="center">'.$CobranzaAbonado.'</td>-->
                      <td align="right" >'.$v_1_15.'</td>
                      <td align="right" >'.$v_16_30.'</td>
                      <td align="right" >'.$v_31_60.'</td>
					  <td align="right" >'.$v_61_90.'</td>
					  <td align="right" >'.$v_90.'</td>

                    </tr>

                    ';
					
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT SUM(CobranzaSaldo) AS Tsaldo FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente." AND Date(Creado) Between '".$fechai."' And '".$fechaf."' ";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
					$resSQL05 = "SELECT SUM(CobranzaAbonado) AS Tabonado FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente."Date(Creado) Between '".$fechai."' And '".$fechaf."' ";
					$runSQL05 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tabonado_t = $rowSQL04['Tabonado'];
						$Tabonado = "$".number_format($Tabonado_t,2);
					}
					
					$html.='     
						<tr>
						  <td colspan="9"><hr></td>
						</tr>
						<tr>
						  <td colspan="3" align="right"><strong>TOTALES</strong></td>
						  <td align="right"><strong>'.$Tsaldo.'</strong></td>
						  <td align="right"><strong>'.$aux_15_t.'</strong></td>
						  <td align="right"><strong>'.$aux_30_t.'</strong></td>
						  <td align="right"><strong>'.$aux_60_t.'</strong></td>
						  <td align="right"><strong>'.$aux_90_t.'</strong></td>
						  <td align="right"><strong>'.$aux_90m_t.'</strong></td>
						</tr>
						<tr>
						  <td colspan="9"><hr></td>
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
$mpdf->Output('Antigüedades_saldos_de_clientes.pdf', 'I');

//} elseif ($boton == 'Excel' and $moneda == 'PESOS') {
} elseif ($boton == 'Excel') {	
	header("Content-type: application/vnd.ms-excel");
	$nombre="Antigüedades_saldos_de_clientes_".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "Antigüedades saldos de clientes Moneda ".$moneda." DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha</th>
							<th align="center" style="font-size: 12px;">Folio</th>
							<th align="right" style="font-size: 12px;">Importe</th>
							<!-- <th align="center" style="font-size: 12px;">Abonado</th>-->
							<th align="right" style="font-size: 12px;">Por Vencer</th>
							<th align="right" style="font-size: 12px;">De 1 a 30</th>
							<th align="right" style="font-size: 12px;">De 31 a 60</th>
							<th align="right" style="font-size: 12px;">De 61 a 90</th>
							<th align="right" style="font-size: 12px;">Más de 90</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	
	$TSaldot = 0.00;
	$v_1_15_tt = 0.00;
	$v_16_30_tt = 0.00;
	$v_31_60_tt = 0.00;
	$v_61_90_tt = 0.00;
	$v_90_tt = 0.00;
	
				$resSQL01 = "SELECT a.CargoAFactura_RID, b.RazonSocial FROM ".$prefijobd."factura a Inner Join ".$prefijobd."clientes b On a.CargoAFactura_RID = b.Id WHERE Date(a.Creado) Between '".$fechai."' And '".$fechaf."'".$sql_cliente." AND a.Moneda='".$moneda."' GROUP BY a.CargoAFactura_RID, b.RazonSocial ORDER BY b.RazonSocial";

				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
	?>
			<tr>
                <td colspan="8" align="left"><strong><?php echo $nom_cliente; ?></strong></td>
			</tr>
	<?php
					$v_1_15_t = 0.00;
					$v_16_30_t = 0.00;
					$v_31_60_t = 0.00;
					$v_61_90_t = 0.00;
					$v_90_t = 0.00;
					
					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente." AND Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."' ORDER BY Vence ";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$XFolio = $rowSQL03['XFolio'];
						$moneda_t = $rowSQL03['Moneda'];
						$Vence = $rowSQL03['Vence'];
						$CobranzaSaldo_t = $rowSQL03['CobranzaSaldo'];
						$TSaldot = $TSaldot + $CobranzaSaldo_t;
						$CobranzaSaldo = "$".number_format($CobranzaSaldo_t,2);
						$CobranzaAbonado_t = $rowSQL03['CobranzaAbonado'];
						$CobranzaAbonado = "$".number_format($CobranzaAbonado_t,2);
						//Poner saldo en columna correspondiente
						$diff = abs(strtotime($fecha2) - strtotime($Vence));
						$dias_vencimiento = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						
						if($dias_vencimiento >= 1 AND $dias_vencimiento <=15){
							$v_1_15 = $CobranzaSaldo;
							$aux_15 = $CobranzaSaldo_t;
							$v_1_15_t = $v_1_15_t + $aux_15;
							$v_1_15_tt = $v_1_15_tt + $aux_15;
							$aux_15_t ="$".number_format($v_1_15_t,2);
						} else {
							$v_1_15 = 0.00;
							$aux_15 = 0.00;
							$v_1_15_t = $v_1_15_t + $aux_15;
							$aux_15_t ="$".number_format($v_1_15_t,2);
						}
						
						if($dias_vencimiento >= 16 AND $dias_vencimiento <=30){
							$v_16_30 = $CobranzaSaldo;
							$aux_30 = $CobranzaSaldo_t;
							$v_16_30_t = $v_16_30_t + $aux_30;
							$v_16_30_tt = $v_16_30_tt + $aux_30;
							$aux_30_t ="$".number_format($v_16_30_t,2);
						} else {
							$v_16_30 = 0.00;
							$aux_30 = 0.00;
							$v_16_30_t = $v_16_30_t + $aux_30;
							$aux_30_t ="$".number_format($v_16_30_t,2);
						}
						
						if($dias_vencimiento >= 31 AND $dias_vencimiento <=60){
							$v_31_60 = $CobranzaSaldo;
							$aux_60 = $CobranzaSaldo_t;
							$v_31_60_t = $v_31_60_t + $aux_60;
							$v_31_60_tt = $v_31_60_tt + $aux_60;
							$aux_60_t ="$".number_format($v_31_60_t,2);
						} else {
							$v_31_60 = 0.00;
							$aux_60 = 0.00;
							$v_31_60_t = $v_31_60_t + $aux_60;
							$aux_60_t ="$".number_format($v_31_60_t,2);
						}
						
						if($dias_vencimiento >= 61 AND $dias_vencimiento <=90){
							$v_61_90 = $CobranzaSaldo;
							$aux_90 = $CobranzaSaldo_t;
							$v_61_90_t = $v_61_90_t + $aux_90;
							$v_61_90_tt = $v_61_90_tt + $aux_90;
							$aux_90_t ="$".number_format($v_61_90_t,2);
						} else {
							$v_61_90 = 0.00;
							$aux_90 = 0.00;
							$v_61_90_t = $v_61_90_t + $aux_90;
							$aux_90_t ="$".number_format($v_61_90_t,2);
						}
						
						if($dias_vencimiento > 90){
							$v_90 = $CobranzaSaldo;
							$aux_90m = $CobranzaSaldo_t;
							$v_90_t = $v_90_t + $aux_90m;
							$v_90_tt = $v_90_tt + $aux_90m;
							$aux_90m_t ="$".number_format($v_90_t,2);
						} else {
							$v_90 = 0.00;
							$aux_90m= 0.00;
							$v_90_t = $v_90_t + $aux_90m;
							$aux_90m_t ="$".number_format($v_90_t,2);
						}
						
	?>
					<tr>
					  <td align="center"><?php echo $Vence; ?></td>
                      <td align="center"><?php echo $XFolio; ?></td>
                      <td align="right"><?php echo $CobranzaSaldo; ?></td>
                     <!--  <td align="center">'.$CobranzaAbonado.'</td>-->
                      <td align="right" ><?php echo $v_1_15; ?></td>
                      <td align="right" ><?php echo $v_16_30; ?></td>
                      <td align="right" ><?php echo $v_31_60; ?></td>
					  <td align="right" ><?php echo $v_61_90; ?></td>
					  <td align="right" ><?php echo $v_90; ?></td>

                    </tr>
	<?php	
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT SUM(CobranzaSaldo) AS Tsaldo FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente." AND Date(Creado) Between '".$fechai."' And '".$fechaf."' AND Moneda='".$moneda."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
					$resSQL05 = "SELECT SUM(CobranzaAbonado) AS Tabonado FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND CargoAFactura_RID = ".$id_cliente."Date(Creado) Between '".$fechai."' And '".$fechaf."' ";
					$runSQL05 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tabonado_t = $rowSQL04['Tabonado'];
						$Tabonado = "$".number_format($Tabonado_t,2);
					}	
	?>
						
						<tr>
						  <td colspan="2" align="right"><strong>SUMAS</strong></td>
						  <td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						  <td align="right"><strong><?php echo $aux_15_t; ?></strong></td>
						  <td align="right"><strong><?php echo $aux_30_t; ?></strong></td>
						  <td align="right"><strong><?php echo $aux_60_t; ?></strong></td>
						  <td align="right"><strong><?php echo $aux_90_t; ?></strong></td>
						  <td align="right"><strong><?php echo $aux_90m_t; ?></strong></td>
						</tr>
						
	<?php
	}
	
	$TSaldots = "$".number_format($TSaldot,2);
	$v_1_15_tts = "$".number_format($v_1_15_tt,2);
	$v_16_30_tts = "$".number_format($v_16_30_tt,2);
	$v_31_60_tts = "$".number_format($v_31_60_tt,2);
	$v_61_90_tts = "$".number_format($v_61_90_tt,2);
	$v_90_tts = "$".number_format($v_90_tt,2);
	

//http://localhost/cfdipro/reporte_cobranza_sls.php?prefijodb=sls_

?>

						<tr>
						  <td colspan="2" align="right"><strong>TOTALES</strong></td>
						  <td align="right"><strong><?php echo $TSaldots; ?></strong></td>
						  <td align="right"><strong><?php echo $v_1_15_tts; ?></strong></td>
						  <td align="right"><strong><?php echo $v_16_30_tts; ?></strong></td>
						  <td align="right"><strong><?php echo $v_31_60_tts; ?></strong></td>
						  <td align="right"><strong><?php echo $v_61_90_tts; ?></strong></td>
						  <td align="right"><strong><?php echo $v_90_tts; ?></strong></td>
						</tr>

				</tbody>
             </table>  
      
<?php 



	}
?>