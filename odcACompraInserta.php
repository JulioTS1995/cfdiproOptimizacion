<?php
set_time_limit(350);
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

function obtenerSiguienteID($cnx_cfdi2) {
    $begintrans = mysqli_query($cnx_cfdi2, "BEGIN");

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
    $result_qry_basidgen = mysqli_query($cnx_cfdi2, $qry_basidgen);

    if (!$result_qry_basidgen) {
        $endtrans = mysqli_query($cnx_cfdi2, "ROLLBACK");
        echo "Error4";
        return false;
    } else {
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
        $basidgen = $rowbasidgen[0] + 1;

        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi2, $upd_basidgen);

        if ($result_upd_basidgen) {
            $endtrans = mysqli_query($cnx_cfdi2, "COMMIT");
            return $basidgen;
        }
    }

    return false;
}

$prefijo = $_POST["prefijo"];
$prefijo2 = str_replace("_", "", $prefijo);
$idCompra = $_POST['id'];
$odcsSeleccionadas = $_POST['odcsSeleccionados'];

if(empty($odcsSeleccionadas)){
    echo "<script>alert('Debe seleccionar al menos un producto');</script>";//Imprime error
    exit;
}
$cont = 0;
$cont2 = 0;
foreach ($odcsSeleccionadas as $odcs) {

    $newid =  obtenerSiguienteID($cnx_cfdi2);
    $queryP = "INSERT INTO {$prefijo}ComprasSub (
        ID,
        FolioSub_REN,
        FolioSub_RID,
        PrecioUnitario,
        Nombre,
        ProductoA_REN,
        ProductoA_RID,
        Cantidad,
        TasaIVA,
        TasaRetencion,
        TasaISR,
        ImporteIVA,
        ImporteRetencion,
        ImporteISR,
        IEPS,
        Importe,
        ImporteTotal,
        ODC,
        OrdenCompraSub_REN,
        OrdenCompraSub_RID
    )
    SELECT 
        {$newid},
        'Compras',
        {$idCompra},
        PrecioUnitario,
        Nombre,
        ProductoA_REN,
        ProductoA_RID,
        CantidadPendiente,
        TasaIVA,
        TasaRetencion,
        TasaISR,
        ImporteIVA,
        ImporteRetencion,
        ImporteISR,
        IEPS,
        Importe,
        ImporteTotal,
        (SELECT XFolio FROM {$prefijo}OrdenCompra WHERE ID = (SELECT FolioSub_RID FROM {$prefijo}OrdenComprasSub WHERE ID = {$odcs})),
        'OrdenComprasSub',
        '$odcs'


    FROM {$prefijo}OrdenComprasSub
    WHERE ID = {$odcs};";



    $runP= mysqli_query($cnx_cfdi2, $queryP);
    if (!$runP) {//debug
        $mensaje  = 'Consulta no valida: [INSERT]' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $queryP;
        die($mensaje);
    }else{
        $cont++;
    }

$queryUpdate = "
    UPDATE {$prefijo}OrdenComprasSub odcs
    JOIN {$prefijo}OrdenCompra odc ON odcs.FolioSub_RID = odc.ID
    JOIN {$prefijo}Compras c       ON c.ID = {$idCompra}
    LEFT JOIN {$prefijo}Productos p     ON p.ID = odcs.ProductoA_RID
    SET odc.Compra   = c.XFolio,
        c.ODC        = odc.XFolio,
        odcs.Compra  = c.XFolio,
        p.Existencia = p.Existencia + odcs.CantidadPendiente
    WHERE odcs.ID = {$odcs};";


    $runUpdate= mysqli_query($cnx_cfdi2, $queryUpdate);
    if (!$runUpdate) {//debug
        $mensaje  = 'Consulta no valida: [UPDATE]' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $queryUpdate;
        die($mensaje);
    }else{
        $cont2++;
    }
}
if($cont>0){
    echo "<script>alert('Se insertaron {$cont} registros')</script>";
}else{
    echo "<script>alert('No se insertaron registros')</script>"; //error. Igual no deberia entrar
}


?>
