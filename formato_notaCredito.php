<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(800);
ini_set('memory_limit', '512M');
require_once ('cnx_cfdi.php');
require_once ('cnx_cfdi2.php');

//um250325

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die('Falta el prefijo de la BBDD');
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


$anio_logs = date('Y');
$mes_logs = date('m');
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

function convertir($numero, $nc_moneda) {

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);

    
    $nc_moneda = trim(strtoupper($nc_moneda));
    $nc_moneda = str_replace(["\t", "\n", "\r"], "", $nc_moneda); 

   
    if ($nc_moneda === "PESOS") {
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
	if (isset($rowSQL07['ColorFormatos'])) {
		$coloresMulti = $rowSQL07['ColorFormatos'];
	} else {
		$coloresMulti = '';
	}
	
	
}

#busca NC
#/*REN TablaGeneral en 33_RID*/
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
	$nc_ID= $rowSQL01['ID'];
    $nc_XFolio = $rowSQL01['XFolio'];
    $nc_cfdiuuid= $rowSQL01['cfdiuuid'];
	$nc_cancelada = $rowSQL01['cCanceladoT'];
    $cfdSerie= $rowSQL01 ['cfdserie'];
    $cfdSelloCFD = $rowSQL01['cfdiselloCFD'];
    $cfdSelloSAT= $rowSQL01['cfdiselloSAT'];
    $nc_tipoPago = $rowSQL01['TipoPago_RID'];
	$nc_moneda = $rowSQL01 ['Moneda'];
    $cfdCadenaOr = $rowSQL01['cfdcadenaoriginal'];
    $cfdSelloCadOrg = $rowSQL01['cfdiselloCadenaOriginal'];
    $cfdFolio = $rowSQL01 ['cfdfolio'];
    $cfdSelloDigital= $rowSQL01 ['cfdsellodigital'];
    $cfdfchhra = $rowSQL01['cfdfchhra'];
    $cfdiFechaTimbrado= $rowSQL01['cfdifechaTimbrado'];    
    $nc_formaPago = $rowSQL01['formapago33_RID'];
    $nc_metodoPago= $rowSQL01['metodopago33_RID'];
    $nc_usoCFDI = $rowSQL01['usocfdi33_RID'];
    $comentarios = $rowSQL01 ['Comentarios'];
    $totalImporte= number_format((float)$rowSQL01['TotalImporte'],2, '.', ',');
	$totalLetra=number_format((float)$rowSQL01['TotalLetra'],2, '.', ',');
    $nc_IDcliente= $rowSQL01['Cliente_RID'];
    $totalIVA= number_format((float)$rowSQL01['TotalIVA'],2, '.', ',');
    $totalSubtotal= number_format((float)$rowSQL01['TotalSubtotal'],2, '.', ',');
    $totalRetencion= number_format((float)$rowSQL01['TotalRetencion'],2, '.', ',');
	$nc_cfdinoCertificadoSAT = $rowSQL01['cfdinoCertificadoSAT'];
	$nc_cfdnocertificado = $rowSQL01['cfdnocertificado'];
    $nc_totalLetra = convertir($totalLetra, $nc_moneda);
	if ($Multi == 1) {
		$emisor_id = $rowSQL01['Emisor_RID'];
	}
	if(isset($rowSQL01['SinDocumentoRelacionado'])){
		$nc_sin_doc_rela = $rowSQL01['SinDocumentoRelacionado'];
	}else{
		$nc_sin_doc_rela = 0;
	}
	$anticipo = $rowSQL01['Anticipo'];
	
}
function cancelado($nc_cancelada) {
    if (!empty($nc_cancelada)) {
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
//encabezado MULTIEMISOR
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



//Buscar Cliente

$cliente_colonia = '';
$cliente_municipio = '';
$cliente_estado = '';
$cliente_ciudad = '';

if(empty($nc_IDcliente)){
	
	
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
IFNULL(d.Descripcion, '') as Municipio, IFNULL(e.Descripcion, '') as Localidad FROM {$prefijobd}clientes a 
LEFT JOIN {$prefijobd}estados b 
On a.Estado_RID = b.ID left join {$prefijobd}c_colonia c 
On a.c_Colonia_RID=c.ID left join {$prefijobd}c_municipio d 
on a.c_Municipio_RID=d.ID left join {$prefijobd}c_localidad e 
On a.Localidad_RID=e.ID WHERE a.id=".$nc_IDcliente;

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
$f_usocfdi  = '';
$f_usocfdi_dsc = '';
if($nc_usoCFDI > 0){
	$resSQL07 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$nc_usoCFDI;
	$runSQL07 = mysqli_query( $cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$f_usocfdi_dsc = $rowSQL07['Descripcion'];
		$f_usocfdi = $rowSQL07['ID2'];
	}
}

//Buscar metodopago
$f_metodopago  = '';
$f_metodopago_dsc = '';
if($nc_metodoPago > 0){
	$resSQL08 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$nc_metodoPago;
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
if($nc_formaPago > 0){
	$resSQL09 = "SELECT ID2, Descripcion FROM ".$prefijobd."tablageneral WHERE id=".$nc_formaPago;
	$runSQL09 = mysqli_query( $cnx_cfdi2 ,$resSQL09);
	while($rowSQL09 = mysqli_fetch_array($runSQL09)){
		$f_formapago_dsc = $rowSQL09['Descripcion'];
		$f_formapago = $rowSQL09['ID2'];
	}
}

// trae los parametros para color de fondo, color letra

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
//parametro para agrupar conceptos
$parametro_juntado_cfdis = 501;
$resSQL501 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_juntado_cfdis";
$runSQL501 = mysqli_query($cnx_cfdi2, $resSQL501);
	 
while ($rowSQL501 = mysqli_fetch_array($runSQL501)) {
	$param= $rowSQL501['id2'];
	$agrupa= $rowSQL501 ['VLOGI'];
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
$total_qr = $parte1.",".$parte2;

$sellocorto= substr($cfdSelloDigital, -8);

//QR
$dir = "C:/xampp/htdocs/XML_{$prefijo}/";

if(!file_exists($dir)){
	mkdir($dir);
}

$filename = $dir.$cfdSerie.'-'.$cfdFolio.'.svg';


$contenido = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id='.$nc_cfdiuuid.'&re='.$RFC.'&rr='.$cliente_rfc.'&tt='.$total_qr.'&fe='.$sellocorto ;


// URL de la imagen QR
$contenido = urlencode($contenido);
$url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$contenido}&format=svg";

//die($url);
// Obtener el contenido de la imagen
$imageContent = file_get_contents($url);

// Guardar la imagen en el servidor
file_put_contents($filename, $imageContent);
/* 
if (!file_exists("../cfdipro/imagenes/{$prefijo}.jpg")) {
    die("Error: Imagen prueba.jpg no encontrada.");
} */
/* if (!file_exists("C:/xampp/htdocs/XML_{$prefijo}/{$cfdSerie}-{$cfdFolio}.svg")) {
    die("Error: Imagen NC-1.svg no encontrada.");
} */


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
		
		
		
		<title>Nota de Credito: <?php echo "NC".$nc_XFolio."=".$cfdSerie."-".$cfdFolio ;?></title>
	</head>
	<body>
		<header>
			<div>
				<table border="0" style="margin:0; border-collapse: collapse;" width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> style="width:150px; height:auto;" alt=" "/></td>
						<td style="text-align:center; width:45%; font-size: 11px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-size: 12px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Nota de Credito</b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;"><b>Tipo Comprobante</b></td>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;">E - Egreso</td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;"><b>Serie y folio</b></td>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;"><label style="color:red"><b><?php echo $nc_XFolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-size: 12px;padding: 1;vertical-align: center;"><?php echo date("d-m-Y H:i:s", strtotime($cfdfchhra)); ?></td>
								</tr>

							</table>
						</td>
					</tr>

				</table>
			</div>
		</header>
		
		
		<footer>
			
			<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<tr style="margin:0; padding:0" >
						
						<td style="text-align:Left; width:70%; font-size: 12px;padding-bottom: 0px;" colspan='1'><b>Comentarios: <?php echo $comentarios; ?></b>
							
						</td>
						<td style="text-align:left; width:30%; font-size: 9px;padding-bottom: 0px;border: 2px solid rgba(139, 139, 139, 0.5);vertical-align:right;">
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

					 <?php  ?>
				</table>
				
			</div>
						
					</tr>
				</table>
				
			</div>
		
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
                			
					<tr style="margin:0; padding:0" >
						<!-- QR IMG 										file:///C:/xampp/htdocs/XML_PRUEBA/FT-231238.svg-->
						
						<td style="text-align:center; width:25%;" rowspan='7'>
							<img src='C:/xampp/htdocs/XML_<?php echo $prefijo.'/'.$cfdSerie.'-'.$cfdFolio.'.svg'?>' style="width:100px; height:auto;"  alt="QR"/>
						</td>

						<td style="text-align:center; width:80%; font-size:12px;padding-bottom: 0px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan='2'>
							<b>Este documento es una representación impresa de un CFDI</b>
						</td>

					</tr>
                   
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Serie del Certificado del emisor:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $nc_cfdnocertificado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Folio Fiscal:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $nc_cfdiuuid; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>No. de serie del Certificado del SAT:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $nc_cfdinoCertificadoSAT. $nc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0" >
						<td style="text-align:left; width:5%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<b>Fecha y hora de certificación:</b>
						</td>
						<td style="text-align:left; width:75%; font-size:8px;padding-bottom: 0px;word-wrap: break-word;">
							<?php echo $cfdiFechaTimbrado; ?>
						</td>
					</tr>
				</table>
			
			<table border="0" style="width:100%; border-collapse:collapse; margin-top:2px;">
					<tr>
						<td colspan="3" style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px;"><b>SELLOS</b></td>
					</tr>
					<tr>
						<td style="text-align:center; font-size:12px;<?php echo $estilo_fondo; ?>font-size:10px;"><b>Sello digital del CFDI</b></td>
						<td style="text-align:center; font-size:12px;<?php echo $estilo_fondo; ?>font-size:10px;"><b>Cadena original</b></td>
						<td style="text-align:center; font-size:12px;<?php echo $estilo_fondo; ?>font-size:10px;"><b>Sello del SAT</b></td>				
					
					</tr>
					
					<tr>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 8px;"><?php echo $cfdSelloCFD; ?></td>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 8px;"><?php echo $cfdSelloCadOrg; ?></td>
						<td style="word-wrap: break-word; white-space: normal; width: 33%; font-size: 8px;"><?php echo $cfdSelloSAT; ?></td>
					</tr>
    		</table>
		</footer>
		
		
		<main>
			<!-- Subreporte 1 -->
			<!--<div class="page-break"></div>-->
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;border: 1px solid rgba(128, 128, 128, 0.5)" width="100%">
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:55%; font-size: 12px;">
							<b>Cliente:</b> <?php echo $cliente_nombre; ?>
						</td>
						
						<td style="text-align:left; width:40%; font-size: 12px;">
							<b>RFC:</b> <?php echo $cliente_rfc; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 12px;">
							<b>Domicilio:</b> <?php echo $cliente_calle.' '.$cliente_numext.' '.$cliente_numint.' '.$cliente_ciudad; ?>
						</td>
						
						<td style="text-align:left; font-size: 12px;">
							<b>Municipio:</b> <?php echo $cliente_municipio; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 12px;">
							<b>Colonia:</b> <?php echo $cliente_colonia; ?>
						</td>
						<td style="text-align:left; font-size: 12px;">
							<b>Estado:</b> <?php echo $cliente_estado; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 12px;" >
							<b>CP:</b> <?php echo $cliente_cp; ?>
						</td>
						<td style="text-align:left; font-size: 12px;">
							
						</td>
					</tr>
				</table>
			
			<br>
			<div>
    <!-- 🔹 Primera tabla: Cantidad a Importe -->
    <table border="1" style="margin:0; border-collapse: collapse;" width="100%">
        <thead>
            <tr>
                <th style="text-align:center; width:8%; font-size: 11px; <?php echo $estilo_fondo; ?>">Cantidad</th>
                <th style="text-align:center; width:8%; font-size: 11px; <?php echo $estilo_fondo; ?>">Clave Unidad SAT</th>
                <th style="text-align:center; width:12%; font-size: 11px; <?php echo $estilo_fondo; ?>">Clave Producto/Servicio</th>
                <th style="text-align:center; width:9%; font-size: 11px; <?php echo $estilo_fondo; ?>">Valor Unitario</th>
                <th style="text-align:center; width:9%; font-size: 11px; <?php echo $estilo_fondo; ?>">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center; font-size: 11px;">1.00</td>
                <td style="text-align:center; font-size: 11px;">ACT - Actividad</td>
                <td style="text-align:center; font-size: 11px;">84111506 - Servicios de facturacion</td>
                <td style="text-align:center; font-size: 11px;"><?php echo "$ ".$totalImporte; ?></td>
                <td style="text-align:center; font-size: 11px;"><?php echo "$ ".$totalImporte; ?></td>
            </tr>
        </tbody>
    </table>

    <br> <!-- Espacio opcional entre tablas -->

    <!-- 🔹 Segunda tabla: CFDI Relacionados -->
    <table border="1" style="margin:0; border-collapse: collapse;" width="100%">
        <thead>
            <tr>
                <th colspan="3" style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">CFDI Relacionados</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="text-align:center; font-size: 11px; background-color:#ffffff;">
                    <b>Tipo Relación: <?php if ($anticipo) {
						echo '07 - APLICACION DE ANTICIPO';
					} else {
						echo '01 - Nota de crédito de los documentos relacionados';
					}
					?>
					 </b>
                </td>
            </tr>
			<?php if ($nc_sin_doc_rela >=1 ) { ?>
            <tr>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Clave Pod/servicio</th>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Descripcion</th>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Impuestos</th>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Importe</th>
            </tr>
            
            <?php 
			cancelado($nc_cancelada);

		
					$resSQL02 = "SELECT a.Subtotal, a.IVAImporte,  b.Concepto, a.Detalle FROM {$prefijobd}abonossub2 as a
					LEFT JOIN {$prefijobd}conceptos as b on a.Concepto_RID = b.ID WHERE a.FolioSub_RID = {$nc_ID}";
					$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);

					while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
						$nc_sdr_concepto = $rowSQL02['Concepto'];
						$nc_sdr_desc = $rowSQL02['Detalle'];
						$nc_sdr_impuestos = number_format((float)$rowSQL02 ['IVAImporte'], 2, '.', ',');
						$nc_sdr_impPagado = $rowSQL02 ['Subtotal'];
						

						if (!$runSQL02) { // Debug
							$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
							$mensaje .= 'Consulta completa: ' . $resSQL02;
							die($mensaje);
						} ?>
						<tr>
						<td style="text-align:center; font-size: 11px;"><?php echo $nc_sdr_concepto; ?></td>
						<td style="text-align:center; font-size: 11px;"><?php echo $nc_sdr_desc; ?></td>
						<td style="text-align:center; font-size: 11px;"><?php echo 'IVA:'. ' $'.$nc_sdr_impuestos; ?></td>
						<td style="text-align:center; font-size: 11px;"><?php echo "$ ".$nc_sdr_impPagado; ?> </td>
					</tr>
					<?php
					}
			 } else { ?>
			  <tr>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">UUID</th>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Folio Factura</th>
                <th style="text-align:center; font-size: 11px; <?php echo $estilo_fondo; ?>">Importe</th>
            </tr>
			<?php 
						if ($agrupa >= 1){

							$resSQL02 = "SELECT 
										b.cfdiuuid, 
										b.XFolio, 
										SUM(a.Importe) AS Importe
									FROM {$prefijobd}abonossub AS a
                        			LEFT JOIN {$prefijobd}factura AS b 
									ON a.AbonoFactura_RID = b.ID 
									WHERE a.FolioSub_RID = {$nc_ID}
									GROUP BY b.XFolio
									ORDER BY b.XFolio";
					
							} else {
							$resSQL02 = "SELECT 
											b.cfdiuuid, 
											b.XFolio, 
											a.Importe 
										FROM {$prefijobd}abonossub AS a
										LEFT JOIN {$prefijobd}factura AS b 
										ON a.AbonoFactura_RID = b.ID 
										WHERE a.FolioSub_RID = {$nc_ID}";
						}
				$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);

				while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
					$f_cfdiuuid = $rowSQL02['cfdiuuid'];
					$f_XFolio = $rowSQL02['XFolio'];
					$ncf_impPagado = number_format((float)$rowSQL02 ['Importe'], 2, '.', ',');

					if (!$runSQL02) { // Debug
						$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
						$mensaje .= 'Consulta completa: ' . $resSQL02;
						die($mensaje);
					} ?>
					<tr>
                    <td style="text-align:center; font-size: 11px;"><?php echo $f_cfdiuuid; ?></td>
                    <td style="text-align:center; font-size: 11px;"><?php echo $f_XFolio; ?></td>
					<td style="text-align:center; font-size: 11px;"><?php echo "$ ".$ncf_impPagado; ?> </td>
                </tr>
				<?php }
			

            # Consulta de CFDI Relacionados
           
            ?>
               
            <?php } ?>
        </tbody>
    </table>
