<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000);

require_once('cnx_cfdi2.php');
require_once __DIR__ . '/vendor/autoload.php'; // composer mpdf 6.1

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function normaliza_prefijo($raw){
    $raw = str_replace(array("'", '"', ";"), "", $raw);
    $raw = preg_replace('/[^a-zA-Z0-9_]/', '', $raw);
    if ($raw === '') return '';
    if (strpos($raw, "_") === false) $raw .= "_";
    return $raw;
}

if (!isset($_GET['prefijodb']) || $_GET['prefijodb'] === '') die("Falta el prefijo de la BD");
if (!isset($_GET['tipo']) || $_GET['tipo'] === '') die("Falta tipo (emp/op)");
if (!isset($_GET['id'])   || $_GET['id'] === '')   die("Falta id");

$prefijobd     = normaliza_prefijo($_GET['prefijodb']);
$tipoImpresion = $_GET['tipo']; // emp | op
$id            = (int)$_GET['id'];

if ($prefijobd === '') die("Prefijo inválido.");
if ($id <= 0) die("ID inválido.");

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$prefijo = rtrim($prefijobd, "_");

/* Settings */
$razonSocial   = '';
$sitioWeb      = '';
$telEmergencia = '';

$sql01 = "SELECT RazonSocial, Web as sitioWeb, telefono FROM {$prefijobd}systemsettings LIMIT 1";
$run01 = mysqli_query($cnx_cfdi2, $sql01);
if (!$run01) die("Error settings: ".mysqli_error($cnx_cfdi2));
if (mysqli_num_rows($run01) > 0) {
    $rowSql = mysqli_fetch_assoc($run01);
    $razonSocial   = $rowSql['RazonSocial'];
    $sitioWeb      = ($rowSql['sitioWeb'] !== '') ? $rowSql['sitioWeb'] : '';
    $telEmergencia = $rowSql['telefono'];
}

/* Colores */
$color = '#D60000';
$color_letra = '#FFFFFF';

$runSQL921 = mysqli_query($cnx_cfdi2, "SELECT VCHAR FROM {$prefijobd}parametro WHERE id2 = 921 LIMIT 1");
if ($runSQL921 && mysqli_num_rows($runSQL921) > 0) {
    $r = mysqli_fetch_assoc($runSQL921);
    if (trim($r['VCHAR']) !== '') $color = trim($r['VCHAR']);
}
$runSQL922 = mysqli_query($cnx_cfdi2, "SELECT VCHAR FROM {$prefijobd}parametro WHERE id2 = 922 LIMIT 1");
if ($runSQL922 && mysqli_num_rows($runSQL922) > 0) {
    $r = mysqli_fetch_assoc($runSQL922);
    if (trim($r['VCHAR']) !== '') $color_letra = trim($r['VCHAR']);
}

/* Logo */
$logo_rel = "imagenes/" . $prefijo . ".jpg";
if (!file_exists(__DIR__ . "/" . $logo_rel)) $logo_rel = "imagenes/NOLOGO.jpg";

/* Datos */
$nombre = '';
$idCard = '';
$puesto = '';
$rfc = '';
$correo = '';
$imss = '';
$curp = '';
$tipoSangre = '';
$telefono = '';
$fotoCredencial = '';

