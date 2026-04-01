<?php

require_once('cnx_cfdi3.php'); // Conexión a la base de datos
set_time_limit(300);

$id = $_GET['id'];
$prefijo = $_GET['prefijo'];
 if (!isset($_GET['tipoT']) || empty($_GET['tipoT'])) {
        $tipoArchivo = "factura";
    } else {
        $tipoArchivo = $_GET['tipoT'];
    }

$tabla = $prefijo.$tipoArchivo;
$sql = "SELECT cfdiuuid, cfdmsgerror FROM $tabla WHERE ID = ? LIMIT 1";

$stmt = $cnx_cfdi3->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$stmt->bind_result($cfdiuuid, $cfdmsgerror);
$stmt->fetch();
$stmt->close();

if (($cfdmsgerror === 'Este documento ya se intento timbrar, Debe recuperar folio para intentar timbrar otra vez') && (!empty($cfdiuuid))){
    $cfdmsgerror = NULL;
}

if (($cfdmsgerror === 'Folio previamente utilizado.') && (!empty($cfdiuuid))){
    $cfdmsgerror = NULL;    
}


if ((!empty($cfdiuuid)) && (empty($cfdmsgerror))) {
    echo json_encode([
        "existe" => true,
        "mensaje" => ""
    ]);
} else {
    echo json_encode([
        "existe" => false,
        "mensaje" => $cfdmsgerror ?: "No se encontró UUID o hay un error en la factura."
    ]);
}

?>