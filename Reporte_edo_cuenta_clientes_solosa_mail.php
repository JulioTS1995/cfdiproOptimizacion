<?php
	set_time_limit(450);
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
	
	
	//Buscar Clientes-Facturas
	$resSQL11="SELECT DISTINCT(C.ID) as id_cliente FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O, ".$prefijobd."clientes C WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND F.CargoAFactura_RID = C.ID AND F.cfdfchhra > '1990-01-01 00:00:00' ORDER BY C.RazonSocial";
	//echo "<br>".$resSQL11;
	$runSQL11=mysql_query($resSQL11);
	$total_clientes_t = mysql_num_rows($runSQL11);
	$total_clientes = number_format($total_clientes_t,0);
	while ($rowSQL11=mysql_fetch_array($runSQL11)){
		//Obtener_variables
		$id_cliente = $rowSQL11['id_cliente'];
									
		//Consultar Nombre del Cliente
		$resSQL12="SELECT * FROM ".$prefijobd."clientes WHERE ID = ".$id_cliente;
		//echo "<br>".$resSQL12;
		$runSQL12=mysql_query($resSQL12);
		while ($rowSQL12=mysql_fetch_array($runSQL12)){
			//Obtener_variables
			$nombre_cliente = $rowSQL12['RazonSocial'];
			$cliente_correo_cobranza = $rowSQL12['CorreoCobranza'];
		}
		
		//echo "<br>".$nombre_cliente;



			$mensaje = '
				<head>
				  <meta charset="UTF-8">
				</head>
				<h2>Estado de Cuenta</h2>
				<p>Estimado Cliente, le enviamos un cordial saludo y aprovechamos para enviarle su estado de cuenta, así mismo le invitamos a ponerse al corriente con las facturas que aparecieran en el reporte como "Vencido" </p>
				<table  border="1" bordercolor="666633" cellpadding="2" cellspacing="0">
				  <tr>
						<td colspan="6"><b>'.$nombre_cliente.'</b></td>
				  </tr>
				  <tr>
					<th class="input">Fecha Timbrado</th>
					<th class="input">Folio</th>
					<th class="input">Estatus</th>
					<th class="input">Fecha Vencimiento</th>
					<th class="input">Saldo Vencido</th>
					<th class="input">Saldo Factura</th>
				  </tr> 
			';
			
	

	
		$saldo_vencido_suma = 0;
		$saldo_suma = 0;
								
						
		//Buscar Facturas
		$resSQL4="SELECT F.ID as ID, F.Moneda as Moneda, F.XFolio as XFolio, F.Creado as Creado, F.zTotal as zTotal, F.CobranzaAbonado as CobranzaAbonado, F.CobranzaSaldo as CobranzaSaldo, F.Vence as Vence, F.Comentarios as Comentarios, F.cfdfchhra as FechaTimbrado, F.DiasCredito as DiasCredito FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND F.CargoAFactura_RID = ".$id_cliente." AND F.cfdfchhra > '1990-01-01 00:00:00' ORDER BY F.XFolio";
		//echo "<br>".$resSQL4;
		$runSQL4=mysql_query($resSQL4);
		$total_registros_t2 = mysql_num_rows($runSQL4);
		$total_registros2 = number_format($total_registros_t2,0);
		while ($rowSQL4=mysql_fetch_array($runSQL4)){
			//Obtener_variables
			$id_factura = $rowSQL4['ID'];
			//$nom_cliente = $rowSQL4['nom_cliente'];
			$moneda = $rowSQL4['Moneda'];
			$xfolio = $rowSQL4['XFolio'];
			$creado_t = $rowSQL4['Creado'];
			$creado = date("d-m-Y H:i:s", strtotime($creado_t));
			$fecha_timbrado_t = $rowSQL4['FechaTimbrado'];
			$fecha_timbrado = date("d-m-Y H:i:s", strtotime($fecha_timbrado_t));
			$total_t = $rowSQL4['zTotal'];
			$total = number_format($total_t,2);
			$cobranza_abonado_t = $rowSQL4['CobranzaAbonado'];
			$cobranza_abonado = number_format($cobranza_abonado_t,2);
			$cobranza_saldo_t = $rowSQL4['CobranzaSaldo'];
			$cobranza_saldo = number_format($cobranza_saldo_t,2);
			$vence_t = $rowSQL4['Vence'];
			$vence = date("d-m-Y", strtotime($vence_t));
			$diascredito = $rowSQL4['DiasCredito'];
			$diff = abs(strtotime($fecha2) - strtotime($vence_t));
			//$years = floor($diff / (365*60*60*24));
			//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$years=0;
			$months=0;
			$atraso = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
									
			//Validar si esta vigente el Vencimiento (Negativo)
			if($vence_t < $fecha2_t) {
				$atraso=$atraso*-1;
			}else {
			}
									
			if($vence_t < '1990-01-01'){
				$vence ='';
			}
									
		    //Validar Estatus
			if($vence_t < $fecha2_t){
				$estatus='Vencido';
			} elseif($vence_t >= $fecha2_t){
				//Valida dias pendientes por vencer
				if($atraso > 7){
					$estatus='En Tiempo';
				} else {
					$estatus='Proximo a Vencer';
				}
			}
									
			$saldo_vencido_t = 0;
			if($estatus == 'Vencido'){
				$saldo_vencido_t = $cobranza_saldo_t;
			}
			$saldo_vencido = number_format($saldo_vencido_t,2);
									
			$saldo_vencido_suma = $saldo_vencido_suma + $saldo_vencido_t;
									
									
			$saldo_suma = $saldo_suma + $cobranza_saldo_t;

	
		
				
				$mensaje .= '
					<tr>
						<td width="60" class="table">'.$fecha_timbrado.'</td>
						<td width="80" class="table">'.$xfolio.'</td>
						<td width="80" class="table">'.$estatus.'</td>
						<td width="80" class="table">'.$vence.'</td>
						<td width="150" class="table">'.$saldo_vencido.'</td>
						<td width="60" class="table">'.$cobranza_saldo.'</td>
					  </tr> 
				';
		}  //Fin Buscar Facturas
								  				
		$saldo_vencido_suma_t = number_format($saldo_vencido_suma,2);
		$saldo_suma_t = number_format($saldo_suma,2);
		
		$mensaje .= '
					<tr>
						<td colspan="4"></td>
						<td><b>'.$saldo_vencido_suma_t.'</b></td>
						<td><b>'.$saldo_suma_t.'</b></td>
					</tr> 
		
				</table>
		';
		
		//echo $mensaje;
		
		
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
			
			//echo "<br>Host: ".$v_host;
			//echo "<br>User: ".$v_username;
			//echo "<br>Pss: ".$v_pass;
			//echo "<br>Port: ".$v_port;
			//echo "<br>MailF: ".$v_mail_from;
		
			$mail = new PHPMailer();
 
				// CONFIGURACIÓN PHPMAILER /////////////////////////


				$v_port = '465'; //TEMPORAL 
				//$mail->CharSet = 'UTF-8';
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

				$mail->setFrom($v_mail_from); //REMITENTE
				
				//DESTINATARIO
				$array_correos = explode(";", $cliente_correo_cobranza);
				$no_correos = count($array_correos);
				
				//echo "<br>No Correos registrados: ".$no_correos." <br><br>";
				
				$x=0;
				while($x < $no_correos){
					//echo "<br>";
					//echo "Correo ".$x.": ".$array_correos[$x];
					//echo "<br>";
					$mail->addAddress($array_correos[$x]);
					$x = $x +1;
				}

				
				//$mail->addAddress($correo_factura); 

				$mail->Subject = 'Notificacion Estado de Cuenta '.$nombre_cliente.'';
				$mail->Body = $mensaje;

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "<br>No se pudo enviar  ";
					echo "<br>";
					echo "No se pudo enviar correo a ".$nombre_cliente;
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
					echo "<br>";
					
				} else {
					echo "<br>Se envio correo a ".$nombre_cliente;
					
				}
		
		
		
		
		
		
		
		
		
		
    	
	} //Fin Busca Cliente
			
			
			




?>
