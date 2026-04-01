<?php
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

// Normalizar prefijo con guion bajo al final
$prefijo_raw = $_GET['prefijodb'];
$prefijo_raw = str_replace(array("'", '"', ";"), "", $prefijo_raw);
if (strpos($prefijo_raw, "_") === false) {
    $prefijobd = $prefijo_raw . "_";
} else {
    $prefijobd = $prefijo_raw;
}

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Cargar proveedores
$proveedores = array();
$sqlProv = "SELECT ID, RazonSocial FROM ".$prefijobd."Proveedores ORDER BY RazonSocial";
$resProv = mysqli_query($cnx_cfdi2, $sqlProv);
if ($resProv) {
    while ($row = mysqli_fetch_assoc($resProv)) {
        $proveedores[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Centro de Costos · Filtros</title>
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
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
    }
    body{
      margin:0;
      font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial;
      background:var(--bg);
      color:var(--text);
    }
    .container{
      max-width:600px;
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
      font-size:1.6rem;
      font-weight:700;
      letter-spacing:-.5px;
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
      padding:20px;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .field{
      margin-bottom:14px;
    }
    label{
      display:block;
      margin-bottom:4px;
      font-size:.9rem;
      font-weight:600;
      color:var(--text-soft);
    }
    select,input[type="date"]{
      width:100%;
      padding:9px 11px;
      border-radius:999px;
      border:var(--border);
      background:var(--bg);
      color:var(--text);
      font-size:.95rem;
    }
    .actions{
      margin-top:16px;
      display:flex;
      gap:8px;
      justify-content:flex-end;
    }
    .btn{
      border:none;
      padding:9px 16px;
      border-radius:999px;
      font-weight:700;
      cursor:pointer;
      font-size:.95rem;
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
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Centro de Costos</h1>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="post" action="centros_costos_ardica.php">
        <div class="field">
          <label for="proveedor">Proveedor</label>
          <select name="proveedor" id="proveedor" required>
            <option value="0">Todos los proveedores</option>
            <?php foreach($proveedores as $p){ ?>
              <option value="<?php echo $p['ID']; ?>"><?php echo htmlspecialchars($p['RazonSocial']); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="field" style="width:96%;">
          <label for="fechai">Fecha inicial</label>
          <input type="date" name="fechai" id="fechai" required />
        </div>
        <div class="field" style="width:96%;">
          <label for="fechaf">Fecha final</label>
          <input type="date" name="fechaf" id="fechaf" required />
        </div>
        <input type="hidden" name="base" value="<?php echo htmlspecialchars($prefijobd); ?>" />
        <div class="actions">
          <button type="reset" class="btn ghost">Limpiar</button>
          <button type="submit" name="consultar" value="1" class="btn primary">Consultar</button>
        </div>
      </form>
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
