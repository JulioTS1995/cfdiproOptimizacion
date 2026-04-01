<?php
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

//buscador

$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysql_real_escape_string($searchTherm);


$whereSearch = '';
if ($searchThermSafe !== '') {
    $whereSearch = " AND (
        u.Unidad LIKE '%$searchThermSafe%' OR
        u.Tipo LIKE '%$searchThermSafe%' OR
        uc.Clase LIKE '%$searchThermSafe%' OR
        u.Placas LIKE '%$searchThermSafe%' OR
        u.PesoBrutoVehicular LIKE '%$searchThermSafe%'
    ) ";
}

// ----- PAGINACIÓN -----
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// base from/where
$baseFromWhere = "
FROM ".$prefijobd."unidades AS u
LEFT JOIN ".$prefijobd."unidadesclase AS uc ON u.Clase_RID = uc.ID
LEFT JOIN (
    SELECT DISTINCT r.Unidad_RID
    FROM ".$prefijobd."remisiones r
    WHERE (r.Creado >= NOW() - INTERVAL 30 DAY)
      AND (r.EstatusTerminadoT IS NULL OR r.EstatusTerminadoT = '')
) AS r30 ON r30.Unidad_RID = u.ID
LEFT JOIN (
    SELECT DISTINCT m.UnidadMantenimiento_RID AS Unidad_RID
    FROM ".$prefijobd."mantenimientossub ms
    INNER JOIN ".$prefijobd."mantenimientos m ON ms.FolioSub_RID = m.ID
    WHERE (ms.Terminado IS NULL OR ms.Terminado = '')
) AS mnt ON mnt.Unidad_RID = u.ID
WHERE r30.Unidad_RID IS NULL AND mnt.Unidad_RID IS NULL";

$count_q = "SELECT COUNT(*) AS total ".$baseFromWhere. " ".$whereSearch ." ;";
$count_r = mysql_query($count_q, $cnx_cfdi);
$count_row = mysql_fetch_assoc($count_r);
$total_rows = $count_row['total'];
$total_pages = ceil($total_rows / $per_page);

// Consulta con LIMIT
$query="
SELECT 
    u.Unidad AS unidad,
    u.Tipo AS tipo,
    uc.Clase AS clase,
    u.Placas AS placas,
    u.PesoBrutoVehicular AS peso
".$baseFromWhere. "
 ".$whereSearch ." 
ORDER BY unidad
LIMIT $per_page OFFSET $offset;
";


$result = mysql_query($query,$cnx_cfdi);

$baseQS = 'prefijodb='. urlencode($_GET['prefijodb']);
if ($searchTherm !== '') {
    $baseQS .= '&q=' . urlencode($searchTherm);
}


