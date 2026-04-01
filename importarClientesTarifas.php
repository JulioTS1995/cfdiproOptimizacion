<?php

error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores
set_time_limit(3000);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

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

$modalidadesValidas = [
    'General',
    'One Way',
    'Round Trip'
];

$estatusValidos = [
    'Activa',
    'Baja'
];

date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

$cont=0;
$error=0;
$errorArray = array();
$errorArray = array();

if($_FILES['file']['name']){
    $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
    if($filename[1] == 'csv'){
        $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
        fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
        while(($data = fgetcsv($handle,10000,","))!==FALSE){
            
            $concepto = mysqli_real_escape_string($cnx_cfdi2, $data[0]);
            $cantidad = limpiarNumero(mysqli_real_escape_string($cnx_cfdi2, $data[1]));
            $precio = limpiarNumero(mysqli_real_escape_string($cnx_cfdi2, $data[2]));
            $descuento = limpiarNumero(mysqli_real_escape_string($cnx_cfdi2, $data[3]));
            $detalle = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
            $fechaInicio = date('Y-m-d', strtotime(str_replace("/", "-", mysqli_real_escape_string($cnx_cfdi2, $data[5]))));
            $fechaFin = date('Y-m-d', strtotime(str_replace("/", "-", mysqli_real_escape_string($cnx_cfdi2, $data[6]))));
            $estatus = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
            $modalidad = mysqli_real_escape_string($cnx_cfdi2, $data[8]);
            $comentario = mysqli_real_escape_string($cnx_cfdi2, $data[9]);

            if (!in_array($modalidad, $modalidadesValidas)) {
                $error = 1;
                $errorArray[] = ['error' => "Modalidad no valida: $modalidad"];
            }
 
            if (!in_array($estatus, $estatusValidos)) {
                $error = 1;
                $errorArray[] = ['error' => "estatus no valido: $estatus"];
            }
            
            $query3 = "SELECT ID, Concepto, Tipo, IVA, Retencion FROM ".$prefijobd."Conceptos WHERE Concepto ='".$concepto."' AND Activo='1';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);
            if (!$runsql3) {
                $error=1;
                $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
            }else{
                if (mysqli_num_rows($runsql3) == 0) {
                    $error = 1;
                    $errorArray[] = ['error' => "Concepto no encontrado: $concepto"];
                } else {
                    while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                        $conceptoID = $rowsql3['ID'];
                        $conceptoTipo = $rowsql3['Tipo'];
                        $conceptoIVA = $rowsql3['IVA'];
                        $conceptoRetencion = $rowsql3['Retencion'];
                    }
                }
            }
            
            
            if($error==0){

                $newid = obtenerSiguienteID($cnx_cfdi2);
                $newidPartida = obtenerSiguienteID($cnx_cfdi2);

                $importeIVA = $precio * ($conceptoIVA/100);
                $importeRetencion = $precio * ($conceptoRetencion/100);
                $importe = ($precio * $cantidad) + $importeIVA - $importeRetencion;
                
                $queryP = "INSERT INTO ".$prefijobd."ClientesTarifasPartidas(ID,ConceptoPartida,FolioConceptos_REN,FolioConceptos_RID,IVA,Retencion,Cantidad,PrecioUnitario,
                Subtotal,IVAImporte,RetencionImporte,Importe,Tipo,Detalle,FolioSub_REN, FolioSub_RID, FechaInicio, FechaVigencia,Comentarios,Modalidad,Estatus) values
                ('$newidPartida','$concepto','Conceptos','$conceptoID','$conceptoIVA','$conceptoRetencion','1','$precio','$precio','$importeIVA','$importeRetencion',
                '$importe','$conceptoTipo','$detalle','ClientesTarifas','$newid', '$fechaInicio', '$fechaFin', '$comentario','$modalidad','$estatus');";
                $queryP=str_replace("''","NULL",$queryP);
                $queryP=str_replace("' '","NULL",$queryP);
                $runP= mysqli_query($cnx_cfdi2, $queryP);
                if (!$runP) {
                    $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
                }

                $queryC = "INSERT INTO ".$prefijobd."ClientesTarifas(ID,Moneda,FolioTarifas_REN,FolioTarifas_RID,Ruta_REN,Ruta_RID,Clase_REN,Clase_RID,Tipo) values
                ('$newid','PESOS','Clientes','".$_POST['cliente']."','Rutas','".$_POST['ruta']."','UnidadesClase','".$_POST['clase']."', '".$_POST['tipo']."');";
                
                $queryC=str_replace("''","NULL",$queryC);
                $queryC=str_replace("' '","NULL",$queryC);
                $runC= mysqli_query($cnx_cfdi2, $queryC);
                if (!$runC) {
                    $errorArray[] = ['error' => "Error en la consulta SQL: " . mysqli_error($cnx_cfdi2)];
                }else{
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
    
    echo "<script>alert('Importacion Exitosa, se crearon ".$cont." Tarifas');</script>";
}

?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar Clientes Tarifas</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar Clientes Tarifas</h3><br />
  <form method="post" enctype="multipart/form-data" id="importarCTForm">
    <div class="form-group">
        <label>Cliente:</label>
        <select class="form-control inputdefault" name="cliente" id="cliente" required aria-required="true">
            <option value='0'>Selecciona Cliente</option>
        <?php

        $resSQL = "SELECT ID, RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Estatus = 'Activo' ORDER BY Cliente";
        $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  
        while ($rowSQL = mysqli_fetch_assoc($runSQL))
        {
            ?>
            <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Cliente']; ?></option>
        <?php
        
        }
        ?>
        </select></div>

        <div class="form-group">
        <label>Ruta:</label>
        <select class="form-control inputdefault" name="ruta" id="ruta" required aria-required="true">
            <option value='0'>Selecciona Ruta</option>
        <?php

        $resSQL = "SELECT ID, Ruta FROM ".$prefijobd."Rutas WHERE Estatus = 'Activo' ORDER BY Ruta";
        $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  
        while ($rowSQL = mysqli_fetch_assoc($runSQL))
        {
            ?>
            <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Ruta']; ?></option>
        <?php
        
        }
        ?>
        </select></div>

        <div class="form-group">
        <label>Clase:</label>
        <select class="form-control inputdefault" name="clase" id="clase" required aria-required="true">
            <option value='0'>Selecciona Clase</option>
        <?php

        $resSQL = "SELECT ID, Clase FROM ".$prefijobd."UnidadesClase ORDER BY Clase";
        $runSQL = mysqli_query($cnx_cfdi2, $resSQL);  
        while ($rowSQL = mysqli_fetch_assoc($runSQL))
        {
            ?>
            <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Clase']; ?></option>
        <?php
        
        }
        ?>
        </select></div>

                <div class="col-md-12">
				<div class="form-group">

                <label>Tipo:
                <select class="form-control inputdefault" name="tipo" id="tipo">
                <option value='0'>Selecciona el Tipo</option>
                <option value='Carga General'>Carga General</option>
                <option value='Naviera'>Naviera</option>
                <option value='IMO (peligroso)'>IMO (peligroso)</option>
                <option value='Refrigerada'>Refrigerada</option>
                <option value='IMO Refrigerada'>IMO Refrigerada</option>
          
	    	</select></label>
        </div>
        
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
  <script>
        document.getElementById('importarCTForm').addEventListener('submit', function(event) {
            const selects = document.querySelectorAll('select[required]');
            let valid = true;
            selects.forEach(function(select) {
                if (select.value === '0') {
                    alert('Por favor, selecciona una opción válida para ' + select.previousElementSibling.innerText);
                    valid = false;
                    event.preventDefault();
                }
            });
            return valid;
        });
    </script>
 </body>  
</html>