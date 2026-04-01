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
        r.XFolio LIKE '%$searchThermSafe%' OR
        DATE_FORMAT(r.Creado, '%d-%m-%Y') LIKE '%$searchThermSafe%' OR
        c.RazonSocial LIKE '%$searchThermSafe%' OR
        ru.Ruta LIKE '%$searchThermSafe%' OR
        o.Operador LIKE '%$searchThermSafe%' OR
        u.Unidad LIKE '%$searchThermSafe%' OR
        u.Placas LIKE '%$searchThermSafe%' OR
        ur1.Unidad LIKE '%$searchThermSafe%' OR
        ur2.Unidad LIKE '%$searchThermSafe%'
    ) ";
}


// ----- PAGINACIÓN -----
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// base from/where
$baseFromWhere = "
FROM ".$prefijobd."remisiones AS r
LEFT JOIN ".$prefijobd."clientes AS c ON r.CargoACliente_RID = c.ID
LEFT JOIN ".$prefijobd."rutas AS ru ON r.Ruta_RID = ru.ID
LEFT JOIN ".$prefijobd."operadores AS o ON r.Operador_RID = o.ID
LEFT JOIN ".$prefijobd."unidades AS u ON r.Unidad_RID = u.ID
LEFT JOIN ".$prefijobd."unidades AS ur1 ON r.uRemolqueA_RID = ur1.ID
LEFT JOIN ".$prefijobd."unidades AS ur2 ON r.uRemolqueB_RID = ur2.ID
WHERE (r.Creado >= NOW() - INTERVAL 30 DAY) 
  AND (r.EstatusTerminadoT IS NULL OR r.EstatusTerminadoT='') ";

$count_q = "SELECT COUNT(*) AS total ".$baseFromWhere. " ".$whereSearch ." ;";

$count_r = mysql_query($count_q, $cnx_cfdi);
$count_row = mysql_fetch_assoc($count_r);
$total_rows = $count_row['total'];
$total_pages = ceil($total_rows / $per_page);

// Consulta principal filtro y limit

$query = "SELECT
            r.*,
            c.RazonSocial as nom_cliente,
            ru.Ruta as nom_ruta,
            o.Operador as nom_operador,
            u.Unidad as nom_unidad,
            u.Placas as nom_unidad_placa,
            ur1.Unidad as nom_remolque,
            ur2.Unidad as nom_remolque2
          ".$baseFromWhere. " ".$whereSearch ." 
          ORDER BY r.ID DESC
          LIMIT $per_page OFFSET $offset;";
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
  <title>Estatus Viajes 30 días</title>
  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #ffffffff;
      --panel: #ffffffcc;
      --text: #0b0c0f;
      --text-soft: #5c6270;
      --tint: #0a84ff;
      --radius: 16px;
      --shadow: 0 8px 24px rgba(0,0,0,.08);
      --border: 1px solid rgba(10,12,16,.08);
      --row-hover: #f1f4fb;
      --header-bg: rgba(255,255,255,.72);
    }
    body {
      margin: 0;
      font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial;
      background: var(--bg);
      color: var(--text);
    }
    .container { 
      max-width: 1700px; 
      margin: 40px auto; 
      padding: 20px; 
    }
    .header { 
      text-align: center; 
      margin-bottom: 20px; 
    }
    .header h1 { 
      font-size: 1.8rem; 
      font-weight: 700; 
      letter-spacing: -0.5px; 
    }
    .panel {
      background: var(--panel);
      backdrop-filter: blur(18px) saturate(1.2);
      -webkit-backdrop-filter: blur(18px) saturate(1.2);
      border-radius: var(--radius);
      border: var(--border);
      box-shadow: var(--shadow);
      overflow: hidden;
    }
    .table-container { 
      max-height: 700px; 
      overflow-y: auto; 
    }
    table { 
      width: 100%; 
      border-collapse: separate; 
      font-size: 0.9rem; 
    }
    thead th {
      position: sticky; 
      top: 0;
      background: var(--header-bg);
      font-weight: 600; 
      padding: 12px; 
      text-align: center;
      font-size: 0.8rem; 
      color: var(--text-soft); 
      border-bottom: var(--border);
      backdrop-filter: blur(10px);
    }
    tbody td { 
       text-align: center;
       padding: 12px;
       background: #fff;
       border-bottom: 1px solid rgba(0,0,0,.05);
       transition: background .25s;
     }
    tbody tr:hover td { 
      background: var(--row-hover); 
    }
    .btn {
      appearance: none; 
      border: none;
      background: linear-gradient(180deg, #0a84ff, #007aff);
      color: #fff; 
      padding: 6px 12px; 
      border-radius: 999px;
      font-weight: 600; 
      cursor: pointer; 
      transition: .25s;
    }
    .btn:hover { 
      opacity: .9; 
      transform: translateY(-1px); 
    }
    .pagination { 
      margin: 20px 0; 
      display: flex; 
      gap: 6px; 
      justify-content: center; 
      flex-wrap: wrap; 
    }
    .pagination a {
      padding: 6px 12px; 
      border-radius: 999px; 
      border: var(--border);
      background: #fff; 
      text-decoration: none; 
      color: var(--text); 
      font-weight: 600;
      transition:.2s;
    }
    .pagination a:hover { 
      background: var(--row-hover); 
    }
    .pagination .active { 
      background: var(--tint); 
      color:#fff; 
      border:none; 
    }
    :root{
  --bg: #ffffffff;              /* preferencia del usuario */
  --panel: #ffffffcc;
  --text: #0b0c0f;
  --text-soft: #5c6270;
  --tint: #0a84ff;
  --radius: 16px;
  --shadow: 0 8px 24px rgba(0,0,0,.08);
  --border: 1px solid rgba(10,12,16,.08);
  --row-hover: #f1f4fb;
  --header-bg: rgba(221, 221, 221, 0.72);
}

html[data-theme="dark"]{
  --bg:#0b0c0f;
  --panel: #0f1218cc;
  --text:#f5f7fb;
  --text-soft:#a6aec2;
  --tint:#0a84ff;
  --shadow: 0 8px 24px rgba(0,0,0,.35);
  --border: 1px solid rgba(255,255,255,.06);
  --row-hover:#1a2030;
  --header-bg: rgba(20,24,36,.7);
}

/* Asegúrate que body use --bg */
body{ background: var(--bg); color:var(--text); }

/* Botón toggle iOS-like */
.btn.theme-toggle{
  display:inline-flex; align-items:center; gap:6px;
  border: var(--border);
  background: var(--panel);
  color: var(--text);
  padding: 8px 12px;
  border-radius: 999px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
  transition:.2s ease;
}
tbody td {
  text-align: center;
  padding: 12px;
  background: var(--row-bg);        
  border-bottom: 1px solid rgba(0,0,0,.05);
  transition: background .25s;
}
tbody tr:hover td { background: var(--row-hover); }

/* Botones de paginación */
.pagination a {
  padding: 6px 12px;
  border-radius: 999px;
  border: var(--border);
  background: var(--panel);         
  text-decoration: none;
  color: var(--text);
  font-weight: 600;
  transition:.2s;
}
.pagination a:hover { background: var(--row-hover); }
.pagination .active {
  background: var(--tint);
  color:#fff;
  border:none;
}
.btn.theme-toggle:hover{ transform: translateY(-1px); }
/* --- Modal overlay --- */
.modal {
  display: none; 
  position: fixed; 
  z-index: 2000; 
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.25);
  backdrop-filter: blur(4px);
}

