<?php
error_reporting(0);
set_time_limit(900);
ini_set('default_charset', 'UTF-8');

$prefijobd = '';
if (isset($_POST['prefijodb'])) $prefijobd = $_POST['prefijodb'];
if (isset($_GET['prefijodb']))  $prefijobd = $_GET['prefijodb'];

if ($prefijobd == '') die("Falta el prefijo de la BD");

// Normalizar prefijo (con _)
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);
if (strpos($prefijobd, "_") === false) $prefijobd .= "_";


$prefijo_logo = rtrim($prefijobd, "_");


$logo_file = __DIR__ . "/imagenes/" . $prefijo_logo . ".jpg";

// Si no existe .jpg, opcional: intenta .png
if (!file_exists($logo_file)) {
    $logo_png = __DIR__ . "/imagenes/" . $prefijo_logo . ".png";
    if (file_exists($logo_png)) $logo_file = $logo_png;
}

// Si de plano no existe, lo dejamos vacío
if (!file_exists($logo_file)) $logo_file = '';

$fechai = isset($_POST['fechai']) ? $_POST['fechai'] : (isset($_GET['fechai']) ? $_GET['fechai'] : '');
$fechaf = isset($_POST['fechaf']) ? $_POST['fechaf'] : (isset($_GET['fechaf']) ? $_GET['fechaf'] : '');

if ($fechai=='' || $fechaf=='') {
    $hoy = date('Y-m-d');
    $menos30 = date('Y-m-d', strtotime('-30 days'));
    if ($fechai=='') $fechai = $menos30;
    if ($fechaf=='') $fechaf = $hoy;
}

$cliente_id = 0;
if (isset($_POST['cliente_id'])) $cliente_id = intval($_POST['cliente_id']);
if (isset($_GET['cliente_id']))  $cliente_id = intval($_GET['cliente_id']);
if (isset($_POST['cliente']))    $cliente_id = intval($_POST['cliente']); // compat viejo

$moneda = '';
if (isset($_POST['moneda'])) $moneda = strtoupper(trim($_POST['moneda']));
if (isset($_GET['moneda']))  $moneda = strtoupper(trim($_GET['moneda']));
if ($moneda=='') $moneda = 'TODOS';

$sucursal = 0;
if (isset($_POST['sucursal'])) $sucursal = intval($_POST['sucursal']);
if (isset($_GET['sucursal']))  $sucursal = intval($_GET['sucursal']);

$q = '';
if (isset($_POST['q'])) $q = trim($_POST['q']);
if (isset($_GET['q']))  $q = trim($_GET['q']);

$export = '';
if (isset($_POST['export'])) $export = $_POST['export'];
if (isset($_GET['export']))  $export = $_GET['export'];

// compat viejo: btnEnviar=PDF|Excel
if ($export=='') {
    if (isset($_POST['btnEnviar'])) {
        $export = (strtoupper($_POST['btnEnviar'])=='PDF') ? 'pdf' : 'excel';
    } else {
        $export = 'pdf';
    }
}

if ($export!='pdf' && $export!='excel') die("Export inválido.");

// ============================
// Conexion mysqli (cnx_cfdi3)
// ============================
require_once('cnx_cfdi3.php');
mysqli_select_db($cnx_cfdi3, $database_cfdi);
mysqli_query($cnx_cfdi3, "SET NAMES 'utf8'");

// ============================
// Encabezado empresa (igual)
// ============================
$RazonSocial = '';
$resSQL0 = mysqli_query($cnx_cfdi3, "SELECT RazonSocial FROM ".$prefijobd."systemsettings LIMIT 1");
if ($resSQL0 && mysqli_num_rows($resSQL0)>0) {
    $rowSQL0 = mysqli_fetch_assoc($resSQL0);
    $RazonSocial = $rowSQL0['RazonSocial'];
}

// Fecha “bonita” como tu estilo viejo
$anio_logs = date('Y');
$mes_logs  = date('m');
$dia_logs  = date('d');

switch ($mes_logs) {
  case '01': $mes2="Enero"; break;
  case '02': $mes2="Febrero"; break;
  case '03': $mes2="Marzo"; break;
  case '04': $mes2="Abril"; break;
  case '05': $mes2="Mayo"; break;
  case '06': $mes2="Junio"; break;
  case '07': $mes2="Julio"; break;
  case '08': $mes2="Agosto"; break;
  case '09': $mes2="Septiembre"; break;
  case '10': $mes2="Octubre"; break;
  case '11': $mes2="Noviembre"; break;
  case '12': $mes2="Diciembre"; break;
}
$fecha = $dia_logs." de ".$mes2." de ".$anio_logs;

