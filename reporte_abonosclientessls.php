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


header("Content-type: application/vnd.ms-excel");
$nombre="Reporte de Abonos Clientes ".date("h:i:s")."_".date("d-m-Y").".xls";
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
			<th align="center" style="font-size: 12px;" colspan="7"><?php echo $RazonSocial.'</strong>' ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;" colspan="7"><?php echo "COBRANZA POR CLIENTE ".$moneda." DEL: ".$fechai." AL: ".$fechaf; ?></th>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;">Fecha</th>
			<th align="center" style="font-size: 12px;">Folio</th>
			<th align="center" style="font-size: 12px;">Cliente</th>
			<th align="center" style="font-size: 12px;">Subtotal</th>
			<th align="center" style="font-size: 12px;">IVA</th>
			<th align="center" style="font-size: 12px;">Retenido</th>
			<th align="center" style="font-size: 12px;">Neto</th>
		</tr>
	</thead>
<tbody>	

<?php


	$resSQL05 = "select a.Cliente_RID, b.RazonSocial from ".$prefijobd."abonos a Inner Join ".$prefijobd."clientes b On a.Cliente_RID = b.Id where Date(a.Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and a.Oficina_RID=".$oficina_id." and a.Moneda='".$moneda."' And a.cCanceladoT Is NULL Group By a.Cliente_RID, b.RazonSocial Order By b.RazonSocial";

	$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
	while($rowSQL05 = mysql_fetch_array($runSQL05)){	
	
		$id_cliente = $rowSQL05['Cliente_RID'];

		$resSQL02 = "select * from ".$prefijobd."abonos where Cliente_RID = ".$id_cliente." and Oficina_RID=".$oficina_id." and Moneda='".$moneda."' And cCanceladoT Is NULL And Date(Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' Order By Fecha, XFolio";
	
		$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
		while($rowSQL02 = mysql_fetch_array($runSQL02)){	
		
			//Buscar nombre del cliente
			$resSQL08 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$id_cliente;
			$runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
			while($rowSQL08 = mysql_fetch_array($runSQL08)){
				$nom_cliente = $rowSQL08['RazonSocial'];
			}

			$creado = $rowSQL02['Fecha'];
			$folio = $rowSQL02['XFolio'];
			$moneda_t = $rowSQL02['Moneda'];
					
			$subtotal_t = $rowSQL02['TotalSubtotal']; 
			$subtotal = "$".number_format($subtotal_t,2);
					
			$totaliva_t = $rowSQL02['TotalIVA'];
			$totaliva = "$".number_format($totaliva_t,2);
					
			$totalretenido_t = $rowSQL02['TotalRetencion'];
			$totalretenido = "$".number_format($totalretenido_t,2);
					
			$totalImporte_t = $rowSQL02['TotalImporte'];
			$totalImporte = "$".number_format($totalImporte_t,2);

			$comentarios = $rowSQL02['Comentarios'];
			$fechacancelado = $rowSQL02['cCanceladoT'];
?>

			<tr>
				<td align="center"><?php echo $creado; ?></td>
				<td align="center"><?php echo $folio; ?></td>
				<td align="left"><?php echo $nom_cliente; ?></td>
				<td align="right"><?php echo $subtotal; ?></td>
				<td align="right"><?php echo $totaliva; ?></td>
				<td align="right"><?php echo $totalretenido; ?></td>
				<td align="right" ><?php echo $totalImporte; ?></td>
			</tr>

<?php	
		} // FIN del WHILE $resSQL02

		//Agregar Totales x Cliente
		$resSQL03 = "select SUM(a.TotalSubtotal) as Sub, SUM(a.TotalIVA) as IVA, SUM(a.TotalRetencion) as Ret, SUM(a.TotalImporte) as Tot from ".$prefijobd."abonos a where a.Cliente_RID=".$id_cliente." and Date(a.Fecha) Between '".$fechai." 00:00:00' And '".$fechaf." 23:59:59' and a.Oficina_RID=".$oficina_id." and a.Moneda='".$moneda."' And a.cCanceladoT Is NULL Order By a.Fecha, a.XFolio";
	
		$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
		while($rowSQL03 = mysql_fetch_array($runSQL03)){
			$SubFec_t = $rowSQL03['Sub'];
			$SubFec = "$".number_format($SubFec_t,2);
			$IvaFec_t = $rowSQL03['IVA'];
			$IvaFec = "$".number_format($IvaFec_t,2);
			$RetFec_t = $rowSQL03['Ret'];
			$RetFec = "$".number_format($RetFec_t,2);
			$TotFec_t = $rowSQL03['Tot'];
			$TotFec = "$".number_format($TotFec_t,2);
		}
?>

		<tr>
			<td colspan="3" align="right"><strong>SUMAS</strong></td>
			<td align="right"><?php echo $SubFec; ?></td>
			<td align="right"><?php echo $IvaFec; ?></td>
			<td align="right"><?php echo $RetFec; ?></td>
			<td align="right"><?php echo $TotFec; ?></td>
		</tr>

<?php

	}
	

 // FIN del WHILE $resSQL01

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


