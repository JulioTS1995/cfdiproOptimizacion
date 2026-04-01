<?php
error_reporting(0);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

function normaliza_prefijo($raw){
  $raw = str_replace(array("'", '"', ";"), "", $raw);
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

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$prefijobd = isset($_GET['prefijodb']) ? normaliza_prefijo($_GET['prefijodb']) : '';
$sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;

if ($q === '' || strlen($q) < 2 || $prefijobd === '') exit;

$facturaTable = $prefijobd.'factura';
$oficinasTable = $prefijobd.'Oficinas';

if (!table_exists($cnx_cfdi2, $database_cfdi, $facturaTable)) exit;

$whereSucursal = "";
if ($sucursal > 0
    && table_exists($cnx_cfdi2, $database_cfdi, $oficinasTable)
    && column_exists($cnx_cfdi2, $database_cfdi, $oficinasTable, "Sucursal_RID")
    && column_exists($cnx_cfdi2, $database_cfdi, $facturaTable, "Oficina_RID")
) {
  $whereSucursal = " AND Oficina_RID IN (
                      SELECT ID FROM {$oficinasTable} WHERE Sucursal_RID = ".intval($sucursal)."
                    ) ";
}

$qLike = '%'.$q.'%';

// Sugerencias: por folio o uuid, solo canceladas en sistema
$sql = "SELECT ID, XFolio, cfdiuuid
        FROM {$facturaTable}
        WHERE IFNULL(cCanceladoT,'') <> ''
          {$whereSucursal}
          AND (XFolio LIKE ? OR cfdiuuid LIKE ?)
        ORDER BY XFolio DESC
        LIMIT 30";

$stmt = mysqli_prepare($cnx_cfdi2, $sql);
mysqli_stmt_bind_param($stmt, "ss", $qLike, $qLike);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($res) {
  while ($row = mysqli_fetch_assoc($res)) {
    $id = (int)$row['ID'];
    $folio = $row['XFolio'];
    $uuid = $row['cfdiuuid'];

    echo '<div class="sugerencia" data-id="'.intval($id).'" data-folio="'.h($folio).'">'
       . '<b>'.h($folio).'</b>'
       . ' <span style="opacity:.75;">· '.h(substr($uuid,0,12)).'…</span>'
       . '</div>';
  }
}
mysqli_stmt_close($stmt);
