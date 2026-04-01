<?php
set_time_limit(3000);
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');
require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo

$maxId=0;//se inicializa el ID maximo de awareim
$cont3=0;
$Proveedor = "";
$diasCredito = "";

date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"])){//cuando se presiona el votor enviar... 
    $registrosInsertados = array();
    if($_POST['oficina'] == 0){
        echo "<script>alert('Es necesario seleccionar oficina');</script>";
        die("Intenta de nuevo. Se debe seleccionar una oficina");
    }
    
	foreach ($_FILES['file']['tmp_name'] as $key => $value) {

                    //Crear Nuevo ID
                $begintrans = mysqli_query($cnx_cfdi2, "BEGIN");
                $qry_basidgen = "SELECT MAX_ID from bas_idgen";
                $result_qry_basidgen = mysqli_query($cnx_cfdi2, $qry_basidgen);

                if (!$result_qry_basidgen) {
                    // No se pudo obtener el siguiente BASIDGEN
                    mysqli_query($cnx_cfdi2, "ROLLBACK");
                    echo "Error4";
                } else {
                    $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
                    $basidgen = $rowbasidgen[0] + 1;
                    $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                    $result_upd_basidgen = mysqli_query($cnx_cfdi2, $upd_basidgen);

                    if ($result_upd_basidgen) {
                        mysqli_query($cnx_cfdi2, "COMMIT");
                    }
                }
    $idPrincipal = $basidgen;

 if($value)
 {
  $filename = explode(".", $value);//verifica que sea xml
  if($filename[1] == ('xml')||('XML')){

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($value);

    $xml2 = simplexml_load_file($target_file);
    $ns = $xml2->getNamespaces(true);
    $xml2->registerXPathNamespace('c', $ns['cfdi']);
    $xml2->registerXPathNamespace('t', $ns['tfd']);
	foreach ($xml2->xpath('//cfdi:Comprobante') as $Comprobante) {

		    $fecha = $Comprobante['Fecha'];
            $fecha=str_replace("T"," ",$fecha);
			$folio = $Comprobante['Folio'];
            $formaPago = $Comprobante['FormaPago'];
            $tipoComprobante = $Comprobante['TipoDeComprobante'];
            


            $query3 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE id2 ='".$formaPago."';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);
            if (!$runsql3) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query3;
                die($mensaje);
            }
            while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                $formaPago = $rowsql3['ID'];
            }

            $metodoPago=$Comprobante['MetodoPago'];

            $query4 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE id2 ='".$metodoPago."';"; 
            $runsql4 = mysqli_query($cnx_cfdi2, $query4);
            if (!$runsql4) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query4;
                die($mensaje);
            }
            while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                $metodoPago = $rowsql4['ID'];
            }

            $moneda= $Comprobante['Moneda'];
            if($moneda=="MXN"){
                $moneda="PESOS";
            }else{
                $moneda="DOLARES";
            }

            $noCertificado=$Comprobante['NoCertificado'];
            $sellocfd=$Comprobante['Sello'];
            $selloDigital=$Comprobante['Sello'];
            $serieF=$Comprobante['Serie'];
            $subtotal=$Comprobante['SubTotal'];
            $total=$Comprobante['Total'];
			
	}//end Ubicacion 
	

    foreach ($xml2->xpath('//t:TimbreFiscalDigital') as $Complemento) {
        $fechaTimbrado=$Complemento['FechaTimbrado'];
        $fechaTimbrado=str_replace("T"," ",$fechaTimbrado);
        $noCertificadoSAT=$Complemento['NoCertificadoSAT'];
        $UUID=$Complemento['UUID'];
        $selloSAT=$Complemento['SelloSAT'];
        
        
        
    }
    
    foreach ($xml2->xpath('//cfdi:Emisor') as $Emisor) {
        
        $RFCEmisor=$Emisor['Rfc'];
        $NombreEmisor=$Emisor['Nombre'];
        $queryProv = "SELECT * FROM ".$prefijobd."Proveedores WHERE RFC ='".$RFCEmisor."';"; 
        $runsqlProv = mysqli_query($cnx_cfdi2, $queryProv);
        $numRenglones=mysqli_num_rows($runsqlProv);
        if (!$runsqlProv) {//debug
            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $queryProv;
            die($mensaje);
        }
        while ($rowsqlProv = mysqli_fetch_assoc($runsqlProv)){
            $Proveedor = $rowsqlProv['ID'];
            $diasCredito = $rowsqlProv['DiasCredito'];
        }
        $vencimiento = strtotime($time,"+ ".$diasCredito." days");

    }
