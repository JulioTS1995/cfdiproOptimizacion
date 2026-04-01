<?php
$prefijobd = $_GET['prefijodb'];
$sucursal = $_GET["sucursal"];//trae sucursal

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Pagos_Depositados_".$fecha_inicio_f."-"."$fecha_fin_f"."__".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
	<thead>
	<tr>
		<th align="center" colspan="7" style="font-size: 18px;">Pagos Depositados. Periodo: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?></th>
	</tr>
	<tr>
		<th align="center" style="font-size: 12px;">Folio</th>
		<th align="center" style="font-size: 12px;">Fcha Creado</th>
		<th align="center" style="font-size: 12px;">Proveedor</th>
		<th align="center" style="font-size: 12px;">Factura</th>
		<th align="center" style="font-size: 12px;">Compra</th>
		<th align="center" style="font-size: 12px;">Forma Pago</th>
		<th align="center" style="font-size: 12px;">Importe</th>
	</tr>
	</thead>
	<tbody>

<?php

$resSQL="SELECT p.ID, p.XFolio, p.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Proveedores WHERE ID = p.Proveedor_RID) AS Proveedor, 
	p.Total FROM ".$prefijobd."Pagos AS p WHERE p.Depositado='1' AND 
	Date(p.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND p.OficinaPagos_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal.") ORDER BY p.Fecha;";
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$pagoID = $rowSQL['ID'];
		$xfolio = $rowSQL['XFolio'];
		$creado = $rowSQL['Fecha'];
		$proveedor = $rowSQL['Proveedor'];
		$total = $rowSQL['Total'];

		$creado = date("d-m-Y", strtotime($creado));
		$queryPagosSub = "SELECT (SELECT XFolio FROM ".$prefijobd."Compras WHERE ID = pSub.Compra_RID) AS FolioCompras, FormaPago, 
		FacturaP  FROM ".$prefijobd."PagosSub AS pSub WHERE FolioSubPago_RID ='".$pagoID."';"; 
		$runsqlPagosSub = mysqli_query($cnx_cfdi2, $queryPagosSub);
		if (!$runsqlPagosSub) {//debug
			$mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
			$mensaje .= 'Consulta completa: ' . $queryPagosSub;
			die($mensaje);
		}
		while ($rowsqlPagosSub = mysqli_fetch_assoc($runsqlPagosSub)){
			$compra = $rowsqlPagosSub['FolioCompras'];
			$formaPago = $rowsqlPagosSub['FormaPago'];
			$factura = $rowsqlPagosSub['FacturaP'];
			?>
				<tr>
					<td align="center"><?php echo $xfolio ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $proveedor ?> </td>
					<td align="left"><?php echo $factura ?> </td>
					<td align="left"><?php echo $compra ?> </td>
					<td align="left"><?php echo $formaPago ?> </td>
					<td align="left"><?php echo ("$".number_format($total,2)) ?> </td>
				</tr>
			<?php
		}

                  } // FIN del WHILE
				  ?>
	</tbody>
</table>
