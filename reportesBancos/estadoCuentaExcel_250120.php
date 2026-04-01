<?php
$prefijobd = $_GET['prefijodb'];

$banco = $_GET["banco"];
$doc = $_GET["doc"];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Estado_Cuenta_".$fecha_inicio_f."-"."$fecha_fin_f"."__".date("d-m-Y")."_".date("h:i").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('../cnx_cfdi.php');require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

if($banco!='0'){//verifica que exista banco definido
	$gastoBancoQuery="AND g.TransferenciaBanco_RID = '".$banco."'";
	$pagoBancoQuery="AND p.Banco_RID = '".$banco."'";
	$abonoBancoQuery="AND a.CuentaBancaria_RID = '".$banco."'";
	$liquiBancoQuery="AND l.CuentaBancaria_RID = '".$banco."'";
}else{
	$gastoBancoQuery="";
	$pagoBancoQuery="";
	$abonoBancoQuery="";
	$liquiBancoQuery="";
}

if($doc!='0'){//verifica que exista tipoDoc definido
	$docQuery="WHERE TipoDoc = '".$doc."'";
}else{$docQuery="";}

?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

<table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
	<thead>
	<tr>
		<th align="center" colspan="9" style="font-size: 18px;">Estado de Cuenta. Periodo: <?php echo $fecha_inicio_f." a ".$fecha_fin_f; ?></th>
	</tr>
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
	Date(g.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' ".$gastoBancoQuery."
	UNION
	SELECT p.XFolio, p.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Proveedores WHERE ID = p.Proveedor_RID) AS Detalle, 
	'' AS Referencia, p.Total AS Cargo, 0 AS Abono, 'Pago' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = p.Banco_RID) AS Cuenta
	 FROM ".$prefijobd."Pagos AS p WHERE p.Depositado='1' AND 
	Date(p.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' ".$pagoBancoQuery." 
	UNION
	SELECT a.XFolio, a.Fecha, (SELECT RazonSocial FROM ".$prefijobd."Clientes WHERE ID = a.Cliente_RID) AS Detalle, 
	'' AS Referencia, 0 AS Cargo, a.TotalImporte AS Abono, 'Abono' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = a.CuentaBancaria_RID) AS Cuenta
	 FROM ".$prefijobd."Abonos AS a WHERE a.Depositado='1' AND 
	Date(a.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' ".$abonoBancoQuery." 
	UNION
	SELECT l.XFolio, l.Fecha, (SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = l.OperadorLiqui_RID) AS Detalle, 
	'' AS Referencia, l.yComisionOperador AS Cargo, 0 AS Abono, 'Liquidacion' AS TipoDoc, (SELECT Cuenta FROM ".$prefijobd."Bancos WHERE ID = l.CuentaBancaria_RID) AS Cuenta
	 FROM ".$prefijobd."Liquidaciones AS l WHERE l.Depositado='1' AND 
	Date(l.Fecha)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' ".$liquiBancoQuery.") AS Resultados

	".$docQuery."
	
	ORDER BY Fecha;";
	$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
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
