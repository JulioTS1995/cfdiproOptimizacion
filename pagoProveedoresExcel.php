<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// =====================
// PARAMS (GET)
// =====================
$prefijobd = '';
if (isset($_GET['prefijodb']) && $_GET['prefijodb'] != '') {
    $prefijobd = $_GET['prefijodb'];
}
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);

$id_proveedor_filtro = 0;
if (isset($_GET['proveedor'])) {
    $id_proveedor_filtro = intval($_GET['proveedor']);
}

$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

$moneda = 'AMBOS';
if (isset($_GET['moneda']) && $_GET['moneda'] !== '') {
    $moneda = $_GET['moneda'];
}
$moneda = in_array($moneda, ['PESOS','DOLARES','AMBOS']) ? $moneda : 'AMBOS';

$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTherm);

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

// =====================
// WHERE proveedor
// =====================
$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND pg.Proveedor_RID = ".$id_proveedor_filtro." ";
}

// =====================
// WHERE moneda (por TipoCambio max en PagosSub)
// =====================
$whereMoneda = "";
if ($moneda === 'PESOS') {
    $whereMoneda = " AND ps.monedasub = 'PESOS' ";
} elseif ($moneda === 'DOLARES') {
    $whereMoneda = " AND ps.monedasub = 'DOLARES' ";
} else {
    $whereMoneda = " ";
}
// =====================
// WHERE búsqueda
// =====================
$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = " AND (
      pg.XFolio LIKE '%$searchThermSafe%' OR
      p.RazonSocial LIKE '%$searchThermSafe%' OR
      pg.ReferenciaBancaria LIKE '%$searchThermSafe%' OR
      CAST(pg.Total AS CHAR) LIKE '%$searchThermSafe%'
  )";
}

// =====================
// SQL (sin LIMIT, sin OFFSET)
// =====================
$sql = "SELECT
          p.ID AS ProveedorID,
          p.RazonSocial,
          pg.ID AS PagoID,
          pg.XFolio,
          pg.Fecha,
          pg.Total,
          pg.ReferenciaBancaria,
          CASE WHEN IFNULL(ps.tc_max,1) > 1 THEN 'DOLARES' ELSE 'PESOS' END AS Moneda,
          IFNULL(ps.tc_max,1) AS TipoCambio,
          ps.monedasub as monedasub
        FROM {$prefijobd}Pagos pg
        LEFT JOIN {$prefijobd}Proveedores p ON p.ID = pg.Proveedor_RID
        LEFT JOIN (
          SELECT
            pasub.FolioSubPago_RID AS PagoID,
            MAX(IFNULL(pasub.TipoCambio,1)) AS tc_max,
            cmp.Moneda as monedasub
          FROM {$prefijobd}PagosSub as pasub
          LEFT JOIN {$prefijobd}compras as cmp ON pasub.Compra_RID = cmp.ID
          GROUP BY FolioSubPago_RID
        ) ps ON ps.PagoID = pg.ID
        WHERE pg.Fecha BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59'
        {$cntQuery}
        {$whereMoneda}
        {$whereSearch}
        ORDER BY p.ID ASC, pg.Fecha DESC, pg.XFolio DESC
";

$res = mysqli_query($cnx_cfdi2, $sql);
if (!$res) {
    die("Error SQL: ".mysqli_error($cnx_cfdi2));
}

// =====================
// HEADERS Excel (HTML .xls)
// =====================
$nombreArchivo = "Pago_Proveedores_{$fecha_inicio}_a_{$fecha_fin}_{$moneda}.xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$nombreArchivo}\"");
header("Pragma: no-cache");
header("Expires: 0");

