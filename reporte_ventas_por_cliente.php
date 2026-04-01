<?php
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) die("Falta el prefijo de la BD");

// Normalizar prefijo
$prefijo_raw = $_GET['prefijodb'];
$prefijo_raw = str_replace(array("'", '"', ";"), "", $prefijo_raw);
if (strpos($prefijo_raw, "_") === false) $prefijobd = $prefijo_raw . "_"; else $prefijobd = $prefijo_raw;

$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;
$fechai   = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fechaf   = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';
$moneda   = isset($_GET['moneda']) ? strtoupper(trim($_GET['moneda'])) : 'TODOS';
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

if ($moneda!='TODOS' && $moneda!='PESOS' && $moneda!='DOLARES') $moneda='TODOS';

// defaults fecha
if ($fechai=='' || $fechaf=='') {
    $hoy = date('Y-m-d');
    $menos30 = date('Y-m-d', strtotime('-30 days'));
    if ($fechai=='') $fechai = $menos30;
    if ($fechaf=='') $fechaf = $hoy;
}

require_once('cnx_cfdi3.php');
mysqli_select_db($cnx_cfdi3, $database_cfdi);
mysqli_query($cnx_cfdi3, "SET NAMES 'utf8'");

$clientes_table = $prefijobd."clientes";
$factura_table  = $prefijobd."factura";
$factdet_table  = $prefijobd."facturasdetalle";
$rem_table      = $prefijobd."remisiones";

// detectar sucursal en clientes
$has_sucursal = 0;
$sqlChk = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA='".mysqli_real_escape_string($cnx_cfdi3,$database_cfdi)."'
             AND TABLE_NAME='".mysqli_real_escape_string($cnx_cfdi3,$clientes_table)."'
             AND COLUMN_NAME='Sucursal_RID'
           LIMIT 1";
$resChk = mysqli_query($cnx_cfdi3, $sqlChk);
if ($resChk && mysqli_num_rows($resChk) > 0) $has_sucursal = 1;

// encabezado empresa
$RazonSocial = '';
$r0 = mysqli_query($cnx_cfdi3, "SELECT RazonSocial FROM {$prefijobd}systemsettings LIMIT 1");
if ($r0 && mysqli_num_rows($r0)>0){ $x=mysqli_fetch_assoc($r0); $RazonSocial=$x['RazonSocial']; }

// filtros
$where = " WHERE f.FECreado IS NOT NULL
           AND f.cCanceladoT IS NULL
           AND f.CargoAFactura_RID IS NOT NULL
           AND f.Creado BETWEEN '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' ";

if ($moneda!='TODOS') $where .= " AND f.Moneda='".mysqli_real_escape_string($cnx_cfdi3,$moneda)."' ";
if ($cliente_id!=0) $where .= " AND f.CargoAFactura_RID=".(int)$cliente_id." ";
if ($sucursal!=0 && $has_sucursal==1) $where .= " AND c.Sucursal_RID=".(int)$sucursal." ";

$q_safe = mysqli_real_escape_string($cnx_cfdi3, $q);
if ($q_safe!='') {
    $where .= " AND (
        f.XFolio LIKE '%$q_safe%' OR
        f.Ticket LIKE '%$q_safe%' OR
        c.RazonSocial LIKE '%$q_safe%' OR
        r.XFolio LIKE '%$q_safe%'
    ) ";
}

// paginación 
$per_page = 10;

$sqlCount = "SELECT COUNT(DISTINCT f.ID) AS total
             FROM $factura_table f
             INNER JOIN $clientes_table c ON f.CargoAFactura_RID=c.ID
             LEFT JOIN $factdet_table d ON d.FolioSubDetalle_RID=f.ID
             LEFT JOIN $rem_table r ON d.Remision_RID=r.ID
             $where";
$rc = mysqli_query($cnx_cfdi3, $sqlCount);
$total_rows = 0;
if ($rc && ($xc=mysqli_fetch_assoc($rc))) $total_rows = (int)$xc['total'];

$total_pages = ($total_rows>0) ? (int)ceil($total_rows/$per_page) : 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page-1)*$per_page;

