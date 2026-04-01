<?php
error_reporting(0);
function obtenerSiguienteID($cnx_cfdi3) {

    if (!$cnx_cfdi3->begin_transaction()) {
        echo "Error al iniciar la transaccion.";
        return false;
    }

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
    $result_qry_basidgen = $cnx_cfdi3->query($qry_basidgen);

    if (!$result_qry_basidgen) {
        $cnx_cfdi3->rollback();
        echo "Error en la consulta SELECT.";
        return false;
    }

    $rowbasidgen = $result_qry_basidgen->fetch_row();
    $basidgen = $rowbasidgen[0] + 1;

    $upd_basidgen = "UPDATE bas_idgen SET MAX_ID = $basidgen";
    if ($cnx_cfdi3->query($upd_basidgen)) {
        $cnx_cfdi3->commit();
        return $basidgen;
    } else {
        $cnx_cfdi3->rollback();
        echo "Error en la consulta UPDATE.";
        return false;
    }
}

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexion a la base de datos.');
}

$idLiq = $_GET["id"];
$xFolioLiq = $_GET["XFolio"];
$prefijobd = $_GET["prefijodb"];

$queryGastosEnLiq = $cnx_cfdi3->prepare("SELECT GastosLiq_RID 
FROM ".$prefijobd."LiquidacionesGastos WHERE FolioSubGastos_RID = ?");
if (!$queryGastosEnLiq) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

$queryGastosEnLiq->bind_param("s", $idLiq);
$queryGastosEnLiq->execute();
$queryGastosEnLiq->store_result();

$queryGastosEnLiq->bind_result($idGastoDelete);

$resultadosDelete = [];

while ($queryGastosEnLiq->fetch()) {
    $resultadosDelete[] = [
        'idGasto' => $idGastoDelete
    ];
}

$updateDeleteQuery = $cnx_cfdi3->prepare("UPDATE ".$prefijobd."GastosViajes SET Liquidacion = NULL WHERE ID = ?");
if (!$updateDeleteQuery) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

foreach ($resultadosDelete as $fila) {
    $updateDeleteQuery->bind_param(
        "s",
        $fila['idGasto']
    );
    $updateDeleteQuery->execute();
}

$deleteQuery = $cnx_cfdi3->prepare("DELETE FROM ".$prefijobd."LiquidacionesGastos WHERE GastosLiq_RID = ?");
if (!$deleteQuery) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

foreach ($resultadosDelete as $fila) {
    $deleteQuery->bind_param(
        "s",
        $fila['idGasto']
    );
    $deleteQuery->execute();
}


$queryRemEnLiq = $cnx_cfdi3->prepare("SELECT ID, Fecha, TipoVale, LitrosCombustible, Subtotal, Importe, Concepto, Unidad_RID, KmsHrs,
(SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = GV.OperadorNombre_RID) AS Operador
FROM ".$prefijobd."GastosViajes GV WHERE Remision_RID IN 
(SELECT RemisionLiq_RID FROM ".$prefijobd."LiquidacionesSub WHERE FolioSub_RID = ?)");
if (!$queryRemEnLiq) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

$queryRemEnLiq->bind_param("s", $idLiq);
$queryRemEnLiq->execute();
$queryRemEnLiq->store_result();

$queryRemEnLiq->bind_result($idGasto, $fecha, $tipoVale, $litrosCombustible, $subtotal, $importe, $concepto, $unidadRID, $kmsHrs, $operador);

$resultados = [];

while ($queryRemEnLiq->fetch()) {
    $resultados[] = [
        'idGasto' => $idGasto,
        'Fecha' => $fecha,
        'TipoVale' => $tipoVale,
        'LitrosCombustible' => $litrosCombustible,
        'Subtotal' => $subtotal,
        'Importe' => $importe,
        'Concepto' => $concepto,
        'Unidad_RID' => $unidadRID,
        'KmsHrs' => $kmsHrs,
        'Operador' => $operador
    ];
}

$contInsert = 0;

$unidadesREN = 'Unidades';
$liquidacionesREN = 'Liquidaciones';
$gastosViajesREN = 'GastosViajes';

$insertQuery = $cnx_cfdi3->prepare("INSERT INTO ".$prefijobd."LiquidacionesGastos 
(ID, Fecha, Tipo, Litros, Subtotal, Importe, Concepto, Unidad_REN, Unidad_RID, Operador, FolioSubGastos_REN, FolioSubGastos_RID, GastosLiq_REN, GastosLiq_RID) VALUES 
(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$insertQuery) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

foreach ($resultados as $fila) {
    $insertQuery->bind_param(
        "sssddssssssssi",
        obtenerSiguienteID($cnx_cfdi3),
        $fila['Fecha'],
        $fila['TipoVale'],
        $fila['LitrosCombustible'],
        $fila['Subtotal'],
        $fila['Importe'],
        $fila['Concepto'],
        $unidadesREN,
        $fila['Unidad_RID'],
        $fila['Operador'],
        $liquidacionesREN,
        $idLiq,
        $gastosViajesREN,
        $fila['idGasto']

    );
    if ($insertQuery->execute()) {
        $contInsert++;
    }
}

if ($contInsert > 0) {
    echo "<script>alert('Se insertaron " . $contInsert . " filas correctamente.');</script>";//Imprime exito
} else {
    echo "<script>alert('No se encontraron datos para insertar.');</script>";
}

$updateQuery = $cnx_cfdi3->prepare("UPDATE ".$prefijobd."GastosViajes SET Liquidacion = ? WHERE ID = ?");
if (!$updateQuery) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

foreach ($resultados as $fila) {
    $updateQuery->bind_param(
        "ss",
        $xFolioLiq,
        $fila['idGasto']
    );
    $updateQuery->execute();
}


$queryRemEnLiq->close();
$insertQuery->close();
$cnx_cfdi3->close();

?>