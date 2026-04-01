<?php  
ini_set('memory_limit', '1024M');
set_time_limit(300);
require 'phpqrcode/qrlib.php';

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_real_escape_string($_GET["prefijodb"]);

$id_factura = $_GET["id"];
//$id_factura = 4183703;


//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

echo $prefijobd;

require_once('cnx_cfdi2.php');
require_once('cnx_cfdi.php');

//require_once('lib_mpdf/pdf/mpdf.php');

//mysql_select_db($database_cfdi, $cnx_cfdi);
mysqli_select_db($cnx_cfdi2,$database_cfdi);
mysqli_query("SET NAMES 'utf8'");

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

////////////////Agregar nombre del Mes

////////////////// Funcion Numeros a letra

function unidad($numuero){
	switch ($numuero)
	{
		case 9:
		{
			$numu = "NUEVE";
			break;
		}
		case 8:
		{
			$numu = "OCHO";
			break;
		}
		case 7:
		{
			$numu = "SIETE";
			break;
		}
		case 6:
		{
			$numu = "SEIS";
			break;
		}
		case 5:
		{
			$numu = "CINCO";
			break;
		}
		case 4:
		{
			$numu = "CUATRO";
			break;
		}
		case 3:
		{
			$numu = "TRES";
			break;
		}
		case 2:
		{
			$numu = "DOS";
			break;
		}
		case 1:
		{
			$numu = "UNO";
			break;
		}
		case 0:
		{
			$numu = "";
			break;
		}
	}
	return $numu;
}
 
function decena($numdero){
 
		if ($numdero >= 90 && $numdero <= 99)
		{
			$numd = "NOVENTA ";
			if ($numdero > 90)
				$numd = $numd."Y ".(unidad($numdero - 90));
		}
		else if ($numdero >= 80 && $numdero <= 89)
		{
			$numd = "OCHENTA ";
			if ($numdero > 80)
				$numd = $numd."Y ".(unidad($numdero - 80));
		}
		else if ($numdero >= 70 && $numdero <= 79)
		{
			$numd = "SETENTA ";
			if ($numdero > 70)
				$numd = $numd."Y ".(unidad($numdero - 70));
		}
		else if ($numdero >= 60 && $numdero <= 69)
		{
			$numd = "SESENTA ";
			if ($numdero > 60)
				$numd = $numd."Y ".(unidad($numdero - 60));
		}
		else if ($numdero >= 50 && $numdero <= 59)
		{
			$numd = "CINCUENTA ";
			if ($numdero > 50)
				$numd = $numd."Y ".(unidad($numdero - 50));
		}
		else if ($numdero >= 40 && $numdero <= 49)
		{
			$numd = "CUARENTA ";
			if ($numdero > 40)
				$numd = $numd."Y ".(unidad($numdero - 40));
		}
		else if ($numdero >= 30 && $numdero <= 39)
		{
			$numd = "TREINTA ";
			if ($numdero > 30)
				$numd = $numd."Y ".(unidad($numdero - 30));
		}
		else if ($numdero >= 20 && $numdero <= 29)
		{
			if ($numdero == 20)
				$numd = "VEINTE ";
			else
				$numd = "VEINTI".(unidad($numdero - 20));
		}
		else if ($numdero >= 10 && $numdero <= 19)
		{
			switch ($numdero){
			case 10:
			{
				$numd = "DIEZ ";
				break;
			}
			case 11:
			{
				$numd = "ONCE ";
				break;
			}
			case 12:
			{
				$numd = "DOCE ";
				break;
			}
			case 13:
			{
				$numd = "TRECE ";
				break;
			}
			case 14:
			{
				$numd = "CATORCE ";
				break;
			}
			case 15:
			{
				$numd = "QUINCE ";
				break;
			}
			case 16:
			{
				$numd = "DIECISEIS ";
				break;
			}
			case 17:
			{
				$numd = "DIECISIETE ";
				break;
			}
			case 18:
			{
				$numd = "DIECIOCHO ";
				break;
			}
			case 19:
			{
				$numd = "DIECINUEVE ";
				break;
			}
			}
		}
		else
			$numd = unidad($numdero);
	return $numd;
}
 
	function centena($numc){
		if ($numc >= 100)
		{
			if ($numc >= 900 && $numc <= 999)
			{
				$numce = "NOVECIENTOS ";
				if ($numc > 900)
					$numce = $numce.(decena($numc - 900));
			}
			else if ($numc >= 800 && $numc <= 899)
			{
				$numce = "OCHOCIENTOS ";
				if ($numc > 800)
					$numce = $numce.(decena($numc - 800));
			}
			else if ($numc >= 700 && $numc <= 799)
			{
				$numce = "SETECIENTOS ";
				if ($numc > 700)
					$numce = $numce.(decena($numc - 700));
			}
			else if ($numc >= 600 && $numc <= 699)
			{
				$numce = "SEISCIENTOS ";
				if ($numc > 600)
					$numce = $numce.(decena($numc - 600));
			}
			else if ($numc >= 500 && $numc <= 599)
			{
				$numce = "QUINIENTOS ";
				if ($numc > 500)
					$numce = $numce.(decena($numc - 500));
			}
			else if ($numc >= 400 && $numc <= 499)
			{
				$numce = "CUATROCIENTOS ";
				if ($numc > 400)
					$numce = $numce.(decena($numc - 400));
			}
			else if ($numc >= 300 && $numc <= 399)
			{
				$numce = "TRESCIENTOS ";
				if ($numc > 300)
					$numce = $numce.(decena($numc - 300));
			}
			else if ($numc >= 200 && $numc <= 299)
			{
				$numce = "DOSCIENTOS ";
				if ($numc > 200)
					$numce = $numce.(decena($numc - 200));
			}
			else if ($numc >= 100 && $numc <= 199)
			{
				if ($numc == 100)
					$numce = "CIEN ";
				else
					$numce = "CIENTO ".(decena($numc - 100));
			}
		}
		else
			$numce = decena($numc);
 
		return $numce;
}
 
	function miles($nummero){
		if ($nummero >= 1000 && $nummero < 2000){
			$numm = "MIL ".(centena($nummero%1000));
		}
		if ($nummero >= 2000 && $nummero <10000){
			$numm = unidad(Floor($nummero/1000))." MIL ".(centena($nummero%1000));
		}
		if ($nummero < 1000)
			$numm = centena($nummero);
 
		return $numm;
	}
 
	function decmiles($numdmero){
		if ($numdmero == 10000)
			$numde = "DIEZ MIL";
		if ($numdmero > 10000 && $numdmero <20000){
			$numde = decena(Floor($numdmero/1000))."MIL ".(centena($numdmero%1000));
		}
		if ($numdmero >= 20000 && $numdmero <100000){
			$numde = decena(Floor($numdmero/1000))." MIL ".(miles($numdmero%1000));
		}
		if ($numdmero < 10000)
			$numde = miles($numdmero);
 
		return $numde;
	}
 
	function cienmiles($numcmero){
		if ($numcmero == 100000)
			$num_letracm = "CIEN MIL";
		if ($numcmero >= 100000 && $numcmero <1000000){
			$num_letracm = centena(Floor($numcmero/1000))." MIL ".(centena($numcmero%1000));
		}
		if ($numcmero < 100000)
			$num_letracm = decmiles($numcmero);
		return $num_letracm;
	}
 
	function millon($nummiero){
		if ($nummiero >= 1000000 && $nummiero <2000000){
			$num_letramm = "UN MILLON ".(cienmiles($nummiero%1000000));
		}
		if ($nummiero >= 2000000 && $nummiero <10000000){
			$num_letramm = unidad(Floor($nummiero/1000000))." MILLONES ".(cienmiles($nummiero%1000000));
		}
		if ($nummiero < 1000000)
			$num_letramm = cienmiles($nummiero);
 
		return $num_letramm;
	}
 
	function decmillon($numerodm){
		if ($numerodm == 10000000)
			$num_letradmm = "DIEZ MILLONES";
		if ($numerodm > 10000000 && $numerodm <20000000){
			$num_letradmm = decena(Floor($numerodm/1000000))."MILLONES ".(cienmiles($numerodm%1000000));
		}
		if ($numerodm >= 20000000 && $numerodm <100000000){
			$num_letradmm = decena(Floor($numerodm/1000000))." MILLONES ".(millon($numerodm%1000000));
		}
		if ($numerodm < 10000000)
			$num_letradmm = millon($numerodm);
 
		return $num_letradmm;
	}
 
	function cienmillon($numcmeros){
		if ($numcmeros == 100000000)
			$num_letracms = "CIEN MILLONES";
		if ($numcmeros >= 100000000 && $numcmeros <1000000000){
			$num_letracms = centena(Floor($numcmeros/1000000))." MILLONES ".(millon($numcmeros%1000000));
		}
		if ($numcmeros < 100000000)
			$num_letracms = decmillon($numcmeros);
		return $num_letracms;
	}
 
	function milmillon($nummierod){
		if ($nummierod >= 1000000000 && $nummierod <2000000000){
			$num_letrammd = "MIL ".(cienmillon($nummierod%1000000000));
		}
		if ($nummierod >= 2000000000 && $nummierod <10000000000){
			$num_letrammd = unidad(Floor($nummierod/1000000000))." MIL ".(cienmillon($nummierod%1000000000));
		}
		if ($nummierod < 1000000000)
			$num_letrammd = cienmillon($nummierod);
 
		return $num_letrammd;
	}
 
 
