<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idCot = $_GET["ID"];//trae id remision

$maxId=0;//se inicializa el ID maximo de awareim

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{
 
 //Borrar Cotizacionessub que tenga la Solicitud antes de actualizar (FolioSub_RID)
 
 $query2 = "DELETE FROM ".$prefijobd."Cotizacionessub WHERE FolioSub_RID = ".$idCot;
 //echo $query2;
	
  mysqli_query($cnx_cfdi2,$query2);
	
 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
  if($filename[1] == 'csv')
  {
   $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
   fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
   while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos,
   {


                $cantidad = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //se empiezan a leer las columnas del CSV
                $peso = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
                $descripcion = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                $alto = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
                $ancho = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
                $largo = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
                $claveUnidadPeso = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
                $importe = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
                
                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$claveUnidadPeso."';"; 
                $runsql = mysqli_query($cnx_cfdi2, $query);
                if (!$runsql) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query;
                    die($mensaje);
                }
                while ($rowsql = mysqli_fetch_assoc($runsql)){
                    $claveUnidadPesoId = $rowsql['ID'];
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
                
                $newid = $basidgen;
				//inserta Cotizacionessub (Embalaje)
                $queryP = "INSERT INTO ".$prefijobd."Cotizacionessub(ID,FolioSub_REN, FolioSub_RID, Cantidad, Descripcion, Alto, Ancho, Largo, UnidadPeso_REN, UnidadPeso_RID, 
                 Importe, Peso) values
                 ('$newid','Cotizaciones', '$idCot', '$cantidad', '$descripcion', '$alto', '$ancho', '$largo', 'c_ClaveUnidadPeso', '$claveUnidadPesoId', '$importe', '$peso');";
				 //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                //die($newquery);
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
  <h3 align="center">Importar Embalaje</h3><br />
  <form method="post" enctype="multipart/form-data">
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" />
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

