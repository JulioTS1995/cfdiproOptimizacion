<?php
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
$cnx_cfdi3->set_charset("utf8");

// Validar prefijo
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
$prefijobd_raw = $_GET['prefijodb'];
$prefijobd = mysqli_real_escape_string($cnx_cfdi3, $prefijobd_raw);

// Asegurar guion bajo al final
if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

// ----- Paginacion -----
$per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;


$baseQuery = "
    FROM {$prefijobd}Remisiones r
    INNER JOIN {$prefijobd}Unidades u ON r.Unidad_RID = u.ID
    INNER JOIN {$prefijobd}Clientes c ON r.CargoACliente_RID = c.ID
    INNER JOIN {$prefijobd}Operadores o ON r.Operador_RID = o.ID
    INNER JOIN (
        SELECT Unidad_RID, MAX(Creado) AS last_created
        FROM {$prefijobd}Remisiones
        GROUP BY Unidad_RID
    ) lr ON lr.Unidad_RID = r.Unidad_RID AND lr.last_created = r.Creado
";


$baseQuery = str_replace('INNERJOIN', 'INNER JOIN', $baseQuery);

// Total de filas 
$countSql = "SELECT COUNT(*) AS total " . $baseQuery;
$countRes = $cnx_cfdi3->query($countSql);
if (!$countRes) {
    die("Error en conteo: " . $cnx_cfdi3->error);
}
$countRow   = $countRes->fetch_assoc();
$total_rows = intval($countRow['total']);
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;

// Datos paginados
$dataSql = "
    SELECT 
        r.XFolio,
        r.EstatusTerminadoT,
        u.Unidad,
        r.Destinatario,
        c.RazonSocial,
        o.Operador
    " . $baseQuery . "
    ORDER BY u.Unidad ASC
    LIMIT {$per_page} OFFSET {$offset}
";
$dataRes = $cnx_cfdi3->query($dataSql);
if (!$dataRes) {
    die("Error en consulta principal: " . $cnx_cfdi3->error);
}

$rows = array();
$statusCounts = array();
while ($row = $dataRes->fetch_assoc()) {
    $rows[] = $row;
    $status = trim($row['EstatusTerminadoT']) === '' ? 'SIN ESTATUS' : trim($row['EstatusTerminadoT']);
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;
}

