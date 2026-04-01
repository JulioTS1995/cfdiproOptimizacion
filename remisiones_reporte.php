<!DOCTYPE html>
<html>
	<head>
		<title>Reporte Remisiones por Periodo</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="initial-scale=1">    

		<script src="http://cdn.jsdelivr.net/jquery.validation/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js" type="text/javascript"></script>
		
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>  

		<link href="../bootstrap/css/bootstrap.css" rel="stylesheet">
		<link href="../bootstrap/css/estilos.css" rel="stylesheet">
		
	</head>

	<body>
		<main>
			<div id="encabezadoform">
	      		<h1>Reporte Remisiones por Periodo</h1>
	    	</div>
				<center><form name='reporte' method='post' action='remisiones_reportegeneradorcsv.php'>
					<fieldset>

						<div class="form-group">
							<label>Desde:</label>
							<input type="date" class="form-control inputdefault" name='desde' min="" max="" value="<?php echo date("Y-m-d");?>" required="required" step="1" autofocus>
						</div>
						<p></p>

						<div class="form-group">
							<label>Hasta:</label>
							<input type="date" class="form-control inputdefault" name='hasta'  min="" max="" value="<?php echo date("Y-m-d");?>" required="required" step="1" autofocus>
						</div>
						<p></p>

						<!--<input type='hidden' name='unidad' value=<?php //echo $_GET["Unidad"] ?> /><br />-->
						<!--<input type='hidden' name='vista' value=<?php //echo $_GET["vista"] ?> /><br />-->
						<input type='hidden' name='prefijo' value=<?php echo $_GET["prefijo"] ?> /><br />

						<div class="form-group">
							<input type='submit' value='Generar' class="btn btn-info btn-primary btn-lg">
						</div>

					</fieldset>
				</form>
			</center>
		</main>
	</body>
</html>
