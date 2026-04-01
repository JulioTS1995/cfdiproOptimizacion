<?php
	//set_time_limit(350);
	require_once('cnx_cfdi.php');
	require('PHPMailer/PHPMailerAutoload.php');
	require("PHPMailer/class.phpmailer.php");
	require("PHPMailer/class.smtp.php");
  	mysql_select_db($database_cfdi, $cnx_cfdi);

    //if(isset($_POST['base']))
    //    $base = $_POST['base'];
    $rutaarchivo = "";
	
	
	$anio_logs = date('Y');
	$mes_logs = date('m');
	$dia_logs = date('d');
		
	$fecha2_t = $anio_logs."-".$mes_logs."-".$dia_logs;  
	$fecha2 = date("d-m-Y", strtotime($fecha2_t));


	
	$prefijobd = $_GET["prefijobd"];
	$id_solcitud = $_GET["id"];
	
	
	//Buscar Cliente Solicitud
	$resSQL11="SELECT * FROM ".$prefijobd."solicitudes WHERE ID=".$id_solcitud;
	//echo "<br>".$resSQL11;
	$runSQL11=mysql_query($resSQL11);
	//$total_clientes_t = mysql_num_rows($runSQL11);
	//$total_clientes = number_format($total_clientes_t,0);
	while ($rowSQL11=mysql_fetch_array($runSQL11)){
		//Obtener_variables
		$id_cliente= $rowSQL11['CargoASolicitud_RID'];
	}
	
					
		//Consultar Nombre y correo del Cliente
		$resSQL12="SELECT * FROM ".$prefijobd."clientes WHERE ID = ".$id_cliente;
		//echo "<br>".$resSQL12;
		$runSQL12=mysql_query($resSQL12);
		while ($rowSQL12=mysql_fetch_array($runSQL12)){
			//Obtener_variables
			$nombre_cliente = $rowSQL12['RazonSocial'];
			$cliente_correo_trafico = $rowSQL12['CorreoTrafico'];
			$solicitud_folio = $rowSQL12['Folio'];
			$solicitud_solicita = $rowSQL12['Solicita'];
			$solicitud_destinatario_poblacion = $rowSQL12['DestinatarioPoblacion'];
		}
		
		//Buscar en SystemSettings
		$resSQL122="SELECT * FROM ".$prefijobd."systemsettings";
		//echo "<br>".$resSQL12;
		$runSQL122=mysql_query($resSQL122);
		while ($rowSQL122=mysql_fetch_array($runSQL122)){
			//Obtener_variables
			$ss_telefono = $rowSQL122['Telefono'];
			$ss_web = $rowSQL122['Web'];
			$ss_correo = $rowSQL122['Correo'];
		}



			$mensaje = '
				<head>
				  <meta charset="UTF-8">
				</head>
				<p>'.$nombre_cliente.'<p/>
				<p>'.$solicitud_solicita.'</p>
				
				<p>Hemos recibido su Solicitud de Carga No. '.$solicitud_folio.', con destino a '.$solicitud_destinatario_poblacion.', y en breve recibira un correo confirmando el servicio, donde incluira la unidad asignada, placas, y operador.</p>
				
				<p>
				Nuestro servicio de atencion en linea es de:  <br>
				Lunes a Viernes 9am a 6pm  <br>
				Sabados 9am a 2pm <br>
				Hora del centro de Mexico <br>
				</p>
				
				<p>Gracias por su preferencia.</p>
				
				<p>
				Robot (Envio automatico) <br>
				Tel: '.$ss_telefono.' <br>
				Web: '.$ss_web.' <br>
				E-mail : '.$ss_correo.' <br>
				*Para hacer una cancelacion de servicio favor de contactarnos en breve.
				</p>
				

			';
			

		
		
			//Buscar datos de correo que envìa notificaión
    		$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress FROM ".$prefijobd."systemsettings S";
	    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
	    		while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
	    			$v_host = $rowSQL4['OutgoingEmailHost'];
	    			$v_username = $rowSQL4['OutgoingEmailUserName'];
	    			$v_pass = $rowSQL4['OutgoingEmailPassword'];
	    			$v_port = $rowSQL4['OutgoingEmailPort'];
	    			$v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
	    			
	    	}
			
		
			$mail = new PHPMailer();

				// CONFIGURACIÓN PHPMAILER /////////////////////////

				
				$v_port = '465'; //TEMPORAL 
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = $v_host;  					  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = $v_username;                 // SMTP username
				$mail->Password = $v_pass;                           // SMTP password
				$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, ssl also accepted
				$mail->Port = $v_port;                                    // TCP port to connect to
				$mail->IsHTML(true);


				// FIN CONFIGURACIÓN PHPMAILER /////////////////////////


				// CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				$mail->setFrom($v_mail_from); //REMITENTE
				
				//DESTINATARIO
				$array_correos = explode(",", $cliente_correo_trafico);
				$no_correos = count($array_correos);
				
				$x=0;
				while($x < $no_correos){
					//echo "<br>";
					//echo "Correo ".$x.": ".$array_correos[$x];
					//echo "<br>";
					$mail->addAddress($array_correos[$x]);
					$x = $x +1;
				}

				
				//$mail->addAddress($correo_factura); 
				

				$mail->Subject = 'Solicitud de servicio No. '.$solicitud_folio.' recibida.';
				$mail->Body = $mensaje;

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "<br>No se pudo enviar  ";
					echo "<br>";
					echo "No se pudo enviar correo a ".$nombre_cliente." - ".$cliente_correo_trafico;
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
					echo "<br>";
					
				} else {
					echo "<br>Se envio correo a ".$nombre_cliente;
					
				}
		
		
		
		
		
		
		
		
		
		
    	

			
			
			




?>
