<?php  
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución



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
//echo $Multi;
/* 
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
	$PermisoSCTsys = $rowSQL0['PermisoSCT'];
	$TipoPermisoSCTsys= $rowSQL0['TipoPermisoSCT'];
	$codLocalidad = '';

} */

//Buscar datos de la Factura - CAMBIAR POR PARAMETRO EL ID
$resSQL01 = " SELECT cfdserie,
cfdfolio,
XFolio,
Creado,
Moneda,
zSubtotal,
zImpuesto,
zRetenido,
zTotal,
usocfdi33_RID,
metodopago33_RID,
formapago33_RID,
CargoACliente_RID,
RemitenteLocalidad2_RID,
CodigoOrigen,
Remitente,
RemitenteRFC,
RemitenteCalle,
RemitenteNumExt,
RemitenteColonia_RID,
RemitenteMunicipio_RID,
RemitenteEstado_RID,
RemitenteCodigoPostal,
RemitentePais,
RemitenteNumRegIdTrib,
CitaCarga,
RemitenteTelefono,
DestinatarioLocalidad2_RID,
CodigoDestino,
Destinatario,
ClaveUnidadPeso_RID,
DestinatarioRFC,
DestinatarioCalle,
DestinatarioNumExt,
DestinatarioColonia_RID,
DestinatarioMunicipio_RID,
DestinatarioEstado_RID,
DestinatarioCodigoPostal,
DestinatarioPais,
DestinatarioNumRegIdTrib,
DestinatarioCitaCarga,
DestinatarioTelefono,
Comentarios,
cfdnocertificado,
cfdiuuid,
cfdinoCertificadoSAT,
cfdfchhra,
cfdifechaTimbrado,
cfdsellodigital,
cfdiselloSAT,
cfdiselloCadenaOriginal,
ConfigAutotranporte_RID,
TipoViaje,
Unidad_RID,
TipoCambio,
cCanceladoT,
Aseguradora,
Poliza,
uRemolqueA_RID,
uRemolqueB_RID,
DistanciaRecorrida,
IdCCP,
Operador_RID,
xPesoTotal,
ComplementoTraslado,
cfdicbbarchivo,
cfdicbbArchivo,
idCCP,
RemitenteSeRecogera,
DestinatarioSeEntregara,
FleteTipo,
RemitenteContacto,
DestinatarioContacto,
Documentador,
yFlete,
ySeguro,
yCarga,
yDescarga,
yRecoleccion,
yRepartos,
yDemoras,
yAutopistas,
yOtros,
zSubtotal,
zImpuesto,
zRetenido,
zTotal,
RemisionOperador,
Ruta_RID,
Oficina_RID
 FROM ".$prefijobd."remisiones WHERE id=".$id_factura;
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);

