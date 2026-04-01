<?php
//base 
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

set_time_limit(3000);
//error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');
require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");
mysqli_select_db($cnx_cfdi2,$database_cfdi);
$prefijobd = $_GET["prefijodb"];//trae prefijo
$idProveedor = $_GET["idProveedor"];//trae prefijo


$Proveedor = "";
$diasCredito = "";

$validImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$validPdfExtensions = ['pdf'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $files = $_FILES['file'];
    $xmlFile = null;
    $uploadedFiles = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];

        if ($fileError !== UPLOAD_ERR_OK) {
            echo "Error al subir el archivo $fileName.<br>";
            continue;
        }

        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (strtolower($fileExtension) === 'xml') {
            if ($xmlFile !== null) {
                echo "<script>alert('Se recibió más de un archivo XML. Solo se permite uno');</script>";
                $rowsNum=1;
                ob_start();

            }
            $xmlFile = [
                'name' => $fileName,
                'tmp_name' => $fileTmpName,
                'size' => $fileSize,
            ];
        } elseif (in_array($fileExtension, $validPdfExtensions) || in_array($fileExtension, $validImageExtensions)) {
            $uploadedFiles[] = [
                'name' => $fileName,
                'tmp_name' => $fileTmpName,
                'size' => $fileSize,
            ];
        } else {
            echo "<script>alert('Formato de archivo no permitido: $fileName');</script>";
            $rowsNum=1;
            ob_start();

        }
    }

    if ($xmlFile === null) {
        echo "<script>alert('No se recibio ningún archivo XML.');</script>";
        $rowsNum=1;
        ob_start();

    }

    $uploadedFiles[] = $xmlFile;

    
    $queryProv = "SELECT ID, DiasCredito, Email FROM ".$prefijobd."Proveedores WHERE ID ='".$_GET['idProveedor']."';"; 
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
        $correoProveedor = $rowsqlProv['Email'];
    }

    //validacion de num documentos
    if (count($uploadedFiles) < 2) {
        echo "<script>alert('Se necesitan subir al menos 2 archivos.');</script>";
        ob_start();
        $rowsNum=1;
    }
    
    
    $xml2 = simplexml_load_file($xmlFile['tmp_name']);
    $ns = $xml2->getNamespaces(true);
    $xml2->registerXPathNamespace('c', $ns['cfdi']);
    $xml2->registerXPathNamespace('t', $ns['tfd']);

    foreach ($xml2->xpath('//cfdi:Comprobante') as $Comprobante) {
        
        $fecha = $Comprobante['Fecha'];
        $fecha=str_replace("T"," ",$fecha);
        $folioXML = $Comprobante['Folio'];
        $formaPago = $Comprobante['FormaPago'];
            
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
        
        $query5 = "SELECT ID FROM ".$prefijobd."Compras WHERE UUID ='".$UUID."';"; 
        $runsql5 = mysqli_query($cnx_cfdi2, $query5);
        $rowsNum=mysqli_num_rows($runsql5);
        if ($rowsNum>0) {//debug
            echo "<script>alert('Este UUID ya existe en una factura en el sistema');</script>";
            //die('<h1>Intenta otra vez con otro XML</h1>');
            ob_start();
        }
        
    }

