<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if ($prefijobd === '') { die("Falta el prefijo de la BD"); }
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;
$emisor   = isset($_GET['emisor']) ? (int)$_GET['emisor'] : 0;

// AJAX: Buscar servicios (selección múltiple)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'buscar_servicios') {
    header('Content-Type: application/json; charset=utf-8');

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $q = mysqli_real_escape_string($cnx_cfdi2, $q);

    $sql = "SELECT ID, Descripcion AS Nombre
            FROM {$prefijobd}servicios
            WHERE Descripcion LIKE '%{$q}%'
            ORDER BY Descripcion
            LIMIT 50";

    $res = mysqli_query($cnx_cfdi2, $sql);
    $out = array();

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = array(
                'id' => (int)$row['ID'],
                'nombre' => $row['Nombre']
            );
        }
    }

    echo json_encode($out);
    exit;
}

// AJAX: Buscar operadores (selección única)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'buscar_operadores') {
    header('Content-Type: application/json; charset=utf-8');

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $q = mysqli_real_escape_string($cnx_cfdi2, $q);

    $sql = "SELECT ID, Operador AS Nombre
            FROM {$prefijobd}operadores
            WHERE Operador LIKE '%{$q}%'
            ORDER BY Operador
            LIMIT 50";

    $res = mysqli_query($cnx_cfdi2, $sql);
    $out = array();

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = array(
                'id' => (int)$row['ID'],
                'nombre' => $row['Nombre']
            );
        }
    }

    echo json_encode($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Reporte Base Tractosoft · Detalle</title>
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

      --row-bg: rgba(255,255,255,.42);
      --row-hover: rgba(10,132,255,.08);
      --danger:#ff3b30;
      --ok:#34c759;
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

      --row-bg: rgba(255,255,255,.06);
      --row-hover: rgba(10,132,255,.14);
    }

    *{ box-sizing:border-box; }

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
      font-size:1.85rem;
      letter-spacing:-.6px;
      line-height:1.1;
    }
    .subtitle{
      color:var(--text-soft);
      font-weight:700;
      margin-top:6px;
    }

    .btn-theme{
      border:none;
      padding:9px 14px;
      border-radius:999px;
      font-weight:900;
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
      cursor:pointer;
      box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex;
      gap:8px;
      align-items:center;
      user-select:none;
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
      gap:14px;
      align-items:start;
    }
    @media (max-width: 860px){
      .grid{ grid-template-columns: 1fr; }
    }

    .field{ margin:0; }
    label{
      display:block;
      font-weight:900;
      margin:4px 0 8px 0;
      letter-spacing:-.2px;
    }

    .form-control,
    input[type="date"],
    input[type="text"]{
      width:100%;
      padding:11px 12px;
      border-radius:14px;
      border:var(--field-border);
      background:var(--field);
      color:var(--text);
      outline:none;
      font-weight:800;
      transition: box-shadow .15s ease, border-color .15s ease, transform .05s ease;
    }
    .form-control:focus,
    input[type="date"]:focus,
    input[type="text"]:focus{
      border-color: rgba(10,132,255,.45);
      box-shadow: 0 0 0 4px rgba(10,132,255,.16);
    }

    .helpbox{
      margin-top:8px;
      color:var(--text-soft);
      font-weight:700;
      font-size:.92rem;
    }

    .section-title{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin:2px 0 10px 0;
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
      font-weight:900;
      user-select:none;
    }
    html[data-theme="dark"] .pill{ background: rgba(255,255,255,.06); }

    .actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top:14px;
      padding-top:12px;
      border-top: var(--border);
    }
    .btn{
      border:none;
      padding:11px 16px;
      border-radius:999px;
      font-weight:950;
      cursor:pointer;
      transition: transform .06s ease, filter .15s ease;
    }
    .btn:active{ transform: scale(.98); }
    .btn.primary{
      background:linear-gradient(180deg,var(--tint), #007aff);
      color:#fff;
      box-shadow:0 10px 22px rgba(0,122,255,.22);
    }
    .btn.ghost{
      background: transparent;
      color: var(--text);
      border: var(--border);
    }
    .btn.danger{
      background: linear-gradient(180deg, #ff5b52, var(--danger));
      color:#fff;
      box-shadow:0 10px 22px rgba(255,59,48,.18);
    }

    /* Picker */
    .picker-wrap{ position:relative; }
    .result-box{
      position:absolute;
      left:0; right:0;
      top: calc(100% + 8px);
      z-index: 2000;
      background: var(--panel);
      border: var(--border);
      border-radius: 14px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(18px) saturate(1.2);
      -webkit-backdrop-filter: blur(18px) saturate(1.2);
      overflow:hidden;
      display:none;
      max-height: 260px;
      overflow-y:auto;
    }
    .result-item{
      padding:11px 12px;
      font-weight:850;
      cursor:pointer;
      border-bottom: 1px solid rgba(10,12,16,.06);
      background: transparent;
      transition: background .12s ease;
      user-select:none;
    }
    html[data-theme="dark"] .result-item{ border-bottom: 1px solid rgba(255,255,255,.06); }
    .result-item:hover{ background: var(--row-hover); }
    .result-item:last-child{ border-bottom:none; }

    .panel-soft{
      border-radius: 16px;
      border: var(--border);
      background: var(--row-bg);
      padding: 12px;
    }

    /* Chips */
    #chips{ display:flex; flex-wrap:wrap; gap:10px; }
    .chip{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:9px 12px;
      border-radius:999px;
      border: 1px solid rgba(10,132,255,.22);
      background: rgba(10,132,255,.10);
      font-weight:950;
      color: var(--text);
      user-select:none;
    }
    html[data-theme="dark"] .chip{
      border: 1px solid rgba(10,132,255,.30);
      background: rgba(10,132,255,.14);
    }
    .chip .x{
      width:22px; height:22px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:999px;
      background: rgba(255,255,255,.55);
      border: 1px solid rgba(10,12,16,.10);
      font-weight:1000;
      line-height:1;
      cursor:pointer;
    }
    html[data-theme="dark"] .chip .x{
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.10);
    }
    .chip .x:hover{ filter: brightness(1.05); }

    /* Selected operador box */
    #operadorSeleccionadoBox{
      margin-top:10px;
      display:none;
    }
    #operadorSeleccionadoTxt{ font-weight:950; }

    .muted{ color: var(--text-soft); font-weight:800; }
    .note{
      margin-top:10px;
      padding:10px 12px;
      border-radius:14px;
      border: var(--border);
      background: rgba(255,255,255,.30);
      font-weight:800;
      color: var(--text-soft);
    }
    html[data-theme="dark"] .note{ background: rgba(255,255,255,.06); }
  </style>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
