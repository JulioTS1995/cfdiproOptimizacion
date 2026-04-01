<?php
set_time_limit(3000);
error_reporting(0);

if (!isset($_POST['prefijodb']) || empty($_POST['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

$prefijobd    = $_POST['prefijodb'];
$tipoReporte  = isset($_POST['tiporeporte']) ? $_POST['tiporeporte'] : 'FACTURA';
$fechaInicio  = isset($_POST['fechai']) ? $_POST['fechai'] : '';
$fechaFin     = isset($_POST['fechaf']) ? $_POST['fechaf'] : '';

if (!$fechaInicio || !$fechaFin) {
    die("Faltan fechas.");
}

$fechaInicio_f = date("d-m-Y", strtotime($fechaInicio));
$fechaFin_f    = date("d-m-Y", strtotime($fechaFin));

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}

// Detectar si existe MultiEmisor (misma lógica que el original)
$existe = 0;
$sqlmulti = "SHOW COLUMNS FROM ".$prefijobd."systemsettings LIKE 'MultiEmisor'";
$resm = mysqli_query($cnx_cfdi3, $sqlmulti);
while($rowm = mysqli_fetch_assoc($resm)){
    $existe++;
}

// Datos del emisor (mismo que en el original)
$sqlEmisor = "SELECT RFC, RazonSocial FROM {$prefijobd}SystemSettings";
$resSQL0 = $cnx_cfdi3->prepare($sqlEmisor);
if (!$resSQL0) {
    die("Error en la preparación de la consulta: " . $cnx_cfdi3->error);
}
if (!$resSQL0->execute()) {
    $mensaje  = 'Consulta no válida: ' . $resSQL0->error . "\n";
    die($mensaje);
}
$resSQL0->store_result();
$resSQL0->bind_result($rfcemisor, $rsocialemisor);
$resSQL0->fetch();

// Arrays para front
$columns = array();
$rows    = array();
$totalPrincipal = 0;
$titulo = '';

/********************************************************
 * FACTURAS
 ********************************************************/
if ($tipoReporte == 'FACTURA') {
    $titulo = 'Detalle de Facturas';

    $columns = array(
        'XFOLIO',
        'SERIE',
        'FOLIO',
        'SUBTOTAL',
        'RETENCIÓN',
        'IVA',
        'TOTAL',
        'MONEDA',
        'UUID',
        'FECHA EMISIÓN',
        'FECHA TIMBRADO',
        'RFC EMISOR',
        'RAZÓN SOCIAL EMISOR',
        'RFC RECEPTOR',
        'RAZÓN SOCIAL RECEPTOR',
        'ID CLIENTE',
        'ESTATUS SAT',
        'FORMA PAGO',
        'MÉTODO PAGO',
        'USO CFDI'
    );

    $sqlFacturas = "SELECT 
        a.ID,
        a.xFolio, 
        a.cfdserie as Serie, 
        a.cfdfolio as Folio, 
        a.zSubtotal as Subtotal, 
        a.zRetenido as Retencion, 
        a.zImpuesto as Impuesto, 
        a.zTotal as Total, 
        a.Moneda, 
        a.cfdiuuid as UUID, 
        a.Creado as FechaEmision, 
        a.cfdifechaTimbrado as FechaTimbrado, 
        b.RFC as RFCReceptor,
        b.razonsocial as RazonSocialReceptor, 
        a.ID as IDCliente, 
        CONCAT(c.ID2, ' - ', c.Descripcion) as MetodoPago, 
        CONCAT(d.ID2, ' - ', d.Descripcion) as UsoCFDI, 
        CONCAT(e.ID2, ' - ', e.Descripcion) as FormaPago 
        FROM {$prefijobd}factura a 
        INNER JOIN {$prefijobd}clientes b ON a.CargoAFactura_RID = b.ID 
        INNER JOIN {$prefijobd}tablageneral c ON a.metodopago33_RID = c.ID 
        INNER JOIN {$prefijobd}tablageneral d ON a.usocfdi33_RID = d.ID
        INNER JOIN {$prefijobd}tablageneral e ON a.formapago33_RID = e.ID
        WHERE a.cfdiuuid IS NOT NULL AND
        Date(a.Creado) BETWEEN ? AND ? ORDER BY a.XFolio";

    $resSQL1 = $cnx_cfdi3->prepare($sqlFacturas);
    if (!$resSQL1) {
        die("Error en la consulta: " . $cnx_cfdi3->error);
    }

    $resSQL1->bind_param('ss', $fechaInicio, $fechaFin);

    if (!$resSQL1->execute()) {
        die("Error al ejecutar la consulta: " . $resSQL1->error);
    }

    $resSQL1->bind_result(
        $idfactura,
        $xfolio,
        $serie,
        $folio,
        $subtotal,
        $retencion,
        $impuesto,
        $total,
        $moneda,
        $uuid,
        $fechaemision, 
        $fechatimbrado,
        $rfcreceptor,
        $rsocialreceptor, 
        $idcliente, 
        $metodopago, 
        $usocfdi, 
        $formapago 
    );

    while ($resSQL1->fetch()) {

        // ==== MISMO SOAP QUE EN EL ORIGINAL ====
        if ($existe>0){
            // En tu código original no hacías nada aquí; lo respetamos.
        }

        $soap ='<soapenv:Envelope
        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
            <soapenv:Body>
                <tem:Consulta>
                    <tem:expresionImpresa>?re='.$rfcemisor.'&amp;rr='.$rfcreceptor.'&amp;tt='.$total.'&amp;id='.$uuid.'</tem:expresionImpresa>
                </tem:Consulta>
            </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            'Content-Type: text/xml;charset=utf-8',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'Content-length: '.strlen($soap)
        );

        $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($res);
        $data = $xml->children('s', true)->children('', true)->children('', true);
        $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
        $obj = json_decode($data);
        $estado = $obj->{'Estado'};

        $totalPrincipal += $total;

        $rows[] = array(
            $xfolio,
            $serie,
            $folio,
            number_format($subtotal,2),
            number_format($retencion,2),
            number_format($impuesto,2),
            number_format($total,2),
            $moneda,
            $uuid,
            $fechaemision,
            $fechatimbrado,
            $rfcemisor,
            $rsocialemisor,
            $rfcreceptor,
            $rsocialreceptor,
            $idcliente,
            $estado,
            $formapago,
            $metodopago,
            $usocfdi
        );
    }

    $resSQL1->free_result();
    $resSQL1->close();
}

/********************************************************
 * REPS
 ********************************************************/
if ($tipoReporte == 'REP') {
    $titulo = 'Detalle de REPs';

    $columns = array(
        'XFOLIO REP',
        'SERIE REP',
        'FOLIO REP',
        'UUID REP',
        'RFC EMISOR',
        'EMISOR',
        'RFC RECEPTOR',
        'RECEPTOR',
        'FECHA EMISIÓN',
        'FECHA TIMBRADO',
        'SUBTOTAL REP',
        'RETENCIÓN REP',
        'IMPUESTO REP',
        'IMPORTE REP',
        'MÉTODO PAGO',
        'FORMA PAGO',
        'USO CFDI',
        'NO PARCIALIDAD',
        'FACTURA',
        'UUID FACTURA',
        'SUBTOTAL FACTURA',
        'RETENCIÓN FACTURA',
        'IMPUESTO FACTURA',
        'TOTAL FACTURA',
        'SALDO ANTERIOR',
        'IMPORTE PAGADO',
        'SALDO INSOLUTO',
        'ESTADO SAT'
    );

    $sqlAbonos = "Select 
        b.XFolio as xFolioREP,
        b.cfdserie as SerieREP,
        b.cfdfolio as FolioREP,
        b.cfdiuuid as UUID_REP,
        b.Fecha as FechaEmision,
        b.cfdifechaTimbrado as FechaTimbrado,
        CONCAT(d.id2, ' - ', d.Descripcion) as MetodoPago,
        CONCAT(e.id2, ' - ', e.Descripcion) as FormaPago,
        CONCAT(f.id2, ' - ', f.Descripcion) as UsoCFDI,
        a.NumParcialidad as NoParcialidad,
        g.RazonSocial,
        g.RFC,
        c.Xfolio as Factura,
        c.cfdiuuid as UUIDFactura,
        b.TotalSubtotal as SubtotalREP,
        b.TotalRetencion as RetencionREP,
        b.TotalIVA as ImpuestoREP,
        b.TotalImporte as ImporteREP, 
        c.zsubtotal As SubtotalFactura,
        c.zRetenido as RetencionFactura,
        c.zImpuesto as ImpuestoFactura,
        c.ztotal as TotalFactura,
        IF(c.cobranzasaldo=0, SUM(c.cobranzasaldo) + SUM(a.ImporteTemp) + SUM(a.Saldo), SUM(c.cobranzasaldo) + SUM(a.ImporteTemp)) as SaldoAnterior,
        SUM(a.ImporteTemp) as ImportePagado,
        c.cobranzasaldo as SaldoInsoluto
        FROM {$prefijobd}abonossub a
        Inner Join {$prefijobd}abonos b on a.FolioSub_RID = b.ID
        Inner Join {$prefijobd}factura c on a.AbonoFactura_RID = c.ID	
        Left Join {$prefijobd}tablageneral d on b.metodopago33_RID = d.ID	
        Left join {$prefijobd}tablageneral e on b.formapago33_RID = e.id
        Left join {$prefijobd}tablageneral f on b.usocfdi33_RID = f.ID
        Left Join {$prefijobd}Clientes g on b.Cliente_RID = g.ID
        Inner Join {$prefijobd}Oficinas h on b.Oficina_RID = h.ID
        Where b.Fecha Between ? And ? And b.cfdiuuid Is Not Null And h.EsPagT=1 
        Group By b.XFolio, c.Xfolio Order By b.xFolio";

    $resSQL1 = $cnx_cfdi3->prepare($sqlAbonos);
    if (!$resSQL1) {
        die("Error en la consulta: " . $cnx_cfdi3->error);
    }

    $resSQL1->bind_param('ss', $fechaInicio, $fechaFin);

    if (!$resSQL1->execute()) {
        die("Error al ejecutar la consulta: " . $resSQL1->error);
    }

    $resSQL1->bind_result(
        $xfoliorep,
        $serierep,
        $foliorep,
        $uuidrep,
        $fechaemision,
        $fechatimbrado,
        $metodopago, 
        $formapago,
        $usoCFDI,
        $noparcialidad,
        $rsocialreceptor,
        $rfcreceptor,
        $factura,
        $uuidfactura,
        $subtotalrep,
        $retencionrep,
        $impuestorep,
        $importerep,
        $subtotalfactura,
        $retencionfactura,
        $impuestofactura, 
        $totalfactura, 
        $saldoanterior,
        $importepagado,
        $saldoinsoluto
    );

    while ($resSQL1->fetch()) {

        // === MISMO SOAP QUE EL ORIGINAL PARA REP (importe = 0) ===
        $importecdp = '0';
        $soap ='<soapenv:Envelope
        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
            <soapenv:Body>
                <tem:Consulta>
                    <tem:expresionImpresa>?re='.$rfcemisor.'&amp;rr='.$rfcreceptor.'&amp;tt='.$importecdp.'&amp;id='.$uuidrep.'</tem:expresionImpresa>
                </tem:Consulta>
            </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            'Content-Type: text/xml;charset=utf-8',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'Content-length: '.strlen($soap)
        );

        $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($res);
        $data = $xml->children('s', true)->children('', true)->children('', true);
        $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
        $obj = json_decode($data);
        $estado = $obj->{'Estado'};

        $totalPrincipal += $importerep;

        $rows[] = array(
            $xfoliorep,
            $serierep,
            $foliorep,
            $uuidrep,
            $rfcemisor,
            $rsocialemisor,
            $rfcreceptor,
            $rsocialreceptor,
            $fechaemision,
            $fechatimbrado,
            number_format($subtotalrep,2),
            number_format($retencionrep,2),
            number_format($impuestorep,2),
            number_format($importerep,2),
            $metodopago,
            $formapago,
            $usoCFDI,
            $noparcialidad,
            $factura,
            $uuidfactura,
            number_format($subtotalfactura,2),
            number_format($retencionfactura,2),
            number_format($impuestofactura,2),
            number_format($totalfactura,2),
            number_format($saldoanterior,2),
            number_format($importepagado,2),
            number_format($saldoinsoluto,2),
            $estado
        );
    }

    $resSQL1->free_result();
    $resSQL1->close();
}

