<?php
$prefijobd = $_GET['prefijodb'];
$sucursal = $_GET['sucursal'];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Liquidaciones_No_Depositadas_".$fecha_inicio_f."-"."$fecha_fin_f"."__".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
	<thead>
	<tr>
		<th align="center" colspan="7" style="font-size: 18px;">Liquidaciones No Depositadas. Periodo: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?></th>
	</tr>
	<tr>
		<th align="center" style="font-size: 12px;">Folio</th>
		<th align="center" style="font-size: 12px;">Fcha Creado</th>
		<th align="center" style="font-size: 12px;">Operador</th>
		<th align="center" style="font-size: 12px;">Unidad</th>
		<th align="center" style="font-size: 12px;">Remision</th>
		<th align="center" style="font-size: 12px;">Banco</th>
		<th align="center" style="font-size: 12px;">Importe</th>
	</tr>
	</thead>
	<tbody>

<?php

$resSQL="SELECT l.ID, l.XFolio, l.Fecha, (SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = l.OperadorLiqui_RID) AS Operador, 
	(SELECT Unidad FROM ".$prefijobd."Unidades WHERE ID = l.UnidadLiqui_RID) AS Unidad, 
	(SELECT Banco FROM ".$prefijobd."Bancos WHERE ID = l.CuentaBancaria_RID) AS Banco, 
	l.yComisionOperador FROM ".$prefijobd."Liquidaciones AS l WHERE l.Depositado='0' AND 
	Date(l.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND l.OficinaLiquidacion_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal.") ORDER BY l.Fecha;";
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$liqID = $rowSQL['ID'];
		$xfolio = $rowSQL['XFolio'];
		$creado = $rowSQL['Fecha'];
		$operador = $rowSQL['Operador'];
		$unidad = $rowSQL['Unidad'];
		$banco = $rowSQL['Banco'];
		$comisionOperador = $rowSQL['yComisionOperador'];

		$creado = date("d-m-Y", strtotime($creado));

		$queryLiquidacionesSub = "SELECT (SELECT XFolio FROM ".$prefijobd."Remisiones WHERE ID = lSub.RemisionLiq_RID) AS FolioRemisiones 
		FROM ".$prefijobd."LiquidacionesSub AS lSub WHERE FolioSub_RID ='".$liqID."';"; 
		$runsqlLiquidacionesSub = mysqli_query($cnx_cfdi2, $queryLiquidacionesSub);
		if (!$runsqlLiquidacionesSub) {//debug
			$mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
			$mensaje .= 'Consulta completa: ' . $queryLiquidacionesSub;
			die($mensaje);
		}
		while ($rowsqlLiquidacionesSub = mysqli_fetch_assoc($runsqlLiquidacionesSub)){
			$remision = $rowsqlLiquidacionesSub['FolioRemisiones'];
			?>
				<tr>
					<td align="center"><?php echo $xfolio ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $operador ?> </td>
					<td align="left"><?php echo $unidad ?> </td>
					<td align="left"><?php echo $remision ?> </td>
					<td align="left"><?php echo $banco ?> </td>
					<td align="left"><?php echo ("$".number_format($comisionOperador,2)) ?> </td>
				</tr>
			<?php
		}

				}
				?>
	</tbody>
</table>
