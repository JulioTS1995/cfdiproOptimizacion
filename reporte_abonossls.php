<?php  
//Recibir variables
$prefijobd = $_POST['prefijodb'];
$fechai = $_POST['txtDesde'];
$fechaf = $_POST['txtHasta'];
$cliente_id = $_POST['cliente'];
$moneda = $_POST['moneda'];
//$oficina_id = $_POST['oficina'];
$boton = $_POST['btnEnviar'];

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

$Serie = 'AP';

$resSQL0 = "SELECT DISTINCT(ID) FROM ".$prefijobd."oficinas where Serie='".$Serie."'";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){
	$oficina_id = $rowSQL0['ID'];
}

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
	$sql_cliente2="";
} else {
	$sql_cliente=" AND Cliente_RID = ".$cliente_id;
	$sql_cliente2=" AND a.Cliente_RID = ".$cliente_id;
}

if($boton == 'PDF' and $Serie == 'AP' and ($moneda == 'PESOS' or $moneda == 'DOLARES' )){

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> 
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Reporte de Abonos - Oficina '.$Serie.'</h1>';


       
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
                      <th align="left" style="font-size: 12px;">Observaciones</th>
                    </tr>
                  </thead>
                  <tbody>';


                
                //Agrupar por cliente
				$resSQL01 = "select * from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' and Oficina_RID=".$oficina_id.$sql_cliente." and Moneda='".$moneda."'";
                
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);
					$comentarios = $rowSQL01['Comentarios'];
								
				
                $html.='
                    <tr>
					  <td align="center">'.$creado.'</td>
                      <td align="center">'.$folio.'</td>
					  <td align="center">'.$moneda_t.'</td>
                      <td align="right">'.$totalImporte.'</td>
                      <td align="left" >'.$comentarios.'</td>
                    </tr>

                    ';
					
				} // FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
					$resSQL04 = "select SUM(TotalImporte) AS TSaldo from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' and Oficina_RID=".$oficina_id.$sql_cliente." and Moneda='".$moneda."'";
				
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
					$html.='     
						<tr>
						  <td colspan="5"><hr></td>
						</tr>
						<tr>
						  <td colspan="3" align="right"><strong>TOTAL</strong></td>
						  <td align="right"><strong>'.$Tsaldo.'</strong></td>
						</tr>
						<tr>
						  <td colspan="5"><hr></td>
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
$mpdf->Output('Reporte de Abonos - Oficina '.$Serie.'.pdf', 'I');

} elseif ($boton == 'Excel' and $Serie == 'AP' and ($moneda == 'PESOS' or $moneda == 'DOLARES')) {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Reporte de Abonos ".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "COBRANZA POR FOLIO ".$moneda." DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha</th>
							<th align="center" style="font-size: 12px;">Folio</th>
							<th align="center" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Subtotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
							<th align="center" style="font-size: 12px;">Cancelado</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	
				$resSQL01 = "select * from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."' Order By XFolio";
		
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					
					$id_cliente = $rowSQL01['Cliente_RID'];
					
					//Buscar nombre del cliente
					$resSQL08 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
					while($rowSQL08 = mysql_fetch_array($runSQL08)){
						$nom_cliente = $rowSQL08['RazonSocial'];
					}

					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					
					$subtotal_t = $rowSQL01['TotalSubtotal']; 
					$subtotal = "$".number_format($subtotal_t,2);
					
					$totaliva_t = $rowSQL01['TotalIVA'];
					$totaliva = "$".number_format($totaliva_t,2);
					
					$totalretenido_t = $rowSQL01['TotalRetencion'];
					$totalretenido = "$".number_format($totalretenido_t,2);
					
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);

					$comentarios = $rowSQL01['Comentarios'];
					$fechacancelado = $rowSQL01['cCanceladoT'];
						
	?>
					<tr>
					  <td align="center"><?php echo $creado; ?></td>
                      <td align="center"><?php echo $folio; ?></td>
					  <td align="left"><?php echo $nom_cliente; ?></td>
					  <td align="right"><?php echo $subtotal; ?></td>
					  <td align="right"><?php echo $totaliva; ?></td>
					  <td align="right"><?php echo $totalretenido; ?></td>
                      <td align="right" ><?php echo $totalImporte; ?></td>
                      <td align="center" ><?php echo $fechacancelado; ?></td>
                    </tr>
	<?php	
					}// FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
				$resSQL04 = "select SUM(TotalImporte) AS TSaldo, SUM(TotalSubtotal) as TSubtotal, SUM(TotalIVA) as TIVA, SUM(TotalRetencion) as TRetencion from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
						
						$TSubtotal_t = $rowSQL04['TSubtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						
						$TIVA_t = $rowSQL04['TIVA'];
						$TIVA = "$".number_format($TIVA_t,2);
						
						$TRetencion_t = $rowSQL04['TRetencion'];
						$TRetencion = "$".number_format($TRetencion_t,2);
					
					}	
	?>
						<tr>
							<td colspan="3" align="right"><strong>TOTALES</strong></td>
							<td align="right"><strong><?php echo $TSubtotal; ?></strong></td>
							<td align="right"><strong><?php echo $TIVA; ?></strong></td>
							<td align="right"><strong><?php echo $TRetencion; ?></strong></td>
							<td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						</tr>
						
				</tbody>
             </table>  
      
