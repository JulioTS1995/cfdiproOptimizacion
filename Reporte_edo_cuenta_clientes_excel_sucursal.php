<?php  
//Recibir variables
//$prefijobd = $_POST['prefijodb'];
//$fechai = $_POST['txtDesde'];
//$fechaf = $_POST['txtHasta'];
//$clientee_id = $_POST['clientee'];
//$clientei_id = $_POST['clientei'];
//$operador_id = $_POST['operador'];
//$tracto_id = $_POST['tracto'];
//$origen_id = $_POST['origen'];
//$destino_id = $_POST['destino'];
//$boton = $_POST['btnEnviar'];

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
		
$fecha2_t = $anio_logs."-".$mes_logs."-".$dia_logs;  
$fecha2 = date("d-m-Y", strtotime($fecha2_t));


	
	$sucursal = $_GET["sucursal"];//trae sucursal
	$prefijobd = $_GET["prefijobd"];
	

////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="edo_cuenta_clientes_a1a_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");	



?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
		<table class="table table-hover table-responsive table-condensed" border="1" id="table">
			<thead>
				<tr>
					<th>Fecha Timbrado</th>
					<th>Folio</th>
					<th>Moneda</th>
					<th>Ticket</th>
					<th>Estatus</th>
					<th>Fecha Vencimiento</th>
					<th>Saldo Vencido</th>
					<th>Saldo Factura</th>
				</tr>
			</thead>
		<tbody>
	<?php
		//Buscar Clientes-Facturas
		$resSQL11="SELECT DISTINCT(C.ID) as id_cliente FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O, ".$prefijobd."clientes C WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND F.CargoAFactura_RID = C.ID AND F.cfdfchhra > '1990-01-01 00:00:00' AND O.Sucursal_RID = ".$sucursal." ORDER BY C.RazonSocial";
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
			}
								
	?>
					
			<tr>
				<td colspan="8"><b><?php echo $nombre_cliente; ?></b></td>
			</tr>
    <?php	
			$saldo_vencido_suma = 0;
			$saldo_suma = 0;
									
							
			//Buscar Facturas
			$resSQL4="SELECT F.ID as ID, F.Moneda as Moneda, F.Ticket as Ticket, F.XFolio as XFolio, F.Creado as Creado, F.zTotal as zTotal, F.CobranzaAbonado as CobranzaAbonado, F.CobranzaSaldo as CobranzaSaldo, F.Vence as Vence, F.Comentarios as Comentarios, F.cfdfchhra as FechaTimbrado, F.DiasCredito as DiasCredito FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND F.CargoAFactura_RID = ".$id_cliente." AND F.cfdfchhra > '1990-01-01 00:00:00' ORDER BY F.XFolio";
			//echo "<br>".$resSQL4;
			$runSQL4=mysql_query($resSQL4);
			$total_registros_t2 = mysql_num_rows($runSQL4);
			$total_registros2 = number_format($total_registros_t2,0);
			while ($rowSQL4=mysql_fetch_array($runSQL4)){
				//Obtener_variables
				$id_factura = $rowSQL4['ID'];
				//$nom_cliente = $rowSQL4['nom_cliente'];
				$moneda = $rowSQL4['Moneda'];
				$ticket = $rowSQL4['Ticket'];
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
	?>   
			<tr>
				<td><?php echo $fecha_timbrado; ?></td>
				<td><?php echo $xfolio; ?></td>
				<td><?php echo $moneda; ?></td>
				<td><?php echo $ticket; ?></td>
				<td><?php echo $estatus; ?></td>
				<td><?php echo $vence; ?></td>
				<td><?php echo $saldo_vencido; ?></td>
				<td><?php echo $cobranza_saldo; ?></td>				
			</tr>
	<?php 
			}  //Fin Buscar Facturas
															
			$saldo_vencido_suma_t = number_format($saldo_vencido_suma,2);
			$saldo_suma_t = number_format($saldo_suma,2);
	?>
			<tr>
				<td colspan="6"></td>
				<td><b><?php echo $saldo_vencido_suma_t; ?></b></td>
				<td><b><?php echo $saldo_suma_t; ?></b></td>
			</tr>
	<?php
							
	} //Fin Busca Cliente
							
						  
	?>
		
		</tbody> 
					
	<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
	</table>

