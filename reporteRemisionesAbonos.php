<?php
set_time_limit(3000);
error_reporting(0);

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return '$'.number_format((float)$n, 2); }

function normaliza_prefijo($raw){
    $raw = str_replace(array("'", '"', ";"), "", $raw);
    $raw = preg_replace('/[^a-zA-Z0-9_]/', '', $raw);
    if ($raw === '') return '';
    if (strpos($raw, "_") === false) $raw .= "_";
    return $raw;
}

/* ====== params (POST primera vez / GET para paginación y excel) ====== */
$prefijobd = '';
if (isset($_POST["base"])) $prefijobd = $_POST["base"];
if (isset($_GET["prefijodb"])) $prefijobd = $_GET["prefijodb"];
$prefijobd = normaliza_prefijo($prefijobd);
if ($prefijobd === '') die("Falta el prefijo de la BD.");

$unidad   = isset($_POST["unidad"]) ? (int)$_POST["unidad"] : (isset($_GET["unidad"]) ? (int)$_GET["unidad"] : 0);
$operador = isset($_POST["operador"]) ? (int)$_POST["operador"] : (isset($_GET["operador"]) ? (int)$_GET["operador"] : 0);
$serie    = isset($_POST["serie"]) ? (int)$_POST["serie"] : (isset($_GET["serie"]) ? (int)$_GET["serie"] : 0);
$cliente  = isset($_POST["cliente"]) ? (int)$_POST["cliente"] : (isset($_GET["cliente"]) ? (int)$_GET["cliente"] : 0);

$fechaInicio = '';
$fechaFin    = '';
if (isset($_POST["fechai"])) $fechaInicio = $_POST["fechai"];
if (isset($_POST["fechaf"])) $fechaFin    = $_POST["fechaf"];
if (isset($_GET["fechai"])) $fechaInicio = $_GET["fechai"];
if (isset($_GET["fechaf"])) $fechaFin    = $_GET["fechaf"];

if ($fechaInicio === '' || $fechaFin === '') die("Faltan fechas.");

$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin_f    = date("d-m-Y", strtotime($fechaFin));

$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$exportExcel = (isset($_GET['export']) && $_GET['export'] === 'excel') ? 1 : 0;

/* ===== cnx_cfdi3 ===== */
require_once('cnx_cfdi3.php');
if (!isset($cnx_cfdi3) || $cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
$cnx_cfdi3->query("SET NAMES 'utf8'");

/* ===== Jumex flag ===== */
$esJumex = 0;
$resSQLS = "SELECT Jumex FROM {$prefijobd}systemsettings LIMIT 1";
$stmtS = $cnx_cfdi3->prepare($resSQLS);
if ($stmtS) {
    $stmtS->execute();
    $stmtS->store_result();
    $stmtS->bind_result($esJumex);
    $stmtS->fetch();
    $stmtS->close();
}

/* ===== columnas Jumex (MISMA LÓGICA) ===== */
$hraCreadoHead = 'HORA CREADO';
$descargaEnHead='DESCARGA EN';
$expedienteHead='EXPEDIENTE';
$repartosHead='REPARTOS';
$FacturaHead='FACTURA';
$TransferenciaHead='TRANSFERENCIA';
$tarimasHead='TARIMAS';
$embIdHead='EMB ID';
$fechaDocHEad='FECHA DOCUMENTACION';
$bitacoraHead='BITACORA';

if ($esJumex) {
    $jumexRes = "
      DATE_FORMAT(R.Creado, '%H:%i:%s') AS Creadohora,
      R.DescargaEn,
      R.LlevaRepartos,
      R.Factura,
      R.Transferencia,
      R.IdJumex,
      R.FechaDocumentacion,
      V.XFolio,
    ";
    $jumexJoin = " LEFT JOIN TSARTURO_viajes2 AS V ON R.FolioSubViajes_RID = V.ID ";
} else {
    $jumexRes = "";
    $jumexJoin = "";
}

function jumexhtmlhead($esJumex, $txt){
    if ($esJumex) echo '<th>'.$txt.'</th>';
}
function jumexhtmlbody($esJumex, $val){
    if ($esJumex) echo '<td>'.h($val).'</td>';
}

/* ===== filtros (MISMA LÓGICA) ===== */
$filtroWhere = "";
if ($unidad > 0)   $filtroWhere .= " AND R.Unidad_RID = {$unidad} ";
if ($operador > 0) $filtroWhere .= " AND R.Operador_RID = {$operador} ";
if ($cliente > 0)  $filtroWhere .= " AND R.CargoACliente_RID = {$cliente} ";
if ($serie > 0)    $filtroWhere .= " AND R.Oficina_RID = {$serie} ";

/* ===== búsqueda general en resultados (extra, no altera lógica) ===== */
$whereSearch = "";
if ($searchTherm !== '') {
    $st = mysqli_real_escape_string($cnx_cfdi3, $searchTherm);
    $whereSearch = " AND (
        C.RazonSocial LIKE '%{$st}%'
        OR R.XFolio LIKE '%{$st}%'
        OR IFNULL(R.cfdiuuid,'') LIKE '%{$st}%'
        OR IFNULL(R.RemisionOperador,'') LIKE '%{$st}%'
        OR IFNULL(U.Unidad,'') LIKE '%{$st}%'
        OR IFNULL(O.Operador,'') LIKE '%{$st}%'
        OR IFNULL(Ru.Ruta,'') LIKE '%{$st}%'
        OR IFNULL(R.Destinatario,'') LIKE '%{$st}%'
        OR IFNULL(A.XFolio,'') LIKE '%{$st}%'
    ) ";
}

