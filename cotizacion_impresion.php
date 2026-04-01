<?php

ini_set('memory_limit', '2048M');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución


require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi2.php');

if (!isset($_GET['prefijobd']) || empty($_GET['prefijobd'])) {
    die("Falta el prefijo de la BD");
}


$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijobd"]);

$idCotizacion = $_GET["id"];
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

function convertir($numero, $moneda) {

    $num = str_replace(",", "", $numero);
    $num = number_format($num, 2, '.', '');
    $cents = substr($num, -2);
    $tempnum = explode('.', $num);
    $numf = millones((int)$tempnum[0]);



    $moneda = trim(strtoupper($moneda));
    $moneda = str_replace(["\t", "\n", "\r"], "", $moneda); 

   
    if ($moneda === "PESOS") {
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


// Consulta general 
$resSQL01 = "SELECT            
                    a.XFolio, 
                    a.Moneda,
                    a.Creado, 
                    a.Remitente, 
                    a.RemitenteIdClienteDestino,  
                    cr.NombreAsentamiento AS ColoniaRemitente, 
                    er.Estado AS EstadoRemitente, 
                    a.RemitentePoblacion, 
                    a.RemitenteRFC, 
                    a.RemitenteCP, 
                    lr.Descripcion AS LocalidadRemitente,
                    a.RemitenteCalle, 
                    rm.Descripcion AS MunicipioRemitente, 
                    a.RemitenteNumRegIdTrib,
                    a.RemitenteCitaCarga, 
                    a.RemitentePais,
                    a.RemitenteTelefono,
                    a.RemitenteNumExt, 
                    a.RemitenteNumInt, 
                    a.DestinatarioNumExt, 
                    a.DestinatarioNumInt,
                    a.DestinatarioTelefono, 
                    ld.Descripcion AS LocalidadDestinatario,
                    a.DestinatarioDireccion, 
                    cd.NombreAsentamiento AS ColoniaDestinatario, 
                    a.DestinatarioCalle,
                    a.DestinatarioPoblacion, 
                    a.DestinatarioNumRegIdTrib, 
                    a.DestinatarioCitaCarga,
                    a.DestinatarioPais, 
                    a.TipoOperacion,
                    a.Destinatario, 
                    ed.Estado AS EstadoDestinatario, 
                    a.DestinatarioIdClienteDestino,
                    dm.Descripcion AS MunicipioDestinatario, 
                    a.DestinatarioCP, 
                    a.DestinatarioRFC,
                    a.CantidadTarima, 
                    a.TotalCantidad, 
                    a.TamanoContenedor, 
                    a.DistanciaRecorrida, 
                    a.CantidadContenedor, 
                    a.PesoTara, 
                    a.CantidadCamion, 
                    a.ValorDeclarado, 
                    a.Asegurado, 
                    a.CantidadCaja, 
                    a.TipoEnvio,
                    a.TipoDeServicio, 
                    a.TipoCarga,
                    a.TotalImporte,        
                    a.MaterialPeligroso,
                    a.Comentarios,
                    c.RazonSocial  AS ClienteRazonSocial,
                    c.RFC          AS ClienteRFC,
                    c.Pais         AS ClientePais,
                    c.Calle        AS ClienteDomicilio,
                    mp.Descripcion AS MetodoPagoDEscripcion,
                    mp.ID2         AS MetodoPagoClave,
                    rf.clave       AS RegimenFiscalClave,
                    rf.Descripcion AS RegimenFiscalDescripcion,
                    uc.Descripcion AS UsoCFDIDescripcion,
                    uc.ID2         AS UsoCFDIClave,
                    fp.Descripcion AS FormaPagoDescripcion,
                    fp.ID2         AS FormaPagoClave,  
                    tc.Clase       AS TipoCamion_RID,
                    a.TipoContenedor_RID,
                    tco.Descripcion AS TipoContenedorDescripcion,
                    a.TipoMaterial_RID,
                    tm.Descripcion AS TipoMaterialDescripcion  
				FROM {$prefijobd}cotizaciones  a 
                LEFT JOIN {$prefijobd}c_municipio     rm ON a.RemitenteMunicipio_RID = rm.ID 
                LEFT JOIN {$prefijobd}c_municipio     dm ON a.DestinatarioMunicipio_RID = dm.ID 
                LEFT JOIN {$prefijobd}c_colonia       cr ON a.RemitenteColonia_RID = cr.ID
                LEFT JOIN {$prefijobd}c_colonia       cd ON a.DestinatarioColonia_RID = cd.ID
                LEFT JOIN {$prefijobd}c_localidad     lr ON a.RemitenteLocalidad_RID = lr.ID
                LEFT JOIN {$prefijobd}c_localidad     ld ON a.DestinatarioLocalidad_RID = ld.ID
                LEFT JOIN {$prefijobd}estados         er ON a.RemitenteEstado_RID = er.ID
                LEFT JOIN {$prefijobd}estados         ed ON a.DestinatarioEstado_RID = ed.ID 
                LEFT JOIN {$prefijobd}clientes        c  ON a.Cliente_RID = c.ID
                LEFT JOIN {$prefijobd}tablageneral    mp ON c.metodopago33_RID = mp.ID
                LEFT JOIN {$prefijobd}c_regimenfiscal rf ON c.cRegimenFiscal_RID = rf.ID
                LEFT JOIN {$prefijobd}tablageneral    uc ON c.usocfdi_RID = uc.ID
                LEFT JOIN {$prefijobd}tablageneral    fp ON c.formapago33_RID = fp.ID
                LEFT JOIN {$prefijobd}unidadesclase   tc ON a.TipoCamion_RID = tc.ID
                LEFT JOIN {$prefijobd}contenedor     tco ON a.TipoContenedor_RID = tco.ID
                LEFT JOIN {$prefijobd}material        tm ON a.TipoMaterial_RID = tm.ID
         WHERE a.id=".$idCotizacion;
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);
//die($resSQL01);
while($rowSQL01 = mysqli_fetch_array($runSQL01)){
    
    $XFolio = $rowSQL01['XFolio'];
    $moneda = $rowSQL01['Moneda'];
    $CreadoRaw = $rowSQL01['Creado'];
    $Creado = date("d/m/Y", strtotime($CreadoRaw));
    $Remitente = $rowSQL01['Remitente'];
    $RemitenteIdClienteDestino = $rowSQL01['RemitenteIdClienteDestino'];
    $ColoniaRemitente = $rowSQL01['ColoniaRemitente'];
    $EstadoRemitente = $rowSQL01['EstadoRemitente'];
    $RemitentePoblacion = $rowSQL01['RemitentePoblacion'];
    $RemitenteRFC = $rowSQL01['RemitenteRFC'];
    $RemitenteCP = $rowSQL01['RemitenteCP'];
    $LocalidadRemitente = $rowSQL01['LocalidadRemitente'];
    $RemitenteCalle = $rowSQL01['RemitenteCalle'];
    $MunicipioRemitente = $rowSQL01['MunicipioRemitente'];
    $RemitenteNumRegIdTrib = $rowSQL01['RemitenteNumRegIdTrib'];
    $RemitenteCitaCarga = $rowSQL01['RemitenteCitaCarga'];
    $RemitentePais = $rowSQL01['RemitentePais'];
    $RemitenteTelefono = $rowSQL01['RemitenteTelefono'];
    $DestinatarioTelefono = $rowSQL01['DestinatarioTelefono'];
    $LocalidadDestinatario = $rowSQL01['LocalidadDestinatario'];
    $DestinatarioDireccion = $rowSQL01['DestinatarioDireccion'];
    $ColoniaDestinatario = $rowSQL01['ColoniaDestinatario'];
    $DestinatarioCalle = $rowSQL01['DestinatarioCalle'];
    $DestinatarioPoblacion = $rowSQL01['DestinatarioPoblacion'];
    $DestinatarioNumRegIdTrib = $rowSQL01['DestinatarioNumRegIdTrib'];
    $DestinatarioCitaCarga = $rowSQL01['DestinatarioCitaCarga'];
    $DestinatarioPais = $rowSQL01['DestinatarioPais'];
    $TipoCamion_RID = $rowSQL01['TipoCamion_RID'];
    $TipoOperacion = $rowSQL01['TipoOperacion'];
    $Destinatario = $rowSQL01['Destinatario'];
    $EstadoDestinatario = $rowSQL01['EstadoDestinatario'];
    $DestinatarioIdClienteDestino = $rowSQL01['DestinatarioIdClienteDestino'];
    $MunicipioDestinatario = $rowSQL01['MunicipioDestinatario'];
    $DestinatarioCP = $rowSQL01['DestinatarioCP'];
    $DestinatarioRFC = $rowSQL01['DestinatarioRFC'];
    $CantidadTarima = $rowSQL01['CantidadTarima'];
    $TotalCantidad = $rowSQL01['TotalCantidad'];
    $TamanoContenedor = $rowSQL01['TamanoContenedor'];
    $DistanciaRecorrida = $rowSQL01['DistanciaRecorrida'];
    $CantidadContenedor = $rowSQL01['CantidadContenedor'];
    $PesoTara = $rowSQL01['PesoTara'];
    $CantidadCamion = $rowSQL01['CantidadCamion'];
    $Valor = $rowSQL01['ValorDeclarado'];
    $Asegurado = $rowSQL01['Asegurado'];
    $EstaAsegurado = ($Asegurado != 0 ) ? 'SI' : 'NO';
    $CantidadCaja = $rowSQL01['CantidadCaja'];
    $TipoEnvio = $rowSQL01['TipoEnvio'];
    $TipoDeServicio = $rowSQL01['TipoDeServicio'];
    $TipoCarga = $rowSQL01['TipoCarga'];
    $TotalImporte = $rowSQL01['TotalImporte'];
    $TotalImporteFloat = (floatval($rowSQL01['TotalImporte']));
    $MaterialPeligroso = $rowSQL01['MaterialPeligroso'];
    $EsMaterialPeligroso = ($MaterialPeligroso != 0 ) ? 'SI' : 'NO';
    $comentarios = $rowSQL01['Comentarios'];
    $ClienteDomicilio = $rowSQL01['ClienteDomicilio'];
    $ClientePais = $rowSQL01['ClientePais'];
    $ClienteRazonSocial = $rowSQL01['ClienteRazonSocial'];
    $ClienteRFC = $rowSQL01['ClienteRFC'];
    $MetodoPagoDEscripcion = $rowSQL01['MetodoPagoDEscripcion'];
    $MetodoPagoClave = $rowSQL01['MetodoPagoClave'];
    $metodoPago = $MetodoPagoClave . ' - ' . $MetodoPagoDEscripcion;
    $RegimenFiscalClave = $rowSQL01['RegimenFiscalClave'];
    $RegimenFiscalDescripcion = $rowSQL01['RegimenFiscalDescripcion'];
    $clienteRegimen = $rowSQL01['RegimenFiscalClave'] . ' - ' . $rowSQL01['RegimenFiscalDescripcion'];  
    $UsoCFDIDescripcion = $rowSQL01['UsoCFDIDescripcion'];
    $UsoCFDIClave = $rowSQL01['UsoCFDIClave'];
    $usoCfdi = $usoCFDIClave . ' - ' . $UsoCFDIDescripcion;
    $FormaPagoDescripcion = $rowSQL01['FormaPagoDescripcion'];
    $FormaPagoClave = $rowSQL01['FormaPagoClave'];
    $formaPago = $FormaPagoClave . ' - ' . $FormaPagoDescripcion;
    $totalLetra = convertir($TotalImporte, $moneda);
    $RemitenteNumExt = (!empty($rowSQL01['RemitenteNumInt'])) ? $rowSQL01['RemitenteNumExt'].' Int: '.$rowSQL01['RemitenteNumInt'] : $rowSQL01['RemitenteNumExt'];
    $DestinatarioNumExt = (!empty($rowSQL01['DestinatarioNumInt'])) ? $rowSQL01['DestinatarioNumExt'].' Int: '.$rowSQL01['DestinatarioNumInt'] : $rowSQL01['DestinatarioNumExt'];
    $TipoContenedor = $rowSQL01['TipoContenedorDescripcion'];
    $TipoMaterial = $rowSQL01['TipoMaterialDescripcion'];


}

//Consulta embalajes
$resSQL02 = "SELECT 
        a.Cantidad,
        a.Descripcion,  
        a.Ancho,  
        a.UnidadPeso_RID,  
        a.Largo,  
        a.Peso, 
        a.Alto, 
        a.Importe, 
        b.ClaveUnidad
 FROM {$prefijobd}cotizacionessub a
 LEFT JOIN {$prefijobd}c_ClaveUnidadPeso b ON a.UnidadPeso_RID = b.ID
 WHERE FolioSub_RID = {$idCotizacion}";
$runSQL02 = mysqli_query($cnx_cfdi2 , $resSQL02);


//parametros de color
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

if ($moneda === "PESOS") {
					$nomMoneda = "MXN - PESOS";
				} else {
					$nomMoneda = "<b>USD - DÓLARES<br>";
				}

function totalizadorIngreso($TotalImporte, $estilo_fondo, $usoCfdi, $metodoPago, $totalLetra, $formaPago, $nomMoneda) {
	
	echo '<div>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;'.$estilo_fondo.'"><b>Total con Letra
					</b></td>
					<td style="text-align:left; width:20%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Total:</b></td>
					<td style="text-align:center; width:10%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"> $ '.number_format($TotalImporte,2,'.',',').'</td>
				</tr>
				<tr>
					<td style="text-align:center; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b> '.$totalLetra.' </b></td>			
					<td style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"></td>
					<td style="text-align:center; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"></td>
				</tr>
				<tr>
					<td style="text-align:left; width:70%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>Uso CFDI : </b> '.$usoCfdi.'</td>
					<td colspan="2" style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"></td>

				</tr>			
				<tr>
					<td style="text-align:left; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;">
                            <b>Metodo de Pago: </b> '.$metodoPago.'<br>
                            <b>Forma de Pago: </b> '.$formaPago.' <br>
                             <b>'.$nomMoneda.'</b></td>
					<td colspan="2" style="text-align:left; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"></td>
				</tr>
					
			</table>
		  </div>
          
          ';
	}

$datos_cliente = array(
	array('Cliente ', $ClienteRazonSocial),
	array('RFC ', $ClienteRFC),
	array('Domicilio ', $ClienteDomicilio),
	array('Pais ', $ClientePais),
	array('Régimen Fiscal ', $clienteRegimen),
	
);
function celda($label, $valor) {
	if (trim($valor) !== '') {
		return '<td style="text-align:left; font-family: Helvetica; font-size: 12px; width:33%;"><b>' . $label . ':</b> ' . $valor .  '</td>  ';
	}
	return '<td></td>';
}

function imprimirTablaCliente($datos_cliente, $estilo_fondo) {

	// Filtrar elementos nulos (por condiciones) y reindexar
	$datosFiltrados = array();
	foreach ($datos_cliente as $item) {
		if ($item !== null) {
			$datosFiltrados[] = $item;
		}
	}

	// Imprimir la tabla en filas de 3 columnas
	echo '<table border="0" style="margin:0; border-collapse: collapse; padding-top:-10px; border: 1px solid rgba(128, 128, 128, 0.5);" width="100%">
                        <thead>
							<tr >
								<td colspan="4" style="text-align:center; width:100%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;'.$estilo_fondo.'">
								Cliente
								
								</td>
							
							</tr>
                        </thead> <tbody>';
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

function concatenaRfcYRegId ($rfcExtranjero, $RemitenteRFC, $RemitenteNumRegIdTrib, $DestinatarioRFC, $DestinatarioNumRegIdTrib){
	  $fRfcCompletoRemitente = ($RemitenteRFC === $rfcExtranjero)
        ? $RemitenteRFC . ' Id. Tributaria: ' . $RemitenteNumRegIdTrib
        : $RemitenteRFC;

    $fRfcCompletoDestinatario = ($DestinatarioRFC === $rfcExtranjero)
        ? $DestinatarioRFC . ' Id. Tributaria: ' . $DestinatarioNumRegIdTrib
        : $DestinatarioRFC;

    return [
        'remitente' => $fRfcCompletoRemitente,
        'destinatario' => $fRfcCompletoDestinatario
    ];
}

$rfcCompletos = concatenaRfcYRegId ($rfcExtranjero, $RemitenteRFC, $RemitenteNumRegIdTrib, $DestinatarioRFC, $DestinatarioNumRegIdTrib);

$fRfcCompletoRemitente = $rfcCompletos['remitente'];
$fRfcCompletoDestinatario = $rfcCompletos['destinatario'];

$datos_domicilios = [
   'estilo_fondo' => $estilo_fondo,
   'remitente_localidad_nombre' => $LocalidadRemitente,
   'destinatario_localidad_nombre' => $LocalidadDestinatario,
   'f_codigoorigen' => $RemitenteIdClienteDestino,
   'f_remitente' => $Remitente,
   'f_remitente_rfc' => $fRfcCompletoRemitente,
   'f_remitente_calle' => $RemitenteCalle,
   'f_remitente_numext' => $RemitenteNumExt,
   'remitente_colonia_nombre' => $ColoniaRemitente,
   'remitente_municipio_nombre' => $MunicipioRemitente,
   'remitente_estado_nombre' => $EstadoRemitente,
   'f_remitente_cp' => $RemitenteCP,
   'f_remitente_pais' => $RemitentePais,
   'f_citacarga' => $RemitenteCitaCarga,
   'f_codigodestino' => $DestinatarioIdClienteDestino,
   'f_destinatario' => $Destinatario,
   'f_destinatario_rfc' => $fRfcCompletoDestinatario,
   'f_destinatario_calle' => $DestinatarioCalle,
   'f_destinatario_numext' => $DestinatarioNumExt,
   'destinatario_colonia_nombre' => $ColoniaDestinatario,
   'destinatario_municipio_nombre' => $MunicipioDestinatario,
   'destinatario_estado_nombre' => $EstadoDestinatario,
   'f_destinatario_cp' => $DestinatarioCP,
   'f_destinatario_pais' => $DestinatarioPais,
   'f_destinatario_citacarga' => $DestinatarioCitaCarga,
   'f_DistanciaRecorrida' => $DistanciaRecorrida,
   'remitenteTelefono' => $RemitenteTelefono,
   'destinatarioTelefono' => $DestinatarioTelefono

];

function domicilios($datos_domicilios){
	
		
			echo'<div>
			<table border="1" style="table-layout: fixed; width:100%; border-collapse: collapse; margin:0;">
				<thead>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Origen - '.$datos_domicilios ['remitente_localidad_nombre'].'</b>
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px; padding-bottom: 0px; vertical-align:top; '.$datos_domicilios ['estilo_fondo'].'">
							<b>Destino - '.$datos_domicilios ['destinatario_localidad_nombre'].'</b>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Origen: '.$datos_domicilios ['f_codigoorigen'].'<br>
							Razón Social: '.$datos_domicilios ['f_remitente'].'<br>
							RFC: '.$datos_domicilios ['f_remitente_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_remitente_calle'].' No.'.$datos_domicilios ['f_remitente_numext'].'<br>
							'.'Col.'.$datos_domicilios ['remitente_colonia_nombre'].', '.$datos_domicilios ['remitente_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['remitente_estado_nombre'].' C.P.'.$datos_domicilios ['f_remitente_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_remitente_pais'].'<br>
							Fecha de Salida: '.$datos_domicilios ['f_citacarga'].'<br>';
							if (!empty($datos_domicilios ['remitenteTelefono'])) {
								echo'
							Teléfono: '.$datos_domicilios ['remitenteTelefono'].'<br>
							';
							}
					echo'
						</td>
						<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px; padding-bottom: 0px; vertical-align:top; word-wrap: break-word; overflow-wrap: break-word;">
							Código Destino: '.$datos_domicilios ['f_codigodestino'].'<br>
							Razón Social: '.$datos_domicilios ['f_destinatario'].'<br>
							RFC: '.$datos_domicilios ['f_destinatario_rfc'].'<br>
							Domicilio: '.$datos_domicilios ['f_destinatario_calle'].' No.'.$datos_domicilios ['f_destinatario_numext'].'<br>
							'.'Col.'.$datos_domicilios ['destinatario_colonia_nombre'].', '.$datos_domicilios ['destinatario_municipio_nombre'].','.'<br>
							'.$datos_domicilios ['destinatario_estado_nombre'].' C.P.'.$datos_domicilios ['f_destinatario_cp'].'<br>
							Residencia Fiscal: '.$datos_domicilios ['f_destinatario_pais'].'<br>
							Fecha de Llegada: '.$datos_domicilios ['f_destinatario_citacarga'].'<br>';
							if (!empty($datos_domicilios ['destinatarioTelefono'])) {
								echo'
							Teléfono: '.$datos_domicilios ['destinatarioTelefono'].'<br>
							';
							}
					echo'
							Distancia a Recorrer: '.$datos_domicilios ['f_DistanciaRecorrida'].' Km <br>
						</td>
					</tr>
				</tbody>
			</table>
		</div>';
						
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
		
		
		
		<title>Cotizacion<?php echo ': '.$XFolio ;?></title>	
	</head>
	<body>
	<htmlpageheader name="myHeader">
			<div style = "padding-top: -10px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> width="130px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-family: Helvetica; font-size: 12px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-family: Helvetica; font-size: 12px;padding-bottom: 0px;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-family: Helvetica; font-size: 14px;padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?>"><b>Cotizacion</b></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Folio</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><label style="color:red"><b style="color:red"><?php echo $XFolio; ?></b></label></td>
								</tr>
								<tr>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><b>Fecha</b></td>
									<td style="text-align:left; width:50%; font-family: Helvetica; font-size: 12px;padding: 1;vertical-align: center;"><?php echo $Creado; ?></td>
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
                    <table border="1" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td style="text-align:center; width:100%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;<?php echo $estilo_fondo;?>"><b>Comentarios
                            </b></td>                           
                        </tr>
                        <tr>
                            <td style="text-align:center; width:100%; font-family: Helvetica; font-size: 11px;padding: 1;vertical-align: center;"><b>&nbsp;<?php echo $comentarios;?><br>&nbsp;</b></td>  
                        </tr>
                    </table>
                </div>
            </htmlpagefooter>
            <sethtmlpagefooter name="myFooter" value="on" />

    <main>
        <?php
        imprimirTablaCliente($datos_cliente, $estilo_fondo);
        domicilios($datos_domicilios);
        ?>
    <br>
			<div>
				<table border="0" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
					<tr>
						<td style="text-align:center; width:100%; font-family: Helvetica; font-size: 14px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="5">
							<b>Detalle de la Cotizacion</b>
						</td>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px; vertical-align:center;">
							<b>Tipo de Carga</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Tipo de Operacion</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Tipo de Envio</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Cantidad en Tarima</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Cantidad de Caja</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
								<?php echo $TipoCarga; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $TipoOperacion; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $TipoEnvio; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $CantidadTarima; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $CantidadCaja; ?>
						</td>
					</tr>
                    <tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px; vertical-align:center;">
							<b>Tipo de Camion</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Cantidad de Camion</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Tipo de Contendor</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Cantidad de Contenedor</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Tamaño de Contendor</b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
								<?php echo $TipoCamion_RID; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $CantidadCamion; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $TipoContenedor; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $CantidadContenedor; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $TamanoContenedor; ?>
						</td>
					</tr>
                    <tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px; vertical-align:center;">
							<b>Tipo de Material</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Material Peligroso</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Valor Declarado</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b>Asegurado</b>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<b> </b>
						</td>
					</tr>
					<tr>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
								<?php echo $TipoMaterial; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $EsMaterialPeligroso; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $Valor; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							<?php echo $EstaAsegurado; ?>
						</td>
						<td style="text-align:center; width:20%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
							
						</td>
					</tr>

					</tbody>
					
				</table>
			</div>
            <br>
            	<div>
				<table border="1" style="margin:0;border-collapse: collapse;" width="100%">
					<thead>
                        <tr>
                            <td style="text-align:center; width:100%; font-family: Helvetica; font-size: 14px;vertical-align:center;<?php echo $estilo_fondo; ?>" colspan="8">
                                <b>Detalle de Mercancia</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px; vertical-align:center;">
                                <b>Cantidad</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Descripcion</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Unidad Peso</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Largo</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Ancho</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Alto</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Peso</b>
                            </td>
                            <td style="text-align:center; width:12%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                <b>Importe</b>
                            </td>
                        </tr>
					</thead>
					<tbody>
                        <?php 
                            while ($rowSQL02 = mysqli_fetch_assoc($runSQL02)) {
                                $subCantidad = $rowSQL02['Cantidad'];
                                $subDescripcion = $rowSQL02['Descripcion'];
                                $subUnidadPeso = $rowSQL02['ClaveUnidad'];
                                $subLargo = $rowSQL02['Largo'];
                                $subAncho = $rowSQL02['Ancho'];
                                $subAlto = $rowSQL02['Alto'];
                                $subPeso = $rowSQL02['Peso'];
                                $subImporte = $rowSQL02['Importe'];

                                ?>
                                <tr>
                                    <td style="text-align:center; width:7%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subCantidad; ?>
                                    </td>
                                    <td style="text-align:center; width:31%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subDescripcion; ?>
                                    </td>
                                    <td style="text-align:center; width:21%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subUnidadPeso; ?>
                                    </td>
                                    <td style="text-align:center; width:7%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subLargo; ?>
                                    </td>
                                    <td style="text-align:center; width:7%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subAncho; ?>
                                    </td>
                                    <td style="text-align:center; width:7%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subAlto; ?>
                                    </td>
                                    <td style="text-align:center; width:10%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subPeso; ?>
                                    </td>
                                    <td style="text-align:center; width:10%; font-family: Helvetica; font-size: 14px;vertical-align:center;">
                                        <?php echo $subImporte; ?>
                                    </td>
                          <?php      
                            }
                        ?>
                    </tbody>
                </table>

            <?php
            totalizadorIngreso($TotalImporte, $estilo_fondo, $usoCfdi, $metodoPago, $totalLetra, $formaPago, $nomMoneda);
            ?>


    </main>
    </body>
    </html>
<?php
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();
$mpdf = new mPDF('utf-8', 'letter');

$mpdf->SetFont('helvetica');


$mpdf->WriteHTML($html);
$nombre_pdf = $prefijo . "-".$XFolio;

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

header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"{$nombre_pdf}.pdf\"");
readfile($file_path);

exit;
?>
