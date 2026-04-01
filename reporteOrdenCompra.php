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
    $cntQuery = " AND ProveedorNo_RID = ".$id_proveedor_filtro." ";
}
//buscador 
$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTherm);

$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = "AND (
    c.Estatus       LIKE '%$searchThermSafe%' OR
    c.CompraAbierta  LIKE '%$searchThermSafe%' OR
    c.XFolio        LIKE '%$searchThermSafe%' OR
    c.Documentador  LIKE '%$searchThermSafe%' OR
    p.RazonSocial   LIKE '%$searchThermSafe%' OR
     
     EXISTS (
        SELECT 1
        FROM {$prefijobd}comprassub cs
        LEFT JOIN {$prefijobd}productos p ON cs.ProductoA_RID = p.ID
        WHERE cs.FolioSub_RID = c.ID
          AND (
            cs.Nombre LIKE '%$searchThermSafe%' OR
            IFNULL(p.Codigo,'') LIKE '%%$searchThermSafe'
            )
        )
     )";
}
// ---------- PAGINACIÓN ----------
$per_page = 5;
$page = 1;
if (isset($_GET['page']) && intval($_GET['page']) > 0) {
    $page = intval($_GET['page']);
}
$offset = ($page - 1) * $per_page;

// Conteo de filas (Compras + ComprasSub)
$count_sql = "
SELECT COUNT(*) AS total
FROM ".$prefijobd."Compras C

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
  c.ID,
  c.XFolio,
  c.Fecha,
  c.Estatus,
  c.CompraAbierta,
  c.Total,
  c.Comentarios,
  c.FolioRecepcion,
  c.Documentador,
  p.RazonSocial
