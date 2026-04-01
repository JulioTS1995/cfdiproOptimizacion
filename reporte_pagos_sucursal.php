<?php  

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$sucursal = $_GET["sucursal"];//trae sucursal


//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");




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
      <h1 style="font-size: 20px;">Cuentas por Pagar</h1>';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>


              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Folio</th>
                      <th align="center" style="font-size: 12px;">Fecha</th>
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


                
                //Agrupar por Proveedor
                $resSQL01 = "SELECT DISTINCT(ProveedorNo_RID) FROM ".$prefijobd."compras WHERE PagosSaldo > 0 AND OficinaCompras_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ORDER BY ProveedorNo_RID";
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$id_proveedor = $rowSQL01['ProveedorNo_RID'];
					//Buscar nombre del proveedor
					$resSQL02 = "SELECT * FROM ".$prefijobd."proveedores WHERE ID = ".$id_proveedor;
					$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
					while($rowSQL02 = mysql_fetch_array($runSQL02)){
						$nom_proveedor = $rowSQL02['RazonSocial'];
					}
				$html.='
                    <tr>
                      <td colspan="9" align="left"><strong>'.$nom_proveedor.'</strong></td>
					</tr>
				';
					
					$miarray4 = array(); // creo el array
					$v_1_15_t = 0;
					$v_16_30_t = 0;
					$v_31_60_t = 0;
					$v_61_90_t = 0;
					$v_90_t = 0;
					
					//Buscar facturas del cliente
					$resSQL03 = "SELECT * FROM ".$prefijobd."compras WHERE PagosSaldo > 0 AND ProveedorNo_RID = ".$id_proveedor." ORDER BY Vence";
					$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
					while($rowSQL03 = mysql_fetch_array($runSQL03)){
						$XFolio = $rowSQL03['XFolio'];
						$Vence = $rowSQL03['Vence'];
						$CobranzaSaldo_t = $rowSQL03['PagosSaldo'];
						$CobranzaSaldo = number_format($CobranzaSaldo_t,2);
						$CobranzaAbonado_t = $rowSQL03['PagosAbonado'];
						$CobranzaAbonado = number_format($CobranzaAbonado_t,2);
						//Poner saldo en columna correspondiente
						$diff = abs(strtotime($fecha2) - strtotime($Vence));
						$dias_vencimiento = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						
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
                      <td align="center">'.$XFolio.'</td>
                      <td align="center">'.$Vence.'</td>
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
					
					$resSQL04 = "SELECT SUM(PagosSaldo) AS Tsaldo FROM ".$prefijobd."compras WHERE PagosSaldo > 0 AND ProveedorNo_RID = ".$id_proveedor;
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['Tsaldo'];
						$Tsaldo = number_format($Tsaldo_t,2);
					}
					
					$resSQL05 = "SELECT SUM(PagosAbonado) AS Tabonado FROM ".$prefijobd."compras WHERE PagosSaldo > 0 AND ProveedorNo_RID = ".$id_proveedor;
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
						  <td colspan="9"><hr></td>
						</tr>
						<tr>
						  <td colspan="2" align="right"><strong>TOTAL</strong></td>
						  <td align="center"><strong>'.$Tsaldo.'</strong></td>
						  <td align="center"><strong>'.$Tabonado.'</strong></td>
						  <td align="center"><strong>'.$v_1_15_t.'</strong></td>
						  <td align="center"><strong>'.$v_16_30_t.'</strong></td>
						  <td align="center"><strong>'.$v_31_60_t.'</strong></td>
						  <td align="center"><strong>'.$v_61_90_t.'</strong></td>
						  <td align="center"><strong>'.$v_90_t.'</strong></td>
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
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('cuentas_por_cobrar.pdf', 'I');

//http://localhost/cfdipro/reporte_pagos.php?prefijodb=tamul_

?>