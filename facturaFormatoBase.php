<?php  
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución

require 'phpqrcode/qrlib.php';
require_once __DIR__ . '/vendor/autoload.php';
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if(!isset($_GET['tipo']) || empty($_GET['tipo'])){
	$tipoArchivo = 'dwld';
}else {
	$tipoArchivo = $_GET['tipo'];
}


require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$id_factura = $_GET["id"];
/* $id_factura = 9790989; */


//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

//$prefijobd = 'prueba_';
$prefijo = rtrim($prefijobd, "_");

//require_once('lib_mpdf/pdf/mpdf.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

//Va a dictar cuantos decimales lleva el documento
$parametro_decim = 930;
$resSQL930 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_decim";
$runSQL930 = mysqli_query($cnx_cfdi2, $resSQL930);
$rowSQL930 = mysqli_fetch_array($runSQL930);
	 
if (!$rowSQL930) {
	$numDecimales = 2;
} else {
	$llevaMasDecim= $rowSQL930['VLOGI'];
	$numDecimales= intval($rowSQL930 ['VCHAR']);
}

//parametro regfiscal
$parametro_rf = 150;
$resSQL150 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_rf";
$runSQL150 = mysqli_query($cnx_cfdi2, $resSQL150);
$rowSQL150 = mysqli_fetch_array($runSQL150);
	 
if (!$rowSQL150) {
	$regPorParametro = false;
} else {
	$regPorParametro = $rowSQL150['VLOGI'];
	if ($regPorParametro === '1') {
		$regPorParametro = true;
	}
}







$anio_logs = date('Y');
$mes_2 = date('m');
$dia_logs = date('d');

////////////////Agregar nombre del Mes

////////////////// Funcion Numeros a letra

function unidad($numero) {
    $numeros = [
        1 => "UNO",
        2 => "DOS",
        3 => "TRES",
        4 => "CUATRO",
        5 => "CINCO",
        6 => "SEIS",
        7 => "SIETE",
        8 => "OCHO",
        9 => "NUEVE"
    ];
    return isset($numeros[$numero]) ? $numeros[$numero] : '';
}

function decena($numero) {
    $especiales = [
        10 => "DIEZ",
        11 => "ONCE",
        12 => "DOCE",
        13 => "TRECE",
        14 => "CATORCE",
        15 => "QUINCE",
        16 => "DIECISÉIS",
        17 => "DIECISIETE",
        18 => "DIECIOCHO",
        19 => "DIECINUEVE"
    ];

    $decenas = [
        2 => "VEINTE",
        3 => "TREINTA",
        4 => "CUARENTA",
        5 => "CINCUENTA",
        6 => "SESENTA",
        7 => "SETENTA",
        8 => "OCHENTA",
        9 => "NOVENTA"
    ];

    if ($numero < 10) return unidad($numero);
    if (isset($especiales[$numero])) return $especiales[$numero];

    $decena = floor($numero / 10);
    $unidad = $numero % 10;

    if ($numero >= 21 && $numero <= 29) {
        return "VEINTI" . unidad($unidad);
    }

    return isset($decenas[$decena]) ? $decenas[$decena] . ($unidad ? " Y " . unidad($unidad) : "") : '';
}

function centena($numero) {
    $centenas = [
        1 => "CIENTO",
        2 => "DOSCIENTOS",
        3 => "TRESCIENTOS",
        4 => "CUATROCIENTOS",
        5 => "QUINIENTOS",
        6 => "SEISCIENTOS",
        7 => "SETECIENTOS",
        8 => "OCHOCIENTOS",
        9 => "NOVECIENTOS"
    ];

    if ($numero == 100) return "CIEN";
    if ($numero < 100) return decena($numero);

    $centena = floor($numero / 100);
    $resto = $numero % 100;

    return isset($centenas[$centena]) ? $centenas[$centena] . ($resto ? " " . decena($resto) : "") : '';
}

function miles($numero) {
    if ($numero < 1000) return centena($numero);

    $miles = floor($numero / 1000);
    $resto = $numero % 1000;

    $milTexto = $miles == 1 ? "MIL" : centena($miles) . " MIL";
    return trim($milTexto . ($resto ? " " . centena($resto) : ""));
}

function millones($numero) {
    if ($numero < 1000000) return miles($numero);

    $millones = floor($numero / 1000000);
    $resto = $numero % 1000000;

    $millonesTexto = $millones == 1 ? "UN MILLÓN" : miles($millones) . " MILLONES";
    return trim($millonesTexto . ($resto ? " " . miles($resto) : ""));
}

function convertir($numero, $f_moneda) {

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);

    
    $f_moneda = trim(strtoupper($f_moneda));
    $f_moneda = str_replace(["\t", "\n", "\r"], "", $f_moneda); 

   
    if ($f_moneda === "PESOS") {
        $monedaTexto = "PESOS";
		$moneda_nom = " M.N";
    } else {
        $monedaTexto = "DOLARES";
		$moneda_nom = " U.S.D";

    }

    return trim($numf) .' '.$monedaTexto.' '. $cents . '/100'. $moneda_nom;
}



//////////////////// FIN Funcion Numeros a letra
//Seleccionar Mes letra
$mes_logs = [
	1 => "Enero",
	2 => "Febrero",
	3 => "Marzo",
	4 => "Abril",
	5 => "Mayo",
	6 => "Junio",
	7 => "Julio",
	8 => "Agosto",
	9 => "Septiembre",
	10 => "Octubre",
	11 => "Noviembre",
	12 => "Diciembre"
]; 

$fecha = $dia_logs." de ".$mes_2." de ". $anio_logs;


$fecha2 = (is_array($anio_logs) ? implode("", $anio_logs) : $anio_logs) . "-" .
          (is_array($mes_logs) ? implode("", $mes_logs) : $mes_logs) . "-" .
          (is_array($dia_logs) ? implode("", $dia_logs) : $dia_logs);
#$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;

// multiemisor
$resSQL006 = "SELECT * FROM basdb.".$prefijobd."systemsettings";
	$runSQL006 = mysqli_query($cnx_cfdi2, $resSQL006);
	while($rowSQL006 = mysqli_fetch_array($runSQL006)){

		if (isset($rowSQL006['MultiEmisor'])){
			$Multi = $rowSQL006['MultiEmisor'];
		} else {
			$Multi = '0';
		}
		
	}
	$llevaemisorRID = ($Multi != 0) ? ' f.Emisor_RID, ':'';

 

//esta funcion hace que no truene el regimen fisccal y se pueda  tener en todas las versiones sin importar si tiene cRegimenFiscal_RID
function column_exists($cnx, $dbName, $tableName, $columnName){
	$dbNameSafe = mysqli_real_escape_string($cnx, $dbName);
    $tableSafe  = mysqli_real_escape_string($cnx, $tableName);
    $colSafe    = mysqli_real_escape_string($cnx, $columnName);

    $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '$dbNameSafe'
              AND TABLE_NAME = '$tableSafe'
              AND COLUMN_NAME = '$colSafe'
            LIMIT 1";
    $rs = mysqli_query($cnx, $sql);
    if (!$rs) return false;
    $ok = (mysqli_num_rows($rs) > 0);
    mysqli_free_result($rs);
    return $ok;
}

$tableClientes = $prefijobd . "clientes";

$hasRID    = column_exists($cnx_cfdi2, $database_cfdi, $tableClientes, "cRegimenFiscal_RID");
$hasReg    = column_exists($cnx_cfdi2, $database_cfdi, $tableClientes, "RegimenFiscal");
$hasRegDes = column_exists($cnx_cfdi2, $database_cfdi, $tableClientes, "RegimenFiscalDescripcion");


$selRegimenRID = $hasRID ? "cl.cRegimenFiscal_RID" : "0 AS cRegimenFiscal_RID";
$selRegimen    = $hasReg ? "cl.RegimenFiscal" : "'' AS RegimenFiscal";
$selRegimenDes = $hasRegDes ? "cl.RegimenFiscalDescripcion" : "'' AS RegimenFiscalDescripcion";

//correcion de ISR
$tableFactura = $prefijobd . "factura";
$hasISR = column_exists($cnx_cfdi2, $database_cfdi, $tableFactura, "ISRImporte");


$selISR = $hasISR ? "f.ISRImporte" : "0 AS ISRImporte";

//Buscar datos de la Factura 
$resSQL01 = "SELECT 
        f.cfdserie,
        f.cfdfolio,
        f.XFolio,
        f.Creado,
        f.Ticket,
        f.Moneda,
        f.zSubtotal,
        f.zImpuesto,
        f.zRetenido,
        f.zTotal,
        f.usocfdi33_RID,
        f.metodopago33_RID,
        f.formapago33_RID,
        f.CargoAFactura_RID,
        f.RemitenteLocalidad2_RID,
        f.CodigoOrigen,
        f.Remitente,
        f.RemitenteRFC,
        f.RemitenteCalle,
        f.RemitenteNumExt,
        f.RemitenteColonia_RID,
        f.RemitenteMunicipio_RID,
        f.RemitenteEstado_RID,
        f.RemitenteCodigoPostal,
        f.RemitentePais,
        f.RemitenteNumRegIdTrib,
        f.CitaCarga,
        f.RemitenteTelefono,
        f.DestinatarioLocalidad2_RID,
        f.CodigoDestino,
        f.Destinatario,
        f.ClaveUnidadPeso_RID,
        f.DestinatarioRFC,
        f.DestinatarioCalle,
        f.DestinatarioNumExt,
        f.DestinatarioColonia_RID,
        f.DestinatarioMunicipio_RID,
        f.DestinatarioEstado_RID,
        f.DestinatarioCodigoPostal,
        f.DestinatarioPais,
        f.DestinatarioNumRegIdTrib,
        f.DestinatarioCitaCarga,
        f.DestinatarioTelefono,
        f.Comentarios,
        f.Instrucciones,
        f.cfdnocertificado,
        f.cfdiuuid,
        f.DiasCredito,
        f.Vence,
        f.cfdinoCertificadoSAT,
        f.cfdfchhra,
        f.cfdifechaTimbrado,
        f.cfdsellodigital,
        f.cfdiselloSAT,
        f.cfdiselloCadenaOriginal,
        f.TipoViaje,
        f.PesoBrutoVehicular,
        f.PesoNeto,
        f.Unidad_RID,
        f.TipoCambio,
        f.cCanceladoT,
        {$llevaemisorRID}
        {$selISR},
        f.Aseguradora,
        f.Poliza,
        f.Remolque_RID,
        f.Remolque2_RID,
        f.Dolly_RID,
        f.DistanciaRecorrida,
        COALESCE(f.PermisionarioFact_RID, '') AS Permisionario_RID,
		f.Operador_RID,
        f.xPesoTotal,
        f.ComplementoTraslado,
        f.LlevaRepartos,
        COALESCE(f.cfdicbbarchivo, f.cfdicbbArchivo) AS cfdicbbArchivo,
        COALESCE(f.ConfigAutotranporte_RID, 0) AS ConfigAutotranporte_RID,
        f.IdCCP,
        f.Addendas,
        f.AddCampoA,
        f.RemitenteSeRecogera,
        f.DestinatarioSeEntregara,
        cl.RazonSocial AS clienteNombre,
        cl.Calle AS clienteCalle,
        cl.NumeroExterior AS clienteNumExt,
        cl.NumeroInterior AS clienteNumInt,
        cl.RFC AS clienteRFC,
        cl.CodigoPostal AS clienteCP,
        COALESCE(cco.NombreAsentamiento, '') AS clienteColonia,
        COALESCE(cmu.Descripcion, '') AS clienteMunicipio,
        COALESCE(clo.Descripcion, '') AS clienteLocalidad,
        COALESCE(ces.Estado, '') AS clienteEstado,
         {$selRegimenRID},
		{$selRegimen},
		 {$selRegimenDes},
        cup.ClaveUnidad,
        COALESCE(ucf.ID2, '') AS usocfdi33_ID2,
        COALESCE(ucf.Descripcion, '') AS usocfdi33_Descripcion,
        COALESCE(ump.ID2, '') AS metodopago33_ID2,
        COALESCE(ump.Descripcion, '') AS metodopago33_Descripcion,
        COALESCE(ufp.ID2, '') AS formapago33_ID2,
        COALESCE(ufp.Descripcion, '') AS formapago33_Descripcion,
        COALESCE(rlo.Descripcion, '') AS remitenteLocalidad,
        COALESCE(rco.NombreAsentamiento, '') AS remitenteColonia,
        COALESCE(rmu.Descripcion, '') AS remitenteMunicipio,
        COALESCE(res.Estado, '') AS remitenteEstado,
        COALESCE(dlo.Descripcion, '') AS destinatarioLocalidad,
        COALESCE(dco.NombreAsentamiento, '') AS destinatarioColonia,
        COALESCE(dmu.Descripcion, '') AS destinatarioMunicipio,
        COALESCE(dest.Estado, '') AS destinatarioEstado,
        un.Unidad AS unidad1Nombre,
        un.Polizano AS unidad1PolizaNo,
        un.Placas AS unidad1Placas,
        un.Ano AS unidad1Ano,
        un.PesoBrutoVehicular AS unidad1PesoBrutoVehicular,
        un.PermisoSCT AS unidad1PermisoSCT,
        un.TipoPermisoSCT AS unidad1TipoPermisoSCT,
        una.Aseguradora AS unidad1AseguradoraNombre,
        CASE 
            WHEN COALESCE(f.ConfigAutotranporte_RID, 0) > 0 THEN COALESCE(cf.Descripcion, '')
            ELSE COALESCE(unc.Descripcion,'')
        END AS unidad1ConfigAutotransporteDescripcion,

        CASE 
            WHEN COALESCE(f.ConfigAutotranporte_RID, 0) > 0 THEN COALESCE(cf.ClaveNomenclatura, '')
            ELSE COALESCE(unc.ClaveNomenclatura,'')
        END AS unidad1ConfigAutotransporteClaveNomenclatura
        
    FROM {$prefijobd}factura AS f 
    LEFT JOIN {$prefijobd}clientes AS cl ON f.CargoAFactura_RID = cl.ID
    LEFT JOIN {$prefijobd}estados AS ces ON cl.Estado_RID = ces.ID
    LEFT JOIN {$prefijobd}c_colonia AS cco ON cl.c_Colonia_RID = cco.ID 
    LEFT JOIN {$prefijobd}c_municipio AS cmu ON cl.c_Municipio_RID = cmu.ID 
    LEFT JOIN {$prefijobd}c_localidad AS clo ON cl.Localidad_RID = clo.ID 
    LEFT JOIN {$prefijobd}c_CLaveunidadPeso AS cup ON f.ClaveUnidadPeso_RID = cup.ID
    LEFT JOIN {$prefijobd}tablageneral AS ucf ON f.usocfdi33_RID = ucf.ID 
    LEFT JOIN {$prefijobd}tablageneral AS ump ON f.metodopago33_RID = ump.ID 
    LEFT JOIN {$prefijobd}tablageneral AS ufp ON f.formapago33_RID = ufp.ID 
    LEFT JOIN {$prefijobd}c_localidad  AS rlo ON f.RemitenteLocalidad2_RID = rlo.ID
    LEFT JOIN {$prefijobd}c_colonia  AS rco ON f.RemitenteColonia_RID = rco.ID
    LEFT JOIN {$prefijobd}c_municipio  AS rmu ON f.RemitenteMunicipio_RID = rmu.ID
    LEFT JOIN {$prefijobd}estados  AS res ON f.RemitenteEstado_RID = res.ID
    LEFT JOIN {$prefijobd}c_localidad AS dlo ON f.DestinatarioLocalidad2_RID = dlo.ID
    LEFT JOIN {$prefijobd}c_colonia AS dco ON f.DestinatarioColonia_RID = dco.ID
    LEFT JOIN {$prefijobd}c_municipio AS dmu ON f.DestinatarioMunicipio_RID = dmu.ID
    LEFT JOIN {$prefijobd}estados AS dest ON f.DestinatarioEstado_RID = dest.ID
    LEFT JOIN {$prefijobd}unidades AS un ON f.Unidad_RID = un.ID
    LEFT JOIN {$prefijobd}aseguradoras una ON un.AseguradoraUnidad_RID = una.ID
	LEFT JOIN {$prefijobd}c_configautotransporte unc ON un.ConfigAutotranporte_RID = unc.ID
    LEFT JOIN {$prefijobd}c_configautotransporte AS cf ON cf.ID = f.ConfigAutotranporte_RID
WHERE f.ID = ".$id_factura;

$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);

//espacio para migrar a joins toda la factura

//die($resSQL01);

if (!$runSQL01) {//debug
		$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL01;
		die($mensaje);
	}
