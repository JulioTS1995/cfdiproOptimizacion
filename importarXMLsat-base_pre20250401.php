<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

function obtenerSiguienteID($cnx_cfdi2) {
    $begintrans = mysqli_query($cnx_cfdi2, "BEGIN");

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
    $result_qry_basidgen = mysqli_query($cnx_cfdi2, $qry_basidgen);

    if (!$result_qry_basidgen) {
        $endtrans = mysqli_query($cnx_cfdi2, "ROLLBACK");
        echo "Error4";
        return false;
    } else {
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
        $basidgen = $rowbasidgen[0] + 1;

        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi2, $upd_basidgen);

        if ($result_upd_basidgen) {
            $endtrans = mysqli_query($cnx_cfdi2, "COMMIT");
            return $basidgen;
        }
    }

    return false;
}

date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"])){//cuando se presiona el votor enviar... 

    if($_POST['cliente'] == 0){
        echo "<script>alert('Es necesario seleccionar cliente');</script>";
        ob_start();
    }
    if($_POST['oficina'] == 0){
        echo "<script>alert('Es necesario seleccionar oficina');</script>";
        ob_start();
    }

foreach ($_FILES['file']['tmp_name'] as $key => $value) {


$cont4=0;
$cont5=0;


 if($value)
 {
  $filename = explode(".", $value);//verifica que sea xml
  if($filename[1] == ('xml')||('XML')){

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($value);

    $newid =  obtenerSiguienteID($cnx_cfdi2);

    $query3 = "SELECT DiasCredito FROM ".$prefijobd."Clientes WHERE ID ='".$_POST['cliente']."';"; 
    $runsql3 = mysqli_query($cnx_cfdi2, $query3);
    if (!$runsql3) {//debug
        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $query3;
        die($mensaje);
    }
    while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
        $diasCredito = $rowsql3['DiasCredito'];
    }

    if($diasCredito == 0){
        $vence = $time;
    }else{
        $vence = date('Y-m-d', strtotime("+$diasCredito days", strtotime($time)));
    }


    $xml2 = simplexml_load_file($target_file);
    $ns = $xml2->getNamespaces(true);
    $xml2->registerXPathNamespace('c', $ns['cfdi']);
    $xml2->registerXPathNamespace('t', $ns['tfd']);
    $xml2->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/3');
    $xml2->registerXPathNamespace('cartaporte20', 'http://www.sat.gob.mx/CartaPorte20');
    $xml2->registerXPathNamespace('cartaporte30', 'http://www.sat.gob.mx/CartaPorte30');
    $xml2->registerXPathNamespace('cartaporte31', 'http://www.sat.gob.mx/CartaPorte31');
	foreach ($xml2->xpath('//cfdi:Comprobante') as $Comprobante) {
        

		    $fecha = $Comprobante['Fecha'];
            $fecha=str_replace("T"," ",$fecha);
			$folio = $Comprobante['Folio'];
            $formaPago = $Comprobante['FormaPago'];

            $query3 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE id2 ='".$formaPago."';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);
            if (!$runsql3) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query3;
                die($mensaje);
            }
            while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                $formaPago = $rowsql3['ID'];
            }

            $metodoPago=$Comprobante['MetodoPago'];

            $query4 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE id2 ='".$metodoPago."';"; 
            $runsql4 = mysqli_query($cnx_cfdi2, $query4);
            if (!$runsql4) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query4;
                die($mensaje);
            }
            while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                $metodoPago = $rowsql4['ID'];
            }

            $moneda= $Comprobante['Moneda'];
            if($moneda=="MXN"){
                $moneda="PESOS";
                $tipoCambio=0;
            }else{
                $moneda="DOLARES";
                $tipoCambio=$Comprobante['TipoCambio'];
            }

            $noCertificado=$Comprobante['NoCertificado'];
            $sellocfd=$Comprobante['Sello'];
            $selloDigital=$Comprobante['Sello'];
            $serieF=$Comprobante['Serie'];
            $subtotal=$Comprobante['SubTotal'];
            $total=$Comprobante['Total'];
        

			
	}//end Ubicacion 
	

    foreach ($xml2->xpath('//t:TimbreFiscalDigital') as $Complemento) {
        $fechaTimbrado=$Complemento['FechaTimbrado'];
        $noCertificadoSAT=$Complemento['NoCertificadoSAT'];
        $UUID=$Complemento['UUID'];
        $selloSAT=$Complemento['SelloSAT'];

        $query5 = "SELECT * FROM ".$prefijobd."Factura WHERE cfdiuuid ='".$UUID."';"; 
        $runsql5 = mysqli_query($cnx_cfdi2, $query5);
        $rowsNum=mysqli_num_rows($runsql5);
        if ($rowsNum>0) {//debug
            echo "<script>alert('Este UUID ya existe en una factura en el sistema');</script>";
			die('<h1>Intenta otra vez con otro XML</h1>');
        }

    }

    foreach ($xml2->xpath('//cfdi:Receptor') as $Receptor) {
        $usoCFDI=$Receptor['UsoCFDI'];

 
        $query5 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE id2 ='".$usoCFDI."';"; 
        $runsql5 = mysqli_query($cnx_cfdi2, $query5);
        if (!$runsql5) {//debug
            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query5;
            die($mensaje);
        }
        while ($rowsql5 = mysqli_fetch_assoc($runsql5)){
            $usoCFDI = $rowsql5['ID'];
        }
    }


    
        $sumImporteIVA = 0;
        $sumImporteRetencion = 0;
        $sumImporteISR = 0;
        $sumImporteDescuento = 0;
        $factTasaISR = 0;
        //UnidadPeso
        foreach ($xml2->xpath('//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {

            $cantidad=$Concepto['Cantidad'];
            $claveProdServ=$Concepto['ClaveProdServ'];
            $claveUnidad=$Concepto['ClaveUnidad'];
            $descripcion=$Concepto['Descripcion'];
            $importeP=$Concepto['Importe'];
            $valorUnitario=$Concepto['ValorUnitario'];
			$descuento=$Concepto['Descuento'];
            
			
			if($descuento==NULL){
				$descuento=0;
			}
            $sumImporteDescuento += $descuento;

            $importeIVA = 0;
            $tasaIVA = 0;
            $importeRetencion = 0;
            $tasaRetencion = 0;
            $importeISR = 0;
            $tasaISR = 0;
            
            $impuestos = $Concepto->children('cfdi', true)->Impuestos;

            if ($impuestos->Traslados) {
                foreach ($impuestos->Traslados->Traslado as $traslado) {
                    $importeIVA = $traslado->attributes()['Importe'];
                    $tasaIVA = $traslado->attributes()['TasaOCuota'];
                    (float)$tasaIVA=(float)$tasaIVA*100;
                    $sumImporteIVA += $importeIVA;
        
        
                }
            }
        

            if ($impuestos->Retenciones) {
                foreach ($impuestos->Retenciones->Retencion as $retencion) {
                    $impuesto = (string)$retencion->attributes()['Impuesto'];
                    $importe = (float)$retencion->attributes()['Importe'];
                    $tasa = (float)$retencion->attributes()['TasaOCuota'] * 100;

                    if ($impuesto === '002') {
                        $importeRetencion = $importe;
                        $tasaRetencion = $tasa;
                        $sumImporteRetencion += $importeRetencion;

                    } elseif ($impuesto === '001') {
                        $importeISR = $importe;
                        $tasaISR = $tasa;
                        $factTasaISR = $tasaISR;
                        $sumImporteISR += $importeISR;

                    }
                }
            }
			
			(float)$importeTotal=(float)$importeP+(float)$importeIVA-(float)$importeRetencion-(float)$importeISR-(float)$descuento;
       
        $new2id =  obtenerSiguienteID($cnx_cfdi2);
    
    
                   $queryP = "INSERT INTO ".$prefijobd."FacturaPartidas(ID,Cantidad,prodserv33,claveunidad33,Detalle,Importe,PrecioUnitario,IVAImporte,IVA,RetencionImporte,Retencion,FolioSub_REN,FolioSub_RID,ConceptoPartida,Tipo,Subtotal1,Subtotal,DescuentoImporte,CobranzaSaldo,ISRImporte,ISR, CobranzaAbonado) values
                    ('$new2id','$cantidad','$claveProdServ','$claveUnidad','$descripcion','$importeTotal','$valorUnitario','".$importeIVA."','".$tasaIVA."','".$importeRetencion."','".$tasaRetencion."','Factura','$newid','FLETE','Flete','$importeP','$importeP','$descuento','$importeTotal','$importeISR','$tasaISR', '0');";
						//die($queryP);
                $queryP=str_replace("''","NULL",$queryP);
                $queryP=str_replace("á","a",$queryP);
                $queryP=str_replace("é","e",$queryP);
                $queryP=str_replace("í","i",$queryP);
                $queryP=str_replace("ó","o",$queryP);
                $queryP=str_replace("ú","u",$queryP);
                $runP= mysqli_query($cnx_cfdi2, $queryP);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $queryP;
                    die($mensaje);
                }
    
            }//Sub-v-2

            $cartaporteAttrs = $xml2->xpath('//cfdi:Complemento//cartaporte31:CartaPorte | //cfdi:Complemento//cartaporte30:CartaPorte | //cfdi:Complemento//cartaporte20:CartaPorte');

            $TranspInternacValue = '';
            $EntradaSalidaMercValue = '';

            if (!empty($cartaporteAttrs)) {
                foreach ($cartaporteAttrs as $cartaporte) {
                    $attrs = $cartaporte->attributes();
                    if (isset($attrs['TranspInternac'])) {
                        $TranspInternacValue = trim(mb_strtolower((string) $attrs['TranspInternac'], 'UTF-8'));
                    }
                    if (isset($attrs['EntradaSalidaMerc'])) {
                        $EntradaSalidaMercValue = trim(mb_strtolower((string) $attrs['EntradaSalidaMerc'], 'UTF-8'));
                    }
                }
            }
            

            // Aplicar las condiciones
            if ($TranspInternacValue !== 'no' && $EntradaSalidaMercValue === 'entrada') {
                $TipoViaje = 'IMPORTACIÓN';
            } elseif ($TranspInternacValue !== 'no' && $EntradaSalidaMercValue === 'salida') {
                $TipoViaje = 'EXPORTACIÓN';
            } elseif ($TranspInternacValue === 'no') {
                $TipoViaje = 'NACIONAL';
            }
            //var_dump($TipoViaje);


            foreach ($xml2->xpath('//cartaporte20:Ubicacion | //cartaporte30:Ubicacion | //cartaporte31:Ubicacion') as $Ubicacion) {
                if($cont4==0){
                $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                //die($tipoUbicacion);
                //if ($tipoUbicacion=="Origen"){
                    $RFCRemitente = $Ubicacion['RFCRemitenteDestinatario'];
                    $NombreRemitente = $Ubicacion['NombreRemitenteDestinatario'];
                    $FechaCargaOrigen=$Ubicacion['FechaHoraSalidaLlegada'];
                    $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);
                    $cont4++;
                }else{
                
                
                    $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                    //die($tipoUbicacion);
                    //if ($tipoUbicacion=="Origen"){
                        $RFCDestinatario = $Ubicacion['RFCRemitenteDestinatario'];
                        $NombreDestinatario = $Ubicacion['NombreRemitenteDestinatario'];
                        $FechaCitaDestino=$Ubicacion['FechaHoraSalidaLlegada'];
                        $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
            
                    }
        
                    
            }//end Ubicacion 
            
        
            foreach ($xml2->xpath('//cartaporte20:Ubicacion//cartaporte20:Domicilio | //cartaporte30:Ubicacion//cartaporte30:Domicilio | //cartaporte31:Ubicacion//cartaporte31:Domicilio') as $Domicilio) {
                if ($cont5==0){
                $CodigoPostalRemitente=$Domicilio['CodigoPostal'];
                $PaisRemitente=$Domicilio['Pais'];
                $EstadoRemitente=$Domicilio['Estado'];
				
				if($EstadoRemitente=="DIF"){
					$EstadoRemitente="CMX";
				}
        
                $query3 = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$EstadoRemitente."';"; 
                $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                if (!$runsql3) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query3;
                    die($mensaje);
                }
                while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                    $EstadoRemitente = $rowsql3['ID'];
                }
        
                $MunicipioRemitente=$Domicilio['Municipio'];
                $query4 = "SELECT * FROM ".$prefijobd."c_municipio WHERE ClaveMunicipio ='".$MunicipioRemitente."' AND Estado_RID ='".$EstadoRemitente."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                if (!$runsql4) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query4;
                    die($mensaje);
                }
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $MunicipioRemitente2 = $rowsql4['ID'];
                }
                $ReferenciaRemitente=$Domicilio['Referencia'];
                $LocalidadRemitente=$Domicilio['Localidad'];
                $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$LocalidadRemitente."' AND Estado_RID ='".$EstadoRemitente."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $LocalidadRemitente2 = $rowsql1['ID'];
                }
                $ColoniaRemitente=$Domicilio['Colonia'];
                $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$CodigoPostalRemitente."' AND ClaveColonia ='".$ColoniaRemitente."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $ColoniaRemitente = $rowsql1['ID'];
                }
                $NumeroExteriorRemitente=$Domicilio['NumeroExterior'];
                $NumeroInteriorRemitente=$Domicilio['NumeroInterior'];
                $CalleRemitente=$Domicilio['Calle'];
        
                $cont5++;
        
                }else{
        
                    $CodigoPostalDestinatario=$Domicilio['CodigoPostal'];
                    $PaisDestinatario=$Domicilio['Pais'];
                    $EstadoDestinatario=$Domicilio['Estado'];
					
					if($EstadoDestinatario=="DIF"){
					$EstadoDestinatario="CMX";
					}
            
                    $query3 = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$EstadoDestinatario."';"; 
                    $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                    if (!$runsql3) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query3;
                        die($mensaje);
                    }
                    while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                        $EstadoDestinatario = $rowsql3['ID'];
                    }
            
                    $MunicipioDestinatario=$Domicilio['Municipio'];
                    $query4 = "SELECT * FROM ".$prefijobd."c_municipio WHERE ClaveMunicipio ='".$MunicipioDestinatario."' AND Estado_RID ='".$EstadoDestinatario."';"; 
                    $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                    if (!$runsql4) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query4;
                        die($mensaje);
                    }
                    while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                        $MunicipioDestinatario2 = $rowsql4['ID'];
                    }
                    $ReferenciaDestinatario=$Domicilio['Referencia'];
                    $LocalidadDestinatario=$Domicilio['Localidad'];
                    $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$LocalidadDestinatario."' AND Estado_RID ='".$EstadoDestinatario."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $LocalidadDestinatario2 = $rowsql1['ID'];
                    }
                    $ColoniaDestinatario=$Domicilio['Colonia'];
                    $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$CodigoPostalDestinatario."' AND ClaveColonia ='".$ColoniaDestinatario."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $ColoniaDestinatario = $rowsql1['ID'];
                    }
                    $NumeroExteriorDestinatario=$Domicilio['NumeroExterior'];
                    $NumeroInteriorDestinatario=$Domicilio['NumeroInterior'];
                    $CalleDestinatario=$Domicilio['Calle'];
                }
        
            }
            foreach ($xml2->xpath('//cartaporte20:Mercancias | //cartaporte30:Mercancias') as $Mercancias) {
                $CargoPorTasacion=$Mercancias['CargoPorTasacion'];
                $UnidadPeso=$Mercancias['UnidadPeso'];
                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$UnidadPeso."';"; 
                $runsql = mysqli_query($cnx_cfdi2, $query);
                if (!$runsql) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query;
                    die($mensaje);
                }
                while ($rowsql = mysqli_fetch_assoc($runsql)){
                    $UnidadPeso = $rowsql['ID'];
                }
            }
                foreach ($xml2->xpath('//cartaporte20:Mercancias//cartaporte20:IdentificacionVehicular | //cartaporte30:Mercancias//cartaporte30:IdentificacionVehicular | //cartaporte31:Mercancias//cartaporte31:IdentificacionVehicular') as $Vehicular) {
                    $ConfigVehicular=$Vehicular['ConfigVehicular'];
                    $query = "SELECT * FROM ".$prefijobd."c_configautotransporte WHERE ClaveNomenclatura ='".$ConfigVehicular."';"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $ConfigVehicular = $rowsql['ID'];
                    }

                    $PlacaVM=$Vehicular['PlacaVM'];

                    $query2 = "SELECT * FROM ".$prefijobd."Unidades WHERE Placas ='".$PlacaVM."';"; 
                    $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                    $rowsNum1=mysqli_num_rows($runsql2);
                    while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                        $PlacaVM = $rowsql2['ID'];
                    }
                    if ($rowsNum1==0) {//debug
                        $PlacaVM='';
                    }


                }
                $tiposFigura = $xml2->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte20:CartaPorte//cartaporte20:FiguraTransporte//cartaporte20:TiposFigura | //cfdi:Comprobante//cfdi:Complemento//cartaporte30:CartaPorte//cartaporte30:FiguraTransporte//cartaporte30:TiposFigura | //cfdi:Comprobante//cfdi:Complemento//cartaporte31:CartaPorte//cartaporte31:FiguraTransporte//cartaporte31:TiposFigura')[0];
                $rfcOperador = (string) $tiposFigura['RFCFigura'];
                $query3 = "SELECT * FROM ".$prefijobd."Operadores WHERE RFC ='".$rfcOperador."';"; 
				//DIE($query3);
                $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                $rowsNum2=mysqli_num_rows($runsql3);
                while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                    $Operador = $rowsql3['ID'];
                }
                if ($rowsNum2==0) {//debug
                    $Operador='';
                }
                //if($cont6==0){
                $Placa=$xml2->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte20:CartaPorte//cartaporte20:Mercancias//cartaporte20:Remolques//cartaporte20:Remolque//@Placa | //cfdi:Comprobante//cfdi:Complemento//cartaporte30:CartaPorte//cartaporte30:Mercancias//cartaporte30:Remolques//cartaporte30:Remolque//@Placa | //cfdi:Comprobante//cfdi:Complemento//cartaporte31:CartaPorte//cartaporte31:Mercancias//cartaporte31:Remolques//cartaporte31:Remolque//@Placa');
                $query4 = "SELECT * FROM ".$prefijobd."Unidades WHERE Placas ='".$Placa[0]."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                $rowsNum3=mysqli_num_rows($runsql4);
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $Rem1 = $rowsql4['ID'];
                }
                if ($rowsNum3==0) {//debug
                    $Rem1='';
                }
				//$cont6++;
                //}else{
            
                
                $query4 = "SELECT * FROM ".$prefijobd."Unidades WHERE Placas ='".$Placa[1]."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                $rowsNum4=mysqli_num_rows($runsql4);
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $Rem2 = $rowsql4['ID'];
                }
                if ($rowsNum4==0) {//debug
                    $Rem2='';
                }
				//}
            

                //UnidadPeso
                foreach ($xml2->xpath('//cartaporte20:Mercancias//cartaporte20:Mercancia | //cartaporte30:Mercancias//cartaporte30:Mercancia | //cartaporte31:Mercancias//cartaporte31:Mercancia') as $Merc) {
                    $ClaveUnidad=$Merc['ClaveUnidad'];
                    $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidad."';"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $ClaveUnidad = $rowsql['ID'];
                    }
        
                    $Cantidad=$Merc['Cantidad'];
                    $Cantidad=str_replace(" ","",$Cantidad);
        
                    $Descripcion=$Merc['Descripcion'];
                    $PesoEnKg=$Merc['PesoEnKg'];
                    $PesoEnKg=str_replace(" ","",$PesoEnKg);
                    $BienesTransp=$Merc['BienesTransp'];
                    $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$BienesTransp."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $BienesTransp = $rowsql1['ID'];
                    }
                    $ClaveSTCC=$Merc['ClaveSTCC'];

                    $documentacionAduanera = $Merc->xpath('.//cartaporte30:DocumentacionAduanera');
                    if (!empty($documentacionAduanera)) {
                        $numPedimento = (string) $documentacionAduanera[0]['NumPedimento'];
                        $rfcImpo = (string) $documentacionAduanera[0]['RFCImpo'];
                        $tipoDocumento = (string) $documentacionAduanera[0]['TipoDocumento'];

                        $query1 = "SELECT ID FROM ".$prefijobd."c_DocumentoAduanero WHERE Clave ='".$tipoDocumento."';"; 
                        $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                        if (mysqli_num_rows($runsql1) == 0) {//debug
                            $tipoDocumento = '';

                        }
                        while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                            $tipoDocumento = $rowsql1['ID'];
                        }

                        $identDocAduanero = (string) $documentacionAduanera[0]['IdentDocAduanero'];
                
                    }else{
                        $numPedimento = '';
                        $rfcImpo = '';
                        $tipoDocumento = '';
                        $identDocAduanero = '';

                    }
        
                    $new2id = obtenerSiguienteID($cnx_cfdi2);
            
            
                            $queryP = "INSERT INTO ".$prefijobd."FacturasSub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, BL, Pedimento, Tipo, Peso,
                            BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                            ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                            MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, UUIDComercioExt, TipoMateria_REN, TipoMateria_RID, RFCImpo, IdentDocAduanero,
                            TipoDocumento_REN, TipoDocumento_RID, NumeroPedimento) values
                            ('$new2id',0,'$time',0,0,'$item6','$time',0,'Tractosoft','Factura', '$newid', '$Cantidad', '$DescripEmbalaje', '0', '0', '0', '$PesoEnKg', '0', '0', 
                            '0', '0', '0', '0', '0', '$Descripcion', 'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$Embalaje', 
                            '$MaterialPeligroso', 'c_MaterialPeligroso', '$CveMaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria', '$UUIDComercioExt', 'c_TipoMateria', '$tipoDocumento', '$rfcImpo',
                            '$identDocAduanero', 'c_DocumentoAduanero', '$tipoDocumento', '$numPedimento');";
                            //$newquery=$queryP;
                        $queryP=str_replace("''","NULL",$queryP);
                        $queryP=str_replace("á","a",$queryP);
                        $queryP=str_replace("é","e",$queryP);
                        $queryP=str_replace("í","i",$queryP);
                        $queryP=str_replace("ó","o",$queryP);
                        $queryP=str_replace("ú","u",$queryP);
                        $runP= mysqli_query($cnx_cfdi2, $queryP);
                        if (!$runP) {//debug
                            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                            $mensaje .= 'Consulta completa: ' . $queryP;
                            die($mensaje);
                        }
            
        
                    }
    }

  
}	
                    /**/
                    $query5 = "SELECT * FROM ".$prefijobd."Oficinas WHERE ID ='".$_POST['oficina']."';"; 
                    $runsql5 = mysqli_query($cnx_cfdi2, $query5);
                    if (!$runsql5) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query5;
                        die($mensaje);
                    }
                    while ($rowsql5 = mysqli_fetch_assoc($runsql5)){
                        $serie2 = $rowsql5['Serie'];
						$ivaOficina =$rowsql5['IVA'];
						$retOficina = $rowsql5['Retencion'];
                    }
                    /**/
                    $query6 = "SELECT max(Folio) FROM ".$prefijobd."Factura WHERE Oficina_RID ='".$_POST['oficina']."';"; 
                      //die($query6);
                      $runsql6 = mysqli_query($cnx_cfdi2, $query6);
                      if (!$runsql6) {//debug
                          $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query6;
                          die($mensaje);
                      }
                      while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                          $folio2 = $rowsql6['max(Folio)'];
                      }
                    /**/
                    $folio2=$folio2+1;
                    $xfolio="".$serie2."".$folio2."";


                    $query = "SELECT * FROM ".$prefijobd."SystemSettings;"; 
                      $runsql = mysqli_query($cnx_cfdi2, $query);
                      if (!$runsql) {//debug
                          $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query;
                          die($mensaje);
                      }
                      while ($rowsql = mysqli_fetch_assoc($runsql)){
                          $fchaVigenciaCSD = $rowsql['FchaVencimientoSellos'];
                      }
                    //die($xfolio);
                    /**/
                      $subtotal1=$subtotal-$sumImporteDescuento;

				//inserta remisiones
                $queryP = "INSERT INTO ".$prefijobd."Factura(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoAFactura_REN,CargoAFactura_RID,Oficina_REN,Oficina_RID,FchaVencimientoCSD,cfdfchhra,formapago33_REN,formapago33_RID,
                metodopago33_REN,metodopago33_RID,Moneda,cfdnocertificado,cfdiselloCFD,cfdsellodigital,cfdserie,zSubtotal,zTotal,cfdifechaTimbrado,cfdinoCertificadoSAT,cfdiuuid,usocfdi33_REN,usocfdi33_RID,Creado,feMetodoPago,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,
                RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
                RemitenteReferencia,DestinatarioReferencia,CitaCarga,DestinatarioCitaCarga,Unidad_REN,Unidad_RID,ConfigAutotranporte_REN,ConfigAutotranporte_RID,cfdiselloSAT,Operador_REN,Operador_RID,TipoViaje,Remolque_REN,Remolque_RID,Remolque2_REN,Remolque2_RID,Comentarios,xRetencion,xIVA,FECreado,
                RemitenteNumRegIdTrib, DestinatarioNumRegIdTrib,RegimenAduanero_REN,RegimenAduanero_RID,IdCCP,DiasCredito, Vence, TipoCambio, FechaRevision, CobranzaSaldo, CobranzaAbonado, zImpuesto, zRetenido, ISRImporte, ISR, yDescuentos, zSubtotal1) values
                 ('$newid','$xfolio','$folio2','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$fchaVigenciaCSD','".$fecha."','TablaGeneral','".$formaPago."','TablaGeneral','".$metodoPago."',
                 '".$moneda."','".$noCertificado."','".$sellocfd."','".$selloDigital."','".$serieF."','".$subtotal."','".$total."','".$fechaTimbrado."','".$noCertificadoSAT."','".$UUID."','TablaGeneral','".$usoCFDI."','$fecha','NO IDENTIFICADO','".$NombreRemitente."','".$RFCRemitente."','".$CalleRemitente."','".$NumeroExteriorRemitente."','".$NumeroInteriorRemitente."','".$PaisRemitente."',
                 '".$CodigoPostalRemitente."','".$NombreDestinatario."','".$RFCDestinatario."','".$CalleDestinatario."','".$NumeroExteriorDestinatario."','".$NumeroInteriorDestinatario."','".$PaisDestinatario."','".$CodigoPostalDestinatario."','c_Colonia','".$ColoniaRemitente."','c_Colonia','".$ColoniaDestinatario."','Estados','".$EstadoRemitente."','Estados','".$EstadoDestinatario."',
                 'c_Localidad','".$LocalidadRemitente2."','c_Localidad','".$LocalidadDestinatario2."','c_Municipio','".$MunicipioRemitente2."','c_Municipio','".$MunicipioDestinatario2."','".$ReferenciaRemitente."','".$ReferenciaDestinatario."','".$FechaCargaOrigen2."','".$FechaCitaDestino2."','Unidades','".$PlacaVM."','c_ConfigAutotransporte','".$ConfigVehicular."','".$selloSAT."','Operadores','".$Operador."','".$TipoViaje."',
                'Unidades','".$Rem1."','Unidades','".$Rem2."','$folio','$retOficina','$ivaOficina','".$fecha."','$numRegIdTribRemitente', '$numRegIdTribDestinatario','c_RegimenAduanero','$regimenAduanero','$idCCPT', '$diasCredito', '$vence', '$tipoCambio', '$fecha', 
                '$total', '0', '$sumImporteIVA', '$sumImporteRetencion', '$sumImporteISR', '$factTasaISR', '$sumImporteDescuento','$subtotal1');";
				 //$newquery=$queryP;
                $queryP=str_replace("''","NULL",$queryP);
                $queryP=str_replace("á","a",$queryP);
                $queryP=str_replace("é","e",$queryP);
                $queryP=str_replace("í","i",$queryP);
                $queryP=str_replace("ó","o",$queryP);
                $queryP=str_replace("ú","u",$queryP);
                //die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $queryP);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $queryP;
                    die($mensaje);
                }
                echo "<script>alert('Importacion Exitosa, se creo la Factura: ".$xfolio."');</script>";//Imprime exito
   }//fin foreach File[]
   //fclose($handle);//cierra el archivo
   }



 //}


//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar XML</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar XML</h3><br />
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
                <label>Cliente:</label>
                <select class="form-control inputdefault" name="cliente" id="cliente" required aria-required="true">
                    <option value='0'>Selecciona Cliente</option>
                <?php
        
                $resSQL = "SELECT ID,RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Estatus = 'Activo' ORDER BY Cliente";
                $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  
                while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Cliente']; ?></option>
                <?php
                
                }
                ?>
                </select></div>
        
        <div class="form-group">
                <label>Oficina:</label>
                <select class="form-control inputdefault" name="oficina" id="oficina" required aria-required="true">
                    <option value='0'>Selecciona Oficina</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsFact=1 ORDER BY Oficina";
                    $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  
                    while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo "".$rowSQL['Serie']." / ".$rowSQL['Oficina']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>
        
   <div align="center">  
    <label>Selecciona el archivos XML:</label>
    <input type="file" name="file[]" required multiple/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

