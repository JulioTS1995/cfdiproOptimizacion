<?php
require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
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
<title>Reporte General</title>
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
	  <form action="reporteGeneral.php" method="post">
	<div class="col-md-8 col-md-offset-2">
		<div class="row">
			<div class="col-md-12">
				<h2><b>Reporte General</b></h2>
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
			<div class="col-md-12">
				<div class="form-group">
					<label>Cliente:</label>
						<select class="form-control" name="cliente" id="cliente">
							<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Clientes 
									$sql2 = "SELECT * FROM ".$prefijodb."clientes ORDER BY RazonSocial";
									$res2 = mysql_query($sql2, $cnx_cfdi);
									while($row2 = mysql_fetch_array($res2)){
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
		</div>
		<p><input type="submit" value="Generar Reporte" name="button" id="button" class="btn btn-success btn-lg "> </p>
	</div>


	</form>
	</body>
  
  
</html>

<!-- http://174.142.186.234/descargar_facturas_pdf_fechas.php?prefijodb=prbxtrapak_ 
	 http://174.142.186.234/descargar_facturas_pdf_fechas.php?prefijodb=xtrapak_-->