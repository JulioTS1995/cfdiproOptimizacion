<?php
			//Para cada concepto obtengo los datos necesarios y creo la linea
			$qryparametrob = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrobanco;
			$resultqryparametrob = mysql_query($qryparametrob, $cnx_cfdi);
			$rowparametrob = mysql_fetch_row($resultqryparametrob);
			$parametrob = $rowparametrob[0];

			//Para cada concepto obtengo los datos necesarios y creo la linea
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametrocliente;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$parametro = $rowparametro[0];

			//================================================================
			// Obtiene parameto $parametro_iva_provCar...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_iva_provCar;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$iva_provCarCta = $rowparametro[0];
			 
			//================================================================
			// Obtiene parameto $parametro_iva_provAbo...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_iva_provAbo;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$iva_provAboCta = $rowparametro[0];

			//================================================================
			// Obtiene parameto $parametro_ivaret_provCar...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_ivaret_provCar;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$ivaret_provCarCta = $rowparametro[0];
			 
			//================================================================
			// Obtiene parameto $parametro_ivaret_provAbo...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_ivaret_provAbo;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$ivaret_provAboCta = $rowparametro[0];

			//================================================================
			// Obtiene parameto $parametro_flete_provCar_provCar...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_flete_provCar;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$flete_provCarCta = $rowparametro[0];
			 
			//================================================================
			// Obtiene parameto $parametro_flete_provCar_provAbo...
			$qryparametro = "SELECT vchar FROM " . $prefijobd . "parametro WHERE id2=" . $parametro_flete_provAbo;
			$resultqryparametro = mysql_query($qryparametro, $cnx_cfdi);
			$rowparametro = mysql_fetch_row($resultqryparametro);
			$flete_provAboCta = $rowparametro[0];
?>