if ($tipoImpresion === 'emp') {
    $sqlEm = "SELECT Nombre, NumNomina, Cargo, RFC, IMSS, CURP, TipoSangre, Telefono, FotografiaEm_rutadoc
              FROM {$prefijobd}empleados
              WHERE ID = {$id}
              LIMIT 1";
    $runEm = mysqli_query($cnx_cfdi2, $sqlEm);
    if (!$runEm) die("Error empleados: ".mysqli_error($cnx_cfdi2));
    if (mysqli_num_rows($runEm) > 0) {
        $row = mysqli_fetch_assoc($runEm);
        $nombre     = $row['Nombre'];
        $idCard     = $row['NumNomina'];
        $puesto     = $row['Cargo'];
        $rfc        = $row['RFC'];
        $imss       = $row['IMSS'];
        $curp       = $row['CURP'];
        $tipoSangre = $row['TipoSangre'];
        $telefono   = $row['Telefono'];
        $fotoCredencial = $row['FotografiaEm_rutadoc'];
    }
} else {
    $sqlOp = "SELECT Nombre, NumeroOperador, RFC, CURP, IMSS, TipoSangre, Telefono, FotografiaOp_rutadoc
              FROM {$prefijobd}operadores
              WHERE ID = {$id}
              LIMIT 1";
    $runOp = mysqli_query($cnx_cfdi2, $sqlOp);

    if ($runOp && mysqli_num_rows($runOp) > 0) {
        $row = mysqli_fetch_assoc($runOp);
        $nombre     = $row['Nombre'];
        $idCard     = $row['NumeroOperador'];
        $puesto     = 'Operador';
        $rfc        = $row['RFC'];
        $curp       = $row['CURP'];
        $imss       = $row['IMSS'];
        $tipoSangre = $row['TipoSangre'];
        $telefono   = $row['Telefono'];
        $fotoCredencial = $row['FotografiaOp_rutadoc'];
    } else {
        $sqlOp2 = "SELECT Operador, Apodo, RFC, CURP, IMSS, TipoSangre, Telefono, FotografiaOp_rutadoc
                   FROM {$prefijobd}operadores
                   WHERE ID = {$id}
                   LIMIT 1";
        $runOp2 = mysqli_query($cnx_cfdi2, $sqlOp2);
        if (!$runOp2) die("Error operadores: ".mysqli_error($cnx_cfdi2));
        if (mysqli_num_rows($runOp2) > 0) {
            $row = mysqli_fetch_assoc($runOp2);
            $nombre     = $row['Operador'];
            $idCard     = $row['Apodo'];
            $puesto     = 'Operador';
            $rfc        = $row['RFC'];
            $curp       = $row['CURP'];
            $imss       = $row['IMSS'];
            $tipoSangre = $row['TipoSangre'];
            $telefono   = $row['Telefono'];
            $fotoCredencial = $row['FotografiaOp_rutadoc'];
        }
    }
}

if ($nombre === '') die("No encontré el registro con ese ID.");

/* mPDF 6.1 */
$cardW = 53.98;
$cardH = 85.60;

$mpdf = new mPDF('utf-8', array($cardW, $cardH), 0, '', 0, 0, 0, 0, 0, 0);
$mpdf->SetAutoPageBreak(false);
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetTitle('Credencial');
$mpdf->SetAuthor('TractoSoft');
$mpdf->SetBasePath(__DIR__ . '/');

ob_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin:0; }
html,body{ margin:0; padding:0; }
body{ font-family:helvetica; font-size:9.8pt; }

.card{
  width:100%;
  height:100%;
  border:0.7mm solid <?= h($color) ?>;
  border-radius:3.2mm;
  overflow:hidden;
  background:#fff;
}

.layout{ width:100%; height:100%; border-collapse:collapse; border-spacing:0; }
.layout td, .layout tr{ padding:0; border:0; }

