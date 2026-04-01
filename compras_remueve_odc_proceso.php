<?php
	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);

	$id_odc = $_GET["id_odc"];
	$id_compra = $_GET["id_compra"];
	$prefijobd = $_GET["prefijobd"];

	/*echo "ID ODC: ".$id_odc;
	echo "<br>"; 
	echo "Prefijo: ".$prefijobd;
	echo "<br>"; 
	echo "ID Compra: ".$id_compra;*/


	
	
	//Buscar ODC
	$sql03="SELECT * FROM " . $prefijobd . "ordencompra WHERE ID = ".$id_odc;
	$res_sql03=mysql_query($sql03);
									
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
		$xfolio_odc = $fila_sql03['XFolio'];
		
	}
	
	//Buscar Compra
	$sql04="SELECT * FROM " . $prefijobd . "compras WHERE ID = ".$id_compra;
	$res_sql04=mysql_query($sql04);
									
	while ($fila_sql04 = mysql_fetch_array($res_sql04)){
		$xfolio_compra = $fila_sql04['XFolio'];
		
	}
	
	
	//Actualiza Orden Compra 
	mysql_query("UPDATE " . $prefijobd . "ordencompra SET 
	Compra = ''
	WHERE ID = ".$id_odc."");
	
	
?>

<!DOCTYPE html>
<html lang="en">
<head>

<!-- Latest compiled and minified CSS Estilos MENU Header -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>


  <link rel="stylesheet" href="css/estilo_forms.css" type="text/css"/>

  <link rel="stylesheet" href="css/table_search.css" type="text/css"/>
  <script src="js/table_search.js"></script>
 

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Ordenes de Compra</title>

    <link rel="shortcut icon" href="imagenes/logo_ts.ico">


    

</head>
<body >

<div class="container" style="margin-top: 0;">
	<div style="margin-top: 20px;left: 30%; position:fixed;">
		<h3 class="titulo_1 col-12"> <small class="text-muted">Orden de Compra: </small><?php echo $xfolio_odc; ?><small class="text-muted">, se removio de la Compra: </small><?php echo $xfolio_compra; ?></h3>
	</div>
	<div style="margin: 0;left: 2%;">
        <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
    </div>
	<br>
	

</div>

   
</body>
</html>