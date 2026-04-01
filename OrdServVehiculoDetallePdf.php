<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// ===== PARAMS =====
$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);
$prefijo = rtrim($prefijobd, "_");
$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

$unidadID = isset($_GET['unidad']) ? intval($_GET['unidad']) : 0;
$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;
$emisor   = isset($_GET['emisor']) ? intval($_GET['emisor']) : 0;

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// Logo por emisor (si aplica)
$rutaLogo = '';
if ($emisor > 0) {
  $sqlLogo = "SELECT RutaLogo FROM {$prefijobd}Emisores WHERE ID = ".intval($emisor)." LIMIT 1";
  $resLogo = mysqli_query($cnx_cfdi2, $sqlLogo);
  if ($resLogo && mysqli_num_rows($resLogo) > 0) {
    $rl = mysqli_fetch_assoc($resLogo);
    $rutaLogo = $rl ? $rl['RutaLogo'] : '';
  }
}else{
  $rutaLogo = '../cfdipro/imagenes/'.$prefijo.'.jpg';
}
//die($rutaLogo);


// WHERE sucursal opcional
$whereSucursal = "";
if ($sucursal > 0) {
    $whereSucursal = " AND M.OficinaMant_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ";
}

// WHERE unidad opcional
$whereUnidad = "";
if ($unidadID > 0) {
    $whereUnidad = " AND M.UnidadMantenimiento_RID = ".$unidadID." ";
}

// ===== QUERY =====
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

// ===== PDF HTML (TU MÉTODO) =====
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body{ font-family: helvetica, sans-serif; font-size:8.5pt; }
    h1{ font-size:12pt; text-align:center; margin:0 0 4px 0; }
    .sub{ font-size:9pt; text-align:center; margin:0 0 8px 0; color:#333; }

    table{ width:100%; border-collapse:collapse; font-size:7.5pt; }
    th, td{ border:0.3px solid #444; padding:2px; }
    th{ background:#e0e0e0; font-weight:bold; text-align:center; }

    td.num{ text-align:right; }
    td.left{ text-align:left; }
    td.center{ text-align:center; }

    tr.parent td{ background:#f4f4f4; font-weight:bold; }
    tr.subhead td{ background:#d9ebff; font-weight:bold; }
    tr.child td{ background:#ffffff; }
  </style>
</head>
<body>
<table  border="0" style="margin:0; border-collapse: collapse; width: 100%;">
  <tr>
      <!-- LOGO IMG -->
      <td style="text-align:center; width:25%;">
          <img src="<?php echo $rutaLogo;?>" width="100px" alt=" "/>
      </td>
      <td>
        <h1>Órdenes de Servicio por Vehículo · Detalle</h1>
        <div class="sub">
          Periodo: <?php echo htmlspecialchars($fecha_inicio_f)." - ".htmlspecialchars($fecha_fin_f); ?>
          · Unidad: <?php echo ($unidadID>0 ? (int)$unidadID : 'TODAS'); ?>
          · Sucursal: <?php echo ($sucursal>0 ? (int)$sucursal : 'TODAS'); ?>
        </div>
      </td>
  </tr>
</table>

<table autosize="1" border="0">
  <thead>
    <tr>
      <th>Orden</th><th>Fecha</th><th>Vehículo</th><th>Km</th><th>Servicio</th><th>Taller</th>
      <th>Subtotal</th><th>IVA</th><th>Total</th>
      <th>Artículo</th><th>Cant</th><th>P.Unit</th><th>Importe</th><th>IVA</th><th>Total</th>
    </tr>
  </thead>
  <tbody>
  <?php
    $ultimoFolio = null;

    if ($res && mysqli_num_rows($res) > 0):
      while ($row = mysqli_fetch_assoc($res)):
        $folio = $row['XFolio'];
        $v_fecha = !empty($row['Fecha']) ? date("d-m-Y", strtotime($row['Fecha'])) : '';
        $isNew = ($ultimoFolio === null || $ultimoFolio !== $folio);
  ?>
    <?php if ($isNew): ?>
      <tr class="parent">
        <td class="center"><?php echo htmlspecialchars($folio); ?></td>
        <td class="center"><?php echo htmlspecialchars($v_fecha); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Unidad']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Kilometros']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Reparacion']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Taller']); ?></td>
        <td class="num">$<?php echo number_format((float)$row['Subtotal'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['Impuesto'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['Total'],2); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['Nombre']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Cantidad']); ?></td>
        <td class="num">$<?php echo number_format((float)$row['PrecioUnitario'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['Importe'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['ImporteIVA'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['ImporteTotal'],2); ?></td>
      </tr>
    <?php else: ?>
      <tr class="child">
        <td colspan="9"></td>
        <td class="left"><?php echo htmlspecialchars($row['Nombre']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['Cantidad']); ?></td>
        <td class="num">$<?php echo number_format((float)$row['PrecioUnitario'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['Importe'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['ImporteIVA'],2); ?></td>
        <td class="num">$<?php echo number_format((float)$row['ImporteTotal'],2); ?></td>
      </tr>
    <?php endif; ?>

  <?php
        $ultimoFolio = $folio;
      endwhile;
    else:
  ?>
    <tr><td colspan="15" class="center">Sin resultados con los filtros actuales.</td></tr>
  <?php endif; ?>
  </tbody>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/vendor/autoload.php';

// TU MISMO PATRÓN
$mpdf = new mPDF('utf-8','letter');
$mpdf->WriteHTML($html);

$nombre_pdf = "OrdServVehiculoDetalle_" . date("Ymd_His") . ".pdf";
$mpdf->Output($nombre_pdf, 'D');
exit;
