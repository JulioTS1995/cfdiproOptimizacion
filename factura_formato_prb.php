<?php  
ini_set('memory_limit', '2048M');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución

require 'phpqrcode/qrlib.php';
require 'libreria/dompdf/autoload.inc.php';
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
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
	01 =>"Enero",
	02 =>"Febrero",
	03 =>"Marzo",
	04 =>"Abril",
	05 =>"Mayo",
	06 =>"Junio",
	07 =>"Julio",
	08 =>"Agosto",
	09 =>"Septiembre",
	10 =>"Octubre",
	11 =>"Noviembre",
	12 =>"Diciembre"
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
	//echo $Multi;
	
	$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
	$runSQL0 = mysqli_query($cnx_cfdi2 ,$resSQL0);
	while($rowSQL0 = mysqli_fetch_array($runSQL0)){
		$RazonSocial = $rowSQL0['RazonSocial'];
		$Calle = $rowSQL0['Calle'];
		$NumeroExterior = $rowSQL0['NumeroExterior'];
		$NumeroInterior = $rowSQL0['NumeroInterior'];
		$Colonia = $rowSQL0['Colonia'];
		$CodigoPostal = $rowSQL0['CodigoPostal'];
		$Ciudad = $rowSQL0['Ciudad'];
		$Estado = $rowSQL0['Estado'];
		//$codLocalidad = $rowSQL0['codLocalidad'];
		$Telefono = $rowSQL0['Telefono'];
		$RFC = $rowSQL0['RFC'];
		$Pais = $rowSQL0['Pais'];
		$Municipio = $rowSQL0['Municipio'];
		$xml_dir= $rowSQL0['xmldir'];
		$Regimen = $rowSQL0['Regimen'];
		$PermisoSCT = $rowSQL0['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
		$codLocalidad = '';
	
	}

//Buscar datos de la Factura - CAMBIAR POR PARAMETRO EL ID


$resSQL01 = "SELECT * FROM ".$prefijobd."factura WHERE id=".$id_factura;
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);

while($rowSQL01 = mysqli_fetch_array($runSQL01)){
	
	$f_cfdserie = $rowSQL01['cfdserie'];
	$f_cfdfolio = $rowSQL01['cfdfolio'];
	$f_xfolio = $rowSQL01['XFolio'];
	$f_creado_t = $rowSQL01['Creado'];
	$f_creado = date("d-m-Y H:i:s", strtotime($f_creado_t));
	$f_ticket = $rowSQL01['Ticket'];
	$f_moneda = $rowSQL01['Moneda'];
	$f_subtotal_t = $rowSQL01['zSubtotal'];
	$f_subtotal = number_format($f_subtotal_t,2); 
	$f_impuesto_t = $rowSQL01['zImpuesto'];
	$f_impuesto = number_format($f_impuesto_t,2);
	$f_retenido_t = $rowSQL01['zRetenido'];
	$f_retenido = number_format($f_retenido_t,2); 
	$f_total_t = $rowSQL01['zTotal'];
	$f_total = number_format($f_total_t,2); 
	$f_total2 =	number_format($f_total_t, 2, ".", "");	
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
	$f_remitente_telefono = $rowSQL01['RemitenteTelefono'];
	$f_destinatario_localidad_id = $rowSQL01['DestinatarioLocalidad2_RID'];
	$f_codigodestino = $rowSQL01['CodigoDestino'];
	$f_destinatario = $rowSQL01['Destinatario'];
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
	$f_cfdnocertificado = $rowSQL01['cfdnocertificado'];
	$f_cfdiuuid = $rowSQL01['cfdiuuid'];
	$f_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$f_cfdifechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
	$f_cfdsellodigital = $rowSQL01['cfdsellodigital']; 
	$f_cfdiselloSAT = $rowSQL01['cfdiselloSAT'];
	$f_cfdiselloCadenaOriginal = $rowSQL01['cfdiselloCadenaOriginal'];
	$f_configautotransporte_id = $rowSQL01['ConfigAutotranporte_RID'];
	$f_tipo_viaje = $rowSQL01['TipoViaje'];
	$f_unidad_id= $rowSQL01['Unidad_RID'];
	$f_unidad_id2= $rowSQL01['Unidad2_RID'];
	$f_remolque_id= $rowSQL01['Remolque_RID'];
	$f_remolque2_id= $rowSQL01['Remolque2_RID'];
	$f_dolly_id= $rowSQL01['Dolly_RID'];
	$f_DistanciaRecorrida = $rowSQL01['DistanciaRecorrida'];
/* 	$f_permisionario_id= $rowSQL01['Permisionario_RID'];
 */	if (isset($rowSQL01['Permisionario_RID'])){
		$f_permisionario_id = $rowSQL01['Permisionario_RID'];
	} else {
		$f_permisionario_id = $rowSQL01['PermisionarioFact_RID'];
	}
	$f_IDCCP= $rowSQL01['IdCCP'];
	$f_operador_id= $rowSQL01['Operador_RID'];
	$f_operador_id2= $rowSQL01['Operador2_RID'];
	//$f_totalcantidad_t= $rowSQL01['TotalCantidad'];
	//$f_totalcantidad = number_format($f_totalcantidad_t,2); 
	//$f_totalcantidad = 0;
	$f_pesototal_t= $rowSQL01['xPesoTotal'];
	$f_pesototal = number_format($f_pesototal_t,2);
	$f_complemento_traslado= $rowSQL01['ComplementoTraslado'];
	$f_lleva_repartos= $rowSQL01['LlevaRepartos'];
/* 	$f_qrFsencilla= $rowSQL01['cfdicbbarchivo']; */	
	if (isset($rowSQL01['cfdicbbarchivo'])){
		$f_qrFsencilla = $rowSQL01['cfdicbbarchivo'];
	} else {
		$f_qrFsencilla = $rowSQL01['cfdicbbArchivo'];
	}
	/* $f_idCCP = $rowSQL01['idCCP']; */
	if (isset($rowSQL01['idCCP'])){
		$f_idCCP = $rowSQL01['idCCP'];
	} else {
		$f_idCCP = $rowSQL01['IdCCP'];
	}
	$f_total_letra = convertir($f_total, $f_moneda);
	if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}
	
}