FROM ".$prefijobd."compras c 
LEFT JOIN ".$prefijobd."proveedores p ON p.ID = c.ProveedorNo_RID
WHERE c.Fecha BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery." ".$whereSearch."
ORDER BY c.Fecha desc, p.RazonSocial desc
LIMIT ".$per_page." OFFSET ".$offset;
$res = mysqli_query($cnx_cfdi2, $sql);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Ordenes de compra · Resultados</title>
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

    /*buscador*/
    .search-bar{
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 0 16px 10px 16px;
    }
    .search-bar input[type="text"]{
        flex: 0 0 260px;
        padding: 6px 10px;
        border-radius: 999px;
        border: var(--border);
        font-size: 0.85rem;
        background: var(--row-bg);
        color:var(--text);
        outline: none;
    }
    .search-bar button{
        padding: 6px 12px;
        border-radius: 999px;
        border: var(--border);
        background: var(--tint);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }
    .search-bar button:hover{
        background: #0558a7ff;
    }
    .pagination a.ghost{
        background: transparent;
        opacity:none;
    }
    /* ===== jerarquía compra -> subpartidas ===== */
    tr.row-parent td{
      font-weight: 700;
    }

    tr.row-subhead td{
      background: rgba(10,132,255,.08);
      color: var(--text);
      font-weight: 700;
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

    td.indent{
      text-align: left !important;
      padding-left: 28px !important;
    }

    td.mini{
      font-size: .78rem;
      color: var(--text-soft);
    
    }

  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Ordenes de compra</h1>
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
            $baseParams = ($searchTherm !== '') ? $baseParams.'&q='.urlencode($searchTherm) : $baseParams;
          ?>
          <a class="btn ghost" href="reporteOrdenCompraExcel.php?<?php echo $baseParams; ?>"> 📊 Exportar Excel</a>
          <!-- <a class="btn primary" href="reporteOrdenCompraPdf.php?<?php echo $baseParams; ?>"> 📄 Exportar PDF</a> -->
        </div>
      </div>
      <form method="get" class="search-bar">
        <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>" />
        <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fecha_inicio); ?>" />
        <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fecha_fin); ?>" />
        <input type="hidden" name="proveedor" value="<?php echo intval($id_proveedor_filtro); ?>" />
        <input type="text" name="q" placeholder="Buscar..." value="<?php echo htmlspecialchars($searchTherm); ?>" />
        <button type="submit">Buscar</button>
      </form>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Folio</th>
              <th>Fecha</th>
              <th>Proveedor</th>
              <th>Estatus</th>
              <th>Compra</th>
              <th>Total</th>
              <th>Comentarios</th>
              <th>Folio Recepcion</th>
              <th>Documentador</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($res) {
              while ($row = mysqli_fetch_assoc($res)) {
                $xfolio         = $row['XFolio'];
                $v_fecha_t      = $row['Fecha'];
                $id_compra      = $row['ID'];
                $v_fecha        = date("d-m-Y", strtotime($v_fecha_t));
                $proveedor      = $row['RazonSocial'];
                $estatus        = $row['Estatus'];
                $compraAbierta  = $row['CompraAbierta'];
                $total          = $row['Total'];
                $comentarios    = $row['Comentarios'];
                $folioRecepcion = $row['FolioRecepcion'];
                $documentador   = $row['Documentador'];
            ?>
              <tr class="row-parent">
                    <td><?php echo htmlspecialchars($xfolio); ?></td>
                    <td><?php echo htmlspecialchars($v_fecha); ?></td>
                    <td style="text-align:left;"><?php echo htmlspecialchars($proveedor); ?></td>
                    <td><?php echo htmlspecialchars($estatus); ?></td>
                    <td><?php echo htmlspecialchars($compraAbierta); ?></td>
                    <td style="text-align:right;"><?php echo htmlspecialchars($total); ?></td>
                    <td style="text-align:left;"><?php echo htmlspecialchars($comentarios); ?></td>
                    <td><?php echo htmlspecialchars($folioRecepcion); ?></td>
                    <td><?php echo htmlspecialchars($documentador); ?></td>
              </tr>
              <?php
                                // ================== SUBPARTIDAS ==================
              // OJO: Respeta MAYÚSCULAS igual que tu BD (aquí uso ComprasSub, Productos, Almacenes)
              $sqlSub = "SELECT
                          cs.Nombre AS Descripcion,
                          cs.Cantidad,
                          cs.Descuento,
                          cs.PrecioUnitario,
                          cs.Importe,
                          IFNULL(p.Codigo,'') AS ProductoCodigo,
                          IFNULL(a.Nombre,'') AS AlmacenNombre
                      FROM {$prefijobd}ComprasSub cs
                      LEFT JOIN {$prefijobd}Productos p ON p.ID = cs.ProductoA_RID
                      LEFT JOIN {$prefijobd}Almacenes a ON a.ID = cs.Almacen_RID
                      WHERE cs.FolioSub_RID = ".intval($id_compra)."
                      ORDER BY cs.ID ASC
              ";

              $resSub = mysqli_query($cnx_cfdi2, $sqlSub);

              // Si la query falla, mínimo muéstralo en pantalla (sin código fuente)
              if (!$resSub) {
                  echo '<tr class="row-child"><td colspan="9" style="text-align:left;color:#b00020;font-weight:700;">Error subpartidas: '.htmlspecialchars(mysqli_error($cnx_cfdi2)).'</td></tr>';
              } else if (mysqli_num_rows($resSub) > 0) {
              ?>
              <tr class="row-subhead">
                <td colspan="9">↳Detalle de compra</td>
              </tr>

              <tr class="row-child">
                <td>&nbsp;</td>
                <td  class="mini indent">Código</td>
                <td class="mini">Almacén</td>
                <td colspan="3" class="mini" style="text-align:left;">Descripción</td>
                <td class="mini" style="text-align:right;">Cant</td>
                <td class="mini" style="text-align:right;">P.Unit</td>
                <td class="mini" style="text-align:right;">Importe</td>
              </tr>

              <?php
                  while ($rowSub = mysqli_fetch_assoc($resSub)) {
                      $productoCodigo    = $rowSub['ProductoCodigo'];
                      $almacenNombre     = $rowSub['AlmacenNombre'];
                      $descripcion       = $rowSub['Descripcion'];
                      $cantidadSub       = $rowSub['Cantidad'];
                      $descuentoSub      = $rowSub['Descuento'];
                      $precioUnitarioSub = $rowSub['PrecioUnitario'];
                      $importeSub        = $rowSub['Importe'];

                      $descTxt = '';
                      if ((float)$descuentoSub > 0) {
                          $descTxt = ' (Desc: '.htmlspecialchars($descuentoSub).')';
                      }
              ?>
              <tr class="row-child">
                <td>&nbsp;</td>
                <td  class="indent"><?php echo htmlspecialchars($productoCodigo); ?></td>
                <td><?php echo htmlspecialchars($almacenNombre); ?></td>
                <td colspan="3" style="text-align:left;"><?php echo htmlspecialchars($descripcion).$descTxt; ?></td>
                <td style="text-align:right;"><?php echo htmlspecialchars($cantidadSub); ?></td>
                <td style="text-align:right;"><?php echo htmlspecialchars($precioUnitarioSub); ?></td>
                <td style="text-align:right;"><?php echo htmlspecialchars($importeSub); ?></td>
              </tr>
              <?php
                  }
              }
              ?>

                        <?php
                }
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
                        '&proveedor='.intval($id_proveedor_filtro);

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

            if ($page > 1) {
                pageLink(1, '« Primera', $basePag);
                pageLink($page-1, '‹ Anterior', $basePag);
            }

            pageLink(1, '1', $basePag, ($page==1));

            if ($start > 2) {
                echo '<a class="ghost" href="javascript:void(0)" style="cursor:default; opacity:.75;">…</a>';
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($i == 1 || $i == $total_pages) continue;
                pageLink($i, (string)$i, $basePag, ($i==$page));
            }

           
            if ($end < ($total_pages - 1)) {
                echo '<a class="ghost" href="javascript:void(0)" style="cursor:default; opacity:.75;">…</a>';
            }

          
            if ($total_pages > 1) {
                pageLink($total_pages, (string)$total_pages, $basePag, ($page==$total_pages));
            }

            
            if ($page < $total_pages) {
                pageLink($page+1, 'Siguiente ›', $basePag);
                pageLink($total_pages, 'Última »', $basePag);
            }
            ?>
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
