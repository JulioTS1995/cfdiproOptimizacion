<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$sucursal= $_GET["sucursal"];//trae sucursal
$emisor = $_GET["emisor"];//trae emisor

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


?>
<html>
  <head>
    <title>Vale de Entrada Detalle</title>
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
				  <h2 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Vale de Entrada Detalle</h2>
				</div>
				<br>
				<center>
					<form method="post" action="ValeEntradaDetalleSucursal.php" enctype="multipart/form-data">

					<div class="form-group">
					
						
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
						<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
						<input type="submit" value="consultar" name="consultar" class="btn btn-info">
						<input type="hidden" name="emisor" id="emisor" value='<?php echo $emisor; ?>'>
						<input type="hidden" name="sucursal" id="sucursal" value='<?php echo $sucursal; ?>'>

						</div>
					
					</form>
				</center>
			</div>
		</div>
	</div>
  </body>
</html>
