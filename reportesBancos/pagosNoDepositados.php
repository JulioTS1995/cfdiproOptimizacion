<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Pagos No Depositados</title>

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
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Pagos No Depositados</h1>
                </div>
				

        </div>

        <hr>
        
        <div class="row" style="overflow:auto;">
			<div class="col-lg-12">
			  <!--<div id="2" style="float: left; width: 33%; text-align:center;">
					<h1 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Resumen </h1>
			  </div>-->
			  <label>Periodo Consultado: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?> </label>
			  <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
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
	p.Total FROM ".$prefijobd."Pagos AS p WHERE p.Depositado='0' AND 
	Date(p.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ORDER BY p.Fecha;";
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

				}
				?>
			</tbody>
			</table>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-12" style="text-align:left">
			<a href="pagosNoDepositadosExcel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
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