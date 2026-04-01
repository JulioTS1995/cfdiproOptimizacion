
<?php  
//Recibir variables
$prefijobd = $_GET['prefijodb'];
$ide_viaje = $_GET['ide'];

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_viajes_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

$resSQL00 ="SELECT * FROM ".$prefijobd."viajes2 WHERE ID=".$ide_viaje;
//echo $resSQL00;
$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
while($rowSQL00 = mysql_fetch_array($runSQL00)){
	//Buscar facturas 
	$viaje_xfolio = $rowSQL00['XFolio'];
	$viaje_comentarios = $rowSQL00['Comentarios'];
	$viaje_operador_id = $rowSQL00['Operador_RID'];
	$viaje_unidad_id = $rowSQL00['Unidad_RID'];
	$viaje_remolque_id = $rowSQL00['uRemolqueA_RID'];
	$viaje_fecha_t = $rowSQL00['Creado'];
	$viaje_fecha = date("d-m-Y H:i:s", strtotime($viaje_fecha_t));
	
}

//Buscar en Operador
if($viaje_operador_id > 0){
	$resSQL04 ="SELECT * FROM ".$prefijobd."operadores where id =".$viaje_operador_id;
	//echo $resSQL04;
	$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
	while($rowSQL04 = mysql_fetch_array($runSQL04)){
		$operador_nombre = $rowSQL04['Operador'];
	}
} else {
	$operador_nombre = '';
}

//Buscar Remolque
if($viaje_remolque_id > 0){
	$resSQL05 ="SELECT * FROM ".$prefijobd."unidades where id =".$viaje_remolque_id;
	//echo $resSQL05;
	$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
	while($rowSQL05 = mysql_fetch_array($runSQL05)){
		$remolque_nombre = $rowSQL05['Unidad'];
		$remolque_placas = $rowSQL05['Placas'];
	}
} else {
	$remolque_nombre = '';
	$remolque_placas = '';
}
			
			
//Buscar Unidad
if($viaje_unidad_id > 0){
	$resSQL06 ="SELECT * FROM ".$prefijobd."unidades where id =".$viaje_unidad_id;
	//echo $resSQL06;
	$runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
	while($rowSQL06 = mysql_fetch_array($runSQL06)){
		$unidad_nombre = $rowSQL06['Unidad'];
		$unidad_placas = $rowSQL06['Placas'];
	}
} else {
	$unidad_nombre = '';
	$unidad_placas = '';
}


