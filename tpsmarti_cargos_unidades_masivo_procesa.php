<?php
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
//mysqli_query($conexion,"SET NAMES 'utf8'");

$prefijobd = $_POST['base'];
$importe = $_POST['importe'];
$comentario = $_POST['comentario'];
$documentador = $_POST['documentador'];

$fecha = $_POST["fechai"];
$fecha_f = date("d-m-Y", strtotime($fecha));

$time = time();
$fecha_actual = date("Y-m-d H:i:s", $time);


//////////Buscar Unidades
//Solo Unidades Tipo=Unidad
$resSQL11="SELECT * FROM ".$prefijobd."unidades WHERE Tipo='Unidad'";
//echo $resSQL11;
$runSQL11=mysql_query($resSQL11);
while ($rowSQL11=mysql_fetch_array($runSQL11)){
	//Obtener_variables
	$id_unidad = $rowSQL11['ID'];
	//$unidad = $rowSQL11['Unidad'];
	
	//Crear nuevo registro en CargosUnidades
	
	//Obtener Folios
	$resSQL20="SELECT MAX(Folio) as folio FROM ".$prefijobd."cargosunidades";
	//echo $resSQL20;
	$runSQL20=mysql_query($resSQL20);
	while ($rowSQL20=mysql_fetch_array($runSQL20)){
		//Obtener_variables
		$folio_v = $rowSQL20['folio'] + 1;
	}
	
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
		}
		
	}
	
	$newid = $basidgen;
	
	$sql = "INSERT INTO " . $prefijobd . "cargosunidades (ID, BASVERSION, BASTIMESTAMP, Folio, Documentador, Importe, Comentarios, Creado, Unidad_REN, Unidad_RID) VALUES (".$newid.", 1, '".$fecha_actual."', '".$folio_v."', '".$documentador."', ".$importe.", '".$comentario."', '".$fecha."', 'Unidades', ".$id_unidad.")";
	
	//echo $sql."<br>";
		
	mysql_query($sql,$cnx_cfdi);
	
	

	
}

echo "<h2>Se crearon los registros de Cargos Unidades por Unidad con Exito</h2>"





?>

