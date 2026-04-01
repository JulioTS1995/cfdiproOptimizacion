<?php  

//Recibir variables
$fecha_inicio = $_GET["fechai"];
$fecha_fin = $_GET["fechaf"];
$prefijobd = $_GET["prefijodb"];


$boton = $_GET["button"];


//Formato a Fechas

$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t = date("d-m-Y", strtotime($fecha_fin));

$fecha_inicio_t2 = date("Y-m-d", strtotime($fecha_inicio));
$fecha_fin_t2 = date("Y-m-d", strtotime($fecha_fin));

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
	///////////////////////////////////////////////////////////////////////////////////// PDF

	require_once('cnx_cfdi.php');
	require_once('lib_mpdf/pdf/mpdf.php');
	mysql_select_db($database_cfdi, $cnx_cfdi);
	

	mysql_query("SET NAMES 'utf8'");

	

		$mpdf = new mPDF('c', 'A4');
		$css = file_get_contents('css/style_pdf.css');
		//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
		$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
		//$mpdf->setFooter('Página {PAGENO}');
		$mpdf->defaultfooterline = 0;
		$mpdf->writeHTML($css, 1);
		
	//Buscar datos para encabezado
	$resSQL01 = "SELECT * FROM ".$prefijobd."systemsettings LIMIT 1";
	$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
	while($rowSQL01 = mysql_fetch_array($runSQL01)){
		$RazonSocial = $rowSQL01['RazonSocial'];
		$RFC = $rowSQL01['RFC'];
		$CodigoPostal = $rowSQL01['CodigoPostal'];
		$Calle = $rowSQL01['Calle'];
		$NumeroExterior = $rowSQL01['NumeroExterior'];
		$Colonia = $rowSQL01['Colonia'];
		$Ciudad = $rowSQL01['Ciudad'];
		$Pais = $rowSQL01['Pais'];
		$Estado = $rowSQL01['Estado'];
		$Municipio = $rowSQL01['Municipio'];
	}
	
	
	$c1 = 0;
	

	//Consultar Cargos Unidades
	$resSQL00 = "SELECT * FROM ".$prefijobd."cargosunidades WHERE Creado >='".$fecha_inicio_t2." 00:00:00' AND Creado<='".$fecha_fin_t2." 23:59:59' ORDER BY Folio";
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	$numero_reg = mysql_num_rows($runSQL00);
	while($rowSQL00 = mysql_fetch_array($runSQL00)){
		$folio = $rowSQL00['Folio'];
		$creado_t = $rowSQL00['Creado'];
		$creado = date("d-m-Y", strtotime($creado_t));
		$importe_t = $rowSQL00['Importe'];
		$importe = '$'.number_format($importe_t ,2);
		$unidad_id = $rowSQL00['Unidad_RID'];
		$comentarios = $rowSQL00['Comentarios'];
		$documentador = $rowSQL00['Documentador'];
		
		$c1 = $c1 + 1;
			
		$html = '<header class="clearfix">
		<meta charset="utf-8">';
	


		$html .= '
		<div class="row">
		  <div style="width: 100%;">
			<div align="left" style="width: 50%;float: left; ">
				<p style="font-size: 12px;"><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
			 </div>
			 
			<div align="right" style="width: 50%;float: left; text-align: right;">
				<img src="img_logos/TPSMARTI1.jpeg" width="150x">
			</div>
		  </div>
		  <h1 style="font-size: 15px;">Cargos Unidades</h1>
		</div>';
		//Buscar Unidad
		$resSQL02 = "SELECT * FROM ".$prefijobd."unidades WHERE ID = ".$unidad_id;
		$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
		while($rowSQL02 = mysql_fetch_array($runSQL02)){
			$nom_unidad = $rowSQL02['Unidad'];
		}
	
		
		  
		  
		  
		  $html .= '
		<div class="row">
		  <div style="width: 100%;">
			<div align="left" style="width: 70%;float: left; ">
				<p> </p>
			</div>
			 
			<div align="right" style="width: 30%;float: left; text-align: right;">
				<!--<div style="font-size: 7px; text-align: right;"><span>'.$fecha.'</span></div>-->
				<table class="table" border="0" style="padding:0px; margin:0 0 15 0;font-size: 10px;" cellspacing="0" cellpadding="0">
					<tr>
						<td align="right"><b>Vale:</b></td>
						<td align="left">'.$folio.'</td>
					</tr>
					<tr>
						<td align="right"><b>Fecha:</b></td>
						<td align="left">'.$creado.'</td>
					</tr>
					<tr>
						<td align="right"><b>Aplicar:</b></td>
						<td align="left"></td>
					</tr>
					<tr>
						<td align="right"><b>Importe:</b></td>
						<td align="left">'.$importe.'</td>
					</tr>
				</table>
							
				
			</div>
		  </div>
		</div>';
		
		$html .= '
		<div class="row">
		  <div style="width: 100%;">
			<div align="left" style="width: 70%;float: left; ">
				<table class="table" border="0" style="font-size: 10px;">
					<tr>
						<td align="right" width="20%"><b>Unidad:</b></td>
						<td align="left" width="80%">'.$nom_unidad.'</td>
					</tr>
					<tr>
						<td align="right"><b>Concepto:</b></td>
						<td align="left"></td>
					</tr>
					<tr>
						<td align="right"><b>Comentario:</b></td>
						<td align="left">'.$comentarios.'</td>
					</tr>
				</table>
			</div>
			 
			<div align="right" style="width: 30%;float: left; text-align: right;">
				
			</div>
		  </div>
		</div>';
		
		
		
		$html .= '
		<div class="row">
		  <div style="width: 100%;">
			<div align="left" style="width: 45%;float: left; ">
				<table class="table" border="0" style="font-size: 10px;">
					<tr>
						<td align="center"><span style="visibility:hidden">TEST</span></b></td>
					</tr>
					<hr>
					<tr style="height:15px">
						<td align="center"><span style="visibility:hidden">TEST</span></td>
					</tr>
					<tr>
						<td align="center"><span style="visibility:hidden">TEST</span></td>
					</tr>
					<hr>
				</table>
			</div>
			 
			<div align="right" style="width: 55%;float: left; text-align: right;">
				<table class="table" border="0" style="font-size: 10px;">
					<tr>
						<td align="center"><span style="visibility:visible">'.$documentador.'</span></td>
					</tr>
					<hr>
					<tr>
						<td align="center"><b>Documentador</b></td>
						
					</tr>
					<tr>
						<td align="center"><span style="visibility:hidden">TEST</span></td>
					</tr>
					<hr>
					<tr>
						<td align="center"><b>Autoriza</b></td>
					</tr>
				</table>
			</div>
		  </div>
		</div>';
		  

	
	
		$html.='</header>';
	
		$mpdf->writeHTML($html);
		if($c1 < $numero_reg){
			$mpdf->AddPage();
			$html = '';
		}
		
		
		
	
	}// Fin Cargos Unidades	
		
		$mpdf->Output('cargos_unidades'.date("h:i:s")."_".date("d-m-Y").'.pdf', 'I');
		
		//$mpdf->Output('cargos_unidades.pdf', 'I');
		
		
		

	
}


?>