<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi3.php');
if (!isset($cnx_cfdi3) || $cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// ===================== PARAMS =====================
$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') die("Falta prefijodb");
if (strpos($prefijobd, "_") === false) $prefijobd .= "_";
$prefijo = rtrim($prefijobd, "_");
$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;
$emisor   = isset($_GET['emisor'])   ? (int)$_GET['emisor']   : 0;

$fechaInicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fechaFin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';
if ($fechaInicio === '' || $fechaFin === '') die("Faltan fechas");

$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin_f    = date("d-m-Y", strtotime($fechaFin));

// búsqueda opcional (si luego la conectas desde display)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$qLike = '%' . $q . '%';


$MAX_ROWS_PDF = 8000;


$rutaLogo = '';
if ($emisor > 0) {
    $sqlEmisor = "SELECT RutaLogo FROM {$prefijobd}Emisores WHERE ID = ?";
    $stmtEmisor = $cnx_cfdi3->prepare($sqlEmisor);
    if (!$stmtEmisor) {
        die("Error emisor: " . $cnx_cfdi3->error);
    }
    $stmtEmisor->bind_param('i', $emisor);
    $stmtEmisor->execute();
    $stmtEmisor->bind_result($rutaLogo);
    $stmtEmisor->fetch();
    $stmtEmisor->close();
}else{
    $rutaLogo =  '../cfdipro/imagenes/'.$prefijo.'.jpg';
}


$hasSucursal = false;
try {
    $chk = $cnx_cfdi3->query("SHOW COLUMNS FROM {$prefijobd}Oficinas LIKE 'Sucursal_RID'");
    $hasSucursal = ($chk && $chk->num_rows > 0);
} catch (Exception $e) {
    $hasSucursal = false;
}

$extraSucursalSQL = "";
if ($sucursal > 0 && $hasSucursal) {
    $extraSucursalSQL = " AND Vs.OficinaVSalida_RID IN (
        SELECT ID FROM {$prefijobd}Oficinas WHERE Sucursal_RID = ?
    ) ";
}


$extraSearchSQL = "";
if ($q !== '') {
    $extraSearchSQL = " AND (
        Vs.XFolio LIKE ?
        OR U.Unidad LIKE ?
        OR Prd.Codigo LIKE ?
        OR Prd.Nombre LIKE ?
        OR VsS.Descripcion LIKE ?
    ) ";
}


$sql = "
SELECT
    Vs.XFolio,
    Vs.Fecha,
    IFNULL(U.Unidad,'') AS Unidad,
    IFNULL(Prd.Codigo,'') AS Codigo,
    IFNULL(Prd.Nombre,'') AS Nombre,
    IFNULL(VsS.Descripcion,'') AS Descripcion,
    IFNULL(VsS.Cantidad,0) AS Cantidad,
    IFNULL(VsS.PrecioUnitario,0) AS PrecioUnitario,
    IFNULL(VsS.Importe,0) AS Importe
FROM {$prefijobd}valessalida AS Vs
LEFT JOIN {$prefijobd}unidades AS U ON Vs.Unidad_RID = U.ID
LEFT JOIN {$prefijobd}valessalidasub AS VsS ON Vs.ID = VsS.FolioSub_RID
LEFT JOIN {$prefijobd}productos AS Prd ON Prd.ID = VsS.ProductoV_RID
WHERE DATE(Vs.Fecha) BETWEEN ? AND ?
{$extraSucursalSQL}
{$extraSearchSQL}
ORDER BY Vs.XFolio ASC, VsS.ID ASC
LIMIT {$MAX_ROWS_PDF}
";

$stmt = $cnx_cfdi3->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $cnx_cfdi3->error);
}


$params = [];
$types  = "ss";
$params[] = $fechaInicio;
$params[] = $fechaFin;

if ($sucursal > 0 && $hasSucursal) {
    $types .= "i";
    $params[] = $sucursal;
}

if ($q !== '') {
    $types .= "sssss";
    $params[] = $qLike;
    $params[] = $qLike;
    $params[] = $qLike;
    $params[] = $qLike;
    $params[] = $qLike;
}

