<?php 
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

// Validar prefijo
$prefijobd = isset($_GET['prefijobd']) ? $_GET['prefijobd'] : '';
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$subpagina_actual = isset($_GET['subpagina']) ? intval($_GET['subpagina']) : 1;

$clientes_por_pagina = 1;
$inicio = ($pagina_actual - 1) * $clientes_por_pagina;

$fecha_hoy = date('Y-m-d');
$fecha2 = date("d-m-Y", strtotime($fecha_hoy));


//Ajuste portal centros D
$esPortal = '0';
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){
	if (!isset($rowSQL0['factura_portal'])) {
        # code...
        $esPortal = '0';

    }else {
        # code...
        $esPortal = $rowSQL0['factura_portal'];
    }
	
}
$facturaPortal = ($esPortal != '0' || $esPortal == '1')  ? true : false; 
$ctnPortal = '';
$ctnPortal = ($facturaPortal) ? ' AND  EnPortal = "1"' : '' ;
$ctnPortalTotal = ($facturaPortal) ? ' AND  F.EnPortal = "1"' : '' ;



// Total clientes
$sql_count = "SELECT COUNT(DISTINCT C.ID) AS total 
              FROM {$prefijobd}factura F 
              JOIN {$prefijobd}clientes C ON F.CargoAFactura_RID = C.ID
              WHERE F.CobranzaSaldo > 0 AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') 
                AND F.cfdfchhra > '1990-01-01 00:00:00'";
$total_clientes = mysql_fetch_assoc(mysql_query($sql_count))['total'];
$total_paginas = ceil($total_clientes / $clientes_por_pagina);

// Obtener cliente actual
$sql_cliente = "SELECT DISTINCT C.ID AS id_cliente, C.RazonSocial 
                FROM {$prefijobd}factura F 
                JOIN {$prefijobd}clientes C ON F.CargoAFactura_RID = C.ID
                WHERE F.CobranzaSaldo > 0 AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') 
                  AND F.cfdfchhra > '1990-01-01 00:00:00'
                ORDER BY C.RazonSocial
                LIMIT $inicio, $clientes_por_pagina";
$res_cliente = mysql_query($sql_cliente);
$row_cliente = mysql_fetch_assoc($res_cliente);
$id_cliente = $row_cliente['id_cliente'];
$razon_social = $row_cliente['RazonSocial'];

// Subpaginación de facturas
$facturas_por_pagina = 8;
$inicio_sub = ($subpagina_actual - 1) * $facturas_por_pagina;

$sql_count_facturas = "SELECT COUNT(*) AS total 
                       FROM {$prefijobd}factura 
                       WHERE CargoAFactura_RID = $id_cliente 
                         AND CobranzaSaldo > 0 AND (cCanceladoT IS NULL OR cCanceladoT = '') 
                         AND cfdfchhra > '1990-01-01 00:00:00'{$ctnPortal}";
$total_facturas = mysql_fetch_assoc(mysql_query($sql_count_facturas))['total'];
$total_subpaginas = ceil($total_facturas / $facturas_por_pagina);

// Obtener facturas del cliente actual con paginación
$sql_facturas = "SELECT * FROM {$prefijobd}factura 
                 WHERE CargoAFactura_RID = $id_cliente 
                   AND CobranzaSaldo > 0 AND (cCanceladoT IS NULL OR cCanceladoT = '') 
                   AND cfdfchhra > '1990-01-01 00:00:00'{$ctnPortal}
                 ORDER BY XFolio
                 LIMIT $inicio_sub, $facturas_por_pagina";
                 //die($sql_facturas);