while($rowSQL01 = mysqli_fetch_array($runSQL01)){
	
	$f_cfdserie = $rowSQL01['cfdserie'];
	$f_cfdfolio = $rowSQL01['cfdfolio'];
	$f_xfolio = $rowSQL01['XFolio'];
	$f_creado_t = $rowSQL01['Creado'];
	$f_ticket1 = $rowSQL01['Ticket1'];
	$f_ticket2 = $rowSQL01['Ticket2'];
	$f_creado = date("d-m-Y H:i:s", strtotime($f_creado_t));
	$f_ticket = null;
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
	$f_id_cliente = $rowSQL01['CargoACliente_RID'];
	$f_remitente_localidad_id = $rowSQL01['RemitenteLocalidad2_RID'];
	$f_codigoorigen = $rowSQL01['CodigoOrigen'];
	$f_remitente = $rowSQL01['Remitente'];
	$f_remitente_contacto = $rowSQL01['RemitenteContacto'];
	$f_destinatario_contacto = $rowSQL01['DestinatarioContacto'];
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
	$fClaveUnidadPesos = $rowSQL01['ClaveUnidadPeso_RID'];
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
	$f_indicaciones = $rowSQL01['Indicaciones'];
	$f_cp_instrucciones = null;
	$f_cfdnocertificado = $rowSQL01['cfdnocertificado'];
	$f_cfdiuuid = $rowSQL01['cfdiuuid'];
	$dias_credito = $rowSQL01 ['DiasCredito'];
	$vence_factura = date("d-m-Y", strtotime($rowSQL01 ['Vence']));
	$f_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$f_cfdffcchh = $rowSQL01['cfdfchhra'];
	$f_cfdifechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
	$f_cfdsellodigital = (empty($rowSQL01['cfdsellodigital'])) ? 'hR6GJIRtqz1HYA8TzDoR9K1qJ9w8/LJItDZKTGgfGwZSrRdCB/9zLcBBE41+sWEjOyBY26cSCOWOJdKsqAQdaF6EWUAvCGQs+gqulGHnEe+7cu
		DJKvSio06I5OLz/RHWBTz6rChQVW61jt2gcypk4ZzlT+jbg14cxIuNCOiA3spIYwuOxdG+wgBiX4rBz+WAHOFZXIazIgtxhx551TCADljvSEfu
		sfcO7daVWzHKQwqwskN7nc2nBU62K65O5805hUzDH3WDva/2/r6RX55PYThck42KfrMAcd+KvfhWKcryXdKRx47IJXHMhVoDLbZN8osJxSDscK
		aCI0u2rDdpMA==' : $rowSQL01['cfdsellodigital'] ; 
	$f_cfdiselloSAT = (empty($rowSQL01['cfdiselloSAT'])) ? 'AVfPLcS1eGnbBrXu5tfJxFGx9tQ1BH/8ADy4Er+Uwl97CfA/Le3cANp7ekHr8BuR6XiXF624qDNXDP11DkXU57UJfmLXEQ+hKkdWoudcN/EKlI
		v3ETcGHyv6X4Wo30lOh/Di3G5DKRjwECrNBmUvaFtR3KDdjwHiDfRJWK06jg0IBOZ0CtfvTw9Q0/YcjPVEM/FzXIjj1Iw2J/D5mDQ96y4AnBFe
		Sl1z7tKmA/qlIKkU+cptheTjDIRcv8vxoK2E8PQjztHakS7VzqFchXRJpgSSo/X0X+wX4UaKMHm8oKVzMgBUQNGr1hORaSYZYSgYcy0fAJ7bOB
		CB7EPDTYdJPg==' : $rowSQL01['cfdiselloSAT'];
	$f_cfdiselloCadenaOriginal = (empty($rowSQL01['cfdiselloCadenaOriginal'])) ? '||1.1|5AD8EEA5-2DE4-40C5-9768-BA77ED6E7483|'.$f_citacarga_t.'||FCG840618N51|AVfPLcS1eGnbBrXu5tfJxFGx9tQ1BH/8AD
		y4Er+Uwl97CfA/Le3cANp7ekHr8BuR6XiXF624qDNXDP11DkXU57UJfmLXEQ+hKkdWoudcN/EKlIv3ETcGHyv6X4Wo30lOh/Di3G5DKRjwECrN
		BmUvaFtR3KDdjwHiDfRJWK06jg0IBOZ0CtfvTw9Q0/YcjPVEM/FzXIjj1Iw2J/D5mDQ96y4AnBFeSl1z7tKmA/qlIKkU+cptheTjDIRcv8vxoK
		2E8PQjztHakS7VzqFchXRJpgSSo/X0X+wX4UaKMHm8oKVzMgBUQNGr1hORaSYZYSgYcy0fAJ7bOBCB7EPDTYdJPg==|00001000000516053874||' : $rowSQL01['cfdiselloCadenaOriginal'];
	$f_configautotransporte_id = $rowSQL01['ConfigAutotranporte_RID'];
	$f_tipo_viaje = $rowSQL01['TipoViaje'];
	$f_pesobrutovehicular = null ;
	$f_documentador = $rowSQL01['Documentador'];
	$f_unidad_id= $rowSQL01['Unidad_RID'];
	$f_tipocambio = $rowSQL01['TipoCambio'];
	$f_cancelada= $rowSQL01['cCanceladoT'];
	$f_ISR = null;
	$f_aseguradora = $rowSQL01['Aseguradora'];
	$f_poliza = $rowSQL01['Poliza'];
	
	
	$f_remolque_id= $rowSQL01['uRemolqueA_RID'];
	$f_remolque2_id= $rowSQL01['uRemolqueB_RID'];
	$f_DistanciaRecorrida = $rowSQL01['DistanciaRecorrida'];
	$f_permisionario_id= null;

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
 	
	$f_IDCCP= $rowSQL01['IdCCP'];
	$f_operador_id= $rowSQL01['Operador_RID'];
	

	$f_pesototal_t= $rowSQL01['xPesoTotal'];
	$f_pesototal = number_format($f_pesototal_t, $numDecimales);
	$f_complemento_traslado= $rowSQL01['ComplementoTraslado'];
	
	if (isset($rowSQL01['cfdicbbarchivo'])){
		$f_qrFsencilla = $rowSQL01['cfdicbbarchivo'];
	} else {
		$f_qrFsencilla = $rowSQL01['cfdicbbArchivo'];
	}
	
	if (isset($rowSQL01['idCCP'])){
		$f_idCCP = $rowSQL01['idCCP'];
	} else {
		$f_idCCP = $rowSQL01['IdCCP'];
	}
	$f_total_letra = convertir($f_total, $f_moneda);
	$f_rutaId = $rowSQL01['Ruta_RID'];
	$f_oficinaId = $rowSQL01['Oficina_RID'];	
	$f_seRecoge = $rowSQL01['RemitenteSeRecogera'];
	$f_seEntrega = $rowSQL01['DestinatarioSeEntregara'];
	$fleteTipo = $rowSQL01['FleteTipo'];
	$valorDeclarado = ((!isset($rowSQL01['ValorDeclarado'])) || (empty($rowSQL01['ValorDeclarado']))) ? '0.00' : $rowSQL01['ValorDeclarado'] ;
	$poliza = ((!isset($rowSQL01['Poliza'])) || (empty($rowSQL01['Poliza']))) ? 'N/A' : $rowSQL01['Poliza'];
	$base = ((!isset($rowSQL01['base'])) || (empty($rowSSQL01['base']))) ? '0' : $rowSQL01['base'];
	$altura = ((!isset($rowSQL01['altura'])) || (empty($rowSSQL01['altura']))) ? '0' : $rowSQL01['altura'];
	$profundidad = ((!isset($rowSQL01['profundidad'])) || (empty($rowSSQL01['profundidad']))) ? '0' : $rowSQL01['profundidad'];
	
	
}