?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
		<table class="table table-hover table-responsive table-condensed" border="1" id="table">
			<thead>
				<tr>
					<td align="left" style="font-size: 12px;">INFORME NO:</td>
					<td align="left" style="font-size: 12px;" colspan="18"><?php echo $viaje_comentarios; ?></td>
				</tr>
				<tr>
					<td align="left" style="font-size: 12px;">PLACAS TRACTO:</td>
					<td align="left" style="font-size: 12px;" colspan="18"><?php echo $unidad_nombre." / ".$unidad_placas; ?></td>
				</tr>
				<tr>
					<td align="left" style="font-size: 12px;">PLACAS REMOLQUE:</td>
					<td align="left" style="font-size: 12px;" colspan="18"><?php echo $remolque_nombre." / ".$remolque_placas; ?></td>
				</tr>
				<tr>
					<td align="left" style="font-size: 12px;">FECHA:</td>
					<td align="left" style="font-size: 12px;" colspan="18"><?php echo $viaje_fecha; ?></td>
				</tr>
				<tr>
					<th align="center" style="font-size: 12px;">TALON</th>
					<th align="center" style="font-size: 12px;">REMITENTE</th>
					<th align="center" style="font-size: 12px;">DESTINATARIO</th>
					<th align="center" style="font-size: 12px;">DESTINO</th>
					<th align="center" style="font-size: 12px;">BULTOS</th>
					<th align="center" style="font-size: 12px;">CONTENIDO</th>
					<th align="center" style="font-size: 12px;">PESO</th>
					<th align="center" style="font-size: 12px;">VALOR</th>
					<th align="center" style="font-size: 12px;">SEGURO</th>
					<th align="center" style="font-size: 12px;">FLETE</th>
					<th align="center" style="font-size: 12px;">TIPO PAGO</th>
					<th align="center" style="font-size: 12px;">MANIOBRAS</th>
					<th align="center" style="font-size: 12px;">SUBTOTAL</th>
					<th align="center" style="font-size: 12px;">IVA</th>
					<th align="center" style="font-size: 12px;">IVA RETENIDO</th>
					<th align="center" style="font-size: 12px;">TOTAL</th>
					<th align="center" style="font-size: 12px;">ESTATUS </th>
					<th align="center" style="font-size: 12px;">METODO DE PAGO</th>
					<th align="center" style="font-size: 12px;">OBSERVACIONES</th>
				</tr>
			</thead>
		<tbody>
	<?php
		
		$rem_cantidad_total_t = 0;
		$rem_valor_declarado_total_t = 0;
		$rem_seguro_total_t = 0;
		$rem_flete_total_t = 0;
		$rem_descarga_total_t = 0;
		$rem_subtotal_total_t = 0;
		$rem_impuesto_total_t = 0;
		$rem_retenido_total_t = 0;
		$rem_total_total_t = 0;
	
		$resSQL01 ="SELECT * FROM ".$prefijobd."remisiones WHERE FolioSubViajes_RID=".$ide_viaje." ORDER BY XFolio";
		//echo $resSQL01;
		$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
		while($rowSQL01 = mysql_fetch_array($runSQL01)){
		//Buscar facturas 
			$rem_id = $rowSQL01['ID'];
			$rem_xfolio = $rowSQL01['XFolio'];
			$rem_remitente = $rowSQL01['Remitente'];
			$rem_destinatario = $rowSQL01['Destinatario'];
			$rem_se_entregara = $rowSQL01['DestinatarioSeEntregara'];
			$rem_cantidad_t = $rowSQL01['xCantidadTotal'];
			$rem_cantidad = number_format($rem_cantidad_t,2);
			$rem_peso_t = $rowSQL01['xPesoTotal'];
			$rem_peso = number_format($rem_peso_t,2);
			$rem_valor_declarado_t = $rowSQL01['ValorDeclarado'];
			$rem_valor_declarado = number_format($rem_valor_declarado_t,2);
			$rem_seguro_t = $rowSQL01['ySeguro'];
			$rem_seguro = number_format($rem_seguro_t,2);
			$rem_flete_t = $rowSQL01['yFlete'];
			$rem_flete = number_format($rem_flete_t,2);
			$rem_fletetipo = $rowSQL01['FleteTipo'];
			$rem_descarga_t = $rowSQL01['yDescarga'];
			$rem_descarga = number_format($rem_descarga_t,2);
			$rem_subtotal_t = $rowSQL01['zSubtotal'];
			$rem_subtotal = number_format($rem_subtotal_t,2);
			$rem_impuesto_t = $rowSQL01['zImpuesto'];
			$rem_impuesto = number_format($rem_impuesto_t,2);
			$rem_retenido_t = $rowSQL01['zRetenido'];
			$rem_retenido = number_format($rem_retenido_t,2);
			$rem_total_t = $rowSQL01['zTotal'];
			$rem_total = number_format($rem_total_t,2);
			
			//Buscar en Remisiones Sub
			if($rem_id > 0){
				$resSQL03 ="SELECT * FROM ".$prefijobd."remisionessub WHERE FolioSub_RID =".$rem_id." LIMIT 1";
				//echo $resSQL03;
				$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
				while($rowSQL03 = mysql_fetch_array($runSQL03)){
					$remsub_descripcion = $rowSQL03['Descripcion'];
					
				}
			} else {
				$remsub_descripcion = '';
			}

			
			
	?>
					
			<tr>
				<td align="center" ><?php echo $rem_xfolio; ?></td>
				<td align="left" ><?php echo $rem_remitente; ?></td>
                <td align="left" ><?php echo $rem_destinatario; ?></td>
                <td align="left" ><?php echo $rem_se_entregara; ?></td>
                <td align="center" ><?php echo $rem_cantidad; ?></td>
                <td align="left" ><?php echo $remsub_descripcion; ?></td>
				<td align="right" ><?php echo $rem_peso; ?></td>
                <td align="right" ><?php echo $rem_valor_declarado; ?></td>
                <td align="right" ><?php echo $rem_seguro; ?></td>
				<td align="right" ><?php echo $rem_flete; ?></td>
				<td align="center" ><?php echo $rem_fletetipo; ?></td>
				<td align="right" ><?php echo $rem_descarga; ?></td>
				<td align="right" ><?php echo $rem_subtotal; ?></td>
				<td align="right" ><?php echo $rem_impuesto; ?></td>
				<td align="right" ><?php echo $rem_retenido; ?></td>
				<td align="right" ><?php echo $rem_total; ?></td>
				<td align="left" ></td>
				<td align="left" ></td>
				<td align="left" ></td>
            </tr>
			
    <?php	
			if($rem_cantidad_t > 0){
			} else {
				$rem_cantidad_t = 0;
			}
			if($rem_valor_declarado_t > 0){
			} else {
				$rem_valor_declarado_t = 0;
			}
			if($rem_seguro_t > 0){
			} else {
				$rem_seguro_t = 0;
			}
			if($rem_flete_t > 0){
			} else {
				$rem_flete_t = 0;
			}
			if($rem_descarga_t > 0){
			} else {
				$rem_descarga_t = 0;
			}
			if($rem_subtotal_t > 0){
			} else {
				$rem_subtotal_t = 0;
			}
			if($rem_impuesto_t > 0){
			} else {
				$rem_impuesto_t = 0;
			}
			if($rem_retenido_t > 0){
			} else {
				$rem_retenido_t = 0;
			}
			if($rem_total_t > 0){
			} else {
				$rem_total_t = 0;
			}
	
	
			$rem_cantidad_total_t = $rem_cantidad_total_t + $rem_cantidad_t;
			$rem_valor_declarado_total_t = $rem_valor_declarado_total_t + $rem_valor_declarado_t;
			$rem_seguro_total_t = $rem_seguro_total_t + $rem_seguro_t;
			$rem_flete_total_t = $rem_flete_total_t + $rem_flete_t;
			$rem_descarga_total_t = $rem_descarga_total_t + $rem_descarga_t;
			$rem_subtotal_total_t = $rem_subtotal_total_t + $rem_subtotal_t;
			$rem_impuesto_total_t = $rem_impuesto_total_t + $rem_impuesto_t;
			$rem_retenido_total_t = $rem_retenido_total_t + $rem_retenido_t;
			$rem_total_total_t = $rem_total_total_t + $rem_total_t;
	
		} // FIN del WHILE $resSQL01 
		
		//Formato
		$rem_cantidad_total = number_format($rem_cantidad_total_t,2);
		$rem_valor_declarado_total = number_format($rem_valor_declarado_total_t,2);
		$rem_seguro_total = number_format($rem_seguro_total_t,2);
		$rem_flete_total = number_format($rem_flete_total_t,2);
		$rem_descarga_total = number_format($rem_descarga_total_t,2);
		$rem_subtotal_total = number_format($rem_subtotal_total_t,2);
		$rem_impuesto_total = number_format($rem_impuesto_total_t,2);
		$rem_retenido_total = number_format($rem_retenido_total_t,2);
		$rem_total_total = number_format($rem_total_total_t,2);
		
	?>   
			<tr>
				<td align="center" ></td>
				<td align="left" ></td>
                <td align="left" ></td>
                <td align="left" >TOTALES</td>
                <td align="center" ><?php echo $rem_cantidad_total; ?></td>
                <td align="left" ></td>
				<td align="left" ></td>
                <td align="right" ><?php echo $rem_valor_declarado_total; ?></td>
                <td align="right" ><?php echo $rem_seguro_total; ?></td>
				<td align="right" ><?php echo $rem_flete_total; ?></td>
				<td align="center" ></td>
				<td align="right" ><?php echo $rem_descarga_total; ?></td>
				<td align="right" ><?php echo $rem_subtotal_total; ?></td>
				<td align="right" ><?php echo $rem_impuesto_total; ?></td>
				<td align="right" ><?php echo $rem_retenido_total; ?></td>
				<td align="right" ><?php echo $rem_total_total; ?></td>
				<td align="left" ></td>
				<td align="left" ></td>
				<td align="left" ></td>
			</tr>
			
       
		</tbody>
	</table>

<?php

//http://ts-c12.ddns.net/cfdipro/reporte_viajes2.php?ide=6570871&prefijodb=prbrapidoscuenca

?>