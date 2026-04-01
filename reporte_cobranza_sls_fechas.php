<?php
set_time_limit(3000);
error_reporting(0);
ini_set('memory_limit', '512M');

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

$prefijobd = mysqli_real_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

// Buscar clientes
$sql1 = "SELECT ID, RazonSocial FROM ".$prefijobd."clientes ORDER BY RazonSocial";
$res1 = mysqli_query($cnx_cfdi2, $sql1);

// Buscar bandera portal
$esPortal = 0;
$resSQL0 = "SELECT factura_portal FROM {$prefijobd}systemsettings LIMIT 1";
$runSQL0 = mysqli_query($cnx_cfdi2, $resSQL0);
if ($runSQL0 && mysqli_num_rows($runSQL0) > 0) {
    $rowSQL0 = mysqli_fetch_assoc($runSQL0);
    $esPortal = (!empty($rowSQL0['factura_portal'])) ? (int)$rowSQL0['factura_portal'] : 0;
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>Antigüedades saldos de clientes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <style>
        body{
            background:#f5f6fa;
            padding-top:30px;
            padding-bottom:30px;
        }
        .panel-reporte{
            background:#fff;
            border-radius:14px;
            padding:28px;
            box-shadow:0 8px 25px rgba(0,0,0,.08);
        }
        #encabezadoform{
            margin-bottom:25px;
            text-align:center;
        }
        #encabezadoform h1{
            margin-top:0;
            font-size:30px;
            font-weight:700;
        }
        .subtitulo{
            color:#666;
            margin-top:8px;
        }
        .switch-wrap{
            margin-top:8px;
            margin-bottom:18px;
            text-align:left;
            background:#fafafa;
            border:1px solid #e8e8e8;
            border-radius:12px;
            padding:14px 16px;
        }
        .switch-label{
            display:block;
            margin-bottom:10px;
            font-weight:bold;
        }
        .switch-container{
            display:flex;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 56px;
            height: 30px;
            margin: 0;
            vertical-align: middle;
        }
        .switch input {
            display:none;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #d7d7d7;
            transition: .25s;
            border-radius: 999px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .25s;
            border-radius: 50%;
            box-shadow:0 2px 6px rgba(0,0,0,.18);
        }
        input:checked + .slider {
            background-color: #5cb85c;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .switch-text{
            font-weight:600;
            color:#333;
        }
        .modo-box{
            margin-top:8px;
            margin-bottom:20px;
            padding:16px;
            border:1px solid #e8e8e8;
            border-radius:12px;
            background:#fafafa;
        }
        .modo-titulo{
            font-weight:700;
            margin-bottom:10px;
        }
        .radio-inline{
            margin-right:20px;
            font-weight:600;
        }
        .btn{
            min-width:130px;
            border-radius:10px;
            font-weight:700;
            padding:10px 18px;
        }
        .btn-danger{
            background:#d9534f;
            border-color:#d9534f;
        }
        .btn-success{
            background:#28a745;
            border-color:#28a745;
        }
        .nota-ayuda{
            margin-top:10px;
            color:#666;
            font-size:13px;
        }
        @media (max-width: 767px){
            .panel-reporte{
                padding:18px;
            }
            #encabezadoform h1{
                font-size:24px;
            }
            .btn{
                width:100%;
                margin-bottom:10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel-reporte">
                <div id="encabezadoform">
                    <h1>Reporte Antigüedad de Saldos</h1>
                    
                </div>

                <form method="post" action="reporte_cobranza_sls.php" enctype="multipart/form-data" target="_blank" id="frmReporte">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha inicio:</label>
                                <input type="date" name="txtDesde" id="txtDesde" class="form-control" required="required">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha fin:</label>
                                <input type="date" name="txtHasta" id="txtHasta" class="form-control" required="required">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cliente:</label>
                                <select class="form-control" name="cliente" id="cliente">
                                    <option value="0">- Seleccione -</option>
                                    <?php while($row1 = mysqli_fetch_assoc($res1)){ ?>
                                        <option value="<?php echo (int)$row1['ID']; ?>">
                                            <?php echo htmlspecialchars($row1['RazonSocial'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Moneda:</label>
                                <select class="form-control" name="moneda" id="moneda">
                                    <option value="PESOS">PESOS</option>
                                    <option value="DOLARES">DOLARES</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="modo-box">
                                <div class="modo-titulo">Modo del reporte:</div>

                                <label class="radio-inline">
                                    <input type="radio" name="modo_reporte" value="por_vencer" checked>
                                    Días por vencer
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="modo_reporte" value="vencidos">
                                    Días vencidos
                                </label>

                                <div class="nota-ayuda">
                                    <strong>Por vencer:</strong> facturas que aún no vencen. &nbsp; | &nbsp;
                                    <strong>Vencidos:</strong> facturas cuya fecha de vencimiento ya pasó.
                                </div>
                            </div>
                        </div>

                        <?php if ((int)$esPortal === 1) { ?>
                        <div class="col-md-12">
                            <div class="switch-wrap">
                                <label class="switch-label">Facturas portal:</label>

                                <input type="hidden" name="solo_portal" id="solo_portal_hidden" value="0">

                                <div class="switch-container">
                                    <label class="switch">
                                        <input type="checkbox" id="solo_portal_check" value="1">
                                        <span class="slider"></span>
                                    </label>
                                    <span class="switch-text">Buscar solo facturas marcadas para portal</span>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                    </div>

                    <input type="hidden" name="prefijodb" id="prefijodb" value="<?php echo htmlspecialchars($prefijobd, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="esPortal" id="esPortal" value="<?php echo (int)$esPortal; ?>">

                    <div class="text-center" style="margin-top:20px;">
                        <button type="submit" name="btnEnviar" value="PDF" class="btn btn-danger">PDF</button>
                        <button type="submit" name="btnEnviar" value="Excel" class="btn btn-success">Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var form = document.getElementById('frmReporte');
        var chkPortal = document.getElementById('solo_portal_check');
        var hidPortal = document.getElementById('solo_portal_hidden');

        if (form && hidPortal) {
            form.addEventListener('submit', function () {
                if (chkPortal) {
                    hidPortal.value = chkPortal.checked ? '1' : '0';
                } else {
                    hidPortal.value = '0';
                }
            });
        }
    })();
    </script>
</body>
</html>