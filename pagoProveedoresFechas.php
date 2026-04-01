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
  <title>Reporte Pago a Proveedores · Filtros</title>
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

    
    select:focus, input[type="date"]:focus, input[type="text"]:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }

    
    select:required:invalid{
      color:rgba(92,98,112,.75);
    }
    html[data-theme="dark"] select:required:invalid{
      color:rgba(166,174,194,.75);
    }

   
    select option[value=""]{
      color:rgba(92,98,112,.85);
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
    #resultadoBusqueda{
      margin-top:6px;
      background:var(--panel);
      border-radius:14px;
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }

    
    .sugerencia{
      padding:10px 14px;
      cursor:pointer;
      font-size:.95rem;
      color:var(--text);
      transition:background .15s ease;
    }

   
    .sugerencia:hover{
      background:rgba(10,132,255,.12);
    }

   
    .sugerencia + .sugerencia{
      border-top:1px solid rgba(0,0,0,.04);
    }

    html[data-theme="dark"] .sugerencia + .sugerencia{
      border-top:1px solid rgba(255,255,255,.06);
    }

    #resultadoBusqueda div {
        padding: 8px;
        cursor: pointer;
    }

    .autocomplete-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px; 
    }

    #buscar_1:focus + #resultadoBusqueda{
      margin-top:4px;
    }

    #resultadoBusqueda{
      display:none;
    }
    .dd{ position:relative; width:100%; }

    .dd-btn{
      width:100%;
      text-align:left;
      padding:9px 44px 9px 11px;
      border-radius:999px;
      border:var(--border);
      background:var(--bg);
      color:rgba(92,98,112,.75);
      font-size:.95rem;
      cursor:pointer;
      outline:none;

      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23606a7a' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
      background-repeat:no-repeat;
      background-position:right 14px center;
      background-size:18px 18px;
    }

    html[data-theme="dark"] .dd-btn{
      color:rgba(166,174,194,.75);
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23b8c0d4' d='M5.5 7.5 10 12l4.5-4.5 1.2 1.2L10 14.4 4.3 8.7z'/%3E%3C/svg%3E");
    }

    .dd.open .dd-btn{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }

    .dd-menu{
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

    .dd.open .dd-menu{ display:block; }

    .dd-item{
      padding:10px 14px;
      cursor:pointer;
      color:var(--text);
    }

    .dd-item:hover{ background:rgba(10,132,255,.12); }

    .dd-item + .dd-item{ border-top:1px solid rgba(0,0,0,.04); }
    html[data-theme="dark"] .dd-item + .dd-item{ border-top:1px solid rgba(255,255,255,.06); }

  </style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Reporte Pago a Proveedores · Filtros</h1>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="post" action="pagoProveedores.php" autocomplete="off">
        <div class="form-group field" style="width:95%;">
          <label for="proveedor">Proveedor</label>
          <input type="text" id="buscar_1" class="form_control" placeholder="Escribe la Razon Social del Proveedor">
          <div id="resultadoBusqueda" style="width:95%;"></div>
          <input type="hidden" name="proveedor" id="proveedor">
        </div>
        <div class="field" style="width:96%;">
          <label for="fechai">Fecha inicial</label>
          <input type="date" name="fechai" id="fechai" required />
        </div>
        <div class="field" style="width:96%;">
          <label for="fechaf">Fecha final</label>
          <input type="date" name="fechaf" id="fechaf" required />
        </div>
        <div class="field" style="width:96%;">
            <label for="moneda_ui">Moneda</label>

            <div class="dd" id="monedaDD">
              <button type="button" class="dd-btn" id="moneda_ui">Selecciona moneda...</button>
              <div class="dd-menu" id="moneda_menu">
                <div class="dd-item" data-val="PESOS">PESOS</div>
                <div class="dd-item" data-val="DOLARES">DOLARES</div>
                <div class="dd-item" data-val="AMBOS">AMBOS</div>
              </div>
            </div>

            <input type="hidden" name="moneda" id="moneda" required>
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

/* buscador de keyup */


$(document).ready(function () {
  $('#buscar_1').on('keyup', function () {
    var query = $(this).val();
    var prefijodb = "<?php echo $prefijobd?>";

    if (query.length > 1) {
      $.ajax({
        url: "buscarProveedores.php",
        method: "GET",
        data : {q: query, prefijodb: prefijodb},
        success: function (data) {
          if (data.trim() !== '') {
              $('#resultadoBusqueda').html(data).fadeIn(100);
            } else {
              $('#resultadoBusqueda').fadeOut(100);
            }

        }
      });
    } else {
      $('#resultadoBusqueda').fadeOut();
    }
  });

  $(document).on('click', '.sugerencia',function () {
     var id = $(this).data('id');
     var texto = $(this).text();

     $('#buscar_1').val(texto);
     $('#proveedor').val(id);
     $('#resultadoBusqueda').fadeOut();
  });

  $(document).click(function (e) {
    if (!$(e.target).closest('#buscar_1, #resultadoBusqueda').length) {
      $('#resultadoBusqueda').fadeOut();
    }
  });

});

(function(){
  var dd = document.getElementById('monedaDD');
  if(!dd) return;

  var btn = dd.querySelector('.dd-btn');
  var menu = dd.querySelector('.dd-menu');
  var hidden = document.getElementById('moneda');

  function closeDD(){ dd.classList.remove('open'); }

  btn.addEventListener('click', function(){
    dd.classList.toggle('open');
  });

  menu.addEventListener('click', function(e){
    var item = e.target.closest('.dd-item');
    if(!item) return;
    var val = item.getAttribute('data-val');
    hidden.value = val;
    btn.textContent = val;
    btn.style.color = 'var(--text)';
    closeDD();
  });

  document.addEventListener('click', function(e){
    if(!dd.contains(e.target)) closeDD();
  });
})();



</script>
</body>
</html>
