<?php
set_time_limit(3000);
error_reporting(0);

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos.');
}


$prefijobd   = isset($_GET['prefijodb']) ? $_GET['prefijodb'] : '';
$prefijobd   = trim($prefijobd);                   
$prefijobd   = preg_replace('/[^A-Za-z0-9_]/','', $prefijobd); 

// Asegura que termina en guion bajo
if ($prefijobd !== '' && substr($prefijobd, -1) !== '_') {
    $prefijobd .= '_';
}

$tipoReporte = isset($_GET['tiporeporte']) ? $_GET['tiporeporte'] : 'FACTURA';
$fechaInicio = isset($_GET["fechai"]) ? $_GET["fechai"] : '';
$fechaInicio_f = $fechaInicio ? date("d-m-Y", strtotime($fechaInicio)) : '';
$fechaFin    = isset($_GET["fechaf"]) ? $_GET["fechaf"] : '';
$fechaFin_f  = $fechaFin ? date("d-m-Y", strtotime($fechaFin)) : '';

// multi para despues
$existe = 0;
$sqlmulti = "SHOW COLUMNS FROM `{$prefijobd}systemsettings` LIKE 'MultiEmisor'";
$resm = mysqli_query($cnx_cfdi3, $sqlmulti);
if ($resm) {
    while($rowm = mysqli_fetch_assoc($resm)){
        $existe++;
    }
}

//Datos del emisor
$sqlEmisor = "SELECT RFC, RazonSocial FROM `{$prefijobd}systemsettings`";
$resSQL0 = $cnx_cfdi3->prepare($sqlEmisor);
if (!$resSQL0) {
    die("Error en la preparacion de la consulta: " . $cnx_cfdi3->error);
}

if (!$resSQL0->execute()) {
    $mensaje  = 'Consulta no valida: ' . $resSQL0->error . "\n";
    die($mensaje);
}

$resSQL0->store_result();
$resSQL0->bind_result($rfcemisor, $rsocialemisor);
$resSQL0->fetch();


