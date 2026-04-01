<?php
set_time_limit(350);
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");

$prefijobd = $_GET["base"];
$idCompra = $_GET['idCompra'];
$serv = $_GET['serv'];


$queryCompras="SELECT 
    (SELECT XFolio FROM ".$prefijobd."OrdenCompra WHERE Compra = Com.XFolio) AS OrdenCompra,
    (SELECT Email FROM ".$prefijobd."Proveedores WHERE ID = Com.ProveedorNo_RID) AS Correos
    FROM ".$prefijobd."Compras Com WHERE Com.ID = '".$idCompra."';";
$runsqlCompras = mysqli_query($cnx_cfdi2, $queryCompras);
if (!$runsqlCompras) {//debug
    $mensajeE  = 'Consulta no valida [Compras]: ' . mysqli_error() . "\n";
    //$mensajeE .= 'Consulta completa: ' . $queryCompras;
    //die($mensajeE);
}while ($rowsqlCompras = mysqli_fetch_assoc($runsqlCompras)){

    $folioOC = $rowsqlCompras['OrdenCompra'];
    $correoProveedor = isset($rowsqlCompras['Correos']) ? $rowsqlCompras['Correos'] : '';
    
}


//


$mensaje = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificacion Evidencia Revisada ' . $folioOC .'</title>
</head>
<body>
    <p>Proveedor,</p>
    <p>Su documentación de la <strong>OC '.$folioOC.'</strong> ha sido <strong>aprobada</strong> y prosigue al proceso de Pago. </p>
    <br><br>
    <p>Liga del Portal: '.$serv.':8443/AwareIM/logonOp.aw?domain='.str_replace('_', '', $prefijobd).'</p><br>


    <p>Saludos cordiales,</p>
    <p>Departamento de Compras<br>
</p>
</body>
</html>';

$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress
            FROM " . $prefijobd . "systemsettings S";
$runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
$rowSQL4 = mysqli_fetch_assoc($runSQL4);

$v_host = $rowSQL4['OutgoingEmailHost'];
$v_username = $rowSQL4['OutgoingEmailUserName'];
$v_pass = $rowSQL4['OutgoingEmailPassword'];
$v_port = $rowSQL4['OutgoingEmailPort'];
$v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];

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
$correosValidos = [];
$array_correos = explode(";", $correoProveedor);
foreach ($array_correos as $correo) {
    $correo = trim($correo);
    if (!empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mail->addAddress($correo);
        $correosValidos[] = $correo;
    }
}
if (empty($correosValidos)) {
    echo "<script>alert('Ningún correo válido encontrado.')</script>";
    die();
}else{

    
    $mail->Subject = 'Notificacion Evidencia Revisada ' . $folioOC;
    $mail->Body = $mensaje;
    
    
    if (!$mail->send()) {
        echo "No se pudo enviar<br>";
        echo "ERROR de PHPMailer " . $mail->ErrorInfo . "<br>";
    } else {
        echo "<script>alert('Correo enviado. Puede cerrar esta pestaña')</script>";
    }
}

?>