/* Contenido centrado */
.modal-content {
  background: #ffffff18;
  margin: 5% auto;
  padding: 0;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  width: 60%;
  height: 62%;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* Botón de cerrar */
.modal .close {
  position: absolute;
  top: 10px; right: 20px;
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--text);
  cursor: pointer;
  z-index: 2100;
}

/* Iframe que ocupa el modal */
#mapFrame {
  flex: 1;
  width: 100%;
  border: none;
  border-radius: 0 0 var(--radius) var(--radius);
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
  <div class="header"><h1>Estatus Viajes (últimos 30 días)  </h1>
<button id="themeToggle" class="btn theme-toggle" aria-label="Cambiar tema">
  <span class="sun">🌓 Tema</span>
  <span class="moon" style="display:none;">🌓 Tema</span>
</button></div>
  <div class="panel">
    <form method="get" class="search-bar">
      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($_GET['prefijodb']);?>">
      <input type="text" name="q" value="<?php echo htmlspecialchars($searchTherm);?>" placeholder="Busca por folio, fecha, unidad, etc...">
      <button type="submit">Buscar</button>
    </form>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Folio</th><th>Creado</th><th>Cliente</th><th>Ruta</th><th>Operador</th>
            <th>Unidad</th><th>Placa</th><th>Remolque 1</th><th>Remolque 2</th>
            <th>Tracking</th><th>Ubicación</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = mysql_fetch_array($result)) {
          $id_remisiones    = $row['ID'];
          $XFolio           = $row['XFolio'];
          $Creado           = date("d-m-Y", strtotime($row['Creado']));
          $nom_cliente      = $row['nom_cliente'];
          $nom_ruta         = $row['nom_ruta'];
          $nom_operador     = $row['nom_operador'];
          $nom_unidad       = $row['nom_unidad'];
          $nom_unidad_placa = $row['nom_unidad_placa'];
          $nom_remolque     = $row['nom_remolque'];
          $id_remolque2     = $row['nom_remolque2'];

         
          $re_tracking2="";

          $r = mysql_query("
                  SELECT 
                  Estatus FROM ".$prefijobd."remisionesestatus2 
                  WHERE FolioEstatus2_RID=".$id_remisiones." 
                  ORDER BY id DESC LIMIT 1;
                  ",$cnx_cfdi);
          if($f=mysql_fetch_array($r)) $re_tracking2=$f['Estatus'];
            // último punto válido (si el último es NULL, toma anterior)
            $coordsRow=mysql_fetch_assoc(mysql_query("
            SELECT longitud, latitud
            FROM ".$prefijobd."remisionesestatus2
            WHERE FolioEstatus2_RID=".$id_remisiones."
            ORDER BY 
              (longitud IS NULL OR longitud='' OR latitud IS NULL OR latitud='' OR (latitud=0 AND longitud=0)) ASC,
              id DESC
            LIMIT 1;
          ",$cnx_cfdi));


          $lat = isset($coordsRow['latitud'])  && $coordsRow['latitud']  !== '' ? $coordsRow['latitud']  : 0;
          $lng = isset($coordsRow['longitud']) && $coordsRow['longitud'] !== '' ? $coordsRow['longitud'] : 0;

          if ($lat == 0 && $lng == 0) {
            // tratar como sin coordenadas válidas
          }
        ?>
          <tr>
            <td><?= $XFolio ?></td>
            <td><?= $Creado ?></td>
            <td style="text-align:left"><?= $nom_cliente ?></td>
            <td style="text-align:left"><?= $nom_ruta ?></td>
            <td style="text-align:left"><?= $nom_operador ?></td>
            <td style="text-align:left"><?= $nom_unidad ?></td>
            <td><?= $nom_unidad_placa ?></td>
            <td><?= $nom_remolque ?></td>
            <td><?= $nom_remolque2 ?></td>
            <td><?= $re_tracking2 ?></td>
            <td><button class="btn open-map" data-lat="<?= $lat ?>" data-lng="<?= $lng ?>">Ver mapa</button></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
    <!-- PAGINADOR -->
     <div class="pagination">
          <?php if($page>1): ?>
            <a href="?<?php echo $baseQS; ?>&page=<?= $page-1 ?>">‹ Anterior</a>
          <?php endif; ?>

          <?php for($i=1;$i<=$total_pages;$i++): ?>
            <a class="<?= ($i==$page)?'active':'' ?>" href="?<?php echo $baseQS; ?>&page=<?= $i ?>"><?= $i ?></a>
          <?php endfor; ?>

          <?php if($page<$total_pages): ?>
            <a href="?<?php echo $baseQS; ?>&page=<?= $page+1 ?>">Siguiente ›</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
      <div id="mapModal" class="modal">
        <div class="modal-content">
          <span class="close">&times;</span>
          <iframe id="mapFrame" src="" frameborder="0"></iframe>
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
  var root = document.documentElement; // <html>
  var key = 'ui-theme'; // localStorage key

  // 1) Cargar preferencia guardada o respetar sistema
  var saved = localStorage.getItem(key);
  if(saved === 'light' || saved === 'dark'){
    root.setAttribute('data-theme', saved);
  } else {
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
  }

  // 2) Sincronizar iconos
  function syncIcons(){
    var isDark = root.getAttribute('data-theme') === 'dark';
    var sun = document.querySelector('#themeToggle .sun');
    var moon = document.querySelector('#themeToggle .moon');
    if(sun && moon){
      sun.style.display = isDark ? 'none' : 'inline';
      moon.style.display = isDark ? 'inline' : 'none';
    }
  }
  syncIcons();

  // 3) Toggle al hacer clic
  var btn = document.getElementById('themeToggle');
  if(btn){
    btn.addEventListener('click', function(){
      var current = root.getAttribute('data-theme') || 'light';
      var next = (current === 'light') ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      localStorage.setItem(key, next);
      syncIcons();
    });
  }

  // 4) Escuchar cambios del sistema SOLO si el usuario no ha elegido manualmente
  if(!saved && window.matchMedia){
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e){
      root.setAttribute('data-theme', e.matches ? 'dark' : 'light');
      syncIcons();
    });
  }
})();
  // Si está embebido en iframe, esconde el botón de tema local
  if (window.self !== window.top) {
    var btn = document.getElementById('themeToggle');
    if (btn) btn.style.display = 'none';
  }
  // Modal del mapa
// Modal del mapa
var modal=document.getElementById("mapModal");
var iframe=document.getElementById("mapFrame");
var span=document.querySelector(".modal .close");

document.querySelectorAll(".open-map").forEach(btn=>{
  btn.addEventListener("click",function(){
    let lat=this.dataset.lat;
    let lng=this.dataset.lng;
    iframe.src="ET/mapaTracking.php?latitud="+lat+"&longitud="+lng;
    modal.style.display="block";
  });
});
span.onclick=function(){ modal.style.display="none"; iframe.src=""; }
window.onclick=function(e){ if(e.target==modal){ modal.style.display="none"; iframe.src=""; } }

</script>
</body>
</html>
