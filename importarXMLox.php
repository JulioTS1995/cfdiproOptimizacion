<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

$maxId=0;//se inicializa el ID maximo de awareim

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
	
	$ns = $xml2->getNamespaces(true);
	$xml2->registerXPathNamespace('c', $ns['cartaporte']);
	//$xml2->registerXPathNamespace('t', $ns['tfd']);
	foreach ($xml2->xpath('//Ubicacion') as $ubicaciones){
		echo $ubicaciones['DistanciaRecorrida']; 
		echo "<br />"; 
		
	}
	foreach ($xml2->xpath('//cartaporte:Ubicacion//cartaporte:Origen') as $origen){
		echo $origen['IDOrigen']; 
		echo "<br />"; 
		echo $origen['RFCRemitente']; 
		echo "<br />"; 
		echo $origen['NombreRemitente']; 
		echo "<br />"; 
		echo $origen['FechaHoraSalida']; 
		echo "<br />"; 
		
	}
	foreach ($xml2->xpath('//cartaporte:Ubicacion//cartaporte:Destino') as $destino){
		echo $destino['IDDestino']; 
		echo "<br />"; 
		echo $destino['RFCDestinatario']; 
		echo "<br />"; 
		echo $destino['NomDestinatario']; 
		echo "<br />"; 
		echo $destino['FechaHoraLlegada']; 
		echo "<br />"; 
		
	}
	foreach ($xml2->xpath('//cartaporte:Ubicacion//cartaporte:Domicilio') as $domicilio){
		echo $domicilio['Calle']; 
		echo "<br />"; 
		echo $domicilio['NumeroExterior']; 
		echo "<br />"; 
		echo $domicilio['Colonia']; 
		echo "<br />"; 
		echo $domicilio['Localidad']; 
		echo "<br />"; 
		echo $domicilio['Municipio']; 
		echo "<br />"; 
		echo $domicilio['Estado']; 
		echo "<br />"; 
		echo $domicilio['Pais']; 
		echo "<br />"; 
		echo $domicilio['CodigoPostal']; 
		echo "<br />"; 
		
	}
	foreach ($xml2->xpath('//cartaporte:Mercancias') as $mercancias){
		echo $mercancias['NumTotalMercancias']; 
		echo "<br />"; 
		
	}
		foreach ($xml2->xpath('//cartaporte:Mercancias//cartaporte:Mercancia') as $mercancia){
		echo $mercancia['BienesTransp']; 
		echo "<br />"; 
		echo $mercancia['Descripcion']; 
		echo "<br />"; 
		echo $mercancia['Cantidad']; 
		echo "<br />"; 
		echo $mercancia['ClaveUnidad']; 
		echo "<br />"; 
		echo $mercancia['Unidad']; 
		echo "<br />"; 
		echo $mercancia['Dimensiones']; 
		echo "<br />"; 
		echo $mercancia['MaterialPeligroso']; 
		echo "<br />"; 
		echo $mercancia['CveMaterialPeligroso']; 
		echo "<br />"; 
		echo $mercancia['Embalaje']; 
		echo "<br />"; 
		echo $mercancia['DescripEmbalaje']; 
		echo "<br />"; 
		echo $mercancia['PesoEnKg']; 
		echo "<br />"; 
		echo $mercancia['ValorMercancia']; 
		echo "<br />"; 
		echo $mercancia['Moneda']; 
		echo "<br />"; 
		
		foreach ($xml2->xpath('//cartaporte:Mercancias//cartaporte:CantidadTransporta') as $cant){
		echo $cant['Cantidad']; 
		echo "<br />"; 
		echo $cant['IDOrigen']; 
		echo "<br />"; 
		echo $cant['IDDestino']; 
		echo "<br />"; 
		echo $cant['CvesTransporte']; 
		echo "<br />"; 
		
	}
		
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
                /*$queryP = "INSERT INTO ".$prefijobd."remisiones(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,Ruta_REN,Ruta_RID,Unidad_REN,Unidad_RID,Operador_REN,Operador_RID,Creado,feMetodoPago,Moneda) values
                 ('$newid','$xfolio','$folio','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$NombreRemitente','$RFCRemitente','$CalleRemitente','$NumExtRemitente','$NumIntRemitente','$PaisRemitente',
                 '$CodigoPostalRemitente','$NombreDestinatario','$RFCDestinatario','$CalleDestinatario','$NumExtDestinatario','$NumIntDestinatario','$PaisDestinatario','$CodigoPostalDestinatario','c_Colonia','$ColoniaRemitente','c_Colonia',
                 '$ColoniaDestinatario','Estados','$EstadoRemitente','Estados','$EstadoDestinatario','Rutas','".$_POST['ruta']."','Unidades','".$_POST['unidad']."','Operadores','".$_POST['operador']."','$time','NO INDENTIFICADO','PESOS');";
				 //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                //die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }*/
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

