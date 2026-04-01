<?php
set_time_limit(3000);
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

$maxId=0;//se inicializa el ID maximo de awareim
$cont1=0;
$cont2=0;
$cont3=0;
$cont4=0;
$cont5=0;
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
  if($filename[1] == 'xml')
  {

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($_FILES["file"]["tmp_name"]);

    /*Variables a utilizar*/
        $RFCRemitenteDestinatario='';//remitente
        $NombreRemitenteDestinatario='';
        $PaisRemitente='';
        $EstadoRemitente='';
        $CodigoPostalRemitente='';
        $ColoniaRemitente='';
        $CalleRemitente='';
        $NumExtRemitente='';
        $NumIntRemitente='';
        $MunicipioRemitente='';
        $ReferenciaRemitente='';
        $LocalidadRemitente='';
        /*xxxx*/
        $RFCDestinatario='';//destinatario
        $NombreDestinatario='';
        $PaisDestinatario='';
        $EstadoDestinatario='';
        $CodigoPostalDestinatario='';
        $ColoniaDestinatario='';
        $CalleDestinatario='';
        $NumExtDestinatario='';
        $NumIntDestinatario='';
        $MunicipioDestinatario='';
        $ReferenciaDestinatario='';
        $LocalidadDestinatario='';
        /*xxxx*/
        $NumeroFactura='';//mercancias(embalaje)
        $BienesTransp='';
        $Descripcion='';
        $Cantidad='';
        $ClaveUnidad='';
        $Unidad='';
        $MaterialPeligroso='';
        $CveMaterialPeligroso='';
        $Embalaje='';
        $DescripEmbalaje='';
        $PesoEnKg='';
        $ValorMercancia='';
        $Moneda='';
        $FraccionArancelaria='';
        $UUIDComercioExt='';
        $FechaCargaOrigen='';
        $FechaCitaDestino='';
        $FechaCargaOrigen2='';
        $FechaCitaDestino2='';

        //String $Estado[];

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
	foreach ($xml2->xpath('//cartaporte:Ubicacion') as $Ubicacion) {
        if($cont4==0){
            $tipoUbicacion = $Ubicacion['TipoUbicacion'];
            //die($tipoUbicacion);
            //if ($tipoUbicacion=="Origen"){
                $RFCRemitente = $Ubicacion['RFCRemitenteDestinatario'];
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
                    $NombreDestinatario = $Ubicacion['NombreRemitenteDestinatario'];
                    $FechaCitaDestino=$Ubicacion['FechaHoraSalidaLlegada'];
                    $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
                    $IDDestinoMain=$Ubicacion['IDUbicacion'];
                    $cont4++;

                    if($RFCDestinatario == NULL){
                        $RFCDestinatario = $RFCRemitente;
                    }

                    if($NombreDestinatario == NULL){
                        $NombreDestinatario = $NombreRemitente;
                    }

            }
    
    foreach ($Ubicacion->xpath('.//cartaporte:Domicilio') as $Domicilio) {
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
		$CalleRemitente=str_replace("'"," ",$CalleRemitente);


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
			$CalleDestinatario=str_replace("'"," ",$CalleDestinatario);

            $cont5++;

        }else{//INSERTA REPARTOS

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
			$CalleRepartos=str_replace("'"," ",$CalleRepartos);
            $RFCRepartos=$Ubicacion['RFCRemitenteDestinatario'];
            $NombreReparto = $Ubicacion['NombreRemitenteDestinatario'];
            $IDDestinoReparto = $Ubicacion['IDUbicacion'];
            $FechaReparto=$Ubicacion['FechaHoraSalidaLlegada'];
            $FechaReparto=str_replace("T"," ",$FechaReparto);

            if($RFCRepartos == NULL){
                $RFCRepartos = $RFCRemitente;
            }
            if($NombreReparto == NULL){
                $NombreReparto = $NombreRemitente;
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
$idRemReparto = $basidgen;

            $queryP = "INSERT INTO {$prefijobd}RemisionesRepartos(ID,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,
            DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,DestinatarioColonia_REN,
            DestinatarioColonia_RID,DestinatarioEstado_REN,DestinatarioEstado_RID, DestinatarioLocalidad2_REN,
            DestinatarioLocalidad2_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
            FolioSub_REN, FolioSub_RID, CodigoDestino, DistanciaRecorrida, DestinatarioCitaCarga, Remitente, CodigoOrigen, RemitenteCodigoPostal, RemitenteEstado_REN, RemitenteEstado_RID, RemitentePais, RemitenteRFC, RemitenteLocalidad2_REN, RemitenteLocalidad2_RID, RemitenteMunicipio_REN, RemitenteMunicipio_RID, RemitenteColonia_REN ,RemitenteColonia_RID) VALUES ('{$idRemReparto}','{$NombreReparto}','{$RFCRepartos}',
            '{$CalleRepartos}','{$NumeroExteriorRepartos}','{$NumeroInteriorRepartos}','{$PaisRepartos}','{$CodigoPostalRepartos}',
            'c_Colonia','{$ColoniaRepartos}','Estados','{$EstadoRepartos}',
            'c_Localidad','{$LocalidadRepartos}','c_Municipio','{$MunicipioRepartos}','Remisiones','{$newid}', '{$IDDestinoReparto}', '1', '{$FechaReparto}',
            'ALMACEN FINSA','OR000033', {$CodigoPostalRemitente}, 'Estados', 8356, 'MEX', 'CCO8605231N4', 'c_Localidad', '{$LocalidadRemitente}', 'c_Municipio', {$MunicipioRemitente}, 'c_Colonia', {$ColoniaRemitente});";
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
    foreach ($xml2->xpath('//Mercancias') as $Mercancias) {
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


    

        //UnidadPeso
        foreach ($xml2->xpath('//cartaporte:Mercancias//cartaporte:Mercancia') as $Merc) {
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
            $Dimensiones=$Merc['Dimensiones'];
            $CveMaterialPeligroso=$Merc['CveMaterialPeligroso'];
            $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$CveMaterialPeligroso."';"; 
            $runsql2 = mysqli_query($cnx_cfdi2, $query2);
            if (!$runsql2) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query2;
                die($mensaje);
            }
            while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                $CveMaterialPeligroso = $rowsql2['ID'];
            }
            if($CveMaterialPeligroso != NULL){
                $item18 = 1;
            }else{
                $item18 = 0;
            }
            $Embalaje=$Merc['Embalaje'];
            $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$Embalaje."';"; 
            $runsql4 = mysqli_query($cnx_cfdi2, $query4);
            if (!$runsql4) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query4;
                die($mensaje);
            }
            while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                $Embalaje = $rowsql4['ID'];
            }

            $PesoEnKg=$Merc['PesoEnKg'];
            if($PesoEnKg>0 and $PesoEnKg<1){
                $PesoEnKg=1;
            }
            
            $DescripEmbalaje=$Merc['Unidad'];
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
            $UUIDComercioExt=$Merc['UUIDComercioExt'];
            $FraccionArancelaria=$Merc['FraccionArancelaria'];
            $query3 = "SELECT * FROM ".$prefijobd."c_FraccionArancelaria WHERE Codigo ='".$FraccionArancelaria."';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);//busca el ID de la solicitud
            if (!$runsql3) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query3;
                die($mensaje);
            }
            while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                $FraccionArancelaria = $rowsql3['ID'];
            }
            $ValorMercancia=$Merc['ValorMercancia'];
            $Moneda=$Merc['Moneda'];
            //--
            
            $Pedimento=$xml2->xpath('//cartaporte:Mercancias//cartaporte:Pedimentos//@Pedimento');
            
            $IDOrigen=$xml2->xpath('//cartaporte:Mercancias//cartaporte:CantidadTransporta//@IDOrigen');

            $IDDestino=$xml2->xpath('//cartaporte:Mercancias//cartaporte:CantidadTransporta//@IDDestino');

            $PesoBruto=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoBruto');
            $PesoNeto=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoNeto');
            $PesoTara=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoTara');

            /*foreach ($xml2->xpath('//Mercancias//DetalleMercancia')[0] as $Detalle) {
                $PesoBruto=$Detalle['PesoBruto'];
                $PesoNeto=$Detalle['PesoNeto'];
                $PesoTara=$Detalle['PesoTara'];
            }*/



            
        
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
        $new2id = $basidgen;
    
    
                    $queryP = "INSERT INTO ".$prefijobd."remisionessub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, BL, Pedimento, Tipo, Peso,
                    BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                    ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                    MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, NumeroPedimento, UUIDComercioExt,PesoNeto,PesoTara,Dimensiones,ValorMercancia,Moneda) values
                    ('$new2id',0,'$time',0,0,'$item6','$time',0,'Tractosoft','Remisiones', '$newid', '$Cantidad', '$DescripEmbalaje', '0', '', '0', '$PesoEnKg', '0', '0', 
                    '0', '0', '0', '0', '0', '$Descripcion', 'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$Embalaje', 
                    '$item18', 'c_MaterialPeligroso', '$CveMaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria', '','$UUIDComercioExt','".$PesoNeto[$cont3]."','".$PesoTara[$cont3]."','$Dimensiones','$ValorMercancia','$Moneda');";
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
    
            $cont3++;
            }
    }


    //echo $key.": ".$value."</br>";
  
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
                        $serie = $rowsql5['Serie'];
                    }
                    /**/
                    /**/
                    $query6 = "SELECT max(Folio) FROM ".$prefijobd."Remisiones WHERE Oficina_RID ='".$_POST['oficina']."';"; 
                    //die($query6);
                    $runsql6 = mysqli_query($cnx_cfdi2, $query6);
                    if (!$runsql6) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query6;
                        die($mensaje);
                    }
                    while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                        $folio = $rowsql6['max(Folio)'];
                    }
                    $folio=$folio+1;
                    $xfolio="".$serie."".$folio."";

                    $query7 = "SELECT * FROM ".$prefijobd."Rutas WHERE ID ='".$_POST['ruta']."';"; 
                    $runsql7 = mysqli_query($cnx_cfdi2, $query7);
                    if (!$runsql7) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query7;
                        die($mensaje);
                    }
                    while ($rowsql7 = mysqli_fetch_assoc($runsql7)){
                        $kmsRecorridos = $rowsql7['Kms'];
                    }

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
                $queryP = "INSERT INTO ".$prefijobd."remisiones(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,Ruta_REN,Ruta_RID,Unidad_REN,Unidad_RID,Operador_REN,Operador_RID,Creado,feMetodoPago,Moneda,
                RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,
                RemitenteReferencia,DestinatarioReferencia,CitaCarga,DestinatarioCitaCarga,RemitenteNumRegIdTrib,DestinatarioNumRegIdTrib,CuotaxTon,DistanciaRecorrida,FchaVencimientoCSD,CodigoOrigen,CodigoDestino) values
                 ('$newid','$xfolio','$folio','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','".$NombreRemitente."','".$RFCRemitente."','".$CalleRemitente."','".$NumeroExteriorRemitente."','".$NumeroInteriorRemitente."','".$PaisRemitente."',
                 '".$CodigoPostalRemitente."','".$NombreDestinatario."','".$RFCDestinatario."','".$CalleDestinatario."','".$NumeroExteriorDestinatario."','".$NumeroInteriorDestinatario."','".$PaisDestinatario."','".$CodigoPostalDestinatario."','c_Colonia','".$ColoniaRemitente."','c_Colonia',
                 '".$ColoniaDestinatario."','Estados','".$EstadoRemitente."','Estados','".$EstadoDestinatario."','Rutas','".$_POST['ruta']."','Unidades','".$_POST['unidad']."','Operadores','".$_POST['operador']."','$time','NO INDENTIFICADO','PESOS',
                'c_Localidad','".$LocalidadRemitente."','c_Localidad','".$LocalidadDestinatario."','c_Municipio','".$MunicipioRemitente."','c_Municipio','".$MunicipioDestinatario."','".$ReferenciaRemitente."','".$ReferenciaDestinatario."','".$FechaCargaOrigen2."','".$FechaCitaDestino2."'
                ,'".$RegTribRemitente."','".$RegTribDestinatario."','0','$kmsRecorridos','$fchaVigenciaCSD','$IDOrigenMain','$IDDestinoMain');";
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
                echo "<script>alert('Importacion Exitosa, se creo la remision ".$xfolio."');</script>";//Imprime exito
   }
   //fclose($handle);//cierra el archivo


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
        
        require_once('cnx_cfdi.php');
        mysql_select_db($database_cfdi, $cnx_cfdi);
                $resSQL = "SELECT ID,RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Estatus = 'Activo' ORDER BY Cliente";
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
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas ORDER BY Oficina";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
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
                    $resSQL = "SELECT ID,Ruta as Ruta FROM ".$prefijobd."Rutas ORDER BY Ruta";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Ruta']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Operador:</label>
                <select class="form-control inputdefault" name="operador" id="operador">
                    <option value='0'>Selecciona Operador</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Operador FROM ".$prefijobd."Operadores WHERE Estatus = 'Activo' ORDER BY Operador";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Operador']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Unidad:</label>
                <select class="form-control inputdefault" name="unidad" id="unidad">
                    <option value='0'>Selecciona Unidad</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Unidad FROM ".$prefijobd."Unidades WHERE Activa = 'Activa' ORDER BY Unidad";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Unidad']; ?></option>
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

