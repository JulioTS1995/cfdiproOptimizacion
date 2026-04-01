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

$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;

require_once('cnx_cfdi3.php');
mysqli_select_db($cnx_cfdi3, $database_cfdi);
mysqli_query($cnx_cfdi3, "SET NAMES 'utf8'");

// detectar si existe Sucursal_RID en clientes
$clientes_table = $prefijobd."clientes";
$has_sucursal = 0;
$sqlChk = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA='".mysqli_real_escape_string($cnx_cfdi3,$database_cfdi)."'
             AND TABLE_NAME='".mysqli_real_escape_string($cnx_cfdi3,$clientes_table)."'
             AND COLUMN_NAME='Sucursal_RID'
           LIMIT 1";
$resChk = mysqli_query($cnx_cfdi3, $sqlChk);
if ($resChk && mysqli_num_rows($resChk) > 0) $has_sucursal = 1;

// ===== AJAX clientes (MISMO archivo, como tú lo haces) =====
if (isset($_GET['ajax']) && $_GET['ajax'] == 'clientes') {
    header('Content-Type: application/json; charset=utf-8');

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $q = mysqli_real_escape_string($cnx_cfdi3, $q);

    $where = " WHERE 1=1 ";
    if ($q != '') {
        $where .= " AND (RazonSocial LIKE '%$q%' OR RFC LIKE '%$q%') ";
    }
    if ($sucursal != 0 && $has_sucursal == 1) {
        $where .= " AND Sucursal_RID = ".(int)$sucursal." ";
    }

    $sql = "SELECT ID, RazonSocial, RFC
            FROM $clientes_table
            $where
            ORDER BY RazonSocial
            LIMIT 25";

    $res = mysqli_query($cnx_cfdi3, $sql);
    $items = array();

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $txt = $r['RazonSocial'];
            if ($r['RFC'] != '') $txt .= " · ".$r['RFC'];
            $items[] = array("id"=>$r['ID'], "text"=>$txt);
        }
    }

    echo json_encode(array("ok"=>true, "items"=>$items));
    exit;
}

