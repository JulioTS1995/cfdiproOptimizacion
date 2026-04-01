<?php
	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	set_time_limit(350);
	$id_liqviaje = $_GET["id"];
	//$id_liq = $_GET["id_liq"];
	$prefijobd = $_GET["prefijodb"];
	
	
	$time = time();
	$fecha = date("Y-m-d H:i:s", $time);
	
	//Buscar ID Liq en LiqBitacora
	$sql03="SELECT * FROM " . $prefijobd . "liquidacionesviajes WHERE ID = ".$id_liqviaje;
	$res_sql03=mysql_query($sql03);								
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
		$id_liq = $fila_sql03['FolioSub_RID'];
		$folio_viaje = $fila_sql03['FolioViaje'];
	}
	
	//Buscar Remisiones Anexadas a la Viajes
	$sql04="SELECT * FROM " . $prefijobd . "viajes2 WHERE XFolio = '".$folio_viaje."'";
	$res_sql04=mysql_query($sql04);								
	while ($fila_sql04 = mysql_fetch_array($res_sql04)){
		$id_viaje = $fila_sql04['ID'];
	}
	
	$sql05="SELECT * FROM " . $prefijobd . "remisiones WHERE FolioSubViajes_RID = '".$id_viaje."'";
	$res_sql05=mysql_query($sql05);								
	while ($fila_sql05 = mysql_fetch_array($res_sql05)){
		$rem_id = $fila_sql05['ID'];
		
		//Buscar ID LiquidacionesSub 
		$sql06="SELECT * FROM " . $prefijobd . "liquidacionessub WHERE RemisionLiq_RID = '".$rem_id."'";
		$res_sql06=mysql_query($sql06);								
		while ($fila_sql06 = mysql_fetch_array($res_sql06)){
			$liqsub_id = $fila_sql06['ID'];
		}
		
		////Actualizar campo Liquidacion de Remisiones 
		mysql_query("UPDATE " . $prefijobd . "remisiones SET 
		Liquidacion = ''
		WHERE ID = ".$rem_id."");
		
		//Eliminar LiquidacionSub
		mysql_query("DELETE FROM " . $prefijobd . "liquidacionessub WHERE ID = ".$liqsub_id."");
		

	
	

		//Suma Importes
		$sql20="SELECT SUM(KmsCargado) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql20=mysql_query($sql20);								
		while ($fila_sql20 = mysql_fetch_array($res_sql20)){
			$v_KmsCargado = $fila_sql20['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		KmsCargado = ".$v_KmsCargado."
		WHERE ID = ".$id_liq."");
		
		$sql20="SELECT SUM(KmsVacio) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql20=mysql_query($sql20);								
		while ($fila_sql20 = mysql_fetch_array($res_sql20)){
			$v_KmsVacio = $fila_sql20['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		KmsVacio = ".$v_KmsVacio."
		WHERE ID = ".$id_liq."");
		
		$sql21="SELECT SUM(ComisionOperador) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql21=mysql_query($sql21);								
		while ($fila_sql21 = mysql_fetch_array($res_sql21)){
			$v_comision_oper = $fila_sql21['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		yComisionOperador = ".$v_comision_oper."
		WHERE ID = ".$id_liq."");
		
		$sql22="SELECT SUM(RepartosForaneos) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql22=mysql_query($sql22);								
		while ($fila_sql22 = mysql_fetch_array($res_sql22)){
			$v_repartos_foraneos = $fila_sql22['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		RepartosForaneos = ".$v_repartos_foraneos."
		WHERE ID = ".$id_liq."");
		
		$sql23="SELECT SUM(RepartosLocales) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql23=mysql_query($sql23);								
		while ($fila_sql23 = mysql_fetch_array($res_sql23)){
			$v_repartos_locales = $fila_sql23['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		RepartosLocales = ".$v_repartos_locales."
		WHERE ID = ".$id_liq."");
		
		$sql24="SELECT SUM(Total) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql24=mysql_query($sql24);								
		while ($fila_sql24 = mysql_fetch_array($res_sql24)){
			$v_total1 = $fila_sql24['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xTotal = ".$v_total1."
		WHERE ID = ".$id_liq."");
		
		$sql25="SELECT SUM(Subtotal) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql25=mysql_query($sql25);								
		while ($fila_sql25 = mysql_fetch_array($res_sql25)){
			$v_subtotal = $fila_sql25['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xSubtotal = ".$v_subtotal."
		WHERE ID = ".$id_liq."");
		
		$sql26="SELECT SUM(Seguro) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql26=mysql_query($sql26);								
		while ($fila_sql26 = mysql_fetch_array($res_sql26)){
			$v_seguro = $fila_sql26['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xSeguro = ".$v_seguro."
		WHERE ID = ".$id_liq."");
		
		$sql27="SELECT SUM(Retenido) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql27=mysql_query($sql27);								
		while ($fila_sql27 = mysql_fetch_array($res_sql27)){
			$v_retenido = $fila_sql27['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRetenido = ".$v_retenido."
		WHERE ID = ".$id_liq."");
		
		$sql28="SELECT SUM(Repartos) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql28=mysql_query($sql28);								
		while ($fila_sql28 = mysql_fetch_array($res_sql28)){
			$v_repartos = $fila_sql28['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRepartos = ".$v_repartos."
		WHERE ID = ".$id_liq."");
		
		$sql29="SELECT SUM(Recoleccion) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql29=mysql_query($sql29);								
		while ($fila_sql29 = mysql_fetch_array($res_sql29)){
			$v_recoleccion = $fila_sql29['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRecoleccion = ".$v_recoleccion."
		WHERE ID = ".$id_liq."");
		
		$sql30="SELECT SUM(Otros) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql30=mysql_query($sql30);								
		while ($fila_sql30 = mysql_fetch_array($res_sql30)){
			$v_otros = $fila_sql30['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xOtros = ".$v_otros."
		WHERE ID = ".$id_liq."");
		
		$sql31="SELECT SUM(Impuesto) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql31=mysql_query($sql31);								
		while ($fila_sql31 = mysql_fetch_array($res_sql31)){
			$v_impuesto = $fila_sql31['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xImpuesto = ".$v_impuesto."
		WHERE ID = ".$id_liq."");
		
		$sql32="SELECT SUM(Descarga) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql32=mysql_query($sql32);								
		while ($fila_sql32 = mysql_fetch_array($res_sql32)){
			$v_descarga = $fila_sql32['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xDescarga = ".$v_descarga."
		WHERE ID = ".$id_liq."");
		
		$sql33="SELECT SUM(Demoras) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql33=mysql_query($sql33);								
		while ($fila_sql33 = mysql_fetch_array($res_sql33)){
			$v_demoras = $fila_sql33['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xDemoras = ".$v_demoras."
		WHERE ID = ".$id_liq."");
		
		$sql34="SELECT SUM(Carga) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql34=mysql_query($sql34);								
		while ($fila_sql34 = mysql_fetch_array($res_sql34)){
			$v_carga = $fila_sql34['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xCarga = ".$v_carga."
		WHERE ID = ".$id_liq."");
		
		$sql35="SELECT SUM(Autopistas) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql35=mysql_query($sql35);								
		while ($fila_sql35 = mysql_fetch_array($res_sql35)){
			$v_autopistas = $fila_sql35['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xAutopistas = ".$v_autopistas."
		WHERE ID = ".$id_liq."");
		
		$sql36="SELECT SUM(Flete) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql36=mysql_query($sql36);								
		while ($fila_sql36 = mysql_fetch_array($res_sql36)){
			$v_flete = $fila_sql36['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xFlete = ".$v_flete."
		WHERE ID = ".$id_liq."");
		
		$sql37="SELECT SUM(Peso) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql37=mysql_query($sql37);								
		while ($fila_sql37 = mysql_fetch_array($res_sql37)){
			$v_peso = $fila_sql37['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		Peso = ".$v_peso."
		WHERE ID = ".$id_liq."");
		
		$sql38="SELECT SUM(Mt3) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql38=mysql_query($sql38);								
		while ($fila_sql38 = mysql_fetch_array($res_sql38)){
			$v_mt3 = $fila_sql38['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		Mt3 = ".$v_mt3."
		WHERE ID = ".$id_liq."");
		
		$sql39="SELECT SUM(TotalMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql39=mysql_query($sql39);								
		while ($fila_sql39 = mysql_fetch_array($res_sql39)){
			$v_totalmb = $fila_sql39['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xTotalMB = ".$v_totalmb."
		WHERE ID = ".$id_liq."");
		
		$sql40="SELECT SUM(SubtotalMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql40=mysql_query($sql40);								
		while ($fila_sql40 = mysql_fetch_array($res_sql40)){
			$v_subtotalmb = $fila_sql40['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xSubtotalMB = ".$v_subtotalmb."
		WHERE ID = ".$id_liq."");
		
		$sql41="SELECT SUM(SeguroMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql41=mysql_query($sql41);								
		while ($fila_sql41 = mysql_fetch_array($res_sql41)){
			$v_seguromb = $fila_sql41['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xSeguroMB = ".$v_seguromb."
		WHERE ID = ".$id_liq."");
		
		$sql42="SELECT SUM(RetenidoMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql42=mysql_query($sql42);								
		while ($fila_sql42 = mysql_fetch_array($res_sql42)){
			$v_retenidomb = $fila_sql42['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRetenidoMB = ".$v_retenidomb."
		WHERE ID = ".$id_liq."");
		
		$sql43="SELECT SUM(RepartosMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql43=mysql_query($sql43);								
		while ($fila_sql43 = mysql_fetch_array($res_sql43)){
			$v_repartosmb = $fila_sql43['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRepartosMB = ".$v_repartosmb."
		WHERE ID = ".$id_liq."");
		
		$sql44="SELECT SUM(RecoleccionMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql44=mysql_query($sql44);								
		while ($fila_sql44 = mysql_fetch_array($res_sql44)){
			$v_recoleccionmb = $fila_sql44['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xRecoleccionMB = ".$v_recoleccionmb."
		WHERE ID = ".$id_liq."");
		
		$sql45="SELECT SUM(OtrosMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql45=mysql_query($sql45);								
		while ($fila_sql45 = mysql_fetch_array($res_sql45)){
			$v_otrosmb = $fila_sql45['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xOtrosMB = ".$v_otrosmb."
		WHERE ID = ".$id_liq."");
		
		$sql46="SELECT SUM(ImpuestoMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql46=mysql_query($sql46);								
		while ($fila_sql46 = mysql_fetch_array($res_sql46)){
			$v_impuestomb = $fila_sql46['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xImpuestoMB = ".$v_impuestomb."
		WHERE ID = ".$id_liq."");
		
		$sql47="SELECT SUM(DescargaMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql47=mysql_query($sql47);								
		while ($fila_sql47 = mysql_fetch_array($res_sql47)){
			$v_descargamb = $fila_sql47['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xDescargaMB = ".$v_descargamb."
		WHERE ID = ".$id_liq."");
		
		$sql48="SELECT SUM(DemorasMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql48=mysql_query($sql48);								
		while ($fila_sql48 = mysql_fetch_array($res_sql48)){
			$v_demorasmb = $fila_sql48['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xDemorasMB = ".$v_demorasmb."
		WHERE ID = ".$id_liq."");
		
		$sql49="SELECT SUM(CargaMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql49=mysql_query($sql49);								
		while ($fila_sql49 = mysql_fetch_array($res_sql49)){
			$v_cargamb = $fila_sql49['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xCargaMB = ".$v_cargamb."
		WHERE ID = ".$id_liq."");
		
		$sql50="SELECT SUM(AutopistasMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql50=mysql_query($sql50);								
		while ($fila_sql50 = mysql_fetch_array($res_sql50)){
			$v_autopistasmb = $fila_sql50['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xAutopistasMB = ".$v_autopistasmb."
		WHERE ID = ".$id_liq."");
		
		$sql51="SELECT SUM(FleteMB) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql51=mysql_query($sql51);								
		while ($fila_sql51 = mysql_fetch_array($res_sql51)){
			$v_fletemb = $fila_sql51['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		xFleteMB = ".$v_fletemb."
		WHERE ID = ".$id_liq."");
		
		$sql52="SELECT SUM(ComisionOperador) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql52=mysql_query($sql52);								
		while ($fila_sql52 = mysql_fetch_array($res_sql52)){
			$v_comision_operador = $fila_sql52['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		ComisionOperadorSuma = ".$v_comision_operador."
		WHERE ID = ".$id_liq."");
		
		/*$sql53="SELECT SUM(Egreso3ro) as sum_var FROM " . $prefijobd . "liquidacionessub WHERE FolioSub_RID =".$id_liq;
		$res_sql53=mysql_query($sql53);								
		while ($fila_sql53 = mysql_fetch_array($res_sql53)){
			$v_Egreso3ro = $fila_sql53['sum_var'];
		}
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		Egreso3ro = ".$v_Egreso3ro."
		WHERE ID = ".$id_liq."");*/
		
		//Calcula Egreso3ro Porcentaje
		/*if($v_fletemb > 0){
			$v_Egreso3roPorcentaje = $v_Egreso3ro /  $v_fletemb;
			mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			Egreso3roPorcentaje = ".$v_Egreso3roPorcentaje."
			WHERE ID = ".$id_liq."");
		}*/
		
		
		//Actualiza Total Flete en Liq
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		TotalFlete = ".$v_flete."
		WHERE ID = ".$id_liq."");
		
	} // Fin Remisiones	

		
		//Actualizar campo Liq.TotalFlete = Liq.xFlete 
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			TotalFlete = '".$v_flete."'
			WHERE ID = ".$id_liq."");
		

	//Actualizar campo Liquidacion en la Viajes2 seleccionada 
	mysql_query("UPDATE " . $prefijobd . "viajes2 SET 
			Liquidacion = ''
			WHERE ID = ".$id_viaje."");


	//Eliminar registro en liquidacionesviajes
	mysql_query("DELETE FROM " . $prefijobd . "liquidacionesviajes WHERE ID = ".$id_liqviaje."");
	
	
	
	
	
			
			
		
	

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

    <title>Viajes - Liquidación</title>

    <link rel="shortcut icon" href="imagenes/logo_ts.ico">


    

</head>
<body >

<div class="container" style="margin-top: 0;">
	<div style="margin-top: 20px;left: 30%; position:fixed;">
		<h3 class="titulo_1 col-12"> <small class="text-muted">Viaje: </small><?php echo $folio_viaje; ?><small class="text-muted">, se elimino de la Liquidacion </small></h3>
	</div>
	<div style="margin: 0;left: 2%;">
        <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
    </div>
	<br>
	

</div>

   
</body>
</html>