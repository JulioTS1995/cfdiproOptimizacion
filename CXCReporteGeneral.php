<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$moneda = $_POST["moneda"];
//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));
$prefijobd = $_POST["base"];



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte General CXC</title>

 <!-- Bootstrap links -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable -->

</head>

<body>
 
    <div id = "container1" style = "width: 80%; margin: 0 auto; text-align:center;" >
        <div id="contenedor2" style="overflow:hidden;">
                <!--<div id="1" style="float: left; width: 33%; text-align:left;">
                    <img src="img/logo_ts.png" height="120">
                </div>-->
                
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte General CXC</h1>
                </div>

        </div>

        <hr>
        
        <div class="row">
			<div class="col-lg-12">
			  <!--<div id="2" style="float: left; width: 33%; text-align:center;">
					<h1 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Resumen </h1>
			  </div>-->
			  <label>Periodo Consultado: <?php echo $fecha_inicio_f." - ".$fecha_fin_f; ?> </label>
			  <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
				  <tr>
				  	<th align="center" style="font-size: 12px;">RFC</th>
					<th align="center" style="font-size: 12px;">RAZON SOCIAL</th>
					<th align="center" style="font-size: 12px;">MONEDA</th>
					<th align="center" style="font-size: 12px;">TOTAL</th>
				  </tr>
				</thead>
				<tbody>
<?php

	
$resSQLClientes = "SELECT ID, RazonSocial, RFC FROM ".$prefijobd."Clientes ORDER BY RazonSocial;";
	$runSQLClientes = mysqli_query($cnx_cfdi2, $resSQLClientes);
	while($rowSQLClientes = mysqli_fetch_array($runSQLClientes)){
		$clienteID = $rowSQLClientes['ID'];
		$razonSocial = $rowSQLClientes['RazonSocial'];
		$rfc = $rowSQLClientes['RFC'];

		$saldoMXN=0;

		$resSQLSumFact = "SELECT SUM(CobranzaSaldo) AS Saldo FROM ".$prefijobd."Factura WHERE
		 Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
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
				Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
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
		Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
		AND CobranzaSaldo>0 AND cCanceladoT IS NULL AND Moneda='DOLARES';";
		$runSQLSumFactUSD = mysqli_query($cnx_cfdi2, $resSQLSumFactUSD);
		while($rowSQLSumFactUSD = mysqli_fetch_array($runSQLSumFactUSD)){
			$totalUSD = $rowSQLSumFactUSD['TotalUSD'];
		}

		$resSQLSumFact = "SELECT SUM(CobranzaSaldo) AS TotalMXN FROM ".$prefijobd."Factura WHERE
		Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
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
		Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
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
		Date(Vence)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
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
			</div>
        </div>
        <br>
		<div class="row">
			<div class="col-md-12" style="text-align:left">
				<a href="CXCReporteGeneralExcel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>&moneda=<?php echo $moneda; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
			</div>
		</div>
		<br>
		<br>

    </div>
	
	<script>
	  $(document).ready(function() {
		$('#table').DataTable();
	  } );
	</script>
	
  </body>
</html>
<?php
//mysqli_free_result($runSQL);
//mysqli_close($cnx_cfdi2);
?>