//medidas para la tabla de embalajes que esta estructura se mete a la funcion que imprime el header de los emabaljes
$medidas =[
	'base'=>$base,
	'altura'=>$altura,
	'profundidad'=>$profundidad

];
//var_dump ($medidas);

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


//Concat de numero exterior para que aparezca o no el # 
if (!empty($NumeroExterior)) {
	$NumeroExterior = '# '.$NumeroExterior;
}else {
	$NumeroExterior = '';
}

if (!empty($NumeroInterior)) {
	$NumeroInterior = 'int. '.$NumeroInterior;
}else {
	$NumeroInterior = '';
}

//domicilio restante
if (!empty($Colonia || $Estado || $Ciudad)) {
	$domicilioRestante = 'Col. '.$Colonia.', </br>'.$Ciudad.', '.$Estado.', CP: '.$CodigoPostal;
} else {
	$domicilioRestante = '';
}

//Buscar CFDI Relacionado
$resSQL022 = "SELECT COUNT(ID) as total FROM ".$prefijobd."remisionesUUIDRelacionadoSub WHERE FolioSub_RID=".$id_factura;
$runSQL022 = mysqli_query( $cnx_cfdi2 ,$resSQL022);
while($rowSQL022 = mysqli_fetch_array($runSQL022)){
	$tmp_cfdirel = $rowSQL022['total']; 
}