</div>
			
			<div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td style="text-align:center; width:70%; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Total con letra:</b></td>
						<td style="text-align:left; width:20%; font-size: 12px;padding: 1;vertical-align: center;"><b>Subtotal:</b><?php echo " $ ".$totalSubtotal; ?></td>
						<td style="text-align:left; width:10%; font-size: 12px;padding: 1;vertical-align: center;"></td>
					</tr>
					<tr>
						<td style="text-align:center;font-size: 11px;padding: 1;vertical-align: center;"><b><?php echo $nc_totalLetra; ?></b></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA:</b><?php echo " $ ".$totalIVA; ?></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 11px;padding: 1;vertical-align: left;"><b>Uso CFDi:</b> <?php echo $f_usocfdi.' - '.$f_usocfdi_dsc; ?></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"><b>IVA Retenidos:</b><?php echo " $ ".$totalRetencion; ?></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 11px;padding: 1;vertical-align: left;"><b>Método de Pago:</b> <?php echo $f_metodopago.' - '.$f_metodopago_dsc; ?></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"><b>Total:</b><?php echo " $ ".$totalImporte; ?></td>
						<td style="text-align:left; font-size: 12px;padding: 1;vertical-align: center;"></td>
					</tr>
					<tr>
						<td style="text-align:left; font-size: 11px;padding: 1;vertical-align: left;"><b>Forma de Pago:</b> <?php echo $f_formapago.' - '.$f_formapago_dsc; ?></td>
						
					</tr>
				</table>
			</div>

			
					
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
	
	//die($html);
	
	require_once 'libreria/dompdf/autoload.inc.php';

	use Dompdf\Dompdf;
	use Dompdf\Options;

	$options = new Options();
	$options->set('defaultFont', 'open sans');
	$options->set('isHtml5ParserEnabled', true);
	$options->set('isImageEnabled', true);
	$options->set('isRemoteEnabled', true);
	$options->set('isFontSubsettingEnabled', true);

	
	$dompdf = new Dompdf($options);
	$dompdf->loadHtml($html);
	$dompdf->set_option("isPhpEnabled", true);

	$dompdf->set_option('debugPng', true);
	$dompdf->setPaper('letter', 'portrait');
	$dompdf->render();

	$canvas = $dompdf->get_canvas();
	$font = 'default_font';
    $size = 8;
    $y = $canvas->get_height() - 22;
    $x = $canvas->get_width() - 50;
	$canvas->page_text($x, $y, "Página {PAGE_NUM}", $font, $size);
	$canvas->page_text(20, 805, "Versión del comprobante: 4.0", null, 8, array(0, 0, 0));
	
	
	
	
	

	//Attachment" => false -- Para que no se descargue automaticamente
	$dompdf->stream("NC".$nc_XFolio."=".$cfdSerie."-".$cfdFolio.".pdf",["Attachment" => true]);

	$file_path = "C:/xampp/htdocs/XML_".$prefijo."/NC".$nc_XFolio."=".$cfdSerie."-".$cfdFolio.".pdf";
	file_put_contents($file_path, $dompdf->output());

//  **Forzar la descarga en la computadora del cliente**
	header('Content-Type: application/pdf');
	header("Content-Disposition: attachment; filename=NC".$nc_XFolio."=".$cfdSerie."-".$cfdFolio.".pdf");
	echo file_get_contents($file_path);
		
	

//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703 
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703


?>