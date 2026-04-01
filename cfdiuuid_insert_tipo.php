<?php

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

$cfdiuuid       = isset($_GET["cfdiuuid"])       ? trim($_GET["cfdiuuid"]) : '';
$xfolio         = isset($_GET["xfolio"])         ? trim($_GET["xfolio"]) : '';
$tiporelacion   = isset($_GET["tiporelacion"])   ? trim($_GET["tiporelacion"]) : '';
$idfactura      = isset($_GET["foliofactura"])   ? (int)$_GET["foliofactura"] : 0;
$prefijodb_raw  = isset($_GET["prefijodb"])      ? $_GET["prefijodb"] : '';
$facturaorigen  = isset($_GET["facturaorigen"])  ? trim($_GET["facturaorigen"]) : '';
$id_factura_sel = isset($_GET["id_factura_sel"]) ? (int)$_GET["id_factura_sel"] : 0;
$tiporelacion2  = isset($_GET["tiporelacion2"])  ? trim($_GET["tiporelacion2"]) : '';

$prefijodb = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijodb_raw);
if (strpos($prefijodb, '_') === false) {
    $prefijodb .= '_';
}
if (substr($prefijodb, -1) !== '_') {
    $prefijodb .= '_';
}

if (!$cfdiuuid || !$xfolio || !$tiporelacion || !$idfactura || !$prefijodb || !$facturaorigen) {
    die("Faltan datos para relacionar el CFDI.");
}

$newid    = null;
$basidgen = null;

/**
 * =========================
 *  Generar ID (bas_idgen)
 * =========================
 */
mysqli_query($cnx_cfdi3, "BEGIN");

$qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
$result_qry_basidgen = mysqli_query($cnx_cfdi3, $qry_basidgen);

if (!$result_qry_basidgen) {
    mysqli_query($cnx_cfdi3, "ROLLBACK");
    die("Error al obtener bas_idgen.");
}

$rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
if ($rowbasidgen) {
    $basidgen = (int)$rowbasidgen[0] + 1;
} else {
    $basidgen = 1;
}

$upd_basidgen = "UPDATE bas_idgen SET MAX_ID = ?";
$stmt_upd = $cnx_cfdi3->prepare($upd_basidgen);
if (!$stmt_upd) {
    mysqli_query($cnx_cfdi3, "ROLLBACK");
    die("Error al preparar actualización de bas_idgen.");
}
$stmt_upd->bind_param('i', $basidgen);
if (!$stmt_upd->execute()) {
    mysqli_query($cnx_cfdi3, "ROLLBACK");
    $stmt_upd->close();
    die("Error al actualizar bas_idgen.");
}
$stmt_upd->close();

mysqli_query($cnx_cfdi3, "COMMIT");

$newid = $basidgen;

$resultado = '';
$esError   = false;
$time      = time();
$fecha     = date("Y-m-d H:i:s", $time);

/**
 * ==========================================================
 *  VALIDACIÓN CORRECTA:
 *  - Antes bloqueabas si ya existía ALGUNA relación con:
 *      FolioSub_RID + TipoRelacion
 *  - Ahora SOLO bloqueamos duplicado exacto:
 *      FolioSub_RID + TipoRelacion + TipoRelacion2 + cfdiuuidRelacionado
 *    (así puedes meter N folios relacionados distintos)
 * ==========================================================
 */
$esDuplicado = false;
$sqlCheck = "SELECT COUNT(*) as total
             FROM {$prefijodb}facturauuidrelacionadosub
             WHERE FolioSub_RID = ?
               AND TipoRelacion = ?
               AND IFNULL(TipoRelacion2,'') = ?
               AND cfdiuuidRelacionado = ?";

$stmtCheck = $cnx_cfdi3->prepare($sqlCheck);
if (!$stmtCheck) {
    die("Error al preparar validación de relación.");
}

$tr2 = $tiporelacion2; // para bind
$stmtCheck->bind_param('isss', $idfactura, $tiporelacion, $tr2, $cfdiuuid);
$stmtCheck->execute();
$stmtCheck->bind_result($totalDup);
if ($stmtCheck->fetch()) {
    $esDuplicado = ((int)$totalDup > 0);
}
$stmtCheck->close();

