<?php
// tarifasClientes_pdf.php — Export PDF con mPDF 6.1 vía Composer

@ini_set('memory_limit', '512M');
@set_time_limit(120);

require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");

// -------- Parámetros / filtros --------
$prefijobd = isset($_GET["base"]) ? @mysql_escape_string($_GET["base"]) : '';
$Cliente   = isset($_GET['Cliente']) ? intval($_GET['Cliente']) : 0;
$Ruta      = isset($_GET['Ruta'])    ? intval($_GET['Ruta'])    : 0;
$Clase     = isset($_GET['Clase'])   ? intval($_GET['Clase'])   : 0;

// WHERE dinámico
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

// Helpers de nombres
function fetchOne($sql, $field, $cnx){
  $r = mysql_query($sql, $cnx);
  if ($r) { $f = mysql_fetch_assoc($r); return $f ? $f[$field] : ''; }
  return '';
}

$clienteNom = ($Cliente>0) ? fetchOne("SELECT RazonSocial AS n FROM ".$prefijobd."clientes WHERE ID=".$Cliente, 'n', $cnx_cfdi) : 'Todos los clientes';
$rutaNom    = ($Ruta>0)    ? fetchOne("SELECT Ruta AS n FROM ".$prefijobd."rutas WHERE ID=".$Ruta, 'n', $cnx_cfdi) : 'Todas las rutas';
$claseNom   = ($Clase>0)   ? fetchOne("SELECT Clase AS n FROM ".$prefijobd."unidadesclase WHERE ID=".$Clase, 'n', $cnx_cfdi) : 'Todas las clases';

$dateStr = date('Y-m-d H:i');

// -------- mPDF 6.1 --------
$mpdf = new \mPDF('utf-8', 'A4', 0, '', 10, 10, 38, 20, 10, 10); // márgenes top/bottom p/ header/footer
$mpdf->SetDisplayMode('fullpage');

// Header / Footer
$mpdf->SetHTMLHeader('
  <div style="font-family:sans-serif; font-size:12px; color:#555; border-bottom:1px solid #ddd; padding-bottom:6px;">
    <table width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td style="font-weight:bold; font-size:14px;">Tarifas de Clientes</td>
        <td style="text-align:right;">Generado: '.$dateStr.'</td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:12px; color:#666;">
          Filtros: '.htmlspecialchars($clienteNom).' · '.htmlspecialchars($rutaNom).' · '.htmlspecialchars($claseNom).'
        </td>
      </tr>
    </table>
  </div>
');

$mpdf->SetHTMLFooter('
  <div style="font-family:sans-serif; font-size:11px; color:#666; border-top:1px solid #ddd; padding-top:6px;">
    <table width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td>TractoSoft</td>
        <td style="text-align:right;">Página {PAGENO} de {nb}</td>
      </tr>
    </table>
  </div>
');

// CSS simple (amigable a PDF)
$css = '
*{ font-family: DejaVu Sans, sans-serif; }
.h1{ font-size:18px; font-weight:bold; margin:0 0 8px 0; }
.meta{ font-size:12px; color:#444; margin-bottom:10px; }

.table{ width:100%; border-collapse:collapse; font-size:12px; }
.table thead th{
  background:#e9eefb; color:#333; font-weight:bold; text-align:center;
  padding:8px; border-bottom:1px solid #ccc;
}
.table tbody td{
  background:#fff; color:#111; padding:8px; border-bottom:1px solid #eee;
}
.table tbody tr:nth-child(even) td{ background:#f8fafe; }
.right{ text-align:right; }
.left{ text-align:left; }
';

$mpdf->WriteHTML($css, 1);

// HTML del reporte
ob_start();
?>
<table class="table">
  <thead>
    <tr>
      <th class="left">Cliente</th>
      <th class="left">Ruta</th>
      <th class="left">Tipo de Unidad</th>
      <th class="left">Concepto</th>
      <th class="left">Tipo</th>
      <th class="right">Precio Unitario</th>
      <th class="right">Importe</th>
    </tr>
  </thead>
  <tbody>
  <?php
    $totalImporte = 0;
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
      $importe  = $row['Importe'];
      $totalImporte += floatval($importe);
      echo '<tr>'.
           '<td class="left">'.htmlspecialchars($cliente).'</td>'.
           '<td class="left">'.htmlspecialchars($ruta).'</td>'.
           '<td class="left">'.htmlspecialchars($clase).'</td>'.
           '<td class="left">'.htmlspecialchars($concepto).'</td>'.
           '<td class="left">'.htmlspecialchars($tipo).'</td>'.
           '<td class="right">'.number_format($precioU,2).'</td>'.
           '<td class="right">'.number_format($importe,2).'</td>'.
           '</tr>';
    }
  ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="6" class="right" style="font-weight:bold;">Total</td>
      <td class="right" style="font-weight:bold;"><?php echo number_format($totalImporte, 2); ?></td>
    </tr>
  </tfoot>
</table>
<?php
$html = ob_get_clean();

$mpdf->WriteHTML($html, 2);
$filename = 'tarifas_clientes_'.date('Y-m-d').'.pdf';
$mpdf->Output($filename, 'D'); // 'I' muestra en navegador; usa 'D' para descargar directamente
exit;
