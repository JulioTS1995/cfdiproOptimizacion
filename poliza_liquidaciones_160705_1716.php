<?php
/*******
 * poliza_liquidaciones.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
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
$titulo = "P&oacute;liza Liquidaciones";

//Cargo el archivo de acuerdo al sistema contable al que se quiere exportar
include("polizas_contpaq4.php");

//Obtengo la ruta en que se guardara el archivo
$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=104";
$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
$rowparametro = mysql_fetch_row($resultqryparametro);
$directorio = $rowparametro[0];

$directoriodesc = str_replace("c:\\", "", $directorio);
$directoriodesc = str_replace("C:\\", "", $directorio);

$archivo = $directorio . "\poliza_liquidaciones.txt";
$descarga = $RutaServer . "/" . $directoriodesc . "/" . "poliza_liquidaciones.txt";

$debug = 1;

//Select con los tipos de poliza
$select_tipo = "<select name='tipo' id='tipo'>";
$select_tipo .= "<option value='1'>Ingresos</option>";
$select_tipo .= "<option value='2'>Egresos</option>";
$select_tipo .= "<option value='3'>Diario</option>";

//Select con las opciones
$select_opciones = "<select name='opciones' id='opciones'>";
$select_opciones .= "<option value='1'>P&oacute;liza por liquidaci&oacute;n</option>";
$select_opciones .= "<option value='2'>P&oacute;liza por d&iacute;a</option>";

//Arreglo multidimensional con los distintos datos que obtendre de la liquidacion
//parametro, campo a utilizar, compuesto, campo con que se compone, concepto, Tipo de Operacion (1 Abonos, 2 Cargos)
$datosliq = array(
				array("2250", "yDeposito", 1, "CuentaContable", "Acumulado dep a ope", 2),
				array("2255", "yComisionOperador", 0, "", "Sueldo", 1),
				array("2260", "zRefaccionesIVAb", 0, "", "Refacciones y partes", 1),
				array("2265", "zViaticosIVAb", 0, "", "Viaticos", 1),
				array("2270", "zCombustibleIVAb", 0, "", "Combustible", 1),
				array("2275", "zVariosIVAb", 0, "", "Diversos", 1),
				array("2280", "zManiobras2IVAb", 0, "", "CELULARES", 1),
				array("2285", "zPeajeIVAb", 0, "", "Autopistas", 1),
				array("2290", "zReparacionesIVAb", 0, "", "Mant y Conserv", 1),
				//array("2295", "zSegurosIVAb", 0, "", "FUMIGACIONES", 1),
				array("2300", "zTotala", 0, "", "IVA16% Comp y gastos", 1),
				array("2305", "zGastosNoDeduciblesIVAb", 0, "", "MENSAJERIA Y PAQUETERIA", 1),
				array("2310", "zGastosFacilidadesIVAb", 0, "", "Gastos Facilidades", 1),
				array("2315", "yImpuestosImss", 1, "", "Imss", 2),
				array("2758", "yImpuestosFonacot", 1, "", "Fonacot", 2),
				array("2757", "yImpuestosInfonavit", 1, "", "Infonavit", 2),
				array("2320", "yImpuestosIsp", 1, "", "Ispt", 2),
				//array("2300", "roSubtotal", 1, "CuentaContable", "pago al operador, apartado de sueldo", 2),
				array("2756", "roSubtotal", 1, "CuentaContable", "sueldo pagado", 2)
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
	//Armo el query para las liquidaciones
	//select * from atprimavera_liquidaciones, atprimavera_operadores, atprimavera_unidades where atprimavera_operadores.ID=OperadorLiqui_RID AND atprimavera_unidades.ID=UnidadLiqui_RID
	$qryliquidaciones = "SELECT a.*,b.*";
	$qryliquidaciones = $qryliquidaciones.",(SELECT c.Unidad FROM ".$prefijobd."Unidades as c WHERE c.id=a.UnidadLiqui_RID) as NomUni";
	$qryliquidaciones = $qryliquidaciones.",(SELECT d.cuentacontable FROM ".$prefijobd."Unidades as d WHERE d.id=a.UnidadLiqui_RID) as CCUnidad";
	$qryliquidaciones = $qryliquidaciones." FROM " . $prefijobd . "liquidaciones as a," . $prefijobd . "operadores as b WHERE (DATE(a.fecha)>='" . $_REQUEST["fechaini"] . "' AND DATE(a.fecha) <='" . $_REQUEST["fechafin"] . "') AND b.ID=a.OperadorLiqui_RID ORDER BY a.Fecha";
		

	if ($debug == 1) {
		echo $qryliquidaciones . "<br>";
	}

	$resultqryliquidaciones = mysql_query($qryliquidaciones, $cnx_cfdi);

	if (!$resultqryliquidaciones) {
	    die('No existen liquidaciones para ese rango de fechas: ' . mysql_error());
	}
	
	while ($rowliquidaciones = mysql_fetch_array($resultqryliquidaciones))
	{
		//print_r($rowliquidaciones);
		echo "<br>";

		if ($_POST["opciones"] == "2")
		{
			//Si es poliza por fecha
			//Reviso que no haya cambiado de fecha
			if ($rowliquidaciones["Fecha"] == $fechaant)
			{
				//No cambio de fecha, no hago nada
			} else 
			{
				//Mando llamar la funcion para crear la linea de encabezado
				$concepto_poliza = "Liquidaciones  " . $rowliquidaciones["XFolio"]."  ".$rowliquidaciones["NomUni"] ;
				GenLineaPolizaEncabezado($archivotxt,$rowliquidaciones["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);
				
				//Aumento 1 a la poliza
				$numpoliza++;	
			}
		} 
		elseif ($_POST["opciones"] == "1")
		{
			//Si es poliza por abono
			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "Liquidacion  " . $rowliquidaciones["XFolio"]."  ".$rowliquidaciones["NomUni"] ;
			GenLineaPolizaEncabezado($archivotxt,$rowliquidaciones["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);	
			
			//Aumento 1 a la poliza
			$numpoliza++;			
		}	

		//Recorro el arreglo para crear los distintos movimientos
		foreach($datosliq as $llave=>$valor)
		{

			//parametro, campo a utilizar, compuesto, campo con que se compone, concepto, Tipo de Operacion (1 Abonos, 2 Cargos)
			//Reviso si el campo es mayor que 0, en caso de que no sea asi no hago nada y salto al siguiente valor
			if ($valor[1] > "0")
			{

				//Para cada concepto obtengo los datos necesarios y creo la linea
				$qryparametro = "SELECT vchar,vint FROM " . $prefijobd . "parametro WHERE id2=" . $valor[0];
				$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
				$rowparametro = mysql_fetch_row($resultqryparametro);
				$parametro = $rowparametro[0];
				$parametro2 = $rowparametro[1];

				//Si la cuenta es compuesta concateno
				if ($valor[2] == "1" AND ($valor[0]<>2756) AND ($valor[0]<>"2757") AND ($valor[0]<>"2758") AND ($valor[0]<>"2315") AND ($valor[0]<>"2320"))
				{
					$cuenta = $parametro . $rowliquidaciones[$valor[3]];
				}
				else {
					if (($valor[0]=="2250") OR ($valor[0]=="2255") OR ($valor[0]=="2260") OR ($valor[0]=="2265") OR ($valor[0]=="2270") OR ($valor[0]=="2275") OR ($valor[0]=="2280") OR ($valor[0]=="2285") OR ($valor[0]=="2290") OR ($valor[0]=="2305") OR ($valor[0]=="2310"))
					{
						$cuenta = str_replace("_",substr($rowliquidaciones["CCUnidad"],-2),$parametro);
					}
					else
					{
						if(($valor[0]=="2757") OR ($valor[0]=="2758") OR ($valor[0]=="2315") OR ($valor[0]=="2320"))
						{
							if(($valor[0]=="2757") OR ($valor[0]=="2758"))
							{
								$cuenta = $parametro.$rowliquidaciones["CuentaContable"];
							}
							else
							{
								if($valor[0]=="2315")
								{
									$cuenta = $parametro.$rowliquidaciones["CCIMSS"];
								}
								else
								{
									$cuenta = $parametro.$rowliquidaciones["CCISP"];
								}
							}
						}
						else
						{
							$cuenta = $parametro;
						}
					}
				}
				//Hago la linea de las liquidaciones
				//$concepto_movimiento = "";
				
				if(strlen($valor[4])>20)
					$valor[4]=substr($valor[4],1,20);
				if($rowliquidaciones[$valor[1]]<>0)
					GenLineaPolizaMovimiento2($archivotxt, $cuenta, $valor[4], $valor[2], $rowliquidaciones[$valor[1]],$parametro2);
					
			}
				
		}
		
		//Igualo la fecha anterior a la fecha para el caso de polizas por dia
		$fechaant = $rowliquidaciones["Fecha"];

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
<title>P&oacute;lizas de Liquidaciones</title>
</head>
<body>
	<form name="poliza" method="post" action="poliza_liquidaciones.php">
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