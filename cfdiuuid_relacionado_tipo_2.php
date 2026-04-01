<?php
if (!isset($_POST['prefijodb']) || empty($_POST['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    die("Falta Factura");
}

if (!isset($_POST['xfolio']) || empty($_POST['xfolio'])) {
    $xfolio = '';
} else {
    $xfolio = $_POST["xfolio"];
}

$prefijobd = $_POST["prefijodb"];
$idfactura = (int)$_POST["id"];
$tiporelacion = isset($_POST["txt_tipo_relacion"]) ? $_POST["txt_tipo_relacion"] : '';
$tiporelacion2 = '00';
$xfolio_buscar = isset($_POST['xfolio_buscar']) ? trim($_POST['xfolio_buscar']) : '';

// paginación
$per_page = 1000;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if ($page < 1) $page = 1;

$prefijobd = preg_replace('/[^a-zA-Z0-9_]/', '', $prefijobd);
if (strpos($prefijobd, '_') === false) {
    $prefijobd .= '_';
}
if (substr($prefijobd, -1) !== '_') {
    $prefijobd .= '_';
}

if ($tiporelacion == '02' || $tiporelacion == '07') {
  $ctnQuery ='';
} else {
  $ctnQuery= " AND (cfdiSustituidaPor IS NULL OR cfdiSustituidaPor = '') ";
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

if (!$idcliente) {
    $cnx_cfdi3->close();
    die("No se encontró el cliente de la factura.");
}

// Buscar nombre del cliente
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

// Verificar cuantos registros hay
$numero = 0;
$rows   = array();
$usaRemisiones = false;

// Ajustes tiporelacion (igual que tu lógica)
if ($tiporelacion == '066' || $tiporelacion == '05') {
    $usaRemisiones = true;
}

// Conteo + filtro server-side por folio
$whereFolio = "";
$likeFolio = "";
$useLike = false;
if ($xfolio_buscar !== '') {
    $useLike = true;
    $likeFolio = '%' . $xfolio_buscar . '%';
    $whereFolio = " AND XFolio LIKE ? ";
}

if ($usaRemisiones) {
    $sqlCount = "SELECT COUNT(*) as total
                 FROM {$prefijobd}remisiones
                 WHERE CargoACliente_RID = ?
                   AND cfdiselloCFD IS NOT NULL
                   AND (RelacionadoPor IS NULL OR RelacionadoPor = '')
                   {$whereFolio}";
} else {
    $sqlCount = "SELECT COUNT(*) as total
                 FROM {$prefijobd}factura
                 WHERE CargoAFactura_RID = ?
                   AND (cCanceladoT IS NULL OR cCanceladoT = '')
                   AND cfdiselloCFD IS NOT NULL
                   {$ctnQuery}
                   {$whereFolio}";
}

$stmtCount = $cnx_cfdi3->prepare($sqlCount);
if ($stmtCount) {
    if ($useLike) {
        $stmtCount->bind_param('is', $idcliente, $likeFolio);
    } else {
        $stmtCount->bind_param('i', $idcliente);
    }
    $stmtCount->execute();
    $stmtCount->bind_result($totalReg);
    if ($stmtCount->fetch()) {
        $numero = (int)$totalReg;
    }
    $stmtCount->close();
}

// Ajustes tiporelacion (misma lógica)
if ($usaRemisiones) {
    if ($tiporelacion == '066') {
        $tiporelacion  = '06';
        $tiporelacion2 = '066';
    } elseif ($tiporelacion == '05') {
        $tiporelacion  = '05';
        $tiporelacion2 = '066';
    }
}

// Paginación calculada con el total
$total_pages = ($numero > 0) ? (int)ceil($numero / $per_page) : 1;
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $per_page;
$offset = (int)$offset;
$per_page = (int)$per_page;

// Consulta principal paginada (LIMIT/OFFSET)
if ($usaRemisiones) {
    $sqlLista = "SELECT ID, XFolio, cfdiuuid
                 FROM {$prefijobd}remisiones
                 WHERE CargoACliente_RID = ?
                   AND cfdiselloCFD IS NOT NULL
                   AND (RelacionadoPor IS NULL OR RelacionadoPor = '')
                   {$whereFolio}
                 ORDER BY Creado DESC 
                 LIMIT {$offset}, {$per_page}";
} else {
    $sqlLista = "SELECT ID, XFolio, cfdiuuid
                 FROM {$prefijobd}factura
                 WHERE CargoAFactura_RID = ?
                   AND (cCanceladoT IS NULL OR cCanceladoT = '')
                   AND cfdiselloCFD IS NOT NULL
                   {$ctnQuery}
                   {$whereFolio}
                 ORDER BY Creado DESC
                 LIMIT {$offset}, {$per_page}";
}

$stmtL = $cnx_cfdi3->prepare($sqlLista);
if ($stmtL) {
    if ($useLike) {
        $stmtL->bind_param('is', $idcliente, $likeFolio);
    } else {
        $stmtL->bind_param('i', $idcliente);
    }

    $stmtL->execute();
    $stmtL->bind_result($idDoc, $xf, $uuid);

    while ($stmtL->fetch()) {
        $rows[] = array(
            'ID'      => $idDoc,
            'XFolio'  => $xf,
            'UUID'    => $uuid
        );
    }
    $stmtL->close();
}

$cnx_cfdi3->close();
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8">
  <title>CFDI UUID Relacionados - Selección</title>
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
      --row-hover:#f1f4fb;
      --header-bg:rgba(221,221,221,0.72);
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
      --header-bg:rgba(20,24,36,.7);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:980px;
      margin:26px auto;
      padding:16px;
    }
    .header{
      margin-bottom:10px;
    }
    .title{
      font-size:1.4rem;
      font-weight:700;
      letter-spacing:-0.3px;
      margin-bottom:4px;
    }
    .subtitle{
      font-size:0.9rem;
      color:var(--text-soft);
    }
    .badge-row{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin:10px 0 14px;
    }
    .badge{
      display:inline-flex;
      padding:3px 10px;
      border-radius:999px;
      border:var(--border);
      font-size:0.78rem;
      color:var(--text-soft);
      background:rgba(255,255,255,.7);
    }
    html[data-theme="dark"] .badge{
      background:rgba(10,10,15,.65);
    }
    .panel{
      background:var(--panel);
      border-radius:var(--radius);
      border:var(--border);
      box-shadow:var(--shadow);
      overflow:hidden;
      backdrop-filter:blur(18px) saturate(1.2);
      -webkit-backdrop-filter:blur(18px) saturate(1.2);
    }
    .panel-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:10px 14px;
      border-bottom:var(--border);
      background:linear-gradient(180deg,rgba(255,255,255,.9),rgba(255,255,255,.6));
      gap:10px;
      flex-wrap:wrap;
    }
    html[data-theme="dark"] .panel-header{
      background:linear-gradient(180deg,rgba(20,24,36,.95),rgba(15,18,26,.8));
    }
    .panel-header-left{
      font-size:0.82rem;
      color:var(--text-soft);
    }
    .panel-header-left strong{ color:var(--text); }
    .search-box{
      display:flex;
      align-items:center;
      gap:6px;
      padding:4px 8px;
      border-radius:999px;
      border:var(--border);
      background:var(--row-bg);
      font-size:0.82rem;
    }
    .search-box input{
      border:none;
      outline:none;
      background:transparent;
      font-size:0.82rem;
      color:var(--text);
      min-width:140px;
    }
    .search-box span{
      font-size:0.8rem;
      color:var(--text-soft);
    }

    .table-container{
      max-height:520px;
      overflow:auto;
    }
    table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      font-size:0.8rem;
    }
    thead th{
      position:sticky;
      top:0;
      background:var(--header-bg);
      padding:7px 8px;
      text-align:left;
      border-bottom:var(--border);
      font-weight:600;
      color:var(--text-soft);
      backdrop-filter:blur(10px);
      z-index:5;
      white-space:nowrap;
    }
    tbody td{
      padding:7px 8px;
      border-bottom:1px solid rgba(0,0,0,.04);
      background:var(--row-bg);
      white-space:nowrap;
      text-align:left;
    }
    tbody tr:hover td{
      background:var(--row-hover);
    }
    a.link-action{
      font-size:0.78rem;
      text-decoration:none;
      color:var(--tint);
      font-weight:600;
    }
    a.link-action:hover{
      text-decoration:underline;
    }
    .empty{
      padding:12px 14px;
      font-size:0.85rem;
      color:var(--text-soft);
    }
    .footer-hint{
      margin-top:10px;
      font-size:0.78rem;
      color:var(--text-soft);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      padding:1px 7px;
      border-radius:999px;
      border:var(--border);
      font-size:0.7rem;
      margin-left:4px;
    }
    .pager{
      display:flex;
      align-items:center;
      gap:8px;
      flex-wrap:wrap;
    }
    .btn{
      appearance:none;
      border:none;
      cursor:pointer;
      border-radius:12px;
      padding:7px 10px;
      background:var(--row-bg);
      border:var(--border);
      color:var(--text);
      font-size:0.78rem;
      font-weight:600;
    }
    .btn:disabled{
      opacity:.5;
      cursor:not-allowed;
    }
    .pagebox{
      display:flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:12px;
      border:var(--border);
      background:var(--row-bg);
      font-size:0.78rem;
      color:var(--text-soft);
    }
    .pagebox input{
      width:70px;
      border:none;
      outline:none;
      background:transparent;
      color:var(--text);
      font-weight:700;
      text-align:center;
      font-size:0.8rem;
    }
    @media(max-width:700px){
      .app-shell{ margin:18px auto; padding:12px; }
      table{ font-size:0.78rem; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="header">
    <div class="title">Relacionar CFDI</div>
    <div class="subtitle">
      Paso 2 · Selecciona el documento que se va a relacionar con la factura origen.
    </div>
    <div class="badge-row">
      <div class="badge">Factura origen: <strong style="margin-left:4px;"><?php echo htmlspecialchars($xfolio); ?></strong></div>
      <div class="badge">Cliente: <strong style="margin-left:4px;"><?php echo htmlspecialchars($v_nom_cliente); ?></strong></div>
      <div class="badge">
        Tipo relación: <strong style="margin-left:4px;"><?php echo htmlspecialchars($tiporelacion); ?></strong>
        <?php if ($tiporelacion2 !== '00'): ?>
          <span class="pill">Relación secundaria: <?php echo htmlspecialchars($tiporelacion2); ?></span>
        <?php endif; ?>
      </div>
      <div class="badge">
        <?php echo $numero; ?> documentos encontrados
        <span class="pill">Mostrando <?php echo count($rows); ?> de <?php echo $per_page; ?> (página <?php echo $page; ?>/<?php echo $total_pages; ?>)</span>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-header-left">
        Lista de documentos timbrados del cliente para relacionar.<br>
        <strong>Haz clic en "Relacionar UUID" para anexar el CFDI.</strong>
      </div>

      <form id="frmFiltro" method="post" action="" style="margin:0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">
        <input type="hidden" name="id" value="<?php echo (int)$idfactura; ?>">
        <input type="hidden" name="xfolio" value="<?php echo htmlspecialchars($xfolio); ?>">
        <input type="hidden" name="txt_tipo_relacion" value="<?php echo htmlspecialchars($tiporelacion2 !== '00' ? $tiporelacion2 : $tiporelacion); ?>">
        <input type="hidden" name="page" id="pageHidden" value="<?php echo (int)$page; ?>">

        <div class="search-box">
          <span>Busca Folio</span>
          <input type="text" id="searchFolio" name="xfolio_buscar"
                 placeholder="Buscar por Folio..."
                 value="<?php echo htmlspecialchars($xfolio_buscar); ?>">
        </div>
      </form>
    </div>

    <?php if ($numero <= 0): ?>
      <div class="empty">
        No se encontraron documentos para este cliente con las condiciones de relación actuales.
      </div>
    <?php else: ?>
      <div class="table-container">
        <table id="tablaDocs">
          <thead>
            <tr>
              <th>Folio</th>
              <th>Cliente</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['XFolio']); ?></td>
              <td><?php echo htmlspecialchars($v_nom_cliente); ?></td>
              <td>
                <a class="link-action"
                   href="cfdiuuid_insert_tipo.php?cfdiuuid=<?php echo urlencode($r['UUID']); ?>
                     &id_factura_sel=<?php echo (int)$r['ID']; ?>
                     &xfolio=<?php echo urlencode($r['XFolio']); ?>
                     &foliofactura=<?php echo (int)$idfactura; ?>
                     &prefijodb=<?php echo urlencode($prefijobd); ?>
                     &facturaorigen=<?php echo urlencode($xfolio); ?>
                     &tiporelacion=<?php echo urlencode($tiporelacion); ?>
                     &tiporelacion2=<?php echo urlencode($tiporelacion2); ?>"
                   target="_blank">
                  Relacionar UUID
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="footer-hint">
    <div>
      TIP: escribe en <strong>"Busca Folio"</strong> y te filtra en server (más rápido con muchos registros).
    </div>

    <div class="pager">
      <button class="btn" type="button" id="btnPrev" <?php echo ($page <= 1 ? 'disabled' : ''); ?>>← Anterior</button>

      <div class="pagebox">
        Página
        <input type="number" min="1" max="<?php echo (int)$total_pages; ?>" id="pageInput" value="<?php echo (int)$page; ?>">
        de <?php echo (int)$total_pages; ?>
      </div>

      <button class="btn" type="button" id="btnGo">Ir</button>
      <button class="btn" type="button" id="btnNext" <?php echo ($page >= $total_pages ? 'disabled' : ''); ?>>Siguiente →</button>
    </div>
  </div>
</div>

<script>
(function(){
  var frm = document.getElementById('frmFiltro');
  var input = document.getElementById('searchFolio');
  var pageHidden = document.getElementById('pageHidden');
  var btnPrev = document.getElementById('btnPrev');
  var btnNext = document.getElementById('btnNext');
  var btnGo = document.getElementById('btnGo');
  var pageInput = document.getElementById('pageInput');

  if (!frm) return;

  function submitPage(p){
    if (typeof p === 'undefined') p = 1;
    if (p < 1) p = 1;
    pageHidden.value = p;
    frm.submit();
  }

  // búsqueda server-side con debounce (te limpia a página 1)
  var t = null;
  if (input){
    input.addEventListener('input', function(){
      if (t) clearTimeout(t);
      t = setTimeout(function(){
        submitPage(1);
      }, 300);
    });
  }

  if (btnPrev){
    btnPrev.addEventListener('click', function(){
      var p = parseInt(pageHidden.value, 10) || 1;
      submitPage(p - 1);
    });
  }

  if (btnNext){
    btnNext.addEventListener('click', function(){
      var p = parseInt(pageHidden.value, 10) || 1;
      submitPage(p + 1);
    });
  }

  if (btnGo){
    btnGo.addEventListener('click', function(){
      var p = parseInt(pageInput.value, 10) || 1;
      submitPage(p);
    });
  }

  if (pageInput){
    pageInput.addEventListener('keydown', function(e){
      if (e.key === 'Enter'){
        e.preventDefault();
        var p = parseInt(pageInput.value, 10) || 1;
        submitPage(p);
      }
    });
  }
})();
</script>
</body>
</html>