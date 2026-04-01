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

require_once('cnx_cfdi2.php');

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$id_remision = $_GET["id"];
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

function convertir($numero, $rem_moneda) {

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);

    
    $rem_moneda = trim(strtoupper($rem_moneda));
    $rem_moneda = str_replace(["\t", "\n", "\r"], "", $rem_moneda); 

   
    if ($rem_moneda === "PESOS") {
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
	1 =>"Enero",
	2 =>"Febrero",
	3 =>"Marzo",
	4 =>"Abril",
	5 =>"Mayo",
	6 =>"Junio",
	7 =>"Julio",
	8 =>"Agosto",
	9 =>"Septiembre",
	10 =>"Octubre",
	11 =>"Noviembre",
	12 =>"Diciembre"
];
  

$fecha = $dia_logs." de ".$mes_2." de ". $anio_logs;


$fecha2 = (is_array($anio_logs) ? implode("", $anio_logs) : $anio_logs) . "-" .
          (is_array($mes_logs) ? implode("", $mes_logs) : $mes_logs) . "-" .
          (is_array($dia_logs) ? implode("", $dia_logs) : $dia_logs);
#$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;

//multiemisor
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

/* razon social emisor */
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
	if (isset($rowSQL0['RegimenFiscal_RID']) && $rowSQL0['RegimenFiscal_RID'] >1 ) {
		$Regimen_prev= $rowSQL0['RegimenFiscal_RID'];
		
		$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
		$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
		$rowSQL007= mysqli_fetch_assoc($runSQL007);
		if ($rowSQL007){
			$Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
		}
	}else{
		$Regimen = $rowSQL0['Regimen'];
	}
	$PermisoSCT = $rowSQL0['PermisoSCT'];
	$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
	$codLocalidad = '';
	
	
}

//Buscar datos de la REMISION 


$resSQL01 = "SELECT * FROM {$prefijobd}remisiones WHERE id=".$id_remision;
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);

while($rowSQL01 = mysqli_fetch_array($runSQL01)){
	
	$rem_cfdserie = $rowSQL01['cfdserie'];
	$rem_cfdfolio = $rowSQL01['cfdfolio'];
	$rem_xfolio = $rowSQL01['XFolio'];
	$rem_creado_t = $rowSQL01['Creado'];
	$rem_creado = date("d-m-Y H:i:s", strtotime($rem_creado_t));
	$rem_ticket = $rowSQL01['Ticket'];
	$rem_moneda = $rowSQL01['Moneda'];
	$rem_subtotal_t = $rowSQL01['zSubtotal'];
	$rem_subtotal = number_format($rem_subtotal_t,$numDecimales); 
	$rem_impuesto_t = $rowSQL01['zImpuesto'];
	$rem_impuesto = number_format($rem_impuesto_t,$numDecimales);
	$rem_retenido_t = $rowSQL01['zRetenido'];
	$rem_retenido = number_format($rem_retenido_t,$numDecimales); 
	$rem_total_t = $rowSQL01['zTotal'];
	$rem_total = number_format($rem_total_t,$numDecimales); 
	$rem_total2 =	number_format($rem_total_t, 2, ".", "");	
	$rem_usocfdi33_id = $rowSQL01['usocfdi33_RID'];
	$rem_metodopago33_id = $rowSQL01['metodopago33_RID'];
	$rem_formapago33_id = $rowSQL01['formapago33_RID'];
	$rem_id_cliente = $rowSQL01['CargoACliente_RID'];
	$rem_remitente_localidad_id = $rowSQL01['RemitenteLocalidad2_RID'];
	$rem_codigoorigen = $rowSQL01['CodigoOrigen'];
	$rem_remitente = $rowSQL01['Remitente'];
	$rem_remitente_rfc = $rowSQL01['RemitenteRFC'];
	$rem_remitente_calle = $rowSQL01['RemitenteCalle'];
	$rem_remitente_numext = $rowSQL01['RemitenteNumExt'].' Int: '.$rowSQL01['RemitenteNumInt'];
	$rem_remitente_colonia_id = $rowSQL01['RemitenteColonia_RID'];
	$rem_remitente_municipio_id = $rowSQL01['RemitenteMunicipio_RID'];
	$rem_remitente_estado_id = $rowSQL01['RemitenteEstado_RID'];
	$rem_remitente_cp = $rowSQL01['RemitenteCodigoPostal'];
	$rem_remitente_pais = $rowSQL01['RemitentePais'];
	$rem_remitente_numregidtrib = $rowSQL01['RemitenteNumRegIdTrib'];
	$rem_citacarga_t = $rowSQL01['CitaCarga'];
	$rem_citacarga = date("d-m-Y H:i:s", strtotime($rem_citacarga_t));
	$rem_remitente_telefono = $rowSQL01['RemitenteTelefono'];
	$rem_destinatario_localidad_id = $rowSQL01['DestinatarioLocalidad2_RID'];
	$rem_codigodestino = $rowSQL01['CodigoDestino'];
	$rem_destinatario = $rowSQL01['Destinatario'];
	$rem_destinatario_rfc = $rowSQL01['DestinatarioRFC'];
	$rem_destinatario_calle = $rowSQL01['DestinatarioCalle'];
	$rem_destinatario_numext = $rowSQL01['DestinatarioNumExt'].' Int: '.$rowSQL01['DestinatarioNumInt'];
	$rem_destinatario_colonia_id = $rowSQL01['DestinatarioColonia_RID'];
	$rem_destinatario_municipio_id = $rowSQL01['DestinatarioMunicipio_RID'];
	$rem_destinatario_estado_id = $rowSQL01['DestinatarioEstado_RID'];
	$rem_destinatario_cp = $rowSQL01['DestinatarioCodigoPostal'];
	$rem_destinatario_pais = $rowSQL01['DestinatarioPais'];
	$rem_destinatario_numregidtrib = $rowSQL01['DestinatarioNumRegIdTrib'];
	$rem_destinatario_citacarga_t = $rowSQL01['DestinatarioCitaCarga'];
	$rem_destinatario_citacarga = date("d-m-Y H:i:s", strtotime($rem_destinatario_citacarga_t));
	$rem_destinatario_telefono = $rowSQL01['DestinatarioTelefono'];
	if (isset($rowSQL01['Comentarios'])){
		$rem_comentarios = $rowSQL01['Comentarios'];
	}else{
		$rem_comentarios = $rowSQL01['Instrucciones'];
	}
	
	
	$rem_cfdnocertificado = $rowSQL01['cfdnocertificado'];
	$rem_cfdiuuid = $rowSQL01['cfdiuuid'];
	$rem_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$rem_cfdifechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
	$rem_cfdsellodigital = $rowSQL01['cfdsellodigital']; 
	$rem_cfdiselloSAT = $rowSQL01['cfdiselloSAT'];
	$rem_cfdiselloCadenaOriginal = $rowSQL01['cfdiselloCadenaOriginal'];
	$rem_configautotransporte_id = $rowSQL01['ConfigAutotranporte_RID'];
	$rem_tipo_viaje = $rowSQL01['TipoViaje'];
	$rem_unidad_id= $rowSQL01['Unidad_RID'];
	$rem_unidad_id2= $rowSQL01['Unidad2_RID'];
	$rem_remolque_id= $rowSQL01['uRemolqueA_RID'];
	$rem_remolque2_id= $rowSQL01['uRemolqueB_RID'];
	$rem_dolly_id= $rowSQL01['Dolly_RID'];
	$rem_DistanciaRecorrida = $rowSQL01['DistanciaRecorrida'];
/* 	$rem_permisionario_id= $rowSQL01['Permisionario_RID'];
 */	if (isset($rowSQL01['Permisionario_RID'])){
		$rem_permisionario_id = $rowSQL01['Permisionario_RID'];
	} else {
		$rem_permisionario_id = $rowSQL01['PermisionarioFact_RID'];
	}
	$rem_IDCCP= $rowSQL01['IdCCP'];
	$rem_operador_id= $rowSQL01['Operador_RID'];
	$rem_operador_id2= $rowSQL01['Operador2_RID'];
	//$rem_totalcantidad_t= $rowSQL01['TotalCantidad'];
	//$rem_totalcantidad = number_format($rem_totalcantidad_t,$numDecimales); 
	//$rem_totalcantidad = 0;
	$rem_pesototal_t= $rowSQL01['xPesoTotal'];
	$rem_pesototal = number_format($rem_pesototal_t,$numDecimales);
	$rem_complemento_traslado= $rowSQL01['ComplementoTraslado'];
	$rem_lleva_repartos= $rowSQL01['LlevaRepartos'];
/* 	$rem_qrFsencilla= $rowSQL01['cfdicbbarchivo']; */	
	if (isset($rowSQL01['cfdicbbarchivo'])){
		$rem_qrFsencilla = $rowSQL01['cfdicbbarchivo'];
	} else {
		$rem_qrFsencilla = $rowSQL01['cfdicbbArchivo'];
	}
	/* $rem_idCCP = $rowSQL01['idCCP']; */
	if (isset($rowSQL01['idCCP'])){
		$rem_idCCP = $rowSQL01['idCCP'];
	} else {
		$rem_idCCP = $rowSQL01['IdCCP'];
	}
	$rem_referencia = $rowSQL01['RemisionOperador'];
	$rem_total_letra = convertir($rem_total, $rem_moneda);
	if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}
	$rem_unidadpesoID = $rowSQL01['ClaveUnidadPeso_RID'];
	$rem_seRecoge = $rowSQL01['RemitenteSeRecogera'];
	$rem_seEntrega = $rowSQL01['DestinatarioSeEntregara'];

	
}
//Buscar datos para encabezado system setting o multiemisor


