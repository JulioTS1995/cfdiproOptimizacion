<?php

use Dom\HTMLDocument;

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

//Buscar datos para encabezado system settings
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
	$Regimen = $rowSQL0['Regimen'];
	$PermisoSCT = $rowSQL0['PermisoSCT'];
	$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
	$codLocalidad = '';
	
	
}

//Buscar datos de la Factura - CAMBIAR POR PARAMETRO EL ID


$resSQL01 = "SELECT * FROM {$prefijobd}remisiones WHERE id=".$id_remision;
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);

while($rowSQL01 = mysqli_fetch_array($runSQL01)){
	
	$rem_cfdserie = $rowSQL01['cfdserie'];
	$rem_cfdfolio = $rowSQL01['cfdfolio'];
	$rem_xfolio = $rowSQL01['XFolio'];
	$rem_creado_t = $rowSQL01['Creado'];
	$rem_revisado_t = $rowSQL01 ['Revision'];
	if(empty($rem_revisado_t)){
		$rem_revisado_t="-";
	}else{
		$rem_revisado_t=date("d-m-Y H:i:s", strtotime($rem_revisado_t));
	}
	$rem_creado = date("d-m-Y H:i:s", strtotime($rem_creado_t));
	$rem_ticket = $rowSQL01['Ticket'];
	$rem_moneda = $rowSQL01['Moneda'];
	$rem_subtotal_t = $rowSQL01['zSubtotal'];
	$rem_subtotal = number_format($rem_subtotal_t,2); 
	$rem_impuesto_t = $rowSQL01['zImpuesto'];
	$rem_impuesto = number_format($rem_impuesto_t,2);
	$rem_retenido_t = $rowSQL01['zRetenido'];
	$rem_retenido = number_format($rem_retenido_t,2); 
	$rem_total_t = $rowSQL01['zTotal'];
	$rem_total = number_format($rem_total_t,2); 
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
	$rem_remitente_numext = $rowSQL01['RemitenteNumExt'];
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
	$rem_destinatario_numext = $rowSQL01['DestinatarioNumExt'];
	$rem_destinatario_colonia_id = $rowSQL01['DestinatarioColonia_RID'];
	$rem_destinatario_municipio_id = $rowSQL01['DestinatarioMunicipio_RID'];
	$rem_destinatario_estado_id = $rowSQL01['DestinatarioEstado_RID'];
	$rem_destinatario_cp = $rowSQL01['DestinatarioCodigoPostal'];
	$rem_destinatario_pais = $rowSQL01['DestinatarioPais'];
	$rem_destinatario_numregidtrib = $rowSQL01['DestinatarioNumRegIdTrib'];
	$rem_destinatario_citacarga_t = $rowSQL01['DestinatarioCitaCarga'];
	$rem_destinatario_citacarga = date("d-m-Y H:i:s", strtotime($rem_destinatario_citacarga_t));
	$rem_destinatario_telefono = $rowSQL01['DestinatarioTelefono'];
	$rem_comentarios = $rowSQL01['Comentarios'];
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
	//$rem_totalcantidad = number_format($rem_totalcantidad_t,2); 
	//$rem_totalcantidad = 0;
	$rem_pesototal_t= $rowSQL01['xPesoTotal'];
	$rem_pesototal = number_format($rem_pesototal_t,2);
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
	$rem_total_letra = convertir($rem_total, $rem_moneda);
	$rem_yflete  = $rowSQL01['yFlete'];
	$rem_yseguro = $rowSQL01['ySeguro'];
	$rem_ycarga = $rowSQL01['yCarga'];
	$rem_ydescraga = $rowSQL01['yDescarga'];
	$rem_yrecoleccion = $rowSQL01['yRecoleccion'];
	$rem_yrepartos = $rowSQL01['yRepartos'];
	$rem_ydemoras = $rowSQL01['yDemoras'];
	$rem_yautopistas = $rowSQL01['yAutopistas'];
	$rem_yotros = $rowSQL01['yOtros']; 
	$rem_zSubTotal = $rowSQL01 ['zSubtotal'];
	$rem_zIVA = $rowSQL01 ['zImpuesto'];
	$rem_zRet = $rowSQL01 ['zRetenido'];
	$rem_zTotal = $rowSQL01 ['zTotal'];  
	$rem_FolioSalida = $rowSQL01 ['FolioSalida'];
	$rem_Concentrado = $rowSQL01 ['Concentrado'];
	$rem_Instrucciones = $rowSQL01 ['Instrucciones'];
	$remitente_localidad_nombre_REM =$rowSQL01 ['RemitenteLocalidad'];
	$destinatario_localidad_nombre_REM =$rowSQL01 ['DestinatarioLocalidad'];
   if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}

}

