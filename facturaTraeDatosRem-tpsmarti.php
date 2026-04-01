<?php
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	include (__DIR__. '\\factura_trae_datos_remision-tpsmarti.php');
	//die('php '.__DIR__. '\\factura_trae_datos_remision.php?id='.$id_factura.'&prefijodb='.$prefijodb.'');
	sleep(2);
	if($prefijodb=='Ardica_' OR 'LEDS_'){
		include (__DIR__.'\\factura_update_partidas.php');
	}else{
		include (__DIR__.'\\factura_update_partidas2.php');
	}
	
	sleep(2);
	include (__DIR__.'\\factura_trae_embalaje_rem-tpsmarti.php');
	
	sleep(2);
	include (__DIR__.'\\factura_update_repartos_remision.php');
?>

