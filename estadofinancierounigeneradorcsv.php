<?php
	set_time_limit(350);
	require_once('../connections/cnx_cfdi.php');
    	mysql_select_db($database_cfdi, $cnx_cfdi);

    if(isset($_GET['base']))
        $base = $_GET['base'];

    if(isset($_GET['vista']))
       $vista = $_GET['vista'];

    if (isset($_GET['unidad']))
		 $unidad = $_GET['unidad'];


	$resRuta ="C:\\xampp\\htdocs\\ff\\EstadoFinancieroUnidades\\EstadoFinancieroUni.csv";
	$escribir =fopen($resRuta,"w+"); //este comando "fopen" abre el archivo en la variable $escribir

	$runSQL1 = mysql_query($_GET['consulta'], $cnx_cfdi);
	$rowSQL1 = mysql_fetch_assoc($runSQL1);

	fwrite($escribir,"Unidad,");
	fwrite($escribir,"Clase,");
	fwrite($escribir,"Facturacion,");
	//fwrite($escribir,"ComisionesVentas,");
	fwrite($escribir,"SueldoOper,");
	fwrite($escribir,"Combustible,");
	fwrite($escribir,"Casetas,");
	fwrite($escribir,"Mantenimiento,");
	fwrite($escribir,"Rastreo,");
	//fwrite($escribir,"ViaticosOper,");
	fwrite($escribir,"Pension,");
	fwrite($escribir,"Multas,");
	fwrite($escribir,"Maniobras,");
	//fwrite($escribir,"Permisionario");
	fwrite($escribir,"Margen de Contribucion\r\n");

	//variables de totales
	$facturaciontotal=0;
	//$comisionesventastotal=0;
	$sueldooperadortotal=0;
	$combustibletotal=0;
	$casetastotal=0;
	$mantenimientototal=0;
	$rastreototal=0;
	//$viaticostotal=0;
	$pensiontotal=0;
	$multastotal=0;
	$maniobrastotal=0;
	$fletetotal=0;
	$sumrenglones=0;

	do {
  		if (isset($rowSQL1['Unidad'])) {
			$renglontotal=0;
			fwrite($escribir,utf8_encode("\"".$rowSQL1['Unidad']."\"").",");
			fwrite($escribir,utf8_encode("\"".$rowSQL1['Clase']."\"").",");

			$resSQL2 = "SELECT Round(Sum(IF(a.zSubTotalConvertido is null,0,a.zSubTotalConvertido)),2) as Facturacion FROM ".$base."_remisiones as a,".$base."_factura as b WHERE a.sefacturoen=b.xfolio AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') AND a.zsubtotalconvertido > 0 AND a.Unidad_RID=".$rowSQL1['id']." AND DATE(b.creado) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND (b.cCanceladoT IS NULL OR b.cCanceladoT='') AND (b.Oficina_RID=12763 OR b.Oficina_RID=16985);";//",Round(if(Sum(a.comision) is null,0,Sum(a.comision)),2) as ComisionesVentas";
    		//$resSQL2 = $resSQL2." FROM (SELECT if(b.zSubTotalConvertido IS NULL,0,b.zSubTotalConvertido) as subtotal,if((b.zSubTotalConvertido*(b.porcentajecomision/100)) is null,0,(b.zSubTotalConvertido*(b.porcentajecomision/100))) as comision FROM ".$base."_remisiones as b,".$base."_factura as c WHERE b.sefacturoen=c.xfolio AND (b.cCanceladoT IS NULL OR b.cCanceladoT='') AND (b.Porcentajecomision > 0 or b.zsubtotalconvertido > 0) AND b.Unidad_RID=".$rowSQL1['id']." AND DATE(c.creado) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND (c.cCanceladoT IS NULL OR c.cCanceladoT='') AND (c.Oficina_RID=12763 OR c.Oficina_RID=16985)) as a;";
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		$rowSQL2 = mysql_fetch_assoc($runSQL2);
			$RFacturacion=Round($rowSQL2['Facturacion'],2);
			fwrite($escribir,$RFacturacion.",");
			$renglontotal=$renglontotal+$RFacturacion;
			//fwrite($escribir,$rowSQL2['ComisionesVentas'].",");

			$resSQL3 = "SELECT Round(SUM(if(b.zCombustibleIVAb IS NULL,0,b.zCombustibleIVAb)),2) as Combustible FROM ".$base."_liquidaciones as b WHERE b.zCombustibleIVAb>0 AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		//$resSQL3 = $resSQL3." FROM (SELECT if(b.zCombustibleIVAb IS NULL,0,b.zCombustibleIVAb) as comb FROM ".$base."_liquidaciones as b WHERE b.zCombustibleIVAb>0 AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].") as a;";
    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
    		$rowSQL3 = mysql_fetch_assoc($runSQL3);

    		$resSQL9 = "SELECT Round(SUM(if(b.SubTotalComb IS NULL,0,b.SubTotalComb)),2) as GCombustible FROM ".$base."_GastosViajes as b WHERE b.TipoVale='Combustible' AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Completado' AND b.Unidad_RID=".$rowSQL1['id'].";";
    		//$resSQL9 = $resSQL9." FROM (SELECT if(b.SubTotalComb IS NULL,0,b.SubTotalComb) as GComb FROM ".$base."_GastosViajes as b WHERE b.TipoVale='Combustible' AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Completado' AND b.Unidad_RID=".$rowSQL1['id'].") as a;";
    		$runSQL9 = mysql_query($resSQL9, $cnx_cfdi);
    		$rowSQL9 = mysql_fetch_assoc($runSQL9);

    		$resSQL4 = "SELECT Round(SUM(if(b.yComisionOperador IS NULL,0,b.yComisionOperador)),2) as Sueldo,Round(SUM(if(b.ySueldoGarantia IS NULL,0,b.ySueldoGarantia)),2) as SueldoG FROM ".$base."_liquidaciones as b WHERE DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id']."";//b.rosubtotal<>0 AND 
    		//$resSQL4 = $resSQL4." FROM (SELECT if(b.rosubtotal IS NULL,0,b.rosubtotal) as sueldo FROM ".$base."_liquidaciones as b WHERE b.rosubtotal<>0 AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].") as a;";
    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
    		$rowSQL4 = mysql_fetch_assoc($runSQL4);
			$RSueldo=Round($rowSQL4['Sueldo']+$rowSQL4['SueldoG'],2);
			$RCombustible=Round($rowSQL3['Combustible']+$rowSQL9['GCombustible'],2);
			fwrite($escribir,"\"".number_format($RSueldo,2)."\",");
			fwrite($escribir,"\"".number_format($RCombustible,2)."\",");
			$renglontotal=$renglontotal-$RSueldo-$RCombustible;

			$resSQL5 = "SELECT Round(SUM(If(b.zCasetasIVAb IS NULL,0,b.zCasetasIVAb)),2) as Casetas";
    		$resSQL5 = $resSQL5." FROM ".$base."_liquidaciones as b WHERE b.zCasetasIVAb>0 AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		//$resSQL5 = $resSQL5." WHERE (b.Liquidacion IS NOT NULL OR b.Liquidacion='') and b.Unidad_RID=".$rowSQL1['id']." AND DATE(b.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."';";
    		$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
    		$rowSQL5 = mysql_fetch_assoc($runSQL5);

    		$resSQL6 = "SELECT Round(SUM(If(b.gimporte IS NULL,0,b.gimporte)),2) as Casetas";
    		$resSQL6 = $resSQL6." FROM ".$base."_iave as b";
    		$resSQL6 = $resSQL6." WHERE b.bUnidad='".$rowSQL1['Unidad']."' AND DATE(b.cfecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."';";
    		$runSQL6 = mysql_query($resSQL6, $cnx_cfdi);
    		$rowSQL6 = mysql_fetch_assoc($runSQL6);
			$RCasetas=Round(($rowSQL6['Casetas']+$rowSQL5['Casetas'])/1.16,2);
			fwrite($escribir,"\"".number_format($RCasetas,2)."\",");
			$renglontotal=$renglontotal-$RCasetas;

			$resSQL11 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMP,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DES";
    		$resSQL11 = $resSQL11." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."') AND b.Estatus='Completado' AND c.EsMantenimiento='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
    		$runSQL11 = mysql_query($resSQL11, $cnx_cfdi);
    		$rowSQL11 = mysql_fetch_assoc($runSQL11);

    		$resSQL12 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
    		$resSQL12 = $resSQL12." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."') AND b.Estatus='Completado' AND c.EsRastreo='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
    		$runSQL12 = mysql_query($resSQL12, $cnx_cfdi);
    		$rowSQL12 = mysql_fetch_assoc($runSQL12);
			$RMantenimiento=Round($rowSQL11['IMP']-$rowSQL11['DES'],2);
			$RRastreo=Round($rowSQL12['IMPO']-$rowSQL12['DESO'],2);
			fwrite($escribir,$RMantenimiento.",");
			fwrite($escribir,$RRastreo.",");
			$renglontotal=$renglontotal-$RMantenimiento-$RRastreo;

			$resSQL13 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
			$resSQL13 = $resSQL13." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."') AND b.Estatus='Completado' AND c.EsPension='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
			$runSQL13 = mysql_query($resSQL13,$cnx_cfdi);
			$rowSQL13 = mysql_fetch_assoc($runSQL13);

    		$resSQL7 = "SELECT Round(SUM(If(a.zPensionIVAb is null,0,a.zPensionIVAb)),2) as Pension";
    		$resSQL7 = $resSQL7." FROM ".$base."_liquidaciones as a WHERE a.zPensionIVAb>0 AND DATE(a.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL7 = mysql_query($resSQL7, $cnx_cfdi);
    		$rowSQL7 = mysql_fetch_assoc($runSQL7);
			//fwrite($escribir,$rowSQL7['Viaticos1']+$rowSQL7['Viaticos2']+$rowSQL7['Viaticos3'].",");
			$RPension=Round($rowSQL7['Pension']+($rowSQL13['IMPO']-$rowSQL13['DESO']),2);
			fwrite($escribir,$RPension.",");
			$renglontotal=$renglontotal-$RPension;

			$resSQL8 = "SELECT Round(SUM(If(a.zFederalesIVAb IS NULL,0,a.zFederalesIVAb)),2) as Multas,Round(if(SUM(a.zManiobrasIVAb) IS NULL,0,SUM(a.zManiobrasIVAb)),2) as Maniobras";
    		$resSQL8 = $resSQL8." FROM ".$base."_liquidaciones as a WHERE DATE(a.Fecha) BETWEEN '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL8 = mysql_query($resSQL8, $cnx_cfdi);
    		$rowSQL8 = mysql_fetch_assoc($runSQL8);
			$RMultas=Round($rowSQL8['Multas'],2);
			$RManiobras=Round($rowSQL8['Maniobras'],2);
			fwrite($escribir,$RMultas.",");
			fwrite($escribir,$RManiobras.",");
			$renglontotal=$renglontotal-$RMultas-$RManiobras;

			fwrite($escribir,$renglontotal."\r\n");
			//$resSQL14 = "SELECT Round(IF(SUM(a.Importe) IS NULL,0,SUM(a.Importe)),2) as IMPO,Round(IF(SUM(a.Descuento) IS NULL,0,SUM(a.Descuento)),2) as DESO FROM (SELECT if(Importe IS NULL,0,Importe) as Importe,if(Descuento IS NULL,0,Descuento) as Descuento";
			//$resSQL14 = $resSQL14." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID Left Join ".$base."_proveedores as d ON b.ProveedorNo_RID=d.ID WHERE (date(b.fecha) Between '".$rowSQL1['fechad']."' AND '".$rowSQL1['fechah']."') AND b.Estatus='Completado' AND c.EsFlete='1' AND d.Permisionario='1' AND a.Unidad_RID=".$rowSQL1['id'].") as a;";
			//$runSQL14 = mysql_query($resSQL14, $cnx_cfdi);
			//$rowSQL14 = mysql_fetch_assoc($runSQL14);
			//fwrite($escribir,$rowSQL14['IMPO']-$rowSQL14['DESO']."");

			$facturaciontotal=$facturaciontotal+$rowSQL2['Facturacion'];
			//$comisionesventastotal=$comisionesventastotal+$rowSQL2['ComisionesVentas'];
			$sueldooperadortotal=$sueldooperadortotal+$rowSQL4['Sueldo']+$rowSQL4['SueldoG'];
			$combustibletotal=$combustibletotal+$rowSQL3['Combustible']+$rowSQL9['GCombustible'];
			$casetastotal=$casetastotal+($rowSQL6['Casetas']+$rowSQL5['Casetas'])/1.16;
			$mantenimientototal=$mantenimientototal+$rowSQL11['IMP']-$rowSQL11['DES'];
			$rastreototal=$rastreototal+$rowSQL12['IMPO']-$rowSQL12['DESO'];
			//$viaticostotal=$viaticostotal+$rowSQL7['Viaticos1']+$rowSQL7['Viaticos2']+$rowSQL7['Viaticos3'];
			$pensiontotal=$pensiontotal+$rowSQL7['Pension']+($rowSQL13['IMPO']-$rowSQL13['DESO']);
			$multastotal=$multastotal+$rowSQL8['Multas'];
			$maniobrastotal=$maniobrastotal+$rowSQL8['Maniobras'];
			//$fletetotal=$fletetotal+$rowSQL14['IMPO']-$rowSQL14['DESO'];
			$sumrenglones=$sumrenglones+$renglontotal;
		}
	} while ($rowSQL1 = mysql_fetch_assoc($runSQL1));

	fwrite($escribir,"-,-,".$facturaciontotal.",".$sueldooperadortotal.",".$combustibletotal.",".$casetastotal.",".$mantenimientototal.",".$rastreototal.",".$pensiontotal.",".$multastotal.",".$maniobrastotal.",".$sumrenglones."\r\n");

	fclose($escribir);

	//Si la variable archivo que pasamos por URL no esta
	//establecida acabamos la ejecucion del script.
	if (!isset($_GET['archivo']) || empty($_GET['archivo'])) {
   		exit();
	}

	//Utilizamos basename por seguridad, devuelve el
	//nombre del archivo eliminando cualquier ruta.
	$archivo = basename($_GET['archivo']);

	$ruta = $archivo;

	if (is_file($ruta))
	{
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename='.$archivo);
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($ruta));

		readfile($ruta);
	}
	else
	exit();

?>