// =====================
// Helpers
// =====================
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function printTotalProveedorExcel($moneda, $sumPesos, $sumDol, $sumDolEnPesos, $countPagos){
    echo '<tr style="font-weight:bold;background:#eef4ff;">';

    if ($moneda === 'AMBOS') {
        $totalMXN = $sumPesos + $sumDolEnPesos;

        echo '<td colspan="2" style="text-align:left;">TOTAL PROVEEDOR</td>';
        echo '<td style="text-align:right;">'.intval($countPagos).' pago(s)</td>';
        echo '<td colspan="3" style="text-align:right;">';
            echo 'PESOS: $'.number_format($sumPesos,2).' &nbsp; ';
            echo 'DOLARES: $'.number_format($sumDol,2).' &nbsp; ';
            echo 'USD→MXN: $'.number_format($sumDolEnPesos,2).' &nbsp; ';
            echo 'TOTAL MXN: $'.number_format($totalMXN,2);
        echo '</td>';
    } else {
        $solo = ($moneda === 'DOLARES') ? $sumDol : $sumPesos;

        echo '<td colspan="2" style="text-align:left;">TOTAL PROVEEDOR</td>';
        echo '<td style="text-align:right;">'.intval($countPagos).' pago(s)</td>';
        echo '<td colspan="3" style="text-align:right;">$ '.number_format($solo,2).'</td>';
    }

    echo '</tr>';
}

// =====================
// Output HTML table
// =====================
echo '<html><head><meta charset="utf-8"></head><body>';

echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial;font-size:12px;width:100%;">';
echo '<tr style="font-weight:bold;background:#dfe9ff;">';
echo '<td colspan="6">Reporte Pago a Proveedores · Periodo: '.h($fecha_inicio).' al '.h($fecha_fin).' · Moneda: '.h($moneda).'</td>';
echo '</tr>';

echo '<tr style="font-weight:bold;background:#f5f7fb;">';
echo '<td>Proveedor</td>';
echo '<td>Moneda</td>';
echo '<td>Folio</td>';
echo '<td>Fecha</td>';
echo '<td>Referencia Bancaria</td>';
echo '<td>Abonado</td>';
echo '</tr>';

// =====================
// Agrupación
// =====================
$provActual = null;

$sumPesosProv = 0.0;
$sumDolProv = 0.0;
$sumDolEnPesosProv = 0.0;
$countPagos = 0;

$hayFilas = false;

while ($row = mysqli_fetch_assoc($res)) {
    $hayFilas = true;

    $provId = (int)$row['ProveedorID'];         
    $provNombre = $row['RazonSocial'];
    $referenciaBancaria = $row['ReferenciaBancaria'];

    // cambio de proveedor
    if ($provActual === null || $provActual !== $provId) {

        // cerrar proveedor anterior
        if ($provActual !== null) {
            printTotalProveedorExcel($moneda, $sumPesosProv, $sumDolProv, $sumDolEnPesosProv, $countPagos);
            // fila separadora
            echo '<tr><td colspan="6" style="background:#ffffff;height:6px;"></td></tr>';

            // reset
            $sumPesosProv = 0.0;
            $sumDolProv = 0.0;
            $sumDolEnPesosProv = 0.0;
            $countPagos = 0;
        }

        // header proveedor
        echo '<tr style="font-weight:bold;background:#eef4ff;">';
        echo '<td colspan="6">'.h($provNombre).'</td>';
        echo '</tr>';

        $provActual = $provId;
    }

    // fila pago
    $monto = floatval($row['Total']);
    $tc = isset($row['TipoCambio']) ? (float)$row['TipoCambio'] : 1.0;
    if ($tc <= 0) $tc = 1.0;

    $countPagos++;

    if ($row['monedasub'] === 'DOLARES') {
        $sumDolProv += $monto;
        $sumDolEnPesosProv += ($monto * $tc);
    } else {
        $sumPesosProv += $monto;
    }

    $fechaFmt = '';
    if (!empty($row['Fecha'])) {
        $fechaFmt = date("d-m-Y", strtotime($row['Fecha']));
    }

    echo '<tr>';
    echo '<td>'.h($provNombre).'</td>';              
    echo '<td>'.h($row['monedasub']).'</td>';
    echo '<td>'.h($row['XFolio']).'</td>';
    echo '<td>'.h($fechaFmt).'</td>';
    echo '<td>'.h($referenciaBancaria).'</td>';
    echo '<td style="text-align:right;">'.number_format($monto,2).'</td>';
    echo '</tr>';
}

// total del último proveedor
if ($hayFilas && $provActual !== null) {
    printTotalProveedorExcel($moneda, $sumPesosProv, $sumDolProv, $sumDolEnPesosProv, $countPagos);
}

if (!$hayFilas) {
    echo '<tr><td colspan="6" style="color:#666;">Sin resultados con los filtros actuales.</td></tr>';
}

echo '</table>';
echo '</body></html>';
exit;
?>
