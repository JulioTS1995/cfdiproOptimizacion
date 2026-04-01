<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$prefijobd = '';
if (isset($_POST['base']) && $_POST['base'] != '') {
    $prefijobd = $_POST['base'];
} elseif (isset($_GET['prefijodb']) && $_GET['prefijodb'] != '') {
    $prefijobd = $_GET['prefijodb'];
}
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);

$unidadID = 0;
if (isset($_POST['unidad']) && $_POST['unidad'] !== '') {
    $unidadID = intval($_POST['unidad']);
} elseif (isset($_GET['unidad'])) {
    $unidadID = intval($_GET['unidad']);
}

$sucursal = 0;
if (isset($_POST['sucursal'])) $sucursal = intval($_POST['sucursal']);
elseif (isset($_GET['sucursal'])) $sucursal = intval($_GET['sucursal']);

$emisor = 0;
if (isset($_POST['emisor'])) $emisor = intval($_POST['emisor']);
elseif (isset($_GET['emisor'])) $emisor = intval($_GET['emisor']);


$fecha_inicio = '';
$fecha_fin    = '';
if (isset($_POST['fechai']) && isset($_POST['fechaf'])) {
    $fecha_inicio = $_POST['fechai'];
    $fecha_fin    = $_POST['fechaf'];
} else {
    if (isset($_GET['fechai'])) $fecha_inicio = $_GET['fechai'];
    if (isset($_GET['fechaf'])) $fecha_fin    = $_GET['fechaf'];
}

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// WHERE sucursal opcional
$whereSucursal = "";
if ($sucursal > 0) {
    $whereSucursal = " AND M.OficinaMant_RID IN (SELECT ID FROM ".$prefijobd."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ";
}

// WHERE unidad opcional
$whereUnidad = "";
if ($unidadID > 0) {
    $whereUnidad = " AND M.UnidadMantenimiento_RID = ".$unidadID." ";
}

// PAGINACIÓN (10)
$per_page = 10;
$page = 1;
if (isset($_GET['page']) && intval($_GET['page']) > 0) {
    $page = intval($_GET['page']);
}
$offset = ($page - 1) * $per_page;

// COUNT
$count_sql = "
SELECT COUNT(*) AS total
FROM {$prefijobd}ValesSalidaSub VSS
LEFT JOIN {$prefijobd}ValesSalida VS ON VS.ID = VSS.FolioSub_RID
LEFT JOIN {$prefijobd}Mantenimientos M ON M.ID = VS.MantVSalida_RID
LEFT JOIN {$prefijobd}MantenimientosSub Ms ON Ms.FolioSub_RID = M.ID
LEFT JOIN {$prefijobd}Unidades U ON U.ID = M.UnidadMantenimiento_RID
LEFT JOIN {$prefijobd}Reparaciones R ON R.ID = Ms.Reparacion_RID
LEFT JOIN {$prefijobd}Talleres T ON T.ID = Ms.Taller_RID
LEFT JOIN {$prefijobd}Productos P ON P.ID = VSS.ProductoV_RID
WHERE DATE(M.Fecha) BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
{$whereSucursal}
{$whereUnidad}
";
$count_res = mysqli_query($cnx_cfdi2, $count_sql);
$total_rows = 0;
if ($count_res) {
    $cr = mysqli_fetch_assoc($count_res);
    if ($cr) $total_rows = intval($cr['total']);
}
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;
if ($page < 1) $page = 1;
if ($page > $total_pages) $page = $total_pages;

// Totales generales (sin paginar)
// Sub/IVA/Total por VS (distinct), y partidas por VSS
$tot_vs_sql = "
SELECT
  IFNULL(SUM(t.Subtotal),0) AS sumSubtotal,
  IFNULL(SUM(t.Impuesto),0) AS sumImpuesto,
  IFNULL(SUM(t.Total),0) AS sumTotal
FROM (
  SELECT DISTINCT VS.ID, VS.Subtotal, VS.Impuesto, VS.Total
  FROM {$prefijobd}ValesSalidaSub VSS
  LEFT JOIN {$prefijobd}ValesSalida VS ON VS.ID = VSS.FolioSub_RID
  LEFT JOIN {$prefijobd}Mantenimientos M ON M.ID = VS.MantVSalida_RID
  WHERE DATE(M.Fecha) BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
  {$whereSucursal}
  {$whereUnidad}
) t
";
$tot_vss_sql = "
SELECT
  IFNULL(SUM(VSS.Cantidad),0) AS sumCantidad,
  IFNULL(SUM(VSS.Importe),0) AS sumImporte,
  IFNULL(SUM(VSS.ImporteIVA),0) AS sumImporteIVA,
  IFNULL(SUM(VSS.ImporteTotal),0) AS sumImporteTotal
