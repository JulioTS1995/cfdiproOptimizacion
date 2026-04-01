<?php
$prefijobd = $_GET['prefijodb'];
$sucursal = $_GET['sucursal'];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Gastos_No_Depositados_".$fecha_inicio_f."-"."$fecha_fin_f"."__".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
	<thead>
	<tr>
		<th align="center" colspan="7" style="font-size: 18px;">Gastos No Depositados. Periodo: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?></th>
	</tr>
	<tr>
		<th align="center" style="font-size: 12px;">Folio</th>
		<th align="center" style="font-size: 12px;">Fcha Creado</th>
		<th align="center" style="font-size: 12px;">Operador</th>
		<th align="center" style="font-size: 12px;">Unidad</th>
		<th align="center" style="font-size: 12px;">Concepto</th>
		<th align="center" style="font-size: 12px;">Banco</th>
		<th align="center" style="font-size: 12px;">Importe</th>
	</tr>
	</thead>
	<tbody>

<?php

$resSQL="SELECT g.ID, g.XFolio, g.Fecha,(SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = g.OperadorNombre_RID) AS Operador, 
	(SELECT Unidad FROM ".$prefijobd."Unidades WHERE ID = g.Unidad_RID) AS Unidad, 
	(SELECT Banco FROM ".$prefijobd."Bancos WHERE ID = g.TransferenciaBanco_RID) AS Banco, 
	g.Importe, g.Concepto FROM ".$prefijobd."GastosViajes AS g WHERE g.Depositado='0' AND 
	Date(g.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND OficinaGastos_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal."  ORDER BY g.Fecha;";
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$gastoID = $rowSQL['ID'];
		$xfolio = $rowSQL['XFolio'];
		$creado = $rowSQL['Fecha'];
		$operador = $rowSQL['Operador'];
		$unidad = $rowSQL['Unidad'];
		$concepto = $rowSQL['Concepto'];
		$banco = $rowSQL['Banco'];
		$importe = $rowSQL['Importe'];

		$creado = date("d-m-Y", strtotime($creado));
		?>
			<tr>
				<td align="center"><?php echo $xfolio ?> </td>
				<td align="left"><?php echo $creado ?> </td>
				<td align="left"><?php echo $operador ?> </td>
				<td align="left"><?php echo $unidad ?> </td>
				<td align="left"><?php echo $concepto ?> </td>
				<td align="left"><?php echo $banco ?> </td>
				<td align="left"><?php echo ("$".number_format($importe,2)) ?> </td>
			</tr>
		<?php

                  } // FIN del WHILE
				  ?>
	</tbody>
</table>
