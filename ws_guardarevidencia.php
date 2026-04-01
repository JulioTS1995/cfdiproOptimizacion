<?php
/******************************************
 * WebService que guarda los datos del folio. Pendiente ver si sera un solo webservice para todas las operaciones
 */

//Realizo la conexion a la base de datos
include("cnx_cfdi.php");

//Selecciono la base de datos
mysql_select_db($database_cfdi, $cnx_cfdi);

$debug=0;


//Debi haber recibido el folio como parametro, hago la actualizacion de la base de datos
$codigo = $_POST["codigo"];
$sufijo = $_POST["sufijo"];
$nombre = $_POST["nombre"];
$comentarios = $_POST["comentarios"];
$usuario = $_POST["usuario"];
$password = $_POST["password"];

if ($debug == 1) {
	$codigo = "G48";
	$sufijo = "soluciones_";
	$nombre = "prueba";
	$comentarios = "Estos son los comentarios";
	$usuario = "prueba";
	$password = "3333333333";
}
		
//Antes de realizar cualquier operacion valido que el usuario este activo y los datos correctos, en caso contrario no realizo nada y devuelvo un error
$qry_login = "SELECT LoginName, Activo FROM " . $sufijo . "usuarios WHERE LoginName='" . $usuario . "' AND celular='" . $password . "';";
//echo $qry_login;

$result_qrylogin = mysql_query($qry_login, $cnx_cfdi);

//echo "Result query: <br>" . $result_qrylogin;
//echo "<br>";
if (!$result_qrylogin){
	//No encontre al usuario
	echo "Error1";
}
else {
	//Valido que el usuario este activo
	$rowusuario = mysql_fetch_row($result_qrylogin);
	
	$loginName = $rowusuario[0];
	$activo = $rowusuario[1];

	if ($activo == "1") {
		//Usuario activo, puedo hacer las modificaciones
		$upd_qry = "UPDATE " . $sufijo . "remisiones SET zwRecibio='" . $nombre . "', zwRecibioComentarios='" . $comentarios . "', zwRecibioTiempo=now() WHERE xFolio='" . $codigo . "'";
		//echo $upd_qry;
		$resultqry = mysql_query($upd_qry, $cnx_cfdi);
		
		if ($resultqry) {
			//Si pudo realizar el update, regreso exito
			echo "NOERROR";
		} 
		else {
			//No pudo realizar el update, regreso fracaso
			echo "Error3";
		}
	}
	else {
		//Regreso que el usuario no esta activo
		echo "Error2";
	}
}


?>
