<?php 
// remisiones_tracking3_excel_pdf_sucursal.php
ini_set('memory_limit', '2048M');
set_time_limit(2000);
error_reporting(0);

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die("Error de conexión a la base de datos.");
}
mysqli_select_db($cnx_cfdi3, $database_cfdi);
mysqli_set_charset($cnx_cfdi3, "utf8");

if (!isset($_POST["prefijodb"], $_POST["fechai"], $_POST["fechaf"], $_POST["button"])) {
    die("Faltan parámetros.");
}

$prefijodb    = $_POST["prefijodb"];
$fecha_inicio = $_POST["fechai"];
$fecha_fin    = $_POST["fechaf"];
$id_unidad    = isset($_POST["unidad"])   ? (int)$_POST["unidad"]   : 0;
$id_operador  = isset($_POST["operador"]) ? (int)$_POST["operador"] : 0;
$sucursal     = isset($_POST["sucursal"]) ? (int)$_POST["sucursal"] : 0;
$button       = $_POST["button"];
$buscar       = isset($_POST["busqueda"])   ? trim($_POST["busqueda"])  : '';

// Sanitizar prefijo
$prefijodb = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijodb);
if (strpos($prefijodb, '_') === false) {
    $prefijodb .= '_';
}
if (substr($prefijodb, -1) !== '_') {
    $prefijodb .= '_';
}

// Fechas completas
$fi = $fecha_inicio . " 00:00:00";
$ff = $fecha_fin    . " 23:59:59";

$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t    = date("d-m-Y", strtotime($fecha_fin));

// ------------------------------------------------------------------
// 1) Detectar si existe la columna Sucursal_RID en tabla Oficinas
// ------------------------------------------------------------------
$tieneSucursal = false;
$tablaOficinas = $prefijodb . "Oficinas";

$checkCol = $cnx_cfdi3->query("SHOW COLUMNS FROM {$tablaOficinas} LIKE 'Sucursal_RID'");
if ($checkCol && $checkCol->num_rows > 0) {
    $tieneSucursal = true;
}

// ------------------------------------------------------------------
// 2) Construir SQL base + filtros sucursal / unidad / operador / buscador
// ------------------------------------------------------------------
$sql = "
SELECT
    R.XFolio,
    R.Unidad_RID,
    R.Ruta_RID,
    R.uRemolqueA_RID,
    R.Operador_RID,
    R.CargoACliente_RID,
    R.Instrucciones,
    R.CitaCarga,
    R.Creado,
    R.FechaHoraSalida,
    R.FechaHoraLlegada,
    R.TiempoEsperaCargaViaje,
    RE.Estatus,
    RE.Fecha,
    RE.Documentador,
    RE.Comentarios,
    RE.EstatusLlegada,
    RE.TemperaturaCR,
    RE.ComentariosCR,
    RE.UbicacionUnidad,
    RE.KmRestantes,
    RE.TiempoEstimadoLlegadaDestino,
    RE.DieselTR,
    RE.DieselCR,
    U1.Unidad  AS NomUnidad,
    U2.Unidad  AS NomRemolque,
    O.Operador AS NomOperador,
    C.RazonSocial AS NomCliente,
    Ru.Ruta    AS NomRuta,
    Ru.Kms     AS KmsRuta
FROM {$prefijodb}remisiones R
INNER JOIN {$prefijodb}remisionesestatus2 RE
    ON RE.FolioEstatus2_RID = R.ID
LEFT JOIN {$prefijodb}unidades U1
    ON R.Unidad_RID = U1.ID
LEFT JOIN {$prefijodb}unidades U2
    ON R.uRemolqueA_RID = U2.ID
LEFT JOIN {$prefijodb}operadores O
    ON R.Operador_RID = O.ID
LEFT JOIN {$prefijodb}clientes C
    ON R.CargoACliente_RID = C.ID
LEFT JOIN {$prefijodb}rutas Ru
    ON R.Ruta_RID = Ru.ID
WHERE
    RE.Fecha BETWEEN ? AND ?
";

$types  = "ss";
$params = array($fi, $ff);

