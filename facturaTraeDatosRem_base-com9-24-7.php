<?php
	error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	include (__DIR__. '\\factura_trae_datos_remision_base-com9-24-7.php');
	//die('php '.__DIR__. '\\factura_trae_datos_remision.php?id='.$id_factura.'&prefijodb='.$prefijodb.'');
	sleep(2);
	if($prefijodb=='Ardica_' OR 'LEDS_'){
		include (__DIR__.'\\factura_update_partidas.php');
	}else{
		include (__DIR__.'\\factura_update_partidas-base.php');
	}
	
	sleep(2);
	include (__DIR__.'\\factura_trae_embalaje_rem_base-30.php');
	
	sleep(2);
	include (__DIR__.'\\factura_update_repartos_remision-base.php');
?>

