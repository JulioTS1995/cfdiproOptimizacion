<?php 

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Falta Factura");
}

if (!isset($_GET['xfolio']) || empty($_GET['xfolio'])) {
    die("Falta XFolio");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

$idfactura = $_GET["id"];


$xfolio = $_GET["xfolio"];

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
$flete = 0;
$seguro = 0;
$carga = 0;
$descarga = 0;
$recoleccion = 0;
$repartos = 0;
$autopistas = 0;
$demoras = 0;
$otros = 0;
$sum_cobro_cliente_vl = 0;

//Buscar datos de Facturas
$resSQL1 = "SELECT * FROM " . $prefijobd . "factura WHERE ID = ".$idfactura;
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	while ($rowSQL1 = mysql_fetch_assoc($runSQL1)){ 
		$sum_cobro_cliente_vl = $rowSQL1['yFleteViajesLocales'];
	}

//Buscar datos de Notas de Venta de anexadas a la Factura ------------------------------
	$resSQL00 = "SELECT * FROM " . $prefijobd . "notasventa WHERE FolioSubfactura_RID = ".$idfactura;
	$runSQL00 = mysql_query($resSQL00, $cnx_cfdi);
	while ($rowSQL00 = mysql_fetch_assoc($runSQL00)){ 
		$id_notaventa = $rowSQL00['ID'];
		//echo '<br>';
		//echo 'ID NotaVenta: '.$id_notaventa;
		
		//Buscar Notas de Venta Sub-----------------------------------------------------
		$resSQL01 = "SELECT * FROM " . $prefijobd . "notasventa_ref WHERE ID = ".$id_notaventa;
		$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
		while ($rowSQL01 = mysql_fetch_assoc($runSQL01)){ 
			$id_notaventasub = $rowSQL01['RID'];
			//echo '<br>';
			//echo 'ID NotaVentaSub: '.$id_notaventasub;
			//-----------------------------------------------------------------------
			$resSQL02 = "SELECT * FROM " . $prefijobd . "notasventasub WHERE ID = ".$id_notaventasub;
			$runSQL02 = mysql_query($resSQL02, $cnx_cfdi);
			while ($rowSQL02 = mysql_fetch_assoc($runSQL02)){ 
				$subtotal1 = $rowSQL02['Subtotal1'];
				$id_concepto = $rowSQL02['Concepto1_RID'];
				//echo '<br>';
				//echo 'Importe: '.$subtotal1;
				//echo '<br>';
				//echo 'ID Concepto: '.$id_concepto;
				//Ubicar Tipo de Concepto para agrupación
				$resSQL03 = "SELECT * FROM " . $prefijobd . "conceptos WHERE ID = ".$id_concepto;
				$runSQL03 = mysql_query($resSQL03, $cnx_cfdi);
				while ($rowSQL03 = mysql_fetch_assoc($runSQL03)){ 
					$tipo = $rowSQL03['Tipo'];
					//echo '<br>';
					//echo 'Tipo: '.$tipo;
				}
				
				if($tipo == 'Flete'){
					$flete = $flete + $subtotal1;
				}
				if($tipo == 'Seguro'){
					$seguro = $seguro + $subtotal1;
				}
				if($tipo == 'Carga'){
					$carga = $carga + $subtotal1;
				}
				if($tipo == 'Descarga'){
					$descarga = $descarga + $subtotal1;
				}
				if($tipo == 'Recoleccion'){
					$recoleccion = $recoleccion + $subtotal1;
				}
				if($tipo == 'Repartos'){
					$repartos = $repartos + $subtotal1;
				}
				if($tipo == 'Autopistas'){
					$autopistas = $autopistas + $subtotal1;
				}
				if($tipo == 'Demoras'){
					$demoras = $demoras + $subtotal1;
				}
				if($tipo == 'Otros'){
					$otros = $otros + $subtotal1;
				}
				
				
			}
			
		}  
		
	} 
	//Suma Cobro a Cliente de Viajes Locales a concepto Flete
	
	$flete = $flete + $sum_cobro_cliente_vl;
	
	//Actualizar importes de conceptos ebn Factura
	mysql_query("UPDATE " . $prefijobd . "factura SET 
		yFlete = ".$flete.",
		ySeguro = ".$seguro.",
		yCarga = ".$carga.",
		yDescarga = ".$descarga.",
		yRecoleccion = ".$recoleccion.",
		yRepartos = ".$repartos.",
		yAutopistas = ".$autopistas.",
		yDemoras = ".$demoras.",
		yOtros = ".$otros."
		WHERE ID = ".$idfactura."");
	
//http://localhost/cfdipro/actualiza_factura.php?prefijodb=solosa_&id=1583011&xfolio=PR1

echo "<h2>Importes de Conceptos en Factura ".$xfolio." actualizados correctmente </h2>";
	
?>






