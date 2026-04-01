<?php
$hostname_cfdi = "localhost";
$database_cfdi = "basdb";
$username_cfdi = "root";
$password_cfdi = "OqqIfA9EAK0WVnDVRM";
$cnx_cfdi3 = mysqli_connect($hostname_cfdi, $username_cfdi, $password_cfdi, $database_cfdi) 
    or die(mysqli_error($cnx_cfdi3)); 
	
if ($cnx_cfdi3->connect_error) {
    die('Error de conexión a la base de datos: ' . $cnx_cfdi3->connect_error);
}
?>