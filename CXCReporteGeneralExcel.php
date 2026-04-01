<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

header("Content-type: application/vnd.ms-excel");
$nombre="CXC_Reporte_General".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

//mysqli_query($conexion,"SET NAMES 'utf8'");


$moneda = $_GET["moneda"];
$prefijobd = $_GET['prefijodb'];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));


////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="4" style="font-size: 18px;">CxC Reporte General. Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
					<tr>
					<th align="center" style="font-size: 12px;">RFC</th>
					<th align="center" style="font-size: 12px;">RAZON SOCIAL</th>
					<th align="center" style="font-size: 12px;">MONEDA</th>
					<th align="center" style="font-size: 12px;">TOTAL</th>
				  </tr>
                  </thead>
                  <tbody>



<?php
	//Busca Clientes
	$resSQLClientes = "SELECT ID, RazonSocial, RFC FROM ".$prefijobd."Clientes ORDER BY RazonSocial;";
	$runSQLClientes = mysqli_query($cnx_cfdi2, $resSQLClientes);
	while($rowSQLClientes = mysqli_fetch_array($runSQLClientes)){
		$clienteID = $rowSQLClientes['ID'];
		$razonSocial = $rowSQLClientes['RazonSocial'];
		$rfc = $rowSQLClientes['RFC'];

		$saldoMXN=0;

		$resSQLSumFact = "SELECT SUM(CobranzaSaldo) AS Saldo FROM ".$prefijobd."Factura WHERE
		 Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
		 AND CargoAFactura_RID = '".$clienteID."' AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='PESOS';";
		$runSQLSumFact = mysqli_query($cnx_cfdi2, $resSQLSumFact);
		while($rowSQLSumFact = mysqli_fetch_array($runSQLSumFact)){
			$saldoMXN = $rowSQLSumFact['Saldo'];

			if($saldoMXN>0 && $moneda!='2'){
				?>
				
				<tr>
					<td align="left"><?php echo $rfc;?></td>
					<td align="left"><?php echo $razonSocial;?></td>
					<td align="left">MXN</td>
					<td align="left"><?php echo ("$".number_format($saldoMXN,2));?></td>
				  </tr>

<?php
			}
			}
			$saldoUSD=0;

			$resSQLSumFactUSD = "SELECT SUM(CobranzaSaldo) AS Saldo FROM ".$prefijobd."Factura WHERE
				Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
				AND CargoAFactura_RID = '".$clienteID."' AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='DOLARES';";
			$runSQLSumFactUSD = mysqli_query($cnx_cfdi2, $resSQLSumFactUSD);
			while($rowSQLSumFactUSD = mysqli_fetch_array($runSQLSumFactUSD)){
				$saldoUSD = $rowSQLSumFactUSD['Saldo'];
	
				if($saldoUSD>0 && $moneda!='1'){
					?>
					
					<tr>
						<td align="left"><?php echo $rfc;?></td>
						<td align="left"><?php echo $razonSocial;?></td>
						<td align="left">USD</td>
						<td align="left"><?php echo ("$".number_format($saldoUSD,2));?></td>
					</tr>
				
				<?php
							}
						}
				
				
    } // FIN del WHILE

	if($moneda=='0'){
		$resSQLSumFactUSD = "SELECT SUM(CobranzaSaldo) AS TotalUSD FROM ".$prefijobd."Factura WHERE
		Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
		AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='DOLARES';";
		$runSQLSumFactUSD = mysqli_query($cnx_cfdi2, $resSQLSumFactUSD);
		while($rowSQLSumFactUSD = mysqli_fetch_array($runSQLSumFactUSD)){
			$totalUSD = $rowSQLSumFactUSD['TotalUSD'];
		}

		$resSQLSumFact = "SELECT SUM(CobranzaSaldo) AS TotalMXN FROM ".$prefijobd."Factura WHERE
		Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
		AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='PESOS';";
		$runSQLSumFact = mysqli_query($cnx_cfdi2, $resSQLSumFact);
		while($rowSQLSumFact = mysqli_fetch_array($runSQLSumFact)){
			$totalMXN = $rowSQLSumFact['TotalMXN'];
		}
		?>
		<tr>
			<td align="left" colspan="2"><?php echo ("Total MXN: $".number_format($totalMXN,2));?></td>
			<td align="left" colspan="2"><?php echo ("Total USD: $".number_format($totalUSD,2));?></td>
		</tr>

		<?php

	}

	
	if($moneda=='1'){
		$resSQLSumFact = "SELECT SUM(CobranzaSaldo) AS TotalMXN FROM ".$prefijobd."Factura WHERE
		Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
		AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='PESOS';";
		$runSQLSumFact = mysqli_query($cnx_cfdi2, $resSQLSumFact);
		while($rowSQLSumFact = mysqli_fetch_array($runSQLSumFact)){
			$totalMXN = $rowSQLSumFact['TotalMXN'];
		}
		?>
		<tr>
			<td align="left" colspan="4"><?php echo ("Total MXN: $".number_format($totalMXN,2));?></td>
		</tr>

		<?php

	}
	if($moneda=='2'){
		$resSQLSumFactUSD = "SELECT SUM(CobranzaSaldo) AS TotalUSD FROM ".$prefijobd."Factura WHERE
		Date(Vence)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
		AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='DOLARES';";
		$runSQLSumFactUSD = mysqli_query($cnx_cfdi2, $resSQLSumFactUSD);
		while($rowSQLSumFactUSD = mysqli_fetch_array($runSQLSumFactUSD)){
			$totalUSD = $rowSQLSumFactUSD['TotalUSD'];
		}
		?>
		<tr>
			<td align="left" colspan="4"><?php echo ("Total USD: $".number_format($totalUSD,2));?></td>
		</tr>

		<?php

	}

?>					

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
