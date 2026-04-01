<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");
require_once('cnx_cfdi3.php');


$pdfURL = isset($_GET['pdf']) ? $_GET['pdf'] : '';
$xmlURL = isset($_GET['xml']) ? $_GET['xml'] : '';
$prefijodb = isset($_GET['prefijo']) ? $_GET['prefijo'] : '';
$correoCliente = isset($_GET['correo']) ? $_GET['correo'] : '';
$idDocumento = isset($_GET['iddocumento']) ? $_GET['iddocumento'] : '';
$tipoT = isset($_GET['tipoT']) ? $_GET['tipoT'] : 'factura';

if (empty($prefijodb)) {
    die("Error: prefijo de base de datos no recibido. PREFIJO: " .$prefijodb);
}

//Buscar datos de correo que envìa notificaión
$tabla = $cnx_cfdi3->real_escape_string($prefijodb . 'systemsettings');
$sqlSystems = "SELECT OutgoingEmailHost, OutgoingEmailUserName, OutgoingEmailPassword, OutgoingEmailPort, OutgoingEmailFromAddress, xmldir, Servidor FROM `$tabla`";
$resSQL001 = $cnx_cfdi3->prepare($sqlSystems); 
if (!$resSQL001) {
    die("Error en la preparacion de la consultaMail: " . $cnx_cfdi3->error);    
}

if (!$resSQL001->execute()) {
	$mensaje  = 'Consulta no valida: ' . $resSQL001->error . "\n";
    die($mensaje);
}	
$resSQL001->store_result();
$resSQL001->bind_result($v_host, $v_username, $v_pass, $v_port, $v_mail_from, $v_xmldir, $v_servidor);
$resSQL001->fetch();


