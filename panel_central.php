<?php
// panel_central.php
if (!isset($_GET['prefijodb']) || $_GET['prefijodb']==='') {
  header('Content-Type: text/html; charset=utf-8');
  die('Falta el prefijo de la BD. Úsalo así: panel_central.php?prefijodb=optimizacion_');
}
$prefijo = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['prefijodb']);
if (strpos($prefijo, '_') === false) { $prefijo .= '_'; }
$prefijo_q = rawurlencode($prefijo);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Cuadro de Control</title>

  <!-- Anti-flash de tema -->
  <script>
    (function(){
      var k='ui-theme', s=localStorage.getItem(k);
      if(s==='light'||s==='dark'){ document.documentElement.setAttribute('data-theme',s); }
      else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        document.documentElement.setAttribute('data-theme','dark');
      } else { document.documentElement.setAttribute('data-theme','light'); }
    })();
  </script>

  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#ffffffff;        /* tu preferencia */
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
      --row-bg:#141824;
      --row-hover:#1a2030;
      --header-bg:rgba(20,24,36,.7);
      --border:1px solid rgba(255,255,255,.06);
      --shadow:0 8px 24px rgba(0,0,0,.35);
    }

    body{ margin:0; font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; background:var(--bg); color:var(--text); }
    .container{ max-width:1700px; margin:40px auto; padding:20px; }
    .header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .header h1{ font-size:1.8rem; font-weight:700; margin:0; }

    /* Botón cápsula consistente con tus otros módulos */
    .btn-theme{
      border:none; padding:8px 14px; border-radius:999px; font-weight:700;
      background:linear-gradient(180deg,var(--tint), #3373b8ff); color:#fff; cursor:pointer;
      box-shadow:0 6px 16px rgba(0,122,255,.25); transition:.2s;
      display:inline-flex; align-items:center; gap:8px;
    }
    .btn-theme:hover{ transform:translateY(-1px); }
    .btn-theme .sun{ display:inline; }
    .btn-theme .moon{ display:none; }
    html[data-theme="dark"] .btn-theme .sun{ display:none; }
    html[data-theme="dark"] .btn-theme .moon{ display:inline; }

    /* Tabs menú */
    .tabs{ display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
    .tabs button{
      padding:10px 18px; border-radius:999px; border:var(--border);
      background:var(--panel); color:var(--text); font-weight:600; cursor:pointer; transition:.2s;
    }
    .tabs button.active{ background:var(--tint); color:#fff; border:none; }
    .tabs button:hover{ background:var(--row-hover); }

    /* Panel iframe */
    .panel{
      background:var(--panel);
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
      border-radius:var(--radius); border:var(--border); box-shadow:var(--shadow); overflow:hidden;
    }
    iframe{ width:100%; height:90vh; border:none; border-radius:var(--radius); }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Cuadro de Control</h1>
      <!-- ÚNICO botón de tema para todo -->
      <button id="themeToggle" class="btn-theme" type="button" aria-label="Cambiar tema">
        <span class="sun">☀️</span>
        <span class="moon">🌙</span>
        <span class="label">Tema</span>
      </button>
    </div>

    <!-- Tabs con prefijo propagado -->
    <div class="tabs">
      <button class="active" data-url="cuadro_control_estatus_viaje.php?prefijodb=<?php echo $prefijo_q; ?>">Estatus Viajes</button>
      <button data-url="cuadro_control_vehiculos_disponibles.php?prefijodb=<?php echo $prefijo_q; ?>">Vehículos Disponibles</button>
      <button data-url="cuadro_control_vehiculos_mtto.php?prefijodb=<?php echo $prefijo_q; ?>">Vehículos en Mantenimiento</button>
    </div>

    <div class="panel">
      <iframe id="contentFrame" src="cuadro_control_estatus_viaje.php?prefijodb=<?php echo $prefijo_q; ?>"></iframe>
    </div>
  </div>

  <script>
    // Toggle tema del panel: guarda en localStorage -> los iframes escuchan 'storage' y se actualizan
    (function(){
      var root=document.documentElement, key='ui-theme';
      document.getElementById('themeToggle').addEventListener('click',function(){
        var cur=root.getAttribute('data-theme')||'light';
        var next=(cur==='light')?'dark':'light';
        root.setAttribute('data-theme',next);
        try{ localStorage.setItem(key,next); }catch(e){}
        // Opcional: forzar iconos correctos (CSS ya lo hace por data-theme)
      });
    })();

    // Tabs navegación -> cambia el src del iframe
    (function(){
      var tabs=document.querySelectorAll('.tabs button');
      var frame=document.getElementById('contentFrame');
      tabs.forEach(function(btn){
        btn.addEventListener('click', function(){
          tabs.forEach(function(b){ b.classList.remove('active'); });
          btn.classList.add('active');
          frame.src = btn.getAttribute('data-url');
        });
      });
    })();
  </script>
</body>
</html>
