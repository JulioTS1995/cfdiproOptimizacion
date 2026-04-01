<?php
require_once('cnx_cfdi.php');
//require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

if (!isset($_GET['prefijo']) || empty($_GET['prefijo'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijodb = @mysql_escape_string($_GET["prefijo"]);
$sucursal = $_GET["sucursal"];//trae sucursal

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijodb, "_");

if ($pos === false) {
    $prefijodb = $prefijodb . "_";
} 


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Conciliación Combustible</title>
<style type="text/css">
.style1 {
	font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
}
.style2 {
	text-align: center;
}
.style3 {
	font-weight: bold;
}
</style>

	<meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


    <meta http-equiv="X-UA-Compatible" content="ie=edge">


</head>

	<body>  
	  <form action="conciliacion_combustible_sucursal.php" method="post">
	<div class="col-md-8 col-md-offset-2">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Conciliación Combustible</b></h2>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Fecha Inicio:</label>
					<input type="date" name="fechai" id="fechai" class="form-control" required="required">
					<p class="help-block text-danger"></p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Fecha Fin:</label>
					<input type="date" name="fechaf" id="fechaf" class="form-control" required="required">
					<p class="help-block text-danger"></p>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label>Estación:</label>
						<select class="form-control" name="v_estacion" id="v_estacion">
							<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Estacion 
									$sql2 = "SELECT * FROM ".$prefijodb."estaciones WHERE Sucursal_RID = ".$sucursal." ORDER BY Estacion";
									$res2 = mysql_query($sql2, $cnx_cfdi);
									while($row2 = mysql_fetch_array($res2)){
										$id_estacion = $row2['ID'];
										$nom_estacion = $row2['Estacion'];
								?>
									<option value="<?php echo $id_estacion; ?>"><?php echo $nom_estacion; ?></option>
								<?php
									}
								?>
						</select>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label>Factura:</label>
					<input type="text" name="v_factura" id="v_factura" class="form-control">	
					<input name="prefijodb" id="prefijodb" class="form-control"  type="hidden" value="<?php echo $prefijodb; ?>">
					<input name="sucursal" id="sucursal" class="form-control"  type="hidden" value="<?php echo $sucursal; ?>">
					<p class="help-block text-danger"></p>
				</div>
			</div>
		</div>
		<button type="submit" name="btnGenerar" id="btnGenerar" value="Enviar" class="btn btn-success btn-lg btn-block">Generar Reporte</button>
	</div>


	</form>
	</body>
  
  
</html>

<!-- http://64.15.155.9/cfdipro/conciliacion_combustible_fechas.php?prefijo=prbunidos_ -->
<!-- http://64.15.155.9/cfdipro/conciliacion_combustible_fechas.php?prefijo=unidos_ -->