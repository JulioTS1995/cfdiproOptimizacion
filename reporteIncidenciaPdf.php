<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once('cnx_cfdi2.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// ===== PARAMS =====
$prefijobd = isset($_GET['prefijo']) ? $_GET['prefijo'] : '';
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

/* // WHERE proveedor
$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND c.ProveedorNo_RID = ".$id_proveedor_filtro." ";
}
 */
// WHERE search
$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = " AND (
    rem.Creado        LIKE '%$searchThermSafe%' OR
    un.Unidad         LIKE '%$searchThermSafe%' OR
    rem.XFolio        LIKE '%$searchThermSafe%' OR
    ru.Ruta           LIKE '%$searchThermSafe%' OR
    op.Operador       LIKE '%$searchThermSafe%' OR
    EXISTS (
        SELECT 1
        FROM {$prefijobd}remisiones_ref rmrf
        LEFT JOIN {$prefijobd}evidencias rmev ON rmrf.RID = rmev.ID
        WHERE rmrf.ID = rem.ID
          AND (
            rmev.Cantidad   LIKE '%$searchThermSafe%' OR
            rmev.Comentario LIKE '%$searchThermSafe%'
          )
    )
  ) ";
}

// ===== QUERY PADRES =====
$sql = "SELECT 
  rem.ID,
  rem.XFolio,
  rem.Creado,
  un.Unidad,
  op.Operador,
  ru.Ruta
FROM {$prefijobd}remisiones rem 
LEFT JOIN {$prefijobd}unidades un ON un.ID = rem.Unidad_RID
LEFT JOIN {$prefijobd}operadores op ON op.ID = rem.Operador_RID
LEFT JOIN {$prefijobd}rutas ru ON ru.ID = Ruta_RID
WHERE rem.Creado BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59' {$whereSearch} ORDER BY rem.Creado desc
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

<h1>Incidencias</h1>
<div class="sub">
  Periodo: <?php echo htmlspecialchars($fecha_inicio_f)." - ".htmlspecialchars($fecha_fin_f); ?>
  
  <?php if ($searchTherm !== ''): ?>
    · Búsqueda: "<?php echo htmlspecialchars($searchTherm); ?>"
  <?php endif; ?>
</div>

<table autosize="1">
  <thead>
    <tr>
      <th>Folio</th>
              <th>Creado</th>
              <th>Economico</th>
              <th>Operador</th>
              <th>Ruta</th>
    </tr>
  </thead>
  <tbody>

  <?php foreach ($rows as $row): ?>
    <?php
      $xfolio         = $row['XFolio'];
                $v_fecha_t      = $row['Creado'];
                $rem_id         = $row['ID'];
                $v_fecha        = date("d-m-Y", strtotime($v_fecha_t));
                $unidad         = $row['Unidad'];
                $operador       = $row['Operador'];
                $ruta           = $row['Ruta'];
    ?>

    <!-- PADRE -->
    <tr class="parent">
      <td class="center"><?php echo htmlspecialchars($xfolio); ?></td>
      <td class="center"><?php echo htmlspecialchars($v_fecha); ?></td>
      <td class="left"><?php echo htmlspecialchars($unidad); ?></td>
      <td class="center"><?php echo htmlspecialchars($operador); ?></td>
      <td class="center"><?php echo htmlspecialchars($ruta); ?></td>
      
    </tr>

    <?php
      // SUBPARTIDAS
               $sqlSub = "SELECT
                          rmev.Comentario,
                          rmev.Fecha,
                          rmev.Cantidad
                      FROM {$prefijobd}remisiones_ref as rmrf
                      LEFT JOIN {$prefijobd}evidencias rmev ON rmev.ID = rmrf.RID
                      WHERE rmrf.ID = ".intval($rem_id)."
                      ORDER BY rmev.ID ASC
                      ";
      $resSub = mysqli_query($cnx_cfdi2, $sqlSub);

      if ($resSub && mysqli_num_rows($resSub) > 0):
    ?>

      <tr class="subhead">
        <td colspan="9">↳ Detalle de Incidencias</td>
      </tr>

      <!-- mini header detalle -->
      <tr class="child">
        <td class="center">&nbsp;</td>
        <td class="left indent"><b>Fecha</b></td>
        <td class="left"><b>Cantiddad</b></td>
        <td class="left" colspan="2"><b>Comentario</b></td>
      </tr>

      <?php while ($rowSub = mysqli_fetch_assoc($resSub)): ?>
        <?php
              $comentario    = $rowSub['Comentario'];
              $fechaInc     = $rowSub['Fecha'];
              $cantidadSub       = $rowSub['Cantidad'];
        ?>
        <tr class="child">
          <td class="center">&nbsp;</td>
          <td class="left indent"><?php echo htmlspecialchars($fechaInc); ?></td>
          <td class="left"><?php echo htmlspecialchars($cantidadSub); ?></td>
          <td class="left" colspan="2"><?php echo htmlspecialchars($comentario); ?></td>
          
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

$nombre_pdf = "incidencias_" . date("Ymd_His") . ".pdf";
$mpdf->Output($nombre_pdf, 'D');
exit;
