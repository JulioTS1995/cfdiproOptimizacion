<?php  

//Recibir variables
$fecha_inicio = $_GET["fechai"];
$fecha_fin = $_GET["fechaf"];
$prefijobd = $_GET["prefijodb"];
set_time_limit(300);

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

	//require_once('cnx_cfdi.php');
	require_once('cnx_cfdi2.php');
	mysqli_select_db($cnx_cfdi2,$database_cfdi);
	require_once('lib_mpdf/pdf/mpdf.php');
	//mysql_select_db($database_cfdi, $cnx_cfdi);
	

	//mysql_query("SET NAMES 'utf8'");

	

		$mpdf = new mPDF('c', 'A4');
		$css = file_get_contents('css/style_pdf.css');
		//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
		$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
		//$mpdf->setFooter('Página {PAGENO}');
		$mpdf->defaultfooterline = 0;
		$mpdf->writeHTML($css, 1);
		
	//Buscar datos para encabezado
	$resSQL01 = "SELECT * FROM ".$prefijobd."systemsettings LIMIT 1";
	$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
	while($rowSQL01 = mysqli_fetch_array($runSQL01)){
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
	

// Consultar Cargos Unidades
$resSQL00 = "
SELECT C.Folio, C.Creado, C.Importe, U.Unidad, C.Comentarios, C.Documentador AS DocumCU
FROM {$prefijobd}cargosunidades C
LEFT JOIN {$prefijobd}unidades U ON U.ID = C.Unidad_RID
WHERE Creado >= '{$fecha_inicio_t2} 00:00:00' 
  AND Creado <= '{$fecha_fin_t2} 23:59:59'
ORDER BY Folio
";

$runSQL00 = mysqli_query($cnx_cfdi2, $resSQL00);
$numero_reg = mysqli_num_rows($runSQL00);
//die($resSQL00);
$htmlGlobal = ''; 

while ($rowSQL00 = mysqli_fetch_array($runSQL00)) {
    $folio = $rowSQL00['Folio'];
    $creado = date("d-m-Y", strtotime($rowSQL00['Creado']));
    $importe = '$' . number_format($rowSQL00['Importe'], 2);
    $nom_unidad = $rowSQL00['Unidad'];
    $comentarios = $rowSQL00['Comentarios'];
    $documentador = $rowSQL00['DocumCU'];

    $html = '
    <header class="clearfix">
    <meta charset="utf-8">
    <div class="row">
        <div style="width: 100%;">
            <div style="width: 50%; float: left;">
                <p style="font-size: 12px;">
                    <strong>'.$RazonSocial.'</strong><br>
                    '.$Calle.' '.$NumeroExterior.', '.$Colonia.'<br>
                    '.$Municipio.', '.$Estado.'<br>'.$RFC.'
                </p>
            </div>
            <div style="width: 50%; float: left; text-align: right;">
                <img src="img_logos/TPSMARTI1.jpeg" width="150">
            </div>
        </div>
        <h1 style="font-size: 15px;">Cargos Unidades</h1>
    </div>

    <div class="row">
        <div style="width: 100%;">
            <div style="width: 70%; float: left;"></div>
            <div style="width: 30%; float: left; text-align: right;">
                <table border="0" style="font-size: 10px;">
                    <tr><td align="right"><b>Vale:</b></td><td>'.$folio.'</td></tr>
                    <tr><td align="right"><b>Fecha:</b></td><td>'.$creado.'</td></tr>
                    <tr><td align="right"><b>Importe:</b></td><td>'.$importe.'</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div style="width: 100%;">
            <div style="width: 70%; float: left;">
                <table border="0" style="font-size: 10px;">
                    <tr><td width="20%"><b>Unidad:</b></td><td width="80%">'.$nom_unidad.'</td></tr>
                    <tr><td><b>Comentario:</b></td><td>'.$comentarios.'</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div style="width: 100%;">
            <div style="width: 45%; float: left;">
                <br><br><br>
            </div>
            <div style="width: 55%; float: left; text-align: center;">
                <table border="0" style="font-size: 10px;">
                    <tr><td>'.$documentador.'</td></tr>
                    <tr><td><b>Documentador</b></td></tr>
                    <tr><td><b>Autoriza</b></td></tr>
                </table>
            </div>
        </div>
    </div>
    </header>';

    
    if ($c1 < $numero_reg - 1) {
        $html .= '<pagebreak>';
    }

    $htmlGlobal .= $html;
    $c1++;
}


$mpdf->WriteHTML($htmlGlobal);
$mpdf->Output('cargos_unidades_' . date("His_d-m-Y") . '.pdf', 'I');

		
		//$mpdf->Output('cargos_unidades.pdf', 'I');
		
		
		

	
}


?>