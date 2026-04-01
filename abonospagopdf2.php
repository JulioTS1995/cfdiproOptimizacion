<?php
ini_set('memory_limit', '1024M');
set_time_limit(200);

header('Content-Type: text/html; charset=UTF-8');

// ===== UI no bloqueante (overlay + toast) =====
echo '
<style>
  .ts-overlay{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;
    background:rgba(0,0,0,.35);backdrop-filter:saturate(1.1) blur(2px);z-index:9999;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial}
  .ts-card{background:#fff;padding:16px 18px;border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,.15);min-width:320px;max-width:92%;text-align:center}
  .ts-row{display:flex;gap:12px;align-items:center;justify-content:center}
  .ts-spin{width:22px;height:22px;border:3px solid #e5e7eb;border-top-color:#4b5563;border-radius:50%;animation:ts-spin 1s linear infinite}
  @keyframes ts-spin{to{transform:rotate(360deg)}}
  .ts-msg{font-size:14px;color:#111;margin:0}
  .ts-toast{position:fixed;left:50%;transform:translateX(-50%);bottom:16px;background:#111;color:#fff;padding:10px 14px;border-radius:999px;opacity:.96;z-index:10000}
  .ts-ok{background:#16a34a}.ts-warn{background:#d97706}.ts-err{background:#b91c1c}
</style>
<div id="tsOverlay" class="ts-overlay" style="display:none">
  <div class="ts-card">
    <div class="ts-row">
      <div class="ts-spin"></div>
      <p id="tsMsg" class="ts-msg">Procesando…</p>
    </div>
  </div>
</div>
<script>
  function tsShow(msg){var o=document.getElementById("tsOverlay");var m=document.getElementById("tsMsg");if(m) m.textContent=msg||"Procesando…";o.style.display="flex";}
  function tsUpdate(msg){var m=document.getElementById("tsMsg"); if(m) m.textContent=msg;}
  function tsHide(){var o=document.getElementById("tsOverlay"); if(o) o.style.display="none";}
  function tsToast(text, type){
    var t=document.createElement("div");
    t.className="ts-toast "+(type||"");
    t.textContent=text||"Listo";
    document.body.appendChild(t);
    setTimeout(function(){ if(t && t.parentNode){t.parentNode.removeChild(t);} }, 2600);
  }
</script>
';

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
while (ob_get_level()) { @ob_end_flush(); }
ob_implicit_flush(1);

// ======= Dependencias PHPMailer =======
require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");

// ======= DB =======
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    echo "<script>tsToast('Error de BD','ts-err');</script>";
    die('Error de conexión a la base de datos.');
}

//======================================================================
// Verificación de parámetros
if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    echo "<script>tsToast('Falta id de la factura','ts-err');</script>";
    die("Falta id de la factura");
}
if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    echo "<script>tsToast('Falta prefijo de la BD','ts-err');</script>";
    die("Falta el prefijo de la base de datos");
}

$prefijobd   = $_REQUEST["prefijo"];
$iddocumento = $_REQUEST["id"];
$tipodoc     = 'mail';

// Asegurar sufijo "_"
if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

// ===== Cargar systemsettings base (SMTP / xmldir / servidor) =====
$tabla      = $cnx_cfdi3->real_escape_string($prefijobd . 'systemsettings');
$sqlSystems = "SELECT OutgoingEmailHost, OutgoingEmailUserName, OutgoingEmailPassword, OutgoingEmailPort, OutgoingEmailFromAddress, xmldir, Servidor FROM `$tabla`";
$resSQL001  = $cnx_cfdi3->prepare($sqlSystems);
if (!$resSQL001) {
    echo "<script>tsToast('Error preparando consulta systemsettings','ts-err');</script>";
    die("Error en la preparacion de la consultaMail: " . $cnx_cfdi3->error);
}
if (!$resSQL001->execute()) {
    echo "<script>tsToast('Consulta systemsettings no válida','ts-err');</script>";
    die('Consulta no valida: ' . $resSQL001->error . "\n");
}
$resSQL001->store_result();
$resSQL001->bind_result($v_host, $v_username, $v_pass, $v_port, $v_mail_from, $v_xmldir, $v_servidor);
$resSQL001->fetch();

// ===== Verificar columna DirPHPPDF =====
$tablaCol   = "{$prefijobd}systemsettings";
$columna    = "DirPHPPDF";
$checkSQL   = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND TABLE_SCHEMA = DATABASE()";
$stmt       = $cnx_cfdi3->prepare($checkSQL);
$stmt->bind_param("ss", $tablaCol, $columna);
$stmt->execute();
$stmt->bind_result($columnaExiste);
$stmt->fetch();
$stmt->close();

if (!$columnaExiste) {
    $DirPHPPDF = '0';
} else {
    $sqlDP = "SELECT DirPHPPDF FROM {$tablaCol} LIMIT 1";
    $resDP = $cnx_cfdi3->prepare($sqlDP);
    $resDP->execute();
    $resDP->bind_result($DirPHPPDF);
    $resDP->fetch();
    $resDP->close();
    if ($DirPHPPDF === null || $DirPHPPDF === '') {
        $DirPHPPDF = '0';
    }
}

// ======================================================================
// Si genera por .BAT
if ($DirPHPPDF !== '1') {
    include("cnx_cfdi2.php");
    mysqli_select_db($cnx_cfdi2, $database_cfdi);
    $debug              = 0;
    $nombrebat          = "abonospagopdf2.bat";
    $idnombrereporte    = 176;

    // Nombre del reporte
    $qryreporte = "SELECT VCHAR FROM " . $prefijobd . "parametro WHERE id2 = " . (int)$idnombrereporte;
    $resultqryreporte = mysqli_query($cnx_cfdi2, $qryreporte);
    if (!$resultqryreporte) {
        echo "<script>tsToast('No se encontró reporte','ts-err');</script>";
        die('Nombre de reporte no encontrado: ' . mysqli_error($cnx_cfdi2));
    }
    $rowreporte     = mysqli_fetch_row($resultqryreporte);
    $nombrereporte  = $rowreporte[0];

    echo "<script>tsShow('Generando PDF (BAT)…');</script>";
    flush();

    // Ejecutar BAT
    $cmd   = "C:\\xampp\\htdocs\\cfdipro\\{$nombrebat} " . escapeshellarg($_REQUEST['id']) . " " . escapeshellarg($nombrereporte) . " " . escapeshellarg($prefijobd);
    $linea = exec($cmd);
    if ($debug) { echo htmlspecialchars($linea); }
    echo "<script>tsUpdate('Esperando resultado de la generación…');</script>";
    flush();
}
// ======================================================================
// Si genera por PHP directo (complemento_formato.php)
else {
    // Datos de la factura para armar rutas
    $sqlFactura = "SELECT  b.CorreoCobranza, c.SerieFiscal, a.XFolio, a.Folio, b.RazonSocial
                   FROM {$prefijobd}abonos a
                   INNER JOIN {$prefijobd}clientes b ON a.Cliente_RID = b.ID
                   INNER JOIN {$prefijobd}oficinas c ON a.Oficina_RID = c.ID
                   INNER JOIN {$prefijobd}abonossub d ON a.ID = d.FolioSub_RID
                   WHERE a.ID = ".(int)$iddocumento;
    $resSQLFac   = $cnx_cfdi3->prepare($sqlFactura);
    $resSQLFac->execute();
    $resSQLFac->store_result();
    $resSQLFac->bind_result($correoCliente, $SerieFiscal, $XFolio, $Folio, $razonSocial);
    $resSQLFac->fetch();

    $pdfFilename = 'P'.$XFolio.'='.$SerieFiscal.'-'.$Folio.'.pdf';
    $xmlFilename = 'P'.$XFolio.'='.$SerieFiscal.'-'.$Folio.'.xml';

    $filePDF  = "C:/xampp/htdocs{$v_xmldir}/" . $pdfFilename;
    $filePath = "C:/xampp/htdocs{$v_xmldir}/" . $xmlFilename;

    // Mostrar overlay y generar
    echo "<script>tsShow('Generando tu PDF, por favor espera…');</script>";
    flush();

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://localhost/cfdipro/complemento_formato.php?id={$iddocumento}&prefijodb={$prefijobd}&tipo={$tipodoc}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60
    ]);
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $err = addslashes(curl_error($curl));
        echo "<script>tsUpdate('Error generando PDF/XML'); tsToast('Error: {$err}','ts-err');</script>";
        flush();
        die("Error generando PDF/XML: " . curl_error($curl));
    }
    curl_close($curl);
}

