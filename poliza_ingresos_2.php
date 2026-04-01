<?php
/*******
 * poliza_ingresos.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
 * que se encarga de la salida a txt
 * 
 */
//Conexion a BD
$prefijobd = $_REQUEST["prefijo"];

$debug = 0;
//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

//Realizo la conexion a la base de datos
include("cnx_cfdi.php");

//Selecciono la base de datos
mysql_select_db($database_cfdi, $cnx_cfdi);

//CONFIGURACION

$RutaServer = "ftp://108.163.180.18:21000/";
$titulo = "P&oacute;liza Ingresos";

//Cargo el archivo de acuerdo al sistema contable al que se quiere exportar
include("polizas_contpaq4.php");

$parametrobanco = "2000";
$parametrocliente = "2005";

//Obtengo la ruta en que se guardara el archivo
$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=104";
$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
$rowparametro = mysql_fetch_row($resultqryparametro);
$directorio = $rowparametro[0];

$directoriodesc = str_replace("c:\\", "", $directorio);
$directoriodesc = str_replace("C:\\", "", $directorio);

$archivo = $directorio . "\poliza_ingresos.txt";
$descarga = $RutaServer . "/" . $directoriodesc . "/" . "poliza_ingresos.txt";


//Select con los tipos de poliza
$select_tipo = "<select name='tipo' id='tipo'>";
$select_tipo .= "<option value='1'>Ingresos</option>";
$select_tipo .= "<option value='2'>Egresos</option>";
$select_tipo .= "<option value='3'>Diario</option>";

//Select con las opciones
$select_opciones = "<select name='opciones' id='opciones'>";
$select_opciones .= "<option value='1'>P&oacute;liza por abono</option>";
$select_opciones .= "<option value='2'>P&oacute;liza por d&iacute;a</option>";

//Abro el archivo
$archivotxt = AbreArchivo($archivo);

$fechaant = "";

//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){

	//Reviso si viene la poliza y si no la igualo a 1
	if (!isset($_POST["polizaini"]) or $_POST["polizaini"]==""){
		$numpoliza = 1;
	}
	else {
		$numpoliza = $_POST["polizaini"];
	}
	
	//Armo el query para los abonos
	//select * from atprimavera_abonos, atprimavera_bancos where atprimavera_bancos.id=CuentaBancaria_RID
	$qryabonos = "SELECT " . $prefijobd . "abonos.*, " . $prefijobd . "bancos.CuentaContable as bCtaCtb FROM " . $prefijobd . "abonos, " . $prefijobd . "bancos WHERE fecha>='" . $_REQUEST["fechaini"] . "' AND fecha <='" . $_REQUEST["fechafin"];
	$qryabonos .= "' AND " . $prefijobd . "bancos.ID=CuentaBancaria_RID ORDER BY Fecha";
	
	if ($debug == 1) {
		echo $qryabonos . "<br>";
	}

	$resultqryabonos = mysql_query($qryabonos, $cnx_cfdi);

	if (!$resultqryabonos) {
	    die('No existen abonos para ese rango de fechas: ' . mysql_error());
	}
	
	while ($rowabonos = mysql_fetch_array($resultqryabonos))
	{
		
		if ($_POST["opciones"] == "2")
		{
			//Si es poliza por fecha
			//Reviso que no haya cambiado de fecha
			if ($rowabonos["Fecha"] == $fechaant)
			{
				//No cambio de fecha, no hago nada
			} else 
			{
				//Mando llamar la funcion para crear la linea de encabezado
				$concepto_poliza = "INGRESOS  " ;
				GenLineaPolizaEncabezado($archivotxt, $rowabonos["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);

				//Aumento 1 a la poliza
				$numpoliza++;
			}
		} 
		elseif ($_POST["opciones"] == "1")
		{
			//Si es poliza por abono
			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "INGRESOS " ;
			GenLineaPolizaEncabezado($archivotxt, $rowabonos["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);
			
			//Aumento 1 a la poliza
			$numpoliza++;	
			
		}
		//Hago la linea del abono
		$concepto_movimiento = "";
		
		//Para cada concepto obtengo los datos necesarios y creo la linea
		$qryparametrob = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrobanco;
		$resultqryparametrob = mysql_query($qryparametrob, $cnx_cfdi);
		$rowparametrob = mysql_fetch_row($resultqryparametrob);
		$parametrob = $rowparametrob[0];
		
		if ($rowabonos["bCtaCtb"] == "660000")
		{
			GenLineaPolizaMovimiento($archivotxt, $rowabonos["bCtaCtb"], $concepto_movimiento, "0", $rowabonos["TotalImporte"]);
			
		}
		else 
		{
			GenLineaPolizaMovimiento($archivotxt, $parametrob . $rowabonos["bCtaCtb"], $concepto_movimiento, "0", $rowabonos["TotalImporte"]);
		}
		
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
			
			//Para cada concepto obtengo los datos necesarios y creo la linea
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrocliente;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$parametro = $rowparametro[0];
			
			//Recorro los abonos sub y armo las lineas
			$concepto_movimientosub = "";
			GenLineaPolizaMovimiento($archivotxt, $parametro . $rowabonossub["ctaCliente"], $concepto_movimientosub, "1", $rowabonossub["Importe"]);
			
			if ($debug == 1) {
				print_r($rowabonossub);
			}			
		}
		
		//Igualo la fecha anterior a la fecha para el caso de polizas por dia
		$fechaant = $rowabonos["Fecha"];
		
	}		
	
	//Cierro el archivo
	CierraArchivo($archivotxt);
	
	//Muestro la liga para la descarga del archivo
	echo "<br><a href='" . $descarga . "'>" . $titulo . "</a>";
	
}
else {
	
?>
<html>
<head>
<title>P&oacute;lizas de Ingresos</title>
</head>
<body>
	<form name="poliza" method="post" action="poliza_ingresos.php">
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