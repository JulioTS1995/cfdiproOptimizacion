<?php

/* NombreArchivo.php:
* 
* Recibe:
* 	Id del registro
* 	Instancia de la base de datos - prefijo
*/

//======================================================================
// Se define el nombre del archivo bat.
$nombrebat = "nombrebat.bat";


//======================================================================
//Verifico que vengan todos los parametros y que ninguno sea vacio

if (!isset($_REQUEST['facturaid']) || empty($_REQUEST['facturaid'])) {
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

require_once('cnx_cfdi3.php');

if ($cnx_cfdi3->connect_error) {
    die("Error de conexión: " . $cnx_cfdi3->connect_error);
}

$stmtFact = $cnx_cfdi3->prepare("SELECT d.uuid FROM basdb.{$prefijobd}factura f 
    LEFT JOIN tractosoft.documentos d ON f.cfdiuuid = d.uuid 
    WHERE f.id = ".$_REQUEST["facturaid"].";");

if (!$stmtFact) {
    die("Error en la preparación de la consulta [Factura]: " . $cnx_cfdi3->error);
}

$stmtFact->execute();
$resultFact = $stmtFact->get_result();

$uuidForsedi = null;
while ($row = $resultFact->fetch_assoc()) {
    $uuidForsedi = $row['uuid'];
}
$stmtFact->close();


if (empty($uuidForsedi)) {
    include (__DIR__.'\\prodigia\\apiCfdiCancelar.php');
}else{
    //======================================================================
    // Ejucuta el bat...

    $linea = exec("C:\\xampp\\htdocs\\cfdipro\\factura_cancela1.bat ".$_REQUEST["facturaid"]." ".$prefijobd);

    //echo $linea;
}


?>