if ($tipoT == 'factura') {
        //Busco Memo en el parametro 108 
    $sqlParametro = "SELECT Memo FROM {$prefijodb}parametro where id2='108'";
    $resSQLPara = $cnx_cfdi3->prepare($sqlParametro); 
    if (!$resSQLPara) {
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    }

    if (!$resSQLPara->execute()) {
        $mensaje  = 'Consulta no valida: ' . $resSQLPara->error . "\n";
        die($mensaje);
    }	
    $resSQLPara->store_result();
    $resSQLPara->bind_result($memo);
    $resSQLPara->fetch();

    if ($memo===''){
        $memo = 'Se anexa PDF y XML';    
    }

    //Busco Xfolio y Razón Social cliente para poder enviar los datos en el correo.
    //Buscar nombre del archivo XML 
	$sqlFactura = "SELECT a.cfdserie, a.cfdfolio, b.RazonSocial, b.CorreoFactura
                    FROM {$prefijodb}factura a
                    INNER JOIN {$prefijodb}clientes b ON a.CargoAFactura_RID = b.ID
                        WHERE a.id = $idDocumento";
                    $resSQLFac = $cnx_cfdi3->prepare($sqlFactura);
                    $resSQLFac->execute();
                    $resSQLFac->store_result();
                    $resSQLFac->bind_result($serie, $folio, $razonSocial, $correoCliente);
                    $resSQLFac->fetch();
                    
                    $xfolio =  $serie.'-'.$folio;
                    // Ahora arma la ruta directo con xmldir
                    $pdfFilename =  $serie.'-'.$folio . ".pdf";
                    $xmlFilename =  $serie.'-'.$folio . ".xml";

                    $localPDFPath  = "C:/xampp/htdocs{$v_xmldir}/" . $pdfFilename;
                    $localXMLPath = "C:/xampp/htdocs{$v_xmldir}/" . $xmlFilename;

                    $filePDF = $localPDFPath;
                    $filePath = $localXMLPath;

    if (!file_exists($filePDF)) {
        die("El archivo PDF no existe: $localPDFPath");
    }
    if (!file_exists($filePath)) {
        die("El archivo XML no existe: $localXMLPath");
    }
    $subject = $prefijodb.'  Factura: ';
} elseif ($tipoT == 'abonos') {
    
    //Busco Memo en el parametro 108 
    $sqlParametro = "SELECT Memo FROM {$prefijodb}parametro where id2='109'";
    $resSQLPara = $cnx_cfdi3->prepare($sqlParametro); 
    if (!$resSQLPara) {
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    }
    
    if (!$resSQLPara->execute()) {
        $mensaje  = 'Consulta no valida: ' . $resSQLPara->error . "\n";
        die($mensaje);
    }	
    $resSQLPara->store_result();
    $resSQLPara->bind_result($memo);
    $resSQLPara->fetch();
    
    if ($memo===''){
        $memo = 'Se anexa PDF y XML';    
    }
    
    //Busco Xfolio y Razón Social cliente para poder enviar los datos en el correo.
    $sqlFactura = "SELECT c.SerieFiscal, a.Folio, b.RazonSocial, a.XFolio FROM {$prefijodb}abonos a
                     INNER JOIN {$prefijodb}clientes b ON a.Cliente_RID = b.ID 
                     INNER JOIN {$prefijodb}oficinas c ON a.Oficina_RID = c.ID 
                     WHERE a.id=$idDocumento";

    $resSQLFac = $cnx_cfdi3->prepare($sqlFactura); 
    if (!$resSQLFac) {
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    }
    
    if (!$resSQLFac->execute()) {
        $mensaje  = 'Consulta no valida: ' . $resSQLFac->error . "\n";
        die($mensaje);
    }	
    $resSQLFac->store_result();
    $resSQLFac->bind_result($SerieFiscal, $Folio, $razonSocial, $xfolio);
    $resSQLFac->fetch();
    
    
    $correoCliente = $_GET['correo'];
    
    $localPDFPath = 'C:/xampp/htdocs/'.$v_xmldir.'/P'.$SerieFiscal.$Folio.'='.$SerieFiscal.'-'.$Folio.'.pdf';
    $localXMLPath = 'C:/xampp/htdocs/'.$v_xmldir.'/P'.$SerieFiscal.$Folio.'='.$SerieFiscal.'-'.$Folio.'.xml';
    
    $pdfFilename = $localPDFPath;
    $xmlFilename = $localXMLPath;
    
    if (!file_exists($localPDFPath)) {
        die("El archivo PDF no existe: $localPDFPath");
    }
    if (!file_exists($localXMLPath)) {
        die("El archivo XML no existe: $localXMLPath");
    }
    $subject = $prefijodb.'  Complemento: ';
} elseif ($tipoT == 'remisiones') {
            //Busco Memo en el parametro 108 
    $sqlParametro = "SELECT Memo FROM {$prefijodb}parametro where id2='108'";
    $resSQLPara = $cnx_cfdi3->prepare($sqlParametro); 
    if (!$resSQLPara) {
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    }

    if (!$resSQLPara->execute()) {
        $mensaje  = 'Consulta no valida: ' . $resSQLPara->error . "\n";
        die($mensaje);
    }	
    $resSQLPara->store_result();
    $resSQLPara->bind_result($memo);
    $resSQLPara->fetch();

    if ($memo===''){
        $memo = 'Se anexa PDF y XML';    
    }

    $sqlRemision = "SELECT a.XFolio, b.RazonSocial FROM {$prefijodb}remisiones a Inner Join {$prefijodb}clientes b On a.CargoACliente_RID=b.ID where a.id=$idDocumento";
    $resSQLRem = $cnx_cfdi3->prepare($sqlRemision); 
    if (!$resSQLRem) {
        die("Error en la preparacion de la consulta Servidor: " . $cnx_cfdi3->error);    
    }

    if (!$resSQLRem->execute()) {
        $mensaje  = 'Consulta no valida: ' . $resSQLRem->error . "\n";
        die($mensaje);
    }	
    $resSQLRem->store_result();
    $resSQLRem->bind_result($xfolio, $razonSocial);
    $resSQLRem->fetch();

    $pdfFilenameRem = basename($_GET['pdf']);
    $xmlFilenameRem = basename($_GET['xml']);
    $correoCliente = $_GET['correo'];

    $localPDFPathRem = 'C:/xampp/htdocs/'. $v_xmldir . '/'. $pdfFilenameRem;
    $localXMLPathRem = 'C:/xampp/htdocs/'. $v_xmldir . '/'. $xmlFilenameRem;

    if (!file_exists($localPDFPathRem)) {
        die("El archivo PDF no existe: $localPDFPathRem");
    }
    if (!file_exists($localXMLPathRem)) {
        die("El archivo XML no existe: $localXMLPathRem");
    }
    $subject = $prefijodb.' Viaje: ';
    
}



$mail = new PHPMailer(true);

try {
    // Configura el servidor SMTP
    $v_port = '465'; //TEMPORAL
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $v_host;  					  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $v_username;                 // SMTP username
    $mail->Password = $v_pass;                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $v_port;                                    // TCP port to connect to
    $mail->IsHTML(true);

    $mail->setFrom($v_mail_from, 'Tractosoft'); //REMITENTE

    $array_correos = explode(";", $correoCliente);
    $no_correos = count($array_correos);
    $x=0;
    while($x < $no_correos){
        $mail->addAddress($array_correos[$x]);
        $x = $x +1;
    }

    //$mail->addAddress($correoCliente);

    $mail->Subject = $subject.$xfolio;

    $memo = str_replace('#Cliente# - #ClienteNo#', $razonSocial, $memo);
    $memo = str_replace('#Factura#', $xfolio, $memo);

    $mail->Body = $memo;

    $mail->addAttachment($localPDFPath, $pdfFilename);
    $mail->addAttachment($localXMLPath, $xmlFilename);

    $mail->send();

} 

catch (Exception $e) {
    http_response_code(500);
    echo 'Excepción PHPMailer: ' . $e->getMessage() . '<br>';
    echo 'Detalles del error SMTP: ' . $mail->ErrorInfo;
}

//catch (Exception $e) {
//    http_response_code(500);
//    echo 'Error al enviar el correo: ', $mail->ErrorInfo;
//} 

