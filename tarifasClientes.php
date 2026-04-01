<?php
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");

$prefijobd = isset($_POST["base"]) ? $_POST["base"] : (isset($_GET['base']) ? $_GET['base'] : '');
$prefijobd = @mysql_escape_string($prefijobd);

// filtros
$Cliente = isset($_POST['Cliente']) ? intval($_POST['Cliente']) : (isset($_GET['Cliente']) ? intval($_GET['Cliente']) : 0);
$Ruta    = isset($_POST['Ruta'])    ? intval($_POST['Ruta'])    : (isset($_GET['Ruta'])    ? intval($_GET['Ruta'])    : 0);
$Clase   = isset($_POST['Clase'])   ? intval($_POST['Clase'])   : (isset($_GET['Clase'])   ? intval($_GET['Clase'])   : 0);

$Wextra = "";
if ($Cliente > 0) $Wextra .= " AND T.FolioTarifas_RID = ".$Cliente;
if ($Ruta > 0)    $Wextra .= " AND T.Ruta_RID = ".$Ruta;
if ($Clase > 0)   $Wextra .= " AND T.Clase_RID = ".$Clase;

// paginación
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// conteo total
$count_q = "SELECT COUNT(*) AS total
FROM ".$prefijobd."clientestarifas T
INNER JOIN ".$prefijobd."clientestarifaspartidas P ON P.FolioSub_RID = T.ID
WHERE 1=1 ".$Wextra;
$count_r = mysql_query($count_q, $cnx_cfdi);
$total_rows = 0;
if ($count_r) { $count_row = mysql_fetch_assoc($count_r); $total_rows = intval($count_row['total']); }
$total_pages = max(1, ceil($total_rows / $per_page));

// consulta paginada
$resSQL = "
SELECT 
  T.FolioTarifas_RID, T.Ruta_RID, T.Clase_RID,
  P.conceptopartida, P.tipo, P.preciounitario, P.Importe
FROM ".$prefijobd."clientestarifas T
INNER JOIN ".$prefijobd."clientestarifaspartidas P ON P.FolioSub_RID = T.ID
WHERE 1=1 ".$Wextra."
ORDER BY T.FolioTarifas_RID DESC
LIMIT ".$per_page." OFFSET ".$offset;
$runSQL = mysql_query($resSQL, $cnx_cfdi);

// helpers
function fetchOne($sql, $field, $cnx){ $r = mysql_query($sql, $cnx); if($r){ $f = mysql_fetch_assoc($r); return $f ? $f[$field] : ''; } return ''; }

$clienteNom = $Cliente>0 ? fetchOne("SELECT RazonSocial AS n FROM ".$prefijobd."clientes WHERE ID=".$Cliente, 'n', $cnx_cfdi) : 'Todos los clientes';
$rutaNom    = $Ruta>0    ? fetchOne("SELECT Ruta AS n FROM ".$prefijobd."rutas WHERE ID=".$Ruta, 'n', $cnx_cfdi) : 'Todas las rutas';
$claseNom   = $Clase>0   ? fetchOne("SELECT Clase AS n FROM ".$prefijobd."unidadesclase WHERE ID=".$Clase, 'n', $cnx_cfdi) : 'Todas las clases';