if ($Multi == 1){
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
      $rutalogo= $ruta_logo_multi;
		
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
      $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';
	}
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


if(!empty($rem_unidad_id)){

	$resSQL21 = "SELECT 
				u.Unidad, 
				u.PolizaNo,
				u.Placas
				
				FROM {$prefijobd}unidades as u
				
	 			WHERE u.ID=".$rem_unidad_id;

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
				u.Placas
				
				FROM {$prefijobd}unidades u
				WHERE u.ID= {$rem_unidad_id2}";

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
      
    }else{
		$mensaje  = 'Consulta no valida 1: ' . mysqli_error($cnx_cfdi2) . "\n";
		die($mensaje);
	}
}

	


//Buscar Remolque

$remolque_nombre = '';
$remolque_placas = '';
$remolque_anio = '';


if(!empty($rem_remolque_id)){


$resSQL23 = "SELECT 
				Unidad,
				Placas,
				Ano
				
				FROM {$prefijobd}Unidades 
				
				WHERE ID=".$rem_remolque_id ;

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
		
	}
}



//busca Remolque 2
$remolque2_nombre = '-';
$remolque2_placas = '-';
$remolque2_anio = '-';


if(!empty($rem_remolque2_id)){


$resSQL41 = "SELECT 
				u.Unidad,
				u.Placas,
				u.Ano
				
				FROM {$prefijobd}Unidades u
				
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
					o.LicenciaNo
					
				 FROM {$prefijobd}Operadores as  o
				
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
		
		
	}
} else {
	$operador_tipo_figura = '';
	$operador_nombre = '';
	$operador_rfc = '';
	$operador_licencia = '';
	
}

//busca operador 2
if($rem_operador_id2 > 0){
	//Buscar Operador
	$resSQL25 = "SELECT 
					o.TipoFigura,
					o.Operador,
					o.RFC,
					o.LicenciaNo
					
				 FROM {$prefijobd}Operadores as  o
				 
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
		
		
	}
} else {
	$operador_tipo_figura_2 = '';
	$operador_nombre_2 = '';
	$operador_rfc_2 = '';
	$operador_licencia_2 = '';
	
}


 $resSQL40 = "SELECT
					a.Cantidad 
				FROM {$prefijobd}facturassub a 
				LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
				
				WHERE a.FolioSub_RID =".$id_factura;
$runSQL40 = mysqli_query( $cnx_cfdi2 ,$resSQL40);
$rem_totalcantidad = 0;

