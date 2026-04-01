<?php
ini_set('memory_limit', '1024M');
set_time_limit(900);

/* ===== Salida temprana para pintar la barra en vivo (PHP 5.5) ===== */
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) { @ob_end_flush(); }
ob_implicit_flush(true);

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) { die('Error de conexión a la base de datos.'); }

/* ===== Parámetros ===== */
if (!isset($_REQUEST['id']) || $_REQUEST['id']==='') { die('Falta id del complemento de pago'); }
if (!isset($_REQUEST['prefijo']) || $_REQUEST['prefijo']==='') { die('Falta el prefijo de la base de datos'); }

$iddocumento = (int)$_REQUEST['id'];
$prefijobd   = $_REQUEST['prefijo'];
if (strpos($prefijobd, "_") === false) { $prefijobd .= "_"; }

/* ===== Helpers ===== */
function join_url($a, $b) {
    $a = rtrim($a, "/");
    $b = ltrim($b, "/");
    if ($a === '') return "/".$b;
    return $a . "/" . $b;
}
function calcularTiempoTimbrado($Conteo) {
    $rangos = array(
              array(0,100,8), 
              array(101,800,6), 
              array(801,1500,4), 
              array(1501,2500,3),
              array(2051,100000,2)
    );
    $tiempo = 1;
    foreach ($rangos as $r) {
        if ($Conteo >= $r[0] && $Conteo <= $r[1]) { $tiempo = $r[2]; break; }
    }
    return $tiempo;
}

/* ===== systemsettings (DirPHPPDF, xmldir, Servidor) ===== */
$DirPHPPDF = '0';
$xmldir = ''; $Servidor = '';
if ($st = $cnx_cfdi3->prepare("SELECT COALESCE(DirPHPPDF,'0'), COALESCE(xmldir,''), COALESCE(Servidor,'') FROM {$prefijobd}systemsettings LIMIT 1")) {
    $st->execute();
    $st->bind_result($DirPHPPDF, $xmldir, $Servidor);
    $st->fetch();
    $st->close();
}

/* ===== Datos de remision para nombres/rutas ===== */
$cfdicbbArchivo = ''; $CorreoCd=''; $SerieFiscal=''; $Folio=0; $Conteo=0;
if ($q = $cnx_cfdi3->prepare("
    SELECT a.cfdicbbArchivo, b.CorreoCobranza, c.SerieFiscal, a.Folio, COUNT(*) AS Conteo
    FROM {$prefijobd}remisiones a
    INNER JOIN {$prefijobd}clientes b ON a.CargoACliente_RID = b.ID
    INNER JOIN {$prefijobd}oficinas c ON a.Oficina_RID = c.ID
    INNER JOIN {$prefijobd}remisionessub d ON a.ID = d.FolioSub_RID
    WHERE a.ID = ?")) {
    $q->bind_param("i", $iddocumento);
    $q->execute();
    $q->bind_result($cfdicbbArchivo, $CorreoCd, $SerieFiscal, $Folio, $Conteo);
    $q->fetch();
    $q->close();
}

/* ===== Construcción de rutas ===== */
$fileXMLAbs = str_replace("bmp","xml",$cfdicbbArchivo);
$filePDFAbs = str_replace("bmp","pdf",$cfdicbbArchivo);

/* Relativas (para HEAD desde el navegador) */
$filePDFRel = str_ireplace(array("C:\\xampp\\htdocs","/xampp/htdocs"), "", $filePDFAbs);
$fileXMLRel = str_ireplace(array("C:\\xampp\\htdocs","/xampp/htdocs"), "", $fileXMLAbs);

/* URLs públicas si aplica */
$basePublica = join_url(rtrim($Servidor), rtrim($xmldir));
$urlPDFWeb = join_url($basePublica, $SerieFiscal.'-'.$Folio.'.pdf');
$urlXMLWeb = join_url($basePublica, $SerieFiscal.'-'.$Folio.'.xml');

/* ===== Velocidad de barra ===== */
$subEmbalaje = calcularTiempoTimbrado($Conteo);

/* ===== Llamada a timbrarTraslado.php ===== */


      $host = $_SERVER['SERVER_NAME']; 
      $url = "https://$host:8082/cfdipro/traslado/timbradoRemision.php";

      // Armar query
      $queryParams = http_build_query([
          'remisionid' => $iddocumento,
          'prefijobd' => $prefijobd
      ]);

      // Concatenar todo
      $urlConParametros = $url . '?' . $queryParams;
      //die($urlConParametros);

      // Llamar a la URL

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $urlConParametros);
      curl_setopt($ch, CURLOPT_CAINFO, 'C:/xampp/htdocs/certf/cacert.pem');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = curl_exec($ch);

      if (curl_errno($ch)) {
          echo 'Error de cURL: ' . curl_error($ch);
      } 

      curl_close($ch);

      

