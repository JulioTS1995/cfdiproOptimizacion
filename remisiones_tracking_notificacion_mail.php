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
    //$vlprefijodb = "opl";


	$fecha_inicio = $_POST["fechai"];
	$fecha_fin = $_POST["fechaf"];
	$vlprefijodb = $_POST["base"];


	

			//$id_remision = $_POST['id_remision'];


    //if (isset($_POST['hasta']))
		//  $fechah = $_POST['hasta'];

			$mensaje = '
				<h2>Remisiones Ultimo Estatus Tracking</h2>
				<table border="1">
				  <tr>
					<th class="input">Remision</th>
					<th class="input">Unidad</th>
					<th class="input">Estatus</th>
					<th class="input">Fecha</th>
					<th class="input">Documentador</th>
					<th class="input">Comentario</th>	
				  </tr> 
			';

			//$resSQL2 = "SELECT * FROM opl_remisiones WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' ORDER BY XFolio";	
			$resSQL2="SELECT R.ID, R.XFolio, R.Unidad_RID, R.Creado, U.Unidad  FROM ".$vlprefijodb."remisiones R, ".$vlprefijodb."unidades U WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' AND R.Unidad_RID = U.ID ORDER BY U.Unidad";			
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		while($rowSQL2 = mysql_fetch_assoc($runSQL2)){
    			$id_remision = $rowSQL2['ID'];
				$xfolio = $rowSQL2['XFolio'];
				$unidad = $rowSQL2['Unidad_RID'];
				//$fecha_temp = $rowSQL2['Creado'];
				//$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
				
				if (isset($unidad)){
					
				} else {
					$unidad = 0;
				}
				
				$resSQL4="SELECT Unidad FROM ".$vlprefijodb."unidades WHERE ID = ".$unidad." ";
				$runSQL4=mysql_query($resSQL4);
				$rowSQL4=mysql_fetch_array($runSQL4);
				$nom_unidad = $rowSQL4['Unidad'];
				
				$resSQL5="SELECT * FROM ".$vlprefijodb."remisionesestatus2 WHERE FolioEstatus2_RID = ".$id_remision." order by ID desc limit 1";
				$runSQL5=mysql_query($resSQL5);
				$rowSQL5=mysql_fetch_array($runSQL5);
				
					$estatus = $rowSQL5['Estatus'];
					$fecha_temp = $rowSQL5['Fecha'];
					$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
					$documentador = $rowSQL5['Documentador'];
					$comentario = $rowSQL5['Comentarios'];
				
				$mensaje .= '
					<tr>
						<td width="60" class="table">'.$xfolio.'</td>
						<td width="60" class="table">'.$nom_unidad.'</td>
						<td width="200" class="table">'.$estatus.'</td>
						<td width="80" class="table">'.$fecha.'</td>
						<td width="200" class="table">'.$documentador.'</td>
						<td width="350" class="table">'.$comentario.'</td>
					  </tr> 
				';
				
    		}
			
			$mensaje .= '</table>';
			
			//Buscar correo destinatario
			$resSQL3 = "SELECT CorreoNotificaciones FROM ".$vlprefijodb."systemsettings;";
	    	$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
	    	while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
	    		$correo_factura = $rowSQL3['CorreoNotificaciones'];
	    	}

			//Buscar datos de correo que envìa notificaión
    		$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress FROM ".$vlprefijodb."systemsettings S";
	    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
	    		while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
	    			$v_host = $rowSQL4['OutgoingEmailHost'];
	    			$v_username = $rowSQL4['OutgoingEmailUserName'];
	    			$v_pass = $rowSQL4['OutgoingEmailPassword'];
	    			$v_port = $rowSQL4['OutgoingEmailPort'];
	    			$v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
	    			
	    	}
			
			//$correo_factura = '';

				$mail = new PHPMailer();

				// CONFIGURACIÓN PHPMAILER /////////////////////////


				$mail->isSMTP();                                      // Set mailer to use SMTP
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
				$mail->addAddress($correo_factura); //DESTINATARIO

				$mail->Subject = 'Notificacion Tracking '.$vlprefijodb;
				$mail->Body = $mensaje;

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "No se pudo enviar  ";
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
				} else {
					echo "El correo se envió";
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
				echo "<br>";*/



	



?>
