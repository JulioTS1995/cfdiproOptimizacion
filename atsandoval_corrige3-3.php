<?php
//Inicio la transaccion

	require_once('cnx_cfdi2.php');
    mysqli_select_db($cnx_cfdi2,$database_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_abono = $_GET["id"];
	
	$upd_basidgen = "UPDATE ".$prefijodb."abonossub SET Saldo= ImportePagado, SaldoRem= ImportePagado where FolioSub_RID= '".$id_abono."';";
	//die($upd_basidgen);
	$result_upd_basidgen = mysqli_query($cnx_cfdi2,$upd_basidgen);
	
	$upd_basidgen2 = "UPDATE ".$prefijodb."zzzlogtablas SET parametro='1', vlogi='0' WHERE id='".$id_abono."' and tabla='3' and parametro='2';";
	//die($upd_basidgen2);
	$result_upd_basidgen2 = mysqli_query($cnx_cfdi2,$upd_basidgen2);
	
	if ($result_upd_basidgen && $result_upd_basidgen2) {
		//Se hizo el update sin problemas
		echo "<h2>Se realizo la actualizacion con Exito.</h2>";
	}else{
		echo "<h2>error 33.</h2>";
	}
	
?>