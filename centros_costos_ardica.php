<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Leer parámetros desde POST (primera vez) o GET (paginación/export)
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

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// WHERE proveedor
$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND C.ProveedorNo_RID = ".$id_proveedor_filtro." ";
}

// ---------- PAGINACIÓN ----------
$per_page = 10;
$page = 1;
if (isset($_GET['page']) && intval($_GET['page']) > 0) {
    $page = intval($_GET['page']);
}
$offset = ($page - 1) * $per_page;

// Conteo de filas (Compras + ComprasSub)
$count_sql = "
SELECT COUNT(*) AS total
FROM ".$prefijobd."Compras C
INNER JOIN ".$prefijobd."ComprasSub CS ON CS.FolioSub_RID = C.ID
WHERE DATE(C.Fecha) BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery;
$count_res = mysqli_query($cnx_cfdi2, $count_sql);
$total_rows = 0;
if ($count_res) {
    $cr = mysqli_fetch_assoc($count_res);
    if ($cr) $total_rows = intval($cr['total']);
}
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;

// Consulta principal paginada
$sql = "
SELECT 
  C.ID,
  C.Fecha,
  C.Factura,
  C.Comentarios,
  C.XFolio,
  C.ProveedorNo_RID,
  CS.CeCo,
  CS.Cuenta,
  CS.SubCuenta,
  CS.Cantidad,
  CS.Importe,
  CS.Sucursal,
  CS.ClasificacionCC,
  CS.UnidadSub_RID,
  P.RazonSocial AS ProveedorNombre,
  U.Unidad AS UnidadNombre
FROM ".$prefijobd."Compras C
INNER JOIN ".$prefijobd."ComprasSub CS ON CS.FolioSub_RID = C.ID
LEFT JOIN ".$prefijobd."Proveedores P ON P.ID = C.ProveedorNo_RID
LEFT JOIN ".$prefijobd."Unidades U ON U.ID = CS.UnidadSub_RID
WHERE DATE(C.Fecha) BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery."
ORDER BY C.Fecha, C.ID
LIMIT ".$per_page." OFFSET ".$offset;
$res = mysqli_query($cnx_cfdi2, $sql);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Centro de Costos · Resultados</title>
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
    .subtitle{
      font-size:.95rem;
      color:var(--text-soft);
    }
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
    }
    tbody td{
      padding:8px;
      text-align:center;
      background:var(--row-bg);
      border-bottom:1px solid rgba(0,0,0,.05);
    }
    tbody tr:hover td{
      background:var(--row-hover);
    }
    .footer-row{
      padding:10px 16px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:.85rem;
      color:var(--text-soft);
    }
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
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Centro de Costos</h1>
        <div class="subtitle">Periodo: <?php echo $fecha_inicio_f." al ".$fecha_fin_f; ?></div>
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
        </div>
        <div class="actions">
          <?php
            $baseParams = 'fechai='.urlencode($fecha_inicio).
                          '&fechaf='.urlencode($fecha_fin).
                          '&prefijodb='.urlencode($prefijobd).
                          '&proveedor='.intval($id_proveedor_filtro);
          ?>
          <a class="btn ghost" href="centros_costos_ardica_excel.php?<?php echo $baseParams; ?>">Exportar Excel</a>
          <a class="btn primary" href="centros_costos_ardica_pdf.php?<?php echo $baseParams; ?>">Exportar PDF</a>
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>XFolio</th>
              <th>Fecha</th>
              <th>Mes</th>
              <th>Año</th>
              <th>CeCo</th>
              <th>Sucursal</th>
              <th>Unidad de Negocio</th>
              <th>Departamento</th>
              <th>Cuenta</th>
              <th>Sub cuenta</th>
              <th>Proveedor</th>
              <th>No. Factura</th>
              <th>Cantidad</th>
              <th>Monto</th>
              <th>Observaciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $suma_monto = 0;
            if ($res) {
              while ($row = mysqli_fetch_assoc($res)) {
                $xfolio   = $row['XFolio'];
                $v_fecha_t= $row['Fecha'];
                $id_compra= $row['ID'];
                $v_fecha  = date("d-m-Y", strtotime($v_fecha_t));
                $obj_date = strtotime($v_fecha_t);
                $mes      = date("n", $obj_date);
                $anio     = date("Y", $obj_date);

                $CeCo        = $row['CeCo'];
                $cuenta      = $row['Cuenta'];
                $subCuenta   = $row['SubCuenta'];
                $cantidad    = $row['Cantidad'];
                $monto       = $row['Importe'];
                $suma_monto += floatval($monto);
                $sucursal    = $row['Sucursal'];
                $departamento= $row['ClasificacionCC'];
                $unidad      = $row['UnidadNombre'];
                $proveedor   = $row['ProveedorNombre'];
                $factura     = $row['Factura'];
                $observaciones = $row['Comentarios'];
            ?>
              <tr>
                <td><?php echo htmlspecialchars($xfolio); ?></td>
                <td><?php echo htmlspecialchars($v_fecha); ?></td>
                <td><?php echo htmlspecialchars($mes); ?></td>
                <td><?php echo htmlspecialchars($anio); ?></td>
                <td><?php echo htmlspecialchars($CeCo); ?></td>
                <td><?php echo htmlspecialchars($sucursal); ?></td>
                <td><?php echo htmlspecialchars($unidad); ?></td>
                <td><?php echo htmlspecialchars($departamento); ?></td>
                <td><?php echo htmlspecialchars($cuenta); ?></td>
                <td><?php echo htmlspecialchars($subCuenta); ?></td>
                <td><?php echo htmlspecialchars($proveedor); ?></td>
                <td><?php echo htmlspecialchars($factura); ?></td>
                <td><?php echo htmlspecialchars($cantidad); ?></td>
                <td><?php echo "$".number_format($monto,2); ?></td>
                <td><?php echo htmlspecialchars($observaciones); ?></td>
              </tr>
            <?php
              }
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="footer-row">
        <div>Filas: <?php echo $total_rows; ?></div>
        <div>Total Monto página: $<?php echo number_format($suma_monto,2); ?></div>
      </div>
    </div>

    <div class="pagination">
      <?php
        $basePag = '?prefijodb='.urlencode($prefijobd).
                   '&fechai='.urlencode($fecha_inicio).
                   '&fechaf='.urlencode($fecha_fin).
                   '&proveedor='.intval($id_proveedor_filtro).
                   '&page=%d';
      ?>
      <?php if($page>1): ?>
        <a href="<?php echo sprintf($basePag, $page-1); ?>">‹ Anterior</a>
      <?php endif; ?>
      <?php
        for($i=1; $i<=$total_pages; $i++){
          $cls = ($i==$page)?'active':'';
          echo '<a class="'.$cls.'" href="'.sprintf($basePag,$i).'">'.$i.'</a>';
        }
      ?>
      <?php if($page<$total_pages): ?>
        <a href="<?php echo sprintf($basePag, $page+1); ?>">Siguiente ›</a>
      <?php endif; ?>
    </div>
  </div>

<script>
// toggle tema
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
