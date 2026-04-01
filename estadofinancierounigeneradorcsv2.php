<?php
	set_time_limit(350);
	require_once('../cfdipro/cnx_cfdi.php');
    	mysql_select_db($database_cfdi, $cnx_cfdi);

    if(isset($_POST['base']))
        $base = $_POST['base'];
	
	if (isset($_POST['desde']))
		  $fechad = $_POST['desde'];

    if (isset($_POST['hasta']))
		  $fechah = $_POST['hasta'];

    /*if(isset($_GET['vista']))
       $vista = $_GET['vista'];

    if (isset($_GET['unidad']))
		 $unidad = $_GET['unidad'];*/


	$resRuta ="C:\\xampp\\htdocs\\cfdipro\\EstadoFinancieroUni.csv";
	$escribir =fopen($resRuta,"w+"); //este comando "fopen" abre el archivo en la variable $escribir

	/*$runSQL1 = mysql_query($_GET['consulta'], $cnx_cfdi);
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
  		if (isset($rowSQL1['Unidad'])) {*/
			$renglontotal=0;
			//fwrite($escribir,utf8_encode("\"".$rowSQL1['Unidad']."\"").",");
			//fwrite($escribir,utf8_encode("\"".$rowSQL1['Clase']."\"").",");
			
			fwrite($escribir,",ESTADO DE RESULTADOS DE OPERACIONES \r\n\r\n\r\n");
			fwrite($escribir,"INGRESOS,\r\n");
			
			$resSQL2 = "SELECT Round(Sum(IF(a.ztotal is null,0,a.ztotal)),2) as Facturacion FROM ".$base."factura as a WHERE DATE(a.creado) BETWEEN '".$fechad."' AND '".$fechah."' AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') AND a.ztotal > 0;";
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		$rowSQL2 = mysql_fetch_assoc($runSQL2);
			$RFacturacion=Round($rowSQL2['Facturacion'],2);
			fwrite($escribir,"Facturados,".$RFacturacion.",\r\n");

			$resSQL3 = "SELECT Round(SUM(if(a.Importe IS NULL,0,a.Importe)),2) as Abono FROM ".$base."AbonosSub as a left join ".$base."Abonos as b ON b.ID=a.FolioSub_RID WHERE date(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."';";
    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
    		$rowSQL3 = mysql_fetch_assoc($runSQL3);
			$RSueldo=Round($rowSQL3['Abono'],2);
			fwrite($escribir,"Cobrados,\"".number_format($RSueldo,2)."\",\r\n");

    		$resSQL9 = "SELECT Round(Sum(IF(a.CobranzaSaldo is null,0,a.CobranzaSaldo)),2) as Saldo FROM ".$base."factura as a WHERE DATE(a.Vence) BETWEEN '".$fechad."' AND '".$fechah."' AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') AND a.CobranzaSaldo > 0;";
    		$runSQL9 = mysql_query($resSQL9, $cnx_cfdi);
    		$rowSQL9 = mysql_fetch_assoc($runSQL9);
			$RSaldo=Round($rowSQL9['Saldo'],2);
			fwrite($escribir,"Por Cobrar,\"".number_format($RSaldo,2)."\",\r\n");

    		$resSQL4 = "SELECT Round(Sum(IF(a.CobranzaSaldo is null,0,a.CobranzaSaldo)),2) as SaldoAnterior FROM ".$base."factura as a WHERE DATE(a.Vence) < '".$fechad."' AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') AND a.CobranzaSaldo > 0;";
    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
    		$rowSQL4 = mysql_fetch_assoc($runSQL4);
			$RCombustible=Round($rowSQL4['SaldoAnterior'],2);
			fwrite($escribir,"Fletes Anteriores,\"".number_format($RCombustible,2)."\",\r\n\r\n");
			
			fwrite($escribir,"EGRESOS,\r\n");
			//egresos
			$resSQL5 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL5 = $resSQL5." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Administrativo');";
    		$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
    		$rowSQL5 = mysql_fetch_assoc($runSQL5);
			$RCompras=Round($rowSQL5['Compras'],2);
			fwrite($escribir,"Administrativo,\"".number_format($RCompras,2)."\",\r\n");

    		$resSQL6 = "SELECT Round(SUM(If(a.CostoManoObra IS NULL,0,a.CostoManoObra)),2) as Mantenimiento";
    		$resSQL6 = $resSQL6." FROM ".$base."Mantenimientos as a";
    		$resSQL6 = $resSQL6." WHERE DATE(a.fecha) BETWEEN '".$fechad."' AND '".$fechah."';";
    		$runSQL6 = mysql_query($resSQL6, $cnx_cfdi);
    		$rowSQL6 = mysql_fetch_assoc($runSQL6);
			$RCasetas=Round($rowSQL6['Mantenimiento'],2);
			fwrite($escribir,"Mantenimiento,\"".number_format($RCasetas,2)."\",\r\n");

			$resSQL11 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as Importe";
    		$resSQL11 = $resSQL11." FROM ".$base."GastosViajes as a WHERE date(a.fecha) Between '".$fechad."' AND '".$fechah."' AND a.Estatus='Completado';";
    		$runSQL11 = mysql_query($resSQL11, $cnx_cfdi);
    		$rowSQL11 = mysql_fetch_assoc($runSQL11);
			$RDepositos=Round($rowSQL11['Importe'],2);
			//select a.importe from logisticorp_prestamos as a WHERE date(a.Fecha) between '2018-01-01' AND '2018-06-11';
			//select a.gImporte from logisticorp_iave as a WHERE date(a.cFecha) between '2018-01-01' AND '2018-06-11';
			fwrite($escribir,"Operativo,\"".number_format($RDepositos,2)."\",\r\n");
			
			$resSQL7 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL7 = $resSQL7." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Adquisiciones');";
    		$runSQL7 = mysql_query($resSQL7, $cnx_cfdi);
    		$rowSQL7 = mysql_fetch_assoc($runSQL7);
			$RCompras7=Round($rowSQL7['Compras'],2);
			fwrite($escribir,"Adquisiciones,\"".number_format($RCompras7,2)."\",\r\n");
			
			$resSQL8 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL8 = $resSQL8." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Comisiones');";
    		$runSQL8 = mysql_query($resSQL8, $cnx_cfdi);
    		$rowSQL8 = mysql_fetch_assoc($runSQL8);
			$RCompras8=Round($rowSQL8['Compras'],2);
			fwrite($escribir,"Comisiones,\"".number_format($RCompras8,2)."\",\r\n");
			
			$resSQL10 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL10 = $resSQL10." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Pago Trailer');";
    		$runSQL10 = mysql_query($resSQL10, $cnx_cfdi);
    		$rowSQL10 = mysql_fetch_assoc($runSQL10);
			$RCompras10=Round($rowSQL10['Compras'],2);
			fwrite($escribir,"Pago Trailer,\"".number_format($RCompras10,2)."\",\r\n");
			
			$resSQL12 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL12 = $resSQL12." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Prestamos');";
    		$runSQL12 = mysql_query($resSQL12, $cnx_cfdi);
    		$rowSQL12 = mysql_fetch_assoc($runSQL12);
			$RCompras12=Round($rowSQL12['Compras'],2);
			fwrite($escribir,"Prestamos,\"".number_format($RCompras12,2)."\",\r\n");
			
			$resSQL13 = "SELECT Round(SUM(If(a.Total IS NULL,0,a.Total)),2) as Compras";
    		$resSQL13 = $resSQL13." FROM ".$base."Compras as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Rubro_RID IS NOT NULL AND a.Rubro_RID IN (SELECT b.ID FROM ".$base."rubrocompras as b WHERE b.Rubro='Gastos Extraordinarios');";
    		$runSQL13 = mysql_query($resSQL13, $cnx_cfdi);
    		$rowSQL13 = mysql_fetch_assoc($runSQL13);
			$RCompras13=Round($rowSQL13['Compras'],2);
			fwrite($escribir,"Gastos Extraordinarios,\"".number_format($RCompras13,2)."\",\r\n\r\n");
			
			fwrite($escribir,"Resultados Despues de Impuestos,\r\n");
			//totales
			$TotalIngresos=$RFacturacion;//+$RSueldo+$RSaldo+$RCombustible;
			$TotalEgresos=$RCompras+$RCasetas+$RDepositos+$RCompras7+$RCompras8+$RCompras10+$RCompras12+$RCompras13;
			$Utilidad=$TotalIngresos-$TotalEgresos;
			
			fwrite($escribir,"Fondo revolvente,\"".number_format(($Utilidad*60)/100,2)."\",\r\n");
			fwrite($escribir,"Participacion,\"".number_format(($Utilidad*20)/100,2)."\",\r\n");
			fwrite($escribir,"Participacion,\"".number_format(($Utilidad*20)/100,2)."\",\r\n");
			fwrite($escribir,"Inversion,\"".number_format(($Utilidad*30)/100,2)."\",\r\n");
			

    		/*$resSQL12 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
    		$resSQL12 = $resSQL12." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsRastreo='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
    		$runSQL12 = mysql_query($resSQL12, $cnx_cfdi);
    		$rowSQL12 = mysql_fetch_assoc($runSQL12);
			$RMantenimiento=Round($rowSQL11['IMP']-$rowSQL11['DES'],2);
			$RRastreo=Round($rowSQL12['IMPO']-$rowSQL12['DESO'],2);
			fwrite($escribir,$RMantenimiento.",");
			fwrite($escribir,$RRastreo.",");
			$renglontotal=$renglontotal-$RMantenimiento-$RRastreo;

			$resSQL13 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
			$resSQL13 = $resSQL13." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsPension='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
			$runSQL13 = mysql_query($resSQL13,$cnx_cfdi);
			$rowSQL13 = mysql_fetch_assoc($runSQL13);

    		$resSQL7 = "SELECT Round(SUM(If(a.zPensionIVAb is null,0,a.zPensionIVAb)),2) as Pension";
    		$resSQL7 = $resSQL7." FROM ".$base."_liquidaciones as a WHERE a.zPensionIVAb>0 AND DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL7 = mysql_query($resSQL7, $cnx_cfdi);
    		$rowSQL7 = mysql_fetch_assoc($runSQL7);
			//fwrite($escribir,$rowSQL7['Viaticos1']+$rowSQL7['Viaticos2']+$rowSQL7['Viaticos3'].",");
			$RPension=Round($rowSQL7['Pension']+($rowSQL13['IMPO']-$rowSQL13['DESO']),2);
			fwrite($escribir,$RPension.",");
			$renglontotal=$renglontotal-$RPension;

			$resSQL8 = "SELECT Round(SUM(If(a.zFederalesIVAb IS NULL,0,a.zFederalesIVAb)),2) as Multas,Round(if(SUM(a.zManiobrasIVAb) IS NULL,0,SUM(a.zManiobrasIVAb)),2) as Maniobras";
    		$resSQL8 = $resSQL8." FROM ".$base."_liquidaciones as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL8 = mysql_query($resSQL8, $cnx_cfdi);
    		$rowSQL8 = mysql_fetch_assoc($runSQL8);
			$RMultas=Round($rowSQL8['Multas'],2);
			$RManiobras=Round($rowSQL8['Maniobras'],2);
			fwrite($escribir,$RMultas.",");
			fwrite($escribir,$RManiobras.",");
			$renglontotal=$renglontotal-$RMultas-$RManiobras;

			fwrite($escribir,$renglontotal."\r\n");
			//$resSQL14 = "SELECT Round(IF(SUM(a.Importe) IS NULL,0,SUM(a.Importe)),2) as IMPO,Round(IF(SUM(a.Descuento) IS NULL,0,SUM(a.Descuento)),2) as DESO FROM (SELECT if(Importe IS NULL,0,Importe) as Importe,if(Descuento IS NULL,0,Descuento) as Descuento";
			//$resSQL14 = $resSQL14." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID Left Join ".$base."_proveedores as d ON b.ProveedorNo_RID=d.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsFlete='1' AND d.Permisionario='1' AND a.Unidad_RID=".$rowSQL1['id'].") as a;";
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
	} while ($rowSQL1 = mysql_fetch_assoc($runSQL1));*/

	//fwrite($escribir,"-,-,".$facturaciontotal.",".$sueldooperadortotal.",".$combustibletotal.",".$casetastotal.",".$mantenimientototal.",".$rastreototal.",".$pensiontotal.",".$multastotal.",".$maniobrastotal.",".$sumrenglones."\r\n");

	fclose($escribir);

	//Si la variable archivo que pasamos por URL no esta
	//establecida acabamos la ejecucion del script.
	//if (!isset($_GET['archivo']) || empty($_GET['archivo'])) {
   	//	exit();
	//}

	//Utilizamos basename por seguridad, devuelve el
	//nombre del archivo eliminando cualquier ruta.
	$archivo = basename("C:\\xampp\\htdocs\\cfdipro\\EstadoFinancieroUni.csv");

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
