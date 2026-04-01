<?php

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
	
$prefijodb = $_GET["prefijodb"];
$id_factura = $_GET["id"];
	
?>




<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Valida Documento TS</title>
  </head>
  
  <?php
  
//Buscar datos de Factura
$sql_000="SELECT * FROM ".$prefijodb."factura WHERE id = ".$id_factura;
//echo $sql_000;
$res_000=mysql_query($sql_000);
while ($fila_exp000=mysql_fetch_array($res_000)){
	$f2_xfolio = $fila_exp000['XFolio'];
	$f2_tipoviaje = $fila_exp000['TipoViaje'];
}
  
  
  ?>
  
  <body>
    <h1>Validación de Documentación Factura <?php echo $f2_xfolio; ?></h1>
	<h4>Tipo Viaje: <b><?php echo $f2_tipoviaje; ?></b></h4>
	<div class="col-lg-12" style="height:650px; overflow:scroll;">
	<table class="table table-hover">
	  <thead>
		<tr>
		  <th width="20%">Módulo - Nombre del Campo</th>
		  <th width="80%">Descripción Observación</th>
		</tr>
	  </thead>
	  <tbody>
<?php
//Buscar datos de Factura
$sql_00="SELECT * FROM ".$prefijodb."factura WHERE id = ".$id_factura;
//echo $sql_00;
$validacion = 0;
$res_00=mysql_query($sql_00);
while ($fila_exp00=mysql_fetch_array($res_00)){
	$f_xfolio = $fila_exp00['XFolio'];
	$f_oficina_id = $fila_exp00['Oficina_RID'];
	$f_cliente_id = $fila_exp00['CargoAFactura_RID'];
	$f_usocfdi33 = $fila_exp00['usocfdi33_RID'];
	$f_metodopago33 = $fila_exp00['metodopago33_RID'];
	$f_formapago33 = $fila_exp00['formapago33_RID'];
	$f_complemento_traslado = $fila_exp00['ComplementoTraslado'];
	$f_tipoviaje = $fila_exp00['TipoViaje'];
	$f_codigo_origen = $fila_exp00['CodigoOrigen'];
	$f_codigo_destino = $fila_exp00['CodigoDestino'];
	$f_ruta_id= $fila_exp00['Ruta_RID'];
	$f_distancia_recorrida= $fila_exp00['DistanciaRecorrida'];
	$f_clave_unidad_peso_id= $fila_exp00['ClaveUnidadPeso_RID'];
	$f_operador_id= $fila_exp00['Operador_RID'];
	$f_unidad_id= $fila_exp00['Unidad_RID'];
	$f_remolque_id= $fila_exp00['Remolque_RID'];
	$f_remolque2_id= $fila_exp00['Remolque2_RID'];
	//REMITENTE
	$f_remitente = $fila_exp00['Remitente'];
	$f_remitenterfc = $fila_exp00['RemitenteRFC'];
	$f_remitentecitacarga = $fila_exp00['CitaCarga'];
	$f_remitentecodigopostal = $fila_exp00['RemitenteCodigoPostal'];
	$f_remitenteestado = $fila_exp00['RemitenteEstado_RID'];
	$f_remitentecalle = $fila_exp00['RemitenteCalle'];
	$f_remitentenumext = $fila_exp00['RemitenteNumExt'];
	$f_remitentepais = $fila_exp00['RemitentePais'];
	$f_remitentenumregidtrib = $fila_exp00['RemitenteNumRegIdTrib'];
	//DESTINATARIO
	$f_destinatario = $fila_exp00['Destinatario'];
	$f_destinatariorfc = $fila_exp00['DestinatarioRFC'];
	$f_destinatariocitacarga = $fila_exp00['DestinatarioCitaCarga'];
	$f_destinatariocodigopostal = $fila_exp00['DestinatarioCodigoPostal'];
	$f_destinatarioestado = $fila_exp00['DestinatarioEstado_RID'];
	$f_destinatariocalle = $fila_exp00['DestinatarioCalle'];
	$f_destinatarionumext = $fila_exp00['DestinatarioNumExt'];
	$f_destinatariopais = $fila_exp00['DestinatarioPais'];
	$f_destinatarionumregidtrib = $fila_exp00['DestinatarioNumRegIdTrib'];
	
	//Verificar si hay valores en FacturaSub
	$sql_fs="SELECT COUNT(*) as t_fs FROM ".$prefijodb."facturassub WHERE FolioSub_RID = ".$id_factura;
	//echo $sql_fs;
	$res_fs=mysql_query($sql_fs);
	while ($fila_expfs=mysql_fetch_array($res_fs)){
		$f_total_reg_fs = $fila_expfs['t_fs'];
	}
	
	
	
	if($f_metodopago33 > 0){
		//Obtener Metodo de Pago
		$sql_tbl01="SELECT * FROM ".$prefijodb."tablageneral WHERE ID=".$f_metodopago33;
		//echo $sql_tbl01;
		$res_tbl01=mysql_query($sql_tbl01);
		while ($fila_tbl01=mysql_fetch_array($res_tbl01)){
			$metodopago_id2 = $fila_tbl01['ID2'];
			$metodopago_descripcion = $fila_tbl01['Descripcion'];
		}
	} else {
		$metodopago_id2 = '';
		$metodopago_descripcion = '';
	}
	
	if($f_formapago33 > 0){
		//Obtener Forma de Pago
		$sql_tbl02="SELECT * FROM ".$prefijodb."tablageneral WHERE ID=".$f_formapago33;
		//echo $sql_tbl02;
		$res_tbl02=mysql_query($sql_tbl02);
		while ($fila_tbl02=mysql_fetch_array($res_tbl02)){
			$formapago_id2 = $fila_tbl02['ID2'];
			$formapago_descripcion = $fila_tbl02['Descripcion'];
		}
	} else {
		$formapago_id2 = '';
		$formapago_descripcion = '';
	}
	
	
	
	//Buscar datos de SystemSettings
	$sql_03="SELECT * FROM ".$prefijodb."systemsettings LIMIT 1";
	//echo $sql_03;
	$res_03=mysql_query($sql_03);
	while ($fila_exp03=mysql_fetch_array($res_03)){
		$st_codigopostal = $fila_exp03['CodigoPostal'];
	}
	
	
	
	//Buscar datos de Oficina
	$sql_01="SELECT * FROM ".$prefijodb."oficinas WHERE id = ".$f_oficina_id;
	//echo $sql_01;
	$res_01=mysql_query($sql_01);
	while ($fila_exp01=mysql_fetch_array($res_01)){
		$o_oficina = $fila_exp01['Oficina'];
		$o_serie_fiscal = $fila_exp01['SerieFiscal'];
		$o_iva = $fila_exp01['IVA'];
		$o_retencion = $fila_exp01['Retencion'];		
	}
	
	if((empty($o_serie_fiscal))||($o_serie_fiscal == '')) {
    ?>
		<tr>
		  <td>Oficina - Serie Fiscal</td>
		  <td>Capturar el campo Seríe Fiscal, de la Oficina: <?php echo $o_oficina; ?> en el Catálogo de Oficinas</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if($o_iva>=0) {
	}else {
	?>
		<tr>
		  <td>Oficina - IVA</td>
		  <td>Capturar el campo IVA, de la Oficina: <?php echo $o_oficina; ?> en el Catálogo de Oficinas</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if($o_retencion>=0) {
	}else {
	?>
		<tr>
		  <td>Oficina - Retencion</td>
		  <td>Capturar el campo Retencion, de la Oficina: <?php echo $o_oficina; ?> en el Catálogo de Oficinas</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	//Buscar datos de Cliente
	$sql_02="SELECT * FROM ".$prefijodb."clientes WHERE id = ".$f_cliente_id;
	//echo $sql_02;
	$res_02=mysql_query($sql_02);
	while ($fila_exp02=mysql_fetch_array($res_02)){
		$c_numero_cliente = $fila_exp02['NumeroCliente'];
		$c_persona = $fila_exp02['Persona'];
		$c_razonsocial = $fila_exp02['RazonSocial'];
		$c_rfc = $fila_exp02['RFC'];
		$c_codigopostal = $fila_exp02['CodigoPostal'];
		$c_regimenfiscal = $fila_exp02['RegimenFiscal'];
		
		
	}
	
	if((empty($c_razonsocial))||($c_razonsocial == '')) {
    ?>
		<tr>
		  <td>Clientes - Razon Social</td>
		  <td>Capturar el campo Razon Social, del Numero Cliente: <?php echo $c_numero_cliente; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if((empty($c_persona))||($c_persona == '')) {
    ?>
		<tr>
		  <td>Clientes - Persona</td>
		  <td>Capturar el campo Persona, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if((empty($c_persona))||($c_persona == '')) {
    ?>
		<tr>
		  <td>Clientes - Persona</td>
		  <td>Capturar el campo Persona, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if($c_persona=='MORAL' || $c_persona=='FISICA'){
		if((empty($c_rfc)) || ($c_rfc == '') || ($c_rfc == 'XEXX010101000')) {
	?>
		<tr>
		  <td>Clientes - RFC</td>
		  <td>Capturar el campo RFC y/o modificar si tiene el siguiente valor:XEXX010101000 no es valido, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php	
			$validacion = 1;
		}
		
		if((empty($c_codigopostal)) || ($c_codigopostal == '')) {
	?>
		<tr>
		  <td>Clientes - Código Postal</td>
		  <td>Capturar el campo Código Postal, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
			$validacion = 1;
		}
		
		if((empty($c_regimenfiscal)) || ($c_regimenfiscal == '')) {
	?>
		<tr>
		  <td>Clientes - Regimen Fiscal</td>
		  <td>Capturar el campo Regimen Fiscal, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
			$validacion = 1;
		}
		
	
	} //Fin valida Clientes Moral o Fisico
	
	if($c_persona=='EXTRANJERA'){
		if((empty($c_rfc)) || ($c_rfc == '') || ($c_rfc == 'XEXX010101000')) {
	?>
		<tr>
		  <td>Clientes - RFC</td>
		  <td>El campo RFC debe tener el siguiente valor: XEXX010101000, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php	
			$validacion = 1;
		}
		
		if((empty($st_codigopostal)) || ($st_codigopostal == '')) {
	?>
		<tr>
		  <td>Preferencias - Código Postal</td>
		  <td>Capturar el campo Código Postal, en el apartado Preferencias - Preferencias </td>
		</tr>
	<?php
			$validacion = 1;
		}
		
		if($c_regimenfiscal == '616') {
		}else{
	?>
		<tr>
		  <td>Clientes - Regimen Fiscal</td>
		  <td>Capturar en el campo Regimen Fiscal el valor: 616, del Cliente: <?php echo $c_razonsocial; ?> en el Catálogo de Clientes</td>
		</tr>
	<?php
			$validacion = 1;
		}
		
	} //Fin valida Clientes Extranjero
	
	
	
	//$c_persona
	//Buscar datos de FacturaPartidas de la Factura Seleccionada
	$sql_04="SELECT * FROM ".$prefijodb."facturapartidas WHERE FolioSub_RID =".$id_factura;
	//echo $sql_04;
	$res_04=mysql_query($sql_04);
	$partidas_total = mysql_num_rows($res_04);
	
	if($partidas_total > 0){
	
		while ($fila_exp04=mysql_fetch_array($res_04)){
			$fp_concepto_id = $fila_exp04['FolioConceptos_RID'];
			$fp_precio_unitario = $fila_exp04['PrecioUnitario'];
			$fp_cantidad = $fila_exp04['Cantidad'];
			
			//Buscar datos de Concepto
			$sql_05="SELECT * FROM ".$prefijodb."conceptos WHERE ID =".$fp_concepto_id;
			//echo $sql_05;
			$res_05=mysql_query($sql_05);
			while ($fila_exp05=mysql_fetch_array($res_05)){
				$c_folio_concepto = $fila_exp05['Concepto']; 
				$c_clave_producto_servicio = $fila_exp05['prodserv33dsc']; 
				$c_clave_unidad = $fila_exp05['claveunidad33']; 
				$c_objeto_impuesto = $fila_exp05['ObjetoImpuesto']; 
				$c_iva = $fila_exp05['IVA']; 
				$c_retencion = $fila_exp05['Retencion']; 
			}
			
			if((empty($c_clave_producto_servicio)) || ($c_clave_producto_servicio == '')) {
		?>
			<tr>
			  <td>Conceptos - prodserv33</td>
			  <td>Capturar el campo prodserv33, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			if((empty($c_clave_unidad)) || ($c_clave_unidad == '')) {
		?>
			<tr>
			  <td>Conceptos - claveunidad33</td>
			  <td>Capturar el campo claveunidad33, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			if((empty($c_objeto_impuesto)) || ($c_objeto_impuesto == '')) {
		?>
			<tr>
			  <td>Conceptos - Objeto Impuesto</td>
			  <td>Seleccionar valor en el campo Objeto Impuesto, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			
			if($c_iva >= 0) {
			} else {
		?>
			<tr>
			  <td>Conceptos - IVA</td>
			  <td>Capturar valor igual a Cero o Mayor a Cero en el campo IVA, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			if($c_persona == 'FISICA'){
				if($c_retencion == 0) {
				} else {
			?>
				<tr>
				  <td>Conceptos - Retencion</td>
				  <td>Capturar valor igual a Cero en el campo Retencion, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
				</tr>
			<?php
					$validacion = 1;
				}
			} else {
				if($c_retencion >= 0) {
				} else {
			?>
				<tr>
				  <td>Conceptos - Retencion</td>
				  <td>Capturar valor igual a Cero o Mayor a Cero en el campo Retencion, en el Concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
				</tr>
			<?php
					$validacion = 1;
				}
			}
			
			if($fp_cantidad > 0) {
			} else {
		?>
			<tr>
			  <td>Factura Partidas - Cantidad</td>
			  <td>El valor del campo Cantidad tiene que ser Mayor a Cero, en el apartado de Factura Partida en el concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			if($fp_precio_unitario > 0) {
			} else {
		?>
			<tr>
			  <td>Factura Partidas - Precio Unitario</td>
			  <td>El valor del campo Precio Unitario tiene que ser Mayor a Cero, en el apartado de Factura Partida en el concepto: <?php echo $c_folio_concepto; ?> del catalogo Conceptos y posterirormente actualizar las partidas en la Factura </td>
			</tr>
		<?php
				$validacion = 1;
			}
			
			
			
			
		}//Fin Busca FacturaPartidas
	
	} else {
		
		
	?>
		<tr>
		  <td>Factura Partidas</td>
		  <td>No se encontraron Partidas - Capturar al menos una partida en Factura Partidas</td>
		</tr>
	<?php
			$validacion = 1;
		
	}
	
	//$f_usocfdi33 = $fila_exp00['usocfdi33_RID'];
	//$f_metodopago33 = $fila_exp00['metodopago33_RID'];
	//$f_formapago33 = $fila_exp00['formapago33_RID'];
	
	if($f_usocfdi33 > 0) {
	} else {
    ?>
		<tr>
		  <td>Factura - usocfdi</td>
		  <td>Seleccionar valor en el campo usocfdi, en la pestaña CFDI4.0</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if($f_metodopago33 > 0) {
	} else {
    ?>
		<tr>
		  <td>Factura - metodopago33</td>
		  <td>Seleccionar valor en el campo metodopago33, en la pestaña CFDI4.0</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	if($f_formapago33 > 0) {
	
		if($metodopago_id2 == 'PPD') {
			if($formapago_id2 == '99'){
			}else{
				?>
					<tr>
					  <td>Factura - formapago33</td>
					  <td>En la pestaña CFDI4.0, el Metodo de Pago actual es PPD, por lo tanto la Forma de Pago debe ser 99 (POR DEFINIR)</td>
					</tr>
				<?php
					$validacion = 1;
			}
		} 
		
		if($metodopago_id2 == 'PUE') {
			if($formapago_id2 == '99'){
				?>
					<tr>
					  <td>Factura - formapago33</td>
					  <td>En la pestaña CFDI4.0, el Metodo de Pago actual es PUE, por lo tanto la Forma de Pago NO puede ser 99 (POR DEFINIR)</td>
					</tr>
				<?php
					$validacion = 1;
			}else{
				
			}
		} 
		
	} else {
    ?>
		<tr>
		  <td>Factura - formapago33</td>
		  <td>Seleccionar valor en el campo formapago33, en la pestaña CFDI4.0</td>
		</tr>
	<?php
		$validacion = 1;
	}
	
	//Validación Carta Porte Traslado--------------------------------------------------------------------------------------------------------------
	if($f_complemento_traslado == 1){
		if($f_tipoviaje == 'NACIONAL'){
			
			//REMITENTE-------------------------------------------------------------------------------------
			if($f_remitente=='' || (empty($f_remitente))){
				?>
					<tr>
					  <td>Factura - Remitente</td>
					  <td>El campo Remitente de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenterfc=='' || (empty($f_remitenterfc))){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El campo Remitente RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitenterfc == 'XEXX010101000'){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El Remitente RFC de la Factura, no puede ser XEXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				}
			}
			
			
			$f_remitentecitacarga_t = strtotime($f_remitentecitacarga);
			$rem_citacarga_hora = date("H:i:s",$f_remitentecitacarga_t);
			$f_t2 = '00:00:00';
			$f_temp2_t = strtotime($f_t2);
			$f_temp2 = date("H:i:s",$f_temp2_t);			
			if($f_remitentecitacarga=='' || (empty($f_remitentecitacarga))){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_citacarga_hora == $f_temp2){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_remitentecodigopostal=='' || (empty($f_remitentecodigopostal))){
				?>
					<tr>
					  <td>Factura - Remitente Codigo Postal</td>
					  <td>El campo Remitente Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenteestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Remitente Estado</td>
					  <td>Debe seleccionar un elemento del campo Remitente Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentecalle=='' || (empty($f_remitentecalle))){
				?>
					<tr>
					  <td>Factura - Remitente Calle</td>
					  <td>El campo Remitente Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_remitentenumext=='' || (empty($f_remitentenumext))){
				?>
					<tr>
					  <td>Factura - Remitente Numero Exterior</td>
					  <td>El campo Remitente Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentepais=='' || (empty($f_remitentepais))){
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitentepais == 'MEX'){
				} else {
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais debe contener el valor MEX</td>
					</tr>
				<?php
					$validacion = 1;
				}
			}
			
			if($f_remitentenumregidtrib=='' || (empty($f_remitentenumregidtrib))){
			} else {
				?>
					<tr>
					  <td>Factura - Remitente Num Reg Id Trib</td>
					  <td>El campo Remitente Num Reg Id Trib de la Factura, debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			//DESTINATARIO-------------------------------------------------------------------------------------
			if($f_destinatario=='' || (empty($f_destinatario))){
				?>
					<tr>
					  <td>Factura - Destinatario</td>
					  <td>El campo Destinatario de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariorfc=='' || (empty($f_destinatariorfc))){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El campo Destinatario RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariorfc == 'XEXX010101000'){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El Destinatario RFC de la Factura, no puede ser XEXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				}
			}
			
			
			$f_destinatariocitacarga_t = strtotime($f_destinatariocitacarga);
			$rem_descitacarga_hora = date("H:i:s",$f_destinatariocitacarga_t);	
			$f_tt2 = '00:00:00';
			$f_temp2_tt = strtotime($f_tt2);
			$f_ttemp2 = date("H:i:s",$f_temp2_tt);			
			if($f_destinatariocitacarga=='' || (empty($f_destinatariocitacarga))){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_descitacarga_hora == $f_ttemp2){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_destinatariocodigopostal=='' || (empty($f_destinatariocodigopostal))){
				?>
					<tr>
					  <td>Factura - Destinatario Codigo Postal</td>
					  <td>El campo Destinatario Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatarioestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Destinatario Estado</td>
					  <td>Debe seleccionar un elemento del campo Destinatario Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariocalle=='' || (empty($f_destinatariocalle))){
				?>
					<tr>
					  <td>Factura - Destinatario Calle</td>
					  <td>El campo Destinatario Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_destinatarionumext=='' || (empty($f_destinatarionumext))){
				?>
					<tr>
					  <td>Factura - Destinatario Numero Exterior</td>
					  <td>El campo Destinatario Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariopais=='' || (empty($f_destinatariopais))){
				?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariopais == 'MEX'){
				} else {
				?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais debe contener el valor MEX</td>
					</tr>
				<?php
					$validacion = 1;
				}
			}
			
			if($f_destinatarionumregidtrib=='' || (empty($f_destinatarionumregidtrib))){
			} else {
				?>
					<tr>
					  <td>Factura - Destinatario Num Reg Id Trib</td>
					  <td>El campo Destinatario Num Reg Id Trib de la Factura, debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
				
				
		} elseif ($f_tipoviaje == 'EXPORTACIÓN'){
			
			//REMITENTE-------------------------------------------------------------------------------------
			if($f_remitente=='' || (empty($f_remitente))){
				?>
					<tr>
					  <td>Factura - Remitente</td>
					  <td>El campo Remitente de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenterfc=='' || (empty($f_remitenterfc))){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El campo Remitente RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitenterfc == 'XAXX010101000'){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El Remitente RFC de la Factura, no puede ser XAXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				} 
			}
			
			
			$f_remitentecitacarga_t = strtotime($f_remitentecitacarga);
			$rem_citacarga_hora = date("H:i:s",$f_remitentecitacarga_t);
			$f_t2 = '00:00:00';
			$f_temp2_t = strtotime($f_t2);
			$f_temp2 = date("H:i:s",$f_temp2_t);			
			if($f_remitentecitacarga=='' || (empty($f_remitentecitacarga))){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_citacarga_hora == $f_temp2){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_remitentecodigopostal=='' || (empty($f_remitentecodigopostal))){
				?>
					<tr>
					  <td>Factura - Remitente Codigo Postal</td>
					  <td>El campo Remitente Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenteestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Remitente Estado</td>
					  <td>Debe seleccionar un elemento del campo Remitente Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentecalle=='' || (empty($f_remitentecalle))){
				?>
					<tr>
					  <td>Factura - Remitente Calle</td>
					  <td>El campo Remitente Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_remitentenumext=='' || (empty($f_remitentenumext))){
				?>
					<tr>
					  <td>Factura - Remitente Numero Exterior</td>
					  <td>El campo Remitente Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentepais=='' || (empty($f_remitentepais))){
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitentepais == 'MEX'){
				} else {
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais debe contener el valor MEX</td>
					</tr>
				<?php
					$validacion = 1;
				}
			}
			
			if($f_remitentenumregidtrib=='' || (empty($f_remitentenumregidtrib))){
			} else {
				?>
					<tr>
					  <td>Factura - Remitente Num Reg Id Trib</td>
					  <td>El campo Remitente Num Reg Id Trib de la Factura, debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			//DESTINATARIO-------------------------------------------------------------------------------------
			if($f_destinatario=='' || (empty($f_destinatario))){
				?>
					<tr>
					  <td>Factura - Destinatario</td>
					  <td>El campo Destinatario de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariorfc=='' || (empty($f_destinatariorfc))){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El campo Destinatario RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariorfc == 'XAXX010101000'){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El Destinatario RFC de la Factura, no puede ser XAXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				}
			}
			
			
			$f_destinatariocitacarga_t = strtotime($f_destinatariocitacarga);
			$rem_descitacarga_hora = date("H:i:s",$f_destinatariocitacarga_t);	
			$f_tt2 = '00:00:00';
			$f_temp2_tt = strtotime($f_tt2);
			$f_ttemp2 = date("H:i:s",$f_temp2_tt);			
			if($f_destinatariocitacarga=='' || (empty($f_destinatariocitacarga))){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_descitacarga_hora == $f_ttemp2){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_destinatariocodigopostal=='' || (empty($f_destinatariocodigopostal))){
				?>
					<tr>
					  <td>Factura - Destinatario Codigo Postal</td>
					  <td>El campo Destinatario Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatarioestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Destinatario Estado</td>
					  <td>Debe seleccionar un elemento del campo Destinatario Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariocalle=='' || (empty($f_destinatariocalle))){
				?>
					<tr>
					  <td>Factura - Destinatario Calle</td>
					  <td>El campo Destinatario Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_destinatarionumext=='' || (empty($f_destinatarionumext))){
				?>
					<tr>
					  <td>Factura - Destinatario Numero Exterior</td>
					  <td>El campo Destinatario Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariopais=='' || (empty($f_destinatariopais))){
				?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariopais == 'MEX'){
					?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais debe ser diferente al valor MEX</td>
					</tr>
					<?php
					$validacion = 1;
				} 
			}
			
			if($f_destinatarionumregidtrib=='' || (empty($f_destinatarionumregidtrib))){
				?>
					<tr>
					  <td>Factura - Destinatario Num Reg Id Trib</td>
					  <td>El campo Destinatario Num Reg Id Trib de la Factura, no debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} 
			
			
			
		} elseif($f_tipoviaje == 'IMPORTACIÓN'){
			//REMITENTE-------------------------------------------------------------------------------------
			if($f_remitente=='' || (empty($f_remitente))){
				?>
					<tr>
					  <td>Factura - Remitente</td>
					  <td>El campo Remitente de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenterfc=='' || (empty($f_remitenterfc))){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El campo Remitente RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitenterfc == 'XAXX010101000'){
				?>
					<tr>
					  <td>Factura - Remitente RFC</td>
					  <td>El Remitente RFC de la Factura, no puede ser XAXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				} 
			}
			
			
			$f_remitentecitacarga_t = strtotime($f_remitentecitacarga);
			$rem_citacarga_hora = date("H:i:s",$f_remitentecitacarga_t);
			$f_t2 = '00:00:00';
			$f_temp2_t = strtotime($f_t2);
			$f_temp2 = date("H:i:s",$f_temp2_t);			
			if($f_remitentecitacarga=='' || (empty($f_remitentecitacarga))){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_citacarga_hora == $f_temp2){
				?>
					<tr>
					  <td>Factura - Remitente Cita de Carga</td>
					  <td>El campo Remitente Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_remitentecodigopostal=='' || (empty($f_remitentecodigopostal))){
				?>
					<tr>
					  <td>Factura - Remitente Codigo Postal</td>
					  <td>El campo Remitente Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitenteestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Remitente Estado</td>
					  <td>Debe seleccionar un elemento del campo Remitente Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentecalle=='' || (empty($f_remitentecalle))){
				?>
					<tr>
					  <td>Factura - Remitente Calle</td>
					  <td>El campo Remitente Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_remitentenumext=='' || (empty($f_remitentenumext))){
				?>
					<tr>
					  <td>Factura - Remitente Numero Exterior</td>
					  <td>El campo Remitente Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_remitentepais=='' || (empty($f_remitentepais))){
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_remitentepais == 'MEX'){
				?>
					<tr>
					  <td>Factura - Remitente Pais</td>
					  <td>El campo Remitente Pais debe ser diferente al valor MEX</td>
					</tr>
				<?php
					$validacion = 1;
				} else {
				
				}
			}
			
			if($f_remitentenumregidtrib=='' || (empty($f_remitentenumregidtrib))){
				?>
					<tr>
					  <td>Factura - Remitente Num Reg Id Trib</td>
					  <td>El campo Remitente Num Reg Id Trib de la Factura, no debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} 
			
			//DESTINATARIO-------------------------------------------------------------------------------------
			if($f_destinatario=='' || (empty($f_destinatario))){
				?>
					<tr>
					  <td>Factura - Destinatario</td>
					  <td>El campo Destinatario de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariorfc=='' || (empty($f_destinatariorfc))){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El campo Destinatario RFC de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariorfc == 'XAXX010101000'){
				?>
					<tr>
					  <td>Factura - Destinatario RFC</td>
					  <td>El Destinatario RFC de la Factura, no puede ser XAXX010101000</td>
					</tr>
				<?php	
					$validacion = 1;
					
				}
			}
			
			
			$f_destinatariocitacarga_t = strtotime($f_destinatariocitacarga);
			$rem_descitacarga_hora = date("H:i:s",$f_destinatariocitacarga_t);	
			$f_tt2 = '00:00:00';
			$f_temp2_tt = strtotime($f_tt2);
			$f_ttemp2 = date("H:i:s",$f_temp2_tt);			
			if($f_destinatariocitacarga=='' || (empty($f_destinatariocitacarga))){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($rem_descitacarga_hora == $f_ttemp2){
				?>
					<tr>
					  <td>Factura - Destinatario Cita de Carga</td>
					  <td>El campo Destinatario Cita de Carga de la Factura, no puede tener Hora 00:00:00</td>
					</tr>
				<?php	
					$validacion = 1;
				}
			}
			
			if($f_destinatariocodigopostal=='' || (empty($f_destinatariocodigopostal))){
				?>
					<tr>
					  <td>Factura - Destinatario Codigo Postal</td>
					  <td>El campo Destinatario Código Postal de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatarioestado > 0){
			} else {
				?>
					<tr>
					  <td>Factura - Destinatario Estado</td>
					  <td>Debe seleccionar un elemento del campo Destinatario Estado de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariocalle=='' || (empty($f_destinatariocalle))){
				?>
					<tr>
					  <td>Factura - Destinatario Calle</td>
					  <td>El campo Destinatario Calle de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
				
			
			if($f_destinatarionumext=='' || (empty($f_destinatarionumext))){
				?>
					<tr>
					  <td>Factura - Destinatario Numero Exterior</td>
					  <td>El campo Destinatario Numero Exterior de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			if($f_destinatariopais=='' || (empty($f_destinatariopais))){
				?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			} else {
				if($f_destinatariopais == 'MEX'){
					
				} else {
					?>
					<tr>
					  <td>Factura - Destinatario Pais</td>
					  <td>El campo Destinatario Pais debe ser el valor MEX</td>
					</tr>
					<?php
					$validacion = 1;
				}
			}
			
			if($f_destinatarionumregidtrib=='' || (empty($f_destinatarionumregidtrib))){
				
			} else {
				?>
					<tr>
					  <td>Factura - Destinatario Num Reg Id Trib</td>
					  <td>El campo Destinatario Num Reg Id Trib de la Factura, debe estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
			}
			
			
		} else {
		?>
			<tr>
			  <td>Factura - Tipo Viaje</td>
			  <td>Seleccionar valor en el campo Tipo Viaje</td>
			</tr>
		<?php
			$validacion = 1;
		
		}
		
		if($f_codigo_origen=='' || (empty($f_codigo_origen))){
		?>
			<tr>
			  <td>Factura - Código Origen</td>
			  <td>Revisar el cliente <?php echo $c_razonsocial; ?> en el cátalogo de Clientes, en la pestaña Destinos y revisar que el registro correspondiente cuente con ID Cliente Destino generado. Posteriormente en la Factura volver a ejecutar el proceso Remitentes y volver a seleccionar el registro que corresponda</td>
			</tr>
		<?php
			$validacion = 1;	
		} 
		
		if($f_codigo_destino=='' || (empty($f_codigo_destino))){
		?>
			<tr>
			  <td>Factura - Código Destino</td>
			  <td>Revisar el cliente <?php echo $c_razonsocial; ?> en el cátalogo de Clientes, en la pestaña Destinos y revisar que el registro correspondiente cuente con ID Cliente Destino generado. Posteriormente en la Factura volver a ejecutar el proceso Destinatarios y volver a seleccionar el registro que corresponda</td>
			</tr>
		<?php
			$validacion = 1;	
		} 
		
		if($f_ruta_id > 0){
			//Consultar Ruta
			$sql_r="SELECT * FROM ".$prefijodb."rutas WHERE id = ".$f_ruta_id;
			//echo $sql_r;
			$res_r=mysql_query($sql_r);
			while ($fila_expr=mysql_fetch_array($res_r)){
				$r_kms = $fila_expr['Kms'];
			}
			
			if($r_kms > 0){
			} else {
			?>
				<tr>
				  <td>Catálogo Ruta</td>
				  <td>Revisar en el catalogo Ruta, que el campo Kms sea mayor a cero, de la Ruta correspondiente a la Factura</td>
				</tr>
			<?php
				$validacion = 1;
			}
			
		} else {
		?>
			<tr>
			  <td>Factura - Ruta</td>
			  <td>Seleccionar una Ruta en la Factura</td>
			</tr>
		<?php
			$validacion = 1;	
		} 
		
		if($f_distancia_recorrida > 0){
		} else {
		?>
			<tr>
			  <td>Factura - Distancia Recorrida</td>
			  <td>Seleccionar una Ruta en la Factura</td>
			</tr>
		<?php
			$validacion = 1;
		}
		
		if($f_clave_unidad_peso_id > 0){
		} else {
		?>
			<tr>
			  <td>Factura - Clave Unidad Peso</td>
			  <td>Seleccionar elemento del campo Clave Unidad Peso en la Factura</td>
			</tr>
		<?php
			$validacion = 1;
		}
		
		if($f_operador_id > 0){
			//Consultar Operador
			$sql_o="SELECT * FROM ".$prefijodb."operadores WHERE id = ".$f_operador_id;
			//echo $sql_o;
			$res_o=mysql_query($sql_o);
			while ($fila_expo=mysql_fetch_array($res_o)){
				$o_operador = $fila_expo['Operador'];
				$o_calle = $fila_expo['Calle'];
				$o_num_ext = $fila_expo['NumExt'];
				$o_codigo_postal = $fila_expo['CodigoPostal'];
				$o_rfc = $fila_expo['RFC'];
				$o_licencia = $fila_expo['LicenciaNo'];
				$o_residencia_fiscal = $fila_expo['ResidenciaFiscal'];
				$o_tipo_figura = $fila_expo['TipoFigura'];
				$o_num_reg_id_trib = $fila_expo['NumRegIdTrib'];
				
				
				if($o_operador=='' || (empty($o_operador))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Operador</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_calle=='' || (empty($o_calle))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Calle del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_num_ext=='' || (empty($o_num_ext))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Num Ext del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_codigo_postal=='' || (empty($o_codigo_postal))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Código Postal del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_rfc=='' || (empty($o_rfc))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo RFC del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_licencia=='' || (empty($o_licencia))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Licencia No del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_residencia_fiscal=='' || (empty($o_residencia_fiscal))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Residencia Fiscal del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_tipo_figura=='' || (empty($o_tipo_figura))){
				?>
					<tr>
					  <td>Catalogo Operadores</td>
					  <td>Capturar el campo Tipo Figura del Operador de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($o_residencia_fiscal=='MEX'){
				} else {
					if($o_num_reg_id_trib=='' || (empty($o_num_reg_id_trib))){
					?>
						<tr>
						  <td>Catalogo Operadores</td>
						  <td>Capturar el campo Num Reg Id Trib del Operador de la Factura</td>
						</tr>
					<?php	
						$validacion = 1;
						
					}
				
				}
				
			}
		} else {
		?>
			<tr>
			  <td>Factura - Operador</td>
			  <td>Seleccionar Operador en la Factura</td>
			</tr>
		<?php
			$validacion = 1;
		}
		
		
		
		if($f_unidad_id > 0){
			//Consultar Unidad
			$sql_u="SELECT * FROM ".$prefijodb."unidades WHERE id = ".$f_unidad_id;
			//echo $sql_u;
			$res_u=mysql_query($sql_u);
			while ($fila_expu=mysql_fetch_array($res_u)){
				$u_unidad = $fila_expu['Unidad'];
				$u_placas = $fila_expu['Placas'];
				$u_anio = $fila_expu['Ano'];
				$u_configautotransporte = $fila_expu['ConfigAutotranporte_RID'];
				$u_polizano = $fila_expu['PolizaNo'];
				$u_aseguradora = $fila_expu['AseguradoraUnidad_RID'];
				
				if($u_unidad=='' || (empty($u_unidad))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Unidad de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($u_placas=='' || (empty($u_placas))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Placas de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($u_anio=='' || (empty($u_anio))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Año de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($u_configautotransporte > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Config Autotransporte de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($u_polizano=='' || (empty($u_polizano))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Poliza No de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($u_aseguradora > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Aseguradora de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				
				
			}
		} else {
		?>
			<tr>
			  <td>Factura - Unidad</td>
			  <td>Seleccionar Unidad en la Factura</td>
			</tr>
		<?php
			$validacion = 1;
		}
		
		//Valida Remolque_1
		if($f_remolque_id > 0){
			//Consultar Remolque1
			$sql_rm1="SELECT * FROM ".$prefijodb."unidades WHERE id = ".$f_remolque_id;
			//echo $sql_rm1;
			$res_rm1=mysql_query($sql_rm1);
			while ($fila_exp_rm1=mysql_fetch_array($res_rm1)){
				$rm1_unidad = $fila_exp_rm1['Unidad'];
				$rm1_placas = $fila_exp_rm1['Placas'];
				$rm1_anio = $fila_exp_rm1['Ano'];
				$rm1_subtiporem = $fila_exp_rm1['SubTipoRem_RID'];
				$rm1_polizano = $fila_exp_rm1['PolizaNo'];
				$rm1_aseguradora = $fila_exp_rm1['AseguradoraUnidad_RID'];
				
				if($rm1_unidad=='' || (empty($rm1_unidad))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Unidad de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm1_placas=='' || (empty($rm1_placas))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Placas de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm1_anio=='' || (empty($rm1_anio))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Año de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm1_subtiporem > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Sub Tipo Rem de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm1_polizano=='' || (empty($rm1_polizano))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Poliza No de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm1_aseguradora > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Aseguradora de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				

			}
			
		}
		
		
		//Valida Remolque_2
		if($f_remolque2_id > 0){
			//Consultar Remolque1
			$sql_rm2="SELECT * FROM ".$prefijodb."unidades WHERE id = ".$f_remolque2_id;
			//echo $sql_rm2;
			$res_rm2=mysql_query($sql_rm2);
			while ($fila_exp_rm2=mysql_fetch_array($res_rm2)){
				$rm2_unidad = $fila_exp_rm2['Unidad'];
				$rm2_placas = $fila_exp_rm2['Placas'];
				$rm2_anio = $fila_exp_rm2['Ano'];
				$rm2_subtiporem = $fila_exp_rm2['SubTipoRem'];
				$rm2_polizano = $fila_exp_rm2['PolizaNo'];
				$rm2_aseguradora = $fila_exp_rm2['AseguradoraUnidad'];
				
				if($rm2_unidad=='' || (empty($rm2_unidad))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Unidad de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm2_placas=='' || (empty($rm2_placas))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Placas de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm2_anio=='' || (empty($rm2_anio))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Año de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm2_subtiporem > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Sub Tipo Rem de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm2_polizano=='' || (empty($rm2_polizano))){
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Poliza No de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($rm2_aseguradora > 0){
				} else {
				?>
					<tr>
					  <td>Catalogo Unidades</td>
					  <td>Capturar el campo Aseguradora de la Unidad de la Factura</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				

			}
			
		}
		
		
		if($f_total_reg_fs > 0){
			$fsub_tmp = 1;
			$sql_f_sub="SELECT * FROM ".$prefijodb."facturassub WHERE FolioSub_RID = ".$id_factura." ORDER BY id";
			//echo $sql_f_sub;
			$res_f_sub=mysql_query($sql_f_sub);
			while ($fila_exp_f_sub=mysql_fetch_array($res_f_sub)){
				
				$fsub_cantidad = $fila_exp_f_sub['Cantidad'];
				$fsub_embalaje = $fila_exp_f_sub['Embalaje'];
				$fsub_peso= $fila_exp_f_sub['Peso'];
				$fsub_descripcion = $fila_exp_f_sub['Descripcion'];
				$fsub_claveunidadpeso= $fila_exp_f_sub['ClaveUnidadPeso_RID'];
				$fsub_claveprodserv = $fila_exp_f_sub['ClaveProdServCP_RID'];
				$fsub_tipo_embalaje= $fila_exp_f_sub['TipoEmbalaje_RID'];
				$fsub_material_peligroso_c = $fila_exp_f_sub['MaterialPeligrosoC'];
				$fsub_material_peligroso = $fila_exp_f_sub['MaterialPeligroso_RID'];
				$fsub_fraccion_arancelaria = $fila_exp_f_sub['FraccionArancelaria_RID'];
				$fsub_numero_pedimento = $fila_exp_f_sub['NumeroPedimento'];
				$fsub_uuid_comercio_ext = $fila_exp_f_sub['UUIDComercioExt'];
				
				if($fsub_cantidad > 0){
				} else {
				?>
					<tr>
						<td>Embalaje - <?php echo $fsub_tmp; ?></td>
						<td>El campo Cantidad del Embalaje <?php echo $fsub_tmp; ?> de la Factura, debe ser mayor a cero</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_embalaje=='' || (empty($fsub_embalaje))){
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>El campo Embalaje del Embalaje <?php echo $fsub_tmp; ?> de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_peso > 0){
				} else {
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>El campo Peso del Embalaje <?php echo $fsub_tmp; ?> de la Factura, debe ser mayor a cero</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_descripcion=='' || (empty($fsub_descripcion))){
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>El campo Descripción del Embalaje <?php echo $fsub_tmp; ?> de la Factura, no puede estar vacío</td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_claveunidadpeso > 0){
				} else {
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>Debe seleccionar un elemento del campo Clave Unidad Peso del Embalaje <?php echo $fsub_tmp; ?> de la Factura </td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_claveprodserv > 0){
				} else {
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>Debe seleccionar un elemento del campo Clave Prod Serv CP del Embalaje <?php echo $fsub_tmp; ?> de la Factura </td>
					</tr>
				<?php	
					$validacion = 1;
				}
				
				if($fsub_tipo_embalaje > 0){
				} else {
					if($f_tipoviaje == 'EXPORTACIÓN'){
					} else {
				?>
					<tr>
					  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
					  <td>Debe seleccionar un elemento del campo Tipo Embalaje del Embalaje <?php echo $fsub_tmp; ?> de la Factura </td>
					</tr>
				<?php	
						$validacion = 1;
					}
				}
				
				if($fsub_material_peligroso_c > 0){
					if($fsub_material_peligroso > 0){
					} else {
					?>
						<tr>
						  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
						  <td>Debe seleccionar un elemento del campo Material Peligroso del Embalaje <?php echo $fsub_tmp; ?> de la Factura </td>
						</tr>
					<?php	
						$validacion = 1;
					}
				} 
				
				/*if($f_tipoviaje == 'EXPORTACIÓN'){
					if($fsub_fraccion_arancelaria > 0){
					} else {
					?>
						<tr>
						  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
						  <td>Debe seleccionar un elemento del campo Fraccion Arancelaria del Embalaje <?php echo $fsub_tmp; ?> de la Factura </td>
						</tr>
					<?php	
						$validacion = 1;
					}
					
					if($fsub_numero_pedimento=='' || (empty($fsub_numero_pedimento))){
					?>
						<tr>
						  <td>Embalaje - <?php echo $fsub_tmp; ?></td>
						  <td>El campo Numero Pedimento del Embalaje <?php echo $fsub_tmp; ?> de la Factura, no puede estar vacío</td>
						</tr>
					<?php	
						$validacion = 1;
					}
					
					//fsub_uuid_comercio_ext es OPCIONAL
					
				}*/
				
				
				
					
				
				
				$fsub_tmp = $fsub_tmp  + 1;
			}
			
			
		} // Fin valida registros FacturaSub
		
		
		
		
		
	
	} //Fin Validación Carta Porte Traslado ---------------------------------------------------------
	
	
	
	
	
	
	
	
	
	
	
	
	
    
	if($validacion == 0) {
	?>
		<tr>
		  <td colspan="2" style="text-align:center;"><h2>Todo parece correcto, documento Validado <i class="glyphicon glyphicon-ok"></h2></i> </td>
		</tr>
		
	<?php
	}


}//Fin Busca datos Factura

?>
	  </tbody>
	</table>
	</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>	



<!-- http://localhost/cfdipro/validar_documentacion.php?prefijodb=tractosoft09_&id=906717 -->

