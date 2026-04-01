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
	$prefijobd = $_POST["base"];


	

			//$id_remision = $_POST['id_remision'];


    //if (isset($_POST['hasta']))
		//  $fechah = $_POST['hasta'];

			$mensaje = '
				<h2>Remisiones Ultimo Estatus Tracking</h2>
				<table  border="1" bordercolor="666633" cellpadding="2" cellspacing="0">
				  <tr>
					<th class="input">Remision</th>
					<th class="input">Unidad</th>
					<th class="input">CR</th>
					<th class="input">Operador</th>
					<th class="input">Ruta</th>
					<th class="input">Kms Ruta</th>
					<th class="input">Cliente</th>
					<th class="input">Fecha y Hora de Salida</th>
					<th class="input">Fecha y Hora de Llegada</th>
					<th class="input">Estatus</th>
					<th class="input">Tiempo en Espera de Carga/Viaje</th>
					<th class="input">Fecha de Tracking</th>
					<th class="input">Documentador</th>
					<th class="input">Cita</th>
					<th class="input">Especificaciones de Viaje del Cliente</th>
					<th class="input">Comentarios TR</th>
					<th class="input">Temperatura CR</th>
					<th class="input">Comentarios CR</th>
					<th class="input">Ubicación de Unidad</th>
					<th class="input">Kms Restantes</th>
					<th class="input">Tiempo Estimado para llegar a Destino</th>
					<th class="input">Estatus de Llegada</th>	
					<th class="input">Diesel TR</th>
					<th class="input">Diesel CR</th>
				  </tr> 
			';

	$resSQL="SELECT DISTINCT(FolioEstatus2_RID) FROM ".$prefijobd."remisionesestatus2 WHERE Date(Fecha) Between '".$fecha_inicio."' AND '".$fecha_fin."' AND FolioEstatus2_RID <> '' ORDER BY ID";
	//echo $resSQL;
	$runSQL=mysql_query($resSQL);
	while ($rowSQL=mysql_fetch_array($runSQL)){
		//Obtener_variables
		$id_remision = $rowSQL['FolioEstatus2_RID'];
		
		//Buscar datos de la remision
		$resSQL1="SELECT R.ID, R.XFolio, R.Unidad_RID, R.Ruta_RID, R.URemolqueA_RID, R.Operador_RID, R.CargoACliente_RID, R.Instrucciones, R.CitaCarga, R.Creado, U.Unidad, R.FechaHoraSalida, R.FechaHoraLlegada, R.TiempoEsperaCargaViaje FROM ".$prefijobd."remisiones R, ".$prefijobd."unidades U WHERE R.Unidad_RID = U.ID AND R.ID = ".$id_remision." ORDER BY U.Unidad";
		//echo $resSQL1."<br>";
		$runSQL1=mysql_query($resSQL1);
		while ($rowSQL1=mysql_fetch_array($runSQL1)){
			//Obtener_variables
			//$id_remision = $rowSQL1['ID'];
			$xfolio = $rowSQL1['XFolio'];
			$unidad = $rowSQL1['Unidad_RID'];
			$ruta_id = $rowSQL1['Ruta_RID'];
			$remolque_id = $rowSQL1['URemolqueA_RID'];
			$operador_id = $rowSQL1['Operador_RID'];
			$cliente_id = $rowSQL1['CargoACliente_RID'];
			$instrucciones = $rowSQL1['Instrucciones'];
			$cita_fecha_temp = $rowSQL1['CitaCarga'];
			$cita_fecha = date("d-m-Y H:i:s", strtotime($cita_fecha_temp));
			$fecha_temp2 = $rowSQL1['Creado'];
			$fecha2 = date("d-m-Y H:i:s", strtotime($fecha_temp2));
			$fecha_hora_salida_temp = $rowSQL1['FechaHoraSalida'];
			$fecha_hora_salida = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
			$fecha_hora_llegada_temp = $rowSQL1['FechaHoraLlegada'];
			$fecha_hora_llegada = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
			$tiempo_espera_carga_viaje = $rowSQL1['TiempoEsperaCargaViaje'];
			
			if($fecha_hora_salida_temp < '1990-01-01 00:00:00'){
				$fecha_hora_salida ='';
			}
			if($fecha_hora_llegada_temp < '1990-01-01 00:00:00'){
				$fecha_hora_llegada ='';
			}
			if($cita_fecha_temp < '1990-01-01 00:00:00'){
				$cita_fecha ='';
			}
			
			if (isset($unidad)){
				
			} else {
				$unidad = 0;
			}
			
			if (isset($remolque_id)){
				
			} else {
				$remolque_id = 0;
			}
			
			if (isset($operador_id)){
				
			} else {
				$operador_id = 0;
			}
			
			if (isset($cliente_id)){
				
			} else {
				$cliente_id = 0;
			}
			
			$resSQL6="SELECT Unidad FROM ".$prefijobd."unidades WHERE ID = ".$remolque_id." ";
			//echo $resSQL2;
			$runSQL6=mysql_query($resSQL6);
			$rowSQL6=mysql_fetch_array($runSQL6);
			$nom_remolque = $rowSQL6['Unidad'];
			
			$resSQL7="SELECT Operador FROM ".$prefijobd."operadores WHERE ID = ".$operador_id." ";
			//echo $resSQL2;
			$runSQL7=mysql_query($resSQL7);
			$rowSQL7=mysql_fetch_array($runSQL7);
			$nom_operador = $rowSQL7['Operador'];
			
			$resSQL8="SELECT RazonSocial FROM ".$prefijobd."clientes WHERE ID = ".$cliente_id." ";
			//echo $resSQL8;
			$runSQL8=mysql_query($resSQL8);
			$rowSQL8=mysql_fetch_array($runSQL8);
			$nom_cliente = $rowSQL8['RazonSocial'];
			
			$resSQL2="SELECT Unidad FROM ".$prefijobd."unidades WHERE ID = ".$unidad." ";
			//echo $resSQL2;
			$runSQL2=mysql_query($resSQL2);
			$rowSQL2=mysql_fetch_array($runSQL2);
			$nom_unidad = $rowSQL2['Unidad'];
			
			if($ruta_id > 0){
				$resSQL5="SELECT * FROM ".$prefijobd."rutas WHERE ID = ".$ruta_id." ";
				$runSQL5=mysql_query($resSQL5);
				$rowSQL5=mysql_fetch_array($runSQL5);
				$ruta = $rowSQL5['Ruta'];
				$kms_ruta_temp = $rowSQL5['Kms'];
				$kms_ruta = number_format($kms_ruta_temp,2); 
			}else{
				$ruta = '';
				$kms_ruta = 0;
			}
			
			$resSQL3="SELECT MAX(ID) as max_id FROM ".$prefijobd."remisionesestatus2 WHERE FolioEstatus2_RID = ".$id_remision." ";
			//echo $resSQL3;
			$runSQL3=mysql_query($resSQL3);
			$rowSQL3=mysql_fetch_array($runSQL3);
			$ultimo_id_tracking = $rowSQL3['max_id'];
			
			$resSQL4="SELECT * FROM ".$prefijobd."remisionesestatus2 WHERE ID = ".$ultimo_id_tracking." ";
			//echo $resSQL4;
			$runSQL4=mysql_query($resSQL4);
			$rowSQL4=mysql_fetch_array($runSQL4);
			$estatus = $rowSQL4['Estatus'];
			$fecha_temp = $rowSQL4['Fecha'];
			$fecha00 = date("Y-m-d H:i:s", strtotime($fecha_temp));
			$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
			if($fecha < '01-01-1990 00:00:00') {
				$fecha = '';
			} 
			$documentador = $rowSQL4['Documentador'];
			$comentario = $rowSQL4['Comentarios'];
			//$fecha_hora_salida_temp = $rowSQL4['FechaHoraSalida'];
			//$fecha_hora_salida = date("d-m-Y H:i:s", strtotime($fecha_hora_salida_temp));
			//$fecha_hora_llegada_temp = $rowSQL4['FechaHoraLlegada'];
			//$fecha_hora_llegada = date("d-m-Y H:i:s", strtotime($fecha_hora_llegada_temp));
			//$tiempo_espera_carga_viaje = $rowSQL4['TiempoEsperaCargaViaje'];
			$estatus_llegada = $rowSQL4['EstatusLlegada'];
			$temperatura_cr = $rowSQL4['TemperaturaCR'];
			$comentarios_cr = $rowSQL4['ComentariosCR'];
			$ubicacion_unidad = $rowSQL4['UbicacionUnidad'];
			$km_restantes_temp = $rowSQL4['KmRestantes'];
			$km_restantes = number_format($km_restantes_temp,2);
			$tiempo_estimado_llegada_destino = $rowSQL4['TiempoEstimadoLlegadaDestino'];
			$diesel_tr_temp = $rowSQL4['DieselTR'];
			$diesel_tr = number_format($diesel_tr_temp,2);
			$diesel_cr_temp = $rowSQL4['DieselCR'];
			$diesel_cr = number_format($diesel_cr_temp,2);
		
		} 
		
		//echo $fecha00;
		//echo "<br>";
		$fi = $fecha_inicio;
		$ff = $fecha_fin;
		$fi2 = date("Y-m-d H:i:s", strtotime($fi));
		$ff2 = date("Y-m-d H:i:s", strtotime($ff));
		$nuevafecha_fin = strtotime ('+23 hour +59 minute + 59 second', strtotime($ff2));
		

		$nuevafecha_fin = date ('Y-m-d H:i:s' , $nuevafecha_fin);
				
		/*echo $fi2;
		echo "<br>";
		echo $nuevafecha_fin;
		echo "<br>";*/
		
		
		//Validar que la Fecha este en el rango especificado
		if(($fecha00 >= $fi2) AND ($fecha00 <= $nuevafecha_fin)){
				
				$mensaje .= '
					<tr>
						<td width="60" class="table">'.$xfolio.'</td>
						<td width="80" class="table">'.$nom_unidad.'</td>
						<td width="80" class="table">'.$nom_remolque.'</td>
						<td width="80" class="table">'.$nom_operador.'</td>
						<td width="150" class="table">'.$ruta.'</td>
						<td width="60" class="table">'.$kms_ruta.'</td>
						<td width="80" class="table">'.$nom_cliente.'</td>
						<td width="60" class="table">'.$fecha_hora_salida.'</td>
						<td width="60" class="table">'.$fecha_hora_llegada.'</td>
						<td width="80" class="table">'.$estatus.'</td>
						<td width="80" class="table">'.$tiempo_espera_carga_viaje.'</td>
						<td width="60" class="table">'.$fecha.'</td>
						<td width="60" class="table">'.$documentador.'</td>
						<td width="60" class="table">'.$cita_fecha.'</td>
						<td width="150" class="table">'.$instrucciones.'</td>
						<td width="150" class="table">'.$comentario.'</td>
						<td width="60" class="table">'.$temperatura_cr.'</td>
						<td width="150" class="table">'.$comentarios_cr.'</td>
						<td width="80" class="table">'.$ubicacion_unidad.'</td>
						<td width="80" class="table">'.$km_restantes.'</td>
						<td width="80" class="table">'.$tiempo_estimado_llegada_destino.'</td>
						<td width="60" class="table">'.$estatus_llegada.'</td>
						<td width="60" class="table">'.$diesel_tr.'</td>
						<td width="60" class="table">'.$diesel_cr.'</td>
					  </tr> 
				';
				
    	} else {
		}
	}  //Fin Buscar ID's Remisiones
			
			
			$mensaje .= '</table>';
			
			//Buscar correo destinatario
			$resSQL3 = "SELECT CorreoNotificaciones FROM ".$prefijobd."systemsettings;";
	    	$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
	    	while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
	    		$correo_factura = $rowSQL3['CorreoNotificaciones'];
	    	}
			

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
			
			//$correo_factura = '';

				$mail = new PHPMailer();

				// CONFIGURACIÓN PHPMAILER /////////////////////////


				$mail->CharSet = 'UTF-8';
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
				
				//DESTINATARIO
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

				
				//$mail->addAddress($correo_factura); 

				$mail->Subject = 'Notificacion Tracking '.$prefijobd;
				$mail->Body = $mensaje;

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "No se pudo enviar  ";
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
					echo "<br>";
					
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
				echo "<br>";*/



	



?>