while($rowSQL01 = mysqli_fetch_array($runSQL01)){
	
	$f_cfdserie = $rowSQL01['cfdserie'];
	$f_cfdfolio = $rowSQL01['cfdfolio'];
	$f_xfolio = $rowSQL01['XFolio'];
	$f_creado_t = $rowSQL01['Creado'];
	$f_creado = date("d-m-Y H:i:s", strtotime($f_creado_t));
	$f_ticket = $rowSQL01['Ticket'];
	$f_moneda = $rowSQL01['Moneda'];
	$f_subtotal_t = $rowSQL01['zSubtotal'];
	$f_subtotal = number_format($f_subtotal_t, $numDecimales); 
	$f_impuesto_t = $rowSQL01['zImpuesto'];
	$f_impuesto = number_format($f_impuesto_t, $numDecimales);
	$f_retenido_t = $rowSQL01['zRetenido'];
	$f_retenido = number_format($f_retenido_t, $numDecimales); 
	$f_total_t = $rowSQL01['zTotal'];
	$f_total = number_format($f_total_t, $numDecimales); 
	$f_total2 =	number_format($f_total_t, $numDecimales, ".", ",");	
	$f_usocfdi33_id = $rowSQL01['usocfdi33_RID'];
	$f_metodopago33_id = $rowSQL01['metodopago33_RID'];
	$f_formapago33_id = $rowSQL01['formapago33_RID'];
	$f_id_cliente = $rowSQL01['CargoAFactura_RID'];
	$f_remitente_localidad_id = $rowSQL01['RemitenteLocalidad2_RID'];
	$f_codigoorigen = $rowSQL01['CodigoOrigen'];
	$f_remitente = $rowSQL01['Remitente'];
	$f_remitente_rfc = $rowSQL01['RemitenteRFC'];
	$f_remitente_calle = $rowSQL01['RemitenteCalle'];
	$f_remitente_numext = $rowSQL01['RemitenteNumExt'];
	$f_remitente_colonia_id = $rowSQL01['RemitenteColonia_RID'];
	$f_remitente_municipio_id = $rowSQL01['RemitenteMunicipio_RID'];
	$f_remitente_estado_id = $rowSQL01['RemitenteEstado_RID'];
	$f_remitente_cp = $rowSQL01['RemitenteCodigoPostal'];
	$f_remitente_pais = $rowSQL01['RemitentePais'];
	$f_remitente_numregidtrib = $rowSQL01['RemitenteNumRegIdTrib'];
	$f_citacarga_t = $rowSQL01['CitaCarga'];
	$f_citacarga = date("d-m-Y H:i:s", strtotime($f_citacarga_t));
	$f_citacargaq= date("Y-m-d H:i:s", strtotime($f_citacarga));
	$f_remitente_telefono = $rowSQL01['RemitenteTelefono'];
	$f_destinatario_localidad_id = $rowSQL01['DestinatarioLocalidad2_RID'];
	$f_codigodestino = $rowSQL01['CodigoDestino'];
	$f_destinatario = $rowSQL01['Destinatario'];
	$f_claveunidadpeso = $rowSQL01['ClaveUnidad'];
	$f_destinatario_rfc = $rowSQL01['DestinatarioRFC'];
	$f_destinatario_calle = $rowSQL01['DestinatarioCalle'];
	$f_destinatario_numext = $rowSQL01['DestinatarioNumExt'];
	$f_destinatario_colonia_id = $rowSQL01['DestinatarioColonia_RID'];
	$f_destinatario_municipio_id = $rowSQL01['DestinatarioMunicipio_RID'];
	$f_destinatario_estado_id = $rowSQL01['DestinatarioEstado_RID'];
	$f_destinatario_cp = $rowSQL01['DestinatarioCodigoPostal'];
	$f_destinatario_pais = $rowSQL01['DestinatarioPais'];
	$f_destinatario_numregidtrib = $rowSQL01['DestinatarioNumRegIdTrib'];
	$f_destinatario_citacarga_t = $rowSQL01['DestinatarioCitaCarga'];
	$f_destinatario_citacarga = date("d-m-Y H:i:s", strtotime($f_destinatario_citacarga_t));
	$f_destinatario_telefono = $rowSQL01['DestinatarioTelefono'];
	$f_comentarios = $rowSQL01['Comentarios'];
	$f_cp_instrucciones = $rowSQL01['Instrucciones'];
	$f_cfdnocertificado = $rowSQL01['cfdnocertificado'];
	$f_cfdiuuid = $rowSQL01['cfdiuuid'];
	$dias_credito = $rowSQL01 ['DiasCredito'];
	$vence_factura = date("d-m-Y", strtotime($rowSQL01 ['Vence']));
	$f_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$f_cfdffcchh = $rowSQL01['cfdfchhra'];
	$f_cfdifechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
	$f_cfdsellodigital = $rowSQL01['cfdsellodigital']; 
	$f_cfdiselloSAT = $rowSQL01['cfdiselloSAT'];
	$f_cfdiselloCadenaOriginal = $rowSQL01['cfdiselloCadenaOriginal'];
	$f_configautotransporte_id = $rowSQL01['ConfigAutotranporte_RID'];
	$f_tipo_viaje = $rowSQL01['TipoViaje'];
	$f_pesobrutovehicular = $rowSQL01['PesoBrutoVehicular'];
	$fPesoNeto = $rowSQL01['PesoNeto'];	
	$f_unidad_id= $rowSQL01['Unidad_RID'];
	$f_tipocambio = $rowSQL01['TipoCambio'];
	$f_cancelada= $rowSQL01['cCanceladoT'];
	$f_ISR = $rowSQL01 ['ISRImporte'];
	$f_aseguradora = $rowSQL01['Aseguradora'];
	$f_poliza = $rowSQL01['Poliza'];
/* 	$f_unidad_id2= $rowSQL01['Unidad2_RID']; */
	$f_remolque_id= $rowSQL01['Remolque_RID'];
	$f_remolque2_id= $rowSQL01['Remolque2_RID'];
	$f_dolly_id= $rowSQL01['Dolly_RID'];
	$f_DistanciaRecorrida = $rowSQL01['DistanciaRecorrida'];
	$f_permisionario_id = $rowSQL01['Permisionario_RID'];
	$f_operador_id= $rowSQL01['Operador_RID'];
	$f_pesototal_t= $rowSQL01['xPesoTotal'];
	$f_pesototal = number_format($f_pesototal_t, $numDecimales);
	$f_complemento_traslado= $rowSQL01['ComplementoTraslado'];
	$f_lleva_repartos= $rowSQL01['LlevaRepartos'];
	$f_qrFsencilla = $rowSQL01['cfdicbbArchivo'];
	$f_idCCP = $rowSQL01['IdCCP'];
	$f_total_letra = convertir($f_total, $f_moneda);
	if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}
	$f_addendas = $rowSQL01['Addendas'];
	$f_AddCampoA = $rowSQL01['AddCampoA'];
	$f_seRecoge = $rowSQL01['RemitenteSeRecogera'];
	$f_seEntrega = $rowSQL01['DestinatarioSeEntregara'];
    $cliente_nombre = $rowSQL01['clienteNombre'];
    $cliente_calle = $rowSQL01['clienteCalle'];
    $cliente_numext = $rowSQL01['clienteNumExt'];
    $cliente_numint = $rowSQL01['clienteNumInt'];
    $cliente_rfc = $rowSQL01['clienteRFC'];
    $cliente_cp = $rowSQL01['clienteCP'];
    $cliente_colonia = $rowSQL01['clienteColonia'];
    $cliente_municipio = $rowSQL01['clienteMunicipio'];
    $cliente_estado = $rowSQL01['clienteEstado'];
    $cliente_ciudad = $rowSQL01['clienteLocalidad'];
    $clienteIdRegfiscal = $rowSQL01['cRegimenFiscal_RID'];
    $clienteClaveRegfiscal = $rowSQL01['RegimenFiscal'];
    $clienteDescRegfiscal = $rowSQL01['RegimenFiscalDescripcion'];
	$clienteIdRegfiscal    = (int)$rowSQL01['cRegimenFiscal_RID'];
	$clienteClaveRegfiscal = $rowSQL01['RegimenFiscal'];
	$clienteDescRegfiscal  = $rowSQL01['RegimenFiscalDescripcion'];

	if ($regPorParametro && $clienteIdRegfiscal > 0) {

		$Regimen_prev = $clienteIdRegfiscal;

		$resSQL007 = "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID = {$Regimen_prev}";
		$runSQL007 = mysqli_query($cnx_cfdi2, $resSQL007);
		$rowSQL007 = mysqli_fetch_assoc($runSQL007);

		if ($rowSQL007){
			$cliente_Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
		} else {
			
			$cliente_Regimen = trim($clienteClaveRegfiscal).", ".trim($clienteDescRegfiscal);
		}

	} else {
		
		$cliente_Regimen = trim($clienteClaveRegfiscal).", ".trim($clienteDescRegfiscal);
	}
    $f_usocfdi_dsc = $rowSQL01['usocfdi33_Descripcion'];
    $f_usocfdi = $rowSQL01['usocfdi33_ID2'];
    $f_metodopago_dsc = $rowSQL01['metodopago33_Descripcion'];
    $f_metodopago = $rowSQL01['metodopago33_ID2'];
    $f_formapago_dsc = $rowSQL01['formapago33_Descripcion'];
    $f_formapago = $rowSQL01['formapago33_ID2'];
    $remitente_localidad_nombre = $rowSQL01['remitenteLocalidad'];
    $remitente_colonia_nombre = $rowSQL01['remitenteColonia'];
    $remitente_municipio_nombre = $rowSQL01['remitenteMunicipio'];
    $remitente_estado_nombre = $rowSQL01['remitenteEstado'];
    $destinatario_localidad_nombre = $rowSQL01['destinatarioLocalidad'];
    $destinatario_colonia_nombre = $rowSQL01['destinatarioColonia'];
    $destinatario_municipio_nombre = $rowSQL01['destinatarioMunicipio'];
    $rdestinatario_estado_nombre = $rowSQL01['destinatarioEstado'];    
    $unidad_nombre = $rowSQL01['unidad1Nombre'];
    $unidad_polizano = $rowSQL01['unidad1PolizaNo'];
    $unidad_placas = $rowSQL01['unidad1Placas'];
    $unidad_anio = $rowSQL01['unidad1Ano'];
    $unidad_peso = $rowSQL01['unidad1PesoBrutoVehicular'];
    $PermisoSCT = !empty($rowSQL01['unidad1PermisoSCT']) ? $rowSQL01['unidad1PermisoSCT'] : $PermisoSCTsys;
    $TipoPermisoSCT= !empty($rowSQL01['unidad1TipoPermisoSCT']) ? $rowSQL01['unidad1TipoPermisoSCT'] :  $TipoPermisoSCTsys;
    $unidad_aseguradora_nombre = $rowSQL01['unidad1AseguradoraNombre'];
    $configautotransporte_descripcion = $rowSQL01['unidad1ConfigAutotransporteDescripcion'];
    $configautotransporte_clavenomenclatura = $rowSQL01['unidad1ConfigAutotransporteClaveNomenclatura'];

}
if ($Multi === "1"){
	$resSQL07 = "SELECT *  FROM {$prefijobd}emisores WHERE ID={$emisor_id}";
	//echo $resSQL07;
	$runSQL07 = mysqli_query($cnx_cfdi2, $resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$RazonSocial = $rowSQL07['RazonSocial'];
		$Calle = $rowSQL07['Calle'];
		$NumeroExterior = $rowSQL07['NumeroExterior'];
		$NumeroInterior = $rowSQL07['NumeroInterior'];
		$Colonia = $rowSQL07['Colonia'];
		$CodigoPostal = $rowSQL07['CodigoPostal'];
		$Ciudad = $rowSQL07['Ciudad'];
		$Estado = $rowSQL07['Estado'];
		//$codLocalidad = $rowSQL07['codLocalidad'];
		$Telefono = $rowSQL07['Telefono'];
		$RFC = $rowSQL07['RFC'];
		$Pais = $rowSQL07['Pais'];
		$Municipio = $rowSQL07['Municipio'];
		$xml_dir= $rowSQL07['xmldir'];
			if ($regPorParametro) {
			$Regimen_prev= $rowSQL07['RegimenFiscal_RID'];
			
			$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
			$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
			$rowSQL007= mysqli_fetch_assoc($runSQL007);
			if ($rowSQL007){
				$Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
			}
		}else{
			$Regimen = $rowSQL07['Regimen'];
		}
		
		$ruta_logo_multi= $rowSQL07['RutaLogo'];
		$rutalogo= $ruta_logo_multi;
		if (isset($rowSQL07['ColorFormatos'])) {
			$coloresMulti = $rowSQL07['ColorFormatos'];
		} else {
			$coloresMulti = '';
		}
		
		
		
	}
} else {
	$resSQL07 = "SELECT * FROM ".$prefijobd."systemsettings";
	$runSQL07 = mysqli_query($cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$RazonSocial = $rowSQL07['RazonSocial'];
		$Calle = $rowSQL07['Calle'];
		$NumeroExterior = $rowSQL07['NumeroExterior'];
		$NumeroInterior = $rowSQL07['NumeroInterior'];
		$Colonia = $rowSQL07['Colonia'];
		$CodigoPostal = $rowSQL07['CodigoPostal'];
		$Ciudad = $rowSQL07['Ciudad'];
		$Estado = $rowSQL07['Estado'];
		//$codLocalidad = $rowSQL07['codLocalidad'];
		$Telefono = $rowSQL07['Telefono'];
		$RFC = $rowSQL07['RFC'];
		$Pais = $rowSQL07['Pais'];
		$Municipio = $rowSQL07['Municipio'];
		$xml_dir= $rowSQL07['xmldir'];
		if ($regPorParametro) {
			$Regimen_prev= $rowSQL07['RegimenFiscal_RID'];
			
			$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
			$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
			$rowSQL007= mysqli_fetch_assoc($runSQL007);
			if ($rowSQL007){
				$Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
			}
		}else{
			$Regimen = $rowSQL07['Regimen'];
		}
		$PermisoSCTsys = $rowSQL07['PermisoSCT'];
		$TipoPermisoSCTsys= $rowSQL07['TipoPermisoSCT'];
		$codLocalidad = '';
		$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';
	}
}


//Concat de numero exterior para que aparezca o no el # y en interior el int. 
$NumeroExterior = (!empty($NumeroExterior)) ? '# '.$NumeroExterior : '';
$NumeroInterior = (!empty($NumeroInterior)) ? 'int. '.$NumeroInterior : '';

//domicilio restante cliente
$domicilioRestante = (!empty($Colonia || $Estado || $Ciudad)) ? 'Col. '.$Colonia.', </br>'.$Ciudad.', '.$Estado.', CP: '.$CodigoPostal : '';

//transporte internacional condicion
$f_transporte_internacional = ($f_tipo_viaje === 'NACIONAL') ? 'NO' : 'SI';

//busca version CCP
$f_versionCCP = 3.1;

//Buscar CFDI Relacionado
$resSQL022 = "SELECT COUNT(ID) as total FROM ".$prefijobd."FacturaUUIDRelacionadoSub WHERE FolioSub_RID=".$id_factura;
$runSQL022 = mysqli_query( $cnx_cfdi2 ,$resSQL022);
while($rowSQL022 = mysqli_fetch_array($runSQL022)){
    $tmp_cfdirel = $rowSQL022['total']; 
}

if($tmp_cfdirel > 0){
    $resSQL02 = "SELECT TipoRelacion, cfdiuuidRelacionado  FROM ".$prefijobd."FacturaUUIDRelacionadoSub WHERE FolioSub=".$id_factura;
    $runSQL02 = mysqli_query( $cnx_cfdi2 ,$resSQL02);
    while($rowSQL02 = mysqli_fetch_array($runSQL02)){
        $fr_tiporelacion = $rowSQL02['TipoRelacion']; 
        $fr_cfdiuuidRelacionado = $rowSQL02['cfdiuuidRelacionado'];
    }
} else {
    $fr_tiporelacion = ''; 
    $fr_cfdiuuidRelacionado = '';
}





//Buscar ConfigAutotransporte
if(empty($f_configautotransporte_id)){
	$f_configautotransporte_descripcion = '';
	$f_configautotransporte_clavenomenclatura = '';
} else {
	$resSQL20 = "SELECT Descripcion, ClaveNomenclatura FROM ".$prefijobd."c_ConfigAutotransporte WHERE ID=".$f_configautotransporte_id ;
	$runSQL20 = mysqli_query( $cnx_cfdi2 ,$resSQL20);
	while($rowSQL20 = mysqli_fetch_array($runSQL20)){
		$f_configautotransporte_descripcion = $rowSQL20['Descripcion'];
		$f_configautotransporte_clavenomenclatura = $rowSQL20['ClaveNomenclatura'];
	}
}






//busca unidad 2
$resSQLU2 = "SELECT Unidad2_RID FROM {$prefijobd}factura  WHERE ID = {$id_factura}";
$runSQLU2 = mysqli_query($cnx_cfdi2, $resSQLU2);
if ($rowSQLU2 = mysqli_fetch_array($runSQLu2)) {
	$f_unidad_id2 = $rowSQLU2['Unidad2_RID'];
} else {
	$f_unidad_id2 = '';
}


if(!empty($f_unidad_id2)){

	$resSQL24 = "SELECT 
				u.Unidad, 
				u.PolizaNo,
				u.Placas, 
				u.Ano,
				u.PesoBrutoVehicular,
				u.PermisoSCT,
				u.TipoPermisoSCT,
				a.Aseguradora AS unidad_aseguradora_nombre,
				c.Descripcion as configuracionautotransporte_descripcion,
				c.ClaveNomenclatura as configautotransporte_clavenomenclatura
				FROM {$prefijobd}unidades u
				LEFT JOIN {$prefijobd}aseguradoras a ON u.AseguradoraUnidad_RID = a.ID
				LEFT JOIN {$prefijobd}c_ConfigAutotransporte c ON u.ConfigAutotranporte_RID = c.ID

	 			WHERE u.ID= {$f_unidad_id2}" ;

	$runSQL24 = mysqli_query( $cnx_cfdi2 ,$resSQL24);
	if (!$runSQL24) {//debug
		$mensaje  = 'Consulta unidad 2 no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQ24;
		die($mensaje);
	}

	if ($rowSQL24 = mysqli_fetch_array($runSQL24)) {
        $unidad_2_nombre = $rowSQL24['Unidad'];
        $unidad_2_polizano = $rowSQL24['PolizaNo'];
        $unidad_2_placas = $rowSQL24['Placas'];
        $unidad_2_anio = $rowSQL24['Ano'];
		$unidad_2_peso = $rowSQL24['PesoBrutoVehicular'];
		$PermisoSCT2 = $rowSQL24['PermisoSCT'];
		$TipoPermisoSCT2= $rowSQL24['TipoPermisoSCT'];
        $unidad_2_aseguradora_nombre = isset($rowSQL24['unidad_aseguradora_nombre']) ? $rowSQL24['unidad_aseguradora_nombre'] : '';
		$configautotransporte_2_descripcion = isset($rowSQL24['configuracionautotransporte_descripcion']) ? $rowSQL24['configuracionautotransporte_descripcion'] : '';
        $configautotransporte_2_clavenomenclatura = isset($rowSQL24['configautotransporte_clavenomenclatura']) ? $rowSQL24['configautotransporte_clavenomenclatura'] : '';
    }else{
		$mensaje  = 'Consulta no valida 1: ' . mysqli_error($cnx_cfdi2) . "\n";
		die($mensaje);
	}
}

	


