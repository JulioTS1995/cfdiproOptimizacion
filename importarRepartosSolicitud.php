<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
set_time_limit(300);
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idRem = $_GET["ID"];//trae id remision
//$remisionID=0;
$maxId=0;//se inicializa el ID maximo de awareim
$cont=0;
date_default_timezone_set("America/Mexico_City");
$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"])){//cuando se presiona el votor enviar... 

 if($_FILES['file']['name']){
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
  if($filename[1] == 'csv'){
   $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
   fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
   while(($data = fgetcsv($handle,10000,","))!==FALSE){//empieza a leer los datos,

        $rem = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //se empiezan a leer las columnas del CSV
        $remRFC = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
        /*$citaCarga = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                    $citaCarga2 = date("d/m/Y", strtotime($citaCarga));
					$citaCarganew = date("Y-m-d", strtotime($citaCarga2));*/
        $remID = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
        $remCalle = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
        $remNum1 = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
        $remNum2 = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
		
        $remEstado = mysqli_real_escape_string($cnx_cfdi2, $data[9]);
		//$remEstado=str_replace(" ","''",$remEstado);
        $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$remEstado."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $remEstado = $rowsql['ID'];
            }
        $remLocalidad = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
		//$remLocalidad=str_replace(" ","''",$remLocalidad);

        $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$remLocalidad."' AND Estado_RID ='".$remEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remLocalidad = $rowsql1['ID'];
            }
        $remMunicipio = mysqli_real_escape_string($cnx_cfdi2, $data[8]);
		//$remMunicipio=str_replace(" ","''",$remMunicipio);
        $query1 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE ClaveMunicipio ='".$remMunicipio."' AND Estado_RID ='".$remEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remMunicipio = $rowsql1['ID'];
            }

        $remCP = mysqli_real_escape_string($cnx_cfdi2, $data[10]);
		        $remColonia = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
				//$remColonia=str_replace(" ","''",$remColonia);

        $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$remCP."' AND ClaveColonia ='".$remColonia."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remColonia = $rowsql1['ID'];
            }
        $remPais = mysqli_real_escape_string($cnx_cfdi2, $data[11]);
        $des = mysqli_real_escape_string($cnx_cfdi2, $data[12]);
        $desID = mysqli_real_escape_string($cnx_cfdi2, $data[13]);
        $desRFC = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
        /*$citaDescarga = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
            $citaDescarga2 = date("d/m/Y", strtotime($citaDescarga));
            $citaDescarganew = date("Y-m-d", strtotime($citaDescarga2));*/
        $desCalle = mysqli_real_escape_string($cnx_cfdi2, $data[15]);
        $desNum1 = mysqli_real_escape_string($cnx_cfdi2, $data[16]);
        $desNum2 = mysqli_real_escape_string($cnx_cfdi2, $data[17]);

        $desEstado = mysqli_real_escape_string($cnx_cfdi2, $data[21]);
		//$desEstado=str_replace(" ","''",$desEstado);
        $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$desEstado."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $desEstado = $rowsql['ID'];
            }
        $desLocalidad = mysqli_real_escape_string($cnx_cfdi2, $data[19]);
		//$desLocalidad=str_replace(" ","''",$desLocalidad);

        $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$desLocalidad."' AND Estado_RID ='".$desEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desLocalidad = $rowsql1['ID'];
            }
        $desMunicipio = mysqli_real_escape_string($cnx_cfdi2, $data[20]);
		//$desMunicipio=str_replace(" ","''",$desMunicipio);
        $query1 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE ClaveMunicipio ='".$desMunicipio."' AND Estado_RID ='".$desEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desMunicipio = $rowsql1['ID'];
            }

        $desCP = mysqli_real_escape_string($cnx_cfdi2, $data[22]);
		        $desColonia = mysqli_real_escape_string($cnx_cfdi2, $data[18]);
				//$desColonia=str_replace(" ","''",$desColonia);

        $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$desCP."' AND ClaveColonia ='".$desColonia."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desColonia = $rowsql1['ID'];
            }
        $desPais = mysqli_real_escape_string($cnx_cfdi2, $data[23]);
       
                
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
				//inserta solicitudessub (Embalaje)
$queryP = "INSERT INTO ".$prefijobd."SolicitudesRepartos(ID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumInt,RemitenteNumExt,RemitenteEstado_REN,RemitenteEstado_RID,
RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,RemitenteMunicipio_REN,RemitenteMunicipio_RID,RemitenteColonia_REN,RemitenteColonia_RID,RemitenteCodigoPostal,RemitentePais,Destinatario,
DestinatarioRFC,DestinatarioCalle,DestinatarioNumInt,DestinatarioNumExt,DestinatarioEstado_REN,DestinatarioEstado_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,
DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,DestinatarioCodigoPostal,DestinatarioPais,CodigoOrigen,CodigoDestino,
FolioSub_REN,FolioSub_RID) values('$newid','$rem','$remRFC','$remCalle',
'$remNum1','$remNum2','Estados','$remEstado','c_Localidad','$remLocalidad','c_Municipio','$remMunicipio','c_Colonia','$remColonia','$remCP','$remPais','$des','$desRFC','$desCalle',
'$desNum1','$desNum2','Estados','$desEstado','c_Localidad','$desLocalidad','c_Municipio','$desMunicipio','c_Colonia','$desColonia','$desCP','$desPais','$remID','$desID','Solicitudes','$idRem');";
				 //$newquery=$queryP;
                 $cont++;
                $newquery=str_replace("''","NULL",$queryP);
				$newquery=str_replace("' '","NULL",$newquery);
				//die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }
            }
   }

  }
                      /**/

  

                  fclose($handle);//cierra el archivo
				  if ($runP) {
                  echo "<script>alert('Importacion Exitosa, se creo la solicitud ".$xfolio."');</script>";//Imprime exito
				  }
 }


//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar Repartos</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar Repartos</h3><br />
  <form method="post" enctype="multipart/form-data">
   
        
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

