<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idRem = $_GET["ID"];
$maxId=0;//se inicializa el ID maximo de awareim
$cont1=0;
$cont2=0;
$cont3=0;
date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"])){//cuando se presiona el votor enviar... 

    /*if($_POST['cliente'] == 0){
        echo "<script>alert('Es necesario seleccionar cliente');</script>";
        ob_start();
    }
    if($_POST['oficina'] == 0){
        echo "<script>alert('Es necesario seleccionar oficina');</script>";
        ob_start();
    }*/

 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea xml
  if($filename[1] == 'xml')
  {

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($_FILES["file"]["tmp_name"]);
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
        $FechaCargaOrigen='';
        $FechaCitaDestino='';
        $FechaCargaOrigen2='';
        $FechaCitaDestino2='';
        $xml2 = simplexml_load_file($target_file);
        
    foreach ($xml2->xpath('//Mercancias') as $Mercancias) {
        $CargoPorTasacion=$Mercancias['CargoPorTasacion'];
        $UnidadPeso=$Mercancias['UnidadPeso'];
        $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$UnidadPeso."';"; 
        $runsql = mysqli_query($cnx_cfdi2, $query);
        if (!$runsql) {//debug
            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query;
            die($mensaje);
        }
        while ($rowsql = mysqli_fetch_assoc($runsql)){
            $UnidadPeso = $rowsql['ID'];
        }
    }


    

        //UnidadPeso
        foreach ($xml2->xpath('//cartaporte:Mercancias//cartaporte:Mercancia') as $Merc) {
            $ClaveUnidad=$Merc['ClaveUnidad'];
            $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidad."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $ClaveUnidad = $rowsql['ID'];
            }

            $Cantidad=$Merc['Cantidad'];
            $Cantidad=str_replace(" ","",$Cantidad);

            $Descripcion=$Merc['Descripcion'];
            $Dimensiones=$Merc['Dimensiones'];
            $CveMaterialPeligroso=$Merc['CveMaterialPeligroso'];
            $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$CveMaterialPeligroso."';"; 
            $runsql2 = mysqli_query($cnx_cfdi2, $query2);
            if (!$runsql2) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query2;
                die($mensaje);
            }
            while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                $CveMaterialPeligroso = $rowsql2['ID'];
            }
            if($CveMaterialPeligroso != NULL){
                $item18 = 1;
            }else{
                $item18 = 0;
            }
            $Embalaje=$Merc['Embalaje'];
            $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$Embalaje."';"; 
            $runsql4 = mysqli_query($cnx_cfdi2, $query4);
            if (!$runsql4) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query4;
                die($mensaje);
            }
            while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                $Embalaje = $rowsql4['ID'];
            }

            $PesoEnKg=$Merc['PesoEnKg'];
			if($PesoEnKg>0 and $PesoEnKg<1){
                $PesoEnKg=1;
            }
            $DescripEmbalaje=$Merc['Unidad'];
            $PesoEnKg=str_replace(" ","",$PesoEnKg);
            $BienesTransp=$Merc['BienesTransp'];
            $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$BienesTransp."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $BienesTransp = $rowsql1['ID'];
            }
            $ClaveSTCC=$Merc['ClaveSTCC'];
            $UUIDComercioExt=$Merc['UUIDComercioExt'];
            $FraccionArancelaria=$Merc['FraccionArancelaria'];
            $query3 = "SELECT * FROM ".$prefijobd."c_FraccionArancelaria WHERE Codigo ='".$FraccionArancelaria."';"; 
            $runsql3 = mysqli_query($cnx_cfdi2, $query3);//busca el ID de la solicitud
            if (!$runsql3) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query3;
                die($mensaje);
            }
            while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                $FraccionArancelaria = $rowsql3['ID'];
            }
            $ValorMercancia=$Merc['ValorMercancia'];
            $Moneda=$Merc['Moneda'];
            //--
            
            $Pedimento=$xml2->xpath('//cartaporte:Mercancias//cartaporte:Pedimentos//@Pedimento');
            
            $IDOrigen=$xml2->xpath('//cartaporte:Mercancias//cartaporte:CantidadTransporta//@IDOrigen');

            $IDDestino=$xml2->xpath('//cartaporte:Mercancias//cartaporte:CantidadTransporta//@IDDestino');

            $PesoBruto=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoBruto');
            $PesoNeto=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoNeto');
            $PesoTara=$xml2->xpath('//cartaporte:Mercancias//cartaporte:DetalleMercancia//@PesoTara');

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
            $new2id = $basidgen;
    
    
                    $queryP = "INSERT INTO ".$prefijobd."remisionessub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, BL, Pedimento, Tipo, Peso,
                    BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                    ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                    MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, NumeroPedimento, UUIDComercioExt,PesoNeto,PesoTara,Dimensiones,ValorMercancia,Moneda) values
                    ('$new2id',0,'$time',0,0,'$item6','$time',0,'Tractosoft','Remisiones', '$idRem', '$Cantidad', '$DescripEmbalaje', '0', '', '0', '$PesoEnKg', '0', '0', 
                    '0', '0', '0', '0', '0', '$Descripcion', 'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp', 'c_TipoEmbalaje', '$Embalaje', 
                    '$item18', 'c_MaterialPeligroso', '$CveMaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria', '','$UUIDComercioExt','".$PesoNeto[$cont3]."','".$PesoTara[$cont3]."','$Dimensiones','$ValorMercancia','$Moneda');";
                    //$newquery=$queryP;
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
    
            $cont3++;
            }
    }


}	
                    
                echo "<script>alert('Importacion Exitosa');</script>";//Imprime exito
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
    
        
   <div align="center">  
    <label>Selecciona el archivo XML:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

