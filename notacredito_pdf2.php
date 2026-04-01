<?php
/* NombreArchivo.php:
 * 
 * Recibe:
 * 	Id del registro
 * 	Instancia de la base de datos - prefijo
 * 	Numero de reporte - numreporte
 */

$debug = 0;

//Emulo los parametros para la prueba
if ($debug == 1) {
	$_REQUEST['facturaid'] = 1;
	$_REQUEST['prefijo'] = "rojo";
	$_REQUEST['numreporte'] = 1;
}

//======================================================================
// Se define el nombre del archivo bat.
$nombrebat = "notacredito_pdf2.bat";


//Configuro el valor que voy a buscar de la tabla de parametros
//-------------------------MODIFICAR--------------------------------//
$idnombrereporte = 126;

//Verifico que vengan todos los parametros y que ninguno sea vacio

if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die("Falta id de la factura");
}
if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    die("Falta el prefijo de la base de datos");
}

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

//Obtengo el nombre del reporte que se le enviara al bat
$qryreporte = "SELECT VCHAR FROM " . $prefijobd . "parametro WHERE id2 = " . $idnombrereporte;

if ($debug == 1) {
	echo $qryreporte;
}

$resultqryreporte = mysql_query($qryreporte, $cnx_cfdi);

if (!$resultqryreporte) {
    die('Nombre de reporte no encontrado: ' . mysql_error());
}

$rowreporte = mysql_fetch_row($resultqryreporte);

$nombrereporte = $rowreporte[0];

if ($debug == 1) {
	echo $nombrereporte;
}

//Linea original:
//$linea = exec("C:\\xampp\\htdocs\\cfdipro\\notacredito_pdf2.bat ".$_REQUEST["notacreditoid"]);

//Linea modificada:
$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$nombrebat." ".$_REQUEST["id"]." ".$nombrereporte." ".$prefijobd);

if ($debug == 1) {
	echo $linea;
	
	//$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$nombrebat." ".$_REQUEST["id"]." ".$nombrereporte." ".$prefijobd);
	echo $linea;
}

?>

