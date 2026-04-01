<?php
/*******
 * poliza_ingresos.php permite elegir 2 fechas y el tipo de poliza que se generara, obtiene los datos y los pasa al programa
 * que se encarga de la salida a txt
 * 
 */
//Conexion a BD
$prefijobd = $_REQUEST["prefijo"];
$FactorSubTotal = 0;

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

//=============================================
// Parametros de cuentas de Flete provicion
$parametro_flete_provCar = "2011";
$parametro_flete_provAbo = "2012";

//=============================================
// Parametros de cuentas de IVA provicion
$parametro_iva_provCar = "2016";
$parametro_iva_provAbo = "2017";

//=============================================
// Parametros de cuentas de IVA Ret provicion
$parametro_ivaret_provCar = "2021";
$parametro_ivaret_provAbo = "2022";


//=============================================
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
	
	if ($_POST["opciones"] == "2")
	{
		//Si es poliza por fecha
		//Armo el query para los abonos
		$qryabonos = "SELECT a.FechaAplicacion FROM ".$prefijobd."abonossub as a WHERE a.FechaAplicacion>='".$_REQUEST["fechaini"]."' AND a.FechaAplicacion<='" . $_REQUEST["fechafin"]."' GROUP BY a.FechaAplicacion ORDER BY a.FechaAplicacion";
	
		if ($debug == 1) {
			echo $qryabonos . "<br>";
		}

		$resultqryabonos = mysql_query($qryabonos, $cnx_cfdi);

		if (!$resultqryabonos) {
	    		die('No existen abonos para ese rango de fechas: ' . mysql_error());
		}
	
		while ($rowabonos = mysql_fetch_array($resultqryabonos))
		{
		
			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "INGRESOS " ;
			GenLineaPolizaEncabezado($archivotxt, $rowabonos["FechaAplicacion"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);
			
			//Aumento 1 a la poliza
			$numpoliza++;

//			$qryabonossub = $qryabonossub.", (SELECT d.xfolio FROM ".$prefijobd."factura as d WHERE d.ID=a.AbonoFactura_RID) as factura";

			//consulta abonossub
			$qryabonossub = "SELECT a.*,b.*, d.xfolio as factura, d.xiva, d.xretencion, d.zimpuesto, d.zretenido, d.zsubtotal, d.ztotal AS FactTotal, dd.cuentacontable AS CCUnidad";
			$qryabonossub = $qryabonossub.", (SELECT c.CuentaContable FROM ".$prefijobd."bancos as c WHERE c.ID=a.CuentaBancaria_RID) as bCtaCtb";
			$qryabonossub = $qryabonossub.", (SELECT e.CuentaContable FROM ".$prefijobd."Clientes as e WHERE e.ID=b.Cliente_RID) as ctaCliente";
			$qryabonossub = $qryabonossub." FROM ".$prefijobd."abonossub as a";
			$qryabonossub = $qryabonossub." LEFT JOIN ".$prefijobd."abonos  as b ON a.FolioSub_RID = b.ID";
			$qryabonossub = $qryabonossub." LEFT JOIN ".$prefijobd."factura as d ON d.ID=a.AbonoFactura_RID";
			$qryabonossub = $qryabonossub." LEFT JOIN ".$prefijobd."Unidades as dd ON dd.ID=d.unidad_RID";
			$qryabonossub = $qryabonossub." WHERE a.FechaAplicacion='".$rowabonos["FechaAplicacion"]."'";
			$qryabonossub = $qryabonossub." ORDER BY b.XFolio";
			
			$resultqryabonossub = mysql_query($qryabonossub, $cnx_cfdi);

			//Obtengo los abonossub del abono
			//$idabono = $rowabonos["ID"];
		
			if ($debug == 1) {
				echo $qryabonossub . "<br>";
			}

			if ($debug == 1) {
				print_r($rowabonos);
			}
			
			include("poliza_ingresos_inc1.php");
			
			while ($rowabonossub = mysql_fetch_array($resultqryabonossub))
			{

				//Hago la linea del abono
				$concepto_movimiento = "";
		
				if ($rowabonossub["bCtaCtb"] == "660000")
				{
					GenLineaPolizaMovimiento($archivotxt, $rowabonossub["bCtaCtb"], $rowabonossub["factura"], "1", $rowabonossub["Importe"]);
				}
				else 
				{
					GenLineaPolizaMovimiento($archivotxt, $parametro . $rowabonossub["ctaCliente"],$rowabonossub["factura"], "1",$rowabonossub["Importe"]);
				}	
			
				if ($debug == 1) {
					print_r($rowabonossub);
				}

				//========================================================
				// Calcula importe base segun impuestos, IVA - RET, Si es negativo se pasa a positivo...
//				$FactorSubTotal = ($rowabonossub["xiva"] / 100) - ($rowabonossub["xretencion"] / 100);
//				if ($FactorSubTotal < 0) {
//						$FactorSubTotal = $FactorSubTotal * -1;
//				}

				if (($rowabonossub["zimpuesto"] > 0) && ($rowabonossub["zretenido"] > 0)) {
					$FactorSubTotal = ($rowabonossub["xiva"] / 100) - ($rowabonossub["xretencion"] / 100);
				}
				elseif ($rowabonossub["zimpuesto"] > 0) {
					$FactorSubTotal = ($rowabonossub["xiva"] / 100);
				}
				elseif ($rowabonossub["zretenido"] > 0) {
					$FactorSubTotal = ($rowabonossub["xretencion"] / 100);
				}
				
				// Detarmina ImporteBase de calculos..
				$SubTotalTmp = $rowabonossub["Importe"] / (1 + $FactorSubTotal); 
				//echo $FactorSubTotal."<Br>";
				//echo $SubTotalTmp."<Br>";
				//echo $rowabonossub["Importe"]."<Br>";
				
				//========================================================
				// Genera lineas de IVA Provision(Cargo y abono)
				$iva_provImporte = ($SubTotalTmp * ($rowabonossub["xiva"] / 100));
				GenLineaPolizaMovimiento($archivotxt, $iva_provAboCta, $rowabonossub["factura"], "1", $iva_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $iva_provCarCta, $rowabonossub["factura"], "2", $iva_provImporte);

				//========================================================
				// Genera lineas de Retencion de IVA Provision(Cargo y abono)
				$ivaret_provImporte = ($SubTotalTmp * ($rowabonossub["xretencion"] / 100));
				GenLineaPolizaMovimiento($archivotxt, $ivaret_provAboCta, $rowabonossub["factura"], "1", $ivaret_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $ivaret_provCarCta, $rowabonossub["factura"], "2", $ivaret_provImporte);

				//========================================================
				// Genera lineas de Flete Provision(Cargo y abono)
				//$flete_provImporte = ($SubTotalTmp - ($iva_provImporte + $ivaret_provImporte));
				$flete_provImporte = $SubTotalTmp;
								// Actualiza cta de unidad...
//echo "flete_provCarCta:".$flete_provCarCta."<Br>";				
//echo "Unidad:".$rowabonos["CCUnidad"]."<Br>";				
				$flete_provCarCtaTmp = str_replace("_",substr($rowabonossub["CCUnidad"],-2),$flete_provCarCta);				
				$flete_provAboCtaTmp = str_replace("_",substr($rowabonossub["CCUnidad"],-2),$flete_provAboCta);				
//echo "CarCta:".$flete_provCarCta."<Br>";				
//echo "AboCta:".$flete_provAboCta."<Br>";				
				GenLineaPolizaMovimiento($archivotxt, $flete_provAboCtaTmp, $rowabonossub["factura"], "1", $flete_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $flete_provCarCtaTmp, $rowabonossub["factura"], "2", $flete_provImporte);

			}

			$qryabonosbancos = "SELECT SUM(a.Importe) as Importe,(SELECT c.CuentaContable FROM ".$prefijobd."bancos as c WHERE c.ID=a.CuentaBancaria_RID) as bCtaCtb FROM ".$prefijobd."abonossub as a WHERE a.FechaAplicacion='".$rowabonos["FechaAplicacion"]."' AND (SELECT d.CuentaContable FROM ".$prefijobd."bancos as d WHERE d.ID=a.CuentaBancaria_RID)<>'660000' GROUP BY a.CuentaBancaria_RID";
			$resultqryabonosbancos = mysql_query($qryabonosbancos, $cnx_cfdi);

			while ($rowabonosbancos = mysql_fetch_array($resultqryabonosbancos))
			{
				GenLineaPolizaMovimiento($archivotxt, $parametrob . $rowabonosbancos["bCtaCtb"], $concepto_movimiento.$rowabonossub["factura"], "0",$rowabonosbancos["Importe"]);
			}
		
			//Igualo la fecha anterior a la fecha para el caso de polizas por dia
			$fechaant = $rowabonos["FechaAplicacion"];
		
		}
	}
	elseif ($_POST["opciones"] == "1")
	{
		//Si es poliza por abono
		//Armo el query para los abonos
		//select * from atprimavera_abonos, atprimavera_bancos where atprimavera_bancos.id=CuentaBancaria_RID
//		$qryabonos = "SELECT a.*,b.*,(SELECT c.CuentaContable FROM ".$prefijobd."bancos as c WHERE c.ID=a.CuentaBancaria_RID) as bCtaCtb,(SELECT d.xfolio FROM ".$prefijobd."factura as d WHERE d.ID=a.AbonoFactura_RID) as factura,(SELECT e.CuentaContable FROM ".$prefijobd."Clientes as e WHERE e.ID=b.Cliente_RID) as ctaCliente FROM ".$prefijobd."abonossub as a LEFT JOIN ".$prefijobd."abonos as b ON a.FolioSub_RID=b.ID WHERE a.FechaAplicacion>='".$_REQUEST["fechaini"]."' AND a.FechaAplicacion<='" . $_REQUEST["fechafin"]."' ORDER BY b.XFolio,a.FechaAplicacion";
//		$qryabonos = $qryabonos.", (SELECT d.xfolio FROM ".$prefijobd."factura as d WHERE d.ID=a.AbonoFactura_RID) as factura";

		//$qryabonos = "SELECT a.*,b.*, d.xfolio as factura, d.xiva, d.xretencion, d.zimpuesto, d.zretenido, d.zsubtotal, d.ztotal";
		$qryabonos = "SELECT a.*,b.*, d.xfolio as factura, d.xiva, d.xretencion, d.zimpuesto, d.zretenido, d.zsubtotal, d.ztotal AS FactTotal, dd.cuentacontable AS CCUnidad";
		$qryabonos = $qryabonos.", (SELECT c.CuentaContable FROM ".$prefijobd."bancos as c WHERE c.ID=a.CuentaBancaria_RID) as bCtaCtb";
		$qryabonos = $qryabonos.", (SELECT e.CuentaContable FROM ".$prefijobd."Clientes as e WHERE e.ID=b.Cliente_RID) as ctaCliente";
		$qryabonos = $qryabonos."  FROM ".$prefijobd."abonossub as a";
		$qryabonos = $qryabonos."  LEFT JOIN ".$prefijobd."abonos as b ON a.FolioSub_RID=b.ID";
		$qryabonos = $qryabonos."  LEFT JOIN ".$prefijobd."factura as d ON d.ID=a.AbonoFactura_RID";
		$qryabonos = $qryabonos." LEFT JOIN ".$prefijobd."Unidades as dd ON dd.ID=d.unidad_RID";
		$qryabonos = $qryabonos."  WHERE a.FechaAplicacion >='".$_REQUEST["fechaini"]."'";
		$qryabonos = $qryabonos."  AND   a.FechaAplicacion <='".$_REQUEST["fechafin"]."'";
		$qryabonos = $qryabonos."  ORDER BY b.XFolio,a.FechaAplicacion";
	
		if ($debug == 1) {
			echo $qryabonos . "<br>";
		}

		include("poliza_ingresos_inc1.php");
		
		$resultqryabonos = mysql_query($qryabonos, $cnx_cfdi);

		if (!$resultqryabonos) {
	    		die('No existen abonos para ese rango de fechas: ' . mysql_error());
		}
	
		while ($rowabonos = mysql_fetch_array($resultqryabonos))
		{
		
			//Mando llamar la funcion para crear la linea de encabezado
			$concepto_poliza = "INGRESOS " ;
			GenLineaPolizaEncabezado($archivotxt, $rowabonos["Fecha"], $_REQUEST["tipo"], $numpoliza, $concepto_poliza);
			
			//Aumento 1 a la poliza
			$numpoliza++;
			
			//Hago la linea del abono
			$concepto_movimiento = "";
		
			//Para cada concepto obtengo los datos necesarios y creo la linea
			$qryparametrob = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrobanco;
			$resultqryparametrob = mysql_query($qryparametrob, $cnx_cfdi);
			$rowparametrob = mysql_fetch_row($resultqryparametrob);
			$parametrob = $rowparametrob[0];
		
			if ($rowabonos["bCtaCtb"] == "660000")
			{
				GenLineaPolizaMovimiento($archivotxt, $rowabonos["bCtaCtb"], $concepto_movimiento.$rowabonos["factura"], "1", $rowabonos["Importe"]);
			}
			else 
			{
				GenLineaPolizaMovimiento($archivotxt, $parametrob . $rowabonos["bCtaCtb"], $concepto_movimiento.$rowabonos["factura"], "2",$rowabonos["Importe"]);
			}

				//========================================================
				// Calcula importe base segun impuestos, IVA - RET, Si es negativo se pasa a positivo...
				if (($rowabonos["zimpuesto"] > 0) && ($rowabonos["zretenido"] > 0)) {
					$FactorSubTotal = ($rowabonos["xiva"] / 100) - ($rowabonos["xretencion"] / 100);
				}
				elseif ($rowabonos["zimpuesto"] > 0) {
					$FactorSubTotal = ($rowabonos["xiva"] / 100);
				}
				elseif ($rowabonos["zretenido"] > 0) {
					$FactorSubTotal = ($rowabonos["xretencion"] / 100);
				}

				if ($FactorSubTotal < 0) {
					$FactorSubTotal = $FactorSubTotal * -1;
				}

				// Detarmina ImporteBase de calculos..
				$SubTotalTmp = $rowabonos["Importe"] / (1 + $FactorSubTotal); 
				//echo $FactorSubTotal."<Br>";
				//echo $SubTotalTmp."<Br>";
				//echo $rowabonos["Importe"]."<Br>";
				
				//========================================================
				// Genera lineas de IVA Provision(Cargo y abono)
				if ($rowabonos["zimpuesto"] > 0) {
					$iva_provImporte = ($SubTotalTmp * ($rowabonos["xiva"] / 100));
					GenLineaPolizaMovimiento($archivotxt, $iva_provCarCta, $rowabonos["factura"], "2", $iva_provImporte);
					GenLineaPolizaMovimiento($archivotxt, $iva_provAboCta, $rowabonos["factura"], "1", $iva_provImporte);
				}

				//========================================================
				// Genera lineas de Retencion de IVA Provision(Cargo y abono)
				if ($rowabonos["zretenido"] > 0) {
					$ivaret_provImporte = ($SubTotalTmp * ($rowabonos["xretencion"] / 100));
					GenLineaPolizaMovimiento($archivotxt, $ivaret_provCarCta, $rowabonos["factura"], "2", $ivaret_provImporte);
					GenLineaPolizaMovimiento($archivotxt, $ivaret_provAboCta, $rowabonos["factura"], "1", $ivaret_provImporte);
				}
				//========================================================
				// Genera lineas de Flete Provision(Cargo y abono)
//				$flete_provImporte = ($SubTotalTmp - ($iva_provImporte + $ivaret_provImporte));
				$flete_provImporte = $SubTotalTmp;
				// Actualiza cta de unidad...
//echo "flete_provCarCta:".$flete_provCarCta."<Br>";				
//echo "Unidad:".$rowabonos["CCUnidad"]."<Br>";				
				$flete_provCarCtaTmp = str_replace("_",substr($rowabonos["CCUnidad"],-2),$flete_provCarCta);				
				$flete_provAboCtaTmp = str_replace("_",substr($rowabonos["CCUnidad"],-2),$flete_provAboCta);				
//echo "CarCta:".$flete_provCarCta."<Br>";				
//echo "AboCta:".$flete_provAboCta."<Br>";				
				GenLineaPolizaMovimiento($archivotxt, $flete_provCarCtaTmp, $rowabonos["factura"], "2", $flete_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $flete_provAboCtaTmp, $rowabonos["factura"], "1", $flete_provImporte);
			
			//Obtengo los abonossub del abono
			$idabono = $rowabonos["ID"];
		
			if ($debug == 1) {
				echo $qryabonossub . "<br>";
			}

			if ($debug == 1) {
				print_r($rowabonos);
			}	
					
			//Para cada concepto obtengo los datos necesarios y creo la linea
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrocliente;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$parametro = $rowparametro[0];
			
			//Recorro los abonos sub y armo las lineas
			$concepto_movimientosub = "";
			GenLineaPolizaMovimiento($archivotxt, $parametro . $rowabonos["ctaCliente"], $concepto_movimientosub.$rowabonos["factura"], "1",$rowabonos["Importe"]);
			
			if ($debug == 1) {
				print_r($rowabonossub);
			}
		
			//Igualo la fecha anterior a la fecha para el caso de polizas por dia
			$fechaant = $rowabonos["Fecha"];
		
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