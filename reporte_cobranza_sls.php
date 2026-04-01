<?php
set_time_limit(3000);
error_reporting(0);
ini_set('memory_limit', '512M');

require_once('cnx_cfdi2.php');
require_once('lib_mpdf/pdf/mpdf.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd   = isset($_POST['prefijodb']) ? trim($_POST['prefijodb']) : '';
$fechai      = isset($_POST['txtDesde']) ? trim($_POST['txtDesde']) : '';
$fechaf      = isset($_POST['txtHasta']) ? trim($_POST['txtHasta']) : '';
$cliente_id  = isset($_POST['cliente']) ? (int)$_POST['cliente'] : 0;
$moneda      = isset($_POST['moneda']) ? trim($_POST['moneda']) : 'PESOS';
$boton       = isset($_POST['btnEnviar']) ? trim($_POST['btnEnviar']) : '';
$esPortal    = isset($_POST['esPortal']) ? (int)$_POST['esPortal'] : 0;
$solo_portal = (isset($_POST['solo_portal']) && $_POST['solo_portal'] == '1') ? 1 : 0;
$modoReporte = isset($_POST['modo_reporte']) ? trim($_POST['modo_reporte']) : 'por_vencer';

if ($prefijobd === '') {
    die('Falta el prefijo de la BD');
}

if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

if ($fechai === '' || $fechaf === '') {
    die('Falta el rango de fechas');
}

if ($modoReporte !== 'por_vencer' && $modoReporte !== 'vencidos') {
    $modoReporte = 'por_vencer';
}

$fechaHoy = date('Y-m-d');
$fechaHoyTs = strtotime($fechaHoy);


$RazonSocial = '';
$resSQL0 = "SELECT RazonSocial, factura_portal FROM {$prefijobd}systemsettings LIMIT 1";
$runSQL0 = mysqli_query($cnx_cfdi2, $resSQL0);
if ($runSQL0 && mysqli_num_rows($runSQL0) > 0) {
    $rowSQL0 = mysqli_fetch_assoc($runSQL0);
    $RazonSocial = $rowSQL0['RazonSocial'];
}

// =========================
// FILTROS
// =========================
$sql_cliente = ($cliente_id > 0) ? " AND a.CargoAFactura_RID = ".$cliente_id." " : "";

if ($esPortal == 1) {
    $ctnPortal = ($solo_portal == 1) ? ' AND a.EnPortal = "1" ' : ' AND a.EnPortal = "0" ';
} else {
    $ctnPortal = '';
}

// =========================
// FUNCIONES
// =========================
function formatoMoneda($importe) {
    return '$' . number_format((float)$importe, 2);
}

function obtenerRangoDias($fechaVence, $fechaHoyTs, $modoReporte) {
    $venceTs = strtotime(substr($fechaVence, 0, 10));
    if (!$venceTs) {
        return 0;
    }

    if ($modoReporte === 'por_vencer') {
        $dias = floor(($venceTs - $fechaHoyTs) / 86400);
    } else {
        $dias = floor(($fechaHoyTs - $venceTs) / 86400);
    }

    return (int)$dias;
}

function etiquetasColumnas($modoReporte) {
    if ($modoReporte === 'vencidos') {
        return array(
            'titulo' => 'Antigüedades saldos de clientes - DÍAS VENCIDOS',
            'c1' => 'DÍAS VENCIDOS<br>De 1 a 15',
            'c2' => 'DÍAS VENCIDOS<br>De 16 a 30',
            'c3' => 'DÍAS VENCIDOS<br>De 31 a 60',
            'c4' => 'DÍAS VENCIDOS<br>De 61 a 90',
            'c5' => 'DÍAS VENCIDOS<br>Más de 90'
        );
    }

    return array(
        'titulo' => 'Antigüedades saldos de clientes - DÍAS POR VENCER',
        'c1' => 'DÍAS RESTANTES POR VENCER<br>De 1 a 15',
        'c2' => 'DÍAS RESTANTES POR VENCER<br>De 16 a 30',
        'c3' => 'DÍAS RESTANTES POR VENCER<br>De 31 a 60',
        'c4' => 'DÍAS RESTANTES POR VENCER<br>De 61 a 90',
        'c5' => 'DÍAS RESTANTES POR VENCER<br>Más de 90'
    );
}

function obtenerClientesReporte($cnx_cfdi2, $prefijobd, $fechai, $fechaf, $sql_cliente, $moneda, $ctnPortal, $modoReporte, $fechaHoyTs) {
    $clientes = array();

    $sql = "
        SELECT
            a.CargoAFactura_RID,
            b.RazonSocial
        FROM {$prefijobd}factura a
        INNER JOIN {$prefijobd}clientes b ON a.CargoAFactura_RID = b.ID
        WHERE DATE(a.Creado) BETWEEN '".$fechai."' AND '".$fechaf."'
          {$sql_cliente}
          AND a.Moneda = '".mysqli_real_escape_string($cnx_cfdi2, $moneda)."'
          AND a.CobranzaSaldo > 0
          AND a.cCanceladoT IS NULL
          {$ctnPortal}
        GROUP BY a.CargoAFactura_RID, b.RazonSocial
        ORDER BY b.RazonSocial
    ";

    $res = mysqli_query($cnx_cfdi2, $sql);
    if (!$res) {
        return $clientes;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $idCliente = (int)$row['CargoAFactura_RID'];
        $nombreCliente = $row['RazonSocial'];

        $facturas = array();
        $totales = array(
            'saldo' => 0,
            'r1' => 0,
            'r2' => 0,
            'r3' => 0,
            'r4' => 0,
            'r5' => 0
        );

        $sqlFacturas = "
            SELECT
                a.XFolio,
                a.Moneda,
                a.Vence,
                a.Creado,
                a.CobranzaSaldo
            FROM {$prefijobd}factura a
            WHERE a.CargoAFactura_RID = {$idCliente}
              AND DATE(a.Creado) BETWEEN '".$fechai."' AND '".$fechaf."'
              AND a.Moneda = '".mysqli_real_escape_string($cnx_cfdi2, $moneda)."'
              AND a.CobranzaSaldo > 0
              AND a.cCanceladoT IS NULL
              {$ctnPortal}
            ORDER BY a.Vence ASC
        ";

        $resFacturas = mysqli_query($cnx_cfdi2, $sqlFacturas);
        if ($resFacturas) {
            while ($fac = mysqli_fetch_assoc($resFacturas)) {
                $dias = obtenerRangoDias($fac['Vence'], $fechaHoyTs, $modoReporte);

                // Solo se toman registros positivos para el reporte correspondiente
                if ($dias <= 0) {
                    continue;
                }

                $saldo = (float)$fac['CobranzaSaldo'];

                $r1 = 0;
                $r2 = 0;
                $r3 = 0;
                $r4 = 0;
                $r5 = 0;

                if ($dias >= 1 && $dias <= 15) {
                    $r1 = $saldo;
                    $totales['r1'] += $saldo;
                } elseif ($dias >= 16 && $dias <= 30) {
                    $r2 = $saldo;
                    $totales['r2'] += $saldo;
                } elseif ($dias >= 31 && $dias <= 60) {
                    $r3 = $saldo;
                    $totales['r3'] += $saldo;
                } elseif ($dias >= 61 && $dias <= 90) {
                    $r4 = $saldo;
                    $totales['r4'] += $saldo;
                } elseif ($dias > 90) {
                    $r5 = $saldo;
                    $totales['r5'] += $saldo;
                } else {
                    continue;
                }

                $totales['saldo'] += $saldo;

                $facturas[] = array(
                    'FechaFactura' => substr($fac['Creado'], 0, 10),
                    'Vence' => $fac['Vence'],
                    'XFolio' => $fac['XFolio'],
                    'Moneda' => $fac['Moneda'],
                    'CobranzaSaldo' => $saldo,
                    'r1' => $r1,
                    'r2' => $r2,
                    'r3' => $r3,
                    'r4' => $r4,
                    'r5' => $r5
                );
            }
        }

        // Si no trae facturas útiles para el modo elegido, no se agrega el cliente
        if (count($facturas) > 0) {
            $clientes[] = array(
                'id_cliente' => $idCliente,
                'nom_cliente' => $nombreCliente,
                'facturas' => $facturas,
                'totales' => $totales
            );
        }
    }

    return $clientes;
}

$labels = etiquetasColumnas($modoReporte);
$clientesReporte = obtenerClientesReporte(
    $cnx_cfdi2,
    $prefijobd,
    $fechai,
    $fechaf,
    $sql_cliente,
    $moneda,
    $ctnPortal,
    $modoReporte,
    $fechaHoyTs
);

// Totales generales
$granTotalSaldo = 0;
$granTotalR1 = 0;
$granTotalR2 = 0;
$granTotalR3 = 0;
$granTotalR4 = 0;
$granTotalR5 = 0;

foreach ($clientesReporte as $clienteTmp) {
    $granTotalSaldo += $clienteTmp['totales']['saldo'];
    $granTotalR1 += $clienteTmp['totales']['r1'];
    $granTotalR2 += $clienteTmp['totales']['r2'];
    $granTotalR3 += $clienteTmp['totales']['r3'];
    $granTotalR4 += $clienteTmp['totales']['r4'];
    $granTotalR5 += $clienteTmp['totales']['r5'];
}

// =========================
// PDF
// =========================
if ($boton == 'PDF') {

    $html = '
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: sans-serif; font-size: 11px; }
            table { width: 100%; border-collapse: collapse; }
            thead th {
                background: #efefef;
                border: 1px solid #ccc;
                padding: 6px;
                font-size: 10px;
            }
            tbody td {
                border: 1px solid #ddd;
                padding: 5px;
                font-size: 10px;
            }
            .cliente-row td{
                background:#f7f7f7;
                font-weight:bold;
                font-size:11px;
            }
            .total-row td{
                font-weight:bold;
                background:#fafafa;
            }
            .general-row td{
                font-weight:bold;
                background:#eaeaea;
            }
            .titulo{
                text-align:center;
                font-size:18px;
                font-weight:bold;
                margin-bottom:8px;
            }
            .subtitulo{
                text-align:center;
                font-size:11px;
                margin-bottom:14px;
            }
        </style>
    </head>
    <body>';

    $html .= '<div class="titulo">'.htmlspecialchars($RazonSocial, ENT_QUOTES, 'UTF-8').'</div>';
    $html .= '<div class="subtitulo">'.$labels['titulo'].'<br>Moneda '.$moneda.' | Del '.$fechai.' al '.$fechaf.'</div>';

    $html .= '
    <table>
        <thead>
            <tr>
                <th align="center">Emisión</th>
                <th align="center">Vencimiento</th>
                <th align="center">Folio</th>
                <th align="center">Moneda</th>
                <th align="right">Importe</th>
                <th align="right">'.$labels['c1'].'</th>
                <th align="right">'.$labels['c2'].'</th>
                <th align="right">'.$labels['c3'].'</th>
                <th align="right">'.$labels['c4'].'</th>
                <th align="right">'.$labels['c5'].'</th>
            </tr>
        </thead>
        <tbody>';

    if (count($clientesReporte) == 0) {
        $html .= '
            <tr>
                <td colspan="10" align="center">No se encontraron registros para los filtros seleccionados.</td>
            </tr>';
    } else {
        foreach ($clientesReporte as $cliente) {
            $html .= '
            <tr class="cliente-row">
                <td colspan="10" align="left">'.htmlspecialchars($cliente['nom_cliente'], ENT_QUOTES, 'UTF-8').'</td>
            </tr>';

            foreach ($cliente['facturas'] as $fac) {
                $html .= '
                <tr>
                    <td align="center">'.$fac['FechaFactura'].'</td>
                    <td align="center">'.$fac['Vence'].'</td>
                    <td align="center">'.htmlspecialchars($fac['XFolio'], ENT_QUOTES, 'UTF-8').'</td>
                    <td align="center">'.htmlspecialchars($fac['Moneda'], ENT_QUOTES, 'UTF-8').'</td>
                    <td align="right">'.formatoMoneda($fac['CobranzaSaldo']).'</td>
                    <td align="right">'.formatoMoneda($fac['r1']).'</td>
                    <td align="right">'.formatoMoneda($fac['r2']).'</td>
                    <td align="right">'.formatoMoneda($fac['r3']).'</td>
                    <td align="right">'.formatoMoneda($fac['r4']).'</td>
                    <td align="right">'.formatoMoneda($fac['r5']).'</td>
                </tr>';
            }

            $html .= '
            <tr class="total-row">
                <td colspan="4" align="right">TOTALES</td>
                <td align="right">'.formatoMoneda($cliente['totales']['saldo']).'</td>
                <td align="right">'.formatoMoneda($cliente['totales']['r1']).'</td>
                <td align="right">'.formatoMoneda($cliente['totales']['r2']).'</td>
                <td align="right">'.formatoMoneda($cliente['totales']['r3']).'</td>
                <td align="right">'.formatoMoneda($cliente['totales']['r4']).'</td>
                <td align="right">'.formatoMoneda($cliente['totales']['r5']).'</td>
            </tr>';
        }

        $html .= '
        <tr class="general-row">
            <td colspan="4" align="right">TOTALES GENERALES</td>
            <td align="right">'.formatoMoneda($granTotalSaldo).'</td>
            <td align="right">'.formatoMoneda($granTotalR1).'</td>
            <td align="right">'.formatoMoneda($granTotalR2).'</td>
            <td align="right">'.formatoMoneda($granTotalR3).'</td>
            <td align="right">'.formatoMoneda($granTotalR4).'</td>
            <td align="right">'.formatoMoneda($granTotalR5).'</td>
        </tr>';
    }

    $html .= '
        </tbody>
    </table>
    </body>
    </html>';

    $mpdf = new mPDF('c', 'LETTER-L');
    $mpdf->setFooter('{DATE d-m-Y} / Tractosoft / Hoja {PAGENO}');
    $mpdf->defaultfooterline = 0;
    $mpdf->WriteHTML($html);
    $mpdf->Output('Antiguedades_saldos_de_clientes.pdf', 'I');
    exit;
}

