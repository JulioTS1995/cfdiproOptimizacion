<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi.php');
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

$id_proveedor_filtro = 0;
if (isset($_POST['proveedor'])) {
    $id_proveedor_filtro = intval($_POST['proveedor']);
} elseif (isset($_GET['proveedor'])) {
    $id_proveedor_filtro = intval($_GET['proveedor']);
}

$fecha_inicio = '';
$fecha_fin    = '';
if (isset($_POST['fechai']) && isset($_POST['fechaf'])) {
    $fecha_inicio = $_POST['fechai'];
    $fecha_fin    = $_POST['fechaf'];
} else {
    if (isset($_GET['fechai'])) $fecha_inicio = $_GET['fechai'];
    if (isset($_GET['fechaf'])) $fecha_fin    = $_GET['fechaf'];
}

$moneda = 'AMBOS';
if (isset($_POST['moneda']) && $_POST['moneda'] !== '') {
    $moneda = $_POST['moneda'];
} elseif (isset($_GET['moneda']) && $_GET['moneda'] !== '')  {
    $moneda = $_GET['moneda'];
}
$moneda = in_array($moneda, ['PESOS','DOLARES','AMBOS']) ? $moneda : 'AMBOS';

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND pg.Proveedor_RID = ".$id_proveedor_filtro." ";
}


$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTherm);

$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = " AND (
      pg.XFolio LIKE '%$searchThermSafe%' OR
      p.RazonSocial LIKE '%$searchThermSafe%' OR
      pg.ReferenciaBancaria LIKE '%$searchThermSafe%' OR
      CAST(pg.Total AS CHAR) LIKE '%$searchThermSafe%'
  )";
}


$whereMoneda = "";
if ($moneda === 'PESOS') {
    $whereMoneda = " AND ps.monedasub = 'PESOS' ";
} elseif ($moneda === 'DOLARES') {
    $whereMoneda = " AND ps.monedasub = 'DOLARES' ";
} else {
    $whereMoneda = " ";
}

// PAGINACIÓN
$per_page = 5; 
$page = 1;
if (isset($_GET['page']) && intval($_GET['page']) > 0) {
    $page = intval($_GET['page']);
}
$offset = ($page - 1) * $per_page;

//COUNT 
$count_sql = "SELECT COUNT(*) AS total
                FROM {$prefijobd}Pagos pg
                LEFT JOIN {$prefijobd}Proveedores p ON p.ID = pg.Proveedor_RID
                LEFT JOIN (
                  SELECT
                      pasub.FolioSubPago_RID AS PagoID,
                      MAX(IFNULL(pasub.TipoCambio,1)) AS tc_max,
                      cmp.Moneda as monedasub
                    FROM {$prefijobd}PagosSub as pasub
                    LEFT JOIN {$prefijobd}compras as cmp ON pasub.Compra_RID = cmp.ID
                    GROUP BY FolioSubPago_RID
                  ) ps ON ps.PagoID = pg.ID
                WHERE pg.Fecha BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59'
                {$cntQuery}
                {$whereMoneda}
                {$whereSearch}
                ";
$count_res = mysqli_query($cnx_cfdi2, $count_sql);
$total_rows = 0;
if ($count_res) {
    $cr = mysqli_fetch_assoc($count_res);
    if ($cr) $total_rows = intval($cr['total']);
}
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;


$sql = "SELECT
          p.ID AS ProveedorID,
          p.RazonSocial,
          pg.ID AS PagoID,
          pg.XFolio,
          pg.Fecha,
          pg.Total,
          pg.ReferenciaBancaria,
          CASE WHEN IFNULL(ps.tc_max,1) > 1 THEN 'DOLARES' ELSE 'PESOS' END AS Moneda,
          IFNULL(ps.tc_max,1) AS TipoCambio,
          ps.monedasub as monedasub
        FROM {$prefijobd}Pagos pg
        LEFT JOIN {$prefijobd}Proveedores p ON p.ID = pg.Proveedor_RID
        LEFT JOIN (
          SELECT
            pasub.FolioSubPago_RID AS PagoID,
            MAX(IFNULL(pasub.TipoCambio,1)) AS tc_max,
            cmp.Moneda as monedasub
          FROM {$prefijobd}PagosSub as pasub
          LEFT JOIN {$prefijobd}compras as cmp ON pasub.Compra_RID = cmp.ID
          GROUP BY FolioSubPago_RID
        ) ps ON ps.PagoID = pg.ID
        WHERE pg.Fecha BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59'
        {$cntQuery}
        {$whereMoneda}
        {$whereSearch}
        ORDER BY p.ID ASC, pg.Fecha DESC, pg.XFolio DESC

        LIMIT {$per_page} OFFSET {$offset}
        ";