// Filtro por sucursal SOLO si:
//  - la tabla Oficinas tiene columna Sucursal_RID
//  - y el parámetro sucursal > 0
if ($tieneSucursal && $sucursal > 0) {
    $sql     .= " AND R.Oficina_RID IN (SELECT ID FROM {$tablaOficinas} WHERE Sucursal_RID = ?)";
    $types   .= "i";
    $params[] = $sucursal;
}

// Filtro por unidad
if ($id_unidad != 0) {
    $sql     .= " AND R.Unidad_RID = ?";
    $types   .= "i";
    $params[] = $id_unidad;
}

// Filtro por operador
if ($id_operador != 0) {
    $sql     .= " AND R.Operador_RID = ?";
    $types   .= "i";
    $params[] = $id_operador;
}

// Filtro de buscador general (igual que en display)
if ($buscar !== '') {
    $sql .= " AND (
        R.XFolio                LIKE CONCAT('%', ?, '%')
        OR IFNULL(U1.Unidad,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(U2.Unidad,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(O.Operador,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(C.RazonSocial,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(Ru.Ruta,'')   LIKE CONCAT('%', ?, '%')
        OR IFNULL(RE.Estatus,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(RE.Documentador,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(RE.Comentarios,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(RE.UbicacionUnidad,'') LIKE CONCAT('%', ?, '%')
        OR IFNULL(RE.TiempoEstimadoLlegadaDestino,'') LIKE CONCAT('%', ?, '%')
    )";
    // 11 columnas incluidas arriba
    $types   .= str_repeat('s', 11);
    for ($i = 0; $i < 11; $i++) {
        $params[] = $buscar;
    }
}

$sql .= " ORDER BY R.XFolio";

$stmt = $cnx_cfdi3->prepare($sql);
if (!$stmt) {
    die("Error en la consulta: " . $cnx_cfdi3->error);
}

// bind_param dinámico
$bindArgs = array_merge(array($types), $params);
$tmp = array();
foreach ($bindArgs as $k => $v) {
    $tmp[$k] = &$bindArgs[$k];
}
call_user_func_array(array($stmt, 'bind_param'), $tmp);

if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$stmt->bind_result(
    $xf,
    $unidad_id,
    $ruta_id,
    $remolque_id,
    $operador_id,
    $cliente_id,
    $instrucciones,
    $cita_fecha_temp,
    $creado_temp,
    $fecha_hora_salida_temp,
    $fecha_hora_llegada_temp,
    $tiempo_espera_carga_viaje,
    $estatus,
    $fecha_temp,
    $documentador,
    $comentario,
    $estatus_llegada,
    $temperatura_cr,
    $comentarios_cr,
    $ubicacion_unidad,
    $km_restantes_temp,
    $tiempo_estimado_llegada_destino,
    $diesel_tr_temp,
    $diesel_cr_temp,
    $nom_unidad,
    $nom_remolque,
    $nom_operador,
    $nom_cliente,
    $ruta,
    $kms_ruta_temp
);

// Armamos filas
$rows = array();
while ($stmt->fetch()) {
    $cita_fecha          = date("d-m-Y H:i:s", strtotime($cita_fecha_temp));
    $fecha2              = date("d-m-Y H:i:s", strtotime($creado_temp));
    $fecha_hora_salida   = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
    $fecha_hora_llegada  = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
    $fecha00             = date("Y-m-d H:i:s", strtotime($fecha_temp));
    $fecha               = date("d-m-Y H:i:s", strtotime($fecha_temp));

    if ($fecha_hora_salida_temp < '1990-01-01 00:00:00') {
        $fecha_hora_salida = '';
    }
    if ($fecha_hora_llegada_temp < '1990-01-01 00:00:00') {
        $fecha_hora_llegada = '';
    }
    if ($cita_fecha_temp < '1990-01-01 00:00:00') {
        $cita_fecha = '';
    }
    if ($fecha < '01-01-1990 00:00:00') {
        $fecha = '';
    }

    $km_restantes = number_format((float)$km_restantes_temp, 2);
    $kms_ruta     = number_format((float)$kms_ruta_temp, 2);
    $diesel_tr    = number_format((float)$diesel_tr_temp, 2);
    $diesel_cr    = number_format((float)$diesel_cr_temp, 2);

    $rows[] = array(
        'XFolio'        => $xf,
        'NomUnidad'     => $nom_unidad,
        'NomRemolque'   => $nom_remolque,
        'NomOperador'   => $nom_operador,
        'Ruta'          => $ruta,
        'KmsRuta'       => $kms_ruta,
        'NomCliente'    => $nom_cliente,
        'FechaSalida'   => $fecha_hora_salida,
        'FechaLlegada'  => $fecha_hora_llegada,
        'Estatus'       => $estatus,
        'TiempoEspera'  => $tiempo_espera_carga_viaje,
        'FechaTracking' => $fecha,
        'Documentador'  => $documentador,
        'Cita'          => $cita_fecha,
        'Instrucciones' => $instrucciones,
        'ComentariosTR' => $comentario,
        'TemperaturaCR' => $temperatura_cr,
        'ComentariosCR' => $comentarios_cr,
        'Ubicacion'     => $ubicacion_unidad,
        'KmRestantes'   => $km_restantes,
        'ETA'           => $tiempo_estimado_llegada_destino,
        'EstatusLlegada'=> $estatus_llegada,
        'DieselTR'      => $diesel_tr,
        'DieselCR'      => $diesel_cr
    );
}

$stmt->close();
$cnx_cfdi3->close();

// ===== EXCEL =====
if ($button === 'Excel') {
    header("Content-type: application/vnd.ms-excel");
    $nombre = "tracking3_sucursal_" . date("Ymd_His") . ".xls";
    header("Content-Disposition: attachment; filename=$nombre");
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <table border="1" cellspacing="0" cellpadding="2">
        <thead>
        <tr>
            <th colspan="24" style="font-size:16px; text-align:center;">
                Tracking 3 por Viaje (<?php echo $fecha_inicio_t . " - " . $fecha_fin_t; ?>)
            </th>
        </tr>
        <tr>
            <th>Viaje</th>
            <th>Unidad</th>
            <th>CR</th>
            <th>Operador</th>
            <th>Ruta</th>
            <th>Kms Ruta</th>
            <th>Cliente</th>
            <th>Fecha y Hora de Salida</th>
            <th>Fecha y Hora de Llegada</th>
            <th>Estatus</th>
            <th>Tiempo en Espera de Carga/Viaje</th>
            <th>Fecha de Tracking</th>
            <th>Documentador</th>
            <th>Cita</th>
            <th>Especificaciones de Viaje del Cliente</th>
            <th>Comentarios TR</th>
            <th>Temperatura CR</th>
            <th>Comentarios CR</th>
            <th>Ubicación de Unidad</th>
            <th>Kms Restantes</th>
            <th>Tiempo Estimado para llegar a Destino</th>
            <th>Estatus de Llegada</th>
            <th>Diesel TR</th>
            <th>Diesel CR</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo $r['XFolio']; ?></td>
                <td><?php echo $r['NomUnidad']; ?></td>
                <td><?php echo $r['NomRemolque']; ?></td>
                <td><?php echo $r['NomOperador']; ?></td>
                <td><?php echo $r['Ruta']; ?></td>
                <td><?php echo $r['KmsRuta']; ?></td>
                <td><?php echo $r['NomCliente']; ?></td>
                <td><?php echo $r['FechaSalida']; ?></td>
                <td><?php echo $r['FechaLlegada']; ?></td>
                <td><?php echo $r['Estatus']; ?></td>
                <td><?php echo $r['TiempoEspera']; ?></td>
                <td><?php echo $r['FechaTracking']; ?></td>
                <td><?php echo $r['Documentador']; ?></td>
                <td><?php echo $r['Cita']; ?></td>
                <td><?php echo $r['Instrucciones']; ?></td>
                <td><?php echo $r['ComentariosTR']; ?></td>
                <td><?php echo $r['TemperaturaCR']; ?></td>
                <td><?php echo $r['ComentariosCR']; ?></td>
                <td><?php echo $r['Ubicacion']; ?></td>
                <td><?php echo $r['KmRestantes']; ?></td>
                <td><?php echo $r['ETA']; ?></td>
                <td><?php echo $r['EstatusLlegada']; ?></td>
                <td><?php echo $r['DieselTR']; ?></td>
                <td><?php echo $r['DieselCR']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    exit;
}

// ===== PDF =====
if ($button === 'PDF') {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body{
                font-family: helvetica, sans-serif;
                font-size:8.5pt;
            }
            h1{
                font-size:12pt;
                text-align:center;
                margin-bottom:4px;
            }
            .sub{
                font-size:9pt;
                text-align:center;
                margin-bottom:8px;
            }
            table{
                width:100%;
                border-collapse:collapse;
                font-size:7pt;
            }
            th, td{
                border:0.3px solid #444;
                padding:2px;
            }
            th{
                background:#e0e0e0;
                font-weight:bold;
                text-align:center;
            }
            td.num{
                text-align:right;
            }
        </style>
    </head>
    <body>
    <h1>Tracking 3 por Viaje</h1>
    <div class="sub">
        Periodo: <?php echo $fecha_inicio_t . " - " . $fecha_fin_t; ?>
        <?php if ($sucursal > 0): ?>
            · Sucursal ID: <?php echo (int)$sucursal; ?>
        <?php endif; ?>
    </div>

    <table autosize="1">
        <thead>
        <tr>
            <th>Viaje</th>
            <th>Unidad</th>
            <th>CR</th>
            <th>Operador</th>
            <th>Ruta</th>
            <th>Kms Ruta</th>
            <th>Cliente</th>
            <th>F/H Salida</th>
            <th>F/H Llegada</th>
            <th>Estatus</th>
            <th>Tiempo Espera</th>
            <th>F. Tracking</th>
            <th>Documentador</th>
            <th>Cita</th>
            <th>Espec. Viaje</th>
            <th>Comentarios TR</th>
            <th>Temp CR</th>
            <th>Comentarios CR</th>
            <th>Ubicación</th>
            <th>Kms Restantes</th>
            <th>ETA Destino</th>
            <th>Estatus Llegada</th>
            <th>Diesel TR</th>
            <th>Diesel CR</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['XFolio']); ?></td>
                <td><?php echo htmlspecialchars($r['NomUnidad']); ?></td>
                <td><?php echo htmlspecialchars($r['NomRemolque']); ?></td>
                <td><?php echo htmlspecialchars($r['NomOperador']); ?></td>
                <td><?php echo htmlspecialchars($r['Ruta']); ?></td>
                <td class="num"><?php echo $r['KmsRuta']; ?></td>
                <td><?php echo htmlspecialchars($r['NomCliente']); ?></td>
                <td><?php echo htmlspecialchars($r['FechaSalida']); ?></td>
                <td><?php echo htmlspecialchars($r['FechaLlegada']); ?></td>
                <td><?php echo htmlspecialchars($r['Estatus']); ?></td>
                <td><?php echo htmlspecialchars($r['TiempoEspera']); ?></td>
                <td><?php echo htmlspecialchars($r['FechaTracking']); ?></td>
                <td><?php echo htmlspecialchars($r['Documentador']); ?></td>
                <td><?php echo htmlspecialchars($r['Cita']); ?></td>
                <td><?php echo htmlspecialchars($r['Instrucciones']); ?></td>
                <td><?php echo htmlspecialchars($r['ComentariosTR']); ?></td>
                <td><?php echo htmlspecialchars($r['TemperaturaCR']); ?></td>
                <td><?php echo htmlspecialchars($r['ComentariosCR']); ?></td>
                <td><?php echo htmlspecialchars($r['Ubicacion']); ?></td>
                <td class="num"><?php echo $r['KmRestantes']; ?></td>
                <td><?php echo htmlspecialchars($r['ETA']); ?></td>
                <td><?php echo htmlspecialchars($r['EstatusLlegada']); ?></td>
                <td class="num"><?php echo $r['DieselTR']; ?></td>
                <td class="num"><?php echo $r['DieselCR']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    require_once __DIR__ . '/vendor/autoload.php';

    $mpdf = new mPDF('utf-8','letter');
    $mpdf->WriteHTML($html);
    $nombre_pdf = "tracking3_sucursal_" . date("Ymd_His") . ".pdf";
    $mpdf->Output($nombre_pdf, 'D'); 
    exit;
}

die("Acción no reconocida.");
