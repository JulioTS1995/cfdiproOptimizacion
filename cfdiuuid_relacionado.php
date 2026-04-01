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

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);

//Buscar datos de Nueva Factura
$resSQL00 = "SELECT * FROM " . $prefijobd . "factura WHERE ID = ".$idfactura;
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	$rowSQL00 = mysql_fetch_assoc($runSQL00);
	do { 
		$idcliente = $rowSQL00['CargoAFactura_RID'];
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
<link href="sierraestilo.css" rel="stylesheet" type="text/css">
</head>

<body class="twoColElsLtHdr">

<div id="container">
  <div id="header">
    <h1>Lista de Facturas Timbradas y Canceladas del Cliente <?php echo $v_nom_cliente; ?>
      <!-- end #header -->
    </h1>
  </div>
  <div id="sidebar1">
    <h3>&nbsp;</h3>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">
	<div><h2><b>Factura Seleccionada: <?php echo $xfolio ?></b></h2></div>
    <table border="1">
  <tr>
    <td class="input">Folio</td>
    <td class="input">Cliente</td>
    <td class="input"></td>
  </tr>
  <?php //do { ?>
  <?php
    //Buscar facturas Canceladas y Timbradas del Cliente de la Nueva Factura 
	
	//Verifica si hay registros
	$resSQLcount = "SELECT COUNT(*) as total FROM " . $prefijobd . "factura WHERE CargoAFactura_RID = " . $idcliente . " AND cCanceladoT IS NOT NULL AND cfdiselloCFD IS NOT NULL AND (cfdiSustituidaPor IS NULL OR cfdiSustituidaPor = '') Order by XFolio ASC";
	//$resSQLcount = "SELECT COUNT(*) as total FROM " . $prefijobd . "factura WHERE ID = 706102";
	$runSQLcount=mysql_query($resSQLcount);
	$rowSQLcount=mysql_fetch_assoc($runSQLcount);
	$numero = $rowSQLcount['total'];
	
	//Busca los registros
  	$resSQL1 = "SELECT * FROM " . $prefijobd . "factura WHERE CargoAFactura_RID = " . $idcliente . " AND cCanceladoT IS NOT NULL AND cfdiselloCFD IS NOT NULL AND (cfdiSustituidaPor IS NULL OR cfdiSustituidaPor = '') Order by XFolio ASC";
	//$resSQL1 = "SELECT * FROM " . $prefijobd . "factura WHERE ID = 706102";

	//echo "Numero: ".$numero;
	//echo $resSQL1;
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	if ($numero > 0){
	while($rowSQL1 = mysql_fetch_array($runSQL1))
          {
		
  ?>
  <tr>
    <td width="60" class="table"><?php echo $rowSQL1['XFolio']; ?></td>
    <td width="180" class="table"><?php echo $v_nom_cliente; ?></td>
    <td width="180" class="table"><a href="cfdiuuid_insert.php?cfdiuuid=<?php echo $rowSQL1['cfdiuuid']; ?>&id_factura_sel=<?php echo $rowSQL1['ID']; ?>&xfolio=<?php echo $rowSQL1['XFolio']; ?>&foliofactura=<?php echo $idfactura; ?>&prefijodb=<?php echo $prefijobd; ?>&facturaorigen=<?php echo $xfolio; ?>" target ="_blank">Relacionar UUID</a></td>
	<!--<td><?php echo "HREF: cfdiuuid_insert.php?cfdiuuid=". $rowSQL1['cfdiuuid'] ." &xfolio=". $rowSQL1['XFolio']."&foliofactura=". $idfactura."&prefijodb=". $prefijobd. ""; ?></td>-->
	
  </tr>
  <?php 
	
		} 
	} else {
	}
		?>
</table>
<?php



?>
</form>
  <!-- end #mainContent --></div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
    <p>TractoSoft</p>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
<?php
//mysql_free_result($runSQL);
mysql_close($cnx_cfdi);


//http://localhost/cfdipro/cfdiuuid_relacionado.php?prefijodb=base_&id=706102&cliente=658215&xfolio=PR1
//http://localhost/cfdipro/cfdiuuid_relacionado.php?prefijodb=base_&id=706102&cliente=654372&xfolio=PR2


?>