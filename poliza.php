<?php
/*******
 * poliza.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
 * que se encarga de la salida a txt
 * 
 */

//CONFIGURACION
//Select con los tipos de poliza

//Cargo el archivo de acuerdo al sistema contable al que se quiere exportar
include("polizas_contpaq4.php");

//Revisar si se va a guardar en un parametro o sera fijo
$archivo="C:\xml\poliza_ingresos.txt";

$debug = 1;

$select_tipo = "<select name='tipo' id='tipo'>";
$select_tipo .= "<option value='1'>Ingresos</option>";
$select_tipo .= "<option value='2'>Egresos</option>";
$select_tipo .= "<option value='3'>Diario</option>";

//Select con las opciones
$select_opciones = "<select name='opciones' id='opciones'>";
$select_opciones .= "<option value='1'>P&oacute;liza por abono</option>";
$select_opciones .= "<option value='2'>P&oacute;liza por d&iacute;a</option>";

$prefijobd = $_REQUEST["prefijo"];

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

//Realizo la conexion a la base de datos
include("cnx_cfdi.php");

//Selecciono la base de datos
mysql_select_db($database_cfdi, $cnx_cfdi);

//Abro el archivo
$archivotxt = AbreArchivo($archivo);

//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){

	//Mando llamar la funcion para crear la linea de encabezado
	$concepto_poliza = "Ingresos entre " . $_REQUEST["fechaini"] . " " . $_REQUEST["fechafin"];
	GenLineaPolizaEncabezado($archivotxt, $_REQUEST["fechaini"], $_REQUEST["tipo"], $_REQUEST["polizaini"], $concepto_poliza);	
	
	//Armo el query para los abonos
	//select * from atprimavera_abonos, atprimavera_bancos where atprimavera_bancos.id=CuentaBancaria_RID
	$qryabonos = "SELECT * FROM " . $prefijobd . "abonos, " . $prefijobd . "bancos WHERE fecha>='" . $_REQUEST["fechaini"] . "' AND fecha <='" . $_REQUEST["fechafin"];
	$qryabonos .= "' AND " . $prefijobd . "bancos.ID=CuentaBancaria_RID";
	
	if ($debug == 1) {
		echo $qryabonos . "<br>";
	}

	$resultqryabonos = mysql_query($qryabonos, $cnx_cfdi);

	if (!$resultqryabonos) {
	    die('No existen abonos para ese rango de fechas: ' . mysql_error());
	}
	
	while ($rowabonos = mysql_fetch_array($resultqryabonos))
	{
		
		//Hago la linea del abono
		$concepto_movimiento = "";
		
		GenLineaPolizaMovimiento($archivotxt, $rowabonos["CuentaContable"], $concepto_movimiento, 1, $rowabonos["TotalImporte"]);
		
		//Obtengo los abonossub del abono
		$idabono = $rowabonos["ID"];

		//select * from stprimavera_abonossub, stprimavera_bancos, stprimavera_factura where stprimavera_bancos.ID=CuentaBancara_RID and stprimavera_factura.ID=AbonoFactura_RID
		$qryabonossub = "SELECT * FROM " . $prefijobd . "abonossub, " . $prefijobd . "bancos, " . $prefijobd . "factura WHERE " . $prefijobd . "abonossub.FolioSub_RID=" . $idabono . " AND " . $prefijobd . "bancos.ID=CuentaBancaria_RID AND " . $prefijobd . "factura.ID=AbonoFactura_RID" ;
		
		if ($debug == 1) {
			echo $qryabonossub . "<br>";
		}
		$resultqryabonossub = mysql_query($qryabonossub, $cnx_cfdi);

		if ($debug == 1) {
			print_r($rowabonos);
		}	
		
		while ($rowabonossub = mysql_fetch_array($resultqryabonossub))
		{
			//Recorro los abonos sub y armo las lineas
			$concepto_movimientosub = "";
			GenLineaPolizaMovimiento($archivotxt, $rowabonossub["ctaCliente"], $concepto_movimientosub, 0, $rowabonossub["Importe"]);
			
			if ($debug == 1) {
				print_r($rowabonossub);
			}			
		}
	}		
	
	//Cierro el archivo
	CierraArchivo($archivotxt);
	
	
}
else {
	
?>
<html>
<head>
<title>P&oacute;lizas</title>
</head>
<body>
	<form name="poliza" method="post" action="poliza.php">
	<input type="hidden" name="procesar" value="1">
	<input type="hidden" name="prefijo" value="<?php echo $prefijobd; ?>">
	<table>
		<tr>
			<td>Fecha Inicial:</td>
			<td><input type="date" name="fechaini" id="fechaini" /></td>
		</tr>
		<tr>
			<td>Fecha Final:</td>
			<td><input type="date" name="fechafin" id="fechafin" /></td>
		</tr>
		<tr>
			<td>Tipo de p&oacute;liza:</td>
			<td><?php echo $select_tipo; ?></td>
		</tr>
		<tr>
			<td>Opciones:</td>
			<td><?php echo $select_opciones?></td>
		</tr>
		<tr>
			<td>P&oacute;liza Inicial:</td>
			<td><input type="text" name="polizaini" id="polizaini" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" value="Enviar" /></td>
		</tr>
	</table>
	</form>
</body>
</html>
<?php 
}
?>