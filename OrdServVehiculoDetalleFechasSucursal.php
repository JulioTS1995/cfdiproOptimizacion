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

$emisor   = isset($_GET['emisor']) ? intval($_GET['emisor']) : 0;
$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Cargar Unidades
$unidades = array();
$sqlU = "SELECT ID, Unidad FROM ".$prefijobd."Unidades ORDER BY Unidad";
$resU = mysqli_query($cnx_cfdi2, $sqlU);
if ($resU) {
    while ($row = mysqli_fetch_assoc($resU)) {
        $unidades[] = $row;
    }
}

//cargar emisores
//systemsettingsconsulta si es multi
$sqlSS = "SELECT MultiEmisor FROM {$prefijobd}systemsettings";
$resSS = mysqli_query($cnx_cfdi2, $sqlSS);
if ($resSS) {
  while ($rowSS = mysqli_fetch_assoc($resSS)) {
     $esMulti = $rowSS['MultiEmisor'];
  }
}
if ($esMulti != 0) {
  $emisores = array();
  $sqlEm = "SELECT ID, RazonSocial FROM {$prefijobd}emisores";
  $resEm = mysqli_query($cnx_cfdi2, $sqlEm);
  if ($resEm) {
    while ($rowEm = mysqli_fetch_assoc($resEm)) {
      $emisores [] = $rowEm;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Órdenes de Servicio por Vehículo · Filtros</title>
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
    select, input[type="date"]{
      width:100%;
      padding:9px 11px;
      border-radius:999px;
      border:var(--border);
      background:var(--bg);
      color:var(--text);
      font-size:.95rem;
      outline:none;
    }
    select{
      appearance:none;
      -webkit-appearance:none;
      -moz-appearance:none;
      padding-right:44px;
      cursor:pointer;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23606a7a' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
      background-repeat:no-repeat;
      background-position:right 14px center;
      background-size:18px 18px;
    }
    html[data-theme="dark"] select{
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23b8c0d4' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
    }
    select:focus, input[type="date"]:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
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
      <h1>Órdenes de Servicio por Vehículo · Filtros</h1>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="post" action="OrdServVehiculoDetalle.php" autocomplete="off">
      
      <?php if ($esMulti != 0) { ?>
        <div class="field" style="width:96%;">
        <label for="emisor">Emisores</label>
        <select name="emisor" id="emisor" required>
        <option value="0">Selecciona Emisor...</option>
        
        <?php foreach($emisores as $e): ?>
              <option value="<?php echo (int)$e['ID']; ?>"><?php echo htmlspecialchars($e['RazonSocial']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
     <?php   } else { ?>
      <input type="hidden" name="emisor" value="<?php echo (int)$emisor; ?>" />
 <?php    }
         
         ?>
        <div class="field" style="width:96%;">
          <label for="unidad">Unidad</label>
          <select name="unidad" id="unidad" required>
            <option value="">Selecciona Unidad...</option>
            <option value="0">TODAS</option>
            <?php foreach($unidades as $u): ?>
              <option value="<?php echo (int)$u['ID']; ?>"><?php echo htmlspecialchars($u['Unidad']); ?></option>
            <?php endforeach; ?>
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
        
        <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>" />

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