//UnidadPeso

    foreach ($xml2->xpath('//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {

        $descripcion=$Concepto['Descripcion'];

        /*$folio = $matches[0];
        //echo "Folio encontrado: $folio<br>";
        $folio=str_replace("-","",$folio);*/
        
        $query4 = "SELECT Total, XFolio FROM ".$prefijobd."Compras WHERE ID ='".$_POST['ordenCompra']."' LIMIT 1;"; 
        $runsql4 = mysqli_query($cnx_cfdi2, $query4);
        if (!$runsql4) {//debug
            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query4;
            die($mensaje);
        }
        while ($rowsql4 = mysqli_fetch_assoc($runsql4)){
            $totalODC = $rowsql4['Total'];
			$folio = $rowsql4['XFolio'];

        }


    }

    //die($xfolio);

    //inserta evidencias, update etc
    if($numRenglones!=0 && $rowsNum==0){

        $queryUpdate = "UPDATE {$prefijobd}Compras SET UUID = '".$UUID."' WHERE ID = '".$_POST['ordenCompra']."';";

        // Ejecutar la consulta
        $runUpdate = mysqli_query($cnx_cfdi2, $queryUpdate);

        if ($runUpdate) {
            //echo "Registro insertado en la base de datos para: $fileName<br>";
        } else {
            echo "Error insertar UUID: " . mysqli_error($cnx_cfdi2) . "<br>";
        }

        $query9 = "UPDATE ".$prefijobd."Compras SET FechaCargaEvidencia = '$fechaTimbrado', Factura = '$serieF$folioXML' WHERE ID = ".$_POST['ordenCompra'].";"; 
        //die($query7);
        $runsql9 = mysqli_query($cnx_cfdi2, $query9);
        if (!$runsql9) {//debug
            $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query9;
            die($mensaje);
        }

        $mensaje = '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Notificación confirmación carga archivos</title>
        </head>
        <body>
            <p>Proveedor,</p>
            <p>Nos complace informarle que se ha recibido el XML con folio <strong>' . $serieF . $folioXML . ' con el UUID ' . $UUID . '</strong><br>
            de la orden de compra <strong>' . $folio . '</strong></p>

            <p>Saludos cordiales,</p>
            <p>Departamento de Compras<br></p>
        </body>
        </html>';



        $resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress, S.RutaDocumentosProveedores
                    FROM " . $prefijobd . "systemsettings S";
        $runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
        $rowSQL4 = mysqli_fetch_assoc($runSQL4);

        $v_host = $rowSQL4['OutgoingEmailHost'];
        $v_username = $rowSQL4['OutgoingEmailUserName'];
        $v_pass = $rowSQL4['OutgoingEmailPassword'];
        $v_port = $rowSQL4['OutgoingEmailPort'];
        $v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
        $uploadDir = $rowSQL4['RutaDocumentosProveedores'];

        $mail = new PHPMailer();

        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $v_host;
        $mail->SMTPAuth = true;
        $mail->Username = $v_username;
        $mail->Password = $v_pass;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;//$v_port;
        $mail->IsHTML(true);

        $mail->setFrom($v_mail_from); // REMITENTE

        // DESTINATARIO
        $array_correos = explode(";", $correoProveedor);
        foreach ($array_correos as $correo) {
            $mail->addAddress($correo);
        }
        //$mail->addCC('zxc@xcv.net');

        $mail->Subject = 'Notificación confirmacion carga archivos';
        $mail->Body = $mensaje;

        if (!$mail->send()) {
            echo "No se pudo enviar<br>";
            echo "ERROR de PHPMailer " . $mail->ErrorInfo . "<br>";
        } else {
            echo "<script>alert('Correo enviado. Puede cerrar esta pestaña')</script>";
        }

    }//Imprime EXITO

    $estatusPendiente = 'Pendiente';
    $currentDate = date('Y-m-d H:i:s');
    $tipoDocumento = 'Compras';
    $categoria = 'EvidenciasProveedor';

    if($numRenglones!=0 && $rowsNum==0){
        foreach ($uploadedFiles as $file) { // procesa evidencias
            $fileName = $file['name'];
            $fileContent = file_get_contents($file['tmp_name']);
            $uploadDir = str_replace('\\', '/', $uploadDir);
            $destinationPath = $uploadDir . basename($fileName);
            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                //echo "Archivo subido correctamente: $fileName<br>";

                $idEvidencia = obtenerSiguienteID($cnx_cfdi2);
                // Construir la consulta directamente
                $query = "INSERT INTO {$prefijobd}ComprasDocsProveedores 
                (ID, BASTIMESTAMP, RutaDocumento, FolioCompra_REN, FolioCompra_RID, FolioCompra_RMA, Documento_DOCTYPE, Estatus, FechaCarga) 
                VALUES (
                    '$idEvidencia', 
                    '$currentDate', 
                    '$destinationPath', 
                    '$tipoDocumento', 
                    '".$_POST['ordenCompra']."', 
                    '$categoria', 
                    '$fileName',
                    '$estatusPendiente',
                    '$fechaTimbrado'
                )";

                // Ejecutar la consulta
                $runP = mysqli_query($cnx_cfdi2, $query);

                if ($runP) {
                //echo "Registro insertado en la base de datos para: $fileName<br>";
                } else {
                echo "Error al insertar en la base de datos: " . mysqli_error($cnx_cfdi2) . "<br>";
                }

            } else {
                echo "Error al mover el archivo $fileName al directorio destino.<br>";
            }
        }
    }
} else {
    //echo "No se recibieron archivos.<br>";
}