if (!$esDuplicado) {

    // Insert en facturauuidrelacionadosub (permitir múltiples)
    $sqlInsert = "INSERT INTO {$prefijodb}facturauuidrelacionadosub
        (ID, cfdiuuidRelacionado, XFolio, FolioSub_RID, FolioSub_REN, FolioSub_RMA, BASTIMESTAMP, TipoRelacion, TipoRelacion2)
        VALUES (?, ?, ?, ?, 'Factura', 'FolioSubUUIDRelacionado', ?, ?, ?)";

    $stmtIns = $cnx_cfdi3->prepare($sqlInsert);
    if (!$stmtIns) {
        $esError   = true;
        $resultado = "Ocurrió un error al preparar el registro de relación.";
    } else {
        $stmtIns->bind_param(
            'ississs',
            $newid,
            $cfdiuuid,
            $xfolio,
            $idfactura,
            $fecha,
            $tiporelacion,
            $tiporelacion2
        );
        if (!$stmtIns->execute()) {
            $esError   = true;
            $resultado = "Ocurrió un error al guardar la relación en base de datos.";
        }
        $stmtIns->close();
    }

    if (!$esError) {

        // Si es relación con remisiones (tiporelacion2 == '066')
        if ($tiporelacion2 === '066') {

            // Actualizar remisión RelacionadoPor = facturaorigen
            $sqlUpdRem = "UPDATE {$prefijodb}remisiones
                          SET RelacionadoPor = ?
                          WHERE ID = ?";
            $stmtRem = $cnx_cfdi3->prepare($sqlUpdRem);
            if ($stmtRem) {
                $stmtRem->bind_param('si', $facturaorigen, $id_factura_sel);
                if (!$stmtRem->execute()) {
                    $esError   = true;
                    $resultado = "Se guardó la relación, pero ocurrió un error al actualizar la remisión.";
                }
                $stmtRem->close();
            } else {
                $esError   = true;
                $resultado = "Se guardó la relación, pero ocurrió un error al preparar actualización de remisión.";
            }

            // Actualizar TipoRelacion en factura origen (esto puede quedar igual)
            if (!$esError) {
                $sqlUpdFactTipo = "UPDATE {$prefijodb}factura
                                   SET TipoRelacion = ?
                                   WHERE ID = ?";
                $stmtTF = $cnx_cfdi3->prepare($sqlUpdFactTipo);
                if ($stmtTF) {
                    $stmtTF->bind_param('si', $tiporelacion, $idfactura);
                    if (!$stmtTF->execute()) {
                        $esError   = true;
                        $resultado = "Se guardó la relación, pero ocurrió un error al actualizar la factura origen.";
                    }
                    $stmtTF->close();
                } else {
                    $esError   = true;
                    $resultado = "Se guardó la relación, pero ocurrió un error al preparar actualización de factura origen.";
                }
            }

            if (!$esError) {
                $resultado = "La remisión <strong>{$xfolio}</strong> fue anexada con éxito a la factura <strong>{$facturaorigen}</strong>.";
            }

        } else {

            // Relación entre facturas
           
            $TIPO_SUSTITUCION = '04';

            
                $sqlUpdFactTipo = "UPDATE {$prefijodb}factura
                                   SET TipoRelacion = ?
                                   WHERE ID = ?";
                $stmtTF = $cnx_cfdi3->prepare($sqlUpdFactTipo);
                if ($stmtTF) {
                    $stmtTF->bind_param('si', $tiporelacion, $idfactura);
                    if (!$stmtTF->execute()) {
                        $esError   = true;
                        $resultado = "Se guardó la relación, pero ocurrió un error al actualizar la factura origen.";
                    }
                    $stmtTF->close();
                } else {
                    $esError   = true;
                    $resultado = "Se guardó la relación, pero ocurrió un error al preparar actualización de la factura origen.";
                }
           
                
           
                $sqlUpdSel = "UPDATE {$prefijodb}factura
                              SET cfdiSustituidaPor = ?
                              WHERE ID = ?";
                $stmtSel = $cnx_cfdi3->prepare($sqlUpdSel);
                if ($stmtSel) {
                    $stmtSel->bind_param('si', $facturaorigen, $id_factura_sel);
                    if (!$stmtSel->execute()) {
                        $esError   = true;
                        $resultado = "Se guardó la relación, pero ocurrió un error al actualizar la factura relacionada.";
                    }
                    $stmtSel->close();
                } else {
                    $esError   = true;
                    $resultado = "Se guardó la relación, pero ocurrió un error al preparar actualización de la factura relacionada.";
                }
            

            // Actualizar TipoRelacion en factura origen (ok si todas las relacionadas comparten el mismo tipo)
            if (!$esError) {
                $sqlUpdFactTipo = "UPDATE {$prefijodb}factura
                                   SET TipoRelacion = ?
                                   WHERE ID = ?";
                $stmtTF = $cnx_cfdi3->prepare($sqlUpdFactTipo);
                if ($stmtTF) {
                    $stmtTF->bind_param('si', $tiporelacion, $idfactura);
                    if (!$stmtTF->execute()) {
                        $esError   = true;
                        $resultado = "Se guardó la relación, pero ocurrió un error al actualizar la factura origen.";
                    }
                    $stmtTF->close();
                } else {
                    $esError   = true;
                    $resultado = "Se guardó la relación, pero ocurrió un error al preparar actualización de la factura origen.";
                }
            }

            if (!$esError) {
                $resultado = "La factura <strong>{$xfolio}</strong> fue anexada con éxito a la factura <strong>{$facturaorigen}</strong>.";
            }
        }
    }

} else {
    // Duplicado exacto (mismo UUID ya relacionado con ese tipo)
    $resultado = "El documento <strong>{$xfolio}</strong> ya estaba relacionado previamente con la factura <strong>{$facturaorigen}</strong> (misma relación).<br>No se insertó un duplicado.";
}