// =========================
// EXCEL
// =========================
if ($boton == 'Excel') {
    header("Content-type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=Antiguedades_saldos_de_clientes_".date("H-i-s")."_".date("d-m-Y").".xls");
    echo "\xEF\xBB\xBF";
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <table border="1">
        <thead>
            <tr>
                <th colspan="10"><?php echo htmlspecialchars($RazonSocial, ENT_QUOTES, 'UTF-8'); ?></th>
            </tr>
            <tr>
                <th colspan="10"><?php echo $labels['titulo'].' Moneda '.$moneda.' DEL '.$fechai.' AL '.$fechaf; ?></th>
            </tr>
            <tr>
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th>Folio</th>
                <th>Moneda</th>
                <th>Importe</th>
                <th><?php echo strip_tags(str_replace('<br>', ' ', $labels['c1'])); ?></th>
                <th><?php echo strip_tags(str_replace('<br>', ' ', $labels['c2'])); ?></th>
                <th><?php echo strip_tags(str_replace('<br>', ' ', $labels['c3'])); ?></th>
                <th><?php echo strip_tags(str_replace('<br>', ' ', $labels['c4'])); ?></th>
                <th><?php echo strip_tags(str_replace('<br>', ' ', $labels['c5'])); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($clientesReporte) == 0) { ?>
                <tr>
                    <td colspan="10" align="center">No se encontraron registros para los filtros seleccionados.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($clientesReporte as $cliente) { ?>
                    <tr>
                        <td colspan="10"><strong><?php echo htmlspecialchars($cliente['nom_cliente'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    </tr>

                    <?php foreach ($cliente['facturas'] as $fac) { ?>
                        <tr>
                            <td><?php echo $fac['FechaFactura']; ?></td>
                            <td><?php echo $fac['Vence']; ?></td>
                            <td><?php echo htmlspecialchars($fac['XFolio'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($fac['Moneda'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['CobranzaSaldo']); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['r1']); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['r2']); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['r3']); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['r4']); ?></td>
                            <td align="right"><?php echo formatoMoneda($fac['r5']); ?></td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="4" align="right"><strong>TOTALES</strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['saldo']); ?></strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['r1']); ?></strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['r2']); ?></strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['r3']); ?></strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['r4']); ?></strong></td>
                        <td align="right"><strong><?php echo formatoMoneda($cliente['totales']['r5']); ?></strong></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" align="right"><strong>TOTALES GENERALES</strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalSaldo); ?></strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalR1); ?></strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalR2); ?></strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalR3); ?></strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalR4); ?></strong></td>
                    <td align="right"><strong><?php echo formatoMoneda($granTotalR5); ?></strong></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    exit;
}

die('Acción no válida');
?>