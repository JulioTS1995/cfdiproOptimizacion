<?php 

/*if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}*/

//Internalizo los parametros previo escape de caracteres especiales
//$prefijobd = @mysql_escape_string($_GET["base"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
//$pos = strpos($prefijobd, "_");

//if ($pos === false) {
//    $prefijobd = $prefijobd . "_";
//} 

//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["base"];

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
    
//	$resSQL = "SELECT * FROM " . $prefijobd . "unidades Order by Unidad ASC";
//echo $resSQL . "<BR>";
//	$runSQL = mysql_query($resSQL, $cnx_cfdi);
//    $rowSQL = mysql_fetch_assoc($runSQL);
    
//	$registros=20;
//    $pagina= $_GET["numP"];
	
//if (is_numeric($pagina)) 
//	$inicio=(($pagina-1)*$registros);
//else
//	$inicio=0;

//	$paginas = ceil($numReg/$registros);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Listado Tracking por Remision</title>
<link href="sierraestilo.css" rel="stylesheet" type="text/css">
</head>

<body class="twoColElsLtHdr">

<div id="container">
  <div id="header">
    <h1>Ultimo Tracking por Viaje
      <!-- end #header -->
    </h1>
  </div>
  <div id="sidebar1">
    <h3>Listado</h3>
    <p>&nbsp;</p>
	<form method="post" action="remisiones_tracking_notificacion_mail.php" enctype="multipart/form-data">
		<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
		<input type="date" class="form-control inputdefault" name="fechai" id="fecha" hidden value="<?php echo $fecha_inicio; ?>">
		<input type="date" class="form-control inputdefault" name="fechaf" id="fecha" hidden value="<?php echo $fecha_fin; ?>">
    <p><input type="submit" value="Enviar al Email" name="send_notificacion" class="btn btn-info"></p>
	</form>
    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">

    <table border="1">
  <tr>
    <th class="input">Viaje</th>
    <th class="input">Unidad</th>
    <th class="input">Estatus</th>
	<th class="input">Fecha</th>
	<th class="input">Documentador</th>
	<th class="input">Comentario</th>

	
  </tr>
  <?php
  
	//$resSQL="SELECT * FROM opl_remisiones WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' ORDER BY Unidad_RID";
	$resSQL="SELECT R.ID, R.XFolio, R.Unidad_RID, R.Creado, U.Unidad  FROM ".$prefijobd."remisiones R, ".$prefijobd."unidades U WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' AND R.Unidad_RID = U.ID ORDER BY U.Unidad";
	$runSQL=mysql_query($resSQL);
	while ($rowSQL=mysql_fetch_array($runSQL)){
		//Obtener_variables
		$id_remision = $rowSQL['ID'];
		$xfolio = $rowSQL['XFolio'];
		$unidad = $rowSQL['Unidad_RID'];
		//$fecha_temp = $rowSQL['Creado'];
		//$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
		
		if (isset($unidad)){
			
		} else {
			$unidad = 0;
		}
		
		$resSQL2="SELECT Unidad FROM ".$prefijobd."unidades WHERE ID = ".$unidad." ";
		$runSQL2=mysql_query($resSQL2);
		$rowSQL2=mysql_fetch_array($runSQL2);
		$nom_unidad = $rowSQL2['Unidad'];
		
		
		$resSQL1="SELECT * FROM ".$prefijobd."remisionesestatus2 WHERE FolioEstatus2_RID = ".$id_remision." order by ID desc limit 1";
		//echo $resSQL1;
		$runSQL1=mysql_query($resSQL1);
		$rowSQL1=mysql_fetch_array($runSQL1);
		
			$estatus = $rowSQL1['Estatus'];
			$fecha_temp = $rowSQL1['Fecha'];
			$fecha = date("d-m-Y H:i:s", strtotime($fecha_temp));
			$documentador = $rowSQL1['Documentador'];
			$comentario = $rowSQL1['Comentarios'];
		
		
  ?>
  <tr>

    <td width="60" class="table"><?php echo $xfolio; ?></td>
    <td width="60" class="table"><?php echo $nom_unidad; ?></td>
    <td width="200" class="table"><?php echo $estatus; ?></td>
	<td width="80" class="table"><?php echo $fecha; ?></td>
	<td width="200" class="table"><?php echo $documentador; ?></td>
	<td width="350" class="table"><?php echo $comentario; ?></td>

  </tr>
  <?php }  ?>
</table>
<?php



//	for($cont=1; $cont<=$paginas; $cont++)
//	{

//		echo "<a href='timbradoslist.php?numP=".$cont."' >$cont</a> ";	
//	}

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
mysql_free_result($runSQL);
mysql_close($cnx_cfdi);
?>