?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Vehículos Disponibles</title>
  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #ffffffff;
      --panel: #ffffffcc;
      --text: #0b0c0f;
      --text-soft: #5c6270;
      --tint: #0a84ff;
      --radius:16px;
      --shadow:0 8px 24px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff;
      --row-hover:#f1f4fb;
      --header-bg: rgba(221, 221, 221, 0.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --row-bg:#141824;
      --row-hover:#1a2030;
      --header-bg:rgba(20,24,36,.7);
      --border:1px solid rgba(255,255,255,.06);
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
    .header{text-align:center;
      margin-bottom:20px;
    }
    .header h1{font-size:1.8rem;
      font-weight:700;
      letter-spacing:-0.5px;
    }
    .panel{
      background:var(--panel);
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .table-container{max-height:700px;overflow-y:auto;}
    table{width:100%;border-collapse:separate;font-size:0.9rem;}
    thead th{
      position:sticky;top:0;background:var(--header-bg);
      font-weight:600;padding:12px;text-align:center;
      font-size:0.8rem;color:var(--text-soft);border-bottom:var(--border);
      backdrop-filter:blur(10px);
    }
    tbody td{
      text-align:center;padding:12px;
      background:var(--row-bg);
      border-bottom:1px solid rgba(0,0,0,.05);
      transition:background .25s;
    }
    tbody tr:hover td{background:var(--row-hover);}
    .btn.theme-toggle{
      margin-bottom:15px;
      display:inline-flex;align-items:center;gap:6px;
      border:var(--border);
      background:var(--panel);
      color:var(--text);
      padding:6px 12px;
      border-radius:999px;
      font-weight:600;cursor:pointer;
      transition:.2s;
    }
    .btn.theme-toggle:hover{transform:translateY(-1px);}
    /* Paginación */
    .pagination{margin:20px 0;display:flex;gap:6px;justify-content:center;flex-wrap:wrap;}
    .pagination a{
      padding:6px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      text-decoration:none;
      color:var(--text);
      font-weight:600;
      transition:.2s;
    }
    .pagination a:hover
    {
      background:var(--row-hover);
    }
    .pagination .active
    {
      background:var(--tint);color:#fff;border:none;
    }

    /*buscador*/
    .search-bar
    {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      padding: 10px 16px 0 16px;
    }
    .search-bar input[type="text"]
    {
      flex: 0 0 260px;
      padding: 6px 10px;
      border-radius: 999px;
      border: var(--border);
      font-size: 0.85rem;
      background: var(--row-bg);
      color: var(--text);
      outline: none;
    }
    .search-bar button
    {
      padding: 6px 12px;
      border-radius: 999px;
      border: var(--boorder);
      background: var(--tint);
      color: #fff;
      font-weight: 600;
      cursor: pointer;
    }
     .search-bar button:hover
     {
        background: #0558a7ff;
     }

  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Vehículos Disponibles</h1>
    <button id="themeToggle" class="btn theme-toggle">
      <span class="sun">🌓 Tema</span><span class="moon" style="display:none;">🌓 Tema</span>
    </button>
  </div>
  <div class="panel">
    <form method="get" class="search-bar">
      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($_GET['prefijodb']);?>">
      <input type="text" name="q" value="<?php echo htmlspecialchars($searchTherm);?>" placeholder="Busca por unidad, tipo, clase, placas o peso">
      <button type="submit">Buscar</button>
    </form>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>No. Económico</th>
            <th>Tipo</th>
            <th>Clase</th>
            <th>Placas</th>
            <th>Peso Bruto Vehicular</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysql_fetch_array($result)) {
            $unidad=$row['unidad'];
            $tipo=$row['tipo'];
            $clase=$row['clase'];
            $placas=$row['placas'];
            $peso=number_format($row['peso'],2);
          ?>
          <tr>
            <td><?= $unidad ?></td>
            <td><?= $tipo ?></td>
            <td style="text-align:left"><?= $clase ?></td>
            <td style="text-align:left"><?= $placas ?></td>
            <td><?= $peso ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- PAGINADOR -->
  <div class="pagination">
    <?php if($page>1): ?>
      <a href="?prefijodb=<?= $_GET['prefijodb'] ?>&page=<?= $page-1 ?>">‹ Anterior</a>
    <?php endif; ?>
    <?php for($i=1;$i<=$total_pages;$i++): ?>
      <a class="<?= ($i==$page)?'active':'' ?>" href="?prefijodb=<?= $_GET['prefijodb'] ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($page<$total_pages): ?>
      <a href="?prefijodb=<?= $_GET['prefijodb'] ?>&page=<?= $page+1 ?>">Siguiente ›</a>
    <?php endif; ?>
  </div>
</div>
<script>
  (function(){
  var k='ui-theme', s=localStorage.getItem(k);
  if(s==='light'||s==='dark'){ document.documentElement.setAttribute('data-theme', s); }
})();


window.addEventListener('storage', function(e){
  if(e.key === 'ui-theme' && (e.newValue==='light' || e.newValue==='dark')){
    document.documentElement.setAttribute('data-theme', e.newValue);
  }
});
(function(){
  var root=document.documentElement;
  var key='ui-theme';
  var saved=localStorage.getItem(key);
  if(saved){root.setAttribute('data-theme',saved);}
  else{
    var prefersDark=window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme',prefersDark?'dark':'light');
  }
  function syncIcons(){
    var isDark=root.getAttribute('data-theme')==='dark';
    document.querySelector('#themeToggle .sun').style.display=isDark?'none':'inline';
    document.querySelector('#themeToggle .moon').style.display=isDark?'inline':'none';
  }
  syncIcons();
  document.getElementById('themeToggle').addEventListener('click',function(){
    var current=root.getAttribute('data-theme');
    var next=(current==='light')?'dark':'light';
    root.setAttribute('data-theme',next);
    localStorage.setItem(key,next);
    syncIcons();
  });
})();
  // Si está embebido en iframe, esconde el botón de tema local
  if (window.self !== window.top) {
    var btn = document.getElementById('themeToggle');
    if (btn) btn.style.display = 'none';
  }
</script>
</body>
</html>