/* ===== HTML inmediato + JS (jQuery 2.1.3, Bootstrap 3) ===== */
echo str_repeat("<!-- pad -->\n", 4096); // empuja buffers de Apache/Chrome
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Timbrando… no cierre esta ventana</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<style>
  /* ===== Tema blanco minimal ===== */
  body{font-family:Arial,Helvetica,sans-serif;padding:24px;background:#fff;color:#111}
  .container-limit{max-width:900px;margin:0 auto}
  .card{background:#fff;border:1px solid #dadadaff;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.03)}
  .progress{height:26px;background: #ffffff;border:1px solid #e5e5e5;border-radius:999px;box-shadow:inset 0 0 0 999px #fff}
  .progress-bar{background:rgba(86, 141, 196, 1); color: #ddd;font-weight:bold;box-shadow:none}
  .eta{color:#666;font-size:.9rem;margin-top:6px;text-align:right;display:none}
  .alert-box{background:#fff;border:1px solid #e5e5e5;border-left:4px solid #bbb;border-radius:8px;padding:10px 12px;margin-top:12px}
  .alert-box.ok   { border-left-color:#888; }
  .alert-box.warn { border-left-color:#aaa; }
  .alert-box.err  { border-left-color:#444; }
  .btn-white{background: #428bca;border:1px solid #ddd;color: #ddd}
  .btn-white:hover{background: #5a97ccff}
  .muted{color:#666}
</style>
</head>
<body>
  <div class="container-limit">
    <h3>Timbrando su Viaje</h3>
    <p class="muted">No cierre la ventana hasta terminar el proceso. Espere por favor…</p>

    <div class="card">
      <div class="progress">
        <div id="bar" class="progress-bar" role="progressbar"
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
      </div>
      <div id="eta" class="eta">Estimando tiempo…</div>
    </div>

    <div id="msgDone" class="alert-box" style="display:none;">
      <center><b>Finalizando timbrado, preparando verificación de UUID…</b></center>
    </div>
    <div id="msgUUID" class="alert-box" style="display:none;">
      <center>Buscando UUID… intento <span id="uuidTry">0</span>/20</center>
    </div>
    <div id="result" style="margin-top:10px;"></div>
  </div>

<script>
/* ===== Config desde PHP ===== */
var CFG = {
  id:        <?php echo json_encode($iddocumento); ?>,
  prefijo:   <?php echo json_encode($prefijobd); ?>,
  dirphp:    <?php echo json_encode($DirPHPPDF === '1'); ?>, // true/false
  pdfHEAD:   <?php echo json_encode($filePDFRel); ?>,        // para HEAD
  pdfDL:     <?php echo json_encode($urlPDFWeb); ?>,
  xmlDL:     <?php echo json_encode($urlXMLWeb); ?>,
  correo:    <?php echo json_encode($CorreoCd); ?>,
  serie:     <?php echo json_encode($SerieFiscal); ?>,
  folio:     <?php echo json_encode($Folio); ?>
};
var subEmbalaje = <?php echo json_encode($subEmbalaje); ?>;

/* ===== Barra ===== */
(function(){
  var progreso=0, start=Date.now();
  var t = setInterval(function(){
    progreso += subEmbalaje;
    var pct = Math.min(100, Math.round(progreso));
    $('#bar').css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');

    if (pct >= 20 && pct < 100) {
      var elapsed = (Date.now()-start)/1000;
      var speed = pct / Math.max(1, elapsed);
      var eta = Math.round((100-pct)/Math.max(0.1, speed));
      $('#eta').text('Tiempo estimado restante: ~' + eta + ' s').show();
    }

    if (pct >= 100) {
      clearInterval(t);
      $('#eta').hide();
      $('#msgDone').removeClass('ok warn err').addClass('ok').show();
      verificarUUID(); // inicia 20 intentos
    }
  }, 1000);
})();

/* ===== Verificación UUID (20 intentos, recursivo setTimeout) ===== */
var iUUID=0, maxUUID=15;
function verificarUUID(){
  iUUID++;
  $('#msgUUID').show(); $('#uuidTry').text(iUUID);
  $.ajax({
    url: 'verificar_uuid_bd.php',
    type: 'GET',
    cache: false,
    data: { id: CFG.id, prefijo: CFG.prefijo, tipoT: 'remisiones', _t: Date.now() },
    success: function(resp){
      var data = null;
      try { data = (typeof resp==='object') ? resp : JSON.parse(resp); } catch(e){}
      if (data && data.existe === true) {
        $('#msgUUID').hide();
        $('#msgDone').removeClass('ok warn err').addClass('ok')
                     .html('<center><b>UUID encontrado. Generando/validando PDF…</b></center>');
        if (CFG.dirphp) {
          generarYVerificarPDF(); // modo DirPHPPDF = '1'
        } else {
          verificarPDF(0);        // modo sin DirPHPPDF
        }
      } else {
        if (iUUID < maxUUID) { setTimeout(verificarUUID, 2000); }
        else {
          var msj = (data && data.mensaje) ? data.mensaje : 'No se encontró UUID dentro del tiempo límite.';
          fallo(msj);
        }
      }
    },
    error: function(){
      if (iUUID < maxUUID) { setTimeout(verificarUUID, 2000); }
      else { fallo('Error consultando el UUID repetidamente.'); }
    }
  });
}

function fallo(msj){
  $('#msgUUID').hide();
  $('#msgDone').removeClass('ok warn err').addClass('err')
               .html('<center><b>'+ msj +'</b></center>');
}

/* ===== Modo DirPHPPDF = '1': genera PDF con PHP y luego HEAD ===== */
function generarYVerificarPDF(){
  $.ajax({
    url: 'rem_ceros_formato.php',
    type: 'GET',
    cache: false,
    data: { id: CFG.id, prefijodb: CFG.prefijo, tipo: 'mail', _t: Date.now() },
    complete: function(){ verificarPDF(0); }
  });
}

/* ===== Verificar aparición del PDF (HEAD) hasta 12 intentos ===== */
function verificarPDF(n){
  var maxN=12;
  $.ajax({
    url: CFG.pdfHEAD + (CFG.pdfHEAD.indexOf('?')>-1 ? '&' : '?') + '_t=' + Date.now(),
    type: 'HEAD',
    success: function(){
      renderDescargas();
      enviarCorreo(); // comenta esta línea si no deseas auto-enviar
    },
    error: function(){
      if (n < maxN) setTimeout(function(){ verificarPDF(n+1); }, 2000);
      else $('#result').html('<div class="alert-box warn"><b>Atención:</b> No se encontró el PDF a tiempo.</div>');
    }
  });
}

function renderDescargas(){
  var pdf = CFG.pdfDL || CFG.pdfHEAD || '#';
  var xml = CFG.xmlDL || <?php echo json_encode($fileXMLRel); ?>;
  if (xml && xml !== '#') {
    xml = xml + (xml.indexOf('?')>-1 ? '&' : '?') + '_t=' + Date.now();
  } else { xml = '#'; }
  var html = '' +
    '<div class="card" style="margin-top:10px;">' +
    '<h4>Documentos listos</h4>' +
    '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">' +
      '<a class="btn btn-white" target="_blank" href="'+ pdf +'">Descargar PDF</a>' +
      '<a class="btn btn-white" target="_blank" href="'+ xml +'">Descargar XML</a>' +
    '</div>' +
    '<div class="muted" style="margin-top:6px;">Serie/Folio: <b>'+ (CFG.serie||'') +'-'+ (CFG.folio||'') +'</b></div>' +
    '</div>';
  $('#result').html(html);
}

/* ===== Envío de correo ===== */
function enviarCorreo(){
  window.onbeforeunload = function(){ return 'El proceso de envío aún no ha terminado. ¿Seguro que deseas salir?'; };
  $.ajax({
    url: 'enviar_archivosmail.php',
    type: 'GET',
    cache: false,
    data: {
      pdf: CFG.pdfDL || CFG.pdfHEAD, xml: CFG.xmlDL || <?php echo json_encode($fileXMLRel); ?>,
      prefijo: CFG.prefijo, correo: CFG.correo, iddocumento: CFG.id, tipoT: 'abonos', _t: Date.now()
    },
    complete: function(){ window.onbeforeunload = null; }
  });
}
</script>
</body>
</html>