// ======================================================================
// Verificar existencia de archivos (en caso PHP directo ya tenemos $filePDF/$filePath)
$existePDF = '';

// Si no estaban definidas rutas (caso BAT), intentamos deducirlas:
if (!isset($filePDF) || !isset($filePath)) {
    // Repetimos consulta de factura (solo si hace falta)
    if (!isset($XFolio) || !isset($SerieFiscal) || !isset($Folio)) {
        $sqlFactura2 = "SELECT  b.CorreoCobranza, c.SerieFiscal, a.XFolio, a.Folio, b.RazonSocial
                        FROM {$prefijobd}abonos a
                        INNER JOIN {$prefijobd}clientes b ON a.Cliente_RID = b.ID
                        INNER JOIN {$prefijobd}oficinas c ON a.Oficina_RID = c.ID
                        WHERE a.ID = ".(int)$iddocumento." LIMIT 1";
        $resSQLFac2 = $cnx_cfdi3->prepare($sqlFactura2);
        $resSQLFac2->execute();
        $resSQLFac2->store_result();
        $resSQLFac2->bind_result($correoCliente, $SerieFiscal, $XFolio, $Folio, $razonSocial);
        $resSQLFac2->fetch();
    }
    $pdfFilename = 'P'.$XFolio.'='.$SerieFiscal.'-'.$Folio.'.pdf';
    $xmlFilename = 'P'.$XFolio.'='.$SerieFiscal.'-'.$Folio.'.xml';
    $filePDF     = "C:/xampp/htdocs{$v_xmldir}/" . $pdfFilename;
    $filePath    = "C:/xampp/htdocs{$v_xmldir}/" . $xmlFilename;
}

