<?php
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	include (__DIR__. '\\factura_trae_datos_remision.php');
	//die('php '.__DIR__. '\\factura_trae_datos_remision.php?id='.$id_factura.'&prefijodb='.$prefijodb.'');
	sleep(2);
	include (__DIR__.'\\factura_update_partidas.php');
	sleep(2);
	include (__DIR__.'\\factura_trae_embalaje_rem.php');
?>