if ($Multi ==1){
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
		if (isset($rowSQL07['RegimenFiscal_RID']) && $rowSQL07['RegimenFiscal_RID'] >1 ) {
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
		$PermisoSCT = $rowSQL07['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL07['TipoPermisoSCT'];
		$ruta_logo_multi= $rowSQL07['RutaLogo'];
		$codLocalidad = '';
		if (isset($rowSQL07['ColorFormatos'])) {
			$coloresMulti = $rowSQL07['ColorFormatos'];
		} else {
			$coloresMulti = '';
		}
		
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
		if (isset($rowSQL0['RegimenFiscal_RID']) && $rowSQL0['RegimenFiscal_RID'] >1 ) {
			$Regimen_prev= $rowSQL0['RegimenFiscal_RID'];
			
			$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
			$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
			$rowSQL007= mysqli_fetch_assoc($runSQL007);
			if ($rowSQL007){
				$Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
			}
		}else{
			$Regimen = $rowSQL0['Regimen'];
		}
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

if(empty($rem_id_cliente)){

	$cliente_nombre = '';
	$cliente_calle = '';
	$cliente_numext = '';
	$cliente_numint = '';
	
	$cliente_rfc = '';
	$cliente_cp = '';
	
} else {
	/* Receptor */
	//$resSQL03 = "SELECT * FROM ".$prefijobd."clientes WHERE id=".$rem_id_cliente;
	$resSQL03 = "SELECT a.RazonSocial, IFNULL(a.Calle, '') as Calle, IFNULL(a.NumeroInterior, '') as NumeroInterior, a.Pais, a.NumeroExterior, a.RFC, a.CodigoPostal, IFNULL(b.Estado, '') as Estado, IFNULL(c.NombreAsentamiento, '') as Colonia, 
	IFNULL(d.Descripcion, '') as Municipio, IFNULL(e.Descripcion, '') as Localidad FROM ".$prefijobd."clientes a 
	left join ".$prefijobd."estados b 
	On a.Estado_RID = b.ID left join ".$prefijobd."c_colonia c 
	On a.c_Colonia_RID=c.ID left join ".$prefijobd."c_municipio d 
	on a.c_Municipio_RID=d.ID left join ".$prefijobd."c_localidad e 
	On a.Localidad_RID=e.ID WHERE a.id=".$rem_id_cliente;

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
$rem_versionCCP = 3.1;
/* $rem_versionCCP_d= 'Version';
if ($rem_versionCCP== 3.1) {
	$resSQL= "SELECT  VCHAR, descripcion FROM {$prefijobd}parametro WHERE VCHAR = '3.1'";
	$runSQL= mysqli_query($cnx_cfdi2,$resSQL);
	while ($rowSQL = mysqli_fetch_assoc($runSQL)) {
		
		$rem_versionCCP_d= $rowSQL['descripcion'];
	}
	if ($runSQL && mysqli_num_rows($runSQL) > 0) {
		while ($rowSQL = mysqli_fetch_assoc($runSQL)) {
			$rem_versionCCP = $rowSQL['VCHAR'];
			$rem_versionCCP_d= $rowSQL['descripcion'];
		}
	} else {
		echo "No se encontraron resultados para VCHAR = '{$rem_versionCCP}'";
	}
} */


//Buscar usocfdi
$rem_usocfdi  = '';
$rem_usocfdi_dsc = '';
if($rem_usocfdi33_id > 0){
	$resSQL07 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$rem_usocfdi33_id;
	$runSQL07 = mysqli_query( $cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$rem_usocfdi_dsc = $rowSQL07['Descripcion'];
		$rem_usocfdi = $rowSQL07['ID2'];
	}
}

//Buscar metodopago
$rem_metodopago  = '';
$rem_metodopago_dsc = '';
if($rem_metodopago33_id > 0){
	$resSQL08 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$rem_metodopago33_id;
	$runSQL08 = mysqli_query( $cnx_cfdi2 ,$resSQL08);
	while($rowSQL08 = mysqli_fetch_array($runSQL08)){
		$rem_metodopago_dsc = $rowSQL08['Descripcion'];
		$rem_metodopago = $rowSQL08['ID2'];
	}
}

//Buscar formapago
$rem_formapago  = '';
$rem_formapago_dsc = '';
if($rem_formapago33_id > 0){
	$resSQL09 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$rem_formapago33_id;
	$runSQL09 = mysqli_query( $cnx_cfdi2 ,$resSQL09);
	while($rowSQL09 = mysqli_fetch_array($runSQL09)){
		$rem_formapago_dsc = $rowSQL09['Descripcion'];
		$rem_formapago = $rowSQL09['ID2'];
	}
}


//Buscar Remitente Localidad
if(empty($rem_remitente_localidad_id)){
	$remitente_localidad_nombre = '';
} else {
	$resSQL10 = "SELECT Descripcion FROM ".$prefijobd."c_Localidad WHERE id=".$rem_remitente_localidad_id ;
	$runSQL10= mysqli_query( $cnx_cfdi2 ,$resSQL10);
	while($rowSQL10 = mysqli_fetch_array($runSQL10)){
		$remitente_localidad_nombre = $rowSQL10['Descripcion'];
	}
}

//Buscar Remitente Colonia
if(empty($rem_remitente_colonia_id)){
	$remitente_colonia_nombre = '';
} else {
	$resSQL11 = "SELECT NombreAsentamiento FROM ".$prefijobd."c_Colonia WHERE id=".$rem_remitente_colonia_id ;
	$runSQL11 = mysqli_query( $cnx_cfdi2 ,$resSQL11);
	while($rowSQL11 = mysqli_fetch_array($runSQL11)){
		$remitente_colonia_nombre = $rowSQL11['NombreAsentamiento'];
	}
}

//Buscar Remitente Municipio
if(empty($rem_remitente_municipio_id)){ 
	$remitente_municipio_nombre = '';
} else {
	$resSQL12= "SELECT Descripcion FROM ".$prefijobd."c_Municipio WHERE id=".$rem_remitente_municipio_id ;
	$runSQL12 = mysqli_query( $cnx_cfdi2 ,$resSQL12);
	while($rowSQL12 = mysqli_fetch_array($runSQL12)){
		$remitente_municipio_nombre = $rowSQL12['Descripcion'];
	}
}

//Buscar Destinatario Localidad
if(empty($rem_destinatario_localidad_id)){ 
	$destinatario_localidad_nombre = '';
} else {
	$resSQL13 = "SELECT Descripcion FROM ".$prefijobd."c_Localidad WHERE id=".$rem_destinatario_localidad_id ;
	$runSQL13 = mysqli_query( $cnx_cfdi2 ,$resSQL13);
	while($rowSQL13 = mysqli_fetch_array($runSQL13)){
		$destinatario_localidad_nombre = $rowSQL13['Descripcion'];
	}
}

//Buscar Destinatario Colonia
if(empty($rem_destinatario_colonia_id)){ 
	$destinatario_colonia_nombre = '';
} else {
	$resSQL14 = "SELECT NombreAsentamiento FROM ".$prefijobd."c_Colonia WHERE id=".$rem_destinatario_colonia_id ;
	$runSQL14 = mysqli_query( $cnx_cfdi2 ,$resSQL14);
	while($rowSQL14 = mysqli_fetch_array($runSQL14)){
		$destinatario_colonia_nombre = $rowSQL14['NombreAsentamiento'];
	}
}


//Buscar Destinatario Municipio
if(empty($rem_destinatario_municipio_id)){
	$destinatario_municipio_nombre = '';
} else {
	$resSQL15 = "SELECT Descripcion FROM ".$prefijobd."c_Municipio WHERE id=".$rem_destinatario_municipio_id ;
	$runSQL15 = mysqli_query( $cnx_cfdi2 ,$resSQL15);
	while($rowSQL15 = mysqli_fetch_array($runSQL15)){
		$destinatario_municipio_nombre = $rowSQL15['Descripcion'];
	}
}

//Buscar Remitente Estado
if(empty($rem_remitente_estado_id)){
	$remitente_estado_nombre = '';
} else {
	$resSQL16 = "SELECT Estado FROM ".$prefijobd."Estados WHERE id=".$rem_remitente_estado_id ;
	$runSQL16 = mysqli_query( $cnx_cfdi2 ,$resSQL16);
	while($rowSQL16 = mysqli_fetch_array($runSQL16)){
		$remitente_estado_nombre = $rowSQL16['Estado'];
	}
}

//Buscar Destinatario Estado
if(empty($rem_destinatario_estado_id)){
	$rdestinatario_estado_nombre = '';
} else {
	$resSQL17 = "SELECT Estado FROM ".$prefijobd."Estados WHERE id=".$rem_destinatario_estado_id ;
	$runSQL17 = mysqli_query( $cnx_cfdi2 ,$resSQL17);
	while($rowSQL17 = mysqli_fetch_array($runSQL17)){
		$rdestinatario_estado_nombre = $rowSQL17['Estado'];
	}
}

//Buscar ConfigAutotransporte
if(empty($rem_configautotransporte_id)){
	$rem_configautotransporte_descripcion = '';
	$rem_configautotransporte_clavenomenclatura = '';
} else {
	$resSQL20 = "SELECT Descripcion, ClaveNomenclatura FROM ".$prefijobd."c_ConfigAutotransporte WHERE ID=".$rem_configautotransporte_id ;
	$runSQL20 = mysqli_query( $cnx_cfdi2 ,$resSQL20);
	while($rowSQL20 = mysqli_fetch_array($runSQL20)){
		$rem_configautotransporte_descripcion = $rowSQL20['Descripcion'];
		$rem_configautotransporte_clavenomenclatura = $rowSQL20['ClaveNomenclatura'];
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




if(!empty($rem_unidad_id)){

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
	 			WHERE u.ID= {$rem_unidad_id}" ;

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
if(!empty($rem_unidad_id2)){

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

	 			WHERE u.ID= {$rem_unidad_id2}" ;

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
$remolque_aseguradora_nombre = '';
$remolque_polizano = '';


if(!empty($rem_remolque_id)){


$resSQL23 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano,
				u.SubTipoRem_RID,
				s.ClaveTipoRemolque,
				s.RemolqueSemiremolque,
				u.PolizaNo
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}c_SubTipoRem s ON u.SubTipoRem_RID = s.ID
				
				WHERE u.ID=".$rem_remolque_id ;

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

if(!empty($rem_remolque2_id)){


$resSQL41 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano,
				u.SubTipoRem_RID,
				s.ClaveTipoRemolque,
				s.RemolqueSemiremolque
				FROM {$prefijobd}Unidades u
				LEFT JOIN {$prefijobd}c_SubTipoRem s ON u.SubTipoRem_RID = s.ID
				WHERE u.ID=".$rem_remolque2_id ;

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


if(!empty($rem_dolly_id)){


$resSQL42 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano
				
				FROM {$prefijobd}Unidades u
				WHERE u.ID=".$rem_dolly_id ;

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

if($rem_operador_id > 0){
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
				 WHERE o.ID={$rem_operador_id}";
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
if($rem_operador_id2 > 0){
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
				 WHERE o.ID={$rem_operador_id2}";
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
				FROM {$prefijobd}remisionessub a 
				LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
				
				WHERE a.FolioSub_RID =".$id_remision;
$runSQL40 = mysqli_query( $cnx_cfdi2 ,$resSQL40);
$rem_totalcantidad = 0;

while($rowSQL40 = mysqli_fetch_array($runSQL40)){
	$fs_cantidad_t = $rowSQL40['Cantidad'];
	
	$rem_totalcantidad++ ;


	
} 

//busca unidad de peso
$resSQL40 = "SELECT ClaveUnidad FROM {$prefijobd}c_claveunidadpeso where ID = {$rem_unidadpesoID}";
$runSQL40 = mysqli_query($cnx_cfdi2, $resSQL40);
while($rowSQL40 = mysqli_fetch_array($runSQL40)){
	$rem_claveunidadpeso = $rowSQL40['ClaveUnidad'];
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
$parametro_bitacora = 924;
$resSQL924 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 = $parametro_bitacora";
$runSQL924 = mysqli_query($cnx_cfdi2, $resSQL924);
	 
while ($rowSQL924 = mysqli_fetch_array($runSQL924)) {
	$param= $rowSQL924['id2'];
	$req_bitacora = $rowSQL924 ['VLOGI'];
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

$parametroBarCode = 936;
$resSQL936 = "SELECT id2, VCHAR, VLOGI, dsc FROM {$prefijobd}parametro Where id2 = $parametroBarCode";
$runSQL936 = mysqli_query($cnx_cfdi2, $resSQL936);

while ($rowSQL936 = mysqli_fetch_array($runSQL936)) {
	$param= $rowSQL936['id2'];
	$llevaCodigoBarras = $rowSQL936 ['VLOGI'];
	$campoBaseDatos = $rowSQL936 ['dsc'];
	$aliasCampoBaseDatos = $rowSQL936 ['VCHAR'];
}
$parametroOrdenPersonalizado = 938;
$ordenPersonalizado = 0;
$resSQL938 = "SELECT id2, VLOGI FROM {$prefijobd}parametro Where id2 = $parametroOrdenPersonalizado";
$runSQL938 = mysqli_query($cnx_cfdi2, $resSQL938);	
while ($rowSQL938 = mysqli_fetch_array($runSQL938)) {
	$param= $rowSQL938['id2'];
	$ordenPersonalizado = $rowSQL938 ['VLOGI'];
}

$parametroCambioIngreso = 940;
$resSQL940 = "SELECT id2,  VLOGI FROM {$prefijobd}parametro Where id2 = $parametroCambioIngreso";
$runSQL940 = mysqli_query($cnx_cfdi2, $resSQL940);
while ($rowSQL940 = mysqli_fetch_array($runSQL940)) {
	$param= $rowSQL940['id2'];
	$cambioIngreso = $rowSQL940 ['VLOGI'];
}
$parametroNoCliente = 944;
//$noCliente = 0;
$resSQL944 = "SELECT id2,  VLOGI FROM {$prefijobd}parametro Where id2 = $parametroNoCliente";
$runSQL944 = mysqli_query($cnx_cfdi2, $resSQL944);
while ($rowSQL944 = mysqli_fetch_array($runSQL944)) {
	$param= $rowSQL944['id2'];
	$noCliente = $rowSQL944 ['VLOGI'];
}



///////// Cambios de traslado a ingreso 
$tipoComprobanteNombre = 'T-Traslado';
if ($cambioIngreso == 1) {
	$tipoComprobanteNombre = 'I-Ingreso';
}

function totalizadorIngreso($cambioIngreso, $estilo_fondo, $rem_comentarios) {
	if ($cambioIngreso == 1) {
	echo '<div>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;'.$estilo_fondo.'"><b>Total con Letra
					</b></td>
					<td style="text-align:left; width:20%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
					<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"> $ 0.00</td>
				</tr>
				<tr>
					<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>***Cero Pesos***</b></td>			
					<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Descuento:</b></td>
					<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;">$ 0.00</td>
				</tr>
				<tr>
					<td style="text-align:left; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Uso CFDI : </b> S01- Sin Efectos Fiscales</td>

					
					
					<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Total:</b></td>
					<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;">$ 0.00</td>
				</tr>
					
				
				<tr>
					<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Metodo de Pago: </b> PPD - PAGO EN PARCIALIDADES O DIFERIDO <br><b>Forma de Pago: </b> 99 - POR DEFINIR <br> <b>MXN - Pesos Mexicanos</b></td>
					<td colspan="2" style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;">&nbsp;'.$rem_comentarios.'</td>
				</tr>
					
			</table>
		  </div>';
	}else{
		echo '<b>Comentarios: '.$rem_comentarios.'</b>';
	}
	


}



//  ///////////////////////////////////////////////////////////////////////////////GENERAR BARRA DE CODIGO


if ($llevaCodigoBarras == 1) {

    $resSQLBC = "SELECT {$campoBaseDatos} as {$aliasCampoBaseDatos} 
                 FROM {$prefijobd}remisiones 
                 WHERE ID = {$id_remision}";
    $runSQLBC = mysqli_query($cnx_cfdi2, $resSQLBC);

    $campoEnCodigoBarra = '';
    if ($runSQLBC && $rowSQLBC = mysqli_fetch_array($runSQLBC)) {
        $campoEnCodigoBarra = $rowSQLBC[$aliasCampoBaseDatos];
    }

    
    $codigoRemision = (string)$campoEnCodigoBarra;

    $dir = "C:/xampp/htdocs/XML_{$prefijo}/";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    $fileName = $dir . 'BC-' . $rem_xfolio . '.svg';

  
    require_once __DIR__ . '/vendor/autoload.php';

    
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();

    $svg = $generator->getBarcode($codigoRemision, $generator::TYPE_CODE_128, 2, 60);

    if ($svg === false || strlen($svg) < 20) {
        die("No se pudo generar el código de barras local para remisión.");
    }

    if (file_put_contents($fileName, $svg) === false) {
        die("No se pudo guardar en $fileName");
    }
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
$separar = explode($separador, $rem_total2);
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

$sello_digital_final = substr($rem_cfdsellodigital, -8);

//echo "Ultimos 8 caracteres: ".$sello_digital_final."\n \n";

//QR
$dir = "C:/xampp/htdocs/XML_{$prefijo}/";

if(!file_exists($dir)){
	mkdir($dir);
}

$filename = $dir.$rem_cfdserie.'-'.$rem_cfdfolio.'.svg';


$contenido = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?re='.$RFC.'&rr='.$cliente_rfc.'&tt='.$total_qr.'&id='.$rem_cfdiuuid.'&fe='.$sello_digital_final ;


// URL de la imagen QR
$contenido = urlencode($contenido);
$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido}&format=svg";

//die($url);
// Obtener el contenido de la imagen
$imageContent = file_get_contents($url);

// Guardar la imagen en el servidor
file_put_contents($filename, $imageContent);

$filename_ccp='';
if ($rem_complemento_traslado>0) {
	
	$filename_ccp = $dir.$rem_cfdserie.'-'.$rem_cfdfolio.'_CCP.svg';


	$contenido2 = 'https://verificacfdi.facturaelectronica.sat.gob.mx/verificaccp/default.aspx?IdCCP='.$rem_IDCCP.'&FechaOrig='.$rem_citacarga.'&FechaTimb='.$rem_cfdifechaTimbrado ;


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
if ($rem_complemento_traslado == 1) {
	$nombre_factura = 'Factura / Complemento CCP';
	} else {
	$nombre_factura= 'Factura';
}
$rfcExtranjero = 'XEXX010101000'; 

function concatenaRfcYRegId ($rfcExtranjero, $rem_remitente_rfc, $rem_remitente_numregidtrib, $rem_destinatario_rfc, $rem_destinatario_numregidtrib){
	  $fRfcCompletoRemitente = ($rem_remitente_rfc === $rfcExtranjero)
        ? $rem_remitente_rfc . ' Id. Tributaria: ' . $rem_remitente_numregidtrib
        : $rem_remitente_rfc;

    $fRfcCompletoDestinatario = ($rem_destinatario_rfc === $rfcExtranjero)
        ? $rem_destinatario_rfc . ' Id. Tributaria: ' . $rem_destinatario_numregidtrib
        : $rem_destinatario_rfc;

    return [
        'remitente' => $fRfcCompletoRemitente,
        'destinatario' => $fRfcCompletoDestinatario
    ];
}

$rfcCompletos = concatenaRfcYRegId ($rfcExtranjero, $rem_remitente_rfc, $rem_remitente_numregidtrib, $rem_destinatario_rfc, $f_destinatario_numregidtrib);

$fRfcCompletoRemitente = $rfcCompletos['remitente'];
$fRfcCompletoDestinatario = $rfcCompletos['destinatario'];

$datos_domicilios = [
   'estilo_fondo' => $estilo_fondo,
   'remitente_localidad_nombre' => $remitente_localidad_nombre,
   'destinatario_localidad_nombre' => $destinatario_localidad_nombre,
   'f_codigoorigen' => $rem_codigoorigen,
   'f_remitente' => $rem_remitente,
   'f_remitente_rfc' => $fRfcCompletoRemitente,
   'f_remitente_calle' => $rem_remitente_calle,
   'f_remitente_numext' => $rem_remitente_numext,
   'remitente_colonia_nombre' => $remitente_colonia_nombre,
   'remitente_municipio_nombre' => $remitente_municipio_nombre,
   'remitente_estado_nombre' => $remitente_estado_nombre,
   'f_remitente_cp' => $rem_remitente_cp,
   'f_remitente_pais' => $rem_remitente_pais,
   'f_citacarga' => $rem_citacarga,
   'f_codigodestino' => $rem_codigodestino,
   'f_destinatario' => $rem_destinatario,
   'f_destinatario_rfc' => $fRfcCompletoDestinatario,
   'f_destinatario_calle' => $rem_destinatario_calle,
   'f_destinatario_numext' => $rem_destinatario_numext,
   'destinatario_colonia_nombre' => $destinatario_colonia_nombre,
   'destinatario_municipio_nombre' => $destinatario_municipio_nombre,
   'destinatario_estado_nombre' => $rdestinatario_estado_nombre,
   'f_destinatario_cp' => $rem_destinatario_cp,
   'f_destinatario_pais' => $rem_destinatario_pais,
   'f_destinatario_citacarga' => $rem_destinatario_citacarga,
   'f_DistanciaRecorrida' => $rem_DistanciaRecorrida,
   'f_seEntrega' => $rem_seEntrega,
   'f_seRecoge' => $rem_seRecoge

];

function domicilios($datos_domicilios){
	
		if(!empty($datos_domicilios ['f_seRecoge']) || !empty($datos_domicilios ['f_seEntrega'])){
			echo'<div>
			<table border="1" style="table-layout: fixed; width:100%; border-collapse: collapse; margin:0;">
				<thead>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Origen - '.$datos_domicilios ['remitente_localidad_nombre'].'</b>
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Destino - '.$datos_domicilios ['destinatario_localidad_nombre'].'</b>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Origen: '.$datos_domicilios ['f_codigoorigen'].'<br>
							Razón Social: '.$datos_domicilios ['f_remitente'].'<br>
							RFC: '.$datos_domicilios ['f_remitente_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_remitente_calle'].' No.'.$datos_domicilios ['f_remitente_numext'].'<br>
							'.'Col.'.$datos_domicilios ['remitente_colonia_nombre'].', '.$datos_domicilios ['remitente_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['remitente_estado_nombre'].' C.P.'.$datos_domicilios ['f_remitente_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_remitente_pais'].'<br>
							Fecha de Salida: '.$datos_domicilios ['f_citacarga'].'<br>
							Se Recogerá: '.$datos_domicilios ['f_seRecoge'].'
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Destino: '.$datos_domicilios ['f_codigodestino'].'<br>
							Razón Social: '.$datos_domicilios ['f_destinatario'].'<br>
							RFC: '.$datos_domicilios ['f_destinatario_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_destinatario_calle'].' No.'.$datos_domicilios ['f_destinatario_numext'].'<br>
							'.'Col.'.$datos_domicilios ['destinatario_colonia_nombre'].', '.$datos_domicilios ['destinatario_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['destinatario_estado_nombre'].' C.P.'.$datos_domicilios ['f_destinatario_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_destinatario_pais'].'<br>
							Fecha de Llegada: '.$datos_domicilios ['f_destinatario_citacarga'].'<br>
							Se Entregará: '.$datos_domicilios ['f_seEntrega'].'<br>
							Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km <br>
							Total Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km
						</td>
					</tr>
				</tbody>
			</table>
		</div>';
			
		}else {
			echo'<div>
			<table border="1" style="table-layout: fixed; width:100%; border-collapse: collapse; margin:0;">
				<thead>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 11px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Origen - '.$datos_domicilios ['remitente_localidad_nombre'].'</b>
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 11px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Destino - '.$datos_domicilios ['destinatario_localidad_nombre'].'</b>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 11px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Origen: '.$datos_domicilios ['f_codigoorigen'].'<br>
							Razón Social: '.$datos_domicilios ['f_remitente'].'<br>
							RFC: '.$datos_domicilios ['f_remitente_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_remitente_calle'].' No.'.$datos_domicilios ['f_remitente_numext'].'<br>
							'.'Col.'.$datos_domicilios ['remitente_colonia_nombre'].', '.$datos_domicilios ['remitente_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['remitente_estado_nombre'].' C.P.'.$datos_domicilios ['f_remitente_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_remitente_pais'].'<br>
							Fecha de Salida: '.$datos_domicilios ['f_citacarga'].'<br>
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 11px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Destino: '.$datos_domicilios ['f_codigodestino'].'<br>
							Razón Social: '.$datos_domicilios ['f_destinatario'].'<br>
							RFC: '.$datos_domicilios ['f_destinatario_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_destinatario_calle'].' No.'.$datos_domicilios ['f_destinatario_numext'].'<br>
							'.'Col.'.$datos_domicilios ['destinatario_colonia_nombre'].', '.$datos_domicilios ['destinatario_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['destinatario_estado_nombre'].' C.P.'.$datos_domicilios ['f_destinatario_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_destinatario_pais'].'<br>
							Fecha de Llegada: '.$datos_domicilios ['f_destinatario_citacarga'].'<br>
							Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km <br>
							Total Distancia Recorrida: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km
						</td>
					</tr>
				</tbody>
			</table>
		</div>';
		}
	}




function breakSello($sello, $cada = 80) {
    return implode('&#8203;', str_split($sello, $cada));
}

$esTranspoInt = ($rem_tipo_viaje != 'NACIONAL') ? 'SI' : 'NO' ;

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
			font-family: Helvetica; 

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
		
		
		
		<title>Carta Porte<?php echo ': '.$rem_xfolio ;?></title>	
	</head>
	<body>
	<htmlpageheader name="myHeader">
			<div style = "padding-top: -10px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> width="130px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-family: Helvetica; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-family: Helvetica; font-size: 10px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-family: Helvetica; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Carta Porte</b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $tipoComprobanteNombre;?></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $rem_xfolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $rem_creado; ?></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><b>Referencia</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $rem_referencia; ?></td>
								</tr>
							</table>
						</td>
					</tr>

				</table>
			</div>
			</htmlpageheader>
			<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
			<htmlpagefooter name="myFooter">
		
			<div>

				<?php 
				if ($f_lleva_leyenda) {
					echo '<div style="text-align:center; font-family: Helvetica; font-size: 10px; padding-bottom: 0px; vertical-align:center; '.$f_footer_leyenda_color.'"><b>'.$f_footer_leyenda.'</b></div>';
				}
				totalizadorIngreso($cambioIngreso, $estilo_fondo, $rem_comentarios);?>
				
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding-bottom:3px;" >
					<td style="text-align:left; width:10%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='1'>
							<b>QR CP</b>
						</td>
						<td style="text-align:center; width:80%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Este documento es una representación impresa de un CFDI</b>
						</td>
						<td style="text-align:right; width:10%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='3'>
							<b>QR CCP</b>
						</td>
					</tr>
					<tr><td></td></tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:25%;" colspan="1"><img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$rem_cfdserie.'-'.$rem_cfdfolio.'.svg'?>' width="90px" height="90px" alt="QR"/></td>

						<td style="text-align:center; width:80%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;word-wrap: break-word;" colspan="2">
							<b>Serie del Certificado del emisor: </b><?php echo $rem_cfdnocertificado; ?><br>
							<b>Folio Fiscal: </b><?php echo $rem_cfdiuuid; ?> <br>
							<b>No. de serie del Certificado del SAT: </b><?php echo $rem_cfdinoCertificadoSAT; ?><br>
							<b>Fecha y hora de certificación:</b><?php echo $rem_cfdifechaTimbrado; ?>

						</td>
						<?php if ($rem_complemento_traslado >= 1) { ?>
							
							<td style="text-align:right; width:25%;" colspan="3"><img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$rem_cfdserie.'-'.$rem_cfdfolio.'_CCP.svg'?>' width="90px" height="90px" alt="QR"/></td>
						<?php } ?>
							</tr>
							</table>
							<table border="0" style="width:100%; border-collapse:collapse; margin-top:5px;">
					<thead>
					<tr>
						<td style="text-align:center;width: 33%; font-family: Helvetica; font-size:9px;<?php echo $estilo_fondo; ?>"><b>Sello digital del CFDI</b></td>
						<td style="text-align:center;width: 33%; font-family: Helvetica; font-size:9px;<?php echo $estilo_fondo; ?>"><b>Cadena original</b></td>
						<td style="text-align:center;width: 33%; font-family: Helvetica; font-size:9px;<?php echo $estilo_fondo; ?>"><b>Sello del SAT</b></td>				
					
					</tr>
					</thead>
					<tbody>
						<tr>
							<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($rem_cfdiselloSAT); ?></td>
							<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($rem_cfdsellodigital); ?></td>
							<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($rem_cfdiselloCadenaOriginal); ?></td>
						</tr>

					</tbody>
					
    		</table>
							
							
				
			</div>
		
				<table width="100%" style="font-family: Helvetica; font-size: 8pt;">

					<tr>	
						<td width="33%">Versión del comprobante: 4.0</td>
						<td width="33%" align="right"> <?php if ($f_complemento_traslado >= 1) { ?>Complemento Carta Porte Versión 3.1 <?php } ?> </td>
						<td width="33%" align="right">Página {PAGENO}</td>
					</tr>
				</table>
				</htmlpagefooter>
			<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />
		
		

		
		
		<main>
			<!-- Subreporte 1 -->
			<?php if ($noCliente == '1'){
						echo ' ';
					} else {
						
						?>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse; padding-top:-10px;" width="100%">
					<thead>
						<?php if ($llevaCodigoBarras == 1) { ?>
							
							<tr>
							
								<td colspan= "4" style="text-align:right;">
								<img src='<?php echo $fileName?>' width="450px" height="70px" alt="BCODE"/>
								
								</td>
							
							</tr>
					<?php	}  ?>
					
				
					</thead>
					<tbody style= "border: 1px solid rgba(128, 128, 128, 0.5);">
					<tr style="margin:0; padding:0; ">
						<td style="text-align:left; width:55%; font-family: Helvetica; font-size: 11px;">
							<b>Cliente:</b> <?php echo $cliente_nombre; ?>
						</td>
						
						<td style="text-align:left; width:40%; font-family: Helvetica; font-size: 11px;">
							<b>RFC:</b> <?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;">
							<b>Domicilio:</b> <?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;">
							<b>Estado:</b> <?php echo $cliente_estado; ?>
						</td>
					</tr>
					
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;" >
							<b>CP:</b> <?php echo $cliente_cp; ?>
						</td>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;">
							
						</td>
					</tr>
					</tbody>
				</table>
		</div>
				<?php
			 }  ?>
				<?php	domicilios($datos_domicilios);


			 
			 if ($rem_complemento_traslado >= 1) {?>
			<br>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
					<tr>
						<td style="text-align:center; width:100%; font-family: Helvetica; font-size: 20px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="5">
							<b>Detalle del Complemento Carta Porte</b>
						</td>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td style="text-align:center; width: 5%; font-family: Helvetica; font-size:20px; vertical-align:center;">
							<b>VersionCCP</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<b>Medio de Transporte</b>
						</td>
						<td style="text-align:center; width:50%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<b>IDCCP</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<b>Tipo de Transporte</b>
						</td>
						<td style="text-align:center; width:15%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<b>Transporte Internacional</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
								<?php echo $rem_versionCCP; ?>
						</td>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<?php echo '01 - Autotransporte Federal'; ?>
						</td>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<?php echo $rem_IDCCP; ?>
						</td>
						<td style="text-align:center; width:30%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<?php echo $rem_tipo_viaje; ?>
						</td>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 20px;vertical-align:center;">
							<?php echo $esTranspoInt; ?>
						</td>
					</tr>

					</tbody>
					
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo "<b>Permiso SCT:</b>"; ?>
						</td>
						<td style="text-align:left; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo "<b>No. Unidad o Remolque:</b>" ?>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Peso B. Vehicular</b>
						</td>
						<td style="text-align:center; width:12%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Aseguradora</b>
						</td>
						<td style="text-align:center; width:13%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Número Póliza</b>
						</td>
						<td style="text-align:center; width:14%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Unidad/Placa</b>
						</td>
						<td style="text-align:center; width:5%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Año</b>
						</td>
						<td style="text-align:center; width:35%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Config. Vehicular / Tipo Remolque</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo $PermisoSCT.'<br><b>Número de Permiso:</b><br>'.$TipoPermisoSCT; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>1.-</b>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_peso. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_nombre. ' / '.$unidad_placas; ?> 
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_anio; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $configautotransporte_clavenomenclatura.": <br>".$configautotransporte_descripcion ; ?>
						</td>
					</tr>
				<?php if (($rem_unidad_id2 > 1)||($rem_permisionario_id > 1)) { ?>
					
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo '<b>Permisionario</b>'; ?>
							
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>2.-</b>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_peso. ' Ton.'; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_polizano; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_nombre. ' / '.$unidad_2_placas; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_anio; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $configautotransporte_2_clavenomenclatura.': '.$configautotransporte_2_descripcion; ?>
						</td>
					</tr>
					<?php	} ?>
					<?php if ($rem_remolque_id > 1) { ?>
						
						<tr>
							<td style="text-align:left; font-family: Helvetica; font-size: 11px;vertical-align:center;">
								<b>-</b>
							</td>
							<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo ' <b>Rem 1.-</b>'; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo ' - '; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo $remolque_aseguradora_nombre; ?>
							
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo $remolque_polizano; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_nombre. ' / '.$remolque_placas; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_anio; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_clave_tipo_remilque.': '.$remolque_remolque_semiremolque; ?>
						</td>
					</tr>
					<?php	}	?>
					<?php if (($rem_remolque2_id > 1) || ($rem_dolly_id > 1)){?>
					<tr>
						<?php if ($rem_dolly_id > 1) { ?>
				
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;vertical-align:center;" >
							<?php echo '<b>Dolly/ Placa/ Año</b><br>'.$dolly_nombre.'/ '.$dolly_placas.'/ '.$dolly_anio;?>							
						</td>
						<?php }else { ?>
						  <td style="text-align:left; font-size: 11px;vertical-align:center;" >
														
						</td>
						<?php } ?>

						<?php if ($rem_remolque2_id > 1) { ?>
						
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo ' <b>Rem 2.-</b>'; ?>						
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>-</b>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							

						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							

						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_nombre.' / '.$remolque2_placas; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_anio; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
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
						<td style="text-align:center; width:150%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="6">
							<b>Figuras de Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura</b>
						</td>
						<td style="text-align:center; width:35%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Nombre</b>
						</td>
						<td style="text-align:center; width:15%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>RFC</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>C.P.</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>No. Licencia</b>
						</td>
						
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>RF</b>
						</td>
						
					</tr>
					<tr>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal; ?>
						</td>
					
					</tr>
					<?php if ($rem_operador_id2 > 1) { ?>
						
					
					<tr>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Tipo Figura 2</b>
						</td>
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Nombre 2</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>RFC 2</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>C.P. 2</b>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>No. Licencia 2</b>
						</td>
				
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>RF 2</b>
						</td>
						
					</tr>
					<tr>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_tipo_figura_2; ?>
						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre_2; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_rfc_2; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_cp_2; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia_2; ?>
						</td>
					
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal_2; ?>
						</td>
						
					</tr>
					<?php }?>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Num. Total Mercancías:<?php echo $rem_totalcantidad; ?> </b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Detalle Mercancías</b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Clave Unidad de Peso: <?php echo $rem_claveunidadpeso; ?></b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Peso Bruto Total: <?php echo $rem_pesototal; ?></b>
						</td>
					</tr>
				</table>
			</div><br>
			<div>
				<?php 
									$resSQL27MP = "SELECT 
									a.MaterialPeligrosoC
							FROM {$prefijobd}remisionessub a 
							WHERE a.FolioSub_RID =".$id_remision;
						   $runSQL27MP = mysqli_query( $cnx_cfdi2 ,$resSQL27MP);
						   while ($rowSQL27MP = mysqli_fetch_array($runSQL27MP)) {
							$materialPeligroso = $rowSQL27MP['MaterialPeligrosoC'];
						   }
						   if ($materialPeligroso == '0') {
							$materialPeligroso = false;
						   } else {
							$materialPeligroso = true;
						   }
						   
			function imprimirEncabezado($estilo_fondo, $materialPeligroso) {

					if ($materialPeligroso) {
						echo '
					<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
						<thead>
							<tr style="page-break-inside: avoid; page-break-after: auto;">
								<th style="text-align:center; width:7%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Cantidad</b>
								</th>
								<th style="text-align:center; width:23%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Embalaje</b>
								</th>
								<th style="text-align:center; width:40%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
								<b>Descripción</b>
								</th>
								<th style="text-align:center; width:15%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
								<b>Tipo Material Peligroso</b>
								</th>
								<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Clave Unidad</b>
								</th>
								<th style="text-align:center; width:7%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Peso kg</b>
								</th>
							</tr>
						</thead>
				<tbody>';
					} else {
						echo '
					<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
						<thead>
							<tr style="page-break-inside: avoid; page-break-after: auto;">
								<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Cantidad</b>
								</th>
								<th style="text-align:center; width:23%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
								<b>Embalaje</b>
								</th>
								<th style="text-align:center; width:40%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
								<b>Descripción</b>
								</th>
								
								<th style="text-align:center; width:15%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Clave Unidad</b>
								</th>
								<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Peso kg</b>
								</th>
							</tr>
						</thead>
				<tbody>';
					}
					
					
			}
				
					imprimirEncabezado($estilo_fondo, $materialPeligroso);?>
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
										a.IdentDocAduanero,
										a.Sello
									FROM {$prefijobd}remisionessub a 
									LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
									LEFT JOIN {$prefijobd}c_claveprodservcp c on a.ClaveProdServCP_RID = c.id 
									LEFT JOIN {$prefijobd}c_tipoembalaje d on a.TipoEmbalaje_RID = d.id 
									LEFT JOIN {$prefijobd}c_materialpeligroso e on a.MaterialPeligroso_RID = e.id 
									LEFT JOIN {$prefijobd}c_clavestcc f on a.ClaveSTCC_RID = f.id 
									LEFT JOIN {$prefijobd}c_fraccionarancelaria g on a.FraccionArancelaria_RID = g.id 
									LEFT JOIN {$prefijobd}c_documentoaduanero j on a.TipoDocumento_RID = j.ID
									LEFT JOIN {$prefijobd}c_tipomateria k on a.TipoMateria_RID = k.ID
								WHERE a.FolioSub_RID =".$id_remision;
					
					

					$runSQL27 = mysqli_query( $cnx_cfdi2 ,$resSQL27);
					$contador = 0;
					if ($rem_tipo_viaje=== "NACIONAL") {
						$porPagina = 22;
						
					}else {
						$porPagina = 9;

					}
					
					$primer_salto = true;

					

				
					//imprimirEncabezado($estilo_fondo);
					while($rowSQL27 = mysqli_fetch_array($runSQL27)){
						
					
						
						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t,$numDecimales); 
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
						$fs_peso = number_format($fs_peso_t,3); 
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
						$fs_sello= $rowSQL27 ['Sello'];
						
						
					
						?>
						

					
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo number_format($rowSQL27['Cantidad'],$numDecimales); ?>
							</td>
							<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo $rowSQL27['Embalaje']; ?><br>
								<?php echo $rowSQL27['ClaveDesignacion'].' - '.$rowSQL27['Descripcion3']; ?>
							</td>
							<td style="text-align:left;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php 
								if($rem_tipo_viaje !== "NACIONAL"){ 
									echo '<b>Clave:</b> '.$rowSQL27['ClaveProducto'].' - '.$rowSQL27['Descripcion2'].
									'<br><b>Detalles:</b> '.$rowSQL27['fsDescripcion'].
										'<br><b>UUID Com Ext:</b> '.$rowSQL27['UUIDComercioExt'].
										' - <b>Tipo Documento:</b> '.$rowSQL27['Clave'].' - '.$rowSQL27['TdDescripcion'].
										'<br><b>Ident. Doc. Aduanero:</b> '.$rowSQL27['IdentDocAduanero'].
										' - <b>Pedimento:</b> '.$rowSQL27['NumeroPedimento'].
										'<br><b>RFC Impo:</b> '.$rowSQL27['RFCImpo'].
										' - <b>Fracción Arancelaria:</b> '.$rowSQL27['Codigo'].' - '.$rowSQL27['Descripcion6'].
										'<br><b>Tipo Materia:</b> '.$rowSQL27['TmClave'].' <b>Desc. Materia:</b> '.$rowSQL27['TmDescripcion'];
										if (!empty($fs_sello)) {
											echo '<br><b>Sello:</b> '.$fs_sello;
										}
									} else {
										echo '<b>Clave:</b> '.$rowSQL27['ClaveProducto'].' - '.$rowSQL27['Descripcion2'].
										'<br><b>Detalles:</b> '.$rowSQL27['fsDescripcion'];
										if (!empty($rowSQL27['Codigo'])) {
										
										echo '<br><b>Fracción Arancelaria:</b> '.$rowSQL27['Codigo'].' - '.$rowSQL27['Descripcion6'];
										}
										if (!empty($fs_sello)) {
											echo '<br><b>Sello:</b> '.$fs_sello;
										}
									} ?>
							</td>
							<?php if ($materialPeligroso) { ?>
								<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
									<?php echo $rowSQL27['ClaveMaterialPeligroso']; ?>
								</td>
								<?php } ?>
								<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
									<?php echo $rowSQL27['ClaveUnidad'].' - '.$rowSQL27['Nombre']; ?>
								</td>
								<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo number_format($rowSQL27['Peso'],3); ?>
							</td>
						</tr>
						<?php

						
						if ($rem_tipo_viaje === "NACIONAL") {
							// Primera página: 4 embalajes
							if ($contador > 0 && $contador == 6 && $primer_salto) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso); 
								$primer_salto = false;

							// Páginas siguientes: cada 22 embalajes
							} elseif (!$primer_salto && ($contador - 6) % 22 == 0) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso);
							}

						} else {
							// Primera página: 2 embalajes
							if ($contador > 0 && $contador == 2 && $primer_salto) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso); 
								$primer_salto = false;

							// Páginas siguientes: cada 9 embalajes
							} elseif (!$primer_salto && ($contador - 2) % 9 == 0) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso);
							}
						}

						$contador++;
					}
			}else { ?>

<br>
			
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="3">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo "<b>Tipo:</b>" ?>
						</td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo "<b>No. Unidad o Remolque:</b>" ?>
						</td>
						
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Unidad/Placa</b>
						</td>
						
					</tr>
					<tr>
					<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo "Unidad"; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo $unidad_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_placas; ?>
						</td>
						
						
					</tr>
				<?php if (($rem_unidad_id2 > 1)||($rem_permisionario_id > 1)) { ?>
					
					<tr>
					<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo "Unidad"; ?>
						</td>
						
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $unidad_2_placas; ?>
						</td>
						
					</tr>
					<?php	} ?>
					<?php if ($rem_remolque_id > 1) { ?>
						
						<tr>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo "Remolque"; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_nombre; ?>
						</td>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque_placas; ?>
						</td>
						
					</tr>
					<?php	}	?>
					<?php if (($rem_remolque2_id > 1) || ($rem_dolly_id > 1)){?>
					<tr>
						<?php if ($rem_dolly_id > 1) { ?>
				
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;vertical-align:center;" >
							<?php echo '<b>Dolly/ Placa/ Año</b><br>'.$dolly_nombre.'/ '.$dolly_placas.'/ '.$dolly_anio;?>							
						</td>
						<?php } ?>

						<?php if ($rem_remolque2_id > 1) { ?>
							<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
						<?php echo "Remolque"; ?>
						</td>
						
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_nombre; ?>

						</td>
						<td style="text-align:center; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $remolque2_placas; ?>
						</td>
					
						<?php } ?>
					</tr>
					<?php } ?>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:150%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="2">
							<b>Figuras de Transporte</b>
						</td>
					</tr>
					<tr>
						
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Nombre</b>
						</td>
						
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>No. Licencia</b>
						</td>
						
					</tr>
					<tr>
						
						<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						
					</tr>
					<?php if ($rem_operador_id2 > 1) { ?>
						
					
					<tr>
						
						<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>Nombre 2</b>
						</td>
						
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<b>No. Licencia 2</b>
						</td>
						
					</tr>
					<tr>
						
						<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
							<?php echo $operador_nombre_2; ?>
						</td>
						
						
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;vertical-align:center;">
							<?php echo $operador_licencia_2; ?>
						</td>
						
					</tr>
					<?php }?>
				</table>
			</div>
						<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Num. Total Mercancías:<?php echo $rem_totalcantidad; ?> </b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Detalle Mercancías</b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Clave Unidad de Peso: <?php echo $rem_claveunidadpeso; ?></b>
						</td>
						<td style="text-align:center; width:25%; font-family: Helvetica; font-size: 11px;vertical-align:center;<?php echo $estilo_fondo; ?>">
							<b>Peso Bruto Total: <?php echo $rem_pesototal; ?></b>
						</td>
					</tr>
				</table>
			</div><br>
			<div>
				<?php  			$resSQL27MP = "SELECT 
										a.MaterialPeligrosoC
								FROM {$prefijobd}remisionessub a 
								WHERE a.FolioSub_RID =".$id_remision;
				               $runSQL27MP = mysqli_query( $cnx_cfdi2 ,$resSQL27MP);
							   while ($rowSQL27MP = mysqli_fetch_array($runSQL27MP)) {
								$materialPeligroso = $rowSQL27MP['MaterialPeligrosoC'];
							   }
							   if ($materialPeligroso == '0') {
								$materialPeligroso = false;
							   } else {
								$materialPeligroso = true;
							   }
							   
				function imprimirEncabezado($estilo_fondo, $materialPeligroso) {

						if ($materialPeligroso) {
							echo '
						<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
							<thead>
								<tr style="page-break-inside: avoid; page-break-after: auto;">
									<th style="text-align:center; width:7%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Cantidad</b>
									</th>
									<th style="text-align:center; width:23%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Embalaje</b>
									</th>
									<th style="text-align:center; width:40%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Descripción</b>
									</th>
									<th style="text-align:center; width:15%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Tipo Material Peligroso</b>
									</th>
									<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Unidad</b>
									</th>
									<th style="text-align:center; width:7%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Peso kg</b>
									</th>
								</tr>
							</thead>
					<tbody>';
						} else {
							echo '
						<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
							<thead>
								<tr style="page-break-inside: avoid; page-break-after: auto;">
									<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Cantidad</b>
									</th>
									<th style="text-align:center; width:23%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Embalaje</b>
									</th>
									<th style="text-align:center; width:40%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Descripción</b>
									</th>
									
									<th style="text-align:center; width:15%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
									<b>Unidad</b>
									</th>
									<th style="text-align:center; width:10%; font-family: Helvetica; font-size: 10px;vertical-align:center;'. $estilo_fondo.'">
										<b>Peso kg</b>
									</th>
								</tr>
							</thead>
					<tbody>';
						}
						
						
				}
				
					imprimirEncabezado($estilo_fondo, $materialPeligroso);?>
		
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
									FROM {$prefijobd}remisionessub a 
									LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
									LEFT JOIN {$prefijobd}c_claveprodservcp c on a.ClaveProdServCP_RID = c.id 
									LEFT JOIN {$prefijobd}c_tipoembalaje d on a.TipoEmbalaje_RID = d.id 
									LEFT JOIN {$prefijobd}c_materialpeligroso e on a.MaterialPeligroso_RID = e.id 
									LEFT JOIN {$prefijobd}c_clavestcc f on a.ClaveSTCC_RID = f.id 
									LEFT JOIN {$prefijobd}c_fraccionarancelaria g on a.FraccionArancelaria_RID = g.id 
									LEFT JOIN {$prefijobd}c_documentoaduanero j on a.TipoDocumento_RID = j.ID
									LEFT JOIN {$prefijobd}c_tipomateria k on a.TipoMateria_RID = k.ID
								WHERE a.FolioSub_RID =".$id_remision;
					
					

					$runSQL27 = mysqli_query( $cnx_cfdi2 ,$resSQL27);
					$contador = 0;
					if ($rem_tipo_viaje=== "NACIONAL") {
						$porPagina = 22;
						
					}else {
						$porPagina = 9;

					}
					
					$primer_salto = true;

					

				
					//imprimirEncabezado($estilo_fondo);
					while($rowSQL27 = mysqli_fetch_array($runSQL27)){
						
					
						
						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t,$numDecimales); 
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
						$fs_peso = number_format($fs_peso_t,3); 
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
						$fs_sello= $rowSQL27 ['Sello'];
						
						
					
						?>
						

						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo number_format($rowSQL27['Cantidad'],$numDecimales); ?>
							</td>
							<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo $rowSQL27['Embalaje']; ?><br>
								<?php echo $rowSQL27['ClaveDesignacion'].' - '.$rowSQL27['Descripcion3']; ?>
							</td>
							<td style="text-align:left;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php 
								if($rem_tipo_viaje !== "NACIONAL"){ 
									echo '<b>Clave:</b> '.$rowSQL27['ClaveProducto'].' - '.$rowSQL27['Descripcion2'].
									'<br><b>Detalles:</b> '.$rowSQL27['fsDescripcion'].
									'<br><b>UUID Com Ext:</b> '.$rowSQL27['UUIDComercioExt'].
									' - <b>Tipo Documento:</b> '.$rowSQL27['Clave'].' - '.$rowSQL27['TdDescripcion'].
									'<br><b>Ident. Doc. Aduanero:</b> '.$rowSQL27['IdentDocAduanero'].
									' - <b>Pedimento:</b> '.$rowSQL27['NumeroPedimento'].
									'<br><b>RFC Impo:</b> '.$rowSQL27['RFCImpo'].
									' - <b>Fracción Arancelaria:</b> '.$rowSQL27['Codigo'].' - '.$rowSQL27['Descripcion6'].
									'<br><b>Tipo Materia:</b> '.$rowSQL27['TmClave'].' <b>Desc. Materia:</b> '.$rowSQL27['TmDescripcion'];
									if (!empty($fs_sello)) {
											echo '<br><b>Sello:</b> '.$fs_sello;
										}
								} else {
									echo '<b>Clave:</b> '.$rowSQL27['ClaveProducto'].' - '.$rowSQL27['Descripcion2'].
									'<br><b>Detalles:</b> '.$rowSQL27['fsDescripcion'];
									if (!empty($rowSQL27['Codigo'])) {
										
										echo '<br><b>Fracción Arancelaria:</b> '.$rowSQL27['Codigo'].' - '.$rowSQL27['Descripcion6'];
									}
									if (!empty($fs_sello)) {
											echo '<br><b>Sello:</b> '.$fs_sello;
										}
								} ?>
							</td>
							<?php if ($materialPeligroso) { ?>
								<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
									<?php echo $rowSQL27['ClaveMaterialPeligroso']; ?>
								</td>
							<?php } ?>
							<td style="text-align:center; font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo $rowSQL27['ClaveUnidad'].' - '.$rowSQL27['Nombre']; ?>
							</td>
							<td style="text-align:center;font-family: Helvetica; font-size: 10px;vertical-align:center;">
								<?php echo number_format($rowSQL27['Peso'],3); ?>
							</td>
						</tr>
						<?php

						
						if ($rem_tipo_viaje === "NACIONAL") {
							// Primera página: 4 embalajes
							if ($contador > 0 && $contador == 6 && $primer_salto) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso); 
								$primer_salto = false;

							// Páginas siguientes: cada 22 embalajes
							} elseif (!$primer_salto && ($contador - 6) % 22 == 0) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso);
							}

						} else {
							// Primera página: 2 embalajes
							if ($contador > 0 && $contador == 2 && $primer_salto) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso); 
								$primer_salto = false;

							// Páginas siguientes: cada 9 embalajes
							} elseif (!$primer_salto && ($contador - 2) % 9 == 0) {
								echo '</tbody></table>';
								echo '<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />';
								imprimirEncabezado($estilo_fondo, $materialPeligroso);
							}
						}

						$contador++;
					}
				 }
						?>
					</tbody>
				</table>
			<?php if ($rem_lleva_repartos >=1 || $rem_tipo_viaje != 'NACIONAL' && $rem_complemento_traslado >=1) { ?>
					 <pagebreak />
				<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
				<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />
				<?php }  ?>
						
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
                FROM {$prefijobd}remisionesrepartos a 
                LEFT JOIN {$prefijobd}c_colonia b on a.RemitenteColonia_RID = b.id 
                LEFT JOIN {$prefijobd}estados c on a.RemitenteEstado_RID = c.id 
                LEFT JOIN {$prefijobd}c_localidad d on a.RemitenteLocalidad2_RID = d.id 
                LEFT JOIN {$prefijobd}c_municipio e on a.RemitenteMunicipio_RID = e.id 
                LEFT JOIN {$prefijobd}c_colonia f on a.DestinatarioColonia_RID = f.id 
                LEFT JOIN {$prefijobd}estados g on a.DestinatarioEstado_RID = g.id 
                LEFT JOIN {$prefijobd}c_localidad h on a.DestinatarioLocalidad2_RID = h.id 
                LEFT JOIN {$prefijobd}c_municipio i on a.DestinatarioMunicipio_RID = i.id
                WHERE a.FolioSub_RID =".$id_remision;

    $runSQL31 = mysqli_query($cnx_cfdi2, $resSQL31);

/* 	die($resSQL31);
 */
    if ((mysqli_num_rows($runSQL31) > 0) && ($rem_lleva_repartos >=1)) {
        while ($rowSQL31 = mysqli_fetch_assoc($runSQL31)) {
            foreach ($rowSQL31 as $key => $value) {
                $rowSQL31[$key] = htmlspecialchars($value);
            }

            extract($rowSQL31); 

            ?>
            <table style="margin-bottom: 5px; width:100%; font-family: Helvetica; font-size:12px; border-collapse: collapse;">
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
			
			<br>


				<?php 
				if(!($rem_tipo_viaje === "NACIONAL") && $rem_complemento_traslado >= 1){
					?>
					
					<div style= "margin-top:-3px;">
						<table  style="margin:0;border-collapse: collapse;" width="100%">
							<tr colspan='2'>
							<td style="text-align:center; width:50%; font-family: Helvetica; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="2">
									<b>REGIMEN ADUANERO </b>
									<tr>
										<td style="text-align:left; width:50%; font-family: Helvetica; font-size:11px;padding: 2px;vertical-align:center;<?php echo $estilo_fondo; ?>">
											<b>CLAVE</b>
										</td>
										<td style="text-align:left; width:50%; font-family: Helvetica; font-size:11px;padding: 2px;vertical-align:center;<?php echo $estilo_fondo; ?>">
											<b>DESCRIPCION</b>
										</td>
					<?php
					
					
								$resSQL32 = "SELECT
										Ra.Clave, 
										Ra.Descripcion 
									FROM {$prefijobd}remisiones as f
									LEFT JOIN {$prefijobd}remisionesregimenaduanero as Fr on Fr.FolioSub_RID= f.ID 
									LEFT JOIN {$prefijobd}c_regimenaduanero as Ra on Ra.ID = fr.Regimen_RID 
								
								WHERE f.ID =".$id_remision;
		
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
									<td style='text-align:left; width:50%; font-family: Helvetica; font-size:10px;padding: 2px;'>$fra_clave</td>
									<td style='text-align:left; width:50%; font-family: Helvetica; font-size:10px;padding: 2px;'>$fra_descripcion</td>
								</tr>";
		
							$t2++; 
						}
		
						echo "</table>"; 
					} else {
						echo "<p>No se encontraron resultados.</p>";
					}
		
					}
					?>
					
					</td>
					</tr>
					</table>
				</div>

<?php

function bitacora()
{
    global $req_bitacora,
           $rutalogo, $RazonSocial, $RFC, $rem_xfolio, $rem_creado, $rem_referencia,
           $unidad_nombre, $unidad_placas, $cliente_nombre,
           $remitente_localidad_nombre, $destinatario_localidad_nombre,
           $operador_nombre, $operador_licencia, $estilo_fondo;

    if ($req_bitacora >= 1) { 
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
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $rem_xfolio; ?></b></label></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $rem_creado; ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Referencia</b></td>
                                    <td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $rem_referencia; ?></td>
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
                        <td><?php echo $rem_creado; ?></td>
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
        } 
    } 
}

?>



	<?php
	function imprimirContrato()
	{
			global $req_contrato, $f_complemento_traslado;
			
		
		if ($req_contrato >= 1) { ?>
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
	</body>
</html>


<?php
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();
// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();
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

$nombre_pdf = ($rem_cfdfolio > 0) ? $rem_cfdserie . "-" . $rem_cfdfolio : $prefijo . " - " . $rem_xfolio;

//Attachment" => false -- Para que no se descargue automaticamente
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

?>
A