<div class="container">
  <div class="header">
    <div>
      <h1>Reporte Base Tractosoft · Detalle</h1>
      <div class="subtitle">Consulta por periodo</div>
    </div>
    <button id="themeToggle" class="btn-theme" type="button">
      <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
    </button>
  </div>

  <div class="panel">
    <div class="panel-body">

      <form method="post" action="ValeSalidaDetalle.php" target="_self">

        <!-- necesario para tu JS -->
        <input type="hidden" id="prefijodb" value="<?php echo htmlspecialchars($prefijobd, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="grid">
          <div class="field">
            <label>Fecha inicial</label>
            <input type="date" name="fechai" required>
          </div>

          <div class="field">
            <label>Fecha final</label>
            <input type="date" name="fechaf" required>
          </div>

          <!-- Servicios / Circuitos (multi) -->
          <div class="field" style="grid-column: 1 / -1;">
            <div class="section-title">
              <label style="margin:0;">Circuitos / Servicios (selecciona varios)</label>
              <span class="pill">🔎 Multi-select</span>
            </div>

            <div class="grid" style="grid-template-columns: 1.2fr .8fr;">
              <div class="picker-wrap">
                <input type="text" class="form-control" id="buscaServicio" placeholder="Escribe para buscar...">
                <div class="result-box" id="resultBox"></div>
                <div class="helpbox">Tip: clic en un resultado para agregarlo. Puedes agregar varios.</div>
              </div>

              <div class="panel-soft">
                <div style="font-weight:950; margin-bottom:8px;">Seleccionados</div>
                <div id="chips"></div>
                <div class="helpbox" id="emptyHint">Aún no has seleccionado nada.</div>
              </div>
            </div>

            <input type="hidden" name="circuito" id="circuito" value="">
          </div>

          <!-- Operador (single) -->
          <div class="field" style="grid-column: 1 / -1;">
            <div class="section-title">
              <label style="margin:0;">Operador</label>
              <span class="pill">👤 Single</span>
            </div>

            <div class="picker-wrap">
              <input type="text" class="form-control" id="buscaOperador" placeholder="Escribe para buscar operador...">
              <div class="result-box" id="resultBoxOperador"></div>

              <div class="helpbox" id="operadorHint">
                Si no seleccionas nada, se considera <b>Todos</b>.
              </div>

              <input type="hidden" name="operador" id="operador" value="0">

              <div id="operadorSeleccionadoBox" class="panel-soft">
                <div><b>Seleccionado:</b> <span id="operadorSeleccionadoTxt"></span></div>
                <button type="button" class="btn danger" id="btnQuitarOperador" style="margin-top:10px;">
                  Quitar selección (Todos)
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Hidden de contexto -->
        <input type="hidden" name="base" value="<?php echo htmlspecialchars($prefijobd, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>">
        <input type="hidden" name="emisor" value="<?php echo (int)$emisor; ?>">

        <div class="actions">
          <button class="btn primary" type="submit" name="consultar" value="1">Consultar</button>
        </div>

        <div class="note">
          <b>Nota:</b> Este es tu “reporte base” frosted. Aquí ya tienes: periodo + multi-select + single-select por ajax, con estilo uniforme.
        </div>

      </form>
    </div>
  </div>
