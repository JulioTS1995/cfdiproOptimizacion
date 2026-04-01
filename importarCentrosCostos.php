<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idRem = $_GET["ID"];//trae id remision

$maxId=0;//se inicializa el ID maximo de awareim

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
   while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos,
   {

                $unidad='';
                $item1 = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //se empiezan a leer las columnas del CSV
                $item2 = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
                $item3 = mysqli_real_escape_string($cnx_cfdi2, $data[2]);

                $query4 = "SELECT * FROM ".$prefijobd."Unidades WHERE Unidad ='".$item3."';"; 
                    $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                    $rowsNum=mysqli_num_rows($runsql4);
                    if ($rowsNum==0) {//debug
                        echo "<script>alert('La unidad ".$item3." no existe en el sistema');</script>";
                        $item3='';
                    }
            
                    if (!$runsql4) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query4;
                        die($mensaje);
                    }
                    while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                        $unidad = $rowsql4['ID'];

                    }
                $item4 = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
                $item5 = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
                $item6 = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
                
				
				
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
				//inserta remisionessub (Embalaje)
                $queryP = "INSERT INTO ".$prefijobd."CentroCosto(ID,CeCo,Sucursal,UnidadNegocio_REN,UnidadNegocio_RID,ClasificacionCC,Cuenta,SubCuenta) values
                 ('$newid','$item1','$item2','Unidades','$unidad','$item4','$item5','$item6');";
				 //$newquery=$queryP;
                $newquery=str_replace("''","NULL",$queryP);
                $newquery=preg_replace("/[^a-zA-Z0-9_ -'(),-:]/s"," ",$newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }
                $MaterialPeligroso='';
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
  <title>Importar Centros de Costos</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar Centros de Costos</h3><br />
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

