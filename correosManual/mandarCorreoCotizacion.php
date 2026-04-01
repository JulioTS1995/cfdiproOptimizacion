<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'fatal' => true,
            'error' => $error
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
});

require_once('../cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

require 'phpmailer82/PHPMailer.php';
require 'phpmailer82/SMTP.php';
require 'phpmailer82/Exception.php';
require 'phpmailer82/POP3.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\POP3;

/* ==========================
   Helpers
========================== */
function respond($ok, $msg, $extra = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'ok' => (bool)$ok,
        'message' => (string)$msg
    ], $extra), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sanitize_prefijo($p) {
    $p = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$p);
    if ($p === '') return '';
    if (strpos($p, "_") === false) $p .= "_";
    return $p;
}

function autenticarIMAP(array $remitente): void
{
    $host = $remitente['CorreoHost'];
    $port = '465';

    $mailbox = sprintf('{%s:%d/imap/ssl/novalidate-cert}INBOX', $host, $port);

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

    switch (strtolower((string)$remitente['CorreoProtocolo'])) {
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
    $mail->Port       = '465';

    $seg = strtolower((string)$remitente['CorreoSeguridad']);
    if ($seg === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($seg === 'tls' || $seg === 'starttls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = false;
    }

    $mail->SMTPOptions = [
        'ssl' => [
            'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
        ]
    ];

    $mail->setFrom(
        $remitente['CorreoEmpresa'],
        $remitente['EmisorRazonSocial'] ?? $remitente['CorreoEmpresa']
    );
}


$prefijo = sanitize_prefijo($_POST['prefijo'] ?? $_GET['prefijo'] ?? '');
$prefijoNL = rtrim($prefijo, '_');
$usuario = (int)($_POST['usuario'] ?? $_GET['usuario'] ?? 0);

// compatibilidad: id o idFact
$idFact  = (int)($_POST['id'] ?? $_GET['id'] ?? $_POST['idFact'] ?? $_GET['idFact'] ?? 0);

if ($prefijo === '' ||  $idFact <= 0) {
    respond(false, 'Faltan parámetros obligatorios: prefijo, usuario, id', [
        'received' => ['prefijo' => $prefijo, 'id' => $idFact]
    ]);
}

/*  Datos factura + SystemSettings */

$stmt = $cnx_cfdi2->prepare("SELECT 
            OutgoingEmailFromAddress as CorreoEmpresa, 
            OutgoingEmailHost as CorreoHost,  
            OutgoingEmailPassword as CorreoPassword, 
            IncomingEmailProtocol as CorreoProtocolo, 
            'ssl' as Correoseguridad, 
            xmldir,
            RazonSocial as EmisorRazonSocial,
            rfc AS EmisorRFC,
            CONCAT(Calle, ' ',NumeroExterior, ' int: ', NumeroInterior, ' ', Colonia, ' ', municipio, ' ', estado , ' CP: ', CodigoPostal, ' ',  Pais ) as EmisorDomicilio
    FROM {$prefijo}systemsettings");
$stmt->execute();
$remitente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$remitente) {
    respond(false, 'No hay correo configurados .');
}

$stmt2 = $cnx_cfdi2->prepare("SELECT 
        f.XFolio,
        f.Creado AS Fecha,
        f.TotalImporte AS Total,
        f.Cliente_RID,
        c.RazonSocial,
        c.RFC
       
    FROM {$prefijo}cotizaciones f
    LEFT JOIN {$prefijo}clientes c ON c.ID = f.Cliente_RID
    WHERE f.ID = ?
    LIMIT 1
");
$stmt2->bind_param('i', $idFact);
$stmt2->execute();
$factura = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$factura) {
    respond(false, 'Factura no válida o no encontrada.', ['id' => $idFact]);
}

$dir = str_replace('\\', '/', (string)$remitente['xmldir']);
$dir = rtrim($dir, '/');

$basePath = 'C:/xampp/htdocs' . $dir .'/';
$nombreArchivo = $basePath . $prefijoNL . '-' . $factura['XFolio'];

$pdfPath = $nombreArchivo . '.pdf';
$pdfPath =  rtrim($pdfPath, '\\');
$pdfPath = str_replace('\\', '/',  (string)$pdfPath);
/* var_dump($pdfPath);
die (); */
if (!file_exists($pdfPath)) {
    respond(false, 'No se encontró el PDF para adjuntar.', ['pdf' => $pdfPath]);
}

/* ==========================
   3) Destinatarios automáticos (del cliente)
========================== */
$clienteRid = (int)$factura['Cliente_RID'];

$stmt3 = $cnx_cfdi2->prepare("SELECT 
        CorreoCotizacion, 
        RazonSocial as Nombre
    FROM {$prefijo}clientes
    WHERE ID = ?
");
$stmt3->bind_param('i', $clienteRid);
$stmt3->execute();
$res3 = $stmt3->get_result();

$destinatarios = [];
while ($row = $res3->fetch_assoc()) {
    $correo = trim((string)$row['CorreoCotizacion']);
    if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $destinatarios[] = $correo;
    }
}
$stmt3->close();

$destinatarios = array_values(array_unique($destinatarios));

if (count($destinatarios) === 0) {
    respond(false, 'El cliente no tiene correos válidos en CorreoCotizacion.', [
        'cliente' => $clienteRid
    ]);
}


$asunto = '';
$cuerpoTpl = '';


$cfdfolio = (string)$factura['XFolio'];
$xFolio   = $cfdfolio;

$rfcCliente    = (string)$factura['RFC'];
$nombreCliente = (string)$factura['RazonSocial'];
$fechaFactura  = (string)$factura['Creado'];
$totalFactura  = (string)$factura['Total'];

$emisorRS  = (string)($remitente['EmisorRazonSocial'] ?? '');
$emisorRFC = (string)($remitente['EmisorRFC'] ?? '');
$emisorDom = (string)($remitente['EmisorDomicilio'] ?? '');

if ($asunto === '') {
    $asunto = 'Cotizacion para ' . $rfcCliente . ' - ' . $xFolio;
}


$h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

if (trim($cuerpoTpl) !== '') {
   
    $reemplazos = [
        '{RFC}' => $rfcCliente,
        '{CLIENTE}' => $nombreCliente,
        '{FOLIO}' => $xFolio,
        '{FECHA}' => $fechaFactura,
        '{TOTAL}' => $totalFactura,
        '{EMISOR_RAZON}' => $emisorRS,
        '{EMISOR_RFC}' => $emisorRFC,
        '{EMISOR_DOM}' => $emisorDom,
    ];
    $html = strtr($cuerpoTpl, $reemplazos);
} else {
   
    $folio = $xFolio;
    $tipo = 'COTIZACION'; 

    $html = '<!doctype html>
    <html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
    <body style="margin:0;padding:0;background:#f3f4f6;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:18px 0;">
        <tr><td align="center" style="padding:0 12px;">
          <table role="presentation" width="760" cellspacing="0" cellpadding="0" style="max-width:760px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
            <tr>
              <td style="padding:16px 18px;font-family:Arial, sans-serif;">
                <div style="font-size:18px;font-weight:700;color:#111827;">
                  ENVÍO A '.$h($rfcCliente).' CFD | '.$h($folio).'
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 18px 14px 18px;font-family:Arial, sans-serif;">
                <div style="font-size:13px;color:#111827;line-height:1.55;">
                  Estimado Cliente:
                  <br><br>
                  Por este medio le compartimos la cotizacion solicitada.
                </div>

                <div style="margin:10px 0 0 0;font-size:12px;color: #111827;">Cotizacion de:</div>
                <div style="margin:4px 0 0 0;font-size:13px;font-weight:700;color: #1d4ed8;">
                  '.$h($emisorRS !== '' ? $emisorRS : 'Empresa').'
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 18px;font-family:Arial, sans-serif;">
                <div style="background: #e48744;color: #ffffff;font-weight:700;font-size:12px;padding:8px 10px;border-radius:6px 6px 0 0;">
                  INFORMACIÓN DE Cotizacion
                </div>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-top:0;border-radius:0 0 6px 6px;overflow:hidden;">
                  <tr>
                    <td style="width:160px;padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Folio de Cotizacion:</td>
                    <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($xFolio).'</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Fecha documento:</td>
                    <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($fechaFactura).'</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #e5e7eb;">Tipo documento:</td>
                    <td style="padding:8px 10px;font-size:12px;color:#111827;border-bottom:1px solid #e5e7eb;">'.$h($tipo).'</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 10px;font-size:12px;color:#374151;">Total:</td>
                    <td style="padding:8px 10px;font-size:12px;color:#111827;">'.$h($totalFactura).'</td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td style="padding:14px 18px 0 18px;font-family:Arial, sans-serif;">
                <div style="background:#f97316;color:#ffffff;font-weight:700;font-size:12px;padding:8px 10px;border-radius:6px 6px 0 0;">
                  Receptor de cotizacion
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
                  que podrá imprimir libremente e incluirlo en su contabilidad y resguardarlo.
                </div>

                <div style="margin-top:12px;font-size:12px;color:#111827;">
                    Cotizacion_'.$h($rfcCliente).'_'.$h($xFolio).'.pdf
                </div>

                <div style="margin-top:14px;font-size:13px;color:#111827;">
                  Atentamente
                  <br><br>
                  <p>'.$h($emisorRS).'<br>'.$h($emisorRFC).'<br>'.$h($emisorDom).'</p>
                </div>
              </td>
            </tr>
          </table>
        </td></tr>
      </table>
    </body></html>';
}

/* Enviar correo (HTML + PDF)*/

$mail = new PHPMailer(true);

try {
    configurarMailer($mail, $remitente);

    foreach ($destinatarios as $correo) {
        $mail->addAddress($correo);
    }

    $mail->Subject = $asunto;
    $mail->Body    = $html;
    $mail->isHTML(true);
    $mail->addAttachment($pdfPath, basename($pdfPath));


    $mail->send();

    respond(true, 'Correo enviado correctamente.', [
        'remitente' => $remitente['CorreoEmpresa'],
        'destinatarios' => $destinatarios,
        'adjunto' => $pdfPath,
        'factura' => [
            'id' => $idFact,
            'cliente' => $clienteRid,
            'folio' => $xFolio
        ]
    ]);

} catch (Exception $e) {
    respond(false, 'Error al enviar correo: ' . $mail->ErrorInfo, [
        'remitente' => $remitente['CorreoEmpresa'] ?? null
    ]);
}
