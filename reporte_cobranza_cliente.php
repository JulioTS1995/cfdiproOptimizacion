<?php
ini_set('memory_limit', '2048M');
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi3.php');
if (!isset($cnx_cfdi3) || $cnx_cfdi3->connect_error) {
  die("Error de conexión a la base de datos.");
}
mysqli_select_db($cnx_cfdi3, $database_cfdi);
mysqli_set_charset($cnx_cfdi3, "utf8");

// -------------------------
// 1) Recibir POST
// -------------------------
$prefijobd  = isset($_POST['prefijodb']) ? $_POST['prefijodb'] : '';
$fechai     = isset($_POST['txtDesde']) ? $_POST['txtDesde'] : '';
$fechaf     = isset($_POST['txtHasta']) ? $_POST['txtHasta'] : '';
$cliente_id = isset($_POST['cliente'])  ? (int)$_POST['cliente'] : 0;
$moneda     = isset($_POST['moneda'])   ? trim($_POST['moneda']) : 'PESOS';
$boton      = isset($_POST['btnEnviar'])? trim($_POST['btnEnviar']) : '';
$sucursal   = isset($_POST['sucursal']) ? (int)$_POST['sucursal'] : 0;

if ($prefijobd === '' || $fechai === '' || $fechaf === '' || $boton === '') {
  die("Faltan parámetros.");
}

// Sanitizar prefijo
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if (strpos($prefijobd, '_') === false) { $prefijobd .= '_'; }
if (substr($prefijobd, -1) !== '_') { $prefijobd .= '_'; }

// Moneda
$moneda = strtoupper($moneda);
if ($moneda !== 'PESOS' && $moneda !== 'DOLARES') { $moneda = 'PESOS'; }

// Fechas formato YYYY-MM-DD
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaf)) {
  die("Fechas inválidas.");
}

// Helper HTML
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return '$' . number_format((float)$n, 2); }

// -------------------------
// 2) Detectar columna sucursal (tolerante)
// -------------------------
$tablaClientes = $prefijobd . "clientes";
$tieneSucursal = false;
$chkCol = mysqli_query($cnx_cfdi3, "SHOW COLUMNS FROM {$tablaClientes} LIKE 'Sucursal_RID'");
if ($chkCol && mysqli_num_rows($chkCol) > 0) { $tieneSucursal = true; }

// -------------------------
// 3) Encabezado empresa (systemsettings) - tolerante
// -------------------------
$RazonSocial = 'TractoSoft';
$RFC = ''; $Calle = ''; $NumeroExterior = ''; $Colonia = ''; $Municipio = ''; $Estado = '';
//Ajuste portal centros D
$esPortal = '0';



$tablaSys = $prefijobd . "systemsettings";
$chkSys = mysqli_query($cnx_cfdi3, "SHOW TABLES LIKE '{$tablaSys}'");
if ($chkSys && mysqli_num_rows($chkSys) > 0) {
  $rsSys = mysqli_query($cnx_cfdi3, "SELECT * FROM {$tablaSys} LIMIT 1");
  if ($rsSys && ($row = mysqli_fetch_assoc($rsSys))) {
    if (isset($row['RazonSocial'])) $RazonSocial = $row['RazonSocial'];
    if (isset($row['RFC'])) $RFC = $row['RFC'];
    if (isset($row['Calle'])) $Calle = $row['Calle'];
    if (isset($row['NumeroExterior'])) $NumeroExterior = $row['NumeroExterior'];
    if (isset($row['Colonia'])) $Colonia = $row['Colonia'];
    if (isset($row['Municipio'])) $Municipio = $row['Municipio'];
    if (isset($row['Estado'])) $Estado = $row['Estado'];
    if (isset($row['factura_portal'])) $esPortal = $row['factura_portal'];
  }
}
$facturaPortal = ($esPortal == '1')  ? true : false; 
$ctnPortal = '';
$ctnPortal = ($facturaPortal) ? ' AND  EnPortal = "1"' : '' ;
$ctnPortalTotal = ($facturaPortal) ? ' AND  f.EnPortal = "1"' : '' ;


// -------------------------
// 4) Query única (sin get_result)
// -------------------------
$tablaAbonosSub = $prefijobd . "abonossub";
$tablaFactura   = $prefijobd . "factura";
$tablaAbonos    = $prefijobd . "abonos";

$sql = "
SELECT
  DATE(ab.FechaAplicacion) AS FechaPago,
  f.XFolio AS Factura,
  a.XFolio AS Abono,
  c.ID AS ClienteID,
  c.RazonSocial AS Cliente,
  ab.SubTotal,
  ab.Impuesto,
  ab.Retenido,
  ab.Importe,
  a.TipoCambio
