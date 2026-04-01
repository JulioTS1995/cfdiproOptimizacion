<?php
	set_time_limit(350);
	require_once('cnx_cfdi.php');
	require('PHPMailer/PHPMailerAutoload.php');
	require("PHPMailer/class.phpmailer.php");
	require("PHPMailer/class.smtp.php");
  	mysql_select_db($database_cfdi, $cnx_cfdi);

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
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		while($rowSQL2 = mysql_fetch_assoc($runSQL2)){
    			$id_cliente = $rowSQL2['Cliente_RID'];
				$v_xfolio = $rowSQL2['XFolio'];
				$v_cfdserie = $rowSQL2['cfdserie'];
				$v_cfdfolio = $rowSQL2['cfdfolio'];
				
			}
			
			//echo $resSQL2;
			
			
			//Buscar correo destinatario
			$resSQL3 = "SELECT * FROM ".$vlprefijodb."clientes WHERE ID = ".$id_cliente;
	    	$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
	    	while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
	    		$correo_factura = $rowSQL3['CorreoCobranza'];
	    	}
			
			

			//Buscar datos de correo que envìa notificaión
    		$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress, S.xmldir, S.Servidor FROM ".$vlprefijodb."systemsettings S";
	    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
	    		while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
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
			
			$mensaje = 'Se anexa PDF y XML ';
			
			//$correo_factura = '';

				$mail = new PHPMailer();

				// CONFIGURACIÓN PHPMAILER /////////////////////////


				$mail->isSMTP();                                      // Set mailer to use SMTP
				//$mail->SMTPDebug = 2;
				$mail->Host = $v_host;  					  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = $v_username;                 // SMTP username
				$mail->Password = $v_pass;                           // SMTP password
				$mail->SMTPSecure = '';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = $v_port;                                    // TCP port to connect to
				$mail->IsHTML(true);


				// FIN CONFIGURACIÓN PHPMAILER /////////////////////////


				// CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				$mail->setFrom($v_mail_from); //REMITENTE
			
				$array_correos = explode(";", $correo_factura);
				$no_correos = count($array_correos);
				
				$x=0;
				while($x < $no_correos){
					//echo "<br>";
					//echo "Correo ".$x.": ".$array_correos[$x];
					//echo "<br>";
					$mail->addAddress($array_correos[$x]);
					$x = $x +1;
				}
				
				
				//$mail->addAddress($correo_factura); //DESTINATARIO

				$mail->Subject = 'Factura Electronica';
				$mail->Body = $mensaje;
				
				$mail->AddAttachment($ruta_archivo);
				$mail->AddAttachment($ruta_archivo2);

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "No se pudo enviar  ";
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
				} else {
					echo "El correo se envio";
				}


				// FIN ENVIO DE MAIL /////////////////////////

				/*echo "<br>";
				echo "<br>";
				echo "Host: ".$v_host;
				echo "<br>";
				echo "Username: ".$v_username;
				echo "<br>";
				echo "Password: ".$v_pass;
				echo "<br>";
				echo "Port: ".$v_port;
				echo "<br>";
				echo "FROM: ".$v_mail_from;
				echo "<br>";
				echo "TO: ".$correo_factura;
				echo "<br>";
				echo "Mensaje: ".$mensaje;
				echo "<br>";
				echo "RUTA1: ".$ruta_archivo;
				echo "<br>";
				echo "RUTA2: ".$ruta_archivo2;*/



	



?>
