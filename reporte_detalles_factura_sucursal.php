
<?php  
//Recibir variables
$prefijobd = $_POST['prefijodb'];
$fechai = $_POST['txtDesde'];
$fechaf = $_POST['txtHasta'];
$cliente_id = $_POST['cliente'];
$ruta_id = $_POST['ruta'];
$operador_id = $_POST['operador'];
$unidad_id = $_POST['unidad'];
$v_xfolio = $_POST['txtxfolio'];
$sucursal = $_POST["sucursal"];//trae sucursal

$boton = $_POST['btnEnviar'];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="reporte_detalle_factura_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

if($v_xfolio == ''){
	$sql_xfolio="";
} else {
	$sql_xfolio=" AND XFolio = '".$v_xfolio."'";
}

if($cliente_id == 0){
	$sql_cliente="";
} else {
	$sql_cliente=" AND f.CargoAFactura_RID = ".$cliente_id;
}

if($ruta_id == 0){
	$sql_ruta="";
} else {
	$sql_ruta=" AND f.Ruta_RID = ".$ruta_id;
}

if($operador_id == 0){
	$sql_operador="";
} else {
	$sql_operador=" AND f.Operador_RID = ".$operador_id;
}

if($unidad_id == 0){
	$sql_unidad="";
} else {
	$sql_unidad=" AND f.Unidad_RID = ".$unidad_id;
}




//Validar que contenga datos la consulta
$resSQL01 = "SELECT COUNT(*) as total FROM ".$prefijobd."facturapartidas as p INNER JOIN ".$prefijobd."factura as f on p.FolioSub_RID=f.ID WHERE Date(f.Creado) BETWEEN '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59' AND AND f.Oficina_RID IN (SELECT ID FROM ".$prefijodb."Oficinas WHERE Sucursal_RID = ".$sucursal." ) ".$sql_xfolio.$sql_cliente.$sql_ruta.$sql_operador.$sql_unidad;


$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
while($rowSQL01 = mysql_fetch_array($runSQL01)){
	$total_reg = $rowSQL01['total'];
}


?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
	