FROM {$tablaAbonosSub} ab
INNER JOIN {$tablaFactura} f ON ab.AbonoFactura_RID = f.ID
INNER JOIN {$tablaAbonos}  a ON ab.FolioSub_RID = a.ID
INNER JOIN {$tablaClientes} c ON f.CargoAFactura_RID = c.ID
WHERE DATE(ab.FechaAplicacion) BETWEEN ? AND ?
  AND a.Moneda = ? {$ctnPortalTotal}
";

$types = "sss";
$params = array($fechai, $fechaf, $moneda);

if ($cliente_id > 0) {
  $sql .= " AND f.CargoAFactura_RID = ? ";
  $types .= "i";
  $params[] = $cliente_id;
}

if ($tieneSucursal && $sucursal > 0) {
  $sql .= " AND c.Sucursal_RID = ? ";
  $types .= "i";
  $params[] = $sucursal;
}

$sql .= " ORDER BY c.RazonSocial, FechaPago, Factura, Abono ";

$stmt = mysqli_prepare($cnx_cfdi3, $sql);
if (!$stmt) { die("Error en la consulta."); }


$bind = array();
$bind[] = $types;
for ($i=0; $i<count($params); $i++) { $bind[] = &$params[$i]; }
call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt), $bind));

if (!mysqli_stmt_execute($stmt)) {
  die("Error al ejecutar la consulta.");
}

mysqli_stmt_bind_result(
  $stmt,
  $FechaPago,
  $Factura,
  $Abono,
  $ClienteID,
  $Cliente,
  $SubTotal,
  $Impuesto,
  $Retenido,
  $Importe,
  $TipoCambio
);

// Agrupar por cliente
$grupos = array(); // [ClienteID => ['nombre'=>..., 'rows'=>[], 'tot'=>...]]
while (mysqli_stmt_fetch($stmt)) {
  $cid = (int)$ClienteID;

  if (!isset($grupos[$cid])) {
    $grupos[$cid] = array(
      'nombre' => (string)$Cliente,
      'rows' => array(),
      'tot' => array(
        'Subtotal'=>0, 'IVA'=>0, 'Ret'=>0, 'Neto'=>0,
        'SubtotalMXN'=>0, 'IVAMXN'=>0, 'RetMXN'=>0, 'NetoMXN'=>0
      )
    );
  }

  $sub = (float)$SubTotal;
  $iva = (float)$Impuesto;
  $ret = (float)$Retenido;
  $net = (float)$Importe;
  $tc  = (float)$TipoCambio;

  $grupos[$cid]['rows'][] = array(
    'FechaPago' => $FechaPago,
    'Factura'   => $Factura,
    'Abono'     => $Abono,
    'TipoCambio'=> $tc,
    'SubTotal'  => $sub,
    'IVA'       => $iva,
    'Ret'       => $ret,
    'Neto'      => $net
  );

  $grupos[$cid]['tot']['Subtotal'] += $sub;
  $grupos[$cid]['tot']['IVA']      += $iva;
  $grupos[$cid]['tot']['Ret']      += $ret;
  $grupos[$cid]['tot']['Neto']     += $net;

  if ($moneda === 'DOLARES') {
    $grupos[$cid]['tot']['SubtotalMXN'] += $tc * $sub;
    $grupos[$cid]['tot']['IVAMXN']      += $tc * $iva;
    $grupos[$cid]['tot']['RetMXN']      += $tc * $ret;
    $grupos[$cid]['tot']['NetoMXN']     += $tc * $net;
  }
}

mysqli_stmt_close($stmt);
mysqli_close($cnx_cfdi3);

