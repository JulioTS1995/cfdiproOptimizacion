<?php
// tarifasClientes_excel.php — Export a Excel (XLS HTML) compatible con PHP 5.x

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");

// Parámetros / filtros
$prefijobd = isset($_GET["base"]) ? @mysql_escape_string($_GET["base"]) : '';
$Cliente   = isset($_GET['Cliente']) ? intval($_GET['Cliente']) : 0;
$Ruta      = isset($_GET['Ruta'])    ? intval($_GET['Ruta'])    : 0;
$Clase     = isset($_GET['Clase'])   ? intval($_GET['Clase'])   : 0;

$Wextra = "";
if ($Cliente > 0) $Wextra .= " AND T.FolioTarifas_RID = ".$Cliente;
if ($Ruta > 0)    $Wextra .= " AND T.Ruta_RID = ".$Ruta;
if ($Clase > 0)   $Wextra .= " AND T.Clase_RID = ".$Clase;

// Consulta completa (sin paginar)
$sql = "
SELECT 
  T.FolioTarifas_RID, T.Ruta_RID, T.Clase_RID,
  P.conceptopartida, P.tipo, P.preciounitario, P.Importe
FROM ".$prefijobd."clientestarifas T
INNER JOIN ".$prefijobd."clientestarifaspartidas P ON P.FolioSub_RID = T.ID
WHERE 1=1 ".$Wextra."
ORDER BY T.FolioTarifas_RID DESC
";
$res = mysql_query($sql, $cnx_cfdi);

// Helpers
function fetchOne($sql, $field, $cnx){
  $r = mysql_query($sql, $cnx); if($r){ $f = mysql_fetch_assoc($r); return $f ? $f[$field] : ''; }
  return '';
}
$clienteNom = ($Cliente>0) ? fetchOne("SELECT RazonSocial AS n FROM ".$prefijobd."clientes WHERE ID=".$Cliente, 'n', $cnx_cfdi) : 'Todos los clientes';
$rutaNom    = ($Ruta>0)    ? fetchOne("SELECT Ruta AS n FROM ".$prefijobd."rutas WHERE ID=".$Ruta, 'n', $cnx_cfdi) : 'Todas las rutas';
$claseNom   = ($Clase>0)   ? fetchOne("SELECT Clase AS n FROM ".$prefijobd."unidadesclase WHERE ID=".$Clase, 'n', $cnx_cfdi) : 'Todas las clases';

$filename = 'tarifas_clientes_'.date('Y-m-d').'.xls';

// Headers de descarga
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=".$filename);
header("Pragma: no-cache");
header("Expires: 0");

// Estilos simples para Excel (HTML)
echo '<html><head><meta charset="UTF-8">
<style>
  body{font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#111;}
  h2{margin:0 0 8px 0;}
  .meta{margin:0 0 10px 0; color:#555;}
  table{border-collapse:collapse; width:100%;}
  th, td{border:1px solid #888; padding:6px;}
  thead th{background:#dae5ff; font-weight:bold;}
  tfoot td{font-weight:bold;}
</style>
</head><body>';

echo '<h2>Tarifas de Clientes</h2>';
echo '<div class="meta"><strong>Filtros:</strong> '.htmlspecialchars($clienteNom).' · '.htmlspecialchars($rutaNom).' · '.htmlspecialchars($claseNom).'</div>';

echo '<table>';
echo '<thead><tr>
        <th>Cliente</th>
        <th>Ruta</th>
        <th>Tipo de Unidad</th>
        <th>Concepto</th>
        <th>Tipo</th>
        <th>Precio Unitario</th>
        <th>Importe</th>
      </tr></thead><tbody>';

$total = 0;
while($row = mysql_fetch_assoc($res)){
  $id      = $row['FolioTarifas_RID'];
  $rutaId  = $row['Ruta_RID'];
  $claseId = $row['Clase_RID'];

  $cliente = fetchOne("SELECT RazonSocial AS rs FROM ".$prefijobd."clientes WHERE ID='".$id."'", 'rs', $cnx_cfdi);
  $ruta    = fetchOne("SELECT Ruta AS r FROM ".$prefijobd."rutas WHERE ID='".$rutaId."'", 'r', $cnx_cfdi);
  $clase   = fetchOne("SELECT Clase AS c FROM ".$prefijobd."unidadesclase WHERE ID='".$claseId."'", 'c', $cnx_cfdi);

  $concepto = $row['conceptopartida'];
  $tipo     = $row['tipo'];
  $precioU  = $row['preciounitario'];
  $importe  = $row['Importe']; $total += floatval($importe);

  echo '<tr>
          <td>'.htmlspecialchars($cliente).'</td>
          <td>'.htmlspecialchars($ruta).'</td>
          <td>'.htmlspecialchars($clase).'</td>
          <td>'.htmlspecialchars($concepto).'</td>
          <td>'.htmlspecialchars($tipo).'</td>
          <td>'.number_format($precioU,2).'</td>
          <td>'.number_format($importe,2).'</td>
        </tr>';
}
echo '</tbody><tfoot><tr>
        <td colspan="6" style="text-align:right;">Total</td>
        <td>'.number_format($total,2).'</td>
      </tr></tfoot></table>';

echo '</body></html>';
exit;
