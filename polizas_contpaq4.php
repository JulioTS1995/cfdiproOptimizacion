<?php
//Incluido para las funciones de la version 4 de contpaq


/*****
 * Funcion AgregaEspacios
 * Agrega la cantidad de espacios necesarios para que la cadena cumpla el tamaño enviado
 */
function AgregaEspacios($cadena, $tamaniototal)
{
	//Reviso el tamaño de la cadena enviada
	return str_pad($cadena, $tamaniototal); 
}
function AgregaEspaciosIzq($cadena, $tamaniototal)
{
	//Reviso el tamaño de la cadena enviada
	return str_pad($cadena, $tamaniototal, " ", STR_PAD_LEFT); 
}

/********
 * Funcion GenLineaPolizaEncabezado
 * Genera la linea del encabezado de acuerdo a los parametros enviados- AgregaEspacios(str_replace('-', '', $fecha), 12).AgregaEspacios($tipopoliza, 4).AgregaEspaciosIzq($folio, 7).AgregaEspacios($fijo, 14) . AgregaEspacios($concepto, 101); 
 */
function GenLineaPolizaEncabezado($archivo, $fecha, $tipopoliza, $folio, $concepto)
{
	//Póliza(P)	Fecha	TipoPol	Folio	Clase	IdDiario	Concepto	SistOrig	Impresa	Ajuste	Guid
	//P	20110901	3	11090001	1	0		11	0	0	B321648D-011E-486C-869A-13E4C659CB32
	$fijo = " 1 0 ";
	$encabezado = "P  " .str_replace ("-" , "",substr($fecha,0,10))." ".AgregaEspaciosIzq($tipopoliza,4)." ".AgregaEspaciosIzq($folio,9).AgregaEspacios($fijo, 14) . AgregaEspacios($concepto, 100)." 11 1 0 ";
	$debug =1;
	if ($debug == 1)
	{
		echo "Encabezado: " . $encabezado . "<br>";
	}
	EscribeArchivo($encabezado, $archivo);
}

/*******
 * Funcion GenLineaPolizaMovimiento
 * Genera la linea del movimiento de acuerdo a los parametros enviados
 */
function GenLineaPolizaMovimiento($archivo, $cuenta, $concepto, $tipomov, $importe)
{
	//Movimiento de póliza(M1)	IdCuenta	Referencia	TipoMovto	Importe	IdDiario	ImporteME	Concepto	IdSegNeg	Guid	
	//M1	112100068		1	1000	0	0	TRASPASO DE PUEBLA A MEXICALI	   1	BDFAB8D4-2F7C-4CDB-806D-057C1A3029BD

	//Reviso si existe el punto y si no se lo agrego
	$concepto2=" ";
	//$pospunto = strpos($importe, ".");

	//if ($pospunto === false) {
 	//	$importe = $importe . ".0";
	//} 

	$linea = "M1 ".AgregaEspacios($cuenta,31) . AgregaEspacios($concepto, 21) . $tipomov." ".str_pad(number_format($importe,2,".",""),20," ")." 0          0.0                  ".AgregaEspacios($concepto2,101)."     ";
	$debug =1;	
	if ($debug == 1)
	{
		echo "Linea: " . $linea . "<br>";
	}
	EscribeArchivo($linea, $archivo);
	
}

function GenLineaPolizaMovimiento2($archivo, $cuenta, $concepto, $tipomov, $importe, $diario)
{
	//Movimiento de póliza(M1)	IdCuenta	Referencia	TipoMovto	Importe	IdDiario	ImporteME	Concepto	IdSegNeg	Guid	
	//M1	112100068		1	1000	0	0	TRASPASO DE PUEBLA A MEXICALI	   1	BDFAB8D4-2F7C-4CDB-806D-057C1A3029BD
	$diario2=0;
	if (isset($diario))
	{
		$diario2=$diario;
	}
	//Reviso si existe el punto y si no se lo agrego
	$concepto2=" ";
	//$pospunto = strpos($importe, ".");

	//if ($pospunto === false) {
 	//	$importe = $importe . ".0";
	//} 

	$linea = "M1 ".AgregaEspacios($cuenta,31) . AgregaEspacios($concepto, 21) . $tipomov." ".str_pad(number_format($importe,2,".",""),20," ")." ".str_pad($diario2,10," ")." 0.0                  ".AgregaEspacios($concepto2,101)."     ";
	$debug =1;	
	if ($debug == 1)
	{
		echo "Linea: " . $linea . "<br>";
	}
	EscribeArchivo($linea, $archivo);
	
}

/*****
 * Funcion AbreArchivo
 * Abre el archivo de texto para escritura
 */
function AbreArchivo($archivo)
{
	$archivotxt = fopen($archivo, "w");
	return $archivotxt;
}

/*****
 * Funcion CierraArchivo
 * Cierra el archivo
 */
function CierraArchivo($archivo)
{
	fclose($archivo);
}

/*****
 * Funcion EscribeArchivo
 * Escribe en el archivo de texto la linea que se envia
 */
function EscribeArchivo($linea, $archivo)
{
	fwrite($archivo, $linea . PHP_EOL);
}
?>