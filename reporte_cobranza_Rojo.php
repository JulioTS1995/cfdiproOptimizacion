<?php
ini_set('memory_limit', '256M');
set_time_limit(300);

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

require_once('cnx_cfdi2.php');
require_once('lib_mpdf/pdf/mpdf.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

function normaliza_prefijo($prefijo)
{
    $prefijo = trim($prefijo);
    $prefijo = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijo);

    if ($prefijo === '') {
        return '';
    }

    if (strpos($prefijo, '_') === false) {
        $prefijo .= '_';
    }

    return $prefijo;
}

function h($texto)
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

$prefijobd = normaliza_prefijo($_GET['prefijodb']);

if ($prefijobd === '') {
    die("Prefijo inválido");
}
$prefijo = rtrim($prefijobd,'_');
$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';
$mensajeError = '';

if (isset($_POST['submit'])) {

    $fechai = isset($_POST['fechai']) ? trim($_POST['fechai']) : '';
    $fechaf = isset($_POST['fechaf']) ? trim($_POST['fechaf']) : '';
    $monedaRaw = isset($_POST['moneda']) ? trim($_POST['moneda']) : '0';

    if ($fechai === '' || $fechaf === '') {
        $mensajeError = 'Es necesario capturar ambas fechas.';
    } else {

        $anio_logs = date('Y');
        $mes_logs  = date('m');
        $dia_logs  = date('d');

        switch ($mes_logs) {
            case '01': $mes2 = "Enero"; break;
            case '02': $mes2 = "Febrero"; break;
            case '03': $mes2 = "Marzo"; break;
            case '04': $mes2 = "Abril"; break;
            case '05': $mes2 = "Mayo"; break;
            case '06': $mes2 = "Junio"; break;
            case '07': $mes2 = "Julio"; break;
            case '08': $mes2 = "Agosto"; break;
            case '09': $mes2 = "Septiembre"; break;
            case '10': $mes2 = "Octubre"; break;
            case '11': $mes2 = "Noviembre"; break;
            case '12': $mes2 = "Diciembre"; break;
            default:   $mes2 = ""; break;
        }

        $fecha_impresion = $dia_logs . " de " . $mes2 . " de " . $anio_logs;
        $fecha_hoy = date('Y-m-d');

       
        $RazonSocial = '';
        $RFC = '';
        $Calle = '';
        $NumeroExterior = '';
        $Colonia = '';
        $Municipio = '';
        $Estado = '';

        $sqlEmpresa = "SELECT RazonSocial, RFC, Calle, NumeroExterior, Colonia, Municipio, Estado 
                       FROM " . $prefijobd . "systemsettings 
                       LIMIT 1";
        $rsEmpresa = mysqli_query($cnx_cfdi2, $sqlEmpresa);

        if ($rsEmpresa && mysqli_num_rows($rsEmpresa) > 0) {
            $rowEmpresa = mysqli_fetch_assoc($rsEmpresa);
            $RazonSocial = $rowEmpresa['RazonSocial'];
            $RFC = $rowEmpresa['RFC'];
            $Calle = $rowEmpresa['Calle'];
            $NumeroExterior = $rowEmpresa['NumeroExterior'];
            $Colonia = $rowEmpresa['Colonia'];
            $Municipio = $rowEmpresa['Municipio'];
            $Estado = $rowEmpresa['Estado'];
        }

    
        $monedaWhere = "";
        $monedaLabel = "Todas";

        if ($monedaRaw == '1') {
            $monedaWhere = " AND F.Moneda = 'PESOS' ";
            $monedaLabel = "MXN";
        } elseif ($monedaRaw == '2') {
            $monedaWhere = " AND F.Moneda = 'DOLARES' ";
            $monedaLabel = "USD";
        } else {
            $monedaWhere = "";
            $monedaLabel = "Todas";
        }

        $fechaiSafe = mysqli_real_escape_string($cnx_cfdi2, $fechai);
        $fechafSafe = mysqli_real_escape_string($cnx_cfdi2, $fechaf);

       
        $sql = "
            SELECT 
                F.ID,
                F.XFolio,
                F.Creado,
                F.zTotal,
                F.CobranzaAbonado,
                F.CobranzaSaldo,
                F.Vence,
                F.Comentarios,
                F.CargoAFactura_RID,
                F.Moneda,
                C.RazonSocial AS ClienteRazonSocial
            FROM " . $prefijobd . "factura AS F
            INNER JOIN " . $prefijobd . "clientes AS C
                ON C.ID = F.CargoAFactura_RID
            WHERE F.CobranzaSaldo > 0
              AND F.cCanceladoT IS NULL
              " . $monedaWhere . "
              AND DATE(F.Vence) BETWEEN '" . $fechaiSafe . "' AND '" . $fechafSafe . "'
            ORDER BY C.RazonSocial ASC, F.CargoAFactura_RID ASC, F.Vence ASC, F.ID ASC
        ";

        $rs = mysqli_query($cnx_cfdi2, $sql);

        if (!$rs) {
            die("Error en la consulta: " . mysqli_error($cnx_cfdi2));
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="cuentas_por_cobrar.pdf"');

        $html = '
        <meta charset="utf-8">
        <style>
            body{font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#111;}
            .header-table{width:100%; border-collapse:collapse; margin-bottom:10px;}
            .header-table td{vertical-align:top;}
            .titulo{font-size:20px; font-weight:bold; text-align:center; margin:10px 0 16px 0;}
            .fecha{text-align:right; font-size:12px; margin-bottom:12px;}
            .meta{font-size:11px; margin-bottom:10px;}
            table.reporte{width:100%; border-collapse:collapse; font-size:10px;}
            table.reporte thead th{
                background:#eaeaea;
                border:1px solid #999;
                padding:6px 4px;
                text-align:center;
            }
            table.reporte tbody td{
                border:1px solid #cfcfcf;
                padding:5px 4px;
            }
            .cliente-row td{
                background:#f5f5f5;
                font-weight:bold;
                font-size:11px;
            }
            .text-right{text-align:right;}
            .text-center{text-align:center;}
            .total-row td{
                font-weight:bold;
                background:#fafafa;
            }
            .dias-vencido{color:#c62828; font-weight:bold;}
            .dias-hoy{color:#d97706; font-weight:bold;}
            .dias-futuro{color:#166534;}
        </style>

        <table class="header-table">
            <tr>
                <td width="30%" >
                  <img src="'. $rutalogo.'" style="width: 140px; height: auto;" alt="Logo" />
                </td>
                <td width="50%">
                    <strong>' . h($RazonSocial) . '</strong><br>
                    ' . h($Calle . ' ' . $NumeroExterior . ', ' . $Colonia) . '<br>
                    ' . h($Municipio . ', ' . $Estado) . '<br>
                    ' . h($RFC) . '
                </td>
                <td width="20%" class="text-right">
                    ' . h($fecha_impresion) . '
                </td>
            </tr>
        </table>

        <div class="titulo">Cuentas por Cobrar</div>

        <div class="meta">
            <strong>Rango:</strong> ' . h($fechai) . ' al ' . h($fechaf) . ' &nbsp;&nbsp;&nbsp;
            <strong>Moneda:</strong> ' . h($monedaLabel) . '
        </div>

        <table class="reporte">
            <thead>
                <tr>
                    <th width="10%">Folio</th>
                    <th width="12%">Creado</th>
                    <th width="9%">Moneda</th>
                    <th width="11%">Total</th>
                    <th width="11%">Abonado</th>
                    <th width="11%">Saldo</th>
                    <th width="12%">Vence</th>
                    <th width="10%">Días por vencer</th>
                    <th width="14%">Comentarios</th>
                </tr>
            </thead>
            <tbody>
        ';

        $clienteActualId = '';
        $totalCliente = 0;
        $hayResultados = false;

        while ($row = mysqli_fetch_assoc($rs)) {
            $hayResultados = true;

            $clienteId = $row['CargoAFactura_RID'];
            $clienteNombre = $row['ClienteRazonSocial'];

            if ($clienteActualId !== '' && $clienteActualId != $clienteId) {
                $html .= '
                    <tr class="total-row">
                        <td colspan="9" class="text-right">TOTAL: $' . number_format($totalCliente, 2) . '</td>
                    </tr>
                ';
                $totalCliente = 0;
            }

            if ($clienteActualId != $clienteId) {
                $clienteActualId = $clienteId;

                $html .= '
                    <tr class="cliente-row">
                        <td colspan="9">' . h($clienteNombre) . '</td>
                    </tr>
                ';
            }

            $XFolio = $row['XFolio'];
            $creado = $row['Creado'];
            $monedaFila = $row['Moneda'];
            $total = (float)$row['zTotal'];
            $abonado = (float)$row['CobranzaAbonado'];
            $saldo = (float)$row['CobranzaSaldo'];
            $vence = $row['Vence'];
            $comentarios = $row['Comentarios'];

            $fechaVenceSolo = '';
            if (!empty($vence) && $vence != '0000-00-00' && $vence != '0000-00-00 00:00:00') {
                $fechaVenceSolo = date('Y-m-d', strtotime($vence));
            }

            $dias_por_vencer = '';
            $claseDias = '';

            if ($fechaVenceSolo !== '') {
                $dias_por_vencer = floor((strtotime($fechaVenceSolo) - strtotime($fecha_hoy)) / 86400);

                if ($dias_por_vencer < 0) {
                    $claseDias = 'dias-vencido';
                } elseif ($dias_por_vencer == 0) {
                    $claseDias = 'dias-hoy';
                } else {
                    $claseDias = 'dias-futuro';
                }
            }

            $html .= '
                <tr>
                    <td class="text-center">' . h($XFolio) . '</td>
                    <td class="text-center">' . h($creado) . '</td>
                    <td class="text-center">' . h($monedaFila) . '</td>
                    <td class="text-right">$' . number_format($total, 2) . '</td>
                    <td class="text-right">$' . number_format($abonado, 2) . '</td>
                    <td class="text-right">$' . number_format($saldo, 2) . '</td>
                    <td class="text-center">' . h($vence) . '</td>
                    <td class="text-center ' . $claseDias . '">' . h((string)$dias_por_vencer) . '</td>
                    <td>' . h($comentarios) . '</td>
                </tr>
            ';

            $totalCliente += $saldo;
        }

        if ($hayResultados) {
            $html .= '
                <tr class="total-row">
                    <td colspan="9" class="text-right">TOTAL: $' . number_format($totalCliente, 2) . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td colspan="9" class="text-center">No se encontraron registros con los filtros seleccionados.</td>
                </tr>
            ';
        }

        $html .= '
            </tbody>
        </table>
        ';

        $css = '';
        if (file_exists('css/style_pdf.css')) {
            $css = file_get_contents('css/style_pdf.css');
        }

        $mpdf = new mPDF('c', 'A4');
        $mpdf->setFooter('{DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
        $mpdf->defaultfooterline = 0;

        if ($css !== '') {
            $mpdf->WriteHTML($css, 1);
        }

        $mpdf->WriteHTML($html);
        $mpdf->Output('cuentas_por_cobrar.pdf', 'D');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Cobranza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <style>
        body{
            background:#f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            padding:30px 15px;
        }
        .reporte-wrap{
            max-width:900px;
            margin:0 auto;
        }
        .reporte-card{
            background:#ffffff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            padding:28px;
            border:1px solid #e8edf3;
        }
        .reporte-title{
            margin:0 0 8px 0;
            font-size:28px;
            font-weight:bold;
            color:#1f2937;
            text-align:center;
        }
        .reporte-subtitle{
            text-align:center;
            color:#6b7280;
            margin-bottom:25px;
        }
        .form-group label{
            font-weight:bold;
            color:#374151;
        }
        .btn-primary{
            background:#0a84ff;
            border-color:#0a84ff;
            border-radius:8px;
            padding:10px 24px;
            font-weight:bold;
        }
        .help-box{
            margin-top:18px;
            padding:12px 14px;
            border-radius:8px;
            background:#f8fafc;
            border:1px solid #e5e7eb;
            color:#4b5563;
            font-size:13px;
        }
        .alert{
            border-radius:10px;
        }
    </style>
</head>
<body>
    <div class="reporte-wrap">
        <div class="reporte-card">
            <h1 class="reporte-title">Reporte Cobranza</h1>
            <div class="reporte-subtitle">Generación de PDF de cuentas por cobrar</div>

            <?php if ($mensajeError != '') { ?>
                <div class="alert alert-danger"><?php echo h($mensajeError); ?></div>
            <?php } ?>

            <form method="post" action="?prefijodb=<?php echo urlencode($prefijobd); ?>">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="fechai">Fecha Inicial</label>
                            <input
                                type="date"
                                class="form-control"
                                name="fechai"
                                id="fechai"
                                required="required"
                                value="<?php echo isset($_POST['fechai']) ? h($_POST['fechai']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="fechaf">Fecha Final</label>
                            <input
                                type="date"
                                class="form-control"
                                name="fechaf"
                                id="fechaf"
                                required="required"
                                value="<?php echo isset($_POST['fechaf']) ? h($_POST['fechaf']) : ''; ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <div class="form-group">
                            <label for="moneda">Moneda</label>
                            <select class="form-control" name="moneda" id="moneda">
                                <option value="0" <?php echo (!isset($_POST['moneda']) || $_POST['moneda'] == '0') ? 'selected="selected"' : ''; ?>>Todas</option>
                                <option value="1" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == '1') ? 'selected="selected"' : ''; ?>>MXN</option>
                                <option value="2" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == '2') ? 'selected="selected"' : ''; ?>>USD</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center" style="margin-top:20px;">
                    <button type="submit" name="submit" value="1" class="btn btn-primary">
                        Generar PDF
                    </button>
                </div>
            </form>

           
        </div>
    </div>
</body>
</html>