// defaults fechas
$hoy = date('Y-m-d');
$menos30 = date('Y-m-d', strtotime('-30 days'));
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Ventas por Cliente · Filtros</title>
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
    .container{ max-width:600px; margin:40px auto; padding:20px; }
    .header{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      margin-bottom:16px; flex-wrap:wrap;
    }
    .header h1{ margin:0; font-size:1.6rem; font-weight:700; letter-spacing:-.5px; }
    .btn-theme{
      border:none; padding:8px 14px; border-radius:999px; font-weight:700;
      background:linear-gradient(180deg,var(--tint), #3373b8ff);
      color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex; gap:8px; align-items:center;
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
    .field{ margin-bottom:14px; position:relative; }
    label{
      display:block; margin-bottom:4px; font-size:.9rem; font-weight:600; color:var(--text-soft);
    }
    select, input[type="date"], input[type="text"]{
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
      appearance:none; -webkit-appearance:none; -moz-appearance:none;
      padding-right:44px; cursor:pointer;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23606a7a' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
      background-repeat:no-repeat; background-position:right 14px center; background-size:18px 18px;
    }
    html[data-theme="dark"] select{
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23b8c0d4' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
    }
    select:focus, input[type="date"]:focus, input[type="text"]:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }
    .actions{
      margin-top:16px;
      display:flex;
      gap:8px;
      justify-content:flex-end;
      flex-wrap:wrap;
    }
    .btn{
      border:none;
      padding:9px 16px;
      border-radius:999px;
      font-weight:700;
      cursor:pointer;
      font-size:.95rem;
    }
    .btn.primary{ background:linear-gradient(180deg,var(--tint), #007aff); color:#fff; }
    .btn.ghost{ background:var(--panel); color:var(--text); border:var(--border); }

    /* dropdown clientes */
    .drop{
      position:absolute;
      top:100%;
      left:0; right:0;
      margin-top:8px;
      background:var(--panel);
      border:var(--border);
      box-shadow:var(--shadow);
      border-radius:16px;
      overflow:hidden;
      display:none;
      z-index:50;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .item{
      padding:10px 14px;
      font-size:.95rem;
      cursor:pointer;
      border-bottom:1px solid rgba(10,12,16,.06);
    }
    html[data-theme="dark"] .item{ border-bottom:1px solid rgba(255,255,255,.06); }
    .item:last-child{ border-bottom:none; }
    .item:hover{ background:rgba(10,132,255,.10); }
    .hint{ margin-top:6px; font-size:.85rem; color:var(--text-soft); }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Ventas por Cliente · Filtros</h1>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="get" action="reporte_ventas_por_cliente.php" autocomplete="off">
        <div class="field">
          <label for="fechai">Fecha inicial</label>
          <input type="date" name="fechai" id="fechai" required value="<?php echo htmlspecialchars($menos30); ?>" />
        </div>

        <div class="field">
          <label for="fechaf">Fecha final</label>
          <input type="date" name="fechaf" id="fechaf" required value="<?php echo htmlspecialchars($hoy); ?>" />
        </div>

        <div class="field">
          <label for="cliente_txt">Cliente (keyup)</label>
          <input type="text" name="cliente_txt" id="cliente_txt" placeholder="Todos (vacío) o escribe para buscar..." />
          <input type="hidden" name="cliente_id" id="cliente_id" value="0" />
          <div class="drop" id="cliente_drop"></div>
          <div class="hint">Escribe 2+ letras y selecciona. Para todos: borra el texto.</div>
        </div>

        <div class="field">
          <label for="moneda">Moneda</label>
          <select name="moneda" id="moneda">
            <option value="TODOS">TODOS</option>
            <option value="PESOS">PESOS</option>
            <option value="DOLARES">DOLARES</option>
          </select>
        </div>

        <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>" />
        <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>" />

        <div class="actions">
          <button type="reset" class="btn ghost" id="btnClear">Limpiar</button>
          <button type="submit" class="btn primary">Consultar</button>
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

// keyup clientes (ajax en el mismo archivo)
(function(){
  var inp = document.getElementById('cliente_txt');
  var hid = document.getElementById('cliente_id');
  var drop = document.getElementById('cliente_drop');
  var t = null;

  function closeDrop(){ drop.style.display='none'; drop.innerHTML=''; }
  function openDrop(){ drop.style.display='block'; }

  function pick(id, text){
    hid.value = id;
    inp.value = text;
    closeDrop();
  }

  function load(q){
    var url = "<?php echo htmlspecialchars(basename(__FILE__)); ?>?prefijodb=<?php echo htmlspecialchars($prefijobd); ?>&sucursal=<?php echo (int)$sucursal; ?>&ajax=clientes&q="+encodeURIComponent(q);
    fetch(url).then(function(r){ return r.json(); }).then(function(js){
      if(!js || !js.ok){ closeDrop(); return; }
      var it = js.items || [];
      if(it.length===0){ closeDrop(); return; }

      var html = '';
      html += '<div class="item" data-id="0" data-text="">(Todos)</div>';
      for(var i=0;i<it.length;i++){
        var txt = (it[i].text||'');
        html += '<div class="item" data-id="'+it[i].id+'" data-text="'+txt.replace(/"/g,'&quot;')+'">'+txt+'</div>';
      }
      drop.innerHTML = html;
      openDrop();

      var els = drop.querySelectorAll('.item');
      for(var k=0;k<els.length;k++){
        els[k].addEventListener('click', function(){
          pick(this.getAttribute('data-id'), this.getAttribute('data-text'));
        });
      }
    }).catch(function(){ closeDrop(); });
  }

  inp.addEventListener('keyup', function(){
    var v = (inp.value || '').trim();
    if(v === ''){
      hid.value = '0';
      closeDrop();
      return;
    }
    hid.value = '0';
    clearTimeout(t);
    t = setTimeout(function(){
      if(v.length < 2){ closeDrop(); return; }
      load(v);
    }, 180);
  });

  document.addEventListener('click', function(e){
    if(e.target !== inp && !drop.contains(e.target)) closeDrop();
  });

  document.getElementById('btnClear').addEventListener('click', function(){
    hid.value='0';
    closeDrop();
  });
})();
</script>
</body>
</html>