FROM {$prefijobd}ValesSalidaSub VSS
LEFT JOIN {$prefijobd}ValesSalida VS ON VS.ID = VSS.FolioSub_RID
LEFT JOIN {$prefijobd}Mantenimientos M ON M.ID = VS.MantVSalida_RID
WHERE DATE(M.Fecha) BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
{$whereSucursal}
{$whereUnidad}
";

$sumSubtotal=0; $sumImpuesto=0; $sumTotal=0;
$sumCantidad=0; $sumImporte=0; $sumImporteIVA=0; $sumImporteTotal=0;

$rt = mysqli_query($cnx_cfdi2, $tot_vs_sql);
if ($rt) {
  $trow = mysqli_fetch_assoc($rt);
  if ($trow) {
    $sumSubtotal = (float)$trow['sumSubtotal'];
    $sumImpuesto = (float)$trow['sumImpuesto'];
    $sumTotal    = (float)$trow['sumTotal'];
  }
}
$rt2 = mysqli_query($cnx_cfdi2, $tot_vss_sql);
if ($rt2) {
  $trow2 = mysqli_fetch_assoc($rt2);
  if ($trow2) {
    $sumCantidad     = (float)$trow2['sumCantidad'];
    $sumImporte      = (float)$trow2['sumImporte'];
    $sumImporteIVA   = (float)$trow2['sumImporteIVA'];
    $sumImporteTotal = (float)$trow2['sumImporteTotal'];
  }
}

// DATA paginada
$sql = " SELECT
          M.XFolio,
          M.Fecha,
          U.Unidad,
          Ms.Kilometros,
          R.Reparacion,
          T.Taller,
          VS.Subtotal,
          VS.Impuesto,
          VS.Total,
          P.Nombre,
          VSS.Cantidad,
          VSS.PrecioUnitario,
          VSS.Importe,
          VSS.ImporteIVA,
          VSS.ImporteTotal
          FROM {$prefijobd}ValesSalidaSub VSS
          LEFT JOIN {$prefijobd}ValesSalida VS ON VS.ID = VSS.FolioSub_RID
          LEFT JOIN {$prefijobd}Mantenimientos M ON M.ID = VS.MantVSalida_RID
          LEFT JOIN (
          SELECT
          FolioSub_RID,
          MAX(Kilometros) AS Kilometros,
          MIN(Reparacion_RID) AS Reparacion_RID,
          MIN(Taller_RID) AS Taller_RID
          FROM {$prefijobd}MantenimientosSub
          GROUP BY FolioSub_RID
          ) Ms ON Ms.FolioSub_RID = M.ID
          LEFT JOIN {$prefijobd}Unidades U ON U.ID = M.UnidadMantenimiento_RID
          LEFT JOIN {$prefijobd}Reparaciones R ON R.ID = Ms.Reparacion_RID
          LEFT JOIN {$prefijobd}Talleres T ON T.ID = Ms.Taller_RID
          LEFT JOIN {$prefijobd}Productos P ON P.ID = VSS.ProductoV_RID
          WHERE DATE(M.Fecha) BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
          {$whereSucursal}
          {$whereUnidad}
          ORDER BY U.Unidad ASC, M.XFolio ASC, VSS.ID ASC
          LIMIT {$per_page} OFFSET {$offset}
          ";
$res = mysqli_query($cnx_cfdi2, $sql);



