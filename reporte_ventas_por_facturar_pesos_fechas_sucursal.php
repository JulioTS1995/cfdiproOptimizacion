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
    <title>Reporte Ventas por Facturar Pesos</title>
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
			  <h1>Reporte Facturas por Folios</h1>
			</div>
			<center>
			  <form method="post" action="reporte_ventas_por_facturar_sucursal.php" enctype="multipart/form-data" target="_blank">
				
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Fecha Inicio:</label>
							<input type="date" name="txtDesde" id="txtDesde" class="form-control" required="required">
							<p class="help-block text-danger"></p>
						</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Fecha Fin:</label>
								<input type="date" name="txtHasta" id="txtHasta" class="form-control" required="required">
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
								<label>Moneda:</label>
								<select class="form-control" name="moneda" id="moneda">
								  <option value="PESOS">PESOS</option>
								  <option value="DOLARES">DOLARES</option>
								</select>
								<p class="help-block text-danger"></p>
							</div>
						</div>
					</div>
					<input type="hidden" name="prefijodb" id="prefijodb" value='<?php echo $prefijobd; ?>'>
					<input type="hidden" name="sucursal" id="sucursal" value='<?php echo $sucursal; ?>'>
					<button type="submit" name="btnEnviar" id="btnEnviar" value="PDF" class="btn btn-danger">PDF</button>
					<button type="submit" name="btnEnviar" id="btnEnviar" value="Excel" class="btn btn-success">Excel</button>
					
				</div>
				
			  </form>
			</center>
		</div>
	</body>
</html>

<!-- http://174.142.68.199/cfdipro/reporte_cobranza_tpch_fechas.php?prefijodb=tpch_ -->