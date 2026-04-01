<?php
ini_set('memory_limit', '1024M');
set_time_limit(200);

header('Content-Type: text/html; charset=UTF-8');
//if (ob_get_level() == 0) ob_start();

require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");

require_once('cnx_cfdi3.php');
if ($cnx_cfdi3->connect_error) {
	die('Error de conexión a la base de datos.');
}

//======================================================================
//Verifico que vengan todos los parametros y que ninguno sea vacio

if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die("Falta id de la factura");
}
if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    die("Falta el prefijo de la base de datos");
}

$prefijobd = $_REQUEST["prefijo"];
$iddocumento = $_REQUEST["id"];
$tipodoc     = 'mail';

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");
if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 
$tabla = $cnx_cfdi3->real_escape_string($prefijobd . 'systemsettings');
    	$sqlSystems = "SELECT OutgoingEmailHost, OutgoingEmailUserName, OutgoingEmailPassword, OutgoingEmailPort, OutgoingEmailFromAddress, xmldir, Servidor FROM `$tabla`";
    	$resSQL001 = $cnx_cfdi3->prepare($sqlSystems); 
    	if (!$resSQL001) {
        	die("Error en la preparacion de la consultaMail: " . $cnx_cfdi3->error);        
    	}

    	if (!$resSQL001->execute()) {
	    	$mensaje  = 'Consulta no valida: ' . $resSQL001->error . "\n";
        	die($mensaje);
    	}	
    	$resSQL001->store_result();
    	$resSQL001->bind_result($v_host, $v_username, $v_pass, $v_port, $v_mail_from, $v_xmldir, $v_servidor);
    	$resSQL001->fetch();

$tabla = "{$prefijobd}systemsettings";
$columna = "DirPHPPDF";

$checkColumnSQL = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND TABLE_SCHEMA = DATABASE()";
$stmt = $cnx_cfdi3->prepare($checkColumnSQL);
$stmt->bind_param("ss", $tabla, $columna);
$stmt->execute();
$stmt->bind_result($columnaExiste);
$stmt->fetch();
$stmt->close();

if (!$columnaExiste) {
    
    $DirPHPPDF = '0';
} else {
  
    $sqlSystems = "SELECT DirPHPPDF FROM {$tabla} LIMIT 1";
    $resSQL00 = $cnx_cfdi3->prepare($sqlSystems);
    $resSQL00->execute();
    $resSQL00->bind_result($DirPHPPDF);
    $resSQL00->fetch();
    $resSQL00->close();

    // Por si acaso, si viene NULL o vacío, fuerza a '0'
    if ($DirPHPPDF === null || $DirPHPPDF === '') {
        $DirPHPPDF = '0';
    }
}

//die ($DirPHPPDF);

