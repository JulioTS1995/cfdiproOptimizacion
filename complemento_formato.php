<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución

require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');
require 'phpqrcode/qrlib.php';
require_once __DIR__ . '/vendor/autoload.php';


if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die('Falta el prefijo de la BBDD');
}

if(!isset($_GET['tipo']) || empty($_GET['tipo'])){
	$tipoArchivo = 'dwld';
}else {
	$tipoArchivo = $_GET['tipo'];
}


 //Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$id_abono = $_GET["id"];
/* $id_abono = 9790989; */

$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

$prefijo = rtrim($prefijobd, "_");

//require_once('lib_mpdf/pdf/mpdf.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");
mysqli_set_charset($cnx_cfdi2, 'utf8mb4');
mysqli_query($cnx_cfdi2, "SET NAMES utf8mb4");

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
$mes_logs = date('m');
$dia_logs = date('d');

//helpers 
function u8($s) {
    if ($s === null) return '';
    if (is_array($s)) return $s;

    // Si ya es UTF-8 válido, regresa tal cual
    if (mb_check_encoding($s, 'UTF-8')) return $s;

    // Intenta convertir desde las codificaciones típicas en MX
    $converted = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

    // Si aún así trae basura, elimina bytes inválidos (último recurso)
    if (!mb_check_encoding($converted, 'UTF-8')) {
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $converted);
    }

    return $converted;
}

function u8h($s) {
    // UTF-8 + escapar para HTML (evita que un caracter raro truene el HTML)
    return htmlspecialchars(u8($s), ENT_QUOTES, 'UTF-8');
}

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

function convertir($numero, $cd_monedaG) {

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);

    
    $cd_monedaG = trim(strtoupper($cd_monedaG));
    $cd_monedaG = str_replace(["\t", "\n", "\r"], "", $cd_monedaG); 

   
   
        $monedaTexto = "PESOS";
		$moneda_nom = " M.N";
    

    return trim($numf) .' '.$monedaTexto.' '. $cents . '/100'. $moneda_nom;
}

function convertirDls($numero, $cd_monedaG){

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);

    
    $cd_monedaG = trim(strtoupper($cd_monedaG));
    $cd_monedaG = str_replace(["\t", "\n", "\r"], "", $cd_monedaG); 

   
    
		$monedaTexto = "DOLARES";
		$moneda_nom = " U.S.D";
	

    return trim($numf) .' '.$monedaTexto.' '. $cents . '/100'. $moneda_nom;
}


//////////////////// FIN Funcion Numeros a letra

//Seleccionar Mes letra
$mes_logs = [
	'01' =>"Enero",
	'02' =>"Febrero",
	'03' =>"Marzo",
	'04' =>"Abril",
	'05' =>"Mayo",
	'06' =>"Junio",
	'07' =>"Julio",
	'08' =>"Agosto",
	'09' =>"Septiembre",
	'10' =>"Octubre",
	'11' =>"Noviembre",
	'12' =>"Diciembre"
];
  
$fecha = $dia_logs." de ".$mes_2." de ". $anio_logs;


$fecha2 = (is_array($anio_logs) ? implode("", $anio_logs) : $anio_logs) . "-" .
(is_array($mes_logs) ? implode("", $mes_logs) : $mes_logs) . "-" .
(is_array($dia_logs) ? implode("", $dia_logs) : $dia_logs);

//Buscar datos para encabezado system settings
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
//razon social  a un solo emisor 
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysqli_query($cnx_cfdi2 ,$resSQL0);
while($rowSQL0 = mysqli_fetch_array($runSQL0)){
	$xml_dir= $rowSQL0['xmldir'];
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
	$PermisoSCT = $rowSQL0['PermisoSCT'];
	$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
	$codLocalidad = '';
	
	
}

