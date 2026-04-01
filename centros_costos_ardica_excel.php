<?php
error_reporting(0);

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Parámetros
$prefijobd    = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd    = str_replace(array("'", '"', ";"), "", $prefijobd);
$id_proveedor = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;
$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

$cntQuery = "";
if ($id_proveedor != 0) {
    $cntQuery = " AND C.ProveedorNo_RID = ".$id_proveedor." ";
}

// Query completa (sin paginar)
$sql = "
SELECT 
  C.ID,
  C.Fecha,
  C.Factura,
  C.Comentarios,
  C.XFolio,
  C.ProveedorNo_RID,
  CS.CeCo,
  CS.Cuenta,
  CS.SubCuenta,
  CS.Cantidad,
  CS.Importe,
  CS.Sucursal,
  CS.ClasificacionCC,
  CS.UnidadSub_RID,
  P.RazonSocial AS ProveedorNombre,
  U.Unidad AS UnidadNombre
FROM ".$prefijobd."Compras C
INNER JOIN ".$prefijobd."ComprasSub CS ON CS.FolioSub_RID = C.ID
LEFT JOIN ".$prefijobd."Proveedores P ON P.ID = C.ProveedorNo_RID
LEFT JOIN ".$prefijobd."Unidades U ON U.ID = CS.UnidadSub_RID
WHERE DATE(C.Fecha) BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery."
ORDER BY C.Fecha, C.ID
";
$res = mysqli_query($cnx_cfdi2, $sql);

// Cabeceras Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
$nombre="Centros_Costos_".date("d-m-Y")."_".date("H-i-s").".xls";
header("Content-Disposition: attachment; filename=".$nombre);
header("Pragma: no-cache");
header("Expires: 0");

echo '<html><head><meta charset="UTF-8">
<style>
  body{font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#111;}
  table{border-collapse:collapse; width:100%;}
  th,td{border:1px solid #555; padding:4px;}
  thead th{background:#dbe4ff; font-weight:bold;}
</style>
</head><body>';

echo '<table>';
echo '<thead>
  <tr>
    <th colspan="15" style="font-size:16px;">Centros de Costos: '.$fecha_inicio_f.' - '.$fecha_fin_f.'</th>
  </tr>
  <tr>
    <th>XFolio</th>
    <th>Fecha</th>
    <th>Mes</th>
    <th>Año</th>
    <th>CeCo</th>
    <th>Sucursal</th>
    <th>Unidad de Negocio</th>
    <th>Departamento</th>
    <th>Cuenta</th>
    <th>Sub cuenta</th>
    <th>Proveedor</th>
    <th>No. Factura</th>
    <th>Cantidad</th>
    <th>Monto</th>
    <th>Observaciones</th>
  </tr>
</thead><tbody>';

$total_monto = 0;
if ($res) {
  while ($row = mysqli_fetch_assoc($res)) {
    $xfolio   = $row['XFolio'];
    $v_fecha_t= $row['Fecha'];
    $v_fecha  = date("d-m-Y", strtotime($v_fecha_t));
    $obj_date = strtotime($v_fecha_t);
    $mes      = date("n", $obj_date);
    $anio     = date("Y", $obj_date);

    $CeCo        = $row['CeCo'];
    $cuenta      = $row['Cuenta'];
    $subCuenta   = $row['SubCuenta'];
    $cantidad    = $row['Cantidad'];
    $monto       = $row['Importe'];
    $total_monto+= floatval($monto);
    $departamento= $row['ClasificacionCC'];
    $sucursal    = $row['Sucursal'];
    $unidad      = $row['UnidadNombre'];
    $proveedor   = $row['ProveedorNombre'];
    $factura     = $row['Factura'];
    $observ      = $row['Comentarios'];

    echo '<tr>'.
           '<td>'.htmlspecialchars($xfolio).'</td>'.
           '<td>'.htmlspecialchars($v_fecha).'</td>'.
           '<td>'.htmlspecialchars($mes).'</td>'.
           '<td>'.htmlspecialchars($anio).'</td>'.
           '<td>'.htmlspecialchars($CeCo).'</td>'.
           '<td>'.htmlspecialchars($sucursal).'</td>'.
           '<td>'.htmlspecialchars($unidad).'</td>'.
           '<td>'.htmlspecialchars($departamento).'</td>'.
           '<td>'.htmlspecialchars($cuenta).'</td>'.
           '<td>'.htmlspecialchars($subCuenta).'</td>'.
           '<td>'.htmlspecialchars($proveedor).'</td>'.
           '<td>'.htmlspecialchars($factura).'</td>'.
           '<td>'.htmlspecialchars($cantidad).'</td>'.
           '<td>'.number_format($monto,2).'</td>'.
           '<td>'.htmlspecialchars($observ).'</td>'.
         '</tr>';
  }
}
echo '</tbody>';
echo '<tfoot><tr>'.
       '<td colspan="13" style="text-align:right;font-weight:bold;">Total</td>'.
       '<td style="font-weight:bold;">'.number_format($total_monto,2).'</td>'.
       '<td></td>'.
     '</tr></tfoot>';
echo '</table>';

echo '</body></html>';
exit;
