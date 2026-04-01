<?php

require_once('cnx_cfdi2.php');
require('PHPMailer/PHPMailerAutoload.php');
require("PHPMailer/class.phpmailer.php");
require("PHPMailer/class.smtp.php");
mysqli_select_db($cnx_cfdi2,$database_cfdi);

$prefijobd = $_GET["prefijodb"];
$usuario = $_GET["usuario"];
$codigo = rand(100000, 999999);
$update = "UPDATE ".$prefijobd."Usuarios SET Codigo2V='".$codigo."' WHERE ID =" .$usuario.";";//Query
          //die($update);
          $result_update = mysqli_query($cnx_cfdi2,$update);//Ejecuta Query
          if (!$result_update) {//debug
            $mensaje  = 'Hubo un error sql [Reporta a soporte |OPC1] ' . mysql_error() . "\n";
            //$mensaje .= 'Consulta completa: ' . $update;
            die($mensaje);
          }
//Buscar datos de correo 
$resSQL4 = "SELECT S.OutgoingEmailHost, S.OutgoingEmailUserName, S.OutgoingEmailPassword, S.OutgoingEmailPort, S.OutgoingEmailFromAddress FROM ".$prefijobd."systemsettings S;";
    $runSQL4 = mysqli_query($cnx_cfdi2, $resSQL4);
    while($rowSQL4 = mysqli_fetch_assoc($runSQL4)){
        $v_host = $rowSQL4['OutgoingEmailHost'];
        $v_username = $rowSQL4['OutgoingEmailUserName'];
        $v_pass = $rowSQL4['OutgoingEmailPassword'];
        $v_port = $rowSQL4['OutgoingEmailPort'];
        $v_mail_from = $rowSQL4['OutgoingEmailFromAddress'];
        
}

//die($v_host.'/'.$v_username.'/'.$v_pass.'/'.$v_port.'/'.$v_mail_from.'/');

$resSQL5 = "SELECT CorreoEmpresa, Nombre FROM ".$prefijobd."Usuarios WHERE ID =" .$usuario.";";
    $runSQL5 = mysqli_query($cnx_cfdi2, $resSQL5);
    while($rowSQL5 = mysqli_fetch_assoc($runSQL5)){
        $correo = $rowSQL5['CorreoEmpresa'];
        $nombreDestino = $rowSQL5['Nombre'];
}

$mail = new PHPMailer();

$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->Host = $v_host;
$mail->SMTPAuth = true;
$mail->Username = $v_username;
$mail->Password = $v_pass;
$mail->SMTPSecure = 'ssl';
$mail->Port = $v_port;
$mail->IsHTML(true);
$mail->setFrom($v_mail_from);
$mail->addAddress($correo, $nombreDestino);
$mail->Subject = 'Codigo verificacion Tractosoft';
$mail->Body = 'Tu codigo de acceso es: ' . $codigo;

if ($mail->send() == false) {
    echo 'Codigo generado y correo enviado correctamente. Puede cerrar esta pestaña';
} else {
    // Si hubo un error al enviar el correo, registrar el error en el archivo de registro
    $errorMessage = 'Error al enviar el correo: ' . $mail->ErrorInfo;
    file_put_contents('logMail2daVerificacion.txt', $errorMessage . "\n", FILE_APPEND);
}

?>