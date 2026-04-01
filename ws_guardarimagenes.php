<?php
/******************************************
 * WebService que guarda las imagenes. Afecta las tablas prefijo_remisionesevidencias y prefijo_remisionesref
 */

//Realizo la conexion a la base de datos
include("cnx_cfdi.php");

//Selecciono la base de datos
mysql_select_db($database_cfdi, $cnx_cfdi);

$debug=0;


//Debi haber recibido el folio como parametro, hago la actualizacion de la base de datos
 

$codigo = $_POST["codigo"];
$sufijo = $_POST["sufijo"];
//$nombre = $_POST["nombre"];
//$comentarios = $_POST["comentarios"];
$usuario = $_POST["usuario"];
$password = $_POST["password"];

$image = file_get_contents($_FILES['image']['tmp_name']);
$image = addslashes($image);

$nombre_imagen = $_FILES['image']['name'];

/*// Read the file
$fp = fopen($_FILES['image']['tmp_name'], 'r');
$data = fread($fp, filesize($tmpName));
$data = addslashes($data);
fclose($fp);

$image = $data;*/


// Create the query and insert
// into our database.
//$query = "INSERT INTO tbl_images ";
//$query .= "(image) VALUES ('$data')";
//$results = mysql_query($query, $link);

//print_r($_FILES['image']);

if ($debug == 1) {
	$codigo = "G48";
	$sufijo = "soluciones_";
	//$nombre = "prueba";
	//$comentarios = "Estos son los comentarios";
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
		
		
		//Obtengo el id de remisiones
		$qry_idremisiones = "SELECT ID from " . $sufijo . "remisiones WHERE xFOLIO='" . $codigo . "'";
		//echo $qry_idremisiones;
		$result_qry_idremisiones = mysql_query($qry_idremisiones, $cnx_cfdi);
		
		if (!$result_qry_idremisiones) {
			//No encuentro ninguna remision con el codigo enviado
			echo "Error3";
		}
		else {
			$rowid = mysql_fetch_row($result_qry_idremisiones);
	
			$id_remision = $rowid[0];
			//echo "<br>ID REMISION" . $id_remision . "<br>";
			
			//Inicio la transaccion
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
				
				//echo "<br>Basidgen" . $basidgen . "<br>";
				
				$upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
				$result_upd_basidgen = mysql_query($upd_basidgen, $cnx_cfdi);
				
				if ($result_upd_basidgen) {
					//Se hizo el update sin problemas
					$endtrans = mysql_query("COMMIT", $cnx_cfdi);
					
					//Ya que tengo el Basidgen realizo el insert de la evidencia
		
					$qry_insert_evidencia = "INSERT INTO " . $sufijo . "remisionesevidencias (ID, BASVERSION, BASTIMESTAMP, Fecha, Documentador, Foto1_DOCTYPE, Foto1_DOCDATA)";
					$qry_insert_evidencia .= " VALUES (" . $basidgen . ", 3, now(), CURDATE(), '" . $usuario . "', '" . $nombre_imagen . "', '" . $image . "')";
					
					//echo "<br>Query insert evidencia" . $qry_insert_evidencia . "<br>";
					$result_insert_evidencia = mysql_query($qry_insert_evidencia, $cnx_cfdi);
					
					//echo "<br>Insert evidencia" . $result_insert_evidencia . "<br>";
					if ($result_insert_evidencia) {
						//Se realizo el insert, realizo el insert de la referencia
						$qry_insert_ref = "INSERT INTO " .$sufijo . "remisiones_ref (ID, FIELD_NAME, REN, RID, RMA) VALUES ";
						$qry_insert_ref .= "(" . $id_remision . ", 'FolioEvidencias', 'RemisionesEvidencias', " . $basidgen . ", 'FolioEvidencias')";
						
						$result_insert_ref = mysql_query($qry_insert_ref, $cnx_cfdi);
						if ($result_insert_ref) {
							//Se realizo la insercion correctamente
							echo "NOERROR";
						}
						else {
							//Hubo problemas con la insercion
							echo "Error6";
						}
					}
					else {
						//No se realizo el insert, genero un error
						echo "Error7";
					}
					
				}
				else {
					//No se pudo realizar el update del basidgen
					echo "Error5";
				}
			}
			
			
		}
		
	}
	else {
		//Regreso que el usuario no esta activo
		echo "Error2";
	}
}


?>