<?php 
	}elseif($boton == 'PDF' and $Serie == 'NC' and $moneda == 'PESOS'){

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> 
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Reporte de Abonos - Oficina '.$Serie.'</h1>';


       
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
					  <th align="left" style="font-size: 12px;">Cliente</th>
                      <th align="right" style="font-size: 12px;">Importe</th>
                    </tr>
                  </thead>
                  <tbody>';

                //Agrupar por cliente
					$resSQL01 = "select *,c.RazonSocial from ".$prefijobd."abonos as a inner join ".$prefijobd."clientes c on a.Cliente_RID=c.ID
					where Date(a.Fecha) Between '".$fechai."' And '".$fechaf."' and a.Oficina_RID=".$oficina_id.$sql_cliente2." and a.Moneda='".$moneda."'";
                
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);
					$clientes = $rowSQL01['RazonSocial'];
								
				
                $html.='
                    <tr>
					  <td align="center">'.$creado.'</td>
                      <td align="center">'.$folio.'</td>
					  <td align="center">'.$moneda_t.'</td>
					  <td align="left" >'.$clientes.'</td>
                      <td align="right">'.$totalImporte.'</td>
                    </tr>

                    ';
					
				} // FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
					$resSQL04 = "select SUM(TotalImporte) AS TSaldo from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' and Oficina_RID=".$oficina_id.$sql_cliente." and Moneda='".$moneda."'";
		
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
					}
					
					$html.='     
						<tr>
						  <td colspan="5"><hr></td>
						</tr>
						<tr>
						  <td colspan="4" align="right"><strong>TOTAL</strong></td>
						  <td align="right"><strong>'.$Tsaldo.'</strong></td>
						</tr>
						<tr>
						  <td colspan="5"><hr></td>
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
$mpdf->Output('Reporte de Abonos - Oficina '.$Serie.'.pdf', 'I');

}elseif ($boton == 'Excel' and $Serie == 'NC' and $moneda == 'PESOS') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Notas Credito Pesos ".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "NOTAS DE CREDITO POR FOLIO (PESOS) DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha</th>
							<th align="center" style="font-size: 12px;">Folio</th>
							<th align="center" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Subtotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
							<th align="center" style="font-size: 12px;">Cancelado</th>
						</tr>
					</thead>
					<tbody>	

	<?php
	
				$resSQL01 = "select * from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."'";
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){

					$id_cliente = $rowSQL01['Cliente_RID'];
					
					//Buscar nombre del cliente
					$resSQL28 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL28 = mysql_query($resSQL28, $cnx_cfdi);
							
					while($rowSQL28 = mysql_fetch_array($runSQL28)){
						$nom_cliente = $rowSQL28['RazonSocial'];
					}
		
					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					
					$subtotal_t = $rowSQL01['TotalSubtotal']; 
					$subtotal = "$".number_format($subtotal_t,2);
					
					$totaliva_t = $rowSQL01['TotalIVA'];
					$totaliva = "$".number_format($totaliva_t,2);
					
					$totalretenido_t = $rowSQL01['TotalRetencion'];
					$totalretenido = "$".number_format($totalretenido_t,2);
					
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);
					
					$fechacancelado = $rowSQL01['cCanceladoT'];
				
	?>
					<tr>
					  <td align="center"><?php echo $creado; ?></td>
                      <td align="center"><?php echo $folio; ?></td>
					  <td align="left" ><?php echo $nom_cliente; ?></td>
  					  <td align="right"><?php echo $subtotal; ?></td>
					  <td align="right"><?php echo $totaliva; ?></td>
					  <td align="right"><?php echo $totalretenido; ?></td>
                      <td align="right" ><?php echo $totalImporte; ?></td>
                      <td align="center" ><?php echo $fechacancelado; ?></td>
                    </tr>
					
	<?php	
					}// FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
				$resSQL04 = "select SUM(TotalImporte) AS TSaldo, SUM(TotalSubtotal) as TSubtotal, SUM(TotalIVA) as TIVA, SUM(TotalRetencion) as TRetencion from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
						
						$TSubtotal_t = $rowSQL04['TSubtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						
						$TIVA_t = $rowSQL04['TIVA'];
						$TIVA = "$".number_format($TIVA_t,2);
						
						$TRetencion_t = $rowSQL04['TRetencion'];
						$TRetencion = "$".number_format($TRetencion_t,2);
					}	
	?>
						
						<tr>
							<td colspan="3" align="right"><strong>TOTALES</strong></td>
							<td align="right"><strong><?php echo $TSubtotal; ?></strong></td>
							<td align="right"><strong><?php echo $TIVA; ?></strong></td>
							<td align="right"><strong><?php echo $TRetencion; ?></strong></td>
							<td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						</tr>
						
				</tbody>
             </table>  
      
