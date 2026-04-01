<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi2.php'); // mysqli
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// ===== PARAMS =====
$prefijobd = isset($_POST['base']) ? $_POST['base'] : (isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '');
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') { die("Falta prefijodb"); }
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

$sucursal = isset($_POST['sucursal']) ? (int)$_POST['sucursal'] : (isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0);
$emisor   = isset($_POST['emisor']) ? (int)$_POST['emisor'] : (isset($_GET['emisor']) ? (int)$_GET['emisor'] : 0);

$fechaInicio = isset($_POST['fechai']) ? $_POST['fechai'] : (isset($_GET['fechai']) ? $_GET['fechai'] : '');
$fechaFin    = isset($_POST['fechaf']) ? $_POST['fechaf'] : (isset($_GET['fechaf']) ? $_GET['fechaf'] : '');
if ($fechaInicio === '' || $fechaFin === '') { die("Faltan fechas"); }

$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin_f    = date("d-m-Y", strtotime($fechaFin));

// Buscador
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$qSafe = mysqli_real_escape_string($cnx_cfdi2, $q);

// ===== Sucursal filter robusto (si no existe Sucursal_RID no tronamos) =====
$hasSucursal = false;
try{
  $chk = mysqli_query($cnx_cfdi2, "SHOW COLUMNS FROM {$prefijobd}Oficinas LIKE 'Sucursal_RID'");
  $hasSucursal = ($chk && mysqli_num_rows($chk) > 0);
}catch(Exception $e){
  $hasSucursal = false;
}

$extraWhereSucursal = "";
if ($sucursal > 0 && $hasSucursal) {
  $extraWhereSucursal = " AND Vs.OficinaVSalida_RID IN (SELECT ID FROM {$prefijobd}Oficinas WHERE Sucursal_RID = {$sucursal}) ";
}

// ===== WHERE search =====
$whereSearch = "";
if ($qSafe !== '') {
  $whereSearch = " AND (
      Vs.XFolio LIKE '%{$qSafe}%'
      OR U.Unidad LIKE '%{$qSafe}%'
      OR Prd.Codigo LIKE '%{$qSafe}%'
      OR Prd.Nombre LIKE '%{$qSafe}%'
      OR VsS.Descripcion LIKE '%{$qSafe}%'
  ) ";
}

// ===== PAGINACIÓN =====
$per_page = 50;
$page = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Conteo (para paginación) - cuenta renglones de detalle
$count_sql = "  SELECT COUNT(*) AS total
  FROM {$prefijobd}valessalida Vs
  LEFT JOIN {$prefijobd}valessalidasub VsS ON Vs.ID = VsS.FolioSub_RID
  LEFT JOIN {$prefijobd}unidades U ON Vs.Unidad_RID = U.ID
  LEFT JOIN {$prefijobd}productos Prd ON Prd.ID = VsS.ProductoV_RID
  WHERE DATE(Vs.Fecha) BETWEEN '{$fechaInicio}' AND '{$fechaFin}'
  {$extraWhereSucursal}
  {$whereSearch}
";
$count_res = mysqli_query($cnx_cfdi2, $count_sql);
$total_rows = 0;
if ($count_res) {
  $cr = mysqli_fetch_assoc($count_res);
  $total_rows = $cr ? (int)$cr['total'] : 0;
}
$total_pages = max(1, (int)ceil($total_rows / $per_page));

// Query paginada
$sql = "  SELECT
    Vs.XFolio,
    Vs.Fecha,
    U.Unidad,
    Prd.Codigo,
    Prd.Nombre,
    VsS.Descripcion,
    VsS.Cantidad,
    VsS.PrecioUnitario,
    VsS.Importe
  FROM {$prefijobd}valessalida Vs
  LEFT JOIN {$prefijobd}unidades U ON Vs.Unidad_RID = U.ID
  LEFT JOIN {$prefijobd}valessalidasub VsS ON Vs.ID = VsS.FolioSub_RID
  LEFT JOIN {$prefijobd}productos Prd ON Prd.ID = VsS.ProductoV_RID
  WHERE DATE(Vs.Fecha) BETWEEN '{$fechaInicio}' AND '{$fechaFin}'
  {$extraWhereSucursal}
  {$whereSearch}
  ORDER BY Vs.XFolio ASC, VsS.ID ASC
  LIMIT {$per_page} OFFSET {$offset}
";
$res = mysqli_query($cnx_cfdi2, $sql);

// Base params links (export/paginación)
$baseParams = 'fechai='.urlencode($fechaInicio).
              '&fechaf='.urlencode($fechaFin).
              '&prefijodb='.urlencode($prefijobd).
              '&sucursal='.(int)$sucursal.
              '&emisor='.(int)$emisor;