?>
<!DOCTYPE html>  
<html>  
 <head>  
  <title>Importar Archivos Proveedores</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    #resultadoBusqueda {
        border: 1px solid #ccc;
        max-height: 150px;
        overflow-y: auto;
        position: absolute;
        z-index: 1000;
        width: 100%;
        background-color: white;
    }
    #resultadoBusqueda div {
        padding: 8px;
        cursor: pointer;
    }
    #resultadoBusqueda div:hover {
        background-color: #f0f0f0;
    }
    .autocomplete-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px; 
    }
</style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
 </head>  
 <body>  
  <h3 align="center">Importar Archivos Proveedores</h3><br />

  <h4 align="center">Favor de incluir en la carga el XML, PDF de la factura, asi como la evidencia firmada</h4><br />
  <form method="post" autocomplete="off" enctype="multipart/form-data">
    <div class="form-group">

   <div align="center">  

    <div class="form-group autocomplete-wrapper">
            <label>Buscar Orden de Compra:</label>
            <input align="center" type="text" id="buscar_1" class="form-control" placeholder="Escribe Folio de Orden de Compra o Compra">
            <div id="resultadoBusqueda"></div>
            <input type="hidden" id="ordenCompra" name="ordenCompra">
            <input type="hidden" name="prefijodb" value="<?php echo $prefijobd; ?>">
    </div>
   
    </div>  
        
                

   <div align="center">  
    <label>Selecciona los archivos:</label>
    <input type="file" name="file[]" required multiple/>
    <br />
    <input type="submit" name="submit" value="Importar" class="btn btn-info" />
   </div>
  </form>
   <script>
    $(document).ready(function () {
        $('#buscar_1').on('keyup', function () {
            var query = $(this).val();
            var prefijodb = "<?php echo $prefijobd; ?>";
            var idProveedor = "<?php echo $idProveedor; ?>";

            if (query.length > 1) {
                $.ajax({
                    url: "buscarOrdenesCompras.php",
                    method: "GET",
                    data: { 
                        q: query, 
                        prefijodb: prefijodb,
                        idProveedor: idProveedor
                     },
                    success: function (data) {
                        $('#resultadoBusqueda').fadeIn().html(data);
                    }
                });
            } else {
                $('#resultadoBusqueda').fadeOut();
            }
        });

        
        $(document).on('click', '.sugerencia', function () {
            var id = $(this).data('id');
            var texto = $(this).text();

            $('#buscar_1').val(texto);
            $('#ordenCompra').val(id);
            $('#resultadoBusqueda').fadeOut();
        });

    
        $(document).click(function(e) {
            if (!$(e.target).closest('#buscar_1, #resultadoBusqueda').length) {
                $('#resultadoBusqueda').fadeOut();
            }
        });
    });
    </script>

 </body>  
</html>
