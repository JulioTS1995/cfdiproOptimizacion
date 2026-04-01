<?php
	set_time_limit(350);
	require_once('cnx_cfdi.php');
	require('PHPMailer/PHPMailerAutoload.php');
	require("PHPMailer/class.phpmailer.php");
	require("PHPMailer/class.smtp.php");
  	mysql_select_db($database_cfdi, $cnx_cfdi);
	
	if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Falta ID de Compra");
	}
	
	$id_compra = $_GET['id'];

    if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}

	//Internalizo los parametros previo escape de caracteres especiales
	$vlprefijodb = @mysql_escape_string($_GET["prefijodb"]);

	//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
	$pos = strpos($vlprefijodb, "_");

	if ($pos === false) {
		$vlprefijodb = $vlprefijodb . "_";
	} 
			
			//Buscar datos de RequisicionCompra
			$resSQL1="SELECT * FROM ".$vlprefijodb."compras WHERE ID = ".$id_compra;			
    		$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
    		while($rowSQL1 = mysql_fetch_assoc($runSQL1)){
    			$oficina_id = $rowSQL1['OficinaCompras_RID'];
				$dias_credito = $rowSQL1['DiasCredito'];
				$ultima_actualizacion_temp = $rowSQL1['UltimaActualizacion'];
				$ultima_actualizacion = date('d-m-Y' ,strtotime($ultima_actualizacion_temp));
				$rubro_id = $rowSQL1['Rubro_RID'];
				$proveedor_id = $rowSQL1['ProveedorNo_RID'];
				$retenido_t = $rowSQL1['Retenido'];
				$retenido = "$".number_format($retenido_t ,2);
				$total_t = $rowSQL1['Total'];
				$total = "$".number_format($total_t ,2);
				$iva_t = $rowSQL1['IVA'];
				$iva = "$".number_format($iva_t ,2);
				$fecha_temp = $rowSQL1['Fecha'];
				$fecha = date('d-m-Y' ,strtotime($fecha_temp));
				$subtotal_t = $rowSQL1['Subtotal'];
				$subtotal = "$".number_format($subtotal_t ,2);
				$ultimo_documentador = $rowSQL1['UltimoDocumentador'];
				$comentarios = $rowSQL1['Comentarios'];
				$xfolio = $rowSQL1['XFolio'];
				$impuesto_t = $rowSQL1['Impuesto'];
				$impuesto = "$".number_format($impuesto_t ,2);
				$estatus = $rowSQL1['Estatus'];
				$retencion_t = $rowSQL1['Retencion'];
				$retencion = "$".number_format($retencion_t ,2);
				
				
				
			
			}
			
			
			//Buscar Oficina
			$resSQL3="SELECT * FROM ".$vlprefijodb."oficinas WHERE ID = ".$oficina_id;			
    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
    		while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
    			$oficina_serie = $rowSQL3['Serie'];
			}
			
			
			
			/**************** Validar que este capturado ******************///
			if($rubro_id > 0){
				//Buscar Rubro
				$resSQL5="SELECT * FROM ".$vlprefijodb."rubrocompras WHERE ID = ".$rubro_id;			
				$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
				while($rowSQL5 = mysql_fetch_assoc($runSQL5)){
					$rubro = $rowSQL5['Rubro'];
				}
			} else {
				$rubro = '';
			}
			
			
			
			/**************** Validar que este capturado ******************///
			if($proveedor_id > 0){
				//Buscar Proveedor
				$resSQL6="SELECT * FROM ".$vlprefijodb."proveedores WHERE ID = ".$proveedor_id;			
				$runSQL6 = mysql_query($resSQL6, $cnx_cfdi);
				while($rowSQL6 = mysql_fetch_assoc($runSQL6)){
					$proveedor_razon_social = $rowSQL6['RazonSocial'];
					$proveedor_email = $rowSQL6['Email'];
				}
			} else {
				$proveedor_razon_social = '';
				$proveedor_email = '';
			}
			
			

			$mensaje = '
				<img src="http://72.55.137.152/cfdipro/imagenes/logo_ts.png" alt="tslogo" height="120">
				<h2>Compras: '.$xfolio.' </h2>
				<h2>'.$proveedor_razon_social.' </h2>
				<hr>
				<h4><strong>FECHA: </strong>'.$fecha.'</h4>
				<hr>
				<table border="1">
				  <tr>
					<th>Cantidad</th>
					<th>Producto</th>
					<th>Precio Unitario</th>
					<th>Importe</th>
				  </tr> ';
				  
			//Buscar RDC Sub
			$resSQL7="SELECT * FROM ".$vlprefijodb."comprassub WHERE FolioSub_RID = ".$id_compra;			
    		$runSQL7 = mysql_query($resSQL7, $cnx_cfdi);
    		while($rowSQL7 = mysql_fetch_assoc($runSQL7)){
    			$odc_sub_cantidad_temp = $rowSQL7['Cantidad'];
				$odc_sub_cantidad = number_format($odc_sub_cantidad_temp ,2);
				$odc_sub_nombre = $rowSQL7['Nombre'];
				$odc_preciounitario_t = $rowSQL7['PrecioUnitario'];
				$odc_preciounitario = "$".number_format($odc_preciounitario_t ,2);
				$odc_importe_t = $rowSQL7['Importe'];
				$odc_importe = "$".number_format($odc_importe_t ,2);
				
				$mensaje.= '	  
				  <tr>
					<td>'.$odc_sub_cantidad.'</td>
					<td>'.$odc_sub_nombre.'</td>
					<td>'.$odc_preciounitario.'</td>
					<td>'.$odc_importe.'</td>
				  </tr>';
				
				
				
			}	  
			
				  
			$mensaje.= '	  
				</table>
				<br>
			';
			

			

			//Buscar datos de correo que envìa notificaión
    		$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress FROM ".$vlprefijodb."systemsettings S";
			//echo $resSQL4;
	    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
	    		while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
	    			$v_host = $rowSQL4['OutgoingEmailHost'];
	    			$v_username = $rowSQL4['OutgoingEmailUserName'];
	    			$v_pass = $rowSQL4['OutgoingEmailPassword'];
	    			$v_port = $rowSQL4['OutgoingEmailPort'];
	    			$v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
					//$v_mail_to = $rowSQL4['DestinatarioSolicitudGasto'];
	    			
	    	}
			
			

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
				//$mail->addAddress($correo_cliente); //DESTINATARIO
				
				$array_correos = explode(";", $proveedor_email);
				$no_correos = count($array_correos);
				
				$x=0;
				while($x < $no_correos){
					//echo "<br>";
					//echo "Correo ".$x.": ".$array_correos[$x];
					//echo "<br>";
					$mail->addAddress($array_correos[$x]);
					$x = $x +1;
				}

				$mail->Subject = 'Notificacion de Compras: '.$xfolio;
				$mail->Body = $mensaje;

				// FIN CONFIGURACIÓN DE CORREO A ENVIAR /////////////////////////

				// ENVIO DE MAIL /////////////////////////

				if($mail->send() == false){
					echo "No se pudo enviar  ";
					echo "<br>";
					echo "ERROR de PHPMailer ".$mail->ErrorInfo;
				} else {
					echo "<link rel='shortcut icon' href='imagenes/logo_ts.ico'>";
					echo "<img src='imagenes/logo_ts.png' alt='tslogo' height='120'>";
					echo "<br>";
					echo "<hr>";
					echo "<h2>La notificacion de la Compra: ".$xfolio."  se envio con Exito</h2>";
					echo "<hr>";
				}


				/*
				echo "<br>";
				echo "Mensaje: ".$mensaje;
				echo "<br>";*/



	



?>
