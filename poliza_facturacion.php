<?php
/*******
 * poliza_facturacion.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
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
$titulo = "P&oacute;liza Facturaci&oacute;n";

//Cargo el archivo de acuerdo al sistema contable al que se quiere exportar
include("polizas_contpaq4.php");

//Obtengo la ruta en que se guardara el archivo
$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=104";
$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
$rowparametro = mysql_fetch_row($resultqryparametro);
$directorio = $rowparametro[0];

$directoriodesc = str_replace("c:\\", "", $directorio);
$directoriodesc = str_replace("C:\\", "", $directorio);

$archivo = $directorio . "\poliza_facturacion.txt";
$descarga = $RutaServer . "/" . $directoriodesc . "/" . "poliza_facturacion.txt";

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
$datosfactura = array(
				array("2500", "zTotal", 1, "cCtaCtb", "XFolio", 0),
				array("2505", "zRetenido", 0, "", "", 0),
				array("2510", "zImpuesto", 0, "", "", 1),
				array("2515", "zSubtotal", 1, "cCtaUni", "", 1)				
			);

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

	if ($_POST["opciones"] == "2")
	{
		//Si es poliza por fecha
		//Armo el query para las facturas
		//$qryfactura = "SELECT a.*,b.CuentaContable as cCtaCtb,c.CuentaContable as cCtaUni FROM " . $prefijobd . "factura as a LEFT JOIN " . $prefijobd . "clientes as b ON a.CargoAFactura_RID=b.ID LEFT JOIN " . $prefijobd . "unidades as c ON a.Unidad_RID=c.ID WHERE a.Creado>='" . $_REQUEST["fechaini"] . "' AND a.Creado <='" . $_REQUEST["fechafin"] . "' ORDER BY Creado";
		$qryfactura = "SELECT MONTH(a.creado) as mes,YEAR(a.creado) as anio,DAY(LAST_DAY(a.creado)) as dia FROM " . $prefijobd . "factura as a WHERE DATE(a.Creado)>='" . $_REQUEST["fechaini"] . "' AND DATE(a.Creado) <='" . $_REQUEST["fechafin"] . "' AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') GROUP BY 1,2";
	
		if ($debug == 1) {
			echo $qryfactura . "<br>";
		}

		$resultqryfactura = mysql_query($qryfactura, $cnx_cfdi);

		if (!$resultqryfactura) {
	    		die('No existen facturas para ese rango de fechas: ' . mysql_error());
		}
	
		while ($rowfactura = mysql_fetch_array($resultqryfactura))
		{
			//print_r($rowfactura["mes"]."-".$rowfactura["anio"]);
			echo "<br>";

			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "FLETES POR COBRAR " . $rowfactura["anio"]."-".str_pad($rowfactura["mes"],2, "0", STR_PAD_LEFT)."-".$rowfactura["dia"];
			GenLineaPolizaEncabezado($archivotxt,$rowfactura["anio"]."-".str_pad($rowfactura["mes"],2, "0", STR_PAD_LEFT)."-".$rowfactura["dia"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);	
			
			//Aumento 1 a la poliza
			$numpoliza++;	

			$qryfactura2 = "SELECT a.*,b.CuentaContable as cCtaCtb,c.CuentaContable as cCtaUni FROM " . $prefijobd . "factura as a LEFT JOIN " . $prefijobd . "clientes as b ON a.CargoAFactura_RID=b.ID LEFT JOIN " . $prefijobd . "unidades as c ON a.Unidad_RID=c.ID WHERE MONTH(a.Creado)=" . $rowfactura["mes"] . " AND YEAR(a.Creado)=" . $rowfactura["anio"] . " AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') ORDER BY a.Creado";
			$resultqryfactura2 = mysql_query($qryfactura2, $cnx_cfdi);
			while ($rowfactura2 = mysql_fetch_array($resultqryfactura2))
			{

				//Recorro el arreglo para crear los distintos movimientos
				foreach($datosfactura as $llave=>$valor)
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
							$cuenta = $parametro . $rowfactura2[$valor[3]];
						}
						else {
							$cuenta = $parametro;
						}
						//Hago la linea de las liquidaciones
						//$concepto_movimiento = "";
				
						if ($valor[4] == "XFolio")
						{
							$valor[4] = $rowfactura2["XFolio"];
						}
//echo "$cuenta:".$cuenta."$valor[4]:".$valor[4]."$valor[5]:".$valor[5]."rowfactura2:".$rowfactura2[$valor[1]];
						// A la cuenta se le concatena 001
						if ($valor[0] == "2515")
						{
							$cuenta = $cuenta."001";
						}
//echo $cuenta."<BR>";
						GenLineaPolizaMovimiento($archivotxt,$cuenta,$valor[4],$valor[5], $rowfactura2[$valor[1]]);
					
					}
				
				}
			}

			//Igualo la fecha anterior a la fecha para el caso de polizas por dia
			//$fechaant = $rowfactura["anio"]."-".$rowfactura["mes"]."-".$rowfactura["dia"];
		}
	}
	elseif ($_POST["opciones"] == "1")
	{
		//Si es poliza por factura
		//Armo el query para las facturas
		$qryfactura = "SELECT a.*,b.CuentaContable as cCtaCtb,c.CuentaContable as cCtaUni FROM " . $prefijobd . "factura as a LEFT JOIN " . $prefijobd . "clientes as b ON a.CargoAFactura_RID=b.ID LEFT JOIN " . $prefijobd . "unidades as c ON a.Unidad_RID=c.ID WHERE a.Creado>='" . $_REQUEST["fechaini"] . "' AND a.Creado <='" . $_REQUEST["fechafin"] . "' AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') ORDER BY Creado";
	
		if ($debug == 1) {
			echo $qryfactura . "<br>";
		}

		$resultqryfactura = mysql_query($qryfactura, $cnx_cfdi);

		if (!$resultqryfactura) {
	    		die('No existen facturas para ese rango de fechas: ' . mysql_error());
		}
	
		while ($rowfactura = mysql_fetch_array($resultqryfactura))
		{
			//print_r($rowfactura);
			echo "<br>";

			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "FLETES POR COBRAR " . $rowfactura["Creado"] ;
			GenLineaPolizaEncabezado($archivotxt, $rowfactura["Creado"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);	
			
			//Aumento 1 a la poliza
			$numpoliza++;	

			//Recorro el arreglo para crear los distintos movimientos
			foreach($datosfactura as $llave=>$valor)
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
						$cuenta = $parametro . $rowfactura[$valor[3]];
					}
					else {
						$cuenta = $parametro;
					}
					//Hago la linea de las liquidaciones
					//$concepto_movimiento = "";
				
					if ($valor[4] == "XFolio")
					{
						$valor[4] = $rowfactura["XFolio"];
					}
//echo "cuenta:".$cuenta."valor[4]:".$valor[4]."valor[5]:".$valor[5]."rowfactura2:".$rowfactura[$valor[1]] . "<br>";
					// A la cuenta se le concatena 001
					if ($valor[0] == "2515")
					{
						$cuenta = $cuenta."001";
					}
						
					GenLineaPolizaMovimiento($archivotxt,$cuenta,$valor[4],$valor[5], $rowfactura[$valor[1]]);
					
				}
				
			}

			//Igualo la fecha anterior a la fecha para el caso de polizas por dia
			$fechaant = $rowfactura["Creado"];
		}
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
<title>P&oacute;lizas de Facturas</title>
</head>
<body>
	<form name="poliza" method="post" action="poliza_facturacion.php">
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