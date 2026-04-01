<?php
set_time_limit(3000);
error_reporting(0);

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

// ====== PARAMS (GET) ======
$prefijobd = isset($_GET['prefijo']) ? $_GET['prefijo'] : '';
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
/* $cntQuery = "";
if ($id_proveedor_filtro != 0) {
    $cntQuery = " AND c.ProveedorNo_RID = ".$id_proveedor_filtro." ";
}
 */
// WHERE search
$whereSearch = "";
if ($searchThermSafe !== ''){
  $whereSearch = " AND (
    rem.Creado        LIKE '%$searchThermSafe%' OR
    un.Unidad         LIKE '%$searchThermSafe%' OR
    rem.XFolio        LIKE '%$searchThermSafe%' OR
    ru.Ruta           LIKE '%$searchThermSafe%' OR
    op.Operador       LIKE '%$searchThermSafe%' OR
    EXISTS (
        SELECT 1
        FROM {$prefijobd}remisiones_ref rmrf
        LEFT JOIN {$prefijobd}evidencias rmev ON rmrf.RID = rmev.ID
        WHERE rmrf.ID = rem.ID
          AND (
            rmev.Cantidad   LIKE '%$searchThermSafe%' OR
            rmev.Comentario LIKE '%$searchThermSafe%'
          )
    )
  ) ";
}

// ====== DATA ======
$sql = "SELECT 
  rem.ID,
  rem.XFolio,
  rem.Creado,
  un.Unidad,
  op.Operador,
  ru.Ruta
FROM {$prefijobd}remisiones rem 
LEFT JOIN {$prefijobd}unidades un ON un.ID = rem.Unidad_RID
LEFT JOIN {$prefijobd}operadores op ON op.ID = rem.Operador_RID
LEFT JOIN {$prefijobd}rutas ru ON ru.ID = Ruta_RID
WHERE rem.Creado BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59' {$whereSearch} ORDER BY rem.Creado desc
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
    <td class="title" colspan="9">Incidencias — Periodo: <?php echo htmlspecialchars($fecha_inicio_f." al ".$fecha_fin_f); ?></td>
  </tr>
  <tr>
              <th>Folio</th>
              <th>Creado</th>
              <th>Economico</th>
              <th>Operador</th>
              <th>Ruta</th>
  </tr>

<?php
         if ($res) {
              while ($row = mysqli_fetch_assoc($res)) {
                $xfolio         = $row['XFolio'];
                $v_fecha_t      = $row['Creado'];
                $rem_id         = $row['ID'];
                $v_fecha        = date("d-m-Y", strtotime($v_fecha_t));
                $unidad         = $row['Unidad'];
                $operador       = $row['Operador'];
                $ruta           = $row['Ruta'];

                ?>
              <tr class="row-parent">
                    <td><?php echo htmlspecialchars($xfolio); ?></td>
                    <td><?php echo htmlspecialchars($v_fecha); ?></td>
                    <td style="text-align:left;"><?php echo htmlspecialchars($unidad); ?></td>
                    <td><?php echo htmlspecialchars($operador); ?></td>
                    <td><?php echo htmlspecialchars($ruta); ?></td>
                  
                  </tr>

        <?php
        // ===== SUBPARTIDAS =====
              $sqlSub = "SELECT
                          rmev.Comentario,
                          rmev.Fecha,
                          rmev.Cantidad
                      FROM {$prefijobd}remisiones_ref as rmrf
                      LEFT JOIN {$prefijobd}evidencias rmev ON rmev.ID = rmrf.RID
                      WHERE rmrf.ID = ".intval($rem_id)."
                      ORDER BY rmev.ID ASC
                      ";
        $resSub = mysqli_query($cnx_cfdi2, $sqlSub);

        if ($resSub && mysqli_num_rows($resSub) > 0) {
            ?>
            <tr class="row-subhead">
              <td colspan="5">↳ Detalle de Incidencias</td>
            </tr>
            <tr class="row-child">
              <td>&nbsp;</td>
              <td class="left indent"><b>Fecha</b></td>
              <td class="left"><b>Cantidad</b></td>
              <td class="left" colspan="2"><b>Comentario</b></td>
            </tr>
            <?php
            while ($rowSub = mysqli_fetch_assoc($resSub)) {
               $comentario    = $rowSub['Comentario'];
                      $fechaInc     = $rowSub['Fecha'];
                      $cantidadSub       = $rowSub['Cantidad'];
                ?>
                <tr class="row-child">
                  <td>&nbsp;</td>
                  <td class="left indent"><?php echo htmlspecialchars($fechaInc); ?></td>
                  <td class="left"><?php echo htmlspecialchars($cantidadSub); ?></td>
                  <td class="left" colspan="2"><?php echo htmlspecialchars($comentario); ?></td>
                </tr>
                <?php
            }
        }
    }
}
?>
</table>