$sql = "SELECT
            f.ID, f.Creado, f.XFolio, f.Moneda, f.Ticket,
            f.zSubtotal, f.zImpuesto, f.zRetenido, f.zTotal,
            c.RazonSocial,
            MAX(r.XFolio) AS CartaPorte
        FROM $factura_table f
        INNER JOIN $clientes_table c ON f.CargoAFactura_RID=c.ID
        LEFT JOIN $factdet_table d ON d.FolioSubDetalle_RID=f.ID
        LEFT JOIN $rem_table r ON d.Remision_RID=r.ID
        $where
        GROUP BY f.ID
        ORDER BY c.RazonSocial, f.XFolio
        LIMIT $per_page OFFSET $offset";
$res = mysqli_query($cnx_cfdi3, $sql);

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function build_qs($over=array()){
    $p = $_GET;
    foreach($over as $k=>$v){ $p[$k]=$v; }
    return http_build_query($p);
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Ventas por Cliente · Display</title>
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
      --bg:#ffffffff; --panel:#ffffffcc; --text:#0b0c0f; --text-soft:#5c6270;
      --tint:#0a84ff; --radius:16px; --shadow:0 8px 24px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f; --panel:#0f1218cc; --text:#f5f7fb; --text-soft:#a6aec2;
      --tint:#0a84ff; --shadow:0 8px 24px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
    }
    body{ margin:0; font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; background:var(--bg); color:var(--text); }
    .container{ max-width:1100px; margin:30px auto; padding:20px; }
    .header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
    .header h1{ margin:0; font-size:1.6rem; font-weight:700; letter-spacing:-.5px; }
    .btn-theme{
      border:none; padding:8px 14px; border-radius:999px; font-weight:700;
      background:linear-gradient(180deg,var(--tint), #3373b8ff);
      color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex; gap:8px; align-items:center;
    }
    .panel{
      background:var(--panel);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      padding:16px;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .row{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    label{ font-size:.9rem; font-weight:600; color:var(--text-soft); margin-right:6px; }
    input[type="text"]{
      flex:1; min-width:260px;
      padding:9px 11px; border-radius:999px; border:var(--border);
      background:var(--bg); color:var(--text); font-size:.95rem; outline:none;
    }
    input[type="text"]:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }
    .btn{
      border:none; padding:9px 16px; border-radius:999px; font-weight:700;
      cursor:pointer; font-size:.95rem; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;
    }
    .btn.primary{ background:linear-gradient(180deg,var(--tint), #007aff); color:#fff; }
    .btn.pdf{ background:rgba(239,68,68,.12); border:var(--border); color:var(--text); }
    .btn.xls{ background:rgba(34,197,94,.12); border:var(--border); color:var(--text); }
    .meta{ margin-top:10px; font-size:.9rem; color:var(--text-soft); }

    .tablewrap{ margin-top:14px; overflow:auto; border-radius:16px; border:var(--border); }
    table{ width:100%; border-collapse:separate; border-spacing:0; min-width:900px; }
    th, td{ padding:10px 12px; border-bottom:1px solid rgba(10,12,16,.08); font-size:.95rem; }
    html[data-theme="dark"] th, html[data-theme="dark"] td{ border-bottom:1px solid rgba(255,255,255,.06); }
    th{ text-align:left; color:var(--text-soft); font-weight:700; background:rgba(255,255,255,.55); position:sticky; top:0; }
    html[data-theme="dark"] th{ background:rgba(15,18,24,.55); }
    tr:hover td{ background:rgba(10,132,255,.06); }
    .num{ text-align:right; }
    .center{ text-align:center; }

    .pager{ display:flex; gap:8px; justify-content:flex-end; margin-top:14px; flex-wrap:wrap; }
    .pill{
      border:var(--border);
      background:var(--panel);
      padding:8px 12px;
      border-radius:999px;
      text-decoration:none;
      color:var(--text);
      font-weight:700;
      box-shadow:0 6px 16px rgba(0,0,0,.06);
    }
    .pill.active{ box-shadow:0 0 0 4px rgba(10,132,255,.12); border:1px solid rgba(10,132,255,.35); }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Reporte Facturas por Cliente</h1>
        <div class="meta"><?php echo h($RazonSocial); ?> · Del <?php echo h($fechai); ?> al <?php echo h($fechaf); ?> · <?php echo h($moneda); ?></div>
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="get" action="">
        <?php
        foreach($_GET as $k=>$v){
          if($k=='q' || $k=='page') continue;
          echo '<input type="hidden" name="'.h($k).'" value="'.h($v).'">';
        }
        ?>
        <div class="row">
          <label for="q">Buscar</label>
          <input type="text" name="q" id="q" value="<?php echo h($q); ?>" placeholder="Folio, cliente, tracking, carta porte...">
          <button class="btn primary" type="submit">Filtrar</button>

          <a class="btn xls" href="ventas_por_cliente_export.php?<?php echo h(build_qs(array('export'=>'excel','page'=>1))); ?>">Excel</a>
          <a class="btn pdf" target="_blank" href="ventas_por_cliente_export.php?<?php echo h(build_qs(array('export'=>'pdf','page'=>1))); ?>">PDF</a>
        </div>

        <div class="meta">
          Total: <b><?php echo (int)$total_rows; ?></b> · Página <b><?php echo (int)$page; ?></b> / <b><?php echo (int)$total_pages; ?></b> · Paginación: <b>6</b>
        </div>
      </form>

      <div class="tablewrap">
        <table>
          <thead>
            <tr>
              <th class="center">Fecha</th>
              <th class="center">Folio</th>
              <th class="center">Moneda</th>
              <th>Cliente</th>
              <th class="center">Tracking</th>
              <th class="center">Carta Porte</th>
              <th class="num">Subtotal</th>
              <th class="num">IVA</th>
              <th class="num">IVA Ret</th>
              <th class="num">Neto</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!$res || mysqli_num_rows($res)==0){
            echo '<tr><td colspan="10" class="center" style="padding:18px;color:var(--text-soft);">Sin resultados.</td></tr>';
          } else {
            while($r=mysqli_fetch_assoc($res)){
              echo '<tr>';
              echo '<td class="center">'.h($r['Creado']).'</td>';
              echo '<td class="center">'.h($r['XFolio']).'</td>';
              echo '<td class="center">'.h($r['Moneda']).'</td>';
              echo '<td>'.h($r['RazonSocial']).'</td>';
              echo '<td class="center">'.h($r['Ticket']).'</td>';
              echo '<td class="center">'.h($r['CartaPorte']).'</td>';
              echo '<td class="num">'.number_format((float)$r['zSubtotal'],2).'</td>';
              echo '<td class="num">'.number_format((float)$r['zImpuesto'],2).'</td>';
              echo '<td class="num">'.number_format((float)$r['zRetenido'],2).'</td>';
              echo '<td class="num">'.number_format((float)$r['zTotal'],2).'</td>';
              echo '</tr>';
            }
          }
          ?>
          </tbody>
        </table>
      </div>

      <div class="pager">
        <?php
        $start = $page - 4; if ($start < 1) $start = 1;
        $end = $start + 9; if ($end > $total_pages) $end = $total_pages;
        $start = $end - 9; if ($start < 1) $start = 1;

        if ($page > 1){
          echo '<a class="pill" href="?'.h(build_qs(array('page'=>1))).'">«</a>';
          echo '<a class="pill" href="?'.h(build_qs(array('page'=>$page-1))).'">‹</a>';
        }
        for($p=$start;$p<=$end;$p++){
          $cls = ($p==$page) ? 'pill active' : 'pill';
          echo '<a class="'.$cls.'" href="?'.h(build_qs(array('page'=>$p))).'">'.$p.'</a>';
        }
        if ($page < $total_pages){
          echo '<a class="pill" href="?'.h(build_qs(array('page'=>$page+1))).'">›</a>';
          echo '<a class="pill" href="?'.h(build_qs(array('page'=>$total_pages))).'">»</a>';
        }
        ?>
      </div>
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
