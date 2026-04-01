<?php 
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
//Recibir variable
$prefijobd = $_POST["prefijodb"];
//$boton = $_POST["consultar"];


$id_cliente = $_POST["cliente"];
$tipo = $_POST["tipo"];
$modalidad = $_POST["modalidad"];
$id_ruta = $_POST["ruta"];

if($id_cliente!=0){//verifica que exista cliente definido
	$q_cliente=" AND ct.FolioTarifas_RID = ".$id_cliente."";
}else{$q_cliente="";}

if($id_ruta!=0){//verifica que exista ruta definido
	$q_ruta=" AND ct.Ruta_RID = ".$id_ruta."";
}else{$q_ruta="";}

if($tipo!=0){//verifica que exista tipo definido
	$q_tipo=" AND ct.Tipo = '".$tipo."'";
}else{$q_tipo="";}

if($modalidad!=0){//verifica que exista modalidad definido
	$q_modalidad=" AND ctp.Modalidad = '".$modalidad."'";
}else{$q_modalidad="";}



//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));


require_once('cnx_cfdi2.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte Tarifas Clientes</title>

 <!-- Bootstrap links -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable -->

</head>

<body>
 
    <div id = "container1" style = "width: 80%; margin: 0 auto; text-align:center;" >
        <div id="contenedor2" style="overflow:hidden;">
                <!--<div id="1" style="float: left; width: 33%; text-align:left;">
                    <img src="img/logo_ts.png" height="120">
                </div>-->
                
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte Tarifas Clientes</strong></h1>
                </div>

        </div>

        <hr>
        
        <div class="row">
			<div class="col-lg-12">
			  <!--<div id="2" style="float: left; width: 33%; text-align:center;">
					<h1 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Resumen </h1>
			  </div>-->
			  <label>Periodo Consultado: <?php echo $fecha_inicio_f." - ".$fecha_fin_f; ?> </label>
			  <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
				  <tr>
					<th align="center" style="font-size: 12px;">Cliente</th>
					<th align="center" style="font-size: 12px;">Ruta</th>
					<th align="center" style="font-size: 12px;">Clase</th>
					<th align="center" style="font-size: 12px;">Fecha Inicio</th>
					<th align="center" style="font-size: 12px;">Fecha Vigencia</th>
					<th align="center" style="font-size: 12px;">Estatus</th>
					<th align="center" style="font-size: 12px;">Modalidad</th>
					<th align="center" style="font-size: 12px;">Tipo</th>
					<th align="center" style="font-size: 12px;">Concepto</th>
					<th align="center" style="font-size: 12px;">Importe</th>
					<th align="center" style="font-size: 12px;">Comentarios</th>
				  </tr>
				</thead>
				<tbody>
				<?php
					

					$resSQL="SELECT c.RazonSocial as cliente, r.Ruta as ruta, uc.Clase as clase, ctp.FechaInicio as fecha_inicio, ctp.FechaVigencia as fecha_vigencia, ctp.Estatus as estatus,
						ctp.Modalidad as modalidad, ct.Tipo as tipo, ctp.ConceptoPartida as concepto, ctp.Importe as importe, ctp.Comentarios as comentarios
						FROM ".$prefijobd."clientestarifaspartidas as ctp
						LEFT OUTER JOIN ".$prefijobd."clientestarifas AS ct ON ct.ID = ctp.FolioSub_RID
						LEFT OUTER JOIN ".$prefijobd."clientes AS c ON c.ID = ct.FolioTarifas_RID
						LEFT OUTER JOIN ".$prefijobd."rutas AS r ON r.ID = ct.Ruta_RID
						LEFT OUTER JOIN ".$prefijobd."unidadesclase AS uc ON uc.ID = ct.Clase_RID
						WHERE Date(ctp.FechaVigencia) Between '".$fecha_inicio."' AND '".$fecha_fin."' 
						".$q_cliente."
						".$q_ruta."
						".$q_tipo."
						".$q_modalidad."
						ORDER BY c.RazonSocial
						";

						//echo $resSQL;
					$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
					
					while ($rowSQL=mysqli_fetch_array($runSQL)){
						$cliente = $rowSQL["cliente"];
						$ruta = $rowSQL["ruta"];
						$clase = $rowSQL["clase"];
						$fecha_inicio_t = $rowSQL["fecha_inicio"];
						$fecha_inicio = date("d-m-Y", strtotime($fecha_inicio_t));
						$fecha_vigencia_t = $rowSQL["fecha_vigencia"];
						$fecha_vigencia = date("d-m-Y", strtotime($fecha_vigencia_t));
						$estatus = $rowSQL["estatus"];
						$modalidad = $rowSQL["modalidad"];
						$tipo = $rowSQL["tipo"];
						$concepto = $rowSQL["concepto"];
						$importe_t = $rowSQL["importe"];
						$importe = "$".number_format($importe_t,2);
						$comentarios = $rowSQL["comentarios"];


				
				?>
								  <tr>
									<td align="center"><?php echo $cliente ?> </td>
									<td align="left"><?php echo $ruta ?> </td>
									<td align="left"><?php echo $clase ?> </td>
									<td align="center"><?php echo $fecha_inicio ?> </td>
									<td align="center"><?php echo $fecha_vigencia ?> </td>
									<td align="left"><?php echo $estatus ?> </td>
									<td align="left"><?php echo $modalidad ?> </td>
									<td align="left"><?php echo $tipo ?> </td>
									<td align="left"><?php echo $concepto ?> </td>
									<td align="left"><?php echo $importe ?> </td>
									<td align="left"><?php echo $comentarios ?> </td>
									
				
								  </tr>
				  
				<?php
						
					}
				?>  
				</tbody>
			  </table>
			</div>
        </div>
        <br>
		<div class="row">
			<div class="col-md-12" style="text-align:left">
				<a href="reporte_tarifas_clientes_toscano_excel.php?fechai=<?php echo $fecha_inicio_t; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>&cliente=<?php echo $id_cliente; ?>&ruta=<?php echo $id_ruta; ?>&tipo=<?php echo $tipo; ?>&modalidad=<?php echo $modalidad; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
			</div>
		</div>
		<br>
		<br>

    </div>
	
	<script>
	  $(document).ready(function() {
		$('#table').DataTable();
	  } );
	</script>
	
  </body>
</html>
<?php
//mysqli_free_result($runSQL);
//mysqli_close($cnx_cfdi2);
?>