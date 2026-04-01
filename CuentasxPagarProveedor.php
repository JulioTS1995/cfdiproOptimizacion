<?php  

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

//Recibir variables
$prefijobd = $_GET["prefijobd"];
$imagen="imagenes/".$prefijobd.".jpg";

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$fecha = $anio_logs."-".$mes_logs."-".$dia_logs;  

if (!isset($_GET['prefijobd']) || empty($_GET['prefijobd'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijodb = @mysql_escape_string($_GET["prefijobd"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijodb = $prefijobd . "_";
} 

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


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
	    <img style="float:left; margin:10px;" alt="" src="'.$imagen.'" width="150px">  
		<p align=center><font size=6>'.$RazonSocial.'</font> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.', '.$RFC.' </p>
 	  </div>

      <h1 style="font-size: 16px;" align=right>Cuentas por Pagar por Proveedor</h1>';

			$html .='
				<br>
				<hr>
                <table style="width:100%">
                  <thead>
                    <tr>
					  <th style="width:200px" align=left>Proveedor</th>
                      <th style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total</th>
					  <th style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Abonos</th>
                      <th style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saldo</th>
                      <th style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Acumulado</th>
                    </tr>
				  </thead>
				 <tbody>';


$TotalAcumulado = 0;

//Buscar datos Banco Proveedor (Origen)
$resSQL1 = "SELECT * FROM ".$prefijobd."proveedores";
$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
While ($rowSQL1 = mysql_fetch_array($runSQL1)){
	$Saldo = 0;
	$Idproveedor = $rowSQL1['ID'];
	$Rzproveedor = $rowSQL1['RazonSocial'];

	//Sumar Total Compras
	$resSQL2 = "SELECT Sum(Total) As TotalCompras FROM ".$prefijobd."compras WHERE ProveedorNo_RID=".$Idproveedor."";
	$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
	while ($rowSQL2 = mysql_fetch_array($runSQL2)){
	$TotalCompras_t = $rowSQL2['TotalCompras'];
	$TotalCompras = number_format($TotalCompras_t,2);
	}
	
	//Sumar Total Pagos
	$resSQL3 = "SELECT Sum(Total) As TotalPagos FROM ".$prefijobd."pagos WHERE Proveedor_RID=".$Idproveedor."";
	$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
	while ($rowSQL3 = mysql_fetch_array($runSQL3)){
	$TotalPagos_t = $rowSQL3['TotalPagos'];
	$TotalPagos = number_format($TotalPagos_t,2);
	}

	$Saldo = $TotalCompras_t - $TotalPagos_t;
	$Saldo_t =number_format($Saldo,2);
	$TotalAcumulado = $TotalAcumulado + $Saldo;
	$TotalAcumulado_t = number_format($TotalAcumulado,2);

	If ($Saldo_t>0) {
	//If ($TotalCompras>0) {	

		$html.='
        <tr>
		  <td style="width:200px" align=left>'.$Rzproveedor.'</td>
          <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$TotalCompras.'</td>
		  <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$TotalPagos.'</td>
		  <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$Saldo_t.'</td>
		  <td style="width:50px" align=center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.$TotalAcumulado_t.'</td>
        </tr>';

	}
}	

$html.='</tbody>
		</table>
		</header>';

$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('CuentasxPagarProveedor.pdf', 'I');


?>