// FACTURAS
if ($tipoReporte == 'FACTURA'){
    header("Content-type: application/vnd.ms-excel");
    $nombre="general_facturas_".date("d-m-Y")."_".date("h:i:s").".xls";
    header("Content-Disposition: attachment; filename=$nombre");
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
    <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
        <thead>
            <tr>
                <th align="center" colspan="20" style="font-size: 20px;">
                    Detalle de Facturas. Periodo: <?php echo $fechaInicio_f."-".$fechaFin_f; ?>
                </th>
            </tr>
            <tr>
                <th align="center" style="font-size: 16px;">XFOLIO</th>
                <th align="center" style="font-size: 16px;">SERIE</th>
                <th align="center" style="font-size: 16px;">FOLIO</th>
                <th align="center" style="font-size: 16px;">SUBTOTAL</th>
                <th align="center" style="font-size: 16px;">RETENCION</th>
                <th align="center" style="font-size: 16px;">IVA</th>
                <th align="center" style="font-size: 16px;">TOTAL</th>
                <th align="center" style="font-size: 16px;">MONEDA</th>
                <th align="center" style="font-size: 16px;">UUID</th>
                <th align="center" style="font-size: 16px;">FECHA EMISION</th>
                <th align="center" style="font-size: 16px;">FECHA TIMBRADO</th>
                <th align="center" style="font-size: 16px;">RFC EMISOR</th>
                <th align="center" style="font-size: 16px;">RAZON SOCIAL EMISOR</th>
                <th align="center" style="font-size: 16px;">RFC RECEPTOR</th>
                <th align="center" style="font-size: 16px;">RAZON SOCIAL RECEPTOR</th>
                <th align="center" style="font-size: 16px;">ID CLIENTE</th>
                <th align="center" style="font-size: 16px;">ESTATUS SAT</th>
                <th align="center" style="font-size: 16px;">FORMA PAGO</th>
                <th align="center" style="font-size: 16px;">METODO PAGO</th>
                <th align="center" style="font-size: 16px;">USO CFDI</th>
            </tr>
        </thead>
        <tbody>
    <?php
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
        ?>
        <tr>
            <td align="center" style="font-size: 15px;"><?php echo $xfolio; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $serie; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $folio; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($subtotal,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($retencion,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($impuesto,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($total,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $moneda; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $uuid; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechaemision; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechatimbrado; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $idcliente; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $estado; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $formapago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $metodopago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $usocfdi; ?></td>
        </tr>
        <?php
    }
    $resSQL1->free_result();
    $resSQL1->close();
    $resSQL0->free_result();
    $resSQL0->close();
    $cnx_cfdi3->close();
    ?>
        </tbody>
    </table>
    <?php
    exit;
}

// REPS
if ($tipoReporte == 'REP'){
    header("Content-type: application/vnd.ms-excel");
    $nombre="general_reps_".date("d-m-Y")."_".date("h:i:s").".xls";
    header("Content-Disposition: attachment; filename=$nombre");
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
    <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
        <thead>
            <tr>
                <th align="center" colspan="27" style="font-size: 20px;">
                    Detalle de REPS. Periodo: <?php echo $fechaInicio_f."-".$fechaFin_f; ?>
                </th>
            </tr>
            <tr>
                <th align="center" style="font-size: 16px;">XFOLIOREP</th>
                <th align="center" style="font-size: 16px;">SERIEREP</th>
                <th align="center" style="font-size: 16px;">FOLIOREP</th>
                <th align="center" style="font-size: 16px;">UUIDREP</th>
                <th align="center" style="font-size: 16px;">RFCEMISOR</th>
                <th align="center" style="font-size: 16px;">EMISOR</th>
                <th align="center" style="font-size: 16px;">RFCRECEPTOR</th>
                <th align="center" style="font-size: 16px;">RECEPTOR</th>
                <th align="center" style="font-size: 16px;">FECHAEMISION</th>
                <th align="center" style="font-size: 16px;">FECHATIMBRADO</th>
                <th align="center" style="font-size: 16px;">SUBTOTALREP</th>
                <th align="center" style="font-size: 16px;">RETENCIONREP</th>
                <th align="center" style="font-size: 16px;">IMPUESTOREP</th>
                <th align="center" style="font-size: 16px;">IMPORTEREP</th>
                <th align="center" style="font-size: 16px;">METODOPAGO</th>
                <th align="center" style="font-size: 16px;">FORMAPAGO</th>
                <th align="center" style="font-size: 16px;">USOCFDI</th>
                <th align="center" style="font-size: 16px;">NOPARCIALIDAD</th>
                <th align="center" style="font-size: 16px;">FACTURA</th>
                <th align="center" style="font-size: 16px;">UUIDFACTURA</th>
                <th align="center" style="font-size: 16px;">SUBTOTALFACTURA</th>
                <th align="center" style="font-size: 16px;">RETENCIONFACTURA</th>
                <th align="center" style="font-size: 16px;">IMPUESTOFACTURA</th>
                <th align="center" style="font-size: 16px;">TOTALFACTURA</th>
                <th align="center" style="font-size: 16px;">SALDOANTERIOR</th>
                <th align="center" style="font-size: 16px;">IMPORTEPAGADO</th>
                <th align="center" style="font-size: 16px;">SALDOINSOLUTO</th>
                <th align="center" style="font-size: 16px;">ESTADOSAT</th>
            </tr>
        </thead>
        <tbody>
    <?php
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
        ?>
        <tr>
            <td align="center" style="font-size: 15px;"><?php echo $xfoliorep; ?></td>
            <td align="center" style="font-size: 15px;"><?php echo $serierep; ?></td>
            <td align="center" style="font-size: 15px;"><?php echo $foliorep; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $uuidrep; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechaemision; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechatimbrado; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($subtotalrep,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($retencionrep,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($impuestorep,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($importerep,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $metodopago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $formapago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $usoCFDI; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $noparcialidad; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $factura; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $uuidfactura; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($subtotalfactura,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($retencionfactura,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($impuestofactura,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($totalfactura,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($saldoanterior,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($importepagado,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($saldoinsoluto,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $estado; ?></td>
        </tr>
        <?php
    }
    $resSQL1->free_result();
    $resSQL1->close();
    $resSQL0->free_result();
    $resSQL0->close();
    $cnx_cfdi3->close();
    ?>
        </tbody>
    </table>
    <?php
    exit;
}

// NOTAS DE CRÉDITO
if ($tipoReporte == 'NOTACREDITO'){
    header("Content-type: application/vnd.ms-excel");
    $nombre="general_ncs_".date("d-m-Y")."_".date("h:i:s").".xls";
    header("Content-Disposition: attachment; filename=$nombre");
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
    <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
        <thead>
            <tr>
                <th align="center" colspan="18" style="font-size: 20px;">
                    Detalle de Notas de Crédito. Periodo: <?php echo $fechaInicio_f."-".$fechaFin_f; ?>
                </th>
            </tr>
            <tr>
                <th align="center" style="font-size: 16px;">XFOLIO</th>
                <th align="center" style="font-size: 16px;">SERIE</th>
                <th align="center" style="font-size: 16px;">FOLIO</th>
                <th align="center" style="font-size: 16px;">SUBTOTAL</th>
                <th align="center" style="font-size: 16px;">RETENCION</th>
                <th align="center" style="font-size: 16px;">IMPUESTO</th>
                <th align="center" style="font-size: 16px;">TOTAL</th>
                <th align="center" style="font-size: 16px;">UUID</th>
                <th align="center" style="font-size: 16px;">FECHAEMISION</th>
                <th align="center" style="font-size: 16px;">FECHATIMBRADO</th>
                <th align="center" style="font-size: 16px;">RFCEMISOR</th>
                <th align="center" style="font-size: 16px;">EMISOR</th>
                <th align="center" style="font-size: 16px;">IDCLIENTE</th>
                <th align="center" style="font-size: 16px;">RFCRECEPTOR</th>
                <th align="center" style="font-size: 16px;">RECEPTOR</th>
                <th align="center" style="font-size: 16px;">METODOPAGO</th>
                <th align="center" style="font-size: 16px;">FORMAPAGO</th>
                <th align="center" style="font-size: 16px;">USOCFDI</th>
                <th align="center" style="font-size: 16px;">ESTADOSAT</th>
            </tr>
        </thead>
        <tbody>
    <?php
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
        ?>
        <tr>
            <td align="center" style="font-size: 15px;"><?php echo $xfolionc; ?></td>
            <td align="center" style="font-size: 15px;"><?php echo $serienc; ?></td>
            <td align="center" style="font-size: 15px;"><?php echo $folionc; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($subtotalnc,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($retencionnc,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($impuestonc,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo number_format($importenc,2); ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $uuidnc; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechaemision; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $fechatimbrado; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialemisor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $idcliente; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rfcreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $rsocialreceptor; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $metodopago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $formapago; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $usoCFDI; ?></td>
            <td align="left" style="font-size: 15px;"><?php echo $estado; ?></td>
        </tr>
        <?php
    }
    $resSQL1->free_result();
    $resSQL1->close();
    $resSQL0->free_result();
    $resSQL0->close();
    $cnx_cfdi3->close();
    ?>
        </tbody>
    </table>
    <?php
    exit;
}