if ($Multi =='1'){
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
		$Regimen = $rowSQL07['Regimen'];
		$PermisoSCT = $rowSQL07['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL07['TipoPermisoSCT'];
		$ruta_logo_multi= $rowSQL07['RutaLogo'];
		$codLocalidad = '';
		
	}
} else {
	$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
	$runSQL0 = mysqli_query($cnx_cfdi2 ,$resSQL0);
	while($rowSQL0 = mysqli_fetch_array($runSQL0)){
		$RazonSocial = $rowSQL0['RazonSocial'];
		$Calle = $rowSQL0['Calle'];
		$NumeroExterior = $rowSQL0['NumeroExterior'];
		$NumeroInterior = $rowSQL0['NumeroInterior'];
		$Colonia = $rowSQL0['Colonia'];
		$CodigoPostal = $rowSQL0['CodigoPostal'];
		$Ciudad = $rowSQL0['Ciudad'];
		$Estado = $rowSQL0['Estado'];
		//$codLocalidad = $rowSQL0['codLocalidad'];
		$Telefono = $rowSQL0['Telefono'];
		$RFC = $rowSQL0['RFC'];
		$Pais = $rowSQL0['Pais'];
		$Municipio = $rowSQL0['Municipio'];
		$xml_dir= $rowSQL0['xmldir'];
		$Regimen = $rowSQL0['Regimen'];
		$PermisoSCT = $rowSQL0['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
		$codLocalidad = '';
	}
}


 //RUTAS LOGO 

 $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

 if ($Multi == 1 ) {
	$rutalogo= $ruta_logo_multi;
 }

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
	
	//Buscar Cliente

	$cliente_colonia = '';
	$cliente_municipio = '';
	$cliente_estado = '';
	$cliente_ciudad = '';

if(empty($f_id_cliente)){

	$cliente_nombre = '';
	$cliente_calle = '';
	$cliente_numext = '';
	$cliente_numint = '';
	
	$cliente_rfc = '';
	$cliente_cp = '';
	
} else {
	/* Receptor */
	//$resSQL03 = "SELECT * FROM ".$prefijobd."clientes WHERE id=".$f_id_cliente;
	$resSQL03 = "SELECT a.RazonSocial, IFNULL(a.Calle, '') as Calle, IFNULL(a.NumeroInterior, '') as NumeroInterior, a.Pais, a.NumeroExterior, a.RFC, a.CodigoPostal, IFNULL(b.Estado, '') as Estado, IFNULL(c.NombreAsentamiento, '') as Colonia, 
	IFNULL(d.Descripcion, '') as Municipio, IFNULL(e.Descripcion, '') as Localidad FROM ".$prefijobd."clientes a 
	left join ".$prefijobd."estados b 
	On a.Estado_RID = b.ID left join ".$prefijobd."c_colonia c 
	On a.c_Colonia_RID=c.ID left join ".$prefijobd."c_municipio d 
	on a.c_Municipio_RID=d.ID left join ".$prefijobd."c_localidad e 
	On a.Localidad_RID=e.ID WHERE a.id=".$f_id_cliente;

	$runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
	if (!$runSQL03) {//debug
		$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
		//$mensaje .= 'Consulta completa: ' . $resSQL03;
		die($mensaje);
	}
	
	while($rowSQL03 = mysqli_fetch_array($runSQL03)){
		$cliente_nombre = $rowSQL03['RazonSocial'];
		$cliente_calle = $rowSQL03['Calle'];
		$cliente_numext = $rowSQL03['NumeroExterior'];
		$cliente_numint = $rowSQL03['NumeroInterior'];
		//$cliente_ciudad = $rowSQL03['Ciudad'];
		//$cliente_colonia_id = $rowSQL03['c_Colonia_RID'];
		$cliente_rfc = $rowSQL03['RFC'];
		//$cliente_municipio_id = $rowSQL03['c_Municipio_RID'];
		//$cliente_estado_id = $rowSQL03['Estado_RID'];
		$cliente_cp = $rowSQL03['CodigoPostal'];
		
		$cliente_colonia = $rowSQL03['Colonia'];
		$cliente_municipio = $rowSQL03['Municipio'];
		$cliente_estado = $rowSQL03['Estado'];
		$cliente_ciudad = $rowSQL03['Localidad'];
		
	}
	
	
}





//busca version CCP
$f_versionCCP = 3.1;



//Buscar usocfdi
$f_usocfdi  = '';
$f_usocfdi_dsc = '';
if($f_usocfdi33_id > 0){
	$resSQL07 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$f_usocfdi33_id;
	$runSQL07 = mysqli_query( $cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$f_usocfdi_dsc = $rowSQL07['Descripcion'];
		$f_usocfdi = $rowSQL07['ID2'];
	}
}

//Buscar metodopago
$f_metodopago  = '';
$f_metodopago_dsc = '';
if($f_metodopago33_id > 0){
	$resSQL08 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$f_metodopago33_id;
	$runSQL08 = mysqli_query( $cnx_cfdi2 ,$resSQL08);
	while($rowSQL08 = mysqli_fetch_array($runSQL08)){
		$f_metodopago_dsc = $rowSQL08['Descripcion'];
		$f_metodopago = $rowSQL08['ID2'];
	}
}

//Buscar formapago
$f_formapago  = '';
$f_formapago_dsc = '';
if($f_formapago33_id > 0){
	$resSQL09 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$f_formapago33_id;
	$runSQL09 = mysqli_query( $cnx_cfdi2 ,$resSQL09);
	while($rowSQL09 = mysqli_fetch_array($runSQL09)){
		$f_formapago_dsc = $rowSQL09['Descripcion'];
		$f_formapago = $rowSQL09['ID2'];
	}
}


//Buscar Remitente Localidad
if(empty($f_remitente_localidad_id)){
	$remitente_localidad_nombre = '';
} else {
	$resSQL10 = "SELECT Descripcion FROM ".$prefijobd."c_Localidad WHERE id=".$f_remitente_localidad_id ;
	$runSQL10= mysqli_query( $cnx_cfdi2 ,$resSQL10);
	while($rowSQL10 = mysqli_fetch_array($runSQL10)){
		$remitente_localidad_nombre = $rowSQL10['Descripcion'];
	}
}

//Buscar Remitente Colonia
if(empty($f_remitente_colonia_id)){
	$remitente_colonia_nombre = '';
} else {
	$resSQL11 = "SELECT NombreAsentamiento FROM ".$prefijobd."c_Colonia WHERE id=".$f_remitente_colonia_id ;
	$runSQL11 = mysqli_query( $cnx_cfdi2 ,$resSQL11);
	while($rowSQL11 = mysqli_fetch_array($runSQL11)){
		$remitente_colonia_nombre = $rowSQL11['NombreAsentamiento'];
	}
}

//Buscar Remitente Municipio
if(empty($f_remitente_municipio_id)){ 
	$remitente_municipio_nombre = '';
} else {
	$resSQL12= "SELECT Descripcion FROM ".$prefijobd."c_Municipio WHERE id=".$f_remitente_municipio_id ;
	$runSQL12 = mysqli_query( $cnx_cfdi2 ,$resSQL12);
	while($rowSQL12 = mysqli_fetch_array($runSQL12)){
		$remitente_municipio_nombre = $rowSQL12['Descripcion'];
	}
}

//Buscar Destinatario Localidad
if(empty($f_destinatario_localidad_id)){ 
	$destinatario_localidad_nombre = '';
} else {
	$resSQL13 = "SELECT Descripcion FROM ".$prefijobd."c_Localidad WHERE id=".$f_destinatario_localidad_id ;
	$runSQL13 = mysqli_query( $cnx_cfdi2 ,$resSQL13);
	while($rowSQL13 = mysqli_fetch_array($runSQL13)){
		$destinatario_localidad_nombre = $rowSQL13['Descripcion'];
	}
}

//Buscar Destinatario Colonia
if(empty($f_destinatario_colonia_id)){ 
	$destinatario_colonia_nombre = '';
} else {
	$resSQL14 = "SELECT NombreAsentamiento FROM ".$prefijobd."c_Colonia WHERE id=".$f_destinatario_colonia_id ;
	$runSQL14 = mysqli_query( $cnx_cfdi2 ,$resSQL14);
	while($rowSQL14 = mysqli_fetch_array($runSQL14)){
		$destinatario_colonia_nombre = $rowSQL14['NombreAsentamiento'];
	}
}


//Buscar Destinatario Municipio
if(empty($f_destinatario_municipio_id)){
	$destinatario_municipio_nombre = '';
} else {
	$resSQL15 = "SELECT Descripcion FROM ".$prefijobd."c_Municipio WHERE id=".$f_destinatario_municipio_id ;
	$runSQL15 = mysqli_query( $cnx_cfdi2 ,$resSQL15);
	while($rowSQL15 = mysqli_fetch_array($runSQL15)){
		$destinatario_municipio_nombre = $rowSQL15['Descripcion'];
	}
}

//Buscar Remitente Estado
if(empty($f_remitente_estado_id)){
	$remitente_estado_nombre = '';
} else {
	$resSQL16 = "SELECT Estado FROM ".$prefijobd."Estados WHERE id=".$f_remitente_estado_id ;
	$runSQL16 = mysqli_query( $cnx_cfdi2 ,$resSQL16);
	while($rowSQL16 = mysqli_fetch_array($runSQL16)){
		$remitente_estado_nombre = $rowSQL16['Estado'];
	}
}

//Buscar Destinatario Estado
if(empty($f_destinatario_estado_id)){
	$rdestinatario_estado_nombre = '';
} else {
	$resSQL17 = "SELECT Estado FROM ".$prefijobd."Estados WHERE id=".$f_destinatario_estado_id ;
	$runSQL17 = mysqli_query( $cnx_cfdi2 ,$resSQL17);
	while($rowSQL17 = mysqli_fetch_array($runSQL17)){
		$rdestinatario_estado_nombre = $rowSQL17['Estado'];
	}
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


//Buscar Unidad
$unidad_nombre = '';
$unidad_polizano = '';
$unidad_placas = '';
$unidad_anio = '';
$unidad_aseguradora_nombre = '';
$configautotransporte_descripcion = '';
$configautotransporte_clavenomenclatura = '';




if(!empty($f_unidad_id)){

	$resSQL21 = "SELECT 
				u.Unidad, 
				u.PolizaNo,
				u.Placas, 
				u.Ano,
				u.PesoBrutoVehicular,
				a.Aseguradora AS unidad_aseguradora_nombre,
				c.Descripcion as configuracionautotransporte_descripcion,
				c.ClaveNomenclatura as configautotransporte_clavenomenclatura
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}Aseguradoras a ON u.AseguradoraUnidad_RID = a.ID
				LEFT JOIN {$prefijobd}c_ConfigAutotransporte c ON u.ConfigAutotranporte_RID = c.ID
	 			WHERE u.ID= {$f_unidad_id}" ;

	$runSQL21 = mysqli_query( $cnx_cfdi2 ,$resSQL21);
	if (!$runSQL21) {//debug
		$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL21;
		die($mensaje);
	}

	if ($rowSQL21 = mysqli_fetch_array($runSQL21)) {
        $unidad_nombre = $rowSQL21['Unidad'];
        $unidad_polizano = $rowSQL21['PolizaNo'];
        $unidad_placas = $rowSQL21['Placas'];
        $unidad_anio = $rowSQL21['Ano'];
		$unidad_peso = $rowSQL21['PesoBrutoVehicular'];
        $unidad_aseguradora_nombre = isset($rowSQL21['unidad_aseguradora_nombre']) ? $rowSQL21['unidad_aseguradora_nombre'] : '';
        $configautotransporte_descripcion = isset($rowSQL21['configuracionautotransporte_descripcion']) ? $rowSQL21['configuracionautotransporte_descripcion'] : '';
        $configautotransporte_clavenomenclatura = isset($rowSQL21['configautotransporte_clavenomenclatura']) ? $rowSQL21['configautotransporte_clavenomenclatura'] : '';
    }else{
		$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
		die($mensaje);
	}
}

//busca unidad 2
if(!empty($f_unidad_id2)){

	$resSQL24 = "SELECT 
				u.Unidad, 
				u.PolizaNo,
				u.Placas, 
				u.Ano,
				u.PesoBrutoVehicular,
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
				Unidad,
				Placas,
				Ano,
				SubTipoRem_RID,
				ClaveTipoRemolque,
				RemolqueSemiremolque
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}c_SubTipoRem s ON u.SubTipoRem_RID = s.ID
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
$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

$parametro_contrato = 923;
$resSQL923 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_contrato";
$runSQL923 = mysqli_query($cnx_cfdi2, $resSQL923);
	 
while ($rowSQL923 = mysqli_fetch_array($runSQL923)) {
	$param= $rowSQL923['id2'];
	$req_contrato = $rowSQL923 ['VLOGI'];
	
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
//$parte2_t = $separar[1];
//echo "PARTE2: ".$parte2_t;
$parte2 =  zero_fill_right($parte2_t,6);
//echo "PARTE2: ".$parte2;
//echo "PARTE2: ".$parte1."\n \n";
//Concatenar
$total_qr = $parte1.",".$parte2;
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


	$contenido2 = 'https://verificacfdi.facturaelectronica.sat.gob.mx/verificaccp/default.aspx?IdCCP='.$f_IDCCP.'&FechaOrig='.$f_citacarga.'&FechaTimb='.$f_cfdifechaTimbrado ;


	// URL de la imagen QR
	$contenido2 = urlencode($contenido2);
	$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido2}&format=svg";

	//die($url);
	// Obtener el contenido de la imagen
	$imageContent = file_get_contents($url);

	// Guardar la imagen en el servidor
	file_put_contents($filename_ccp, $imageContent);
	
}
 

