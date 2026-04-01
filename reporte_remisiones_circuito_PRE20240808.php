<?php
require_once('cnx_cfdi.php');
//require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Remisiones Circuito</title>
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
	  <form action="reporte_remisiones_circuito_2.php" method="post">
	<div class="col-md-8 col-md-offset-2">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Reporte Remisiones Circuito</b></h2>
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
				<div class="invisible">
					<input type="text" name="prefijodb" id="prefijodb" class="form-control btn btn-primary btn-lg"  type="hidden" value="<?php echo $prefijodb; ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<!--<label>Circuito:</label>
						<select class="form-control" name="circuito" id="circuito">
							<option value="0">- Seleccione -</option>
							<option value="DEDICADO LOCAL PERAL (481)">DEDICADO LOCAL PERAL (481)</option>
							<option value="DEDICADO FORANEO (280)">DEDICADO FORANEO (280)</option>
							<option value="DEDICADOS TORTHON (480)">DEDICADOS TORTHON (480)</option>
							<option value="JUGUERA (CONCENTRADO)">JUGUERA (CONCENTRADO)</option>
							<option value="TRAILER SPOT">TRAILER SPOT</option>
							<option value="CITRUS GOLDEN (TUXPAN)">CITRUS GOLDEN (TUXPAN)</option>
							<option value="MONTERREY CONCENTRADOS">MONTERREY CONCENTRADOS</option>
							<option value="CONCENTRADOS LOCAL (FRIALSA, NAFTA, IRESA, ARCOSA, FRIMESA)">CONCENTRADOS LOCAL (FRIALSA, NAFTA, IRESA, ARCOSA, FRIMESA)</option>
							<option value="LOCAL PRODUCTO TERMINADO (CEDIS LA VILLA, CUAUTITLAN, NAUCALPAN Y CENTROS DE DISTRIBUCION)">LOCAL PRODUCTO TERMINADO (CEDIS LA VILLA, CUAUTITLAN, NAUCALPAN Y CENTROS DE DISTRIBUCION)</option>
							<option value="CAMIONETA SPOT">CAMIONETA SPOT</option>
							<option value="DEDICADO INTERPLANTAS">DEDICADO INTERPLANTAS</option>
							<option value="TORTON SPOT">TORTON SPOT</option>
						</select>-->
						<label style="font-size:15px">Circuito:</label>
						<select name="circuito" id="circuito" class="form-control" >
							<?php 
								$resSQL0111 = "SELECT * FROM ".$prefijodb."circuito WHERE Estatus='Activo' ORDER BY Nombre";
								//echo $resSQL0111;
								$runSQL0111 = mysql_query($resSQL0111, $cnx_cfdi);
							?>
							<option value="0" selected="selected">-- Seleccione --</option>
							<?php
								while($rowSQL0111 = mysql_fetch_array($runSQL0111)){
									$id_circuito = $rowSQL0111['ID'];
									$nombre_circuito = $rowSQL0111['Nombre'];
							?>
							<option value="<?php echo $id_circuito; ?>"><?php echo $nombre_circuito; ?></option>
							<?php
								}
							?>
						</select>
						<p class="help-block text-danger"></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Operador:</label>
						<select class="form-control" name="operador" id="operador">
							<option value="0">- Seleccione -</option>
								<?php 
									//Buscar origen 
									$sql3 = "SELECT * FROM ".$prefijodb."operadores ORDER BY Operador";
									$res3 = mysql_query($sql3, $cnx_cfdi);
									while($row3 = mysql_fetch_array($res3)){
										$id_operador = $row3['ID'];
										$nom_operador = $row3['Operador'];
								?>
									<option value="<?php echo $id_operador; ?>"><?php echo $nom_operador; ?></option>
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
	</body>
  
  
</html>

<!-- http://174.142.199.104/cfdipro/reporte_remisiones_circuito.php?prefijodb=prbkamionaje_ -->