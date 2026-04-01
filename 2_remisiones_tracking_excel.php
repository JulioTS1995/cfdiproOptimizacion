<?php
// Exportar a Excel tracking por remisión

$fecha_inicio = isset($_POST["fechai"]) ? $_POST["fechai"] : '';
$fecha_fin    = isset($_POST["fechaf"]) ? $_POST["fechaf"] : '';
$prefijobd    = isset($_POST["base"])   ? $_POST["base"]   : '';
$sucursal     = isset($_POST["sucursal"]) ? trim($_POST["sucursal"]) : '';

if (!$fecha_inicio || !$fecha_fin || !$prefijobd) {
    die("Faltan parámetros obligatorios.");
}

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// 1) IDs de remisión
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

$rows = [];

$fi2 = date("Y-m-d H:i:s", strtotime($fecha_inicio));
$ff2 = date("Y-m-d H:i:s", strtotime($fecha_fin));
$nuevafecha_fin_ts = strtotime('+23 hour +59 minute +59 second', strtotime($ff2));
$nuevafecha_fin    = date('Y-m-d H:i:s', $nuevafecha_fin_ts);

while ($rowId = $resIds->fetch_assoc()) {
    $id_remision = (int)$rowId['FolioEstatus2_RID'];
    if ($id_remision <= 0) continue;

    $sqlRem = "SELECT 
                  R.ID, R.XFolio, R.Unidad_RID, R.Ruta_RID, R.URemolqueA_RID,
                  R.Operador_RID, R.CargoACliente_RID, R.Instrucciones,
                  R.CitaCarga, R.Creado, U.Unidad,
                  R.FechaHoraSalida, R.FechaHoraLlegada, R.TiempoEsperaCargaViaje
               FROM {$prefijobd}remisiones R
               JOIN {$prefijobd}unidades U ON R.Unidad_RID = U.ID
               WHERE R.ID = ?";

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

        $nom_remolque = '';
        if ($remolque_id > 0) {
            $sql6 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$remolque_id;
            $res6 = mysqli_query($cnx_cfdi3, $sql6);
            $row6 = $res6 ? mysqli_fetch_assoc($res6) : null;
            $nom_remolque = $row6 ? $row6['Unidad'] : '';
        }

        $nom_operador = '';
        if ($operador_id > 0) {
            $sql7 = "SELECT Operador FROM {$prefijobd}operadores WHERE ID = ".$operador_id;
            $res7 = mysqli_query($cnx_cfdi3, $sql7);
            $row7 = $res7 ? mysqli_fetch_assoc($res7) : null;
            $nom_operador = $row7 ? $row7['Operador'] : '';
        }

        $nom_cliente = '';
        if ($cliente_id > 0) {
            $sql8 = "SELECT RazonSocial FROM {$prefijobd}clientes WHERE ID = ".$cliente_id;
            $res8 = mysqli_query($cnx_cfdi3, $sql8);
            $row8 = $res8 ? mysqli_fetch_assoc($res8) : null;
            $nom_cliente = $row8 ? $row8['RazonSocial'] : '';
        }

        $nom_unidad = '';
        if ($unidad > 0) {
            $sql2 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$unidad;
            $res2 = mysqli_query($cnx_cfdi3, $sql2);
            $row2 = $res2 ? mysqli_fetch_assoc($res2) : null;
            $nom_unidad = $row2 ? $row2['Unidad'] : '';
        }

        $ruta     = '';
        $kms_ruta = 0;
        if ($ruta_id > 0) {
            $sql5 = "SELECT * FROM {$prefijobd}rutas WHERE ID = ".$ruta_id;
            $res5 = mysqli_query($cnx_cfdi3, $sql5);
            $row5 = $res5 ? mysqli_fetch_assoc($res5) : null;
            if ($row5) {
                $ruta          = $row5['Ruta'];
                $kms_ruta_temp = $row5['Kms'];
                $kms_ruta      = number_format($kms_ruta_temp, 2);
            }
        }

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

        if ($fecha00 && ($fecha00 >= $fi2) && ($fecha00 <= $nuevafecha_fin)) {
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

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "tracking_viajes_".date("Ymd_His").".xls";
header("Content-Disposition: attachment; filename=$nombre");
header("Pragma: no-cache");
header("Expires: 0");

echo "<meta charset='UTF-8' />";
echo "<table border='1' cellspacing='0' cellpadding='2'>";
echo "<thead>";
echo "<tr><th colspan='24' style='font-size:16px;'>Último Tracking por Viaje - Periodo: ".$fecha_inicio_f." al ".$fecha_fin_f."</th></tr>";
echo "<tr>";
$cols = array(
    'Viaje','Unidad','CR','Operador','Ruta','Kms Ruta','Cliente',
    'Fecha y Hora de Salida','Fecha y Hora de Llegada','Estatus',
    'Tiempo en Espera Carga/Viaje','Fecha de Tracking','Documentador','Cita',
    'Especificaciones de Viaje del Cliente','Comentarios TR','Temperatura CR',
    'Comentarios CR','Ubicación de Unidad','Kms Restantes',
    'Tiempo Estimado para llegar a Destino','Estatus de Llegada',
    'Diesel TR','Diesel CR'
);
foreach($cols as $c){
    echo "<th>".htmlspecialchars($c)."</th>";
}
echo "</tr>";
echo "</thead><tbody>";

foreach($rows as $r){
    echo "<tr>";
    foreach($r as $cell){
        echo "<td>".htmlspecialchars($cell)."</td>";
    }
    echo "</tr>";
}

echo "</tbody></table>";
?>