//Buscar Remolque

$remolque_nombre = '';
$remolque_placas = '';
$remolque_anio = '';
$remolque_subtiporem_id= '';
$remolque_clave_tipo_remilque = '';
$remolque_remolque_semiremolque = '';

if(!empty($f_remolque_id)){


$resSQL23 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano,
				u.SubTipoRem_RID,
				u.PermisoSCT,
				u.TipoPermisoSCT,
				s.ClaveTipoRemolque,
				s.RemolqueSemiremolque,
				u.PolizaNo,
				a.Aseguradora AS unidad_aseguradora_nombre
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}c_SubTipoRem s ON u.SubTipoRem_RID = s.ID
				LEFT JOIN {$prefijobd}aseguradoras a ON u.AseguradoraUnidad_RID = a.ID
				WHERE u.ID=".$f_remolque_id ;

$runSQL23 = mysqli_query( $cnx_cfdi2 ,$resSQL23);
if (!$runSQL23) {//debug
	$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQL23;
	die($mensaje);
}
	if ($rowSQL23 = mysqli_fetch_array($runSQL23)) {
		$remolque_nombre = $rowSQL23['Unidad'];
		$remolque_placas = $rowSQL23['Placas'];
		$remolque_anio = $rowSQL23['Ano'];
        $remolque_polizano = $rowSQL23['PolizaNo'];
		$remolque_permisoSCT = $rowSQL23['PermisoSCT'].' - '.$rowSQL23['TipoPermisoSCT'];
        $remolque_aseguradora_nombre = isset($rowSQL23['unidad_aseguradora_nombre']) ? $rowSQL23['unidad_aseguradora_nombre'] : $unidad_aseguradora_nombre;
		$remolque_subtiporem_id= $rowSQL23['SubTipoRem_RID'];
		$remolque_clave_tipo_remilque = $rowSQL23['ClaveTipoRemolque'];
		$remolque_remolque_semiremolque = $rowSQL23['RemolqueSemiremolque'];
	}
}



//busca Remolque 2
$remolque2_nombre = '-';
$remolque2_placas = '-';
$remolque2_anio = '-';
$remolque2_subtiporem_id= '-';
$remolque2_clave_tipo_remilque = '-';
$remolque2_remolque_semiremolque = '-';

if(!empty($f_remolque2_id)){


$resSQL41 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano,
				u.SubTipoRem_RID,
				u.PermisoSCT,
				u.TipoPermisoSCT,
				s.ClaveTipoRemolque,
				s.RemolqueSemiremolque
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}c_SubTipoRem s ON u.SubTipoRem_RID = s.ID
				WHERE u.ID=".$f_remolque2_id ;

$runSQL41 = mysqli_query( $cnx_cfdi2 ,$resSQL41);
if (!$runSQL41) {//debug
	$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQ41;
	die($mensaje);
}
	if ($rowSQL41 = mysqli_fetch_array($runSQL41)) {
		$remolque2_nombre = $rowSQL41['Unidad'];
		$remolque2_placas = $rowSQL41['Placas'];
		$remolque2_anio = $rowSQL41['Ano'];
		$remolque2_subtiporem_id= $rowSQL41['SubTipoRem_RID'];
		$remolque2_clave_tipo_remilque = $rowSQL41['ClaveTipoRemolque'];
		$remolque2_remolque_semiremolque = $rowSQL41['RemolqueSemiremolque'];
	}
}

//busca DOLLY
$dolly_nombre = '-';
$dolly_placas = '-';
$dolly_anio = '-';


if(!empty($f_dolly_id)){


$resSQL42 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano
				
				FROM {$prefijobd}Unidades u
				WHERE u.ID=".$f_dolly_id ;

$runSQL42 = mysqli_query( $cnx_cfdi2 ,$resSQL42);
if (!$runSQL42) {//debug
	$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQ42;
	die($mensaje);
}
	if ($rowSQL42 = mysqli_fetch_array($runSQL42)) {
		$dolly_nombre = $rowSQL42['Unidad'];
		$dolly_placas = $rowSQL42['Placas'];
		$dolly_anio = $rowSQL42['Ano'];
		
	}
}

if($f_operador_id > 0){
	//Buscar Operador
	$resSQL26 = "SELECT 
					o.TipoFigura,
					o.Operador,
					o.RFC,
					o.LicenciaNo,
					o.ResidenciaFiscal,
					o.NumRegIdTrib,
					o.CodigoPostal,
					e.Estado
				 FROM {$prefijobd}Operadores as  o
				 LEFT JOIN {$prefijobd}estados  as e ON o.Estado_RID = e.ID
				 WHERE o.ID={$f_operador_id}";
	$runSQL26 = mysqli_query( $cnx_cfdi2 ,$resSQL26);
	if (!$runSQL26) {//debug
		$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQ41;
		die($mensaje);
	}
	while($rowSQL26 = mysqli_fetch_array($runSQL26)){
		$operador_tipo_figura = $rowSQL26['TipoFigura'];
		$operador_nombre = $rowSQL26['Operador'];
		$operador_rfc = $rowSQL26['RFC'];
		$operador_licencia = $rowSQL26['LicenciaNo'];
		$operador_residencia_fiscal = $rowSQL26['ResidenciaFiscal'];
		$operador_identidad_tributaria = $rowSQL26['NumRegIdTrib'];
		$operador_cp = $rowSQL26 ['CodigoPostal'];
		$operador_estado = $rowSQL26 ['Estado'];
		
	}
} else {
	$operador_tipo_figura = '';
	$operador_nombre = '';
	$operador_rfc = '';
	$operador_licencia = '';
	$operador_residencia_fiscal = '';
	$operador_identidad_tributaria = '';
	$operador_cp= '';
	$operador_estado= '';
}

//busca operador 2
$f_operador_id2 = 0;
$resSQLOp2 = "SELECT Operador2_RID FROM {$prefijobd}factura  WHERE ID = {$id_factura}";
$runSQLOp2 = mysqli_query($cnx_cfdi2, $resSQLOp2);
if ($rowSQLOp2 = mysqli_fetch_array($runSQLOp2)) {
	$f_operador_id2 = $rowSQLOp2['Operador2_RID'];
} else {
	$f_operador_id2 = 0;
}

if($f_operador_id2 > 0){
	//Buscar Operador
	$resSQL25 = "SELECT 
					o.TipoFigura,
					o.Operador,
					o.RFC,
					o.LicenciaNo,
					o.ResidenciaFiscal,
					o.NumRegIdTrib,
					o.CodigoPostal,
					e.Estado
				 FROM {$prefijobd}Operadores as  o
				 LEFT JOIN {$prefijobd}estados  as e ON o.Estado_RID = e.ID
				 WHERE o.ID={$f_operador_id2}";
	$runSQL25 = mysqli_query( $cnx_cfdi2, $resSQL25);
	if (!$runSQL25) {//debug
		$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQ25;
		die($mensaje);
	}
	while($rowSQL25 = mysqli_fetch_array($runSQL25)){
		$operador_tipo_figura_2 = $rowSQL25['TipoFigura'];
		$operador_nombre_2 = $rowSQL25['Operador'];
		$operador_rfc_2 = $rowSQL25['RFC'];
		$operador_licencia_2 = $rowSQL25['LicenciaNo'];
		$operador_residencia_fiscal_2 = $rowSQL25['ResidenciaFiscal'];
		$operador_identidad_tributaria_2 = $rowSQL25['NumRegIdTrib'];
		$operador_cp_2 = $rowSQL25 ['CodigoPostal'];
		$operador_estado_2 = $rowSQL25 ['Estado'];
		
	}
} else {
	$operador_tipo_figura_2 = '';
	$operador_nombre_2 = '';
	$operador_rfc_2 = '';
	$operador_licencia_2 = '';
	$operador_residencia_fiscal_2 = '';
	$operador_identidad_tributaria_2 = '';
	$operador_cp_2= '';
	$operador_estado_2= '';
}


 $resSQL40 = "SELECT
					a.Cantidad 
				FROM {$prefijobd}facturassub a 
				LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
				
				WHERE a.FolioSub_RID =".$id_factura;
$runSQL40 = mysqli_query( $cnx_cfdi2 ,$resSQL40);
$f_totalcantidad = 0;

while($rowSQL40 = mysqli_fetch_array($runSQL40)){
	$fs_cantidad_t = $rowSQL40['Cantidad'];
	
	$f_totalcantidad++ ;


	
} 

$resSQL43= "SELECT fac.ParteTransporte1_RID, 
					tr.Clave as pt1Clave, tr.Descripcion as pt1Dsc, fac.ParteTransporte2_RID, 
					tr2.Clave as pt2Clave, tr2.Descripcion as pt2Dsc, 
			tr3.Clave as pt3Clave, tr3.Descripcion as pt3Dsc,fac.ParteTransporte3_RID,fac.FiguraTransporteTipo1, 
		op.Operador as Operador1, fac.FiguraTransporte1_RID, op2.Operador as Operador2, fac.FiguraTransporte2_RID,  fac.FiguraTransporteTipo2
			FROM basdb.{$prefijobd}factura  as fac
			LEFT JOIN {$prefijobd}c_transporte as tr on fac.ParteTransporte1_RID = tr.ID
			LEFT JOIN {$prefijobd}c_transporte as tr2 on fac.ParteTransporte2_RID = tr2.ID
			LEFT JOIN {$prefijobd}c_transporte as tr3 on fac.ParteTransporte3_RID = tr3.ID
			LEFT JOIN {$prefijobd}operadores as op on fac.FiguraTransporte1_RID = op.ID
			LEFT JOIN {$prefijobd}operadores as op2 on fac.FiguraTransporte2_RID = op2.ID
			WHERE fac.ID ={$id_factura}";
$runSQL43= mysqli_query($cnx_cfdi2, $resSQL43);
while($rowSQL43 = mysqli_fetch_array($runSQL43)){
	$ft_op1_ID = $rowSQL43['FiguraTransporte1_RID'];
	$parte_transporte1= $rowSQL43['pt1Clave']." - ".$rowSQL43['pt1Dsc'];
	$parte_transporte2= $rowSQL43['pt2Clave']." - ".$rowSQL43['pt2Dsc'];
	$parte_transporte3= $rowSQL43['pt3Clave']." - ".$rowSQL43['pt3Dsc'];
	$figura_transporte1 = $rowSQL43['FiguraTransporteTipo1']." - ".$rowSQL43['Operador1'];
	$figura_transporte2 = $rowSQL43['FiguraTransporteTipo2']." - ".$rowSQL43['Operador2'];
	
}

/* PARAMETROS, asigno el id2 el numero de parametro  que se utilizara en la plataforma, el parametro de decimales esta al inicio ya que se necesita leer primero */
// trae los parametros para color de fondo, color letra, para contrato y para bitacora, en ese orden se los trae

$parametro_bgc = 921;
$resSQL921 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_bgc";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
	 
while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
	$param= $rowSQL921['id2'];
	$color= $rowSQL921 ['VCHAR'];
}

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}
//estilo de colores
if (($Multi ==1) && !empty($coloresMulti)) {
	$estilo_fondo = $coloresMulti;
}else {
$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

}

$parametro_contrato = 923;
$resSQL923 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_contrato";
$runSQL923 = mysqli_query($cnx_cfdi2, $resSQL923);
	 
while ($rowSQL923 = mysqli_fetch_array($runSQL923)) {
	$param= $rowSQL923['id2'];
	$req_contrato = $rowSQL923 ['VLOGI'];
	
}

$parametro_bitacora = 939;
$resSQL939 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 = $parametro_bitacora";
$runSQL939 = mysqli_query($cnx_cfdi2, $resSQL939);
	 
while ($rowSQL939 = mysqli_fetch_array($runSQL939)) {
	$param= $rowSQL939['id2'];
	$req_bitacora = $rowSQL939 ['VLOGI'];
}

$parametro_footer_lyd= 926;
$f_lleva_leyenda = 0;
$resSQL926 = "SELECT id2, dsc, VCHAR, VLOGI FROM {$prefijobd}parametro WHERE id2= $parametro_footer_lyd";
$runSQL926 = mysqli_query($cnx_cfdi2, $resSQL926);
while ($rowSQL926 = mysqli_fetch_array($runSQL926)){
	$param_footer = $rowSQL926['id2'];
	$f_footer_leyenda = $rowSQL926['dsc'];
	$f__leyenda_color = $rowSQL926['VCHAR'];
	$f_lleva_leyenda= $rowSQL926['VLOGI'];
	$f_footer_leyenda_color = "color:".$f__leyenda_color.";";
}


$parametro_domi = 927;
$forzar_domicilios = 0;
$resSQL927 = "SELECT id2, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_domi";
$runSQL927 = mysqli_query($cnx_cfdi2, $resSQL927);
$rowSQL927 = mysqli_fetch_array($runSQL927);

if ($rowSQL927) {
	
	$forzar_domicilios = $rowSQL927 ['VLOGI'];
	
}

$parametro_unid_ope = 928;
$lleva_unidad_operador = 0;
$resSQL928 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2= {$parametro_unid_ope}";
$runSQL928 = mysqli_query($cnx_cfdi2, $resSQL928);
while ($rowSQL928 = mysqli_fetch_array($runSQL928)) {
	$lleva_unidad_operadores = $rowSQL928['VLOGI'];
}
if (!empty($lleva_unidad_operadores)) {
	$lleva_unidad_operador = $lleva_unidad_operadores;
}

$parametro_part_comen = 929;
$partida_enComen = 0;
$resSQL929 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2= {$parametro_unid_ope}";
$runSQL929 = mysqli_query($cnx_cfdi2, $resSQL929);
while ($rowSQL929 = mysqli_fetch_array($runSQL929)) {
	$partida_enComent = $rowSQL929['VLOGI'];
}
if (!empty($partida_enComent)) {
	$partida_enComen = $partida_enComent;
}

$parametro_sin_logo = 931;
$sinLogo = 0;
$resSQL931 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2= {$parametro_sin_logo}";
$runSQL931 = mysqli_query($cnx_cfdi2, $resSQL931);
while ($rowSQL931 = mysqli_fetch_array($runSQL931)) {
	$sinLogos = $rowSQL931['VLOGI'];
}

if ($sinLogos =='1') {
	$rutalogo =  '../cfdipro/imagenes/NOLOGO.jpg';
}

//Buscar Nombre Comercial

$parametro_nombre_comercial = 932;
$resSQL932 = "SELECT id2, VLOGI, dsc FROM {$prefijobd}parametro WHERE id2= {$parametro_nombre_comercial}";
$runSQL932 = mysqli_query($cnx_cfdi2, $resSQL932);
while ($rowSQL932 = mysqli_fetch_array($runSQL932)) {
	$nombre_comercial = $rowSQL932['dsc'];
	$nombre_comercial = substr($nombre_comercial, 0, 35);
	$cambio_a_nombre_comercial = $rowSQL932['VLOGI'];
}

$param_contrato_forzado = 933;
$resSQL933 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2= {$param_contrato_forzado}";
$runSQL933 = mysqli_query($cnx_cfdi2, $resSQL933);
while ($rowSQL933 = mysqli_fetch_array($runSQL933)) {
	$contrato_forzado = $rowSQL933['VLOGI'];

}

$param_recuadro_arriba = 934;
$resSQL934 = "SELECT id2, VLOGI, dsc, VCHAR FROM {$prefijobd}parametro WHERE id2= {$param_recuadro_arriba}";
$runSQL934 = mysqli_query($cnx_cfdi2, $resSQL934);
while ($rowSQL934 = mysqli_fetch_array($runSQL934)) {
	$campo_original = $rowSQL934['dsc'];
	$campo_alias = $rowSQL934['VCHAR'];
	$inicio_del_campo = $rowSQL934['MEMO'];
	$lleva_recuadro_arriba = $rowSQL934['VLOGI'];


}

$parametroBarCode = 937;
$resSQL937 = "SELECT id2, VCHAR, VLOGI, dsc FROM {$prefijobd}parametro Where id2 = $parametroBarCode";
$runSQL937 = mysqli_query($cnx_cfdi2, $resSQL937);

while ($rowSQL937 = mysqli_fetch_array($runSQL937)) {
	$param= $rowSQL937['id2'];
	$llevaCodigoBarras = $rowSQL937 ['VLOGI'];
	$campoBaseDatos = $rowSQL937 ['dsc'];
	$aliasCampoBaseDatos = $rowSQL937 ['VCHAR'];
}

$parametroOrdenPersonalizado = 938;
$ordenPersonalizado = 0;
$resSQL938 = "SELECT id2, VLOGI FROM {$prefijobd}parametro Where id2 = $parametroOrdenPersonalizado";
$runSQL938 = mysqli_query($cnx_cfdi2, $resSQL938);	
while ($rowSQL938 = mysqli_fetch_array($runSQL938)) {
	$param= $rowSQL938['id2'];
	$ordenPersonalizado = $rowSQL938 ['VLOGI'];
}

$parametroComentariodFDetalles = 941;
$comentarioFactDetalle = 0;
$resSQL941 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2 = $parametroComentariodFDetalles";
$runSQL941 = mysqli_query($cnx_cfdi2, $resSQL941);
while ($rowSQL941 = mysqli_fetch_array($runSQL941)) {
	$comentarioFactDetalle = $rowSQL941['VLOGI'];
}

if ($comentarioFactDetalle == 1) {

		$resSQLFD = "SELECT a.Descripcion,
							b.XFolio,
							b.Creado
						FROM {$prefijobd}facturasdetalle  AS a 
						LEFT JOIN {$prefijobd}remisiones AS b ON a.Remision_RID = b.ID
						WHERE FolioSubDetalle_RID = {$id_factura}";
		$runSQLFD = mysqli_query($cnx_cfdi2, $resSQLFD);
		$f_comentarios ='';
		while ($rowSQLFD = mysqli_fetch_array($runSQLFD)) {
			$fdXFolio       = $rowSQLFD['XFolio'];
			$fdCreado       = $rowSQLFD['Creado'];
			$fdCreadoFormat = date("d-m-Y H:i", strtotime($fdCreado));
			$fdDescripcion  = $rowSQLFD['Descripcion'];


			$f_comentarios  = '/'.$fdXFolio.' '.$fdCreadoFormat.' '.$fdDescripcion;
		}


	
		$comentario_db = substr($f_comentarios, 0, 999); 


		$sqlUpd = "UPDATE {$prefijobd}factura
				SET Comentarios = ? 
				WHERE ID = ?";
		$stmtUpd = mysqli_prepare($cnx_cfdi2, $sqlUpd);
		if (!$stmtUpd) {
			die('Error en prepare de UPDATE: ' . mysqli_error($cnx_cfdi2));
		}

		mysqli_stmt_bind_param($stmtUpd, "si", $comentario_db, $id_factura);
		mysqli_stmt_execute($stmtUpd);

		mysqli_stmt_close($stmtUpd);

		$f_comentarios = $comentario_db;
} 