</div>

<script>
/* Tema */
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

/* Multi: servicios */
(function(){
  var prefijodb = $('#prefijodb').val();

  var selected = {}; // {id: {id, nombre}}
  var $box = $('#resultBox');
  var $chips = $('#chips');
  var $hidden = $('#circuito');
  var $emptyHint = $('#emptyHint');

  function syncHidden(){
    var ids = Object.keys(selected);
    $hidden.val(ids.join(','));
    $emptyHint.toggle(ids.length === 0);
  }

  function renderChips(){
    $chips.empty();
    Object.keys(selected).forEach(function(id){
      var item = selected[id];
      var $c = $('<span class="chip"></span>');
      $c.text(item.nombre);

      var $x = $('<span class="x" title="Quitar">×</span>');
      $x.on('click', function(){
        delete selected[id];
        renderChips();
        syncHidden();
      });

      $c.append($x);
      $chips.append($c);
    });
  }

  var debounceTimer = null;

  $('#buscaServicio').on('keyup focus', function(){
    var q = $(this).val().trim();

    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function(){
      if (q.length === 0) {
        $box.hide().empty();
        return;
      }

      $.getJSON(window.location.pathname, {
        prefijodb: prefijodb,
        ajax: 'buscar_servicios',
        q: q
      }).done(function(data){
        $box.empty();

        if (!data || !data.length) {
          $box.append('<div class="result-item" style="color:#999; cursor:default;">Sin resultados</div>');
          $box.show();
          return;
        }

        data.forEach(function(item){
          var id = String(item.id);
          var disabled = !!selected[id];
          var label = item.nombre + ' (ID: ' + id + ')';

          var $it = $('<div class="result-item"></div>');
          $it.text(label);

          if (disabled) {
            $it.css({opacity:.55, cursor:'not-allowed'});
          } else {
            $it.on('click', function(){
              selected[id] = { id:id, nombre:item.nombre };
              renderChips();
              syncHidden();
              $('#buscaServicio').val('').focus();
              $box.hide().empty();
            });
          }

          $box.append($it);
        });

        $box.show();
      }).fail(function(){
        $box.empty().append('<div class="result-item" style="color:#ff3b30; cursor:default;">Error consultando servicios</div>').show();
      });
    }, 250);
  });

  $(document).on('click', function(e){
    var $t = $(e.target);
    if ($t.closest('#resultBox, #buscaServicio').length === 0) {
      $box.hide();
    }
  });

  syncHidden();
})();

/* Single: operador */
(function(){
  var prefijodb = $('#prefijodb').val();

  var $boxOp = $('#resultBoxOperador');
  var $inputOp = $('#buscaOperador');
  var $hidOp = $('#operador');
  var $selBox = $('#operadorSeleccionadoBox');
  var $selTxt = $('#operadorSeleccionadoTxt');

  var tOp = null;

  function setOperador(id, nombre){
    $hidOp.val(id);
    $selTxt.text(nombre);
    $selBox.show();
    $('#operadorHint').hide();
  }

  function clearOperador(){
    $hidOp.val('0');
    $inputOp.val('');
    $selBox.hide();
    $('#operadorHint').show();
  }

  $('#btnQuitarOperador').on('click', function(){
    clearOperador();
  });

  $inputOp.on('keyup focus', function(){
    var q = $(this).val().trim();

    if ($hidOp.val() !== '0' && q.length > 0) {
      $hidOp.val('0');
      $selBox.hide();
      $('#operadorHint').show();
    }

    if (tOp) clearTimeout(tOp);
    tOp = setTimeout(function(){
      if (q.length === 0) {
        $boxOp.hide().empty();
        return;
      }

      $.getJSON(window.location.pathname, {
        prefijodb: prefijodb,
        ajax: 'buscar_operadores',
        q: q
      }).done(function(data){
        $boxOp.empty();

        if (!data || !data.length) {
          $boxOp.append('<div class="result-item" style="color:#999; cursor:default;">Sin resultados</div>');
          $boxOp.show();
          return;
        }

        data.forEach(function(item){
          var id = String(item.id);
          var label = item.nombre + ' (ID: ' + id + ')';
          var $it = $('<div class="result-item"></div>');
          $it.text(label);

          $it.on('click', function(){
            setOperador(id, item.nombre);
            $inputOp.val(item.nombre);
            $boxOp.hide().empty();
          });

          $boxOp.append($it);
        });

        $boxOp.show();
      }).fail(function(){
        $boxOp.empty().append('<div class="result-item" style="color:#ff3b30; cursor:default;">Error consultando operadores</div>').show();
      });
    }, 250);
  });

  $(document).on('click', function(e){
    if ($(e.target).closest('#resultBoxOperador, #buscaOperador').length === 0) {
      $boxOp.hide();
    }
  });

  clearOperador();
})();
</script>
</body>
</html>