<?php
if($total_reg > 0){

?>
	
		<table class="table table-hover table-responsive table-condensed" border="1" id="table">
			<thead>
				<tr>
					<th align="center" style="font-size: 12px;">Moneda</th>
					<th align="center" style="font-size: 12px;">XFolio</th>
					<th align="center" style="font-size: 12px;">Cliente</th>
					<th align="center" style="font-size: 12px;">Cliente RFC</th>
					<th align="center" style="font-size: 12px;">Operador</th>
					<th align="center" style="font-size: 12px;">Unidad</th>
					<th align="center" style="font-size: 12px;">Remolque</th>
					<th align="center" style="font-size: 12px;">Dolly</th>
					<th align="center" style="font-size: 12px;">Remolque2</th>
					<th align="center" style="font-size: 12px;">cfdiuuid</th>
					<th align="center" style="font-size: 12px;">Fecha Creado</th>
					<th align="center" style="font-size: 12px;">Flete</th>
					<th align="center" style="font-size: 12px;">Subtotal Factura</th>
					<th align="center" style="font-size: 12px;">Impuesto Factura</th>
					<th align="center" style="font-size: 12px;">Retenido Factura</th>
					<th align="center" style="font-size: 12px;">Total Factura</th>
					<th align="center" style="font-size: 12px;">Cobranza Abonado</th>
					<th align="center" style="font-size: 12px;">Cobranza Saldo</th>
					<th align="center" style="font-size: 12px;">Vence</th>
					<th align="center" style="font-size: 12px;">Concepto</th>
					<th align="center" style="font-size: 12px;">Detalle</th>
					<th align="center" style="font-size: 12px;">Cantidad</th>
					<th align="center" style="font-size: 12px;">Precio Unitario</th>
					<th align="center" style="font-size: 12px;">Subtotal Partida</th>
					<th align="center" style="font-size: 12px;">IVA Partida</th>
					<th align="center" style="font-size: 12px;">Retencion Partida</th>
					<th align="center" style="font-size: 12px;">Total Partida</th>
				</tr>
			</thead>
		<tbody>
	<?php
		
		$resSQL02 = "SELECT f.Moneda as Moneda, f.XFolio as xfolio, f.CargoAFactura_RID as id_cliente, f.Operador_RID as id_operador, f.Unidad_RID as id_unidad, f.Remolque_RID as id_remolque, f.Dolly_RID as id_dolly, f.Remolque2_RID as id_remolque2, f.Creado as creado, f.yFlete as flete_factura, f.zSubtotal as subtotal_factura, f.zImpuesto as iva_factura, f.zRetenido as retenido_factura, f.zTotal as total_factura, f.CobranzaAbonado as cobranza_abonado_factura, f.CobranzaSaldo as cobranza_saldo_factura, f.Vence as vence, p.ConceptoPartida as concepto_partida, p.Detalle as detalle_partida, p.Cantidad as cantidad_partida, p.PrecioUnitario as precio_unitario_partida, p.Subtotal as subtotal_partida, p.IVAImporte as iva_partida, p.RetencionImporte as retenido_partida, p.Importe as total_partida FROM ".$prefijobd."facturapartidas as p INNER JOIN ".$prefijobd."factura as f on p.FolioSub_RID=f.ID WHERE Date(f.Creado) BETWEEN '".$fechai." 00:00:00' AND '".$fechaf." 23:59:59'".$sql_xfolio.$sql_cliente.$sql_ruta.$sql_operador.$sql_unidad;
		
		//echo $resSQL02;
		$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
		while($rowSQL02 = mysql_fetch_array($runSQL02)){
		//Buscar facturas y abonos del cliente
			$f_moneda = $rowSQL02['Moneda'];
			$f_xfolio = $rowSQL02['xfolio'];
			$f_id_cliente = $rowSQL02['id_cliente'];
			$f_id_operador = $rowSQL02['id_operador'];
			$f_id_unidad = $rowSQL02['id_unidad'];
			$f_id_remolque = $rowSQL02['id_remolque'];
			$f_id_dolly = $rowSQL02['id_dolly'];
			$f_id_remolque2 = $rowSQL02['id_remolque2'];
			$f_cfdiuuid = $rowSQL02['cfdiuuid'];
			$f_creado_t = $rowSQL02['creado'];
			$f_creado = date("d-m-Y H:i:s", strtotime($f_creado_t));
			$f_flete_t = $rowSQL02['flete_factura'];
			$f_flete = "$".number_format($f_flete_t,2);
			$f_subtotal_t = $rowSQL02['subtotal_factura'];
			$f_subtotal = "$".number_format($f_subtotal_t,2);
			$f_iva_t = $rowSQL02['iva_factura'];
			$f_iva = "$".number_format($f_iva_t,2);
			$f_retenido_t = $rowSQL02['retenido_factura'];
			$f_retenido = "$".number_format($f_retenido_t,2);
			$f_total_t = $rowSQL02['total_factura'];
			$f_total = "$".number_format($f_total_t,2);
			$f_cobranza_abonado_t = $rowSQL02['cobranza_abonado_factura'];
			$f_cobranza_abonado = "$".number_format($f_cobranza_abonado_t,2);
			$f_cobranza_saldo_t = $rowSQL02['cobranza_saldo_factura'];
			$f_cobranza_saldo = "$".number_format($f_cobranza_saldo_t,2);
			$f_vence_t = $rowSQL02['vence'];
			$f_vence = date("d-m-Y", strtotime($f_vence_t));
			$p_concepto = $rowSQL02['concepto_partida'];
			$p_detalle = $rowSQL02['detalle_partida'];
			$p_cantidad_t = $rowSQL02['cantidad_partida'];
			$p_cantidad = number_format($p_cantidad_t,0);
			$p_precio_unitario_t = $rowSQL02['precio_unitario_partida'];
			$p_precio_unitario = "$".number_format($p_precio_unitario_t,0);
			$p_subtotal_t = $rowSQL02['subtotal_partida'];
			$p_subtotal = "$".number_format($p_subtotal_t,2);
			$p_iva_t = $rowSQL02['iva_partida'];
			$p_iva = "$".number_format($p_iva_t,2);
			$p_retenido_t = $rowSQL02['retenido_partida'];
			$p_retenido = "$".number_format($p_retenido_t,2);
			$p_total_t = $rowSQL02['total_partida'];
			$p_total = "$".number_format($p_total_t,2);
			
			
			
			
			
			//Buscar datos Cliente
			if($f_id_cliente > 0){
				$resSQL03 = "SELECT * FROM ".$prefijobd."clientes WHERE ID = ".$f_id_cliente;
				$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
				while($rowSQL03 = mysql_fetch_array($runSQL03)){
					$cliente_nombre = $rowSQL03['RazonSocial'];
					$cliente_rfc = $rowSQL03['RFC'];
				}
			} else {
				$cliente_nombre = '';
				$cliente_rfc = '';
			}
			
			//Buscar datos Operador
			if($f_id_operador > 0){
				$resSQL04 = "SELECT * FROM ".$prefijobd."operadores WHERE ID = ".$f_id_operador;
				$runSQL04 = mysql_query($resSQL04, $cnx_cfdi);
				while($rowSQL04 = mysql_fetch_array($runSQL04)){
					$operador_nombre = $rowSQL04['Operador'];
				}
			} else {
				$operador_nombre = '';
			}
			
			//Buscar datos Unidad
			if($f_id_unidad > 0){
				$resSQL05 = "SELECT * FROM ".$prefijobd."unidades WHERE ID = ".$f_id_unidad;
				$runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
				while($rowSQL05 = mysql_fetch_array($runSQL05)){
					$unidad_nombre = $rowSQL05['Unidad'];
				}
			} else {
				$unidad_nombre = '';
			}
			
			//Buscar datos Remolque
			if($f_id_remolque > 0){
				$resSQL06 = "SELECT * FROM ".$prefijobd."unidades WHERE ID = ".$f_id_remolque;
				$runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
				while($rowSQL06 = mysql_fetch_array($runSQL06)){
					$remolque_nombre = $rowSQL06['Unidad'];
				}
			} else {
				$remolque_nombre = '';
			}
			
			//Buscar datos Dolly
			if($f_id_dolly > 0){
				$resSQL07 = "SELECT * FROM ".$prefijobd."unidades WHERE ID = ".$f_id_dolly;
				$runSQL07 = mysql_query($resSQL07, $cnx_cfdi);
				while($rowSQL07 = mysql_fetch_array($runSQL07)){
					$dolly_nombre = $rowSQL07['Unidad'];
				}
			} else {
				$dolly_nombre = '';
			}
			
			//Buscar datos Remolque2
			if($f_id_remolque2 > 0){
				$resSQL08 = "SELECT * FROM ".$prefijobd."unidades WHERE ID = ".$f_id_remolque2;
				$runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
				while($rowSQL08 = mysql_fetch_array($runSQL08)){
					$remolque2_nombre = $rowSQL08['Unidad'];
				}
			} else {
				$remolque2_nombre = '';
			}
			
	
	?>
					
			<tr>
				<td align="center" ><?php echo $f_moneda; ?></td>
				<td align="center" ><?php echo $f_xfolio; ?></td>
                <td align="left" ><?php echo $cliente_nombre; ?></td>
                <td align="center" ><?php echo $cliente_rfc; ?></td>
                <td align="left" ><?php echo $operador_nombre; ?></td>
                <td align="left" ><?php echo $unidad_nombre; ?></td>
                <td align="left" ><?php echo $remolque_nombre; ?></td>
                <td align="left" ><?php echo $dolly_nombre; ?></td>
				<td align="left" ><?php echo $remolque2_nombre; ?></td>
				<td align="left" ><?php echo $f_cfdiuuid; ?></td>
				<td align="center" ><?php echo $f_creado; ?></td>
				<td align="right" ><?php echo $f_flete; ?></td>
				<td align="right" ><?php echo $f_subtotal; ?></td>
				<td align="right" ><?php echo $f_iva; ?></td>
				<td align="right" ><?php echo $f_retenido; ?></td>
				<td align="right" ><?php echo $f_total; ?></td>
				<td align="right" ><?php echo $f_cobranza_abonado; ?></td>
				<td align="right" ><?php echo $f_cobranza_saldo; ?></td>
				<td align="center" ><?php echo $f_vence; ?></td>
				<td align="left" ><?php echo $p_concepto; ?></td>
				<td align="left" ><?php echo $p_detalle; ?></td>
				<td align="center" ><?php echo $p_cantidad; ?></td>
				<td align="right" ><?php echo $p_precio_unitario; ?></td>
				<td align="right" ><?php echo $p_subtotal; ?></td>
				<td align="right" ><?php echo $p_iva; ?></td>
				<td align="right" ><?php echo $p_retenido; ?></td>
				<td align="right" ><?php echo $p_total; ?></td>
            </tr>
			
    <?php	
		} 	
	?>   

		</tbody>
	</table>

<?php
} else {

echo "<h2>No se encontraron registros</h2>";

}	// Fin valida total de registros
?>