<?php 
	}elseif($boton == 'PDF' and $Serie == 'NC' and $moneda == 'DOLARES'){

$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> 
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Reporte de Abonos - Oficina '.$Serie.'</h1>';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <!-- <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>-->
              
              <div><br></div>
              <div>
                <table>
                  <thead>
					<tr>
						<th colspan="5"></th>
						<th>DOLARES</th>
						<th>PESOS</th>
					</tr>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha</th>
					  <th align="center" style="font-size: 12px;">Folio</th>
					  <th align="center" style="font-size: 12px;">Moneda</th>
					  <th align="left" style="font-size: 12px;">Cliente</th>
					  <th align="center" style="font-size: 12px;">Tipo Cambio</th>
                      <th align="right" style="font-size: 12px;">Importe</th>
					  <th align="right" style="font-size: 12px;">Importe</th>
                    </tr>
                  </thead>
                  <tbody>';

                //Agrupar por cliente
					$resSQL01 = "select *,c.RazonSocial from ".$prefijobd."abonos as a inner join ".$prefijobd."clientes c on a.Cliente_RID=c.ID
					where Date(a.Fecha) Between '".$fechai."' And '".$fechaf."' and a.Oficina_RID=".$oficina_id.$sql_cliente2." and a.Moneda='".$moneda."'";
                
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);
					$clientes = $rowSQL01['RazonSocial'];
					$tipoCambio_t = $rowSQL01['TipoCambio'];
					$tipoCambio = "$".number_format($tipoCambio_t,2);
					$totalImportePesos = "$".number_format($rowSQL01['TotalImporte']*$rowSQL01['TipoCambio'],2);			
				
                $html.='
                    <tr>
					  <td align="center">'.$creado.'</td>
                      <td align="center">'.$folio.'</td>
					  <td align="center">'.$moneda_t.'</td>
					  <td align="left" >'.$clientes.'</td>
					  <td align="right" >'.$tipoCambio.'</td>
                      <td align="right">'.$totalImporte.'</td>
					  <td align="right">'.$totalImportePesos.'</td>
                    </tr>

                    ';
					
				} // FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
					$resSQL04 = "select SUM(TotalImporte) AS TSaldo,TipoCambio from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai."' And '".$fechaf."' and Oficina_RID=".$oficina_id.$sql_cliente." and Moneda='".$moneda."'";
				
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$tipoCambio_t = $rowSQL04['TipoCambio'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
						$TsaldoPesos = "$".number_format($rowSQL04['TSaldo']*$rowSQL04['TipoCambio'],2);
						
					}
					
					$html.='     
						<tr>
						  <td colspan="7"><hr></td>
						</tr>
						<tr>
						  <td colspan="5" align="right"><strong>TOTAL</strong></td>
						  <td align="right"><strong>'.$Tsaldo.'</strong></td>
						  <td align="right"><strong>'.$TsaldoPesos.'</strong></td>
						</tr>
						<tr>
						  <td colspan="7"><hr></td>
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
$mpdf->Output('Reporte de Abonos - Oficina '.$Serie.'.pdf', 'I');

}
elseif ($boton == 'Excel' and $Serie == 'NC' and $moneda == 'DOLARES') {
	header("Content-type: application/vnd.ms-excel");
	$nombre="Reporte de Abonos - Oficina '".$Serie."'_".date("h:i:s")."_".date("d-m-Y").".xls";
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
							<th align="center" style="font-size: 12px;" colspan="8"><?php echo "NOTAS DE CREDITO POR FOLIO (DOLARES) DEL: ".$fechai." AL: ".$fechaf; ?></th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;">Fecha</th>
							<th align="center" style="font-size: 12px;">Folio</th>
							<th align="center" style="font-size: 12px;">Cliente</th>
							<th align="center" style="font-size: 12px;">Subtotal</th>
							<th align="center" style="font-size: 12px;">IVA</th>
							<th align="center" style="font-size: 12px;">Retenido</th>
							<th align="center" style="font-size: 12px;">Neto</th>
							<th align="center" style="font-size: 12px;">Cancelado</th>
						</tr>
					</thead>
					<tbody>	
	<?php
	
				$resSQL01 = "select * from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."'";
                
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					
					$id_cliente = $rowSQL01['Cliente_RID'];
					
					//Buscar nombre del cliente
					$resSQL28 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
					$runSQL28 = mysql_query($resSQL28, $cnx_cfdi);
							
					while($rowSQL28 = mysql_fetch_array($runSQL28)){
						$nom_cliente = $rowSQL28['RazonSocial'];
					}
					
					$creado = $rowSQL01['Fecha'];
					$folio = $rowSQL01['XFolio'];
					$moneda_t = $rowSQL01['Moneda'];
					
					$subtotal_t = $rowSQL01['TotalSubtotal']; 
					$subtotal = "$".number_format($subtotal_t,2);
					
					$totaliva_t = $rowSQL01['TotalIVA'];
					$totaliva = "$".number_format($totaliva_t,2);
					
					$totalretenido_t = $rowSQL01['TotalRetencion'];
					$totalretenido = "$".number_format($totalretenido_t,2);
					
					$totalImporte_t = $rowSQL01['TotalImporte'];
					$totalImporte = "$".number_format($totalImporte_t,2);
					$clientes = $rowSQL01['RazonSocial'];
					$tipoCambio_t = $rowSQL01['TipoCambio'];
					$tipoCambio = "$".number_format($tipoCambio_t,2);
					$totalImportePesos = "$".number_format($rowSQL01['TotalImporte']*$rowSQL01['TipoCambio'],2);

					$fechacancelado = $rowSQL01['cCanceladoT'];
	?>
					<tr>
					  <td align="center"><?php echo $creado; ?></td>
                      <td align="center"><?php echo $folio; ?></td>
					  <td align="left" ><?php echo $nom_cliente; ?></td>
  					  <td align="right"><?php echo $subtotal; ?></td>
					  <td align="right"><?php echo $totaliva; ?></td>
					  <td align="right"><?php echo $totalretenido; ?></td>
                      <td align="right" ><?php echo $totalImporte; ?></td>
					  <td align="right" ><?php echo $fechacancelado; ?></td>
                    </tr>
	<?php	
					}// FIN del WHILE $resSQL01	
					//////Agregar Totales por Clientes
				$resSQL04 = "select SUM(TotalImporte) AS TSaldo, SUM(TotalSubtotal) as TSubtotal, SUM(TotalIVA) as TIVA, SUM(TotalRetencion) as TRetencion from ".$prefijobd."abonos where Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and Oficina_RID=".$oficina_id." and Moneda='".$moneda."'";
					$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
					while($rowSQL04 = mysql_fetch_array($runSQL04)){
						$Tsaldo_t = $rowSQL04['TSaldo'];
						$Tsaldo = "$".number_format($Tsaldo_t,2);
						
						$TSubtotal_t = $rowSQL04['TSubtotal'];
						$TSubtotal = "$".number_format($TSubtotal_t,2);
						
						$TIVA_t = $rowSQL04['TIVA'];
						$TIVA = "$".number_format($TIVA_t,2);
						
						$TRetencion_t = $rowSQL04['TRetencion'];
						$TRetencion = "$".number_format($TRetencion_t,2);
					}
	?>
						
						<tr>
							<td colspan="3" align="right"><strong>TOTALES</strong></td>
							<td align="right"><strong><?php echo $TSubtotal; ?></strong></td>
							<td align="right"><strong><?php echo $TIVA; ?></strong></td>
							<td align="right"><strong><?php echo $TRetencion; ?></strong></td>
							<td align="right"><strong><?php echo $Tsaldo; ?></strong></td>
						</tr>
						
				</tbody>
             </table>  
      
<?php 
	}
?>