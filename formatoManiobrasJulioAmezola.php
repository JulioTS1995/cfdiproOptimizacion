<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$cliente = $_POST["cliente"];
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
<title>Formato maniobras Julio Amezola</title>

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
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Formato maniobras Julio Amezola</h1>
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
				  	<th align="center" style="font-size: 12px;">SOLICITO</th>
					<th align="center" style="font-size: 12px;">FECHA</th>
					<th align="center" style="font-size: 12px;">O. DE EMBARQUE</th>
					<th align="center" style="font-size: 12px;">NO. DE FACTURA</th>
					<th align="center" style="font-size: 12px;">NO. TRANSFERENCIA</th>
					<th align="center" style="font-size: 12px;">CLIENTE</th>
					<th align="center" style="font-size: 12px;">ID</th>
					<th align="center" style="font-size: 12px;">CONCEPTO</th>
					<th align="center" style="font-size: 12px;">CALCULO MANIOBRAS</th>
					<th align="center" style="font-size: 12px;"># CAJAS</th>
					<th align="center" style="font-size: 12px;">TOTAL</th>
				  </tr>
				</thead>
				<tbody>
<?php

		$resSQLGastoSum = "SELECT 
		REM.ID AS idRem,
		GAS.ID AS idGas,
		REM.Creado,
		REM.RemisionOperador,
		REM.Destinatario,
		REM.Factura,
		REM.Transferencia,
		GAS.Concepto AS ConceptoGas,
		GAS.Importe AS ImporteGas,
		REM.CargoASolicito
	FROM
		".$prefijobd."GastosViajesSub AS SUB
			INNER JOIN
		".$prefijobd."GastosViajes_REF AS REF ON REF.RID = SUB.ID
			INNER JOIN
		".$prefijobd."GastosViajes AS GAS ON GAS.ID = REF.ID
			INNER JOIN
		".$prefijobd."Remisiones AS REM ON REM.ID = GAS.Remision_RID
	WHERE
		SUB.Concepto = 'Maniobras'
			AND Date(REM.Creado)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59'
			AND REM.CargoACliente_RID = '".$cliente."'
	GROUP BY GAS.ID;";
		$runSQLGastoSum = mysqli_query($cnx_cfdi2, $resSQLGastoSum);
		while($rowSQLGastoSum = mysqli_fetch_array($runSQLGastoSum)){
			$idRemision = $rowSQLGastoSum['idRem'];
			$idGasto = $rowSQLGastoSum['idGas'];
			$creado = $rowSQLGastoSum['Creado'];
			$ticket = $rowSQLGastoSum['RemisionOperador'];
			$factura = $rowSQLGastoSum['Factura'];
			$destinatario = $rowSQLGastoSum['Destinatario'];
			$transferencia = $rowSQLGastoSum['Transferencia'];
			$concepto = $rowSQLGastoSum['ConceptoGas'];
			$importe = $rowSQLGastoSum['ImporteGas'];
			$solicito = $rowSQLGastoSum['CargoASolicito'];
		

		//Buscar datos de Embalaje
		$resSQLEmbalaje = "SELECT Referencia, Cantidad FROM ".$prefijobd."RemisionesSub WHERE FolioSub_RID=".$idRemision." LIMIT 0,1;";
		$runSQLEmbalaje = mysqli_query($cnx_cfdi2, $resSQLEmbalaje);
		while($rowSQLEmbalaje = mysqli_fetch_array($runSQLEmbalaje)){
			$referencia = $rowSQLEmbalaje['Referencia'];
			$cantidadEmbalaje = $rowSQLEmbalaje['Cantidad'];
		}


		$creado = date("d-m-Y", strtotime($creado));


				?>
				
				  <tr>
				    <td align="left"><?php echo $solicito ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $ticket ?> </td>
					<td align="left"><?php echo $factura ?> </td>
					<td align="left"><?php echo $transferencia ?> </td>
					<td align="left"><?php echo $destinatario ?> </td>
					<td align="left"><?php echo $referencia ?> </td>
					<td align="left">Maniobras</td>
					<td align="left"><?php echo $concepto ?> </td>
					<td align="left"><?php echo $cantidadEmbalaje ?></td>
					<td align="left"><?php echo "$".$importe?> </td>
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
				<a href="formatoManiobrasJulioAmezolaExcel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>&cliente=<?php echo $cliente; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
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