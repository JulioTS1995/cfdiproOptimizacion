<?php
// ================== VALIDACIÓN DE PREFIJO ==================
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

// Internalizo los parámetros
$prefijobd_raw = $_GET['prefijodb'];


$prefijobd_raw = str_replace(array("'", '"', ';', ' '), '', $prefijobd_raw);

// Me aseguro que termine en guion bajo
$pos = strpos($prefijobd_raw, "_");
if ($pos === false || $pos !== strlen($prefijobd_raw) - 1) {
    $prefijobd = $prefijobd_raw . "_";
} else {
    $prefijobd = $prefijobd_raw;
}

// Sucursal opcional 
$sucursal = isset($_GET['sucursal']) ? $_GET['sucursal'] : '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Viajes por Periodo - Tracking</title>
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
            --row-hover: #f1f4fb;
        }
        html[data-theme="dark"]{
            --bg:#0b0c0f;
            --panel:#0f1218cc;
            --text:#f5f7fb;
            --text-soft:#a6aec2;
            --tint:#0a84ff;
            --shadow: 0 8px 24px rgba(0,0,0,.35);
            --border: 1px solid rgba(255,255,255,.06);
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
            max-width:580px;
            margin:32px auto;
            padding:16px;
        }
        .app-header{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:12px;
            margin-bottom:16px;
        }
        .app-title{
            font-size:1.5rem;
            font-weight:700;
            letter-spacing:-0.3px;
        }
        .app-subtitle{
            font-size:0.85rem;
            color:var(--text-soft);
        }
        .badge{
            display:inline-flex;
            padding:2px 8px;
            border-radius:999px;
            font-size:0.75rem;
            border:var(--border);
            margin-top:4px;
            background:rgba(255,255,255,.4);
        }
        html[data-theme="dark"] .badge{
            background:rgba(10,10,15,.6);
        }
        .btn.theme-toggle{
            display:inline-flex;
            align-items:center;
            gap:6px;
            border:var(--border);
            background:var(--panel);
            color:var(--text);
            padding:8px 12px;
            border-radius:999px;
            font-weight:600;
            cursor:pointer;
            box-shadow:0 2px 8px rgba(0,0,0,.06);
            transition:.2s;
            font-size:0.85rem;
        }
        .btn.theme-toggle:hover{ transform:translateY(-1px); }

        .panel{
            background:var(--panel);
            backdrop-filter:blur(18px) saturate(1.2);
            -webkit-backdrop-filter:blur(18px) saturate(1.2);
            border-radius:var(--radius);
            border:var(--border);
            box-shadow:var(--shadow);
            padding:18px 16px 16px;
        }
        .form-row{
            display:flex;
            flex-direction:column;
            gap:8px;
            margin-bottom:16px;
        }
        .field-group{
            display:flex;
            flex-direction:column;
            gap:4px;
        }
        label{
            font-size:0.8rem;
            font-weight:600;
            color:var(--text-soft);
        }
        input[type="date"]{
            width:100%;
            padding:8px 10px;
            border-radius:10px;
            border:var(--border);
            font-size:0.85rem;
            background:rgba(255,255,255,.9);
            color:var(--text);
        }
        html[data-theme="dark"] input[type="date"]{
            background:rgba(10,12,18,.9);
        }
        .hint{
            font-size:0.75rem;
            color:var(--text-soft);
        }
        .actions{
            display:flex;
            justify-content:flex-end;
            gap:8px;
            margin-top:10px;
        }
        .btn{
            border-radius:999px;
            border:var(--border);
            background:var(--panel);
            padding:8px 14px;
            font-size:0.85rem;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            gap:6px;
            color:var(--text);
        }
        .btn:hover{ background:var(--row-hover); }
        .btn-primary{
            border:none;
            background:linear-gradient(180deg,var(--tint),#0051b8);
            color:#fff;
            font-weight:600;
            box-shadow:0 4px 12px rgba(0,122,255,.25);
        }
        .btn-primary:hover{
            transform:translateY(-1px);
            opacity:.96;
        }
        @media (max-width:600px){
            .app-shell{ margin:20px auto; padding:12px; }
            .app-header{ flex-direction:column; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="app-header">
        <div>
            <div class="app-title">Viajes por periodo</div>
            <div class="app-subtitle">
                Tracking de viajes por rango de fechas.
            </div>
            <div class="badge">
               
                <?php if(!empty($sucursal)): ?>
                    Sucursal: <?php echo htmlspecialchars($sucursal); ?>
                <?php endif; ?>
            </div>
        </div>
        <button id="themeToggle" class="btn theme-toggle" type="button" aria-label="Cambiar tema">
            <span class="sun">☀️ Tema</span>
            <span class="moon" style="display:none;">🌙 Tema</span>
        </button>
    </div>

    <div class="panel">
        <form method="post" action="2_remisiones_tracking.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="field-group">
                    <label for="fechai">Fecha inicial</label>
                    <input type="date" name="fechai" id="fechai" required autofocus>
                </div>
                <div class="field-group">
                    <label for="fechaf">Fecha final</label>
                    <input type="date" name="fechaf" id="fechaf" required>
                </div>
                <div class="hint">
                    Selecciona el rango de fechas para consultar los viajes registrados en ese periodo.
                </div>
            </div>

            <input type="hidden" name="base" id="base" value="<?php echo htmlspecialchars($prefijobd); ?>">
            <?php if(!empty($sucursal)): ?>
                <input type="hidden" name="sucursal" id="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>">
            <?php endif; ?>

            <div class="actions">
                <button type="reset" class="btn">Cancelar</button>
                <button type="submit" name="consultar" class="btn btn-primary"
                        title="Favor de esperar... el reporte se está generando">
                    Consultar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tema claro/oscuro
(function(){
    var root = document.documentElement;
    var key  = 'ui-theme';
    var saved = localStorage.getItem(key);

    if(saved === 'light' || saved === 'dark'){
        root.setAttribute('data-theme', saved);
    } else {
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
    }

    function syncIcons(){
        var isDark = root.getAttribute('data-theme') === 'dark';
        var sun = document.querySelector('#themeToggle .sun');
        var moon = document.querySelector('#themeToggle .moon');
        if(sun && moon){
            sun.style.display  = isDark ? 'none'  : 'inline';
            moon.style.display = isDark ? 'inline': 'none';
        }
    }
    syncIcons();

    var btn = document.getElementById('themeToggle');
    if(btn){
        btn.addEventListener('click', function(){
            var current = root.getAttribute('data-theme') || 'light';
            var next    = (current === 'light') ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            localStorage.setItem(key, next);
            syncIcons();
        });
    }


    if (window.self !== window.top) {
        // btn.style.display = 'none';
    }
})();
</script>
</body>
</html>
