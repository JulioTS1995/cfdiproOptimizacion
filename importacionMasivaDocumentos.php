<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
set_time_limit(300);
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$oficinasID = array($_POST['oficinaFact'], $_POST['oficinaRem']);

$tablaRegistros=' 
                <table class="table table-hover table-responsive table-condensed" id="table">
                    <thead>
                        <tr>
                            <th align="center" style="font-size: 12px;">Folio Viaje</th>
                            <th align="center" style="font-size: 12px;">Folio Factura</th>
                        </tr>
                    </thead>
                    <tbody>';


date_default_timezone_set("America/Mexico_City");
$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{

    
    if($_FILES['file']['name'])
    {
        $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
        if($filename[1] == 'csv')
        {
            $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
            fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
            while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos, el indice representa la columna. Itera por filas
            {
                $clienteRFC = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //datos documento
                $rutaNum = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
                $codOrigen = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                $codDestino = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
                $unidadNumEco = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
                $operadorNum = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
                $remolquePlaca = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
                $cvePeso = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
                $citaCarga = mysqli_real_escape_string($cnx_cfdi2, $data[17]);
                $citaDescarga = mysqli_real_escape_string($cnx_cfdi2, $data[18]);
                $recoge = mysqli_real_escape_string($cnx_cfdi2, $data[19]);
                $entrega = mysqli_real_escape_string($cnx_cfdi2, $data[20]);
                $ticket = mysqli_real_escape_string($cnx_cfdi2, $data[21]);

                $citaCarga=str_replace("T"," ",$citaCarga);
                $citaDescarga=str_replace("T"," ",$citaDescarga);
                
                $queryCliente = "SELECT ID, DiasCredito, metodopago33_RID, formapago33_RID, usocfdi_RID FROM ".$prefijobd."Clientes WHERE RFC='$clienteRFC';"; 
                $runsqlCliente = mysqli_query($cnx_cfdi2, $queryCliente);
                if (!$runsqlCliente) {//debug
                    $mensaje  = 'Consulta no válida [queryCliente]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryCliente;
                    die($mensaje);
                }
                while ($rowsqlCliente = mysqli_fetch_assoc($runsqlCliente)){
                    $clienteID = $rowsqlCliente['ID'];
                    $diasCredito = $rowsqlCliente['DiasCredito'];
                    $metodoPago = $rowsqlCliente['metodopago33_RID'];
                    $formaPago = $rowsqlCliente['formapago33_RID'];
                    $usoCfdi = $rowsqlCliente['usocfdi_RID'];
                }

                $queryRuta = "SELECT ID, Kms, Ruta FROM ".$prefijobd."Rutas WHERE NumeroRuta='$rutaNum';"; 
                $runsqlRuta = mysqli_query($cnx_cfdi2, $queryRuta);
                if (!$runsqlRuta) {//debug
                    $mensaje  = 'Consulta no válida [queryRuta]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryRuta;
                    die($mensaje);
                }
                while ($rowsqlRuta = mysqli_fetch_assoc($runsqlRuta)){
                    $RutaID = $rowsqlRuta['ID'];
                    $kmsRecorridos = $rowsqlRuta['Kms'];
                    $ruta = $rowsqlRuta['Ruta'];
                }
                
                $idOrigen = $codOrigen;
                $idDestino = $codDestino;
                $codOrigen = substr($codOrigen, 2);//quita 2 primeros caracteres
                $codDestino = substr($codDestino, 2);



                $queryClientesDOrigen = "SELECT * FROM ".$prefijobd."ClientesDestinos WHERE IdClienteDestino='$codOrigen';"; 
                $runsqlClientesDOrigen = mysqli_query($cnx_cfdi2, $queryClientesDOrigen);
                if (!$runsqlClientesDOrigen) {//debug
                    $mensaje  = 'Consulta no válida [queryClientesDOrigen]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryClientesDOrigen;
                    die($mensaje);
                }
                while ($rowsqlClientesDOrigen = mysqli_fetch_assoc($runsqlClientesDOrigen)){
                    $rem= $rowsqlClientesDOrigen['Destinatario'];
                    $remEstado= $rowsqlClientesDOrigen['Estado_RID'];
                    $remMunicipio= $rowsqlClientesDOrigen['Municipio_RID'];
                    $remLocalidad= $rowsqlClientesDOrigen['Localidad_RID'];
                    $remColonia= $rowsqlClientesDOrigen['Colonia_RID'];
                    $remNum1= $rowsqlClientesDOrigen['NumInt'];
                    $remNum2= $rowsqlClientesDOrigen['NumExt'];
                    $remCalle= $rowsqlClientesDOrigen['Calle'];
                    $remRFC= $rowsqlClientesDOrigen['RFC'];
                    $remCP= $rowsqlClientesDOrigen['CodigoPostal'];
                    $remPais= $rowsqlClientesDOrigen['Pais'];
                    $remContacto= $rowsqlClientesDOrigen['Contacto'];
                    $remReferencia= $rowsqlClientesDOrigen['Referencia'];
                    $remTelefono= $rowsqlClientesDOrigen['Telefono'];
                }

                $queryClientesDDestino = "SELECT * FROM ".$prefijobd."ClientesDestinos WHERE IdClienteDestino='$codDestino';"; 
                $runsqlClientesDDestino = mysqli_query($cnx_cfdi2, $queryClientesDDestino);
                if (!$runsqlClientesDDestino) {//debug
                    $mensaje  = 'Consulta no válida [queryClientesDDestino]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryClientesDDestino;
                    die($mensaje);
                }
                while ($rowsqlClientesDDestino = mysqli_fetch_assoc($runsqlClientesDDestino)){
                    $des= $rowsqlClientesDDestino['Destinatario'];
                    $desEstado= $rowsqlClientesDDestino['Estado_RID'];
                    $desMunicipio= $rowsqlClientesDDestino['Municipio_RID'];
                    $desLocalidad= $rowsqlClientesDDestino['Localidad_RID'];
                    $desColonia= $rowsqlClientesDDestino['Colonia_RID'];
                    $desNum1= $rowsqlClientesDDestino['NumInt'];
                    $desNum2= $rowsqlClientesDDestino['NumExt'];
                    $desCalle= $rowsqlClientesDDestino['Calle'];
                    $desRFC= $rowsqlClientesDDestino['RFC'];
                    $desCP= $rowsqlClientesDDestino['CodigoPostal'];
                    $desPais= $rowsqlClientesDDestino['Pais'];
                    $desContacto= $rowsqlClientesDDestino['Contacto'];
                    $desReferencia= $rowsqlClientesDDestino['Referencia'];
                    $desTelefono= $rowsqlClientesDDestino['Telefono'];
                }

                $queryUnidad = "SELECT ID, Unidad FROM ".$prefijobd."Unidades WHERE Unidad='$unidadNumEco';"; 
                $runsqlUnidad = mysqli_query($cnx_cfdi2, $queryUnidad);
                if (!$runsqlUnidad) {//debug
                    $mensaje  = 'Consulta no válida [queryUnidad]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryUnidad;
                    die($mensaje);
                }
                while ($rowsqlUnidad = mysqli_fetch_assoc($runsqlUnidad)){
                    $unidadID= $rowsqlUnidad['ID'];
                }
                
                $queryOperador = "SELECT ID FROM ".$prefijobd."Operadores WHERE NumeroOperador='$operadorNum';"; 
                $runsqlOperador = mysqli_query($cnx_cfdi2, $queryOperador);
                if (!$runsqlOperador) {//debug
                    $mensaje  = 'Consulta no válida [queryOperador]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryOperador;
                    die($mensaje);
                }
                while ($rowsqlOperador = mysqli_fetch_assoc($runsqlOperador)){
                    $operadorID= $rowsqlOperador['ID'];
                }

                $queryRemolque = "SELECT ID FROM ".$prefijobd."Unidades WHERE Placas='$remolquePlaca';"; 
                $runsqlRemolque = mysqli_query($cnx_cfdi2, $queryRemolque);
                if (!$runsqlRemolque) {//debug
                    $mensaje  = 'Consulta no válida [queryRemolque]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryRemolque;
                    die($mensaje);
                }
                while ($rowsqlRemolque = mysqli_fetch_assoc($runsqlRemolque)){
                    $remolqueID= $rowsqlRemolque['ID'];
                }

                $queryCvePeso = "SELECT ID FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad='$cvePeso';"; 
                $runsqlCvePeso = mysqli_query($cnx_cfdi2, $queryCvePeso);
                if (!$runsqlCvePeso) {//debug
                    $mensaje  = 'Consulta no válida [queryCvePeso]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryCvePeso;
                    die($mensaje);
                }
                while ($rowsqlCvePeso = mysqli_fetch_assoc($runsqlCvePeso)){
                    $cvePesoID= $rowsqlCvePeso['ID'];
                }
                
                for ($i = 0; $i < 2; $i++) {//genera los 2 de [Documento, Partida y Embalaje]
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
                    if($i==1){
                        $remisionID = $basidgen;
                    }else{
                        $facturaID = $basidgen;
                    }
                
                

                $cantidadEmbalaje = mysqli_real_escape_string($cnx_cfdi2, $data[8]);//datos embalaje
                $embalaje = mysqli_real_escape_string($cnx_cfdi2, $data[9]);
                $pesoBruto = mysqli_real_escape_string($cnx_cfdi2, $data[10]);
                $cveProdServ = mysqli_real_escape_string($cnx_cfdi2, $data[11]);
                $tipoEmbalaje = mysqli_real_escape_string($cnx_cfdi2, $data[12]);
                $descripcion = mysqli_real_escape_string($cnx_cfdi2, $data[13]);

                $queryCveProdServ = "SELECT ID FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto='$cveProdServ';"; 
                $runsqlCveProdServ = mysqli_query($cnx_cfdi2, $queryCveProdServ);
                if (!$runsqlCveProdServ) {//debug
                    $mensaje  = 'Consulta no válida [queryCveProdServ]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryCveProdServ;
                    die($mensaje);
                }
                while ($rowsqlCveProdServ = mysqli_fetch_assoc($runsqlCveProdServ)){
                    $cveProdServID= $rowsqlCveProdServ['ID'];
                }

                $queryTipoEmbalaje = "SELECT ID FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion='$TipoEmbalaje';"; 
                $runsqlTipoEmbalaje = mysqli_query($cnx_cfdi2, $queryTipoEmbalaje);
                if (!$runsqlTipoEmbalaje) {//debug
                    $mensaje  = 'Consulta no válida [queryTipoEmbalaje]: ' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $queryTipoEmbalaje;
                    die($mensaje);
                }
                while ($rowsqlTipoEmbalaje = mysqli_fetch_assoc($runsqlTipoEmbalaje)){
                    $TipoEmbalajeID= $rowsqlTipoEmbalaje['ID'];
                }


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
                    if($i==1){
                        $remisionEmbalajeID = $basidgen;
                    }else{
                        $facturaEmbalajeID = $basidgen;
                    }
                


                    if($i==1){
                        $embalajeID=$remisionEmbalajeID;
                        $docID=$remisionID;
                        $tipoDoc="Remisiones";
                        $tipoDocS="Remisiones";
                    }else{
                        $embalajeID=$facturaEmbalajeID;
                        $docID=$facturaID;
                        $tipoDoc="Factura";
                        $tipoDocS="Facturas";
                    }
                    
                    $queryP = "INSERT INTO ".$prefijobd.$tipoDocS."Sub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN,FolioSub_RID,Cantidad,Embalaje,Peso,Descripcion,ClaveUnidadPeso_REN,ClaveUnidadPeso_RID,
                        ClaveProdServCP_REN,ClaveProdServCP_RID,TipoEmbalaje_REN,TipoEmbalaje_RID,MaterialPeligroso_REN,MaterialPeligroso_RID,FraccionArancelaria_REN,FraccionArancelaria_RID,
                        NumeroPedimento,UUIDComercioExt,MaterialPeligrosoC,BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra,Dimensiones,ValorMercancia,Moneda,PesoNeto,PesoTara, ImportadoM) values
                        ('$embalajeID',0,'$time','0','0','0','$time','0','Tractosoft','$tipoDoc', '$docID', '$cantidadEmbalaje', '$embalaje', '$pesoBruto', '$descripcion','c_ClaveUnidadPeso','$cvePesoID','c_ClaveProdServCP','$cveProdServID',
                        'c_TipoEmbalaje','$tipoEmbalajeID','c_MaterialPeligroso',NULL,'c_FraccionArancelaria',NULL,NULL,NULL,NULL,'0','0','0','0','0','0','0'
                        ,NULL,'0','MXN','0','0','1');";
                        //$newquery=$queryP;
                        $newquery=str_replace("''","NULL",$queryP);
                        $newquery=str_replace("' '","NULL",$newquery);
                        //die($newquery);
                        $runP= mysqli_query($cnx_cfdi2, $newquery);
                        if (!$runP) {//debug
                            $mensaje  = 'Error en consulta. Informe a soporte: [Embalaje]' . mysqli_error() . "\n";
                            //$mensaje .= 'Consulta completa: ' . $newquery;
                            echo($tablaRegistros);
                            die($mensaje);
                        }
                

                $cantidadPartida = mysqli_real_escape_string($cnx_cfdi2, $data[14]);//datos partida
                $precioUnitario = mysqli_real_escape_string($cnx_cfdi2, $data[15]);
                $detalle = mysqli_real_escape_string($cnx_cfdi2, $data[16]);
                $yFlete = $cantidadPartida*$precioUnitario;


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
                    if($i==1){
                        $remisionPartidaID = $basidgen;
                    }else{
                        $facturaPartidaID = $basidgen;
                    }

                
                    if($i==1){
                        $PartidaID=$remisionPartidaID;
                        $docID=$remisionID;
                        $tipoDoc="Remisiones";
                    }else{
                        $PartidaID=$facturaPartidaID;
                        $docID=$facturaID;
                        $tipoDoc="Factura";
                    }
                    
                    $queryP = "INSERT INTO ".$prefijobd.$tipoDoc."Partidas(ID,ConceptoPartida,FolioConceptos_REN,FolioConceptos_RID, FolioSub_REN,
                        FolioSub_RID,DescuentoImporte,IVA,Retencion,Detalle,Tipo,prodserv33,claveunidad33,Cantidad,PrecioUnitario,Excento,prodserv33dsc,ISR,ImportadoM) 
                        values
                        ('$PartidaID','FLETE','Conceptos','1','$tipoDoc','$docID','0','16','4','$detalle','Flete','78101800','E48',
                        '$cantidadPartida','$precioUnitario','0','Transporte de Carga por Carretera','0','1');";
                        //$newquery=$queryP;
                        $newquery=str_replace("''","NULL",$queryP);
                        $newquery=str_replace("' '","NULL",$newquery);
                        //die($newquery);
                        $runP= mysqli_query($cnx_cfdi2, $newquery);
                        if (!$runP) {//debug
                            $mensaje  = 'Error en consulta. Informe a soporte: [Partidas]' . mysqli_error() . "\n";
                            //$mensaje .= 'Consulta completa: ' . $newquery;
                            echo($tablaRegistros);
                            die($mensaje);
                        }
                
                

                    if($i==1){
                        $docID=$remisionID;
                        $tipoDoc="Remisiones";
                    }else{
                        $docID=$facturaID;
                        $tipoDoc="Factura";
                    }

                    /**/
                    $query6 = "SELECT max(Folio) FROM ".$prefijobd.$tipoDoc." WHERE Oficina_RID ='".$oficinasID[$i]."';"; 
                    //die($query6);
                    $runsql6 = mysqli_query($cnx_cfdi2, $query6);
                    if (!$runsql6) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysqli_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query6;
                        die($mensaje);
                    }
                    while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                        $folio = $rowsql6['max(Folio)'];
                    }
                    
                    $query = "SELECT FchaVencimientoSellos, RutaDocumentos FROM ".$prefijobd."SystemSettings;"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysqli_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $fchaVigenciaCSD = $rowsql['FchaVencimientoSellos'];
                        $rutaDocs = $rowsql['RutaDocumentos'];
                    }

                    $rutaDocs = addslashes($rutaDocs);

                    $querySerie = "SELECT Serie FROM ".$prefijobd."Oficinas WHERE ID ='".$oficinasID[$i]."';"; 
                    //die($querySerie);
                    $runsqlSerie = mysqli_query($cnx_cfdi2, $querySerie);
                    if (!$runsqlSerie) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysqli_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $querySerie;
                        die($mensaje);
                    }
                    while ($rowsqlSerie = mysqli_fetch_assoc($runsqlSerie)){
                        $serie = $rowsqlSerie['Serie'];
                    }

                    $folio=$folio+1;
                    $xfolio="".$serie."".$folio."";
                    //die($xfolio);
                    /**/

                    if($i==1){
                        $xfolioRemision = $xfolio;
                        $folioRemision = $folio;
                    }else{
                        $xfolioFactura = $xfolio;
                        $folioFactura = $folio;
                    }
                
                


                    if($i==1){
                        $docID=$remisionID;
                        $tipoDoc="Remisiones";
                        $xfolioDoc=$xfolioRemision;
                        $folioDoc=$folioRemision;
                        $cargoA='CargoACliente';
                        $SeFacturoCampo=', SeFacturoEn, KmFinal, EntradaRemolque2LitrosAproxEvidencia_rutadoc, EntradaRemolque2HorasSwithEvidencia_rutadoc, SalidaRemolque2HorasSwithEvidencia_rutadoc, EntradaRemolque2HoraEngineEvidencia_rutadoc, SalidaRemolque1LitrosAproxEvidencia_rutadoc, EntradaRemolque1LitrosAproxEvidencia_rutadoc, EntradaRemolque1HorasSwithEvidencia_rutadoc, SalidaRemolque1HorasSwithEvidencia_rutadoc, SalidaRemolque2HoraEngineEvidencia_rutadoc, SalidaRemolque2LitrosAproxEvidencia_rutadoc, EntradaRemolque1HoraEngineEvidencia_rutadoc, SalidaRemolque1HoraEngineEvidencia_rutadoc';
                        $SeFacturoValor = ",'".$xfolioFactura."','0', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs', '$rutaDocs'";
                        $campoRemolque='uRemolqueA';
                        $campoTicket='RemisionOperador';
						$ticket = "";
                    }else{
                        $docID=$facturaID;
                        $tipoDoc="Factura";
                        $xfolioDoc=$xfolioFactura;
                        $folioDoc=$folioFactura;
                        $cargoA='CargoAFactura';
                        $SeFacturoCampo=', DiasCredito';
                        $SeFacturoValor = ",'".$diasCredito."'";
                        $campoRemolque='Remolque';
                        $campoTicket='Ticket';
						$ticket = mysqli_real_escape_string($cnx_cfdi2, $data[21]);
                    }
               
                $queryP = "INSERT INTO ".$prefijobd.$tipoDoc."(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,".$cargoA."_REN,".$cargoA."_RID,Oficina_REN,Oficina_RID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,RemitenteReferencia,RemitenteContacto,RemitenteTelefono,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,Ruta_REN,Ruta_RID,Unidad_REN,Unidad_RID,".$campoRemolque."_REN,".$campoRemolque."_RID,
                Operador_REN,Operador_RID,Creado,feMetodoPago,Moneda,CitaCarga,DestinatarioCitaCarga,RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,
                RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,DestinatarioReferencia,DestinatarioContacto,DestinatarioTelefono,CodigoOrigen,CodigoDestino,DistanciaRecorrida,FchaVencimientoCSD,TipoViaje,FleteTipo, ImportadoM, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID,
                usocfdi33_REN, usocfdi33_RID, formapago33_REN, formapago33_RID, metodopago33_REN, metodopago33_RID, RemitenteSeRecogera, DestinatarioSeEntregara, $campoTicket".$SeFacturoCampo.") values
                ('$docID','$xfolioDoc','$folioDoc','$time','1','Clientes','".$clienteID."','Oficinas','".$oficinasID[$i]."','$rem','$remRFC','$remCalle','$remNum2','$remNum1','$remPais',
                '$remCP','$remReferencia','$remContacto','$remTelefono','$des','$desRFC','$desCalle','$desNum2','$desNum1','$desPais','$desCP','c_Colonia','$remColonia','c_Colonia',
                '$desColonia','Estados','$remEstado','Estados','$desEstado','Rutas','".$RutaID."','Unidades','".$unidadID."','Unidades','".$remolqueID."',
                'Operadores','".$operadorID."','$time','NO INDENTIFICADO','PESOS','".$citaCarga."','".$citaDescarga." ','c_Localidad','$remLocalidad','c_Localidad','$desLocalidad','c_Municipio','$remMunicipio','c_Municipio','$desMunicipio','$desReferencia','$desContacto','$desTelefono','$idOrigen','$idDestino',
                '$kmsRecorridos','$fchaVigenciaCSD','NACIONAL','FLETE POR COBRAR AL REGRESO','1','c_ClaveUnidadPeso','$cvePesoID','TablaGeneral','$usoCfdi','TablaGeneral','$formaPago','TablaGeneral','$metodoPago', '$recoge','$entrega','$ticket'".$SeFacturoValor.");";
                //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                $newquery=str_replace("' '","NULL",$newquery);
                //die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Error en consulta. Informe a soporte: [Documentos]' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $newquery;
                    echo($tablaRegistros);
                    die($mensaje);
                }
                
                  
				
                  
				  
                }//fin FOR(2)

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
                
                $facturaDetalleID = $basidgen;
                
                $queryP = "INSERT INTO ".$prefijobd."FacturasDetalle(ID, Unidad, Fecha, yFlete, Descripcion, Peso, Remision_REN, 
                Remision_RID, Remision_RMA, FolioSubDetalle_REN, FolioSubDetalle_RID, FolioSubDetalle_RMA, ImportadoM) 
                values
                ('$facturaDetalleID','$UnidadNumEco','".date('Y-m-d')."','$yFlete','$ruta', '$pesoBruto', 'Remisiones', '$remisionID', 
                'RemisionFactDetalle', 'Factura', '$facturaID', 'FolioSubDetalle', '1');";
                //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                $newquery=str_replace("' '","NULL",$newquery);
                //die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Error en consulta. Informe a soporte: [FacturaDetalle]' . mysqli_error() . "\n";
                    //$mensaje .= 'Consulta completa: ' . $newquery;
                    echo($tablaRegistros);
                    die($mensaje);
                }

                $tablaRegistros .= '
                <tr>
					<td align="left">'.$xfolioRemision.'</td>
					<td align="left">'.$xfolioFactura.'</td>
                </tr>';

            }//fin While ROWS

            echo "<script>alert('Registros creados correctamente');</script>";
            fclose($handle);//cierra el archivo
            $tablaRegistros .= '
                </tbody>
                </table>';

            echo($tablaRegistros);
        }
    }
}


//abajo esta el "front"
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importacion Masiva de Documentos</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
   <!-- Bootstrap links -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable -->
 </head>  
 <body>  
  <h3 align="center">Importacion Masiva de Documentos</h3><br />
  <form method="post" enctype="multipart/form-data">
  <div class="form-group">
                <label>Oficina Viajes:</label>
                <select class="form-control inputdefault" name="oficinaRem" id="oficinaRem" required aria-required="true">
                    <option value='0'>Selecciona Oficina</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID, Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsRem = 1 ORDER BY Oficina";
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
                <label>Oficina Factura:</label>
                <select class="form-control inputdefault" name="oficinaFact" id="oficinaFact" required aria-required="true">
                    <option value='0'>Selecciona Oficina</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID, Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsFact = 1 ORDER BY Oficina";
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
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" id="btnImportar" onClick="hideMe()" />
   </div>
  </form>
 </body>  
 <script>
    function hideMe(){
        $("#btnImportar").hide();

    }
    </script>
</html>