$res_facturas = mysql_query($sql_facturas);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .container { margin-top: 20px; }
        .table th, .table td { font-size: 12px; }
        .pagination { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
<div class="container-fluid" style="margin-bottom: 20px;">
<div class="row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
    <div style="min-width: 250px;">
        <h2 style="margin: 0;"><b>Estado de Cuenta</b></h2>
        <h4 style="margin: 0;">Fecha: <?php echo $fecha2; ?></h4>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap; min-width: 300px;">
        <a href="Reporte_edo_cuenta_clientes_mail.php?prefijobd=<?php echo $prefijobd; ?>">
            <button class="btn btn-info btn-lg">
                <i class="fas fa-envelope"></i> Enviar Mail
            </button>
        </a>
        <a href="Reporte_edo_cuenta_clientes_excel.php?prefijobd=<?php echo $prefijobd; ?>">
            <button class="btn btn-success btn-lg">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </button>
        </a>
    </div>
</div>

    </div>

   

    <h3><b><?php echo strtoupper($razon_social); ?></b></h3>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Fecha Timbrado</th>
                <th>Folio</th>
                <th>Moneda</th>
                <th>Ticket</th>
                <th>Estatus</th>
                <th>Fecha Revisión</th>
                <th>Fecha Vencimiento</th>
                <th>Saldo Vencido</th>
                <th>Saldo Factura</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $total_saldo = 0;
        $total_vencido = 0;
        while ($row = mysql_fetch_assoc($res_facturas)) {
            $fecha_timbrado = date("d-m-Y H:i:s", strtotime($row['cfdfchhra']));
            $folio = $row['XFolio'];
            $moneda = $row['Moneda'];
            $ticket = $row['Ticket'];
            $vence = $row['Vence'];
            $fecha_vence = ($vence && $vence > '1990-01-01') ? date("d-m-Y", strtotime($vence)) : '';
            $saldo = floatval($row['CobranzaSaldo']);
            $total = floatval($row['zTotal']);

            $estatus = '';
            $dias_atraso = (strtotime($fecha_hoy) - strtotime($vence)) / (60*60*24);
            if ($vence < $fecha_hoy) {
                $estatus = 'Vencido';
                $total_vencido += $saldo;
            } elseif ($dias_atraso <= 7) {
                $estatus = 'Próximo a Vencer';
            } else {
                $estatus = 'En Tiempo';
            }
            $fechaRevision = date_format(date_create($row['FechaRevision']), 'd-m-Y H:i:s');

            
        ?>
            <tr>
                <td><?php echo $fecha_timbrado; ?></td>
                <td><?php echo $folio; ?></td>
                <td><?php echo $moneda; ?></td>
                <td><?php echo $ticket; ?></td>
                <td><?php echo $estatus; ?></td>
                <td><?php echo $fechaRevision; ?></td>
                <td><?php echo $fecha_vence; ?></td>
                <td><?php echo number_format(($estatus == 'Vencido' ? $saldo : 0), 2); ?></td>
                <td><?php echo number_format($saldo, 2); ?></td>
            </tr>
        <?php } 
        
        $sql_total_cliente = "
                    SELECT 
                        SUM(F.CobranzaSaldo) AS total_saldo,
                        SUM(CASE 
                            WHEN F.Vence < CURDATE() THEN F.CobranzaSaldo 
                            ELSE 0 
                        END) AS total_vencido
                    FROM {$prefijobd}factura F
                    WHERE F.CargoAFactura_RID = $id_cliente
                    AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '')
                    AND F.cfdfchhra > '1990-01-01 00:00:00' {$ctnPortalTotal}
                ";
                $res_total_cliente = mysql_query($sql_total_cliente);
                $totales = mysql_fetch_assoc($res_total_cliente);

                $total_saldo = floatval($totales['total_saldo']);
                $total_vencido = floatval($totales['total_vencido']);?>
            <tr>
                <td colspan="6" class="text-right"><strong>TOTALES:</strong></td>
                <td><strong><?php echo number_format($total_vencido, 2); ?></strong></td>
                <td><strong><?php echo number_format($total_saldo, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
	<?php if ($total_subpaginas > 1): ?>
    <h4 style="margin-top: 5px;"><b>Paginación de Facturas para el Cliente</b></h4>
    <p class="text-muted">Navega entre las páginas para ver todas las facturas.</p>
	<div>
    <nav>
        <ul class="pagination">
            <?php if ($subpagina_actual > 1): ?>
                <li><a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo $pagina_actual; ?>&subpagina=<?php echo $subpagina_actual - 1; ?>">&laquo; Facturas anteriores</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_subpaginas; $i++): ?>
                <li <?php echo ($i == $subpagina_actual) ? 'class="active"' : ''; ?>>
                    <a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo $pagina_actual; ?>&subpagina=<?php echo $i; ?>">Página <?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($subpagina_actual < $total_subpaginas): ?>
                <li><a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo $pagina_actual; ?>&subpagina=<?php echo $subpagina_actual + 1; ?>">Facturas siguientes &raquo;</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Paginación de Clientes con nombre -->
<?php
// Obtener todos los clientes para mostrarlos en la paginación
$sql_nombres_clientes = "SELECT DISTINCT C.ID, C.RazonSocial 
                         FROM {$prefijobd}factura F 
                         JOIN {$prefijobd}clientes C ON F.CargoAFactura_RID = C.ID
                         JOIN {$prefijobd}oficinas O ON F.Oficina_RID = O.ID
                         WHERE F.CobranzaSaldo > 0 
                           AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') 
                           AND F.cfdfchhra > '1990-01-01 00:00:00'
                         ORDER BY C.RazonSocial";

$res_nombres = mysql_query($sql_nombres_clientes);
$nombres = [];
while ($row = mysql_fetch_assoc($res_nombres)) {
    $nombres[] = $row['RazonSocial'];
}
?>

<?php if ($total_paginas > 1): ?>
    <h4 style="margin-top: 5px;"><b>Paginación de Clientes</b></h4>
    <p class="text-muted">Haz clic en el nombre del cliente para ver su estado de cuenta.</p>
    <nav>
        <ul class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <li>
                    <a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo ($pagina_actual - 1); ?>&subpagina=1">
                        &laquo; Anterior
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li <?php echo ($i == $pagina_actual) ? 'class="active"' : ''; ?>>
                    <a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo $i; ?>&subpagina=1">
                        <?php 
                        $nombre = isset($nombres[$i - 1]) ? $nombres[$i - 1] : "Cliente $i";
                        echo strtoupper(substr($nombre, 0, 25)); 
                        ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <li>
                    <a href="?prefijobd=<?php echo $prefijobd; ?>&pagina=<?php echo ($pagina_actual + 1); ?>&subpagina=1">
                        Siguiente &raquo;
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
</div>
</div>
</body>
</html>
<?php
mysql_close($cnx_cfdi);
?>