while($rowSQL40 = mysqli_fetch_array($runSQL40)){
	$fs_cantidad_t = $rowSQL40['Cantidad'];
	
	$rem_totalcantidad++ ;


	
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

 //colores multi
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

$parametro_bitacora = 924;
$resSQL924 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 = $parametro_bitacora";
$runSQL924 = mysqli_query($cnx_cfdi2, $resSQL924);
	 
while ($rowSQL924 = mysqli_fetch_array($runSQL924)) {
	$param= $rowSQL924['id2'];
	$req_bitacora = $rowSQL924 ['VLOGI'];
}

$parametroNoCliente = 944;
$noCliente = 0;
$resSQL944 = "SELECT id2,  VLOGI FROM {$prefijobd}parametro Where id2 = $parametroNoCliente";
$runSQL944 = mysqli_query($cnx_cfdi2, $resSQL944);
while ($rowSQL944 = mysqli_fetch_array($runSQL944)) {
	$param= $rowSQL944['id2'];
	$noCliente = $rowSQL944 ['VLOGI'];
}
 

$nombre_factura= '';
if ($rem_complemento_traslado == 1) {
	$nombre_factura = 'Factura / Complemento CCP';
	} else {
	$nombre_factura= 'Factura';
}





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
		
		
		
		<title> <?php echo $RazonSocial.': '.$rem_xfolio ;?></title>	
	</head>
	<body>
		<header>
			<div style = "margin-top: 5px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src='<?php echo $rutalogo;?>' width="130px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-size: 10px;">
							<table border="1" cellspacing="0" cellpadding="0" width="100%" style= "position:fixed;top:-105px;right:-5px;border: 2px solid rgba(0, 0, 0, 0.95);">
								<tr style="border: 2px solid rgba(0, 0, 0, 0.95);">
									<td colspan="2" style="text-align:center; border: 1px solid rgba(0, 0, 0, 0.5);font-size: 20px;padding: 1;height:40px;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Carta Porte</b></td>
								</tr>
								
								<tr style="border: 2px solid rgba(0, 0, 0, 0.95);">
									<td style="text-align:center; width:100%%; font-size: 20px;padding: 1;vertical-align: center;border: 1px solid rgba(0, 0, 0, 0.95);" colspan="2"><label style="color:red"><b><?php echo $rem_xfolio; ?></b></label></td>
								</tr>
							
							</table>
						</td>
					</tr>

				</table>
			</div>
		</header>

		
		

		
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
			<div>
				<table border="0" width="100%" style="margin-top:-32px;border-collapse:collapse;">
					<thead style= "border: 1px solid rgba(0, 0, 0, 0.97);">
						<tr>
							<td style="text-align:Center; width:43%; font-size:12px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>" colspan='1'>Lugar de expedicion</td>
							<td style="text-align:Center; width:29%; font-size:12px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>" colspan='2'>Fecha de expedicion</td>
							<td style="text-align:Center; width:35%; font-size:12px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>" colspan='3'>Fecha de vencimiento</td>
						</tr>

					</thead>
					<tbody style= "border:1px solid rgba(0, 0, 0, 0.97);">
						<td style="text-align:center; width:40%; font-size:10px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);" colspan='1'> <?php echo "".$Ciudad.", ".$Estado.", ".$CodigoPostal;?></td>
						<td style="text-align:center; width:26%; font-size:10px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);" colspan='2'> <?php echo date("d-m-Y H:i:s", strtotime($rem_creado_t));?> </td>
						<td style="text-align:center; width:33%; font-size:10px;padding-bottom: 0px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);" colspan='3'> <?php echo $rem_revisado_t;?></td>
					</tbody>

				</table>
			</div>
         <?php if ($noCliente == '1'){
						echo ' ';
					} else {
						
						?>
			<div>
				<table border="0" style="margin-top:-5px;border-collapse: collapse; padding-top:3px;border: 1px solid rgb(0, 0, 0);" width="100%">
					<thead>
						<tr>
							<td colspan = "2" style="font-size:13px;vertical-align:left;<?php echo $estilo_fondo?>">CLIENTE</td>
						</tr>
					</thead>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-size: 11px;">
							<?php echo $cliente_nombre; ?>
						</td>
						
						<td style="text-align:left; width:40%; font-size: 11px;">
							<?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 11px;">
							<?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						
						<td style="text-align:left; font-size: 11px;">
							
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 11px;">
							COL <?php echo $cliente_colonia; ?>
						</td>
						<td style="text-align:left; font-size: 11px;">
							
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 11px;" >
							CP: <?php echo $cliente_cp.' '.$cliente_municipio.' '.$cliente_estado; ?>
						</td>
						<td style="text-align:left; font-size: 11px;">
							
						</td>
					</tr>
				</table>
         </div>
				
            <?php } ?>
			<div>
				<table border="1" style="margin-top:-3px;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:left; width:50%; font-size:14px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Origen</b>
						</td>
						<td style="text-align:left; width:50%; font-size:14px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='4'>
							<b>Destino</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:13px;padding-bottom: 0px;vertical-align:center;" colspan='2'>
							<b>SE RECOGERA EN: </b><br><br>
							<?php echo $rem_remitente; ?><br>
							RFC: <?php echo $rem_remitente_rfc; ?><br>
							<?php echo $rem_remitente_calle.' No.'.$rem_remitente_numext; ?><?php echo 'Col.'.$remitente_colonia_nombre.', '.$remitente_municipio_nombre.','; ?><?php echo $remitente_estado_nombre.' C.P.'.$rem_remitente_cp.','; ?><br>
							</td>
						<td style="text-align:left; width:50%; font-size:13px;padding-bottom: 0px;vertical-align:center;" colspan='4'>
							<b>SE ENTREGARA EN: </b><br><br>
							<?php echo $rem_destinatario; ?><br>
							RFC: <?php echo $rem_destinatario_rfc; ?><br>
							<?php echo $rem_destinatario_calle.' No.'.$rem_destinatario_numext; ?><?php echo 'Col.'.$destinatario_colonia_nombre.', '.$destinatario_municipio_nombre.','; ?><?php echo $destinatario_estado_nombre.' C.P.'.$rem_destinatario_cp.','; ?><br>
							
						</td>
					</tr>
				</table>
			</div>
			<div>
			<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<thead style= "border: 1px solid rgba(0, 0, 0, 0.97);">
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<th style="text-align:center; width:5%; font-size: 11px;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>CANT.</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 11px;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>UNI.</b>
							</th>
							<th style="text-align:center; width:50%; font-size: 11px;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>DESCRIPCIÓN</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 11px;vertical-align:center;border-bottom: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:13%; font-size: 11px;vertical-align:center;border-bottom: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>VOLUMEN</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);border-bottom: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:7%; font-size: 11px;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>CONCEPTO</b>
							</th>
							<th style="text-align:center; width:7%; font-size: 11px;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>IMPORTE</b>
							</th>
						</tr>
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<th style="text-align:center; width:5%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:10%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:50%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:15%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>PESO</b>
							</th>
							<th style="text-align:center; width:13%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>M3</b>
							</th>
							<th style="text-align:center; width:7%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b>PESO EST.</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
							<th style="text-align:center; width:15%; font-size: 11px;vertical-align:center;border-right: 1px solid rgba(0, 0, 0, 0.97);<?php echo $estilo_fondo; ?>">
								<b></b>
							</th>
						</tr>
					</thead>
					<tbody style="height:200px;">
					
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
										a.PesoEstimado,
										a.Mt3
									FROM {$prefijobd}remisionessub a 
									LEFT JOIN {$prefijobd}c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id 
									LEFT JOIN {$prefijobd}c_claveprodservcp c on a.ClaveProdServCP_RID = c.id 
									LEFT JOIN {$prefijobd}c_tipoembalaje d on a.TipoEmbalaje_RID = d.id 
									LEFT JOIN {$prefijobd}c_materialpeligroso e on a.MaterialPeligroso_RID = e.id 
									LEFT JOIN {$prefijobd}c_clavestcc f on a.ClaveSTCC_RID = f.id 
									LEFT JOIN {$prefijobd}c_fraccionarancelaria g on a.FraccionArancelaria_RID = g.id 
									LEFT JOIN {$prefijobd}clientesdestinos h on a.IDDestino_RID = h.id 
									LEFT JOIN {$prefijobd}clientesdestinos i on a.IDOrigen_RID = i.id
								WHERE a.FolioSub_RID =".$id_remision;
					

					$runSQL27 = mysqli_query( $cnx_cfdi2 ,$resSQL27);
					
           
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
						$fs_peso_est= $rowSQL27['PesoEstimado'];
						$fs_mt3 = $rowSQL27['Mt3'];
						
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
						}else{
							$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
							die($mensaje);
						} */
					
						
						

					?>
						<tr  style="page-break-inside: avoid; page-break-after: auto;border: 1px solid rgba(0, 0, 0, 0.97);height:200px;"  >
							<td style="text-align:center; font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);">
								<?php echo $fs_cantidad; ?>
							</td>
							<td style="text-align:center; font-size: 10px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								<?php echo $fs_nombre; ?>
							</td>
							<td style="text-align:left;font-size: 10px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">

								<?php echo $fs_descripcion1; ?>
							</td>
							<td style="text-align:center;font-size: 12px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								<?php echo $fs_peso; ?>
							</td>
							<td style="text-align:center;font-size: 10px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								<?php echo $fs_mt3;?>
								
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								<?php echo $fs_peso_est;?>
							</td>
                  </tr>
						<?php
						
						}
                
                 ?>
						<tr  style="page-break-inside: avoid; page-break-after: auto;border: 1px solid rgba(0, 0, 0, 0.97);height:200px;"  >
							<td style="text-align:center; font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97);">
								
							</td>
							<td style="text-align:center; font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								
							</td>
							<td style="text-align:center;font-size: 10px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">

								Folios de viaje: <?php echo $rem_FolioSalida;?> <br>
								Concentrado: <?php echo $rem_Concentrado;?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
								
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
							
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;border: 1px solid rgba(0, 0, 0, 0.97)">
							</td>
               </tr>
               </tbody>
			</div>

	
			
			<br>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;position:fixed;top: 245px;right:-5px;font-size:11px;" width="21%">
					<tr>
						<td style="width:50%;"colspan="1">FLETE</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yflete,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">SEGURO</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yseguro,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">CARGA</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_ycarga,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">DESCARGA</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_ydescraga,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">RECOLECCION</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yrecoleccion,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">REPARTOS</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yrepartos,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">DEMORAS</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_ydemoras,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">AUTOPISTAS</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yautopistas,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;"colspan="1">OTROS</td>
						<td style="width:50%;text-align:right;"colspan="2"><?php echo "$ ".number_format((float)$rem_yotros,2,',','.')?></td>
					</tr>
				</table>
			</div>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;position:fixed;top:660px;right:-2px;font-size:11px;" width="21%">
					<tr>
						<td style="width:50%;<?php echo $estilo_fondo; ?>"colspan="1">SUBTOTAL</td>
						<td style="width:50%;"colspan="2"><?php echo "$ ".number_format((float)$rem_zSubTotal,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;<?php echo $estilo_fondo; ?>"colspan="1">I.V.A 16%</td>
						<td style="width:50%;"colspan="2"><?php echo "$ ".number_format((float)$rem_zIVA,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;<?php echo $estilo_fondo; ?>"colspan="1">RETENCION 4%</td>
						<td style="width:50%;"colspan="2"><?php echo "$ ".number_format((float)$rem_zRet,2,',','.')?></td>
					</tr>
					<tr>
						<td style="width:50%;<?php echo $estilo_fondo; ?>"colspan="1">TOTAL</td>
						<td style="width:50%;"colspan="2"><?php echo "$ ".number_format((float)$rem_zTotal,2,',','.')?></td>
					</tr>
					
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;position:fixed;top:740px;right:-1px;font-size:11px;" width="21%">
					<tr>
						<td style="width:100%;border: 1px 1px 0px 1px solid rgba(0, 0, 0, 0.97)"colspan="1">Recibi de conformidad<br><br><br><br><br><br><br></td>
						
					</tr>
					<tr>
						<td style="width:100%;>"colspan="1"><?php echo '<hr style="border: 1px solid black;margin-top: -15px; width:90%;">';?></td>
						
					</tr>
				
					
				</table>
			</div>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;position:fixed;top:700px;" width="79%" >
					<thead>
						<tr>
							<td  style="text-align:center; width:100%; font-size: 12px;" colspan= "2"><b><?php echo "***(".$rem_total_letra.")***"?></b></td>
						</tr>
					</thead>
					<tr>
						<td  style="text-align:left; width:50%; font-size: 11px;vertical-align:left;"">
							<b>Operador: </b> <?php echo $operador_nombre; ?><br>
							<b>No. Licencia: </b> <?php echo $operador_licencia; ?>
						</td>
						<?php if ($rem_operador_id2 > 1) { ?>
							<td style="text-align:left; width:50%; font-size: 11px;vertical-align:left;">
								<b>Operador 2: </b> <?php echo $operador_nombre_2; ?><br>
								<b>No. Licencia OP 2: </b> <?php echo $operador_licencia_2; ?>
							</td>
						<?php } else { ?>
							<td style="text-align:left; width:50%; font-size: 11px;vertical-align:left;">
							
							</td>
						<?php } ?>
						
					</tr>	
					<tr>
							<td style="text-align:left; width:50%; font-size: 11px;vertical-align:left;">
								<b>Unidad: </b><?php echo $unidad_nombre; ?>
								<br>
								<b>Placas: </b><?php echo $unidad_placas; ?>
							</td>
							<td style="text-align:left; width:50%; font-size: 11px;vertical-align:left;">
							
								<b>Remolque A: </b><?php echo $remolque_nombre; if ($rem_remolque2_id > 1) { ?> <b>Remolque B:</b><?php echo $remolque2_nombre; } ?>
								<br>
								<b>Placas: </b><?php echo $remolque_placas; if ($rem_remolque2_id > 1) { ?> <b>Placas B :</b><?php echo $remolque2_placas; } ?>
							</td>
					</tr>
					<tr>
							<td style="text-align:Center; width:50%; font-size: 11px;vertical-align:Center;">
								Comentarios <br>
								<?php echo $rem_comentarios; ?>
							</td>
							<td style="text-align:Center; width:50%; font-size: 11px;vertical-align:Center;">
								Instrucciones
								<br><br><br><br><?php echo $rem_Instrucciones;?>

							</td>
					</tr>
					
				</table>
			</div>

			<div style="position:fixed; bottom:-30px;">
				<b style="text-align:justify; font-size:10px;width:100%; color:rgb(255, 0, 0);">LA MERCANCIA QUE AMPARA ESTA CARTA PORTE, VIAJA POR CUENTA Y RIESGO DEL REMITENTE Y/O DESTINATARIO. EL SERVICIO DE TRANSPORTE, SE REALIZA LIBRE DE MANIOBRAS PARA EL PERSONAL DE ABORDO, POR LO TANTO, DICHAS MANIOBRAS, SON RESPOSABILIDAD DEL REMITENTE Y/O DESTINATARIO.</b>
			</div>

		
					
			
				
				
					
				

			
			<?php if ($req_bitacora == 1) { ?>

         
			
		
			
			<!-- FIN Subreporte 1 -->
			
			 <div class="page-break"></div> 
			 <div>
				<table style="font-size:11px;"width= "100%">
					<tbody>
						<tr>
							<td>UNIDAD: </td>
							<td><b><?php echo $unidad_nombre ?></b></td>
							<td>PLACA: </td>
							<td><b><?php echo $unidad_placas?></b> </td>

						</tr>
						<tr>
							<td>CLIENTE: </td>
							<td><?php echo $cliente_nombre?> </td>
							<td>FECHA</td>
							<td><?php echo $rem_creado_t?> </td>
						</tr>
						<tr>
							<td>ORIGEN: </td>
							<td><?php echo $remitente_localidad_nombre_REM?></td>
							<td>DESTINO: </td>
							<td><?php echo $destinatario_localidad_nombre_REM?></td>
						</tr>
						<tr>
							<td>OPERADOR: </td>
							<td><?php echo $operador_nombre?> </td>
							<td>LICENCIA: </td>
							<td><?php echo $operador_licencia?> </td>
						</tr>
					</tbody>
					
				</table>

			 </div>
			 <table border="0" style="margin-top:0;;border-collapse: collapse;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
			<tr>
				<td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315">BITACORA HORAS DE VIAJE</td>
			</tr>
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 1</b></td>
            </tr>
        </thead>
        <tr style="margin-top:0;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>
<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 2</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table><table border="0" style="margin:0;border-collapse: collapse;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 3</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>
<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 4</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>
<table border="0" style="margin:0;border-collapse: collapse;page-break-inside: avoid; page-break-after: auto;position:fixed;top:460px" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF;  height:15px;" colspan="315"><b>DIA 5</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>
<table border="0" style="margin:0;border-collapse: collapse;page-break-inside: avoid; page-break-after: auto;position:fixed;top:552px;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 6</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>
<table border="0" style="margin:0;border-collapse: collapse;page-break-inside: avoid; page-break-after: auto;position:fixed;top:644px;" width="100%">
        <thead style="height:15px;<?php echo $estilo_fondo; ?>">
            <tr>
                <td style="text-align:center; background-color: #B8B1AF; height:15px;" colspan="315"><b>DIA 7</b></td>
            </tr>
        </thead>
        <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:15%;;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>HORA</b>
            </td>
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>1</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>2</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>3</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>4</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>5</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>6</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>7</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>8</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>9</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
             <b>10</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>11</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>12</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>13</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>14</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>15</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>16</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>17</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>18</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>19</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>20</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>21</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>22</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>23</b>
             </td>
             <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>24</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Conduciendo</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Fuera de servicio</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Serv sin conducir</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       <tr style="margin-top: 80px;background-color: #ffffff;width:50%;">
            <td style="background-color: #ffffff;text-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="1">
                 <b>Descanso</b>
            </td>
            <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="2">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="3">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="4">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="5">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="6">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="7">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="8">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="9">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);"0colspan="04">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="11">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="12">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="13">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="14">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="15">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="16">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="17">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="18">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="19">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="20">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="21">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="22">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="23">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="24">
                <b>|</b>
             </td>
             <td style="background-color: #ffffff;text-align:center;vertical-align:center; font-size:9px;width:03%;border: 1px solid rgba(0, 0, 0, 0.5);" colspan="25">
                <b>|</b>
             </td>
       </tr>
       
</table>

<?php } ?>
			

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
	
	if (!$html || !$html2 ) {
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

	/* '<div style="page-break-before: always;"></div> <div style="all:unset;">' . $html2 . '</div>'. */
	$dompdf = new Dompdf($options);



	$htmlFinal = $html;

	

	if ($req_contrato == '1') {
		$htmlFinal = $htmlFinal.' <div style="page-break-before: always;"></div> <div style="all:unset;">' . $html2 . '</div>';
	}
	 


	$dompdf->loadHtml($htmlFinal);

	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render(false);

	
	
	$canvas = $dompdf->get_canvas();
  
   

	
	
	
	
	
	
	
	

	//Attachment" => false -- Para que no se descargue automaticamente
	$dompdf->stream("".$rem_xfolio.".pdf",["Attachment" => true]);

	$file_path = "C:/xampp/htdocs/XML_".$prefijo."/".$rem_cfdserie."-".$rem_cfdfolio.".pdf";
	file_put_contents($file_path, $dompdf->output());

//  **Forzar la descarga en la computadora del cliente**
	header('Content-Type: application/pdf');
	header("Content-Disposition: attachment; filename=".$rem_cfdserie."-".$rem_cfdfolio.".pdf");
	echo file_get_contents($file_path);
		
	




?>