<?php



ini_set('memory_limit', '2048M');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución


require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi2.php');

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if(!isset($_GET['tipoCarta']) || empty($_GET['tipoCarta'])){
	$tipoCarta = 'Ceros';
}else {
	$tipoCarta = $_GET['tipoCarta'];
}



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
	$f_cancelada = $rowSQL01['cCanceladoT'];
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
	if ($tipoCarta == 'Ceros') {
		$rem_total = 0.00;
		$rem_total_letra = convertir($rem_total, $rem_moneda);
		
	} else {
		$rem_total_letra = convertir($rem_total, $rem_moneda);
		
	}
	
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
	$rem_seRecoge = $rowSQL01['RemitenteSeRecogera'];
	$rem_seEntrega = $rowSQL01['DestinatarioSeEntregara'];
	$rem_NoGuia = $rowSQL01['Poliza'];


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
	  $rutaCedula= '../cfdipro/imagenes/CEDULA_'.$prefijo.'.jpg';
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

 

$nombre_factura= '';
if ($rem_complemento_traslado == 1) {
	$nombre_factura = 'Factura / Complemento CCP';
	} else {
	$nombre_factura= 'Factura';
}

function ceroODatos($valor, $tipoCarta){
	if ($tipoCarta == 'Ceros') {
		$valor = '0.00';
		echo '$ '.number_format((float)$valor,2);
	} else {
		$valor = $valor;
		echo '$ '.number_format((float)$valor,2);
	}
	
	
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
                height: 150px;
				font-family: Helvetica, sans-serif;
				

            }
			
			footer {
                position: fixed; 
                margin: 120px 18px 160px 18px;
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
	<htmlpageheader name="myHeader">
  <div style="margin-top:-10px;">
    <table width="100%" border="0" style="border-collapse:collapse;">
      <tr>
        <!-- Logo -->
        <td style="width:25%; text-align:center;">
          <img src="<?php echo $rutalogo; ?>" width="120" alt="">
        </td>

        <!-- Datos empresa -->
        <td style="width:45%; text-align:center; font-size:11px;">
          <strong style="font-size:13px;"><?php echo strtoupper($RazonSocial); ?></strong><br>
          RFC: <?php echo $RFC; ?><br>
          <?php echo $Calle.' '.$NumeroExterior.(empty($NumeroInterior)?'':' '.$NumeroInterior).', '.$Colonia; ?><br>
          Régimen Fiscal: <?php echo $Regimen; ?><br>
          <strong>Lugar de expedición (C.P.): <?php echo $CodigoPostal; ?></strong>
        </td>

        <!-- Cartela derecha -->
        <td style="width:30%;">
          <table width="100%" cellspacing="0" cellpadding="0" style="border:2px solid #000;">
            <tr>
              <td style="text-align:center; font-size:15px; padding:6px; <?php echo $estilo_fondo; ?>">
                <b>CARTA DE PORTE <br> TRASLADO</b>
              </td>
            </tr>
           
            <tr>
              <td style="padding:6px; border-top:1px solid #000;">
                <table width="100%" style="font-size:11px;">
                  <tr>
                    <td style="text-align:center; font-size:13px; width:45%;border-bottom:1px solid #000;"><b>SERIE - FOLIO </b></td>
				</tr>
				<tr>
					  <td style="text-align:center; font-size:13px; color:#c00;"> <b><?php echo  $rem_xfolio; ?></b></td>

				  </tr>
                 
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
</htmlpageheader>
<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />

<htmlpagefooter name="myFooter">
  <?php
    if (!isset($materialPeligroso)) { $materialPeligroso = false; }
    $rem_indemnizacion = isset($rem_indemnizacion) ? $rem_indemnizacion : 'NO APLICA';
  ?>
  <table width="100%" border="0" style="border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:35%;">
        <span style="display:inline-block; padding:6px 8px; background-color:rgb(218, 168, 5); font-size:12px;">
          INDEMNIZACIÓN: <?php echo strtoupper($rem_indemnizacion); ?>
        </span>
        <br>
		<br>
        <span style="display:inline-block; margin-top:6px; padding:6px 8px; background-color:rgb(218, 168, 5); font-size:12px;">
          Material o Residuo Peligroso: <?php echo ($materialPeligroso ? 'SI' : 'NO'); ?>
        </span>
      </td>

      <td style="width:65%; text-align:right;">
        <?php if (!empty($rutaCedula) && file_exists($rutaCedula)) { ?>
          <!-- Más grande: controla por altura o ancho (elige uno) -->
          <img src="<?php echo $rutaCedula; ?>" 
               alt="Cédula" 
               style="height:200px; margin-top:-24px; padding:6px; background:#fff;">
        <?php } ?>
      </td>
    </tr>
  </table>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />



<!-- ====== CUERPO ====== -->
<main>

  <!-- Tira superior -->
   
  <table width="100%" style="padding-top:10px;" border="0">
	<thead>
		<tr>
			<td style="width:53%;"> SERVICIO PÚBLICO FEDERAL DE CARGA REGULAR Y EXPRESS</td>
			<td style="width:13%;"></td>
			<td style="width:23%; <?php echo $estilo_fondo; ?> text-align:center; font-size:12px; border:1px solid #000;"><b>RÉGIMEN FISCAL</b></td>
			<td style="width:11%; <?php echo $estilo_fondo; ?> text-align:center; font-size:14px; border:1px solid #000;"><b>No. guia</b></td>
		</tr>
	</thead>
	<tbody>

		<tr>
			<td style="width:53%; <?php echo $estilo_fondo; ?> text-align:center; font-size:11px; border:1px solid #000;"><b>LUGAR DE EXPEDICIÓN</b></td>
			<td style="width:22%; <?php echo $estilo_fondo; ?> text-align:center; font-size:11px; border:1px solid #000;"><b>FECHA DE EXPEDICIÓN</b></td>
			<td style="width:23%; border:1px solid #000; text-align:center; font-size:12px;"><b><?php echo $Regimen; ?></td>
			<td style="width:20%; text-align:center; font-size:12px; border:1px solid #000;"><?php  echo $rem_NoGuia ?></td>
		</tr>
    <tr>
		<td style="text-align:center; font-size:10px; border:1px solid #000;"><?php echo $Ciudad.', '.$Estado.', '.$CodigoPostal; ?></td>
		<td style="text-align:center; font-size:10px; border:1px solid #000;"><?php echo date("d-m-Y H:i:s", strtotime($rem_creado_t)); ?></td>
		<td style="width:22%; text-align:right; font-size:11px;"><b>INSTRUCCIONES:</b></td>
		<td style="text-align:left; font-size:10px;"><?php echo nl2br($rem_Instrucciones); ?></td>
    </tr>
	<tr>
		<td style= "border:1px solid #000;" colspan = '2'>
			<b>CLIENTE: </b> <?php echo $cliente_nombre; ?><br>
			<b>RFC: </b> <?php echo $cliente_rfc; ?><br>
			<?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?><br>COL: <?php echo $cliente_colonia; ?><br>
			C.P. <?php echo $cliente_cp; ?>, <?php echo $cliente_municipio.', '.$cliente_estado; ?>
		</td>
		<td> <b style = "background-color:rgb(218, 168, 5); font-size:12px; vertical-align:left;">FECHA PROBABLE DE ENTREGA: </b><br> <br><br>
			<b>________________________</b>

		</td>
		<td style="width:22%; text-align:center; font-size:11px;border:1px solid #000;"><b>FECHA Y HORA DE CARGA</b><br> <br><?php echo !empty($rem_citacarga_t)?date("d-m-Y H:i:s", strtotime($rem_citacarga_t)):""; ?></td>
		<td style="width:22%; text-align:center; font-size:11px;border:1px solid #000;"><b>FECHA Y HORA DE DESCARGA</b><br><br><?php echo !empty($rem_destinatario_citacarga)?date("d-m-Y H:i:s", strtotime($rem_destinatario_citacarga)):""; ?></td>
	</tr>
	<tr>

	</tr>
</tbody>
  </table>

  <!-- Cliente -->
 

  <!-- Origen / Destino + Instrucciones -->
  <table width="100%" border="1" style="border-collapse:collapse; margin-top:6px;">
    <tr>
      <td style="width:50%; <?php echo $estilo_fondo; ?> font-size:12px;"><b>ORIGEN</b></td>
      <td style="width:50%; <?php echo $estilo_fondo; ?> font-size:12px;"><b>DESTINO</b></td>
    </tr>
    <tr>
      <td style="font-size:11px;">
        <b>SE RECOGERÁ EN:</b><?php echo $rem_seRecoge ?><br>
        <?php echo $rem_remitente; ?><br>
        RFC: <?php echo $rem_remitente_rfc; ?><br>
        <?php echo $rem_remitente_calle.' No.'.$rem_remitente_numext; ?>,
        Col. <?php echo $remitente_colonia_nombre; ?>,
        <?php echo $remitente_municipio_nombre; ?>,
        <?php echo $remitente_estado_nombre; ?>, C.P. <?php echo $rem_remitente_cp; ?>
      </td>
      <td style="font-size:11px;">
        <b>SE ENTREGARÁ EN:</b><?php echo $rem_seEntrega ?><br>
        <?php echo $rem_destinatario; ?><br>
        RFC: <?php echo $rem_destinatario_rfc; ?><br>
        <?php echo $rem_destinatario_calle.' No.'.$rem_destinatario_numext; ?>,
        Col. <?php echo $destinatario_colonia_nombre; ?>,
        <?php echo $destinatario_municipio_nombre; ?>,
        <?php echo $destinatario_estado_nombre; ?>, C.P. <?php echo $rem_destinatario_cp; ?>
      </td>
    </tr>
  </table>

  <!-- Barra valor / UDM -->
  <table width="100%" border="0" style=" margin-top:6px;">
    <tr>
      <td style="width:30%; background-color:rgb(218, 168, 5); font-size:12px; border:1px solid #000;"><b>VALOR DECLARADO: NO APLICA</b></td>
      <td style="width:30%; background-color:rgb(218, 168, 5); font-size:12px; border:1px solid #000;"><b>UNIDAD DE MEDIDA: FLETE</b></td>
	  <td>VOLUMEN</td>
	  <td>CONCEPTO</td>
	  <td>IMPORTE</td>
    </tr>
  </table>

  <!-- TABLA PARTIDAS (izquierda) + COLUMNA DERECHA (conceptos) -->
  <!-- CONTENEDOR DE 2 COLUMNAS: IZQ = PARTIDAS, DER = CONCEPTOS -->
<table width="100%" border="0" style="border-collapse:collapse; margin-top:6px;">
  <tr>
    <!-- COLUMNA IZQUIERDA (PARTIDAS) -->
    <td style="width:72%; vertical-align:top;">

      <table width="100%" border="1" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th style="<?php echo $estilo_fondo; ?> font-size:11px; text-align:center; width:15%;">CANTIDAD</th>
            <th style="<?php echo $estilo_fondo; ?> font-size:11px; text-align:center; width:65%;">DESCRIPCIÓN</th>
            <th style="<?php echo $estilo_fondo; ?> font-size:11px; text-align:center; width:20%;">PESO EST.</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $resSQL27 = "SELECT 
              a.Cantidad as Cantidad,
              a.Descripcion as fsDescripcion,
              a.Peso as Peso
            FROM {$prefijobd}remisionessub a
            WHERE a.FolioSub_RID =".$id_remision;
          $runSQL27 = mysqli_query($cnx_cfdi2, $resSQL27);
          while($rowSQL27 = mysqli_fetch_array($runSQL27)){
            $fs_cantidad = number_format($rowSQL27['Cantidad'],2);
            $fs_desc     = $rowSQL27['fsDescripcion'];
            $fs_peso_est = $rowSQL27['Peso'];
          ?>
          <tr>
            <td style="text-align:center; font-size:10px;"><?php echo $fs_cantidad; ?></td>
            <td style="font-size:10px;"><?php echo $fs_desc; ?></td>
            <td style="text-align:center; font-size:10px;"><?php echo $fs_peso_est; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td></td>
            <td style="font-size:10px;">
              Folios de viaje: <?php echo $rem_FolioSalida; ?><br>
              Concentrado: <?php echo $rem_Concentrado; ?>
            </td>
            <td></td>
          </tr>
        </tbody>
      </table>

    </td>

    <!-- COLUMNA DERECHA (CONCEPTOS + TOTALES + RECIBÍ) -->
    <td style="width:28%; vertical-align:top; padding-left:6px;">

      <!-- Conceptos -->
      <table width="100%" border="1" style="border-collapse:collapse; font-size:12px; page-break-inside: avoid;">
        <tr><td>Flete</td><td style="text-align:right;"><?php ceroODatos($rem_yflete, $tipoCarta); ?></td></tr>
        <tr><td>Seguro</td><td style="text-align:right;"><?php ceroODatos($rem_yseguro, $tipoCarta); ?></td></tr>
        <tr><td>Carga</td><td style="text-align:right;"><?php ceroODatos($rem_ycarga, $tipoCarta); ?></td></tr>
        <tr><td>Descarga</td><td style="text-align:right;"><?php ceroODatos($rem_ydescraga, $tipoCarta); ?></td></tr>
        <tr><td>Recolección</td><td style="text-align:right;"><?php ceroODatos($rem_yrecoleccion, $tipoCarta); ?></td></tr>
        <tr><td>Repartos</td><td style="text-align:right;"><?php ceroODatos($rem_yrepartos, $tipoCarta); ?></td></tr>
        <tr><td>Demoras</td><td style="text-align:right;"><?php ceroODatos($rem_ydemoras, $tipoCarta); ?></td></tr>
        <tr><td>Autopistas</td><td style="text-align:right;"><?php ceroODatos($rem_yautopistas, $tipoCarta); ?></td></tr>
        <tr><td>Otros</td><td style="text-align:right;"><?php ceroODatos($rem_yotros, $tipoCarta); ?></td></tr>
      </table>

      <!-- Totales -->
      <table width="100%" border="1" style="border-collapse:collapse; margin-top:8px; font-size:12px; page-break-inside: avoid;">
        <tr><td style="<?php echo $estilo_fondo; ?>">SUBTOTAL</td><td style="text-align:right;"><?php ceroODatos($rem_zSubTotal, $tipoCarta); ?></td></tr>
        <tr><td style="<?php echo $estilo_fondo; ?>">I.V.A. 16%</td><td style="text-align:right;"><?php ceroODatos($rem_zIVA, $tipoCarta); ?></td></tr>
        <tr><td style="<?php echo $estilo_fondo; ?>">RETENCIÓN 4%</td><td style="text-align:right;"><?php ceroODatos($rem_zRet, $tipoCarta); ?></td></tr>
        <tr><td style="<?php echo $estilo_fondo; ?>">TOTAL</td><td style="text-align:right;"><?php ceroODatos($rem_zTotal, $tipoCarta); ?></td></tr>
      </table>
	  <table width="100%"  border="0" style="border-collapse:collapse; font-size:12px; page-break-inside: avoid;">
	  <tr>
		  <td style="height:90px;  border:1px solid #000;">IMPUESTO RETENIDO DE
								  CONFORMIDAD CON LA LEY DEL
								  IMPUESTO AL VALOR
								  AGREGADO
								  CONTRIBUYENTE DEL REGIMEN
								  SIMPLIFICADO
		  </td>
	  </tr>
	  
	</table>
    </td>
  </tr>
</table>


  <!-- Importe con letra + Unidad/Remolques/Operadores -->
  <table width="68%" border="1" style="border-collapse:collapse; margin-top:-30px;">
    <tr>
      <td colspan="3" style="text-align:center; font-size:12px;"><b>*** Importe con letra (<?php echo $rem_total_letra; ?>) ***</b></td>
    </tr>
    <tr>
      <td style="width:33%; font-size:11px;">
        <b>Operador:</b> <?php echo $operador_nombre; ?><br>
        <b>No. Licencia:</b> <?php echo $operador_licencia; ?>
      </td>
	  <td style="width:33%; font-size:11px;">
		<b>FORMA DE PAGO
		</b>
	  </td>
      <td style="width:33%; font-size:11px;">
        <?php if ($rem_operador_id2 > 0) { ?>
          <b>Operador 2:</b> <?php echo $operador_nombre_2; ?><br>
          <b>No. Licencia OP2:</b> <?php echo $operador_licencia_2; ?>
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td style="font-size:11px;">
        <b>Unidad:</b> <?php echo $unidad_nombre; ?> &nbsp;&nbsp;&nbsp;
        <b>Placas:</b> <?php echo $unidad_placas; ?>
      </td>
	  <td>
		PAGO EN UNA SOLA EXHIBICION
	  </td>
      <td style="font-size:11px;">
        <b>Remolque A:</b> <?php echo $remolque_nombre; ?>
        <?php if ($rem_remolque2_id > 0) { ?> &nbsp;&nbsp;<b>Remolque B:</b> <?php echo $remolque2_nombre; ?><?php } ?><br>
        <b>Placas:</b> <?php echo $remolque_placas; ?>
        <?php if ($rem_remolque2_id > 0) { ?> &nbsp;&nbsp;<b>Placas B:</b> <?php echo $remolque2_placas; ?><?php } ?>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11px; <?php echo $estilo_fondo; ?>" colspan = "3">
        <b>ESTA MERCANCIA VIAJE POR CUENTA Y RIESGO DEL REMITENTE</b>

      </td>
      
    </tr>
  </table>
 



 
</main>

<?php if ($req_bitacora != 0) { 
               ?>
				<pagebreak />	
            <htmlpageheader name="myHeader4">
			<div style = "padding-top: -20px;height:130px; padding-bottom:-100px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> width="100px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-family: Helvetica; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
						</td>
						<td style="text-align:center; width:30%; font-family: Helvetica; font-size: 10px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-family: Helvetica; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Bitacora</b></td>
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
			
				<sethtmlpageheader name="myHeader4" value="on" show-this-page="all" />
			 <div style="padding-top:-30px;">
				<table style="font-family: Helvetica; font-size:11px; margin-top:-30px;"width= "100%">
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
          
          <?php 
          $diasBitacora = [
             'Dia 1',
             'Dia 2',
             'Dia 3',
             'Dia 4',
             'Dia 5',
             'Dia 6',
             'Dia 7'
          ];

          $conceptos = [
            'HORA',
            'CONDUCIENDO',
            'FUERA DE SERVICIO',
            'SERV SIN CONDUCIR',
            'DESCANSO',
            'REVISION DE SELLOS'
          ];

             $imprimirTitulo = true;
             
             foreach ($diasBitacora as $dia ) { ?>
           
           <table style="margin:0; border-collapse: collapse; width:100%; border: 1px solid black;">
   <thead style="height:12px;<?php echo $estilo_fondo; ?>">
      <tr>
         <?php if ($imprimirTitulo) { ?>
            <td style="text-align:center; <?php echo $estilo_fondo; ?> font-family: Helvetica; font-size:12px; border: 1px solid black;" colspan="25">
               BITACORA HORAS DE VIAJE
            </td>
         </tr>
         <?php $imprimirTitulo = false; } ?>
         <tr>
            <td style="text-align:center; <?php echo $estilo_fondo; ?> font-family: Helvetica; font-size:12px; border: 1px solid black;" colspan="25">
               <b><?php echo $dia; ?></b>
            </td>
         </tr>
   </thead>

   <?php foreach ($conceptos as $concepto) { ?>
      <tr style="background-color: #ffffff;">
         <!-- Celda del concepto -->
         <td style="background-color: #ffffff; text-align:center; font-family: Helvetica; font-size:9px; width:13%; border: 1px solid black;">
            <b><?php echo $concepto ?></b>
         </td>

         <!-- Celdas dinámicas -->
         <?php for ($i=1; $i <= 24; $i++) { ?>
            <td style="background-color: #ffffff; text-align:center; vertical-align:center; font-family: Helvetica; font-size:9px; width:3%; border: 1px solid black;">
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
          
      
      if ($req_contrato >= 1 ) { ?>
					 <pagebreak resetpagenum="1" pagenumstyle="1" suppress="off" />
					
			
						<htmlpageheader name="myHeader2" >
						<h3 style="background-color: #ffffff; height :200px;"><b style="background-color: #ffffff; ">EL CONTRATO ACTUALIZADO DE PRESTACIÓN DE SERVICIOS, QUE AMPARA LA “CARTA DE
										PORTE O COMPROBANTE PARA AMPARAR EL TRANSPORTE DE MERCANCÍAS”
										</b></h3>
										<p style="font-family: Helvetica; font-size: 11px;background-color: #ffffff;height:32px; margin-top:-7px;"> Condiciones de prestación de servicios que ampara la CARTA DE PORTE O COMPROBANTE PARA EL TRANSPORTE DE MERCANCÍAS</p>
						</htmlpageheader>
						<htmlpagefooter  name="myFooter2" style="background-color: #ffffff; height:180px;">
						<table border="0" style="margin-bottom: 0px;border-collapse: collapse;" width="100%">
								<tr style="margin-top: 100px;background-color: #ffffff;width:50%;">
									<td style="background-color: #ffffff;text-align:left; font-family: Helvetica; font-size:12px;margin-right:0px;width:50%;"colspan="1">
										 <b>SÉPTIMA .- </b>Si la carga no fuere retirada dentro de los 30 días
										hábiles siguientes a aquél en que hubiere sido puesta a disposición
										del consignatario, el "Transportista" podrá solicitar la venta en
										subasta pública con arreglo a lo que dispone el Código de
										Comercio. <br><br>
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
									<td style="background-color: #ffffff;text-align:left; font-family: Helvetica; font-size:12px;width:50%;" colspan="2">
										<p><b>DÉCIMA QUINTA .-</b>Para el caso de que el “Remitente” o
											“Expedidor” contrate carro por entero, este aceptará la
											responsabilidad solidaria para con el "Transportista" mediante la
											figura de la corresponsabilidad que contempla el artículo 10 del
											Reglamento Sobre el Peso, Dimensiones y Capacidad de los
											Vehículos de Autotransporte que Transitan en los Caminos y
											Puentes de Jurisdicción Federal por lo que el “Remitente” o
											“Expedidor” queda obligado a verificar que la carga y el vehículo
											que la transporta, cumplan con el peso y dimensiones máximas
											establecidos en la NOM-012-SCT-2-2014</p> <br><br>
										<p style="background-color: #ffffff;">
											Para el caso de incumplimiento e inobservancia a las disposiciones
											que regulan el peso y dimensiones, por parte del “Remitente” o
											“Expedidor”, este será corresponsable de las infracciones y multas
											que la Secretaría de Comunicaciones y Transportes y la Policía
											Federal impongan al "Transportista", por cargar las unidades con
											exceso de peso. </p>
						
									</td>
						
								</tr>
						</table>
						<table width="100%" style="font-family: Helvetica; font-size: 8pt;">
								<tr>
									<td width="33%">Versión del comprobante: 4.0</td>
									<td width="33%" align="right"><?php if ($f_complemento_traslado >= 1) { ?>Complemento Carta Porte Versión 3.1<?php } ?> </td>
									
								</tr>
							</table>
						</htmlpagefooter>
						<sethtmlpageheader name="myHeader2" value="on" show-this-page="1" />
							<div >
							<table style="margin-top: 0;border-collapse: collapse;position:fixed;top:80px;background-color: #ffffff;" width="100%">
								
								<tr >
									<td style="text-align:left; font-family: Helvetica; font-size:12px;width:50%;"colspan="1">
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
										"Expedidor" deberá entregar al "Transportista" los documentos que
										las leyes y reglamentos exijan para llevar a cabo el servicio, en
										caso de no cumplirse con estos requisitos el "Transportista" está
										obligado a rehusar el transporte de las mercancías.</p><br>
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
									<td style="text-align:left; font-family: Helvetica; font-size:12px;width:50%;" colspan="2">
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

$nombre_pdf = $prefijo." - ".$rem_xfolio;

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
$mpdf->Output($file_path, 'I');
if($tipoArchivo ==='dwld'){
header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"{$nombre_pdf}.pdf\"");
readfile($file_path); 
}
exit;


?>