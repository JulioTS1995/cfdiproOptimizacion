<?php
// remisiones_tracking3.php
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die("Error de conexión a la base de datos.");
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// --- Recibir filtros base ---
$fecha_inicio = isset($_POST["fechai"]) ? $_POST["fechai"] : '';
$fecha_fin    = isset($_POST["fechaf"]) ? $_POST["fechaf"] : '';
$prefijodb_raw= isset($_POST["prefijodb"]) ? $_POST["prefijodb"] : '';
$id_unidad    = isset($_POST["unidad"]) ? (int)$_POST["unidad"] : 0;
$id_operador  = isset($_POST["operador"]) ? (int)$_POST["operador"] : 0;
$sucursal     = isset($_POST["sucursal"]) ? (int)$_POST["sucursal"] : 0;

// Buscador general (solo vive aquí)
$busqueda_raw = isset($_POST["busqueda"]) ? $_POST["busqueda"] : '';
$busqueda_raw = trim($busqueda_raw);

// Validaciones mínimas
if (!$fecha_inicio || !$fecha_fin) {
    $cnx_cfdi3->close();
    die("Faltan fechas de inicio o fin.");
}
if (!$prefijodb_raw) {
    $cnx_cfdi3->close();
    die("Falta el prefijo de la BD");
}

// --- Armar prefijo seguro ---
$prefijodb = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijodb_raw);
if (strpos($prefijodb, '_') === false) {
    $prefijodb .= '_';
}
if (substr($prefijodb, -1) !== '_') {
    $prefijodb .= '_';
}

// Rango de fecha completo
$fecha_inicio_sql = $fecha_inicio . " 00:00:00";
$fecha_fin_sql    = $fecha_fin . " 23:59:59";

// --- Ver si Oficinas tiene Sucursal_RID (para que no truene cuando no exista) ---
$usaSucursal = false;
$colOf = $cnx_cfdi3->query("SHOW COLUMNS FROM {$prefijodb}Oficinas LIKE 'Sucursal_RID'");
if ($colOf && $colOf->num_rows > 0 && $sucursal > 0) {
    $usaSucursal = true;
}

// --- Construir SQL base con JOINs para evitar N consultas por renglón ---
$busqueda = $cnx_cfdi3->real_escape_string($busqueda_raw);
$like     = "%{$busqueda}%";

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
  U.Unidad       AS UnidadNombre,
  UR.Unidad      AS RemolqueNombre,
  O.Operador     AS OperadorNombre,
  C.RazonSocial  AS ClienteNombre,
  Rt.Ruta        AS RutaNombre,
  Rt.Kms         AS RutaKms
FROM {$prefijodb}remisiones R
JOIN {$prefijodb}remisionesestatus2 RE ON RE.FolioEstatus2_RID = R.ID
LEFT JOIN {$prefijodb}unidades   U  ON U.ID  = R.Unidad_RID
LEFT JOIN {$prefijodb}unidades   UR ON UR.ID = R.uRemolqueA_RID
LEFT JOIN {$prefijodb}operadores O  ON O.ID  = R.Operador_RID
LEFT JOIN {$prefijodb}clientes   C  ON C.ID  = R.CargoACliente_RID
LEFT JOIN {$prefijodb}rutas      Rt ON Rt.ID = R.Ruta_RID
WHERE RE.Fecha BETWEEN '{$fecha_inicio_sql}' AND '{$fecha_fin_sql}'
 /*  AND R.FolioEstatus2_RID <> '' */
";

// Sucursal (si aplica y existe la columna)
if ($usaSucursal) {
    $sql .= " AND R.Oficina_RID IN (
                SELECT ID FROM {$prefijodb}Oficinas
                WHERE Sucursal_RID = {$sucursal}
              )";
}

// Filtro por unidad
if ($id_unidad > 0) {
    $sql .= " AND R.Unidad_RID = {$id_unidad}";
}

// Filtro por operador
if ($id_operador > 0) {
    $sql .= " AND R.Operador_RID = {$id_operador}";
}