?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Estatus Unidades - Último Movimiento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">

  <!-- Chart.js para métricas  -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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
      --row-bg: #ffffff;
      --row-hover: #f1f4fb;
      --header-bg: rgba(221, 221, 221, 0.72);
    }

    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow: 0 8px 24px rgba(0,0,0,.35);
      --border: 1px solid rgba(255,255,255,.06);
      --row-bg:#11141c;
      --row-hover:#1a2030;
      --header-bg: rgba(20,24,36,.7);
    }

    *{box-sizing:border-box;}

    body{
      margin:0;
      font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
      background:var(--bg);
      color:var(--text);
    }

    .container{
      max-width:1700px;
      margin:32px auto;
      padding:0 20px 32px;
    }

    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:20px;
      flex-wrap:wrap;
    }

    .header-title h1{
      margin:0;
      font-size:1.7rem;
      letter-spacing:-0.4px;
    }
    .header-title small{
      display:block;
      margin-top:4px;
      color:var(--text-soft);
      font-size:0.85rem;
    }

    /* Botón de tema global tipo pastilla */
    .btn-theme{
      display:inline-flex;
      align-items:center;
      gap:6px;
      border:var(--border);
      background:var(--panel);
      color:var(--text);
      padding:8px 14px;
      border-radius:999px;
      font-weight:600;
      cursor:pointer;
      box-shadow:0 2px 8px rgba(0,0,0,.06);
      transition:.2s ease;
      font-size:0.9rem;
    }
    .btn-theme:hover{ transform:translateY(-1px); }

    .btn-theme span.icon{
      font-size:1rem;
    }

    .layout{
      display:grid;
      grid-template-columns: minmax(0,2.5fr) minmax(0,1.3fr);
      gap:18px;
    }
    @media (max-width:1100px){
      .layout{
        grid-template-columns: minmax(0,1fr);
      }
    }

    .panel{
      background:var(--panel);
      backdrop-filter: blur(18px) saturate(1.2);
      -webkit-backdrop-filter: blur(18px) saturate(1.2);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
    }

    .panel-header{
      padding:14px 18px 8px;
      display:flex;
      align-items:baseline;
      justify-content:space-between;
      gap:8px;
      border-bottom:var(--border);
    }
    .panel-header h2{
      margin:0;
      font-size:1rem;
    }
    .panel-header small{
      color:var(--text-soft);
      font-size:0.8rem;
    }

    .tag{
      display:inline-flex;
      align-items:center;
      padding:4px 10px;
      border-radius:999px;
      font-size:0.75rem;
      border:var(--border);
      background:rgba(0,0,0,.02);
      color:var(--text-soft);
      gap:6px;
    }

    .table-container{
      max-height:650px;
      overflow:auto;
    }

    table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      font-size:0.85rem;
    }

    thead th{
      position:sticky;
      top:0;
      z-index:2;
      background:var(--header-bg);
      backdrop-filter:blur(12px);
      padding:10px 8px;
      text-align:center;
      font-weight:600;
      font-size:0.78rem;
      color:var(--text-soft);
      border-bottom:var(--border);
    }

    tbody td{
      padding:9px 8px;
      border-bottom:1px solid rgba(0,0,0,.04);
      background:var(--row-bg);
      text-align:left;
      transition:background .2s;
      white-space:nowrap;
    }
    tbody td.text-center{
      text-align:center;
    }
    tbody td.text-right{
      text-align:right;
    }
    tbody td.wrap{
      white-space:normal;
    }

    tbody tr:hover td{
      background:var(--row-hover);
    }

    .status-pill{
      display:inline-flex;
      align-items:center;
      padding:3px 10px;
      border-radius:999px;
      font-size:0.75rem;
      border:1px solid rgba(0,0,0,.08);
    }
    .status-pill.ok{
      background:rgba(52,199,89,.12);
      color:#15803d;
      border-color:rgba(52,199,89,.4);
    }
    .status-pill.pending{
      background:rgba(255,204,0,.12);
      color:#92400e;
      border-color:rgba(255,204,0,.4);
    }
    .status-pill.other{
      background:rgba(0,122,255,.10);
      color:#0b5394;
      border-color:rgba(0,122,255,.35);
    }

    .pagination{
      padding:10px 16px 16px;
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      justify-content:center;
    }
    .pagination a{
      text-decoration:none;
      padding:5px 11px;
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      color:var(--text);
      font-size:0.8rem;
      font-weight:600;
      transition:.15s;
    }
    .pagination a:hover{
      background:var(--row-hover);
    }
    .pagination .active{
      background:var(--tint);
      color:#fff;
      border:none;
    }

    .panel-body{
      padding:14px 18px 16px;
    }

    .metrics-grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:10px;
      margin-bottom:12px;
    }
    @media (max-width:700px){
      .metrics-grid{
        grid-template-columns:minmax(0,1fr);
      }
    }

    .metric-card{
      border-radius:12px;
      border:var(--border);
      padding:10px 12px;
      background:rgba(255,255,255,.35);
    }
    html[data-theme="dark"] .metric-card{
      background:rgba(15,18,24,.9);
    }
    .metric-label{
      font-size:0.78rem;
      color:var(--text-soft);
      margin-bottom:4px;
    }
    .metric-value{
      font-size:1.2rem;
      font-weight:700;
    }
    .metric-sub{
      font-size:0.75rem;
      color:var(--text-soft);
      margin-top:2px;
    }

    .chart-wrapper{
      margin-top:6px;
    }

    .actions-row{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      justify-content:flex-end;
      margin-bottom:8px;
    }

    .btn{
      appearance:none;
      border:none;
      border-radius:999px;
      padding:7px 12px;
      font-size:0.8rem;
      font-weight:600;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:6px;
      transition:.18s;
    }
    .btn-primary{
      background:linear-gradient(180deg,#0a84ff,#007aff);
      color:#fff;
      box-shadow:0 6px 16px rgba(10,132,255,.35);
    }
    .btn-primary:hover{
      opacity:.95;
      transform:translateY(-1px);
    }
    .btn-outline{
      background:transparent;
      border:var(--border);
      color:var(--text);
      background:var(--panel);
    }
    .btn-outline:hover{
      background:var(--row-hover);
    }

  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="header-title">
      <h1>Estatus Unidades – Último Movimiento</h1>
      <small>
       
        Total de viajes: <strong><?php echo $total_rows; ?></strong>
      </small>
    </div>
    <button id="themeToggle" class="btn-theme" aria-label="Cambiar tema">
      <span class="icon">🌓</span>
      <span class="label">Tema</span>
    </button>
  </div>

  <div class="layout">

   
    <div class="panel">
      <div class="panel-header">
        <div>
          <h2>Último movimiento por unidad</h2>
         
        </div>
        <div>
        
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Folio</th>
              <th>Estatus</th>
              <th>Unidad</th>
              <th>Destino</th>
              <th>Cliente</th>
              <th>Operador</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="6" class="text-center" style="padding:18px;">
                No se encontraron movimientos para las unidades.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach($rows as $r): 
              $estatus = trim($r['EstatusTerminadoT']) === '' ? 'SIN ESTATUS' : trim($r['EstatusTerminadoT']);
              $cls = 'other';
              if (stripos($estatus,'Terminado') !== false) $cls = 'ok';
              elseif (stripos($estatus,'Transito') !== false || stripos($estatus,'En curso') !== false) $cls = 'pending';
            ?>
              <tr>
                <td class="text-center"><?php echo htmlspecialchars($r['XFolio'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-center">
                  <span class="status-pill <?php echo $cls; ?>">
                    <?php echo htmlspecialchars($estatus, ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </td>
                <td class="text-center"><?php echo htmlspecialchars($r['Unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="wrap"><?php echo htmlspecialchars($r['Destinatario'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="wrap"><?php echo htmlspecialchars($r['RazonSocial'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="wrap"><?php echo htmlspecialchars($r['Operador'], ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php
          $baseUrl = 'logistica.php?prefijodb=' . urlencode($prefijobd_raw);
        ?>
        <?php if ($page > 1): ?>
          <a href="<?php echo $baseUrl.'&page='.($page-1); ?>">‹ Anterior</a>
        <?php endif; ?>

        <?php for($i=1; $i<=$total_pages; $i++): ?>
          <a href="<?php echo $baseUrl.'&page='.$i; ?>"
             class="<?php echo ($i==$page?'active':''); ?>">
             <?php echo $i; ?>
          </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <a href="<?php echo $baseUrl.'&page='.($page+1); ?>">Siguiente ›</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- PANEL METRICAS / EXPORTS -->
    <div class="panel">
      <div class="panel-header">
        <div>
          <h2>Resumen logístico</h2>
          <small>Distribución de estatus y acciones rápidas.</small>
        </div>
      </div>
      <div class="panel-body">
        <?php
          $totalEnPagina = count($rows);
          $totalTerminado = isset($statusCounts['Terminado']) ? $statusCounts['Terminado'] : 0;
          $totalSinEstatus = isset($statusCounts['SIN ESTATUS']) ? $statusCounts['SIN ESTATUS'] : 0;
        ?>
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-label">Unidades en esta página</div>
            <div class="metric-value"><?php echo $totalEnPagina; ?></div>
            <div class="metric-sub">De <?php echo $total_rows; ?> con movimiento registrado.</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Con estatus "Terminado"</div>
            <div class="metric-value"><?php echo $totalTerminado; ?></div>
            <div class="metric-sub">
              <?php
              $pct = ($totalEnPagina>0) ? round(($totalTerminado/$totalEnPagina)*100,1) : 0;
              echo $pct; ?> % de la página.
            </div>
          </div>
        </div>

        <div class="chart-wrapper">
          <canvas id="statusChart" height="170"></canvas>
        </div>

        <div class="actions-row" style="margin-top:14px;">
          <a href="logistica_excel.php?prefijodb=<?php echo urlencode($prefijobd_raw); ?>" class="btn btn-outline">
            📊 Exportar a Excel
          </a>
          <a href="logistica_pdf.php?prefijodb=<?php echo urlencode($prefijobd_raw); ?>" class="btn btn-primary">
            📄 Exportar a PDF
          </a>
        </div>
      </div>
    </div>

  </div><!-- /layout -->
</div>

<script>
// THEME GLOBAL (igual que en los otros reports)
(function(){
  var root = document.documentElement;
  var key  = 'ui-theme';
  var saved = localStorage.getItem(key);

  if(saved === 'light' || saved === 'dark'){
    root.setAttribute('data-theme', saved);
  }else{
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
  }

  function syncBtnLabel(){
    var isDark = root.getAttribute('data-theme') === 'dark';
    var btn = document.getElementById('themeToggle');
    if(!btn) return;
    var label = btn.querySelector('.label');
    var icon  = btn.querySelector('.icon');
    if(label){
      label.textContent = isDark ? 'Tema' : 'Tema';
    }
    if(icon){
      icon.textContent = isDark ? '🌙' : '☀️';
    }
  }
  syncBtnLabel();

  var btn = document.getElementById('themeToggle');
  if(btn){
    btn.addEventListener('click', function(){
      var current = root.getAttribute('data-theme') || 'light';
      var next = (current === 'light') ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      localStorage.setItem(key, next);
      syncBtnLabel();
      window.dispatchEvent(new StorageEvent('storage', {key:key,newValue:next}));
    });
  }

  if(!saved && window.matchMedia){
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e){
      root.setAttribute('data-theme', e.matches ? 'dark':'light');
      syncBtnLabel();
    });
  }

  // Si está en iframe, se podría ocultar el botón local y desactuvamos y jala
  // if (window.self !== window.top && btn) btn.style.display = 'none';
})();

// CHART: Distribución de estatus
(function(){
  var ctx = document.getElementById('statusChart');
  if(!ctx) return;

  var data = <?php echo json_encode($statusCounts); ?>;
  var labels = Object.keys(data);
  var values = labels.map(function(k){ return data[k]; });

  if(labels.length === 0){
    ctx.parentNode.innerHTML = '<div style="font-size:0.8rem;color:var(--text-soft);margin-top:8px;">Sin datos suficientes para graficar estatus.</div>';
    return;
  }

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Unidades por estatus',
        data: values
      }]
    },
    options: {
      responsive:true,
      maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{enabled:true}
      },
      scales:{
        x:{ticks:{font:{size:10}}},
        y:{beginAtZero:true, ticks:{stepSize:1}}
      }
    }
  });
})();
</script>
</body>
</html>