if($numRenglones==0){// IMPORTACION FALLIDA: RFC NO REGISTRADO
            echo '<script type="text/javascript">alert("Importacion Fallida: No existe un proveedor registrado con este RFC\nUUID: '.$UUID.'\nProveedor: '.$RFCEmisor.'.");</script>';
        }

        if ($tipoComprobante!='I') {// IMPORTACION FALLIDA: DOCUMENTO NO TIPO INGRESO
            echo '<script type="text/javascript">alert("Importacion Fallida: Este documento no es de tipo ingreso\nUUID: '.$UUID.'\nProveedor: '.$RFCEmisor.'.");</script>';
        }
    
        $query5 = "SELECT ID FROM ".$prefijobd."Compras WHERE Comentarios ='".$UUID."';"; 
        $runsql5 = mysqli_query($cnx_cfdi2, $query5);
        $rowsNum=mysqli_num_rows($runsql5);
        if ($rowsNum>0) {// IMPORTACION FALLIDA: UUID YA REGISTRADO
            echo '<script type="text/javascript">alert("Importacion Fallida: Este UUID ya existe en una factura en el sistema\nUUID: '.$UUID.'\nProveedor: '.$RFCEmisor.'.");</script>';
        }

    foreach ($xml2->xpath('//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {

        $cantidad=$Concepto['Cantidad'];
        $claveProdServ=$Concepto['ClaveProdServ'];
        $claveUnidad=$Concepto['ClaveUnidad'];
        $descripcion=$Concepto['Descripcion'];
        
        $descripcion=str_replace("'","",$descripcion);
        $descripcion=str_replace('"','',$descripcion);
        
        $valorUnitario=$Concepto['ValorUnitario'];
        $descuento=$Concepto['Descuento'];
        (float)$importe=((float)$valorUnitario*(float)$cantidad)-(float)$descuento;

        $importeIVA = null;
        $IVA = null;
        $importeRetencion = null;
        $Retencion = null;
        
        $impuestos = $Concepto->children('cfdi', true)->Impuestos;

        if ($impuestos->Traslados) {
            foreach ($impuestos->Traslados->Traslado as $traslado) {
                $importeIVA = $traslado->attributes()['Importe'];
                $IVA = $traslado->attributes()['TasaOCuota'];
                (float)$IVA=(float)$IVA*100;
    
    
            }
        }
    
        if ($impuestos->Retenciones) {
            foreach ($impuestos->Retenciones->Retencion as $retencion) {
                $importeRetencion = $retencion->attributes()['Importe'];
                $Retencion = $retencion->attributes()['TasaOCuota'];
                (float)$Retencion=(float)$tasaRetencion*100;
            }
        }
        
    
                                   //Crear Nuevo ID
                $begintrans = mysqli_query($cnx_cfdi2, "BEGIN");
                $qry_basidgen = "SELECT MAX_ID from bas_idgen";
                $result_qry_basidgen = mysqli_query($cnx_cfdi2, $qry_basidgen);

                if (!$result_qry_basidgen) {
                    // No se pudo obtener el siguiente BASIDGEN
                    mysqli_query($cnx_cfdi2, "ROLLBACK");
                    echo "Error4";
                } else {
                    $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);
                    $basidgen = $rowbasidgen[0] + 1;
                    $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
                    $result_upd_basidgen = mysqli_query($cnx_cfdi2, $upd_basidgen);

                    if ($result_upd_basidgen) {
                        mysqli_query($cnx_cfdi2, "COMMIT");
                    }
                }
                $new2id = $basidgen;

        if($numRenglones!=0 && $tipoComprobante=='I'){
                $queryP = "INSERT INTO ".$prefijobd."ComprasSub(ID,Cantidad,Nombre,Importe,PrecioUnitario,FolioSub_REN,FolioSub_RID,Descuento,TasaIVA,ImporteIVA,TasaRetencion,ImporteRetencion) values
                ('$new2id','$cantidad','$descripcion','$importe','$valorUnitario','Compras','$idPrincipal','$descuento','".$IVA."','".$importeIVA."','".$Retencion."','".$importeRetencion."');";
                    //die($queryP);
            $queryP=str_replace("''","NULL",$queryP);
            $queryP=str_replace("á","a",$queryP);
            $queryP=str_replace("é","e",$queryP);
            $queryP=str_replace("í","i",$queryP);
            $queryP=str_replace("ó","o",$queryP);
            $queryP=str_replace("ú","u",$queryP);
            $runP= mysqli_query($cnx_cfdi2, $queryP);
            if (!$runP) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $queryP;
                die($mensaje);
            }
        }

        $cont3++;
    }//Sub-v-2
    $IVA=0;
    $Retencion=0;
    foreach ($xml2->xpath('//cfdi:Impuestos') as $Impuestos) {
        $importeIVA2=$Impuestos['TotalImpuestosTrasladados'];
        if($importeIVA2!=0){
            $IVA=16;
        }
        $importeRetencion=$Impuestos['TotalImpuestosRetenidos'];
        if($importeRetencion!=0){
            $Retencion=4;
        }

    }

    }


    //echo $key.": ".$value."</br>";
  
}	
                    /**/
    $query5 = "SELECT * FROM ".$prefijobd."Oficinas WHERE ID ='".$_POST['oficina']."';"; 
    $runsql5 = mysqli_query($cnx_cfdi2, $query5);
    if (!$runsql5) {//debug
        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $query5;
        die($mensaje);
    }
    while ($rowsql5 = mysqli_fetch_assoc($runsql5)){
        $serie2 = $rowsql5['Serie'];
        $ivaOficina =$rowsql5['IVA'];
        $retOficina = $rowsql5['Retencion'];
    }
    /**/
    $query6 = "SELECT max(Folio) FROM ".$prefijobd."Compras WHERE OficinaCompras_RID ='".$_POST['oficina']."';"; 
        //die($query6);
        $runsql6 = mysqli_query($cnx_cfdi2, $query6);
        if (!$runsql6) {//debug
            $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query6;
            die($mensaje);
        }
        while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
            $folio2 = $rowsql6['max(Folio)'];
        }
    /**/
    $folio2=$folio2+1;
    $xfolio="".$serie2."".$folio2."";

    $query7 = "SELECT SUM(Importe) FROM ".$prefijobd."ComprasSub WHERE FolioSub_RID ='".$idPrincipal."';"; 
    //die($query7);
    $runsql7 = mysqli_query($cnx_cfdi2, $query7);
    if (!$runsql7) {//debug
        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $query7;
        die($mensaje);
    }
    while ($rowsql7 = mysqli_fetch_assoc($runsql7)){
        $TotalCompra = $rowsql7['SUM(Importe)'];
    }
    $registro = array(
        'Folio' => $folio2,
        'UUID' => $UUID,
        'Proveedor' => $RFCEmisor,
        // Agrega más campos según sea necesario
    );
    $registrosInsertados[] = $registro;
