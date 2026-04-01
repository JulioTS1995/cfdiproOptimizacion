<?php 
//error_reporting(0);
function obtenerSiguienteID($cnx_cfdi3) {
    mysqli_query($cnx_cfdi3, "BEGIN");

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen FOR UPDATE";
    $result_qry_basidgen = mysqli_query($cnx_cfdi3, $qry_basidgen);

    if (!$result_qry_basidgen) {
        mysqli_query($cnx_cfdi3, "ROLLBACK");
        echo "Error4";
        return false;
    } else {
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
        $basidgen = $rowbasidgen[0] + 1;

        mysqli_free_result($result_qry_basidgen); 

        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi3, $upd_basidgen);

        if ($result_upd_basidgen) {
            mysqli_query($cnx_cfdi3, "COMMIT");
            return $basidgen;
        } else {
            mysqli_query($cnx_cfdi3, "ROLLBACK");
        }
    }

    return false;
}


$prefijo = $_GET["prefijo"];
$idFact = $_GET["idFact"];
$folioSubRen = 'Remisiones';
$datosActualizados = 0;

require_once('cnx_cfdi3.php');

if ($cnx_cfdi3->connect_error) {
    die('Error de conexion a la base de datos.');
}



//borra partidas y embalaje
$queryDeletePartidas = "DELETE FROM {$prefijo}Remisionespartidas WHERE FolioSub_RID IN (SELECT Remision_RID 
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

$remisiones = [];

$stmtRemisiones = $cnx_cfdi3->prepare($queryPartidas);
if (!$stmtRemisiones) {
    die("Error en la preparacion de la consulta[S:Partidas]: " . $cnx_cfdi3->error);
}

$stmtRemisiones->execute();
$stmtRemisiones->store_result();
$stmtRemisiones->bind_result($idRem);

while ($stmtRemisiones->fetch()) {
    $remisiones[] = $idRem;
}

$stmtRemisiones->free_result();
$stmtRemisiones->close(); 
foreach ($remisiones as $idRem) {
    
    $queryDetallePartidas = "SELECT 
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
        Tipo
    FROM {$prefijo}FacturaPartidas 
    WHERE FolioSub_RID = {$idFact};";

    $stmtDetalle = $cnx_cfdi3->prepare($queryDetallePartidas);
    if (!$stmtDetalle) {
        die("Error en la preparacion de la consulta[Detalle:Partidas]: " . $cnx_cfdi3->error);
    }

    $stmtDetalle->execute();
    $stmtDetalle->bind_result(
        $cantidad,
        $claveunidad33,
        $conceptoPartida,
        $descripcionClaveUnidad,
        $descuento,
        $descuentoImporte,
        $detalle,
        $excento,
        $folioConceptos_REN,
        $folioConceptos_RID,
        $folioConceptos_RMA,
        $importe,
        $iva,
        $ivaImporte,
        $precioUnitario,
        $prodserv33,
        $prodserv33dsc,
        $retencion,
        $retencionImporte,
        $subtotal,
        $subtotal1,
        $tipo
    );

    $partidas = [];

    while ($stmtDetalle->fetch()) {
        $partidas[] = [
            'cantidad' => $cantidad,
            'claveunidad33' => $claveunidad33,
            'conceptoPartida' => $conceptoPartida,
            'descripcionClaveUnidad' => $descripcionClaveUnidad,
            'descuento' => $descuento,
            'descuentoImporte' => $descuentoImporte,
            'detalle' => $detalle,
            'excento' => $excento,
            'folioConceptos_REN' => $folioConceptos_REN,
            'folioConceptos_RID' => $folioConceptos_RID,
            'folioConceptos_RMA' => $folioConceptos_RMA,
            'importe' => $importe,
            'iva' => $iva,
            'ivaImporte' => $ivaImporte,
            'precioUnitario' => $precioUnitario,
            'prodserv33' => $prodserv33,
            'prodserv33dsc' => $prodserv33dsc,
            'retencion' => $retencion,
            'retencionImporte' => $retencionImporte,
            'subtotal' => $subtotal,
            'subtotal1' => $subtotal1,
            'tipo' => $tipo
        ];
    }

    $stmtDetalle->free_result();
    $stmtDetalle->close();

    $queryInsertPartidas = "INSERT INTO {$prefijo}RemisionesPartidas (
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
        FolioSub_RID
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtInsertPartidas = $cnx_cfdi3->prepare($queryInsertPartidas);
    if (!$stmtInsertPartidas) {
        die("Error en la preparacion de la consulta[I:Partidas]: " . $cnx_cfdi3->error);
    }

    foreach ($partidas as $partida) {
        $idPartida = obtenerSiguienteID($cnx_cfdi3);

        $stmtInsertPartidas->bind_param(
            'sssssssssssssssssssssssss',
            $idPartida,
            $partida['cantidad'],
            $partida['claveunidad33'],
            $partida['conceptoPartida'],
            $partida['descripcionClaveUnidad'],
            $partida['descuento'],
            $partida['descuentoImporte'],
            $partida['detalle'],
            $partida['excento'],
            $partida['folioConceptos_REN'],
            $partida['folioConceptos_RID'],
            $partida['folioConceptos_RMA'],
            $partida['importe'],
            $partida['iva'],
            $partida['ivaImporte'],
            $partida['precioUnitario'],
            $partida['prodserv33'],
            $partida['prodserv33dsc'],
            $partida['retencion'],
            $partida['retencionImporte'],
            $partida['subtotal'],
            $partida['subtotal1'],
            $partida['tipo'],
            $folioSubRen,
            $idRem
        );

        if ($stmtInsertPartidas->execute()) {
            $datosActualizados++;
        } else {
            echo "Error al insertar partida: " . $stmtInsertPartidas->error;
        }
    }

    $stmtInsertPartidas->close();


}


echo "<script>alert('Se actualizaron {$datosActualizados} registros');</script>";


?>