<?php
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
  die("Falta el prefijo de la BD");
}
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
// asegurar guion bajo final si así lo usas (si no, deja comentado):
// if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

// leer embed para ocultar botón de tema si viene desde panel
$embed = !empty($_GET['embed']);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Tarifas de Clientes · Filtros</title>

  <!-- anti-flash tema -->
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
      --bg:#ffffffff; --panel:#ffffffcc; --text:#0b0c0f; --text-soft:#5c6270; --tint:#0a84ff;
      --radius:16px; --shadow:0 8px 24px rgba(0,0,0,.08); --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff; --row-hover:#f1f4fb; --header-bg:rgba(255,255,255,.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f; --panel:#0f1218cc; --text:#f5f7fb; --text-soft:#a6aec2; --border:1px solid rgba(255,255,255,.06);
      --row-bg:#141824; --row-hover:#1a2030; --header-bg:rgba(20,24,36,.7); --shadow:0 8px 24px rgba(0,0,0,.35);
    }
    body{margin:0; font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; background:var(--bg); color:var(--text);}
    .container{max-width:900px; margin:40px auto; padding:20px;}
    .header{display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;}
    .header h1{margin:0; font-size:1.8rem; font-weight:700; letter-spacing:-.5px;}
    .btn-theme{border:none; padding:8px 14px; border-radius:999px; font-weight:700; background:linear-gradient(180deg,var(--tint), #3373b8ff); color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25); display:inline-flex; gap:8px; align-items:center;}
    .panel{background:var(--panel); border-radius:var(--radius); border:var(--border); box-shadow:var(--shadow); padding:18px;}
    .form-grid{display:grid; grid-template-columns:1fr; gap:14px;}
    @media(min-width:720px){ .form-grid{grid-template-columns:1fr 1fr;} }
    label{font-weight:600; color:var(--text-soft); margin-bottom:6px; display:block;}
    select{width:100%; padding:10px 12px; border-radius:12px; border:var(--border); background:var(--row-bg); color:var(--text); font-weight:600;}
    .actions{display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;}
    .btn{border:none; padding:10px 16px; border-radius:999px; font-weight:700; cursor:pointer;}
    .btn.primary{background:linear-gradient(180deg,var(--tint), #007aff); color:#fff;}
    .btn.ghost{background:var(--panel); color:var(--text); border:var(--border);}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Tarifas de Clientes</h1>
      <?php if (!$embed): ?>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
      <?php endif; ?>
    </div>

    <div class="panel">
      <form method="post" action="tarifasClientes.php<?php echo isset($_GET['embed']) ? '?embed=1' : '' ?>">
        <div class="form-grid">
          <div>
            <label for="Cliente">Cliente</label>
            <select name="Cliente" id="Cliente" required>
              <option value="0">Selecciona Cliente</option>
              <?php
                $resSQL = "SELECT ID, RazonSocial as Cliente FROM ".$prefijobd."clientes ORDER BY Cliente";
                $runSQL = mysql_query($resSQL, $cnx_cfdi);
                while ($row = mysql_fetch_assoc($runSQL)) {
                  echo "<option value='".$row['ID']."'>".$row['Cliente']."</option>";
                }
              ?>
            </select>
          </div>

          <div>
            <label for="Ruta">Ruta</label>
            <select name="Ruta" id="Ruta">
              <option value="0">Selecciona Ruta</option>
              <?php
                $resSQL2 = "SELECT ID, Ruta FROM ".$prefijobd."rutas ORDER BY Ruta";
                $runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
                while ($row = mysql_fetch_assoc($runSQL2)) {
                  echo "<option value='".$row['ID']."'>".$row['Ruta']."</option>";
                }
              ?>
            </select>
          </div>

          <div>
            <label for="Clase">Tipo de unidad</label>
            <select name="Clase" id="Clase">
              <option value="0">Selecciona tipo de Unidad</option>
              <?php
                $resSQL3 = "SELECT ID, Clase FROM ".$prefijobd."unidadesclase ORDER BY Clase";
                $runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
                while ($row = mysql_fetch_assoc($runSQL3)) {
                  echo "<option value='".$row['ID']."'>".$row['Clase']."</option>";
                }
              ?>
            </select>
          </div>
        </div>

        <input type="hidden" name="base" value="<?php echo htmlspecialchars($prefijobd); ?>">
        <div class="actions">
          <button class="btn primary" type="submit" name="consultar" value="1">Consultar</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
      </form>
    </div>
  </div>

<script>
// toggle tema (solo si hay botón)
(function(){
  var btn=document.getElementById('themeToggle'); if(!btn) return;
  var root=document.documentElement, key='ui-theme';
  function sync(){ var d=root.getAttribute('data-theme')==='dark';
    btn.querySelector('.sun').style.display=d?'none':'inline';
    btn.querySelector('.moon').style.display=d?'inline':'none';
  }
  sync();
  btn.addEventListener('click', function(){
    var cur=root.getAttribute('data-theme')||'light';
    var next=(cur==='light')?'dark':'light';
    root.setAttribute('data-theme', next);
    try{ localStorage.setItem(key,next);}catch(e){}
    sync();
  });
})();
</script>
</body>
</html>
