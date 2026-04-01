<?php
/*******
 * poliza_depositos.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
 * que se encarga de la salida a txt
 * 
 */

//Conexion a BD
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

//CONFIGURACION

$RutaServer = "ftp://108.163.180.18:21000/";
$titulo = "P&oacute;liza Dep&oacute;sitos";

//Cargo el archivo de acuerdo al sistema contable al que se quiere exportar
include("polizas_contpaq4.php");

//Obtengo la ruta en que se guardara el archivo
$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=104";
$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
$rowparametro = mysql_fetch_row($resultqryparametro);
$directorio = $rowparametro[0];

$directoriodesc = str_replace("c:\\", "", $directorio);
$directoriodesc = str_replace("C:\\", "", $directorio);

$archivo = $directorio . "\poliza_depositos.txt";
$descarga = $RutaServer . "/" . $directoriodesc . "/" . "poliza_depositos.txt";
//echo $descarga;

$debug = 1;

//Select con los tipos de poliza
$select_tipo = "<select name='tipo' id='tipo'>";
$select_tipo .= "<option value='1'>Ingresos</option>";
$select_tipo .= "<option value='2'>Egresos</option>";
$select_tipo .= "<option value='3'>Diario</option>";

//Select con las opciones
$select_opciones = "<select name='opciones' id='opciones'>";
$select_opciones .= "<option value='1'>P&oacute;liza por factura</option>";
$select_opciones .= "<option value='2'>P&oacute;liza por d&iacute;a</option>";

//Arreglo multidimensional con los distintos datos que obtendre de la liquidacion
//parametro, campo a utilizar, compuesto, campo con que se compone, concepto, Tipo de Operacion (1 Abonos, 2 Cargos)
$datosdeposito = array(
				array("2750", "Importe", 1, "oCtaCtb", "Deposito", 2),
				array("2755", "Importe", 1, "bCtaCtb", "Deposito", 1)				
			);

//Abro el archivo
$archivotxt = AbreArchivo($archivo);

$fechaant = "";
//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){

	//Reviso si viene la poliza y si no la igualo a 1
	if (isset($_POST["polizaini"])){
		$numpoliza = $_POST["polizaini"];
	}
	else {
		$numpoliza = 1;
	}
	//Armo el query para las facturas
	//select * from atprimavera_gastosviajes, atprimavera_operadores, atprimavera_bancos where fecha>='2015-03-31' AND fecha <='2015-04-02' AND TipoVale='Deposito' AND atprimavera_operadores.ID=OperadorNombre_RID AND atprimavera_bancos.ID=TransferenciaBanco_RID
	$qrydeposito = "SELECT a.*,b.CuentaContable as oCtaCtb,c.CuentaContable as bCtaCtb,IF(a.FolioEmpresa IS NULL,'N/A',a.FolioEmpresa) as Ticket,(SELECT d.Unidad FROM ".$prefijobd."unidades as d WHERE d.ID=a.Unidad_RID) as NomUnidad FROM " . $prefijobd . "gastosviajes as a LEFT JOIN " . $prefijobd . "operadores as b ON b.ID=a.OperadorNombre_RID LEFT JOIN " . $prefijobd . "bancos as c ON c.ID=a.TransferenciaBanco_RID WHERE (DATE(a.Fecha)>='" . $_REQUEST["fechaini"] . "' AND DATE(a.Fecha) <='" . $_REQUEST["fechafin"] . "') AND a.TipoVale='Deposito' AND a.Estatus<>'Cancelado' ORDER BY a.Fecha";
	
	if ($debug == 1) {
		echo $qrydeposito . "<br>";
	}

	$resultqrydeposito = mysql_query($qrydeposito, $cnx_cfdi);

	if (!$resultqrydeposito) {
	    die('No existen depositos para ese rango de fechas: ' . mysql_error());
	}
	
	while ($rowdeposito = mysql_fetch_array($resultqrydeposito))
	{
		//print_r($rowdeposito);
		echo "<br>";

		if ($_POST["opciones"] == "2")
		{
			//Si es poliza por fecha
			//Reviso que no haya cambiado de fecha
			if ($rowdeposito["Fecha"] == $fechaant)
			{
				//No cambio de fecha, no hago nada
			} else 
			{
				//Mando llamar la funcion para crear la linea de encabezado
				$concepto_poliza = $rowdeposito["Ticket"]." ".$rowdeposito["Concepto"]."  ".$rowdeposito["NomUnidad"];
				GenLineaPolizaEncabezado($archivotxt, $rowdeposito["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);
				
				//Aumento 1 a la poliza
				$numpoliza++;	
			}
		} 
		elseif ($_POST["opciones"] == "1")
		{
			//Si es poliza por abono
			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = $rowdeposito["Ticket"]." ".$rowdeposito["Concepto"]."  ".$rowdeposito["NomUnidad"];
			GenLineaPolizaEncabezado($archivotxt, $rowdeposito["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);	
			
			//Aumento 1 a la poliza
			$numpoliza++;			
		}	

		//Recorro el arreglo para crear los distintos movimientos
		foreach($datosdeposito as $llave=>$valor)
		{

			//parametro, campo a utilizar, compuesto, campo con que se compone, concepto, Tipo de Operacion (1 Abonos, 2 Cargos)
			//Reviso si el campo es mayor que 0, en caso de que no sea asi no hago nada y salto al siguiente valor
			if ($valor[1] > "0")
			{

				//Para cada concepto obtengo los datos necesarios y creo la linea
				$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $valor[0];
				$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
				$rowparametro = mysql_fetch_row($resultqryparametro);
				$parametro = $rowparametro[0];
				
				//Si la cuenta es compuesta concateno
				if ($valor[2] == "1")
				{
					$cuenta = $parametro . $rowdeposito[$valor[3]];
				}
				else {
					$cuenta = $parametro;
				}
				//Hago la linea de las liquidaciones
				//$concepto_movimiento = "";
				
				GenLineaPolizaMovimiento($archivotxt, $cuenta, $valor[4], $valor[5], $rowdeposito[$valor[1]]);
					
			}
				
		}

		//Igualo la fecha anterior a la fecha para el caso de polizas por dia
		$fechaant = $rowdeposito["Fecha"];
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
<title>P&oacute;lizas de Dep&oacute;sitos</title>
</head>
<body>
	<form name="poliza" method="post" action="poliza_depositos.php">
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