<?php 

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Falta Factura");
}

if (!isset($_GET['xfolio']) || empty($_GET['xfolio'])) {
    die("Falta XFolio");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

$idfactura = $_GET["id"];


$xfolio = $_GET["xfolio"];

//$tiporelacion = $_GET["tipo"];


//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);

//Buscar datos de Nueva Remision
$resSQL00 = "SELECT * FROM " . $prefijobd . "remisiones WHERE ID = ".$idfactura;
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	$rowSQL00 = mysql_fetch_assoc($runSQL00);
	do { 
		$idcliente = $rowSQL00['CargoACliente_RID'];
	} while ($rowSQL00 = mysql_fetch_assoc($runSQL00)); 
	
	
//Buscar Nombre del Cliente
$resSQL0 = "SELECT * FROM " . $prefijobd . "clientes WHERE ID = ".$idcliente;
//echo $resSQL0;
	$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
	$rowSQL0 = mysql_fetch_assoc($runSQL0);
	do { 
		$v_nom_cliente = $rowSQL0['RazonSocial'];
	} while ($rowSQL0 = mysql_fetch_assoc($runSQL0)); 
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>CFDI UUID Relacionados</title>
<!--<link href="sierraestilo.css" rel="stylesheet" type="text/css">-->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<!-- datatable -->
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
<!-- datatable -->

</head>

<body >

<div id="container">
	
	<div class="row">
		<div class="col-md-12 text-center">
			<h1><b>Relacionar CFDi</b></h1>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-md-12 text-center">
			<h2><b>Remision Seleccionada: <?php echo $xfolio ?></b></h2>
		</div>
	</div>
	<hr>
	<br>
	<div class="row">
		<div class="col-md-3">
		</div>
		<div class="col-md-6">
			<form action="remisiones_cfdiuuid_relacionado_tipo_2.php" name="form1" method="post" enctype="application/x-www-form-urlencoded" id="form1">
				<div class="form-group">
					<label><strong>Tipo de Relacion:</strong></label>
					<select class="form-control" id="txt_tipo_relacion" name="txt_tipo_relacion">
						<!--<option value="02">02 - Nota de débito de los documentos relacionados</option>-->
						<option value="04">04 - Sustitución de los CFDI previos</option>
						<option value="05">05 - Traslados de Mercancias Facturados Previamente</option>
						<!--<option value="06">06 - Factura Generada por los Traslados Previos</option>-->
						<option value="066">06 - Carta Porte Traslado</option>
						<!--<option value="07">07 - CFDI por aplicación de anticipo</option>-->
						<!--<option value="09">09 - Factura Generada por Pagos Diferidos</option>-->	
					</select>
					<input id="id" type="hidden" width="100%" name="id" value="<?php echo $idfactura ?>" readonly size="10" style="background-color:#D7D7D7"  />
					<input id="xfolio" type="hidden" width="100%" name="xfolio" value="<?php echo $xfolio ?>" readonly size="10" style="background-color:#D7D7D7"  />
					<input id="prefijodb" type="hidden" width="100%" name="prefijodb" value="<?php echo $prefijobd ?>" readonly size="10" style="background-color:#D7D7D7"  />
				</div>
				<br>
				<br>
				<button type="submit" class="btn btn-primary">Siguiente <span class="glyphicon glyphicon-chevron-right"></span></button>
				<br>
				<br>
			</form>
		</div>
		<div class="col-md-3">
		</div>
	</div>

</div>
</body>
</html>
<?php
//mysql_free_result($runSQL);
mysql_close($cnx_cfdi);


//http://localhost/cfdipro/remisiones_cfdiuuid_relacionado_tipo.php?prefijodb=prueba_&id=3822282&xfolio=PR243

//Ejemplo
//CT213
//CT214



?>