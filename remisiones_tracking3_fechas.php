<?php
// remisiones_tracking3_fechas.php
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die("Error de conexión a la base de datos.");
}
mysqli_set_charset($cnx_cfdi3, "utf8");

// --- Prefijo ---
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

$prefijodb_raw = $_GET['prefijodb'];
$prefijodb = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijodb_raw);
if (strpos($prefijodb, '_') === false) {
    $prefijodb .= '_';
}
if (substr($prefijodb, -1) !== '_') {
    $prefijodb .= '_';
}

// --- Sucursal (puede venir vacía) ---
$sucursal = isset($_GET['sucursal']) && $_GET['sucursal'] !== ''
    ? (int)$_GET['sucursal']
    : 0;

// --- Arrays para combos ---
$unidades   = [];
$operadores = [];

// Detectar si unidades/operadores tienen Sucursal_RID
$hasSucursalUnidades   = false;
$hasSucursalOperadores = false;

$checkUni = $cnx_cfdi3->query("SHOW COLUMNS FROM {$prefijodb}unidades LIKE 'Sucursal_RID'");
if ($checkUni && $checkUni->num_rows > 0) {
    $hasSucursalUnidades = true;
}
$checkOps = $cnx_cfdi3->query("SHOW COLUMNS FROM {$prefijodb}operadores LIKE 'Sucursal_RID'");
if ($checkOps && $checkOps->num_rows > 0) {
    $hasSucursalOperadores = true;
}

// --- Cargar Unidades ---
$sqlUnidades = "SELECT ID, Unidad FROM {$prefijodb}unidades";
if ($hasSucursalUnidades && $sucursal > 0) {
    $sqlUnidades .= " WHERE Sucursal_RID = " . (int)$sucursal;
}
$sqlUnidades .= " ORDER BY Unidad";

if ($resU = $cnx_cfdi3->query($sqlUnidades)) {
    while ($row = $resU->fetch_assoc()) {
        $unidades[] = [
            'ID'     => (int)$row['ID'],
            'Unidad' => $row['Unidad'],
        ];
    }
}

// --- Cargar Operadores ---
$sqlOps = "SELECT ID, Operador FROM {$prefijodb}operadores";
if ($hasSucursalOperadores && $sucursal > 0) {
    $sqlOps .= " WHERE Sucursal_RID = " . (int)$sucursal;
}
$sqlOps .= " ORDER BY Operador";

if ($resO = $cnx_cfdi3->query($sqlOps)) {
    while ($row = $resO->fetch_assoc()) {
        $operadores[] = [
            'ID'       => (int)$row['ID'],
            'Operador' => $row['Operador'],
        ];
    }
}

$cnx_cfdi3->close();
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Viajes por Periodo - Tracking 3</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg: #ffffffff;
      --panel: #ffffffcc;
      --text: #0b0c0f;
      --text-soft: #5c6270;
      --tint: #0a84ff;
      --radius: 16px;
      --shadow: 0 8px 24px rgba(0,0,0,.08);
      --border: 1px solid rgba(10,12,16,.08);
      --row-bg: #ffffffff;
      --row-hover: #f1f4fb;
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
      --row-hover:#1a2030;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:720px;
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
      margin-bottom:16px;
    }
    .field-group{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      margin-bottom:12px;
    }
    .field{
      flex:1 1 200px;
      display:flex;
      flex-direction:column;
      gap:4px;
      font-size:0.85rem;
    }
    label{
      font-weight:500;
      color:var(--text-soft);
    }
    input[type="date"],
    select{
      border-radius:12px;
      border:var(--border);
      padding:6px 10px;
      font-size:0.9rem;
      background:var(--row-bg);
      color:var(--text);
    }
    .actions{
      display:flex;
      gap:10px;
      margin-top:10px;
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
    .btn-secondary{
      border-radius:999px;
      padding:8px 14px;
      font-size:0.85rem;
      border:var(--border);
      background:var(--row-bg);
      color:var(--text);
      cursor:pointer;
    }
    @media (max-width:600px){
      .app-shell{ margin:20px auto; padding:12px; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="card">
    <div class="title">Viajes por Periodo - Tracking 3</div>
    <div class="subtitle">
      Selecciona un periodo y filtros. El buscador general va en el listado, no aquí.
    </div>

    <form method="post" action="remisiones_tracking3.php">
      <div class="field-group">
        <div class="field">
          <label for="fechai">Fecha inicio</label>
          <input type="date" name="fechai" id="fechai" required>
        </div>
        <div class="field">
          <label for="fechaf">Fecha fin</label>
          <input type="date" name="fechaf" id="fechaf" required>
        </div>
      </div>

      <div class="field-group">
        <div class="field">
          <label for="unidad">Unidad</label>
          <select name="unidad" id="unidad">
            <option value="0">Todas</option>
            <?php foreach ($unidades as $u): ?>
              <option value="<?php echo (int)$u['ID']; ?>">
                <?php echo htmlspecialchars($u['Unidad']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="operador">Operador</label>
          <select name="operador" id="operador">
            <option value="0">Todos</option>
            <?php foreach ($operadores as $o): ?>
              <option value="<?php echo (int)$o['ID']; ?>">
                <?php echo htmlspecialchars($o['Operador']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Prefijo y sucursal ocultos -->
      <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijodb); ?>">
      <input type="hidden" name="sucursal"  value="<?php echo (int)$sucursal; ?>">

      <div class="actions">
        <button type="submit" class="btn-primary">Consultar</button>
        <button type="reset" class="btn-secondary">Limpiar</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
