<?php 
require_once ('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$termino = isset($_GET['q']) ? mysqli_real_escape_string($cnx_cfdi2, $_GET['q']) : '';
$prefijodb = isset($_GET['prefijodb']) ? mysqli_real_escape_string($cnx_cfdi2, $_GET['prefijodb']) : '';

if (!empty($termino) && !empty($prefijodb)) {
    $sql = "SELECT 
            ID,
            RazonSocial
            FROM {$prefijodb}proveedores
            WHERE RazonSocial LIKE '%$termino%'
            ORDER BY RazonSocial ASC 
            LIMIT 20";

    $res = mysqli_query($cnx_cfdi2, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        echo'<div class="sugerencia" data-id="'. htmlspecialchars($row['ID']).'"> '.
            htmlspecialchars($row['RazonSocial'])
            .'</div>';
    }
}

?>