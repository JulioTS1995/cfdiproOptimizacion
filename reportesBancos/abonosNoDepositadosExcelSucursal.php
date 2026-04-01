<?php
$prefijobd = $_GET['prefijodb'];
$sucursal = $_GET["sucursal"];//trae sucursal

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Abonos_No_Depositados_".$fecha_inicio_f."-"."$fecha_fin_f"."__".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
	<thead>
	<tr>
		<th align="center" colspan="11" style="font-size: 18px;">Abonos No Depositados. Periodo: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?></th>
	</tr>
	<tr>
		<th align="center" style="font-size: 12px;">Folio</th>
		<th align="center" style="font-size: 12px;">Fcha Creado</th>
		<th align="center" style="font-size: 12px;">Cliente</th>
		<th align="center" style="font-size: 12px;">Facturas</th>
		<th align="center" style="font-size: 12px;">Forma Pago</th>
		<th align="center" style="font-size: 12px;">Banco</th>
		<th align="center" style="font-size: 12px;">Cuenta Bancaria</th>
		<th align="center" style="font-size: 12px;">Importe</th>
		<th align="center" style="font-size: 12px;">Subtotal</th>
		<th align="center" style="font-size: 12px;">IVA</th>
		<th align="center" style="font-size: 12px;">Retenido</th>
	</tr>
	</thead>
	<tbody>

<?php

$resSQL="SELECT a.ID, a.XFolio, a.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Clientes WHERE ID = a.Cliente_RID) AS Cliente, 
(SELECT Descripcion FROM ".$prefijobd."TablaGeneral WHERE ID = a.formapago33_RID) AS FormaPagoD, 
(SELECT Banco FROM ".$prefijobd."Bancos WHERE ID = a.CuentaBancaria_RID) AS Banco, 
(SELECT CLABE FROM ".$prefijobd."Bancos WHERE ID = a.CuentaBancaria_RID) AS CLABE, 
a.TotalImporte, a.TotalSubtotal, a.TotalIVA, a.TotalRetencion FROM ".$prefijobd."Abonos AS a WHERE a.Depositado='0' AND 
Date(a.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND a.Oficina_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ORDER BY a.Fecha;";
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$abonoID = $rowSQL['ID'];
		$xfolio = $rowSQL['XFolio'];
		$creado = $rowSQL['Fecha'];
		$cliente = $rowSQL['Cliente'];
		$formaPago = $rowSQL['FormaPagoD'];
		$banco = $rowSQL['Banco'];
		$clabe = $rowSQL['CLABE'];
		$total = $rowSQL['TotalImporte'];
		$subtotal = $rowSQL['TotalSubtotal'];
		$iva = $rowSQL['TotalIVA'];
		$retencion = $rowSQL['TotalRetencion'];

		$creado = date("d-m-Y", strtotime($creado));
		$queryAbonosSub = "SELECT (SELECT XFolio FROM ".$prefijobd."Factura WHERE ID = aSub.AbonoFactura_RID) AS FolioFactura FROM ".$prefijobd."AbonosSub AS aSub WHERE FolioSub_RID ='".$abonoID."';"; 
		$runsqlAbonosSub = mysqli_query($cnx_cfdi2, $queryAbonosSub);
		if (!$runsqlAbonosSub) {//debug
			$mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
			$mensaje .= 'Consulta completa: ' . $queryAbonosSub;
			die($mensaje);
		}
		while ($rowsqlAbonosSub = mysqli_fetch_assoc($runsqlAbonosSub)){
			$facturas = $rowsqlAbonosSub['FolioFactura'];
?>
			<tr>
				<td align="center"><?php echo $xfolio ?> </td>
				<td align="left"><?php echo $creado ?> </td>
				<td align="left"><?php echo $cliente ?> </td>
				<td align="left"><?php echo $facturas ?> </td>
				<td align="left"><?php echo $formaPago ?> </td>
				<td align="left"><?php echo $banco ?> </td>
				<td align="left"><?php echo $clabe ?> </td>
				<td align="left"><?php echo ("$".number_format($total,2)) ?> </td>
				<td align="left"><?php echo ("$".number_format($subtotal,2)) ?> </td>
				<td align="left"><?php echo ("$".number_format($iva,2)) ?> </td>
				<td align="left"><?php echo ("$".number_format($retencion,2)) ?> </td>
			</tr>
<?php
		}

				

                  } // FIN del WHILE
				  ?>
	</tbody>
</table>
