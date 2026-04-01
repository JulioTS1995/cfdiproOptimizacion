<?php
set_time_limit(300);
error_reporting(0);
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idRem = $_GET["ID"];

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{
 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
  if($filename[1] == 'csv')
  {
	$queryDelete = "DELETE FROM ".$prefijobd."FacturasSub WHERE FolioSub_RID = '".$idRem."';";//borra los registros previos 
    $runsqlDelete = mysqli_query($cnx_cfdi2, $queryDelete);
	if (!$runsqlDelete) {//debug
        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $queryDelete;
        die($mensaje);
    }
   $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
   fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
   while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos,
   {
	   
                
                $item1 = mysqli_real_escape_string($cnx_cfdi2, $data[0]); 
                $item2 = mysqli_real_escape_string($cnx_cfdi2, $data[1]);//se empiezan a leer las columnas
                $item3 = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                $item4 = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
                $item5 = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
                $item6 = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
                $item7 = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
                $item8 = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
                $item9 = mysqli_real_escape_string($cnx_cfdi2, $data[8]);
                $item10 = mysqli_real_escape_string($cnx_cfdi2, $data[9]);
                $item11 = mysqli_real_escape_string($cnx_cfdi2, $data[10]);
                $item12 = mysqli_real_escape_string($cnx_cfdi2, $data[11]);
                $item13 = mysqli_real_escape_string($cnx_cfdi2, $data[12]);
                $item14 = mysqli_real_escape_string($cnx_cfdi2, $data[13]);
                $item15 = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$item15."';"; 
                $runsql = mysqli_query($cnx_cfdi2, $query);//busca el ID de la solicitud
                if (!$runsql) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query;
                    die($mensaje);
                }
                while ($rowsql = mysqli_fetch_assoc($runsql)){
                    $ClaveUnidadPeso = $rowsql['ID'];
                }
                $item16 = mysqli_real_escape_string($cnx_cfdi2, $data[15]);
                $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$item16."';"; 
                $runsql1 = mysqli_query($cnx_cfdi2, $query1);//busca el ID de la solicitud
                if (!$runsql1) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query1;
                    die($mensaje);
                }
                while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                    $CodigoProdServ = $rowsql1['ID'];
                }
                $item17 = mysqli_real_escape_string($cnx_cfdi2, $data[16]);
                $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$item17."';"; 
                $runsql4 = mysqli_query($cnx_cfdi2, $query4);//busca el ID de la solicitud
                if (!$runsql4) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query4;
                    die($mensaje);
                }
                while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                    $idTE = $rowsql4['ID'];
                }
                //$item18 = mysqli_real_escape_string($cnx_cfdi2, $data[17]);
                $item19 = mysqli_real_escape_string($cnx_cfdi2, $data[17]);
                $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$item19."';"; 
                $runsql2 = mysqli_query($cnx_cfdi2, $query2);//busca el ID de la solicitud
                if (!$runsql2) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query2;
                    die($mensaje);
                }
                while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                    $MaterialPeligroso = $rowsql2['ID'];
                }
                ///
                if($item19 != NULL){
                    $item18 = 1;
                }else{
                    $item18 = 0;
                }
                
                $item20 = mysqli_real_escape_string($cnx_cfdi2, $data[18]);
                $query3 = "SELECT * FROM ".$prefijobd."c_FraccionArancelaria WHERE Codigo ='".$item20."';"; 
                $runsql3 = mysqli_query($cnx_cfdi2, $query3);//busca el ID de la solicitud
                if (!$runsql3) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query3;
                    die($mensaje);
                }
                while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                    $FraccionArancelaria = $rowsql3['ID'];
                }
                $item21 = mysqli_real_escape_string($cnx_cfdi2, $data[19]);
                $item22 = mysqli_real_escape_string($cnx_cfdi2, $data[20]);
                $item23 = mysqli_real_escape_string($cnx_cfdi2, $data[21]);//
                $item24 = mysqli_real_escape_string($cnx_cfdi2, $data[22]);
                $item25 = mysqli_real_escape_string($cnx_cfdi2, $data[23]);
                $item26 = mysqli_real_escape_string($cnx_cfdi2, $data[24]);
                $item27 = mysqli_real_escape_string($cnx_cfdi2, $data[25]);
				
				if($item24==NULL){
					$item24='0';
				}
				if($item25==NULL){
					$item25='MXN';
				}
				if($item26==NULL){
					$item26='0';
				}
				if($item27==NULL){
					$item27='0';
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
				//inserta solicitudessub
                $queryP = "INSERT INTO ".$prefijobd."facturassub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN, FolioSub_RID, Cantidad, Embalaje, BL, Pedimento, Tipo, Peso,
                 BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra, Descripcion, ClaveUnidadPeso_REN, ClaveUnidadPeso_RID, 
                 ClaveProdServCP_REN, ClaveProdServCP_RID, TipoEmbalaje_REN, TipoEmbalaje_RID, MaterialPeligrosoC, MaterialPeligroso_REN, 
                 MaterialPeligroso_RID, FraccionArancelaria_REN, FraccionArancelaria_RID, NumeroPedimento, UUIDComercioExt,Dimensiones,ValorMercancia,Moneda,PesoNeto,PesoTara) values
                 ($newid,0,'$time',0,0,'$item6','$time',0,'Tractosoft','Factura', '$idRem', '$item1', '$item2', '$item3', '$item4', '$item5', '$item6', '$item7', '$item8', 
                 '$item9', '$item10', '$item11', '$item12', '$item13', '$item14', 'c_ClaveUnidadPeso', '$ClaveUnidadPeso','c_ClaveProdServCP','$CodigoProdServ', 'c_TipoEmbalaje', '$idTE', 
                 '$item18', 'c_MaterialPeligroso', '$MaterialPeligroso', 'c_FraccionArancelaria', '$FraccionArancelaria', '$item21','$item22','$item23','$item24','$item25','$item26','$item27');";
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