if ($q !== '') $baseParams .= '&q='.urlencode($q);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Vale Salida · Detalle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script>
    (function(){
      var k='ui-theme', s=null;
      try{s=localStorage.getItem(k);}catch(e){}
      if(s==='light'||s==='dark'){ document.documentElement.setAttribute('data-theme',s); }
      else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        document.documentElement.setAttribute('data-theme','dark');
      } else document.documentElement.setAttribute('data-theme','light');
    })();
  </script>
  <style>
    :root{
      --bg:#ffffffff; --panel:#ffffffcc; --text:#0b0c0f; --text-soft:#5c6270; --tint: #0a84ff;
      --radius:16px; --shadow:0 8px 24px rgba(0,0,0,.08); --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff; --row-hover:#f1f4fb; --header-bg:rgba(255,255,255,.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f; --panel:#0f1218cc; --text:#f5f7fb; --text-soft:#a6aec2; --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.35); --border:1px solid rgba(255,255,255,.06);
      --row-bg:#141824; --row-hover:#1a2030; --header-bg:rgba(20,24,36,.7);
    }
    body{ margin:0; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Segoe UI",Roboto,Helvetica,Arial,sans-serif; background:var(--bg); color:var(--text); }
    .container{ max-width:1700px; margin:40px auto; padding:20px; }
    .header{ display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .header h1{ margin:0; font-size:1.8rem; font-weight:800; letter-spacing:-.5px; }
    .subtitle{ font-size:.95rem; color:var(--text-soft); font-weight:700; }
    .btn-theme{ border:none; padding:8px 14px; border-radius:999px; font-weight:900; background:linear-gradient(180deg,var(--tint), #007aff); color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25); display:inline-flex; gap:8px; align-items:center; }

    .panel{ background:var(--panel); border-radius:var(--radius); border:var(--border); box-shadow:var(--shadow); overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2); -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .panel-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px 16px; flex-wrap:wrap; }
    .meta{ color:var(--text-soft); font-weight:800; }
    .actions{ display:flex; gap:8px; flex-wrap:wrap; }
    .btn{ border:none; padding:8px 14px; border-radius:999px; font-weight:900; cursor:pointer; font-size:.9rem; text-decoration:none; display:inline-flex; gap:8px; align-items:center; }
    .btn.excel{ background:linear-gradient(180deg,#2ecc71,#1f9d55); color:#fff; }
    .btn.ghost{ background:var(--panel); color:var(--text); border:var(--border); }
    .table-container{ max-height:700px; overflow:auto; border-top:var(--border); }
    table{ width:100%; border-collapse:separate; font-size:.85rem; }
    thead th{ position:sticky; top:0; background:var(--header-bg); font-weight:800; padding:8px; text-align:center; font-size:.75rem; color:var(--text-soft); border-bottom:var(--border); backdrop-filter:blur(10px); }
    tbody td{ padding:8px; text-align:center; background:var(--row-bg); border-bottom:1px solid rgba(0,0,0,.05); }
    tbody tr:hover td{ background:var(--row-hover); }

    .search-bar{ display:flex; justify-content:flex-end; gap:8px; padding:0 16px 12px 16px; }
    .search-bar input[type="text"]{ flex:0 0 300px; padding:8px 12px; border-radius:999px; border:var(--border); font-size:.85rem; background:var(--row-bg); color:var(--text); outline:none; }
    .search-bar button{ padding:8px 14px; border-radius:999px; border:var(--border); background:var(--tint); color:#fff; font-weight:900; cursor:pointer; }
    .search-bar button:hover{ filter:brightness(.95); }

    .pagination{ margin:16px 0; display:flex; gap:6px; justify-content:center; flex-wrap:wrap; }
    .pagination a{ padding:6px 12px; border-radius:999px; border:var(--border); background:var(--panel); text-decoration:none; color:var(--text); font-weight:900; font-size:.85rem; }
    .pagination a:hover{ background:var(--row-hover); }
    .pagination a.active{ background:var(--tint); color:#fff; border:none; }
    .pagination a.ghost{ background:transparent; border:none; cursor:default; opacity:.8; }
	.btn.primary{
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h1>Vale Salida · Detalle</h1>
      <div class="subtitle">Periodo: <?php echo $fechaInicio_f." al ".$fechaFin_f; ?></div>
    </div>
    <button id="themeToggle" class="btn-theme" type="button">
      <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
    </button>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div class="meta">
        Registros: <?php echo number_format($total_rows); ?>
        <?php if ($sucursal>0): ?> · Sucursal: <?php echo (int)$sucursal; ?><?php endif; ?>
        <?php if (!$hasSucursal && $sucursal>0): ?> · (Sucursal  no existe: sin filtro)<?php endif; ?>
      </div>
      <div class="actions">
        <a class="btn excel" href="ValeSalidaDetalleExcel.php?<?php echo $baseParams; ?>">📊 Exportar Excel</a>
        <a class="btn primary" href="ValeSalidaDetallePdf.php?<?php echo $baseParams; ?>">📄 Exportar PDF</a>
        <a class="btn ghost" href="ValeSalidaDetalleFechas.php?prefijodb=<?php echo urlencode($prefijobd); ?>&sucursal=<?php echo (int)$sucursal; ?>&emisor=<?php echo (int)$emisor; ?>">↩️ Cambiar fechas</a>
      </div>
    </div>

    <form method="get" class="search-bar">
      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">
      <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fechaInicio); ?>">
      <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fechaFin); ?>">
      <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>">
      <input type="hidden" name="emisor" value="<?php echo (int)$emisor; ?>">
      <input type="text" name="q" placeholder="Buscar folio, unidad, código, nombre, descripción..." value="<?php echo htmlspecialchars($q); ?>">
      <button type="submit">Buscar</button>
    </form>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Folio</th>
            <th>Fecha</th>
            <th>Unidad</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sumCantidad = 0;
          $sumPrecioU  = 0;
          $sumTotal    = 0;

          if ($res) {
            while($row = mysqli_fetch_assoc($res)){
              $folio = $row['XFolio'];
              $fecha = $row['Fecha'] ? date("d-m-Y", strtotime($row['Fecha'])) : '';
              $unidad = $row['Unidad'];
              $codigo = $row['Codigo'];
              $nombre = $row['Nombre'];
              $descripcion = $row['Descripcion'];
              $cantidad = (float)$row['Cantidad'];
              $precioU  = (float)$row['PrecioUnitario'];
              $importe  = (float)$row['Importe'];

              $sumCantidad += $cantidad;
              $sumPrecioU  += $precioU;
              $sumTotal    += $importe;
              ?>
              <tr>
                <td style="text-align:left;"><?php echo htmlspecialchars($folio); ?></td>
                <td><?php echo htmlspecialchars($fecha); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($unidad); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($codigo); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($nombre); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($descripcion); ?></td>
                <td style="text-align:right;"><?php echo number_format($cantidad, 2); ?></td>
                <td style="text-align:right;"><?php echo "$".number_format($precioU, 2); ?></td>
                <td style="text-align:right;"><?php echo "$".number_format($importe, 2); ?></td>
              </tr>
              <?php
            }
          }
          ?>
          <tr>
            <td colspan="6" style="text-align:right;font-weight:900;">TOTAL (página):</td>
            <td style="text-align:right;font-weight:900;"><?php echo number_format($sumCantidad, 2); ?></td>
            <td style="text-align:right;font-weight:900;"><?php echo "$".number_format($sumPrecioU, 2); ?></td>
            <td style="text-align:right;font-weight:900;"><?php echo "$".number_format($sumTotal, 2); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="pagination">
    <?php
      $basePag = '?prefijodb='.urlencode($prefijobd).
                 '&fechai='.urlencode($fechaInicio).
                 '&fechaf='.urlencode($fechaFin).
                 '&sucursal='.(int)$sucursal.
                 '&emisor='.(int)$emisor;
      if ($q !== '') $basePag .= '&q='.urlencode($q);
      $basePag .= '&page=%d';

      function pageLink($p, $label, $basePag, $isActive=false){
        $cls = $isActive ? 'active' : '';
        echo '<a class="'.$cls.'" href="'.sprintf($basePag, $p).'">'.$label.'</a>';
      }

      $window = 2;
      $start = max(1, $page - $window);
      $end   = min($total_pages, $page + $window);

      if ($page > 1) {
        pageLink(1, '« Primera', $basePag);
        pageLink($page-1, '‹ Anterior', $basePag);
      }

      pageLink(1, '1', $basePag, ($page==1));

      if ($start > 2) echo '<a class="ghost">…</a>';

      for ($i=$start; $i<=$end; $i++){
        if ($i==1 || $i==$total_pages) continue;
        pageLink($i, (string)$i, $basePag, ($i==$page));
      }

      if ($end < ($total_pages-1)) echo '<a class="ghost">…</a>';

      if ($total_pages > 1) pageLink($total_pages, (string)$total_pages, $basePag, ($page==$total_pages));

      if ($page < $total_pages) {
        pageLink($page+1, 'Siguiente ›', $basePag);
        pageLink($total_pages, 'Última »', $basePag);
      }
    ?>
  </div>
</div>

<script>
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
</script>
</body>
</html>
