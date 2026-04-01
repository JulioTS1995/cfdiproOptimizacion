<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
set_time_limit(300);
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
//$idRem = $_GET["ID"];//trae id remision
$remisionID=0;
$maxId=0;//se inicializa el ID maximo de awareim
$cont=0;
date_default_timezone_set("America/Mexico_City");
$time = date('Y-m-d H:i:s');//CURRENT_TIME

if(isset($_POST["submit"]))//cuando se presiona el votor enviar... 
{
    if($_POST['cliente'] == 0){
        echo "<script>alert('Es necesario seleccionar cliente');</script>";
        ob_start();
    }
    if($_POST['oficina'] == 0){
        echo "<script>alert('Es necesario seleccionar oficina');</script>";
        ob_start();
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

                $remisionID = $basidgen;
 if($_FILES['file']['name'])
 {
  $filename = explode(".", $_FILES['file']['name']);//verifica que sea csv
  if($filename[1] == 'csv')
  {
   $handle = fopen($_FILES['file']['tmp_name'], "r"); //abre el archivo
   fgets($handle);//lee la primera linea, y no hace nada (se salta el encabezado)
   while(($data = fgetcsv($handle,10000,","))!==FALSE)//empieza a leer los datos,
   {
       if($cont==0){
        $rem = mysqli_real_escape_string($cnx_cfdi2, $data[0]); //se empiezan a leer las columnas del CSV
        $remRFC = mysqli_real_escape_string($cnx_cfdi2, $data[1]);
        /*$citaCarga = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
                    $citaCarga2 = date("d/m/Y", strtotime($citaCarga));
					$citaCarganew = date("Y-m-d", strtotime($citaCarga2));*/
        $remCalle = mysqli_real_escape_string($cnx_cfdi2, $data[2]);
        $remNum1 = mysqli_real_escape_string($cnx_cfdi2, $data[3]);
        $remNum2 = mysqli_real_escape_string($cnx_cfdi2, $data[4]);
		
        $remEstado = mysqli_real_escape_string($cnx_cfdi2, $data[8]);
		//$remEstado=str_replace(" ","''",$remEstado);
        $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$remEstado."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $remEstado = $rowsql['ID'];
            }
        $remLocalidad = mysqli_real_escape_string($cnx_cfdi2, $data[6]);
		//$remLocalidad=str_replace(" ","''",$remLocalidad);

        $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$remLocalidad."' AND Estado_RID ='".$remEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remLocalidad = $rowsql1['ID'];
            }
        $remMunicipio = mysqli_real_escape_string($cnx_cfdi2, $data[7]);
		//$remMunicipio=str_replace(" ","''",$remMunicipio);
        $query1 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE ClaveMunicipio ='".$remMunicipio."' AND Estado_RID ='".$remEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remMunicipio = $rowsql1['ID'];
            }

        $remCP = mysqli_real_escape_string($cnx_cfdi2, $data[9]);
		        $remColonia = mysqli_real_escape_string($cnx_cfdi2, $data[5]);
				//$remColonia=str_replace(" ","''",$remColonia);

        $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$remCP."' AND ClaveColonia ='".$remColonia."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $remColonia = $rowsql1['ID'];
            }
        $remPais = mysqli_real_escape_string($cnx_cfdi2, $data[10]);
        $des = mysqli_real_escape_string($cnx_cfdi2, $data[11]);
        $desRFC = mysqli_real_escape_string($cnx_cfdi2, $data[12]);
        /*$citaDescarga = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
            $citaDescarga2 = date("d/m/Y", strtotime($citaDescarga));
            $citaDescarganew = date("Y-m-d", strtotime($citaDescarga2));*/
        $desCalle = mysqli_real_escape_string($cnx_cfdi2, $data[13]);
        $desNum1 = mysqli_real_escape_string($cnx_cfdi2, $data[14]);
        $desNum2 = mysqli_real_escape_string($cnx_cfdi2, $data[15]);

        $desEstado = mysqli_real_escape_string($cnx_cfdi2, $data[19]);
		//$desEstado=str_replace(" ","''",$desEstado);
        $query = "SELECT * FROM ".$prefijobd."Estados WHERE abreviacion ='".$desEstado."';"; 
            $runsql = mysqli_query($cnx_cfdi2, $query);
            if (!$runsql) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $query;
                die($mensaje);
            }
            while ($rowsql = mysqli_fetch_assoc($runsql)){
                $desEstado = $rowsql['ID'];
            }
        $desLocalidad = mysqli_real_escape_string($cnx_cfdi2, $data[17]);
		//$desLocalidad=str_replace(" ","''",$desLocalidad);

        $query1 = "SELECT * FROM ".$prefijobd."c_Localidad WHERE ClaveLocalidad ='".$desLocalidad."' AND Estado_RID ='".$desEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desLocalidad = $rowsql1['ID'];
            }
        $desMunicipio = mysqli_real_escape_string($cnx_cfdi2, $data[18]);
		//$desMunicipio=str_replace(" ","''",$desMunicipio);
        $query1 = "SELECT * FROM ".$prefijobd."c_Municipio WHERE ClaveMunicipio ='".$desMunicipio."' AND Estado_RID ='".$desEstado."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desMunicipio = $rowsql1['ID'];
            }

        $desCP = mysqli_real_escape_string($cnx_cfdi2, $data[20]);
		        $desColonia = mysqli_real_escape_string($cnx_cfdi2, $data[16]);
				//$desColonia=str_replace(" ","''",$desColonia);

        $query1 = "SELECT * FROM ".$prefijobd."c_Colonia WHERE CodigoPostal = '".$desCP."' AND ClaveColonia ='".$desColonia."';"; 
            $runsql1 = mysqli_query($cnx_cfdi2, $query1);
            if (!$runsql1) {//debug
                $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
                $mensaje .= 'Consulta completa: ' . $query1;
                die($mensaje);
            }
            while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                $desColonia = $rowsql1['ID'];
            }
        $desPais = mysqli_real_escape_string($cnx_cfdi2, $data[21]);
        $idOrigen = mysqli_real_escape_string($cnx_cfdi2, $data[38]);
        $idDestino = mysqli_real_escape_string($cnx_cfdi2, $data[39]);
		if($idOrigen==''){//VALIDA QUE TENGA UN VALOR. DE LO CONTRARIO SE INICIALIZA
			$idOrigen='OR999998';
		}
		if($idDestino==''){
			$idDestino='DE999999';
		}
       }


                $cantidad = mysqli_real_escape_string($cnx_cfdi2, $data[22]); //se empiezan a leer las columnas del CSV
                $embalaje = mysqli_real_escape_string($cnx_cfdi2, $data[23]);
                $peso = mysqli_real_escape_string($cnx_cfdi2, $data[24]);
                $descripcion = mysqli_real_escape_string($cnx_cfdi2, $data[25]);
                $ClaveUnidadPeso = mysqli_real_escape_string($cnx_cfdi2, $data[26]);
				//$ClaveUnidadPeso=str_replace(" ","''",$ClaveUnidadPeso);

                $query = "SELECT * FROM ".$prefijobd."c_ClaveUnidadPeso WHERE ClaveUnidad ='".$ClaveUnidadPeso."';"; 
                    $runsql = mysqli_query($cnx_cfdi2, $query);
                    if (!$runsql) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query;
                        die($mensaje);
                    }
                    while ($rowsql = mysqli_fetch_assoc($runsql)){
                        $ClaveUnidadPeso = $rowsql['ID'];
                    }
                $CodigoProdServ = mysqli_real_escape_string($cnx_cfdi2, $data[27]);
				//$CodigoProdServ=str_replace(" ","''",$CodigoProdServ);
                $query1 = "SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ClaveProducto ='".$CodigoProdServ."';"; 
                    $runsql1 = mysqli_query($cnx_cfdi2, $query1);
                    if (!$runsql1) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query1;
                        die($mensaje);
                    }
                    while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
                        $CodigoProdServ = $rowsql1['ID'];
                    }
                $idTE = mysqli_real_escape_string($cnx_cfdi2, $data[28]);
				//$idTE=str_replace(" ","''",$idTE);
                    $query4 = "SELECT * FROM ".$prefijobd."c_TipoEmbalaje WHERE ClaveDesignacion ='".$idTE."';"; 
                    $runsql4 = mysqli_query($cnx_cfdi2, $query4);
                    if (!$runsql4) {//debug
                        $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $query4;
                        die($mensaje);
                    }
                    while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
                        $idTE = $rowsql4['ID'];
                    }
                $MaterialPeligroso = mysqli_real_escape_string($cnx_cfdi2, $data[29]);
				//$MaterialPeligroso=str_replace(" ","''",$MaterialPeligroso);
                $query2 = "SELECT * FROM ".$prefijobd."c_MaterialPeligroso WHERE ClaveMaterialPeligroso ='".$MaterialPeligroso."';"; 
                $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                if (!$runsql2) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query2;
                    die($mensaje);
                }
                while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                    $MaterialPeligroso = $rowsql2['ID'];
                }
                ///
                if($MaterialPeligroso != NULL){
                    $item18 = 1;
                }else{
                    $item18 = 0;
                }
                $FraccionArancelaria = mysqli_real_escape_string($cnx_cfdi2, $data[30]);
				//$FraccionArancelaria=str_replace(" ","''",$FraccionArancelaria);
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
                $item10 = mysqli_real_escape_string($cnx_cfdi2, $data[31]);
                $item11 = mysqli_real_escape_string($cnx_cfdi2, $data[32]);
                $item12 = mysqli_real_escape_string($cnx_cfdi2, $data[33]);
                $item13 = mysqli_real_escape_string($cnx_cfdi2, $data[34]);
                $item14 = mysqli_real_escape_string($cnx_cfdi2, $data[35]);
                $item15 = mysqli_real_escape_string($cnx_cfdi2, $data[36]);
                $item16 = mysqli_real_escape_string($cnx_cfdi2, $data[37]);
                
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

                $newid = $basidgen;
				//inserta remisionessub (Embalaje)
