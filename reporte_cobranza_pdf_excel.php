<?php  

//Recibir variables
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$v_serie = $_POST["serie"];
$id_cliente = $_POST["cliente"];
$v_moneda = $_POST["moneda"];

$boton = $_POST["button"];

if ($v_serie != "") {
    //echo "Variable definida!!!";
	$sql_serie = "AND F.Serie = '".$v_serie."' ";
}else{
	//echo "Variable NO definida!!!";
	$sql_serie = "";
}

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

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
    
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;  

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


if($boton == 'PDF'){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar PDF

/////////////////////////////////////////////////////////////////////////////////////////////   PDF

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");




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


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Cuentas por Cobrar</h1>';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>


              <div>
                <table>
                  <thead>
                    <tr>
					  <th align="center" style="font-size: 12px;">Moneda</th>
                      <th align="center" style="font-size: 12px;">Folio</th>
					  <th align="center" style="font-size: 12px;">Fecha Creacion</th>
                      <th align="center" style="font-size: 12px;">Fecha Vence</th>
					  <th align="center" style="font-size: 12px;">Dias de Crédito</th>
                      <th align="center" style="font-size: 12px;">Saldo</th>
                      <th align="center" style="font-size: 12px;">Abonado</th>
                      <th align="center" style="font-size: 12px;">De 1-15</th>
                      <th align="center" style="font-size: 12px;">De 16-30</th>
                      <th align="center" style="font-size: 12px;">De 31-60</th>
					  <th align="center" style="font-size: 12px;">De 61-90</th>
                      <th align="center" style="font-size: 12px;">Más de 90</th>
                    </tr>
                  </thead>
                  <tbody>';


                
                //Agrupar por cliente
                $resSQL01 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE CobranzaSaldo > 0 AND Date(F.Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') ".$sql_cliente."".$sql_serie."".$sql_moneda." ORDER BY F.CargoAFactura_RID";
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				/*$html.='
                    <tr>
                      <td colspan="9" align="left"><strong>'.$resSQL01.'</strong></td>
					</tr>
				';*/
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_cliente = $rowSQL01['CargoAFactura_RID'];
					//Buscar nombre del cliente
					$resSQL02 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_cliente = $rowSQL02['RazonSocial'];
					}
				$html.='
                    <tr>
                      <td colspan="12" align="left"><strong>'.$nom_cliente.'</strong></td>
					</tr>
				';
					
					$miarray4 = array(); // creo el array
					$v_1_15_t = 0;
					$v_16_30_t = 0;
					$v_31_60_t = 0;
					$v_61_90_t = 0;
					$v_90_t = 0;
					
					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE CobranzaSaldo > 0 AND Date(F.Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND CargoAFactura_RID = ".$id_cliente." ".$sql_serie."".$sql_moneda." ORDER BY F.Vence";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$Moneda = $rowSQL03['Moneda'];
						$XFolio = $rowSQL03['XFolio'];
						$Vence_t = $rowSQL03['Vence'];
						$Vence = date("d-m-Y", strtotime($Vence_t));
						$Creado_t = $rowSQL03['Creado'];
						$Creado = date("d-m-Y", strtotime($Creado_t));
						$CobranzaSaldo_t = $rowSQL03['CobranzaSaldo'];
						$CobranzaSaldo = number_format($CobranzaSaldo_t,2);
						$CobranzaAbonado_t = $rowSQL03['CobranzaAbonado'];
						$CobranzaAbonado = number_format($CobranzaAbonado_t,2);
						$dias_credito = $rowSQL03['DiasCredito'];
						
						$diff = abs(strtotime($fecha2) - strtotime($Vence_t));
						//$years = floor($diff / (365*60*60*24));
						//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
						$years=0;
						$months=0;
						$dias_vencimiento = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						
						//Validar si esta vigente el Vencimiento (Negativo)
						if($Vence_t >= $fecha2) {
							$dias_vencimiento=$dias_vencimiento*-1;
						}else {
						}
						
						if($dias_vencimiento >= 1 AND $dias_vencimiento <=15){
							$v_1_15 = $CobranzaSaldo;
							$v_1_15_t = $v_1_15_t + $CobranzaSaldo_t;
						} else {
							$v_1_15 = 0;
							$v_1_15_t = $v_1_15_t + $v_1_15;
						}
						
						
						if($dias_vencimiento >= 16 AND $dias_vencimiento <=30){
							$v_16_30 = $CobranzaSaldo;
							$v_16_30_t = $v_16_30_t + $CobranzaSaldo_t;
						} else {
							$v_16_30 = 0;
							$v_16_30_t = $v_16_30_t + $v_16_30;
						}
						
						
						if($dias_vencimiento >= 31 AND $dias_vencimiento <=60){
							$v_31_60 = $CobranzaSaldo;
							$v_31_60_t = $v_31_60_t + $CobranzaSaldo_t;
						} else {
							$v_31_60 = 0;
							$v_31_60_t = $v_31_60_t + 0;
						}
						
						
						if($dias_vencimiento >= 61 AND $dias_vencimiento <=90){
							$v_61_90 = $CobranzaSaldo;
							$v_61_90_t = $v_61_90_t + $CobranzaSaldo_t;
						} else {
							$v_61_90 = 0;
							$v_61_90_t = $v_61_90_t + $v_61_90;
						}
						
						
						if($dias_vencimiento > 90){
							$v_90 = $CobranzaSaldo;
							$v_90_t = $v_90_t + $CobranzaSaldo_t;
						} else {
							$v_90 = 0;
							$v_90_t = $v_90_t + $v_90;
						}
						
						
				
                $html.='
                    <tr>
					  <td align="center">'.$Moneda.'</td>
                      <td align="center">'.$XFolio.'</td>
					  <td align="center">'.$Creado.'</td>
                      <td align="center">'.$Vence.'</td>
					  <td align="center">'.$dias_credito.'</td>
                      <td align="center">'.$CobranzaSaldo.'</td>
                      <td align="center">'.$CobranzaAbonado.'</td>
                      <td align="center" >'.$v_1_15.'</td>
                      <td align="center" >'.$v_16_30.'</td>
                      <td align="center" >'.$v_31_60.'</td>
					  <td align="center" >'.$v_61_90.'</td>
					  <td align="center" >'.$v_90.'</td>

                    </tr>

                    ';
					
					} // FIN del WHILE $resSQL03 
					
					//////Agregar Totales por Clientes
					
					$resSQL04 = "SELECT SUM(CobranzaSaldo) AS Tsaldo FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND CargoAFactura_RID = ".$id_cliente;
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = number_format($Tsaldo_t,2);
					}
					
					$resSQL05 = "SELECT SUM(CobranzaAbonado) AS Tabonado FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND CargoAFactura_RID = ".$id_cliente;
					$runSQL05 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tabonado_t = $rowSQL04['Tabonado'];
						$Tabonado = number_format($Tabonado_t,2);
					}

					$v_1_15_t =number_format($v_1_15_t,2);
					$v_16_30_t =number_format($v_16_30_t,2);
					$v_31_60_t =number_format($v_31_60_t,2);
					$v_61_90_t =number_format($v_61_90_t,2);
					$v_90_t =number_format($v_90_t,2);
					
					
					$html.='     
						<tr>
						  <td colspan="12"><hr></td>
						</tr>
						<tr>
						  <td colspan="5" align="right"><strong>TOTAL</strong></td>
						  <td align="center"><strong>'.$Tsaldo.'</strong></td>
						  <td align="center"><strong>'.$Tabonado.'</strong></td>
						  <td align="center"><strong>'.$v_1_15_t.'</strong></td>
						  <td align="center"><strong>'.$v_16_30_t.'</strong></td>
						  <td align="center"><strong>'.$v_31_60_t.'</strong></td>
						  <td align="center"><strong>'.$v_61_90_t.'</strong></td>
						  <td align="center"><strong>'.$v_90_t.'</strong></td>
						</tr>
						<tr>
						  <td colspan="12"><hr></td>
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
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('cuentas_por_cobrar.pdf', 'I');



}elseif($boton == 'Excel'){
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="CuentasPorCobrar_".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="12">
								<h2><b>Cuentas por Cobrar</b></h2>
							</th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="12">
								<h4>Periodo: <?php echo $fecha_inicio_t." - ".$fecha_fin_t; ?></h4>
							</th>
						</tr>
						<tr>
							<th class="input">Moneda</th>
							<th class="input">Folio</th>
							<th class="input">Fecha Creacion</th>
							<th class="input">Fecha Vence</th>
							<th class="input">Dias de Crédito</th>
							<th class="input">Saldo</th>
							<th class="input">Abonado</th>
							<th class="input">De 1-15</th>
							<th class="input">De 16-30</th>
							<th class="input">De 31-60</th>
							<th class="input">De 61-90</th>
							<th class="input">Más de 90</th>
						</tr>
					</thead>
					<tbody>
					<?php
					
					//Agrupar por cliente
					$resSQL01 = "SELECT DISTINCT(CargoAFactura_RID) FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE CobranzaSaldo > 0 AND Date(F.Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') ".$sql_cliente."".$sql_serie."".$sql_moneda." ORDER BY F.CargoAFactura_RID";
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					/*$html.='
						<tr>
						  <td colspan="9" align="left"><strong>'.$resSQL01.'</strong></td>
						</tr>
					';*/
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
                      <td colspan="12" align="left"><strong><?php echo $nom_cliente; ?></strong></td>
					</tr>
					
					<?php
					
						$miarray4 = array(); // creo el array
						$v_1_15_t = 0;
						$v_16_30_t = 0;
						$v_31_60_t = 0;
						$v_61_90_t = 0;
						$v_90_t = 0;
						
						//Buscar facturas del cliente
						$resSQL03 = "SELECT * FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE CobranzaSaldo > 0 AND Date(F.Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND CargoAFactura_RID = ".$id_cliente." ".$sql_serie."".$sql_moneda." ORDER BY F.Vence";
						$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
						while($rowSQL03 = mysql_fetch_array($runSQL03)){
							$Moneda = $rowSQL03['Moneda'];
							$XFolio = $rowSQL03['XFolio'];
							$Vence_t = $rowSQL03['Vence'];
							$Vence = date("d-m-Y", strtotime($Vence_t));
							$Creado_t = $rowSQL03['Creado'];
							$Creado = date("d-m-Y", strtotime($Creado_t));
							$CobranzaSaldo_t = $rowSQL03['CobranzaSaldo'];
							$CobranzaSaldo = number_format($CobranzaSaldo_t,2);
							$CobranzaAbonado_t = $rowSQL03['CobranzaAbonado'];
							$CobranzaAbonado = number_format($CobranzaAbonado_t,2);
							$dias_credito = $rowSQL03['DiasCredito'];
							
							$diff = abs(strtotime($fecha2) - strtotime($Vence_t));
							//$years = floor($diff / (365*60*60*24));
							//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
							$years=0;
							$months=0;
							$dias_vencimiento = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
							
							//Validar si esta vigente el Vencimiento (Negativo)
							if($Vence_t >= $fecha2) {
								$dias_vencimiento=$dias_vencimiento*-1;
							}else {
							}
							
							if($dias_vencimiento >= 1 AND $dias_vencimiento <=15){
								$v_1_15 = $CobranzaSaldo;
								$v_1_15_t = $v_1_15_t + $CobranzaSaldo_t;
							} else {
								$v_1_15 = 0;
								$v_1_15_t = $v_1_15_t + $v_1_15;
							}
							
							
							if($dias_vencimiento >= 16 AND $dias_vencimiento <=30){
								$v_16_30 = $CobranzaSaldo;
								$v_16_30_t = $v_16_30_t + $CobranzaSaldo_t;
							} else {
								$v_16_30 = 0;
								$v_16_30_t = $v_16_30_t + $v_16_30;
							}
							
							
							if($dias_vencimiento >= 31 AND $dias_vencimiento <=60){
								$v_31_60 = $CobranzaSaldo;
								$v_31_60_t = $v_31_60_t + $CobranzaSaldo_t;
							} else {
								$v_31_60 = 0;
								$v_31_60_t = $v_31_60_t + 0;
							}
							
							
							if($dias_vencimiento >= 61 AND $dias_vencimiento <=90){
								$v_61_90 = $CobranzaSaldo;
								$v_61_90_t = $v_61_90_t + $CobranzaSaldo_t;
							} else {
								$v_61_90 = 0;
								$v_61_90_t = $v_61_90_t + $v_61_90;
							}
							
							
							if($dias_vencimiento > 90){
								$v_90 = $CobranzaSaldo;
								$v_90_t = $v_90_t + $CobranzaSaldo_t;
							} else {
								$v_90 = 0;
								$v_90_t = $v_90_t + $v_90;
							}
						  
							
					?>
				
                    <tr>
						<td align="center"><?php echo $Moneda; ?></td>
						<td align="center"><?php echo $XFolio; ?></td>
						<td align="center"><?php echo $Creado; ?></td>
						<td align="center"><?php echo $Vence; ?></td>
						<td align="center"><?php echo $dias_credito; ?></td>
						<td align="right"><?php echo $CobranzaSaldo; ?></td>
						<td align="right"><?php echo $CobranzaAbonado; ?></td>
						<td align="right"><?php echo $v_1_15; ?></td>
						<td align="right"><?php echo $v_16_30; ?></td>
						<td align="right"><?php echo $v_31_60; ?></td>
						<td align="right"><?php echo $v_61_90; ?></td>
						<td align="right"><?php echo $v_90; ?></td>

                    </tr>

					<?php 
						} // FIN del WHILE $resSQL03 
					
						//////Agregar Totales por Clientes
						
						$resSQL04 = "SELECT SUM(CobranzaSaldo) AS Tsaldo FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND CargoAFactura_RID = ".$id_cliente;
						$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
						while($rowSQL04 = mysql_fetch_array($runSQL04)){
							$Tsaldo_t = $rowSQL04['Tsaldo'];
							$Tsaldo = number_format($Tsaldo_t,2);
						}
						
						$resSQL05 = "SELECT SUM(CobranzaAbonado) AS Tabonado FROM ".$prefijobd."factura WHERE CobranzaSaldo > 0 AND cCanceladoT IS NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND CargoAFactura_RID = ".$id_cliente;
						$runSQL05 = mysql_query($resSQL04, $cnx_cfdi);
						while($rowSQL04 = mysql_fetch_array($runSQL04)){
							$Tabonado_t = $rowSQL04['Tabonado'];
							$Tabonado = number_format($Tabonado_t,2);
						}

						$v_1_15_t =number_format($v_1_15_t,2);
						$v_16_30_t =number_format($v_16_30_t,2);
						$v_31_60_t =number_format($v_31_60_t,2);
						$v_61_90_t =number_format($v_61_90_t,2);
						$v_90_t =number_format($v_90_t,2);
						  
				     ?>
					<tr>
						<td colspan="5" align="right"><strong>TOTAL</strong></td>
						<td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						<td align="right"><strong><?php echo $Tabonado; ?></strong></td>
						<td align="right"><strong><?php echo $v_1_15_t; ?></strong></td>
						<td align="right"><strong><?php echo $v_16_30_t; ?></strong></td>
						<td align="right"><strong><?php echo $v_31_60_t; ?></strong></td>
						<td align="right"><strong><?php echo $v_61_90_t; ?></strong></td>
						<td align="right"><strong><?php echo $v_90_t; ?></strong></td>
					</tr>
					 <?php
					  } // FIN del WHILE $resSQL01
					 ?>
					 
				<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
					</tbody>
				</table>

<?php


/////////////////////////////////////////////////////////////////////////////////////////////////////////////Fin Ejecutar Excel
}





?>