// baseParams para export/paginación (sin page)
$baseParams = 'fechai='.urlencode($fecha_inicio)
            .'&fechaf='.urlencode($fecha_fin)
            .'&prefijodb='.urlencode($prefijobd)
            .'&unidad='.intval($unidadID)
            .'&sucursal='.intval($sucursal)
            .'&emisor='.intval($emisor);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Órdenes de Servicio · Resultados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script>
    (function(){
      var k='ui-theme', s=null;
      try{s=localStorage.getItem(k);}catch(e){}
      if(s==='light'||s==='dark'){ document.documentElement.setAttribute('data-theme',s); }
      else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        document.documentElement.setAttribute('data-theme','dark');
      } else {
        document.documentElement.setAttribute('data-theme','light');
      }
    })();
  </script>
  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#ffffffff;
      --panel:#ffffffcc;
      --text:#0b0c0f;
      --text-soft:#5c6270;
      --tint:#0a84ff;
      --radius:16px;
      --shadow:0 8px 24px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff;
      --row-hover:#f1f4fb;
      --header-bg:rgba(255,255,255,.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
      --row-bg:#141824;
      --row-hover:#1a2030;
      --header-bg:rgba(20,24,36,.7);
    }
    body{
      margin:0;
      font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial;
      background:var(--bg);
      color:var(--text);
    }
    .container{
      max-width:1700px;
      margin:40px auto;
      padding:20px;
    }
    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:16px;
      flex-wrap:wrap;
    }
    .header h1{
      margin:0;
      font-size:1.8rem;
      font-weight:700;
      letter-spacing:-.5px;
    }
    .subtitle{ font-size:.95rem; color:var(--text-soft); }
    .btn-theme{
      border:none;
      padding:8px 14px;
      border-radius:999px;
      font-weight:700;
      background:linear-gradient(180deg,var(--tint), #3373b8ff);
      color:#fff;
      cursor:pointer;
      box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex;
      gap:8px;
      align-items:center;
    }
    .panel{
      background:var(--panel);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .panel-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      padding:14px 16px;
      flex-wrap:wrap;
    }
    .meta{
      color:var(--text-soft);
      font-weight:600;
    }
    .actions{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
    }
    .btn{
      border:none;
      padding:8px 14px;
      border-radius:999px;
      font-weight:700;
      cursor:pointer;
      font-size:.9rem;
      text-decoration:none;
      display:inline-block;
    }
    .btn.primary{
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
    }
    .btn.ghost{
      background:var(--panel);
      color:var(--text);
      border:var(--border);
    }
    .table-container{
      max-height:700px;
      overflow:auto;
      border-top:var(--border);
    }
    table{
      width:100%;
      border-collapse:separate;
      font-size:.85rem;
    }
    thead th{
      position:sticky;
      top:0;
      background:var(--header-bg);
      font-weight:600;
      padding:8px;
      text-align:center;
      font-size:.75rem;
      color:var(--text-soft);
      border-bottom:var(--border);
      backdrop-filter:blur(10px);
      z-index:2;
    }
    tbody td{
      padding:8px;
      text-align:center;
      background:var(--row-bg);
      border-bottom:1px solid rgba(0,0,0,.05);
    }
    tbody tr:hover td{ background:var(--row-hover); }

    .pagination{
      margin:16px 0;
      display:flex;
      gap:6px;
      justify-content:center;
      flex-wrap:wrap;
    }
    .pagination a{
      padding:6px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      text-decoration:none;
      color:var(--text);
      font-weight:700;
      font-size:.85rem;
    }
    .pagination a:hover{ background:var(--row-hover); }
    .pagination a.active{
      background:var(--tint);
      color:#fff;
      border:none;
    }
    .pagination a.ghost{
      background:transparent;
      border:none;
      cursor:default;
      opacity:.75;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Órdenes de Servicio por Vehículo · Detalle</h1>
        <div class="subtitle">Periodo: <?php echo $fecha_inicio_f." al ".$fecha_fin_f; ?> · Registros: <?php echo intval($total_rows); ?></div>
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="meta">
          Unidad: <?php echo ($unidadID>0 ? intval($unidadID) : 'TODAS'); ?>
          · Sucursal: <?php echo ($sucursal>0 ? intval($sucursal) : 'TODAS'); ?>
        </div>
        <div class="actions">
          <a class="btn ghost" href="OrdServVehiculoDetalleExcel.php?<?php echo $baseParams; ?>"> 📊 Exportar Excel</a>
          <a class="btn ghost" href="OrdServVehiculoDetallePDF.php?<?php echo $baseParams; ?>"> 📄 Exportar PDF</a>
          <a class="btn primary" href="OrdServVehiculoDetalleFechas.php?prefijodb=<?php echo urlencode($prefijobd); ?>&emisor=<?php echo intval($emisor); ?>&sucursal=<?php echo intval($sucursal); ?>">🗓️ Cambiar filtros</a>
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Orden Servicio</th>
              <th>Fecha</th>
              <th>Vehiculo</th>
              <th>Km</th>
              <th>Servicio</th>
              <th>Taller</th>
              <th>Subtotal</th>
              <th>IVA</th>
              <th>Total</th>
              <th>Articulo</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Importe</th>
              <th>IVA</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($res) {
              $hubo = false;
              $ultimoFolio = null;

              while ($row = mysqli_fetch_assoc($res)) {
                $hubo = true;

                $folio = $row['XFolio'];
                $v_fecha = !empty($row['Fecha']) ? date("d-m-Y", strtotime($row['Fecha'])) : '';
                $unidad = $row['Unidad'];
                $kms = $row['Kilometros'];
                $servicio = $row['Reparacion'];
                $taller = $row['Taller'];

                $subtotal = (float)$row['Subtotal'];
                $impuesto = (float)$row['Impuesto'];
                $total = (float)$row['Total'];

                $nombre = $row['Nombre'];
                $cantidad = (float)$row['Cantidad'];
                $precioU = (float)$row['PrecioUnitario'];
                $importe = (float)$row['Importe'];
                $importeIVA = (float)$row['ImporteIVA'];
                $importeTotal = (float)$row['ImporteTotal'];

                $isNew = ($ultimoFolio === null || $ultimoFolio !== $folio);

                if ($isNew) {
                  echo '<tr>';
                  echo '<td>'.htmlspecialchars($folio).'</td>';
                  echo '<td>'.htmlspecialchars($v_fecha).'</td>';
                  echo '<td>'.htmlspecialchars($unidad).'</td>';
                  echo '<td>'.htmlspecialchars($kms).'</td>';
                  echo '<td>'.htmlspecialchars($servicio).'</td>';
                  echo '<td>'.htmlspecialchars($taller).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($subtotal,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($impuesto,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($total,2).'</td>';
                  echo '<td>'.htmlspecialchars($nombre).'</td>';
                  echo '<td>'.htmlspecialchars($cantidad).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($precioU,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importe,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importeIVA,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importeTotal,2).'</td>';
                  echo '</tr>';
                } else {
                  echo '<tr>';
                  echo '<td colspan="9"></td>';
                  echo '<td>'.htmlspecialchars($nombre).'</td>';
                  echo '<td>'.htmlspecialchars($cantidad).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($precioU,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importe,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importeIVA,2).'</td>';
                  echo '<td style="text-align:right;">$'.number_format($importeTotal,2).'</td>';
                  echo '</tr>';
                }

                $ultimoFolio = $folio;
              }

              // TOTAL general (no paginado)
              if ($hubo) {
                echo '<tr>';
                echo '<td style="font-weight:800;">TOTAL</td>';
                echo '<td colspan="5"></td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumSubtotal,2).'</td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumImpuesto,2).'</td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumTotal,2).'</td>';
                echo '<td></td>';
                echo '<td style="font-weight:900;">'.number_format($sumCantidad,2).'</td>';
                echo '<td></td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumImporte,2).'</td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumImporteIVA,2).'</td>';
                echo '<td style="text-align:right;font-weight:900;">$'.number_format($sumImporteTotal,2).'</td>';
                echo '</tr>';
              } else {
                echo '<tr><td colspan="15" style="padding:16px;color:var(--text-soft);">Sin resultados con los filtros actuales.</td></tr>';
              }

            } else {
              echo '<tr><td colspan="15" style="padding:16px;color:#b00020;font-weight:700;">Error SQL: '.htmlspecialchars(mysqli_error($cnx_cfdi2)).'</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="pagination">
      <?php
        $basePag = '?prefijodb='.urlencode($prefijobd)
                .'&fechai='.urlencode($fecha_inicio)
                .'&fechaf='.urlencode($fecha_fin)
                .'&unidad='.intval($unidadID)
                .'&sucursal='.intval($sucursal)
                .'&emisor='.intval($emisor)
                .'&page=%d';

        function pageLink($p, $label, $basePag, $isActive=false){
            $cls = $isActive ? 'active' : '';
            echo '<a class="'.$cls.'" href="'.sprintf($basePag, $p).'">'.$label.'</a>';
        }

        $window = 2;
        $start = max(1, $page - $window);
        $end   = min($total_pages, $page + $window);

        if ($total_pages > 1) {
          if ($page > 1) {
              pageLink(1, '« Primera', $basePag);
              pageLink($page-1, '‹ Anterior', $basePag);
          }

          pageLink(1, '1', $basePag, ($page==1));

          if ($start > 2) {
              echo '<a class="ghost" href="javascript:void(0)">…</a>';
          }

          for ($i = $start; $i <= $end; $i++) {
              if ($i == 1 || $i == $total_pages) continue;
              pageLink($i, (string)$i, $basePag, ($i==$page));
          }

          if ($end < ($total_pages - 1)) {
              echo '<a class="ghost" href="javascript:void(0)">…</a>';
          }

          if ($total_pages > 1) {
              pageLink($total_pages, (string)$total_pages, $basePag, ($page==$total_pages));
          }

          if ($page < $total_pages) {
              pageLink($page+1, 'Siguiente ›', $basePag);
              pageLink($total_pages, 'Última »', $basePag);
          }
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

// si está embebido en iframe, ocultar botón local
if (window.self !== window.top) {
  var btn = document.getElementById('themeToggle');
  if (btn) btn.style.display = 'none';
}
</script>
</body>
</html>