$queryP = "INSERT INTO ".$prefijobd."remisionessub(ID,Mt3,Modificado,PesoVolumen,PesoEstimado,PesoCobrar,BASTIMESTAMP,BASVERSION,Documentador,FolioSub_REN,FolioSub_RID,Cantidad,Embalaje,Peso,Descripcion,ClaveUnidadPeso_REN,ClaveUnidadPeso_RID,
                ClaveProdServCP_REN,ClaveProdServCP_RID,TipoEmbalaje_REN,TipoEmbalaje_RID,MaterialPeligroso_REN,MaterialPeligroso_RID,FraccionArancelaria_REN,FraccionArancelaria_RID,
                NumeroPedimento,UUIDComercioExt,MaterialPeligrosoC,BKG, Movimientos, Sello, Referencia, Orden, Proveedor, OrdenCompra,Dimensiones,ValorMercancia,Moneda,PesoNeto,PesoTara) values
                 ('$newid',0,'$time','0','0','0','$time','0','Tractosoft','Remisiones', '$remisionID', '$cantidad', '$embalaje', '$peso', '$descripcion','c_ClaveUnidadPeso','$ClaveUnidadPeso','c_ClaveProdServCP','$CodigoProdServ',
                'c_TipoEmbalaje','$idTE','c_MaterialPeligroso','$MaterialPeligroso','c_FraccionArancelaria','$FraccionArancelaria','$item10','$item11','$item18','0','0','0','0','0','0','0'
                ,'$item12','$item13','$item14','$item15','$item16');";
				 //$newquery=$queryP;
                 $cont++;
                $newquery=str_replace("''","NULL",$queryP);
				$newquery=str_replace("' '","NULL",$newquery);
				//die($newquery);
                $runP= mysqli_query($cnx_cfdi2, $newquery);
                if (!$runP) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $newquery;
                    die($mensaje);
                }
            }
   }

  }
                      /**/
                      $query5 = "SELECT * FROM ".$prefijobd."Oficinas WHERE ID ='".$_POST['oficina']."';"; 
                      $runsql5 = mysqli_query($cnx_cfdi2, $query5);
                      if (!$runsql5) {//debug
                          $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query5;
                          die($mensaje);
                      }
                      while ($rowsql5 = mysqli_fetch_assoc($runsql5)){
                          $serie = $rowsql5['Serie'];
                          $usocfdi33_ren = $rowsql5['uso33_REN'];
                          $usocfdi33_rid = $rowsql5['uso33_RID'];
                          $metodo33_ren = $rowsql5['metodo33_REN'];
                          $metodo33_rid = $rowsql5['metodo33_RID'];
                          $forma33_ren = $rowsql5['forma33_REN'];
                          $forma33_rid = $rowsql5['forma33_RID'];
                      }
                      /**/
                      /**/
                      $query6 = "SELECT max(Folio) FROM ".$prefijobd."Remisiones WHERE Oficina_RID ='".$_POST['oficina']."';"; 
                      //die($query6);
                      $runsql6 = mysqli_query($cnx_cfdi2, $query6);
                      if (!$runsql6) {//debug
                          $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query6;
                          die($mensaje);
                      }
                      while ($rowsql6 = mysqli_fetch_assoc($runsql6)){
                          $folio = $rowsql6['max(Folio)'];
                      }
                      $query7 = "SELECT * FROM ".$prefijobd."Rutas WHERE ID ='".$_POST['ruta']."';"; 
                      $runsql7 = mysqli_query($cnx_cfdi2, $query7);
                      if (!$runsql7) {//debug
                          $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                          $mensaje .= 'Consulta completa: ' . $query7;
                          die($mensaje);
                      }
                      while ($rowsql7 = mysqli_fetch_assoc($runsql7)){
                          $kmsRecorridos = $rowsql7['Kms'];
                      }

                      $query = "SELECT * FROM ".$prefijobd."SystemSettings;"; 
                        $runsql = mysqli_query($cnx_cfdi2, $query);
                        if (!$runsql) {//debug
                            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                            $mensaje .= 'Consulta completa: ' . $query;
                            die($mensaje);
                        }
                        while ($rowsql = mysqli_fetch_assoc($runsql)){
                            $fchaVigenciaCSD = $rowsql['FchaVencimientoSellos'];
                        }

                        

                      $folio=$folio+1;
                      $xfolio="".$serie."".$folio."";
                      //die($xfolio);
                      /**/
  
  
                  //inserta remisiones
                  $queryP = "INSERT INTO ".$prefijobd."remisiones(ID,XFolio,Folio,BASTIMESTAMP,BASVERSION,CargoACliente_REN,CargoACliente_RID,Oficina_REN,Oficina_RID,Remitente,RemitenteRFC,RemitenteCalle,RemitenteNumExt,
                  RemitenteNumInt,RemitentePais,RemitenteCodigoPostal,Destinatario,DestinatarioRFC,DestinatarioCalle,DestinatarioNumExt,DestinatarioNumInt,DestinatarioPais,DestinatarioCodigoPostal,RemitenteColonia_REN,
                  RemitenteColonia_RID,DestinatarioColonia_REN,DestinatarioColonia_RID,RemitenteEstado_REN,RemitenteEstado_RID,DestinatarioEstado_REN,DestinatarioEstado_RID,Ruta_REN,Ruta_RID,Unidad_REN,Unidad_RID,
                  Operador_REN,Operador_RID,Creado,feMetodoPago,Moneda,CitaCarga,DestinatarioCitaCarga,RemitenteLocalidad2_REN,RemitenteLocalidad2_RID,DestinatarioLocalidad2_REN,DestinatarioLocalidad2_RID,
                  RemitenteMunicipio_REN,RemitenteMunicipio_RID,DestinatarioMunicipio_REN,DestinatarioMunicipio_RID,CodigoOrigen,CodigoDestino,DistanciaRecorrida,FchaVencimientoCSD, usocfdi33_REN, usocfdi33_RID, metodopago33_REN, metodopago33_RID, formapago33_REN, formapago33_RID) values
                   ('$remisionID','$xfolio','$folio','$time','1','Clientes','".$_POST['cliente']."','Oficinas','".$_POST['oficina']."','$rem','$remRFC','$remCalle','$remNum1','$remNum2','$remPais',
                   '$remCP','$des','$desRFC','$desCalle','$desNum1','$desNum2','$desPais','$desCP','c_Colonia','$remColonia','c_Colonia',
                   '$desColonia','Estados','$remEstado','Estados','$desEstado','Rutas','".$_POST['ruta']."','Unidades','".$_POST['unidad']."',
                   'Operadores','".$_POST['operador']."','$time','NO INDENTIFICADO','PESOS','$time','$time','c_Localidad','$remLocalidad','c_Localidad','$desLocalidad','c_Municipio','$remMunicipio','c_Municipio','$desMunicipio','$idOrigen','$idDestino','$kmsRecorridos','$fchaVigenciaCSD', '$usocfdi33_ren', $usocfdi33_rid, '$metodo33_ren',$metodo33_rid, '$forma33_ren',$forma33_rid);";
                   //$newquery=$queryP; 
                  $newquery=str_replace("''","NULL",$queryP);
				  $newquery=str_replace("' '","NULL",$newquery);
                  //die($newquery);
                  $runP= mysqli_query($cnx_cfdi2, $newquery);
                  if (!$runP) {//debug
                      $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                      $mensaje .= 'Consulta completa: ' . $newquery;
                      die($mensaje);
                  }
                  fclose($handle);//cierra el archivo
				  if ($runP) {
                  echo "<script>alert('Importacion Exitosa, se creo la remision ".$xfolio."');</script>";//Imprime exito
				  }
 }