#busca CD
#/*REN TablaGeneral en 33_RID*/
#falta agregar moneda
$resSQL01 = "SELECT 
                    *
                FROM {$prefijobd}abonos WHERE ID = {$id_abono}";
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
if (!$runSQL01) {//debug
	$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQ01;
	die($mensaje);
}
while( $rowSQL01 = mysqli_fetch_array($runSQL01)){
	$cd_ID= $rowSQL01['ID'];
    $cd_XFolio = $rowSQL01['XFolio'];
    $cd_cfdiuuid= $rowSQL01['cfdiuuid'];
	$cd_cancelada = $rowSQL01['cCanceladoT'];
    $cfdSerie= $rowSQL01 ['cfdserie'];
    $cfdSelloCFD = $rowSQL01['cfdiselloCFD'];
    $cfdSelloSAT= $rowSQL01['cfdiselloSAT'];
    $cd_tipoPago = $rowSQL01['TipoPago_RID'];
    $cfdCadenaOr = $rowSQL01['cfdcadenaoriginal'];
    $cfdSelloCadOrg = $rowSQL01['cfdiselloCadenaOriginal'];
    $cfdFolio = $rowSQL01 ['cfdfolio'];
    $cfdSelloDigital= $rowSQL01 ['cfdsellodigital'];
    $cfdfchhraRaw = $rowSQL01['cfdfchhra'];
	$cfdfchhra = date("d-m-Y H:i:s", strtotime($cfdfchhraRaw));
    $cfdiFechaTimbrado= $rowSQL01['cfdifechaTimbrado'];    
    $cd_formaPago = $rowSQL01['formapago33_RID'];
    $cd_metodoPago= $rowSQL01['metodopago33_RID'];
    $cd_usoCFDI = $rowSQL01['usocfdi33_RID'];
	$cd_oficina_ID = $rowSQL01['Oficina_RID'];
    $comentarios = $rowSQL01 ['Comentarios'];
	$totalImportefct = $rowSQL01['TotalImporte2'] - $rowSQL01 ['TotalImportePagado'];
	$totalImportefcto = number_format((float)$totalImportefct,2, '.', ',');
    $totalImporte= number_format((float)$rowSQL01['TotalImporte2'],2, '.', ',');
	$totalImporteDls= number_format((float)$rowSQL01['TotalImporte'],2,'.',',');
    $cd_IDcliente= $rowSQL01['Cliente_RID'];
	$cd_IDclientefactoeaje = $rowSQL01['ClienteFactoraje_RID'];
    $totalIVA= $rowSQL01['TotalIVA'];
    $totalIVAn= number_format((float)$totalIVA,3,'.',',');
    $totalSubtotal= number_format((float)$rowSQL01['TotalSubtotal'],2,'.',',');
    $totalRetencion= $rowSQL01['TotalRetencion'];
    $totalRetencionN= number_format((float)$totalRetencion,3,'.',',');
	$totalISR= $rowSQL01['TotalISR'];
	$totalISRn= number_format((float)$totalISR,3,'.',',');
	$cd_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$cd_cfdnocertificado = $rowSQL01['cfdnocertificado'];
	$cdf_impPagadoFac = $rowSQL01 ['TotalImportePagado'];
	$cdf_impPagadoFact = number_format((float)$rowSQL01 ['TotalImportePagado'],2, '.', ',');
	$cd_monedaG = $rowSQL01 ['Moneda'];
    $cd_totalLetra = convertir($totalImporte, $cd_monedaG);
    $cd_totalLetraDls = convertirDls($totalImporteDls, $cd_monedaG);
	$cdFactoraje1 = $rowSQL01 ['Factoraje1'];
	$cdFactoraje2 = $rowSQL01 ['Factoraje2'];

	$cd_factoraje2 = ($cdFactoraje1 >= 1 || $cdFactoraje2 >= 1 ) ? 1 : 0;
	
	$cd_fechaAplicado = $rowSQL01 ['FechaAplicado'];
	$cd_tipoCambio = number_format((float)$rowSQL01['TipoCambio'],2, '.', ',');
	if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}
	$fechaCreadoRaw = $rowSQL01['Fecha'];
	$fechaCreado = date("d-m-Y H:i:s", strtotime($fechaCreadoRaw));


	
}
//Buscar datos para encabezado system setting o multiemisor

function cancelado($cd_cancelada) {
    if (!empty($cd_cancelada)) {
        echo '
        <div style="
            position: fixed;
            top: 35%;
            left: 5%;
            width: 100%;
            transform: rotate(-45deg);
            opacity: 0.5;
            z-index: -1;
            font-family: Helvetica; font-size: 100px;
            color: red;
            text-align: center;
        ">
            CANCELADO
        </div>';
    }
}

