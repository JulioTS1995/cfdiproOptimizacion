<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

$maxId=0;//se inicializa el ID maximo de awareim
date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{
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
        $RFCRemitente='';//remitente
        $NombreRemitente='';
        $PaisRemitente='';
        $EstadoRemitente='';
        $CodigoPostalRemitente='';
        $ColoniaRemitente='';
        $CalleRemitente='';
        $NumExtRemitente='';
        $NumIntRemitente='';
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
        /*xxxx*/
        $ID_Carga_WTMS='';//mercancias(embalaje)
        $BienesTransp='';
        $Descripcion='';
        $CantidadTipo='';
        $CantidadItem='';
        $ClaveUnidad='';
        $NumTotalMercancias='';
        $MaterialPeligroso='';
        $CveMaterialPeligroso='';
        $SubTipoRem='';
        $DescripEmbalaje='';
        $PesoEnKg='';
        $FechaCargaOrigen='';
        $FechaCitaDestino='';
        $FechaCargaOrigen2='';
        $FechaCitaDestino2='';
        $FraccionArancelaria='';
        $UUIDComercioExt='';
        $NumeroFactura='';
        $Unidad='';
        $ValorMercancia='';
        $Moneda='';
        $Unidad='';

        //Crear Nuevo ID
        $begintrans = mysql_query("BEGIN", $cnx_cfdi);
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
            //echo "<br>Basidgen" . $basidgen . "<br>"          
            $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
            $result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
                            
            if ($result_upd_basidgen) {
                //Se hizo el update sin problemas
                $endtrans = mysql_query("COMMIT", $cnx_cfdi);
            }
        }
        $newid = $basidgen;




    $xml2 = simplexml_load_file($target_file);
    //die($CalleDestinatario."\n".$EstadoDestinatario."\n".$PaisDestinatario."\n".$CodigoPostalDestinatario."\n".$CalleRemitente."\n".$EstadoRemitente."\n".$PaisRemitente."\n".$CodigoPostalRemitente."\n");
	/*foreach ($xml2 as $key => $value) {
	foreach ($value as $key => $value) {
		//die("si entra");
		    if ($key == 'ID_Carga_WTMS'){
                $ID_Carga_WTMS=$value;
            }
			if ($key == 'RFCTransportista'){
                $RFCTransportista=$value;
            }
			if ($key == 'RFCDestinatario'){
                $RFCDestinatario=$value;
            }
			if ($key == 'FechaCargaOrigen'){
                $FechaCargaOrigen2=$value;
            }
			if ($key == 'FechaCargaOrigen'){
                $FechaCargaOrigen2=$value;
            }
			//die("$FechaCargaOrigen2");
			/*die("si entra");
            $ID_Carga_WTMS=$CartaPorte->ID_Carga_WTMS;
            $RFCTransportista=$CartaPorte->RFCTransportista;
            $RFCDestinatario=$CartaPorte->RFCDestinatario;
            $FechaCargaOrigen=$CartaPorte->FechaCargaOrigen;
            $FechaCitaDestino=$CartaPorte->FechaCitaDestino;
            $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
            $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);*/

    
        /*}
	}*/
    foreach ($xml2->trasladoMercancias->Remolque->Traslados->Traslado->Mercancias->Mercancia as $Mercancia) {
//die('sientra');
            /*if ($key == 'ID_Carga_WTMS'){
                        Mercancias
                $ID_Carga_WTMS=$Mercancia->ID_Carga_WTMS;
            }*/
            
                $BienesTransp=$Mercancia->BienesTransp;
                $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$BienesTransp."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $BienesTransp = $rowsql1['ID'];
                    }
            
                $Descripcion=$Mercancia->Descripcion;
            
            /*if ($key == 'FechaCargaOrigen'){
                $FechaCargaOrigen=$value;
                $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);
            }
            if ($key == 'FechaCitaDestino'){
                $FechaCitaDestino=$value;
                $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
            }*/

                $CantidadTipo=$Mercancia->CantidadTipo;
            
                $CantidadItem=$Mercancia->CantidadItem;

                $ClaveUnidad=$Mercancia->ClaveUnidad;
                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidad."';"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $ClaveUnidad = $rowsql['ID'];
                    }


                //$NumTotalMercancias=$Mercancia->NumTotalMercancias;


                $MaterialPeligroso=$Mercancia->MaterialPeligroso;

                $PesoEnKg=$Mercancia->PesoEnKg;

                /*$CveMaterialPeligroso=$Mercancia->CveMaterialPeligroso;
                $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$CveMaterialPeligroso."';"; 
                $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                if (!$runsql2) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query2;
                    die($mensaje);
                }
                while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                    $CveMaterialPeligroso = $rowsql2['ID'];
                }*/
            /*if ($key == 'Embalaje'){
                $Embalaje=$value;
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
            }*/

                $DescripEmbalaje=$Mercancia->DescripEmbalaje;
                $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$DescripEmbalaje."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                if (!$runsql4) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query4;
                    die($mensaje);
                }
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $DescripEmbalaje = $rowsql4['ID'];

                }
                if($DescripEmbalaje=='No'){$DescripEmbalaje='';}

            /*if ($key == 'ValorMercancia'){
                $ValorMercancia=$value;
            }
            if ($key == 'Moneda'){
                $Moneda=$value;
            }*/
            /*if ($key == 'RFCDestinatario'){
                $RFCDestinatario=$value;
            }
            if ($key == 'SubTipoRem'){
                $SubTipoRem=$value;
            }*/
                    //Crear Nuevo ID
                $begintrans = mysql_query("BEGIN", $cnx_cfdi);
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
                    //echo "<br>Basidgen" . $basidgen . "<br>"          
                    $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                    $result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
                                    
                    if ($result_upd_basidgen) {
                        //Se hizo el update sin problemas
                        $endtrans = mysql_query("COMMIT", $cnx_cfdi);
                    }
                }
                $new2id = $basidgen;


                $queryP = "INSERT INTO ".$prefijobd."remisionessub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,Peso,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad,
                Descripcion,MaterialPeligrosoC, MaterialPeligroso_REN,MaterialPeligroso_RID,ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID) values
                ('$new2id',0,'$time',0,0,'".$PesoEnKg."','$time',0,'Tractosoft','Remisiones', '$newid', '$CantidadItem', '$Descripcion','$MaterialPeligroso', 'c_MaterialPeligroso', '$CveMaterialPeligroso',
                'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$DescripEmbalaje');";
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
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $queryP;
                die($mensaje);
            }
    }//finEmbalaje

        
        foreach ($xml2->trasladoMercancias->Remolque->Traslados->Traslado->DireccionDestino as $DireccionDestino) {
            $CalleDestinatario=$DireccionDestino->Calle;
            $EstadoDestinatario=$DireccionDestino->Estado;
    
            $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$EstadoDestinatario."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $EstadoDestinatario = $rowsql['ID'];
            }
    
            $PaisDestinatario=$DireccionDestino->Pais;
            $CodigoPostalDestinatario=$DireccionDestino->CodigoPostal;
    
        }
    
        foreach ($xml2->trasladoMercancias->Remolque->Traslados->Traslado->DireccionOrigen as $DireccionOrigen) {
            $CalleRemitente=$DireccionOrigen->Calle;
            $EstadoRemitente=$DireccionOrigen->Estado;
    
            $query3 = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$EstadoRemitente."';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);
            if (!$runsql3) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query3;
                die($mensaje);
            }
            while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                $EstadoRemitente = $rowsql3['ID'];
            }
    
            $PaisRemitente=$DireccionOrigen->Pais;
            $CodigoPostalRemitente=$DireccionOrigen->CodigoPostal;
    
        }
        foreach ($xml2->RFCDestinatario as $RFCDestinatario) {
            $RFCDestinatario2=$RFCDestinatario;
		}
		foreach ($xml2->ID_Carga_WTMS as $ID_Carga_WTMS) {
			$ID_Carga_WTMS2=$ID_Carga_WTMS;
		}
		foreach ($xml2->FechaCargaOrigen as $FechaCargaOrigen) {
            $FechaCargaOrigen2=$FechaCargaOrigen;
			$FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen2);
			$FechaCargaOrigen2=substr($FechaCargaOrigen2,0,19);
		}
		foreach ($xml2->FechaCitaDestino as $FechaCitaDestino) {
			$FechaCitaDestino2=$FechaCitaDestino;
            $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino2);
			$FechaCitaDestino2=substr($FechaCitaDestino2,0,19);
		}

        


    //echo $key.": ".$value."</br>";
  }
}	
                    /**/
                    $query5 = "SELECT * FROM ".$prefijobd."Oficinas WHERE ID ='".$_POST['oficina']."';"; 
                    $runsql5 = mysqli_query($cnx_cfdi2, $query5);
                    if (!$runsql5) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
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
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query6;
                        die($mensaje);
                    }
                    while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                        $folio = $rowsql6['max(Folio)'];
                    }
                    $folio=$folio+1;
                    $xfolio="".$serie."".$folio."";
                    //die($xfolio);
                    /**/


				//inserta remisiones
                $queryP = "INSERT INTO ".$prefijobd."remisiones(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,Ruta_REN,Ruta_RID,Unidad_REN,Unidad_RID,Operador_REN,Operador_RID,Creado,feMetodoPago,Moneda,
                RemisionOperador,CitaCarga,DestinatarioCitaCarga) values
                 ('$newid','$xfolio','$folio','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$NombreRemitente','$RFCRemitente','$CalleRemitente','$NumExtRemitente','$NumIntRemitente','$PaisRemitente',
                 '$CodigoPostalRemitente','$NombreDestinatario','$RFCDestinatario2','$CalleDestinatario','$NumExtDestinatario','$NumIntDestinatario','$PaisDestinatario','$CodigoPostalDestinatario','c_Colonia','$ColoniaRemitente','c_Colonia',
                 '$ColoniaDestinatario','Estados','$EstadoRemitente','Estados','$EstadoDestinatario','Rutas','".$_POST['ruta']."','Unidades','".$_POST['unidad']."','Operadores','".$_POST['operador']."','$time','NO INDENTIFICADO','PESOS','$ID_Carga_WTMS2',
                '$FechaCargaOrigen2','$FechaCitaDestino2');";
				 //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                //die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }
   
   //fclose($handle);//cierra el archivo
   echo "<script>alert('Importacion Exitosa, se creo la remision ".$xfolio."');</script>";//Imprime exito
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
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
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
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsRem = 1 ORDER BY Oficina";
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

