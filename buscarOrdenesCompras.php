<?php
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$termino = isset($_GET['q']) ? mysqli_real_escape_string($cnx_cfdi2, $_GET['q']) : '';
$prefijodb = isset($_GET['prefijodb']) ? mysqli_real_escape_string($cnx_cfdi2, $_GET['prefijodb']) : '';
$idProveedor = isset($_GET['idProveedor']) ? mysqli_real_escape_string($cnx_cfdi2, $_GET['idProveedor']) : '';

if (!empty($termino) && !empty($prefijodb)) {
    $sql = "SELECT ID, XFolio, ODC FROM {$prefijodb}Compras WHERE ProveedorNo_RID=".$idProveedor." AND Total>0
            AND UUID IS NULL AND Estatus!='Cancelado' AND (ODC LIKE '%$termino%' OR XFolio LIKE '%$termino%') ORDER BY XFolio LIMIT 20";
    //die($sql);
    $res = mysqli_query($cnx_cfdi2, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        echo '<div class="sugerencia" data-id="' . htmlspecialchars($row['ID']) . '">' .
             htmlspecialchars($row['XFolio']) . ' / ' . htmlspecialchars($row['ODC']) .
             '</div>';
    }
}
?>