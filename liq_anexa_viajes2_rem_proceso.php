<?php
	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	set_time_limit(350);
	$id_viaje = $_GET["id_viaje"];
	$id_liq = $_GET["id_liq"];
	$prefijobd = $_GET["prefijobd"];
	//$id_oper = $_GET["id_operador"];
	
	$time = time();
	$fecha = date("Y-m-d H:i:s", $time);
	
	//Buscar Viaje
	$sql03="SELECT * FROM " . $prefijobd . "viajes2 WHERE ID = ".$id_viaje;
	$res_sql03=mysql_query($sql03);								
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
		$xfolio_viaje = $fila_sql03['XFolio'];
	}
	
	//Buscar Liquidación
	$sql04="SELECT * FROM " . $prefijobd . "liquidaciones WHERE ID = ".$id_liq;
	$res_sql04=mysql_query($sql04);								
	while ($fila_sql04 = mysql_fetch_array($res_sql04)){
		$xfolio_liq = $fila_sql04['XFolio'];	
		$liq_comisionoperador= $fila_sql04['ComisionOperador'];	
		$liq_sueldodiario= $fila_sql04['SueldoDiario'];	
		$liq_dias_laborados= $fila_sql04['DiasLaborados'];	
		$liq_xFleteMB= $fila_sql04['xFleteMB'];	
	}
	
	//Buscar Remisiones de Viaje
	$sql05="SELECT * FROM " . $prefijobd . "remisiones WHERE FolioSubViajes_RID = ".$id_viaje;
	//echo $sql05;
	$res_sql05=mysql_query($sql05);								
	while ($fila_sql05 = mysql_fetch_array($res_sql05)){
		$rem_modalidad = $fila_sql05['Modalidad'];
		$rem_xfolio = $fila_sql05['XFolio'];
		$rem_ruta_id = $fila_sql05['Ruta_RID'];
		$rem_ruta_id_ren = 'Rutas';
		//Buscar Ruta
		$sql06="SELECT * FROM " . $prefijobd . "rutas WHERE ID = ".$rem_ruta_id;
		$res_sql06=mysql_query($sql06);								
		while ($fila_sql06 = mysql_fetch_array($res_sql06)){
			$rem_ruta = $fila_sql06['Ruta'];
			$rem_ruta_kms = $fila_sql06['Kms'];
			//$rem_ruta_comida = $fila_sql06['Comidas'];
			//$rem_ruta_egreso3ro = $fila_sql06['Egreso3ro'];
			/*if ($rem_ruta_egreso3ro > 0){
				//echo "Variable definida!!!";
			}else{
				//echo "Variable NO definida!!!";
				$rem_ruta_egreso3ro = 0;
		    }*/
		}
		$rem_seco = $fila_sql05['Seco'];
		$rem_id = $fila_sql05['ID'];
		$rem_cliente_id = $fila_sql05['CargoACliente_RID'];
		//Buscar Cliente
		$sql07="SELECT * FROM " . $prefijobd . "clientes WHERE ID = ".$rem_cliente_id;
		$res_sql07=mysql_query($sql07);								
		while ($fila_sql07 = mysql_fetch_array($res_sql07)){
			$rem_cliente = $fila_sql07['RazonSocial'];
		}
		$rem_kmsrecorridos = $fila_sql05['KmsRecorridos'];
		$rem_demoras = $fila_sql05['yDemoras'];
		$rem_remisionoperador = $fila_sql05['RemisionOperador'];
		$rem_creado = $fila_sql05['Creado'];
		$rem_flete = $fila_sql05['yFlete'];
		$rem_fletedolares_pesos = $fila_sql05['FleteDolaresAPeso'];
		$rem_moneda = $fila_sql05['Moneda'];
		$rem_seguro = $fila_sql05['ySeguro'];
		$rem_carga = $fila_sql05['yCarga'];
		$rem_descarga = $fila_sql05['yDescarga'];
		$rem_recoleccion = $fila_sql05['yRecoleccion'];
		$rem_repartos = $fila_sql05['yRepartos'];
		$rem_autopistas = $fila_sql05['yAutopistas'];
		$rem_otros = $fila_sql05['yOtros'];
		$rem_subtotal = $fila_sql05['zSubtotal'];
		$rem_impuesto = $fila_sql05['zImpuesto'];
		$rem_retenido = $fila_sql05['zRetenido'];
		$rem_total = $fila_sql05['zTotal'];
		$rem_peso = $fila_sql05['xPesoTotal'];
		$rem_mt3 = $fila_sql05['xMts3'];
		$rem_fleteMB = $fila_sql05['yFleteMB'];
		$rem_seguroMB = $fila_sql05['ySeguroMB'];
		$rem_cargaMB = $fila_sql05['yCargaMB'];
		$rem_descargaMB = $fila_sql05['yDescargaMB'];
		$rem_recoleccionMB = $fila_sql05['yRecoleccionMB'];
		$rem_repartosMB = $fila_sql05['yRepartosMB'];
		$rem_demorasMB = $fila_sql05['yDemorasMB'];
		$rem_autopistasMB = $fila_sql05['yAutopistasMB'];
		$rem_otrosMB = $fila_sql05['yOtrosMB'];
		$rem_subtotalMB = $fila_sql05['zSubtotalMB'];
		$rem_impuestoMB = $fila_sql05['zImpuestoMB'];
		$rem_retenidoMB = $fila_sql05['zRetenidoMB'];
		$rem_totalMB = $fila_sql05['zTotalMB'];
		$rem_comision_descuentos = $fila_sql05['zComisionDescuentos'];
		$rem_otros_descuentos = $fila_sql05['zOtrosDescuentos'];
		$rem_liq_ren = 'Remisiones';
		$rem_foliosub_ren = 'Liquidaciones';
		$rem_foliosub_rma = 'FolioSub';
		
		$rem_comisionoperador = 0;
		
		
		
		//Crear registro en LiquidacionesSub
		//Obtengo el siguiente BASIDGEN
			$qry_basidgen = "SELECT MAX_ID from bas_idgen";
			$result_qry_basidgen = mysql_query($qry_basidgen, $cnx_cfdi);
					
			if (!$result_qry_basidgen){
				//No pude obtener el siguiente basidgen
				$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
				echo "Error4";
			}
			else {
								
				//Le sumo uno y hago el update
				$rowbasidgen = mysql_fetch_row($result_qry_basidgen);
								
				$basidgen = $rowbasidgen[0]+1;
								
				//echo "<br>Basidgen" . $basidgen . "<br>";
								
				$upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
				$result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
								
				if ($result_upd_basidgen) {
					//Se hizo el update sin problemas
					$endtrans = mysql_query("COMMIT", $cnx_cfdi);
				}
						
			}
					
			$newid = $basidgen;
			
		$sql_liqsub = "INSERT INTO " . $prefijobd . "liquidacionessub (ID, BASTIMESTAMP, SeguroMB, Recoleccion, Modalidad, SubtotalMB, Retenido, zFleteMXN, Moneda, Ruta, TotalMB, ImpuestoMB, CargaMB, KmsCargado, Rutas_REN, Rutas_RID, OtrosDescuentos, OtrosMB, KmsRecorridos, Total, Otros, FleteMB, RemisionLiq_REN, RemisionLiq_RID, Peso, DescargaMB, FolioSub_REN, FolioSub_RID, FolioSub_RMA, Flete, ComisionDescuentos, Seguro, RepartosMB, Carga, Mt3, Autopistas, RecoleccionMB, RetenidoMB, AutopistasMB, Descarga, Repartos, Impuesto, Seco, DemorasMB, Demoras, FechaInicio, Subtotal, Cliente, ComisionOperador) VALUES 
			(". $newid .", 
			'".$fecha."', 
			".$rem_seguroMB.",
			".$rem_recoleccion.",
			'".$rem_modalidad."',
			".$rem_subtotalMB.",
			".$rem_retenido.",
			".$rem_fletedolares_pesos.",
			'".$rem_moneda."',
			'".$rem_ruta."',
			".$rem_totalMB.",
			".$rem_impuestoMB.",
			".$rem_cargaMB.",
			".$rem_ruta_kms.",
			'Rutas',
			".$rem_ruta_id.",
			".$rem_otros_descuentos.",
			".$rem_otrosMB.",
			".$rem_kmsrecorridos.",
			".$rem_total.",
			".$rem_otros.",
			".$rem_fleteMB.",
			'Remisiones',
			".$rem_id.",
			".$rem_peso.",
			".$rem_descargaMB.",
			'Liquidaciones',
			".$id_liq.",
			'FolioSub',
			".$rem_flete.",
			".$rem_comision_descuentos.",
			".$rem_seguro.",
			".$rem_repartosMB.",
			".$rem_carga.",
			".$rem_mt3.",
			".$rem_autopistas.",
			".$rem_recoleccionMB.",
			".$rem_retenidoMB.",
			".$rem_autopistasMB.",
			".$rem_descarga.",
			".$rem_repartos.",
			".$rem_impuesto.",
			".$rem_seco.",
			".$rem_demorasMB.",
			".$rem_demoras.",
			'".$rem_creado."',
			".$rem_subtotal.",
			'".$rem_cliente."',
			".$rem_comisionoperador."
			)";
			
			//echo $sql_liqsub;
			
			mysql_query($sql_liqsub,$cnx_cfdi);
		
		
		//Actualizar campo Liquidacion de Remisiones con XFolio de la Liquidación
		mysql_query("UPDATE " . $prefijobd . "remisiones SET 
			Liquidacion = '".$xfolio_liq."'
			WHERE ID = ".$rem_id."");
			
		//Ejecutar procesos correspondientes a LiqSub
		
		//Comision Porcentaje
		if(($liq_sueldodiario == 0) && ($liq_comisionoperador > 0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$v_comision_operador = $rem_flete*($liq_comisionoperador/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador."
			WHERE ID = ".$newid."");
		}
		
		//Comision Porcentaje Ruta
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($rem_moneda=='PESOS') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql08="SELECT MAX(Porcentaje) as porcentaje FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			//echo $sql08;
			$res_sql08=mysql_query($sql08);								
			while ($fila_sql08 = mysql_fetch_array($res_sql08)){
				$v_porcentaje = $fila_sql08['porcentaje'];
			}
			$v_comision_operador2 = ($rem_flete-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentaje/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador2."
			WHERE ID = ".$newid."");
		}
		
		//Comision Porcentaje Ruta SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($rem_moneda=='PESOS') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql09="SELECT MAX(PorcentajeSeco) as porcentajeseco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			$res_sql09=mysql_query($sql09);								
			while ($fila_sql09 = mysql_fetch_array($res_sql09)){
				$v_porcentajeseco = $fila_sql09['porcentajeseco'];
			}
			$v_comision_operador3 = ($rem_flete-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentajeseco/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador3."
			WHERE ID = ".$newid."");
		}
		
		//Porcentaje Full
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($rem_moneda=='PESOS') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql10="SELECT MAX(Porcentaje) as porcentaje FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql10=mysql_query($sql10);								
			while ($fila_sql10 = mysql_fetch_array($res_sql10)){
				$v_porcentaje = $fila_sql10['porcentaje'];
			}
			$v_comision_operador4 = ($rem_flete-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentaje/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador4."
			WHERE ID = ".$newid."");
		}
		
		//Porcentaje Full SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($rem_moneda=='PESOS') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql11="SELECT MAX(PorcentajeSeco) as porcentajeseco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_porcentajeseco = $fila_sql11['porcentajeseco'];
			}
			$v_comision_operador5 = ($rem_flete-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentajeseco/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador5."
			WHERE ID = ".$newid."");
		}
		
		//Comision Porcentaje Sencillo Dolares
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($rem_moneda=='DOLARES') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Porcentaje) as porcentaje FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_porcentaje = $fila_sql11['porcentaje'];
			}
			$v_comision_operador6 = ($rem_fletedolares_pesos-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentaje/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador6."
			WHERE ID = ".$newid."");
		}
		
		//Comision Porcentaje Full Dolares
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($rem_moneda=='DOLARES') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Porcentaje) as porcentaje FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_porcentaje = $fila_sql11['porcentaje'];
			}
			$v_comision_operador7 = ($rem_fletedolares_pesos-$rem_comision_descuentos-$rem_otros_descuentos)*($v_porcentaje/100);
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador7."
			WHERE ID = ".$newid."");
		}
		
		//Comision Kms Sencillo
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Kms) as Kms FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			//echo $sql11;
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_kms = $fila_sql11['Kms'];
			}
			$v_comision_operador8 = $rem_ruta_kms*$v_kms;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador8."
			WHERE ID = ".$newid."");
		}
		
		//Comision Kms Sencillo SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql11="SELECT MAX(KmsSeco) as KmsSeco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_kmsseco = $fila_sql11['KmsSeco'];
			}
			$v_comision_operador9 = $rem_ruta_kms*$v_kmsseco;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador9."
			WHERE ID = ".$newid."");
		}
		
		//Comision Kms Full
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Kms) as Kms FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_kms = $fila_sql11['Kms'];
			}
			$v_comision_operador10 = $rem_ruta_kms*$v_kms;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador10."
			WHERE ID = ".$newid."");
		}
		
		//Comision Kms Full SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql11="SELECT MAX(KmsSeco) as KmsSeco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_kmsseco = $fila_sql11['KmsSeco'];
			}
			$v_comision_operador11 = $rem_ruta_kms*$v_kmsseco;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador11."
			WHERE ID = ".$newid."");
		}
		
		//Comision Fijo Sencillo
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Fijo) as fijo FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			//echo $sql11;
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_fijo = $fila_sql11['fijo'];
			}
			$v_comision_operador12 = $v_fijo;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador12."
			WHERE ID = ".$newid."");
		}
		
		//Comision Fijo Sencillo SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Sencillo') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql11="SELECT MAX(FijoSeco) as FijoSeco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Sencillo'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_fijoseco = $fila_sql11['FijoSeco'];
			}
			$v_comision_operador13 = $v_fijoseco;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador13."
			WHERE ID = ".$newid."");
		}
		
		//Comision Fijo Full
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($liq_sueldodiario==0) && (($rem_seco == 0) || ($rem_seco == ''))){
			$sql11="SELECT MAX(Fijo) as fijo FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_fijo = $fila_sql11['fijo'];
			}
			$v_comision_operador14 = $v_fijo;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador14."
			WHERE ID = ".$newid."");
		}
		
		//Comision Fijo Full SECO
		if(($rem_comisionoperador==0) && ($rem_modalidad=='Full') && ($liq_sueldodiario==0) && ($rem_seco == 1)){
			$sql11="SELECT MAX(FijoSeco) as FijoSeco FROM " . $prefijobd . "RutasComisiones WHERE RutaComisiones_RID = ".$rem_ruta_id." AND Unidad = 'Full'";
			$res_sql11=mysql_query($sql11);								
			while ($fila_sql11 = mysql_fetch_array($res_sql11)){
				$v_fijoseco = $fila_sql11['FijoSeco'];
			}
			$v_comision_operador15 = $v_fijoseco;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			ComisionOperador = ".$v_comision_operador15."
			WHERE ID = ".$newid."");
		}
		
		//Comision Sueldo Diario
		if($liq_sueldodiario > 0){
			$v_comision_sueldo_diario = $liq_sueldodiario*$liq_dias_laborados;
			mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			ComisionSueldoDiario = ".$v_comision_sueldo_diario."
			WHERE ID = ".$id_liq."");
		}
		
		//Comision Sueldo Diario
		if($liq_sueldodiario > 0){
			$v_comision_sueldo_diario = $liq_sueldodiario*$liq_dias_laborados;
			mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			ComisionSueldoDiario = ".$v_comision_sueldo_diario."
			WHERE ID = ".$id_liq."");
		}
		
		
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
		
		
		//Actualiza Total Flete en Liq
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
		TotalFlete = ".$v_flete."
		WHERE ID = ".$id_liq."");
		
		

		//Comision Porcentaje Operador
		if($liq_comisionoperador > 0){
			$v_comision_porcentaje_operador = ($liq_comisionoperador/$liq_xFleteMB)/100;
			mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			ComisionPorcentajeOperador = ".$v_comision_porcentaje_operador."
			WHERE ID = ".$id_liq."");
		}
		
		//Bono Comida
		/*if($liq_comisionoperador > 0){
			$v_bono_comida = $rem_ruta_comida *  $liq_dias_laborados;
			mysql_query("UPDATE " . $prefijobd . "liquidacionessub SET 
			BonoComida = ".$v_bono_comida."
			WHERE ID = ".$newid."");
		}*/
		
		//Actualizar campo Liq.TotalFlete = Liq.xFlete 
		mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			TotalFlete = '".$v_flete."'
			WHERE ID = ".$id_liq."");

		//Calcula Egreso3ro Porcentaje
		/*if($v_fletemb > 0){
			$v_Egreso3roPorcentaje = $v_Egreso3ro /  $v_fletemb;
			mysql_query("UPDATE " . $prefijobd . "liquidaciones SET 
			Egreso3roPorcentaje = ".$v_Egreso3roPorcentaje."
			WHERE ID = ".$id_liq."");
		}*/
		
		

	}
	
	
	
	//Crear registro de LiquidacionesBitacora en base a la Bitacora seleccionada
	//Obtengo el siguiente BASIDGEN
	$qry_basidgen = "SELECT MAX_ID from bas_idgen";
	$result_qry_basidgen = mysql_query($qry_basidgen, $cnx_cfdi);
					
	if (!$result_qry_basidgen){
		//No pude obtener el siguiente basidgen
		$endtrans = mysql_query("ROLLBACK", $cnx_cfdi);
		echo "Error4";
	}
	else {
								
		//Le sumo uno y hago el update
		$rowbasidgen = mysql_fetch_row($result_qry_basidgen);
						
		$basidgen = $rowbasidgen[0]+1;
								
		//echo "<br>Basidgen" . $basidgen . "<br>";
								
		$upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
		$result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
								
		if ($result_upd_basidgen) {
			//Se hizo el update sin problemas
			$endtrans = mysql_query("COMMIT", $cnx_cfdi);
		}
						
	}
					
	$newid = $basidgen;
			
	$sql_liqviaje = "INSERT INTO " . $prefijobd . "liquidacionesviajes (ID, BASTIMESTAMP, FolioSub_REN, FolioSub_RID, FolioSub_RMA, FolioViaje) VALUES 
			(". $newid .", 
			'".$fecha."', 
			'Liquidaciones',
			".$id_liq.",
			'FolioSubViaje',
			'".$xfolio_viaje."'
			)";
			
			//echo $sql_liqviaje;
			
			mysql_query($sql_liqviaje,$cnx_cfdi);
	
	
	
	//Actualizar campo Liquidacion en el Viaje seleccionada 
	mysql_query("UPDATE " . $prefijobd . "viajes2 SET 
			Liquidacion = '".$xfolio_liq."'
			WHERE ID = ".$id_viaje."");
	
	
	
	
	
	
			
			
		
	

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

    <title>Viajes a Liquidación</title>

    <link rel="shortcut icon" href="imagenes/logo_ts.ico">


    

</head>
<body >

<div class="container" style="margin-top: 0;">
	<div style="margin-top: 20px;left: 30%; position:fixed;">
		<h3 class="titulo_1 col-12"> <small class="text-muted">Viaje: </small><?php echo $xfolio_viaje; ?><small class="text-muted">, se anexo con exito a la Liquidacion: </small><?php echo $xfolio_liq; ?></h3>
	</div>
	<div style="margin: 0;left: 2%;">
        <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
    </div>
	<br>
	

</div>

   
</body>
</html>