if ($busqueda_raw !== '') {
    $bEsc = $cnx_cfdi3->real_escape_string($busqueda_raw);
    $likeSql = "'%" . $bEsc . "%'";
    $sql .= "
      AND (
        R.XFolio           LIKE {$likeSql} OR
        IFNULL(U.Unidad,'')       LIKE {$likeSql} OR
        IFNULL(UR.Unidad,'')      LIKE {$likeSql} OR
        IFNULL(O.Operador,'')     LIKE {$likeSql} OR
        IFNULL(C.RazonSocial,'')  LIKE {$likeSql} OR
        IFNULL(Rt.Ruta,'')        LIKE {$likeSql} OR
        IFNULL(RE.Estatus,'')     LIKE {$likeSql} OR
        IFNULL(RE.Comentarios,'') LIKE {$likeSql} OR
        IFNULL(RE.UbicacionUnidad,'') LIKE {$likeSql}
      )
    ";
}

$sql .= " ORDER BY R.XFolio";

$result = $cnx_cfdi3->query($sql);
if (!$result) {
    die("Error al consultar Tracking 3: " . $cnx_cfdi3->error . "<br>SQL: " . htmlspecialchars($sql));
}
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$total_registros = count($rows);

// Formateos de fechas para encabezado
$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t    = date("d-m-Y", strtotime($fecha_fin));