// ============================
// Filtros SQL (misma lógica)
// ============================
$sql_cliente = ($cliente_id==0) ? "" : " AND f.CargoAFactura_RID = ".$cliente_id;

$sql_moneda = "";
if ($moneda!='TODOS' && $moneda!='') {
    $moneda_safe = mysqli_real_escape_string($cnx_cfdi3, $moneda);
    $sql_moneda = " AND f.Moneda='".$moneda_safe."' ";
}

// buscador SQL en export también (opcional)
$sql_q = "";
if ($q!='') {
    $q_safe = mysqli_real_escape_string($cnx_cfdi3, $q);
    $sql_q = " AND (
        f.XFolio LIKE '%$q_safe%' OR
        f.Ticket LIKE '%$q_safe%' OR
        c.RazonSocial LIKE '%$q_safe%' OR
        r.XFolio LIKE '%$q_safe%'
    ) ";
}

// sucursal tolerante (si no existe, no filtra)
$has_sucursal = 0;
$clientes_table = $prefijobd."clientes";
$sqlChk = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA='".mysqli_real_escape_string($cnx_cfdi3,$database_cfdi)."'
             AND TABLE_NAME='".mysqli_real_escape_string($cnx_cfdi3,$clientes_table)."'
             AND COLUMN_NAME='Sucursal_RID'
           LIMIT 1";
$resChk = mysqli_query($cnx_cfdi3, $sqlChk);
if ($resChk && mysqli_num_rows($resChk)>0) $has_sucursal = 1;

$sql_sucursal = "";
if ($sucursal!=0 && $has_sucursal==1) {
    $sql_sucursal = " AND c.Sucursal_RID = ".$sucursal." ";
}

// ============================
// Query base (para PDF y Excel)
// ============================
$sql = "SELECT
            f.ID, f.Creado, f.XFolio, f.Moneda, f.Ticket,
            f.zSubtotal, f.zImpuesto, f.zRetenido, f.zTotal,
            c.RazonSocial,
            MAX(r.XFolio) AS CartaPorte
        FROM ".$prefijobd."factura f
        INNER JOIN ".$prefijobd."clientes c ON f.CargoAFactura_RID=c.ID
        LEFT JOIN ".$prefijobd."facturasdetalle d ON d.FolioSubDetalle_RID=f.ID
        LEFT JOIN ".$prefijobd."remisiones r ON d.Remision_RID=r.ID
        WHERE DATE(f.Creado) BETWEEN '".$fechai."' AND '".$fechaf."'
          AND f.FECreado IS NOT NULL
          AND f.cCanceladoT IS NULL
          AND f.CargoAFactura_RID IS NOT NULL
          $sql_cliente
          $sql_moneda
          $sql_sucursal
          $sql_q
        GROUP BY f.ID
        ORDER BY c.RazonSocial, f.XFolio";

$res = mysqli_query($cnx_cfdi3, $sql);

