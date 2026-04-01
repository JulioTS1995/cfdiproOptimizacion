<?php
	set_time_limit(350);
	require_once('cnx_cfdi2.php');
	require('PHPMailer/PHPMailerAutoload.php');
	require("PHPMailer/class.phpmailer.php");
	require("PHPMailer/class.smtp.php");
  	mysqli_select_db($cnx_cfdi2, $database_cfdi);

    //if(isset($_POST['base']))
    //    $base = $_POST['base'];
    $rutaarchivo = "";
    $vlprefijodb = "demo";
	
	
	if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die("Falta id de la factura");
	}
	if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
		die("Falta el prefijo de la base de datos");
	}
	if (!isset($_REQUEST['prefijo2']) || empty($_REQUEST['prefijo2'])) {
		die("Falta el prefijo de Pago o Nota de Credito");
	}

	$v_id_abono = $_REQUEST['id'];
	$vlprefijodb = $_REQUEST['prefijo'];
	$v_prefijo2 = $_REQUEST['prefijo2'];

	
			//Buscar id del cliente del Abono
			$resSQL2="SELECT * FROM ".$vlprefijodb."abonos WHERE ID = ".$v_id_abono;			
    		$runSQL2 = mysqli_query($cnx_cfdi2, $resSQL2);
    		while($rowSQL2 = mysqli_fetch_assoc($runSQL2)){
    			$id_cliente = $rowSQL2['Cliente_RID'];
				$v_xfolio = $rowSQL2['XFolio'];
				$v_cfdserie = $rowSQL2['cfdserie'];
				$v_cfdfolio = $rowSQL2['cfdfolio'];
				
			}
			
			//echo $resSQL2;
			
			
			//Buscar correo destinatario
			$resSQL3 = "SELECT * FROM ".$vlprefijodb."clientes WHERE ID = ".$id_cliente;
	    	$runSQL3 = mysqli_query($cnx_cfdi2, $resSQL3);
	    	while($rowSQL3 = mysqli_fetch_assoc($runSQL3)){
	    		$correo_factura = $rowSQL3['CorreoCobranza'];
	    	}
			

			//Buscar mensaje
			$resSQL5 = "SELECT * FROM ".$vlprefijodb."parametro WHERE id2 = '108'";
	    	$runSQL5 = mysqli_query($cnx_cfdi2, $resSQL5);
	    	while($rowSQL5 = mysqli_fetch_assoc($runSQL5)){
	    		$mensaje = $rowSQL5['MEMO'];
	    	}
			

			//Buscar datos de correo que envìa notificaión
    		$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress, S.xmldir, S.Servidor FROM ".$vlprefijodb."systemsettings S";
	    		$runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
	    		while($rowSQL4 = mysqli_fetch_assoc($runSQL4)){
	    			$v_host = $rowSQL4['OutgoingEmailHost'];
	    			$v_username = $rowSQL4['OutgoingEmailUserName'];
	    			$v_pass = $rowSQL4['OutgoingEmailPassword'];
	    			$v_port = $rowSQL4['OutgoingEmailPort'];
	    			$v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
					$v_xmldir = $rowSQL4['xmldir'];
					$v_servidor = $rowSQL4['Servidor'];
	    			
	    	}
			
			$v2_xmldir = substr($v_xmldir,1);
			
			$ruta_archivo = "C:\\xampp\\htdocs\\".$v2_xmldir."\\".$v_prefijo2.$v_xfolio."=".$v_cfdserie."-".$v_cfdfolio.".pdf";
			$ruta_archivo2 = "C:\\xampp\\htdocs\\".$v2_xmldir."\\".$v_prefijo2.$v_xfolio."=".$v_cfdserie."-".$v_cfdfolio.".xml";
			
			//echo $ruta_archivo;
			//echo "<br>";
			//echo $ruta_archivo2;
			
			//$ruta_archivo = "ftp://".$v_servidor.":21000".$v_xmldir."/".$v_prefijo2.$v_xfolio."=NC-2.pdf";
			//$ruta_archivo2 = "ftp://".$v_servidor.":21000".$v_xmldir."/".$v_prefijo2.$v_xfolio."=NC-2.xml";
			
			//echo $ruta_archivo;
			
			//$mensaje = 'Se anexa PDF y XML ';
			
			//$correo_factura = '';

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
				// FIN CONFIGURACIÓN PHPMAILER /////////////////////////


				// CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				$mail->setFrom($v_mail_from, 'Tractosoft'); //REMITENTE
				
				
				$array_correos = explode(";", $correo_factura);
				$no_correos = count($array_correos);
				$x=0;
				while($x < $no_correos){
					$mail->addAddress($array_correos[$x]);
					$x = $x +1;
				}
				//$mail->addAddress($correo_factura); //DESTINATARIO

				$mail->Subject = 'Complemento de pago';
				$mail->Body = $mensaje;
				
				$mail->AddAttachment($ruta_archivo);
				$mail->AddAttachment($ruta_archivo2);

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				$mail->send();

        	echo "<div style='padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:4px;'>
        	 Correo enviado correctamente a $correo_factura
        	</div>";

        	echo "<script>
            	alert('Correo enviado correctamente');
        	</script>";
    	}

    	catch (Exception $e) {
        	http_response_code(500);
        	echo 'Excepción PHPMailer: ' . $e->getMessage() . '<br>';
        	echo 'Detalles del error SMTP: ' . $mail->ErrorInfo;
    	}



	



?>