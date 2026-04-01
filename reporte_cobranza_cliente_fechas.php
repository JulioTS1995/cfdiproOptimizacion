<?php
require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) { die("Error de conexión a la base de datos."); }
mysqli_set_charset($cnx_cfdi3, "utf8");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
  die("Falta el prefijo de la BD");
}

$prefijobd_raw = $_GET['prefijodb'];
$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;

// Sanitizar prefijo
$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd_raw);
if (strpos($prefijobd, '_') === false) { $prefijobd .= '_'; }
if (substr($prefijobd, -1) !== '_') { $prefijobd .= '_'; }

// Validar tabla clientes
$tablaClientes = $prefijobd . "clientes";
$chkTbl = $cnx_cfdi3->query("SHOW TABLES LIKE '{$tablaClientes}'");
if (!$chkTbl || $chkTbl->num_rows === 0) {
  die("No existe la tabla {$tablaClientes}");
}

// Detectar si existe la columna Sucursal_RID en clientes (tolerante)
$tieneSucursalClientes = false;
$chkCol = $cnx_cfdi3->query("SHOW COLUMNS FROM {$tablaClientes} LIKE 'Sucursal_RID'");
if ($chkCol && $chkCol->num_rows > 0) {
  $tieneSucursalClientes = true;
}

// SQL clientes
$sqlClientes = "SELECT ID, RazonSocial FROM {$tablaClientes}";
if ($tieneSucursalClientes && $sucursal > 0) {
  $sqlClientes .= " WHERE Sucursal_RID = ?";
}
$sqlClientes .= " ORDER BY RazonSocial";

$stmt = $cnx_cfdi3->prepare($sqlClientes);
if (!$stmt) { die("Error prepare clientes: " . $cnx_cfdi3->error); }

if ($tieneSucursalClientes && $sucursal > 0) {
  $stmt->bind_param("i", $sucursal);
}
$stmt->execute();
$res = $stmt->get_result();

$clientes = [];
while ($row = $res->fetch_assoc()) {
  $clientes[] = $row;
}

