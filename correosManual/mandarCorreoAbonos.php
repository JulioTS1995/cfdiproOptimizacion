<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        echo '<pre>';
        print_r($error);
        echo '</pre>';
    }
});

require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

require 'phpmailer82/PHPMailer.php';
require 'phpmailer82/SMTP.php';
require 'phpmailer82/Exception.php';
require 'phpmailer82/POP3.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\POP3;

$prefijo = $_POST['prefijo'] ?? $_GET['prefijo'] ?? null;
$usuario = $_POST['usuario'] ?? $_GET['usuario'] ?? null;
$idAbono  = $_POST['idAbono']  ?? $_GET['idAbono']  ?? null;

if (!$prefijo || !$usuario || !$idAbono) {
    die('Faltan parámetros obligatorios');
}

$remitentes = $cnx_cfdi2->query("SELECT 
    ID, CorreoEmpresa, CorreoHost, CorreoPuerto, CorreoPassword, CorreoProtocolo, CorreoSeguridad
    FROM {$prefijo}Usuarios WHERE CorreoHost IS NOT NULL ORDER BY (ID = {$usuario}) DESC, ID
");

$clientesCorreos = $cnx_cfdi2->query("SELECT ID, Correo, Nombre
    FROM {$prefijo}ClientesCorreos
    WHERE FolioSubCorreo_RID = (SELECT Cliente_RID FROM {$prefijo}Abonos WHERE ID = {$idAbono})
");

$flashMsg = '';
$flashType = 'success'; // success | error

if (isset($_POST['enviar'])) {

    if (empty($_POST['remitente_id']) || empty($_POST['destinatarios']) || empty($_POST['adjuntos'])) {
        echo "<script>
            alert('Faltan datos obligatorios');
            window.history.back();
        </script>";
        exit;
    }

    /* Obtener remitente */
    $stmt = $cnx_cfdi2->prepare("SELECT ID, CorreoEmpresa, CorreoHost, CorreoPuerto, CorreoPassword, CorreoProtocolo, CorreoSeguridad, Nombre FROM {$prefijo}Usuarios WHERE ID = ?");
    $stmt->bind_param('i', $_POST['remitente_id']);
    $stmt->execute();
    $remitente = $stmt->get_result()->fetch_assoc();
    if (!$remitente) {
        die('Remitente no valido');
    }

    $stmt2 = $cnx_cfdi2->prepare("SELECT a.cfdfolio, a.cfdserie, a.cfdfchhra as Fecha, a.XFolio, a.TotalImporte2 as Total, o.EsPagT, o.EsNotC, c.RazonSocial, c.RFC,
        (SELECT xmldir FROM {$prefijo}SystemSettings LIMIT 1) AS dirDocumentos, 
        (SELECT VCHAR FROM {$prefijo}parametro WHERE ID2 = 944 LIMIT 1) AS Asunto,
        (SELECT MEMO FROM {$prefijo}parametro WHERE ID2 = 944 LIMIT 1) AS Cuerpo
        FROM {$prefijo}Abonos a 
        LEFT JOIN {$prefijo}Oficinas o ON o.ID = a.Oficina_RID
        LEFT JOIN {$prefijo}clientes c ON c.ID = a.Cliente_RID
        WHERE a.ID = ?");
    $stmt2->bind_param('i', $idAbono);
    $stmt2->execute();
    $abono = $stmt2->get_result()->fetch_assoc();
    if (!$abono) {
        die('factura no valida');
    }
    
    $dir = str_replace('\\', '/', $abono['dirDocumentos']);

    if($abono['EsPagT']){
        $nombreArchivo = 'C:/xampp/htdocs' . rtrim($dir, '/') . '/P'.$abono['cfdserie'] . $abono['cfdfolio']. '=' .$abono['cfdserie'] . '-' . $abono['cfdfolio'];
        $tipo = 'Complemento de pago';
    }else if($abono['EsNotC']){
        $nombreArchivo = 'C:/xampp/htdocs' . rtrim($dir, '/') . '/NC'.$abono['cfdserie'] . $abono['cfdfolio']. '=' .$abono['cfdserie'] . '-' . $abono['cfdfolio'];
        $tipo = 'Note de Credito';
    }
    $folio = $abono['cfdfolio'];
    $xFolio = $abono['XFolio'];
    $fechaAbono = $abono['Fecha'];
    $totalabono = $abono['Total'];
    $nombreCliente = $abono['RazonSocial'];
    $rfcCliente = $abono['RFC'];

    $h = function($s){
      return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    };

    $html = '<!doctype html>
                <html>
                <head>
                  <meta charset="utf-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1">
                </head>
                <body style="margin:0;padding:0;background:#f3f4f6;">
                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:18px 0;">
                    <tr>
                      <td align="center" style="padding:0 12px;">

                        <table role="presentation" width="760" cellspacing="0" cellpadding="0" style="max-width:760px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                          
                        
                          <tr>
                            <td style="padding:16px 18px;font-family:Arial, sans-serif;">
                              <div style="font-size:18px;font-weight:700;color:#111827;">
                                ENVÍO A '.$h($rfcCliente).' CFD | '.$h($xFolio).'
                              </div>
                              
                            </td>
                          </tr>

                    
                          <tr>
                            <td style="padding:0 18px 14px 18px;font-family:Arial, sans-serif;">
                              <div style="font-size:13px;color:#111827;line-height:1.55;">
                                Estimado Cliente:
                                <br><br>
                                Usted está recibiendo un <b>Comprobante Fiscal Digital por Internet (CFDI)</b>.
                              </div>

                              <!-- Emisor (azul como en tu ejemplo) -->
                              <div style="margin:10px 0 0 0;font-size:12px;color: #111827;">
                                CFDI de:
                              </div>
                              <div style="margin:4px 0 0 0;font-size:13px;font-weight:700;color: #1d4ed8;">
                                D2D SERVICIOS LOGISTICOS DE MEXICO
                              </div>
                            </td>
                          </tr>

                         
                          <tr>
                            <td style="padding:0 18px;font-family:Arial, sans-serif;">
                              <div style="background: #f97316;color: #ffffff;font-weight:700;font-size:12px;padding:8px 10px;border-radius:6px 6px 0 0;">
                                INFORMACIÓN DEL COMPROBANTE FISCAL DIGITAL POR INTERNET
                              </div>

                              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-top:0;border-radius:0 0 6px 6px;overflow:hidden;">
                                <tr>
                                  <td style="width:160px;padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Folio:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($xFolio).'</td>
                                </tr>
                                <tr>
                                  <td style="padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Fecha:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($fechaAbono).'</td>
                                </tr>
                                <tr>
                                  <td style="padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Tipo:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($tipo).'</td>
                                </tr>
                              
                                <tr>
                                  <td style="padding:8px 10px;font-size:12px;color:#374151;">Total:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;">'.$h($totalabono).'</td>
                                </tr>
                              </table>
                            </td>
                          </tr>

                          <tr>
                            <td style="padding:14px 18px 0 18px;font-family:Arial, sans-serif;">
                              <div style="background:#f97316;color:#ffffff;font-weight:700;font-size:12px;padding:8px 10px;border-radius:6px 6px 0 0;">
                                RECEPTOR
                              </div>

                              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-top:0;border-radius:0 0 6px 6px;overflow:hidden;">
                                <tr>
                                  <td style="width:160px;padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Nombre:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($nombreCliente).'</td>
                                </tr>
                                <tr>
                                  <td style="padding:8px 10px;font-size:12px;color:#374151;">R.F.C.:</td>
                                  <td style="padding:8px 10px;font-size:12px;color:#111827;">'.$h($rfcCliente).'</td>
                                </tr>
                              </table>
                            </td>
                          </tr>

                         
                          <tr>
                            <td style="padding:14px 18px 18px 18px;font-family:Arial, sans-serif;">
                              <div style="font-size:12px;color:#374151;line-height:1.6;">
                                La entrega del documento fiscal a nuestros socios de negocio se realizará por correo electrónico, en representación gráfica (PDF) y archivo XML,
                                que podrá imprimir libremente e incluirlo en su contabilidad (Articulo 29, Fraccion de CFF) y resguardarlo por un período de 5 años.
                              </div>

                              <div style="margin-top:12px;font-size:12px;color:#111827;">
                                CFD_'.$h($rfcCliente).'_I_'.$h($xFolio).'.xml
                                <br>
                                CFD_'.$h($rfcCliente).'_I_'.$h($xFolio).'.pdf
                              </div>

                              <div style="margin-top:14px;font-size:13px;color:#111827;">
                                Atentamente
                                <br><br>
                                
                                <p>D2D SERVCIOS LOGISTICOS DE MEXICO<br>DSL150909LPA<br>DOM: FRAYLES #99 Col. LA DURAZNERA, TLAQUEPAQUE, JALUSCO, CP:45580</p>
                              </div>
                            </td>
                          </tr>

                        </table>

                      </td>
                    </tr>
                  </table>
                </body>
                </html>
                ';
    $asunto = 'ENVÍO A'. $rfcCliente .'CFD |'.$xFolio;

    $mail = new PHPMailer(true);

    try {
        configurarMailer($mail, $remitente);

        foreach ($_POST['destinatarios'] as $correo) {
            if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($correo);
            }
        }

        $mail->Subject = $asunto;
        $mail->Body    = $html;
        $mail->isHTML(true);

        $tempFiles = [];

        foreach ($_POST['adjuntos'] as $adj) {

            switch ($adj) {
        
                case 'xml':
                    $mail->addAttachment($nombreArchivo . '.xml');
                    break;
        
                case 'pdf':
                    $mail->addAttachment($nombreArchivo . '.pdf');
                    break;
        
                case 'evidencias':
        
                    $sql = "SELECT Evidencia_DOCDATA, Evidencia_DOCTYPE
                        FROM {$prefijo}AbonosEvidencias
                        WHERE ID IN (SELECT ID FROM {$prefijo}AbonosEvidencias_ref WHERE RID = {$idAbono})
                    ";
        
                    $res = mysqli_query($cnx_cfdi2, $sql);
        
                    if (!$res) {
                        throw new Exception(
                            'Error al obtener evidencias: ' . mysqli_error($cnx_cfdi2)
                        );
                    }
        
                    while ($row = mysqli_fetch_assoc($res)) {
        
                        if (empty($row['Evidencia_DOCDATA'])) {
                            continue;
                        }
        
                        $tmp = tempnam(sys_get_temp_dir(), 'ev_');
                        file_put_contents($tmp, $row['Evidencia_DOCDATA']);
        
                        if (filesize($tmp) > 0) {
                            $mail->addAttachment(
                                $tmp,
                                $row['Evidencia_DOCTYPE']
                            );
                            $tempFiles[] = $tmp;
                        }
                    }
        
                    break;
            }
        }        

        $mail->send();

        $flashMsg = 'Correo enviado correctamente';
        $flashType = 'success';

        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

    } catch (Exception $e) {
        $flashMsg = 'Error al enviar correo: ' . $mail->ErrorInfo;
        $flashType = 'error';
    }

}

function autenticarIMAP(array $remitente): void
{
    $host = $remitente['CorreoHost'];
    $port = $remitente['CorreoPuerto'] ?: 993;

    $mailbox = sprintf(
        '{%s:%d/imap/ssl/novalidate-cert}INBOX',
        $host,
        $port
    );

    $imap = @imap_open(
        $mailbox,
        $remitente['CorreoEmpresa'],
        $remitente['CorreoPassword'],
        0,
        1
    );

    if (!$imap) {
        throw new Exception('Error IMAP: ' . imap_last_error());
    }

    imap_close($imap);
}

function configurarMailer(PHPMailer $mail, array $remitente): void
{
    $mail->CharSet = 'UTF-8';

    switch (strtolower($remitente['CorreoProtocolo'])) {

        case 'imap':
            autenticarIMAP($remitente);
            $mail->isSMTP();
            break;

        case 'pop3':
            $pop = new POP3();
            $pop->authorise(
                $remitente['CorreoHost'],
                465,
                30,
                $remitente['CorreoEmpresa'],
                $remitente['CorreoPassword'],
                1
            );
            $mail->isSMTP();
            break;

        case 'smtp':
        default:
            $mail->isSMTP();
            break;
    }

    $mail->Host       = $remitente['CorreoHost'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $remitente['CorreoEmpresa'];
    $mail->Password   = $remitente['CorreoPassword'];
    $mail->Port       = (int)$remitente['CorreoPuerto'];

    $mail->SMTPSecure = match ($remitente['CorreoSeguridad']) {
        'ssl'        => PHPMailer::ENCRYPTION_SMTPS,
        'tls',
        'starttls'  => PHPMailer::ENCRYPTION_STARTTLS,
        default     => false
    };

    $mail->SMTPOptions = [
        'ssl' => [
            'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
        ]
    ];

    $mail->setFrom(
        $remitente['CorreoEmpresa'],
        $remitente['Nombre'] ?? $remitente['CorreoEmpresa']
    );
}

?>
<!doctype html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8">
  <title>Enviar correo · Abono</title>
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
      --panel:rgba(255,255,255,.74);
      --text:#0b0c0f;
      --text-soft:#5c6270;
      --tint:#0a84ff;
      --radius:18px;
      --shadow:0 10px 30px rgba(0,0,0,.10);
      --border:1px solid rgba(10,12,16,.08);
      --row-bg:#fff;
      --row-hover:#f1f4fb;
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:rgba(15,18,24,.78);
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 10px 30px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.08);
      --row-bg:#141824;
      --row-hover:#1a2030;
    }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial;
      background:var(--bg);
      color:var(--text);
    }
    .container{ max-width:900px; margin:36px auto; padding:18px; }
    .header{
      display:flex; align-items:center; justify-content:space-between;
      gap:12px; flex-wrap:wrap; margin-bottom:14px;
    }
    .header h1{ margin:0; font-size:1.55rem; letter-spacing:-.4px; }
    .subtitle{ color:var(--text-soft); font-weight:600; font-size:.95rem; margin-top:2px; }
    .btn-theme{
      border:none; padding:8px 14px; border-radius:999px; font-weight:800;
      background:linear-gradient(180deg,var(--tint), #3373b8ff);
      color:#fff; cursor:pointer; box-shadow:0 6px 16px rgba(0,122,255,.25);
      display:inline-flex; gap:8px; align-items:center;
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
    .grid{ display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    @media (max-width: 820px){ .grid{ grid-template-columns:1fr; } }

    label{ display:block; margin-bottom:6px; color:var(--text-soft); font-weight:800; font-size:.92rem; }
    select{
      width:100%;
      padding:10px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--row-bg);
      color:var(--text);
      outline:none;
      font-weight:700;
    }
    select:focus{ border:1px solid rgba(10,132,255,.45); box-shadow:0 0 0 4px rgba(10,132,255,.12); }
    .section-title{
      margin:4px 0 10px 0;
      font-weight:900;
      font-size:1.02rem;
      letter-spacing:-.2px;
    }

    .list{
      border:var(--border);
      border-radius:16px;
      overflow:hidden;
      background:var(--row-bg);
    }
    .list .row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      padding:10px 12px;
      border-top:1px solid rgba(0,0,0,.05);
    }
    html[data-theme="dark"] .list .row{ border-top:1px solid rgba(255,255,255,.06); }
    .list .row:first-child{ border-top:none; }
    .row:hover{ background:var(--row-hover); }
    .row .left{ display:flex; align-items:center; gap:10px; min-width:0; }
    .row .name{ font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .row .mail{ color:var(--text-soft); font-weight:700; font-size:.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .row input[type="checkbox"]{ width:18px; height:18px; accent-color: var(--tint); }

    .chips{ display:flex; flex-wrap:wrap; gap:10px; }
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:10px 12px;
      border-radius:999px;
      border:var(--border);
      background:var(--row-bg);
      font-weight:900;
      cursor:pointer;
      user-select:none;
    }
    .chip input{ width:18px; height:18px; accent-color: var(--tint); }

    .top-actions{
      display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;
      margin:10px 0 0 0;
      color:var(--text-soft);
      font-weight:800;
      font-size:.9rem;
    }
    .mini-btn{
      border:var(--border);
      background:transparent;
      color:var(--text);
      padding:8px 12px;
      border-radius:999px;
      font-weight:900;
      cursor:pointer;
    }
    .mini-btn:hover{ background:rgba(10,132,255,.10); }

    .actions{
      display:flex; justify-content:flex-end; gap:10px; margin-top:14px; flex-wrap:wrap;
    }
    .btn{
      border:none; padding:10px 16px; border-radius:999px; font-weight:900;
      cursor:pointer; font-size:.95rem; text-decoration:none; display:inline-block;
    }
    .btn.primary{ background:linear-gradient(180deg,var(--tint), #007aff); color:#fff; }
    .btn.ghost{ background:transparent; border:var(--border); color:var(--text); }

    .alert{
      margin-bottom:12px;
      border-radius:16px;
      padding:12px 14px;
      border:var(--border);
      font-weight:900;
    }
    .alert.success{ background:rgba(34,197,94,.12); }
    .alert.error{ background:rgba(244,63,94,.12); }
    .alert small{ display:block; margin-top:4px; font-weight:800; color:var(--text-soft); }

    .footnote{
      margin-top:10px;
      color:var(--text-soft);
      font-weight:700;
      font-size:.88rem;
      line-height:1.35;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Enviar correo · Abono</h1>
        <div class="subtitle">Selecciona remitente, destinatarios y adjuntos</div>
      </div>
      <button id="themeToggle" class="btn-theme" type="button">
        <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
      </button>
    </div>

    <div class="panel">
      <?php if (!empty($flashMsg)): ?>
        <div class="alert <?php echo ($flashType === 'error' ? 'error' : 'success'); ?>">
          <?php echo htmlspecialchars($flashMsg); ?>
          <small><?php echo ($flashType === 'error') ? 'Revisa configuración del remitente y adjuntos.' : 'Listo.'; ?></small>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="grid">
          <div>
            <div class="section-title">Remitente</div>
            <label for="remitente_id">Cuenta emisora</label>
            <select id="remitente_id" name="remitente_id" required>
              <?php while ($r = $remitentes->fetch_assoc()): ?>
                <option value="<?= $r['ID'] ?>">
                  <?= htmlspecialchars((string)$r['CorreoEmpresa']) ?> (<?= htmlspecialchars((string)$r['CorreoPuerto']) ?>)
                </option>
              <?php endwhile; ?>
            </select>

           
          </div>

          <div>
            <div class="section-title">Adjuntos</div>
            <div class="chips" role="group" aria-label="Adjuntos">
              <label class="chip">
                <input type="checkbox" name="adjuntos[]" value="xml">
                XML
              </label>
              <label class="chip">
                <input type="checkbox" name="adjuntos[]" value="pdf" checked>
                PDF
              </label>
              <label class="chip">
                <input type="checkbox" name="adjuntos[]" value="evidencias">
                Evidencias
              </label>
            </div>

            <div class="top-actions" style="margin-top:12px;">
              <div id="adjCount">Adjuntos seleccionados: 2</div>
            </div>
          </div>
        </div>

        <div style="margin-top:14px;">
          <div class="section-title">Destinatarios</div>

          <div class="top-actions">
            <div id="mailCount">Seleccionados: 0</div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <button type="button" class="mini-btn" id="selAll">Seleccionar todo</button>
              <button type="button" class="mini-btn" id="selNone">Quitar selección</button>
            </div>
          </div>

          <div class="list" style="margin-top:10px;">
            <?php while ($c = $clientesCorreos->fetch_assoc()): ?>
              <div class="row">
                <div class="left">
                  <input class="chkDest" type="checkbox" name="destinatarios[]" value="<?= htmlspecialchars($c['Correo']) ?>">
                  <div style="min-width:0;">
                    <div class="name"><?= htmlspecialchars($c['Nombre']) ?></div>
                    <div class="mail"><?= htmlspecialchars($c['Correo']) ?></div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <input type="hidden" name="prefijo" value="<?= htmlspecialchars($prefijo) ?>">
        <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario) ?>">
        <input type="hidden" name="idAbono"  value="<?= htmlspecialchars($idAbono) ?>">

        <div class="actions">
          <button type="reset" class="btn ghost" id="btnReset">Limpiar</button>
          <button type="submit" name="enviar" value="1" class="btn primary">Enviar correo</button>
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

function updateCounts(){
  var dest = document.querySelectorAll('.chkDest');
  var sel = 0;
  dest.forEach(function(c){ if(c.checked) sel++; });
  var mc = document.getElementById('mailCount');
  if(mc) mc.textContent = 'Seleccionados: ' + sel;

  var adj = document.querySelectorAll('input[name="adjuntos[]"]');
  var aSel = 0;
  adj.forEach(function(c){ if(c.checked) aSel++; });
  var ac = document.getElementById('adjCount');
  if(ac) ac.textContent = 'Adjuntos seleccionados: ' + aSel;
}

document.addEventListener('change', function(e){
  if(e.target && (e.target.classList.contains('chkDest') || e.target.name === 'adjuntos[]')){
    updateCounts();
  }
});

document.getElementById('selAll')?.addEventListener('click', function(){
  document.querySelectorAll('.chkDest').forEach(function(c){ c.checked = true; });
  updateCounts();
});
document.getElementById('selNone')?.addEventListener('click', function(){
  document.querySelectorAll('.chkDest').forEach(function(c){ c.checked = false; });
  updateCounts();
});
document.getElementById('btnReset')?.addEventListener('click', function(){
  setTimeout(updateCounts, 0);
});

updateCounts();
</script>
</body>
</html>
