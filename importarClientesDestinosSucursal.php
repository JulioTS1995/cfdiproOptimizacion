<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$sucursal = $_GET["sucursal"];//trae sucursal

$maxId=0;//se inicializa el ID maximo de awareim

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{    if($_POST['cliente'] == 0){
    echo "<script>alert('Es necesario seleccionar cliente');</script>";
    ob_start();
}
 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
  if($filename[1] == 'csv')
  {
   $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
   fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
   while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos,
   {


                $item1 = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //se empiezan a leer las columnas del CSV
                $item2 = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
                $item3 = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                $item4 = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
                $item5 = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
                $item6 = mysqli_real_escape_string($cnx_cfdi2, $data[5]);


                $item10 = mysqli_real_escape_string($cnx_cfdi2, $data[9]);
                $item11 = mysqli_real_escape_string($cnx_cfdi2, $data[10]);
                $item12 = mysqli_real_escape_string($cnx_cfdi2, $data[11]);

                $item13 = mysqli_real_escape_string($cnx_cfdi2, $data[12]);
                $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$item13."';"; 
                $runsql = mysqli_query($cnx_cfdi2, $query);
                if (!$runsql) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query;
                    die($mensaje);
                }
                while ($rowsql = mysqli_fetch_assoc($runsql)){
                    $item13 = $rowsql['ID'];
                }

                $item14 = mysqli_real_escape_string($cnx_cfdi2, $data[13]);
                $item15 = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
                $item16 = mysqli_real_escape_string($cnx_cfdi2, $data[15]);
                $item17 = mysqli_real_escape_string($cnx_cfdi2, $data[16]);
                $item18 = mysqli_real_escape_string($cnx_cfdi2, $data[17]);
                $item19 = mysqli_real_escape_string($cnx_cfdi2, $data[18]);

                
                $item7 = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
                $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$item16."' AND ClaveColonia ='".$item7."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $item7 = $rowsql1['ID'];
                }


                $item8 = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
                $query1 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE ClaveMunicipio ='".$item8."' AND Estado_RID ='".$item13."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $item8 = $rowsql1['ID'];
                }


                $item9 = mysqli_real_escape_string($cnx_cfdi2, $data[8]);
                $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$item9."' AND Estado_RID ='".$item13."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $item9 = $rowsql1['ID'];
                }

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
				//inserta Solicitudessub (Embalaje)
                $queryP = "INSERT INTO ".$prefijobd."Clientesdestinos(ID,IdClienteDestino,Destinatario,Contacto,Calle,NumExt,NumInt,Colonia_REN,Colonia_RID,Municipio_REN,Municipio_RID,Localidad_REN,Localidad_RID,Referencia,
                 Domicilio, Ciudad,Estado_REN, Estado_RID, RFC, Telefono, CodigoPostal, Comentarios, Pais, NumRegIdTrib,FolioDestinos_REN,FolioDestinos_RID) values
                 ('$newid','$item1','$item2','$item3','$item4','$item5','$item6','c_Colonia','$item7','c_Municipio','$item8','c_Localidad','$item9','$item10',
                 '$item11','$item12','Estados','$item13','$item14','$item15','$item16','$item17','$item18','$item19','Clientes','".$_POST['cliente']."');";
				 //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }
   }
   fclose($handle);//cierra el archivo
   echo "<script>alert('Importacion Exitosa');</script>";//Imprime exito
  }
 }
}

//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar Embalaje</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar Clientes Destinos</h3><br />
  <form method="post" enctype="multipart/form-data">
  <div class="form-group">
                <label>Cliente:</label>
                <select class="form-control inputdefault" name="cliente" id="cliente" required aria-required="true">
                    <option value='0'>Selecciona Cliente</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
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
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" />
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