/* ===== paginación 10/10 ===== */
$per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

/* ===== COUNT ===== */
$countSQL = "
SELECT COUNT(*) AS total
FROM {$prefijobd}Remisiones AS R
LEFT JOIN {$prefijobd}Clientes AS C ON C.ID = R.CargoACliente_RID
LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = R.Unidad_RID
LEFT JOIN {$prefijobd}Unidades AS Rem ON Rem.ID = R.uRemolqueA_RID
LEFT JOIN {$prefijobd}Unidades AS Rem2 ON Rem2.ID = R.uRemolqueB_RID
LEFT JOIN {$prefijobd}Operadores AS O ON O.ID = R.Operador_RID
LEFT JOIN {$prefijobd}Rutas AS Ru ON Ru.ID = R.Ruta_RID
LEFT JOIN {$prefijobd}FacturasDetalle AS FD ON FD.Remision_RID = R.ID
LEFT JOIN {$prefijobd}Factura AS F ON F.ID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}AbonosSub AS ABS ON ABS.AbonoFactura_RID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}Abonos AS A ON A.ID = ABS.FolioSub_RID
{$jumexJoin}
WHERE DATE(R.Creado) BETWEEN ? AND ?
{$filtroWhere}
{$whereSearch}
";

$total_rows = 0;
$stmtC = $cnx_cfdi3->prepare($countSQL);
if ($stmtC) {
    $stmtC->bind_param('ss', $fechaInicio, $fechaFin);
    $stmtC->execute();
    $stmtC->bind_result($total_rows);
    $stmtC->fetch();
    $stmtC->close();
}
if ($total_rows < 0) $total_rows = 0;
$total_pages = ($total_rows > 0) ? (int)ceil($total_rows / $per_page) : 1;

/* ===== QUERY PRINCIPAL (MISMA LÓGICA) ===== */
$resSQL = "
SELECT R.ID,
    C.RazonSocial AS Cliente,
    R.Moneda,
    R.XFolio,
    R.RemisionOperador,
    R.cfdiuuid,
    DATE_FORMAT(R.Creado, '%d/%m/%Y') AS Creado,
    {$jumexRes}
    U.Unidad,
    U.Placas,
    Rem.Unidad AS Remolque,
    Rem.Placas AS RemolquePlacas,
    Rem2.Unidad AS Remolque2,
    Rem2.Placas AS Remolque2Placas,
    O.Operador,
    Ru.Ruta,
    R.Remitente,
    R.Destinatario,
    R.SeFacturoEn,
    R.Liquidacion,
    R.xPesoTotal,
    R.yFlete,
    R.zSubtotal,
    R.zImpuesto,
    R.zRetenido,
    R.zTotal,
    R.Documentador,
    R.xCantidadTotal,
    A.XFolio AS Abono,
    F.zSubtotal AS SubtotalFact,
    F.zTotal AS TotalFact,
    F.zImpuesto AS ImpuestoFact,
    F.zRetenido AS RetenidoFact