?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Tracking 3 por Viaje</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg:#ffffffff;
      --panel:#ffffffcc;
      --text:#0b0c0f;
      --text-soft:#6b7280;
      --tint:#0a84ff;
      --border:1px solid rgba(15,23,42,.08);
      --shadow:0 10px 28px rgba(15,23,42,.15);
      --radius:18px;
      --row-bg:#ffffffff;
      --row-hover:#f3f4ff;
      --chip:#e5f0ff;
    }
    html[data-theme="dark"]{
      --bg:#020617;
      --panel:#020617;
      --text:#e5e7eb;
      --text-soft:#9ca3af;
      --tint:#38bdf8;
      --border:1px solid rgba(31,41,55,.9);
      --shadow:0 24px 60px rgba(0,0,0,.75);
      --row-bg:#020617;
      --row-hover:#02081f;
      --chip:#0b1220;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:1200px;
      margin:26px auto;
      padding:14px;
    }
    .card{
      background:var(--panel);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      padding:18px 18px 14px;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .header-row{
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      gap:12px;
      margin-bottom:10px;
    }
    .title-block{
      display:flex;
      flex-direction:column;
      gap:4px;
    }
    .title{
      font-size:1.25rem;
      font-weight:700;
      letter-spacing:-0.3px;
    }
    .subtitle{
      font-size:0.9rem;
      color:var(--text-soft);
    }
    .chips{
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      margin-top:4px;
    }
    .chip{
      font-size:0.78rem;
      padding:3px 9px;
      border-radius:999px;
      background:var(--chip);
      color:var(--text-soft);
      border:1px solid rgba(148,163,184,.45);
    }
    .toolbar{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      align-items:center;
      justify-content:flex-end;
    }
    .search-form{
      display:flex;
      align-items:center;
      gap:6px;
    }
    .search-input{
      border-radius:999px;
      border:var(--border);
      padding:6px 10px;
      font-size:0.85rem;
      background:var(--row-bg);
      color:var(--text);
      min-width:220px;
    }
    .btn{
      border-radius:999px;
      padding:7px 14px;
      font-size:0.82rem;
      cursor:pointer;
      border:none;
      display:inline-flex;
      align-items:center;
      gap:6px;
      text-decoration:none;
    }
    .btn-outline{
      border:var(--border);
      background:transparent;
      color:var(--text-soft);
    }
    .btn-pdf{
      background:linear-gradient(180deg,#ef4444,#b91c1c);
      color:#fff;
      box-shadow:0 4px 12px rgba(248,113,113,.45);
    }
    .btn-excel{
      background:linear-gradient(180deg,#22c55e,#15803d);
      color:#fff;
      box-shadow:0 4px 12px rgba(34,197,94,.4);
    }
    .table-wrap{
      margin-top:12px;
      border-radius:14px;
      border:var(--border);
      overflow:auto;
      max-height:520px;
    }
    table{
      border-collapse:collapse;
      width:100%;
      font-size:0.8rem;
      min-width:900px;
    }
    thead{
      position:sticky;
      top:0;
      z-index:5;
      background:var(--row-bg);
    }
    th,td{
      padding:6px 8px;
      border-bottom:1px solid rgba(148,163,184,.35);
      text-align:left;
      white-space:nowrap;
    }
    th{
      font-size:0.75rem;
      text-transform:uppercase;
      letter-spacing:.04em;
      color:var(--text-soft);
    }
    tbody tr:nth-child(even){
      background:var(--row-bg);
    }
    tbody tr:hover{
      background:var(--row-hover);
    }
    .footer-row{
      margin-top:10px;
      font-size:0.82rem;
      color:var(--text-soft);
      display:flex;
      justify-content:space-between;
      flex-wrap:wrap;
      gap:6px;
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="card">
    <div class="header-row">
      <div class="title-block">
        <div class="title">Tracking 3 por Viaje</div>
        <div class="subtitle">Periodo: <?php echo htmlspecialchars($fecha_inicio_t . " - " . $fecha_fin_t); ?></div>
        <div class="chips">
          <div class="chip">Registros: <?php echo $total_registros; ?></div>
          <?php if ($id_unidad > 0): ?>
            <div class="chip">Unidad filtrada</div>
          <?php endif; ?>
          <?php if ($id_operador > 0): ?>
            <div class="chip">Operador filtrado</div>
          <?php endif; ?>
          <?php if ($busqueda_raw !== ''): ?>
            <div class="chip">Filtro: "<?php echo htmlspecialchars($busqueda_raw); ?>"</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="toolbar">
        <!-- Buscador general (SQL) -->
        <form method="post" action="remisiones_tracking3.php" class="search-form">
          <input type="hidden" name="fechai"    value="<?php echo htmlspecialchars($fecha_inicio); ?>">
          <input type="hidden" name="fechaf"    value="<?php echo htmlspecialchars($fecha_fin); ?>">
          <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijodb); ?>">
          <input type="hidden" name="sucursal"  value="<?php echo (int)$sucursal; ?>">
          <input type="hidden" name="unidad"    value="<?php echo (int)$id_unidad; ?>">
          <input type="hidden" name="operador"  value="<?php echo (int)$id_operador; ?>">

          <input
            type="text"
            name="busqueda"
            class="search-input"
            placeholder="Buscar por folio, cliente, operador, ruta..."
            value="<?php echo htmlspecialchars($busqueda_raw); ?>"
          >
          <button type="submit" class="btn btn-outline">Buscar</button>
        </form>

        <!-- Exportar PDF/Excel con MISMO filtro (incluye 'busqueda') -->
        <form method="post" action="remisiones_tracking3_excel_pdf.php" target="_blank">
          <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijodb); ?>">
          <input type="hidden" name="fechai"    value="<?php echo htmlspecialchars($fecha_inicio); ?>">
          <input type="hidden" name="fechaf"    value="<?php echo htmlspecialchars($fecha_fin); ?>">
          <input type="hidden" name="unidad"    value="<?php echo (int)$id_unidad; ?>">
          <input type="hidden" name="operador"  value="<?php echo (int)$id_operador; ?>">
          <input type="hidden" name="sucursal"  value="<?php echo (int)$sucursal; ?>">
          <input type="hidden" name="busqueda"  value="<?php echo htmlspecialchars($busqueda_raw); ?>">

          <button type="submit" name="button" value="PDF" class="btn btn-pdf">PDF</button>
          <button type="submit" name="button" value="Excel" class="btn btn-excel">Excel</button>
        </form>
      </div>
    </div>

    <div class="table-wrap">
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
            <th>Especificaciones Cliente</th>
            <th>Comentarios TR</th>
            <th>Temperatura CR</th>
            <th>Comentarios CR</th>
            <th>Ubicación Unidad</th>
            <th>Kms Restantes</th>
            <th>Tiempo Estimado a Destino</th>
            <th>Estatus Llegada</th>
            <th>Diesel TR</th>
            <th>Diesel CR</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
          // Formateos
          $fecha_hora_salida   = $r['FechaHoraSalida']   && $r['FechaHoraSalida']   >= '1990-01-01 00:00:00'
              ? date("d-m-Y H:i:s", strtotime($r['FechaHoraSalida'])) : '';
          $fecha_hora_llegada  = $r['FechaHoraLlegada']  && $r['FechaHoraLlegada']  >= '1990-01-01 00:00:00'
              ? date("d-m-Y H:i:s", strtotime($r['FechaHoraLlegada'])) : '';
          $cita_fecha          = $r['CitaCarga']         && $r['CitaCarga']         >= '1990-01-01 00:00:00'
              ? date("d-m-Y H:i:s", strtotime($r['CitaCarga'])) : '';
          $fecha_tracking      = $r['Fecha'] && $r['Fecha'] >= '1990-01-01 00:00:00'
              ? date("d-m-Y H:i:s", strtotime($r['Fecha'])) : '';

          $kms_ruta    = $r['RutaKms']       !== null ? number_format((float)$r['RutaKms'],2) : '';
          $km_rest     = $r['KmRestantes']   !== null ? number_format((float)$r['KmRestantes'],2) : '';
          $diesel_tr   = $r['DieselTR']      !== null ? number_format((float)$r['DieselTR'],2) : '';
          $diesel_cr   = $r['DieselCR']      !== null ? number_format((float)$r['DieselCR'],2) : '';
        ?>
          <tr>
            <td><?php echo htmlspecialchars($r['XFolio']); ?></td>
            <td><?php echo htmlspecialchars($r['UnidadNombre']); ?></td>
            <td><?php echo htmlspecialchars($r['RemolqueNombre']); ?></td>
            <td><?php echo htmlspecialchars($r['OperadorNombre']); ?></td>
            <td><?php echo htmlspecialchars($r['RutaNombre']); ?></td>
            <td><?php echo $kms_ruta; ?></td>
            <td><?php echo htmlspecialchars($r['ClienteNombre']); ?></td>
            <td><?php echo $fecha_hora_salida; ?></td>
            <td><?php echo $fecha_hora_llegada; ?></td>
            <td><?php echo htmlspecialchars($r['Estatus']); ?></td>
            <td><?php echo htmlspecialchars($r['TiempoEsperaCargaViaje']); ?></td>
            <td><?php echo $fecha_tracking; ?></td>
            <td><?php echo htmlspecialchars($r['Documentador']); ?></td>
            <td><?php echo $cita_fecha; ?></td>
            <td><?php echo htmlspecialchars($r['Instrucciones']); ?></td>
            <td><?php echo htmlspecialchars($r['Comentarios']); ?></td>
            <td><?php echo htmlspecialchars($r['TemperaturaCR']); ?></td>
            <td><?php echo htmlspecialchars($r['ComentariosCR']); ?></td>
            <td><?php echo htmlspecialchars($r['UbicacionUnidad']); ?></td>
            <td><?php echo $km_rest; ?></td>
            <td><?php echo htmlspecialchars($r['TiempoEstimadoLlegadaDestino']); ?></td>
            <td><?php echo htmlspecialchars($r['EstatusLlegada']); ?></td>
            <td><?php echo $diesel_tr; ?></td>
            <td><?php echo $diesel_cr; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="footer-row">
      <div>Mostrando <?php echo $total_registros; ?> registros.</div>
      <div>Los filtros aplicados se respetan también en PDF y Excel.</div>
    </div>
  </div>
</div>
</body>
</html>
<?php
$cnx_cfdi3->close();
?>
