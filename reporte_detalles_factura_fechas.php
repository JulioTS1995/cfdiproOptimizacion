<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if (!$prefijobd) { die("Falta el prefijo de la BD"); }
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

$clientes = [];
$rutas = [];
$operadores = [];
$unidades = [];

$q = "SELECT ID, RazonSocial FROM {$prefijobd}clientes ORDER BY RazonSocial";
$res = mysqli_query($cnx_cfdi2, $q);
while($r = mysqli_fetch_assoc($res)){ $clientes[] = $r; }

$q = "SELECT ID, Ruta FROM {$prefijobd}rutas ORDER BY Ruta";
$res = mysqli_query($cnx_cfdi2, $q);
while($r = mysqli_fetch_assoc($res)){ $rutas[] = $r; }

$q = "SELECT ID, Operador FROM {$prefijobd}operadores ORDER BY Operador";
$res = mysqli_query($cnx_cfdi2, $q);
while($r = mysqli_fetch_assoc($res)){ $operadores[] = $r; }

$q = "SELECT ID, Unidad FROM {$prefijobd}unidades ORDER BY Unidad";
$res = mysqli_query($cnx_cfdi2, $q);
while($r = mysqli_fetch_assoc($res)){ $unidades[] = $r; }
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte Detalles Factura · Fechas</title>

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

  <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#ffffffff;
      --panel:#ffffffcc;
      --text:#0b0c0f;
      --text-soft:#5c6270;
      --tint:#0a84ff;
      --radius:18px;
      --shadow:0 10px 30px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
      --field:#fff;
      --field-border:1px solid rgba(10,12,16,.10);
      --row-hover:#f1f4fb;
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 10px 30px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
      --field:#141824;
      --field-border:1px solid rgba(255,255,255,.10);
      --row-hover:#1a2030;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text","Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .container{
      max-width:1100px;
      margin:40px auto;
      padding:20px;
    }
    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      margin-bottom:14px;
    }
    .title{
      margin:0;
      font-size:1.8rem;
      font-weight:800;
      letter-spacing:-.5px;
    }
    .subtitle{
      margin-top:4px;
      color:var(--text-soft);
      font-weight:600;
      font-size:.95rem;
    }
    .btn-theme{
      border:none;
      padding:9px 14px;
      border-radius:999px;
      font-weight:800;
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
      cursor:pointer;
      box-shadow:0 8px 18px rgba(0,122,255,.22);
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
      padding:14px 16px;
      border-bottom:var(--border);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .meta{
      color:var(--text-soft);
      font-weight:700;
    }
    .content{
      padding:16px;
    }
    .grid{
      display:grid;
      grid-template-columns: repeat(12, 1fr);
      gap:12px;
    }
    .col-6{ grid-column: span 6; }
    .col-4{ grid-column: span 4; }
    .col-12{ grid-column: span 12; }

    @media (max-width: 900px){
      .col-6,.col-4{ grid-column: span 12; }
    }

    label{
      display:block;
      font-weight:800;
      margin:0 0 6px 0;
      font-size:.9rem;
      color:var(--text);
    }
    .field, select, input[type="date"], input[type="text"]{
      width:100%;
      padding:10px 12px;
      border-radius:14px;
      background:var(--field);
      border:var(--field-border);
      outline:none;
      color:var(--text);
      font-weight:650;
      font-size:.95rem;
    }
    select{ cursor:pointer; }

    .actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      padding-top:4px;
    }
    .btn{
      border:none;
      padding:10px 14px;
      border-radius:999px;
      font-weight:900;
      cursor:pointer;
      font-size:.95rem;
      display:inline-flex;
      align-items:center;
      gap:8px;
    }
    .btn.excel{
      background:linear-gradient(180deg,#2ecc71,#1ea85a);
      color:#fff;
      box-shadow:0 8px 18px rgba(46,204,113,.22);
    }
    .hint{
      margin-top:10px;
      color:var(--text-soft);
      font-weight:650;
      font-size:.9rem;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1 class="title">Reporte Detalles Factura</h1>
        
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>



      <div class="content">
        <form method="post" action="reporte_detalles_factura.php" target="_blank">
          <div class="grid">
            <div class="col-6">
              <label>Fecha Inicio</label>
              <input type="date" name="txtDesde" class="field" required>
            </div>
            <div class="col-6">
              <label>Fecha Fin</label>
              <input type="date" name="txtHasta" class="field" required>
            </div>

            <div class="col-6">
              <label>Cliente</label>
              <select name="cliente" class="field">
                <option value="0">- Seleccione -</option>
                <?php foreach($clientes as $c): ?>
                  <option value="<?php echo (int)$c['ID']; ?>"><?php echo htmlspecialchars($c['RazonSocial']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label>Ruta</label>
              <select name="ruta" class="field">
                <option value="0">- Seleccione -</option>
                <?php foreach($rutas as $r): ?>
                  <option value="<?php echo (int)$r['ID']; ?>"><?php echo htmlspecialchars($r['Ruta']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label>Operador</label>
              <select name="operador" class="field">
                <option value="0">- Seleccione -</option>
                <?php foreach($operadores as $o): ?>
                  <option value="<?php echo (int)$o['ID']; ?>"><?php echo htmlspecialchars($o['Operador']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label>Unidad</label>
              <select name="unidad" class="field">
                <option value="0">- Seleccione -</option>
                <?php foreach($unidades as $u): ?>
                  <option value="<?php echo (int)$u['ID']; ?>"><?php echo htmlspecialchars($u['Unidad']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label>XFolio</label>
              <input type="text" name="txtxfolio" class="field" placeholder="Ej: F-12345">
            </div>

            <div class="col-6">
              <label>Estatus</label>
              <select name="estatus" class="field">
                <option value="0">Selecciona Estatus</option>
                <option value="vigente">Vigente</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>

            <div class="col-12">
              <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">
              <div class="actions">
                <button type="submit" name="btnEnviar" value="Excel" class="btn excel">📊 Exportar Excel</button>
              </div>
             
            </div>
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
