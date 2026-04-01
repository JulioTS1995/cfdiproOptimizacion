<?php
ini_set('memory_limit', '1024M');
ini_set('default_charset', 'utf-8');
set_time_limit(1200);
require_once('cnx_cfdi2.php');

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$fecha_inicio   = isset($_GET['fechai'])  ? $_GET['fechai']  : '';
$fecha_fin      = isset($_GET['fechaf'])  ? $_GET['fechaf']  : '';
$usuario_filtro = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$searchTerm     = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$fecha_inicio || !$fecha_fin) {
    die("Faltan fechas.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));


$whereExtra = "";

if (!empty($usuario_filtro)) {
    $whereExtra .= " AND Usuario = ".$usuario_filtro." ";
}

$searchTermSafe = mysqli_real_escape_string($cnx_cfdi2, $searchTerm);
if ($searchTermSafe !== '') {
    $whereExtra .= " AND (
        XFolio          LIKE '%{$searchTermSafe}%'
        OR Usuario         LIKE '%{$searchTermSafe}%'
        OR AccionRealizada LIKE '%{$searchTermSafe}%'
        OR Evidencia       LIKE '%{$searchTermSafe}%'
    ) ";
}


$usuarioNom = 'Todos los usuarios';
if (!empty($usuario_filtro)) {
    $usuarioNom = 'Usuario ID: '.$usuario_filtro;
}


$sql = "
    SELECT 
        XFolio,
        Fecha,
        Usuario,
        AccionRealizada,
        Evidencia
    FROM {$prefijobd}logappmovil
    WHERE Fecha BETWEEN '{$fecha_inicio} 00:00:00' AND '{$fecha_fin} 23:59:59'
    {$whereExtra}
    ORDER BY Fecha, ID
";
$res = mysqli_query($cnx_cfdi2, $sql);


$filename = "Evidencias_Bitacora_{$fecha_inicio_f}_{$fecha_fin_f}.xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");


echo "\xEF\xBB\xBF";

?>
<table border="1">
    <tr>
        <th colspan="5" style="font-weight:bold; font-size:14px;">
            Evidencias de Bitácora de Operador
        </th>
    </tr>
    <tr>
        <td colspan="5">
            Periodo: <?php echo htmlspecialchars($fecha_inicio_f.' al '.$fecha_fin_f); ?>
        </td>
    </tr>
    <tr>
        <td colspan="5">
            Usuario: <?php echo htmlspecialchars($usuarioNom); ?>
        </td>
    </tr>
    <?php if ($searchTerm !== ''): ?>
    <tr>
        <td colspan="5">
            Búsqueda: <?php echo htmlspecialchars($searchTerm); ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr><td colspan="5"></td></tr>

    <tr style="font-weight:bold; background-color:#e9eefb;">
        <th>Folio</th>
        <th>Fecha</th>
        <th>Usuario</th>
        <th>Acción realizada</th>
        <th>Evidencia</th>
    </tr>
    <?php
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $xfolio     = $row['XFolio'];
            $v_fecha_t  = $row['Fecha'];
            $v_fecha    = date("d-m-Y H:i", strtotime($v_fecha_t));
            $usuario    = $row['Usuario'];
            $aRealizada = $row['AccionRealizada'];
            $evidencia  = $row['Evidencia'];
            $textoEvidencia = (!empty($evidencia)) ? $evidencia : 'No hay evidencia anexada';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($xfolio); ?></td>
                <td><?php echo htmlspecialchars($v_fecha); ?></td>
                <td><?php echo htmlspecialchars($usuario); ?></td>
                <td><?php echo htmlspecialchars($aRealizada); ?></td>
                <td><?php echo htmlspecialchars($textoEvidencia); ?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="5">No se encontraron registros con los filtros seleccionados.</td>
        </tr>
        <?php
    }
    ?>
</table>
<?php
exit;