function convertir($numero){
		    $num = str_replace(",","",$numero);
		    $num = number_format($num,2,'.','');
		    $cents = substr($num,strlen($num)-2,strlen($num)-1);
			 
			$tempnum=explode('.',$numero);
			if($tempnum[1] == ''){
				$tempnum[1] = '00';
			} 
			$numf = milmillon($tempnum[0]);
			return $numf." PESOS ".$tempnum[1]."/100 M.N.";

}


//////////////////// FIN Funcion Numeros a letra


//Seleccionar Mes letra
  switch ("$mes_logs") {
    case '01':
        $mes2 = "Enero";
      break;
    case '02':
        $mes2 = "Febrero";
      break;
    case '03':
        $mes2 = "Marzo";
      break;
    case '04':
        $mes2 = "Abril";
      break;
    case '05':
        $mes2 = "Mayo";
      break;
    case '06':
        $mes2 = "Junio";
      break;
    case '07':
        $mes2 = "Julio";
      break;
    case '08':
        $mes2 = "Agosto";
      break;
    case '09':
        $mes2 = "Septiembre";
      break;
    case '10':
        $mes2 = "Octubre";
      break;
    case '11':
        $mes2 = "Noviembre";
      break;
    case '12':
        $mes2 = "Diciembre";
      break;
    
  } //Fin switch

$fecha = $dia_logs." de ".$mes2." de ". $anio_logs;
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;


//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysqli_query($cnx_cfdi2, $resSQL0);
while($rowSQL0 = mysqli_fetch_assoc($runSQL0)){
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
	$Regimen = $rowSQL0['Regimen'];
	$PermisoSCT = $rowSQL0['PermisoSCT'];
	$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
	$codLocalidad = '';
}

//Buscar datos de la Factura - CAMBIAR POR PARAMETRO EL ID
$resSQL01 = "SELECT * FROM ".$prefijobd."factura WHERE id=".$id_factura;
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
while($rowSQL01 = mysqli_fetch_assoc($runSQL01)){
	$f_cfdserie = $rowSQL01['cfdserie'];
	$f_cfdfolio = $rowSQL01['cfdfolio'];
	$f_xfolio = $rowSQL01['XFolio'];
	$f_creado_t = $rowSQL01['Creado'];
	$f_creado = date("d-m-Y H:i:s", strtotime($f_creado_t));
	$f_ticket = $rowSQL01['Ticket'];
	//$f_total_letra = $rowSQL01['TotalLetra'];
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
	$f_remolque_id= $rowSQL01['Remolque_RID'];
	$f_operador_id= $rowSQL01['Operador_RID'];
	//$f_totalcantidad_t= $rowSQL01['TotalCantidad'];
	//$f_totalcantidad = number_format($f_totalcantidad_t,2); 
	$f_totalcantidad = 0;
	$f_pesototal_t= $rowSQL01['xPesoTotal'];
	$f_pesototal = number_format($f_pesototal_t,2);
	$f_complemento_traslado= $rowSQL01['ComplementoTraslado'];
	$f_lleva_repartos= $rowSQL01['LlevaRepartos'];
	
	$f_total_letra = convertir($f_total2);
	
}
	
	
	//Buscar CFDI Relacionado
	$resSQL022 = "SELECT COUNT(ID) as total FROM ".$prefijobd."FacturaUUIDRelacionadoSub WHERE FolioSub_RID=".$id_factura;
	$runSQL022 = mysqli_query($cnx_cfdi2, $resSQL022);
	while($rowSQL022 = mysqli_fetch_assoc($runSQL022)){
		$tmp_cfdirel = $rowSQL022['total']; 
	}
	
	if($tmp_cfdirel > 0){
		$resSQL02 = "SELECT * FROM ".$prefijobd."FacturaUUIDRelacionadoSub WHERE FolioSub=".$id_factura;
		$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);
		while($rowSQL02 = mysqli_fetch_assoc($runSQL02)){
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
	$cliente_ciudad = '';
	$cliente_colonia = '';
	$cliente_rfc = '';
	$cliente_municipio = '';
	$cliente_estado = '';
	$cliente_cp = '';
	
} else {
	$resSQL03 = "SELECT * FROM ".$prefijobd."clientes WHERE id=".$f_id_cliente;
	$runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
	while($rowSQL03 = mysqli_fetch_assoc($runSQL03)){
		$cliente_nombre = $rowSQL03['RazonSocial'];
		$cliente_calle = $rowSQL03['Calle'];
		$cliente_numext = $rowSQL03['NumeroExterior'];
		$cliente_numint = $rowSQL03['NumeroInterior'];
		$cliente_ciudad = $rowSQL03['Ciudad'];
		$cliente_colonia_id = $rowSQL03['c_Colonia_RID'];
		$cliente_rfc = $rowSQL03['RFC'];
		$cliente_municipio_id = $rowSQL03['c_Municipio_RID'];
		$cliente_estado_id = $rowSQL03['Estado_RID'];
		$cliente_cp = $rowSQL03['CodigoPostal'];
		
		
	}
	
	//Buscar Colonia
	if($cliente_colonia_id > 0){
		$resSQL04 = "SELECT * FROM ".$prefijobd."c_colonia	WHERE id=".$cliente_colonia_id;
		$runSQL04 = mysqli_query($cnx_cfdi2, $resSQL04);
		while($rowSQL04 = mysqli_fetch_assoc($runSQL04)){
			$cliente_colonia = $rowSQL04['NombreAsentamiento'];
		}
	} else {
		$cliente_colonia = '';
	}
	
	//Buscar Municipio
	if($cliente_municipio_id > 0){
		$resSQL05 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE id=".$cliente_municipio_id;
		$runSQL05 = mysqli_query($cnx_cfdi2, $resSQL05);
		while($rowSQL05 = mysqli_fetch_assoc($runSQL05)){
			$cliente_municipio = $rowSQL05['Descripcion'];
		}
	} else {
		$cliente_municipio = '';
	}
	
	//Buscar Estado
	if($cliente_estado_id > 0){
		$resSQL06 = "SELECT * FROM ".$prefijobd."Estados WHERE id=".$cliente_estado_id;
		$runSQL06 = mysqli_query($cnx_cfdi2, $resSQL06);
		while($rowSQL06 = mysqli_fetch_assoc($runSQL06)){
			$cliente_estado = $rowSQL06['Estado'];
		}
	} else {
		$cliente_estado = '';
	}
	
}