$embed = !empty($_GET['embed']);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Tarifas de Clientes · Resultados</title>

  <script>
    (function(){ // anti-flash tema
      var k='ui-theme', s=localStorage.getItem(k);
      if(s==='light'||s==='dark'){ document.documentElement.setAttribute('data-theme',s); }
      else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        document.documentElement.setAttribute('data-theme','dark');
      } else { document.documentElement.setAttribute('data-theme','light'); }
    })();
  </script>

  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    :root{
      --bg:#ffffffff; --panel:#ffffffcc; --text:#0b0c0f; --text-soft:#5c6270; --tint:#0a84ff;
      --radius:16px; --shadow:0 8px 24px rgba(0,0,0,.08); --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff; --row-hover:#f1f4fb; --header-bg:rgba(255,255,255,.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f; --panel:#0f1218cc; --text:#f5f7fb; --text-soft:#a6aec2; --border:1px solid rgba(255,255,255,.06);
      --row-bg:#141824; --row-hover:#1a2030; --header-bg:rgba(20,24,36,.7); --shadow:0 8px 24px rgba(0,0,0,.35);
    }
    body{margin:0; font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; background:var(--bg); color:var(--text);}
    .container{max-width:1400px; margin:40px auto; padding:20px;}

    .header{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap;}
    .header h1{margin:0; font-size:1.8rem; font-weight:700; letter-spacing:-.5px;}
    .btn-theme{border:none; padding:8px 14px; border-radius:999px; font-weight:700; background:linear-gradient(180deg,var(--tint), #3373b8ff); color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25); display:inline-flex; gap:8px; align-items:center;}

    /* === Layout 2 columnas === */
    .layout{
      display:grid;
      grid-template-columns: 2fr 1fr;
      gap:16px;
    }
    @media(max-width:1100px){
      .layout{ grid-template-columns: 1fr; }
    }

    /* Panel izquierdo (tabla) */
    .panel{
      background:var(--panel); border-radius:var(--radius); border:var(--border); box-shadow:var(--shadow); overflow:hidden;
    }
    .panel-head{display:flex; align-items:center; justify-content:space-between; gap:8px; padding:14px 16px; flex-wrap:wrap;}
    .meta{color:var(--text-soft); font-weight:600;}
    .filters-inline{display:flex; gap:10px; align-items:center; flex-wrap:wrap;}
    .btn{border:none; padding:8px 14px; border-radius:999px; font-weight:700; cursor:pointer;}
    .btn.primary{background:linear-gradient(180deg,var(--tint), #007aff); color:#fff;}
    .btn.ghost{background:var(--panel); color:var(--text); border:var(--border);}
    .search{padding:10px 12px; border-radius:999px; border:var(--border); background:var(--row-bg); color:var(--text); font-weight:600; min-width:240px;}

    .table-container{max-height:62vh; overflow:auto; border-top:var(--border);}
    table{width:100%; border-collapse:separate; font-size:.9rem;}
    thead th{position:sticky; top:0; background:var(--header-bg); font-weight:600; padding:12px; text-align:center; font-size:.8rem; color:var(--text-soft); border-bottom:var(--border); backdrop-filter:blur(10px);}
    tbody td{padding:12px; text-align:center; background:var(--row-bg); border-bottom:1px solid rgba(0,0,0,.05);}
    tbody tr:hover td{background:var(--row-hover);}

    .footer-row{padding:12px 16px; display:flex; gap:20px; align-items:center; justify-content:flex-end;}
    .totals{font-weight:700; color:var(--text);}

    .pagination{margin:16px 0; display:flex; gap:6px; justify-content:center; flex-wrap:wrap;}
    .pagination a{padding:6px 12px; border-radius:999px; border:var(--border); background:var(--panel); text-decoration:none; color:var(--text); font-weight:700;}
    .pagination a:hover{background:var(--row-hover);}
    .pagination a.active{background:var(--tint); color:#fff; border:none;}

    /* Panel derecho (gráficas) */
    .sidebar{
      position: sticky;
      top: 20px;              /* espacio bajo el header de la página */
      height: calc(100vh - 100px);
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .card{
      background:var(--panel); border:var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:12px;
      display:flex; flex-direction:column; min-height: 200px;
    }
    .card h3{margin:0 0 8px 0; font-size:1rem; color:var(--text-soft);}
    .card .canvas-wrap{flex:1; min-height:180px;}
    canvas{width:100% !important; height:100% !important;}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Tarifas de Clientes</h1>
      <?php if (empty($_GET['embed'])): ?>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
      <?php endif; ?>
    </div>

    <div class="layout">
      <!-- ===== Columna izquierda: Tabla ===== -->
      <div class="left">
        <div class="panel">
          <div class="panel-head">
            <div class="meta">
              <strong>Filtros:</strong>
              <span><?php echo htmlspecialchars($clienteNom); ?></span> ·
              <span><?php echo htmlspecialchars($rutaNom); ?></span> ·
              <span><?php echo htmlspecialchars($claseNom); ?></span>
            </div>
            <div class="filters-inline">
              <input id="search" class="search" type="text" placeholder="Buscar en tabla..." />
              <?php
                $exportBase = 'Cliente='.$Cliente.'&Ruta='.$Ruta.'&Clase='.$Clase.'&base='.urlencode($prefijobd);
                if (!empty($_GET['embed'])) $exportBase .= '&embed=1';
              ?>
              <a class="btn ghost"   href="tarifasClientes_excel.php?<?php echo $exportBase; ?>">Excel</a>
              <a class="btn primary" href="tarifasClientes_pdf.php?<?php echo $exportBase; ?>">PDF</a>
            </div>
          </div>

          <div class="table-container" id="tableWrap">
            <table id="reportTable">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Ruta</th>
                  <th>Tipo de Unidad</th>
                  <th>Concepto</th>
                  <th>Tipo</th>
                  <th>Precio Unitario</th>
                  <th>Importe</th>
                </tr>
              </thead>
              <tbody>
                <?php
                while ($rowSQL = $runSQL ? mysql_fetch_assoc($runSQL) : null) {
                  $id      = $rowSQL['FolioTarifas_RID'];
                  $rutaId  = $rowSQL['Ruta_RID'];
                  $claseId = $rowSQL['Clase_RID'];

                  $cliente = fetchOne("SELECT RazonSocial AS rs FROM ".$prefijobd."clientes WHERE ID='".$id."'", 'rs', $cnx_cfdi);
                  $ruta    = fetchOne("SELECT Ruta AS r FROM ".$prefijobd."rutas WHERE ID='".$rutaId."'", 'r', $cnx_cfdi);
                  $clase   = fetchOne("SELECT Clase AS c FROM ".$prefijobd."unidadesclase WHERE ID='".$claseId."'", 'c', $cnx_cfdi);

                  $concepto = $rowSQL['conceptopartida'];
                  $tipo     = $rowSQL['tipo'];
                  $precioU  = $rowSQL['preciounitario'];
                  $precioT  = $rowSQL['Importe'];

                  echo "<tr>
                          <td class='c-cliente' style='text-align:left'>".htmlspecialchars($cliente)."</td>
                          <td class='c-ruta' style='text-align:left'>".htmlspecialchars($ruta)."</td>
                          <td class='c-clase' style='text-align:left'>".htmlspecialchars($clase)."</td>
                          <td class='c-concepto' style='text-align:left'>".htmlspecialchars($concepto)."</td>
                          <td class='c-tipo' style='text-align:left'>".htmlspecialchars($tipo)."</td>
                          <td class='c-precioU' data-num='".htmlspecialchars($precioU)."'>".htmlspecialchars($precioU)."</td>
                          <td class='c-importe' data-num='".htmlspecialchars($precioT)."'>".htmlspecialchars($precioT)."</td>
                        </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <div class="footer-row">
            <div class="totals">Filas: <span id="rowsCount">0</span></div>
            <div class="totals">Total Importe: $ <span id="sumImporte">0.00</span></div>
          </div>
        </div>

        <!-- Paginación debajo de la tabla -->
        <div class="pagination">
          <?php
            $baseUrl = '?page=%d&Cliente='.$Cliente.'&Ruta='.$Ruta.'&Clase='.$Clase.'&base='.urlencode($prefijobd);
            if (!empty($_GET['embed'])) $baseUrl .= '&embed=1';
          ?>
          <?php if($page>1): ?>
            <a href="<?php echo sprintf($baseUrl, $page-1); ?>">‹ Anterior</a>
          <?php endif; ?>
          <?php for($i=1; $i<=$total_pages; $i++): ?>
            <a class="<?php echo ($i==$page)?'active':''; ?>" href="<?php echo sprintf($baseUrl, $i); ?>"><?php echo $i; ?></a>
          <?php endfor; ?>
          <?php if($page<$total_pages): ?>
            <a href="<?php echo sprintf($baseUrl, $page+1); ?>">Siguiente ›</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- ===== Columna derecha: Gráficas sticky ===== -->
      <div class="right">
        <div class="sidebar">
          <div class="card">
            <h3>Importe por Ruta</h3>
            <div class="canvas-wrap"><canvas id="chartRuta"></canvas></div>
          </div>
          <div class="card">
            <h3>Importe por Clase</h3>
            <div class="canvas-wrap"><canvas id="chartClase"></canvas></div>
          </div>
          <div class="card">
            <h3>Distribución por Tipo</h3>
            <div class="canvas-wrap"><canvas id="chartTipo"></canvas></div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
// Toggle tema (si hay botón)
(function(){
  var btn=document.getElementById('themeToggle'); if(!btn) return;
  var root=document.documentElement, key='ui-theme';
  function sync(){
    var d=root.getAttribute('data-theme')==='dark';
    btn.querySelector('.sun').style.display=d?'none':'inline';
    btn.querySelector('.moon').style.display=d?'inline':'none';
  }
  sync();
  btn.addEventListener('click',function(){
    var cur=root.getAttribute('data-theme')||'light';
    var next=(cur==='light')?'dark':'light';
    root.setAttribute('data-theme',next);
    try{ localStorage.setItem(key,next);}catch(e){}
    sync();
  });
})();

// ====== Buscador + Totales + Gráficas ======
var input = document.getElementById('search');
var tbody = document.querySelector('#reportTable tbody');
var rows  = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

var elRowsCount = document.getElementById('rowsCount');
var elSumImporte= document.getElementById('sumImporte');

// Chart.js ctx
var ctxRuta  = document.getElementById('chartRuta').getContext('2d');
var ctxClase = document.getElementById('chartClase').getContext('2d');
var ctxTipo  = document.getElementById('chartTipo').getContext('2d');
var chartRuta, chartClase, chartTipo;

function format(n){ return (Math.round((n+Number.EPSILON)*100)/100).toFixed(2); }

function currentVisibleRows(){
  return rows.filter(function(tr){ return tr.style.display !== 'none'; });
}

function recalcTotals(){
  var vis = currentVisibleRows();
  elRowsCount.textContent = vis.length;

  var sum = 0;
  for (var i=0;i<vis.length;i++){
    var td = vis[i].querySelector('.c-importe');
    var v = parseFloat(td.getAttribute('data-num')||'0') || 0;
    sum += v;
  }
  elSumImporte.textContent = format(sum);
}

function buildAggregates(){
  var vis = currentVisibleRows();
  var byRuta={}, byClase={}, byTipo={};
  for (var i=0;i<vis.length;i++){
    var tr = vis[i];
    var ruta  = (tr.querySelector('.c-ruta')?.textContent || '').trim();
    var clase = (tr.querySelector('.c-clase')?.textContent || '').trim();
    var tipo  = (tr.querySelector('.c-tipo')?.textContent || '').trim();
    var imp   = parseFloat(tr.querySelector('.c-importe').getAttribute('data-num')||'0') || 0;

    byRuta[ruta]  = (byRuta[ruta]||0) + imp;
    byClase[clase]= (byClase[clase]||0) + imp;
    byTipo[tipo]  = (byTipo[tipo]||0) + imp;
  }
  return { byRuta:byRuta, byClase:byClase, byTipo:byTipo };
}

function updateChart(ctx, chart, labels, data, type, title){
  if (chart) chart.destroy();
  var conf = {
    type: type,
    data: {
      labels: labels,
      datasets: [{
        label: title,
        data: data
      }]
    },
    options: {
      responsive:true,
      maintainAspectRatio:false,
      plugins:{ legend:{ display: type==='doughnut' } },
      scales: (type==='doughnut') ? {} : { y:{ beginAtZero:true } }
    }
  };
  return new Chart(ctx, conf);
}

function refreshCharts(){
  var ag = buildAggregates();
  var rLabels = Object.keys(ag.byRuta);   var rData = rLabels.map(function(k){return ag.byRuta[k];});
  var cLabels = Object.keys(ag.byClase);  var cData = cLabels.map(function(k){return ag.byClase[k];});
  var tLabels = Object.keys(ag.byTipo);   var tData = tLabels.map(function(k){return ag.byTipo[k];});

  chartRuta  = updateChart(ctxRuta,  chartRuta,  rLabels, rData, 'bar',     'Importe por Ruta');
  chartClase = updateChart(ctxClase, chartClase, cLabels, cData, 'bar',     'Importe por Clase');
  chartTipo  = updateChart(ctxTipo,  chartTipo,  tLabels, tData, 'doughnut','Importe por Tipo');
}

function applySearch(q){
  q = (q||'').trim().toLowerCase();
  rows.forEach(function(tr){
    if (!q){ tr.style.display=''; return; }
    var found = false;
    var tds = tr.querySelectorAll('td');
    for (var i=0;i<tds.length;i++){
      if ((tds[i].textContent||'').toLowerCase().indexOf(q) !== -1){ found = true; break; }
    }
    tr.style.display = found ? '' : 'none';
  });
  recalcTotals();
  refreshCharts();
}

// init
applySearch('');
input && input.addEventListener('input', function(){ applySearch(this.value); });

// Si está embebido en iframe, esconder botón local
if (window.self !== window.top) {
  var btn = document.getElementById('themeToggle');
  if (btn) btn.style.display = 'none';
}
</script>
</body>
</html>
