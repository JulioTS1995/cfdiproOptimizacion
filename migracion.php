<?php

if(isset($_POST["submit"])){//cuando se presiona el boton enviar... 
    $origen = strtolower($_POST["origen"]);//trae variables
    $destino = strtolower($_POST["destino"]);
    $DB = strtolower($_POST["DB"]);
    require_once('cnx_cfdi2.php');
    mysqli_select_db($cnx_cfdi2,$database_cfdi);

    $file = "QueryMigracion_".$origen."-".$destino.".sql";
    $txt = fopen($file, "w") or die("Unable to open file!");//crea archivo
    //query. Busca las tablas con el nombre del origen
    $tablasOrigen = [];
    $tablasDestino = [];
    $query1 = "SHOW TABLES
    FROM `".$DB."`
    WHERE 
    `Tables_in_".$DB."` LIKE '".$origen."_%';"; 
    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){//llena array tablasOrigen
        $tablasOrigen[] = $rowsql1['Tables_in_'.$DB];
    }

    $query2 = "SHOW TABLES
    FROM `".$DB."`
    WHERE 
    `Tables_in_".$DB."` LIKE '".$destino."_%';"; 
    $runsql2 = mysqli_query($cnx_cfdi2, $query2);
    while ($rowsql2 = mysqli_fetch_assoc($runsql2)){//llena array tablasDestino
        $tablasDestino[] = $rowsql2['Tables_in_'.$DB];
    }
    $tablasOrigenSP = str_replace($origen."_","",$tablasOrigen);
    $tablasDestinoSP = str_replace($destino."_","",$tablasDestino);
    $tablasOutput = array_intersect($tablasOrigenSP, $tablasDestinoSP);
    //print_r($tablasOutput);
    foreach($tablasOutput as &$tabla){

        $query3 = "SELECT `COLUMN_NAME` 
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA`='".$DB."' 
            AND `TABLE_NAME`='".$origen."_".$tabla."';"; 
        //die($query3);
        $runsql3 = mysqli_query($cnx_cfdi2, $query3);
        $columnasOrigen = [];
        while ($rowsql3 = mysqli_fetch_array($runsql3)){
            $columnasOrigen[] = $rowsql3['COLUMN_NAME'];
        }

        $query4 = "SELECT `COLUMN_NAME` 
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA`='".$DB."' 
            AND `TABLE_NAME`='".$destino."_".$tabla."';"; 
        //die($query4);
        $runsql4 = mysqli_query($cnx_cfdi2, $query4);
        $columnasDestino = [];
        while ($rowsql4 = mysqli_fetch_array($runsql4)){
            $columnasDestino[] = $rowsql4['COLUMN_NAME'];
        }

        $columnasOutput = array_intersect($columnasOrigen, $columnasDestino);

        $outputQuery = "INSERT INTO ".$DB.".".$destino."_".$tabla." (".implode(',',$columnasOutput).") SELECT ".implode(',',$columnasOutput)." FROM ".$DB.".".$origen."_".$tabla." WHERE ID NOT IN (SELECT ID FROM ".$destino."_".$tabla.");";
        //echo($outputQuery);
        fwrite($txt, $outputQuery);
    }


    fclose($txt);

    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    header("Content-Type: text/plain");
    readfile($file);
}






?>



<!DOCTYPE html>  
<html>  
    <head>  
        <title>Generador de Query Migracion</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    </head>  
    <body>  
        <h3 align="center">Generador de Query Migracion</h3><br />
        <form method="post" enctype="multipart/form-data">
            <div align="center">  
                <label>Ingresa los datos:</label>
                <input type="text" name="origen" placeholder = "Origen" required/>
                <input type="text" name="destino" placeholder = "Destino" required/>
                <input type="text" name="DB" placeholder = "Base de datos" required/>
                <br />
                <input type="submit" name="submit" value="Generar" class="btn btn-info" />
            </div>
        </form>
    </body>  
</html>