$nombre_factura= '';
if ($f_complemento_traslado == 1) {
	$nombre_factura = 'Factura / Complemento CCP';
	} else {
	$nombre_factura= 'Factura';
}
/* #lista de prefijos sin _ de el color que seleccione el cliente
$negros = ['9898'];
$rojos= ['8787'];
$azules= ['pruebad'];
$grisF= ['pruebas'];

# Determinar si el prefijo está en la lista

if (in_array($prefijo, $negros)) {
	$estilo_fondo = 'background-color: #000000; color: #ffffff;';
} elseif (in_array($prefijo, $rojos)) {
	$estilo_fondo = 'background-color: #a1200f; color: #ffffff;';
} elseif (in_array($prefijo, $azules)) {
	$estilo_fondo = 'background-color: #010161; color: #ffffff;';
} elseif (in_array($prefijo, $grisF)) {
	$estilo_fondo = 'background-color: #666161; color: #000000;';
} else {
	$estilo_fondo = 'background-color: #B8B1AF;'; // Color por defecto (gris)
}
 */





ob_start();

?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<link rel="stylesheet" href="css/style.css">-->
		
		<style>
			@page {
                margin: 150px 25px;
            }
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: Helvetica, sans-serif;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 170px; 
				font-family: Helvetica, sans-serif;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: Helvetica, sans-serif;

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
		<header>
			<div style = "padding-bottom: -40px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%; position: fixed;padding-bottom:1px"><img src=<?php echo $rutalogo;?> width="150px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-size: 10px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b><?php echo $nombre_factura?></b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;">I -Ingreso</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $f_xfolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_creado; ?></td>
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
		</header>
		
		
		<footer >
			
			<div styles="margin-top:85px">
				<table  style="margin:0;border-collapse: collapse; border: 1px solid rgba(128, 128, 128, 0.5)" width="100%">
					<tr style="margin:0; padding:0" >
						
						<td style="text-align:left; width:70%; height:45px; font-size: 10px;vertical-align:right;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5)" colspan='2'><b>Comentarios: <?php echo $f_comentarios; ?></b>
							
						</td>
						<td style="text-align:left; width:30%; font-size: 9px;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5);vertical-align:right;">
							<b>CFDI Relacionado: <?php echo '<br>'.$fr_tiporelacion.' - '.$fr_cfdiuuidRelacionado; ?></b>
						</td>
					</tr>
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
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Serie del Certificado del emisor:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdnocertificado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Folio Fiscal:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdiuuid; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>No. de serie del Certificado del SAT:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $f_cfdinoCertificadoSAT; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Fecha y hora de certificación:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
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
		</footer>
		
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
			
			<div>
				<table border="0" style="margin:0;border-collapse: collapse; padding-top:3px;border: 1px solid rgba(128, 128, 128, 0.5);" width="100%">
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-size: 9px;">
							<b>Cliente:</b> <?php echo $cliente_nombre; ?>
						</td>
						
						<td style="text-align:left; width:40%; font-size: 9px;">
							<b>RFC:</b> <?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;">
							<b>Domicilio:</b> <?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						
						<td style="text-align:left; font-size: 9px;">
							<b>Municipio:</b> <?php echo $cliente_municipio; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 9px;">
							<b>Colonia:</b> <?php echo $cliente_colonia; ?>
						</td>
						<td style="text-align:left; font-size: 9px;">
							<b>Estado:</b> <?php echo $cliente_estado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;" >
							<b>CP:</b> <?php echo $cliente_cp; ?>
						</td>
						<td style="text-align:left; font-size: 9px;">
							
						</td>
					</tr>
				</table>
				<?php if ($f_complemento_traslado >= 1) {?>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Origen - <?php echo $remitente_localidad_nombre;?></b>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='4'>
							<b>Destino - <?php echo $destinatario_localidad_nombre;?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='2'>
							Código Origen: <?php echo $f_codigoorigen; ?><br>
							Razón Social: <?php echo $f_remitente; ?><br>
							RFC: <?php echo $f_remitente_rfc; ?><br>
							Domicilio: <?php echo $f_remitente_calle.' No.'.$f_remitente_numext; ?><?php echo 'Col.'.$remitente_colonia_nombre.', '.$remitente_municipio_nombre.','; ?><?php echo $remitente_estado_nombre.' C.P.'.$f_remitente_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_remitente_pais; ?><br>
							Identidad Tributaria: <?php echo $f_remitente_numregidtrib; ?><br>
							Fecha de Salida: <?php echo $f_citacarga; ?><br>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='4'>
							Código Destino: <?php echo $f_codigodestino;?><br>
							Razón Social: <?php echo $f_destinatario; ?><br>
							RFC: <?php echo $f_destinatario_rfc; ?><br>
							Domicilio: <?php echo $f_destinatario_calle.' No.'.$f_destinatario_numext; ?><?php echo 'Col.'.$destinatario_colonia_nombre.', '.$destinatario_municipio_nombre.','; ?><?php echo $destinatario_estado_nombre.' C.P.'.$f_destinatario_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_destinatario_pais; ?><br>
							Identidad Tributaria: <?php echo $f_destinatario_numregidtrib; ?><br>
							Fecha de Llegada: <?php echo $f_destinatario_citacarga; ?><br>
							Distancia Recorrida: <?php echo $f_DistanciaRecorrida.' Km';?>
						</td>
					</tr>
				</table>
			</div>
			<?php } 
			/* Genera una columna en la tabla de partidas */
			$parametro_columna_ex = 925;
			$resSQL925 = "SELECT id2, VCHAR, VLOGI, dsc FROM {$prefijobd}parametro Where id2 =$parametro_columna_ex";
			$runSQL925 = mysqli_query($cnx_cfdi2, $resSQL925);
				 
			$lleva_row_extra = 0;

			while ($rowSQL925 = mysqli_fetch_array($runSQL925)) {
				$param= $rowSQL925['id2'];
				$variabledinamica= $rowSQL925 ['VCHAR'];
				$descripcion= $rowSQL925['dsc'];
				$lleva_row_extra = $rowSQL925['VLOGI'];
				
			}
			
			?>
			<br>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr style="margin:0; padding:0">
							<th style="text-align:center; width:8%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:8%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Clave Unidad SAT</b>
							</th>
							<th style="text-align:center; width:12%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Clave Producto/Servicio</b>
							</th>
							<th style="text-align:center; width:36%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Descripción</b>
							</th>
							<!-- columna dinamica -->
							<?php if ($lleva_row_extra == 1) {?>
							<th style="text-align:center; width:13%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b><?php echo $descripcion; ?></b>
						</th>
							<?php } ?>
							
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Valor Unitario</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Impuestos</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Retenciones</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Importe</b>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php 
						

						$resSQL18 = "SELECT f.Tipo as Tipo, f.Subtotal1 as Subtotal1, f.Subtotal as Subtotal, f.RetencionImporte as RetencionImporte, f.Retencion as Retencion, f.prodserv33dsc as prodserv33d, f.prodserv33 as prodserv33f, f.PrecioUnitario as PrecioUnitario, f.NoId as NoId, f.IVAImporte as IVAImporte, f.IVA as IVA, f.ISRImporte as ISRImporte, f.ISR as ISR, f.Importe as Importe, f.ID as ID, f.FolioSub_RMA as FolioSub_RMA, f.FolioSub_RID as FolioSub_RID, f.FolioSub_REN as FolioSub_REN, f.FolioConceptos_RMA as FolioConceptos_RMA, f.FolioConceptos_RID as FolioConceptos_RID, f.FolioConceptos_REN as FolioConceptos_REN, f.Excento as Excento, f.Detalle as Detalle, f.DescuentoImporte as DescuentoImporte, f.Descuento as Descuento, f.DescripcionClaveUnidad as DescripcionClaveUnidad, f.ConceptoPartida as ConceptoPartida, f.CobranzaSaldo as CobranzaSaldo, f.CobranzaAbonado as CobranzaAbonado, f.claveunidad33 as claveunidad33f, f.Cantidad as Cantidad, f.BASVERSION as BASVERSION, f.BASTIMESTAMP as BASTIMESTAMP, c.Concepto as ConceptoPartida, c.claveunidad33 as claveunidad33, c.prodserv33 as prodserv33, c.prodserv33dsc as prodserv33dsc FROM ".$prefijobd."FacturaPartidas f LEFT OUTER JOIN ".$prefijobd."Conceptos c on f.FolioConceptos_RID = c.id WHERE FolioSub_RID=".$id_factura ;
						//echo $resSQL18;
						
						$runSQL18 = mysqli_query( $cnx_cfdi2 ,$resSQL18);
						while($rowSQL18 = mysqli_fetch_array($runSQL18)){
							$fp_cantidad_t = $rowSQL18['cantidad'];
							$fp_cantidad = number_format($fp_cantidad_t,2); 
							$fp_claveunidad33 = $rowSQL18['claveunidad33'];
							$fp_prodserv33 = $rowSQL18['prodserv33'];
							$fp_prodserv33dsc = $rowSQL18['prodserv33dsc'];
							$fp_ConceptoPartida = $rowSQL18['ConceptoPartida'];
							$fp_Detalle = $rowSQL18['Detalle'];
							//$fp_xfolio_dataset16 = $rowSQL18[''];
							$fp_PrecioUnitario_t = $rowSQL18['PrecioUnitario'];
							$fp_PrecioUnitario = number_format($fp_PrecioUnitario_t,2); 
							$fp_IVAImporte_t = $rowSQL18['IVAImporte'];
							$fp_IVAImporte = number_format($fp_IVAImporte_t,2); 
							$fp_RetencionImporte_t = $rowSQL18['RetencionImporte'];
							$fp_RetencionImporte = number_format($fp_RetencionImporte_t,2); 
							$fp_Importe_t = $rowSQL18['Importe'];
							$fp_NoId = $rowSQL18['NoId'];
							$fp_tipo = $rowSQL18['Tipo'];
							$fp_Subtotal1=  $rowSQL18['Subtotal1'];
							$fp_Subtotal=  $rowSQL18['Subtotal'];
							$fp_Retencion = $rowSQL18['Retencion'];
							$fp_IVA = $rowSQL18['IVA'];
							$fp_ISRImporte = $rowSQL18['ISRImporte'];
							$fp_ISR = $rowSQL18['ISR'];
							$fp_DescuentoImporte= $rowSQL18['DescuentoImporte'];
							$fp_Descuento = $rowSQL18['Descuento'];
							$fp_DescripcionClaveUnidad = $rowSQL18['DescripcionClaveUnidad'];
							$fp_CobranzaSaldo= $rowSQL18 ['CobranzaSaldo'];
							$fpCobranzaAbonado = $rowSQL18 ['CobranzaAbonado'];

							$fp_valordinamico = $rowSQL18[$variabledinamica];
								

							$fp_Importe = number_format($fp_Importe_t,2); 
							
							
					?>
					<tr>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_cantidad; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_claveunidad33; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_prodserv33.' - '.$fp_prodserv33dsc; ?></td>
						<td style="text-align:left; font-size: 9px;">  <?php echo $fp_ConceptoPartida.' - '.$fp_Detalle; ?></td>
						<!-- columna iterable dinamica -->
						<?php if ($lleva_row_extra == 1) { ?>
							<td style="text-align:center; font-size: 9px;"><?php echo $fp_valordinamico; ?></td>
						<?php } ?>
						<td style="text-align:center; font-size: 9px;"><?php echo "$ ".$fp_PrecioUnitario; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo "$ ".$fp_IVAImporte; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo "$ ".$fp_RetencionImporte; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo "$ ".$fp_Importe; ?></td>
					</tr>
					<?php
						}
					?>
					</tbody>
				</table>
			</div>
			
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Total con letra:</b></td>
						<td style="text-align:right; width:20%; font-size: 10px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:right; width:10%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo "$ ".$f_subtotal; ?></td>
					</tr>
					<tr>
						<td style="text-align:center;font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo $f_total_letra; ?></b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo "$ ".$f_impuesto; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Uso CFDi:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo "$ ".$f_retenido; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>Total:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo "$ ".$f_total; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin-top:25px; border-collapse: collapse;" width="100%">
					<tr>
						<!-- QR IMG 										file:///C:/xampp/htdocs/XML_PRUEBA/FT-231238.svg-->
						
						<td style="text-align:left; width:25%;"><img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'.svg'?>' width="100px" height="100px" alt="QR"/></td>
					</tr>
				</table>
			</div>
			
			<!-- FIN Subreporte 1 -->
			
			<!-- <div class="page-break"></div> -->
			
			<?php 
			if($f_complemento_traslado >= 1){
			?>
			
			<!-- Subreporte 2 -->
			<div class="page-break"></div>

			<br>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse; padding-top:-15px;border: 1px solid rgba(128, 128, 128, 0.5);" width="100%">
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-size: 9px;">
							<b>Cliente:</b> <?php echo $cliente_nombre; ?>
						</td>
						
						<td style="text-align:left; width:40%; font-size: 9px;">
							<b>RFC:</b> <?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;">
							<b>Domicilio:</b> <?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						
						<td style="text-align:left; font-size: 9px;">
							<b>Municipio:</b> <?php echo $cliente_municipio; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 9px;">
							<b>Colonia:</b> <?php echo $cliente_colonia; ?>
						</td>
						<td style="text-align:left; font-size: 9px;">
							<b>Estado:</b> <?php echo $cliente_estado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;" >
							<b>CP:</b> <?php echo $cliente_cp; ?>
						</td>
						<td style="text-align:left; font-size: 9px;">
							
						</td>
					</tr>
				</table>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Origen - <?php echo $remitente_localidad_nombre;?></b>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='4'>
							<b>Destino - <?php echo $destinatario_localidad_nombre;?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='2'>
							Código Origen: <?php echo $f_codigoorigen; ?><br>
							Razón Social: <?php echo $f_remitente; ?><br>
							RFC: <?php echo $f_remitente_rfc; ?><br>
							Domicilio: <?php echo $f_remitente_calle.' No.'.$f_remitente_numext.'Col.'.$remitente_colonia_nombre.', '.$remitente_municipio_nombre.','.$remitente_estado_nombre.' C.P.'.$f_remitente_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_remitente_pais; ?><br>
							Identidad Tributaria: <?php echo $f_remitente_numregidtrib; ?><br>
							Fecha de Salida: <?php echo $f_citacarga; ?><br>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='4'>
							Código Destino: <?php echo $f_codigodestino;?><br>
							Razón Social: <?php echo $f_destinatario; ?><br>
							RFC: <?php echo $f_destinatario_rfc; ?><br>
							Domicilio: <?php echo $f_destinatario_calle.' No.'.$f_destinatario_numext.'Col.'.$destinatario_colonia_nombre.', '.$destinatario_municipio_nombre.','.$destinatario_estado_nombre.' C.P.'.$f_destinatario_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_destinatario_pais; ?><br>
							Identidad Tributaria: <?php echo $f_destinatario_numregidtrib; ?><br>
							Fecha de Llegada: <?php echo $f_destinatario_citacarga; ?><br>
							Distancia Recorrida: <?php echo $f_DistanciaRecorrida.' Km';?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="5">
							<b>Detalle del Complemento Carta Porte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width: 40%; font-size:9px; vertical-align:center;">
							<b>VersionCCP</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<b>Medio de Transporte</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>IDCCP</b>
						</td>
						<td style="text-align:center; width:30%; font-size: 9px;vertical-align:center;">
							<b>Tipo de Transporte</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Vía Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
								<?php echo $f_versionCCP; ?>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<?php echo '01 - Autotransporte Federal'; ?>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<?php echo $f_IDCCP; ?>
						</td>
						<td style="text-align:center; width:30%; font-size: 9px;vertical-align:center;">
							<?php echo $f_tipo_viaje; ?>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<?php echo '01'; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:10%; font-size: 9px;vertical-align:center;">
							<?php echo "<b>Permiso SCT:</b><br>".$PermisoSCT; ?>
						</td>
						<td style="text-align:left; width:10%; font-size: 9px;vertical-align:center;">
							<?php echo "<b>No. Unidad o Remolque:</b>" ?>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Peso B. Vehicular</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Aseguradora</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Número Póliza</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Unidad/Placa</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Año</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Configuración Vehicular / Tipo Remolque</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 9px;vertical-align:center;">
						<?php echo '<b>Número de Permiso:</b><br>'.$TipoPermisoSCT; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<b>1.-</b>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_peso. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_nombre. ' / '.$unidad_placas; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_anio; ?>
						</td>
						<td style="text-align:center;font-size: 8px;vertical-align:center;">
							<?php echo $configautotransporte_clavenomenclatura.": ".$configautotransporte_descripcion ; ?>
						</td>
					</tr>
				<?php if (($f_unidad_id2 > 1)||($f_permisionario_id > 1)) { ?>
					
					<tr>
						<td style="text-align:left; font-size: 9px;vertical-align:center;">
							<?php echo '<b>Permisionario</b>'; ?>
							
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<b>2.-</b>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_2_peso. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_2_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_2_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_2_nombre. ' / '.$unidad_2_placas; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_2_anio; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $configautotransporte_2_clavenomenclatura.': '.$configautotransporte_2_descripcion; ?>
						</td>
					</tr>
					<?php	} ?>
					<?php if ($f_remolque_id > 1) { ?>
						
						<tr>
							<td style="text-align:left; font-size: 9px;vertical-align:center;">
								<b>-</b>
							</td>
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo ' <b>Rem 1.-</b>'; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo ' - '; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
							
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_nombre. ' / '.$remolque_placas; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_anio; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_clave_tipo_remilque.': '.$remolque_remolque_semiremolque; ?>
						</td>
					</tr>
					<?php	}	?>
					<?php if (($f_remolque2_id > 1) || ($f_dolly_id > 1)){?>
					<tr>
						<?php if ($f_dolly_id > 1) { ?>
				
						<td style="text-align:left; font-size: 9px;vertical-align:center;" >
							<?php echo '<b>Dolly/ Placa/ Año</b><br>'.$dolly_nombre.'/ '.$dolly_placas.'/ '.$dolly_anio;?>							
						</td>
						<?php } ?>

						<?php if ($f_remolque2_id > 1) { ?>
						
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo ' <b>Rem 2.-</b>'; ?>						
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<b>-</b>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>

						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>

						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $remolque2_nombre.' / '.$remolque2_placas; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $remolque2_anio; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $remolque2_clave_tipo_remilque.': '.$remolque2_remolque_semiremolque; ?>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:150%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Figuras de Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Tipo Figura</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Nombre</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>RFC</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>C.P.</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>No. Licencia</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Estado</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Residencia Fiscal</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<b>Id. Tributaria</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $operador_tipo_figura; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_rfc; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_cp; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_estado; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria; ?>
						</td>
					</tr>
					<?php if ($f_operador_id2 > 1) { ?>
						
					
					<tr>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Tipo Figura 2</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Nombre 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>RFC 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>C.P. 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>No. Licencia 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Estado 2</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Residencia Fiscal 2</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<b>Id. Tributaria 2</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $operador_tipo_figura_2; ?>
						</td>
						<td style="text-align:center; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_rfc_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_cp_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_licencia_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_estado_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal_2; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria_2; ?>
						</td>
					</tr>
					<?php }?>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:33%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Num. Total Mercancías:<?php echo $f_totalcantidad; ?> </b>
						</td>
						<td style="text-align:center; width:34%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Detalle Mercancías</b>
						</td>
						<td style="text-align:center; width:33%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Peso Bruto Total: <?php echo $f_pesototal; ?></b>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<th style="text-align:center; width:5%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Unidad</b>
							</th>
							<th style="text-align:center; width:50%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Descripción</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Tipo Material Peligroso</b>
							</th>
							<th style="text-align:center; width:13%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Embalaje</b>
							</th>
							<th style="text-align:center; width:7%; font-size: 9px;vertical-align:center;<?php echo $estilo_fondo; ?>">
								<b>Peso kg</b>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php

					$resSQL27 = "SELECT
										a.Cantidad as Cantidad,
										a.Descripcion as fsDescripcion,
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
										g.Codigo as Codigo,
										g.Descripcion as Descripcion6,
										a.TipoDocumento_RID,
										j.Descripcion as TdDescripcion,
										j.Clave,
										a.RFCImpo,
										k.Descripcion as TmDescripcion,
										k.Clave as TmClave,
										a.IdentDocAduanero
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
						$fs_cantidad = number_format($fs_cantidad_t,2); 
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
						$fs_peso = number_format($fs_peso_t,2); 
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

					while($rowSQL27 = mysqli_fetch_array($runSQL27)){
						
						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t,2); 
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
						$fs_peso = number_format($fs_peso_t,2); 
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
						
						
					
						
						

					?>
						<tr  style="page-break-inside: avoid; page-break-after: auto;">
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_cantidad; ?>
							</td>
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_unidad.' - '.$fs_nombre; ?>
							</td>
							<td style="text-align:left;font-size: 9px;vertical-align:center;">

								<?php if(!($f_tipo_viaje === "NACIONAL")){ 
									echo $fs_clave_producto.' - '.$fs_decripcion2.'<br><b>Detalles:</b> '.$fs_descripcion1.' <br><b>UUID Com Ext:</b> '.$fs_uuidcomercioext.' - '.' <b>Tipo Documento:</b> '.$fs_td_clave.' - '.$fs_td_descripcion.'   <br><b>Ident. Doc. Aduanero:</b>  '.$fs_idaduanero.' - '.'<b>Pedimento:</b> '.$fs_numero_pedimento.' <br> <b>RFC Impo:</b> '.$fs_rfcimpo .' - '.' <b>Fracción Arancelaria:</b> '.$fs_codigo.' - '.$fs_descripcion6.'<br> <b>Tipo Materia:</b> '.$fs_tm_clave.' <b>Desc. Materia:</b> '.$fs_tm_descripcion;
								} else {
									echo $fs_clave_producto.' - '.$fs_decripcion2.' <br><b>Detalles:</b> '.$fs_descripcion1.'<br> <b>Fracción Arancelaria:</b> '.$fs_codigo.' - '.$fs_descripcion6;
								} ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_material_peligroso; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_embalaje;?><br>
								<?php echo $fs_clave_designacion.' - '.$fs_descripcion3; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_peso; ?>
							</td>
						</tr>
						<?php
						
						}
						
						?>
					</tbody>
				</table>
				<div>
					<table border="0" style="margin-top:3px; border-collapse: collapse; position: absolute;" width="100%">
						<tr>
							<!-- QR IMG 										file:///C:/xampp/htdocs/XML_PRUEBA/FT-231238.svg-->
							
							<td style="text-align:left; width:25%;"><img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'_CCP.svg'?>' width="90px" height="90px" alt="QR"/></td>
						</tr>
					</table>
				</div>
			</div>
				<?php
			
			
			if($f_lleva_repartos == 1){
			
			?>
			
			<!-- Subreporte 3 -->
			<div class="page-break"></div>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
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
						

						$runSQL31 = mysqli_query( $cnx_cfdi2 ,$resSQL31);

						if (mysqli_num_rows($runSQL31) > 0) {
							echo '<div>
								<table border="1" style="border-collapse: collapse; width:100%; font-size:12px;">';

						$facturaRepartos =[];

						while($rowSQL31 = mysqli_fetch_assoc($runSQL31)) {
							$facturaRepartos[] = [

							$fr_CodigoOrigen = $rowSQL31['CodigoOrigen'],
							$fr_Remitente = $rowSQL31['Remitente'],
							$fr_RemitenteRFC = $rowSQL31['RemitenteRFC'],
							$fr_RemitenteCalle = $rowSQL31['RemitenteCalle'],
							$fr_RemitenteNumExt = $rowSQL31['RemitenteNumExt'],
							$fr_NombreAsentamiento = $rowSQL31['NombreAsentamiento'],
							$fr_Descripcion = $rowSQL31['Descripcion'],
							$fr_Descripcion_1 = $rowSQL31['Descripcion_1'],
							$fr_RemitenteCodigoPostal = $rowSQL31['RemitenteCodigoPostal'],
							$fr_RemitentePais = $rowSQL31['RemitentePais'],
							$fr_RemitenteNumRegIdTrib = $rowSQL31['RemitenteNumRegIdTrib'],
							$fr_CitaCarga_t = $rowSQL31['CitaCarga'],
							$fr_RemitenteTelefono = $rowSQL31['RemitenteTelefono'],
							$fr_CodigoDestino = $rowSQL31['CodigoDestino'],
							$fr_Destinatario = $rowSQL31['Destinatario'],
							$fr_DestinatarioRFC = $rowSQL31['DestinatarioRFC'],
							$fr_DestinatarioCalle = $rowSQL31['DestinatarioCalle'],
							$fr_DestinatarioNumExt = $rowSQL31['DestinatarioNumExt'],
							$fr_NombreAsentamiento_1 = $rowSQL31['NombreAsentamiento_1'],
							$fr_Descripcion_2 = $rowSQL31['Descripcion_2'],
							$fr_Descripcion_3 = $rowSQL31['Descripcion_3'],
							$fr_DestinatarioCodigoPostal = $rowSQL31['DestinatarioCodigoPostal'],
							$fr_DestinatarioPais = $rowSQL31['DestinatarioPais'],
							$fr_DestinatarioNumRegIdTrib = $rowSQL31['DestinatarioNumRegIdTrib'],
							$fr_DestinatarioCitaCarga_t = $rowSQL31['DestinatarioCitaCarga'],
							$fr_DestinatarioTelefono = $rowSQL31['DestinatarioTelefono'],
							$fr_DistanciaRecorrida_t = $rowSQL31['DistanciaRecorrida'],
							$fr_DistanciaRecorrida = $rowSQL31['DistanciaRecorrida'], 
							
						];
						
							foreach ($row as $key => $value) {
								$row[$key] = htmlspecialchars($value);
							}

							}

					?>
					<tr colspan='2'>
						<td style="text-align:center; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="2">
							<b>REPARTO <?php echo $t2; ?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>ORIGEN</b>
						</td>
						<td style="text-align:left; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>DESTINO</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;">
							Código Origen: <?php echo $fr_CodigoOrigen; ?><br>
							Razón Social: <?php echo $fr_Remitente; ?><br>
							RFC: <?php echo $fr_RemitenteRFC; ?><br>
							Domicilio: <?php echo $fr_RemitenteCalle.' No.'.$fr_RemitenteNumExt; ?><?php echo ' Col.'.$fr_NombreAsentamiento.', '.$fr_Descripcion.', '; ?><?php echo $fr_Descripcion_1.', C.P.'.$fr_RemitenteCodigoPostal; ?><br>
							Residencia Fiscal: <?php echo $fr_RemitentePais; ?><br>
							Identidad Tributaria: <?php echo $fr_RemitenteNumRegIdTrib; ?><br>
							Fecha de Salida: <?php echo $fr_CitaCarga_t; ?><br>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;">
							Código Destino: <?php echo $fr_CodigoDestino;?><br>
							Razón Social: <?php echo $fr_Destinatario; ?><br>
							RFC: <?php echo $fr_DestinatarioRFC; ?><br>
							Domicilio: <?php echo $fr_DestinatarioCalle.' No.'.$fr_DestinatarioNumExt; ?><?php echo ' Col.'.$fr_NombreAsentamiento_1.', '.$fr_Descripcion_2.','; ?><?php echo $fr_Descripcion_3.' C.P.'.$fr_DestinatarioCodigoPostal; ?><br>
							Residencia Fiscal: <?php echo $fr_DestinatarioPais; ?><br>
							Identidad Tributaria: <?php echo $fr_DestinatarioNumRegIdTrib; ?><br>
							Fecha de Llegada: <?php echo $fr_DestinatarioCitaCarga_t; ?><br>
							Distancia Recorrida: <?php echo $fr_DistanciaRecorrida; ?>
						</td>
					</tr>
					<?php
							$t2 = $t2 + 1;
						}
						
					
					?>
				</table>
			</div>
			<br>
			

			<!-- FIN Subreporte 3 -->

			<?php
			} 
			
			if(!($f_tipo_viaje === "NACIONAL")){
			?>
			<div class="page-break"></div>
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
		<div>
					<table border="0" style="margin-top:3px; border-collapse: collapse;" width="100%">
						<tr>
							<!-- QR IMG 										file:///C:/xampp/htdocs/XML_PRUEBA/FT-231238.svg-->
							
							<td style="text-align:left; width:25%;"><img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$f_cfdserie.'-'.$f_cfdfolio.'_CCP.svg'?>' width="90px" height="90px" alt="QR"/></td>
						</tr>
					</table>
				</div>
			</div>
		
			
			<?php
			}
		} 
			?>

			

			
			<!-- FIN Subreporte 2 -->
			
			<!-- <div class="page-break"></div> -->
			
			

			
			
			




		
		
		
		
		
		
		
		
		</main>
		
		<script type="text/php">
		  if (isset($dompdf))
			{
			  //$font = Font_Metrics::get_font("Arial", "bold");
			  //$dompdf->page_text(270, 780, "Pagina {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0, 0, 0));
			}
		</script>

	</body>
</html>



<?php
	
	$html=ob_get_clean();
	$html2= file_get_contents('C:/xampp/htdocs/cfdipro/contrato.php');
	if (!$html || !$html2) {
		die("Error: Uno de los archivos HTML está vacío o no se puede leer.");
	}
	
	//die( $html2);
	
	require_once 'libreria/dompdf/autoload.inc.php';

	use Dompdf\Dompdf;
	use Dompdf\Options;

	$options = new Options();
	$options->set('defaultFont', 'open sans');
	$options->set('isImageEnabled', true);
	$options->set('isRemoteEnabled', true);
	$options->set('isFontSubsettingEnabled', true);
	$options->set('defaultMediaType', 'print'); // Reduce carga de renderizado
	$options->set("isPhpEnabled", true);
	$options->set('debugPng', true);
	$options->set('isHtml5ParserEnabled', true);

	
	$dompdf = new Dompdf($options);
	if ($req_contrato >= 1 && $f_complemento_traslado) {
		$htmlFinal = $html.'<div style="page-break-before: always;"></div> <div style="all:unset;">' . $html2 . '</div>';
	} else {
		$htmlFinal = $html;
	}

	$dompdf->loadHtml($htmlFinal);

	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render(false);

	
	
	$canvas = $dompdf->get_canvas();
    //$font = Font_Metrics::get_font("helvetica", "bold");
	$font = 'default_font';
    $size = 8;
    $y = $canvas->get_height() - 22;
    $x = $canvas->get_width() - 50;
    //$canvas->page_text($x, $y, "{PAGE_NUM}/{PAGE_COUNT}", $font, $size);
	$canvas->page_text($x, $y, "Página {PAGE_NUM}", $font, $size);
	if ($f_complemento_traslado >= 1 ) {
		$canvas->page_text(251, 815, "Complemento Carta Porte Versión 3.1", null, 8, array(0, 0, 0));
	}
	
	$canvas->page_text(20, 815, "Versión del comprobante: 4.0", null, 8, array(0, 0, 0));
	
	
	
	
	

	//Attachment" => false -- Para que no se descargue automaticamente
	$dompdf->stream("".$prefijo." ".$f_cfdserie."-".$f_cfdfolio.".pdf",["Attachment" => false]);



	$file_path = "C:/xampp/htdocs/XML_".$prefijo."/".$f_cfdserie."-".$f_cfdfolio.".pdf";
	file_put_contents($file_path, $dompdf->output());

//  **Forzar la descarga en la computadora del cliente**
	header('Content-Type: application/pdf');
	header("Content-Disposition: attachment; filename=".$f_xfolio."=".$f_cfdserie."-".$f_cfdfolio.".pdf");
	echo file_get_contents($f_xfolio.$file_path);
		
	

//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703 
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703


?>