//Buscar usocfdi
$f_usocfdi  = '';
$f_usocfdi_dsc = '';
if($f_usocfdi33_id > 0){
	$resSQL07 = "SELECT * FROM ".$prefijobd."tablageneral WHERE id=".$f_usocfdi33_id;
	$runSQL07 = mysqli_query($cnx_cfdi2, $resSQL07);
	while($rowSQL07 = mysqli_fetch_assoc($runSQL07)){
		$f_usocfdi_dsc = $rowSQL07['Descripcion'];
		$f_usocfdi = $rowSQL07['ID2'];
	}
}

//Buscar metodopago
$f_metodopago  = '';
$f_metodopago_dsc = '';
if($f_metodopago33_id > 0){
	$resSQL08 = "SELECT * FROM ".$prefijobd."tablageneral WHERE id=".$f_metodopago33_id;
	$runSQL08 = mysqli_query($cnx_cfdi2, $resSQL08);
	while($rowSQL08 = mysqli_fetch_assoc($runSQL08)){
		$f_metodopago_dsc = $rowSQL08['Descripcion'];
		$f_metodopago = $rowSQL08['ID2'];
	}
}

//Buscar formapago
$f_formapago  = '';
$f_formapago_dsc = '';
if($f_formapago33_id > 0){
	$resSQL09 = "SELECT * FROM ".$prefijobd."tablageneral WHERE id=".$f_formapago33_id;
	$runSQL09 = mysqli_query($cnx_cfdi2, $resSQL09);
	while($rowSQL09 = mysqli_fetch_assoc($runSQL09)){
		$f_formapago_dsc = $rowSQL09['Descripcion'];
		$f_formapago = $rowSQL09['ID2'];
	}
}


//Buscar Remitente Localidad
if(empty($f_remitente_localidad_id)){
	$remitente_localidad_nombre = '';
} else {
	$resSQL10 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE id=".$f_remitente_localidad_id ;
	$runSQL10= mysqli_query($cnx_cfdi2, $resSQL10);
	while($rowSQL10 = mysqli_fetch_assoc($runSQL10)){
		$remitente_localidad_nombre = $rowSQL10['Descripcion'];
	}
}

//Buscar Remitente Colonia
if(empty($f_remitente_colonia_id)){
	$remitente_colonia_nombre = '';
} else {
	$resSQL11 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE id=".$f_remitente_colonia_id ;
	$runSQL11 = mysqli_query($cnx_cfdi2, $resSQL11);
	while($rowSQL11 = mysqli_fetch_assoc($runSQL11)){
		$remitente_colonia_nombre = $rowSQL11['NombreAsentamiento'];
	}
}

//Buscar Remitente Municipio
if(empty($f_remitente_municipio_id)){ 
	$remitente_municipio_nombre = '';
} else {
	$resSQL12= "SELECT * FROM ".$prefijobd."c_Municipio WHERE id=".$f_remitente_municipio_id ;
	$runSQL12 = mysqli_query($cnx_cfdi2, $resSQL12);
	while($rowSQL12 = mysqli_fetch_assoc($runSQL12)){
		$remitente_municipio_nombre = $rowSQL12['Descripcion'];
	}
}

//Buscar Destinatario Localidad
if(empty($f_destinatario_localidad_id)){ 
	$destinatario_localidad_nombre = '';
} else {
	$resSQL13 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE id=".$f_destinatario_localidad_id ;
	$runSQL13 = mysqli_query($cnx_cfdi2, $resSQL13);
	while($rowSQL13 = mysqli_fetch_assoc($runSQL13)){
		$destinatario_localidad_nombre = $rowSQL13['Descripcion'];
	}
}

//Buscar Destinatario Colonia
if(empty($f_destinatario_colonia_id)){ 
	$destinatario_colonia_nombre = '';
} else {
	$resSQL14 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE id=".$f_destinatario_colonia_id ;
	$runSQL14 = mysqli_query($cnx_cfdi2, $resSQL14);
	while($rowSQL14 = mysqli_fetch_assoc($runSQL14)){
		$destinatario_colonia_nombre = $rowSQL14['NombreAsentamiento'];
	}
}

//Buscar Destinatario Municipio
if(empty($f_destinatario_municipio_id)){
	$destinatario_municipio_nombre = '';
} else {
	$resSQL15 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE id=".$f_destinatario_municipio_id ;
	$runSQL15 = mysqli_query($cnx_cfdi2, $resSQL15);
	while($rowSQL15 = mysqli_fetch_assoc($runSQL15)){
		$destinatario_municipio_nombre = $rowSQL15['Descripcion'];
	}
}