$cnx_cfdi3->close();

$tipoTitulo = (!$esDuplicado && !$esError)
    ? "Relación registrada"
    : ($esError ? "Ocurrió un problema" : "Relación existente");

$emoji = (!$esDuplicado && !$esError)
    ? "✅"
    : ($esError ? "⚠️" : "ℹ️");
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8">
  <title><?php echo $tipoTitulo; ?> - Relación CFDI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg:#f3f4f6;
      --panel:#ffffff;
      --text:#0b0c0f;
      --text-soft:#6b7280;
      --tint:#0a84ff;
      --radius:18px;
      --shadow:0 14px 40px rgba(15,23,42,.18);
      --border:1px solid rgba(148,163,184,.4);
      --success:#22c55e;
      --warning:#f97316;
    }
    html[data-theme="dark"]{
      --bg:#020617;
      --panel:#020617;
      --text:#e5e7eb;
      --text-soft:#9ca3af;
      --tint:#38bdf8;
      --shadow:0 24px 60px rgba(0,0,0,.75);
      --border:1px solid rgba(31,41,55,.9);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:16px;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:radial-gradient(circle at top,#e5f2ff 0,var(--bg) 52%);
      color:var(--text);
    }
    .card{
      max-width:520px;
      width:100%;
      background:var(--panel);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      border:var(--border);
      padding:22px 22px 18px;
      text-align:center;
    }
    .emoji{
      font-size:2.4rem;
      margin-bottom:4px;
    }
    .title{
      font-size:1.35rem;
      font-weight:700;
      margin-bottom:6px;
      letter-spacing:-0.3px;
    }
    .message{
      font-size:0.95rem;
      color:var(--text-soft);
      margin-bottom:16px;
      line-height:1.5;
    }
    .pill-row{
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:8px;
      margin-bottom:14px;
      font-size:0.78rem;
    }
    .pill{
      padding:3px 10px;
      border-radius:999px;
      border:var(--border);
      color:var(--text-soft);
      background:rgba(255,255,255,.7);
    }
    .hint{
      margin-top:10px;
      font-size:0.75rem;
      color:var(--text-soft);
    }
  </style>
</head>
<body>
<div class="card">
  <div class="emoji"><?php echo $emoji; ?></div>
  <div class="title"><?php echo htmlspecialchars($tipoTitulo); ?></div>

  <div class="message">
    <?php echo $resultado; ?>
  </div>

  <div class="pill-row">
    <?php if ($xfolio): ?>
      <div class="pill">Documento seleccionado: <strong style="margin-left:4px;"><?php echo htmlspecialchars($xfolio); ?></strong></div>
    <?php endif; ?>
    <?php if ($facturaorigen): ?>
      <div class="pill">Factura origen: <strong style="margin-left:4px;"><?php echo htmlspecialchars($facturaorigen); ?></strong></div>
    <?php endif; ?>
    <div class="pill">Tipo relación: <strong style="margin-left:4px;"><?php echo htmlspecialchars($tiporelacion); ?></strong></div>
    <?php if ($tiporelacion2): ?>
      <div class="pill">Tipo relación 2: <strong style="margin-left:4px;"><?php echo htmlspecialchars($tiporelacion2); ?></strong></div>
    <?php endif; ?>
  </div>

  <div class="hint">
    Cierra y actualiza el formulario de la factura para ver la relación reflejada.
  </div>
</div>
</body>
</html>