if ($llevaCodigoBarras == 1) {

    $resSQLBC = "SELECT {$campoBaseDatos} as {$aliasCampoBaseDatos} 
                 FROM {$prefijobd}factura 
                 WHERE ID = {$id_factura}";
    $runSQLBC = mysqli_query($cnx_cfdi2, $resSQLBC);

	$campoEnCodigoBarra = '';
	if ($runSQLBC && $rowSQLBC = mysqli_fetch_array($runSQLBC)) {
		$campoEnCodigoBarra = $rowSQLBC[$aliasCampoBaseDatos];
	}
	
    $codigoFactura = urlencode($campoEnCodigoBarra);

    $dir = "C:/xampp/htdocs/XML_{$prefijo}/";
    if(!file_exists($dir)){
        mkdir($dir, 0777, true);
    }

    // Evitar espacios en el nombre del archivo
    $XFolio = 'BC-'.$f_xfolio;
    $fileName = $dir.$XFolio.'.svg';

	
   // (Code128 en formato SVG)
    $Url= "https://barcode.tec-it.com/barcode.ashx?data={$codigoFactura}&code=Code128&filetype=SVG&showlabel=false";


	$ch = curl_init($Url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"User-Agent: Mozilla/5.0",
		"Accept: image/svg+xml,text/*;q=0.8,*/*;q=0.5"
	]);
	
	$imageContent = curl_exec($ch);
	$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError    = curl_error($ch);
	curl_close($ch);
	
	if ($httpCode !== 200 || $imageContent === false) {
		die("Error cURL: $curlError (HTTP $httpCode)");
	}
	
	// Guardar archivo
	$dir = "C:/xampp/htdocs/XML_{$prefijo}/";
	if(!file_exists($dir)){
		mkdir($dir, 0777, true);
	}
	$fileName = $dir.'BC-'.$f_xfolio.'.svg';
	
	if (file_put_contents($fileName, $imageContent) === false) {
		die(" No se pudo guardar en $fileName");
	}
	
}






function recuadroArriba ($campo_original, $campo_alias,  $lleva_recuadro_arriba, $prefijobd, $id_factura, $cnx_cfdi2)
{
	if ($lleva_recuadro_arriba >= '1') {
		$resSQL = "SELECT {$campo_original} as {$campo_alias} FROM {$prefijobd}factura where ID = {$id_factura}";
		$runSQL = mysqli_query($cnx_cfdi2, $resSQL);
		while ($rowSQL = mysqli_fetch_array($runSQL)) {
			$campo_a_mostrar = $rowSQL[$campo_alias];
		}
		//echo $campo_a_mostrar;
		
			echo '<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
					
						<td style="text-align:center; width:70%; height:45px;font-size: 11px;padding-bottom: 0px;"><b>'.$campo_a_mostrar.'</b>
						
						</td>
					
					</tr>
				 </table>';
		}
}

$param_referencia = 935;
$resSQL935 = "SELECT id2, VLOGI FROM {$prefijobd}parametro WHERE id2= {$param_referencia}";
$runSQL935 = mysqli_query($cnx_cfdi2, $resSQL935);
while ($rowSQL935 = mysqli_fetch_array($runSQL935)) {
	$se_cambia_ticket = $rowSQL935['VLOGI'];


}
function cambioReferencia($se_cambia_ticket,  $prefijobd, $id_factura, $cnx_cfdi2, $f_ticket )
{
	if ($se_cambia_ticket >= '1') {
		$resSQL = "SELECT 
						a.Ticket,
						b.XFolio 
						FROM {$prefijobd}factura AS a 
						LEFT JOIN {$prefijobd}remisiones AS b ON a.XFolio = b.SeFacturoEn 					
					WHERE a.ID = {$id_factura}";
		$runSQL = mysqli_query($cnx_cfdi2, $resSQL);
		while ($rowSQL = mysqli_fetch_array($runSQL)) {
			$f_referencia = $rowSQL['XFolio'];
		}

	} else {
		$f_referencia = $f_ticket;
	}
	
	echo $f_referencia;
}




//  ///////////////////////////////////////////////////////////////////////////////GENERAR QR
function zero_fill_left ($valor, $long = 0)
{
	return str_pad($valor, $long, '0', STR_PAD_LEFT);
}
    
function zero_fill_right ($valor, $long = 0)
{
	return str_pad($valor, $long, '0', STR_PAD_RIGHT);
   
}

//Formato a Total
$separador =".";
$separar = explode($separador, $f_total2);
$sep_t = $separar[1];
//Enteros
$parte1_t = $separar[0];
$parte1 =  zero_fill_left($parte1_t,10);
//echo "PARTE1: ".$parte1."\n \n";
//Decimales
if($sep_t == ''){
	$parte2_t = '00';
} else {
	$parte2_t = $separar[1];
}

$parte2 =  zero_fill_right($parte2_t,6);

//Concatenar
$total_qr = $parte1.".".$parte2;
//echo "TOTAL F: ".$total_f."\n \n";

//Formato Sello Digital CFDI

$sello_digital_final = substr($f_cfdsellodigital, -8);

//echo "Ultimos 8 caracteres: ".$sello_digital_final."\n \n";

//QR
$dir = "C:/xampp/htdocs/XML_{$prefijo}/";

if(!file_exists($dir)){
	mkdir($dir);
}

$filename = $dir.$f_cfdserie.'-'.$f_cfdfolio.'.svg';


$contenido = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?re='.$RFC.'&rr='.$cliente_rfc.'&tt='.$total_qr.'&id='.$f_cfdiuuid.'&fe='.$sello_digital_final ;


// URL de la imagen QR
$contenido = urlencode($contenido);
$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido}&format=svg";

//die($url);
// Obtener el contenido de la imagen
$imageContent = file_get_contents($url);

// Guardar la imagen en el servidor
file_put_contents($filename, $imageContent);

$filename_ccp='';
if ($f_complemento_traslado>0) {
	
	$filename_ccp = $dir.$f_cfdserie.'-'.$f_cfdfolio.'_CCP.svg';


	$contenido2 = 'https://verificacfdi.facturaelectronica.sat.gob.mx/verificaccp/default.aspx?IdCCP='.$f_idCCP.'&FechaOrig='.$f_citacargaq.'&FechaTimb='.$f_cfdifechaTimbrado ;



	// URL de la imagen QR
	$contenido2 = urlencode($contenido2);
	$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido2}&format=svg";

	//die($url);
	// Obtener el contenido de la imagen
	$imageContent = file_get_contents($url);

	// Guardar la imagen en el servidor
	file_put_contents($filename_ccp, $imageContent);
	
}

if ($f_pesobrutovehicular > 0) {
	$pesobruto_factura = $f_pesobrutovehicular;

} else {
	$pesobruto_factura = $unidad_peso;
	
}


$nombre_factura= '';
if ($f_complemento_traslado == 1) {
	$nombre_factura = 'Factura / Complemento CCP';
	} else {
	$nombre_factura= 'Factura';
}
function cancelado($f_cancelada) {
    if (!empty($f_cancelada)) {
        echo '
        <div style="
            position: fixed;
            top: 35%;
            left: 5%;
            width: 100%;
            transform: rotate(-45deg);
            opacity: 0.5;
            z-index: -1;
            font-size: 100px;
            color: red;
            text-align: center;
        ">
            CANCELADO
        </div>';
    }
}
function splitLongText($text, $maxLength = 58) {
    
    if (strlen($text) > $maxLength) {
        $text = wordwrap($text, $maxLength, "<br>", true); 
    }
    return $text;
}


$f_cfdiselloSAT = splitLongText($f_cfdiselloSAT);
$f_cfdsellodigital = splitLongText($f_cfdsellodigital);
$f_cfdiselloCadenaOriginal = splitLongText($f_cfdiselloCadenaOriginal);

list($imgWidth, $imgHeight) = getimagesize($rutalogo);

if ($imgWidth > $imgHeight) {
    // Imagen horizontal
    $logoStyle = 'style="width: 100%; height: auto;"';
} else {
    // Imagen vertical o cuadrada
    $logoStyle = 'style="height: 130px; width: auto;"';
}

$datos_cliente = array(
	array('Cliente ', $cliente_nombre),
	array('RFC ', $cliente_rfc),
	array('Domicilio ', $cliente_calle),
	array('Estado ', $cliente_estado),
	array('CP ', $cliente_cp),
	array('Régimen Fiscal ', $cliente_Regimen),
	($dias_credito > 1 ? array('Días de crédito ', $dias_credito) : null),
	($dias_credito > 1 ? array('Vence ', $vence_factura) : null)
);
function celda($label, $valor) {
	if (trim($valor) !== '') {
		return '<td style="text-align:left; font-size:10px; width:33%;"><b>' . $label . ':</b> ' . $valor .  '</td>  ';
	}
	return '<td></td>';
}

function imprimirTablaCliente($datos_cliente , $llevaCodigoBarras, $fileName) {

	// Filtrar elementos nulos (por condiciones) y reindexar
	$datosFiltrados = array();
	foreach ($datos_cliente as $item) {
		if ($item !== null) {
			$datosFiltrados[] = $item;
		}
	}

	// Imprimir la tabla en filas de 3 columnas
	echo '<table border="0" style="margin:0; border-collapse: collapse; padding-top:-10px; border: 1px solid rgba(128, 128, 128, 0.5);" width="100%"><thead>';
		
						 if ($llevaCodigoBarras == 1) { 
						 echo'
							
							<tr>
							
								<td colspan= "4" style="text-align:right;">
								<img src='.$fileName.' width="170px" height="70px" alt="BCODE"/>
								
								</td>
							
							</tr>';
						} 
					
	echo '</thead> <tbody>';
	$total = count($datosFiltrados);
	for ($i = 0; $i < $total; $i += 3) {
		echo '<tr style="margin:0; padding:0">';
		for ($j = 0; $j < 3; $j++) {
			$indice = $i + $j;
			if (isset($datosFiltrados[$indice])) {
				echo celda($datosFiltrados[$indice][0], $datosFiltrados[$indice][1]);
			} else {
				echo '<td></td>';
			}
		}
		echo '</tr>';
	}
	echo '</tbody>
	</table>';
}

//Concatenar ID trib si es extranjero
$rfcExtranjero = 'XEXX010101000'; 

function concatenaRfcYRegId ($rfcExtranjero, $f_remitente_rfc, $f_remitente_numregidtrib, $f_destinatario_rfc, $f_destinatario_numregidtrib){
	  $fRfcCompletoRemitente = ($f_remitente_rfc === $rfcExtranjero)
        ? $f_remitente_rfc . ' Id. Tributaria: ' . $f_remitente_numregidtrib
        : $f_remitente_rfc;

    $fRfcCompletoDestinatario = ($f_destinatario_rfc === $rfcExtranjero)
        ? $f_destinatario_rfc . ' Id. Tributaria: ' . $f_destinatario_numregidtrib
        : $f_destinatario_rfc;

    return [
        'remitente' => $fRfcCompletoRemitente,
        'destinatario' => $fRfcCompletoDestinatario
    ];
}

$rfcCompletos = concatenaRfcYRegId ($rfcExtranjero, $f_remitente_rfc, $f_remitente_numregidtrib, $f_destinatario_rfc, $f_destinatario_numregidtrib);

$fRfcCompletoRemitente = $rfcCompletos['remitente'];
$fRfcCompletoDestinatario = $rfcCompletos['destinatario'];

$datos_domicilios = [
   'estilo_fondo' => $estilo_fondo,
   'f_complemento_traslado' => $f_complemento_traslado,
   'forzar_domicilios' => $forzar_domicilios,
   'remitente_localidad_nombre' => $remitente_localidad_nombre,
   'destinatario_localidad_nombre' => $destinatario_localidad_nombre,
   'f_codigoorigen' => $f_codigoorigen,
   'f_remitente' => $f_remitente,
   'f_remitente_rfc' => $fRfcCompletoRemitente,
   'f_remitente_calle' => $f_remitente_calle,
   'f_remitente_numext' => $f_remitente_numext,
   'remitente_colonia_nombre' => $remitente_colonia_nombre,
   'remitente_municipio_nombre' => $remitente_municipio_nombre,
   'remitente_estado_nombre' => $remitente_estado_nombre,
   'f_remitente_cp' => $f_remitente_cp,
   'f_remitente_pais' => $f_remitente_pais,
   'f_citacarga' => $f_citacarga,
   'f_codigodestino' => $f_codigodestino,
   'f_destinatario' => $f_destinatario,
   'f_destinatario_rfc' => $fRfcCompletoDestinatario,
   'f_destinatario_calle' => $f_destinatario_calle,
   'f_destinatario_numext' => $f_destinatario_numext,
   'destinatario_colonia_nombre' => $destinatario_colonia_nombre,
   'destinatario_municipio_nombre' => $destinatario_municipio_nombre,
   'destinatario_estado_nombre' => $rdestinatario_estado_nombre,
   'f_destinatario_cp' => $f_destinatario_cp,
   'f_destinatario_pais' => $f_destinatario_pais,
   'f_destinatario_citacarga' => $f_destinatario_citacarga,
   'f_DistanciaRecorrida' => $f_DistanciaRecorrida,
   'f_seEntrega' => $f_seEntrega,
   'f_seRecoge' => $f_seRecoge,
   'remitenteTelefono' => $f_remitente_telefono,
   'destinatarioTelefono' => $f_destinatario_telefono

];

function domicilios($datos_domicilios){
	if ($datos_domicilios ['f_complemento_traslado'] >= 1 || $datos_domicilios['forzar_domicilios'] >= 1) {
		
			echo'<div>
			<table border="1" style="table-layout: fixed; width:100%; border-collapse: collapse; margin:0;">
				<thead>
					<tr>
						<td style="text-align:left; width:50%; font-size: 10px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Origen - '.$datos_domicilios ['remitente_localidad_nombre'].'</b>
						</td>
						<td style="text-align:left; width:50%; font-size: 10px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Destino - '.$datos_domicilios ['destinatario_localidad_nombre'].'</b>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align:left; width:50%; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Origen: '.$datos_domicilios ['f_codigoorigen'].'<br>
							Razón Social: '.$datos_domicilios ['f_remitente'].'<br>
							RFC: '.$datos_domicilios ['f_remitente_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_remitente_calle'].' No.'.$datos_domicilios ['f_remitente_numext'].'<br>
							'.'Col.'.$datos_domicilios ['remitente_colonia_nombre'].', '.$datos_domicilios ['remitente_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['remitente_estado_nombre'].' C.P.'.$datos_domicilios ['f_remitente_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_remitente_pais'].'<br>
							Identidad Tributaria: '.$datos_domicilios ['f_remitente_numregidtrib'].'<br>
							Fecha de Salida: '.$datos_domicilios ['f_citacarga'].'<br>';
							if (!empty($datos_domicilios ['f_seRecoge'])) {
								echo'
							Se Recogerá: '.$datos_domicilios ['f_seRecoge'].'
							<br>';
							}
							if (!empty($datos_domicilios ['remitenteTelefono'])) {
								echo'
							Teléfono: '.$datos_domicilios ['remitenteTelefono'].'<br>
							';
							}
					echo'
						</td>
						<td style="text-align:left; width:50%; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Destino: '.$datos_domicilios ['f_codigodestino'].'<br>
							Razón Social: '.$datos_domicilios ['f_destinatario'].'<br>
							RFC: '.$datos_domicilios ['f_destinatario_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_destinatario_calle'].' No.'.$datos_domicilios ['f_destinatario_numext'].'<br>
							'.'Col.'.$datos_domicilios ['destinatario_colonia_nombre'].', '.$datos_domicilios ['destinatario_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['destinatario_estado_nombre'].' C.P.'.$datos_domicilios ['f_destinatario_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_destinatario_pais'].'<br>
							Identidad Tributaria: '.$datos_domicilios ['f_destinatario_numregidtrib'].'<br>
							Fecha de Llegada: '.$datos_domicilios ['f_destinatario_citacarga'].'<br>';
							if (!empty($datos_domicilios ['f_seEntrega'])) {
								echo'
							Se Entregará: '.$datos_domicilios ['f_seEntrega'].'<br>
							';
							}
							if (!empty($datos_domicilios ['destinatarioTelefono'])) {
								echo'
							Teléfono: '.$datos_domicilios ['destinatarioTelefono'].'<br>
							';
							}
					echo'
							Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km <br>
							Total Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km
						</td>
					</tr>
				</tbody>
			</table>
		</div>';
						}	
	}

//Bloque consolidad activar con unidos

$esConsolodidado = 0;
/* 
$resSQL = "SELECT EsConsolidado FROM {$prefijobd}factura WHERE ID = {$id_factura}";
$runSQL = mysqli_query($cnx_cfdi2, $resSQL);
$rowSQL = mysqli_fetch_assoc($runSQL);
if ($rowSQL) {
	if (!isset($rowSQL['EsConsolidado'])) {
		$esConsolodidado = 0;
	}else{
	$esConsolodidado = $rowSQL['EsConsolidado'];
	}
}*/
if ($esConsolodidado >= 1) {
	$nombre_factura = "Factura/Consolidado";
}else{
	$nombre_factura = "Factura";

} 

$fechaFactura = '';
if (!empty($f_cfdfolio)){
$fechaFactura = date("d-m-Y H:i:s", strtotime($f_cfdffcchh));

} else {

$fechaFactura = date("d-m-Y H:i:s", strtotime($f_creado));
}

