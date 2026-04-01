<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$sucursal = $_GET["sucursal"];//trae sucursal

$cont4=0;
$cont5=0;
$llevaRepartos='0';
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

 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea xml
  if($filename[1] == ('xml')||('XML')){

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($_FILES["file"]["tmp_name"]);

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
        $newid = $basidgen;




    $xml2 = simplexml_load_file($target_file);
        //UnidadPeso
        

            $TranspInternac=$xml2->xpath('//Complemento//cartaporte30:CartaPorte//@TranspInternac');
            $EntradaSalidaMerc=$xml2->xpath('//Complemento//cartaporte30:CartaPorte//@EntradaSalidaMerc');

            if(($TranspInternac[0]!='No') AND ($EntradaSalidaMerc[0]=='Entrada')){
                $TipoViaje='IMPORTACION';
            }
            if(($TranspInternac[0]!='No') AND ($EntradaSalidaMerc[0]=='Salida')){
                $TipoViaje='EXPORTACION';
            }
            if($TranspInternac[0]=='No'){
                $TipoViaje='NACIONAL';
            }

            
            foreach ($xml2->xpath('//cartaporte30:Ubicacion') as $Ubicacion) {
                if($cont4==0){
                    $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                    //die($tipoUbicacion);
                    //if ($tipoUbicacion=="Origen"){
                        $RFCRemitente = $Ubicacion['RFCRemitenteDestinatario'];
                        $RFCRemitente=str_replace(" ","",$RFCRemitente);

                        $NombreRemitente = $Ubicacion['NombreRemitenteDestinatario'];
                        $FechaCargaOrigen=$Ubicacion['FechaHoraSalidaLlegada'];
                        $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);
                        $IDOrigenMain=$Ubicacion['IDUbicacion'];
                        $cont4++;
                    }else if($cont4==1){
                    
                    
                        $tipoUbicacion = $Ubicacion['TipoUbicacion'];
                        //die($tipoUbicacion);
                        //if ($tipoUbicacion=="Origen"){
                            $RFCDestinatario = $Ubicacion['RFCRemitenteDestinatario'];
                            $RFCDestinatario=str_replace(" ","",$RFCDestinatario);

                            $NombreDestinatario = $Ubicacion['NombreRemitenteDestinatario'];
                            $FechaCitaDestino=$Ubicacion['FechaHoraSalidaLlegada'];
                            $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
                            $IDDestinoMain=$Ubicacion['IDUbicacion'];
                            $cont4++;

                    }
            
            foreach ($Ubicacion->xpath('.//cartaporte30:Domicilio') as $Domicilio) {
                if ($cont5==0){
                $CodigoPostalRemitente=$Domicilio['CodigoPostal'];
                $PaisRemitente=$Domicilio['Pais'];
                if($PaisRemitente=="DOM"){
                    $PaisRemitente="MEX";
                }
                $EstadoRemitente=$Domicilio['Estado'];
				
				if($EstadoRemitente=="DIF"){
					$EstadoRemitente="CMX";
				}elseif($EstadoRemitente=="SA"){
					$EstadoRemitente="MEX";
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
        
                $cont5++;
        
                }else if($cont5==1){
        
                    $CodigoPostalDestinatario=$Domicilio['CodigoPostal'];
                    $PaisDestinatario=$Domicilio['Pais'];
                    if($PaisDestinatario=="DOM"){
                        $PaisDestinatario="MEX";
                    }
                    $EstadoDestinatario=$Domicilio['Estado'];
					
					if($EstadoDestinatario=="DIF"){
					$EstadoDestinatario="CMX";
					}elseif($EstadoRemitente=="SA"){
                        $EstadoRemitente="MEX";
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
                    $cont5++;

                }else{//INSERTA REPARTOS
                    $llevaRepartos='1';

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
                    $RFCRepartos=str_replace(" ","",$RFCRepartos);

                    
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

                }//FIN REPARTOS
        
            }
            }
            foreach ($xml2->xpath('//cartaporte30:Mercancias') as $Mercancias) {
                $ClaveUnidadDoc=$Mercancias['UnidadPeso'];
                $LogInversa=$Mercancias['LogisticaInversaRecoleccionDevolucion'];
                if($LogInversa==NULL){
                    $LogInversa='0';
                }else{
                    $LogInversa='1';
                }
				//die($ClaveUnidadDoc);
                $query = "SELECT ID FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidadDoc."';"; 
                $runsql = mysqli_query($cnx_cfdi2, $query);
                if (!$runsql) {//debug
                    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query;
                    die($mensaje);
                }
                while ($rowsql = mysqli_fetch_assoc($runsql)){
                    $ClaveUnidadDoc = $rowsql['ID'];
                }

            }
                //UnidadPeso
                foreach ($xml2->xpath('//cartaporte30:Mercancias//cartaporte30:Mercancia') as $Merc) {
                    $ClaveUnidad=$Merc['ClaveUnidad'];
                    $DescripEmbalaje=$Merc['ClaveUnidad'];
                    $query = "SELECT ID FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidad."';"; 
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
					if($BienesTransp=='1010101'){
						$BienesTransp='01010101';
					}
                    $query1 = "SELECT ID, MaterialPeligroso FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$BienesTransp."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $BienesTransp = $rowsql1['ID'];
						$MaterialPeligrosoCveCP = $rowsql1['MaterialPeligroso'];
						
						/*if($MaterialPeligrosoCveCP==1){
							$update = "UPDATE ".$prefijobd."c_ClaveProdServCP SET MaterialPeligroso='0' WHERE ID =".$BienesTransp.";";//Query
						  //die($update);
						  $result_update = mysqli_query($cnx_cfdi2,$update);//Ejecuta Query
						  if (!$result_update) {//debug
							$mensaje  = 'Hubo un error sql [Reporta a soporte |OPC3.1] ' . mysql_error() . "\n";
							//$mensaje .= 'Consulta completa: ' . $update;
							die($mensaje);
						  }
						}*/
                    }
                    $ClaveSTCC=$Merc['ClaveSTCC'];
                

                            ///Crear Nuevo ID
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
                    
            
            
                            $queryP = "INSERT INTO ".$prefijobd."RemisionesSub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, Peso,
                            BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                            ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                            MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, UUIDComercioExt, PesoNeto, PesoTara, Moneda, ValorMercancia) values
                            ('$new2id',0,'$time',0,0,'$PesoEnKg','$time',0,'Tractosoft','Remisiones', '$newid', '$Cantidad', '$DescripEmbalaje', '$PesoEnKg', '0', '0', 
                            '0', '0', '0', '0', '0', '$Descripcion', 'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$Embalaje', 
                            null, 'c_MaterialPeligroso', '$CveMaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria','$UUIDComercioExt', '0', '0', 'MXN', '0');";
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

                        foreach ($Merc->xpath('.//cartaporte30:CantidadTransporta') as $Reparto) {
                            $CantidadRepSub=$Reparto['Cantidad'];
                            $IDOrigenRepSub=$Reparto['IDOrigen'];
                            $IDDestinoRepSub=$Reparto['IDDestino'];
                            //die('entra');
                            ///Crear Nuevo ID
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
                                $idReparto = $basidgen;
                            
                                    $queryP = "INSERT INTO ".$prefijobd."RepartoSub(ID, FolioRemisionSub_REN, FolioRemisionSub_RID, Cantidad, IDOrigen, IDDestino) values
                                    ('$idReparto','RemisionesSub', '$new2id', '$CantidadRepSub', '$IDOrigenRepSub', '$IDDestinoRepSub');";
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

  
}	
                    /**/
                    $query5 = "SELECT Serie, IVA, Retencion FROM ".$prefijobd."Oficinas WHERE ID ='".$_POST['oficina']."';"; 
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


                    $query = "SELECT FchaVencimientoSellos FROM ".$prefijobd."SystemSettings;"; 
                      $runsql = mysqli_query($cnx_cfdi2, $query);
                      if (!$runsql) {//debug
                          $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query;
                          die($mensaje);
                      }
                      while ($rowsql = mysqli_fetch_assoc($runsql)){
                          $fchaVigenciaCSD = $rowsql['FchaVencimientoSellos'];
                      }

                      $query7 = "SELECT Kms FROM ".$prefijobd."Rutas WHERE ID ='".$_POST['ruta']."';"; 
                      $runsql7 = mysqli_query($cnx_cfdi2, $query7);
                      if (!$runsql7) {//debug
                          $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query7;
                          die($mensaje);
                      }
                      while ($rowsql7 = mysqli_fetch_assoc($runsql7)){
                          $kmsRecorridos = $rowsql7['Kms'];
                      }
                    /**/

                $folioManhattan = (string)$xml2->Addenda->Transportista->Folio_Manhattan;
				//inserta remisiones
                $queryP = "INSERT INTO ".$prefijobd."Remisiones(ID,XFolio,Folio,Creado,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,FchaVencimientoCSD,cfdfchhra,formapago33_REN,formapago33_RID,
                metodopago33_REN,metodopago33_RID,Moneda,cfdnocertificado,cfdiselloCFD,cfdsellodigital,cfdserie,zSubtotal,zTotal,cfdifechaTimbrado,cfdinoCertificadoSAT,cfdiuuid,usocfdi33_REN,usocfdi33_RID,feMetodoPago,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,
                RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
                RemitenteReferencia,DestinatarioReferencia,CitaCarga,DestinatarioCitaCarga,Unidad_REN,Unidad_RID,ConfigAutotranporte_REN,ConfigAutotranporte_RID,cfdiselloSAT,Operador_REN,Operador_RID,TipoViaje,Comentarios,xRetencion,xIVA,AddCampoA,Addendas,CodigoOrigen,CodigoDestino,ClaveUnidadPeso_REN,ClaveUnidadPeso_RID,Ruta_REN,Ruta_RID,DistanciaRecorrida,ComplementoTraslado,LlevaRepartos,LogisticaInversa) values
                 ('$newid','$xfolio','$folio2','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$fchaVigenciaCSD','".$fecha."','TablaGeneral','".$formaPago."','TablaGeneral','".$metodoPago."',
                 'PESOS','".$noCertificado."','".$sellocfd."','".$selloDigital."','".$serieF."','".$subtotal."','".$total."','".$fechaTimbrado."','".$noCertificadoSAT."','".$UUID."','TablaGeneral','".$usoCFDI."','NO IDENTIFICADO','".$NombreRemitente."','".$RFCRemitente."','".$CalleRemitente."','".$NumeroExteriorRemitente."','".$NumeroInteriorRemitente."','".$PaisRemitente."',
                 '".$CodigoPostalRemitente."','".$NombreDestinatario."','".$RFCDestinatario."','".$CalleDestinatario."','".$NumeroExteriorDestinatario."','".$NumeroInteriorDestinatario."','".$PaisDestinatario."','".$CodigoPostalDestinatario."','c_Colonia','".$ColoniaRemitente."','c_Colonia','".$ColoniaDestinatario."','Estados','".$EstadoRemitente."','Estados','".$EstadoDestinatario."',
                 'c_Localidad','".$LocalidadRemitente."','c_Localidad','".$LocalidadDestinatario."','c_Municipio','".$MunicipioRemitente."','c_Municipio','".$MunicipioDestinatario."','".$ReferenciaRemitente."','".$ReferenciaDestinatario."','".$FechaCargaOrigen2."','".$FechaCitaDestino2."','Unidades','".$PlacaVM."','c_ConfigAutotransporte','".$ConfigVehicular."','".$selloSAT."','Operadores','".$Operador."','".$TipoViaje."',
                '$folio','$retOficina','$ivaOficina','".$folioManhattan."','HERDEZ','".$IDOrigenMain."','".$IDDestinoMain."','c_ClaveUnidadPeso','".$ClaveUnidadDoc."','Rutas','".$_POST['ruta']."','$kmsRecorridos','1','$llevaRepartos','$LogInversa');";
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
                echo "<script>alert('Importacion Exitosa, se creo la Remision: ".$xfolio."');</script>";//Imprime exito
   }


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
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
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

                $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsRem=1 AND Sucursal_RID = ".$sucursal." ORDER BY Oficina";
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
        <div class="form-group">
                <label>Ruta:</label>
                <select class="form-control inputdefault" name="ruta" id="ruta">
                    <option value='0'>Selecciona Ruta</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                $resSQL = "SELECT ID,Ruta as Ruta FROM ".$prefijobd."Rutas WHERE Sucursal_RID = ".$sucursal." ORDER BY Ruta";
                $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  

                while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Ruta']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>


        
   <div align="center">  
    <label>Selecciona el archivo XML:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>