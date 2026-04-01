<?php
set_time_limit(3000);
error_reporting(0);

$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') { die("Falta el prefijo de la BD"); }
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;
$emisor   = isset($_GET['emisor']) ? (int)$_GET['emisor'] : 0;
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Vale de Salida · Detalle</title>
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
      --bg:#ffffffff;
      --panel:rgba(255,255,255,.74);
      --text:#0b0c0f;
      --text-soft:#5c6270;
      --tint:#0a84ff;
      --radius:16px;
      --shadow:0 10px 30px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
      --field:#ffffff;
      --field-border:1px solid rgba(10,12,16,.10);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:rgba(15,18,24,.72);
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 10px 30px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
      --field:#141824;
      --field-border:1px solid rgba(255,255,255,.08);
    }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Segoe UI",Roboto,Helvetica,Arial,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .container{
      max-width:980px;
      margin:40px auto;
      padding:20px;
    }
    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      margin-bottom:16px;
    }
    .header h1{
      margin:0;
      font-size:1.8rem;
      letter-spacing:-.5px;
    }
    .subtitle{ color:var(--text-soft); font-weight:600; }

    .btn-theme{
      border:none;
      padding:8px 14px;
      border-radius:999px;
      font-weight:800;
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
      cursor:pointer;
      box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex;
      gap:8px;
      align-items:center;
    }

    .panel{
      background:var(--panel);
      border:var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      backdrop-filter: blur(18px) saturate(1.2);
      -webkit-backdrop-filter: blur(18px) saturate(1.2);
      overflow:hidden;
    }
    .panel-body{ padding:18px; }

    .grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:12px;
    }
    @media (max-width: 760px){
      .grid{ grid-template-columns: 1fr; }
    }
    label{ display:block; font-weight:800; margin:4px 0 6px 0; }
    input[type="date"]{
      width:100%;
      padding:10px 12px;
      border-radius:12px;
      border:var(--field-border);
      background:var(--field);
      color:var(--text);
      outline:none;
      font-weight:700;
    }
    .actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top:14px;
    }
    .btn{
      border:none;
      padding:10px 16px;
      border-radius:999px;
      font-weight:900;
      cursor:pointer;
    }
    .btn.primary{
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
    }
    .pill{
      display:inline-flex;
      gap:8px;
      align-items:center;
      padding:8px 12px;
      border-radius:999px;
      border:var(--border);
      background:rgba(255,255,255,.35);
      color:var(--text-soft);
      font-weight:800;
      margin-top:10px;
    }
    html[data-theme="dark"] .pill{ background: rgba(255,255,255,.06); }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h1>Vale de Salida · Detalle</h1>
      <div class="subtitle">Consulta por periodo</div>
    </div>
    <button id="themeToggle" class="btn-theme" type="button">
      <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
    </button>
  </div>

  <div class="panel">
    <div class="panel-body">
      <form method="post" action="ValeSalidaDetalle.php" target="_blank">
        <div class="grid">
          <div>
            <label>Fecha inicial</label>
            <input type="date" name="fechai" required>
          </div>
          <div>
            <label>Fecha final</label>
            <input type="date" name="fechaf" required>
          </div>
        </div>

        <input type="hidden" name="base" value="<?php echo htmlspecialchars($prefijobd); ?>">
        <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>">
        <input type="hidden" name="emisor" value="<?php echo (int)$emisor; ?>">

        <div class="actions">
          <button class="btn primary" type="submit" name="consultar" value="1">Consultar</button>
        </div>

        <div class="pill">
          <span>🧩</span>
          <span>Prefijo: <?php echo htmlspecialchars($prefijobd); ?> · Sucursal: <?php echo (int)$sucursal; ?> · Emisor: <?php echo (int)$emisor; ?></span>
        </div>
      </form>
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
