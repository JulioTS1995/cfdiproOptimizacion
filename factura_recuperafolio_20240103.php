<?php
/* NombreArchivo.php:
 * 
 * Recibe:
 * 	Id del registro
 * 	Instancia de la base de datos - prefijo
 * 	Numero de reporte - numreporte
 */

require_once('cnx_cfdi2.php');
require_once('cnx_cfdi.php');

//======================================================================
// Se define el nombre del archivo bat.
$nombrebat = "factura_recuperafolio.bat";

//======================================================================
//Verifico que vengan todos los parametros y que ninguno sea vacio

if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die("Falta id de la factura");
}
if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    die("Falta el prefijo de la base de datos");
}

$prefijobd = $_REQUEST["prefijo"];
$idfactura = $_REQUEST['id'];


//======================================================================
// Inicializar valores de variables
$Vserie = "";
$Vfolio = "";
$VanualAprobacion = "";
$VnoAprobacion = "";
$VnoCertificado = ""; 
$VcbbDatos = "";
$VcbbArchivo = "";
$Vversion = "1.1";
$Vuuid = "";
$VfechaTimbrado = "";
$Vsellocfd = "";
$VnoCertificadoSat = "";
$VselloSat = "";
$VselloCadenaOriginal = "";
$Vcadenaoriginal = ""; 
$Vsello = "";
$VcfdFecha = "";
$VcfdFechaHora = "";
$VcodigoError = "";
$VmensajeError = "";

//======================================================================
//Traer ruta de archivo INI

//======================================================================
//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

//=====================================================================================
//Busca la ruta donde esta guardando los archivos INI    
$resSQL00 = "Select vchar From basdb.".$prefijobd."parametro Where id2='103'";
$runSQL00 = mysqli_query($cnx_cfdi2, $resSQL00);
while($rowSQL00 = mysqli_fetch_array($runSQL00)){
	$RutaIni = $rowSQL00['vchar'];
}

//Ruta para buscar INIS
$RutaIni2 = substr_replace($RutaIni, '/', 2) . substr($RutaIni, 3);

//Ruta para buscar INIS respaldados
$RutaIniAnt = $RutaIni . '\\IniAnt\\';
//$RutaIniAnt = str_replace("\\",'//',$RutaIniAnt);
$RutaIniAntError = $RutaIniAnt;

//Ruta para respaldar INIS no procesados o tmp
$RutaRespaldosIni = 'C:/INITMP/'. substr($RutaIni, 3); 

//Buscar datos del INI en la tabla zzzlogtablas para revisar si ya se mando timbrar 
$resSQL0 = "SELECT * FROM basdb.".$prefijobd."zzzlogtablas WHERE ID='".$idfactura."';";
$runSQL0 = mysqli_query($cnx_cfdi2, $resSQL0);
$noregistros = intval(mysqli_num_rows($runSQL0));

if ($noregistros<1) {
	$html = '<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Recuperar Folio Tractosoft</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="progress" style="margin:100px">
			<div id="bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" 		style="width: 0%">
				<span class="sr-only">0% Complete</span>
			</div>
		</div> 
		';
	
	$html.="<script>
			var progreso = 0;
			var idIterval = setInterval(function(){
				progreso +=5;
	
			$('#bar').css('width', progreso + '%');
         
			//Si llegó a 100 elimino el interval
				if(progreso == 100){
					clearInterval(idIterval);
				}
			},1000);
			
		</script>
		
	
	<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js' integrity='sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe' crossorigin='anonymous'></script>
	</body>
	</html>
	";

	if (ob_get_level() == 0) ob_start();
	echo $html;
	print str_pad('',4096)."\n";
 
	ob_flush();
	flush();
	sleep(21);
}

	//Busca nuevamente datos del INI en la tabla zzzlogtablas para revisar si ya se mando timbrar 
	$resSQL05 = "SELECT * FROM basdb.".$prefijobd."zzzlogtablas WHERE ID='".$idfactura."';";
	$runSQL05 = mysqli_query($cnx_cfdi2, $resSQL05);	
	$noregistros = intval(mysqli_num_rows($runSQL05));
	if ($noregistros<1) {
		echo "<H2 align='center'>Error No. 101 Contacte a soporte.</H2>";
		die;
	}

//Busca el archivo INI porque si encontro registro en zzzlogtablas
$runSQL0 = mysqli_query($cnx_cfdi2, $resSQL0);
while($rowSQL0 = mysqli_fetch_array($runSQL0)){
	$archivobuscado = $rowSQL0['vchar'];
	$statustimbrado = $rowSQL0['vlogi'];
}
//echo 'Archivo Buscado '.$archivobuscado;
$RutaIniCorrecta = $archivobuscado;