//Buscar Remitente Estado
if(empty($f_remitente_estado_id)){
	$remitente_estado_nombre = '';
} else {
	$resSQL16 = "SELECT * FROM ".$prefijobd."Estados WHERE id=".$f_remitente_estado_id ;
	$runSQL16 = mysqli_query($cnx_cfdi2, $resSQL16);
	while($rowSQL16 = mysqli_fetch_assoc($runSQL16)){
		$remitente_estado_nombre = $rowSQL16['Estado'];
	}
}

//Buscar Destinatario Estado
if(empty($f_destinatario_estado_id)){
	$rdestinatario_estado_nombre = '';
} else {
	$resSQL17 = "SELECT * FROM ".$prefijobd."Estados WHERE id=".$f_destinatario_estado_id ;
	$runSQL17 = mysqli_query($cnx_cfdi2, $resSQL17);
	while($rowSQL17 = mysqli_fetch_assoc($runSQL17)){
		$rdestinatario_estado_nombre = $rowSQL17['Estado'];
	}
}

//Buscar ConfigAutotransporte
if(empty($f_configautotransporte_id)){
	$f_configautotransporte_descripcion = '';
	$f_configautotransporte_clavenomenclatura = '';
} else {
	$resSQL20 = "SELECT * FROM ".$prefijobd."c_ConfigAutotransporte WHERE ID=".$f_configautotransporte_id ;
	$runSQL20 = mysqli_query($cnx_cfdi2, $resSQL20);
	while($rowSQL20 = mysqli_fetch_assoc($runSQL20)){
		$f_configautotransporte_descripcion = $rowSQL20['Descripcion'];
		$f_configautotransporte_clavenomenclatura = $rowSQL20['ClaveNomenclatura'];
	}
}

//Buscar Unidad
if(empty($f_unidad_id)){
	$unidad_nombre = '';
	$unidad_polizano = '';
	$unidad_placas = '';
	$unidad_anio = '';
} else {
	$resSQL21 = "SELECT * FROM ".$prefijobd."Unidades WHERE ID=".$f_unidad_id ;
	$runSQL21 = mysqli_query($cnx_cfdi2, $resSQL21);
	while($rowSQL21 = mysqli_fetch_assoc($runSQL21)){
		$unidad_aseguradora_id = $rowSQL21['AseguradoraUnidad_RID'];
		$unidad_nombre = $rowSQL21['Unidad'];
		$unidad_polizano = $rowSQL21['PolizaNo'];
		$unidad_placas = $rowSQL21['Placas'];
		$unidad_anio = $rowSQL21['Ano'];
		$unidad_configautotransporte_id = $rowSQL21['ConfigAutotranporte_RID'];
	}
	
	if(empty($unidad_aseguradora_id)){
		$unidad_aseguradora_nombre = '';
	} else {
		$resSQL22 = "SELECT * FROM ".$prefijobd."Aseguradoras WHERE ID=".$unidad_aseguradora_id ;
		$runSQL22 = mysqli_query($cnx_cfdi2, $resSQL22);
		while($rowSQL22 = mysqli_fetch_assoc($runSQL22)){
			$unidad_aseguradora_nombre = $rowSQL22['Aseguradora'];
		}
	}
	
	//Buscar ConfigAutotransporte
	if(empty($unidad_configautotransporte_id)){
		$configautotransporte_descripcion = '';
		$configautotransporte_clavenomenclatura = '';
	} else {
		$resSQL25 = "SELECT * FROM ".$prefijobd."c_ConfigAutotransporte WHERE ID=".$unidad_configautotransporte_id ;
		$runSQL25 = mysqli_query($cnx_cfdi2, $resSQL25);
		while($rowSQL25 = mysqli_fetch_assoc($runSQL25)){
			$configautotransporte_descripcion = $rowSQL25['Descripcion'];
			$configautotransporte_clavenomenclatura = $rowSQL25['ClaveNomenclatura'];
		}
	}
	
}

//Buscar Remolque
if(empty($f_remolque_id)){
	$remolque_nombre = '';
	$remolque_placas = '';
	$remolque_anio = '';
	$remolque_subtiporem_id= '';
} else {
	$resSQL23 = "SELECT * FROM ".$prefijobd."Unidades WHERE ID=".$f_remolque_id ;
	$runSQL23 = mysqli_query($cnx_cfdi2, $resSQL23);
	while($rowSQL23 = mysqli_fetch_assoc($runSQL23)){
		$remolque_nombre = $rowSQL23['Unidad'];
		$remolque_placas = $rowSQL23['Placas'];
		$remolque_anio = $rowSQL23['Ano'];
		$remolque_subtiporem_id= $rowSQL23['SubTipoRem_RID'];
	}
	if(empty($remolque_subtiporem_id)){
		$remolque_clave_tipo_remilque = '';
		$remolque_remolque_semiremolque = '';
	} else {
		$resSQL24 = "SELECT * FROM ".$prefijobd."c_SubTipoRem WHERE ID=".$remolque_subtiporem_id ;
		$runSQL24 = mysqli_query($cnx_cfdi2, $resSQL24);
		while($rowSQL24 = mysqli_fetch_assoc($runSQL24)){
			$remolque_clave_tipo_remilque = $rowSQL24['ClaveTipoRemolque'];
			$remolque_remolque_semiremolque = $rowSQL24['RemolqueSemiremolque'];
		}
	}
}

