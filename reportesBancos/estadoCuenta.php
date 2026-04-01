<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];
$banco = $_POST["banco"];
$doc = $_POST["doc"];
//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

if($banco!='0'){//verifica que exista banco definido
	$gastoBancoQuery="AND g.TransferenciaBanco_RID = '".$banco."'";
	$pagoBancoQuery="AND p.Banco_RID = '".$banco."'";
	$abonoBancoQuery="AND a.CuentaBancaria_RID = '".$banco."'";
	$liquiBancoQuery="AND l.CuentaBancaria_RID = '".$banco."'";
	$depositosBancoQuery="AND d.Banco_RID = '".$banco."'";
	$retirosBancoQuery="AND r.Banco_RID = '".$banco."'";
	$prestamosBancoQuery="AND p.Banco_RID = '".$banco."'";
}else{
	$gastoBancoQuery="";
	$pagoBancoQuery="";
	$abonoBancoQuery="";
	$liquiBancoQuery="";
	$depositosBancoQuery="";
	$retirosBancoQuery="";
}

if($doc!='0'){//verifica que exista tipoDoc definido
	$docQuery="WHERE TipoDoc = '".$doc."'";
}else{$docQuery="";}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Estado de Cuenta</title>

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
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Estado de Cuenta</h1>
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
					<th align="center" style="font-size: 12px;">No. Doc</th>
					<th align="center" style="font-size: 12px;">Tipo Doc</th>
					<th align="center" style="font-size: 12px;">Fecha</th>
					<th align="center" style="font-size: 12px;">Detalle</th>
					<th align="center" style="font-size: 12px;">Referencia</th>
					<th align="center" style="font-size: 12px;">Cuenta Bancaria</th>
					<th align="center" style="font-size: 12px;">Cargo</th>
					<th align="center" style="font-size: 12px;">Abono</th>
					<th align="center" style="font-size: 12px;">Saldo</th>
				  </tr>
				</thead>
				<tbody>
<?php
	$cont = 0;
	$saldo = 0;
	$totalCargo = 0;
	$totalAbono = 0;
	$totalSaldo = 0;

	$resSQL="SELECT 
    XFolio, 
    Fecha, 
    Detalle, 
    Referencia, 
    Cargo, 
    Abono, 
    TipoDoc, 
    Cuenta
	FROM (SELECT g.XFolio, g.Fecha, (SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = g.OperadorNombre_RID) AS Detalle, 
	'' AS Referencia, g.Importe AS Cargo, 0 AS Abono, 'Deposito Operador' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = g.TransferenciaBanco_RID) AS Cuenta
	 FROM ".$prefijobd."GastosViajes AS g WHERE g.Depositado='1' AND 
	Date(g.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$gastoBancoQuery."
	UNION
	SELECT p.XFolio, p.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Proveedores WHERE ID = p.Proveedor_RID) AS Detalle, 
	'' AS Referencia, p.Total AS Cargo, 0 AS Abono, 'Pago' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = p.Banco_RID) AS Cuenta
	 FROM ".$prefijobd."Pagos AS p WHERE p.Depositado='1' AND 
	Date(p.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$pagoBancoQuery." 
	UNION
	SELECT a.XFolio, a.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Clientes WHERE ID = a.Cliente_RID) AS Detalle, 
	'' AS Referencia, 0 AS Cargo, a.TotalImporte AS Abono, 'Abono' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = a.CuentaBancaria_RID) AS Cuenta
	 FROM ".$prefijobd."Abonos AS a WHERE a.Depositado='1' AND 
	Date(a.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$abonoBancoQuery." 
	UNION
	SELECT l.XFolio, l.Fecha, (SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = l.OperadorLiqui_RID) AS Detalle, 
	'' AS Referencia, l.yComisionOperador AS Cargo, 0 AS Abono, 'Liquidacion' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = l.CuentaBancaria_RID) AS Cuenta
	 FROM ".$prefijobd."Liquidaciones AS l WHERE l.Depositado='1' AND 
	Date(l.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$liquiBancoQuery."
	UNION
	SELECT d.Folio, d.FechaMovimiento, d.Concepto AS Detalle, 
	'' AS Referencia, 0 AS Cargo, d.Monto AS Abono, 'Deposito' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = d.Banco_RID) AS Cuenta
	 FROM ".$prefijobd."Depositos AS d WHERE d.Depositado='1' AND 
	Date(d.FechaMovimiento)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$depositosBancoQuery."
	UNION
	SELECT r.Folio, r.FechaMovimiento, r.Concepto AS Detalle, 
	'' AS Referencia, r.Monto AS Cargo, 0 AS Abono, 'Retiro' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = r.Banco_RID) AS Cuenta
	 FROM ".$prefijobd."Retiros AS r WHERE r.Depositado='1' AND 
	Date(r.FechaMovimiento)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' ".$retirosBancoQuery.") AS Resultados

	".$docQuery."
	
	ORDER BY Fecha;";

	echo $resSQL;
	echo "<br>";
	echo "Reporte: estadoCuenta.php";
	
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
	if (!$runSQL) {//debug
		$mensaje  = 'Consulta no valida: ' . mysqli_error() . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL;
		die('conexion terminada');
		//echo $mensaje;
	}
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$xfolio = $rowSQL['XFolio'];
		$creado = $rowSQL['Fecha'];
		$detalle = $rowSQL['Detalle'];
		$referencia = $rowSQL['Referencia'];
		$cargo = $rowSQL['Cargo'];
		$abono = $rowSQL['Abono'];
		$tipoDoc = $rowSQL['TipoDoc'];
		$cuenta = $rowSQL['Cuenta'];

		$creado = date("d-m-Y", strtotime($creado));
		//$saldo=$saldo+($cargo-$abono);
		$saldo=$saldo+$cargo;
		$saldo=$saldo-$abono;

		$totalCargo += $cargo;
		$totalAbono += $abono;
		$totalSaldo += $saldo;
		$cont++;
		
		
			?>
				<tr>
					<td align="center"><?php echo $xfolio ?> </td>
					<td align="left"><?php echo $tipoDoc ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $detalle ?> </td>
					<td align="left"><?php echo $referencia ?> </td>
					<td align="left"><?php echo $cuenta ?> </td>
					<td align="left"><?php echo ("$".number_format($cargo,2)) ?> </td>
					<td align="left"><?php echo ("$".number_format($abono,2)) ?> </td>
					<td align="left"><?php echo ("$".number_format($saldo,2)) ?> </td>
				</tr>
			<?php
		}
				?>
	<tr>
    <td align="center" colspan="6"><strong><?php echo $cont; ?> Documentos</strong></td>
    <td align="left"><strong>$<?php echo number_format($totalCargo, 2); ?></strong></td>
    <td align="left"><strong>$<?php echo number_format($totalAbono, 2); ?></strong></td>
    <td align="left"><strong>$<?php echo number_format($saldo, 2); ?></strong></td>
	</tr>
			</tbody>
			</table>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-12" style="text-align:left">
			<a href="estadoCuentaExcel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>&doc=<?php echo $doc; ?>&banco=<?php echo $banco; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
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