ob_start();

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<link rel="stylesheet" href="css/style.css">-->
		
		<style>
			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
    		}
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: helvetica;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 0px; 
				font-family: helvetica;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: helvetica;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
		
		<!--
			border-width: 1px;
			border-style: solid;
			border-color: black;
		-->
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
		
		
		
		<title>Factura<?php echo ': '.$f_xfolio ;?></title>	
	</head>
	<body>
	
	<htmlpageheader name="myHeader">
			<div style = "padding-top: -20px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:left;  vertical-align:top;">  
							<img src="<?php echo $rutalogo; ?>" style="width: 140px; height: auto;" alt="Logo" /> 
						</td>
						<td style="text-align:center; width:40%; font-size: 12px;">
							<?php if ($cambio_a_nombre_comercial == '1'){
								echo '<strong>'.$nombre_comercial.'</strong><br/>';
							} ?>
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' '.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$domicilioRestante.' <br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:35%; font-size: 13px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-size: 16px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b><?php echo $nombre_factura; ?></b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;">I -Ingreso</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><label style="color:red"><b style="color:red"><?php echo $f_xfolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><?php echo date("d-m-Y H:i:s", strtotime($fechaFactura));?></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><b>Referencia</b></td>
									<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><?php cambioReferencia($se_cambia_ticket,  $prefijobd, $id_factura, $cnx_cfdi2, $f_ticket ); ?></td>
								</tr>
								<?php if ($f_addendas == 'HERDEZ') { ?>
									<tr>
										<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><b>Folio Manhattan</b></td>
										<td style="text-align:left; width:50%; font-size: 13px;padding: 1;vertical-align: center;"><?php echo $f_AddCampoA; ?></td>
									</tr>
								<?php } ?>
							</table>
						</td>
					</tr>

				</table>
			</div>
			</htmlpageheader>
			<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
		
		
			<htmlpagefooter name="myFooter">
			
			<div styles="margin-top:85px">
				<table  style="margin:0;border-collapse: collapse; border: 1px solid rgba(128, 128, 128, 0.5);" width="100%">
					<?php if ($f_lleva_leyenda == '1') { ?>
						
					
					<thead style="<?php echo $f_footer_leyenda_color; ?>"><tr>
						<th colspan="3" style="font-size:9px;text-align:center;width:70%;padding-left:100px;<?php echo $f_footer_leyenda_color; ?>"><?php echo $f_footer_leyenda;?></th>
					</tr></thead>
					<tbody>
						<tr style="margin:0; padding:0" >
							
									<?php 
							
								$resSQL44 = "SELECT cfdiuuidRelacionado, XFolio FROM {$prefijobd}facturauuidrelacionadosub WHERE FolioSub_RID = {$id_factura}";
								$runSQL44 = mysqli_query($cnx_cfdi2, $resSQL44);
								       if (mysqli_num_rows($runSQL44) > 0 && mysqli_num_rows($runSQL44) < 3) {
								$datos = []; ?>
							<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='2'>
								<b>Comentarios: <?php echo $f_comentarios; ?></b>
								
							</td>
							<td style="text-align:left; width:30%; font-size: 9px;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5);vertical-align:right;">
								<b>CFDI Relacionado: </b>
									<?php
								while ($rowSQL44 = mysqli_fetch_array($runSQL44)) {
									$uuidrelacionado = $rowSQL44['cfdiuuidRelacionado'];
									$uuidXfolio = $rowSQL44['XFolio'];
									$datos[] = $uuidrelacionado . '-' . $uuidXfolio;
								}
								echo implode(', ', $datos);
							?> </td> <?php
							} else { ?>
								<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='3'>
								<b>Comentarios: <?php echo $f_comentarios; ?></b>
								
							</td>
						<?php	} ?>
							
						</tr>
					</tbody>
						<?php } else { ?>
							
							<tr style="margin:0; padding:0" >
							
									<?php 
							
							$resSQL44 = "SELECT cfdiuuidRelacionado, XFolio FROM {$prefijobd}facturauuidrelacionadosub WHERE FolioSub_RID = {$id_factura}";
							$runSQL44 = mysqli_query($cnx_cfdi2, $resSQL44);
							if (mysqli_num_rows($runSQL44) > 0 && mysqli_num_rows($runSQL44) < 3) {
								$datos = []; ?>
							<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='2'>
								<b>Comentarios: <?php echo $f_comentarios; ?></b>
								
							</td>
							<td style="text-align:left; width:30%; font-size: 9px;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5);vertical-align:right;">
								<b>CFDI Relacionado: </b>
									<?php
								while ($rowSQL44 = mysqli_fetch_array($runSQL44)) {
									$uuidrelacionado = $rowSQL44['cfdiuuidRelacionado'];
									$uuidXfolio = $rowSQL44['XFolio'];
									$datos[] = $uuidrelacionado . '-' . $uuidXfolio;
								}
								echo implode(', ', $datos);
						?> </td> <?php
								} else { ?>
									<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='3'>
										<b>Comentarios: <?php echo $f_comentarios; ?></b>		
									</td>
								<?php	} ?>
									
							</tr>
					
						<?php } ?>
				</table>
				
			</div>
		
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
						
						<td style="text-align:center; width:80%; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Este documento es una representación impresa de un CFDI</b>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:25%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Serie del Certificado del emisor:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdnocertificado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:25%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Folio Fiscal:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdiuuid; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:25%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<b>No. de serie del Certificado del SAT:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdinoCertificadoSAT; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:25%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Fecha y hora de certificación:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:9px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdifechaTimbrado; ?>
						</td>
					</tr>
				</table>
			</div>
			<table border="0" style="width:100%; border-collapse:collapse; margin-top:5px;">
					<tr>
						<td colspan="3" style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px;"><b>SELLOS</b></td>
					</tr>
					<tr>
						<td style="text-align:center; font-size:10px;<?php echo $estilo_fondo; ?>font-size:12px;"><b>Sello digital del CFDI</b></td>
						<td style="text-align:center; font-size:10px;<?php echo $estilo_fondo; ?>font-size:12px;"><b>Cadena original</b></td>
						<td style="text-align:center; font-size:10px;<?php echo $estilo_fondo; ?>font-size:12px;"><b>Sello del SAT</b></td>				
					
					</tr>
					
					<tr>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 7px;"><?php echo $f_cfdiselloSAT; ?></td>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 7px;"><?php echo $f_cfdsellodigital; ?></td>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 7px;"><?php echo $f_cfdiselloCadenaOriginal; ?></td>
					</tr>
    		</table>
			<table width="100%" style="font-size: 8pt;">

					<tr>	
						<td width="33%">Versión del comprobante: 4.0</td>
						<td width="33%" align="right"> <?php if ($f_complemento_traslado >= 1) { ?>Complemento Carta Porte Versión 3.1 <?php } ?> </td>
						<td width="33%" align="right">Página {PAGENO}</td>
					</tr>
				</table>
			</htmlpagefooter>
		<sethtmlpagefooter name="myfooter" value="on" show-this-page="1" />
	
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
			
			<div>
				
				<?php 
				recuadroArriba ($campo_original, $campo_alias, $inicio_del_campo, $lleva_recuadro_arriba, $prefijobd, $id_factura, $cnx_cfdi2);

				imprimirTablaCliente($datos_cliente, $llevaCodigoBarras, $fileName); //toda la estructura html de impresion se encuentra declarada dentro de la funcion :1351

				
				domicilios($datos_domicilios);//toda la estructura html de impresion se encuentra declarada dentro de la funcion :1416	
			 
		
			$parametro_columna_ex = 925;
			$resSQL925 = "SELECT id2, VCHAR, VLOGI, dsc, MEMO FROM {$prefijobd}parametro Where id2 =$parametro_columna_ex";
			$runSQL925 = mysqli_query($cnx_cfdi2, $resSQL925);
				 
			$lleva_row_extra = 0;

			// al agregar el vchar desde la plataforma se debe agregar el f. o el c. para que se haga la consulta correcta, 
			//y el dsc es el alias del campo, MEMO seria el nombre que se mostrara en la tabla del PDF


			while ($rowSQL925 = mysqli_fetch_array($runSQL925)) {
				$param= $rowSQL925['id2'];
				$variabledinamica= $rowSQL925 ['VCHAR'];
				$nombreCampoPartida = $rowSQL925 ['MEMO'];
				$descripcion= $rowSQL925['dsc'];
				$lleva_row_extra = $rowSQL925['VLOGI'];
				
			}
					?>
			
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr style="margin:0; padding:0">
							<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Clave Unidad SAT</b>
							</th>
							
							<th style="text-align:center; width:39%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Descripción</b>
							</th>
							<?php if ($lleva_row_extra == 1) {?>
							<th style="text-align:center; width:13%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b><?php echo $nombreCampoPartida; ?></b>
						</th>
							<?php } ?>
							
							<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Valor Unitario</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
										<b>SubTotal</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Impuestos</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Retenciones</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Importe</b>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php 

					if ($lleva_row_extra == 1 ) {
						
							$resSQL18 = "SELECT f.Tipo as Tipo,
												f.Subtotal1 as Subtotal1,
												f.Subtotal as Subtotal,
												f.RetencionImporte as RetencionImporte,
												f.Retencion as Retencion,
												f.prodserv33dsc as prodserv33d,
												f.prodserv33 as prodserv33f,
												f.PrecioUnitario as PrecioUnitario, 
												f.IVAImporte as IVAImporte,
												f.IVA as IVA, 
												f.Importe as Importe, 
												f.FolioSub_RMA as FolioSub_RMA,
												f.FolioSub_RID as FolioSub_RID,
												f.FolioSub_REN as FolioSub_REN,
												f.FolioConceptos_RMA as FolioConceptos_RMA,
												f.FolioConceptos_RID as FolioConceptos_RID,
												f.FolioConceptos_REN as FolioConceptos_REN,
												f.Excento as Excento,
												f.Detalle as Detalle,
												f.DescuentoImporte as DescuentoImporte,
												f.Descuento as Descuento,
												f.DescripcionClaveUnidad as DescripcionClaveUnidad,
												f.ConceptoPartida as ConceptoPartida,
												f.claveunidad33 as claveunidad33f,
												f.Cantidad as Cantidad,
												f.BASVERSION as BASVERSION,
												f.BASTIMESTAMP as BASTIMESTAMP,
												c.Concepto as ConceptoPartida,
												c.claveunidad33 as claveunidad33,
												c.prodserv33 as prodserv33,
												c.prodserv33dsc as prodserv33dsc,
												 ". $variabledinamica."  as  ".$descripcion. "
												  FROM ".$prefijobd."FacturaPartidas f LEFT OUTER JOIN ".$prefijobd."Conceptos c on
												f.FolioConceptos_RID = c.id WHERE FolioSub_RID=".$id_factura ;
					} else {
								$resSQL18 = "SELECT
													f.Tipo as Tipo,
													f.Subtotal1 as Subtotal1,
													f.Subtotal as Subtotal,
													f.RetencionImporte as RetencionImporte,
													f.Retencion as Retencion,
													f.prodserv33dsc as prodserv33d,
													f.prodserv33 as prodserv33f,
													f.PrecioUnitario as PrecioUnitario, 
													f.IVAImporte as IVAImporte,
													f.IVA as IVA, 
													f.Importe as Importe, 
													f.ID as ID,
													f.FolioSub_RMA as FolioSub_RMA,
													f.FolioSub_RID as FolioSub_RID,
													f.FolioSub_REN as FolioSub_REN,
													f.FolioConceptos_RMA as FolioConceptos_RMA,
													f.FolioConceptos_RID as FolioConceptos_RID,
													f.FolioConceptos_REN as FolioConceptos_REN,
													f.Excento as Excento,
													f.Detalle as Detalle,
													f.DescuentoImporte as DescuentoImporte,
													f.Descuento as Descuento,
													f.DescripcionClaveUnidad as DescripcionClaveUnidad,
													f.ConceptoPartida as ConceptoPartida,
													f.claveunidad33 as claveunidad33f,
													f.Cantidad as Cantidad,
													f.BASVERSION as BASVERSION,
													f.BASTIMESTAMP as BASTIMESTAMP, 
													c.Concepto as ConceptoPartida, 
													c.claveunidad33 as claveunidad33, 
													c.prodserv33 as prodserv33, 
													c.prodserv33dsc as prodserv33dsc   FROM ".$prefijobd."FacturaPartidas f LEFT OUTER JOIN ".$prefijobd."Conceptos c on f.FolioConceptos_RID = c.id WHERE FolioSub_RID=".$id_factura ;
					}
						

									
						
						$runSQL18 = mysqli_query( $cnx_cfdi2 ,$resSQL18);
						
						if (!$runSQL18) {
							echo "Error en la consulta: " . mysqli_error($cnx_cfdi2);
						}

						$contadorPartida = 0;
						$porHojaPartita = 8;
						$primerSaltoPartida = true;

						function imprimirEncabezadoPartida($estilo_fondo, $lleva_row_extra, $nombreCampoPartida) {
							
							if ($lleva_row_extra == 1) {
								echo '
						<table border="1" style="margin:0;border-collapse: collapse;" width="100%;">
							<thead>
								<tr style="margin:0; padding:0">
									<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Cantidad</b>
									</th>
									<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Clave Unidad SAT</b>
									</th>
									
									<th style="text-align:center; width:39%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Descripción</b>
									</th>
									<th style="text-align:center; width:13%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
									<b>'. $nombreCampoPartida.'</b>
								</th>
									
									
									<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Valor Unitario</b>
									</th>
									<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>SubTotal</b>
									</th>
									<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Impuestos</b>
									</th>
									<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Retenciones</b>
									</th>
									<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
										<b>Importe</b>
									</th>
								</tr>
							</thead>
						<tbody>';
							} else {
								echo '
								<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
									<thead>
										<tr style="margin:0; padding:0">
											<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Cantidad</b>
											</th>
											<th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Clave Unidad SAT</b>
											</th>
											
											<th style="text-align:center; width:39%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Descripción</b>
											</th>
											<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Valor Unitario</b>
											</th>
											<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>SubTotal</b>
											</th>
											<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Impuestos</b>
											</th>
											<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Retenciones</b>
											</th>
											<th style="text-align:center; width:9%; font-size: 10px;vertical-align:center;'.$estilo_fondo.'">
												<b>Importe</b>
											</th>
										</tr>
									</thead>
								<tbody>';
							}
							
						
					}

						$totalDescuento = 0;
						while($rowSQL18 = mysqli_fetch_array($runSQL18)){
							if ($contadorPartida > 0 && $contadorPartida ==4 && $primerSaltoPartida) {
								echo '</tbody></table>';
								
								echo '<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" /><pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />';
								 imprimirEncabezadoPartida($estilo_fondo, $lleva_row_extra, $nombreCampoPartida); 
								 $primerSaltoPartida = false;
	
								} elseif (!$primerSaltoPartida && ($contadorPartida - 4) % 13 == 0) {
									// Cada 22 después del primero
									echo '</tbody></table>';
									
									echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />';
									imprimirEncabezadoPartida($estilo_fondo, $lleva_row_extra, $descripcion);
								}
							$fp_cantidad_t = $rowSQL18['Cantidad'];
							$fp_cantidad = number_format($fp_cantidad_t, $numDecimales); 
							$fp_claveunidad33 = $rowSQL18['claveunidad33'];
							$fp_prodserv33 = $rowSQL18['prodserv33'];
							$fp_prodserv33dsc = $rowSQL18['prodserv33dsc'];
							$fp_ConceptoPartida = $rowSQL18['ConceptoPartida'];
							$fp_Detalle = $rowSQL18['Detalle'];
							$fp_Detalle = preg_replace("/\t+/", '<span style="display:inline-block; width:30px;"></span>', $fp_Detalle);
							$fp_Detalle = nl2br($fp_Detalle);
							//$fp_xfolio_dataset16 = $rowSQL18[''];
							$fp_PrecioUnitario_t = $rowSQL18['PrecioUnitario'];
							$fp_PrecioUnitario = number_format($fp_PrecioUnitario_t, $numDecimales); 
							$fp_IVAImporte_t = $rowSQL18['IVAImporte'];
							$fp_IVAImporte = number_format($fp_IVAImporte_t, $numDecimales); 
							$fp_RetencionImporte_t = $rowSQL18['RetencionImporte'];
							$fp_RetencionImporte = number_format($fp_RetencionImporte_t, $numDecimales); 
							$fp_Importe_t = $rowSQL18['Importe'];
							$fp_tipo = $rowSQL18['Tipo'];
							$fp_Subtotal1=  $rowSQL18['Subtotal1'];
							$fp_Subtotal=  $rowSQL18['Subtotal'];
							$fp_Retencion = $rowSQL18['Retencion'];
							$fp_IVA = $rowSQL18['IVA'];
							/* $fp_ISRImporte = $rowSQL18['ISRImporte'];
							$fp_ISR = $rowSQL18['ISR']; */
							$fp_DescuentoImporte= $rowSQL18['DescuentoImporte'];
							$fp_Descuento = $rowSQL18['Descuento'];
							$fp_DescripcionClaveUnidad = $rowSQL18['DescripcionClaveUnidad'];
							/* $fp_CobranzaSaldo= $rowSQL18 ['CobranzaSaldo'];
							$fpCobranzaAbonado = $rowSQL18 ['CobranzaAbonado']; */

							$fp_valordinamico = $rowSQL18[$descripcion];
								

							$fp_Importe = number_format($fp_Importe_t, $numDecimales); 
							$totalDescuento += $fp_DescuentoImporte;
							
							
					?>
					<tr>
						<td style="text-align:center; font-size: 10px;"><?php echo $fp_cantidad; ?></td>
						<td style="text-align:center; font-size: 10px;"><?php echo $fp_claveunidad33.' - '.$fp_DescripcionClaveUnidad; ?></td>
						<td style="text-align:left; font-size: 10px; vertical-align:left;">  <?php echo $fp_ConceptoPartida.': <br> '.$fp_Detalle.'
						<br> <p style=font-size:8px;><b>Clave Producto/Servicio: </b>'.$fp_prodserv33.' - '.$fp_prodserv33dsc.'<p>';?></td>
						<?php if ($lleva_row_extra == 1) { ?>
							<td style="text-align:center; font-size: 10px;"><?php echo $fp_valordinamico; ?></td>
						<?php } ?>
						<td style="text-align:center; font-size: 10px;"><?php echo "$ ".$fp_PrecioUnitario; ?></td>
						<td style="text-align:center; font-size: 10px;"><?php echo "$ ".number_format((float)$fp_Subtotal,2,'.',','); ?></td>
						<td style="text-align:center; font-size: 10px;"><?php echo "$ ".$fp_IVAImporte; ?></td>
						<td style="text-align:center; font-size: 10px;"><?php echo "$ ".$fp_RetencionImporte; ?></td>
						<td style="text-align:center; font-size: 10px;"><?php echo "$ ".$fp_Importe; ?></td>
					</tr>
					<?php
						$contadorPartida++;
						}
					?>
					</tbody>
				</table>
			</div>
			
			<div>
	<table width="100%" style="border-collapse: collapse;">
		<tr>
			<td style="width: 15%;"></td> <!-- Espacio para QR -->
			<td style="text-align:center; font-size: 12px; padding: 2px; vertical-align: middle; <?php echo $estilo_fondo; ?>"><b>Total con letra:</b></td>
			<td style="text-align:left; font-size: 12px; padding: 2px; vertical-align: middle;"><b>Subtotal:</b></td>
			<td style="text-align:right; font-size: 12px; padding: 2px; vertical-align: middle;"><?php echo "$".$f_subtotal; ?></td>
		</tr>

		<tr>
			<td style="text-align:left;">
				<img src='file:///C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'.svg'?>' width="100" height="100" alt="QR"/>
			</td>
			<td style="font-size: 11px; padding: 2px; vertical-align: top;">
				<b style="text-align:center;"><?php echo $f_total_letra; ?></b><br><br>
				<b style="text-align:left;">Uso CFDI:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?><br>
				<b style="text-align:left;">Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?><br>
				<b style="text-align:left;">Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?><br>
				<?php
				if ($f_moneda === "PESOS") {
					echo "<b>MXN - PESOS</b>";
				} else {
					echo "<b>USD - DÓLARES<br>Tipo Cambio: $f_tipocambio</b>";
				}
				if ($f_addendas === 'HAPAG') {
					echo "<br><b>WO_Contenedor : $f_AddCampoA"." "."</b>";
				}
				?>
			</td>

			<td style="text-align:left; font-size: 12px; padding: 2px; vertical-align: top;">
				<b>IVA:</b>
				<?php if ($f_retenido != 0) echo "<br><b>IVA Retenidos:</b>"; ?>
				<?php if ($totalDescuento >= 1) echo "<br><b>Descuento:</b>"; ?>

				<?php if ($f_ISR >= 1) echo "<br><b>ISR:</b>"; ?>
				<br><b>Total:</b>
			</td>

			<td style="text-align:right; font-size: 12px; padding: 2px; vertical-align: top;">
				<?php echo "$".$f_impuesto; ?>
				<?php if ($f_retenido != 0) echo "<br>$".$f_retenido; ?>
				<?php if ($totalDescuento >= 1) echo "<br>$".number_format((float)$totalDescuento, $numDecimales); ?>

				<?php if ($f_ISR >= 1) echo "<br>$".number_format((float)$f_ISR, $numDecimales); ?>
				<br><?php echo "$".$f_total; ?>
			</td>
		</tr>

		<tr>
			<td colspan="4" style="font-size: 8px; padding: 2px;">&nbsp;</td>
		</tr>
		</table>
		<?php if ($lleva_unidad_operador == 1 && $f_complemento_traslado >= 1) { ?>
				<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:150%; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Figuras de Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura</b>
						</td>
						<td style="text-align:center; width:30%; font-size: 11px;vertical-align:center;">
							<b>Nombre</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>RFC</b>
						</td>
						<td style="text-align:center; width:5%; font-size: 11px;vertical-align:center;">
							<b>C.P.</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>No. Licencia</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>Estado</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>Residencia Fiscal</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Id. Tributaria</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_estado; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria; ?>
						</td>
					</tr>
					<?php if ($f_operador_id2 > 1) { ?>
						
					
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura 2</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 11px;vertical-align:center;">
							<b>Nombre 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>RFC 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>C.P. 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>No. Licencia 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Estado 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Residencia Fiscal 2</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Id. Tributaria 2</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura_2; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_estado_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria_2; ?>
						</td>
					</tr>
					<?php } 
					
					if ($ft_op1_ID != 0) { ?>
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center; ">
							<b>Parte Transporte</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Parte Transporte </b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Parte Transporte</b>
						</td>
						<td></td>
						<td style="text-align:center; width:25%; font-size: 11px;vertical-align:center;">
							<b>Figura Transporte</b>
						</td>
						<td></td>
						<td style="text-align:center; width:25%; font-size: 11px;vertical-align:center;">
							<b>Figura Transporte</b>
						</td>
						
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte1; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte3; ?>
						</td><td></td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $figura_transporte1; ?>
						</td><td></td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $figura_transporte2; ?>
						</td>
						
					</tr>
					
					<?php
						}
					?>

				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:10%; font-size: 11px;vertical-align:center;">
							<?php echo "<b>Permiso SCT:</b><br>".$PermisoSCT; ?>
						</td>
						<td style="text-align:left; width:10%; font-size: 11px;vertical-align:center;">
							<?php echo "<b>No. Unidad o Remolque:</b>" ?>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Peso B. Vehicular</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Aseguradora</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Número Póliza</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Unidad/Placa</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Año</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 11px;vertical-align:center;">
							<b>Configuración Vehicular / Tipo Remolque</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 11px;vertical-align:center;">
						<?php echo '<b>Número de Permiso:</b><br>'.$TipoPermisoSCT; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<b>1.-</b>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $pesobruto_factura. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_nombre. ' / '.$unidad_placas; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_anio; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;word-wrap: break-word; overflow-wrap: break-word;">
							<?php echo $configautotransporte_clavenomenclatura.": ".$configautotransporte_descripcion ; ?>
						</td>
					</tr>
					</table>
					</div>
		<?php	} ?>
	