if($tmp_cfdirel > 0){
	$resSQL02 = "SELECT TipoRelacion, cfdiuuidRelacionado  FROM ".$prefijobd."remisionesUUIDRelacionadoSub WHERE FolioSub=".$id_factura;
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
	$resSQL03 = "SELECT a.*, IFNULL(b.Estado, '') as Estado, IFNULL(c.NombreAsentamiento, '') as Colonia, 
	IFNULL(d.Descripcion, '') as Municipio, IFNULL(e.Descripcion, '') as Localidad FROM {$prefijobd}clientes a 
	left join {$prefijobd}estados b 
	On a.Estado_RID = b.ID left join {$prefijobd}c_colonia c 
	On a.c_Colonia_RID=c.ID left join {$prefijobd}c_municipio d 
	on a.c_Municipio_RID=d.ID left join {$prefijobd}c_localidad e 
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
		$cliente_telefono = $rowSQL03['Telefono'];
		$cliente_colonia_id = $rowSQL03['c_Colonia_RID'];
		$cliente_rfc = $rowSQL03['RFC'];
		$clienteDiasCredito = $rowSQL03['DiasCredito'];
		$cliente_municipio_id = $rowSQL03['c_Municipio_RID'];
		//$cliente_estado_id = $rowSQL03['Estado_RID'];
		$cliente_cp = $rowSQL03['CodigoPostal'];
		$cliente_comentarios = $rowSQL03['Comentarios'];
		$cliente_colonia = $rowSQL03['Colonia'];
		$cliente_municipio = $rowSQL03['Municipio'];
		$cliente_estado = $rowSQL03['Estado'];
		$cliente_ciudad = $rowSQL03['Localidad'];
		if ($regPorParametro) {
			if (!isset($rowSQL03['cRegimenFiscal_RID'])) {
				$Regimen_prev = $rowSQL03['RegimenFiscal_RID'] = 1; // Default value if not set
			}else{
				$Regimen_prev = $rowSQL03['cRegimenFiscal_RID'];
			}			
			$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
			$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
			$rowSQL007= mysqli_fetch_assoc($runSQL007);
			if ($rowSQL007){
				$cliente_Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
			}
		}else{
			$cliente_Regimen = $rowSQL03['RegimenFiscal'].", ".$rowSQL03['RegimenFiscalDescripcion'];
		}
		
	}
	
	
}

//clave unidad peso consulta
$resSQLp04 = "SELECT ClaveUnidad FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ID=".$fClaveUnidadPesos;
$resSQLp04 = mysqli_query($cnx_cfdi2, $resSQLp04);
while($rowSQLp04 = mysqli_fetch_array($resSQLp04)){
	$f_claveunidadpeso = $rowSQLp04['ClaveUnidad'];
}

//transporte internacional condicion
if ($f_tipo_viaje === 'NACIONAL'){
	$f_transporte_internacional = 'NO';
	} else {
		$f_transporte_internacional = 'SI';
	
		
	}
//busca version CCP
$f_versionCCP = 3.1;

//DAtos de oficina
$resSQLOF = "SELECT a.Ciudad, b.Abreviacion FROM {$prefijobd}oficinas AS a 
			 LEFT JOIN {$prefijobd}estados  AS b ON a.Estado_RID = b.ID
			 WHERE a.ID = {$f_oficinaId}";
$runSQLOF = mysqli_query($cnx_cfdi2, $resSQLOF);
while ($rowSQLOF = mysqli_fetch_array($runSQLOF)) {
	$oficinaCiudad = $rowSQLOF['Ciudad'];
	$oficinaEstado = $rowSQLOF['Abreviacion'];
}



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

