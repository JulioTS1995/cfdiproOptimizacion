<?php
set_time_limit(3000);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('cnx_cfdi2.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

/* ===== Helpers ===== */
function normaliza_prefijo($raw){
  $raw = str_replace(array("'", '"', ";"), "", (string)$raw);
  $raw = preg_replace('/[^a-zA-Z0-9_]/', '', $raw);
  if ($raw === '') return '';
  if (strpos($raw, "_") === false) $raw .= "_";
  return $raw;
}

function table_exists($cnx, $db, $table){
  $db = mysqli_real_escape_string($cnx, $db);
  $table = mysqli_real_escape_string($cnx, $table);
  $sql = "SELECT 1
          FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA='{$db}' AND TABLE_NAME='{$table}'
          LIMIT 1";
  $r = mysqli_query($cnx, $sql);
  return ($r && mysqli_num_rows($r) > 0);
}

function column_exists($cnx, $db, $table, $col){
  $db = mysqli_real_escape_string($cnx, $db);
  $table = mysqli_real_escape_string($cnx, $table);
  $col = mysqli_real_escape_string($cnx, $col);
  $sql = "SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA='{$db}'
            AND TABLE_NAME='{$table}'
            AND COLUMN_NAME='{$col}'
          LIMIT 1";
  $r = mysqli_query($cnx, $sql);
  return ($r && mysqli_num_rows($r) > 0);
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ===== Params ===== */
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
  die("Falta el prefijo de la BD");
}
$prefijobd = normaliza_prefijo($_GET['prefijodb']);
if ($prefijobd === '') die("Prefijo inválido");

$sucursal = 0;
if (isset($_GET['sucursal']) && $_GET['sucursal'] !== '') {
  $sucursal = intval($_GET['sucursal']);
}

/* ===== Construir filtro sucursal (robusto) =====
   Regla:
   - Si sucursal <= 0 => no filtrar
   - Si no existe Oficinas.Sucursal_RID => no filtrar
   - Si no existe factura.Oficina_RID => no filtrar
*/
$whereSucursal = "";
$oficinasTable = $prefijobd."Oficinas";
$facturaTable  = $prefijobd."factura";

$canFilterSucursal = false;
if ($sucursal > 0
    && table_exists($cnx_cfdi2, $database_cfdi, $oficinasTable)
    && table_exists($cnx_cfdi2, $database_cfdi, $facturaTable)
    && column_exists($cnx_cfdi2, $database_cfdi, $oficinasTable, "Sucursal_RID")
    && column_exists($cnx_cfdi2, $database_cfdi, $facturaTable, "Oficina_RID")
) {
  $canFilterSucursal = true;
  $whereSucursal = " AND a.Oficina_RID IN (
                      SELECT ID FROM {$oficinasTable} WHERE Sucursal_RID = ".intval($sucursal)."
                    ) ";
}

/* ===== Variables de salida ===== */
$folio = $uuid = $receptor = $total = $fecha = '';
$emisor = '';
$consulta = $estado = $escancelable = $estatus = '';
$msg = '';
$idfactura = 0;

/* ===== Acción: Activar (misma intención, solo seguro) ===== */
if (isset($_POST['btnActivar'])) {

  $idfactura = isset($_POST['id']) ? intval($_POST['id']) : 0;
  $estado_in = isset($_POST['estado']) ? trim($_POST['estado']) : '';

  if ($idfactura <= 0) {
    $msg = "Seleccione una factura válida.";
  } elseif ($estado_in === "Cancelado") {
    $msg = "No se puede activar una factura cuando está cancelada en el SAT.";
  } else {
    mysqli_query($cnx_cfdi2, "BEGIN");

    // MISMA ACTUALIZACIÓN que antes, solo con prepared para no romper nada
    $upd = "UPDATE {$facturaTable}
            SET cCanceladoT = NULL,
                cCanceladoCausa_REN = NULL,
                cCanceladoCausa_RID = NULL,
                cCanceladoPor = NULL
            WHERE ID = ?";
    $stmt = mysqli_prepare($cnx_cfdi2, $upd);
    mysqli_stmt_bind_param($stmt, "i", $idfactura);

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
      mysqli_query($cnx_cfdi2, "COMMIT");
      $msg = "La factura se activó correctamente";
    } else {
      mysqli_query($cnx_cfdi2, "ROLLBACK");
      $msg = "Error, No se activó la factura";
    }
  }
}

