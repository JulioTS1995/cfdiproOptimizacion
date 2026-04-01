<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000);

require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi2.php');

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);
$prefijo   = rtrim($prefijobd, "_");

$fecha_inicio = isset($_GET['fechai'])  ? $_GET['fechai']  : '';
$fecha_fin    = isset($_GET['fechaf'])  ? $_GET['fechaf']  : '';
$usuario_filtro = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$searchTerm     = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$fecha_inicio || !$fecha_fin) {
    die("Faltan fechas.");
}

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f    = date("d-m-Y", strtotime($fecha_fin));


// SYSTEMSETTINGS (logo / razón social)

$RazonSocial = '';
$rutalogo    = '../cfdipro/imagenes/'.$prefijo.'.jpg';

$resSQL01 = "SELECT RazonSocial FROM {$prefijobd}systemsettings LIMIT 1";
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
if ($rowSQL01 = mysqli_fetch_assoc($runSQL01)) {
    $RazonSocial = $rowSQL01['RazonSocial'];
}

// COLORES (parametro 921 y 922)

$color_fondo = '#a1a1a3';
$color_letra = '#000000';

// fondo
$parametro_bgc = 921;
$resSQL921 = "SELECT VCHAR FROM {$prefijobd}parametro WHERE id2 = {$parametro_bgc}";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
if ($row921 = mysqli_fetch_assoc($runSQL921)) {
    if (!empty($row921['VCHAR'])) {
        $color_fondo = $row921['VCHAR'];
    }
}

// letra
$parametro_letra_color = 922;
$resSQL922 = "SELECT VCHAR FROM {$prefijobd}parametro WHERE id2 = {$parametro_letra_color}";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
if ($row922 = mysqli_fetch_assoc($runSQL922)) {
    if (!empty($row922['VCHAR'])) {
        $color_letra = $row922['VCHAR'];
    }
}

$estilo_fondo = 'background-color:'.$color_fondo.'; color:'.$color_letra.';';


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


ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evidencias Bitácora Operador</title>
    <style>
        @page {
            margin: 140px 25px 60px 25px;
        }
        body {
            font-family: helvetica, sans-serif;
            font-size: 11px;
        }
        header, footer { font-family: helvetica, sans-serif; }

        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 100px;
        }
        footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
        }

        main {
            position: relative;
            z-index: 1;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #999;
            padding: 4px 5px;
        }
        thead th {
            font-size: 11px;
            text-align: center;
        }
        tbody td {
            font-size: 10px;
        }
        tbody tr:nth-child(even) td {
            background: #f5f5f5;
        }
        .right { text-align:right; }
        .left  { text-align:left;  }
        .center{ text-align:center; }

        .titulo-seccion {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<htmlpageheader name="myHeader">
    <div style="margin-top:-10px;">
        <table width="100%" border="0" style="border-collapse:collapse;">
            <tr>
                <!-- Logo -->
                <td style="width:25%; text-align:left;">
                    <img src="<?php echo $rutalogo; ?>" width="100" alt="">
                </td>

                <!-- Empresa + título -->
                <td style="width:50%; text-align:center; font-size:11px;">
                    <strong style="font-size:14px;"><?php echo strtoupper($RazonSocial); ?></strong><br>
                    <span style="font-size:12px;">Evidencias de Bitácora de Operador</span><br>
                    <span style="font-size:11px;">Periodo: <?php echo $fecha_inicio_f.' al '.$fecha_fin_f; ?></span><br>
                    <span style="font-size:10px;">Usuario: <?php echo htmlspecialchars($usuarioNom); ?></span>
                </td>

                <!-- Cartela derecha -->
                <td style="width:25%;">
                    <table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #000;">
                        <tr>
                            <td style="text-align:center; font-size:11px; padding:4px; <?php echo $estilo_fondo; ?>">
                                <b>Reporte</b><br>Evidencias
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center; font-size:9px; padding:4px;">
                                Generado el <?php echo date('d-m-Y H:i'); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>

<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />

<htmlpagefooter name="myFooter">
    <div>
        <table width="100%" border="0" style="border-collapse:collapse; font-size:9px;">
            <tr>
                <td style="text-align:left; color:#555;">
                    Evidencias Bitácora Operador
                </td>
                <td style="text-align:right; color:#555;">
                    Página {PAGENO} de {nb}
                </td>
            </tr>
        </table>
    </div>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" />

<main>
    <div style="margin-bottom:8px;">
        <span class="titulo-seccion">Listado de evidencias</span>
    </div>

    <table>
        <thead>
            <tr style="<?php echo $estilo_fondo; ?>">
                <th style="width:10%;">Folio</th>
                <th style="width:15%;">Fecha</th>
                <th style="width:15%;">Usuario</th>
                <th style="width:30%;">Acción realizada</th>
                <th style="width:30%;">Evidencia</th>
            </tr>
        </thead>
        <tbody>
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
                <td class="center"><?php echo htmlspecialchars($xfolio); ?></td>
                <td class="center"><?php echo htmlspecialchars($v_fecha); ?></td>
                <td class="left"><?php echo htmlspecialchars($usuario); ?></td>
                <td class="left"><?php echo htmlspecialchars($aRealizada); ?></td>
                <td class="left"><?php echo htmlspecialchars($textoEvidencia); ?></td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="5" class="center">No se encontraron registros con los filtros seleccionados.</td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</main>

</body>
</html>
<?php

$html = ob_get_clean();


$mpdf = new mPDF('utf-8', 'letter');
$mpdf->SetDisplayMode('fullpage');
$mpdf->WriteHTML($html);

$nombre_pdf = "Evidencias_Bitacora_{$fecha_inicio_f}_{$fecha_fin_f}.pdf";
// Descarga directa:
$mpdf->Output($nombre_pdf, 'D');
// ver en navegador cambia aquí:
// $mpdf->Output($nombre_pdf, 'I');
exit;
