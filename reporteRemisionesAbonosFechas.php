<?php
error_reporting(0);
set_time_limit(300);

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function normaliza_prefijo($raw){
    $raw = str_replace(array("'", '"', ";"), "", $raw);
    $raw = preg_replace('/[^a-zA-Z0-9_]/', '', $raw);
    if ($raw === '') return '';
    if (strpos($raw, "_") === false) $raw .= "_";
    return $raw;
}

$prefijobd = normaliza_prefijo($_GET['prefijodb']);
if ($prefijobd === '') die("Prefijo inválido.");


if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    require_once('cnx_cfdi3.php');
    if (!isset($cnx_cfdi3) || $cnx_cfdi3->connect_error) exit;

    $cnx_cfdi3->query("SET NAMES 'utf8'");

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $q    = isset($_GET['q']) ? trim($_GET['q']) : '';
    $q    = substr($q, 0, 80);

    if ($q === '' || strlen($q) < 2) exit;

    $qSafe = mysqli_real_escape_string($cnx_cfdi3, $q);
    $like  = "%".$qSafe."%";

    $sql = '';
    if ($type === 'cliente') {
			$sql = "SELECT 
					ID,
					CONCAT(
						RazonSocial,
						CASE
						WHEN IFNULL(RFC,'') <> '' THEN CONCAT(' (', RFC, ')')
						ELSE ''
						END
					) AS Txt
					FROM {$prefijobd}Clientes
					WHERE Estatus='Activo'
					AND (RazonSocial LIKE '{$like}' OR IFNULL(RFC,'') LIKE '{$like}')
					ORDER BY RazonSocial
					LIMIT 30";
		} elseif ($type === 'serie') {
			$sql = "SELECT ID, Serie AS Txt
					FROM {$prefijobd}Oficinas
					WHERE EsRem='1'
					AND Serie LIKE '{$like}'
					ORDER BY Serie
					LIMIT 30";
		} elseif ($type === 'unidad') {
			$sql = "SELECT 
					ID,
					CONCAT(
						Unidad,
						CASE 
						WHEN IFNULL(Placas,'') <> '' THEN CONCAT(' (', Placas, ')')
						ELSE ''
						END
					) AS Txt
					FROM {$prefijobd}Unidades
					WHERE Tipo='Unidad'
					AND (Unidad LIKE '{$like}' OR IFNULL(Placas,'') LIKE '{$like}')
					ORDER BY Unidad
					LIMIT 30";
		} elseif ($type === 'operador') {
			$sql = "SELECT ID, Operador AS Txt
					FROM {$prefijobd}Operadores
					WHERE Estatus='Activo'
					AND Operador LIKE '{$like}'
					ORDER BY Operador
					LIMIT 30";
    } else {
        exit;
    }

    $res = mysqli_query($cnx_cfdi3, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            echo '<div class="sugerencia" data-id="'.intval($row['ID']).'">'.h($row['Txt']).'</div>';
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Viajes en Abonos · Filtros</title>
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
      font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial;
      background:var(--bg);
      color:var(--text);
    }
    .container{ max-width:720px; margin:40px auto; padding:20px; }
    .header{
      display:flex; align-items:center; justify-content:space-between;
      gap:12px; margin-bottom:16px; flex-wrap:wrap;
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
    .grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:12px;
    }
    @media (max-width:720px){ .grid{ grid-template-columns:1fr; } }

    label{
      display:block; margin-bottom:4px;
      font-size:.9rem; font-weight:600; color:var(--text-soft);
    }
    input[type="text"], input[type="date"]{
      width:90%;
      padding:9px 11px;
      border-radius:999px;
      border:var(--border);
      background:var(--bg);
      color:var(--text);
      font-size:.95rem;
      outline:none;
    }
    input:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }
    .actions{ margin-top:16px; display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
    .btn{
      border:none; padding:9px 16px; border-radius:999px;
      font-weight:700; cursor:pointer; font-size:.95rem;
    }
    .btn.primary{ background:linear-gradient(180deg,var(--tint), #007aff); color:#fff; }
    .btn.ghost{ background:var(--panel); color:var(--text); border:var(--border); }

    .aw{ position:relative; }
    .results{
      display:none;
      position:absolute;
      left:0; right:0;
      top:calc(100% + 6px);
      background:var(--panel);
      border:var(--border);
      border-radius:14px;
      box-shadow:var(--shadow);
      overflow:hidden;
      z-index:50;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .sugerencia{
      padding:10px 14px;
      cursor:pointer;
      font-size:.95rem;
      color:var(--text);
    }
    .sugerencia:hover{ background:rgba(10,132,255,.12); }
    .sugerencia + .sugerencia{ border-top:1px solid rgba(0,0,0,.04); }
    html[data-theme="dark"] .sugerencia + .sugerencia{ border-top:1px solid rgba(255,255,255,.06); }
    .hint{ margin-top:10px; color:var(--text-soft); font-size:.9rem; }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>Viajes en Abonos · Filtros</h1>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="post" action="reporteRemisionesAbonos.php" autocomplete="off">
        <div class="grid">

          <div class="aw">
            <label>Cliente</label>
            <input type="text" id="txt_cliente" placeholder="Escribe el nombre del cliente..." />
            <div class="results" id="res_cliente"></div>
            <input type="hidden" name="cliente" id="cliente" value="0" />
          </div>

          <div class="aw">
            <label>Serie</label>
            <input type="text" id="txt_serie" placeholder="Escribe la serie..." />
            <div class="results" id="res_serie"></div>
            <input type="hidden" name="serie" id="serie" value="0" />
          </div>

          <div class="aw">
            <label>Unidad</label>
            <input type="text" id="txt_unidad" placeholder="Unidad o placas..." />
            <div class="results" id="res_unidad"></div>
            <input type="hidden" name="unidad" id="unidad" value="0" />
          </div>

          <div class="aw">
            <label>Operador</label>
            <input type="text" id="txt_operador" placeholder="Nombre del operador..." />
            <div class="results" id="res_operador"></div>
            <input type="hidden" name="operador" id="operador" value="0" />
          </div>

          <div>
            <label>Fecha inicial</label>
            <input type="date" name="fechai" id="fechai" required />
          </div>

          <div>
            <label>Fecha final</label>
            <input type="date" name="fechaf" id="fechaf" required />
          </div>

        </div>

        <input type="hidden" name="base" value="<?php echo h($prefijobd); ?>" />

        <div class="actions">
          <button type="reset" class="btn ghost" id="btnLimpiar">Limpiar</button>
          <button type="submit" class="btn primary" name="consultar" value="1">Consultar</button>
        </div>

        <div class="hint">
          Tip: escribe mínimo 2 letras y selecciona una sugerencia.
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

var prefijodb = "<?php echo h($prefijobd); ?>";

function wireAutocomplete(inputId, resId, hiddenId, type){
  var $in = $('#'+inputId);
  var $res = $('#'+resId);
  var $hid = $('#'+hiddenId);
  var timer = null;

  function hide(){ $res.hide().empty(); }

  $in.on('keyup', function(){
    var q = $.trim($in.val());

    // si el usuario escribe, invalidamos el id (queda "Todos" hasta que seleccione)
    $hid.val('0');

    if (timer) clearTimeout(timer);
    timer = setTimeout(function(){
      if (q.length < 2){ hide(); return; }
      $.ajax({
        url: 'reporteRemisionesAbonosFechas.php',
        method: 'GET',
        data: { ajax: 1, type: type, q: q, prefijodb: prefijodb },
        success: function(html){
          if ($.trim(html) !== ''){
            $res.html(html).show();
          } else {
            hide();
          }
        }
      });
    }, 180);
  });

  $(document).on('click', '#'+resId+' .sugerencia', function(){
    var id = $(this).data('id');
    var txt = $(this).text();
    $in.val(txt);
    $hid.val(id);
    hide();
  });

  $(document).on('click', function(e){
    if (!$(e.target).closest('#'+inputId+',#'+resId).length) hide();
  });

  $('#btnLimpiar').on('click', function(){
    setTimeout(function(){
      $hid.val('0');
      hide();
    }, 0);
  });
}

$(function(){
  wireAutocomplete('txt_cliente','res_cliente','cliente','cliente');
  wireAutocomplete('txt_serie','res_serie','serie','serie');
  wireAutocomplete('txt_unidad','res_unidad','unidad','unidad');
  wireAutocomplete('txt_operador','res_operador','operador','operador');
});
</script>
</body>
</html>