</div>
			
			
			<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />
			<!-- modulo de folios relacionados
					Solo se va a mostrar cuando sean mas de 4 -->

			<?php 
				$resSQL44 = "SELECT cfdiuuidRelacionado, XFolio FROM {$prefijobd}facturauuidrelacionadosub WHERE FolioSub_RID = {$id_factura}";
				$runSQL44 = mysqli_query($cnx_cfdi2, $resSQL44);
				if (mysqli_num_rows($runSQL44) > 3  ) { ?> 
					<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
						<table border="0" style="margin:0;border-collapse: collapse;" width="100%">	
							<thead>
								<tr>
									<th style="text-align:center; width:100%; font-family: Helvetica; font-size: 15px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
										CFDI Relacionados
									</th>
								</tr>
							</thead>
							<tbody>

								<tr style="margin:0; padding:0" >
									
									<td style="text-align:center; width:50%; font-family: Helvetica; font-size: 15px;vertical-align:center;<?php echo $estilo_fondo; ?>">
										<b>Folio</b>
									</td>
									<td style="text-align:center; width:50%; font-family: Helvetica; font-size: 15px;vertical-align:center;<?php echo $estilo_fondo; ?>">
										<b>UUID Relacionado</b>
									</td>
								</tr>
								<?php 
									
									
									while ($rowSQL44 = mysqli_fetch_array($runSQL44)) {
										$uuidrelacionado = $rowSQL44['cfdiuuidRelacionado'];
										$uuidXfolio = $rowSQL44['XFolio'];
									?>
									<tr style="margin:0; padding:0" >
								
										<td style="text-align:center; width:50%; font-family: Helvetica; font-size: 13px;vertical-align:center;">
											<b><?php echo $uuidXfolio; ?></b>
										</td>
										<td style="text-align:center; width:50%; font-family: Helvetica; font-size: 13px;vertical-align:center;">
											<b><?php echo $uuidrelacionado; ?></b>
										</td>
									</tr>
								<?php	} ?>	
							</tbody>
						</table>
			<?php	} ?>

			<!-- FIN Subreporte 1 -->
			
			<!-- <div class="page-break"></div> -->
			
			<?php 
			if($f_complemento_traslado >= 1){
			?>
			
			<!-- Subreporte 2 -->

			<pagebreak /><htmlpageheader name="myHeader3">
			<div style = "padding-top: -15px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:left;  vertical-align:top; width:25%;">  
							<img src="<?php echo $rutalogo; ?>" style="width: 150px; height: auto;" alt="Logo" /> 
						</td>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 12px;">
							<?php if ($cambio_a_nombre_comercial == '1'){
									echo '<strong>'.$nombre_comercial.'</strong><br/>';
								} ?>
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' '.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$domicilioRestante.' <br/>Régimen Fiscal: '.$Regimen.''; ?><br/>

							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:35%; font-family: Helvetica; font-size: 13px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-family: Helvetica; font-size: 16px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Complemento Carta Porte</b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;">I -Ingreso</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><label style="color:red"><b style="color:red"><?php echo $f_xfolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><?php if (!empty($f_cfdfolio)){
										echo date("d-m-Y H:i:s", strtotime($f_cfdffcchh));} else {
											echo date("d-m-Y H:i:s", strtotime($f_creado));
										} ?></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b>Referencia</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><?php cambioReferencia($se_cambia_ticket,  $prefijobd, $id_factura, $cnx_cfdi2, $f_ticket); ?></td>
								</tr>
								<?php if ($f_addendas == 'HERDEZ') { ?>
									<tr>
										<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b>Folio Manhattan</b></td>
										<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><?php echo $f_AddCampoA; ?></td>
									</tr>
								<?php } ?>
							</table>
						</td>
					</tr>

				</table>
			</div>
			</htmlpageheader>
			<sethtmlpageheader name="myHeader3" value="on" show-this-page="all" />
			<htmlpagefooter name="myFooter3">
				<table>
					<?php 	if ($f_lleva_leyenda) {
					echo '<div style="text-align:center; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:center; '.$f_footer_leyenda_color.'"><b>'.$f_footer_leyenda.'</b></div>';
				}
				?>
					<tr>
						<td style="text-align:left; width:25%;"><img src="C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'_CCP.svg'; ?>" width="90px" height="90px" alt="QR"/></td>
						<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='2'><b> <?php  
						if ($partida_enComen >= 1) {
							$resSQL18 = "SELECT  f.Detalle as Detalle,
												 f.ConceptoPartida as ConceptoPartida, 
												 c.Concepto as ConceptoPartida
										FROM {$prefijobd}FacturaPartidas f LEFT  JOIN {$prefijobd}Conceptos c on f.FolioConceptos_RID = c.id WHERE FolioSub_RID=".$id_factura;
										$runSQL18 = mysqli_query($cnx_cfdi2, $resSQL18);
										$rowSQL18 = mysqli_fetch_array($runSQL18);
										if ($rowSQL18) {
											$fp_ConceptoPartida = $rowSQL18['ConceptoPartida'];
											$fp_Detalle = $rowSQL18['Detalle'];
											echo $fp_ConceptoPartida.' - '.$fp_Detalle;
										} else {
											echo 'Comentarios: '.$f_comentarios. '<br>Instrucciones: '. $f_cp_instrucciones;
									}

						} else{
							echo 'Instrucciones: '. $f_cp_instrucciones;
					}?></b></td>
					</tr>
				</table>
				<table width="100%" style="font-size: 8pt;">
					<tr>
						<td width="33%">Versión del comprobante: 4.0</td>
						<td width="33%" align="right"><?php if ($f_complemento_traslado >= 1) { ?>Complemento Carta Porte Versión 3.1<?php } ?> </td>
						<td width="33%" align="right">Página {PAGENO}</td>
					</tr>
				</table>
			</htmlpagefooter>
			<sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" />


			<br>
			<div>
				<?php imprimirTablaCliente($datos_cliente, $llevaCodigoBarras, $fileName); 
				domicilios($datos_domicilios); ?>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 15px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="5">
							<b>Detalle del Complemento Carta Porte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width: 20%; font-size:15px; vertical-align:center;">
							<b>VersionCCP</b>
						</td>
						<td style="text-align:center; width:30%; font-size: 15px;vertical-align:center;">
							<b>Medio de Transporte</b>
						</td>
						<td style="text-align:center; width:50%; font-size: 15px;vertical-align:center;">
							<b>IDCCP</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 15px;vertical-align:center;">
							<b>Tipo de Transporte</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 15px;vertical-align:center;">
							<b>Transporte Internacional</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-size: 15px;vertical-align:center;">
								<?php echo $f_versionCCP; ?>
						</td>
						<td style="text-align:center; width:30%; font-size: 13px;vertical-align:center;">
							<?php echo '01 - Autotransporte Federal'; ?>
						</td>
						<td style="text-align:center; width:50%; font-size: 15px;vertical-align:center;">
							<?php echo $f_idCCP; ?>
						</td>
						<td style="text-align:center; width:20%; font-size: 15px;vertical-align:center;">
							<?php echo $f_tipo_viaje; ?>
						</td>
						<td style="text-align:center; width:40%; font-size: 15px;vertical-align:center;">
							<?php echo $f_transporte_internacional; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:10%; font-size: 11px;vertical-align:center;">
							<?php echo "<b>Permiso SCT:</b><br>".$PermisoSCT; ?>
						</td>
						<td style="text-align:left; width:10%; font-size: 11px;vertical-align:center;">
							<?php echo "<b>No. Unidad o Remolque:</b>" ?>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Peso B. Vehicular</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Aseguradora</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Número Póliza</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Unidad/Placa</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Año</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 11px;vertical-align:center;">
							<b>Configuración Vehicular / Tipo Remolque</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 11px;vertical-align:center;">
						<?php echo '<b>Número de Permiso:</b><br>'.$TipoPermisoSCT; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<b>1.-</b>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $pesobruto_factura. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_nombre. ' / '.$unidad_placas; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_anio; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;word-wrap: break-word; overflow-wrap: break-word;">
							<?php echo $configautotransporte_clavenomenclatura.": ".$configautotransporte_descripcion ; ?>
						</td>
					</tr>
				<?php if (($f_unidad_id2 > 1)||($f_permisionario_id > 1)) { ?>
					
					<tr>
						<td style="text-align:left; font-size: 11px;vertical-align:center;">
							<?php echo '<b>Permisionario</b>'; ?>
							
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<b>2.-</b>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_peso. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_nombre. ' / '.$unidad_2_placas; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_anio; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;word-wrap: break-word; overflow-wrap: break-word;">
							<?php echo $configautotransporte_2_clavenomenclatura.': '.$configautotransporte_2_descripcion; ?>
						</td>
					</tr>
					<?php	} 
					
					 if ($f_remolque_id > 1) { ?>
						
						<tr>
							<td style="text-align:left; font-size: 11px;vertical-align:center;">
								<b>Permiso Remolque: <br><?php echo $remolque_permisoSCT;?></b>
							</td>
							<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo ' <b>Rem 1.-</b>'; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo ' - '; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_aseguradora_nombre; ?>
							
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $remolque_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $remolque_nombre. ' / '.$remolque_placas; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $remolque_anio; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;word-wrap: break-word; overflow-wrap: break-word;">
							<?php echo $remolque_clave_tipo_remilque.': '.$remolque_remolque_semiremolque; ?>
						</td>
					</tr>
					<?php	}	?>
					<?php if (($f_remolque2_id > 1) || ($f_dolly_id > 1)){?>
					<tr>
						<?php if ($f_dolly_id > 1) { ?>
				
						<td style="text-align:left; font-size: 11px;vertical-align:center;" >
							<?php echo '<b>Dolly/ Placa/ Año</b><br>'.$dolly_nombre.'/ '.$dolly_placas.'/ '.$dolly_anio;?>							
						</td>
						<?php }else { ?>
						  <td style="text-align:left; font-size: 11px;vertical-align:center;" >
														
						</td>
						<?php } ?>

						<?php if ($f_remolque2_id > 1) { ?>
						
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo ' <b>Rem 2.-</b>'; ?>						
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<b>-</b>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>

						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>

						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_nombre.' / '.$remolque2_placas; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_anio; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;word-wrap: break-word; overflow-wrap: break-word;">
							<?php echo $remolque2_clave_tipo_remilque.': '.$remolque2_remolque_semiremolque; ?>
						</td>
						<?php } ?>
					</tr>
					<?php } 
					if (!empty($f_poliza)){ ?>
					<tr>
						<td style="text-align:left; font-size: 11px;vertical-align:center;">
							<?php echo '<b>Seguro mercancia: </b>'.$f_poliza.' - '.$f_aseguradora; ?>
							
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
						</td>
					</tr>

					<?php	
					}
					?>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:150%; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Figuras de Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura</b>
						</td>
						<td style="text-align:center; width:30%; font-size: 11px;vertical-align:center;">
							<b>Nombre</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>RFC</b>
						</td>
						<td style="text-align:center; width:5%; font-size: 11px;vertical-align:center;">
							<b>C.P.</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>No. Licencia</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>Estado</b>
						</td>
						<td style="text-align:center; width:15%; font-size: 11px;vertical-align:center;">
							<b>Residencia Fiscal</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Id. Tributaria</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_estado; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria; ?>
						</td>
					</tr>
					<?php if ($f_operador_id2 > 1) { ?>
						
					
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura 2</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 11px;vertical-align:center;">
							<b>Nombre 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>RFC 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>C.P. 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>No. Licencia 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Estado 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center;">
							<b>Residencia Fiscal 2</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Id. Tributaria 2</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura_2; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_estado_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal_2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria_2; ?>
						</td>
					</tr>
					<?php } 
					
					if ($ft_op1_ID != 0) { ?>
					<tr>
						<td style="text-align:center; width:10%; font-size: 11px;vertical-align:center; ">
							<b>Parte Transporte</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Parte Transporte </b>
						</td>
						<td style="text-align:center; width:20%; font-size: 11px;vertical-align:center;">
							<b>Parte Transporte</b>
						</td>
						<td></td>
						<td style="text-align:center; width:25%; font-size: 11px;vertical-align:center;">
							<b>Figura Transporte</b>
						</td>
						<td></td>
						<td style="text-align:center; width:25%; font-size: 11px;vertical-align:center;">
							<b>Figura Transporte</b>
						</td>
						
					</tr>
					<tr>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte1; ?>
						</td>
						<td style="text-align:center; font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte2; ?>
						</td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $parte_transporte3; ?>
						</td><td></td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $figura_transporte1; ?>
						</td><td></td>
						<td style="text-align:center;font-size: 11px;vertical-align:center;">
							<?php echo $figura_transporte2; ?>
						</td>
						
					</tr>
					
					<?php
						}
					?>

				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Num. Total Mercancías:<?php echo $f_totalcantidad; ?> </b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Peso Neto Total:<?php echo $fPesoNeto; ?> </b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Detalle Mercancías</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Peso Bruto Total: <?php echo $f_pesototal; ?></b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Clave Unidad: <?php echo $f_claveunidadpeso; ?></b>
						</td>
					</tr>
				</table>
			</div>
			<div>
				
					<?php
				
					$resSQL27 = "SELECT
										a.Cantidad as Cantidad,
										a.Descripcion as fsDescripcion,
										a.PesoNeto,
										b.ClaveUnidad as ClaveUnidad,
										b.Nombre as Nombre,
										c.ClaveProducto as ClaveProducto,
										c.Descripcion as Descripcion2, 
										e.ClaveMaterialPeligroso as ClaveMaterialPeligroso,
										d.ClaveDesignacion as ClaveDesignacion, 
										d.Descripcion as Descripcion3, 
										a.Embalaje,
										a.Peso as Peso, 
										a.UUIDComercioExt as UUIDComercioExt,
										a.NumeroPedimento as NumeroPedimento,
										a.Pedimento,
										a.Movimientos, 
										a.Referencia,
										a.FraccionArancelaria_RID,
										g.Codigo as Codigo,
										g.Descripcion as Descripcion6,
										a.TipoDocumento_RID,
										j.Descripcion as TdDescripcion,
										j.Clave,
										a.RFCImpo,
										k.Descripcion as TmDescripcion,
										k.Clave as TmClave,
										a.IdentDocAduanero,
										a.Sello
									FROM {$prefijobd}facturassub a 
									LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
									LEFT JOIN {$prefijobd}c_claveprodservcp c on a.ClaveProdServCP_RID = c.id 
									LEFT JOIN {$prefijobd}c_tipoembalaje d on a.TipoEmbalaje_RID = d.id 
									LEFT JOIN {$prefijobd}c_materialpeligroso e on a.MaterialPeligroso_RID = e.id 
									LEFT JOIN {$prefijobd}c_clavestcc f on a.ClaveSTCC_RID = f.id 
									LEFT JOIN {$prefijobd}c_fraccionarancelaria g on a.FraccionArancelaria_RID = g.id 
									LEFT JOIN {$prefijobd}clientesdestinos h on a.IDDestino_RID = h.id 
									LEFT JOIN {$prefijobd}clientesdestinos i on a.IDOrigen_RID = i.id
									LEFT JOIN {$prefijobd}c_documentoaduanero j on a.TipoDocumento_RID = j.ID
									LEFT JOIN {$prefijobd}c_tipomateria k on a.TipoMateria_RID = k.ID
								WHERE a.FolioSub_RID =".$id_factura;
					

					$runSQL27 = mysqli_query( $cnx_cfdi2 ,$resSQL27);
					/* if ($rowSQL27 = mysqli_fetch_array($runSQL27)) {
						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t, $numDecimales); 
						$fs_descripcion1= $rowSQL27['fsDescripcion'];
						$fs_clave_unidad = $rowSQL27['ClaveUnidad'];
						$fs_nombre = $rowSQL27['Nombre'];
						$fs_clave_producto = $rowSQL27['ClaveProducto'];
						$fs_decripcion2 = $rowSQL27['Descripcion2'];
						$fs_clave_material_peligroso = $rowSQL27['ClaveMaterialPeligroso'];
						$fs_clave_designacion = $rowSQL27['ClaveDesignacion'];
						$fs_descripcion3 = $rowSQL27['Descripcion3'];
						$fs_embalaje = $rowSQL27['Embalaje'];
						$fs_peso_t = $rowSQL27['Peso'];
						$fs_peso = number_format($fs_peso_t, $numDecimales); 
						$fs_uuidcomercioext = $rowSQL27['UUIDComercioExt'];
						$fs_numero_pedimento = $rowSQL27['NumeroPedimento'];
						$fs_codigo = $rowSQL27['Codigo'];
						$fs_descripcion6 = $rowSQL27['Descripcion6'];
						$fs_td_descripcion= $rowSQL27['TdDescripcion'];
						$fs_td_clave = $rowSQL27['Clave'];
						$fs_rfcimpo = $rowSQL27['RFCImpo'];
						$fs_tm_descripcion = $rowSQL27 ['TmDescripcion'];
						$fs_tm_clave = $rowSQL27 ['TmClave'];
						$fs_idaduanero= $rowSQL27 ['IdentDocAduanero'];
					}else{
						$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
						die($mensaje);
					} */ 
					$contador = 0;
					$porPagina = 22;
					$primer_salto = true;

					function imprimirEncabezado($estilo_fondo) {
						echo '
						<table  style="margin:0;" width="100%">
					<thead>
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Embalaje</b>
							</th>
							<th style="text-align:center; width:50%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Descripción</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Tipo Material Peligroso</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Unidad</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;'.$estilo_fondo.'">
								<b>Peso kg</b>
							</th>
						</tr>
					</thead>
					<tbody>';
				}
					function imprimirQR($prefijo, $f_cfdserie, $f_cfdfolio){
					echo'	<div>
						<table border="0" style="margin-top:3px; border-collapse: collapse; position: absolute;" width="100%">
							<tr>
								
								
								<td style="text-align:left; width:25%;"><img src="C:/xampp/htdocs/XML_'.$prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'_CCP.svg" width="90px" height="90px" alt="QR"/></td>
							</tr>
						</table>
					</div>
				</div>';
					
					}
					imprimirEncabezado($estilo_fondo);
					while($rowSQL27 = mysqli_fetch_array($runSQL27)){

						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t, $numDecimales); 
						$fs_descripcion1= $rowSQL27['fsDescripcion'];
						$fs_clave_unidad = $rowSQL27['ClaveUnidad'];
						$fsPesoNeto_t = $rowSQL27['PesoNeto'];
						$fsPesoNeto = number_format($fsPesoNeto_t, $numDecimales);
						$fs_nombre = $rowSQL27['Nombre'];
						$fs_clave_producto = $rowSQL27['ClaveProducto'];
						$fs_decripcion2 = $rowSQL27['Descripcion2'];
						$fs_clave_material_peligroso = $rowSQL27['ClaveMaterialPeligroso'];
						$fs_clave_designacion = $rowSQL27['ClaveDesignacion'];
						$fs_descripcion3 = $rowSQL27['Descripcion3'];
						$fs_embalaje = $rowSQL27['Embalaje'];
						$fs_peso_t = $rowSQL27['Peso'];
						$fs_peso = number_format($fs_peso_t, $numDecimales); 
						$fs_uuidcomercioext = $rowSQL27['UUIDComercioExt'];
						$fs_numero_pedimento = $rowSQL27['NumeroPedimento'];
						$fs_pedimento = $rowSQL27['Pedimento'];
						$fs_movimientos = $rowSQL27['Movimientos'];
						$fs_referencia = $rowSQL27['Referencia'];
						$fs_codigo = $rowSQL27['Codigo'];
						$fs_descripcion6 = $rowSQL27['Descripcion6'];
						$fs_td_descripcion= $rowSQL27['TdDescripcion'];
						$fs_td_clave = $rowSQL27['Clave'];
						$fs_rfcimpo = $rowSQL27['RFCImpo'];
						$fs_tm_descripcion = $rowSQL27 ['TmDescripcion'];
						$fs_tm_clave = $rowSQL27 ['TmClave'];
						$fs_idaduanero= $rowSQL27 ['IdentDocAduanero'];
						$fs_fracArancRID = $rowSQL27['FraccionArancelaria_RID'];
						$fs_sello = $rowSQL27['Sello'];

						if (!empty($fs_clave_material_peligroso) || $f_tipo_viaje != 'NACIONAL') {
							if ($contador > 0 && $contador ==3 && $primer_salto) {
							echo '</tbody></table>';
							
							echo '<pagebreak /><sethtmlpageheader name="myHeader3" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" />';
							 imprimirEncabezado($estilo_fondo); 
							 $primerSalto = false;

							} elseif (!$primerSalto && ($contador - 3) % 9 == 0) {
								// Cada 12 después del primero
								echo '</tbody></table>';
								
								echo '<pagebreak /><sethtmlpageheader name="myHeader3" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo);
							}
						
						} else {
							if ($contador > 0 && $contador ==8 && $primer_salto) {
							echo '</tbody></table>';
							
							echo '<pagebreak /><sethtmlpageheader name="myHeader3" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" />';
							 imprimirEncabezado($estilo_fondo); 
							 $primerSalto = false;

							} elseif (!$primerSalto && ($contador - 8) % 20 == 0) {
								// Cada 22 después del primero
								echo '</tbody></table>';
								
								echo '<pagebreak /><sethtmlpageheader name="myHeader3" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo);
							}
						
						}
						
				
						
					
						
						
					
						?>
						

					
						<tr  style="page-break-inside: avoid; page-break-after: auto;">
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_cantidad; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_embalaje;?><br>
								<?php echo $fs_clave_designacion.' - '.$fs_descripcion3; ?>
							</td>
							<td style="text-align:left;font-size: 9px;vertical-align:center;">
								
								<?php if(!($f_tipo_viaje === "NACIONAL")){ 
									echo '<b>Clave:</b> '.$fs_clave_producto.' - '.$fs_decripcion2.'<br>
									<b>Detalles:</b> '.$fs_descripcion1.' <br>
									<b>UUID Com Ext:</b> '.$fs_uuidcomercioext.' - '.' <b>Tipo Documento:</b> '.$fs_td_clave.' - '.$fs_td_descripcion.'   <br>
									<b>Ident. Doc. Aduanero:</b>  '.$fs_idaduanero.' - '.' <b>RFC Impo:</b> '.$fs_rfcimpo .' <br> '.'  
									 <b>Tipo Materia:</b> '.$fs_tm_clave.' <b>Desc. Materia:</b> '.$fs_tm_descripcion;
									

								} else {
									echo '<b>Clave:</b> '.$fs_clave_producto.' - '.$fs_decripcion2.' <br><b>Detalles:</b> '.$fs_descripcion1;
								} 
									if (!empty($fs_fracArancRID)){ echo '<br> <b>Fracción Arancelaria:</b> '.$fs_codigo.' - '.$fs_descripcion6;}
									if (!empty($fs_pedimento)){ echo '<br> <b>Pedimento:</b> '.$fs_pedimento; }
									if (!empty($fs_numero_pedimento)){ echo '<br> <b>No. Pedimento:</b> '.$fs_numero_pedimento; }
									if (!empty($fs_movimientos)){ echo '<br> <b>Movimientos:</b> '.$fs_movimientos; }
									if (!empty($fs_referencia)){ echo '<br> <b>Referencia:</b> '.$fs_referencia; }
									if (!empty($fs_sello)) { echo '<br><b>Sello:</b> '.$fs_sello;} 
									if (!empty($fsPesoNeto_t)) { echo '<br><b>Peso Neto:</b> '.$fsPesoNeto.' kg'; }
									
									?>
								
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_material_peligroso; ?>
							</td>
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_unidad.' - '.$fs_nombre; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_peso; ?>
							</td>
						</tr>
					
						<?php 
						    $contador++;
							
						}
						
						
						?>
					</tbody>
				</table>
				<br><br><br>
				
				
				<?php

			
			
			if(!($f_tipo_viaje === "NACIONAL") || ($f_lleva_repartos !=0)){
			
			?>
			
			

			<!-- FIN Subreporte 3 -->
			<!-- <pagebreak />
			<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
			<sethtmlpagefooter name="myFooter3" value="on" show-this-page="all" /> -->
			<?php
			}
			
			if(!($f_tipo_viaje === "NACIONAL")){
			?>
			
			<div style= "margin-top:3px;">
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr colspan='2'>
					<td style="text-align:center; width:50%; font-size:15px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="2">
							<b>REGIMEN ADUANERO </b>
							<tr>
								<td style="text-align:left; width:50%; font-size:15px;padding: 5px;vertical-align:center;<?php echo $estilo_fondo; ?>">
									<b>CLAVE</b>
								</td>
								<td style="text-align:left; width:50%; font-size:15px;padding: 5px;vertical-align:center;<?php echo $estilo_fondo; ?>">
									<b>DESCRIPCION</b>
								</td>
			<?php
			
			
						$resSQL32 = "SELECT
								Ra.Clave, 
								Ra.Descripcion 
							FROM {$prefijobd}factura as f
							LEFT JOIN {$prefijobd}facturaregimenaduanero as Fr on Fr.FolioSub_RID= f.ID 
							LEFT JOIN {$prefijobd}c_regimenaduanero as Ra on Ra.ID = fr.Regimen_RID 
						
						WHERE f.ID =".$id_factura;

					$t2=0;
					$runSQL32 = mysqli_query( $cnx_cfdi2 ,$resSQL32);
					if (!$runSQL32) {//debug
					$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
					$mensaje .= 'Consulta completa: ' . $resSQL32;
					die($mensaje);
					}

						$t2 = 0; 

			if (mysqli_num_rows($runSQL32) > 0) {
				

				
				while ($rowSQL32 = mysqli_fetch_assoc($runSQL32)) {
					$fra_clave = $rowSQL32['Clave'];
					$fra_descripcion = $rowSQL32['Descripcion'];

					echo "<tr>
							<td style='text-align:left; width:50%; font-size:12px;padding: 5px;'>$fra_clave</td>
							<td style='text-align:left; width:50%; font-size:12px;padding: 5px;'>$fra_descripcion</td>
						</tr>";

					$t2++; 
				}

				echo "</table>"; 
			} else {
				echo "<p>No se encontraron resultados.</p>";
			}

			
			?>
			
			</td>
			</tr>
			</table>
		</div>
		
		
			
			<?php
			}
		} 	

		if ($f_lleva_repartos != 0) { ?>

			
			
<div>
    <?php
    $t2 = 1;
    $resSQL31 = "SELECT
                    a.CodigoOrigen as CodigoOrigen, a.Remitente as Remitente,
                    a.RemitenteRFC as RemitenteRFC, a.RemitenteCalle as RemitenteCalle, 
                    a.RemitenteNumExt as RemitenteNumExt, b.NombreAsentamiento as NombreAsentamiento,
                    d.Descripcion as Descripcion, e.Descripcion as Descripcion_1,
                    a.RemitenteCodigoPostal as RemitenteCodigoPostal, a.RemitentePais as RemitentePais,
                    a.RemitenteNumRegIdTrib as RemitenteNumRegIdTrib,
                    DATE_FORMAT(a.CitaCarga, '%d-%m-%Y %H:%i:%s') AS CitaCarga, 
                    a.RemitenteTelefono as RemitenteTelefono, a.CodigoDestino as CodigoDestino,
                    a.Destinatario as Destinatario, a.DestinatarioRFC as DestinatarioRFC, 
                    a.DestinatarioCalle as DestinatarioCalle, a.DestinatarioNumExt as DestinatarioNumExt,
                    f.NombreAsentamiento as NombreAsentamiento_1, h.Descripcion as Descripcion_2,
                    i.Descripcion as Descripcion_3, a.DestinatarioCodigoPostal  as DestinatarioCodigoPostal,
                    a.DestinatarioPais as DestinatarioPais, a.DestinatarioNumRegIdTrib as DestinatarioNumRegIdTrib,
                    DATE_FORMAT(a.DestinatarioCitaCarga, '%d-%m-%Y %H:%i:%s') AS DestinatarioCitaCarga,
                    a.DestinatarioTelefono as DestinatarioTelefono, 
                    FORMAT(a.DistanciaRecorrida, 2) as DistanciaRecorrida 
                FROM {$prefijobd}facturasrepartos a 
                LEFT JOIN {$prefijobd}c_colonia b on a.RemitenteColonia_RID = b.id 
                LEFT JOIN {$prefijobd}estados c on a.RemitenteEstado_RID = c.id 
                LEFT JOIN {$prefijobd}c_localidad d on a.RemitenteLocalidad2_RID = d.id 
                LEFT JOIN {$prefijobd}c_municipio e on a.RemitenteMunicipio_RID = e.id 
                LEFT JOIN {$prefijobd}c_colonia f on a.DestinatarioColonia_RID = f.id 
                LEFT JOIN {$prefijobd}estados g on a.DestinatarioEstado_RID = g.id 
                LEFT JOIN {$prefijobd}c_localidad h on a.DestinatarioLocalidad2_RID = h.id 
                LEFT JOIN {$prefijobd}c_municipio i on a.DestinatarioMunicipio_RID = i.id
                WHERE a.FolioSub_RID =".$id_factura;

    $runSQL31 = mysqli_query($cnx_cfdi2, $resSQL31);

    if (mysqli_num_rows($runSQL31) > 0) {
        while ($rowSQL31 = mysqli_fetch_assoc($runSQL31)) {
            foreach ($rowSQL31 as $key => $value) {
                $rowSQL31[$key] = htmlspecialchars($value);
            }

            extract($rowSQL31); 

            ?>
            <table style="margin-bottom: 5px; width:100%; font-size:12px; border-collapse: collapse;">
                <tr>
                    <td style="text-align:center; font-weight: bold; <?php echo $estilo_fondo; ?>" colspan="2">
                        REPARTO <?php echo $t2; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%; font-weight: bold;">ORIGEN</td>
                    <td style="width: 50%; font-weight: bold;">DESTINO</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        Código Origen: <?php echo $CodigoOrigen; ?><br>
                        Razón Social: <?php echo $Remitente; ?><br>
                        RFC: <?php echo $RemitenteRFC; ?><br>
                        Domicilio: <?php echo $RemitenteCalle . ' No.' . $RemitenteNumExt; ?><br>
                        Col. <?php echo $NombreAsentamiento . ', ' . $Descripcion . ', ' . $Descripcion_1 . ', C.P.' . $RemitenteCodigoPostal; ?><br>
                        Residencia Fiscal: <?php echo $RemitentePais; ?><br>
                        Identidad Tributaria: <?php echo $RemitenteNumRegIdTrib; ?><br>
                        Fecha de Salida: <?php echo $CitaCarga; ?><br>
                    </td>
                    <td style="vertical-align: top;">
                        Código Destino: <?php echo $CodigoDestino; ?><br>
                        Razón Social: <?php echo $Destinatario; ?><br>
                        RFC: <?php echo $DestinatarioRFC; ?><br>
                        Domicilio: <?php echo $DestinatarioCalle . ' No.' . $DestinatarioNumExt; ?><br>
                        Col. <?php echo $NombreAsentamiento_1 . ', ' . $Descripcion_2 . ', ' . $Descripcion_3 . ', C.P.' . $DestinatarioCodigoPostal; ?><br>
                        Residencia Fiscal: <?php echo $DestinatarioPais; ?><br>
                        Identidad Tributaria: <?php echo $DestinatarioNumRegIdTrib; ?><br>
                        Fecha de Llegada: <?php echo $DestinatarioCitaCarga; ?><br>
                        Distancia Recorrida: <?php echo $DistanciaRecorrida; ?>
                    </td>
                </tr>
            </table>
            <?php
            $t2++;
        }
    }
    ?>
</div>
<?php } ?>
	<?php