// -------------------------
// 5) EXCEL
// -------------------------
if ($boton === 'Excel') {
  header("Content-type: application/vnd.ms-excel");
  $nombre = "Cobranza_Por_Cliente_" . date("Ymd_His") . ".xls";
  header("Content-Disposition: attachment; filename=$nombre");
  ?>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <table border="1" cellspacing="0" cellpadding="3">
    <thead>
      <tr>
        <th colspan="<?php echo ($moneda==='DOLARES' ? 13 : 8); ?>" style="font-size:13px; text-align:center;">
          <?php echo h($RazonSocial); ?>
        </th>
      </tr>
      <tr>
        <th colspan="<?php echo ($moneda==='DOLARES' ? 13 : 8); ?>" style="font-size:12px; text-align:center;">
          Cobranza Por Cliente <?php echo h($moneda); ?> · Del <?php echo h($fechai); ?> al <?php echo h($fechaf); ?>
          <?php if ($sucursal > 0 && $tieneSucursal): ?> · Sucursal <?php echo (int)$sucursal; ?><?php endif; ?>
        </th>
      </tr>

      <?php if ($moneda === 'DOLARES'): ?>
        <tr>
          <th colspan="5"></th>
          <th colspan="4">DOLARES</th>
          <th colspan="4">PESOS</th>
        </tr>
        <tr>
          <th>Fecha Pago</th><th>Factura</th><th>Rep</th><th>Cliente</th><th>Tipo Cambio</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
        </tr>
      <?php else: ?>
        <tr>
          <th>Fecha Pago</th><th>Factura</th><th>Rep</th><th>Cliente</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
        </tr>
      <?php endif; ?>
    </thead>
    <tbody>
    <?php foreach ($grupos as $g): ?>
      <?php foreach ($g['rows'] as $row): ?>
        <?php if ($moneda === 'DOLARES'): ?>
          <?php
            $tc = (float)$row['TipoCambio'];
            $sub = (float)$row['SubTotal'];
            $iva = (float)$row['IVA'];
            $ret = (float)$row['Ret'];
            $net = (float)$row['Neto'];
          ?>
          <tr>
            <td><?php echo h($row['FechaPago']); ?></td>
            <td><?php echo h($row['Factura']); ?></td>
            <td><?php echo h($row['Abono']); ?></td>
            <td><?php echo h($g['nombre']); ?></td>
            <td><?php echo ($tc > 0 ? number_format($tc,2) : ''); ?></td>

            <td><?php echo money($sub); ?></td>
            <td><?php echo money($iva); ?></td>
            <td><?php echo money($ret); ?></td>
            <td><?php echo money($net); ?></td>

            <td><?php echo money($tc*$sub); ?></td>
            <td><?php echo money($tc*$iva); ?></td>
            <td><?php echo money($tc*$ret); ?></td>
            <td><?php echo money($tc*$net); ?></td>
          </tr>
        <?php else: ?>
          <tr>
            <td><?php echo h($row['FechaPago']); ?></td>
            <td><?php echo h($row['Factura']); ?></td>
            <td><?php echo h($row['Abono']); ?></td>
            <td><?php echo h($g['nombre']); ?></td>
            <td style="text-align:right;"><?php echo money($row['SubTotal']); ?></td>
            <td style="text-align:right;"><?php echo money($row['IVA']); ?></td>
            <td style="text-align:right;"><?php echo money($row['Ret']); ?></td>
            <td style="text-align:right;"><?php echo money($row['Neto']); ?></td>
          </tr>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if ($moneda === 'DOLARES'): ?>
        <tr style="background:#f2f2f2; font-weight:bold;">
          <td colspan="5" style="text-align:right;">SUMAS</td>
          <td style="text-align:right;"><?php echo money($g['tot']['Subtotal']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['IVA']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['Ret']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['Neto']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['SubtotalMXN']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['IVAMXN']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['RetMXN']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['NetoMXN']); ?></td>
        </tr>
      <?php else: ?>
        <tr style="background:#f2f2f2; font-weight:bold;">
          <td colspan="4" style="text-align:right;">SUMAS</td>
          <td style="text-align:right;"><?php echo money($g['tot']['Subtotal']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['IVA']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['Ret']); ?></td>
          <td style="text-align:right;"><?php echo money($g['tot']['Neto']); ?></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php
  exit;
}

