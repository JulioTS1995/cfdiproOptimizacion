<?php 

//Recibir variable
$prefijobd = $_POST["base"];
$boton = $_POST["consultar"];

//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));
$prefijobd = $_POST["base"];
$sucursal = $_POST["sucursal"];



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte Liquidaciones Morquecho</title>

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
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte Liquidaciones</h1>
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
					<th align="center" style="font-size: 12px;">Concepto</th>
					<th align="center" style="font-size: 12px;">Deposito</th>
					<th align="center" style="font-size: 12px;">Autorizado</th>
					<th align="center" style="font-size: 12px;">Diferencia</th>
				  </tr>
				</thead>
				<tbody>
				<?php


					$resSQL="SELECT SUM(LiqC.Diferencia) AS Diferencia, SUM(LiqC.Autorizado) AS Autorizado, SUM(LiqC.Deposito) AS Deposito, (SELECT C.Concepto FROM ".$prefijobd."conceptosliquidaciones AS C WHERE C.ID = LiqC.concepto_RID) AS Concepto FROM ".$prefijobd."liquidacionescomprobadosub AS LiqC, ".$prefijobd."liquidaciones AS Liq WHERE Date(Liq.Fecha)Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND Liq.ID=LiqC.FolioSub_RID AND Liq.OficinaLiquidacion_RID IN (SELECT ID FROM ".$prefijodb."Oficinas WHERE Sucursal_RID = ".$sucursal." )  GROUP BY Concepto;";
					$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
					
					while ($rowSQL=mysqli_fetch_array($runSQL)){
						//Obtener_variables

						$diferencia = $rowSQL['Diferencia'];
						$autorizado = $rowSQL['Autorizado'];
						$deposito = $rowSQL['Deposito'];
						$concepto = $rowSQL['Concepto'];


				?>
				
				  <tr>
					<td align="center"><?php echo $concepto ?> </td>
					<td align="left"><?php echo ("$".number_format($deposito,2)) ?> </td>
					<td align="left"><?php echo ("$".number_format($autorizado,2)) ?> </td>
					<td align="left"><?php echo ("$".number_format($diferencia,2)) ?> </td>
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