/********************************************************
 * NOTAS DE CRÉDITO
 ********************************************************/
if ($tipoReporte == 'NOTACREDITO') {
    $titulo = 'Detalle de Notas de Crédito';

    $columns = array(
        'XFOLIO',
        'SERIE',
        'FOLIO',
        'SUBTOTAL',
        'RETENCIÓN',
        'IMPUESTO',
        'TOTAL',
        'UUID',
        'FECHA EMISIÓN',
        'FECHA TIMBRADO',
        'RFC EMISOR',
        'EMISOR',
        'ID CLIENTE',
        'RFC RECEPTOR',
        'RECEPTOR',
        'MÉTODO PAGO',
        'FORMA PAGO',
        'USO CFDI',
        'ESTADO SAT'
    );

    $sqlAbonos = "Select 
        b.XFolio as xFolioNC,
        b.cfdserie as SerieNC,
        b.cfdfolio as FolioNC,
        b.cfdiuuid as UUID_NC,
        b.Fecha as FechaEmision,
        b.cfdifechaTimbrado as FechaTimbrado,
        CONCAT(d.id2, ' - ', d.Descripcion) as MetodoPago,
        CONCAT(e.id2, ' - ', e.Descripcion) as FormaPago,
        CONCAT(f.id2, ' - ', f.Descripcion) as UsoCFDI,
        g.RazonSocial,
        g.RFC,
        g.ID,
        b.TotalSubtotal as SubtotalNC,
        b.TotalRetencion as RetencionNC,
        b.TotalIVA as ImpuestoNC,
        b.TotalImporte as ImporteNC 
        FROM {$prefijobd}abonossub a
        Inner Join {$prefijobd}abonos b on a.FolioSub_RID = b.ID
        Inner Join {$prefijobd}factura c on a.AbonoFactura_RID = c.ID	
        Left Join {$prefijobd}tablageneral d on b.metodopago33_RID = d.ID	
        Left join {$prefijobd}tablageneral e on b.formapago33_RID = e.id
        Left join {$prefijobd}tablageneral f on b.usocfdi33_RID = f.ID
        Left Join {$prefijobd}Clientes g on b.Cliente_RID = g.ID
        Inner Join {$prefijobd}Oficinas h on b.Oficina_RID = h.ID
        Where b.Fecha Between ? And ? And b.cfdiuuid Is Not Null And h.EsNotC=1 
        Order By b.xFolio";

    $resSQL1 = $cnx_cfdi3->prepare($sqlAbonos);
    if (!$resSQL1) {
        die("Error en la consulta: " . $cnx_cfdi3->error);
    }

    $resSQL1->bind_param('ss', $fechaInicio, $fechaFin);

    if (!$resSQL1->execute()) {
        die("Error al ejecutar la consulta: " . $resSQL1->error);
    }

    $resSQL1->bind_result(
        $xfolionc,
        $serienc,
        $folionc,
        $uuidnc,
        $fechaemision,
        $fechatimbrado,
        $metodopago, 
        $formapago,
        $usoCFDI,
        $rsocialreceptor,
        $rfcreceptor,
        $idcliente,
        $subtotalnc,
        $retencionnc,
        $impuestonc,
        $importenc
    );

    while ($resSQL1->fetch()) {

        // === MISMO SOAP QUE ORIGINAL PARA NC ===
        $soap ='<soapenv:Envelope
        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
            <soapenv:Body>
                <tem:Consulta>
                    <tem:expresionImpresa>?re='.$rfcemisor.'&amp;rr='.$rfcreceptor.'&amp;tt='.$importenc.'&amp;id='.$uuidnc.'</tem:expresionImpresa>
                </tem:Consulta>
            </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            'Content-Type: text/xml;charset=utf-8',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'Content-length: '.strlen($soap)
        );

        $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($res);
        $data = $xml->children('s', true)->children('', true)->children('', true);
        $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
        $obj = json_decode($data);
        $estado = $obj->{'Estado'};

        $totalPrincipal += $importenc;

        $rows[] = array(
            $xfolionc,
            $serienc,
            $folionc,
            number_format($subtotalnc,2),
            number_format($retencionnc,2),
            number_format($impuestonc,2),
            number_format($importenc,2),
            $uuidnc,
            $fechaemision,
            $fechatimbrado,
            $rfcemisor,
            $rsocialemisor,
            $idcliente,
            $rfcreceptor,
            $rsocialreceptor,
            $metodopago,
            $formapago,
            $usoCFDI,
            $estado
        );
    }

    $resSQL1->free_result();
    $resSQL1->close();
}

