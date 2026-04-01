<?php


require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
$cnx_cfdi3->set_charset("utf8");

// Validar prefijo
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
$prefijobd = mysqli_real_escape_string($cnx_cfdi3, $_GET['prefijodb']);
if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

// Consulta: ultimo movimiento por unidad (misma lógica que en logistica.php)
$sql = "
    SELECT 
        r.XFolio,
        r.EstatusTerminadoT,
        u.Unidad,
        r.Destinatario,
        c.RazonSocial,
        o.Operador
    FROM {$prefijobd}Remisiones r
    INNER JOIN {$prefijobd}Unidades   u ON r.Unidad_RID         = u.ID
    INNER JOIN {$prefijobd}Clientes   c ON r.CargoACliente_RID  = c.ID
    INNER JOIN {$prefijobd}Operadores o ON r.Operador_RID       = o.ID
    INNER JOIN (
        SELECT Unidad_RID, MAX(Creado) AS last_created
        FROM {$prefijobd}Remisiones
        GROUP BY Unidad_RID
    ) lr ON lr.Unidad_RID = r.Unidad_RID AND lr.last_created = r.Creado
    ORDER BY u.Unidad ASC
";

$res = $cnx_cfdi3->query($sql);
if (!$res) {
    die("Error en consulta: " . $cnx_cfdi3->error);
}

// Armamos el HTML que va a consumir mPDF
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body{
      font-family: sans-serif;
      font-size: 9pt;
    }
    h1{
      font-size: 14pt;
      margin-bottom: 8px;
    }
    .subtitle{
      font-size: 9pt;
      color: #555;
      margin-bottom: 10px;
    }
    table{
      width: 100%;
      border-collapse: collapse;
      font-size: 8pt;
    }
    th, td{
      border: 0.4pt solid #ccc;
      padding: 4px 3px;
    }
    th{
      background: #f2f2f2;
      font-weight: bold;
      text-align: center;
    }
    td{
      text-align: left;
    }
    td.center{
      text-align: center;
    }
    td.wrap{
      word-wrap: break-word;
    }
  </style>
</head>
<body>
  <h1>Estatus Unidades – Último Movimiento</h1>
  <div class="subtitle">
    
   <b> · Generado: </b><?php echo date("d-m-Y H:i"); ?>
  </div>

  <table autosize="1">
    <thead>
      <tr>
        <th style="width:8%;">Folio</th>
        <th style="width:15%;">Estatus</th>
        <th style="width:10%;">Unidad</th>
        <th style="width:27%;">Destino</th>
        <th style="width:25%;">Cliente</th>
        <th style="width:15%;">Operador</th>
      </tr>
    </thead>
    <tbody>
<?php while ($r = $res->fetch_assoc()): ?>
      <tr>
        <td class="center"><?php echo htmlspecialchars($r['XFolio'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($r['EstatusTerminadoT'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="center"><?php echo htmlspecialchars($r['Unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="wrap"><?php echo htmlspecialchars($r['Destinatario'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="wrap"><?php echo htmlspecialchars($r['RazonSocial'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="wrap"><?php echo htmlspecialchars($r['Operador'], ENT_QUOTES, 'UTF-8'); ?></td>
      </tr>
<?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
<?php
$html = ob_get_clean();


require_once('lib_mpdf/pdf/mpdf.php');

// Parámetros: modo, formato, tamaño fuente, fuente, márgenes L,R,T,B
$mpdf = new mPDF('utf-8', 'A4-L', 0, '', 10, 10, 10, 10);


$mpdf->SetTitle('Estatus Unidades - Último Movimiento');
$mpdf->WriteHTML($html);


$mpdf->Output('logistica_ult_mov.pdf', 'I');
exit;
