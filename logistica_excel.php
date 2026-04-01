<?php
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
$cnx_cfdi3->set_charset("utf8");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
$prefijobd = mysqli_real_escape_string($cnx_cfdi3, $_GET['prefijodb']);
if (strpos($prefijobd, "_") === false) {
    $prefijobd .= "_";
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
$nombre = "logistica_ult_mov_".date("Y-m-d_H-i-s").".xls";
header("Content-Disposition: attachment; filename={$nombre}");

echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';

$baseQuery = "
    SELECT 
        r.XFolio,
        r.EstatusTerminadoT,
        u.Unidad,
        r.Destinatario,
        c.RazonSocial,
        o.Operador
    FROM {$prefijobd}Remisiones r
    INNER JOIN {$prefijobd}Unidades u ON r.Unidad_RID = u.ID
    INNER JOIN {$prefijobd}Clientes c ON r.CargoACliente_RID = c.ID
    INNER JOIN {$prefijobd}Operadores o ON r.Operador_RID = o.ID
    INNER JOIN (
        SELECT Unidad_RID, MAX(Creado) AS last_created
        FROM {$prefijobd}Remisiones
        GROUP BY Unidad_RID
    ) lr ON lr.Unidad_RID = r.Unidad_RID AND lr.last_created = r.Creado
    ORDER BY u.Unidad ASC
";

$res = $cnx_cfdi3->query($baseQuery);
if(!$res){
    die("Error en consulta: ".$cnx_cfdi3->error);
}
?>
<table border="1" cellspacing="0" cellpadding="3">
  <thead>
    <tr>
      <th colspan="6" style="font-size:16px;">Estatus Unidades - Último Movimiento</th>
    </tr>
    <tr>
      <th>Folio</th>
      <th>Estatus</th>
      <th>Unidad</th>
      <th>Destino</th>
      <th>Cliente</th>
      <th>Operador</th>
    </tr>
  </thead>
  <tbody>
<?php while($r = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['XFolio']); ?></td>
      <td><?php echo htmlspecialchars($r['EstatusTerminadoT']); ?></td>
      <td><?php echo htmlspecialchars($r['Unidad']); ?></td>
      <td><?php echo htmlspecialchars($r['Destinatario']); ?></td>
      <td><?php echo htmlspecialchars($r['RazonSocial']); ?></td>
      <td><?php echo htmlspecialchars($r['Operador']); ?></td>
    </tr>
<?php endwhile; ?>
  </tbody>
</table>
<?php
$cnx_cfdi3->close();
