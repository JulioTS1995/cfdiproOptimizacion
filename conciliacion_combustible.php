<?php  

//Recibir variable
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$vale_estacion = $_POST["v_factura"];
$id_estacion = $_POST["v_estacion"];


$boton = $_POST["btnGenerar"];

/*if ($v_serie != "") {
    //echo "Variable definida!!!";
	$sql_serie = "AND F.Serie = '".$v_serie."' ";
}else{
	//echo "Variable NO definida!!!";
	$sql_serie = "";
}*/


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


if($id_estacion > 0){
	//Buscar datos de Estacion
	$resSQLEstacion = "SELECT * FROM ".$prefijobd."estaciones WHERE ID=".$id_estacion;
	$runSQLEstacion = mysql_query($resSQLEstacion, $cnx_cfdi);
	while($rowSQLEstacion = mysql_fetch_array($runSQLEstacion)){
		$nom_estacion = $rowSQLEstacion['Estacion'];
	}
}



$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Conciliación Combustible</h1>
	  <div>
		<p><strong>Fecha Inicio: </strong>'.$fecha_inicio_t.'<br>
		<strong>Fecha Fin: </strong>'.$fecha_fin_t.'<br>
		<strong>Estación: </strong>'.$nom_estacion.'<br>
		<strong>Factura: </strong>'.$vale_estacion.'</p>
      </div>
';


       
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
					  <th align="center" style="font-size: 12px;">Unidad</th>
					  <th align="center" style="font-size: 12px;">Litros</th>
                      <th align="center" style="font-size: 12px;">Importe</th>
                      <th align="center" style="font-size: 12px;">Factura</th>
                      <th align="center" style="font-size: 12px;">Conciliado</th>
                      
                    </tr>
                  </thead>
                  <tbody>';
				  
				if($vale_estacion == '') {
					$vale_estacion = "";
				}else{
					$c_valeemitido = " AND ".$prefijobd."gastosviajes.FolioValeEmitido = '".$vale_estacion."'";
				}
				
				if($id_estacion > 0){
					$c_estacion = " AND ".$prefijobd."gastosviajes.Estacion_RID = ".$id_estacion;
				} else {
					$c_estacion = "";
				}
				
				
				
				


                $repotado_importe = 0;
				$repotado_litros = 0;
				$resSQL01 = "SELECT * FROM ".$prefijobd."gastosviajes LEFT JOIN ".$prefijobd."unidades ON ".$prefijobd."gastosviajes.Unidad_RID = ".$prefijobd."unidades.ID LEFT JOIN ".$prefijobd."estaciones ON ".$prefijobd."gastosviajes.Estacion_RID = ".$prefijobd."estaciones.ID WHERE TipoVale = 'Combustible' AND Date(".$prefijobd."gastosviajes.Fecha) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'".$c_valeemitido.$c_estacion." ORDER BY ".$prefijobd."gastosviajes.Fecha";
				//echo $resSQL01;
				/*$html.='
                    <tr>
					  <td align="center">'.$resSQL01.'</td>
					 <tr>';
				*/
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$xfolio = $rowSQL01['XFolio'];
					$fecha_t = $rowSQL01['Fecha'];
					$fecha = date("d-m-Y", strtotime($fecha_t));
					$n_unidad = $rowSQL01['Unidad'];
					$c_litros_t = $rowSQL01['LitrosCombustible'];
					$c_litros = number_format($c_litros_t ,2);
					$importe_t =$rowSQL01['Importe'];
					$importe =  "$".number_format($importe_t ,2);
					$factura = $rowSQL01['FolioValeEmitido'];
					$conciliacion = $rowSQL01['Conciliacion'];
					
					$repotado_importe = $repotado_importe + $importe_t;
					$repotado_litros = $repotado_litros + $c_litros_t;
					
					
					if($conciliacion == 0){
						$conciliacion_v = "NO"; 
					} else{
						$conciliacion_v = "SI"; 
					}
					
				
						
				
                $html.='
                    <tr>
					  <td align="center">'.$xfolio.'</td>
                      <td align="center">'.$fecha.'</td>
					  <td align="left">'.$n_unidad.'</td>
					  <td align="right">'.$c_litros.'</td>
                      <td align="right">'.$importe.'</td>
                      <td align="center">'.$factura.'</td>
                      <td align="center">'.$conciliacion_v.'</td>
                      
                      

                    </tr>

                    ';
					

                    
                  } // FIN del WHILE $resSQL01

                  



              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>
              ';
			  
			  $repotado_importe_t = "$".number_format($repotado_importe ,2);
			  $repotado_litros_t = number_format($repotado_litros ,2);
			  
			  //Calcular Totales Para Conciliado
				$conciliado_importe_t = 0;
				$conciliado_litros_t = 0;
				$resSQL02 = "SELECT * FROM ".$prefijobd."gastosviajes LEFT JOIN ".$prefijobd."unidades ON ".$prefijobd."gastosviajes.Unidad_RID = ".$prefijobd."unidades.ID LEFT JOIN ".$prefijobd."estaciones ON ".$prefijobd."gastosviajes.Estacion_RID = ".$prefijobd."estaciones.ID WHERE TipoVale = 'Combustible' AND Date(".$prefijobd."gastosviajes.Fecha) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'".$c_valeemitido.$c_estacion." AND ".$prefijobd."gastosviajes.Conciliacion=1 ORDER BY ".$prefijobd."gastosviajes.Fecha";
				$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
				while($rowSQL02 = mysql_fetch_array($runSQL02)){
					$xfolio2 = $rowSQL02['XFolio'];
					$c_litros_t2 = $rowSQL02['LitrosCombustible'];
					$importe_t2 =$rowSQL02['Importe'];
					
					$conciliado_importe_t = $conciliado_importe_t + $importe_t2;
					$conciliado_litros_t = $conciliado_litros_t + $c_litros_t2;
					
				}
				
				$conciliado_litros = number_format($conciliado_litros_t ,2);
				$conciliado_importe =  "$".number_format($conciliado_importe_t ,2);
			  $html.='
				<hr>
				<table>
                    <tr>
					  <td align="center" style="font-size: 10px;" width="10%"></td>
					  <td align="center" style="font-size: 10px;" width="40%" colspan="2"><b>Reportado</b></td>
					  <td align="center" style="font-size: 10px;" width="40%" colspan="2"><b>Conciliado</b></td>
					  <td align="center" style="font-size: 10px;" width="10%"></td>
					</tr>
					<tr>
					  <td align="center" style="font-size: 10px;"></td>
					  <td align="right" style="font-size: 10px;"><b>Importe</b></td>
					  <td align="left" style="font-size: 10px;">'.$repotado_importe_t.'</td>
					  <td align="right" style="font-size: 10px;"><b>Importe</b></td>
					  <td align="left" style="font-size: 10px;">'.$conciliado_importe.'</td>
					  <td align="center" style="font-size: 10px;"></td>
					</tr>
					<tr>
					  <td align="center" style="font-size: 10px;"></td>
					  <td align="right" style="font-size: 10px;"><b>Litros</b></td>
					  <td align="left" style="font-size: 10px;">'.$repotado_litros_t.'</td>
					  <td align="right" style="font-size: 10px;"><b>Litros</b></td>
					  <td align="left" style="font-size: 10px;">'.$conciliado_litros.'</td>
					  <td align="center" style="font-size: 10px;"></td>
					</tr>
				</table>
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
$mpdf->Output('Conciliacion_Combustible_'.$fecha2.'.pdf', 'I');


?>