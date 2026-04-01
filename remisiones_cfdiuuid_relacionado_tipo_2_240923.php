<?php 

if (!isset($_POST['prefijodb']) || empty($_POST['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    die("Falta Factura");
}

if (!isset($_POST['xfolio']) || empty($_POST['xfolio'])) {
    die("Falta XFolio");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_POST["prefijodb"]);

$idfactura = $_POST["id"];


$xfolio = $_POST["xfolio"];

$tiporelacion = $_POST["txt_tipo_relacion"];

$tiporelacion2 = '00';


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

<div class="container" style="margin-top: 0;">
	
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
	<div class="row">
		<div class="col-md-12 text-center">
			<h3><b>Lista de Documentos del Cliente: <?php echo $v_nom_cliente ?></b></h3>
		</div>
	</div>
	<hr>
	<div class="row">
        <div class="col-lg-12" style="width:1050; overflow:scroll;">
            <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
                    <tr>
						<th>Folio</th>
                        <th>Cliente</th>
                        <th></th>
                    </tr>
                </thead>
                    <tbody>
                            <?php 
                                
								//Verifica si hay registros

								if($tiporelacion ==  '066' || $tiporelacion ==  '05'){
									//Verifica si hay registros
									$resSQLcount = "SELECT COUNT(*) as total FROM " . $prefijobd . "remisiones WHERE CargoACliente_RID = " . $idcliente . " AND cfdiselloCFD IS NOT NULL AND (RelacionadoPor IS NULL OR RelacionadoPor = '') Order by XFolio ASC";
								} else {
									$resSQLcount = "SELECT COUNT(*) as total FROM " . $prefijobd . "remisiones WHERE CargoACliente_RID = " . $idcliente . " AND (cCanceladoT IS NULL OR cCanceladoT='') AND cfdiselloCFD IS NOT NULL AND (cfdiSustituidaPor IS NULL OR cfdiSustituidaPor = '') Order by XFolio ASC";
								
								}

								
								$runSQLcount=mysql_query($resSQLcount);
								$rowSQLcount=mysql_fetch_assoc($runSQLcount);
								$numero = $rowSQLcount['total'];


								if($tiporelacion ==  '066' || $tiporelacion ==  '05'){
									//Busca los registros
									$resSQL1 = "SELECT * FROM " . $prefijobd . "remisiones WHERE CargoACliente_RID = " . $idcliente . " AND cfdiselloCFD IS NOT NULL AND (RelacionadoPor IS NULL OR RelacionadoPor = '') Order by XFolio ASC";
									
									if($tiporelacion ==  '066'){
										$tiporelacion = '06';
										$tiporelacion2 = '066';
									} elseif($tiporelacion ==  '05'){
										$tiporelacion = '05';
										$tiporelacion2 = '066';
									}
									

								} else {
									//Busca los registros
									$resSQL1 = "SELECT * FROM " . $prefijobd . "remisiones WHERE CargoACliente_RID = " . $idcliente . " AND (cCanceladoT IS NULL OR cCanceladoT='') AND cfdiselloCFD IS NOT NULL AND (cfdiSustituidaPor IS NULL OR cfdiSustituidaPor = '') Order by XFolio ASC";
									
								}
								

								//echo "Numero: ".$numero;
								//echo $resSQL1;
								$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
								if ($numero > 0){
								while($rowSQL1 = mysql_fetch_array($runSQL1))
									 {
		
									
                            ?>
                            <tr>
								<td style="text-align:center;"><?php echo $rowSQL1['XFolio']; ?></td>
								<td style="text-align:center;"><?php echo $v_nom_cliente; ?></td>
                                <td style="text-align:center;"><a href="remisiones_cfdiuuid_insert_tipo.php?cfdiuuid=<?php echo $rowSQL1['cfdiuuid']; ?>&id_factura_sel=<?php echo $rowSQL1['ID']; ?>&xfolio=<?php echo $rowSQL1['XFolio']; ?>&foliofactura=<?php echo $idfactura; ?>&prefijodb=<?php echo $prefijobd; ?>&facturaorigen=<?php echo $xfolio; ?>&tiporelacion=<?php echo $tiporelacion; ?>&tiporelacion2=<?php echo $tiporelacion2; ?>" target ="_blank">Relacionar UUID</a></td>
																
                            </tr>
                            <?php
									} 
								} else {
								}
							?>
                    </tbody>
            </table>
			<br>
        </div>
		<hr>
		<br>
		<br>
    </div>	
	
	
	
	
<script>
  $(document).ready(function() {
    $('#table').DataTable();
  } );
</script>
</body>
</html>
<?php
//mysql_free_result($runSQL);
mysql_close($cnx_cfdi);


//http://localhost/cfdipro/cfdiuuid_relacionado.php?prefijodb=base_&id=706102&cliente=658215&xfolio=PR1
//http://localhost/cfdipro/cfdiuuid_relacionado.php?prefijodb=base_&id=706102&cliente=654372&xfolio=PR2


?>