// -------------------------
// 6) PDF (mPDF 6.1 legacy)
// -------------------------
if ($boton === 'PDF') {
  require_once('lib_mpdf/pdf/mpdf.php'); // mPDF 6.1

  // letter + utf-8 (como tu estándar viejo)
  $mpdf = new mPDF('utf-8', 'letter');
  $mpdf->setFooter(' {DATE d-m-Y} / Tractosoft / Hoja {PAGENO}');
  $mpdf->defaultfooterline = 0;

  ob_start();
  ?>
  <!doctype html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <style>
      body{ font-family: helvetica, sans-serif; font-size: 9pt; color:#0b0d12; }
      .head{
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        margin-bottom: 10px;
      }
      .rs{ font-size: 12pt; font-weight: bold; }
      .meta{ margin-top: 4px; color:#444; font-size: 9pt; }
      .addr{ margin-top: 6px; color:#333; font-size: 8.5pt; line-height: 1.35; }
      table{ width:100%; border-collapse: collapse; }
      th, td{ border: 0.3px solid #666; padding: 4px 5px; }
      th{
        background:#e9eef9;
        font-weight: bold;
        font-size: 8.5pt;
        text-align: center;
      }
      td{ font-size: 8.5pt; }
      .num{ text-align:right; white-space:nowrap; }
      .tot{ background:#f2f2f2; font-weight:bold; }
      .sp{ height:8px; border:none; }
    </style>
  </head>
  <body>

  <div class="head">
    <div class="rs"><?php echo h($RazonSocial); ?></div>
    <div class="meta">
      Cobranza Por Cliente <?php echo h($moneda); ?> · Del <?php echo h($fechai); ?> al <?php echo h($fechaf); ?>
      <?php if ($sucursal > 0 && $tieneSucursal): ?> · Sucursal <?php echo (int)$sucursal; ?><?php endif; ?>
    </div>
    <?php if (trim($Calle.$NumeroExterior.$Colonia.$Municipio.$Estado.$RFC) !== ''): ?>
      <div class="addr">
        <?php echo h(trim($Calle.' '.$NumeroExterior)); ?>
        <?php if ($Colonia !== ''): ?>, <?php echo h($Colonia); ?><?php endif; ?>
        <?php if ($Municipio !== '' || $Estado !== ''): ?><br><?php echo h(trim($Municipio.' '.$Estado)); ?><?php endif; ?>
        <?php if ($RFC !== ''): ?><br><?php echo h($RFC); ?><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($moneda === 'DOLARES'): ?>
    <table>
      <thead>
        <tr>
          <th colspan="5"></th>
          <th colspan="4">DOLARES</th>
          <th colspan="4">PESOS</th>
        </tr>
        <tr>
          <th>Fecha Pago</th><th>Factura</th><th>Rep</th><th>Cliente</th><th>Tipo Cambio</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($grupos as $g): ?>
        <?php foreach ($g['rows'] as $row): ?>
          <?php
            $tc = (float)$row['TipoCambio'];
            $sub = (float)$row['SubTotal'];
            $iva = (float)$row['IVA'];
            $ret = (float)$row['Ret'];
            $net = (float)$row['Neto'];
          ?>
          <tr>
            <td><?php echo h($row['FechaPago']); ?></td>
            <td><?php echo h($row['Factura']); ?></td>
            <td><?php echo h($row['Abono']); ?></td>
            <td><?php echo h($g['nombre']); ?></td>
            <td class="num"><?php echo ($tc > 0 ? number_format($tc,2) : ''); ?></td>

            <td class="num"><?php echo money($sub); ?></td>
            <td class="num"><?php echo money($iva); ?></td>
            <td class="num"><?php echo money($ret); ?></td>
            <td class="num"><?php echo money($net); ?></td>

            <td class="num"><?php echo money($tc*$sub); ?></td>
            <td class="num"><?php echo money($tc*$iva); ?></td>
            <td class="num"><?php echo money($tc*$ret); ?></td>
            <td class="num"><?php echo money($tc*$net); ?></td>
          </tr>
        <?php endforeach; ?>

        <tr class="tot">
          <td colspan="5" class="num">SUMAS</td>
          <td class="num"><?php echo money($g['tot']['Subtotal']); ?></td>
          <td class="num"><?php echo money($g['tot']['IVA']); ?></td>
          <td class="num"><?php echo money($g['tot']['Ret']); ?></td>
          <td class="num"><?php echo money($g['tot']['Neto']); ?></td>
          <td class="num"><?php echo money($g['tot']['SubtotalMXN']); ?></td>
          <td class="num"><?php echo money($g['tot']['IVAMXN']); ?></td>
          <td class="num"><?php echo money($g['tot']['RetMXN']); ?></td>
          <td class="num"><?php echo money($g['tot']['NetoMXN']); ?></td>
        </tr>

        <tr><td colspan="13" class="sp"></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>

  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Fecha Pago</th><th>Factura</th><th>Rep</th><th>Cliente</th>
          <th>Subtotal</th><th>IVA</th><th>IVA Ret</th><th>Neto</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($grupos as $g): ?>
        <?php foreach ($g['rows'] as $row): ?>
          <tr>
            <td><?php echo h($row['FechaPago']); ?></td>
            <td><?php echo h($row['Factura']); ?></td>
            <td><?php echo h($row['Abono']); ?></td>
            <td><?php echo h($g['nombre']); ?></td>
            <td class="num"><?php echo money($row['SubTotal']); ?></td>
            <td class="num"><?php echo money($row['IVA']); ?></td>
            <td class="num"><?php echo money($row['Ret']); ?></td>
            <td class="num"><?php echo money($row['Neto']); ?></td>
          </tr>
        <?php endforeach; ?>

        <tr class="tot">
          <td colspan="4" class="num">SUMAS</td>
          <td class="num"><?php echo money($g['tot']['Subtotal']); ?></td>
          <td class="num"><?php echo money($g['tot']['IVA']); ?></td>
          <td class="num"><?php echo money($g['tot']['Ret']); ?></td>
          <td class="num"><?php echo money($g['tot']['Neto']); ?></td>
        </tr>

        <tr><td colspan="8" class="sp"></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  </body>
  </html>
  <?php
  $html = ob_get_clean();

 

  $mpdf->WriteHTML($html);
  $mpdf->Output("Cobranza_Por_Cliente_" . date("Ymd_His") . ".pdf", "D");
  exit;
}

die("Acción no reconocida.");
