<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$sucursal = $_POST["sucursal"];//trae sucursal

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
<title>Abonos Depositados</title>

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

    <div id = "container1" style = "height:70%; margin: 0 auto; text-align:center;" >
        <div id="contenedor2" style="overflow:hidden;">
                <!--<div id="1" style="float: left; width: 33%; text-align:left;">
                    <img src="img/logo_ts.png" height="120">
                </div>-->
                
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Abonos Depositados</h1>
                </div>
				

        </div>

        <hr>
        
        <div class="row">
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
	a.TotalImporte, a.TotalSubtotal, a.TotalIVA, a.TotalRetencion FROM ".$prefijobd."Abonos AS a WHERE a.Depositado='1' AND 
	Date(a.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND a.Oficina_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ORDER BY a.Fecha;";
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

				}
				?>
			</tbody>
			</table>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-12" style="text-align:left">
			<a href="abonosDepositadosExcelSucursal.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>&sucursal=<?php echo $sucursal; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
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