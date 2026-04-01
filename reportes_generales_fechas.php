<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");
if ($pos === false) {
    $prefijobd = $prefijobd . "_";
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Reportes Generales</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg: #ffffffff;
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
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow: 0 8px 24px rgba(0,0,0,.35);
      --border: 1px solid rgba(255,255,255,.06);
      --row-hover:#1a2030;
      --header-bg: rgba(20,24,36,.7);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:960px;
      margin:40px auto;
      padding:16px;
    }
    .app-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin-bottom:16px;
    }
    .app-title{
      font-size:1.6rem;
      font-weight:700;
      letter-spacing:-0.4px;
    }
    .app-subtitle{
      font-size:0.85rem;
      color:var(--text-soft);
    }
    .btn.theme-toggle{
      display:inline-flex;
      align-items:center;
      gap:6px;
      border:var(--border);
      background:var(--panel);
      color:var(--text);
      padding:8px 12px;
      border-radius:999px;
      font-weight:600;
      cursor:pointer;
      box-shadow:0 2px 8px rgba(0,0,0,.06);
      transition:.2s;
      font-size:0.85rem;
    }
    .btn.theme-toggle:hover{ transform:translateY(-1px); }

    .panel{
      background:var(--panel);
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      padding:20px 20px 16px;
    }
    .form-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
      gap:16px 20px;
      margin-top:8px;
    }
    .field-label{
      font-size:0.8rem;
      font-weight:600;
      color:var(--text-soft);
      margin-bottom:4px;
    }
    .field-control input,
    .field-control select{
      width:100%;
      padding:8px 10px;
      border-radius:12px;
      border:1px solid rgba(120,120,140,.25);
      background:rgba(255,255,255,.9);
      font-size:0.9rem;
      outline:none;
      transition:.15s;
    }
    html[data-theme="dark"] .field-control input,
    html[data-theme="dark"] .field-control select{
      background:rgba(15,18,26,.9);
      border-color:rgba(255,255,255,.06);
      color:var(--text);
    }
    .field-control input:focus,
    .field-control select:focus{
      border-color:var(--tint);
      box-shadow:0 0 0 1px rgba(10,132,255,.25);
    }
    .actions{
      margin-top:18px;
      display:flex;
      justify-content:flex-end;
      gap:10px;
      flex-wrap:wrap;
    }
    .btn-primary{
      border:none;
      padding:8px 18px;
      border-radius:999px;
      font-weight:600;
      background:linear-gradient(180deg,var(--tint),#0051b8);
      color:#fff;
      cursor:pointer;
      font-size:0.9rem;
      box-shadow:0 6px 16px rgba(0,122,255,.25);
      transition:.2s;
    }
    .btn-primary:hover{ transform:translateY(-1px); opacity:.95; }
    .btn-outline{
      border-radius:999px;
      border:var(--border);
      background:transparent;
      padding:8px 16px;
      font-size:0.9rem;
      font-weight:500;
      cursor:pointer;
      color:var(--text-soft);
    }
    .btn-outline:hover{ background:rgba(0,0,0,.03); }

    @media (max-width:600px){
      .app-shell{ margin:20px auto; padding:12px; }
      .app-header{ flex-direction:column; align-items:flex-start; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="app-header">
    <div>
      <div class="app-title">Reportes Generales</div>
      <div class="app-subtitle">Facturas, REPs y Notas de crédito por periodo</div>
    </div>
    <button id="themeToggle" class="btn theme-toggle" aria-label="Cambiar tema">
      <span class="sun">☀️ Claro</span>
      <span class="moon" style="display:none;">🌙 Oscuro</span>
    </button>
  </div>

  <div class="panel">
  <form method="post" action="reportes_generales_vista.php" enctype="multipart/form-data">
      <div class="form-grid">
        <div>
          <div class="field-label">Fecha inicial</div>
          <div class="field-control">
            <input type="date" name="fechai" required>
          </div>
        </div>
        <div>
          <div class="field-label">Fecha final</div>
          <div class="field-control">
            <input type="date" name="fechaf" required>
          </div>
        </div>
        <div>
          <div class="field-label">Tipo de reporte</div>
          <div class="field-control">
            <select name="tiporeporte" id="tiporeporte">
              <option value="FACTURA">Facturas</option>
              <option value="REP">REPs</option>
              <option value="NOTACREDITO">Notas de crédito</option>
            </select>
          </div>
        </div>
      </div>

      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">

      <div class="actions">
        <button type="reset" class="btn-outline">Limpiar</button>
        <button type="submit" name="consultar" value="1" class="btn-primary" title="Favor de esperar… el reporte se está generando">
          Consultar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Tema global
(function(){
  var root = document.documentElement;
  var key  = 'ui-theme';
  var saved = localStorage.getItem(key);

  if(saved === 'light' || saved === 'dark'){
    root.setAttribute('data-theme', saved);
  } else {
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
  }

  function syncIcons(){
    var isDark = root.getAttribute('data-theme') === 'dark';
    var sun = document.querySelector('#themeToggle .sun');
    var moon = document.querySelector('#themeToggle .moon');
    if(sun && moon){
      sun.style.display  = isDark ? 'none'  : 'inline';
      moon.style.display = isDark ? 'inline': 'none';
    }
  }
  syncIcons();

  var btn = document.getElementById('themeToggle');
  if(btn){
    btn.addEventListener('click', function(){
      var current = root.getAttribute('data-theme') || 'light';
      var next    = (current === 'light') ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      localStorage.setItem(key, next);
      syncIcons();
    });
  }

  if (window.self !== window.top) {
    if (btn) btn.style.display = 'none';
  }

  window.addEventListener('storage', function(e){
    if(e.key === key && (e.newValue === 'light' || e.newValue === 'dark')){
      root.setAttribute('data-theme', e.newValue);
      syncIcons();
    }
  });
})();
</script>
</body>
</html>