FROM {$prefijobd}Remisiones AS R
LEFT JOIN {$prefijobd}Clientes AS C ON C.ID = R.CargoACliente_RID
LEFT JOIN {$prefijobd}Unidades AS U ON U.ID = R.Unidad_RID
LEFT JOIN {$prefijobd}Unidades AS Rem ON Rem.ID = R.uRemolqueA_RID
LEFT JOIN {$prefijobd}Unidades AS Rem2 ON Rem2.ID = R.uRemolqueB_RID
LEFT JOIN {$prefijobd}Operadores AS O ON O.ID = R.Operador_RID
LEFT JOIN {$prefijobd}Rutas AS Ru ON Ru.ID = R.Ruta_RID
LEFT JOIN {$prefijobd}FacturasDetalle AS FD ON FD.Remision_RID = R.ID
LEFT JOIN {$prefijobd}Factura AS F ON F.ID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}AbonosSub AS ABS ON ABS.AbonoFactura_RID = FD.FolioSubDetalle_RID
LEFT JOIN {$prefijobd}Abonos AS A ON A.ID = ABS.FolioSub_RID
{$jumexJoin}
WHERE DATE(R.Creado) BETWEEN ? AND ?
{$filtroWhere}
{$whereSearch}
ORDER BY R.XFolio
";

/* Excel: sin LIMIT. UI: con LIMIT 10/10 */
if (!$exportExcel) {
    $resSQL .= " LIMIT {$per_page} OFFSET {$offset} ";
}

$stmt = $cnx_cfdi3->prepare($resSQL);
if (!$stmt) die("Error en la preparación de la consulta: ".$cnx_cfdi3->error);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$stmt->store_result();

