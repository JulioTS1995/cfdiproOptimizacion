<?php
//error_reporting(0);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_set_charset($cnx_cfdi2, 'utf8');
$time = date('Y-m-d H:i:s');
$prefijo = $_GET["prefijo"];
$prefijoCorto = str_replace("_","",$prefijo);
set_time_limit(300);
$documentador = $_GET["documentador"];

$rutaEjemplo = "docs/".$prefijoCorto."/prbImportarCargoUnidad.csv";
$servidor = "https://" . $_SERVER['HTTP_HOST'];

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

$cont=0;
$errorArray = array();
$exitoArray = array();

if($_FILES['file']['name']){
    $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
    if($filename[1] == 'csv'){
        $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
        fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
        while(($data = fgetcsv($handle,10000,","))!==FALSE){
            $error=0;
            
            $unidad = mysqli_real_escape_string($cnx_cfdi2, $data[0]);
            $viaje = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
            $importe = limpiarNumero(mysqli_real_escape_string($cnx_cfdi2, $data[2]));
            $comentarios = mysqli_real_escape_string($cnx_cfdi2, $data[3]);

            $query5 = "SELECT ID FROM ".$prefijo."Unidades WHERE Unidad = '".$unidad."' LIMIT 1;";
            $runsql5 = mysqli_query($cnx_cfdi2, $query5);
            if (!$runsql5) {//debug
                $error=1;
                $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
            }
            if (mysqli_num_rows($runsql5) == 0) {
                $error = 1;
                $errorArray[] = ['error' => "Unidad no encontrada: $unidad"];
            }else{
                while ($rowsql5 = mysqli_fetch_assoc($runsql5)){
                    $unidadId = $rowsql5['ID'];
                }
            }

            $query1 = "SELECT ID FROM ".$prefijo."Viajes2 WHERE XFolio ='".$viaje."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {
                $error=1;
                $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
            }else{
                if (mysqli_num_rows($runsql1) == 0) {
                    //$error = 1;
                    //$errorArray[] = ['error' => "Viaje no encontrado: $viaje"];
                }else{
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $viajeId = $rowsql1['ID'];
                    }
                }
            }
            
            $query6 = "SELECT max(Folio) FROM ".$prefijo."CargosUnidades;"; 
            //die($query6);
            $runsql6 = mysqli_query($cnx_cfdi2, $query6);
            if (!$runsql6) {//debug
                $mensaje  = 'Consulta no válida: ' . mysqli_error($cnx_cfdi2) . "\n";
                $mensaje .= 'Consulta completa: ' . $query6;
                die($mensaje);
            }
            while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                $folio2 = $rowsql6['max(Folio)'];
            }

            $folio2=$folio2+1;

            if($error===0){
                //die('entro');
                $newid = obtenerSiguienteID($cnx_cfdi2);

                $queryInsert = "INSERT INTO ".$prefijo."CargosUnidades (ID, Unidad_REN, Unidad_RID, Viaje_REN, 
                    Viaje_RID, Importe, Comentarios, Documentador, Modificado, FechaModificado, Creado, Folio) VALUES 
                    ('$newid','Unidades', '$unidadId', 'Viajes2', '$viajeId', '$importe', '$comentarios', '$documentador', '$documentador', '$time', 
                    '$time', '$folio2')";
                    $queryInsert=str_replace("''","NULL",$queryInsert);

                $runInsert= mysqli_query($cnx_cfdi2, $queryInsert);

                
                if (!$runInsert) {//debug
                    $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
                }else{
                    $exitoArray[] = ['exito' => $folio2];
                    $cont++;
                }
                
            }

        }

    }
    if (!empty($errorArray)) {
        echo '<table border="1">';
        echo '<tr><th>Error</th></tr>';
        foreach ($errorArray as $registro) {
            echo '<tr>';
            echo '<td>' . $registro['error'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    
    if (!empty($exitoArray)) {
        echo '<table border="1">';
        echo '<tr><th>Importadas</th></tr>';
        foreach ($exitoArray as $registro) {
            echo '<tr>';
            echo '<td>' . $registro['exito'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo "<script>alert('Importacion Exitosa, se crearon ".$cont." Cargos de Unidades');</script>";
    }
}

?>


<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar CSV Compras</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
 </head>  
 <body>  
  <h3 align="center">Importar CSV Compras</h3><br />
  <form method="post" enctype="multipart/form-data" id="importarCTForm">
        
        
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
    <br><br><br>
    <a href="<?php echo $servidor . '/' . $rutaEjemplo; ?>" download>Descargar Layout CSV</a>
        <i class="bi bi-file-earmark-excel"></i>
    </a>

   </div>
  </form>
 </body>  
</html>