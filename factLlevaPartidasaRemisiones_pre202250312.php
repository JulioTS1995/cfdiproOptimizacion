<?php 
//error_reporting(0);
function obtenerSiguienteID($cnx_cfdi3) {
    $begintrans = mysqli_query($cnx_cfdi3, "BEGIN");

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
    $result_qry_basidgen = mysqli_query($cnx_cfdi3, $qry_basidgen);

    if (!$result_qry_basidgen) {
        $endtrans = mysqli_query($cnx_cfdi3, "ROLLBACK");
        echo "Error4";
        return false;
    } else {
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
        $basidgen = $rowbasidgen[0] + 1;

        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi3, $upd_basidgen);

        if ($result_upd_basidgen) {
            $endtrans = mysqli_query($cnx_cfdi3, "COMMIT");
            return $basidgen;
        }
    }

    return false;
}

$prefijo = $_GET["prefijo"];
$idFact = $_GET["idFact"];

$datosActualizados = 0;

require_once('cnx_cfdi3.php');

if ($cnx_cfdi3->connect_error) {
    die('Error de conexion a la base de datos.');
}



//borra partidas y embalaje
$queryDeletePartidas = "DELETE FROM {$prefijo}Remisionespartidas WHERE FolioSub_RID = (SELECT Remision_RID 
    FROM {$prefijo}facturasdetalle 
    WHERE FolioSubDetalle_RID = {$idFact})";

$stmtDeletePartidas = $cnx_cfdi3->prepare($queryDeletePartidas);
if (!$stmtDeletePartidas) {
	die("Error en la preparacion de la consulta[D:Partidas]: " . $cnx_cfdi3->error);
}
$stmtDeletePartidas->execute();


//inserta partidas
$queryPartidas = "SELECT Remision_RID 
    FROM {$prefijo}facturasdetalle 
    WHERE FolioSubDetalle_RID = {$idFact};";

$stmtPartidas = $cnx_cfdi3->prepare($queryPartidas);
if (!$stmtPartidas) {
	die("Error en la preparacion de la consulta[S:Partidas]: " . $cnx_cfdi3->error);
}

$stmtPartidas->execute();
$stmtPartidas->store_result();

$stmtPartidas->bind_result($idRem);


while ($stmtPartidas->fetch()) {
    $idPartida=obtenerSiguienteID($cnx_cfdi3);
    $queryInsertPartidas = "INSERT INTO {$prefijo}RemisionesPartidas(
        ID,
        Cantidad,
        claveunidad33,
        ConceptoPartida,
        DescripcionClaveUnidad,
        Descuento,
        DescuentoImporte,
        Detalle,
        Excento,
        FolioConceptos_REN,
        FolioConceptos_RID,
        FolioConceptos_RMA,
        Importe,
        IVA,
        IVAImporte,
        PrecioUnitario,
        prodserv33,
        prodserv33dsc,
        Retencion,
        RetencionImporte,
        Subtotal,
        Subtotal1,
        Tipo,
        FolioSub_REN,
        FolioSub_RID) 
    (
    SELECT 
        '{$idPartida}',
        Cantidad,
        claveunidad33,
        ConceptoPartida,
        DescripcionClaveUnidad,
        Descuento,
        DescuentoImporte,
        Detalle,
        Excento,
        FolioConceptos_REN,
        FolioConceptos_RID,
        FolioConceptos_RMA,
        Importe,
        IVA,
        IVAImporte,
        PrecioUnitario,
        prodserv33,
        prodserv33dsc,
        Retencion,
        RetencionImporte,
        Subtotal,
        Subtotal1,
        Tipo,
        'Remisiones',
        '{$idRem}'
    FROM {$prefijo}FacturaPartidas WHERE FolioSub_RID = {$idFact}
    )";
    die($queryInsertPartidas);
    $stmtInsertPartidas = $cnx_cfdi3->prepare($queryInsertPartidas);
    if (!$stmtInsertPartidas) {
        die("Error en la preparacion de la consulta[I:Partidas]: " . $cnx_cfdi3->error);
    }
    
    if($stmtInsertPartidas->execute()){
        $datosActualizados++;
    }



}



echo "<script>alert('Se actualizaron {$datosActualizados} registros');</script>";


?>