//Asinga el nombre del INI a buscar en la carpeta de INIS respaldados
$RutaIniAnt = $RutaIniAnt . substr($archivobuscado, -30);

$procesado = '0';
$error = '0';
$xml = '0';

//Busca el archivo INI 
if (file_exists($archivobuscado)){
	//echo 'Carpeta INI Producción';
	$RutaIniCorrecta = $archivobuscado; //Encontro el archivo INI en la carpeta de producción
	$procesado = '1';
}else{
	if (file_exists($RutaIniAnt)){
		//echo 'Carpeta INI Respaldos';
		$RutaIniCorrecta = $RutaIniAnt; //Encontro el archivo INI en la carpeta de respaldos
		$procesado = '1';
	}else{
		//Busca el archivo INI con error
		//echo 'Busca el INI con error';
		$archivoerror = str_replace("GP", "GE", $archivobuscado);
		$RutaIniAntError = $RutaIniAntError . substr($archivoerror, -30);

		if (file_exists($archivoerror)){
			//echo 'Carpeta INI producción con error';
			$RutaIniCorrecta = $archivoerror; //Encontro el archivo INI con error en la carpeta de producción
			$error = '1';
		}else{
			if (file_exists($RutaIniAntError)){
				//echo 'Carpeta INI respaldos con error';
				$RutaIniCorrecta = $RutaIniAntError; //Encontro el archivo INI con error en la carpeta de respaldos
				$error = '1';
			}else{
				//echo 'Busca en forsedi si el archivo se timbro';
				//Busca si el archivo esta timbrado para recuperar desde el XML
				$resSQL06 = "SELECT * FROM basdb.".$prefijobd."systemsettings";
				$runSQL06 = mysqli_query($cnx_cfdi2, $resSQL06);
				while($rowSQL06 = mysqli_fetch_array($runSQL06)){
					$Rfc1 = $rowSQL06['RFC'];
					$Multi = $rowSQL06['MultiEmisor'];
				}
				$resSQL07 = "SELECT a.folio, b.seriefiscal, c.RFC as RfcMulti FROM basdb.".$prefijobd."factura a inner join basdb.".$prefijobd."oficinas b on a.Oficina_RID = b.id left join basdb.".$prefijobd."emisores c on a.Emisor_RID = c.id where a.id=".$idfactura."";
				$runSQL07 = mysqli_query($cnx_cfdi2, $resSQL07);
				while($rowSQL07 = mysqli_fetch_array($runSQL07)){
					$Rfc2 = $rowSQL07['RfcMulti'];
					$Folio = $rowSQL07['folio'];
					$Serie = $rowSQL07['seriefiscal'];
				}
				
				if ($Multi=='0'){
					$RfcEmisor = $Rfc1;
				}else{
					$RfcEmisor = $Rfc2;
				}
				
				//$resSQL08 = "SELECT * FROM tractosoftprb.documentos";
				$resSQL08 = "SELECT * FROM tractosoft.documentos WHERE rfc_emisor='".$RfcEmisor."' And serie='".$Serie."' and folio=".$Folio."";
				echo $resSQL08;
				
				$runSQL08 = mysqli_query($cnx_cfdi2, $resSQL08);	
				$bandtimbrado = intval(mysqli_num_rows($runSQL08));
				
				if ($bandtimbrado>0){
					$resSQL000 = "Select vchar From basdb.".$prefijobd."parametro Where id2='104'";
					$runSQL000 = mysqli_query($cnx_cfdi2, $resSQL000);
					while($rowSQL000 = mysqli_fetch_array($runSQL000)){
						$RutaXml = $rowSQL000['vchar'];
					}	
					
					$RutaBMP = $RutaXml;
					$RutaXml.= '\\'. $Serie.'-'.$Folio .'.xml';
					
					if (file_exists($RutaXml)){
						
						$xml1 = simplexml_load_file($RutaXml);
						$ns = $xml1->getNamespaces(true);
						$xml1->registerXPathNamespace('c', $ns['cfdi']);
						$xml1->registerXPathNamespace('t', $ns['tfd']);
						foreach ($xml1->xpath('//cfdi:Comprobante') as $Comprobante) {
							$foliofactura = $Comprobante['Folio'];
							$fechafactura = $Comprobante['Fecha'];
							$sellodigital = $Comprobante['Sello'];
							$seriefactura = $Comprobante['Serie'];
							$nocertificado = $Comprobante['NoCertificado'];
						}	
						
						foreach ($xml1->xpath('//t:TimbreFiscalDigital') as $Complemento) {
							$version = $Complemento['Version'];
							$uuid = $Complemento['UUID'];
							$fechatimbrado = $Complemento['FechaTimbrado'];
							$selloCFD = $Complemento['SelloCFD'];
							$nocertificadosat = $Complemento['NoCertificadoSAT'];
							$selloSAT = $Complemento['SelloSAT'];
						}
						
						$RutaBMP.= '\\'. $Serie.'-'.$Folio .'.bmp';
						
						//Actualizar datos del folio en base al XML 
						$begintrans = mysql_query("BEGIN", $cnx_cfdi);
						$resSQL09 = "UPDATE basdb.".$prefijobd."factura SET cfdfolio=".$foliofactura.", cfdfchhra='".$fechafactura."', cfdsellodigital='".$sellodigital."', cfdserie='".$seriefactura."', cfdnocertificado=".$nocertificado.", cfdiversion='".$version."', cfdiuuid='".$uuid."', cfdifechaTimbrado='".$fechatimbrado."', cfdiselloCFD='".$selloCFD."', cfdinoCertificadoSAT=".$nocertificadosat.", cfdiselloSAT='".$selloSAT."', cfdicbbArchivo='".$RutaBMP."' Where ID=".$idfactura.";"; 
	
						$resSQL09=utf8_encode($resSQL09);
						$band_upd_xml = mysql_query($resSQL09, $cnx_cfdi);
						
						if ($band_upd_xml) {
							//Se hizo el update sin problemas
							$endtrans = mysql_query("COMMIT", $cnx_cfdi);
							echo '<H3> La factura se recupero correctamente XML </H3> <br> <H3> Serie: </H3> <H3 style="color:#FF0000">', $seriefactura ,'</H3> <br> <H3> Folio: </H3> <H3 style="color:#FF0000">', $foliofactura, '</H3> <br> <H3> UUID: </H3> <H3 style="color:#FF0000">', $uuid , "</H3>";
							die;
						}else{
							$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
							$error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$resSQL09\n<br>";
							echo $error;
							echo "<H2 align='center'>Error No. 102 Contacte a soporte.</H2>";
							die;
						}
						
					} else {
						echo "<H2 align='center'>Error No. 103 Contacte a soporte.</H2>";
						die;
					}
					
				}else{
					$begintrans = mysql_query("BEGIN", $cnx_cfdi);
					$resSQL10 = "delete from basdb.".$prefijobd."zzzlogtablas Where id=".$idfactura."";
					$band_del_log = mysql_query($resSQL10, $cnx_cfdi);
					if ($band_del_log){
						$endtrans = mysql_query("COMMIT", $cnx_cfdi);
						echo "<H2 align='center'>Se recupero el folio vuelva a timbrar su factura</H2>";
						die;
					} else {
						$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
						$error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$resSQL10\n<br>";
						echo $error;
						echo "<H2 align='center'>Error No. 104 Contacte a soporte.</H2>";
						die;
					}
				}
			}
		}	
	}
}

