<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$emisor = $_GET["emisor"];//trae emisor

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


?>
<html>
  <head>
    <title>Reporte Combustible</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
	<!-- CSS only -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

	<!-- JS, Popper.js, and jQuery -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
	
  </head>

  <body>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
			<br>
				<div id="encabezadoform" style="text-align:center">
				  <h2 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Reporte Combustible</h2>
				</div>
				<br>
				<center>
					<form method="post" action="ReporteCombustible.php" enctype="multipart/form-data">

					<div class="form-group">
					<div>
					<label>Operador:</label>
						<select class="form-control inputdefault" name="proveedor" id="proveedor" required aria-required="true">
							<option value='0'>Selecciona Operador</option>
						<?php
				
				require_once('cnx_cfdi2.php');
				mysqli_select_db($cnx_cfdi2,$database_cfdi);

						$resSQL = "SELECT ID,Operador FROM ".$_GET["prefijodb"]."operadores WHERE Estatus = 'Activo' ORDER BY Operador";
					$runSQL = mysqli_query($cnx_cfdi2,$resSQL);
					if (!$runSQL) {//debug
						$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
						$mensaje .= 'Consulta completa: ' . $resSQL;
						die($mensaje);
					}
						while ($rowSQL = mysqli_fetch_assoc($runSQL))
						{
							?>
							<option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Operador']; ?></option>
						<?php
						
						}
						?>
						</select></div>
						<div>
					<label>Unidad:</label>
						<select class="form-control inputdefault" name="proveedor" id="proveedor" required aria-required="true">
							<option value='0'>Selecciona Unidad</option>
						<?php
				
				require_once('cnx_cfdi2.php');
				mysqli_select_db($cnx_cfdi2,$database_cfdi);

						$resSQL = "SELECT ID,Unidad FROM ".$_GET["prefijodb"]."unidades WHERE Activa = 'Activa' ORDER BY Unidad";
					$runSQL = mysqli_query($cnx_cfdi2,$resSQL);
					if (!$runSQL) {//debug
						$mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
						$mensaje .= 'Consulta completa: ' . $resSQL;
						die($mensaje);
					}
						while ($rowSQL = mysqli_fetch_assoc($runSQL))
						{
							?>
							<option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Unidad']; ?></option>
						<?php
						
						}
						?>
						</select></div>
						
						<div class="col-md-4">
							<label>Fecha Inicial:</label>
						</div>
						<div class="col-md-6">
							<input type="date" class="form-control inputdefault" name="fechai" id="fecha" required="required" autofocus>
						</div>
						</div>
						<div class="form-group">
						<div class="col-md-4">
							<label>Fecha Final:</label>
						</div>
						<div class="col-md-6">
							<input type="date" class="form-control inputdefault" name="fechaf" id="fecha" required="required" autofocus>
						</div>
						</div>
						<div class="form-group">
						<input type="hidden" name="emisor" id="emisor" value='<?php echo $emisor; ?>'>
						<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
						<input type="submit" value="consultar" name="consultar" class="btn btn-info">
						</div>
					
					</form>
				</center>
			</div>
		</div>
	</div>
  </body>
</html>
