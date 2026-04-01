<?php
	set_time_limit ( 720 );
	ini_set('max_execution_time', 720);
	require_once('cnx_cfdi.php');
    	mysql_select_db($database_cfdi, $cnx_cfdi);
    	

	$resSQL3 = "SELECT c.Liquidacion FROM teqsa_iave as c WHERE c.Liquidado='0' and c.Liquidacion IS NOT NULL GROUP BY Liquidacion";
	$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
    	$rowSQL3 = mysql_fetch_assoc($runSQL3);
	//echo $rowSQL3['Liquidacion'];

	$resSQL2 = "UPDATE teqsa_liquidacionesiave as a LEFT JOIN teqsa_iave as b on a.zID_RID=b.ID LEFT JOIN teqsa_Liquidaciones as c on c.xfolio=b.Liquidacion";
	$resSQL2 = $resSQL2." SET a.FolioSubLiqIAVE_REN='Liquidaciones',a.FolioSubLiqIAVE_RID=c.ID,a.FolioSubLiqIAVE_RMA='FolioSubLiqIAVE',b.Liquidado='1' WHERE b.Liquidado='0' AND (b.Liquidacion is not null or b.Liquidacion<>'') AND a.FolioSubLiqIAVE_REN IS NULL";
	$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
	echo $runSQL2;

	do
	{
		$resSQL4 = "UPDATE teqsa_Liquidaciones as b set b.yCasetasElectronicas=(SELECT SUM(c.gImporte) FROM teqsa_liquidacionesiave as c WHERE c.FolioSubLiqIAVE_RID=b.ID) WHERE b.XFolio='".$rowSQL3['Liquidacion']."'";
		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);

	} while ($rowSQL3 = mysql_fetch_assoc($runSQL3));

	echo " Casetas Agregadas";
	echo "<script>window.close();</script>";

?>