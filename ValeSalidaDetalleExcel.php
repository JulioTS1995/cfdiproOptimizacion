<?php
set_time_limit(3000);
error_reporting(0);

// Excel headers
header("Content-type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "Vale_Salida_detalle_" . date("Ymd_His") . ".xls";
header("Content-Disposition: attachment; filename=$nombre");
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";

// Conexión mysqli
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Params
$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') die("Falta prefijodb");
if (strpos($prefijobd, "_") === false) $prefijobd .= "_";

$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;
$fechaInicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fechaFin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';
if ($fechaInicio === '' || $fechaFin === '') die("Faltan fechas");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$qSafe = mysqli_real_escape_string($cnx_cfdi2, $q);

// Sucursal robusto
$hasSucursal = false;
try{
  $chk = mysqli_query($cnx_cfdi2, "SHOW COLUMNS FROM {$prefijobd}Oficinas LIKE 'Sucursal_RID'");
  $hasSucursal = ($chk && mysqli_num_rows($chk) > 0);
}catch(Exception $e){ $hasSucursal = false; }

$extraWhereSucursal = "";
if ($sucursal > 0 && $hasSucursal) {
  $extraWhereSucursal = " AND Vs.OficinaVSalida_RID IN (SELECT ID FROM {$prefijobd}Oficinas WHERE Sucursal_RID = {$sucursal}) ";
}

// Search
$whereSearch = "";
if ($qSafe !== '') {
  $whereSearch = " AND (
      Vs.XFolio LIKE '%{$qSafe}%'
      OR U.Unidad LIKE '%{$qSafe}%'
      OR Prd.Codigo LIKE '%{$qSafe}%'
      OR Prd.Nombre LIKE '%{$qSafe}%'
      OR VsS.Descripcion LIKE '%{$qSafe}%'
  ) ";
}

// Query único
$sql = "
  SELECT
    Vs.XFolio,
    Vs.Fecha,
    U.Unidad,
    Prd.Codigo,
    Prd.Nombre,
    VsS.Descripcion,
    VsS.Cantidad,
    VsS.PrecioUnitario,
    VsS.Importe
  FROM {$prefijobd}valessalida Vs
  LEFT JOIN {$prefijobd}unidades U ON Vs.Unidad_RID = U.ID
  LEFT JOIN {$prefijobd}valessalidasub VsS ON Vs.ID = VsS.FolioSub_RID
  LEFT JOIN {$prefijobd}productos Prd ON Prd.ID = VsS.ProductoV_RID
  WHERE DATE(Vs.Fecha) BETWEEN '{$fechaInicio}' AND '{$fechaFin}'
  {$extraWhereSucursal}
  {$whereSearch}
  ORDER BY Vs.XFolio ASC, VsS.ID ASC
";
$res = mysqli_query($cnx_cfdi2, $sql);
if (!$res) {
  echo "<h2>Error SQL</h2><pre>".htmlspecialchars(mysqli_error($cnx_cfdi2))."</pre>";
  exit;
}

if (mysqli_num_rows($res) <= 0) {
  echo "<h2>No se encontraron registros</h2>";
  exit;
}
?>
<table border="1">
  <thead>
    <tr>
      <th>Folio</th>
      <th>Fecha</th>
      <th>Unidad</th>
      <th>Código</th>
      <th>Nombre</th>
      <th>Descripción</th>
      <th>Cantidad</th>
      <th>Precio Unitario</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $sumCantidad = 0;
      $sumPrecioU  = 0;
      $sumTotal    = 0;

      while($row = mysqli_fetch_assoc($res)){
        $fecha = $row['Fecha'] ? date("d-m-Y", strtotime($row['Fecha'])) : '';
        $cantidad = (float)$row['Cantidad'];
        $precioU  = (float)$row['PrecioUnitario'];
        $importe  = (float)$row['Importe'];

        $sumCantidad += $cantidad;
        $sumPrecioU  += $precioU;
        $sumTotal    += $importe;
        ?>
        <tr>
          <td><?php echo htmlspecialchars($row['XFolio']); ?></td>
          <td><?php echo htmlspecialchars($fecha); ?></td>
          <td><?php echo htmlspecialchars($row['Unidad']); ?></td>
          <td><?php echo htmlspecialchars($row['Codigo']); ?></td>
          <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
          <td><?php echo htmlspecialchars($row['Descripcion']); ?></td>
          <td align="right"><?php echo number_format($cantidad, 2, '.', ''); ?></td>
          <td align="right"><?php echo number_format($precioU, 2, '.', ''); ?></td>
          <td align="right"><?php echo number_format($importe, 2, '.', ''); ?></td>
        </tr>
        <?php
      }
    ?>
    <tr>
      <td colspan="6" align="right"><b>TOTAL:</b></td>
      <td align="right"><b><?php echo number_format($sumCantidad, 2, '.', ''); ?></b></td>
      <td align="right"><b><?php echo number_format($sumPrecioU, 2, '.', ''); ?></b></td>
      <td align="right"><b><?php echo number_format($sumTotal, 2, '.', ''); ?></b></td>
    </tr>
  </tbody>
</table>
<?php exit; ?>
