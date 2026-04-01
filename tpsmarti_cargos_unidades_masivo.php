<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

$v_documentador = $_GET['documentador'];


?>
<html>
  <head>
    <title>Reporte Mensual de Utilidades</title>
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
				  <h2 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Cargo Unidades Masivo  <small style="color:#4da6ff; ">TPSMARTI</small></h2>
				</div>
				<br>
				<center>
					<form method="post" action="tpsmarti_cargos_unidades_masivo_procesa.php" enctype="multipart/form-data">
						<div class="form-group">
                            <div class="col-md-4">
								<label><b>Fecha:</b></label>
							</div>
                            <div class="col-md-6">
								<input type="date" class="form-control inputdefault" name="fechai" id="fechai" required="required">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="col-md-4">
								<label><b>Importe:</b></label>
							</div>
                            <div class="col-md-6">
								<input type="number" class="form-control inputdefault" name="importe" id="importe" required="required" min="0" value="0" step=".01">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="col-md-4">
								<label><b>Comentario:</b></label>
							</div>
                            <div class="col-md-6">
								<textarea class="form-control" name="comentario" id="comentario" rows="4" style="resize: vertical;"></textarea>
                            </div>
                        </div>
						<div class="form-group">
							<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
							<input type="hidden" class="form-control" id="documentador" name="documentador" value="<?php echo $v_documentador; ?>" >
							<input type="submit" value="Crear" name="crear" class="btn btn-info">
						  </div>
					
					
					</form>
				</center>
			</div>
		</div>
	</div>
  </body>
</html>

<!-- http://107.161.78.100/cfdipro/tpsmarti_cargos_unidades_masivo.php?prefijodb=prbtpsmarti_&documentador=tractosoft -->