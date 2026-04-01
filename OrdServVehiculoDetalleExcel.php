<?php


error_reporting(0);
set_time_limit(3000);

header("Content-type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "OrdServVehiculoDetalle_" . date("Ymd_His") . ".xls";
header("Content-Disposition: attachment; filename=" . $nombre);


require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);

$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

$unidadID = isset($_GET['unidad']) ? intval($_GET['unidad']) : 0;
$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;


$emisor = isset($_GET['emisor']) ? intval($_GET['emisor']) : 0;

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));


$whereSucursal = "";
if ($sucursal > 0) {
    $whereSucursal = " AND M.OficinaMant_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ";
}


$whereUnidad = "";
if ($unidadID > 0) {
    $whereUnidad = " AND M.UnidadMantenimiento_RID = ".$unidadID." ";
}

$sql = " SELECT
          M.XFolio,
          M.Fecha,
          U.Unidad,
          Ms.Kilometros,
          R.Reparacion,
          T.Taller,
          VS.Subtotal,
          VS.Impuesto,
          VS.Total,
          P.Nombre,
          VSS.Cantidad,
          VSS.PrecioUnitario,
          VSS.Importe,
          VSS.ImporteIVA,
          VSS.ImporteTotal
          FROM {$prefijobd}ValesSalidaSub VSS
          LEFT JOIN {$prefijobd}ValesSalida VS ON VS.ID = VSS.FolioSub_RID
          LEFT JOIN {$prefijobd}Mantenimientos M ON M.ID = VS.MantVSalida_RID
          LEFT JOIN (
          SELECT
          FolioSub_RID,
          MAX(Kilometros) AS Kilometros,
          MIN(Reparacion_RID) AS Reparacion_RID,
          MIN(Taller_RID) AS Taller_RID
          FROM {$prefijobd}MantenimientosSub
          GROUP BY FolioSub_RID
          ) Ms ON Ms.FolioSub_RID = M.ID
          LEFT JOIN {$prefijobd}Unidades U ON U.ID = M.UnidadMantenimiento_RID
          LEFT JOIN {$prefijobd}Reparaciones R ON R.ID = Ms.Reparacion_RID
          LEFT JOIN {$prefijobd}Talleres T ON T.ID = Ms.Taller_RID
          LEFT JOIN {$prefijobd}Productos P ON P.ID = VSS.ProductoV_RID
          WHERE DATE(M.Fecha) BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
          {$whereSucursal}
          {$whereUnidad}
          ORDER BY U.Unidad ASC, M.XFolio ASC, VSS.ID ASC
         
          ";

$res = mysqli_query($cnx_cfdi2, $sql);

?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<table border="0" cellspacing="0" cellpadding="0" style="width:100%; font-family: Arial, Helvetica, sans-serif;">
  <tr>
    <td colspan="15" style="font-size:14pt; font-weight:bold; text-align:center;">
      Órdenes de Servicio por Vehículo · Detalle
    </td>
  </tr>
  <tr>
    <td colspan="15" style="font-size:10pt; text-align:center; color:#333;">
      Periodo: <?php echo htmlspecialchars($fecha_inicio_f)." - ".htmlspecialchars($fecha_fin_f); ?>
      · Unidad: <?php echo ($unidadID>0 ? intval($unidadID) : 'TODAS'); ?>
      · Sucursal: <?php echo ($sucursal>0 ? intval($sucursal) : 'TODAS'); ?>
    </td>
  </tr>
</table>

<br>

<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse; width:100%; font-family: Arial, Helvetica, sans-serif; font-size:9pt;">
  <thead>
    <tr style="background:#e0e0e0; font-weight:bold; text-align:center;">
      <th>Orden</th>
      <th>Fecha</th>
      <th>Vehículo</th>
      <th>Km</th>
      <th>Servicio</th>
      <th>Taller</th>
      <th>Subtotal</th>
      <th>IVA</th>
      <th>Total</th>
      <th>Artículo</th>
      <th>Cant</th>
      <th>P.Unit</th>
      <th>Importe</th>
      <th>IVA</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
<?php
$ultimoFolio = null;

$sumSubtotal = 0.0;
$sumImpuesto = 0.0;
$sumTotal = 0.0;

$sumCantidad = 0.0;
$sumImporte = 0.0;
$sumImporteIVA = 0.0;
$sumImporteTotal = 0.0;

$hubo = false;

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $hubo = true;

        $folio = $row['XFolio'];
        $v_fecha = !empty($row['Fecha']) ? date("d-m-Y", strtotime($row['Fecha'])) : '';
        $isNew = ($ultimoFolio === null || $ultimoFolio !== $folio);

 
        if ($isNew) {
            $sumSubtotal += floatval($row['Subtotal']);
            $sumImpuesto += floatval($row['Impuesto']);
            $sumTotal    += floatval($row['Total']);
        }

        $sumCantidad      += floatval($row['Cantidad']);
        $sumImporte       += floatval($row['Importe']);
        $sumImporteIVA    += floatval($row['ImporteIVA']);
        $sumImporteTotal  += floatval($row['ImporteTotal']);

        if ($isNew) {
            ?>
            <tr style="background:#f4f4f4; font-weight:bold;">
              <td style="text-align:center;"><?php echo htmlspecialchars($folio); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($v_fecha); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Unidad']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Kilometros']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Reparacion']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Taller']); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['Subtotal'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['Impuesto'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['Total'],2); ?></td>

              <td style="text-align:left;"><?php echo htmlspecialchars($row['Nombre']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Cantidad']); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['PrecioUnitario'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['Importe'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['ImporteIVA'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['ImporteTotal'],2); ?></td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
              <td colspan="9">&nbsp;</td>
              <td style="text-align:left;"><?php echo htmlspecialchars($row['Nombre']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($row['Cantidad']); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['PrecioUnitario'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['Importe'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['ImporteIVA'],2); ?></td>
              <td style="text-align:right;"><?php echo '$'.number_format((float)$row['ImporteTotal'],2); ?></td>
            </tr>
            <?php
        }

        $ultimoFolio = $folio;
    }
} else {
    ?>
    <tr>
      <td colspan="15" style="text-align:center; color:#333;">Sin resultados con los filtros actuales.</td>
    </tr>
    <?php
}

if ($hubo) {
?>
    <tr style="background:#d9ebff; font-weight:bold;">
      <td style="text-align:center;">TOTAL</td>
      <td colspan="5"></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumSubtotal,2); ?></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumImpuesto,2); ?></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumTotal,2); ?></td>

      <td></td>
      <td style="text-align:center;"><?php echo number_format($sumCantidad,2); ?></td>
      <td></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumImporte,2); ?></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumImporteIVA,2); ?></td>
      <td style="text-align:right;"><?php echo '$'.number_format($sumImporteTotal,2); ?></td>
    </tr>
<?php } ?>

  </tbody>
</table>
<?php
// Cerrar result
if ($res) { mysqli_free_result($res); }
?>