//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar Carta Porte de Traslado</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Importar Carta Porte de Traslado</h3><br />
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
                <label>Cliente:</label>
                <select class="form-control inputdefault" name="cliente" id="cliente" required aria-required="true">
                    <option value='0'>Selecciona Cliente</option>
                <?php
        
        require_once('cnx_cfdi.php');
        mysql_select_db($database_cfdi, $cnx_cfdi);
                $resSQL = "SELECT ID,RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Estatus = 'Activo' ORDER BY Cliente";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Cliente']; ?></option>
                <?php
                
                }
                ?>
                </select></div>
        
        <div class="form-group">
                <label>Oficina:</label>
                <select class="form-control inputdefault" name="oficina" id="oficina" required aria-required="true">
                    <option value='0'>Selecciona Oficina</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Serie, Oficina FROM ".$prefijobd."Oficinas WHERE EsRem = 1 ORDER BY Oficina";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo "".$rowSQL['Serie']." / ".$rowSQL['Oficina']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Ruta:</label>
                <select class="form-control inputdefault" name="ruta" id="ruta">
                    <option value='0'>Selecciona Ruta</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Ruta as Ruta FROM ".$prefijobd."Rutas ORDER BY Ruta";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Ruta']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Operador:</label>
                <select class="form-control inputdefault" name="operador" id="operador">
                    <option value='0'>Selecciona Operador</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Operador FROM ".$prefijobd."Operadores WHERE Estatus = 'Activo' ORDER BY Operador";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Operador']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>

        <div class="form-group">
                <label>Unidad:</label>
                <select class="form-control inputdefault" name="unidad" id="unidad">
                    <option value='0'>Selecciona Unidad</option>
                <?php
        
        //require_once('cnx_cfdi.php');
        //mysql_select_db($database_cfdi, $cnx_cfdi);
                    $resSQL = "SELECT ID,Unidad FROM ".$prefijobd."Unidades WHERE Activa = 'Activa' ORDER BY Unidad";
            $runSQL = mysql_query($resSQL, $cnx_cfdi);  
                while ($rowSQL = mysql_fetch_assoc($runSQL))
                {
                    ?>
                    <option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Unidad']; ?></option>
                <?php
                
                }
                ?>
                </select>
        </div>
        
        <!--<div class="form-group">
					<label>Fecha de Carga:</label>
					<input type="date" name="fechaC" id="fechaC" class="form-control">
					<p class="help-block text-danger"></p>
		</div>-->
        <!--<div class="form-group">
					<label>Fecha de Descarga:</label>
					<input type="date" name="fechaD" id="fechaD" class="form-control">
					<p class="help-block text-danger"></p>
		</div>-->
        
   <div align="center">  
    <label>Selecciona el archivo CSV:</label>
    <input type="file" name="file" required/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

