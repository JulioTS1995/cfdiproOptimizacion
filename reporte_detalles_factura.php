<?php
set_time_limit(3000);
error_reporting(0);


header("Content-type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "reporte_detalle_factura_" . date("His") . "_" . date("d-m-Y") . ".xls";
header("Content-Disposition: attachment; filename=$nombre");
header("Pragma: no-cache");
header("Expires: 0");
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";


require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd = isset($_POST['prefijodb']) ? $_POST['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') { die("Falta prefijodb"); }
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

$fechai = isset($_POST['txtDesde']) ? $_POST['txtDesde'] : '';
$fechaf = isset($_POST['txtHasta']) ? $_POST['txtHasta'] : '';
if ($fechai === '' || $fechaf === '') { die("Faltan fechas"); }

$cliente_id  = isset($_POST['cliente']) ? intval($_POST['cliente']) : 0;
$ruta_id     = isset($_POST['ruta']) ? intval($_POST['ruta']) : 0;
$operador_id = isset($_POST['operador']) ? intval($_POST['operador']) : 0;
$unidad_id   = isset($_POST['unidad']) ? intval($_POST['unidad']) : 0;

$v_xfolio = isset($_POST['txtxfolio']) ? trim($_POST['txtxfolio']) : '';
$v_xfolio_safe = mysqli_real_escape_string($cnx_cfdi2, $v_xfolio);

$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '0';
$estatus = ($estatus === 'vigente' || $estatus === 'cancelado') ? $estatus : '0';


$where = " WHERE f.Creado BETWEEN '{$fechai} 00:00:00' AND '{$fechaf} 23:59:59' ";

if ($v_xfolio_safe !== '') {
  $where .= " AND f.XFolio = '{$v_xfolio_safe}' ";
}
if ($cliente_id > 0) {
  $where .= " AND f.CargoAFactura_RID = {$cliente_id} ";
}
if ($ruta_id > 0) {
  $where .= " AND f.Ruta_RID = {$ruta_id} ";
}
if ($operador_id > 0) {
  $where .= " AND f.Operador_RID = {$operador_id} ";
}
if ($unidad_id > 0) {
  $where .= " AND f.Unidad_RID = {$unidad_id} ";
}

if ($estatus === "vigente") {
  $where .= " AND f.cCanceladoT IS NULL ";
} elseif ($estatus === "cancelado") {
  $where .= " AND f.cCanceladoT IS NOT NULL ";
}


$sql = "SELECT
  f.Moneda,
  f.XFolio,
  f.cfdiuuid,
  f.Creado,
  f.Vence,
  CASE WHEN f.cCanceladoT IS NULL THEN 'Vigente' ELSE 'Cancelado' END AS estatusFactura,
  f.yFlete          AS flete_factura,
  f.zSubtotal       AS subtotal_factura,
  f.zImpuesto       AS iva_factura,
  f.zRetenido       AS retenido_factura,
  f.zTotal          AS total_factura,
  f.CobranzaAbonado AS cobranza_abonado_factura,
  f.CobranzaSaldo   AS cobranza_saldo_factura,
  IFNULL(c.RazonSocial,'') AS cliente_nombre,
  IFNULL(c.RFC,'')         AS cliente_rfc,
  IFNULL(op.Operador,'')   AS operador_nombre,
  IFNULL(u.Unidad,'')      AS unidad_nombre,
  IFNULL(r1.Unidad,'')     AS remolque_nombre,
  IFNULL(d1.Unidad,'')     AS dolly_nombre,
  IFNULL(r2.Unidad,'')     AS remolque2_nombre,
  p.ConceptoPartida   AS concepto_partida,
  p.Detalle           AS detalle_partida,
  p.Cantidad          AS cantidad_partida,
  p.PrecioUnitario    AS precio_unitario_partida,
  p.Subtotal          AS subtotal_partida,
  p.IVAImporte        AS iva_partida,
  p.RetencionImporte  AS retenido_partida,
  p.Importe           AS total_partida

FROM {$prefijobd}facturapartidas p
INNER JOIN {$prefijobd}factura f ON p.FolioSub_RID = f.ID
LEFT JOIN {$prefijobd}clientes c     ON c.ID  = f.CargoAFactura_RID
LEFT JOIN {$prefijobd}operadores op  ON op.ID = f.Operador_RID
LEFT JOIN {$prefijobd}unidades u     ON u.ID  = f.Unidad_RID
LEFT JOIN {$prefijobd}unidades r1    ON r1.ID = f.Remolque_RID
LEFT JOIN {$prefijobd}unidades d1    ON d1.ID = f.Dolly_RID
LEFT JOIN {$prefijobd}unidades r2    ON r2.ID = f.Remolque2_RID
{$where}
ORDER BY f.Creado ASC, f.XFolio ASC, p.ID ASC
";

$res = mysqli_query($cnx_cfdi2, $sql);
if (!$res) {
  echo "<h2>Error SQL</h2><pre>" . htmlspecialchars(mysqli_error($cnx_cfdi2)) . "</pre>";
  exit;
}
if (mysqli_num_rows($res) <= 0) {
  echo "<h2>No se encontraron registros</h2>";
  exit;
}

$fecha_inicio_f = date("d-m-Y", strtotime($fechai));
$fecha_fin_f    = date("d-m-Y", strtotime($fechaf));

function getNameById($cnx, $table, $id, $field, $prefijobd){
  if ($id <= 0) return '';
  $id = (int)$id;
  $q = "SELECT {$field} AS nombre FROM {$prefijobd}{$table} WHERE ID = {$id} LIMIT 1";
  $r = mysqli_query($cnx, $q);
  if ($r && ($row = mysqli_fetch_assoc($r))) return $row['nombre'];
  return '';
}

$cliente_nom  = getNameById($cnx_cfdi2, 'clientes',   $cliente_id,  'RazonSocial', $prefijobd);
$ruta_nom     = getNameById($cnx_cfdi2, 'rutas',      $ruta_id,     'Ruta',        $prefijobd);
$operador_nom = getNameById($cnx_cfdi2, 'operadores', $operador_id, 'Operador',    $prefijobd);
$unidad_nom   = getNameById($cnx_cfdi2, 'unidades',   $unidad_id,   'Unidad',      $prefijobd);

$estatus_txt = ($estatus === 'vigente') ? 'Vigente' : (($estatus === 'cancelado') ? 'Cancelado' : 'Todos');
?>
<style>
  
  body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color:#0b0c0f; }
  .wrap { width: 100%; }

  .title {
    font-size: 14pt; font-weight: 800;
    padding: 8px 10px; background: #f3f5f9;
    border: 1px solid #d8dde6;
  }
  .sub {
    font-size: 10pt; color:#4b5563;
    padding: 6px 10px; border: 1px solid #d8dde6; border-top: 0;
  }

  .chips { margin-top: 8px; margin-bottom: 10px; }
  .chip {
    display:inline-block;
    padding: 4px 10px;
    border: 1px solid #d8dde6;
    background:#ffffff;
    border-radius: 999px;
    margin-right: 6px;
    font-size: 9.5pt;
  }
  .chip b{ color:#111827; }

  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #cfd6e3; padding: 4px 6px; vertical-align: top; }
  th {
    background: #eaf1ff;
    font-weight: 800;
    text-align: center;
    color: #1f2937;
  }
  td.center{ text-align:center; }
  td.left{ text-align:left; }
  td.num{ text-align:right; }
  .muted{ color:#6b7280; }

  
  tr.sep td{
    background:#f9fafb;
    height: 6px;
    padding:0;
    border-left: 0; border-right: 0;
  }
</style>

<div class="wrap">
  <div class="title">Reporte Detalle Factura</div>
  <div class="sub">
    Periodo: <b><?php echo htmlspecialchars($fecha_inicio_f); ?></b> al <b><?php echo htmlspecialchars($fecha_fin_f); ?></b>
    </b>
  </div>

  <div class="chips">
    <span class="chip">Estatus: <b><?php echo htmlspecialchars($estatus_txt); ?></b></span>

    <?php if ($v_xfolio !== ''): ?>
      <span class="chip">XFolio: <b><?php echo htmlspecialchars($v_xfolio); ?></b></span>
    <?php endif; ?>

    <?php if ($cliente_id > 0): ?>
      <span class="chip">Cliente: <b><?php echo htmlspecialchars($cliente_nom); ?></b></span>
    <?php endif; ?>

    <?php if ($ruta_id > 0): ?>
      <span class="chip">Ruta: <b><?php echo htmlspecialchars($ruta_nom); ?></b></span>
    <?php endif; ?>

    <?php if ($operador_id > 0): ?>
      <span class="chip">Operador: <b><?php echo htmlspecialchars($operador_nom); ?></b></span>
    <?php endif; ?>

    <?php if ($unidad_id > 0): ?>
      <span class="chip">Unidad: <b><?php echo htmlspecialchars($unidad_nom); ?></b></span>
    <?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>Moneda</th>
        <th>XFolio</th>
        <th>Estatus</th>
        <th>Cliente</th>
        <th>Cliente RFC</th>
        <th>Operador</th>
        <th>Unidad</th>
        <th>Remolque</th>
        <th>Dolly</th>
        <th>Remolque2</th>
        <th>cfdiuuid</th>
        <th>Fecha Creado</th>
        <th>Flete</th>
        <th>Subtotal Factura</th>
        <th>Impuesto Factura</th>
        <th>Retenido Factura</th>
        <th>Total Factura</th>
        <th>Cobranza Abonado</th>
        <th>Cobranza Saldo</th>
        <th>Vence</th>
        <th>Concepto</th>
        <th>Detalle</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal Partida</th>
        <th>IVA Partida</th>
        <th>Retención Partida</th>
        <th>Total Partida</th>
      </tr>
    </thead>
    <tbody>
<?php
$last_folio = null;
while ($row = mysqli_fetch_assoc($res)) {
  $f_creado = ($row['Creado'] ? date("d-m-Y H:i:s", strtotime($row['Creado'])) : '');
  $f_vence  = ($row['Vence'] ? date("d-m-Y", strtotime($row['Vence'])) : '');

  
  if ($last_folio !== null && $last_folio !== $row['XFolio']) {
    echo '<tr class="sep"><td colspan="28"></td></tr>';
  }
  $last_folio = $row['XFolio'];
  ?>
      <tr>
        <td class="center"><?php echo htmlspecialchars($row['Moneda']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['XFolio']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['estatusFactura']); ?></td>

        <td class="left"><?php echo htmlspecialchars($row['cliente_nombre']); ?></td>
        <td class="center"><?php echo htmlspecialchars($row['cliente_rfc']); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['operador_nombre']); ?></td>

        <td class="left"><?php echo htmlspecialchars($row['unidad_nombre']); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['remolque_nombre']); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['dolly_nombre']); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['remolque2_nombre']); ?></td>

        <td class="left"><?php echo htmlspecialchars($row['cfdiuuid']); ?></td>
        <td class="center"><?php echo htmlspecialchars($f_creado); ?></td>

        <td class="num"><?php echo number_format((float)$row['flete_factura'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['subtotal_factura'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['iva_factura'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['retenido_factura'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['total_factura'], 2, '.', ''); ?></td>

        <td class="num"><?php echo number_format((float)$row['cobranza_abonado_factura'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['cobranza_saldo_factura'], 2, '.', ''); ?></td>

        <td class="center"><?php echo htmlspecialchars($f_vence); ?></td>

        <td class="left"><?php echo htmlspecialchars($row['concepto_partida']); ?></td>
        <td class="left"><?php echo htmlspecialchars($row['detalle_partida']); ?></td>

        <td class="center"><?php echo (int)$row['cantidad_partida']; ?></td>
        <td class="num"><?php echo number_format((float)$row['precio_unitario_partida'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['subtotal_partida'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['iva_partida'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['retenido_partida'], 2, '.', ''); ?></td>
        <td class="num"><?php echo number_format((float)$row['total_partida'], 2, '.', ''); ?></td>
      </tr>
  <?php
}
?>
    </tbody>
  </table>

  <div class="sub" style="margin-top:10px;">
    Exportado: <b><?php echo date("d-m-Y H:i:s"); ?></b>
    &nbsp;·&nbsp; Filas: <b><?php echo (int)mysqli_num_rows($res); ?></b>
  </div>
</div>
<?php
exit;
