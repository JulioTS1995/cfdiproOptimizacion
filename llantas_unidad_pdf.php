<?php  

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

//Recibir variables
//$fecha_inicio = $_POST["fechai"];
//$fecha_fin = $_POST["fechaf"];
$prefijobd = $_GET["prefijodb"];
$id_movimiento = $_GET["id"];



//Buscar ID de la Unidad
$resSQL00 = "SELECT * FROM ".$prefijobd."llantasmovimiento WHERE ID = ".$id_movimiento;
//echo $resSQL00;
$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
while($rowSQL00 = mysql_fetch_array($runSQL00)){
	$id_unidad = $rowSQL00['Unidad_RID'];
}	

//Buscar Nombre de la Unidad
$resSQL01 = "SELECT * FROM ".$prefijobd."Unidades WHERE ID = ".$id_unidad;
$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
while($rowSQL01 = mysql_fetch_array($runSQL01)){
	$nom_unidad = $rowSQL01['Unidad'];
}	

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
    
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;  

$fecha_actual = date("d-m-Y", strtotime($fecha2));

$dia_semana=date("l");





$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><h1><b>Llantas en Unidad: '.$nom_unidad.'</b></h1></p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      ';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">

              <div><br></div>


              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 20px;">NO. SERIE</th>
                      <th align="center" style="font-size: 20px;">LLANTA NO</th>
                      <th align="center" style="font-size: 20px;">POSICION</th>
                      <th align="center" style="font-size: 20px;">PRESION</th>
                      <th align="center" style="font-size: 20px;">MARCA</th>
                      <th align="center" style="font-size: 20x;">TIPO</th>
                      <th align="center" style="font-size: 20px;">UBICACION</th>
                    </tr>
                  </thead>
                  <tbody>';

				
                
                //Buscar en Llantas con Ubicación En Unidad
			
				$resSQL="SELECT * FROM ".$prefijobd."llantas WHERE Ubicacion = 'En Unidad'";
				//echo $resSQL;
				$runSQL=mysql_query($resSQL);
				$total_registros_t = mysql_num_rows($runSQL);
				$total_registros = number_format($total_registros_t,0);
				while ($rowSQL1=mysql_fetch_array($runSQL)){
					//Obtener_variables
					$foliosub_ultimo = $rowSQL1['FolioSubUltimoLlantasSub_RID'];
					$foliosub_llantano = $rowSQL1['LlantasNo'];
					$foliosub_marca = $rowSQL1['Marca'];
					$foliosub_tipo = $rowSQL1['Tipo'];
					$foliosub_noserie = $rowSQL1['NoSerie'];
					
					if (isset($foliosub_ultimo)) {
						//echo "Variable definida!!!";
					}else{
						//echo "Variable NO definida!!!";
						$foliosub_ultimo = 0;
					}


				//Buscar en Llantas Sub los que coincidan con la Unidad
					$resSQL4="SELECT * FROM ".$prefijobd."llantassub WHERE ID = ".$foliosub_ultimo;
					//echo "<br>".$resSQL4;
					$runSQL4=mysql_query($resSQL4);
					$total_registros_t2 = mysql_num_rows($runSQL4);
					$total_registros2 = number_format($total_registros_t2,0);
					//echo "<br> Total reg2: ".$total_registros2;
					while ($rowSQL4=mysql_fetch_array($runSQL4)){
						//Obtener_variables
						$llantas_sub_id_unidad = $rowSQL4['Unidad_RID'];
						$llantas_sub_posicion = $rowSQL4['Posicion'];
						$llantas_sub_presion = $rowSQL4['Presion'];
						$llantas_sub_ubicacion = $rowSQL4['Ubicacion'];
						
						if($llantas_sub_id_unidad==$id_unidad){
							
						
				
                $html.='
                    <tr>
                      <td align="center">'.$foliosub_noserie.'</td>
                      <td align="center">'.$foliosub_llantano.'</td>
                      <td align="center">'.$llantas_sub_posicion.'</td>
                      <td align="center">'.$llantas_sub_presion.'</td>
                      <td align="center" >'.$foliosub_marca.'</td>
                      <td align="center" >'.$foliosub_tipo.'</td>
                      <td align="center" >'.$llantas_sub_ubicacion.'</td>
                    </tr>

                    ';
						} 
					
					}  //Fin Buscar LlantasSub
				} //Fin Busca Ids Clientes  

				$html.='
                    <tr>
					  <td colspan="7"><hr></td>
					</tr>
				';

              $html.='     
                   
                  </tbody>
                </table> 
			    <!--<div class="col-lg-12">
					<h3>Total Registros: '.$total_registros.'</h3>
				</div>-->
              </div>

              <div><br></div>

              ';



           

          
$html.='</header>';

//echo $html;


//$mpdf = new mPDF('c', 'A4-L');
$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Llantas_Unidad_'.date("h:i:s").'_'.date("d-m-Y").'.pdf', 'I');

//http://72.55.137.183/cfdipro/llantas_unidad_pdf.php?prefijodb=prbolpega_&id=254781

?>