// ============================
// Construcción HTML (PDF/Excel)
// ============================
$html = '
<meta charset="utf-8">
<style>
  body{ font-family: Arial, sans-serif; font-size: 11.5px; color:#111; }
  .header{
    border-bottom: 1px solid #e6e6e6;
    padding-bottom: 10px;
    margin-bottom: 12px;
  }
  .headtbl{ width:100%; border-collapse:collapse; }
  .headtbl td{ vertical-align:middle; }
  .logo{
    width: 170px;
    height: 52px;
    object-fit: contain;
  }
  .rs{
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .2px;
    margin:0;
  }
  .title{
    margin:2px 0 0 0;
    font-size: 14px;
    font-weight: 700;
  }
  .meta{
    margin-top: 6px;
    color:#555;
    font-size: 11px;
  }
  table.rep{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
  }
  table.rep th{
    background:#f4f6f9;
    border:1px solid #dcdfe6;
    padding:7px 6px;
    font-size:11px;
    text-align:center;
  }
  table.rep td{
    border:1px solid #e3e6ee;
    padding:6px 6px;
    font-size:11px;
  }
  .left{ text-align:left; }
  .center{ text-align:center; }
  .right{ text-align:right; }
  .money{ white-space:nowrap; }
  tr.zebra:nth-child(even) td{ background:#fbfcff; }
  .totals td{
    background:#eef3ff;
    font-weight:700;
  }
</style>

<div class="header">
  <table class="headtbl">
    <tr>
      <td style="width:200px;">';

if ($logo_file != '') {
    // OJO: file:// funciona bien en Windows con mpdf legacy
    $html .= '<img class="logo" src="file:///'.$logo_file.'" />';
}

$html .= '</td>
      <td>
        <p class="rs">'.htmlspecialchars($RazonSocial, ENT_QUOTES, "UTF-8").'</p>
        <p class="title">Ventas por Cliente</p>
        <div class="meta">
          Del <b>'.htmlspecialchars($fechai, ENT_QUOTES, "UTF-8").'</b> al <b>'.htmlspecialchars($fechaf, ENT_QUOTES, "UTF-8").'</b>
          · Moneda: <b>'.htmlspecialchars($moneda, ENT_QUOTES, "UTF-8").'</b>
        </div>
      </td>
      <td style="width:180px;" class="right">
        <div class="meta">Emitido: <b>'.htmlspecialchars($fecha, ENT_QUOTES, "UTF-8").'</b></div>
      </td>
    </tr>
  </table>
</div>

<table class="rep">
  <thead>
    <tr>
      <th style="width:95px;">Fecha</th>
      <th style="width:70px;">Folio</th>
      <th style="width:65px;">Moneda</th>
      <th>Cliente</th>
      <th style="width:85px;">Tracking</th>
      <th style="width:90px;">Carta Porte</th>
      <th style="width:85px;">Subtotal</th>
      <th style="width:75px;">IVA</th>
      <th style="width:75px;">IVA Ret</th>
      <th style="width:85px;">Neto</th>
    </tr>
  </thead>
  <tbody>
';

$TotSub=0; $TotIva=0; $TotRet=0; $TotNet=0;

if ($res) {
  $i=0;
  while($r = mysqli_fetch_assoc($res)){
    $i++;
    $TotSub += (float)$r['zSubtotal'];
    $TotIva += (float)$r['zImpuesto'];
    $TotRet += (float)$r['zRetenido'];
    $TotNet += (float)$r['zTotal'];

    $html .= '
    <tr class="zebra">
      <td class="center">'.htmlspecialchars($r['Creado'],ENT_QUOTES,'UTF-8').'</td>
      <td class="center">'.htmlspecialchars($r['XFolio'],ENT_QUOTES,'UTF-8').'</td>
      <td class="center">'.htmlspecialchars($r['Moneda'],ENT_QUOTES,'UTF-8').'</td>
      <td class="left">'.htmlspecialchars($r['RazonSocial'],ENT_QUOTES,'UTF-8').'</td>
      <td class="center">'.htmlspecialchars($r['Ticket'],ENT_QUOTES,'UTF-8').'</td>
      <td class="center">'.htmlspecialchars($r['CartaPorte'],ENT_QUOTES,'UTF-8').'</td>
      <td class="right money">$'.number_format((float)$r['zSubtotal'],2).'</td>
      <td class="right money">$'.number_format((float)$r['zImpuesto'],2).'</td>
      <td class="right money">$'.number_format((float)$r['zRetenido'],2).'</td>
      <td class="right money">$'.number_format((float)$r['zTotal'],2).'</td>
    </tr>';
  }
}

$html .= '
    <tr class="totals">
      <td colspan="6" class="right">TOTALES</td>
      <td class="right money">$'.number_format($TotSub,2).'</td>
      <td class="right money">$'.number_format($TotIva,2).'</td>
      <td class="right money">$'.number_format($TotRet,2).'</td>
      <td class="right money">$'.number_format($TotNet,2).'</td>
    </tr>
  </tbody>
</table>
';

if ($export=='excel') {
    header("Content-type: application/vnd.ms-excel; charset=UTF-8");
    $nombre="Ventas_Por_Cliente_".date("H-i-s")."_".date("d-m-Y").".xls";
    header("Content-Disposition: attachment; filename=$nombre");
    echo "\xEF\xBB\xBF";
    echo $html;
    exit;
}


require_once('lib_mpdf/pdf/mpdf.php');

$mpdf = new mPDF('c', 'A4');
$mpdf->setFooter(' {DATE d-m-Y } /  Hoja {PAGENO}');
$mpdf->defaultfooterline = 0;

$mpdf->WriteHTML($html);
$mpdf->Output('Ventas_Por_Cliente.pdf', 'I');
exit;