if ($Multi == '1'){
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
		
		/* die($ruta_logo_multi); */

		
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

 //RUTAS LOGO 

 $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

 if ($Multi == 1 ) {
	$rutalogo= $ruta_logo_multi;
 }

//buscar oficina
$resSQLoficina = "SELECT  EsAbon FROM {$prefijobd}oficinas WHERE ID =".$cd_oficina_ID;
$runSQLoficina = mysqli_query($cnx_cfdi2, $resSQLoficina);

if ($runSQLoficina) {
    while ($rowSQLoficina = mysqli_fetch_array($runSQLoficina)) {
        if (isset($rowSQLoficina['EsAbon'])) {
            $cd_oficina_esAbon = $rowSQLoficina['EsAbon'];
        } else {
            $cd_oficina_esAbon = 'NO';
        }

        if ($cd_oficina_esAbon == '1') {
            $nombre_documento = 'Abono';
			$cfdfchhra = $fechaCreado;
        } else {
            $nombre_documento = 'Recibo Electrónico de Pago';
			$cfdfchhra = $cfdfchhra;
        }
    }
} else {
    // Manejo de error si la consulta falla en el campo de EsAbon
    $nombre_documento = 'Recibo Electrónico de Pago';
	$cfdfchhra = $cfdfchhra;
}
if (empty($cd_cfdiuuid)) {
	$cfdfchhra = $fechaCreado;
}else {
	$cfdfchhra = $cfdfchhra;

}

//Buscar Cliente


if(empty($cd_IDcliente)){
	
	
$cliente_nombre = '';


$cliente_rfc = '';
$cliente_cp = '';

} else {
/* Receptor */
//$resSQL03 = "SELECT * FROM ".$prefijobd."clientes WHERE id=".$f_id_cliente;
$resSQL03 = "SELECT * FROM {$prefijobd}clientes  WHERE ID=".$cd_IDcliente;

$runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
if (!$runSQL03) {//debug
    $mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
    //$mensaje .= 'Consulta completa: ' . $resSQL03;
    die($mensaje);
}

while($rowSQL03 = mysqli_fetch_array($runSQL03)){
    $cliente_nombre = $rowSQL03['RazonSocial'];
    $cliente_rfc = $rowSQL03['RFC'];
	$cBanco_ctaBeneficiario= $rowSQL03['zCuentaBeneficiario'];
	$cBanco_RfcBenefBanco = $rowSQL03 ['zRFCBeneficiarioBanco'];
	$cBanco_ctaOrdenante= $rowSQL03['zCuentaOrdenante'];
	$cBanco_RfcOrdBanco = $rowSQL03['zRFCOrdenanteBanco'];
	$cBanco_nombreBAncoOrd= $rowSQL03['zBancoOrdenante'];
	if (isset($rowSQL03['cRegimenFiscal_RID']) && $rowSQL03['cRegimenFiscal_RID'] >=1 ) {
		$Regimen_prev= $rowSQL03['cRegimenFiscal_RID'];
		
		$resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
		$runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
		$rowSQL007= mysqli_fetch_assoc($runSQL007);
		if ($rowSQL007){
			$cliente_regimen = $rowSQL007['Clave']."- ".$rowSQL007['Descripcion'];
		}
	}else{
		$cliente_regimen = $rowSQL03['RegimenFiscal'].", ".$rowSQL03['RegimenFiscalDescripcion'];
		
	}

}}
//buscar Cliente factoraje
$resSQLfact = "SELECT RazonSocial, RFC, cRegimenFiscal_RID FROM {$prefijobd}clientes WHERE ID = {$cd_IDclientefactoeaje}";
$runSQLfact = mysqli_query($cnx_cfdi2, $resSQLfact);
$rowSQLfact = mysqli_fetch_assoc($runSQLfact);
if ($rowSQLfact) {
	$cdf_cliente_factoraje = $rowSQLfact['RazonSocial'];
	$cfd_cliente_factoraje_rfc = $rowSQLfact['RFC'];
	if (isset($rowSQLfact['cRegimenFiscal_RID']) && $rowSQLfact['cRegimenFiscal_RID'] >=1 ) {
		$Regimen_prev= $rowSQLfact['cRegimenFiscal_RID'];
		
		$resSQL007f= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
		$runSQL007f= mysqli_query($cnx_cfdi2, $resSQL007f);
		$rowSQL007f= mysqli_fetch_assoc($runSQL007f);
		if ($rowSQL007f){
			$cdf_cliente_factoraje_regimen = $rowSQL007f['Clave']."- ".$rowSQL007f['Descripcion'];
		}else{
			$cdf_cliente_factoraje_regimen = $rowSQLfact['RegimenFiscal'].", ".$rowSQLfact['RegimenFiscalDescripcion'];
		}

}

//busca banco PEDNIENTE

}
//Buscar usocfdi
$f_usocfdi  = '';
$f_usocfdi_dsc = '';
if($cd_usoCFDI > 0){
	$resSQL07 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$cd_usoCFDI;
	$runSQL07 = mysqli_query( $cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$f_usocfdi_dsc = $rowSQL07['Descripcion'];
		$f_usocfdi = $rowSQL07['ID2'];
	}
}

//Buscar metodopago
$f_metodopago  = '';
$f_metodopago_dsc = '';
if($cd_metodoPago > 0){
	$resSQL08 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$cd_metodoPago;
	$runSQL08 = mysqli_query( $cnx_cfdi2 ,$resSQL08);
	while($rowSQL08 = mysqli_fetch_array($runSQL08)){
		$f_metodopago_dsc = $rowSQL08['Descripcion'];
		$f_metodopago = $rowSQL08['ID2'];
		if (!$runSQL08) {//debug
			$mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
			$mensaje .= 'Consulta completa: ' . $resSQL08;
			die($mensaje);
			}
	}
}

//Buscar formapago
$f_formapago  = '';
$f_formapago_dsc = '';
if($cd_formaPago > 0){
	$resSQL09 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$cd_formaPago;
	$runSQL09 = mysqli_query( $cnx_cfdi2 ,$resSQL09);
	while($rowSQL09 = mysqli_fetch_array($runSQL09)){
		$f_formapago_dsc = $rowSQL09['Descripcion'];
		$f_formapago = $rowSQL09['ID2'];
	}
}

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
$parametro_juntado_cfdis = 501;
$resSQL501 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_juntado_cfdis";
$runSQL501 = mysqli_query($cnx_cfdi2, $resSQL501);
	 
while ($rowSQL501 = mysqli_fetch_array($runSQL501)) {
	$param= $rowSQL501['id2'];
	$agrupa= $rowSQL501 ['VLOGI'];
}
//estilo de colores
if (($Multi ==1) && !empty($coloresMulti)) {
	$estilo_fondo = $coloresMulti;
}else {
$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

}

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
$separar = explode($separador, $totalImporte);
$sep_t = $separar[1];
//Enteros
$parte1_t = $separar[0];
$parte1 =  zero_fill_left($parte1_t,10);
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
$total_qr = $parte1.".".$parte2;

$sellocorto= substr($cfdSelloDigital, -8);

//QR
$dir = "C:/xampp/htdocs/XML_{$prefijo}/";

if(!file_exists($dir)){
	mkdir($dir);
}

$filename = $dir.$cfdSerie.'-'.$cfdFolio.'.svg';

$contenido = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&re='.$RFC.'&rr='.$cliente_rfc.'&tt=0000000000.000000&id='.$cd_cfdiuuid.'&fe='.$sellocorto ;


// URL de la imagen QR
$contenido = urlencode($contenido);
$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido}&format=svg";

