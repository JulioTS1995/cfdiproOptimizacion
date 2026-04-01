<?php
@ini_set('memory_limit', '512M');
@set_time_limit(180);
error_reporting(0);

require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// Parámetros
$prefijobd    = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd    = str_replace(array("'", '"', ";"), "", $prefijobd);
$id_proveedor = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;
$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

$cntQuery = "";
if ($id_proveedor != 0) {
    $cntQuery = " AND C.ProveedorNo_RID = ".$id_proveedor." ";
}

// Opcional: nombre del proveedor para encabezado
$proveedorNom = 'Todos los proveedores';
if ($id_proveedor != 0) {
    $sqlProv = "SELECT RazonSocial FROM ".$prefijobd."Proveedores WHERE ID=".$id_proveedor;
    $resProv = mysqli_query($cnx_cfdi2, $sqlProv);
    if ($resProv && $rowP = mysqli_fetch_assoc($resProv)) {
        $proveedorNom = $rowP['RazonSocial'];
    }
}

// Query completa
$sql = "SELECT 
  C.ID,
  C.Fecha,
  C.Factura,
  C.Comentarios,
  C.XFolio,
  C.ProveedorNo_RID,
  CS.CeCo,
  CS.Cuenta,
  CS.SubCuenta,
  CS.Cantidad,
  CS.Importe,
  CS.Sucursal,
  CS.ClasificacionCC,
  CS.UnidadSub_RID,
  P.RazonSocial AS ProveedorNombre,
  U.Unidad AS UnidadNombre
FROM ".$prefijobd."Compras C
INNER JOIN ".$prefijobd."ComprasSub CS ON CS.FolioSub_RID = C.ID
LEFT JOIN ".$prefijobd."Proveedores P ON P.ID = C.ProveedorNo_RID
LEFT JOIN ".$prefijobd."Unidades U ON U.ID = CS.UnidadSub_RID
WHERE DATE(C.Fecha) BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery."
ORDER BY C.Fecha, C.ID
";
$res = mysqli_query($cnx_cfdi2, $sql);

// Crear mPDF (versión 6.x)
$mpdf = new \mPDF('utf-8', 'A4', 0, '', 10, 10, 35, 20, 5, 5);
$mpdf->SetDisplayMode('fullpage');

// Header / Footer
$mpdf->SetHTMLHeader('
  <div style="font-family:sans-serif; font-size:11px; color:#555; border-bottom:1px solid #ddd; padding-bottom:4px;">
    <table width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td style="font-weight:bold; font-size:13px;">Centros de Costos</td>
        <td style="text-align:right;">Periodo: '.$fecha_inicio_f.' al '.$fecha_fin_f.'</td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:11px; color:#666;">
          Proveedor: '.htmlspecialchars($proveedorNom).'
        </td>
      </tr>
    </table>
  </div>
');
$mpdf->SetHTMLFooter('
  <div style="font-family:sans-serif; font-size:10px; color:#666; border-top:1px solid #ddd; padding-top:4px;">
    <table width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td>TractoSoft</td>
        <td style="text-align:right;">Página {PAGENO} de {nb}</td>
      </tr>
    </table>
  </div>
');

// CSS básico
$css = '
*{ font-family: DejaVu Sans, sans-serif; }
table{ width:100%; border-collapse:collapse; font-size:9pt; }
thead th{
  background:#e9eefb;
  color:#333;
  font-weight:bold;
  text-align:center;
  padding:4px;
  border-bottom:1px solid #ccc;
}
tbody td{
  padding:4px;
  border-bottom:1px solid #eee;
}
tbody tr:nth-child(even) td{ background:#f7f9ff; }
.right{text-align:right;}
.left{text-align:left;}
';
$mpdf->WriteHTML($css,1);

// HTML del reporte
ob_start();
?>
<table>
  <thead>
    <tr>
      <th>XFolio</th>
      <th>Fecha</th>
      <th>Mes</th>
      <th>Año</th>
      <th>CeCo</th>
      <th>Sucursal</th>
      <th>Unidad de Negocio</th>
      <th>Departamento</th>
      <th>Cuenta</th>
      <th>Sub cuenta</th>
      <th>Proveedor</th>
      <th>No. Factura</th>
      <th>Cantidad</th>
      <th>Monto</th>
      <th>Observaciones</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $total_monto = 0;
  if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
      $xfolio   = $row['XFolio'];
      $v_fecha_t= $row['Fecha'];
      $v_fecha  = date("d-m-Y", strtotime($v_fecha_t));
      $obj_date = strtotime($v_fecha_t);
      $mes      = date("n", $obj_date);
      $anio     = date("Y", $obj_date);

      $CeCo        = $row['CeCo'];
      $cuenta      = $row['Cuenta'];
      $subCuenta   = $row['SubCuenta'];
      $cantidad    = $row['Cantidad'];
      $monto       = $row['Importe'];
      $total_monto+= floatval($monto);
      $departamento= $row['ClasificacionCC'];
      $sucursal    = $row['Sucursal'];
      $unidad      = $row['UnidadNombre'];
      $proveedor   = $row['ProveedorNombre'];
      $factura     = $row['Factura'];
      $observ      = $row['Comentarios'];
  ?>
    <tr>
      <td><?php echo htmlspecialchars($xfolio); ?></td>
      <td><?php echo htmlspecialchars($v_fecha); ?></td>
      <td><?php echo htmlspecialchars($mes); ?></td>
      <td><?php echo htmlspecialchars($anio); ?></td>
      <td><?php echo htmlspecialchars($CeCo); ?></td>
      <td><?php echo htmlspecialchars($sucursal); ?></td>
      <td><?php echo htmlspecialchars($unidad); ?></td>
      <td><?php echo htmlspecialchars($departamento); ?></td>
      <td><?php echo htmlspecialchars($cuenta); ?></td>
      <td><?php echo htmlspecialchars($subCuenta); ?></td>
      <td><?php echo htmlspecialchars($proveedor); ?></td>
      <td><?php echo htmlspecialchars($factura); ?></td>
      <td class="right"><?php echo htmlspecialchars($cantidad); ?></td>
      <td class="right"><?php echo number_format($monto,2); ?></td>
      <td><?php echo htmlspecialchars($observ); ?></td>
    </tr>
  <?php
    }
  }
  ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="13" class="right"><strong>Total</strong></td>
      <td class="right"><strong><?php echo number_format($total_monto,2); ?></strong></td>
      <td></td>
    </tr>
  </tfoot>
</table>
<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html,2);

$filename = 'Centros_Costos_'.date('Y-m-d_H-i-s').'.pdf';
$mpdf->Output($filename,'I');
exit;
