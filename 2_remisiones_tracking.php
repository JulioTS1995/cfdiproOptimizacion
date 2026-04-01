<?php 

// Obtener Fechas y parámetros
$fecha_inicio = isset($_POST["fechai"]) ? $_POST["fechai"] : '';
$fecha_fin    = isset($_POST["fechaf"]) ? $_POST["fechaf"] : '';
$prefijobd    = isset($_POST["base"])   ? $_POST["base"]   : '';
$sucursal     = isset($_POST["sucursal"]) ? trim($_POST["sucursal"]) : '';

// Validaciones básicas
if (!$fecha_inicio || !$fecha_fin || !$prefijobd) {
    die("Faltan parámetros obligatorios.");
}

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// ==== LÓGICA ORIGINAL, PORTADA A MYSQLI ====

// 1) Buscar IDs únicos de remisiones en remisionesestatus2
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

// Arreglo para filas finales
$rows = [];

// Rango de fechas como en el original
$fi  = $fecha_inicio;
$ff  = $fecha_fin;
$fi2 = date("Y-m-d H:i:s", strtotime($fi));
$ff2 = date("Y-m-d H:i:s", strtotime($ff));
$nuevafecha_fin_ts = strtotime('+23 hour +59 minute +59 second', strtotime($ff2));
$nuevafecha_fin    = date('Y-m-d H:i:s', $nuevafecha_fin_ts);