/* ===== bind_result (MISMA LÓGICA) ===== */
if ($esJumex) {
    $stmt->bind_result(
        $idRem,
        $clienteTxt,
        $moneda,
        $xfolio,
        $ticket,
        $uuid,
        $creado,
        $creadohora,
        $descargaEn,
        $llevaRepartos,
        $factura,
        $transferencia,
        $idJumex,
        $fechaDocumentacion,
        $xFolioBitacora,
        $unidadTxt,
        $placas,
        $remolque,
        $remolquePlacas,
        $remolque2,
        $remolque2Placas,
        $operadorTxt,
        $ruta,
        $remitente,
        $destinatario,
        $seFacturoEn,
        $liquidacion,
        $pesoTotal,
        $flete,
        $subtotal,
        $impuesto,
        $retencion,
        $total,
        $documentador,
        $cantidadTotal,
        $abono,
        $subtotalFact,
        $totalFact,
        $impuestoFact,
        $retenidoFact
    );
} else {
    $stmt->bind_result(
        $idRem,
        $clienteTxt,
        $moneda,
        $xfolio,
        $ticket,
        $uuid,
        $creado,
        $unidadTxt,
        $placas,
        $remolque,
        $remolquePlacas,
        $remolque2,
        $remolque2Placas,
        $operadorTxt,
        $ruta,
        $remitente,
        $destinatario,
        $seFacturoEn,
        $liquidacion,
        $pesoTotal,
        $flete,
        $subtotal,
        $impuesto,
        $retencion,
        $total,
        $documentador,
        $cantidadTotal,
        $abono,
        $subtotalFact,
        $totalFact,
        $impuestoFact,
        $retenidoFact
    );
}
function siONo($varRem){
    if($varRem == 1){
        return "SI";
    } else {
        return "NO";
    }
}
/* ===== excel headers ===== */
if ($exportExcel) {
    header("Content-type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=Viajes_en_Abonos_".date("Ymd_His").".xls");
    echo "\xEF\xBB\xBF";
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Viajes en Abonos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

<?php if(!$exportExcel): ?>
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
      --row-bg:#fff;
      --row-hover:#f1f4fb;
      --header-bg:rgba(255,255,255,.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
      --row-bg:#141824;
      --row-hover:#1a2030;
      --header-bg:rgba(20,24,36,.7);
    }
    body{ margin:0; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; background:var(--bg); color:var(--text); }
    .container{ max-width:1700px; margin:40px auto; padding:20px; }
    .header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
    .header h1{ margin:0; font-size:1.8rem; font-weight:700; letter-spacing:-.5px; }
    .subtitle{ font-size:.95rem; color:var(--text-soft); }
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
      overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .panel-head{
      display:flex; align-items:center; justify-content:space-between; gap:8px;
      padding:14px 16px; flex-wrap:wrap;
    }
    .meta{ color:var(--text-soft); font-weight:600; }
    .actions{ display:flex; gap:8px; flex-wrap:wrap; }
    .btn{
      border:none; padding:8px 14px; border-radius:999px; font-weight:700;
      cursor:pointer; font-size:.9rem; text-decoration:none; display:inline-block;
    }
    .btn.excel{ background:#22c55e; color:#fff; }
    .search-bar{
      display:flex; justify-content:flex-end; gap:8px; padding:0 16px 10px 16px; flex-wrap:wrap;
    }
    .search-bar input[type="text"]{
      flex:0 0 260px; padding:6px 10px; border-radius:999px; border:var(--border);
      font-size:.85rem; background:var(--row-bg); color:var(--text); outline:none;
    }
    .search-bar button{
      padding:6px 12px; border-radius:999px; border:var(--border);
      background:var(--tint); color:#fff; font-weight:600; cursor:pointer;
    }
    .table-container{ max-height:700px; overflow:auto; border-top:var(--border); }
    table{ width:100%; border-collapse:separate; font-size:.85rem; }
    thead th{
      position:sticky; top:0; background:var(--header-bg);
      font-weight:600; padding:8px; text-align:center;
      font-size:.75rem; color:var(--text-soft);
      border-bottom:var(--border); backdrop-filter:blur(10px); z-index:2;
      white-space:nowrap;
    }
    tbody td{
      padding:8px; text-align:center; background:var(--row-bg);
      border-bottom:1px solid rgba(0,0,0,.05);
      white-space:nowrap;
    }
    tbody tr:hover td{ background:var(--row-hover); }
    .pagination{
      margin:16px 0;
      display:flex; gap:6px; justify-content:center; flex-wrap:wrap;
    }
    .pagination a{
      padding:6px 12px; border-radius:999px; border:var(--border);
      background:var(--panel); text-decoration:none; color:var(--text);
      font-weight:700; font-size:.85rem;
    }
    .pagination a.active{ background:var(--tint); color:#fff; border:none; }
    .pagination a.ghost{ background:transparent; border:none; cursor:default; opacity:.75; }
  </style>
<?php endif; ?>
</head>

<body>
<?php if(!$exportExcel): ?>
<div class="container">
  <div class="header">
    <div>
      <h1>Viajes en Abonos</h1>
      <div class="subtitle">Periodo: <?php echo h($fechaInicio_f)." - ".h($fechaFin_f); ?> · Registros: <?php echo (int)$total_rows; ?></div>
    </div>
    <button id="themeToggle" class="btn-theme" type="button">
      <span class="sun">☀️</span><span class="moon" style="display:none;">🌙</span> Tema
    </button>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div class="meta">
        Cliente ID: <?php echo (int)$cliente; ?> · Serie ID: <?php echo (int)$serie; ?> · Unidad ID: <?php echo (int)$unidad; ?> · Operador ID: <?php echo (int)$operador; ?>
      </div>
      <div class="actions">
        <?php
          $baseParams = 'fechai='.urlencode($fechaInicio).
                        '&fechaf='.urlencode($fechaFin).
                        '&prefijodb='.urlencode($prefijobd).
                        '&unidad='.(int)$unidad.
                        '&operador='.(int)$operador.
                        '&serie='.(int)$serie.
                        '&cliente='.(int)$cliente;
          if ($searchTherm !== '') $baseParams .= '&q='.urlencode($searchTherm);
        ?>
        <a class="btn excel" href="reporteRemisionesAbonos.php?<?php echo $baseParams; ?>&export=excel">📊 Exportar Excel</a>
      </div>
    </div>

    <form method="get" class="search-bar">
      <input type="hidden" name="prefijodb" value="<?php echo h($prefijobd); ?>">
      <input type="hidden" name="fechai" value="<?php echo h($fechaInicio); ?>">
      <input type="hidden" name="fechaf" value="<?php echo h($fechaFin); ?>">
      <input type="hidden" name="unidad" value="<?php echo (int)$unidad; ?>">
      <input type="hidden" name="operador" value="<?php echo (int)$operador; ?>">
      <input type="hidden" name="serie" value="<?php echo (int)$serie; ?>">
      <input type="hidden" name="cliente" value="<?php echo (int)$cliente; ?>">
      <input type="text" name="q" placeholder="Buscar en resultados..." value="<?php echo h($searchTherm); ?>">
      <button type="submit">Buscar</button>
    </form>

    <div class="table-container">
<?php endif; ?>

<table>
  <thead>
    <tr>
      <th>CLIENTE</th>
      <th>MONEDA</th>
      <th>XFOLIO</th>
      <th>TICKET</th>
      <th>UUID</th>
      <th>CREADO</th>
      <?php jumexhtmlhead($esJumex, $hraCreadoHead); ?>
      <th>UNIDAD</th>
      <th>PLACAS</th>
      <th>REMOLQUE</th>
      <th>REM PLACAS</th>
      <th>REMOLQUE 2</th>
      <th>REM 2 PLACAS</th>
      <th>OPERADOR</th>
      <th>RUTA</th>
      <th>REMITENTE</th>
      <th>DESTINATARIO</th>
      <th>SE FACTURO EN</th>
      <?php jumexhtmlhead($esJumex, $descargaEnHead); ?>
      <th>SUBTOTAL FACT</th>
      <th>IVA FACT</th>
      <th>RETENCION FACT</th>
      <th>TOTAL FACT</th>
      <th>ABONO</th>
      <th>LIQUIDACION</th>
      <th>PESO TOTAL</th>
      <th>FLETE</th>
      <th>SUBTOTAL</th>
      <th>IMPUESTO</th>
      <th>RETENIDO</th>
      <th>TOTAL</th>
      <?php jumexhtmlhead($esJumex, $expedienteHead); ?>
      <?php jumexhtmlhead($esJumex, $repartosHead); ?>
      <th>DOCUMENTADOR</th>
      <?php jumexhtmlhead($esJumex, $FacturaHead); ?>
      <?php jumexhtmlhead($esJumex, $TransferenciaHead); ?>
      <th>CANTIDAD TOTAL</th>
      <?php jumexhtmlhead($esJumex, $tarimasHead); ?>
      <?php jumexhtmlhead($esJumex, $embIdHead); ?>
      <?php jumexhtmlhead($esJumex, $fechaDocHEad); ?>
      <?php jumexhtmlhead($esJumex, $bitacoraHead); ?>
    </tr>
  </thead>
  <tbody>
<?php
$hubo = false;

while ($stmt->fetch()) {
    $hubo = true;

    // tu limpieza original para fechas
    $creado = (!empty($creado) && $creado != '0000-00-00') ? $creado : '';

    if ($esJumex) {
        // Tarimas (MISMA LÓGICA)
        $tarimas = 0;
        $tarimaSQL = "SELECT COALESCE(SUM(BL),0)
                      FROM {$prefijobd}remisionessub
                      WHERE FolioSub_RID = ?
                        AND BL IS NOT NULL";
        $stmtT = $cnx_cfdi3->prepare($tarimaSQL);
        if ($stmtT) {
            $stmtT->bind_param('i', $idRem);
            $stmtT->execute();
            $stmtT->bind_result($tarimas);
            $stmtT->fetch();
            $stmtT->close();
        }

        // Expediente (MISMA LÓGICA)
        $expediente = 0;
        $sqlExp = "SELECT EXISTS(
                    SELECT 1
                    FROM {$prefijobd}remisiones_ref
                    WHERE ID = ?
                  )";
        $stmtE = $cnx_cfdi3->prepare($sqlExp);
        if ($stmtE) {
            $stmtE->bind_param('i', $idRem);
            $stmtE->execute();
            $stmtE->bind_result($expediente);
            $stmtE->fetch();
            $stmtE->close();
        }
    }
?>
    <tr>
      <td><?php echo h($clienteTxt); ?></td>
      <td><?php echo h($moneda); ?></td>
      <td><?php echo h($xfolio); ?></td>
      <td><?php echo h($ticket); ?></td>
      <td><?php echo h($uuid); ?></td>
      <td><?php echo h($creado); ?></td>

      <?php if ($esJumex) echo '<td>'.h($creadohora).'</td>'; ?>

      <td><?php echo h($unidadTxt); ?></td>
      <td><?php echo h($placas); ?></td>
      <td><?php echo h($remolque); ?></td>
      <td><?php echo h($remolquePlacas); ?></td>
      <td><?php echo h($remolque2); ?></td>
      <td><?php echo h($remolque2Placas); ?></td>
      <td><?php echo h($operadorTxt); ?></td>
      <td><?php echo h($ruta); ?></td>
      <td><?php echo h($remitente); ?></td>
      <td><?php echo h($destinatario); ?></td>
      <td><?php echo h($seFacturoEn); ?></td>

      <?php if ($esJumex) echo '<td>'.h($descargaEn).'</td>'; ?>

      <td style="text-align:right;"><?php echo money($subtotalFact); ?></td>
      <td style="text-align:right;"><?php echo money($impuestoFact); ?></td>
      <td style="text-align:right;"><?php echo money($retenidoFact); ?></td>
      <td style="text-align:right;"><?php echo money($totalFact); ?></td>

      <td><?php echo h($abono); ?></td>
      <td><?php echo h($liquidacion); ?></td>
      <td><?php echo h($pesoTotal); ?></td>

      <td style="text-align:right;"><?php echo money($flete); ?></td>
      <td style="text-align:right;"><?php echo money($subtotal); ?></td>
      <td style="text-align:right;"><?php echo money($impuesto); ?></td>
      <td style="text-align:right;"><?php echo money($retencion); ?></td>
      <td style="text-align:right;"><?php echo money($total); ?></td>

      <?php if ($esJumex) echo '<td>'.siONo($expediente).'</td>'; ?>
      <?php if ($esJumex) echo '<td>'.siONo($llevaRepartos).'</td>'; ?>

      <td><?php echo h($documentador); ?></td>

      <?php if ($esJumex) echo '<td>'.h($factura).'</td>'; ?>
      <?php if ($esJumex) echo '<td>'.h($transferencia).'</td>'; ?>

      <td><?php echo h($cantidadTotal); ?></td>

      <?php if ($esJumex) echo '<td>'.h($tarimas).'</td>'; ?>
      <?php if ($esJumex) echo '<td>'.h($idJumex).'</td>'; ?>
      <?php if ($esJumex) echo '<td>'.h($fechaDocumentacion).'</td>'; ?>
      <?php if ($esJumex) echo '<td>'.h($xFolioBitacora).'</td>'; ?>
    </tr>
<?php
}

if (!$hubo) {
    echo '<tr><td colspan="40" style="text-align:left;padding:14px;">Sin resultados con los filtros actuales.</td></tr>';
}

$stmt->free_result();
$stmt->close();
$cnx_cfdi3->close();
?>
  </tbody>
</table>

<?php if(!$exportExcel): ?>
    </div>
  </div>

  <div class="pagination">
    <?php
      $basePag = '?prefijodb='.urlencode($prefijobd).
                 '&fechai='.urlencode($fechaInicio).
                 '&fechaf='.urlencode($fechaFin).
                 '&unidad='.(int)$unidad.
                 '&operador='.(int)$operador.
                 '&serie='.(int)$serie.
                 '&cliente='.(int)$cliente;
      if ($searchTherm !== '') $basePag .= '&q='.urlencode($searchTherm);
      $basePag .= '&page=%d';

      function pageLink($p, $label, $basePag, $isActive=false){
        $cls = $isActive ? 'active' : '';
        echo '<a class="'.$cls.'" href="'.sprintf($basePag, $p).'">'.$label.'</a>';
      }

      $window = 2;
      $start = max(1, $page - $window);
      $end   = min($total_pages, $page + $window);

      if ($total_pages > 1) {
        if ($page > 1) {
          pageLink(1, '« Primera', $basePag);
          pageLink($page-1, '‹ Anterior', $basePag);
        }

        pageLink(1, '1', $basePag, ($page==1));

        if ($start > 2) echo '<a class="ghost" href="javascript:void(0)">…</a>';

        for ($i=$start; $i<=$end; $i++){
          if ($i==1 || $i==$total_pages) continue;
          pageLink($i, (string)$i, $basePag, ($i==$page));
        }

        if ($end < ($total_pages-1)) echo '<a class="ghost" href="javascript:void(0)">…</a>';

        if ($total_pages > 1) pageLink($total_pages, (string)$total_pages, $basePag, ($page==$total_pages));

        if ($page < $total_pages) {
          pageLink($page+1, 'Siguiente ›', $basePag);
          pageLink($total_pages, 'Última »', $basePag);
        }
      }
    ?>
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
</script>
<?php endif; ?>
</body>
</html>