.mid{ height:57.6mm; padding: 2.8mm 3mm; vertical-align:top; background:#fff; }

.topTable{ width:100%; border-collapse:collapse; }
.logoCell{ width:15mm; vertical-align:top; }
.rsCell{ vertical-align:top; text-align:right; font-size:8.4pt; font-weight:bold; line-height:1.15; }
.logoImg{ width:14mm; height:auto; display:block; }

.photoWrap{
  width:24mm; height:24mm;
  border-radius:3mm;
  border:0.5mm solid #e6e6e6;
  overflow:hidden;
  margin: 10 auto 2mm auto;
  background:#fff;
  text-align:center;
}
.photoImg{ width:24mm; height:auto; display:block; margin:0 auto; }

.name{ text-align:center; font-size:11.2pt; font-weight:bold; margin-top:1mm; }
.sub{ text-align:center; font-size:9.2pt; margin-top:0.6mm; color:#444; }

.rows{ margin-top:2.6mm; }
.row{ border-top:0.2mm solid #e6e6e6; padding:1.2mm 0; font-size:9pt; }
.lbl{ font-weight:bold; color:#333; width:12mm; display:inline-block; }
.val{ color:#111;  font-size: 8.4pt;}

.center{ text-align:center; }
.left{ text-align: left;}
.small{ font-size:8.4pt; opacity:0.95; }

.signLine{
  border-top:0.2mm solid #999;
  margin-top:6mm;
  padding-top:1mm;
  text-align:center;
  font-size:8.8pt;
  color:#333;
}

.page-break{ page-break-after:always; }
</style>
</head>
<body>

<?php
// Helpers para TOP/BOT con bgcolor (mPDF-safe)
function topBlock($color, $color_letra, $logo_rel, $razonSocial){
    $c  = h($color);
    $cl = h($color_letra);
    ?>
    <table width="100%" height="15mm" cellpadding="0" cellspacing="0" bgcolor="<?php echo $c; ?>" style="border-collapse:collapse;">
      <tr>
        <td style="padding:2.2mm 2.6mm; color:<?php echo $cl; ?>;">
          <table class="topTable">
            <tr>
              <td class="logoCell"><img class="logoImg" src="<?php echo $logo_rel; ?>"></td>
              <td class="rsCell" style="color:<?php echo $cl; ?>;"><?php echo h($razonSocial); ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <?php
}
function botBlock($color, $color_letra, $telEmergencia, $sitioWeb){
    $c  = h($color);
    $cl = h($color_letra);
    ?>
    <table width="100%" height="14mm" cellpadding="0" cellspacing="0" bgcolor="<?php echo $c; ?>" style="border-collapse:collapse;">
      <tr>
        <td style="padding:1.8mm 2.6mm; color:<?php echo $cl; ?>; text-align:center;">
          <div class="center small" style="color:<?php echo $cl; ?>;"><b>Emergencias:</b> <?php echo h($telEmergencia); ?></div>
          <div class="center small" style="color:<?php echo $cl; ?>;"><?php echo h($sitioWeb); ?></div>
        </td>
      </tr>
    </table>
    <?php
}
?>

<!-- FRENTE -->
<div class="card">
  <table class="layout">
    <tr><td><?php topBlock($color,$color_letra,$logo_rel,$razonSocial); ?></td></tr>

    <tr>
      <td class="mid">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
              <table width="46mm" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td align="center"><br>
                    <div class="photoWrap"><img class="photoImg" src="<?= h($fotoCredencial) ?>"><br></div>
                    <div class="name"><?= h($nombre) ?></div>
                    <div class="sub"><?= h($puesto) ?></div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="rows">
                      <div class="row"><span class="lbl">ID:</span> <span class="val"><?= h($idCard) ?></span></div>
                      <div class="row"><span class="lbl">RFC:</span> <span class="val"><?= h($rfc) ?></span></div>
                      <?php if ($correo !== '') { ?>
                        <div class="row"><span class="lbl">Mail:</span> <span class="val"><?= h($correo) ?></span></div>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr><td><?php botBlock($color,$color_letra,$telEmergencia,$sitioWeb); ?></td></tr>
  </table>
</div>

<div class="page-break"></div>

<!-- REVERSO -->
<div class="card">
  <table class="layout">
    <tr><td><?php topBlock($color,$color_letra,$logo_rel,$razonSocial); ?></td></tr>

    <tr>
      <td class="mid">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
              <table width="46mm" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td  style="text-align: left;">
                    <div class="rows">
                        <br>
                      <div class="row left"style="text-align: left;"><span class="lbl left" style="text-align: left;">CURP:</span> <span  class="val"><?= h($curp) ?></span></div>
                      <div class="row left"style="text-align: left;"><span class="lbl left" style="text-align: left;">IMSS:</span> <span class="val"><?= h($imss) ?></span></div>
                      <div class="row left"style="text-align: left;"><span class="lbl left" style="text-align: left;">Sangre:</span> <span class="val"><?= h($tipoSangre) ?></span></div>
                      <div class="row left"style="text-align: left;"><span class="lbl left" style="text-align: left;">Tel:</span> <span class="val"><?= h($telefono) ?></span></div>
                    </div>

                    <br><br>
                    <div class="signLine">Firma Depto. de RRHH</div>
                    <br><br>
                    <div class="signLine" style="margin-top: 5mm;">Firma <?= h($puesto) ?></div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr><td><?php botBlock($color,$color_letra,$telEmergencia,$sitioWeb); ?></td></tr>
  </table>
</div>

</body>
</html>
<?php
$html = ob_get_clean();
while (ob_get_level() > 0) { @ob_end_clean(); }

$mpdf->WriteHTML($html);
$mpdf->Output("Credencial-{$nombre}.pdf", 'I');
exit;