$bind_names = [];
$bind_names[] = $types;
for ($i=0; $i<count($params); $i++) {
    $bind_name = 'b' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

$stmt->execute();


$res = $stmt->get_result();
if (!$res) {
    die("Tu PHP no soporta get_result() (falta mysqlnd). Dime y te lo convierto a bind_result 100%.");
}

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

$stmt->close();
$cnx_cfdi3->close();

$truncado = (count($rows) >= $MAX_ROWS_PDF);


ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body{ font-family: helvetica, sans-serif; font-size:8.5pt; color:#111; }
    table{ width:100%; border-collapse:collapse; font-size:7.5pt; }
    th, td{ border:0.3px solid #444; padding:2px; }
    th{ background:#e0e0e0; font-weight:bold; text-align:center; }
    td.num{ text-align:right; }
    td.left{ text-align:left; }
    td.center{ text-align:center; }
    h1{ font-size:12pt; text-align:center; margin:0 0 4px 0; }
    .sub{ font-size:9pt; text-align:center; margin:0 0 10px 0; color:#333; }
    .note{
      margin: 6px 0 10px 0;
      padding: 6px 8px;
      border: 0.6px solid #999;
      background: #fff8d6;
      font-size: 8pt;
    }
    .logo{ width:60px; height:60px; }
  </style>
</head>
<body>

<table style="width:100%; margin-bottom:6px;">
  <tr>
    <td style="width:80px; text-align:left;">
      <?php if (!empty($rutaLogo)): ?>
        <img class="logo" src="<?php echo htmlspecialchars($rutaLogo); ?>" alt="Logo">
      <?php endif; ?>
    </td>
    <td>
      <h1>Vales de Salida · Detalle</h1>
      <div class="sub">
        Periodo: <?php echo htmlspecialchars($fechaInicio_f)." - ".htmlspecialchars($fechaFin_f); ?>
        <?php if ($sucursal > 0): ?>
          · Sucursal: <?php echo (int)$sucursal; ?>
          <?php if (!$hasSucursal): ?> (sin filtro: no existe Sucursal_RID)<?php endif; ?>
        <?php endif; ?>
        <?php if ($q !== ''): ?>
          · Búsqueda: "<?php echo htmlspecialchars($q); ?>"
        <?php endif; ?>
      </div>
    </td>
  </tr>
</table>

<?php if ($truncado): ?>
  <div class="note">
    Se limitó el PDF a <?php echo (int)$MAX_ROWS_PDF; ?> renglones para evitar que no se imprima por tamaño.
    Si quieres todo, lo correcto es hacerlo por rangos (día/folio) o mandarlo a Excel.
  </div>
<?php endif; ?>

<table autosize="1">
  <thead>
    <tr>
      <th>Folio</th>
      <th>Fecha</th>
      <th>Unidad</th>
      <th>Código</th>
      <th>Nombre</th>
      <th>Descripción</th>
      <th>Cantidad</th>
      <th>Precio</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
  <?php
    $sumCantidad = 0;
    $sumPrecioU  = 0;
    $sumTotal    = 0;

    foreach ($rows as $r):
      $folio = $r['XFolio'];
      $fecha = $r['Fecha'] ? date("d-m-Y", strtotime($r['Fecha'])) : '';
      $unidad = $r['Unidad'];
      $codigo = $r['Codigo'];
      $nombre = $r['Nombre'];
      $descripcion = $r['Descripcion'];
      $cantidad = (float)$r['Cantidad'];
      $precioU  = (float)$r['PrecioUnitario'];
      $importe  = (float)$r['Importe'];

      $sumCantidad += $cantidad;
      $sumPrecioU  += $precioU;
      $sumTotal    += $importe;
  ?>
    <tr>
      <td class="left"><?php echo htmlspecialchars($folio); ?></td>
      <td class="center"><?php echo htmlspecialchars($fecha); ?></td>
      <td class="left"><?php echo htmlspecialchars($unidad); ?></td>
      <td class="left"><?php echo htmlspecialchars($codigo); ?></td>
      <td class="left"><?php echo htmlspecialchars($nombre); ?></td>
      <td class="left"><?php echo htmlspecialchars($descripcion); ?></td>
      <td class="num"><?php echo number_format($cantidad, 2); ?></td>
      <td class="num"><?php echo "$".number_format($precioU, 2); ?></td>
      <td class="num"><?php echo "$".number_format($importe, 2); ?></td>
    </tr>
  <?php endforeach; ?>

    <tr>
      <td class="center"><b>TOTAL:</b></td>
      <td></td><td></td><td></td><td></td><td></td>
      <td class="num"><b><?php echo number_format($sumCantidad, 2); ?></b></td>
      <td class="num"><b><?php echo "$".number_format($sumPrecioU, 2); ?></b></td>
      <td class="num"><b><?php echo "$".number_format($sumTotal, 2); ?></b></td>
    </tr>
  </tbody>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

require_once('lib_mpdf/pdf/mpdf.php');

$mpdf = new mPDF('c', 'A4');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
$mpdf->defaultfooterline = 0;

$cssPath = __DIR__ . '/css/style_pdf.css';
if (is_file($cssPath)) {
    $css = file_get_contents($cssPath);
    if ($css) $mpdf->writeHTML($css, 1);
}

$mpdf->writeHTML($html);

$nombre_pdf = 'Vale_Salida_detalle_' . date("d-m-Y") . "_" . date("Hi") . '.pdf';
$mpdf->Output($nombre_pdf, 'D');
exit;