// Pequeño “wait” pasivo por si el archivo tarda en asentarse (hasta 6 intentos x 500ms = 3s)
$tries = 0;
while (!file_exists($filePDF) && $tries < 6) {
    usleep(500000); // 0.5 s
    $tries++;
}
if (file_exists($filePDF)) {
    $existePDF = '1';
    echo "<script>tsUpdate('PDF listo. Preparando envío por correo…');</script>";
    flush();
} else {
    echo "<script>tsHide(); tsToast('No existe el PDF','ts-warn');</script>";
    echo "No existe el PDF<br>";
}

// ======================================================================
// Envío de correo
if ($existePDF === '1') {

    // Cargar de nuevo systemsettings por seguridad (host/credenciales)
    $resSQL001->free_result();
    $resSQL001->close();

    $resSQL001  = $cnx_cfdi3->prepare($sqlSystems);
    if (!$resSQL001) {
        echo "<script>tsHide(); tsToast('Error preparando SMTP','ts-err');</script>";
        die("Error en la preparacion de la consultaMail: " . $cnx_cfdi3->error);
    }
    if (!$resSQL001->execute()) {
        echo "<script>tsHide(); tsToast('Error leyendo SMTP','ts-err');</script>";
        die('Consulta no valida: ' . $resSQL001->error . "\n");
    }
    $resSQL001->store_result();
    $resSQL001->bind_result($v_host, $v_username, $v_pass, $v_port, $v_mail_from, $v_xmldir, $v_servidor);
    $resSQL001->fetch();

    // Memo (id2=109)
    $sqlParametro = "SELECT Memo FROM {$prefijobd}parametro where id2='109'";
    $resSQLPara = $cnx_cfdi3->prepare($sqlParametro);
    if (!$resSQLPara) {
        echo "<script>tsHide(); tsToast('Error leyendo parámetro memo','ts-err');</script>";
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);
    }
    if (!$resSQLPara->execute()) {
        echo "<script>tsHide(); tsToast('Error ejecutando parámetro memo','ts-err');</script>";
        die('Consulta no valida: ' . $resSQLPara->error . "\n");
    }
    $resSQLPara->store_result();
    $resSQLPara->bind_result($memo);
    $resSQLPara->fetch();
    if ($memo === '') { $memo = 'Se anexa PDF y XML'; }

    // Xfolio/Razón/Correo (si no están)
    if (!isset($xfolio) || !isset($razonSocial) || !isset($correoCliente)) {
        $sqlFactura3 = "SELECT a.Xfolio, b.RazonSocial, b.CorreoCobranza
                        FROM {$prefijobd}abonos a
                        INNER JOIN {$prefijobd}clientes b On a.Cliente_RID = b.ID
                        WHERE a.id=".(int)$iddocumento." LIMIT 1";
        $resSQLFac3 = $cnx_cfdi3->prepare($sqlFactura3);
        if (!$resSQLFac3) {
            echo "<script>tsHide(); tsToast('Error leyendo datos de cliente','ts-err');</script>";
            die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);
        }
        if (!$resSQLFac3->execute()) {
            echo "<script>tsHide(); tsToast('Consulta cliente no válida','ts-err');</script>";
            die('Consulta no valida: ' . $resSQLFac3->error . "\n");
        }
        $resSQLFac3->store_result();
        $resSQLFac3->bind_result($xfolio, $razonSocial, $correoCliente);
        $resSQLFac3->fetch();
    }

    $pdfFilename = basename($filePDF);
    $xmlFilename = basename($filePath);

    // Reconfirmar rutas (evitar doble barra)
    $filePDF  = "C:/xampp/htdocs{$v_xmldir}/" . $pdfFilename;
    $filePath = "C:/xampp/htdocs{$v_xmldir}/" . $xmlFilename;

    if (!file_exists($filePDF)) {
        echo "<script>tsHide(); tsToast('PDF no existe al enviar','ts-err');</script>";
        die("El archivo PDF no existe: $filePDF");
    }
    if (!file_exists($filePath)) {
        echo "<script>tsHide(); tsToast('XML no existe al enviar','ts-err');</script>";
        die("El archivo XML no existe: $filePath");
    }

    $mail = new PHPMailer(true);

    try {
        
        $v_port = '465'; //TEMPORAL
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $v_host;  					  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $v_username;                 // SMTP username
        $mail->Password = $v_pass;                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $v_port;                                    // TCP port to connect to
        $mail->IsHTML(true);
        $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

        $mail->setFrom($v_mail_from, 'Tractosoft'); //REMITENTE

        $array_correos = explode(";", $correoCliente);
        $no_correos = count($array_correos);
        $x=0;
        while($x < $no_correos){
            $mail->addAddress($array_correos[$x]);
            $x = $x +1;
        }

        $mail->Subject = 'Tractosoft WEB Complemento: ' .$xfolio;

        $memo = str_replace('#Cliente# - #ClienteNo#', $razonSocial, $memo);
        $memo = str_replace('#Factura#', $xfolio, $memo);

        $mail->Body = $memo;

        // Adjuntamos usando las variables corregidas:
        $mail->addAttachment($filePDF, $pdfFilename);
        $mail->addAttachment($filePath, $xmlFilename);

        echo "<script>tsUpdate('Enviando correo… por favor espere unos segundos');</script>";
        flush();

        $mail->send();

        echo "<div style=\"padding:10px;background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:4px;\">✅ Correo enviado correctamente a {$correoCliente}</div>";
        echo "<script>tsHide(); tsToast('✅ Correo enviado','ts-ok');</script>";
    } catch (Exception $e) {
        http_response_code(500);
        echo "<script>tsHide(); tsToast('Error al enviar correo','ts-err');</script>";
        echo 'Excepción PHPMailer: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>';
        echo 'Detalles del error SMTP: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8');
    }
}

?>