//echo '	Procesado '. $procesado;
//echo '	Error '. $error;
 
if ($procesado=='1'){
	// Abriendo el archivo
	$archivo = fopen($RutaIniCorrecta, "r");

	// Recorremos todas las lineas del archivo para buscar el error
	while(!feof($archivo)){
		// Leyendo una linea
		$traer = "";
		$traer = fgets($archivo);
		
					
		// Imprimiendo una linea
		//echo nl2br($traer);

		if (strstr($traer, "serie")){
			//echo "Entre Serie";
			$Vserie = rtrim(substr($traer, 6));
			//echo "Serie=". $Vserie. "<br />";
			
		}
		if (strstr($traer, "folio")){
			$Vfolio = substr($traer, 6);
			//echo "Folio=". $Vfolio. "<br />";
		}
		
		if (strstr($traer, "anoAprobacion")){
			$VanualAprobacion = substr($traer, 14);
			//echo "AnualAprobacion=". $VanualAprobacion. "<br />";
		}
		
		if (strstr($traer, "noAprobacion")){
			$temporal = "";
			$temporal = substr($traer, 0, 12);
			if ($temporal=="noAprobacion"){
				$VnoAprobacion = substr($traer, 13);
				//echo "NoAprobacion=". $VnoAprobacion. "<br />";
			}
		}
		if (strstr($traer, "noCertificado")){
			$VnoCertificado = substr($traer, 14);
			//echo "NoCertificado=". $VnoCertificado. "<br />";
		}		
		if (strstr($traer, "cbbdatos")){
			$VcbbDatos = rtrim(substr($traer, 9));
			//echo "CbbDatos=". $VcbbDatos. "<br />";
		}		
		if (strstr($traer, "cbbarchivo")){
			$VcbbArchivo = rtrim(substr($traer, 11));
			$VcbbArchivo = str_replace("\\",'\\\\',$VcbbArchivo);
			//echo "CbbArchivo=". $VcbbArchivo;
		}		
		
		if (strstr($traer, "uuid")){
			$temporal = "";
			$temporal = substr($traer, 0, 5);
			if ($temporal=="uuid="){
				$Vuuid = rtrim(substr($traer, 5));
			}
			//echo "UUID=". $Vuuid. "<br />";
		}	
		
		if (strstr($traer, "fechaTimbrado")){
			$VfechaTimbrado = rtrim(substr($traer, 14));
			//echo "FechaTimbrado=". $VfechaTimbrado. "<br />";
		}	
		if (strstr($traer, "sellocfd")){
			$Vsellocfd = rtrim(substr($traer, 9));
			//echo "Sellocfd=". $Vsellocfd. "<br />";
		}	
		if (strstr($traer, "nocertificadosat")){
			$VnoCertificadoSat = substr($traer, 17);
			//echo "NoCertificadoSat=". $VnoCertificadoSat. "<br />";
		}	
		if (strstr($traer, "sellosat")){
			$VselloSat = rtrim(substr($traer, 9));
			//echo "SelloSAT=". $VselloSat. "<br />";
		}
		if (strstr($traer, "sellocadenaoriginal")){
			$VselloCadenaOriginal = rtrim(substr($traer, 20));
			//echo "SelloCadenaOriginal=". $VselloCadenaOriginal. "<br />";
		}
		if (strstr($traer, "cadenaOriginal")){
			$Vcadenaoriginal = rtrim(substr($traer, 15));
			$Vcadenaoriginal = str_replace("'","\'",$Vcadenaoriginal);
			//echo "CadenaOriginal=". $Vcadenaoriginal. "<br />";
		}
		if (strstr($traer, "sello")){
			$temporal = "";
			$temporal = substr($traer, 0, 6);
			if ($temporal=="sello="){
				$Vsello = rtrim(substr($traer, 6));
				//echo "Sello=". $Vsello. "<br />";
			}
		}
		if (strstr($traer, "fchCFD")){
			$VcfdFecha = rtrim(substr($traer, 7));
			$VfchCFD = substr($VcfdFecha, 6, 4)."-".substr($VcfdFecha, 3, 2)."-".substr($VcfdFecha, 0, 2);
			//echo "CfdFecha=". $VcfdFecha. "<br />";
			//echo "CfdFecha=". $VfchCFD. "<br />";
			
		}
		if (strstr($traer, "hraCFD")){
			$VcfdFechaHora = rtrim(substr($traer, 7));
			$VhrCFD= conversorSegundosHoras($VcfdFechaHora);
			//echo "CfdFechaHora=". $VhrCFD. "<br />";
		}

		$VcodigoError = '0';
		$VmensajeError = '';
	}

		$FechaHoraCFD = $VfchCFD . " " . $VhrCFD;
		//echo $FechaHoraCFD;
 
	// Cerrando el archivo
	fclose($archivo);

	//Actualizar BD 
	$begintrans = mysql_query("BEGIN", $cnx_cfdi);
	$resSQL1 = "UPDATE basdb.".$prefijobd."factura SET cfdfolio=".$Vfolio.", cfdfchhra='".$FechaHoraCFD."', cfdcadenaoriginal='".$Vcadenaoriginal."', cfdsellodigital='".$Vsello."', cfdserie='".$Vserie."', cfdanoaprobacion=".$VanualAprobacion.", cfdnoaprobacion=".$VnoAprobacion.", cfdnocertificado=".$VnoCertificado.", cfdcodigoerror=".$VcodigoError.", cfdmsgerror='".$VmensajeError."', cfdiversion='".$Vversion."', cfdiuuid='".$Vuuid."', cfdifechaTimbrado='".$VfechaTimbrado."', cfdiselloCFD='".$Vsellocfd."', cfdinoCertificadoSAT=".$VnoCertificadoSat.", cfdiselloSAT='".$VselloSat."', cfdiselloCadenaOriginal='".$VselloCadenaOriginal."', cfdicbbDatos='".$VcbbDatos."', cfdicbbArchivo='".$VcbbArchivo."' Where ID=".$idfactura.";"; 
	echo $resSQL1;
	
	$resSQL1=utf8_encode($resSQL1);
	
	$band_upd_timbrado = mysql_query($resSQL1, $cnx_cfdi);
							
	if ($band_upd_timbrado) {
		//Se hizo el update sin problemas
		$endtrans = mysql_query("COMMIT", $cnx_cfdi);
		echo '<H3> La factura se recupero correctamente </H3> <br> <H3> Serie: </H3> <H3 style="color:#FF0000">', $Vserie ,'</H3> <br> <H3> Folio: </H3> <H3 style="color:#FF0000">', $Vfolio, '</H3> <br> <H3> UUID: </H3> <H3 style="color:#FF0000">', $Vuuid , "</H3>";
		
	}else{
		$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
		//echo $resSQL1;
		 $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$resSQL1\n<br>";
		echo $error;
		echo "<H2 align='center'>Error No. 105 Contacte a soporte.</H2>";
		die;
	}
}