function bitacora()
{
    global $req_bitacora, $f_complemento_traslado,
           $rutalogo, $RazonSocial, $RFC, $f_xfolio, $fechaFactura, $f_ticket,
           $unidad_nombre, $unidad_placas, $cliente_nombre,
           $remitente_localidad_nombre, $destinatario_localidad_nombre,
           $operador_nombre, $operador_licencia, $estilo_fondo;

    if ($req_bitacora != 0 && $f_complemento_traslado != 0) { 
        ?>
        <pagebreak />	
        <htmlpageheader name="myHeader4">
            <div style="padding-top:-20px;height:130px; padding-bottom:-100px;">
                <table border="0" style="margin:0; border-collapse: collapse;" width="100%">
                    <tr>
                        <!-- LOGO IMG -->
                        <td style="text-align:center; width:25%;"><img src="<?php echo $rutalogo; ?>" width="80px" alt=" "/></td>
                        <td style="text-align:center; width:45%; font-size: 11px;">
                            <strong><?php echo $RazonSocial; ?></strong> <br/>
                            <?php echo 'RFC: '.$RFC; ?><br/>
                        </td>
                        <td style="text-align:center; width:30%; font-size: 10px;padding-bottom: 0px;">
                            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td colspan="2" style="text-align:center; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Bitacora</b></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $f_xfolio; ?></b></label></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $fechaFactura; ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Referencia</b></td>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_ticket; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </htmlpageheader>
        
        <sethtmlpageheader name="myHeader4" value="on" show-this-page="all" />

        <div style="padding-top:-30px;">
            <table style="font-size:11px; margin-top:-30px;" width="100%">
                <tbody>
                    <tr>
                        <td>UNIDAD: </td>
                        <td><b><?php echo $unidad_nombre; ?></b></td>
                        <td>PLACA: </td>
                        <td><b><?php echo $unidad_placas; ?></b></td>
                    </tr>
                    <tr>
                        <td>CLIENTE: </td>
                        <td><?php echo $cliente_nombre; ?></td>
                        <td>FECHA</td>
                        <td><?php echo $fechaFactura; ?></td>
                    </tr>
                    <tr>
                        <td>ORIGEN: </td>
                        <td><?php echo $remitente_localidad_nombre; ?></td>
                        <td>DESTINO: </td>
                        <td><?php echo $destinatario_localidad_nombre; ?></td>
                    </tr>
                    <tr>
                        <td>OPERADOR: </td>
                        <td><?php echo $operador_nombre; ?></td>
                        <td>LICENCIA: </td>
                        <td><?php echo $operador_licencia; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php 
        $diasBitacora = array('Dia 1','Dia 2','Dia 3','Dia 4','Dia 5','Dia 6','Dia 7');
        $conceptos = array('HORA','CONDUCIENDO','FUERA DE SERVICIO','SERV SIN CONDUCIR','DESCANSO','REVISION DE SELLOS');

        $imprimirTitulo = true;
        foreach ($diasBitacora as $dia) { ?>
            <table style="margin:0; border-collapse: collapse; width:100%; border: 1px solid black;">
                <thead style="height:12px;<?php echo $estilo_fondo; ?>">
                    <tr>
                        <?php if ($imprimirTitulo) { ?>
                            <td style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px; border: 1px solid black;" colspan="25">
                                BITACORA HORAS DE VIAJE
                            </td>
                        </tr>
                        <?php $imprimirTitulo = false; } ?>
                        <tr>
                            <td style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px; border: 1px solid black;" colspan="25">
                                <b><?php echo $dia; ?></b>
                            </td>
                        </tr>
                </thead>

                <?php foreach ($conceptos as $concepto) { ?>
                    <tr style="background-color: #ffffff;">
                        <!-- Celda del concepto -->
                        <td style="background-color: #ffffff; text-align:center; font-size:9px; width:13%; border: 1px solid black;">
                            <b><?php echo $concepto; ?></b>
                        </td>

                        <!-- Celdas dinámicas -->
                        <?php for ($i=1; $i <= 24; $i++) { ?>
                            <td style="background-color: #ffffff; text-align:center; vertical-align:center; font-size:9px; width:3%; border: 1px solid black;">
                                <b>
                                    <?php 
                                    if ($concepto == 'HORA') {
                                        echo $i;
                                    } else {
                                        echo '|';
                                    }
                                    ?>
                                </b>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        <?php 
        } // foreach dias
    } // if banderas
}

?>



	<?php
	function imprimirContrato()
	{
			global $req_contrato, $f_complemento_traslado, $contrato_forzado;
			
		
		if (($req_contrato >= 1 && $f_complemento_traslado >= 1) || $contrato_forzado == 1) { ?>
		 <pagebreak resetpagenum="1" pagenumstyle="1" suppress="off" />
		

			<htmlpageheader name="myHeader2" >
			<h3 style="background-color: #ffffff; "><b style="background-color: #ffffff; ">EL CONTRATO ACTUALIZADO DE PRESTACIÓN DE SERVICIOS, QUE AMPARA LA “CARTA DE
							PORTE O COMPROBANTE PARA AMPARAR EL TRANSPORTE DE MERCANCÍAS”
							</b></h3>
							<p style="font-size: 11px;background-color: #ffffff;height:32px; margin-top:-7px;"> Condiciones de prestación de servicios que ampara la CARTA DE PORTE O COMPROBANTE PARA EL TRANSPORTE DE MERCANCÍAS</p>
			</htmlpageheader>
			<htmlpagefooter  name="myFooter2" style="background-color: #ffffff; height:180px;">
			<table border="0" style="margin-bottom:2px;border-collapse: collapse;" width="100%">
					<tr style="margin-top: 90px;background-color: #ffffff;width:50%;">
						<td style="background-color: #ffffff;text-align:left; font-size:12px;margin-right:5px;width:50%;"colspan="1">
							 <b>SÉPTIMA .- </b>Si la carga no fuere retirada dentro de los 30 días
							hábiles siguientes a aquél en que hubiere sido puesta a disposición
							del consignatario, el "Transportista" podrá solicitar la venta en
							subasta pública con arreglo a lo que dispone el Código de
							Comercio. refiere en Acuerdo que busca alinear la Carta Porte al nuevo complemento<br><br>
							<b>OCTAVA .-</b>El "Transportista" y el “Remitente” o "Expedidor"
							negociarán libremente el precio del servicio, tomando en cuenta su
							tipo, característica de los embarques, volumen, regularidad, clase
							de carga y sistema de pago. <br><br>
							<p style="background-color: #ffffff;"><b style="background-color: #ffffff;">NOVENA.-</b>Si el “Remitente” o "Expedidor" desea que el
							"Transportista" asuma la responsabilidad por el valor de las
							mercancías o efectos que él declare y que cubra toda clase de
							riesgos, inclusive los derivados de caso fortuito o de fuerza mayor,
							las partes deberán convenir un cargo adicional, equivalente al valor
							de la prima del seguro que se contrate, el cual se deberá expresar
							en la Carta de Porte.</p>
			
						</td>
						<td style="background-color: #ffffff;text-align:left; font-size:12px;width:50%;" colspan="2">
							<p><b>DÉCIMA QUINTA .-</b>Para el caso de que el "Remitente" O «Emisor»
							 contrate carro por entero, este aceptará la responsabilidad 
							 solidaria para con el «Transportista» mediante la figura de
							 la corresponsabilidad que contempla el artículo 10 del Reglamento
						 	 Sobre el Peso, Dimensiones y Capacidad de los Vehículos de 
							 Autotransporte que Transitan en los Caminos y Puentes de
							 Jurisdicción Federal, por lo que el "Remitente" o "Expedidor"
							 queda obligado a verificar que la carga y el vehículo que
							 la transporta, cumplan con el peso y dimensiones máximas
							 establecidos en la NOM-012-SCT-2-2017, o la que la sustituya.</p> <br><br>
							<p style="background-color: #ffffff;">
							Para el caso de incumplimiento e inobservancia a las
							disposiciones que regulan el peso y dimensiones, por 
							parte del "Remitente" o «Emisor», este será 
							corresponsable de las infracciones y multas que la 
							Secretaría de Comunicaciones y Transportes y la 
							Guardia Nacional impongan al «Transportista», 
							por cargar las unidades con exceso de peso. </p>
			
						</td>
			
					</tr>
			</table>
			<table width="100%" style="font-size: 8pt;">
					<tr>
						<td width="33%">Versión del comprobante: 4.0</td>
						<td width="33%" align="right"><?php if ($f_complemento_traslado >= 1) { ?>Complemento Carta Porte Versión 3.1<?php } ?> </td>
						
					</tr>
				</table>
			</htmlpagefooter>
			<sethtmlpageheader name="myHeader2" value="on" show-this-page="1" />
				<div >
				<table border="0" style="margin-top: 0;border-collapse: collapse;position:fixed;top:80px;" width="100%">
					
					<tr >
						<td style="text-align:left; font-size:11.5px;width:50%;"colspan="1">
						<p><b>PRIMERA .-</b>Para los efectos del presente contrato de transporte
							se denomina "Transportista" al que realiza el servicio de
							transportación y “Remitente” o "Expedidor" al usuario que contrate
							el servicio o remite la mercancía.</p><br>
							<p><b>SEGUNDA .-</b> El “Remitente” o "Expedidor" es responsable de que
							la información proporcionada al "Transportista" sea veraz y que la
							documentación que entregue para efectos del transporte sea la
							correcta. </p> <br>
							<p><b >TERCERA.-</b> El “Remitente” o "Expedidor" debe declarar al
							"Transportista" el tipo de mercancía o efectos de que se trate,
							peso, medidas y/o número de la carga que entrega para su
							transporte y, en su caso, el valor de la misma. La carga que se
							entregue a granel será pesada por el "Transportista" en el primer
							punto donde haya báscula apropiada o, en su defecto, aforada en
							metros cúbicos con la conformidad del “Remitente” o "Expedidor".</p> <br>
						   <p><b>CUARTA .-</b> Para efectos del transporte, el “Remitente” o
							"Emisor" deberá entregar al «Transportista» los documentos que
							las leyes y reglamentos exijan para llevar a cabo el servicio, en
							caso de no cumplirse con estos requisitos el «Transportista» está
							obligado a rehusar el transporte de las mercancías expone la SCT en la actualización que da paso al complemento Carta Porte</p><br>
							<p><b>QUINTA .-</b> Si por sospecha de falsedad en la declaración del
							contenido de un bulto el "Transportista" deseare proceder a su
							reconocimiento, podrá hacerlo ante testigos y con asistencia del
							“Remitente” o "Expedidor" o del consignatario. Si este último no
							concurriere, se solicitará la presencia de un inspector de la
							Secretaría de Comunicaciones y Transportes, y se levantará el acta
							correspondiente.</p><p> El "Transportista" tendrá en todo caso, la
							obligación de dejar los bultos en el estado en que se encontraban
							antes del reconocimiento. </p><br>
							<p><b>SEXTA .-</b> El "Transportista" deberá recoger y entregar la carga
							precisamente en los domicilios que señale el “Remitente” o
							"Expedidor", ajustándose a los términos y condiciones convenidos.
							El "Transportista" sólo está obligado a llevar la carga al domicilio
							del consignatario para su entrega una sola vez. Si ésta no fuera
							recibida, se dejará aviso de que la mercancía queda a disposición
							del interesado en las bodegas que indique el "Transportista".</p>
			
						</td>
						<td style="text-align:left; font-size:11.5px;width:50%;" colspan="2">
						<p><b>DÉCIMA .- </b> Cuando el importe del flete no incluya el cargo
						adicional, la responsabilidad del "Transportista" queda
						expresamente limitada a la cantidad equivalente a 15 días del
						salario mínimo vigente en el Distrito Federal por tonelada o cuando
						se trate de embarques cuyo peso sea mayor de 200 kg., pero
						menor de 1000 kg; y a 4 días de salario mínimo por remesa cuando
						se trate de embarques con peso hasta de 200 kg.
						</p><br>
			
						<p><b>DÉCIMA PRIMERA .- </b>El precio del transporte deberá pagarse
						en origen, salvo convenio entre las partes de pago en destino.
						Cuando el transporte se hubiere concertado "Flete por Cobrar", la
						entrega de las mercancías o efectos se hará contra el pago del
						flete y el "Transportista" tendrá derecho a retenerlos mientras no se
						le cubra el precio convenido</p><br>
			
						<p><b>DÉCIMA SEGUNDA .- </b>Si al momento de la entrega resultare
						algún faltante o avería, el consignatario deberá hacerla constar en
						ese acto en la Carta de Porte y formular su reclamación por escrito
						al "Transportista", dentro de las 24 horas siguientes.</p>
			<br>
						<p><b>DÉCIMA TERCERA .- </b>El "Transportista" queda eximido de la
						obligación de recibir mercancías o efectos para su transporte, en
						los siguientes casos:</p>
						<p>a.-Cuando se trate de carga que por su naturaleza, peso, volumen,
						embalaje defectuoso o cualquier otra circunstancia no pueda
						transportarse sin destruirse o sin causar daño a los demás
						artículos o al material rodante, salvo que la empresa de que se
						trate tenga el equipo adecuado.</p>
						<p>b.-Las mercancías cuyo transporte haya sido prohibido por
						disposiciones legales o reglamentarias.
			
						Cuando tales disposiciones no prohíban precisamente el transporte
						de determinadas mercancías, pero sí ordenen la presentación de
						ciertos documentos para que puedan ser transportadas, el
						“Remitente” o “Expedidor” estará obligado a entregar al
						"Transportista" los documentos correspondientes.</p>
			<br>
						<p><b>DÉCIMA CUARTA .- </b>Los casos no previstos en las presentes
						condiciones y las quejas derivadas de su aplicación se someterán
						por la vía administrativa a la Secretaría de Comunicaciones y
						Transportes.</p>
			
			
						</td>
			
					</tr>
				</table>
				</div>
				<sethtmlpagefooter name="myFooter2" value="on" show-this-page="1" />
		 
			

				<!-- FIN Subreporte 2 -->
				
				<!-- <div class="page-break"></div> -->
			
				<?php
						}				

					}


					if ($ordenPersonalizado == '1'){
						imprimirContrato();
						bitacora();
					} else {
						bitacora();
						imprimirContrato();
					}
?>

		
		
		</main>
		
		<script type="text/php">
		  if (isset($dompdf))
			{
			  //$font = Font_Metrics::get_font("helvetica", "bold");
			  //$dompdf->page_text(270, 780, "Pagina {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0, 0, 0));
			}
		</script>

	</body>
</html>



<?php
	 /* die($html); */ 
 	require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();


// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


// Pie de página
$footerHtml = '
<table width="100%" style="font-size: 8pt;">
    <tr>
        <td width="33%">Versión del comprobante: 4.0</td>
        <td width="33%" align="center">Página {PAGENO}</td>
        <td width="33%" align="right">' . (($f_complemento_traslado >= 1) ? 'Complemento Carta Porte Versión 3.1' : '') . '</td>
    </tr>
</table>
';
$mpdf->SetHTMLFooter($footerHtml);
if (!empty($f_cancelada)) {
	$mpdf->SetWatermarkText('CANCELADO', 0.1); 
	$mpdf->showWatermarkText = true;
	
	
	$mpdf->watermarkTextFont = 'helvetica';
	$mpdf->watermarkTextAlpha = 0.1; 
	$mpdf->watermarkTextAngle = 45; 
	$mpdf->watermarkTextSize = 100; 
}
$mpdf->SetFont('helvetica');


$mpdf->WriteHTML($html);

// Generar ruta
$nombre_pdf = ($f_cfdfolio > 0) ? $f_cfdserie . "-" . $f_cfdfolio : $prefijo . " - " . $f_xfolio;

if ($Multi >= 1) {
	$folder_path = "{$xml_dir}";
	
}else {
	
	$folder_path = "C:/xampp/htdocs{$xml_dir}";
}
	




if (!is_dir($folder_path)) {
    mkdir($folder_path, 0777, true);
}
$file_path = "{$folder_path}/{$nombre_pdf}.pdf";

if (file_exists($file_path)) {
    unlink($file_path);
}

// Salvar y forzar descarga = F, visualizar = I
$mpdf->Output($file_path, 'F');


if($tipoArchivo ==='dwld'){
	header('Content-Type: application/pdf');
	header("Content-Disposition: attachment; filename=\"{$nombre_pdf}.pdf\"");
	readfile($file_path);
}
exit;
	

//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703 
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703


?>