//die($url);
// Obtener el contenido de la imagen
$imageContent = file_get_contents($url);

// Guardar la imagen en el servidor
file_put_contents($filename, $imageContent);

$RazonSocial = u8($RazonSocial);
$RFC = u8($RFC);
$Calle = u8($Calle);
$NumeroExterior = u8($NumeroExterior);
$NumeroInterior = u8($NumeroInterior);
$Colonia = u8($Colonia);
$Ciudad = u8($Ciudad);
$Estado = u8($Estado);
$Municipio = u8($Municipio);
$Regimen = u8($Regimen);
$comentarios = u8($comentarios);

$cliente_nombre = u8($cliente_nombre);
$cliente_rfc = u8($cliente_rfc);
$cliente_regimen = u8($cliente_regimen);

$cBanco_ctaBeneficiario = u8($cBanco_ctaBeneficiario);
$cBanco_ctaOrdenante = u8($cBanco_ctaOrdenante);
$cBanco_nombreBAncoOrd = u8($cBanco_nombreBAncoOrd);
$cBanco_RfcBenefBanco = u8($cBanco_RfcBenefBanco);
$cBanco_RfcOrdBanco = u8($cBanco_RfcOrdBanco);




$datos_cliente = [
	'lleva_factoraje' => $cd_factoraje2,
	'cliente_factoraje_nombre' => $cdf_cliente_factoraje,
	'cliente_factoraje_rfc' => $cfd_cliente_factoraje_rfc,
	'cliente_factoraje_regimen' => $cdf_cliente_factoraje_regimen,
	'cliente_nombre' => $cliente_nombre,
	'cliente_rfc' => $cliente_rfc,
	'cliente_regimen' => $cliente_regimen
];

function imprimirTablaCliente($datos_cliente){

	if ($datos_cliente['lleva_factoraje']>= 1) {
				echo	'<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
							<thead>
								<tr >
									<th style="text-align:left; font-family: Helvetica;"><b>Receptor (Ordenante del pago)</b></th>
								</tr>
							</thead>
							<tr style="margin:0; padding:0">
								<td style="text-align:left; width:55%; font-family: Helvetica; font-size: 11px;">
									<b>Cliente:</b> '.$datos_cliente['cliente_factoraje_nombre'].'
								</td>
							</tr>
							<tr style="margin:0; padding:0">
								<td style="text-align:left; width:55%; font-family: Helvetica; font-size: 11px;">
									<b>RFC:</b> '.$datos_cliente['cliente_factoraje_rfc'].'
								</td>
							</tr>
							<tr style="margin:0; padding:0">
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;">
									<b>Regimen Fiscal:</b> '.$datos_cliente['cliente_factoraje_regimen'].'
								</td>
								
							</tr>

						</table>';
	} else {
		echo	'<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
						<tr>
							<th style="text-align:left; font-family: Helvetica;"><b>Receptor (Ordenante del pago)</b></th>
						</tr>
					</thead>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-family: Helvetica; font-size: 11px;">
							<b>Cliente:</b> '.$datos_cliente['cliente_nombre'].'
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-family: Helvetica; font-size: 11px;">
							<b>RFC:</b> '.$datos_cliente['cliente_rfc'].'
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;">
							<b>Regimen Fiscal:</b> '.$datos_cliente['cliente_regimen'].'
						</td>
						
					</tr>

				</table>';

	}
	

				
}

