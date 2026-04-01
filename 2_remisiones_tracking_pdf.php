<?php
ini_set('memory_limit', '1024M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000);

// Para ver errores si algo truena en mPDF (puedes quitarlo después)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==== PARÁMETROS DESDE EL FRONT (POST) ====
$fecha_inicio = isset($_POST["fechai"]) ? $_POST["fechai"] : '';
$fecha_fin    = isset($_POST["fechaf"]) ? $_POST["fechaf"] : '';
$prefijobd    = isset($_POST["base"])   ? $_POST["base"]   : '';
$sucursal     = isset($_POST["sucursal"]) ? trim($_POST["sucursal"]) : '';

if ($fecha_inicio == '' || $fecha_fin == '' || $prefijobd == '') {
    die("Faltan parámetros para generar el PDF.");
}

// ==== CONEXIÓN NUEVA (cnx_cfdi3 / mysqli) ====
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// Normalizamos prefijo: siempre termina en "_"
$prefijobd = rtrim($prefijobd, "_") . "_";

// Fechas para filtro exacto (misma lógica que tu back viejo)
$fi2  = date("Y-m-d H:i:s", strtotime($fecha_inicio));
$ff2  = date("Y-m-d H:i:s", strtotime($fecha_fin));
$ff_ts = strtotime('+23 hour +59 minute +59 second', strtotime($ff2));
$nuevafecha_fin = date('Y-m-d H:i:s', $ff_ts);

// ==== ARMO LAS FILAS COMO EN EL BACK ORIGINAL ====
$rows = array();

// 1) IDs de remisiones que tienen tracking en el rango
$sqlIds = "SELECT DISTINCT(FolioEstatus2_RID) AS FolioEstatus2_RID
           FROM {$prefijobd}remisionesestatus2
           WHERE DATE(Fecha) BETWEEN ? AND ?
             AND FolioEstatus2_RID <> ''
           ORDER BY ID";

$stmtIds = $cnx_cfdi3->prepare($sqlIds);
if (!$stmtIds) {
    die("Error en la consulta de IDs: " . $cnx_cfdi3->error);
}
$stmtIds->bind_param('ss', $fecha_inicio, $fecha_fin);
$stmtIds->execute();
$resIds = $stmtIds->get_result();

while ($rowId = $resIds->fetch_assoc()) {
    $id_remision = (int)$rowId['FolioEstatus2_RID'];
    if ($id_remision <= 0) {
        continue;
    }

    // 2) Datos de la remisión + unidad (con sucursal opcional)
    $sqlRem = "SELECT 
                  R.ID, R.XFolio, R.Unidad_RID, R.Ruta_RID, R.URemolqueA_RID,
                  R.Operador_RID, R.CargoACliente_RID, R.Instrucciones,
                  R.CitaCarga, R.Creado,
                  U.Unidad,
                  R.FechaHoraSalida, R.FechaHoraLlegada, R.TiempoEsperaCargaViaje
               FROM {$prefijobd}remisiones R
               JOIN {$prefijobd}unidades U ON R.Unidad_RID = U.ID
               WHERE R.ID = ?";

    // Si viene sucursal → filtramos por sucursal
    $useSucursal = ($sucursal !== '' && ctype_digit($sucursal));
    if ($useSucursal) {
        $sqlRem .= " AND U.Sucursal_RID = ?";
    }
    $sqlRem .= " ORDER BY U.Unidad";

    $stmtRem = $cnx_cfdi3->prepare($sqlRem);
    if (!$stmtRem) {
        die("Error en la consulta de remisión: " . $cnx_cfdi3->error);
    }

    if ($useSucursal) {
        $sucInt = (int)$sucursal;
        $stmtRem->bind_param('ii', $id_remision, $sucInt);
    } else {
        $stmtRem->bind_param('i', $id_remision);
    }

    $stmtRem->execute();
    $resRem = $stmtRem->get_result();

    while ($rowSQL1 = $resRem->fetch_assoc()) {

        $xfolio                    = $rowSQL1['XFolio'];
        $unidad                    = $rowSQL1['Unidad_RID'];
        $ruta_id                   = $rowSQL1['Ruta_RID'];
        $remolque_id               = $rowSQL1['URemolqueA_RID'];
        $operador_id               = $rowSQL1['Operador_RID'];
        $cliente_id                = $rowSQL1['CargoACliente_RID'];
        $instrucciones             = $rowSQL1['Instrucciones'];
        $cita_fecha_temp           = $rowSQL1['CitaCarga'];
        $fecha_temp2               = $rowSQL1['Creado'];
        $fecha_hora_salida_temp    = $rowSQL1['FechaHoraSalida'];
        $fecha_hora_llegada_temp   = $rowSQL1['FechaHoraLlegada'];
        $tiempo_espera_carga_viaje = $rowSQL1['TiempoEsperaCargaViaje'];

        $cita_fecha = $cita_fecha_temp ? date("d-m-Y H:i:s", strtotime($cita_fecha_temp)) : '';
        $fecha2     = $fecha_temp2 ? date("d-m-Y H:i:s", strtotime($fecha_temp2)) : '';
        $fecha_hora_salida  = $fecha_hora_salida_temp ? date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp)) : '';
        $fecha_hora_llegada = $fecha_hora_llegada_temp ? date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp)) : '';

        if ($fecha_hora_salida_temp  < '1990-01-01 00:00:00') $fecha_hora_salida  = '';
        if ($fecha_hora_llegada_temp < '1990-01-01 00:00:00') $fecha_hora_llegada = '';
        if ($cita_fecha_temp         < '1990-01-01 00:00:00') $cita_fecha        = '';

        if (!isset($unidad))      $unidad      = 0;
        if (!isset($remolque_id)) $remolque_id = 0;
        if (!isset($operador_id)) $operador_id = 0;
        if (!isset($cliente_id))  $cliente_id  = 0;

        // Remolque
        $nom_remolque = '';
        if ($remolque_id > 0) {
            $sql6 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$remolque_id;
            $res6 = mysqli_query($cnx_cfdi3, $sql6);
            if ($res6) {
                $row6 = mysqli_fetch_assoc($res6);
                if ($row6) $nom_remolque = $row6['Unidad'];
            }
        }

        // Operador
        $nom_operador = '';
        if ($operador_id > 0) {
            $sql7 = "SELECT Operador FROM {$prefijobd}operadores WHERE ID = ".$operador_id;
            $res7 = mysqli_query($cnx_cfdi3, $sql7);
            if ($res7) {
                $row7 = mysqli_fetch_assoc($res7);
                if ($row7) $nom_operador = $row7['Operador'];
            }
        }

        // Cliente
        $nom_cliente = '';
        if ($cliente_id > 0) {
            $sql8 = "SELECT RazonSocial FROM {$prefijobd}clientes WHERE ID = ".$cliente_id;
            $res8 = mysqli_query($cnx_cfdi3, $sql8);
            if ($res8) {
                $row8 = mysqli_fetch_assoc($res8);
                if ($row8) $nom_cliente = $row8['RazonSocial'];
            }
        }

        // Unidad
        $nom_unidad = '';
        if ($unidad > 0) {
            $sql2 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$unidad;
            $res2 = mysqli_query($cnx_cfdi3, $sql2);
            if ($res2) {
                $row2 = mysqli_fetch_assoc($res2);
                if ($row2) $nom_unidad = $row2['Unidad'];
            }
        }

        // Ruta
        $ruta     = '';
        $kms_ruta = 0;
        if ($ruta_id > 0) {
            $sql5 = "SELECT * FROM {$prefijobd}rutas WHERE ID = ".$ruta_id;
            $res5 = mysqli_query($cnx_cfdi3, $sql5);
            if ($res5) {
                $row5 = mysqli_fetch_assoc($res5);
                if ($row5) {
                    $ruta          = $row5['Ruta'];
                    $kms_ruta_temp = $row5['Kms'];
                    $kms_ruta      = number_format($kms_ruta_temp, 2);
                }
            }
        }

        // Último tracking de esa remisión
        $sql3 = "SELECT MAX(ID) AS max_id 
                 FROM {$prefijobd}remisionesestatus2 
                 WHERE FolioEstatus2_RID = ".$id_remision;
        $res3 = mysqli_query($cnx_cfdi3, $sql3);
        $row3 = $res3 ? mysqli_fetch_assoc($res3) : null;
        $ultimo_id_tracking = $row3 ? (int)$row3['max_id'] : 0;

        if ($ultimo_id_tracking <= 0) {
            continue;
        }

        $sql4 = "SELECT * FROM {$prefijobd}remisionesestatus2 WHERE ID = ".$ultimo_id_tracking;
        $res4 = mysqli_query($cnx_cfdi3, $sql4);
        $row4 = $res4 ? mysqli_fetch_assoc($res4) : null;
        if (!$row4) {
            continue;
        }

        $estatus     = $row4['Estatus'];
        $fecha_temp  = $row4['Fecha'];
        $fecha00     = $fecha_temp ? date("Y-m-d H:i:s", strtotime($fecha_temp)) : '';
        $fecha       = $fecha_temp ? date("d-m-Y H:i:s", strtotime($fecha_temp)) : '';

        if ($fecha < '01-01-1990 00:00:00') {
            $fecha = '';
        }

        $documentador                    = $row4['Documentador'];
        $comentario                      = $row4['Comentarios'];
        $estatus_llegada                 = $row4['EstatusLlegada'];
        $temperatura_cr                  = $row4['TemperaturaCR'];
        $comentarios_cr                  = $row4['ComentariosCR'];
        $ubicacion_unidad                = $row4['UbicacionUnidad'];
        $km_restantes_temp               = $row4['KmRestantes'];
        $km_restantes                    = number_format($km_restantes_temp, 2);
        $tiempo_estimado_llegada_destino = $row4['TiempoEstimadoLlegadaDestino'];
        $diesel_tr_temp                  = $row4['DieselTR'];
        $diesel_tr                       = number_format($diesel_tr_temp, 2);
        $diesel_cr_temp                  = $row4['DieselCR'];
        $diesel_cr                       = number_format($diesel_cr_temp, 2);

        // Mismo filtro final de rango de fecha
        if ($fecha00 != '' && $fecha00 >= $fi2 && $fecha00 <= $nuevafecha_fin) {
            $rows[] = array(
                $xfolio,
                $nom_unidad,
                $nom_remolque,
                $nom_operador,
                $ruta,
                $kms_ruta,
                $nom_cliente,
                $fecha_hora_salida,
                $fecha_hora_llegada,
                $estatus,
                $tiempo_espera_carga_viaje,
                $fecha,
                $documentador,
                $cita_fecha,
                $instrucciones,
                $comentario,
                $temperatura_cr,
                $comentarios_cr,
                $ubicacion_unidad,
                $km_restantes,
                $tiempo_estimado_llegada_destino,
                $estatus_llegada,
                $diesel_tr,
                $diesel_cr
            );
        }
    }

    $stmtRem->close();
}
$stmtIds->close();
mysqli_close($cnx_cfdi3);

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// ==== HTML PARA mPDF (MISMO ESTILO TUYO: ob_start → html → ob_get_clean) ====
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tracking por Remisión</title>
    <style>
        body{ font-family: helvetica, sans-serif; font-size:9px; }
        h2{ text-align:center; margin-bottom:4px; }
        .subtitle{ text-align:center; font-size:8px; margin-bottom:8px; }
        table{ border-collapse:collapse; width:100%; }
        th,td{ border:0.5px solid #666; padding:2px; }
        th{ background:#e0e0e0; font-weight:bold; text-align:center; }
        tr:nth-child(even) td{ background:#f9f9f9; }
    </style>
</head>
<body>

<h2>Último Tracking por Viaje</h2>
<div class="subtitle">
    Periodo: <?php echo $fecha_inicio_f; ?> al <?php echo $fecha_fin_f; ?>
    <?php if ($sucursal !== ''): ?>
        · Sucursal: <?php echo htmlspecialchars($sucursal); ?>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th>Viaje</th>
            <th>Unidad</th>
            <th>CR</th>
            <th>Operador</th>
            <th>Ruta</th>
            <th>Kms Ruta</th>
            <th>Cliente</th>
            <th>Fecha/Hora Salida</th>
            <th>Fecha/Hora Llegada</th>
            <th>Estatus</th>
            <th>Tiempo Espera Carga/Viaje</th>
            <th>Fecha Tracking</th>
            <th>Documentador</th>
            <th>Cita</th>
            <th>Especificaciones Viaje Cliente</th>
            <th>Comentarios TR</th>
            <th>Temperatura CR</th>
            <th>Comentarios CR</th>
            <th>Ubicación Unidad</th>
            <th>Kms Restantes</th>
            <th>Tiempo Estimado Destino</th>
            <th>Estatus Llegada</th>
            <th>Diesel TR</th>
            <th>Diesel CR</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($rows) == 0): ?>
            <tr>
                <td colspan="24" style="text-align:center;">Sin registros en el periodo seleccionado.</td>
            </tr>
        <?php else: ?>
            <?php foreach($rows as $r): ?>
                <tr>
                    <?php foreach($r as $cell): ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

// ==== mPDF 6.1 (tu patrón: mpdf/mpdf.php + new mPDF('utf-8','letter') ====
require_once __DIR__ . '/vendor/autoload.php';


// Horizontal (landscape)
$mpdf = new mPDF('utf-8', 'A4-L');
$mpdf->SetDisplayMode('fullwidth');
$mpdf->WriteHTML($html);

$nombre_pdf = "tracking_viajes_" . date("Ymd_His") . ".pdf";
$mpdf->Output($nombre_pdf, 'D'); 
exit;
