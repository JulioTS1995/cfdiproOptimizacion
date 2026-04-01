<?php
require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");
$prefijobd = $_GET['prefijodb'];
if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$sucursal = $_GET["sucursal"];//trae sucursal

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


?>
<html>
  <head>
    <title>Detalles Factura</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">
  </head>

	<body>
		<div class="col-md-8 col-md-offset-2">
			<div id="encabezadoform">
			  <h1>Reporte Detalles Factura</h1>
			</div>
			<center>
			  <form method="post" action="reporte_detalles_factura_sucursal.php" enctype="multipart/form-data" target="_blank">
				
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Fecha Inicio:</label>
							<input type="date" name="txtDesde" id="txtDesde" class="form-control" required>
							<p class="help-block text-danger"></p>
						</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Fecha Fin:</label>
								<input type="date" name="txtHasta" id="txtHasta" class="form-control" required>
								<p class="help-block text-danger"></p>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Cliente:</label>
								<select class="form-control" name="cliente" id="cliente">
									<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Clientes 
									$sql1 = "SELECT * FROM ".$prefijobd."clientes WHERE Sucursal_RID = ".$sucursal." ORDER BY RazonSocial";
									$res1 = mysql_query($sql1, $cnx_cfdi);
									while($row1 = mysql_fetch_array($res1)){
										$id_cliente = $row1['ID'];
										$nom_cliente = $row1['RazonSocial'];
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
								<label>Ruta:</label>
								<select class="form-control" name="ruta" id="ruta">
									<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Ruta 
									$sql2 = "SELECT * FROM ".$prefijobd."rutas WHERE Sucursal_RID = ".$sucursal." ORDER BY Ruta";
									$res2 = mysql_query($sql2, $cnx_cfdi);
									while($row2 = mysql_fetch_array($res2)){
										$id_ruta= $row2['ID'];
										$nom_ruta = $row2['Ruta'];
								?>
									<option value="<?php echo $id_ruta; ?>"><?php echo $nom_ruta; ?></option>
								<?php
									}
								?>
								</select>
								<p class="help-block text-danger"></p>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Operador:</label>
								<select class="form-control" name="operador" id="operador">
									<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Operador 
									$sql3 = "SELECT * FROM ".$prefijobd."operadores WHERE Sucursal_RID = ".$sucursal." ORDER BY Operador";
									$res3 = mysql_query($sql3, $cnx_cfdi);
									while($row3 = mysql_fetch_array($res3)){
										$id_operador= $row3['ID'];
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
						<div class="col-md-6">
							<div class="form-group">
								<label>Unidad:</label>
								<select class="form-control" name="unidad" id="unidad">
									<option value="0">- Seleccione -</option>
								<?php 
									//Buscar Operador 
									$sql4 = "SELECT * FROM ".$prefijobd."unidades WHERE Sucursal_RID = ".$sucursal." ORDER BY Unidad";
									$res4 = mysql_query($sql4, $cnx_cfdi);
									while($row4 = mysql_fetch_array($res4)){
										$id_unidad= $row4['ID'];
										$nom_unidad = $row4['Unidad'];
								?>
									<option value="<?php echo $id_unidad; ?>"><?php echo $nom_unidad; ?></option>
								<?php
									}
								?>
								</select>
								<p class="help-block text-danger"></p>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>XFolio:</label>
								<input type="text" name="txtxfolio" id="txtxfolio" class="form-control">
								<p class="help-block text-danger"></p>
							</div>
						</div>
					</div>
					<input type="hidden" name="prefijodb" id="prefijodb" value='<?php echo $prefijobd; ?>'>
					<input type="hidden" name="sucursal" id="sucursal" value='<?php echo $sucursal; ?>'>
					<!--<button type="submit" name="btnEnviar" id="btnEnviar" value="PDF" class="btn btn-danger">PDF</button> -->
					<button type="submit" name="btnEnviar" id="btnEnviar" value="Excel" class="btn btn-success">Excel</button>
					
				</div>
				
			  </form>
			</center>
		</div>
	</body>
</html>

<!-- http://64.15.155.9/cfdipro/reporte_detalles_factura_fechas.php?prefijodb=prbfletesrgc_ -->