function breakSello($sello, $cada = 80) {
    return implode('&#8203;', str_split($sello, $cada));
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
                bottom: 12px; 
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
			font-family: Helvetica, sans-serif;
			margin-bottom: 195px;
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
		
		
		
		<title>Complemento de Pago  <?php echo "P".$cd_XFolio."=".$cfdSerie."-".$cfdFolio ;?></title>
	</head>
	<body>
		<htmlpageheader name="myHeader">
			<div>
				<table border="0" style="margin:0; border-collapse: collapse;" width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> style="width: 115px; height:auto;" alt=" "/></td>
					<td style="text-align:center; width:40%; font-family: Helvetica; font-size: 12px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' '.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$domicilioRestante.' <br/>Régimen Fiscal: '.$Regimen.''; ?><br/>

							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-family: Helvetica; font-size: 12px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-family: Helvetica; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b><?php echo $nombre_documento; ?></b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;">P - Pago</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $cd_XFolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Fecha Emision</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo $cfdfchhra; ?></td>
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
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
						
						<td style="text-align:Left; width:70%; font-family: Helvetica; font-size: 12px;padding-bottom: 0px;" colspan='1'><b>Comentarios: <?php echo $comentarios; ?></b>
							
						</td>
							<td style="text-align:left; width:30%; font-family: Helvetica; font-size: 9px;padding-bottom: 0px;border: 1px solid rgba(128, 128, 128, 0.5);vertical-align:right;">
								<b>CFDI Relacionado: <?php 
								
								$resSQL44 = "SELECT cfdiuuidRelacionado, XFolio FROM {$prefijobd}abonosuuidrelacionadosub WHERE FolioSub_RID = {$id_abono}";
								$runSQL44 = mysqli_query($cnx_cfdi2, $resSQL44);
								       if (mysqli_num_rows($runSQL44) > 0) {
								$datos = [];
								while ($rowSQL44 = mysqli_fetch_array($runSQL44)) {
									$uuidrelacionado = $rowSQL44['cfdiuuidRelacionado'];
									$uuidXfolio = $rowSQL44['XFolio'];
									$datos[] = $uuidrelacionado . ', Folio:' . $uuidXfolio.'.';
								}
								echo implode(', ', $datos);
							} else {
								echo "N/A";
							}?></b>
							</td>
					</tr>
				</table>
				
			</div>
		
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
                			
					<tr style="margin:0; padding:0" >
						
						
						<td style="text-align:center; width:25%;" rowspan='7'>
							<img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$cfdSerie.'-'.$cfdFolio.'.svg'?>' style="width:100px; height:auto;"  alt="QR"/>
						</td>

						<td style="text-align:center; width:75%; font-family: Helvetica; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Este documento es una representación impresa de un CFDI</b>
						</td>

					</tr>
                   
					<tr style="margin:0; padding:0 ;width:100%;" >
						<td style="text-align:left; width:15%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<b>Serie del Certificado del emisor:</b>
						</td>
						<td style="text-align:left; width:85%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<?php echo $cd_cfdnocertificado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0;width:100%;" >
						<td style="text-align:left; width:15%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<b>Folio Fiscal: </b>
						</td>
						<td style="text-align:left; width:85%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<?php echo $cd_cfdiuuid; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0;width:100%;" >
						<td style="text-align:left; width:15%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<b>No. de serie del Certificado del SAT:</b>
						</td>
						<td style="text-align:left; width:85%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<?php echo $cd_cfdinoCertificadoSAT. $nc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0;width:100%;" >
						<td style="text-align:left; width:15%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<b>Fecha y hora de certificación:</b>
						</td>
						<td style="text-align:left; width:85%; font-family: Helvetica; font-size:10px;padding-bottom: 0px;">
							<?php echo $cfdiFechaTimbrado; ?>
						</td>
					</tr>
				</table>
			
			<table border="0" style="width:100%; border-collapse:collapse; margin-top:5px;">
					<tr>
						<td colspan="3" style="text-align:center; <?php echo $estilo_fondo; ?> font-family: Helvetica; font-size:12px;"><b>SELLOS</b></td>
					</tr>
					<tr>
						<td style="text-align:center; font-family: Helvetica; font-size:10px;<?php echo $estilo_fondo; ?>font-family: Helvetica; font-size:12px;"><b>Sello digital del CFDI</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size:10px;<?php echo $estilo_fondo; ?>font-family: Helvetica; font-size:12px;"><b>Cadena original</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size:10px;<?php echo $estilo_fondo; ?>font-family: Helvetica; font-size:12px;"><b>Sello del SAT</b></td>				
					
					</tr>
					
					<tr>
					<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($cfdSelloCFD); ?></td>
					<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($cfdSelloCadOrg); ?></td>
					<td style="word-break: break-all; white-space: normal; width: 33%; font-family: Helvetica; font-size: 7px; line-height: 1.2;"><?php echo breakSello($cfdSelloSAT); ?></td>
						
					</tr>
    		</table>
			
					<table width="100%" style="font-family: Helvetica; font-size: 8pt;">

						<tr>	
							<td width="33%" align="right">Página {PAGENO}</td>
						</tr>
					</table>
		</htmlpagefooter>
	<sethtmlpagefooter name="myFooter" value="on" show-this-page="1" />
		
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
		
			<div>
			<?php  imprimirTablaCliente($datos_cliente) ?> 
		
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<thead>
							<tr>
								<th colspan="2" style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Banco ordenante</th>
							</tr>
						</thead>
				
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>RFC Emisor Cuenta Ordenante: </b> <?php echo $cBanco_RfcOrdBanco; ?></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>RFC Emisor Cuenta Beneficiario:</b> <?php echo $cBanco_RfcBenefBanco; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Cuenta ordenante: </b> <?php echo $cBanco_ctaOrdenante; ?></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Cuenta Beneficiario: </b> <?php echo $cBanco_ctaBeneficiario; ?></td>
						
					</tr>
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Nombre Banco Ordenante:</b> <?php echo $cBanco_nombreBAncoOrd; ?></td>
						
					</tr>
					
				</table>
			</div>
	<div>
    <table border="0" style="margin:0; border-collapse: collapse;" width="100%">
        <thead>
            <tr>
                <th style="text-align:center; width:8%; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Cantidad</th>
                <th style="text-align:center; width:8%; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Clave Unidad SAT</th>
                <th style="text-align:center; width:12%; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Clave Producto/Servicio</th>
                <th style="text-align:center; width:9%; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Monto</th>
                <th style="text-align:center; width:9%; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Monto Factoraje</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center; font-family: Helvetica; font-size: 11px;">1.00</td>
                <td style="text-align:center; font-family: Helvetica; font-size: 11px;">ACT - Actividad</td>
                <td style="text-align:center; font-family: Helvetica; font-size: 11px;">84111506 - Servicios de facturacion</td>
                <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo "$ ".$totalImporte. " M.N."; ?></td>
                <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo "$ ".$cdf_impPagadoFact. " M.N."; ?></td>
            </tr>
        </tbody>
    </table>

	
	
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<thead>
							<tr>
								<th colspan="3" style="text-align:center; padding-bottom:0;margin-bottom:-250px;font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Informacion del pago</th>
							</tr>
						</thead>
						<tbody>
						<?php if ($cd_monedaG != "PESOS") { ?>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Fecha de Pago:</b> <?php echo $cd_fechaAplicado; ?><br><b>Uso CFDI:</b> <?php echo $f_usocfdi.'-'.$f_usocfdi_dsc;;?></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Tipo de Cambio:</b></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">$ <?php echo $cd_tipoCambio; ?></td>
							</tr>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></b></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Monto M.N: </b></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">$ <?php echo $totalImporte; ?></td>
							</tr>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">Monto U.S.D: </td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">$ <?php echo $totalImporteDls; ?></td>
								
							</tr>
						<?php } else { ?>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Fecha de Pago:</b> <?php echo $cd_fechaAplicado; ?><br><b>Uso CFDI:</b> <?php echo $f_usocfdi.'-'.$f_usocfdi_dsc;;?></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Tipo de Cambio:</b></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">$ <?php echo $cd_tipoCambio; ?></td>
							</tr>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Monto: </b></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"> <?php if($cd_factoraje2 >= 1){ 
						
						echo "$ ".$totalImportefcto . " M.N.";
					} else {
						echo "$ ".$totalImporte. " M.N."; 
						} ?></td>
							</tr>
							<tr>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"></td>
								<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"></td>
								
							</tr>
						<?php } ?>
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: left;"><b>Moneda:</b> <?php echo $cd_monedaG; ?></td>
						<?php 
						if($cd_factoraje2 >= 1){
						?>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Monto Factoraje: </b></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">$ <?php echo $cdf_impPagadoFact?></td>
						<?php } ?>
					</tr>
					</tbody>
				</table>
			</div>
		<div>

            <?php 
			
            # Consulta de CFDI Relacionados
			if ($agrupa >= 1){
					$resSQL03 = "SELECT 
									a.Moneda, 
									a.TipoCambio, 
									SUM(a.ImporteTemp) AS ImporteTemp, 
									SUM(a.Saldo) AS Saldo, 
									b.cfdiuuid, 
									b.XFolio,
									a.NumParcialidad
								 FROM {$prefijobd}abonossub AS a
								 LEFT JOIN {$prefijobd}factura AS b 
								 ON a.AbonoFactura_RID = b.ID 
								 WHERE a.FolioSub_RID = {$cd_ID} 
								 GROUP BY b.XFolio, a.Moneda, a.TipoCambio, b.cfdiuuid
								 ORDER BY b.XFolio";
				} else {
					$resSQL03 = "SELECT 
									a.Moneda, 
									a.TipoCambio, 
									a.ImporteTemp, 
									a.Saldo, 
									b.cfdiuuid, 
									b.XFolio,
									a.NumParcialidad
								 FROM {$prefijobd}abonossub AS a
								 LEFT JOIN {$prefijobd}factura AS b 
								 ON a.AbonoFactura_RID = b.ID 
								 WHERE a.FolioSub_RID = {$cd_ID} 
								 ORDER BY b.XFolio";
			}

				$runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
			$contador = 0;
			$porPagina = 35;
			$primerSalto = true;
			
			#Encabezado de la tabla que se reimprime
			function imprimirEncabezado($estilo_fondo) {
				echo '
				<table  style="margin-top:2px; border-collapse: collapse; padding-top:30px;" width="100%p">
					<thead>
						<tr>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">UUID</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">Folio Factura</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">Moneda</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">No. Parcialidad</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">Tipo de Cambio</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">Pagado</th>
							<th style="text-align:center; font-family: Helvetica; font-size: 11px; '.$estilo_fondo.'">Saldo insoluto</th>
						</tr>
					</thead>
					<tbody>';
			}
			
			# Imprime la primera tabla
			imprimirEncabezado($estilo_fondo);
			
			while ($rowSQL03 = mysqli_fetch_array($runSQL03)) {
				if (!$runSQL03) {
					$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
					$mensaje .= 'Consulta completa: ' . $resSQL03;
					die($mensaje);
				}
			
				# Si se alcanzó el límite de 38 filas por tabla, cerrar y abrir nueva para optmizar la impresion
				if ($contador > 0 && $contador ==25 && $primerSalto ) {
					echo '</tbody></table>';
								
					echo '<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" /><pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />';
					imprimirEncabezado($estilo_fondo);
					$primerSalto = false;
				}elseif (!$primerSalto && ($contador - 25) % 42 == 0) {
					echo '</tbody></table>';
								
					echo '<sethtmlpagefooter name="myFooter" value="on" show-this-page="all" /><pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />';
					echo '<br><br>';
					imprimirEncabezado($estilo_fondo);
				}
			
				$f_cfdiuuid = $rowSQL03['cfdiuuid'];
				$f_XFolio = $rowSQL03['XFolio'];
				$cds_moneda = $rowSQL03['Moneda'];
				$cds_parcialidad = $rowSQL03['NumParcialidad'];
				$cd_tCambio = number_format((float)$rowSQL03['TipoCambio'],2, '.', ',');
				$cd_Importe = number_format((float)$rowSQL03['ImporteTemp'],2, '.', ',');
				$cd_saldoInsoluto = number_format((float)$rowSQL03['Saldo'], 2,'.', ',');
			
				echo '<tr>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">'.$f_cfdiuuid.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">'.$f_XFolio.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">'.$cds_moneda.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">'.$cds_parcialidad.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">$ '.$cd_tCambio.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">$ '.$cd_Importe.'</td>
					<td style="text-align:center; font-family: Helvetica; font-size: 10px;">$ '.$cd_saldoInsoluto.'</td>
				</tr>';
			
				$contador++;
			}
			
			echo '</tbody></table></div>'; ?>
    <div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Totalizador
						</b></td>
						<td style="text-align:left; width:20%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$".$totalSubtotal; ?></td>
					</tr>
					<tr>
						<?php if ($cd_monedaG != "PESOS") { ?>
							<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Total U.S.D.:</b></td>

						<?php } else { ?>
							<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Total M.N.:</b></td>
						<?php } ?>
							
						
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$".$totalIVAn; ?></td>
					</tr>
					<tr>
						<?php if ($cd_monedaG != "PESOS") { ?>
							<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo $cd_totalLetraDls; ?></b></td>

						<?php } else { ?>
							<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo $cd_totalLetras; ?></b></td>
						<?php } ?>
						
						
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b> <?php if (!empty($totalISR)) {
							echo "<br><b>ISR: </b>";
						} ?></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$".$totalRetencionN; ?> <?php if (!empty($totalISR)) {
							echo "<br> $".$totalISRn;
						} ?></td>
					</tr>
					 <?php if ($cd_monedaG != "PESOS") { ?>
						
					
					<tr>
					<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b></b></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Total U.S.D:</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$".$totalImporteDls; ?></td>
					</tr>
					<?php } else{   ?>
						<tr>
							<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b></b></td>
							<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Total M.N.:</b></td>
							<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$".$totalImporte; ?></td>
							
						</tr>

					<?php } ?> 
						
				</table>
			</div>
		</table>
