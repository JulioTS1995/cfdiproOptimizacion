<?php

function obtenerSiguienteID($cnx_cfdi2) {
    $begintrans = mysqli_query($cnx_cfdi2, "BEGIN");

    $qry_basidgen = "SELECT MAX_ID FROM bas_idgen";
    $result_qry_basidgen = mysqli_query($cnx_cfdi2, $qry_basidgen);

    if (!$result_qry_basidgen) {
        $endtrans = mysqli_query($cnx_cfdi2, "ROLLBACK");
        echo "Error4";
        return false;
    } else {
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
        $basidgen = $rowbasidgen[0] + 1;

        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query($cnx_cfdi2, $upd_basidgen);

        if ($result_upd_basidgen) {
            $endtrans = mysqli_query($cnx_cfdi2, "COMMIT");
            return $basidgen;
        }
    }

    return false;
}

function limpiarNumero($numero) {
    return (float)preg_replace("/[^0-9\.]/", "", $numero);
}
set_time_limit(3000);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_set_charset($cnx_cfdi2, 'utf8');
header("Content-Type: text/html; charset=UTF-8");


$prefijo = $_GET["prefijo"];


$time = date('Y-m-d H:i:s');
$countError=0;
$countInsertados=0;

if(isset($_POST["submit"])){
    if($_FILES['file']['name']){
        $filename = explode(".", $_FILES['file']['name']);
        if($filename[1] == 'csv'){
            $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
            fgets($handle);

            while (($data = fgetcsv($handle)) !== false) {

                $data = array_map(function($value) use ($cnx_cfdi2) {
                    $value = mysqli_real_escape_string($cnx_cfdi2, $value);
                    return iconv('ISO-8859-1', 'UTF-8', $value);
                }, $data);

                
                $codigo = $data[0];
                $unidad = $data[1];
                $fecha = date('Y-m-d', strtotime(str_replace("/", "-", $data[2])));
                $hora = $data[3];
                $caseta = $data[4];
                $importe = limpiarNumero(str_replace(",", "", $data[5]));
                $clase = $data[6];
                $viaje = $data[7];
				$comentarios = $data[8];

                $newid = obtenerSiguienteID($cnx_cfdi2);

                // Consulta para verificar si ya existe el registro
                $checkQuery = "SELECT COUNT(*) AS total FROM {$prefijo}IAVE 
                WHERE 
                bUnidad = '$unidad' 
                AND acodigo = '$codigo' 
                AND cFecha = '$fecha' 
                AND dHora = '$hora' 
                AND eCaseta = '$caseta' 
                AND gImporte = '$importe'";

                $checkResult = mysqli_query($cnx_cfdi2, $checkQuery);
                if (!$checkResult) {
                    die('Error al ejecutar la consulta de validación: ' . mysqli_error($cnx_cfdi2));
                }
				
                $row = mysqli_fetch_assoc($checkResult);
                if ($row['total'] > 0) {
                    $countError++;
                    
                } elseif($checkResult && !empty($codigo)) {
                    $queryP = "INSERT INTO {$prefijo}IAVE(ID, aCodigo, bUnidad, cFecha, dHora, eCaseta, fClase, gImporte, Viaje, Comentarios) 
                                VALUES
                                ('$newid','$codigo','$unidad','$fecha', '$hora', '$caseta','$clase','$importe', '$viaje', '$comentarios');";

                    $newquery = str_replace("''", "NULL", $queryP);
                    $newquery = str_replace("\\", "", $newquery);
                    $runP = mysqli_query($cnx_cfdi2, $newquery);

                    if (!$runP) {
                        $countError++;
                        echo('Error al preparar la consulta [I:IAVE]: '.$cnx_cfdi2->error);
                    } else {
                        $countInsertados++;
						//echo('exito');
                    }
                }

            }
            
            echo "<script>alert(" . json_encode("Se importaron $countInsertados Registros \nError en $countError Registros") . ");</script>";
            fclose($handle); // Cerrar el archivo
        }else{
            echo "<script>alert('Se debe importar un archivo con extension CSV');</script>";//Imprime error archivo
        }
    }
}


?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar IAVE</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar IAVE</h3><br />
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