$resSQL0->free_result();
$resSQL0->close();
$cnx_cfdi3->close();

$registros = count($rows);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Reportes Generales</title>
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
      --header-bg: rgba(221,221,221,0.72);
    }
    html[data-theme="dark"]{
      --bg:#0b0c0f;
      --panel:#0f1218cc;
      --text:#f5f7fb;
      --text-soft:#a6aec2;
      --tint:#0a84ff;
      --shadow: 0 8px 24px rgba(0,0,0,.35);
      --border: 1px solid rgba(255,255,255,.06);
      --row-bg:#11141d;
      --row-hover:#1a2030;
      --header-bg: rgba(20,24,36,.7);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .app-shell{
      max-width:1280px;
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
      font-size:1.6rem;
      font-weight:700;
      letter-spacing:-0.4px;
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
      overflow:hidden;
    }
    .panel-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:12px 16px 8px;
      border-bottom:var(--border);
      gap:10px;
      flex-wrap:wrap;
      background:linear-gradient(180deg,rgba(255,255,255,.9),rgba(255,255,255,.4));
    }
    html[data-theme="dark"] .panel-header{
      background:linear-gradient(180deg,rgba(20,24,36,.95),rgba(15,18,26,.8));
    }
    .panel-header-left{
      font-size:0.85rem;
      color:var(--text-soft);
    }
    .panel-header-left strong{
      color:var(--text);
    }
    .panel-header-right{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
    }
    .btn-sm{
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      padding:6px 12px;
      font-size:0.8rem;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:6px;
      color:var(--text);
      text-decoration:none;
    }
    .btn-sm:hover{ background:var(--row-hover); }
    .btn-primary{
      border:none;
      padding:6px 14px;
      border-radius:999px;
      font-weight:600;
      background:linear-gradient(180deg,var(--tint),#0051b8);
      color:#fff;
      cursor:pointer;
      font-size:0.85rem;
      box-shadow:0 4px 12px rgba(0,122,255,.25);
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .btn-primary:hover{ transform:translateY(-1px); opacity:.96; }

    .table-container{
      max-height:620px;
      overflow-y:auto;
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
      z-index:5;
      background:var(--header-bg);
      font-weight:600;
      padding:8px 6px;
      text-align:center;
      color:var(--text-soft);
      border-bottom:var(--border);
      backdrop-filter:blur(10px);
      white-space:nowrap;
    }
    tbody td{
      padding:8px 6px;
      border-bottom:1px solid rgba(0,0,0,.04);
      background:var(--row-bg);
      text-align:left;
      white-space:nowrap;
    }
    tbody tr:hover td{ background:var(--row-hover); }
    td.num{
      text-align:right;
      font-variant-numeric:tabular-nums;
    }
    .pagination{
      display:flex;
      justify-content:center;
      gap:6px;
      padding:10px 10px 14px;
      flex-wrap:wrap;
      border-top:var(--border);
      background:linear-gradient(180deg,rgba(255,255,255,.7),rgba(255,255,255,.9));
    }
    html[data-theme="dark"] .pagination{
      background:linear-gradient(180deg,rgba(15,18,26,.95),rgba(10,12,18,.9));
    }
    .pagination button{
      min-width:32px;
      padding:4px 8px;
      border-radius:999px;
      border:var(--border);
      background:var(--panel);
      cursor:pointer;
      font-size:0.8rem;
      color:var(--text);
    }
    .pagination button.active{
      background:var(--tint);
      color:#fff;
      border:none;
    }
    .pagination button:hover{ background:var(--row-hover); }
    .pagination button.disabled{
      opacity:.4;
      cursor:default;
    }

    @media (max-width:900px){
      .app-shell{ margin:20px auto; padding:12px; }
      table{ font-size:0.78rem; }
    }
    @media (max-width:600px){
      .app-header{ flex-direction:column; }
      .panel-header{ flex-direction:column; align-items:flex-start; }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <div class="app-header">
    <div>
      <div class="app-title"><?php echo htmlspecialchars($titulo); ?></div>
      <div class="app-subtitle">
        Periodo: <?php echo htmlspecialchars($fechaInicio_f); ?> a <?php echo htmlspecialchars($fechaFin_f); ?>
      </div>
      <div class="badge">
        <?php
          if ($tipoReporte == 'FACTURA') echo 'Facturas';
          elseif ($tipoReporte == 'REP') echo 'REPs';
          else echo 'Notas de crédito';
        ?> · <?php echo $registros; ?> registros
      </div>
    </div>
    <button id="themeToggle" class="btn theme-toggle" aria-label="Cambiar tema">
      <span class="sun">☀️ Claro</span>
      <span class="moon" style="display:none;">🌙 Oscuro</span>
    </button>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-header-left">
        <strong><?php echo $registros; ?></strong> registros · 
        Total importe: <strong>$<?php echo number_format($totalPrincipal,2); ?></strong>
      </div>
      <div class="panel-header-right">
        <!-- Exportar a Excel usando EL MISMO SCRIPT ORIGINAL -->
        <form method="post" action="reportes_generales.php" target="_blank" id="formExcel">
          <input type="hidden" name="prefijodb" value="<?php echo htmlspecialchars($prefijobd); ?>">
          <input type="hidden" name="tiporeporte" value="<?php echo htmlspecialchars($tipoReporte); ?>">
          <input type="hidden" name="fechai" value="<?php echo htmlspecialchars($fechaInicio); ?>">
          <input type="hidden" name="fechaf" value="<?php echo htmlspecialchars($fechaFin); ?>">
          <button type="submit" class="btn-primary">⬇️ Exportar a Excel</button>
        </form>
        <a class="btn-sm"
           href="reportes_generales_fechas.php?prefijodb=<?php echo urlencode(rtrim($prefijobd,'_')); ?>">
          ⬅︎ Cambiar filtros
        </a>
      </div>
    </div>

    <div class="table-container">
      <table id="tablaReporte">
        <thead>
          <tr>
            <?php foreach($columns as $col): ?>
              <th><?php echo htmlspecialchars($col); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
            <tr>
              <?php foreach($row as $cell): ?>
                <td><?php echo htmlspecialchars($cell); ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination" id="pagination"></div>
  </div>
</div>

<script>
// Tema global
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
    if (btn) btn.style.display = 'none';
  }

  window.addEventListener('storage', function(e){
    if(e.key === key && (e.newValue === 'light' || e.newValue === 'dark')){
      root.setAttribute('data-theme', e.newValue);
      syncIcons();
    }
  });
})();

