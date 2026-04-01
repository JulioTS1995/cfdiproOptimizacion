<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$sucursal = $_GET["sucursal"];//trae sucursal


//$TipoViaje='';
date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME
$fecha = date('Y-m-d H:i:s');//CURRENT_TIME
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
$cont1=0;
$cont3=0;
$cont4=0;
$cont5=0;
$cont6=0;

 if($value)
 {
  $filename = explode(".", $value);//verifica que sea xml
  if($filename[1] == ('xml')||('XML')){

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($value);

    $begintrans = mysqli_query($cnx_cfdi2,"BEGIN");
    //Obtengo el siguiente BASIDGEN
    $qry_basidgen = "SELECT MAX_ID from bas_idgen";
    $result_qry_basidgen = mysqli_query($cnx_cfdi2,$qry_basidgen);
    if (!$result_qry_basidgen){
        //No pude obtener el siguiente basidgen
        $endtrans = mysqli_query($cnx_cfdi2,"ROLLBACK");
        echo "Error4";
    }
    else {			
        //Le sumo uno y hago el update
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);          
        $basidgen = $rowbasidgen[0]+1;      
        //echo "<br>Basidgen" . $basidgen . "<br>"          
        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi2,$upd_basidgen);
                        
        if ($result_upd_basidgen) {
            //Se hizo el update sin problemas
            $endtrans = mysqli_query($cnx_cfdi2,"COMMIT");
        }
    }
        $newid = $basidgen;




    $xml2 = simplexml_load_file($target_file);
    $xml2->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
    //$ns = $xml2->getNamespaces(true);
    /*$xml2->registerXPathNamespace('c', $ns['cfdi']);
    $xml2->registerXPathNamespace('t', $ns['tfd']);*/
	/*foreach ($xml2->xpath('//Comprobante') as $Comprobante) {
        

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
            }else{
                $moneda="DOLARES";
            }

            $noCertificado=$Comprobante['NoCertificado'];
            $sellocfd=$Comprobante['Sello'];
            $selloDigital=$Comprobante['Sello'];
            $serieF=$Comprobante['Serie'];
            $subtotal=$Comprobante['SubTotal'];
            $total=$Comprobante['Total'];
        

			
	}//end Ubicacion 
	*/

    /*foreach ($xml2->xpath('//t:TimbreFiscalDigital') as $Complemento) {
        $fechaTimbrado=$Complemento['FechaTimbrado'];
        $noCertificadoSAT=$Complemento['NoCertificadoSAT'];
        $UUID=$Complemento['UUID'];
        $selloSAT=$Complemento['SelloSAT'];

        $query5 = "SELECT * FROM ".$prefijobd."Remisiones WHERE cfdiuuid ='".$UUID."';"; 
        $runsql5 = mysqli_query($cnx_cfdi2, $query5);
        $rowsNum=mysqli_num_rows($runsql5);
        if ($rowsNum>0) {//debug
            echo "<script>alert('Este UUID ya existe en una remision en el sistema');</script>";
			die('<h1>Intenta otra vez con otro XML</h1>');
        }

    }*/

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


    

        //UnidadPeso
        foreach ($xml2->xpath('//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {

            $cantidad=$Concepto['Cantidad'];
            $claveProdServ=$Concepto['ClaveProdServ'];
            $claveUnidad=$Concepto['ClaveUnidad'];
            $descripcion=$Concepto['Descripcion'];
            $importe=$Concepto['Importe'];
            $valorUnitario=$Concepto['ValorUnitario'];
			$descuento=$Concepto['Descuento'];
			
			if($descuento==NULL){
				$descuento=0;
			}
            
            $importeIVA=$xml2->xpath('//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado//@Importe');
            $IVA=$xml2->xpath('//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado//@TasaOCuota');
			(float)$IVA[$cont3]=(float)$IVA[$cont3]*100;
            
            $importeRetencion=$xml2->xpath('//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion//@Importe');
            $Retencion=$xml2->xpath('//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion//@TasaOCuota');
			(float)$Retencion[$cont3]=(float)$Retencion[$cont3]*100;
			
			(float)$importeTotal=(float)$importe+(float)$importeIVA[$cont3]-(float)$importeRetencion[$cont3]-(float)$descuento;
       
            $begintrans = mysqli_query($cnx_cfdi2,"BEGIN");
            //Obtengo el siguiente BASIDGEN
            $qry_basidgen = "SELECT MAX_ID from bas_idgen";
            $result_qry_basidgen = mysqli_query($cnx_cfdi2,$qry_basidgen);
            if (!$result_qry_basidgen){
                //No pude obtener el siguiente basidgen
                $endtrans = mysqli_query($cnx_cfdi2,"ROLLBACK");
                echo "Error4";
            }
            else {			
                //Le sumo uno y hago el update
                $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);          
                $basidgen = $rowbasidgen[0]+1;      
                //echo "<br>Basidgen" . $basidgen . "<br>"          
                $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                $result_upd_basidgen = mysqli_query($cnx_cfdi2,$upd_basidgen);
                                
                if ($result_upd_basidgen) {
                    //Se hizo el update sin problemas
                    $endtrans = mysqli_query($cnx_cfdi2,"COMMIT");
                }
            }
                $new2id = $basidgen;
    
    
                   $queryP = "INSERT INTO ".$prefijobd."RemisionesPartidas(ID,Cantidad,prodserv33,claveunidad33,Detalle,Importe,PrecioUnitario,IVAImporte,IVA,RetencionImporte,Retencion,FolioSub_REN,FolioSub_RID,ConceptoPartida,Tipo,Subtotal1,Subtotal,DescuentoImporte) values
                    ('$new2id','$cantidad','$claveProdServ','$claveUnidad','$descripcion','$importeTotal','$valorUnitario','".$importeIVA[$cont3]."','".$IVA[$cont3]."','".$importeRetencion[$cont3]."','".$Retencion[$cont3]."','Remisiones','$newid','FLETE','Flete','$importe','$importe','$descuento');";
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
    
            $cont3++;
            }//Sub-v-2

            /*$TranspInternac=$xml2->xpath('//Complemento//CartaPorte//@TranspInternac');
            $EntradaSalidaMerc=$xml2->xpath('//Complemento//CartaPorte//@EntradaSalidaMerc');

            if(($TranspInternac[0]!='No') AND ($EntradaSalidaMerc[0]=='Entrada')){
                $TipoViaje='IMPORTACION';
            }
            if(($TranspInternac[0]!='No') AND ($EntradaSalidaMerc[0]=='Salida')){
                $TipoViaje='EXPORTACION';
            }
            if($TranspInternac[0]=='No'){
                $TipoViaje='NACIONAL';
            }*/


            foreach ($xml2->xpath('//cfdi:Ubicacion') as $Ubicacion) {
                if($cont4==0){
                $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                //die($tipoUbicacion);
                //if ($tipoUbicacion=="Origen"){
                    $RFCRemitente = $Ubicacion['RFCRemitenteDestinatario'];
                    $NombreRemitente = $Ubicacion['NombreRemitenteDestinatario'];
                    $FechaCargaOrigen=$Ubicacion['FechaHoraSalidaLlegada'];
                    $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);
                    $IDRemitente=$Ubicacion['IDUbicacion'];
                    $cont4++;
                }else{
                
                
                    $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                    //die($tipoUbicacion);
                    //if ($tipoUbicacion=="Origen"){
                        $RFCDestinatario = $Ubicacion['RFCRemitenteDestinatario'];
                        $NombreDestinatario = $Ubicacion['NombreRemitenteDestinatario'];
                        $FechaCitaDestino=$Ubicacion['FechaHoraSalidaLlegada'];
                        $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
                        $IDDestinatario=$Ubicacion['IDUbicacion'];

            
                    }
        
                    
            }//end Ubicacion 
            
        
            foreach ($xml2->xpath('//cfdi:Ubicacion//cfdi:Domicilio') as $Domicilio) {
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
                    $MunicipioRemitente = $rowsql4['ID'];
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
                    $LocalidadRemitente = $rowsql1['ID'];
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
                //$IDRemitente = $Domicilio['IDUbicacion'];

        
                $cont5++;
        
                }else if($cont5==1){
        
                    $CodigoPostalDestinatario=$Domicilio['CodigoPostal'];
                    $PaisDestinatario=$Domicilio['Pais'];
                    $EstadoDestinatario=$Domicilio['Estado'];
					
					/*if($EstadoDestinatario=="DIF"){
					$EstadoDestinatario="CMX";
					}*/
            
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
                        $MunicipioDestinatario = $rowsql4['ID'];
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
                        $LocalidadDestinatario = $rowsql1['ID'];
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
                    //$IDDestinatario = $Domicilio['IDUbicacion'];

                    $cont5++;
                }else{
                    //INSERTA REPARTOS

                    $CodigoPostalRepartos=$Domicilio['CodigoPostal'];
                    $PaisRepartos=$Domicilio['Pais'];
                    $EstadoRepartos=$Domicilio['Estado'];
					
					if($EstadoRepartos=="DIF"){
					    $EstadoRepartos="CMX";
					}
            
                    $query3 = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$EstadoRepartos."';"; 
                    $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                    if (!$runsql3) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query3;
                        die($mensaje);
                    }
                    while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                        $EstadoRepartos = $rowsql3['ID'];
                    }
            
                    $MunicipioRepartos=$Domicilio['Municipio'];
                    $query4 = "SELECT * FROM ".$prefijobd."c_municipio WHERE ClaveMunicipio ='".$MunicipioRepartos."' AND Estado_RID ='".$EstadoRepartos."';"; 
                    $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                    if (!$runsql4) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query4;
                        die($mensaje);
                    }
                    while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                        $MunicipioRepartos = $rowsql4['ID'];
                    }
                    $ReferenciaRepartos=$Domicilio['Referencia'];
                    $LocalidadRepartos=$Domicilio['Localidad'];
                    $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$LocalidadRepartos."' AND Estado_RID ='".$EstadoRepartos."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $LocalidadRepartos = $rowsql1['ID'];
                    }
                    $ColoniaRepartos=$Domicilio['Colonia'];
                    $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$CodigoPostalRepartos."' AND ClaveColonia ='".$ColoniaRepartos."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n"; 
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $ColoniaRepartos = $rowsql1['ID'];
                    }
                    $NumeroExteriorRepartos=$Domicilio['NumeroExterior'];
                    $NumeroInteriorRepartos=$Domicilio['NumeroInterior'];
                    $CalleRepartos=$Domicilio['Calle'];
                    $RFCRepartos=$Ubicacion['RFCRemitenteDestinatario'];
                    $NombreReparto = $Ubicacion['NombreRemitenteDestinatario'];
                    $IDDestinoReparto = $Ubicacion['IDUbicacion'];
                    $FechaReparto=$Ubicacion['FechaHoraSalidaLlegada'];
                    $FechaReparto=str_replace("T"," ",$FechaReparto);



                    //Crear Nuevo ID
                    $begintrans = mysqli_query($cnx_cfdi2,"BEGIN");
                    //Obtengo el siguiente BASIDGEN
                    $qry_basidgen = "SELECT MAX_ID from bas_idgen";
                    $result_qry_basidgen = mysqli_query($cnx_cfdi2,$qry_basidgen);
                    if (!$result_qry_basidgen){
                        //No pude obtener el siguiente basidgen
                        $endtrans = mysqli_query($cnx_cfdi2,"ROLLBACK");
                        echo "Error4";
                    }
                    else {			
                        //Le sumo uno y hago el update
                        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);          
                        $basidgen = $rowbasidgen[0]+1;      
                        //echo "<br>Basidgen" . $basidgen . "<br>"          
                        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                        $result_upd_basidgen = mysqli_query($cnx_cfdi2,$upd_basidgen);
                                        
                        if ($result_upd_basidgen) {
                            //Se hizo el update sin problemas
                            $endtrans = mysqli_query($cnx_cfdi2,"COMMIT");
                        }
                    }
                    $idRemReparto = $basidgen;

                    $queryP = "INSERT INTO ".$prefijobd."RemisionesRepartos(ID,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,
                    DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,DestinatarioColonia_REN,
                    DestinatarioColonia_RID,DestinatarioEstado_REN,DestinatarioEstado_RID, DestinatarioLocalidad2_REN,
                    DestinatarioLocalidad2_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
                    FolioSub_REN, FolioSub_RID, CodigoDestino, DistanciaRecorrida, DestinatarioCitaCarga) VALUES ('$idRemReparto','".$NombreReparto."','".$RFCRepartos."',
                    '".$CalleRepartos."','".$NumeroExteriorRepartos."','".$NumeroInteriorRepartos."','".$PaisRepartos."','".$CodigoPostalRepartos."',
                    'c_Colonia','".$ColoniaRepartos."','Estados','".$EstadoRepartos."',
                    'c_Localidad','".$LocalidadRepartos."','c_Municipio','".$MunicipioRepartos."','Remisiones','".$newid."', '".$IDDestinoReparto."', '1', '".$FechaReparto."');";
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
                        $mensaje  = 'Consulta no valida: [REPARTOS]' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $queryP;
                        die($mensaje);
                    }
                }
        
            }
            foreach ($xml2->xpath('//cfdi:Mercancias') as $Mercancias) {
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
                /*foreach ($xml2->xpath('//Mercancias//IdentificacionVehicular') as $Vehicular) {
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


                }*/
                /*$Operador=$xml2->xpath('//Comprobante//Complemento//CartaPorte//FiguraTransporte//TiposFigura//@RFCFigura');
                $query3 = "SELECT * FROM ".$prefijobd."Operadores WHERE RFC ='".$Operador[0]."';"; 
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
                $Placa=$xml2->xpath('//Comprobante//Complemento//CartaPorte//Mercancias//Remolques//Remolque//@Placa');
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
            */

                //UnidadPeso
                foreach ($xml2->xpath('//cfdi:Mercancias//cfdi:Mercancia') as $Merc) {
                    //var_dump($Merc);
                    $ClaveUnidad=$Merc['ClaveUnidad'];
                    $DescripEmbalaje=$Merc['ClaveUnidad'];
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
                    $Descripcion=str_replace("'","ft",$Descripcion);
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
        

                    $begintrans = mysqli_query($cnx_cfdi2,"BEGIN");
                    //Obtengo el siguiente BASIDGEN
                    $qry_basidgen = "SELECT MAX_ID from bas_idgen";
                    $result_qry_basidgen = mysqli_query($cnx_cfdi2,$qry_basidgen);
                    if (!$result_qry_basidgen){
                        //No pude obtener el siguiente basidgen
                        $endtrans = mysqli_query($cnx_cfdi2,"ROLLBACK");
                        echo "Error4";
                    }
                    else {			
                        //Le sumo uno y hago el update
                        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);          
                        $basidgen = $rowbasidgen[0]+1;      
                        //echo "<br>Basidgen" . $basidgen . "<br>"          
                        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                        $result_upd_basidgen = mysqli_query($cnx_cfdi2,$upd_basidgen);
                                        
                        if ($result_upd_basidgen) {
                            //Se hizo el update sin problemas
                            $endtrans = mysqli_query($cnx_cfdi2,"COMMIT");
                        }
                    }
                        $new2id = $basidgen;
            
            
                            $queryP = "INSERT INTO ".$prefijobd."RemisionesSub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, BL, Pedimento, Tipo, Peso,
                            BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                            ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                            MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, NumeroPedimento, UUIDComercioExt) values
                            ('$new2id',0,'$time',0,0,'$item6','$time',0,'Tractosoft','Remisiones', '$newid', '$Cantidad', '$DescripEmbalaje', '0', '0', '0', '$PesoEnKg', '0', '0', 
                            '0', '0', '0', '0', '0', '$Descripcion', 'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$Embalaje', 
                            '$MaterialPeligroso', 'c_MaterialPeligroso', '$CveMaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria', '0','$UUIDComercioExt');";
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
                    $query6 = "SELECT max(Folio) FROM ".$prefijobd."Remisiones WHERE Oficina_RID ='".$_POST['oficina']."';"; 
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
                        
                        
                        //inserta remisiones
                $queryP = "INSERT INTO ".$prefijobd."Remisiones(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,FchaVencimientoCSD,cfdfchhra,formapago33_REN,formapago33_RID,
                metodopago33_REN,metodopago33_RID,Moneda,cfdnocertificado,cfdiselloCFD,cfdsellodigital,cfdserie,zSubtotal,zTotal,cfdifechaTimbrado,cfdinoCertificadoSAT,cfdiuuid,usocfdi33_REN,usocfdi33_RID,Creado,feMetodoPago,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,
                RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
                RemitenteReferencia,DestinatarioReferencia,CitaCarga,DestinatarioCitaCarga,Unidad_REN,Unidad_RID,ConfigAutotranporte_REN,ConfigAutotranporte_RID,cfdiselloSAT,Operador_REN,Operador_RID,TipoViaje,uRemolqueA_REN,uRemolqueA_RID,uRemolqueB_REN,uRemolqueB_RID,Comentarios,xRetencion,xIVA, Servicio_REN, Servicio_RID,
                CodigoOrigen, CodigoDestino) values
                ('$newid','$xfolio','$folio2','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$fchaVigenciaCSD','".$fecha."','TablaGeneral','".$formaPago."','TablaGeneral','".$metodoPago."',
                '".$moneda."','".$noCertificado."','".$sellocfd."','".$selloDigital."','".$serieF."','".$subtotal."','".$total."','".$fechaTimbrado."','".$noCertificadoSAT."','".$UUID."','TablaGeneral','".$usoCFDI."','$fecha','NO IDENTIFICADO','".$NombreRemitente."','".$RFCRemitente."','".$CalleRemitente."','".$NumeroExteriorRemitente."','".$NumeroInteriorRemitente."','".$PaisRemitente."',
                '".$CodigoPostalRemitente."','".$NombreDestinatario."','".$RFCDestinatario."','".$CalleDestinatario."','".$NumeroExteriorDestinatario."','".$NumeroInteriorDestinatario."','".$PaisDestinatario."','".$CodigoPostalDestinatario."','c_Colonia','".$ColoniaRemitente."','c_Colonia','".$ColoniaDestinatario."','Estados','".$EstadoRemitente."','Estados','".$EstadoDestinatario."',
                'c_Localidad','".$LocalidadRemitente."','c_Localidad','".$LocalidadDestinatario."','c_Municipio','".$MunicipioRemitente."','c_Municipio','".$MunicipioDestinatario."','".$ReferenciaRemitente."','".$ReferenciaDestinatario."','".$FechaCargaOrigen2."','".$FechaCitaDestino2."','Unidades','".$PlacaVM."','c_ConfigAutotransporte','".$ConfigVehicular."','".$selloSAT."','Operadores','".$Operador."','".$TipoViaje."',
                'Unidades','".$Rem1."','Unidades','".$Rem2."','$folio','$retOficina','$ivaOficina', 'Servicios', '".$_POST['servicio']."', '".$IDRemitente."', '".$IDDestinatario."');";
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
                echo "<script>alert('Importacion Exitosa, se creo la Remisiones: ".$xfolio."');</script>";//Imprime exito
            }
        
          
        }	
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
                $resSQL = "SELECT ID,RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Estatus = 'Activo' AND Sucursal_RID = ".$sucursal." ORDER BY Cliente";
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
        
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsFact=1 AND Sucursal_RID = ".$sucursal." ORDER BY Oficina";
            $runSQL = mysqli_query($cnx_cfdi2,$resSQL);  
                while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo "".$rowSQL['Serie']." / ".$rowSQL['Oficina']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Servicio:</label>
                <select class="form-control inputdefault" name="servicio" id="servicio" required aria-required="true">
                    <option value='0'>Selecciona Servicio</option>
                <?php
        
                    $resSQL = "SELECT ID, Codigo, Descripcion FROM ".$prefijobd."Servicios ORDER BY Codigo";
            $runSQL = mysqli_query($cnx_cfdi2,$resSQL);  
                while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo "".$rowSQL['Codigo']." / ".$rowSQL['Descripcion']; ?></option>
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