while ($rowId = $resIds->fetch_assoc()) {
    $id_remision = (int)$rowId['FolioEstatus2_RID'];
    if ($id_remision <= 0) continue;

    // 2) Buscar datos de la remisión + unidad, con opción de sucursal
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

        // Remolque
        $nom_remolque = '';
        if ($remolque_id > 0) {
            $sql6 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$remolque_id;
            $res6 = mysqli_query($cnx_cfdi3, $sql6);
            $row6 = $res6 ? mysqli_fetch_assoc($res6) : null;
            $nom_remolque = $row6 ? $row6['Unidad'] : '';
        }

        // Operador
        $nom_operador = '';
        if ($operador_id > 0) {
            $sql7 = "SELECT Operador FROM {$prefijobd}operadores WHERE ID = ".$operador_id;
            $res7 = mysqli_query($cnx_cfdi3, $sql7);
            $row7 = $res7 ? mysqli_fetch_assoc($res7) : null;
            $nom_operador = $row7 ? $row7['Operador'] : '';
        }

        // Cliente
        $nom_cliente = '';
        if ($cliente_id > 0) {
            $sql8 = "SELECT RazonSocial FROM {$prefijobd}clientes WHERE ID = ".$cliente_id;
            $res8 = mysqli_query($cnx_cfdi3, $sql8);
            $row8 = $res8 ? mysqli_fetch_assoc($res8) : null;
            $nom_cliente = $row8 ? $row8['RazonSocial'] : '';
        }

        // Unidad
        $nom_unidad = '';
        if ($unidad > 0) {
            $sql2 = "SELECT Unidad FROM {$prefijobd}unidades WHERE ID = ".$unidad;
            $res2 = mysqli_query($cnx_cfdi3, $sql2);
            $row2 = $res2 ? mysqli_fetch_assoc($res2) : null;
            $nom_unidad = $row2 ? $row2['Unidad'] : '';
        }

        // Ruta
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

        // Último tracking
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

        // Rango de fecha
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

$registros      = count($rows);
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Listado Tracking por Remisión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Refresh" content="300" />

  <style>
    :root{
      --bg: #ffffffff;
      --panel: #ffffffcc;
      --text: #0b0c0f;
      --text-soft: #5c6270;
      --tint: #0a84ff;
      --radius: 16px;
      --shadow: 0 8px 24px rgba(0,0,0,.08);
      --border: 1px solid rgba(10,12,16,.08);
      --row-bg: #ffffffff;
      --row-hover: #f1f4fb;
      --header-bg: rgba(221,221,221,0.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow: 0 8px 24px rgba(0,0,0,.35);
      --border: 1px solid rgba(255,255,255,.06);
      --row-bg:#11141d;
      --row-hover:#1a2030;
      --header-bg: rgba(20,24,36,.7);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:1400px;
      margin:24px auto;
      padding:12px;
    }
    .app-header{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
      margin-bottom:16px;
    }
    .app-title{
      font-size:1.6rem;
      font-weight:700;
      letter-spacing:-0.4px;
    }
    .app-subtitle{
      font-size:0.85rem;
      color:var(--text-soft);
    }
    .badge{
      display:inline-flex;
      padding:2px 8px;
      border-radius:999px;
      font-size:0.75rem;
      border:var(--border);
      margin-top:4px;
      background:rgba(255,255,255,.4);
    }
    html[data-theme="dark"] .badge{
      background:rgba(10,10,15,.6);
    }
    .btn.theme-toggle{
      display:inline-flex;
      align-items:center;
      gap:6px;
      border:var(--border);
      background:var(--panel);
      color:var(--text);
      padding:8px 12px;
      border-radius:999px;
      font-weight:600;
      cursor:pointer;
      box-shadow:0 2px 8px rgba(0,0,0,.06);
      transition:.2s;
      font-size:0.85rem;
    }
    .btn.theme-toggle:hover{ transform:translateY(-1px); }

    .panel{
      background:var(--panel);
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .panel-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:12px 16px 8px;
      border-bottom:var(--border);
      gap:10px;
      flex-wrap:wrap;
      background:linear-gradient(180deg,rgba(255,255,255,.9),rgba(255,255,255,.4));
    }
    html[data-theme="dark"] .panel-header{
      background:linear-gradient(180deg,rgba(20,24,36,.95),rgba(15,18,26,.8));
    }
    .panel-header-left{
      font-size:0.85rem;
      color:var(--text-soft);
    }
    .panel-header-left strong{
      color:var(--text);
    }
    .panel-header-right{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
    }
    .btn-sm{
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      padding:6px 12px;
      font-size:0.8rem;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:6px;
      color:var(--text);
      text-decoration:none;
    }
    .btn-sm:hover{ background:var(--row-hover); }

    .table-container{
      max-height:620px;
      overflow-y:auto;
    }
    table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      font-size:0.8rem;
    }
    thead th{
      position:sticky;
      top:0;
      z-index:5;
      background:var(--header-bg);
      font-weight:600;
      padding:8px 6px;
      text-align:center;
      color:var(--text-soft);
      border-bottom:var(--border);
      backdrop-filter:blur(10px);
      white-space:nowrap;
    }
    tbody td{
      padding:8px 6px;
      border-bottom:1px solid rgba(0,0,0,.04);
      background:var(--row-bg);
      text-align:left;
      white-space:nowrap;
    }
    tbody tr:hover td{ background:var(--row-hover); }
    td.num{
      text-align:right;
      font-variant-numeric:tabular-nums;
    }

    .pagination{
      display:flex;
      justify-content:center;
      gap:6px;
      padding:10px 10px 14px;
      flex-wrap:wrap;
      border-top:var(--border);
      background:linear-gradient(180deg,rgba(255,255,255,.7),rgba(255,255,255,.9));
    }
    html[data-theme="dark"] .pagination{
      background:linear-gradient(180deg,rgba(15,18,26,.95),rgba(10,12,18,.9));
    }
    .pagination button{
      min-width:32px;
      padding:4px 8px;
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      cursor:pointer;
      font-size:0.8rem;
      color:var(--text);
    }
    .pagination button.active{
      background:var(--tint);
      color:#fff;
      border:none;
    }
    .pagination button:hover{ background:var(--row-hover); }
    .pagination button.disabled{
      opacity:.4;
      cursor:default;
    }

    @media (max-width:900px){
      .app-shell{ margin:20px auto; padding:12px; }
      table{ font-size:0.75rem; }
    }
    @media (max-width:600px){
      .app-header{ flex-direction:column; }
      .panel-header{ flex-direction:column; align-items:flex-start; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="app-header">
    <div>
      <div class="app-title">Último Tracking por Viaje</div>
      <div class="app-subtitle">
        Periodo: <?php echo htmlspecialchars($fecha_inicio_f); ?> a <?php echo htmlspecialchars($fecha_fin_f); ?>
        <?php if($sucursal !== ''): ?>
          · Sucursal: <?php echo htmlspecialchars($sucursal); ?>
        <?php endif; ?>
      </div>
      <div class="badge">
        <?php echo $registros; ?> viajes encontrados
      </div>
    </div>
    <button id="themeToggle" class="btn theme-toggle" aria-label="Cambiar tema">
      <span class="sun">☀️ Claro</span>
      <span class="moon" style="display:none;">🌙 Oscuro</span>
    </button>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-header-left">
        <strong><?php echo $registros; ?></strong> registros
        <?php if($sucursal !== ''): ?>
          · Filtrado por sucursal
        <?php else: ?>
          · Todas las sucursales
        <?php endif; ?>
      </div>
      <div class="panel-header-right">
        <!-- Enviar al email (igual que antes, pero con estilo nuevo) -->
        <form method="post" action="2_remisiones_tracking_notificacion_mail.php" target="_blank" enctype="multipart/form-data">
          <input type="hidden" name="base"   value="<?php echo htmlspecialchars($prefijobd); ?>">
          <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
          <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fecha_fin); ?>">
          <?php if($sucursal !== ''): ?>
            <input type="hidden" name="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>">
          <?php endif; ?>
          <button type="submit" class="btn-sm">📧 Enviar al Email</button>
        </form>

        <!-- Exportar a Excel -->
        <form method="post" action="2_remisiones_tracking_excel.php" enctype="multipart/form-data">
          <input type="hidden" name="base"   value="<?php echo htmlspecialchars($prefijobd); ?>">
          <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
          <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fecha_fin); ?>">
          <?php if($sucursal !== ''): ?>
            <input type="hidden" name="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>">
          <?php endif; ?>
          <button type="submit" class="btn-sm">📊 Excel</button>
        </form>

        <!-- Exportar a PDF -->
        <form method="post" action="2_remisiones_tracking_pdf.php"  enctype="multipart/form-data">
          <input type="hidden" name="base"   value="<?php echo htmlspecialchars($prefijobd); ?>">
          <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
          <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fecha_fin); ?>">
          <?php if($sucursal !== ''): ?>
            <input type="hidden" name="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>">
          <?php endif; ?>
          <button type="submit" class="btn-sm">📄 PDF</button>
        </form>

        <a class="btn-sm"
           href="2_remisiones_tracking_fechas.php?prefijodb=<?php echo urlencode(rtrim($prefijobd,'_')); ?>">
          ⬅︎ Cambiar filtros
        </a>
      </div>
    </div>

    <div class="table-container">
      <table id="tablaReporte">
        <thead>
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
            <th>Tiempo en Espera Carga/Viaje</th>
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
          <?php foreach($rows as $r): ?>
            <tr>
              <?php foreach($r as $idx => $cell): ?>
                <?php
                  $isNum = in_array($idx, [5,10,16,19,22,23], true);
                ?>
                <td class="<?php echo $isNum ? 'num' : ''; ?>">
                  <?php echo htmlspecialchars($cell); ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination" id="pagination"></div>
  </div>