if($f_operador_id > 0){
	//Buscar Operador
	$resSQL26 = "SELECT * FROM ".$prefijobd."Operadores WHERE ID=".$f_operador_id ;
	$runSQL26 = mysqli_query($cnx_cfdi2, $resSQL26);
	while($rowSQL26 = mysqli_fetch_assoc($runSQL26)){
		$operador_tipo_figura = $rowSQL26['TipoFigura'];
		$operador_nombre = $rowSQL26['Operador'];
		$operador_rfc = $rowSQL26['RFC'];
		$operador_licencia = $rowSQL26['LicenciaNo'];
		$operador_residencia_fiscal = $rowSQL26['ResidenciaFiscal'];
		$operador_identidad_tributaria = $rowSQL26['NumRegIdTrib'];
		
	}
} else {
	$operador_tipo_figura = '';
	$operador_nombre = '';
	$operador_rfc = '';
	$operador_licencia = '';
	$operador_residencia_fiscal = '';
	$operador_identidad_tributaria = '';
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
$dir = 'facturas_qr/';

if(!file_exists($dir)){
	mkdir($dir);
}


$filename = $dir.'factura_'.$f_xfolio.'.png';

//Configurar QR
$tamanio = 10;
$level = 'M';
$frameSize = 3;
$contenido = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?re='.$RFC.'&rr='.$cliente_rfc.'&tt='.$total_qr.'&id='.$f_cfdiuuid.'&fe='.$sello_digital_final ;

QRcode::png($contenido, $filename, $level, $tamanio, $frameSize);

//echo '<img src="'.$filename.'" />';



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
            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 170px; 
            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
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
		
		
		
		<title>Factura</title>
	</head>
	<body>
		<header>
			<div>
				<table border="0" style="margin:0; border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:25%;"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/cfdipro/imagenes/logo_ts.png" width="150px" /></td>
						<td style="text-align:center; width:45%; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.'';
							?>
						</td>
						<td style="text-align:center; width:30%; font-size: 10px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-size: 14px;padding: 1;vertical-align: center;background-color: #B8B1AF;"><b>Factura</b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;">I -Ingreso</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-size: 10px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $f_cfdserie.' '.$f_cfdfolio; ?></b></label></td>
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
					<tr>
						<td style="text-align:center; font-size: 10px;padding: 1;vertical-align: center;" colspan="3">
							<b>Lugar de expedición (C.P.): </b><?php echo $CodigoPostal; ?>
						</td>
					</tr>
				</table>
			</div>
		</header>
		
		
		<footer>
			
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
						<td style="text-align:center; width:70%; font-size: 10px;padding-bottom: 0px;" colspan='2'>
							<?php echo $f_comentarios; ?>
						</td>
						<td style="text-align:center; width:30%; font-size: 10px;padding-bottom: 0px;">
							<b>CFDI Relacionado: <?php echo $fr_tiporelacion.' - '.$fr_cfdiuuidRelacionado; ?></b>
						</td>
					</tr>
				</table>
			</div>
		
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
						<td style="text-align:center; width:20%; font-size:8px;padding-bottom: 0px;" rowspan='7'>
							<img src="http://<?php echo $_SERVER['HTTP_HOST'];?>/cfdipro/<?php echo $filename; ?>" width="150px" />
						<?php //echo $f_satNoCSDSAT; ?>
						</td>
						<td style="text-align:center; width:80%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='2'>
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
					<tr style="margin:0; padding:0" >
						<td style="text-align:center; width:80%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='2'>
							<b>Sello digital del CFDI</b>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:80%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;" colspan='2'>
							<?php echo $f_cfdsellodigital; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:center; width:100%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='3'>
							<b>Sello del SAT</b>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:100%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;" colspan='3'>
							<?php echo $f_cfdiselloSAT; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:center; width:100%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='3'>
							<b>Cadena  original del complemento del certificación digital del SAT</b>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:100%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;" colspan='3'>
							<?php echo $f_cfdiselloCadenaOriginal; ?>
						</td>
					</tr>
				</table>
			</div>
		</footer>
		
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-size: 9px;">
							<b>Cliente:</b> <?php echo $cliente_nombre; ?>
						</td>
						<td style="text-align:left; width:5%; font-size: 9px;">
							
						</td>
						<td style="text-align:left; width:40%; font-size: 9px;">
							<b>RFC:</b> <?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;">
							<b>Domicilio:</b> <?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						<td style="text-align:left;font-size: 9px;">
							
						</td>
						<td style="text-align:left; font-size: 9px;">
							<b>Municipio:</b> <?php echo $cliente_municipio; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 9px;">
							<b>Colonia:</b> <?php echo $cliente_colonia; ?>
						</td>
						<td style="text-align:left;font-size: 9px;">
							
						</td>
						<td style="text-align:left; font-size: 9px;">
							<b>Estado:</b> <?php echo $cliente_estado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 9px;" >
							<b>CP:</b> <?php echo $cliente_cp; ?>
						</td>
						<td style="text-align:left;font-size: 9px;">
							
						</td>
						<td style="text-align:left; font-size: 9px;">
							
						</td>
					</tr>
				</table>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='2'>
							<b>Origen - <?php echo $remitente_localidad_nombre;?></b>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='4'>
							<b>Destino - <?php echo $destinatario_localidad_nombre;?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='2'>
							Código Origen: <?php echo $f_codigoorigen; ?><br>
							Razón Social: <?php echo $f_remitente; ?><br>
							RFC: <?php echo $f_remitente_rfc; ?><br>
							Domicilio: <?php echo $f_remitente_calle.' No.'.$f_remitente_numext; ?><br>
							<?php echo 'Col.'.$remitente_colonia_nombre.', '.$remitente_municipio_nombre.','; ?><br>
							<?php echo $remitente_estado_nombre.' C.P.'.$f_remitente_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_remitente_pais; ?><br>
							Identidad Tributaria: <?php echo $f_remitente_numregidtrib; ?><br>
							Fecha de Salida: <?php echo $f_citacarga; ?><br>
							Teléfono: <?php echo $f_remitente_telefono; ?>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='4'>
							Código Destino: <?php echo $f_codigodestino;?><br>
							Razón Social: <?php echo $f_destinatario; ?><br>
							RFC: <?php echo $f_destinatario_rfc; ?><br>
							Domicilio: <?php echo $f_destinatario_calle.' No.'.$f_destinatario_numext; ?><br>
							<?php echo 'Col.'.$destinatario_colonia_nombre.', '.$destinatario_municipio_nombre.','; ?><br>
							<?php echo $destinatario_estado_nombre.' C.P.'.$f_destinatario_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_destinatario_pais; ?><br>
							Identidad Tributaria: <?php echo $f_destinatario_numregidtrib; ?><br>
							Fecha de Llegada: <?php echo $f_destinatario_citacarga; ?><br>
							Teléfono: <?php echo $f_destinatario_telefono; ?>
						</td>
					</tr>
				</table>
			</div>
			<br>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr style="margin:0; padding:0">
							<th style="text-align:center; width:8%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:8%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Clave Unidad SAT</b>
							</th>
							<th style="text-align:center; width:12%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Clave Producto/Servicio</b>
							</th>
							<th style="text-align:center; width:36%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Descripción</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Valor Unitario</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Impuestos</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Retenciones</b>
							</th>
							<th style="text-align:center; width:9%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
								<b>Importe</b>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php 
						$resSQL18 = "SELECT f.Cantidad as cantidad, c.claveunidad33 as claveunidad33, c.prodserv33 as prodserv33, c.prodserv33dsc as prodserv33dsc, c.Concepto as ConceptoPartida, f.Detalle as Detalle, f.PrecioUnitario as PrecioUnitario, f.IVAImporte as IVAImporte, f.RetencionImporte as RetencionImporte, f.Importe as Importe FROM ".$prefijobd."FacturaPartidas f LEFT OUTER JOIN ".$prefijobd."Conceptos c on f.FolioConceptos_RID = c.id WHERE FolioSub_RID=".$id_factura ;
						//echo $resSQL18;
						$runSQL18 = mysql_query($resSQL18, $cnx_cfdi);
						while($rowSQL18 = mysql_fetch_array($runSQL18)){
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
							$fp_Importe = number_format($fp_Importe_t,2); 
							
						
					?>
					<tr>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_cantidad; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_claveunidad33; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_prodserv33.' - '.$fp_prodserv33dsc; ?></td>
						<td style="text-align:left; font-size: 9px;"><?php echo $fp_ConceptoPartida.' - '.$fp_Detalle; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_PrecioUnitario; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_IVAImporte; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_RetencionImporte; ?></td>
						<td style="text-align:center; font-size: 9px;"><?php echo $fp_Importe; ?></td>
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
						<td style="text-align:center; width:70%; font-size: 11px;padding: 1;vertical-align: center;background-color: #B8B1AF;"><b>Total con letra:</b></td>
						<td style="text-align:right; width:20%; font-size: 10px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:right; width:10%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_subtotal; ?></td>
					</tr>
					<tr>
						<td style="text-align:center;font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo $f_total_letra; ?></b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_impuesto; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Uso CFDi:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_retenido; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>Total:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo $f_total; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
					</tr>
				</table>
			</div>
			<!-- FIN Subreporte 1 -->
			
			<div class="page-break"></div>
			
			<?php 
			if($f_complemento_traslado >= 1){
			?>
			
			<!-- Subreporte 2 -->
			<!--<div class="page-break"></div>-->
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='2'>
							<b>Origen - <?php echo $remitente_localidad_nombre;?></b>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan='4'>
							<b>Destino - <?php echo $destinatario_localidad_nombre;?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='2'>
							Código Origen: <?php echo $f_codigoorigen; ?><br>
							Razón Social: <?php echo $f_remitente; ?><br>
							RFC: <?php echo $f_remitente_rfc; ?><br>
							Domicilio: <?php echo $f_remitente_calle.' No.'.$f_remitente_numext; ?><br>
							<?php echo 'Col.'.$remitente_colonia_nombre.', '.$remitente_municipio_nombre.','; ?><br>
							<?php echo $remitente_estado_nombre.' C.P.'.$f_remitente_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_remitente_pais; ?><br>
							Identidad Tributaria: <?php echo $f_remitente_numregidtrib; ?><br>
							Fecha de Salida: <?php echo $f_citacarga; ?><br>
							Teléfono: <?php echo $f_remitente_telefono; ?>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;" colspan='4'>
							Código Destino: <?php echo $f_codigodestino;?><br>
							Razón Social: <?php echo $f_destinatario; ?><br>
							RFC: <?php echo $f_destinatario_rfc; ?><br>
							Domicilio: <?php echo $f_destinatario_calle.' No.'.$f_destinatario_numext; ?><br>
							<?php echo 'Col.'.$destinatario_colonia_nombre.', '.$destinatario_municipio_nombre.','; ?><br>
							<?php echo $destinatario_estado_nombre.' C.P.'.$f_destinatario_cp.','; ?><br>
							Residencia Fiscal: <?php echo $f_destinatario_pais; ?><br>
							Identidad Tributaria: <?php echo $f_destinatario_numregidtrib; ?><br>
							Fecha de Llegada: <?php echo $f_destinatario_citacarga; ?><br>
							Teléfono: <?php echo $f_destinatario_telefono; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;" colspan="4">
							<b>Detalle del Complemento Carta Porte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<b>Medio de Transporte</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Tipo Autotransporte</b>
						</td>
						<td style="text-align:center; width:30%; font-size: 9px;vertical-align:center;">
							<b>Tipo de Transporte</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Vía de Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<?php echo '01 - Autotransporte Federal'; ?>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<?php echo $f_configautotransporte_descripcion; ?>
						</td>
						<td style="text-align:center; width:30%; font-size: 9px;vertical-align:center;">
							<?php echo $f_tipo_viaje; ?>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<?php echo '01'; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;" colspan="7">
							<b>Detalle del Transporte</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:10%; font-size: 9px;vertical-align:center;">
							<b>Permiso SCT:</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Aseguradora</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Número Póliza</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Unidad/Remolque</b>
						</td>
						<td style="text-align:center; width:40%; font-size: 9px;vertical-align:center;">
							<b>Configuración Vehicular/Tipo de Remolque</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Placa</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Año</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 9px;vertical-align:center;">
							<?php echo $PermisoSCT.' - Autotransporte Federal de carga general'; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $unidad_aseguradora_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_polizano; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $configautotransporte_descripcion.' - '.$configautotransporte_clavenomenclatura; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_placas; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $unidad_anio; ?>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 9px;vertical-align:center;">
							<?php echo '<b>Número de Permiso:</b>'; ?>
						</td>
						<td style="text-align:left; font-size: 9px;vertical-align:center;" colspan='2'>
							<?php echo $TipoPermisoSCT; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_clave_tipo_remilque.' - '.$remolque_remolque_semiremolque; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_placas; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $remolque_anio; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:100%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;" colspan="6">
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
							<b>Licencia</b>
						</td>
						<td style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
							<b>Residencia Fiscal</b>
						</td>
						<td style="text-align:center; width:20%; font-size: 9px;vertical-align:center;">
							<b>Identidad Tributaria</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $operador_tipo_figura; ?>
						</td>
						<td style="text-align:center; font-size: 9px;vertical-align:center;">
							<?php echo $operador_nombre; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_rfc; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_licencia; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_residencia_fiscal; ?>
						</td>
						<td style="text-align:center;font-size: 9px;vertical-align:center;">
							<?php echo $operador_identidad_tributaria; ?>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<tr>
						<td style="text-align:center; width:33%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
							<b>Num. Total Mercancías:<?php echo $f_totalcantidad; ?> </b>
						</td>
						<td style="text-align:center; width:34%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
							<b>Detalle Mercancías</b>
						</td>
						<td style="text-align:center; width:33%; font-size: 9px;vertical-align:center;background-color: #B8B1AF;">
							<b>Peso Bruto Total: <?php echo $f_pesototal; ?></b>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr style="page-break-inside: avoid; page-break-after: auto;">
							<th style="text-align:center; width:5%; font-size: 9px;vertical-align:center;">
								<b>Cantidad</b>
							</th>
							<th style="text-align:center; width:10%; font-size: 9px;vertical-align:center;">
								<b>Unidad</b>
							</th>
							<th style="text-align:center; width:50%; font-size: 9px;vertical-align:center;">
								<b>Descripción</b>
							</th>
							<th style="text-align:center; width:15%; font-size: 9px;vertical-align:center;">
								<b>Tipo Material Peligroso</b>
							</th>
							<th style="text-align:center; width:13%; font-size: 9px;vertical-align:center;">
								<b>Embalaje</b>
							</th>
							<th style="text-align:center; width:7%; font-size: 9px;vertical-align:center;">
								<b>Peso kg</b>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$resSQL27 = "SELECT a.Cantidad as Cantidad, b.ClaveUnidad as ClaveUnidad, b.Nombre as Nombre, c.ClaveProducto as ClaveProducto, c.Descripcion as Descripcion2, e.ClaveMaterialPeligroso as ClaveMaterialPeligroso, d.ClaveDesignacion as ClaveDesignacion, d.Descripcion as Descripcion3, a.Peso as Peso, a.UUIDComercioExt as UUIDComercioExt, a.NumeroPedimento as NumeroPedimento, g.Codigo as Codigo, g.Descripcion as Descripcion6 FROM ".$prefijobd."facturassub a LEFT OUTER JOIN ".$prefijobd."c_claveunidadpeso b on a.ClaveUnidadPeso_RID = b.id LEFT OUTER JOIN ".$prefijobd."c_claveprodservcp c on a.ClaveProdServCP_RID = c.id LEFT OUTER JOIN ".$prefijobd."c_tipoembalaje d on a.TipoEmbalaje_RID = d.id LEFT OUTER JOIN ".$prefijobd."c_materialpeligroso e on a.MaterialPeligroso_RID = e.id LEFT OUTER JOIN ".$prefijobd."c_clavestcc f on a.ClaveSTCC_RID = f.id LEFT OUTER JOIN ".$prefijobd."c_fraccionarancelaria g on a.FraccionArancelaria_RID = g.id LEFT OUTER JOIN ".$prefijobd."clientesdestinos h on a.IDDestino_RID = h.id LEFT OUTER JOIN ".$prefijobd."clientesdestinos i on a.IDOrigen_RID = i.id WHERE FolioSub_RID =".$id_factura;
					$runSQL27 = mysql_query($resSQL27, $cnx_cfdi);
					while($rowSQL27 = mysql_fetch_array($runSQL27)){
						$fs_cantidad_t = $rowSQL27['Cantidad'];
						$fs_cantidad = number_format($fs_cantidad_t,2); 
						$fs_clave_unidad = $rowSQL27['ClaveUnidad'];
						$fs_nombre = $rowSQL27['Nombre'];
						$fs_clave_producto = $rowSQL27['ClaveProducto'];
						$fs_decripcion2 = $rowSQL27['Descripcion2'];
						$fs_clave_material_peligroso = $rowSQL27['ClaveMaterialPeligroso'];
						$fs_clave_designacion = $rowSQL27['ClaveDesignacion'];
						$fs_descripcion3 = $rowSQL27['Descripcion3'];
						$fs_peso_t = $rowSQL27['Peso'];
						$fs_peso = number_format($fs_peso_t,2); 
						$fs_uuidcomercioext = $rowSQL27['UUIDComercioExt'];
						$fs_numero_pedimento = $rowSQL27['NumeroPedimento'];
						$fs_codigo = $rowSQL27['Codigo'];
						$fs_descripcion6 = $rowSQL27['Descripcion6'];
						

					?>
						<tr  style="page-break-inside: avoid; page-break-after: auto;">
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_cantidad; ?>
							</td>
							<td style="text-align:center; font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_unidad.' - '.$fs_nombre; ?>
							</td>
							<td style="text-align:left;font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_producto.' - '.$fs_decripcion2.'<br>UUID Com Ext: '.$fs_uuidcomercioext.' Pedimento: '.$fs_numero_pedimento.'<br> Fracción Arancelaria: '.$fs_codigo.' - '.$fs_descripcion6; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
								<?php echo $fs_clave_material_peligroso; ?>
							</td>
							<td style="text-align:center;font-size: 9px;vertical-align:center;">
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
			</div>
			
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-size: 11px;padding: 1;vertical-align: center;background-color: #B8B1AF;"><b>Total con letra:</b></td>
						<td style="text-align:right; width:20%; font-size: 10px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:right; width:10%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:center;font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo ''; ?></b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Uso CFDi:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>Total:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
					</tr>
				</table>
			</div>
			
			<!-- FIN Subreporte 2 -->
			
			<div class="page-break"></div>
			
			<?php
			} 
			
			if($f_lleva_repartos >= 1){
			
			?>
			
			<!-- Subreporte 3 -->
			<!--<div class="page-break"></div>-->
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<?php
						$t2 = 1;
						$resSQL31 = "SELECT a.CodigoOrigen as CodigoOrigen, a.Remitente as Remitente, a.RemitenteRFC as RemitenteRFC, a.RemitenteCalle as RemitenteCalle, a.RemitenteNumExt as RemitenteNumExt, b.NombreAsentamiento as NombreAsentamiento, d.Descripcion as Descripcion, e.Descripcion as Descripcion_1, a.RemitenteCodigoPostal as RemitenteCodigoPostal, a.RemitentePais as RemitentePais, a.RemitenteNumRegIdTrib as RemitenteNumRegIdTrib, a.CitaCarga as CitaCarga, a.RemitenteTelefono as RemitenteTelefono, a.CodigoDestino as CodigoDestino, a.Destinatario as Destinatario, a.DestinatarioRFC as DestinatarioRFC, a.DestinatarioCalle as DestinatarioCalle, a.DestinatarioNumExt as DestinatarioNumExt, f.NombreAsentamiento as NombreAsentamiento_1, h.Descripcion as Descripcion_2, i.Descripcion as Descripcion_3, a.DestinatarioCodigoPostal  as DestinatarioCodigoPostal, a.DestinatarioPais as DestinatarioPais, a.DestinatarioNumRegIdTrib as DestinatarioNumRegIdTrib, a.DestinatarioCitaCarga as DestinatarioCitaCarga, a.DestinatarioTelefono as DestinatarioTelefono, a.DistanciaRecorrida as DistanciaRecorrida FROM ".$prefijobd."facturasrepartos a LEFT OUTER JOIN ".$prefijobd."c_colonia b on a.RemitenteColonia_RID = b.id LEFT OUTER JOIN ".$prefijobd."estados c on a.RemitenteEstado_RID = c.id LEFT OUTER JOIN ".$prefijobd."c_localidad d on a.RemitenteLocalidad2_RID = d.id LEFT OUTER JOIN ".$prefijobd."c_municipio e on a.RemitenteMunicipio_RID = e.id LEFT OUTER JOIN ".$prefijobd."c_colonia f on a.DestinatarioColonia_RID = f.id LEFT OUTER JOIN ".$prefijobd."estados g on a.DestinatarioEstado_RID = g.id LEFT OUTER JOIN ".$prefijobd."c_localidad h on a.DestinatarioLocalidad2_RID = h.id LEFT OUTER JOIN ".$prefijobd."c_municipio i on a.DestinatarioMunicipio_RID = i.id WHERE a.FolioSub_RID =".$id_factura;
						$runSQL31 = mysql_query($resSQL31, $cnx_cfdi);
						while($rowSQL31 = mysql_fetch_array($runSQL31)){
							$fr_CodigoOrigen = $rowSQL31['CodigoOrigen'];
							$fr_Remitente = $rowSQL31['Remitente'];
							$fr_RemitenteRFC = $rowSQL31['RemitenteRFC'];
							$fr_RemitenteCalle = $rowSQL31['RemitenteCalle'];
							$fr_RemitenteNumExt = $rowSQL31['RemitenteNumExt'];
							$fr_NombreAsentamiento = $rowSQL31['NombreAsentamiento'];
							$fr_Descripcion = $rowSQL31['Descripcion'];
							$fr_Descripcion_1 = $rowSQL31['Descripcion_1'];
							$fr_RemitenteCodigoPostal = $rowSQL31['RemitenteCodigoPostal'];
							$fr_RemitentePais = $rowSQL31['RemitentePais'];
							$fr_RemitenteNumRegIdTrib = $rowSQL31['RemitenteNumRegIdTrib'];
							$fr_CitaCarga_t = $rowSQL31['CitaCarga'];
							$fr_CitaCarga = date("d-m-Y H:i:s", strtotime($fr_CitaCarga_t));
							$fr_RemitenteTelefono = $rowSQL31['RemitenteTelefono'];
							$fr_CodigoDestino = $rowSQL31['CodigoDestino'];
							$fr_Destinatario = $rowSQL31['Destinatario'];
							$fr_DestinatarioRFC = $rowSQL31['DestinatarioRFC'];
							$fr_DestinatarioCalle = $rowSQL31['DestinatarioCalle'];
							$fr_DestinatarioNumExt = $rowSQL31['DestinatarioNumExt'];
							$fr_NombreAsentamiento_1 = $rowSQL31['NombreAsentamiento_1'];
							$fr_Descripcion_2 = $rowSQL31['Descripcion_2'];
							$fr_Descripcion_3 = $rowSQL31['Descripcion_3'];
							$fr_DestinatarioCodigoPostal = $rowSQL31['DestinatarioCodigoPostal'];
							$fr_DestinatarioPais = $rowSQL31['DestinatarioPais'];
							$fr_DestinatarioNumRegIdTrib = $rowSQL31['DestinatarioNumRegIdTrib'];
							$fr_DestinatarioCitaCarga_t = $rowSQL31['DestinatarioCitaCarga'];
							$fr_DestinatarioCitaCarga = date("d-m-Y H:i:s", strtotime($fr_DestinatarioCitaCarga_t));
							$fr_DestinatarioTelefono = $rowSQL31['DestinatarioTelefono'];
							$fr_DistanciaRecorrida_t = $rowSQL31['DistanciaRecorrida'];
							$fr_DistanciaRecorrida = number_format($fr_DistanciaRecorrida_t,2); 
							
					?>
					<tr colspan='2'>
						<td style="text-align:center; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;" colspan="2">
							<b>REPARTO <?php echo $t2; ?></b>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;">
							<b>ORIGEN</b>
						</td>
						<td style="text-align:left; width:50%; font-size:12px;padding-bottom: 0px;vertical-align:center;background-color: #B8B1AF;">
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
							Fecha de Salida: <?php echo $fr_CitaCarga; ?><br>
							Teléfono: <?php echo $fr_RemitenteTelefono; ?>
						</td>
						<td style="text-align:left; width:50%; font-size:10px;padding-bottom: 0px;vertical-align:center;">
							Código Destino: <?php echo $fr_CodigoDestino;?><br>
							Razón Social: <?php echo $fr_Destinatario; ?><br>
							RFC: <?php echo $fr_DestinatarioRFC; ?><br>
							Domicilio: <?php echo $fr_DestinatarioCalle.' No.'.$fr_DestinatarioNumExt; ?><?php echo ' Col.'.$fr_NombreAsentamiento_1.', '.$fr_Descripcion_2.','; ?><?php echo $fr_Descripcion_3.' C.P.'.$fr_DestinatarioCodigoPostal; ?><br>
							Residencia Fiscal: <?php echo $fr_DestinatarioPais; ?><br>
							Identidad Tributaria: <?php echo $fr_DestinatarioNumRegIdTrib; ?><br>
							Fecha de Llegada: <?php echo $fr_DestinatarioCitaCarga; ?><br>
							Teléfono: <?php echo $fr_DestinatarioTelefono; ?><br>
							Distancia Recorrida: <?php echo $fr_DistanciaRecorrida; ?>
						</td>
					</tr>
					<?php
							$t2 = $t2 + 1;
						}
						
					
					?>
				</table>
			</div>
			
			
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-size: 11px;padding: 1;vertical-align: center;background-color: #B8B1AF;"><b>Total con letra:</b></td>
						<td style="text-align:right; width:20%; font-size: 10px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:right; width:10%; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:center;font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo ''; ?></b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Uso CFDi:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><b>Total:</b></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"><?php echo '0.00'; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 8px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
						<td style="text-align:right; font-size: 10px;padding: 1;vertical-align: center;"></td>
					</tr>
				</table>
			</div>
			
			<!-- FIN Subreporte 3 -->
			
			<?php
			}
			?>
			
			




		
		
		
		
		
		
		
		
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
	//echo $html;
	
	
	require_once 'libreria/dompdf/autoload.inc.php';

	use Dompdf\Dompdf;
	$dompdf = new Dompdf();
	
	
	$dompdf->set_option('default_font', 'open sans');

	$options = $dompdf->getOptions();
	$options->set(array('isRemoteEnabled' => true));
	
	$dompdf->setOptions($options);
	//$dompdf->set_option("isPhpEnabled", true);


	$dompdf->loadHtml($html);
	


	$dompdf->setPaper('letter');
	//$dompdf->setPaper('A4', 'landscape');
	
	

	$dompdf->render();
	
	// Obtiene el número total de páginas generadas por dompdf
	//$totalPages = $dompdf->getCanvas()->get_page_number();

	// Itera sobre cada página y agrega el número de página
	/*for ($i = 1; $i <= $totalPages; $i++) {
		$canvas = $dompdf->getCanvas();
		//$canvas->page_text(500, 760, "Página $i de $totalPages", null, 8, array(0, 0, 0));
		$canvas->page_text(570, 760, "Página $i", null, 9, array(0, 0, 0));
		$canvas->page_text(251, 760, "Complemento Carta Porte Versión 2.0", null, 8, array(0, 0, 0));
		$canvas->page_text(20, 760, "Versión del comprobante: 4.0", null, 8, array(0, 0, 0));
	}*/
	
	
	$canvas = $dompdf->get_canvas();
    //$font = Font_Metrics::get_font("helvetica", "bold");
	$font = 'default_font';
    $size = 8;
    $y = $canvas->get_height() - 22;
    $x = $canvas->get_width() - 50;
    //$canvas->page_text($x, $y, "{PAGE_NUM}/{PAGE_COUNT}", $font, $size);
	$canvas->page_text($x, $y, "Página {PAGE_NUM}", $font, $size);
	$canvas->page_text(251, 770, "Complemento Carta Porte Versión 2.0", null, 8, array(0, 0, 0));
	$canvas->page_text(20, 770, "Versión del comprobante: 4.0", null, 8, array(0, 0, 0));
	
	
	
	
	

	//Attachment" => false -- Para que no se descargue automaticamente
	$dompdf->stream("factura_".$f_xfolio.".pdf", array("Attachment" => false));
	
	

//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703 
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703


?>