// Paginación 10 en 10
(function(){
  var table = document.getElementById('tablaReporte');
  if(!table) return;
  var tbody = table.querySelector('tbody');
  var rows  = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
  var perPage = 10;
  var currentPage = 1;
  var totalPages = Math.ceil(rows.length / perPage) || 1;
  var pagContainer = document.getElementById('pagination');

  function renderPage(page){
    currentPage = page;
    var start = (page - 1) * perPage;
    var end   = start + perPage;

    for(var i=0;i<rows.length;i++){
      if(i >= start && i < end){
        rows[i].style.display = '';
      }else{
        rows[i].style.display = 'none';
      }
    }
    renderPagination();
  }

  function createBtn(label, page, disabled, active){
    var btn = document.createElement('button');
    btn.textContent = label;
    if(disabled) btn.classList.add('disabled');
    if(active)   btn.classList.add('active');
    btn.disabled = !!disabled;
    if(!disabled){
      btn.addEventListener('click', function(){
        renderPage(page);
      });
    }
    return btn;
  }

  function renderPagination(){
    pagContainer.innerHTML = '';
    if(totalPages <= 1) return;

    pagContainer.appendChild(
      createBtn('‹', currentPage-1, currentPage === 1, false)
    );

    for(var p=1;p<=totalPages;p++){
      pagContainer.appendChild(
        createBtn(String(p), p, false, p === currentPage)
      );
    }

    pagContainer.appendChild(
      createBtn('›', currentPage+1, currentPage === totalPages, false)
    );
  }

  renderPage(1);
})();
</script>
</body>
</html>