</div>
<?php 
			if($cd_factoraje2 >= 1){
			?>
			
			<!-- Factoraje en otra pagina para mayor claridad-->
			<pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" /><sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />
			<div>
			<table border="1" style="margin:0; border-collapse: collapse;" width="100%">
        <thead>
            <tr>
                <th colspan="3" style="text-align:center; font-family: Helvetica; font-size: 15px; <?php echo $estilo_fondo; ?>"><b>FACTORAJE</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="text-align:center; font-family: Helvetica; font-size: 13px; <?php echo $estilo_fondo; ?>">
                    <b>DOCUMENTOS PAGADOS</b>
                </td>
            </tr>
            <tr>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">UUID</th>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Folio Factura</th>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Moneda</th>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Tipo de Cambio</th>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Pagado</th>
                <th style="text-align:center; font-family: Helvetica; font-size: 11px; <?php echo $estilo_fondo; ?>">Saldo insoluto</th>

            </tr>
            
            <?php 
			$cdf_totalRetFac= $cdf_impPagadoFac*.04;
			$cdf_totalRetFact= number_format((float)$cdf_totalRetFac,3);
			$cdf_totalIVAFac= $cdf_impPagadoFac*.16;
			$cdf_totalIVAFact= number_format((float)$cdf_totalIVAFac,3);
			$cdf_TotalSubtotalFac= $cdf_impPagadoFac - ($cdf_totalIVAFact - $cdf_totalRetFact);
			$cdf_TotalSubtotalFact= number_format((float)$cdf_TotalSubtotalFac,2);
			$cdf_total_letra_fact= convertir($cdf_impPagadoFac, $cd_monedaG);
            # Consulta de CFDI Relacionados
			if ($agrupa >= 1){
				$resSQL02 = "SELECT 
								a.Moneda, 
								a.TipoCambio, 
								SUM(a.ImportePagado) AS ImportePagado, 
								SUM(a.Saldo) AS SaldoRem, 
								b.cfdiuuid, 
								b.XFolio 
							 FROM {$prefijobd}abonossub AS a
							 LEFT JOIN {$prefijobd}factura AS b 
							 ON a.AbonoFactura_RID = b.ID 
							 WHERE a.FolioSub_RID = {$cd_ID} 
							 GROUP BY b.XFolio, a.Moneda, a.TipoCambio, b.cfdiuuid
							 ORDER BY b.XFolio";
			} else {
				$resSQL02 = "SELECT 
								a.Moneda, 
								a.TipoCambio, 
								a.ImportePagado, 
								a.Saldo AS SaldoRem, 
								b.cfdiuuid, 
								b.XFolio 
							 FROM {$prefijobd}abonossub AS a
							 LEFT JOIN {$prefijobd}factura AS b 
							 ON a.AbonoFactura_RID = b.ID 
							 WHERE a.FolioSub_RID = {$cd_ID} 
							 ORDER BY b.XFolio";
			}
			
			$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);

            while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
                $f_cfdiuuid = $rowSQL02['cfdiuuid'];
                $f_XFolio = $rowSQL02['XFolio'];
				$cdf_moneda = $rowSQL02 ['Moneda'];
				$cdf_tCambio = number_format((float)$rowSQL02 ['TipoCambio'],2, '.', ',');
				$cdf_saldoIns = number_format((float)$rowSQL02 ['SaldoRem'],2, '.', ',');
				$cdf_impPagado = number_format((float)$rowSQL02 ['ImportePagado'],2, '.', ',');

                if (!$runSQL02) { // Debug
                    $mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
                    $mensaje .= 'Consulta completa: ' . $resSQL02;
                    die($mensaje);
                }
            ?>
                <tr>
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo $f_cfdiuuid; ?></td>
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo $f_XFolio; ?></td>
					<!-- moneda  -->
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo $cdf_moneda; ?></td>
					<!-- Tipo cambio -->
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo "$ ".$cdf_tCambio; ?></td>
					<!-- pagado -->
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo "$ ".$cdf_impPagado; ?></td>
					<!-- Saldo insoluto -->
                    <td style="text-align:center; font-family: Helvetica; font-size: 11px;"><?php echo "$ ".$cdf_saldoIns; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
			</div>
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Total Factoraje con letra:</b></td>
						<td style="text-align:left; width:20%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Subtotal:</b></td>
						<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$ ".$cdf_TotalSubtotalFact; ?></td>
					</tr>
					<tr>
						<td style="text-align:center;font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA:</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$ ".$cdf_totalIVAFact; ?></td>
					</tr>
					<tr>
						<td style="text-align:center; font-family: Helvetica; font-size: 13px;padding: 1;vertical-align: center;"><b><?php echo $cdf_total_letra_fact; ?></b></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$ ".$cdf_totalRetFact; ?></td>
					</tr>
					<tr>
						<td style="text-align:left; font-family: Helvetica; font-size: 8px;padding: 1;vertical-align: left;"></td>
						<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Total:</b></td>
						<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo "$ ".$cdf_impPagadoFact; ?></td>
					</tr>
					
				</table>
			</div>
			<?php }?>
			

			
					
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
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();
if (!mb_check_encoding($html, 'UTF-8')) {
    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
}
$html = iconv('UTF-8', 'UTF-8//IGNORE', $html);


// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


// Pie de página
$footerHtml = '
<table width="100%" style="font-family: Helvetica; font-size: 8pt;">
    <tr>
        <td width="33%">Versión del comprobante: 4.0</td>
        <td width="33%" align="center">Página {PAGENO}</td>
        <td width="33%" align="right">' . (($f_complemento_traslado >= 1) ? 'Complemento Carta Porte Versión 3.1' : '') . '</td>
    </tr>
</table>
';
$mpdf->SetHTMLFooter($footerHtml);
if (!empty($cd_cancelada)) {
	$mpdf->SetWatermarkText('CANCELADO', 0.1); 
	$mpdf->showWatermarkText = true;
	
	
	$mpdf->watermarkTextFont = 'helvetica';
	$mpdf->watermarkTextAlpha = 0.1; 
	$mpdf->watermarkTextAngle = 45; 
	$mpdf->watermarkTextSize = 100; 
}
$mpdf->SetFont('Helvetica');


$mpdf->WriteHTML($html);


$nombre_pdf = ($cfdFolio > 0) ? "P".$cd_XFolio."=".$cfdSerie."-".$cfdFolio : $prefijo . " - " . $cd_XFolio;


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