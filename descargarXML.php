<?php
set_time_limit(300);
ini_set('memory_limit', '256M');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

function salirError($mensaje, $codigoHttp = 400)
{
    if (!headers_sent()) {
        http_response_code($codigoHttp);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    exit($mensaje);
}

function limpiarPrefijo($prefijo)
{
    $prefijo = trim($prefijo);
    $prefijo = preg_replace('/[^A-Za-z0-9_]/', '', $prefijo);

    if ($prefijo === '') {
        return '';
    }

    if (substr($prefijo, -1) !== '_') {
        $prefijo .= '_';
    }

    return $prefijo;
}

function limpiarId($id)
{
    $id = trim($id);
    $id = preg_replace('/[^A-Za-z0-9\-_]/', '', $id);
    return $id;
}

function enviarArchivoDescarga($rutaFisica, $nombreDescarga)
{
    if (!file_exists($rutaFisica) || !is_file($rutaFisica)) {
        salirError('404 - El XML no existe.', 404);
    }

    if (!is_readable($rutaFisica)) {
        salirError('403 - El XML no se puede leer.', 403);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . basename($nombreDescarga) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: private, no-transform, no-store, must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($rutaFisica));

    readfile($rutaFisica);
    exit;
}

if (!isset($_GET['prefijodb']) || trim($_GET['prefijodb']) === '') {
    salirError('Falta el parametro prefijodb.', 400);
}

if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    salirError('Falta el parametro id.', 400);
}

if (!isset($_GET['tipo']) || trim($_GET['tipo']) === '') {
    salirError('Falta el parametro tipo.', 400);
}

$prefijobd = limpiarPrefijo($_GET['prefijodb']);
$id        = limpiarId($_GET['id']);
$tipo      = (int) $_GET['tipo'];

if ($prefijobd === '') {
    salirError('El prefijo de BD no es valido.', 400);
}

if ($id === '') {
    salirError('El id no es valido.', 400);
}

if (!in_array($tipo, array(1, 2, 3, 4), true)) {
    salirError('El tipo no es valido.', 400);
}

/*

 1 = remisiones
 2 = facturas
 3 = complementos de pago
 4 = notas de credito

*/


$sqlSettings = "SELECT xmldir FROM {$prefijobd}systemsettings LIMIT 1";
$resSettings = mysqli_query($cnx_cfdi2, $sqlSettings);

if (!$resSettings) {
    salirError('Error al consultar config de cliente: ' . mysqli_error($cnx_cfdi2), 500);
}

if (mysqli_num_rows($resSettings) <= 0) {
    mysqli_free_result($resSettings);
    salirError('No se encontro configuracion del cliente.', 404);
}

$rowSettings = mysqli_fetch_assoc($resSettings);
mysqli_free_result($resSettings);

$xml_dir = isset($rowSettings['xmldir']) ? trim($rowSettings['xmldir']) : '';

if ($xml_dir === '') {
    salirError('El campo xmldir esta vacio en configuracion del cliente.', 500);
}

/* Normalizar xmldir */
$xml_dir = str_replace('\\', '/', $xml_dir);
$xml_dir = trim($xml_dir);

if ($xml_dir === '') {
    salirError('El xmldir no es valido.', 500);
}


if (substr($xml_dir, 0, 1) !== '/') {
    $xml_dir = '/' . $xml_dir;
}


$xml_dir = rtrim($xml_dir, '/');


$tabla = '';

switch ($tipo) {
    case 1:
        $tabla = $prefijobd . 'remisiones';
        break;

    case 2:
        $tabla = $prefijobd . 'factura';
        break;

    case 3:
        $tabla = $prefijobd . 'abonos';
        break;

    case 4:
        $tabla = $prefijobd . 'abonos';
        break;
}

/* =========================
   CONSULTAR SERIE Y FOLIO
========================= */

$sql = "
    SELECT cfdserie, cfdfolio, XFolio
    FROM {$tabla}
    WHERE ID = ?
    LIMIT 1
";

$stmt = mysqli_prepare($cnx_cfdi2, $sql);

if (!$stmt) {
    salirError('Error al preparar la consulta SQL: ' . mysqli_error($cnx_cfdi2), 500);
}

mysqli_stmt_bind_param($stmt, 's', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    mysqli_stmt_close($stmt);
    salirError('Error al ejecutar la consulta SQL.', 500);
}

if (mysqli_num_rows($result) <= 0) {
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    salirError('No se encontro el documento solicitado.', 404);
}

$row = mysqli_fetch_assoc($result);

mysqli_free_result($result);
mysqli_stmt_close($stmt);

$cfdserie = isset($row['cfdserie']) ? trim($row['cfdserie']) : '';
$cfdfolio = isset($row['cfdfolio']) ? trim($row['cfdfolio']) : '';
$XFolio   = isset($row['XFolio']) ? trim($row['XFolio']) : '';


if ($cfdfolio === '') {
    salirError('El documento no tiene cfdfolio.', 500);
}

/*
|--------------------------------------------------------------------------
| ARMAR RUTA SEGUN TIPO
|--------------------------------------------------------------------------
| tipo 1 remision:
| C:/xampp/htdocs{$xml_dir}/SERIE-FOLIO.xml
|
| tipo 2 factura:
| C:/xampp/htdocs{$xml_dir}/SERIE-FOLIO.xml
|
| tipo 3 complemento de pago:
| C:/xampp/htdocs{$xml_dir}/PSERIEFOLIO=SERIE-FOLIO.xml
|
| tipo 4 nota de credito:
| C:/xampp/htdocs{$xml_dir}/NCSERIEFOLIO=SERIE-FOLIO.xml
|--------------------------------------------------------------------------
*/

$basePath = 'C:/xampp/htdocs' . $xml_dir . '/';
$rutaXml = '';
$nombreDescarga = '';

switch ($tipo) {
    case 1: // Remision
    case 2: // Factura
        $nombreDescarga = $cfdserie . '-' . $cfdfolio . '.xml';
        $rutaXml = $basePath . $nombreDescarga;
        break;

    case 3: // Complemento de pago
        $nombreDescarga = 'P' . $XFolio. '=' . $cfdserie . '-' . $cfdfolio . '.xml';
        $rutaXml = $basePath . $nombreDescarga;
        break;

    case 4: // Nota de credito
        $nombreDescarga = 'NC' . $XFolio. '=' . $cfdserie . '-' . $cfdfolio . '.xml';
        $rutaXml = $basePath . $nombreDescarga;
        break;
}

$rutaXml = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $rutaXml);

enviarArchivoDescarga($rutaXml, $nombreDescarga);
?>