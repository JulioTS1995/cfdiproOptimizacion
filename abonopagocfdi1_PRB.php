<?php
/* NombreArchivo.php:
 * 
 * Recibe:
 * 	Id del registro
 * 	Instancia de la base de datos - prefijo
 */

//======================================================================
// Se define el nombre del archivo bat.
$nombrebat = "nombrebat_PRB.bat";


//======================================================================
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

//======================================================================
// Ejucuta el bat...

$linea = exec("C:\\xampp\\htdocs\\cfdipro\\abonopagocfdi1_PRB.bat ".$_REQUEST["id"]." ".$prefijobd);

?>
