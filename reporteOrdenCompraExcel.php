<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// ====== PARAMS (GET) ======
$prefijobd = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd = str_replace(array("'", '"', ";"), "", $prefijobd);

$fecha_inicio = isset($_GET['fechai']) ? $_GET['fechai'] : '';
$fecha_fin    = isset($_GET['fechaf']) ? $_GET['fechaf'] : '';

$id_proveedor_filtro = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;

$searchTherm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchThermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTherm);

if (!$prefijobd || !$fecha_inicio || !$fecha_fin) {
    die("Faltan parámetros necesarios.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));

// WHERE proveedor
$cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND c.ProveedorNo_RID = ".$id_proveedor_filtro." ";
}

// WHERE search
$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = "AND (
    c.Estatus       LIKE '%$searchThermSafe%' OR
    c.CompraAbierta  LIKE '%$searchThermSafe%' OR
    c.XFolio        LIKE '%$searchThermSafe%' OR
    c.Documentador  LIKE '%$searchThermSafe%' OR
    p.RazonSocial   LIKE '%$searchThermSafe%' OR
     
     EXISTS (
        SELECT 1
        FROM {$prefijobd}comprassub cs
        LEFT JOIN {$prefijobd}productos p ON cs.ProductoA_RID = p.ID
        WHERE cs.FolioSub_RID = c.ID
          AND (
            cs.Nombre LIKE '%$searchThermSafe%' OR
            IFNULL(p.Codigo,'') LIKE '%%$searchThermSafe'
            )
        )
     )";
}

// ====== DATA ======
$sql = "
SELECT
  c.ID,
  c.XFolio,
  c.Fecha,
  c.Estatus,
  c.CompraAbierta,
  c.Total,
  c.Comentarios,
  c.FolioRecepcion,
  c.Documentador,
  p.RazonSocial
FROM ".$prefijobd."Compras c
LEFT JOIN ".$prefijobd."Proveedores p ON p.ID = c.ProveedorNo_RID
WHERE c.Fecha BETWEEN '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59'
".$cntQuery.$whereSearch."
ORDER BY c.Fecha DESC, p.RazonSocial DESC
";
$res = mysqli_query($cnx_cfdi2, $sql);

// ====== HEADERS EXCEL ======
header("Content-type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "Ordenes_Compra_".date("H-i-s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=".$nombre);
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
  body{ font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
  table{ border-collapse: collapse; width: 100%; }
  th, td{ border:1px solid #999; padding:6px; }
  th{ background:#eee; text-align:center; font-weight:bold; }
  .title{ font-size:14px; font-weight:bold; background:#ddd; }
  .row-parent td{ font-weight:bold; background:#f7f7f7; }
  .row-subhead td{ background:#d9ebff; font-weight:bold; }
  .row-child td{ background:#fbfbfb; }
  .indent{ padding-left:18px; }
  .right{ text-align:right; }
  .left{ text-align:left; }
  .center{ text-align:center; }
</style>

<table>
  <tr>
    <td class="title" colspan="9">Órdenes de compra — Periodo: <?php echo htmlspecialchars($fecha_inicio_f." al ".$fecha_fin_f); ?></td>
  </tr>
  <tr>
    <th>Folio</th>
    <th>Fecha</th>
    <th>Proveedor</th>
    <th>Estatus</th>
    <th>Compra</th>
    <th>Total</th>
    <th>Comentarios</th>
    <th>Folio Recepción</th>
    <th>Documentador</th>
  </tr>

<?php
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {

        $id_compra      = intval($row['ID']);
        $xfolio         = $row['XFolio'];
        $v_fecha        = date("d-m-Y", strtotime($row['Fecha']));
        $proveedor      = $row['RazonSocial'];
        $estatus        = $row['Estatus'];
        $compraAbierta  = $row['CompraAbierta'];
        $total          = $row['Total'];
        $comentarios    = $row['Comentarios'];
        $folioRecepcion = $row['FolioRecepcion'];
        $documentador   = $row['Documentador'];
        ?>
        <tr class="row-parent">
          <td class="center"><?php echo htmlspecialchars($xfolio); ?></td>
          <td class="center"><?php echo htmlspecialchars($v_fecha); ?></td>
          <td class="left"><?php echo htmlspecialchars($proveedor); ?></td>
          <td class="center"><?php echo htmlspecialchars($estatus); ?></td>
          <td class="center"><?php echo htmlspecialchars($compraAbierta); ?></td>
          <td class="right"><?php echo htmlspecialchars($total); ?></td>
          <td class="left"><?php echo htmlspecialchars($comentarios); ?></td>
          <td class="center"><?php echo htmlspecialchars($folioRecepcion); ?></td>
          <td class="center"><?php echo htmlspecialchars($documentador); ?></td>
        </tr>

        <?php
        // ===== SUBPARTIDAS =====
        $sqlSub = "
        SELECT
            cs.Nombre AS Descripcion,
            cs.Cantidad,
            cs.Descuento,
            cs.PrecioUnitario,
            cs.Importe,
            IFNULL(p.Codigo,'') AS ProductoCodigo,
            IFNULL(a.Nombre,'') AS AlmacenNombre
        FROM ".$prefijobd."ComprasSub cs
        LEFT JOIN ".$prefijobd."Productos p ON p.ID = cs.ProductoA_RID
        LEFT JOIN ".$prefijobd."Almacenes a ON a.ID = cs.Almacen_RID
        WHERE cs.FolioSub_RID = ".$id_compra."
        ORDER BY cs.ID ASC
        ";
        $resSub = mysqli_query($cnx_cfdi2, $sqlSub);

        if ($resSub && mysqli_num_rows($resSub) > 0) {
            ?>
            <tr class="row-subhead">
              <td colspan="9">↳ Detalle de compra</td>
            </tr>
            <tr class="row-child">
              <td>&nbsp;</td>
              <td class="left indent"><b>Código</b></td>
              <td class="left"><b>Almacén</b></td>
              <td class="left" colspan="3"><b>Descripción</b></td>
              <td class="right"><b>Cant</b></td>
              <td class="right"><b>P.Unit</b></td>
              <td class="right"><b>Importe</b></td>
            </tr>
            <?php
            while ($rowSub = mysqli_fetch_assoc($resSub)) {
                $productoCodigo    = $rowSub['ProductoCodigo'];
                $almacenNombre     = $rowSub['AlmacenNombre'];
                $descripcion       = $rowSub['Descripcion'];
                $cantidadSub       = $rowSub['Cantidad'];
                $descuentoSub      = $rowSub['Descuento'];
                $precioUnitarioSub = $rowSub['PrecioUnitario'];
                $importeSub        = $rowSub['Importe'];

                $descTxt = '';
                if ((float)$descuentoSub > 0) {
                    $descTxt = ' (Desc: '.$descuentoSub.')';
                }
                ?>
                <tr class="row-child">
                  <td>&nbsp;</td>
                  <td class="left indent"><?php echo htmlspecialchars($productoCodigo); ?></td>
                  <td class="left"><?php echo htmlspecialchars($almacenNombre); ?></td>
                  <td class="left" colspan="3"><?php echo htmlspecialchars($descripcion).htmlspecialchars($descTxt); ?></td>
                  <td class="right"><?php echo htmlspecialchars($cantidadSub); ?></td>
                  <td class="right"><?php echo htmlspecialchars($precioUnitarioSub); ?></td>
                  <td class="right"><?php echo htmlspecialchars($importeSub); ?></td>
                </tr>
                <?php
            }
        }
    }
}
?>
</table>
