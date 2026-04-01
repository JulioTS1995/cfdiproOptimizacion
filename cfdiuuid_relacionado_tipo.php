<?php


if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Falta Factura");
}

if (!isset($_GET['xfolio']) || empty($_GET['xfolio'])) {
    die("Falta XFolio");
}

$prefijobd = $_GET["prefijodb"];
$idfactura = (int)$_GET["id"];
$xfolio    = $_GET["xfolio"];
$tiporelacion = isset($_GET["tipo"]) ? $_GET["tipo"] : "";


$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if (strpos($prefijobd, '_') === false) {
    $prefijobd .= '_';
}
if (substr($prefijobd, -1) !== '_') {
    $prefijobd .= '_';
}

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// Buscar datos de la factura para obtener el cliente
$idcliente     = null;
$v_nom_cliente = '';

$sqlFactura = "SELECT CargoAFactura_RID FROM {$prefijobd}factura WHERE ID = ?";
$stmtF = $cnx_cfdi3->prepare($sqlFactura);
if ($stmtF) {
    $stmtF->bind_param('i', $idfactura);
    $stmtF->execute();
    $stmtF->bind_result($idclienteTmp);
    if ($stmtF->fetch()) {
        $idcliente = $idclienteTmp;
    }
    $stmtF->close();
}

// Buscar nombre del cliente
if ($idcliente) {
    $sqlCliente = "SELECT RazonSocial FROM {$prefijobd}clientes WHERE ID = ?";
    $stmtC = $cnx_cfdi3->prepare($sqlCliente);
    if ($stmtC) {
        $stmtC->bind_param('i', $idcliente);
        $stmtC->execute();
        $stmtC->bind_result($nomCli);
        if ($stmtC->fetch()) {
            $v_nom_cliente = $nomCli;
        }
        $stmtC->close();
    }
}

$cnx_cfdi3->close();
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8">
  <title>Relacionar CFDI - Paso 1</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg:#ffffffff;
      --panel:#ffffffcc;
      --text:#0b0c0f;
      --text-soft:#5c6270;
      --tint:#0a84ff;
      --radius:16px;
      --shadow:0 8px 24px rgba(0,0,0,.08);
      --border:1px solid rgba(10,12,16,.08);
      --row-bg:#ffffffff;
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow:0 8px 24px rgba(0,0,0,.35);
      --border:1px solid rgba(255,255,255,.06);
      --row-bg:#11141d;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:640px;
      margin:32px auto;
      padding:16px;
    }
    .card{
      background:var(--panel);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      padding:20px 18px 18px;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .title{
      font-size:1.4rem;
      font-weight:700;
      margin-bottom:4px;
      letter-spacing:-0.3px;
    }
    .subtitle{
      font-size:0.9rem;
      color:var(--text-soft);
      margin-bottom:10px;
    }
    .badge{
      display:inline-flex;
      padding:2px 8px;
      border-radius:999px;
      border:var(--border);
      font-size:0.75rem;
      color:var(--text-soft);
      margin-bottom:16px;
      background:rgba(255,255,255,.5);
    }
    html[data-theme="dark"] .badge{
      background:rgba(10,10,15,.6);
    }
    .field-group{
      display:flex;
      flex-direction:column;
      gap:12px;
      margin-bottom:14px;
    }
    .field{
      display:flex;
      flex-direction:column;
      gap:4px;
      font-size:0.85rem;
    }
    label{
      font-weight:500;
      color:var(--text-soft);
    }
    select,
    input[type="text"]{
      border-radius:12px;
      border:var(--border);
      padding:7px 10px;
      font-size:0.9rem;
      background:var(--row-bg);
      color:var(--text);
    }
    .hint{
      font-size:0.78rem;
      color:var(--text-soft);
      margin-top:4px;
    }
    .actions{
      display:flex;
      justify-content:flex-end;
      margin-top:16px;
      gap:10px;
      flex-wrap:wrap;
    }
    .btn-primary{
      border:none;
      border-radius:999px;
      padding:8px 18px;
      font-size:0.9rem;
      font-weight:600;
      background:linear-gradient(180deg,var(--tint),#0051b8);
      color:#fff;
      cursor:pointer;
      box-shadow:0 4px 12px rgba(0,122,255,.25);
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .btn-primary:hover{ opacity:.96; transform:translateY(-1px); }
    .btn-secondary{
      border-radius:999px;
      padding:8px 14px;
      font-size:0.85rem;
      border:var(--border);
      background:var(--row-bg);
      color:var(--text);
      cursor:pointer;
    }
    .meta-list{
      font-size:0.8rem;
      color:var(--text-soft);
      margin-bottom:16px;
      line-height:1.4;
    }
    .meta-list strong{ color:var(--text); }
    @media(max-width:600px){
      .app-shell{ margin:20px auto; padding:12px; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="card">
    <div class="title">Relacionar CFDI</div>
    <div class="subtitle">Paso 1 · Selecciona el tipo de relación y captura el folio a relacionar.</div>
    <div class="badge">
      Factura seleccionada: <strong style="margin-left:4px;"><?php echo htmlspecialchars($xfolio); ?></strong>
    </div>

    <div class="meta-list">
      <?php if ($v_nom_cliente): ?>
        <div><strong>Cliente:</strong> <?php echo htmlspecialchars($v_nom_cliente); ?></div>
      <?php endif; ?>
     
    </div>

    <form action="cfdiuuid_relacionado_tipo_2.php" method="post" id="formRelacion">
      <div class="field-group">
        <div class="field">
          <label for="txt_tipo_relacion">Tipo de relación</label>
          <select id="txt_tipo_relacion" name="txt_tipo_relacion">
            <option value="02">02 - Nota de débito de los documentos relacionados</option>
            <option value="04">04 - Sustitución de los CFDI previos</option>
            <option value="05">05 - Traslados de Mercancias Facturados Previamente</option>
            <option value="06">06 - Factura Generada por los Traslados Previos</option>
            <option value="066">06 - Carta Porte Traslado</option>
            <option value="07">07 - CFDI por aplicación de anticipo</option>
            <option value="09">09 - Factura Generada por Pagos Diferidos</option>
          </select>
        </div>

        
      </div>

      <input type="hidden" name="id"        value="<?php echo (int)$idfactura; ?>">
      <input type="hidden" name="xfolio"    value="<?php echo htmlspecialchars($xfolio); ?>">
      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">

      <div class="actions">
        <button type="submit" class="btn-primary">
          Siguiente →
        </button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