$stmt->close();
$cnx_cfdi3->close();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reporte Cuentas por Cobrar</title>

  <style>
    :root{
      --bg: #ffffffff;

      --text: #0b0d12;
      --muted: rgba(11,13,18,.65);

      --panel: rgba(255,255,255,.70);
      --panel-border: rgba(15,23,42,.10);
      --shadow: 0 14px 40px rgba(2,6,23,.10);

      --row-bg: rgba(255,255,255,.55);
      --row-hover: rgba(2,6,23,.04);

      --input-bg: rgba(255,255,255,.78);
      --input-border: rgba(15,23,42,.14);
      --focus: rgba(59,130,246,.35);

      --btn-text: #fff;

      --danger1: #ff4d4f;
      --danger2: #cf1322;
      --success1:#52c41a;
      --success2:#389e0d;

      --chip: rgba(2,6,23,.06);
      --chip-border: rgba(2,6,23,.10);

      --radius: 18px;
      --radius-sm: 14px;
    }

    [data-theme="dark"]{
      --bg: #0b1220;

      --text: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.60);

      --panel: rgba(17,25,40,.65);
      --panel-border: rgba(255,255,255,.10);
      --shadow: 0 14px 40px rgba(0,0,0,.35);

      --row-bg: rgba(255,255,255,.06);
      --row-hover: rgba(255,255,255,.08);

      --input-bg: rgba(255,255,255,.06);
      --input-border: rgba(255,255,255,.14);
      --focus: rgba(59,130,246,.45);

      --chip: rgba(255,255,255,.08);
      --chip-border: rgba(255,255,255,.12);
    }

    *{ box-sizing: border-box; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      background:
        radial-gradient(1200px 600px at 10% -10%, rgba(59,130,246,.20), transparent 60%),
        radial-gradient(900px 500px at 90% 0%, rgba(236,72,153,.16), transparent 55%),
        radial-gradient(900px 700px at 40% 110%, rgba(34,197,94,.12), transparent 60%),
        var(--bg);
      color: var(--text);
      min-height: 100vh;
    }

    .wrap{
      max-width: 980px;
      margin: 28px auto;
      padding: 0 16px 40px;
    }

    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      margin-bottom: 14px;
    }

    .title{
      display:flex;
      flex-direction:column;
      gap: 4px;
    }

    .title h1{
      font-size: 22px;
      line-height: 1.2;
      margin:0;
      letter-spacing: .2px;
    }

    .title .sub{
      color: var(--muted);
      font-size: 13px;
    }

    .chip{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: var(--chip);
      border: 1px solid var(--chip-border);
      color: var(--muted);
      font-size: 12px;
      white-space: nowrap;
    }

    .panel{
      background: var(--panel);
      border: 1px solid var(--panel-border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      padding: 18px;
    }

    .grid{
      display:grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 12px;
    }

    .col-6{ grid-column: span 6; }
    .col-12{ grid-column: span 12; }

    @media (max-width: 820px){
      .col-6{ grid-column: span 12; }
      .topbar{ flex-direction: column; align-items: flex-start; }
    }

    label{
      display:block;
      font-size: 13px;
      color: var(--muted);
      margin: 0 0 6px;
    }

    .input, select{
      width: 100%;
      border-radius: var(--radius-sm);
      border: 1px solid var(--input-border);
      background: var(--input-bg);
      color: var(--text);
      padding: 12px 12px;
      font-size: 14px;
      outline: none;
      transition: box-shadow .15s ease, border-color .15s ease, transform .05s ease;
    }

    .input:focus, select:focus{
      box-shadow: 0 0 0 4px var(--focus);
      border-color: rgba(59,130,246,.55);
    }

    .actions{
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 14px;
    }

    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 10px;
      border: 0;
      padding: 12px 14px;
      border-radius: 999px;
      color: var(--btn-text);
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 10px 24px rgba(2,6,23,.12);
      transition: transform .06s ease, filter .15s ease;
      user-select: none;
    }
    .btn:active{ transform: translateY(1px); }
    .btn:hover{ filter: brightness(1.03); }

    .btn-danger{
      background: linear-gradient(180deg, var(--danger1), var(--danger2));
      box-shadow: 0 10px 24px rgba(207,19,34,.22);
    }
    .btn-success{
      background: linear-gradient(180deg, var(--success1), var(--success2));
      box-shadow: 0 10px 24px rgba(56,158,13,.22);
    }

    .btn-ghost{
      background: transparent;
      color: var(--text);
      border: 1px solid var(--panel-border);
      box-shadow: none;
    }

    .icon{
      width: 18px; height: 18px; display:inline-block;
    }

    .footer-note{
      margin-top: 12px;
      font-size: 12px;
      color: var(--muted);
    }

    .toggle{
      display:inline-flex;
      align-items:center;
      gap:10px;
      border-radius: 999px;
      padding: 10px 12px;
      border: 1px solid var(--panel-border);
      background: var(--panel);
      cursor:pointer;
      user-select:none;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      box-shadow: var(--shadow);
    }
    .toggle .dot{
      width: 40px; height: 22px;
      border-radius: 999px;
      background: rgba(2,6,23,.12);
      border: 1px solid var(--panel-border);
      position: relative;
      flex: 0 0 auto;
    }
    [data-theme="dark"] .toggle .dot{
      background: rgba(255,255,255,.10);
    }
    .toggle .dot::after{
      content:"";
      position:absolute;
      top: 2px; left: 2px;
      width: 18px; height: 18px;
      border-radius: 50%;
      background: rgba(255,255,255,.95);
      box-shadow: 0 6px 14px rgba(0,0,0,.18);
      transition: transform .18s ease;
    }
    [data-theme="dark"] .toggle .dot::after{
      transform: translateX(18px);
      background: rgba(255,255,255,.92);
    }
    .toggle .txt{
      font-size: 13px;
      color: var(--muted);
      font-weight: 600;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="topbar">
      <div class="title">
        <h1>Reporte Cuentas por Cobrar</h1>
        <div class="sub">Cobranza por Cliente · PDF / Excel</div>
      </div>

      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <?php if ($sucursal > 0): ?>
          <div class="chip">🏢 Sucursal ID: <strong style="color:var(--text)"><?php echo (int)$sucursal; ?></strong></div>
        <?php endif; ?>
        <div class="toggle" id="themeToggle" role="button" aria-label="Cambiar tema">
          <div class="dot" aria-hidden="true"></div>
          <div class="txt" id="themeText">Tema</div>
        </div>
      </div>
    </div>

    <div class="panel">
      <form method="post" action="reporte_cobranza_cliente.php" target="_blank" autocomplete="off">
        <div class="grid">
          <div class="col-6">
            <label for="txtDesde">Fecha inicio</label>
            <input class="input" type="date" name="txtDesde" id="txtDesde" required>
          </div>

          <div class="col-6">
            <label for="txtHasta">Fecha fin</label>
            <input class="input" type="date" name="txtHasta" id="txtHasta" required>
          </div>

          <div class="col-6">
            <label for="cliente">Cliente</label>
            <select name="cliente" id="cliente">
              <option value="0">— Seleccione —</option>
              <?php foreach ($clientes as $c): ?>
                <option value="<?php echo (int)$c['ID']; ?>">
                  <?php echo htmlspecialchars($c['RazonSocial']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-6">
            <label for="moneda">Moneda</label>
            <select name="moneda" id="moneda" required>
              <option value="PESOS">PESOS</option>
              <option value="DOLARES">DOLARES</option>
            </select>
          </div>
        </div>

        <input type="hidden" name="sucursal" value="<?php echo (int)$sucursal; ?>">
        <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">

        <div class="actions">
          <button type="submit" class="btn btn-danger" name="btnEnviar" value="PDF">
            <span class="icon" aria-hidden="true">📄</span> PDF
          </button>

          <button type="submit" class="btn btn-success" name="btnEnviar" value="Excel">
            <span class="icon" aria-hidden="true">📊</span> Excel
          </button>

          <button type="button" class="btn btn-ghost" id="clearBtn">
            🧹 Limpiar
          </button>
        </div>

 
      </form>
    </div>
  </div>

  <script>
    (function(){
      const root = document.documentElement;
      const KEY = "ts_theme";
      const saved = localStorage.getItem(KEY);
      if(saved === "dark") root.setAttribute("data-theme","dark");

      const btn = document.getElementById("themeToggle");
      btn.addEventListener("click", () => {
        const isDark = root.getAttribute("data-theme") === "dark";
        if(isDark){
          root.removeAttribute("data-theme");
          localStorage.setItem(KEY, "light");
        }else{
          root.setAttribute("data-theme","dark");
          localStorage.setItem(KEY, "dark");
        }
      });

      // limpiar
      document.getElementById("clearBtn").addEventListener("click", () => {
        const d = document.getElementById("txtDesde");
        const h = document.getElementById("txtHasta");
        const c = document.getElementById("cliente");
        const m = document.getElementById("moneda");
        d.value = "";
        h.value = "";
        c.value = "0";
        m.value = "PESOS";
      });
    })();
  </script>
</body>
</html>
