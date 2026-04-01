<?php 

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];

$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));
$prefijobd = $_POST["prefijodb"];

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

$circuitos = $_POST['circuito'];
$circuitos = mysqli_real_escape_string($cnx_cfdi2, $circuitos);

$v_operador = $_POST['operador'];

$circuitos = isset($_POST["circuito"]) ? $_POST["circuito"] : '';
        
$sql_circuito = !empty($circuitos) ? " AND Circuito2_RID IN (" . $circuitos . ")" : "";

if ($v_operador == 0) {
    $sql_operador = "";
} else {
    $sql_operador = " AND Operador_RID = ".$v_operador."";
}


$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$fecha_actual = $dia_logs."-".$mes_logs."-".$anio_logs;

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Remisiones Circuito</title>
<!--<link href="sierraestilo.css" rel="stylesheet" type="text/css">-->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<!-- datatable -->
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
<!-- datatable -->

</head>

<body >

<div class="container" style="margin-top: 0;">
	
	<div class="row">
		<div class="col-md-12 text-center">
			<h1><b>Remisiones Circuito</b></h1>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-md-12 text-center">
			<h3><b>Periodo: </b><?php echo $fecha_inicio_f." / ".$fecha_fin_f ?></h3>
		</div>
	</div>
	<hr>
	<div class="row">
        <div class="col-lg-12" style="width:1050; overflow:scroll;">
            <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
                    <tr>
						<th>Transporte</th>
                        <th>Circuito</th>
						<th>Fecha Embarque</th>
						<th>Fecha Destino</th>
						<th>Folio Embarque</th>
						<th>Porte</th>
                        <th>Factura</th>
						<th>Transferencia</th>
						<th>Tractor</th>
						<th>Trailer</th>
						<th>Origen</th>
						<th>Destino</th>
						<th>Clave</th>
						<th>Viaje</th>
						<th>Renta</th>
						<th>Maniobras</th>
						<th>Casetas</th>
						<th>Tarimas</th>
						<th>Cajas</th>
						<th>Peso</th>
						<th>Km</th>
						<th>Operador</th>
                    </tr>
                </thead>
                    <tbody>
                            <?php 
								$resSQL2 = "SELECT ID, RazonSocial as empresa FROM " . $prefijobd . "systemsettings LIMIT 1";
								$runSQL2 = mysqli_query($cnx_cfdi2, $resSQL2);
								while($rowSQL2 = mysqli_fetch_array($runSQL2)){
									$r_transporte = $rowSQL2['empresa'];
								}
							
                                $resSQL1 = "SELECT XFolio,Creado,Remitente,Destinatario,DestinatarioCitaCarga,RemisionOperador,Factura,Transferencia, 
								(SELECT Nombre FROM " . $prefijobd . "circuito WHERE ID = Rem.Circuito2_RID) AS NomCircuito,Unidad_RID,uRemolqueA_RID,
								yFlete, yDescarga, yAutopistas, Tarimas, Cajas, xPesoTotal, KmsRecorridos, Operador_RID
								FROM " . $prefijobd . "remisiones Rem WHERE Date(Creado) Between '".$fecha_inicio." 00:00:00' AND '".$fecha_fin." 23:59:59' ".$sql_circuito.$sql_operador." ORDER BY XFolio ASC";
								
								//echo $resSQL1;
								
								
								$runSQL1 = mysqli_query($cnx_cfdi2, $resSQL1);
								
								
								while($rowSQL1 = mysqli_fetch_array($runSQL1))
									 {
										$r_creado_t = $rowSQL1['Creado'];
										$r_creado = date("d-m-Y", strtotime($r_creado_t));
										//$r_circuito = $rowSQL1['Circuito'];
										$r_xfolio = $rowSQL1['XFolio'];
										$r_origen = $rowSQL1['Remitente'];
										$r_destino = $rowSQL1['Destinatario'];
										$r_destinatario_cita_carga_t = $rowSQL1['DestinatarioCitaCarga'];
										$r_destinatario_cita_carga = date("d-m-Y", strtotime($r_destinatario_cita_carga_t));
										$r_ticket = $rowSQL1['RemisionOperador'];
										$r_factura = $rowSQL1['Factura'];
										$r_transferencia = $rowSQL1['Transferencia'];
										$nombre_circuito = $rowSQL1['NomCircuito'];
										
										$r_unidad_id = $rowSQL1['Unidad_RID'];
										
										if($r_unidad_id > 0){
											$resSQL3 = "SELECT * FROM " . $prefijobd . "unidades WHERE ID=".$r_unidad_id;
											$runSQL3 = mysqli_query($cnx_cfdi2, $resSQL3);
											while($rowSQL3 = mysqli_fetch_array($runSQL3)){
												$r_unidad_placas = $rowSQL3['Placas'];
												$r_unidad_unidad = $rowSQL3['Unidad'];
											}
										} else {
											$r_unidad_placas = "";
											$r_unidad_unidad = "";
										}
										
										$r_remolquea_id = $rowSQL1['uRemolqueA_RID'];
										
										if($r_remolquea_id > 0){
											$resSQL4 = "SELECT * FROM " . $prefijobd . "unidades WHERE ID=".$r_remolquea_id;
											$runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
											while($rowSQL4 = mysqli_fetch_array($runSQL4)){
												$r_remolque_placas = $rowSQL4['Placas'];
												$r_remolque_unidad = $rowSQL4['Unidad'];
											}
										} else {
											$r_remolque_placas = "";
											$r_remolque_unidad = "";
										}
										
										
										$r_clave = "";
										$r_viaje = 1;
										
										$r_flete_t = $rowSQL1['yFlete']; 
										$r_flete = "$".number_format($r_flete_t,2);
										
										$r_maniobras_t = $rowSQL1['yDescarga']; 
										$r_maniobras = "$".number_format($r_maniobras_t,2);
										
										$r_casetas_t = $rowSQL1['yAutopistas']; 
										$r_casetas = "$".number_format($r_casetas_t,2);
										
										$r_tarimas_t = $rowSQL1['Tarimas']; 
										$r_tarimas = number_format($r_tarimas_t,0);
										
										$r_cajas_t = $rowSQL1['Cajas']; 
										$r_cajas = number_format($r_cajas_t,0);
										
										$r_peso_t = $rowSQL1['xPesoTotal']; 
										$r_peso = number_format($r_peso_t,2);
										
										$r_km_t = $rowSQL1['KmsRecorridos']; 
										$r_km = number_format($r_km_t,2);
										
										$r_operador_id = $rowSQL1['Operador_RID'];
										
										$resSQL5 = "SELECT * FROM " . $prefijobd . "operadores WHERE ID=".$r_operador_id;
										$runSQL5 = mysqli_query($cnx_cfdi2, $resSQL5);
										while($rowSQL5 = mysqli_fetch_array($runSQL5)){
											$r_operador_nombre = $rowSQL5['Operador'];
										}
										
										
		
									
                            ?>
                            <tr>
								<td style="text-align:center;"><?php echo $r_transporte; ?></td>
								<td style="text-align:left;"><?php echo $nombre_circuito; ?></td>
								<td style="text-align:center;"><?php echo $r_creado; ?></td>
								<td style="text-align:center;"><?php echo $r_destinatario_cita_carga; ?></td>
								<td style="text-align:left;"><?php echo $r_ticket; ?></td>
								<td style="text-align:center;"><?php echo $r_xfolio; ?></td>
								<td style="text-align:left;"><?php echo $r_factura; ?></td>
								<td style="text-align:left;"><?php echo $r_transferencia; ?></td>
								<td style="text-align:center;"><?php echo $r_unidad_unidad; ?></td>
                                <td style="text-align:center;"><?php echo $r_remolque_unidad; ?></td>
								<td style="text-align:left;"><?php echo $r_origen; ?></td>	
								<td style="text-align:left;"><?php echo $r_destino; ?></td>
								<td style="text-align:left;"><?php echo $r_clave; ?></td>
								<td style="text-align:left;"><?php echo $r_viaje; ?></td>
								<td style="text-align:left;"><?php echo $r_flete; ?></td>
								<td style="text-align:left;"><?php echo $r_maniobras; ?></td>
								<td style="text-align:left;"><?php echo $r_casetas; ?></td>
								<td style="text-align:left;"><?php echo $r_tarimas; ?></td>
								<td style="text-align:left;"><?php echo $r_cajas; ?></td>
								<td style="text-align:left;"><?php echo $r_peso; ?></td>
								<td style="text-align:left;"><?php echo $r_km; ?></td>
								<td style="text-align:left;"><?php echo $r_operador_nombre; ?></td>
                            </tr>
                            <?php
									} 
								
							?>
                    </tbody>
            </table>
			<br>
        </div>
		
    </div>	
	<br>
	<div class="row">
		<div class="col-md-12">
			<a href="reporte_remisiones_circuito_xls.php?prefijodb=<?php echo $prefijobd; ?>&finicio=<?php echo $fecha_inicio; ?>&ffin=<?php echo $fecha_fin; ?>&circuito=<?php echo $circuitos; ?>&operador=<?php echo $v_operador; ?>"><button type="button" class="btn btn-success btn-lg btn-block">Exportar a Excel</button></a>
		</div>
	</div>
	<br>
	<br>
	
	
	
<script>
  $(document).ready(function() {
    $('#table').DataTable();
  } );
</script>
</body>
</html>
<?php
//mysqli_free_result($runSQL);
mysqli_close($cnx_cfdi2);



?>