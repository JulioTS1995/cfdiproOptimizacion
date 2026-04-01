<?php
	set_time_limit(350);
//	require_once('../cfdipro/cnx_cfdi.php');
	require_once('cnx_cfdi.php');
    	mysql_select_db($database_cfdi, $cnx_cfdi);

    //if(isset($_POST['prefijo']))
    //    $base = $_POST['prefijo'];
    $rutaarchivo = "";
    $vlprefijodb = "xtrapak_";
  

	
	if (isset($_POST['desde']))
		  $fechad = $_POST['desde'];

    if (isset($_POST['hasta']))
		  $fechah = $_POST['hasta'];

	$fechad = "2018-07-01";
	$fechah = "2018-07-30";

    /*if(isset($_GET['vista']))
       $vista = $_GET['vista'];

    if (isset($_GET['unidad']))
		 $unidad = $_GET['unidad'];*/


	$resRuta ="C:\\xampp\\htdocs\\cfdipro\\ReporteRemisiones.csv";
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
			
			fwrite($escribir,"REPORTE DE REMISIONES \r\n");
			fwrite($escribir,"CargoA,Moneda,XFolio,Operador,RemisionOperador,Creado,Unidad,Ruta,Origen,Destino,Booking,Cantidad Pzas,Peso,Se Facturo En,".utf8_decode("Liquidación").",XPesoTotal,yFlete,Seguro,Carga,Descarga,".utf8_decode("Recolección").",Reparto,Demoras,".utf8_decode("Autopísta").",Otros,zSubtotal,zImpuesto,zRetenido,zTotal\r\n");
			
			$resSQL2 = "SELECT R.ID, (SELECT RazonSocial FROM ".$vlprefijodb."clientes WHERE ID = R.CargoACliente_RID) AS Cargo_A, R.Moneda, R.XFolio, (SELECT Operador FROM ".$vlprefijodb."operadores WHERE ID = R.Operador_RID) AS Operador, R.RemisionOperador, R.Creado, (SELECT Unidad FROM ".$vlprefijodb."unidades WHERE ID = R.Unidad_RID) AS Unidad, (SELECT Ruta FROM ".$vlprefijodb."rutas WHERE ID = R.Ruta_RID) As Ruta, R.Remitente, R.Destinatario, R.SeFacturoEn, R.Liquidacion, R.xPesoTotal, R.yFlete, R.ySeguro, R.yCarga, R.yDescarga, R.yRecoleccion, R.yRepartos, R.yDemoras, R.yAutopistas, R.yOtros, R.zSubtotal, R.zImpuesto, R.zRetenido, R.zTotal FROM ".$vlprefijodb."remisiones R  WHERE R.Creado BETWEEN '".$fechad." 00:00:00' AND '".$fechah." 23:59:59' ORDER BY Cargo_A";
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		while($rowSQL2 = mysql_fetch_assoc($runSQL2)){
    			$id_r=$rowSQL2['ID'];
    			$cargoa=$rowSQL2['Cargo_A'];
    			$moneda=$rowSQL2['Moneda'];
    			$xfolio=$rowSQL2['XFolio'];
    			$operador=$rowSQL2['Operador'];
    			$remisionoperador=$rowSQL2['RemisionOperador'];
    			$fecha_creado_t=$rowSQL2['Creado'];
    			$fecha_creado = date("d-m-Y", strtotime($fecha_creado_t));
    			$unidad=$rowSQL2['Unidad'];
    			$ruta=$rowSQL2['Ruta'];
    			$remitente=$rowSQL2['Remitente'];
    			$destinatario=$rowSQL2['Destinatario'];

    			$cadena_bkg = "/ ";
				$resSQL3 = "SELECT BKG FROM ".$vlprefijodb."remisionessub WHERE FolioSub_RID = $id_r";
	    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
	    		while($rowSQL3 = mysql_fetch_assoc($runSQL3)){
	    			$cadena_bkg.= $rowSQL3['BKG'];
	    			$cadena_bkg.= " / ";
	    		}

	    		$resSQL4 = "SELECT SUM(Cantidad) AS Total1 FROM ".$vlprefijodb."remisionessub WHERE FolioSub_RID = $id_r";
	    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
	    		while($rowSQL4 = mysql_fetch_assoc($runSQL4)){
	    			$cantidadpza_t = $rowSQL4['Total1'];
	    			$cantidadpza = number_format($cantidadpza_t,2);
	    		}

	    		$resSQL5 = "SELECT SUM(Peso) AS Total2 FROM ".$vlprefijodb."remisionessub WHERE FolioSub_RID = $id_r";
	    		$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
	    		while($rowSQL5 = mysql_fetch_assoc($runSQL5)){
	    			$peso_t = $rowSQL5['Total2'];
	    			$peso = number_format($peso_t,2);
	    		}

    			$sefacturoen=$rowSQL2['SeFacturoEn'];
    			$liquidacion=$rowSQL2['Liquidacion'];
    			$xpesototal_t=$rowSQL2['xPesoTotal'];
    			$xpesototal = number_format($xpesototal_t,2);
    			$flete_t=$rowSQL2['yFlete'];
    			$flete = number_format($flete_t,2);
    			$seguro_t=$rowSQL2['ySeguro'];
    			$seguro = number_format($seguro_t,2);
    			$carga_t=$rowSQL2['yCarga'];
    			$carga = number_format($carga_t,2);
    			$descarga_t=$rowSQL2['yDescarga'];
    			$descarga = number_format($descarga_t,2);
    			$recoleccion_t=$rowSQL2['yRecoleccion'];
    			$recoleccion = number_format($recoleccion_t,2);
    			$repartos_t=$rowSQL2['yRepartos'];
    			$repartos = number_format($repartos_t,2);
    			$demoras_t=$rowSQL2['yDemoras'];
    			$demoras = number_format($demoras_t,2);
    			$autopistas_t=$rowSQL2['yAutopistas'];
    			$autopistas = number_format($autopistas_t,2);
    			$otros_t=$rowSQL2['yOtros'];
    			$otros = number_format($otros_t,2);
    			$subtotal_t=$rowSQL2['zSubtotal'];
    			$subtotal = number_format($subtotal_t,2);
    			$impuesto_t=$rowSQL2['zImpuesto'];
    			$impuesto = number_format($impuesto_t,2);
    			$retenido_t=$rowSQL2['zRetenido'];
    			$retenido = number_format($retenido_t,2);
    			$total_t=$rowSQL2['zTotal'];
    			$total=Round($total_t,2);
    			//$total = number_format($total_t,2);

    			fwrite($escribir, $cargoa.",".$moneda.",".$xfolio.",".$operador.",".$remisionoperador.",".$fecha_creado.",".$unidad.",\"".$ruta."\",\"".$remitente."\",\"".$destinatario."\",\"".$cadena_bkg."\",".$cantidadpza.",".$peso_t.",\"".$sefacturoen."\",".$liquidacion.",".$xpesototal_t.",".$flete_t.",".$seguro_t.",".$carga_t.",".$descarga_t.",".$recoleccion_t.",".$repartos_t.",".$demoras_t.",".$autopistas_t.",".$otros_t.",".$subtotal_t.",".$impuesto_t.",".$retenido_t.",".$total_t.",\r\n");
    		}


    			
    		


			

	fclose($escribir);

	//Si la variable archivo que pasamos por URL no esta
	//establecida acabamos la ejecucion del script.
	//if (!isset($_GET['archivo']) || empty($_GET['archivo'])) {
   	//	exit();
	//}

	//Utilizamos basename por seguridad, devuelve el
	//nombre del archivo eliminando cualquier ruta.
	
	$archivo = basename("C:\\xampp\\htdocs\\cfdipro\\ReporteRemisiones.csv");

	$ruta = $archivo;

	if (is_file($ruta))
	{
		//header('Content-Type: application/force-download');
		//header('Content-Disposition: attachment; filename='.$archivo);
		//header('Content-Transfer-Encoding: binary');
		//header('Content-Length: '.filesize($ruta));

		readfile($ruta);
	}
	else
	exit();

?>