$res = mysqli_query($cnx_cfdi2, $sql);
//die($sql);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Pago a proveedores · Resultados</title>
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

    /* buscador */
    .search-bar{
      display:flex;
      justify-content:flex-end;
      gap:8px;
      padding:0 16px 10px 16px;
      flex-wrap:wrap;
    }
    .search-bar input[type="text"]{
      flex:0 0 260px;
      padding:6px 10px;
      border-radius:999px;
      border:var(--border);
      font-size:.85rem;
      background:var(--row-bg);
      color:var(--text);
      outline:none;
    }
    .search-bar button{
      padding:6px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--tint);
      color:#fff;
      font-weight:600;
      cursor:pointer;
    }
    .search-bar button:hover{ background:#0558a7ff; }

    /* paginación */
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

    /* headers por proveedor */
    tr.row-subhead td{
      background: rgba(10,132,255,.08);
      color: var(--text);
      font-weight: 800;
      text-align: left;
      border-bottom: 1px dashed rgba(0,0,0,.12);
    }
    html[data-theme="dark"] tr.row-subhead td{
      background: rgba(10,132,255,.16);
      border-bottom: 1px dashed rgba(255,255,255,.14);
    }
    tr.row-child td{
      background: rgba(0,0,0,.02);
      font-size: .82rem;
    }
    html[data-theme="dark"] tr.row-child td{
      background: rgba(255,255,255,.03);
    }
    tr.row-total td{
      background: rgba(10,132,255,.06);
      border-top: 1px dashed rgba(0,0,0,.12);
      font-size: .82rem;
    }
    html[data-theme="dark"] tr.row-total td{
      background: rgba(10,132,255,.12);
      border-top: 1px dashed rgba(255,255,255,.14);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Pago a proveedores</h1>
        <div class="subtitle">Periodo: <?php echo $fecha_inicio_f." al ".$fecha_fin_f; ?> · Moneda: <?php echo htmlspecialchars($moneda); ?></div>
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="meta">
          <?php if($id_proveedor_filtro): ?>
            Proveedor filtrado: <?php echo intval($id_proveedor_filtro); ?>
          <?php else: ?>
            Todos los proveedores
          <?php endif; ?>
          · Registros: <?php echo intval($total_rows); ?>
        </div>
        <div class="actions">
          <?php
            $baseParams = 'fechai='.urlencode($fecha_inicio).
                          '&fechaf='.urlencode($fecha_fin).
                          '&prefijodb='.urlencode($prefijobd).
                          '&proveedor='.intval($id_proveedor_filtro).
                          '&moneda='.urlencode($moneda);
            $baseParams = ($searchTherm !== '') ? $baseParams.'&q='.urlencode($searchTherm) : $baseParams;
          ?>
        
          <a class="btn ghost" href="pagoProveedoresExcel.php?<?php echo $baseParams; ?>"> 📊 Exportar Excel</a>
        </div>
      </div>

      <form method="get" class="search-bar">
        <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>" />
        <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fecha_inicio); ?>" />
        <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fecha_fin); ?>" />
        <input type="hidden" name="proveedor" value="<?php echo intval($id_proveedor_filtro); ?>" />
        <input type="hidden" name="moneda" value="<?php echo htmlspecialchars($moneda); ?>" />
        
        <input type="text" name="q" placeholder="Buscar por folio, proveedor o total..." value="<?php echo htmlspecialchars($searchTherm); ?>" />
        <button type="submit">Buscar</button>
      </form>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Moneda</th>
              <th>Folio</th>
              <th>Fecha</th>
              <th>Referencia Bancaria</th>
              <th>Abonado</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $provActual = null;
            $provNombre  = '';

            $sumPesosProv = 0.0;
            $sumDolProv = 0.0;
            $sumDolEnPesosProv = 0.0;
            $countPagos = 0;

            function printTotalproveedorConvertido($moneda, $sumPesosProv, $sumDolProv, $sumDolEnPesosProv,$countPagos){
                echo '<tr class="row-total">';

                if ($moneda === 'AMBOS') {
                  $totalgeneralMX = $sumPesosProv + $sumDolEnPesosProv;
                  echo '<td  style="text-align:left;font-weight:800;">Total de Proveedor</td>';
                  echo '<td style = "text-align:right;font-weight:800;">'.intval($countPagos).' pago(s)</td>';

                  echo '<td colspan="3" style="text-align:right;font-weight:900;">';
                    echo '<div style="display:flex;justify-content:flex-end;gap:14px;flex-wrap:wrap;">';
                      echo '<span>PESOS: $'.number_format($sumPesosProv,2).'</span>';
                      echo '<span>DOLARES: $'.number_format($sumDolProv,2).'</span>';
                      echo '<span style="opacity:.9;">USD a MXN: $'.number_format($sumDolEnPesosProv,2).'</span>';
                      echo '<span style="opacity:.95;">TOTAL MXN: $'.number_format($totalgeneralMX,2).'</span>';
                    echo '</div>';
                  echo '</td>';
                } else {
                  $solo = ($moneda === 'DOLARES') ? $sumDolProv : $sumPesosProv;

                  echo'<td style="text-align:right;font-weight:800;">Total de Proveedor</td>';
                  echo'<td style="text-align:right;font-weight:800;">'.intval($countPagos).' pagos</td>';
                  echo'<td colspan="3" style="text-align:right;font-weight:900;">$ '.number_format($solo,2).'</td>';
                }
                
                echo '</tr>';

            } 

            if ($res) {

               $huboFilas = false;
              while ($row = mysqli_fetch_assoc($res)) {
                $huboFilas = true;
                // Header por proveedor
                $provId = (int)$row['ProveedorID'];

                if ($provActual === null || $provActual !== $provId) {

                  if ($provActual !== null) {
                    printTotalproveedorConvertido($moneda, $sumPesosProv, $sumDolProv, $sumDolEnPesosProv, $countPagos);

                    $sumPesosProv = 0.0;
                    $sumDolProv = 0.0;
                    $sumDolEnPesosProv = 0.0;
                    $countPagos = 0;
                  }

                  $provActual = $provId;

                  echo '<tr class="row-subhead">
                          <td colspan="5">'.htmlspecialchars($row['RazonSocial']).'</td>
                        </tr>';
                }
                //pagos
                $v_fecha = '';
                if (!empty($row['Fecha'])) {
                  $v_fecha = date("d-m-Y", strtotime($row['Fecha']));
                }

                $monto = floatval($row['Total']);
                $tc = isset($row['TipoCambio']) ? (float)$row['TipoCambio'] : 1.0;
                if ($tc <= 0) $tc = 1.0;

                $countPagos ++;

                //acomulacion  por moneda
                if ($row['monedasub'] === 'DOLARES') {
                  $sumDolProv += $monto;
                  $sumDolEnPesosProv += ($monto * $tc);
                } else {
                  $sumPesosProv += $monto;
                }
                

                echo '<tr class="row-child">';
                  echo '<td>'.htmlspecialchars($row['monedasub']).'</td>';
                  echo '<td>'.htmlspecialchars($row['XFolio']).'</td>';
                  echo '<td>'.htmlspecialchars($v_fecha).'</td>';
                  echo '<td>'.htmlspecialchars($row['ReferenciaBancaria']).'</td>';
                  echo '<td style="text-align:right;">'.number_format($monto,2).'</td>';
                echo '</tr>';
              }

              //total de ultimo provedor
              if ($huboFilas) {
                printTotalproveedorConvertido($moneda, $sumPesosProv, $sumDolProv, $sumDolEnPesosProv,$countPagos);
              }else  {              
                echo '<tr><td colspan="5" style="padding:16px;color:var(--text-soft);">Sin resultados con los filtros actuales.</td></tr>';
              }


            } else {
              echo '<tr><td colspan="5" style="padding:16px;color:#b00020;font-weight:700;">Error SQL: '.htmlspecialchars(mysqli_error($cnx_cfdi2)).'</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="pagination">
      <?php
        $basePag = '?prefijodb='.urlencode($prefijobd).
                    '&fechai='.urlencode($fecha_inicio).
                    '&fechaf='.urlencode($fecha_fin).
                    '&proveedor='.intval($id_proveedor_filtro).
                    '&moneda='.urlencode($moneda);

        if ($searchTherm !== '') {
            $basePag .= '&q='.urlencode($searchTherm);
        }
        $basePag .= '&page=%d';

        function pageLink($p, $label, $basePag, $isActive=false){
            $cls = $isActive ? 'active' : '';
            echo '<a class="'.$cls.'" href="'.sprintf($basePag, $p).'">'.$label.'</a>';
        }

        $window = 2;
        $start = max(1, $page - $window);
        $end   = min($total_pages, $page + $window);

        if ($page < 1) $page = 1;
        if ($page > $total_pages) $page = $total_pages;

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