/* ===== Acción: Buscar ===== */
if (isset($_POST['btnBuscar'])) {

  $idfactura = isset($_POST['factura']) ? intval($_POST['factura']) : 0;

  if ($idfactura <= 0) {
    $msg = "Seleccione una factura.";
  } else {

    // RFC emisor (igual idea)
    $sqlemisor = "SELECT RFC FROM {$prefijobd}systemsettings LIMIT 1";
    $res2 = mysqli_query($cnx_cfdi2, $sqlemisor);
    if ($res2 && mysqli_num_rows($res2) > 0) {
      $row2 = mysqli_fetch_assoc($res2);
      $emisor = $row2 ? $row2['RFC'] : '';
    }

    // Factura cancelada en sistema + RFC receptor (misma lógica de filtro)
    $sql0 = "SELECT a.ID, a.XFolio, a.cfdiuuid, a.zTotal, a.cCanceladoT, b.RFC
             FROM {$facturaTable} a
             INNER JOIN {$prefijobd}clientes b ON a.CargoAFactura_RID = b.ID
             WHERE a.cCanceladoT <> ''
               AND a.ID = ?
             {$whereSucursal}
             ORDER BY a.XFolio";
    $stmt0 = mysqli_prepare($cnx_cfdi2, $sql0);
    mysqli_stmt_bind_param($stmt0, "i", $idfactura);
    mysqli_stmt_execute($stmt0);
    $res0 = mysqli_stmt_get_result($stmt0);

    if ($res0 && mysqli_num_rows($res0) > 0) {
      $row0 = mysqli_fetch_assoc($res0);

      $folio    = $row0['XFolio'];
      $uuid     = $row0['cfdiuuid'];
      $receptor = $row0['RFC'];
      $total    = $row0['zTotal'];
      $fecha    = $row0['cCanceladoT'];

      /* ==========================================================
         ===========  SAT: LÓGICA 100% IGUAL A LA VIEJA  ============
         ========================================================== */

      $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"><soapenv:Header/><soapenv:Body><tem:Consulta><tem:expresionImpresa>?re='.$emisor.'&amp;rr='.$receptor.'&amp;tt='.$total.'&amp;id='.$uuid.'</tem:expresionImpresa></tem:Consulta></soapenv:Body></soapenv:Envelope>';

      //encabezados
      $headers = [
        'Content-Type: text/xml;charset=utf-8',
        'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
        'Content-length: '.strlen($soap)
      ];

      $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      $res = curl_exec($ch);
      curl_close($ch);

      $xml = simplexml_load_string($res);
      $data = $xml->children('s', true)->children('', true)->children('', true);
      $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
      $obj = json_decode($data);
      $estado = $obj->{'Estado'};
      $consulta = $obj->{'CodigoEstatus'};
      $escancelable = $obj->{'EsCancelable'};
      $estatus = $obj->{'EstatusCancelacion'};

      /* ========================================================== */

    } else {
      $msg = "No se encontró la factura cancelada en sistema (o no coincide con el filtro de sucursal).";
    }

    mysqli_stmt_close($stmt0);
  }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Revisión Facturas Canceladas SAT</title>
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
      --border:1px solid rgba(10,12,16,.15);
      --row-bg:#fff;
      --row-hover:#f1f4fb;
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.65);
      --border:1px solid rgba(255,255,255,.15);
      --row-bg:#141824;
      --row-hover:#1a2030;
    }
    body{
      margin:0;
      font-family:"SF Pro Display",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial;
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
      margin-bottom:16px;
      flex-wrap:wrap;
    }
    .header h1{
      margin:0;
      font-size:1.8rem;
      font-weight:900;
      letter-spacing:-.5px;
    }
    .subtitle{
      color:var(--text-soft);
      font-weight:700;
      margin-top:6px;
      font-size:.95rem;
    }
    .btn-theme{
      border:none;
      padding:8px 14px;
      border-radius:999px;
      font-weight:900;
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
      padding:18px;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }

    .grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:14px;
    }
    @media (max-width: 920px){
      .grid{ grid-template-columns:1fr; }
    }

    label{
      display:block;
      margin-bottom:6px;
      font-size:.9rem;
      font-weight:800;
      color:var(--text-soft);
    }

    input[type="text"]{
      width:90%;
      padding:10px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--bg);
      color:var(--text);
      font-size:.95rem;
      outline:none;
    }
    input[type="text"]:focus{
      border:1px solid rgba(10,132,255,.45);
      box-shadow:0 0 0 4px rgba(10,132,255,.12);
    }

    .actions{
      margin-top:14px;
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      justify-content:flex-end;
    }
    .btn{
      border:none;
      padding:10px 16px;
      border-radius:999px;
      font-weight:900;
      cursor:pointer;
      font-size:.95rem;
      text-decoration:none;
      display:inline-block;
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

    /* autocomplete */
    #resultadoBusqueda{
      margin-top:8px;
      display:none;
      background:var(--panel);
      border-radius:14px;
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
      max-height:260px;
      overflow:auto;
    }
    .sugerencia{
      padding:10px 14px;
      cursor:pointer;
      font-size:.95rem;
      color:var(--text);
      transition:background .15s ease;
    }
    .sugerencia:hover{ background:rgba(10,132,255,.12); }
    .sugerencia + .sugerencia{ border-top:1px solid rgba(0,0,0,.04); }
    html[data-theme="dark"] .sugerencia + .sugerencia{ border-top:1px solid rgba(255,255,255,.06); }

    .note{
      margin-top:10px;
      color:var(--text-soft);
      font-weight:700;
      font-size:.9rem;
    }

    .msg{
      margin:14px 0 0 0;
      padding:10px 12px;
      border-radius:14px;
      border:var(--border);
      background:rgba(10,132,255,.08);
      font-weight:900;
    }
    html[data-theme="dark"] .msg{ background:rgba(10,132,255,.16); }

    .box{
      border:var(--border);
      background: rgba(rgba(255, 255, 255, 0.6));
      border-radius:14px;
      padding:12px;
    }
    html[data-theme="dark"] .box{
      background:rgba(255,255,255,.04);
    }
    .box p{
      margin:8px 0;
      display:flex;
      gap:10px;
      align-items:center;
      flex-wrap:wrap;
    }
    .box p span{
      min-width:190px;
      color:var(--text-soft);
      font-weight:900;
    }
    .box input{
      flex:1;
      min-width:220px;
    }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Revisión Facturas Canceladas SAT</h1>
      
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <form method="post" class="FacturasSat" autocomplete="off">
        <div>
          <label for="buscar_factura">Factura (folio o UUID)</label>
          <input type="text" id="buscar_factura" placeholder="Escribe folio/uuid..." value="<?php echo isset($folio) ? h($folio) : ''; ?>">
          <div id="resultadoBusqueda"></div>

          <input type="hidden" name="factura" id="factura" value="<?php echo isset($idfactura) ? intval($idfactura) : 0; ?>">
          <div class="note">Tip: escribe serie y folio sin guiones ej: FE123</div>
        </div>

        <div class="actions">
          <button type="submit" id="btnBuscar" name="btnBuscar" value="Buscar" class="btn primary">Buscar en SAT</button>
          <button type="submit" id="btnActivar" name="btnActivar" value="Activar" class="btn ghost">Activar Factura en Sistema</button>
        </div>

        <?php if (!empty($msg)): ?>
          <div class="msg"><?php echo h($msg); ?></div>
        <?php endif; ?>

        <div style="height:12px;"></div>

        <div class="grid">
          <div class="box">
            <p><span>ID</span><input type="text" id="id" name="id" value="<?php echo isset($idfactura) ? intval($idfactura) : 0; ?>" readonly></p>
            <p><span>Factura</span><input type="text" id="xfolio" value="<?php echo h($folio); ?>" readonly></p>
            <p><span>UUID</span><input type="text" style="width: 100%;" value="<?php echo h($uuid); ?>" readonly></p>
            <p><span>Emisor</span><input type="text" name="emisor" value="<?php echo h($emisor); ?>" readonly></p>
            <p><span>Receptor</span><input type="text" name="receptor" value="<?php echo h($receptor); ?>" readonly></p>
            <p><span>Total</span><input type="text" name="total" value="<?php echo h($total); ?>" readonly></p>
            <p><span>Fecha Cancelación Sistema</span><input type="text" name="fecha" value="<?php echo h($fecha); ?>" readonly></p>
          </div>

          <div class="box">
            <p><span>Consulta</span><input type="text" style="width:100%;" name="consulta" value="<?php echo h($consulta); ?>" readonly></p>
            <p><span>Estado</span><input type="text" name="estado" value="<?php echo h($estado); ?>" readonly></p>
            <p><span>EsCancelable</span><input type="text" name="escancelable" value="<?php echo h($escancelable); ?>" readonly></p>
            <p><span>Estatus</span><input type="text" name="estatus" value="<?php echo h($estatus); ?>" readonly></p>
          </div>
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

$(document).ready(function () {
  var $input = $('#buscar_factura');
  var $box = $('#resultadoBusqueda');
  var $hidden = $('#factura');

  function doSearch(){
    var q = $input.val();
    if (!q || q.length < 2) { $box.fadeOut(80); return; }

    $.ajax({
      url: "buscar_facturas_canceladas_sat.php",
      method: "GET",
      data: {
        q: q,
        prefijodb: "<?php echo h($prefijobd); ?>",
        sucursal: "<?php echo intval($sucursal); ?>"
      },
      success: function(data){
        if ($.trim(data) !== '') {
          $box.html(data).fadeIn(80);
        } else {
          $box.fadeOut(80);
        }
      }
    });
  }

  $input.on('keyup', function(){ doSearch(); });

  $(document).on('click', '.sugerencia', function(){
    var id = $(this).data('id');
    var folio = $(this).data('folio');
    $hidden.val(id);
    $('#id').val(id);
    $('#xfolio').val(folio);
    $input.val(folio);
    $box.fadeOut(80);
  });

  $(document).click(function(e){
    if (!$(e.target).closest('#buscar_factura, #resultadoBusqueda').length) {
      $box.fadeOut(80);
    }
  });
});
</script>
</body>
</html>