</div>

<script>
// Tema global
(function(){
  var root = document.documentElement;
  var key  = 'ui-theme';
  var saved = localStorage.getItem(key);

  if(saved === 'light' || saved === 'dark'){
    root.setAttribute('data-theme', saved);
  } else {
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
  }

  function syncIcons(){
    var isDark = root.getAttribute('data-theme') === 'dark';
    var sun = document.querySelector('#themeToggle .sun');
    var moon = document.querySelector('#themeToggle .moon');
    if(sun && moon){
      sun.style.display  = isDark ? 'none'  : 'inline';
      moon.style.display = isDark ? 'inline': 'none';
    }
  }
  syncIcons();

  var btn = document.getElementById('themeToggle');
  if(btn){
    btn.addEventListener('click', function(){
      var current = root.getAttribute('data-theme') || 'light';
      var next    = (current === 'light') ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      localStorage.setItem(key, next);
      syncIcons();
    });
  }

  if (window.self !== window.top) {
    if (btn) btn.style.display = 'none';
  }

  window.addEventListener('storage', function(e){
    if(e.key === key && (e.newValue === 'light' || e.newValue === 'dark')){
      root.setAttribute('data-theme', e.newValue);
      syncIcons();
    }
  });
})();

// Paginación 10 en 10
(function(){
  var table = document.getElementById('tablaReporte');
  if(!table) return;
  var tbody = table.querySelector('tbody');
  var rows  = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
  var perPage = 10;
  var currentPage = 1;
  var totalPages = Math.ceil(rows.length / perPage) || 1;
  var pagContainer = document.getElementById('pagination');

  function renderPage(page){
    currentPage = page;
    var start = (page - 1) * perPage;
    var end   = start + perPage;

    for(var i=0;i<rows.length;i++){
      if(i >= start && i < end){
        rows[i].style.display = '';
      }else{
        rows[i].style.display = 'none';
      }
    }
    renderPagination();
  }

  function createBtn(label, page, disabled, active){
    var btn = document.createElement('button');
    btn.textContent = label;
    if(disabled) btn.classList.add('disabled');
    if(active)   btn.classList.add('active');
    btn.disabled = !!disabled;
    if(!disabled){
      btn.addEventListener('click', function(){
        renderPage(page);
      });
    }
    return btn;
  }

  function renderPagination(){
    pagContainer.innerHTML = '';
    if(totalPages <= 1) return;

    pagContainer.appendChild(
      createBtn('‹', currentPage-1, currentPage === 1, false)
    );

    for(var p=1;p<=totalPages;p++){
      pagContainer.appendChild(
        createBtn(String(p), p, false, p === currentPage)
      );
    }

    pagContainer.appendChild(
      createBtn('›', currentPage+1, currentPage === totalPages, false)
    );
  }

  renderPage(1);
})();
</script>
</body>
</html>
