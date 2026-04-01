<?php
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijodb = @mysql_escape_string($_GET["prefijodb"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijodb, "_");

if ($pos === false) {
    $prefijodb = $prefijodb . "_";
} 


?>
<html>
  <head>
    <title>Reporte Tarifas Clientes</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
	<!-- CSS only -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

	<!-- JS, Popper.js, and jQuery -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
	
  </head>

  <body>  
  <center>
	<form action="reporte_tarifas_clientes_toscano.php" method="post">
	<div class="col-md-8 col-md-offset-2">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Reporte Tarifas Clientes</b></h2>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Fecha Inicio:</label>
					<input type="date" name="fechai" id="fechai" class="form-control" required="required">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Fecha Fin:</label>
					<input type="date" name="fechaf" id="fechaf" class="form-control" required="required">
					
				</div>
				<div class="invisible">
					<input type="text" name="prefijodb" id="prefijodb" class="form-control btn btn-primary btn-lg"  type="hidden" value="<?php echo $prefijodb; ?>">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Cliente:</label>
						<select class="form-control" name="cliente" id="cliente">
							<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Cliente 
									$sql2 = "SELECT * FROM ".$prefijodb."clientes ORDER BY RazonSocial";
									$res2 = mysqli_query($cnx_cfdi2,$sql2);
									while($row2 = mysqli_fetch_array($res2)){
										$id_cliente = $row2['ID'];
										$nom_cliente = $row2['RazonSocial'];
								?>
									<option value="<?php echo $id_cliente; ?>"><?php echo $nom_cliente; ?></option>
								<?php
									}
								?>
						</select>
						<p class="help-block text-danger"></p>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="form-group">
					<label>Tipo:</label>
						<select class="form-control" name="tipo" id="tipo">
							<option value="0">- Seleccione -</option>
							<option value="Carga General">Carga General</option>
							<option value="Naviera">Naviera</option>
						</select>
						<p class="help-block text-danger"></p>
				</div>
			</div>
	
			<div class="col-md-6">
				<div class="form-group">
					<label>Modalidad:</label>
						<select class="form-control" name="modalidad" id="modalidad">
							<option value="0">- Seleccione -</option>
							<option value="General">General</option>
							<option value="One Way">One Way</option>
							<option value="Round Trip">Round Trip</option>
						</select>
						<p class="help-block text-danger"></p>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label>Ruta:</label>
						<select class="form-control" name="ruta" id="ruta">
							<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Ruta 
									$sql21 = "SELECT * FROM ".$prefijodb."rutas ORDER BY Ruta";
									$res21 = mysqli_query($cnx_cfdi2,$sql21);
									while($row21 = mysqli_fetch_array($res21)){
										$id_ruta = $row21['ID'];
										$nom_ruta = $row21['Ruta'];
								?>
									<option value="<?php echo $id_ruta; ?>"><?php echo $nom_ruta; ?></option>
								<?php
									}
								?>
						</select>
						<p class="help-block text-danger"></p>
				</div>
			</div>

		</div>
		<button type="submit" name="btnGenerar" id="btnGenerar" value="Enviar" class="btn btn-success btn-lg btn-block">Buscar</button>
	</div>


	</form>
	</center>
	</body>
</html>

<!-- https://tractosoft-espejo71.com/cfdipro/reporte_tarifas_clientes_toscano_fechas.php?prefijodb=optimizacion -->