if ($error=='1'){
   
	//Buscar archivo con error 
	//$archivoerror = str_replace("GP", "GE", $archivobuscado);
	//$RutaIniAntError = $RutaIniAntError . substr($archivoerror, -30);

	//if (file_exists($archivoerror)){
	//	$RutaIniCorrecta = $archivoerror;
	//}else{
	//	if (file_exists($RutaIniAntError)){
	//		$RutaIniCorrecta = $RutaIniAntError;
	//	}else{
	//		echo "<H2 align='center'>No existe archivo INI para recuperar el folio. Contacte a Soporte. Error.</H2>";
	//		die;
	//	}
	//}	
	
		
	// Abriendo el archivo
	$archivo = fopen($RutaIniCorrecta, "r");
 
	// Recorremos todas las lineas del archivo para buscar el error
	while(!feof($archivo)){
		// Leyendo una linea
		$traer = fgets($archivo);
		$error = substr($traer, 7); 
			
		// Imprimiendo una linea
		//echo nl2br($traer);
			
		if (strstr($traer, "retmsj=")){
			$facerror = $error;
		}
	}
 
	// Cerrando el archivo
	fclose($archivo);

	//Busca que exista la carpeta de respaldo de INIS sino que la cree
	if (!file_exists($RutaRespaldosIni)) {
		mkdir($RutaRespaldosIni, 0777, true);
	}
		
	$RutaRespaldosIni = $RutaRespaldosIni . '/';
	//echo 'Ruta Respaldos '.$RutaRespaldosIni;
	//copy($from.'/'.$file, $to.'/'.$file);
		
	//Borra archivos INI temporales o no procesados
	$RutaIni2 = $RutaIni2 . '/*.ini';
		
	//echo 'RutaIni2 '. $RutaIni2;	
	$files = glob($RutaIni2); //obtenemos todos los nombres de los ficheros
		
	foreach($files as $file){
			
		if (strpos($file, '_tmp')) {
			rename ($file, $RutaRespaldosIni . substr($file, -27));
			//rename ($file, $RutaRespaldosIni);
			//echo $file;
			//unlink($file);
		}
			
		if (strpos($file, '_noprocesado')) {
			//echo ' '. substr($file, -35);
			rename ($file, $RutaRespaldosIni . substr($file, -35));
		}
	}
			
	//Actualiza el status a 0 en zzzlogtablas
	$begintrans = mysql_query("BEGIN", $cnx_cfdi);
	$resSQL2 = "UPDATE basdb.".$prefijobd."zzzlogtablas SET vlogi=0 Where id=".$idfactura.";"; 
	//die $resSQL1;
	
	$band_upd_log = mysql_query($resSQL2, $cnx_cfdi);
							
	if ($band_upd_log) {
		//Se hizo el update sin problemas
		$endtrans = mysql_query("COMMIT", $cnx_cfdi);
		echo '<H3>Se recupero el folio, no olvide corregir su error: </H3> <H3 style="color:#FF0000">', $facerror, "</H3><H3> Antes de volver a timbrar la factura</H3>";
			
		//echo "<H2> La factura se recupero correctamente </H3>";
	}else{
		$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
		//echo $resSQL1;
		$error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$resSQL2\n<br>";
		echo $error;
		echo "<H2 align='center'>Error No. 106 Contacte a soporte.</H2>";
		die;
	}
		
	
	//$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$nombrebat." ".$_REQUEST["id"]." ".$prefijobd);
	//echo "El archivo INI existe con error";
}

function conversorSegundosHoras($tiempo_en_segundos) {
    $horas = floor($tiempo_en_segundos / 3600);
    $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
    $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);

    return $horas . ':' . $minutos . ":" . $segundos;
}

//echo "<H2>Procesando...</H2>";

//Linea modificada:
//$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$nombrebat." ".$_REQUEST["id"]." ".$prefijobd);

//echo "<H2>Fin de proceso...</H2>";


?>

