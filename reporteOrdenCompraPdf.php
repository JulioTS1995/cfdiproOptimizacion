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

$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

$id_proveedor_filtro = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;

$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTherm);

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// WHERE proveedor
$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND c.ProveedorNo_RID = ".$id_proveedor_filtro." ";
}

// WHERE search
$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = "AND (
    c.Estatus       LIKE '%$searchThermSafe%' OR
    c.CompraAbierta  LIKE '%$searchThermSafe%' OR
    c.XFolio        LIKE '%$searchThermSafe%' OR
    c.Documentador  LIKE '%$searchThermSafe%' OR
    p.RazonSocial   LIKE '%$searchThermSafe%' OR
     
     EXISTS (
        SELECT 1
        FROM {$prefijobd}comprassub cs
        LEFT JOIN {$prefijobd}productos p ON cs.ProductoA_RID = p.ID
        WHERE cs.FolioSub_RID = c.ID
          AND (
            cs.Nombre LIKE '%$searchThermSafe%' OR
            IFNULL(p.Codigo,'') LIKE '%%$searchThermSafe'
            )
        )
     )";
}

// ===== QUERY PADRES =====
$sql = "
SELECT
  c.ID,
  c.XFolio,
  c.Fecha,
  c.Estatus,
  c.CompraAbierta,
  c.Total,
  c.Comentarios,
  c.FolioRecepcion,
  c.Documentador,
  p.RazonSocial
FROM ".$prefijobd."Compras c
LEFT JOIN ".$prefijobd."Proveedores p ON p.ID = c.ProveedorNo_RID
WHERE c.Fecha BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery.$whereSearch."
ORDER BY c.Fecha DESC, p.RazonSocial DESC
";
$res = mysqli_query($cnx_cfdi2, $sql);
if (!$res) {
    die("Error SQL principal: ".mysqli_error($cnx_cfdi2));
}

$rows = array();
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

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

    /* jerarquía */
    tr.parent td{ background:#f4f4f4; font-weight:bold; }
    tr.subhead td{ background:#d9ebff; font-weight:bold; }
    tr.child td{ background:#ffffff; }
    .indent{ padding-left:10px; }
  </style>
</head>
<body>

<h1>Órdenes de compra</h1>
<div class="sub">
  Periodo: <?php echo htmlspecialchars($fecha_inicio_f)." - ".htmlspecialchars($fecha_fin_f); ?>
  <?php if ($id_proveedor_filtro > 0): ?>
    · Proveedor ID: <?php echo (int)$id_proveedor_filtro; ?>
  <?php endif; ?>
  <?php if ($searchTherm !== ''): ?>
    · Búsqueda: "<?php echo htmlspecialchars($searchTherm); ?>"
  <?php endif; ?>
</div>

<table autosize="1">
  <thead>
    <tr>
      <th>Folio</th>
      <th>Fecha</th>
      <th>Proveedor</th>
      <th>Estatus</th>
      <th>Compra</th>
      <th>Total</th>
      <th>Comentarios</th>
      <th>Folio Recepción</th>
      <th>Documentador</th>
    </tr>
  </thead>
  <tbody>

  <?php foreach ($rows as $row): ?>
    <?php
      $id_compra = (int)$row['ID'];
      $xfolio = $row['XFolio'];
      $v_fecha = date("d-m-Y", strtotime($row['Fecha']));
      $proveedor = $row['RazonSocial'];
      $estatus = $row['Estatus'];
      $compraAbierta = $row['CompraAbierta'];
      $total = $row['Total'];
      $comentarios = $row['Comentarios'];
      $folioRecepcion = $row['FolioRecepcion'];
      $documentador = $row['Documentador'];
    ?>

    <!-- PADRE -->
    <tr class="parent">
      <td class="center"><?php echo htmlspecialchars($xfolio); ?></td>
      <td class="center"><?php echo htmlspecialchars($v_fecha); ?></td>
      <td class="left"><?php echo htmlspecialchars($proveedor); ?></td>
      <td class="center"><?php echo htmlspecialchars($estatus); ?></td>
      <td class="center"><?php echo htmlspecialchars($compraAbierta); ?></td>
      <td class="num"><?php echo htmlspecialchars($total); ?></td>
      <td class="left"><?php echo htmlspecialchars($comentarios); ?></td>
      <td class="center"><?php echo htmlspecialchars($folioRecepcion); ?></td>
      <td class="center"><?php echo htmlspecialchars($documentador); ?></td>
    </tr>

    <?php
      // SUBPARTIDAS
      $sqlSub = "
        SELECT
          cs.Nombre AS Descripcion,
          cs.Cantidad,
          cs.Descuento,
          cs.PrecioUnitario,
          cs.Importe,
          IFNULL(p.Codigo,'') AS ProductoCodigo,
          IFNULL(a.Nombre,'') AS AlmacenNombre
        FROM {$prefijobd}ComprasSub cs
        LEFT JOIN {$prefijobd}Productos p ON p.ID = cs.ProductoA_RID
        LEFT JOIN {$prefijobd}Almacenes a ON a.ID = cs.Almacen_RID
        WHERE cs.FolioSub_RID = {$id_compra}
        ORDER BY cs.ID ASC
      ";
      $resSub = mysqli_query($cnx_cfdi2, $sqlSub);

      if ($resSub && mysqli_num_rows($resSub) > 0):
    ?>

      <tr class="subhead">
        <td colspan="9">↳ Detalle de compra</td>
      </tr>

      <!-- mini header detalle -->
      <tr class="child">
        <td class="center">&nbsp;</td>
        <td class="left indent"><b>Código</b></td>
        <td class="left"><b>Almacén</b></td>
        <td class="left" colspan="3"><b>Descripción</b></td>
        <td class="num"><b>Cant</b></td>
        <td class="num"><b>P.Unit</b></td>
        <td class="num"><b>Importe</b></td>
      </tr>

      <?php while ($s = mysqli_fetch_assoc($resSub)): ?>
        <?php
          $descTxt = '';
          if ((float)$s['Descuento'] > 0) {
              $descTxt = ' (Desc: '.$s['Descuento'].')';
          }
        ?>
        <tr class="child">
          <td class="center">&nbsp;</td>
          <td class="left indent"><?php echo htmlspecialchars($s['ProductoCodigo']); ?></td>
          <td class="left"><?php echo htmlspecialchars($s['AlmacenNombre']); ?></td>
          <td class="left" colspan="3"><?php echo htmlspecialchars($s['Descripcion']).htmlspecialchars($descTxt); ?></td>
          <td class="num"><?php echo htmlspecialchars($s['Cantidad']); ?></td>
          <td class="num"><?php echo htmlspecialchars($s['PrecioUnitario']); ?></td>
          <td class="num"><?php echo htmlspecialchars($s['Importe']); ?></td>
        </tr>
      <?php endwhile; ?>

    <?php endif; ?>

  <?php endforeach; ?>

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

$nombre_pdf = "Ordenes_de_Compra_" . date("Ymd_His") . ".pdf";
$mpdf->Output($nombre_pdf, 'D');
exit;