if ($DirPHPPDF !== '1') {

	//======================================================================
	// Se define el nombre del archivo bat.
	$nombrebat = "facturapdf1_CPT.bat";

	//Configuro el valor que voy a buscar de la tabla de parametros
	//-------------------------MODIFICAR--------------------------------//
	$idnombrereporte = 122;

	//Verifico que vengan todos los parametros y que ninguno sea vacio

	if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    	die("Falta id de la factura");
	}
	if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    	die("Falta el prefijo de la base de datos");
	}

	$prefijobd = $_REQUEST["prefijo"];

	//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
	$pos = strpos($prefijobd, "_");

	if ($pos === false) {
    	$prefijobd = $prefijobd . "_";
	} 

	//Realizo la conexion a la base de datos
	include("cnx_cfdi2.php");

	//Selecciono la base de datos
	mysqli_select_db($cnx_cfdi2, $database_cfdi);

	//Obtengo el nombre del reporte que se le enviara al bat
	$qryreporte = "SELECT VCHAR FROM " . $prefijobd . "parametro WHERE id2 = " . $idnombrereporte;

	if ($debug == 1) {
		echo $qryreporte;
	}

	$resultqryreporte = mysqli_query($cnx_cfdi2, $qryreporte);

	if (!$resultqryreporte) {
    	die('Nombre de reporte no encontrado: ' . mysqli_error($cnx_cfdi2));
	}

	$rowreporte = mysqli_fetch_row($resultqryreporte);
	$nombrereporte = $rowreporte[0];

	$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$nombrebat." ".$_REQUEST["id"]." ".$nombrereporte." ".$prefijobd);

}else{

	//Buscar nombre del archivo XML 
	$sqlFactura = "SELECT a.cfdserie, a.cfdfolio, b.RazonSocial, b.CorreoFactura
               FROM {$prefijobd}factura a
               INNER JOIN {$prefijobd}clientes b ON a.CargoAFactura_RID = b.ID
               WHERE a.id = $iddocumento";
		$resSQLFac = $cnx_cfdi3->prepare($sqlFactura);
		$resSQLFac->execute();
		$resSQLFac->store_result();
		$resSQLFac->bind_result($serie, $folio, $razonSocial, $correoCliente);
		$resSQLFac->fetch();

		// Ahora arma la ruta directo con xmldir
		$pdfFilename =  $serie.'-'.$folio . ".pdf";
		$xmlFilename =  $serie.'-'.$folio . ".xml";

		$filePDF  = "C:/xampp/htdocs{$v_xmldir}/" . $pdfFilename;
		$filePath = "C:/xampp/htdocs{$v_xmldir}/" . $xmlFilename;

	/* var_dump($filePDF);
	echo "Buscando PDF en: $filePDF<br>";
	echo "Buscando XML en: $filePath<br>"; */
	//var_dump ($filePDF);

	$existePDF = '';
	if (file_exists($filePDF)) {
				echo "<script>
			alert('⏳ Generando tu PDF, por favor espera…');
		</script>";

    	$curl = curl_init();
    	curl_setopt_array($curl, [
    		CURLOPT_URL => "http://localhost/cfdipro/factura_formato.php?id={$iddocumento}&prefijodb={$prefijobd}&tipo={$tipodoc}",
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_TIMEOUT => 60
    	]);
    
   		$response = curl_exec($curl);
    	if (curl_errno($curl)) {
        	die("Error generando PDF/XML: " . curl_error($curl));   
    	}
    	curl_close($curl);

    	if (file_exists($filePDF)) {
        	//echo "⏳ Enviando por correo tu PDF, por favor espera…<br>";
        	$existePDF = '1';
    	}else{
        	echo "No existe el PDF<br>";
    	}
	}else{
		echo "<script>
			alert('⏳ Generando tu PDF, por favor espera…');
		</script>";

    	$curl = curl_init();
    	curl_setopt_array($curl, [
    		CURLOPT_URL => "http://localhost/cfdipro/factura_formato.php?id={$iddocumento}&prefijodb={$prefijobd}&tipo={$tipodoc}",
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_TIMEOUT => 60
    	]);
    
   		$response = curl_exec($curl);
    	if (curl_errno($curl)) {
        	die("Error generando PDF/XML: " . curl_error($curl));   
    	}
    	curl_close($curl);

    	if (file_exists($filePDF)) {
        	//echo "⏳ Enviando por correo tu PDF, por favor espera…<br>";
        	$existePDF = '1';
    	}else{
        	echo "No existe el PDF<br>";
    	}
	}

	if ($existePDF === '1'){
    	//Buscar datos de correo que envìa notificaión
    	$tabla = $cnx_cfdi3->real_escape_string($prefijobd . 'systemsettings');
    	$sqlSystems = "SELECT OutgoingEmailHost, OutgoingEmailUserName, OutgoingEmailPassword, OutgoingEmailPort, OutgoingEmailFromAddress, xmldir, Servidor FROM `$tabla`";
    	$resSQL001 = $cnx_cfdi3->prepare($sqlSystems); 
    	if (!$resSQL001) {
        	die("Error en la preparacion de la consultaMail: " . $cnx_cfdi3->error);        
    	}

    	if (!$resSQL001->execute()) {
	    	$mensaje  = 'Consulta no valida: ' . $resSQL001->error . "\n";
        	die($mensaje);
    	}	
    	$resSQL001->store_result();
    	$resSQL001->bind_result($v_host, $v_username, $v_pass, $v_port, $v_mail_from, $v_xmldir, $v_servidor);
    	$resSQL001->fetch();

    	//Busco Memo en el parametro 108 
    	$sqlParametro = "SELECT Memo FROM {$prefijobd}parametro where id2='108'";
    	$resSQLPara = $cnx_cfdi3->prepare($sqlParametro); 
    	if (!$resSQLPara) {
        	die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    	}

    	if (!$resSQLPara->execute()) {
        	$mensaje  = 'Consulta no valida: ' . $resSQLPara->error . "\n";
        	die($mensaje);
    	}	
    	$resSQLPara->store_result();
    	$resSQLPara->bind_result($memo);
    	$resSQLPara->fetch();

    	if ($memo===''){
        	$memo = 'Se anexa PDF y XML';       
		}

    	//Busco Xfolio y Razón Social cliente para poder enviar los datos en el correo.
    	$sqlFactura = "SELECT a.Xfolio, b.RazonSocial, b.CorreoFactura FROM {$prefijobd}factura a Inner Join {$prefijobd}clientes b On a.CargoAFactura_RID=b.ID where a.id=$iddocumento";
    	$resSQLFac = $cnx_cfdi3->prepare($sqlFactura); 
    	if (!$resSQLFac) {
        	die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    	}

    	if (!$resSQLFac->execute()) {
        	$mensaje  = 'Consulta no valida: ' . $resSQLFac->error . "\n";
        	die($mensaje);  
    	}	
    	$resSQLFac->store_result();
    	$resSQLFac->bind_result($xfolio, $razonSocial, $correoCliente);
    	$resSQLFac->fetch();

    	$pdfFilename = basename($filePDF);
    	$xmlFilename = basename($filePath);

		$filePDF  = "C:/xampp/htdocs/{$v_xmldir}/" . $pdfFilename;
		$filePath = "C:/xampp/htdocs/{$v_xmldir}/" . $xmlFilename;

    	// Comprobaciones de existencia
    	if (!file_exists($filePDF)) {
        	die("El archivo PDF no existe: $filePDF");
    	}
    	if (!file_exists($filePath)) {
        	die("El archivo XML no existe: $filePath");
    	}

    	$mail = new PHPMailer(true);

    	try {
        	// Configura el servidor SMTP
        	$v_port = '465'; //TEMPORAL
        	$mail->CharSet = 'UTF-8';
        	$mail->isSMTP();                                      // Set mailer to use SMTP
        	$mail->Host = $v_host;  					  // Specify main and backup SMTP servers
        	$mail->SMTPAuth = true;                               // Enable SMTP authentication
        	$mail->Username = $v_username;                 // SMTP username
        	$mail->Password = $v_pass;                           // SMTP password
        	$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        	$mail->Port = $v_port;                                    // TCP port to connect to
        	$mail->IsHTML(true);
			$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				);

        	$mail->setFrom($v_mail_from, 'Tractosoft'); //REMITENTE

        	$array_correos = explode(";", $correoCliente);
        	$no_correos = count($array_correos);
        	$x=0;
        	while($x < $no_correos){
            	$mail->addAddress($array_correos[$x]);
            	$x = $x +1;
        	}

        	$mail->Subject = 'Tractosoft WEB Factura: ' .$xfolio;

        	$memo = str_replace('#Cliente# - #ClienteNo#', $razonSocial, $memo);
        	$memo = str_replace('#Factura#', $xfolio, $memo);

        	$mail->Body = $memo;

        	// Adjuntamos usando las variables corregidas:
        	$mail->addAttachment($filePDF, $pdfFilename);
        	$mail->addAttachment($filePath, $xmlFilename);

        	$mail->send();

        	echo "<div style='padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:4px;'>
        	✅ Correo enviado correctamente a $correoCliente
        	</div>";

        	echo "<script>
            	alert('✅ Correo enviado correctamente');
        	</script>";
    	}

    	catch (Exception $e) {
			/* 		var_dump([
			'host' => $v_host,
			'username' => $v_username,
			'password' => $v_pass,
			'from' => $v_mail_from
		]); */
        	http_response_code(500);
        	echo 'Excepción PHPMailer: ' . $e->getMessage() . '<br>';
        	echo 'Detalles del error SMTP: ' . $mail->ErrorInfo;
    	}
	}
}
?>