//buscamos Ruta 

$resSQLR = "SELECT Ruta FROM {$prefijobd}rutas where ID = {$f_rutaId}";
$runSQLR = mysqli_query($cnx_cfdi2, $resSQLR);
while ($rowSQLR = mysqli_fetch_array($runSQLR)) {
	$nombreRuta = $rowSQLR['Ruta'];
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
				u.PermisoSCT,
				u.TipoPermisoSCT,
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
		$PermisoSCT = isset($rowSQL21['PermisoSCT']) ? $rowSQL21['PermisoSCT'] : $PermisoSCTsys;
		$TipoPermisoSCT= isset($rowSQL21['TipoPermisoSCT']) ? $rowSQL21['TipoPermisoSCT'] :  $TipoPermisoSCTsys;
        $unidad_aseguradora_nombre = isset($rowSQL21['unidad_aseguradora_nombre']) ? $rowSQL21['unidad_aseguradora_nombre'] : '';
        $configautotransporte_descripcion = isset($rowSQL21['configuracionautotransporte_descripcion']) ? $rowSQL21['configuracionautotransporte_descripcion'] : '';
        $configautotransporte_clavenomenclatura = isset($rowSQL21['configautotransporte_clavenomenclatura']) ? $rowSQL21['configautotransporte_clavenomenclatura'] : '';
    }else{
		$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
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
				FROM {$prefijobd}remimisionesssub a 
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
	$figura_transporte1 = null;
	$figura_transporte2 = null;
	
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

$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

$parametro_contrato = 923;
$resSQL923 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_contrato";
$runSQL923 = mysqli_query($cnx_cfdi2, $resSQL923);
	 
while ($rowSQL923 = mysqli_fetch_array($runSQL923)) {
	$param= $rowSQL923['id2'];
	$req_contrato = $rowSQL923 ['VLOGI'];
	
}

$parametro_bitacora = 935;
$resSQL935 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 = $parametro_bitacora";
$runSQL935 = mysqli_query($cnx_cfdi2, $resSQL935);
	 
while ($rowSQL935 = mysqli_fetch_array($runSQL935)) {
	$param= $rowSQL935['id2'];
	$req_bitacora = $rowSQL935 ['VLOGI'];
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
$rutaISO =  '../cfdipro/imagenes/ISOconcentradora.jpg';

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

//bar code 1 en header

function imprimeBcode39($codigoBarra, $xml_dir, $dir, $f_xfolio){

	$codigoBarra = (!isset($codigoBarra) || empty($codigoBarra)) ? '123456' : $codigoBarra ;

    $codigoFactura = $codigoBarra;

    $dir = "C:/xampp/htdocs/{$xml_dir}/";
    if(!file_exists($dir)){
        mkdir($dir, 0777, true);
    }

    // Evitar espacios en el nombre del archivo
    $XFolio = 'BC-'.$f_xfolio;
    $fileName = $dir.$XFolio.$codigoBarra.'.svg';

	
   // (Code128 en formato SVG)
    $Url= "https://barcode.tec-it.com/barcode.ashx?data={$codigoFactura}&code=Code39&filetype=SVG&showlabel=false";


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
	$dir = "C:/xampp/htdocs/{$xml_dir}/";
	if(!file_exists($dir)){
		mkdir($dir, 0777, true);
	}
	$fileName = $dir.'BC-'.$f_xfolio.$codigoBarra.'.svg';

	
	
	if (file_put_contents($fileName, $imageContent) === false) {
		die(" No se pudo guardar en $fileName");
	}

	echo $fileName;

}
	






function recuadroArriba ($campo_original, $campo_alias, $inicio_del_campo, $lleva_recuadro_arriba, $prefijobd, $id_factura, $cnx_cfdi2)
{
	if ($lleva_recuadro_arriba >= '1') {
		$resSQL = "SELECT {$campo_original} as {$campo_alias} FROM {$prefijobd}remisiones where ID = {$id_factura}";
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


	$contenido2 = 'https://verificacfdi.facturaelectronica.sat.gob.mx/verificaccp/default.aspx?IdCCP='.$f_IDCCP.'&FechaOrig='.$f_citacargaq.'&FechaTimb='.$f_cfdifechaTimbrado ;



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
function splitLongText($text, $maxLength = 110) {
    
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

$datos_cliente = [
	'ID' => $f_id_cliente,
	'Cliente' => $cliente_nombre,
	'RFC' => $cliente_rfc,
	'Domicilio' => $cliente_calle,
	'Estado' => $cliente_estado,
	'CP' => $cliente_cp,
	'Régimen Fiscal' => $cliente_Regimen,
	'Telefono' => $cliente_telefono



];


function imprimirTablaCliente($datos_cliente ) {

	// Filtrar elementos nulos (por condiciones) y reindexar


	// Imprimir la tabla en filas de 3 columnas
	echo '<table border="0" style="margin-top:20px; border-collapse: collapse; padding-top:13px; " width="100%">
			
				<tr>
					<td colspan="1" style="font-size:13px; text-align:left;padding-top:-20px;">'
						.$datos_cliente['ID'].' '.$datos_cliente['Cliente'].'<br>'. 
						 $datos_cliente['Domicilio'].' '.$datos_cliente['Estado'].'C.P.: '.$datos_cliente['CP'].
						 '<br>R.F.C.:'.$datos_cliente['RFC'].' <br>TEL.: '.$datos_cliente['Telefono'].'

					</td>
					
				</tr>
			
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
   'remitente_contacto' => $f_remitente_contacto,
   'destinatario_contacto' => $f_destinatario_contacto,
   'remitente_telefono' => $f_remitente_telefono,
   'destinatario_telefono' => $f_destinatario_telefono,
   'poliza_seguro' => $poliza,
   'valor_declarado' => $valorDeclarado,
   'fleteTipo' => $fleteTipo,
   //'diasCredito' => $clienteDiasCredito

];

function domicilios($datos_domicilios){
	
			echo'<div>
			<table border="0" style="table-layout: fixed; width:100%; border-collapse: collapse; margin:0;">
					<tr>
						<td style="text-align:left; width:100%; font-size: 9px; padding-bottom: 0px; vertical-align:top;"><b>'
							.$datos_domicilios['fleteTipo'].
						'</b></td>

					</tr>
					<tr>
						<td style="text-align:left; width:100%; font-size: 9px; padding-bottom: 0px; vertical-align:top;">
							<b>REMITENTE</b>
						</td>
						
					</tr>
				
					<tr>
						<td style="text-align:left; width:100%; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Razón Social: '.$datos_domicilios ['f_remitente'].'<br>
							RFC: '.$datos_domicilios ['f_remitente_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_remitente_calle'].' No.'.$datos_domicilios ['f_remitente_numext'].'
							'.'Col.'.$datos_domicilios ['remitente_colonia_nombre'].',<br> '.$datos_domicilios ['remitente_municipio_nombre'].','.'
							'.$datos_domicilios ['remitente_estado_nombre'].' C.P.'.$datos_domicilios ['f_remitente_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_remitente_pais'].'<br>
							Telefono: '.$datos_domicilios ['remitente_telefono'].'<br>
							</td>
						</tr>
					<tr>
						
						<td style="text-align:left; width:100%; font-size: 9px; padding-bottom: 0px; vertical-align:top;">
							<b>DESTINATARIO</b>
						</td>
					</tr>
						<tr>
							<td style="text-align:left; width:100%; font-size: 10px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
								Razón Social: '.$datos_domicilios ['f_destinatario'].'<br>
								RFC: '.$datos_domicilios ['f_destinatario_rfc'].'<br>
								Domicilio: '.$datos_domicilios ['f_destinatario_calle'].' No.'.$datos_domicilios ['f_destinatario_numext'].'
								'.'Col.'.$datos_domicilios ['destinatario_colonia_nombre'].',<br> '.$datos_domicilios ['destinatario_municipio_nombre'].','.'
								'.$datos_domicilios ['destinatario_estado_nombre'].' C.P.'.$datos_domicilios ['f_destinatario_cp'].'<br>
								Residencia Fiscal: '.$datos_domicilios ['f_destinatario_pais'].'<br>
								Telefono: '.$datos_domicilios ['destinatario_telefono'].'<br>
							

							</td>
					</tr>							
					
			</table>
		</div>';
}

//Bloque consolidad activar con unidos

$esConsolodidado = 0;

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
$nombre_encabezado = ($f_cfdfolio > 0) ? $f_cfdserie . "-" . $f_cfdfolio : $prefijo . " - " . $f_xfolio;

$fechaprobableEntrega = new DateTime($f_creado_t);
$fechaprobableEntrega->modify('+7 days');
$fechaprobableEntrega = $fechaprobableEntrega->format('d-m-Y');

$diaPagoCalculado = new DateTime($f_creado_t);
$diaPagoCalculado->modify('+'.(int)$dias_credito.' days');
$diaPagoCalculado = $diaPagoCalculado->format('d-m-Y');

function ceroODatos($valor){
	
		$valor = $valor;
		echo '$ '.number_format((float)$valor,2);
	
	
	
}

ob_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
    margin: 18mm 4mm 14mm 4mm;
}
body {
    font-family: helvetica;
    font-size: 10px;
}
table {
    border-collapse: collapse;
}
td, th {
    padding: 2px;
}
</style>
</head>

<body>

<!-- HEADER -->
<htmlpageheader name="myHeader">
<table width="100%" style="font-size:8px; line-height:10px;">
<tr>
<td align="center">
<img src="<?php echo $rutalogo; ?>" style="width:105px;">
</td>
</tr>
<tr>
<td align="center">
<img src="<?php imprimeBcode39($f_xfolio, $xml_dir, $dir, $f_xfolio);?>" height="50">
</td>
</tr>
<tr>
<td align="center">
<strong><?php echo $RazonSocial ?></strong><br>
RFC: <?php echo $RFC ?><br>
<?php echo $Calle.' '.$NumeroExterior ?><br>
C.P. <?php echo $CodigoPostal ?><br> 
RUTA: <?php echo $nombreRuta; ?><br>
</td>
</tr>
</table>
</htmlpageheader>

<sethtmlpageheader name="myHeader" value="on" show-this-page="all"/>

<div style="height:40mm;"></div>

<table width="100%" style="font-size:9px;">
<tr>
<td>

<b>FLETE POR COBRAR AL REGRESO</b><br><br>

<b>REMITENTE</b><br>
Razón Social: <?php echo $datos_domicilios['f_remitente']; ?><br>
RFC: <?php echo $datos_domicilios['f_remitente_rfc']; ?><br>
Domicilio: 
<?php 
echo $datos_domicilios['f_remitente_calle'].' No.'.$datos_domicilios['f_remitente_numext'].' Col. '.$datos_domicilios['remitente_colonia_nombre'].', '.
     $datos_domicilios['remitente_municipio_nombre'].', '.$datos_domicilios['remitente_estado_nombre'].' C.P. '.$datos_domicilios['f_remitente_cp']; 
?><br>
Residencia Fiscal: <?php echo $datos_domicilios['f_remitente_pais']; ?><br>
Teléfono: <?php echo $datos_domicilios['remitente_telefono']; ?><br><br>

<b>DESTINATARIO</b><br>
Razón Social: <?php echo $datos_domicilios['f_destinatario']; ?><br>
RFC: <?php echo $datos_domicilios['f_destinatario_rfc']; ?><br>
Domicilio: 
<?php 
echo $datos_domicilios['f_destinatario_calle'].' No.'.$datos_domicilios['f_destinatario_numext'].' Col. '.$datos_domicilios['destinatario_colonia_nombre'].', '.
     $datos_domicilios['destinatario_municipio_nombre'].', '.$datos_domicilios['destinatario_estado_nombre'].' C.P. '.$datos_domicilios['f_destinatario_cp']; 
?><br>
Residencia Fiscal: <?php echo $datos_domicilios['f_destinatario_pais']; ?><br>
Teléfono: <?php echo $datos_domicilios['destinatario_telefono']; ?><br>

</td>
</tr>
</table>

<br>

<!-- CONTENIDO -->
<table width="100%" border="1">
<thead>
<tr>
    <th width="25%">Cantidad</th>
    <th width="25%">Embalaje</th>
    <th width="50%">Detalles</th>
</tr>
</thead>
<tbody>

<!-- FOOTER -->
<htmlpagefooter name="myFooter">
<table width="100%" style="font-size:9px;">
<tr>
<td>Pedido: <?php echo $f_ticket;?></td>
<!--<td align="right">
<img src="<?php //imprimeBcode39($f_ticket, $xml_dir, $dir, $f_xfolio);?> " height="28">
</td> -->
</tr>
<tr>
<td><b>Se Entregará:</b> <?php echo $datos_domicilios['f_seEntrega'];?></td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpagefooter name="myFooter" value="on" show-this-page="all"/>

<?php
$resSQL27 = "SELECT
rs.Cantidad, rs.Embalaje, c.Descripcion as Descripcion2
FROM {$prefijobd}remisionessub rs
LEFT JOIN {$prefijobd}c_claveprodservcp c on rs.ClaveProdServCP_RID = c.id
WHERE rs.FolioSub_RID =".$id_factura;

$runSQL27 = mysqli_query($cnx_cfdi2, $resSQL27);

$contador = 0;

while($row = mysqli_fetch_array($runSQL27)){
    if($contador > 0 && $contador % 15 == 0){
        echo '</tbody></table><pagebreak />';
        echo '<table width="100%" border="1"><thead><tr>
                <th width="25%">Cantidad</th>
                <th width="25%">Embalaje</th>
                <th width="50%">Detalles</th>
              </tr></thead><tbody>';
    }
?>
<tr>
    <td align="center"><?php echo number_format($row['Cantidad'],2); ?></td>
    <td align="center"><?php echo $row['Embalaje']; ?></td>
    <td><?php echo $row['Descripcion2']; ?></td>
</tr>
<?php
$contador++;
}
?>
</tbody>
</table>

<br>

<?php //domicilios($datos_domicilios); ?>

</body>
</html>

<?php
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();

$customSize = array(101.6, 152.4);
$mgl = 4; 
$mgr = 4; 
$mgt = 36; 
$mgb = 10; 
$mgh = 0; 
$mgf = 0;

$mpdf = new mPDF('utf-8', $customSize, 0, '', $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, 'P');

$mpdf->SetDisplayMode('fullpage');
$mpdf->shrink_tables_to_fit = 1;
$mpdf->use_kwt = true;

if (!empty($f_cancelada)) {
    $mpdf->SetWatermarkText('CANCELADO', 0.1);
    $mpdf->showWatermarkText = true;
}

$mpdf->WriteHTML($html);

$nombre_pdf = ($f_cfdfolio > 0) ? $f_cfdserie . "-" . $f_cfdfolio : $f_xfolio;
$folder_path = "C:/xampp/htdocs/{$xml_dir}";
if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);
$file_path = "{$folder_path}/{$nombre_pdf}_Etiqueta.pdf";

if (file_exists($file_path)) unlink($file_path);
$mpdf->Output($file_path, 'F');

if($tipoArchivo === 'dwld'){
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"{$nombre_pdf}_Etiqueta.pdf\"");
    readfile($file_path);
}
exit;