//inserta Compras
if($numRenglones!=0 && $rowsNum==0 && $tipoComprobante=='I'){
$queryP = "INSERT INTO ".$prefijobd."Compras(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,ProveedorNo_REN,ProveedorNo_RID,OficinaCompras_REN,OficinaCompras_RID,DiasCredito,Fecha,Vence,Estatus,Moneda,TipoCambio,Comentarios,Factura,Total,PagosSaldo,Retenido,Retencion,IVA,Impuesto) values
('$idPrincipal','$xfolio','$folio2','$time','1','Proveedores','".$Proveedor."','Oficinas','".$_POST['oficina']."','$diasCredito','$fechaTimbrado','$vencimiento','Proceso','$moneda',0,'$UUID','$folio','$total','$total','$importeRetenido','$Retencion','$IVA','$importeIVA2');";
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
    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
    $mensaje .= 'Consulta completa: ' . $queryP;
    die($mensaje);
}

echo '<script type="text/javascript">alert("Importacion Exitosa: Se creo la compra: '.$xfolio.'\nUUID: '.$UUID.'\nProveedor: '.$RFCEmisor.'.");</script>';
}//Imprime exito 
   }//fin foreach File[]
   echo '<table border="1">';
echo '<tr><th>Folio</th><th>UUID</th><th>Proveedor</th></tr>';
foreach ($registrosInsertados as $registro) {
    echo '<tr>';
    echo '<td>' . $registro['Folio'] . '</td>';
    echo '<td>' . $registro['UUID'] . '</td>';
    echo '<td>' . $registro['Proveedor'] . '</td>';
    // Agrega más celdas según sea necesario
    echo '</tr>';
}
echo '</table>';


 }
   //fclose($handle);//cierra el archivo


 //}


//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar XMLs Compras</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar XMLs Compras</h3><br />
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
        
        <div class="form-group">
                <label>Oficina:</label>
                <select class="form-control inputdefault" name="oficina" id="oficina" required >
                    <option value='0'>Selecciona Oficina</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsComp=1 ORDER BY Oficina";
            $runSQL = mysqli_query( $cnx_cfdi2, $resSQL);  
                while ($rowSQL = mysqli_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo "".$rowSQL['Serie']." / ".$rowSQL['Oficina']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>


        
   <div align="center">  
    <label>Selecciona los archivos XML:</label>
    <input type="file" name="file[]" required multiple/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

