<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');
require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$idRem = $_GET["ID"];
/*require_once('cnx_cfdi.php');

mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");*/

$prefijobd = $_GET["prefijodb"];//trae prefijo
set_time_limit(300);
$maxId=0;//se inicializa el ID maximo de awareim
date_default_timezone_set("America/Mexico_City");

$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{

 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea xml
  if($filename[1] == 'xml')
  {

    $target_dir = "C:\\xampp\\tmp\\";
    $target_file = $target_dir . basename($_FILES["file"]["tmp_name"]);

    /*Variables a utilizar*/
        /*xxxx*/
        $ID_Carga_WTMS='';//mercancias(embalaje)
        $BienesTransp='';
        $Descripcion='';
        $CantidadTipo='';
        $CantidadItem='';
        $ClaveUnidad='';
        $NumTotalMercancias='';
        $MaterialPeligroso='';
        $CveMaterialPeligroso='';
        $SubTipoRem='';
        $DescripEmbalaje='';
        $PesoEnKg='';
        $FechaCargaOrigen='';
        $FechaCitaDestino='';
        $FechaCargaOrigen2='';
        $FechaCitaDestino2='';
        $FraccionArancelaria='';
        $UUIDComercioExt='';
        $NumeroFactura='';
        $Unidad='';
        $ValorMercancia='';
        $Moneda='';
        $Unidad='';
        $idOrigen='';
        $idDestino='';

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




    $xml2 = simplexml_load_file($target_file);
    //die($CalleDestinatario."\n".$EstadoDestinatario."\n".$PaisDestinatario."\n".$CodigoPostalDestinatario."\n".$CalleRemitente."\n".$EstadoRemitente."\n".$PaisRemitente."\n".$CodigoPostalRemitente."\n");
    foreach ($xml2->Mercancias->Mercancia as $Mercancia) {
//die('sientra');
            /*if ($key == 'ID_Carga_WTMS'){
                        Mercancias
                $ID_Carga_WTMS=$Mercancia->ID_Carga_WTMS;
            }*/
				
                $BienesTransp=$Mercancia->BienesTransp;
				if($BienesTransp==''){
					$BienesTransp='47131500';
				}
				$BienesTransp = explode(",", $BienesTransp);
				$BienesTransp2 = $BienesTransp[0];
                $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$BienesTransp2."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $BienesTransp2 = $rowsql1['ID'];
                    }
            
                $Descripcion=$Mercancia->Descripcion;
                $idOrigen=$Mercancia->IdOrigen;
                $idDestino=$Mercancia->IdDestino;
            
            /*if ($key == 'FechaCargaOrigen'){
                $FechaCargaOrigen=$value;
                $FechaCargaOrigen2=str_replace("T"," ",$FechaCargaOrigen);
            }
            if ($key == 'FechaCitaDestino'){
                $FechaCitaDestino=$value;
                $FechaCitaDestino2=str_replace("T"," ",$FechaCitaDestino);
            }*/

                $CantidadTipo=$Mercancia->CantidadTipo;
            
                $CantidadItem=$Mercancia->Cantidad;

                $ClaveUnidad=$Mercancia->ClaveUnidad;
                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidad."';"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $ClaveUnidad = $rowsql['ID'];
                    }


                //$NumTotalMercancias=$Mercancia->NumTotalMercancias;


                //$MaterialPeligroso=$Mercancia->MaterialPeligroso;

                $PesoEnKg=$Mercancia->PesoEnKg;
				if($PesoEnKg<0){
					$PesoEnKg=1;
				}

                /*$CveMaterialPeligroso=$Mercancia->CveMaterialPeligroso;
                $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$CveMaterialPeligroso."';"; 
                $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                if (!$runsql2) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query2;
                    die($mensaje);
                }
                while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                    $CveMaterialPeligroso = $rowsql2['ID'];
                }*/
            /*if ($key == 'Embalaje'){
                $Embalaje=$value;
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
            }*/

                $DescripEmbalaje=$Mercancia->DescripEmbalaje;
                $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$DescripEmbalaje."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                if (!$runsql4) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query4;
                    die($mensaje);
                }
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $DescripEmbalaje = $rowsql4['ID'];

                }
                if($DescripEmbalaje=='No'){$DescripEmbalaje='';}
				$ValorMercancia=$Mercancia->ValorMercancia;

            /*if ($key == 'ValorMercancia'){
                $ValorMercancia=$value;
            }
            if ($key == 'Moneda'){
                $Moneda=$value;
            }*/
            /*if ($key == 'RFCDestinatario'){
                $RFCDestinatario=$value;
            }
            if ($key == 'SubTipoRem'){
                $SubTipoRem=$value;
            }*/
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


                $queryP = "INSERT INTO ".$prefijobd."remisionessub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,Peso,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad,
                Descripcion,MaterialPeligrosoC, MaterialPeligroso_REN,MaterialPeligroso_RID,ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID,ValorMercancia,Embalaje,Moneda,PesoNeto,PesoTara) values
                ('$new2id',0,'$time',0,0,'".$PesoEnKg."','$time',0,'Tractosoft','Remisiones', '$idRem', '$CantidadItem', '$Descripcion','0', 'c_MaterialPeligroso', '$CveMaterialPeligroso',
                'c_ClaveUnidadPeso', '$ClaveUnidad','c_ClaveProdServCP','$BienesTransp2', 'c_TipoEmbalaje', '$DescripEmbalaje','0','Piezas','MXN','0','0');";
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
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $queryP;
                die($mensaje);
            }
    }//finEmbalaje
}
 }  
   //fclose($handle);//cierra el archivo
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
    <div class="form-group">

        
   <div align="center">  
    <label>Selecciona el archivo XML:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div></div>
  </form>
 </body>  
</html>

